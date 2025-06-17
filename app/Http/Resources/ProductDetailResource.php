<?php

// File: app/Http/Resources/ProductDetailResource.php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array matching the requested format.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $version = $this->resource['version'];
        $performance = $this->resource['performance'];

        $data = [
            'Heat Input (kW)' => (float) $performance->heat_input_kw,
            'Primary Flow Rate (l/s)' => (float) $performance->primary_flow_rate_ls,
            'Secondary Flow Rate (l/s)' => (float) $performance->secondary_flow_rate_ls,
            'Pressure Drop (kPA)' => (float) $performance->pressure_drop_kpa,
            'Model' => $this->formatModelNumber($version->model_number),
        ];

        // Add temperature data if available
        if ($performance->temperatureProfile) {
            $data['Primary Flow Temp'] = (float) $performance->temperatureProfile->primary_flow_temp;
            $data['Primary Return Temp'] = (float) $performance->temperatureProfile->primary_return_temp;
            $data['Secondary Flow Temp'] = (float) $performance->temperatureProfile->secondary_flow_temp;
            $data['Secondary Return Temp'] = (float) $performance->temperatureProfile->secondary_return_temp;
        }

        // Add vessel capacity if available
        if ($performance->vesselConfiguration && $performance->vesselConfiguration->capacity) {
            $data['Vessel Capacity'] = (float) $performance->vesselConfiguration->capacity;
        }

        // Add DHW data if available
        if ($performance->first_hour_dhw_supply) {
            $data['First Hour DHW Supply (L)'] = (float) $performance->first_hour_dhw_supply;
        }

        if ($performance->subsequent_hour_dhw_supply) {
            $data['Subsequent Hour DHW Supply (L)'] = (float) $performance->subsequent_hour_dhw_supply;
        }

        // Add calculated metrics
        $data['Efficiency Ratio'] = (float) $performance->efficiency_ratio;

        // Add additional metrics if available
        if ($performance->additional_metrics) {
            foreach ($performance->additional_metrics as $key => $value) {
                $data[$key] = is_numeric($value) ? (float) $value : $value;
            }
        }

        return $data;
    }

    /**
     * Format model number to ensure it's the correct type
     */
    private function formatModelNumber($modelNumber)
    {
        // If model number is numeric, return as integer
        if (is_numeric($modelNumber)) {
            return (int) $modelNumber;
        }

        // If it contains only digits, convert to int
        if (preg_match('/^\d+$/', $modelNumber)) {
            return (int) $modelNumber;
        }

        // Otherwise return as string (for models like "30/120")
        return $modelNumber;
    }
}
