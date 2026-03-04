<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $vehicles = DB::table('vehicles')
            ->select('id', 'orcr_image_path')
            ->whereNotNull('orcr_image_path')
            ->where('orcr_image_path', '!=', '')
            ->get();

        foreach ($vehicles as $vehicle) {
            $vehicleId = (int) $vehicle->id;
            $path = (string) $vehicle->orcr_image_path;

            if (str_starts_with($path, 'vehicles/orcr/' . $vehicleId . '/')) {
                continue;
            }

            if (!Storage::disk('public')->exists($path)) {
                continue;
            }

            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $filename = (string) Str::uuid() . ($extension !== '' ? '.' . $extension : '');
            $newPath = 'vehicles/orcr/' . $vehicleId . '/' . $filename;

            Storage::disk('public')->copy($path, $newPath);

            DB::table('vehicles')
                ->where('id', $vehicleId)
                ->update([
                    'orcr_image_path' => $newPath,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        // Intentionally left blank.
    }
};
