<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'name', 'img', 'loc', 'desc', 'rate', 'price', 'tags'
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    /**
     * Compute a similarity score between this attraction and another based on tags and location.
     */
    public function similarityTo(Attraction $other)
    {
        // Tags similarity (Jaccard index)
        $tags1 = collect($this->tags ?? []);
        $tags2 = collect($other->tags ?? []);
        $intersection = $tags1->intersect($tags2)->count();
        $union = $tags1->merge($tags2)->unique()->count();
        $tagScore = $union > 0 ? $intersection / $union : 0;

        // Location similarity (exact match = 1, else 0)
        $locScore = ($this->loc === $other->loc) ? 1 : 0;

        // Weighted sum (tags more important than location)
        return 0.7 * $tagScore + 0.3 * $locScore;
    }
}
