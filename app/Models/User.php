<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Recommend attractions based on user's booking history (tags and location similarity).
     */
    public function recommendedAttractions($limit = 4)
    {
        $bookedAttractionIds = $this->bookings()->pluck('attraction_id')->toArray();
        $bookedAttractions = \App\Models\Attraction::whereIn('id', $bookedAttractionIds)->get();
        if ($bookedAttractions->isEmpty()) {
            // If no bookings, recommend random attractions
            return \App\Models\Attraction::inRandomOrder()->limit($limit)->get();
        }
        $all = \App\Models\Attraction::whereNotIn('id', $bookedAttractionIds)->get();
        // Compute similarity for each attraction
        $scored = $all->map(function($attr) use ($bookedAttractions) {
            $score = 0;
            foreach ($bookedAttractions as $booked) {
                $score = max($score, $attr->similarityTo($booked));
            }
            return ['attraction' => $attr, 'score' => $score];
        })->sortByDesc('score')->take($limit);
        return $scored->pluck('attraction');
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }
}
