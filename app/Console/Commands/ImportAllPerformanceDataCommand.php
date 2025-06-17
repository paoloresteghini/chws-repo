<?php

// File: app/Console/Commands/ImportAllPerformanceDataCommand.php
namespace App\Console\Commands;

use App\Models\PerformanceData;
use App\Models\Product;
use App\Models\TemperatureProfile;
use App\Models\Version;
use App\Models\VesselConfiguration;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportAllPerformanceDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'import:all-performance-data {directory}';

    /**
     * The console command description.
     */
    protected $description = 'Import performance data from all Excel files in a directory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $directory = $this->argument('directory');

        if (!is_dir($directory)) {
            $this->error("Directory not found: {$directory}");
            return 1;
        }

        $files = [
            'ProPak.xlsx' => 'ProPak',
            'Aquafast.xlsx' => 'Aquafast',
            'ProRapid.xlsx' => 'ProRapid'
        ];

        $totalImported = 0;

        foreach ($files as $filename => $productName) {
            $filepath = rtrim($directory, '/') . '/' . $filename;

            if (!file_exists($filepath)) {
                $this->warn("File not found: {$filepath}");
                continue;
            }

            $this->info("Processing {$productName}...");
            $imported = $this->importProductData($filepath, $productName);
            $totalImported += $imported;
        }

        $this->info("Total performance data records imported: {$totalImported}");
        return 0;
    }

    private function importProductData($filepath, $productName)
    {
        $product = Product::where('name', $productName)->first();

        if (!$product) {
            $this->error("Product not found: {$productName}");
            return 0;
        }

        $reader = IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load($filepath);

        $importedCount = 0;

        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            $this->line("  Processing sheet: {$sheetName}");

            // Find or create temperature profile
            $temperatureProfile = $this->findOrCreateTemperatureProfile($sheetName);
            if (!$temperatureProfile) {
                $this->warn("    Could not process temperature profile: {$sheetName}");
                continue;
            }

            $worksheet = $spreadsheet->getSheetByName($sheetName);
            $data = $worksheet->toArray();

            // Skip header row and process data
            for ($row = 2; $row <= count($data); $row++) {
                $rowData = $data[$row - 1];

                if (empty($rowData) || !$this->isValidRow($rowData)) {
                    continue;
                }

                $imported = $this->processRow($product, $temperatureProfile, $rowData);
                if ($imported) {
                    $importedCount++;
                }
            }
        }

        $this->info("  Imported {$importedCount} records for {$productName}");
        return $importedCount;
    }

    private function findOrCreateTemperatureProfile($sheetName)
    {
        // First try to find existing profile
        $profile = TemperatureProfile::where('name', $sheetName)->first();
        if ($profile) {
            return $profile;
        }

        // Try to parse the sheet name to create a new profile
        // Expected format: "80-60,10-60"
        if (preg_match('/^(\d+)-(\d+),(\d+)-(\d+)/', trim($sheetName), $matches)) {
            $profile = TemperatureProfile::create([
                'name' => trim($sheetName),
                'primary_flow_temp' => $matches[1],
                'primary_return_temp' => $matches[2],
                'secondary_flow_temp' => $matches[3],
                'secondary_return_temp' => $matches[4],
                'description' => "Primary: {$matches[1]}°→{$matches[2]}°, Secondary: {$matches[3]}°→{$matches[4]}°",
                'is_active' => true,
            ]);

            $this->line("    Created new temperature profile: {$sheetName}");
            return $profile;
        }

        return null;
    }

    private function isValidRow($rowData)
    {
        // Check if row has essential data
        return !empty($rowData[0]) && // Heat input
            !empty($rowData[3]) && // Primary flow rate
            !empty($rowData[6]) && // Secondary flow rate
            !empty($rowData[7]); // Pressure drop or model column
    }

    private function processRow($product, $temperatureProfile, $rowData)
    {
        // Get model number and vessel info based on product type
        [$modelNumber, $vesselInfo] = $this->extractModelAndVessel($product, $rowData);

        if (!$modelNumber) {
            return false;
        }

        $version = Version::where('product_id', $product->id)
            ->where('model_number', $modelNumber)
            ->first();

        if (!$version) {
            $this->warn("    Version not found: {$modelNumber}");
            return false;
        }

        // Find vessel configuration if applicable
        $vesselConfig = null;
        if ($product->has_vessel_options && $vesselInfo) {
            $vesselConfig = VesselConfiguration::where('version_id', $version->id)
                ->where('capacity', $vesselInfo)
                ->first();

            if (!$vesselConfig && $vesselInfo) {
                // Create vessel configuration if it doesn't exist
                $vesselConfig = VesselConfiguration::create([
                    'version_id' => $version->id,
                    'name' => "{$vesselInfo}L",
                    'capacity' => $vesselInfo,
                    'capacity_unit' => 'L',
                    'description' => "{$vesselInfo} liter vessel capacity",
                ]);
                $this->line("    Created vessel configuration: {$vesselInfo}L for {$modelNumber}");
            }
        }

        // Check if data already exists
        $existingData = PerformanceData::where('version_id', $version->id)
            ->where('temperature_profile_id', $temperatureProfile->id)
            ->when($vesselConfig, fn($q) => $q->where('vessel_configuration_id', $vesselConfig->id))
            ->first();

        if ($existingData) {
            return false; // Already exists
        }

        // Create performance data based on product type
        $performanceData = $this->createPerformanceData($product, $version, $temperatureProfile, $vesselConfig, $rowData);

        if ($performanceData) {
            $this->line("    ✓ Imported: {$modelNumber}" . ($vesselInfo ? " ({$vesselInfo}L)" : ""));
            return true;
        }

        return false;
    }

    private function extractModelAndVessel($product, $rowData)
    {
        switch ($product->name) {
            case 'ProPak':
                // ProPak: Model in column 8 (index 8), no vessel
                return [$rowData[8] ?? null, null];

            case 'Aquafast':
                // Aquafast: Model in column 11 (index 11), Vessel in column 10 (index 10)
                return [$rowData[11] ?? null, $rowData[10] ?? null];

            case 'ProRapid':
                // ProRapid: Model in column 8 (index 8), Vessel Capacity in column 9 (index 9)
                return [$rowData[8] ?? null, $rowData[9] ?? null];

            default:
                return [null, null];
        }
    }

    private function createPerformanceData($product, $version, $temperatureProfile, $vesselConfig, $rowData)
    {
        $baseData = [
            'version_id' => $version->id,
            'temperature_profile_id' => $temperatureProfile->id,
            'vessel_configuration_id' => $vesselConfig?->id,
            'heat_input_kw' => $rowData[0] ?? 0,
            'primary_flow_rate_ls' => $rowData[3] ?? 0,
            'secondary_flow_rate_ls' => $rowData[6] ?? 0,
        ];

        // Add product-specific fields based on product type
        if ($product->name === 'Aquafast') {
            // Aquafast has DHW fields and pressure drop in different position
            $baseData['first_hour_dhw_supply'] = $rowData[7] ?? null;
            $baseData['subsequent_hour_dhw_supply'] = $rowData[8] ?? null;
            $baseData['pressure_drop_kpa'] = $rowData[9] ?? 0;
        } else {
            // ProPak and ProRapid have pressure drop in column 7
            $baseData['pressure_drop_kpa'] = $rowData[7] ?? 0;
        }

        try {
            return PerformanceData::create($baseData);
        } catch (\Exception $e) {
            $this->warn("    Error creating performance data: " . $e->getMessage());
            return null;
        }
    }
}
