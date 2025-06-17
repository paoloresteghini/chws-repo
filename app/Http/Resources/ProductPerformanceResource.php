<?php

// File: app/Http/Resources/ProductPerformanceResource.php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductPerformanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * This resource formats performance data exactly as requested in the example
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'Heat Input (kW)' => (int) round($this->heat_input_kw),
            'Primary Flow Temp' => (int) round($this->temperatureProfile?->primary_flow_temp ?? 0),
            'Primary Return Temp' => (int) round($this->temperatureProfile?->primary_return_temp ?? 0),
            'Primary Flow Rate (l/s)' => (float) sprintf('%.2f', $this->primary_flow_rate_ls),
            'Secondary Flow Temp' => (int) round($this->temperatureProfile?->secondary_flow_temp ?? 0),
            'Secondary Return Temp' => (int) round($this->temperatureProfile?->secondary_return_temp ?? 0),
            'Secondary Flow Rate (l/s)' => (float) sprintf('%.2f', $this->secondary_flow_rate_ls),
            'Pressure Drop (kPA)' => (int) round($this->pressure_drop_kpa),
            'Model' => $this->version?->model_number ?? null,
        ];

        // Add vessel capacity if available
        if ($this->vesselConfiguration) {
            $data['Vessel Capacity'] = (int) round($this->vesselConfiguration->capacity);
        }

        // Add DHW specific fields if available (for Aquafast-type products)
        if ($this->first_hour_dhw_supply !== null) {
            $data['First Hour DHW Supply'] = (int) round($this->first_hour_dhw_supply);
        }

        if ($this->subsequent_hour_dhw_supply !== null) {
            $data['Subsequent Hour DHW Supply'] = (int) round($this->subsequent_hour_dhw_supply);
        }

        // Add additional metrics if available
        if ($this->additional_metrics && is_array($this->additional_metrics)) {
            foreach ($this->additional_metrics as $key => $value) {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}
