<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function destroy(Attachment $attachment)
    {
        try {
            // Delete file from S3
            Storage::disk('s3')->delete($attachment->file_path);
            
            // Delete from database
            $attachment->delete();
            
            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Attachment deleted successfully']);
            }
            
            return redirect()->back()->with('success', 'Attachment deleted successfully');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete attachment'], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to delete attachment');
        }
    }
    
    public function preview(Attachment $attachment)
    {
        // For images and PDFs, we can display them directly
        if ($attachment->is_image || $attachment->is_pdf) {
            return response()->stream(function () use ($attachment) {
                echo Storage::disk('s3')->get($attachment->file_path);
            }, 200, [
                'Content-Type' => $attachment->mime_type,
                'Content-Disposition' => 'inline; filename="' . $attachment->file_name . '"',
            ]);
        }
        
        // For other files, redirect to download
        return redirect($attachment->url);
    }
}
