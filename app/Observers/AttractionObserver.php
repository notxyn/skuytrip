<?php

namespace App\Observers;

use App\Models\Attraction;
use Illuminate\Support\Facades\File;

class AttractionObserver
{
    public function saved(Attraction $attraction)
    {
        if ($attraction->img && file_exists(storage_path('app/public/' . $attraction->img))) {
            $publicPath = public_path('storage/' . $attraction->img);
            $storagePath = storage_path('app/public/' . $attraction->img);
            // Always copy (overwrite) to ensure the file is up to date and available
            \Illuminate\Support\Facades\File::copy($storagePath, $publicPath);
        }
    }
} 