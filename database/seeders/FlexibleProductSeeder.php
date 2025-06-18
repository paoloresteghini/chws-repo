<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductFeature;
use App\Models\TemperatureProfile;
use App\Models\Version;
use App\Models\VersionCategory;
use App\Models\VesselConfiguration;
use Illuminate\Database\Seeder;

class FlexibleProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create all temperature profiles first (shared across products)
        $this->createTemperatureProfiles();

        // Create individual products
        $this->createProPakProduct();
        $this->createAquafastProduct();
        $this->createProRapidProduct();

        $this->command->info('All products seeded successfully!');
        $this->command->info('Next: Import performance data using: php artisan import:all-performance-data ./storage/app/excel');
    }

    private function createTemperatureProfiles()
    {
        $this->command->info('Creating temperature profiles...');

        $profiles = [
            ['name' => '80-60,10-60', 'pf' => 80, 'pr' => 60, 'sf' => 10, 'sr' => 60],
            ['name' => '80-60,10-65', 'pf' => 80, 'pr' => 60, 'sf' => 10, 'sr' => 65],
            ['name' => '82-71,10-60', 'pf' => 82, 'pr' => 71, 'sf' => 10, 'sr' => 60],
            ['name' => '82-71,10-65', 'pf' => 82, 'pr' => 71, 'sf' => 10, 'sr' => 65],
            ['name' => '80-45,10-60', 'pf' => 80, 'pr' => 45, 'sf' => 10, 'sr' => 60],
            ['name' => '80-45,10-65', 'pf' => 80, 'pr' => 45, 'sf' => 10, 'sr' => 65],
            ['name' => '70-50,10-60', 'pf' => 70, 'pr' => 50, 'sf' => 10, 'sr' => 60],
            ['name' => '70-50,10-65', 'pf' => 70, 'pr' => 50, 'sf' => 10, 'sr' => 65],
            ['name' => '70-15,10-60', 'pf' => 70, 'pr' => 15, 'sf' => 10, 'sr' => 60],
            ['name' => '70-20,10-60', 'pf' => 70, 'pr' => 20, 'sf' => 10, 'sr' => 60],
            ['name' => '70-25,10-60', 'pf' => 70, 'pr' => 25, 'sf' => 10, 'sr' => 60],
            ['name' => '70-15,10-65', 'pf' => 70, 'pr' => 15, 'sf' => 10, 'sr' => 65],
            ['name' => '70-20,10-65', 'pf' => 70, 'pr' => 20, 'sf' => 10, 'sr' => 65],
            ['name' => '70-25,10-65', 'pf' => 70, 'pr' => 25, 'sf' => 10, 'sr' => 65],
            ['name' => '70-60,10-60', 'pf' => 70, 'pr' => 60, 'sf' => 10, 'sr' => 60],
            ['name' => '70-60,10-60 ', 'pf' => 70, 'pr' => 60, 'sf' => 10, 'sr' => 60], // Note: space at end from Excel
            ['name' => '70-60,10-65', 'pf' => 70, 'pr' => 60, 'sf' => 10, 'sr' => 65],
            ['name' => '70-65,55-60', 'pf' => 70, 'pr' => 65, 'sf' => 55, 'sr' => 60],
            ['name' => '70-65,10-60', 'pf' => 70, 'pr' => 65, 'sf' => 10, 'sr' => 60],
            ['name' => '70-62,10-60', 'pf' => 70, 'pr' => 62, 'sf' => 10, 'sr' => 60],
            ['name' => '70-62,10-65', 'pf' => 70, 'pr' => 62, 'sf' => 10, 'sr' => 65],
            ['name' => '70-40,10-60', 'pf' => 70, 'pr' => 40, 'sf' => 10, 'sr' => 60],
            ['name' => '70-45,10-60', 'pf' => 70, 'pr' => 45, 'sf' => 10, 'sr' => 60],
            ['name' => '70-40,10-65', 'pf' => 70, 'pr' => 40, 'sf' => 10, 'sr' => 65],
            ['name' => '70-45,10-65', 'pf' => 70, 'pr' => 45, 'sf' => 10, 'sr' => 65],
            ['name' => '68-58,10-60', 'pf' => 68, 'pr' => 58, 'sf' => 10, 'sr' => 60],
            ['name' => '65-60,10-60', 'pf' => 65, 'pr' => 60, 'sf' => 10, 'sr' => 60],
            ['name' => '65-55,10-60', 'pf' => 65, 'pr' => 55, 'sf' => 10, 'sr' => 60],
            ['name' => '62-57, 10-60', 'pf' => 62, 'pr' => 57, 'sf' => 10, 'sr' => 60], // Note: space in name from Excel
            ['name' => '60-55,10-55', 'pf' => 60, 'pr' => 55, 'sf' => 10, 'sr' => 55],
            ['name' => '60-40,10-50', 'pf' => 60, 'pr' => 40, 'sf' => 10, 'sr' => 50],
            ['name' => '55-50,10-50', 'pf' => 55, 'pr' => 50, 'sf' => 10, 'sr' => 50],
        ];

        foreach ($profiles as $profile) {
            TemperatureProfile::firstOrCreate(
                ['name' => $profile['name']],
                [
                    'primary_flow_temp' => $profile['pf'],
                    'primary_return_temp' => $profile['pr'],
                    'secondary_flow_temp' => $profile['sf'],
                    'secondary_return_temp' => $profile['sr'],
                    'description' => "Primary: {$profile['pf']}°→{$profile['pr']}°, Secondary: {$profile['sf']}°→{$profile['sr']}°",
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✓ Temperature profiles created');
    }

    private function createProPakProduct()
    {
        $this->command->info('Creating ProPak product...');

        $product = Product::create([
            'name' => 'ProPak',
            'type' => 'heat_exchanger',
            'description' => 'High-efficiency plate heat exchanger system',
            'has_temperature_profiles' => true,
            'has_vessel_options' => false,
        ]);

        // ProPak categories
        $categories = [
            ['name' => '2500 Series', 'prefix' => '25', 'sort_order' => 1],
            ['name' => '3000 Series', 'prefix' => '30', 'sort_order' => 2],
            ['name' => '3800 Series', 'prefix' => '38', 'sort_order' => 3],
            ['name' => '4000 Series', 'prefix' => '40', 'sort_order' => 4],
            ['name' => '5000 Series', 'prefix' => '50', 'sort_order' => 5],
            ['name' => '6000 Series', 'prefix' => '60', 'sort_order' => 6],
        ];

        foreach ($categories as $categoryData) {
            VersionCategory::create(array_merge($categoryData, ['product_id' => $product->id]));
        }

        // ProPak versions (from Excel analysis)
        $modelNumbers = [
            2507, 2509, 2511, 2513, 2515,
            3017, 3023, 3025, 3029, 3033, 3039, 3045,
            3811, 3821, 3829, 3835, 3843, 3851, 3859, 3867, 3875,
            4015, 4017, 4021, 4025, 4029,
            5021, 5025, 5031, 5035, 5039, 5043, 5049,
            6031, 6035, 6039
        ];

        foreach ($modelNumbers as $modelNumber) {
            $prefix = substr($modelNumber, 0, 2);
            $category = VersionCategory::where('product_id', $product->id)->where('prefix', $prefix)->first();

            Version::create([
                'product_id' => $product->id,
                'model_number' => $modelNumber,
                'name' => "ProPak {$modelNumber}",
                'category_id' => $category?->id,
                'has_vessel_options' => false,
                'status' => true,
            ]);
        }

        $this->command->info('✓ ProPak product created with ' . count($modelNumbers) . ' versions');
    }

    private function createAquafastProduct()
    {
        $this->command->info('Creating Aquafast product...');

        $product = Product::create([
            'name' => 'Aquafast',
            'type' => 'dhw_system',
            'description' => 'Domestic Hot Water (DHW) heating system with integrated vessel',
            'has_temperature_profiles' => true,
            'has_vessel_options' => true,
        ]);

        // Aquafast features
        ProductFeature::create([
            'product_id' => $product->id,
            'feature_key' => 'dhw_metrics',
            'feature_name' => 'DHW Performance Metrics',
            'feature_config' => [
                'first_hour_supply' => true,
                'subsequent_hour_supply' => true,
                'unit' => 'liters'
            ],
            'is_enabled' => true,
        ]);

        // Aquafast categories
        $category = VersionCategory::create([
            'product_id' => $product->id,
            'name' => 'Standard Range',
            'prefix' => null,
            'sort_order' => 1,
            'description' => 'Standard DHW heating systems'
        ]);

        // Aquafast versions (from Excel analysis)
        $models = ['30/120', '40/150', '50/200', '60/250', '70/300', '80/350'];
        $vesselSizes = [1000, 1500, 2000, 2500, 3000, 4000, 5000];

        foreach ($models as $model) {
            $version = Version::create([
                'product_id' => $product->id,
                'model_number' => $model,
                'name' => "Aquafast {$model}",
                'category_id' => $category->id,
                'has_vessel_options' => true,
                'status' => true,
            ]);

            // Create vessel configurations for this version
            foreach ($vesselSizes as $size) {
                VesselConfiguration::create([
                    'version_id' => $version->id,
                    'name' => "{$size}L",
                    'capacity' => $size,
                    'capacity_unit' => 'L',
                    'description' => "{$size} liter vessel capacity",
                ]);
            }
        }

        $this->command->info('✓ Aquafast product created with ' . count($models) . ' versions and ' . count($vesselSizes) . ' vessel options each');
    }

    private function createProRapidProduct()
    {
        $this->command->info('Creating ProRapid product...');

        $product = Product::create([
            'name' => 'ProRapid',
            'type' => 'dhw_heat_exchanger',
            'description' => 'Rapid heating system with vessel capacity options',
            'has_temperature_profiles' => true,
            'has_vessel_options' => true,
        ]);

        // ProRapid categories
        $category = VersionCategory::create([
            'product_id' => $product->id,
            'name' => 'Rapid Series',
            'prefix' => null,
            'sort_order' => 1,
            'description' => 'High-performance rapid heating systems'
        ]);

        // ProRapid versions (from Excel analysis)
        $models = [200, 300, 500, 800, 1000, 1500, 2000];
        $vesselCapacities = [196, 312, 511, 883, 987, 1435, 2100];

        foreach ($models as $index => $model) {
            $version = Version::create([
                'product_id' => $product->id,
                'model_number' => $model,
                'name' => "ProRapid {$model}",
                'category_id' => $category->id,
                'has_vessel_options' => true,
                'status' => true,
            ]);

            // Create vessel configuration for this version
            if (isset($vesselCapacities[$index])) {
                VesselConfiguration::create([
                    'version_id' => $version->id,
                    'name' => "{$vesselCapacities[$index]}L",
                    'capacity' => $vesselCapacities[$index],
                    'capacity_unit' => 'L',
                    'description' => "{$vesselCapacities[$index]} liter vessel capacity",
                ]);
            }
        }

        $this->command->info('✓ ProRapid product created with ' . count($models) . ' versions');
    }
}
