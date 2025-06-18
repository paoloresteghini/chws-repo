<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();
        
        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:products',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $request->only(['name', 'description']);
        
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }
        
        Product::create($data);
        
        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load([
            'versions.category',
            'versions.performanceData',
            'versions.vesselConfigurations',
            'versions.attachments',
            'attachments'
        ]);
        
        // Calculate statistics
        $stats = [
            'total_versions' => $product->versions->count(),
            'active_versions' => $product->versions->where('status', true)->count(),
            'total_performance_records' => $product->versions->sum(function($version) {
                return $version->performanceData->count();
            }),
            'temperature_profiles_count' => $product->versions->flatMap(function($version) {
                return $version->performanceData->pluck('temperature_profile_id');
            })->unique()->count(),
            'vessel_configurations_count' => $product->versions->sum(function($version) {
                return $version->vesselConfigurations->count();
            }),
            'heat_input_range' => [
                'min' => $product->versions->flatMap->performanceData->min('heat_input_kw') ?: 0,
                'max' => $product->versions->flatMap->performanceData->max('heat_input_kw') ?: 0,
            ]
        ];
        
        // Get unique temperature profiles used by this product
        $temperatureProfiles = collect();
        if ($product->has_temperature_profiles) {
            $profileIds = $product->versions->flatMap(function($version) {
                return $version->performanceData->pluck('temperature_profile_id');
            })->unique();
            
            if ($profileIds->count() > 0) {
                $temperatureProfiles = \App\Models\TemperatureProfile::whereIn('id', $profileIds)->get();
            }
        }
        
        return view('products.show', compact('product', 'stats', 'temperatureProfiles'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $product->load('attachments');
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:products,name,' . $product->id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,txt,png,jpg,jpeg,gif|max:10240',
            'attachment_names.*' => 'nullable|string|max:255',
            'delete_attachments.*' => 'nullable|exists:attachments,id',
        ]);

        DB::beginTransaction();
        
        try {
            $data = $request->only(['name', 'description']);
            
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($product->image) {
                    \Storage::disk('public')->delete($product->image);
                }
                $data['image'] = $request->file('image')->store('products', 'public');
            }
            
            $product->update($data);
            
            // Handle attachment deletions
            if ($request->has('delete_attachments')) {
                $attachmentsToDelete = $product->attachments()->whereIn('id', $request->delete_attachments)->get();
                foreach ($attachmentsToDelete as $attachment) {
                    Storage::disk('s3')->delete($attachment->file_path);
                    $attachment->delete();
                }
            }
            
            // Handle new attachments
            if ($request->hasFile('attachments')) {
                $attachments = $request->file('attachments');
                $attachmentNames = $request->input('attachment_names', []);
                
                foreach ($attachments as $index => $file) {
                    if ($file) {
                        $path = $file->store('product-attachments/' . $product->id, 's3');
                        $name = isset($attachmentNames[$index]) && !empty($attachmentNames[$index]) 
                            ? $attachmentNames[$index] 
                            : pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        
                        $product->attachments()->create([
                            'name' => $name,
                            'file_path' => $path,
                            'file_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getMimeType(),
                            'file_size' => $file->getSize(),
                        ]);
                    }
                }
            }
            
            DB::commit();
            
            return redirect()->route('products.index')->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update product: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        DB::beginTransaction();
        
        try {
            // Delete all attachments from S3
            foreach ($product->attachments as $attachment) {
                Storage::disk('s3')->delete($attachment->file_path);
            }
            
            // Delete product image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            
            // Delete all version attachments from S3
            foreach ($product->versions as $version) {
                foreach ($version->attachments as $attachment) {
                    Storage::disk('s3')->delete($attachment->file_path);
                }
            }
            
            // The database will cascade delete all related data
            $product->delete();
            
            DB::commit();
            
            return redirect()->route('products.index')->with('success', 'Product and all related data deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete product: ' . $e->getMessage());
        }
    }
}
