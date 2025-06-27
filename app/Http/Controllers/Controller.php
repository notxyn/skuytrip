<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Fetch recommended attractions from the Python API for the current user.
     */
    protected function getRecommendedAttractions($limit = 4)
    {
        if (!auth()->check()) return collect();
        try {
            $response = \Illuminate\Support\Facades\Http::get('http://127.0.0.1:8000/recommend', [
                'user_id' => auth()->id(),
                'top_n' => $limit,
            ]);
            $ids = $response->json('recommendations') ?? [];
            return \App\Models\Attraction::whereIn('id', $ids)->get();
        } catch (\Exception $e) {
            return collect();
        }
    }
}
