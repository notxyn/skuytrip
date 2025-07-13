<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attraction;
use App\Models\Booking;
use Mockery;

class RecommendationServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_recommends_random_attractions_for_new_user()
    {
        $user = new User();
        $user->id = 1;
        
        // Mock the bookings relationship
        $bookings = Mockery::mock();
        $bookings->shouldReceive('pluck')
            ->with('attraction_id')
            ->andReturn(collect([]));
        
        $user->shouldReceive('bookings')
            ->andReturn($bookings);
        
        // Mock Attraction model
        $attractions = collect([
            (object) ['id' => 1, 'name' => 'Beach Resort', 'tags' => ['tourism', 'adventure'], 'loc' => 'Jakarta'],
            (object) ['id' => 2, 'name' => 'Mountain View', 'tags' => ['outdoor', 'adventure'], 'loc' => 'Bandung'],
            (object) ['id' => 3, 'name' => 'Cultural Center', 'tags' => ['cultural', 'historical'], 'loc' => 'Surabaya']
        ]);
        
        Attraction::shouldReceive('inRandomOrder')
            ->andReturnSelf();
        Attraction::shouldReceive('limit')
            ->with(4)
            ->andReturnSelf();
        Attraction::shouldReceive('get')
            ->andReturn($attractions);
        
        $recommendations = $user->recommendedAttractions(4);
        
        $this->assertEquals($attractions, $recommendations);
        $this->assertCount(3, $recommendations);
    }

    /** @test */
    public function it_recommends_attractions_based_on_booking_history()
    {
        $user = new User();
        $user->id = 1;
        
        // Mock the bookings relationship
        $bookings = Mockery::mock();
        $bookings->shouldReceive('pluck')
            ->with('attraction_id')
            ->andReturn(collect([1, 2]));
        
        $user->shouldReceive('bookings')
            ->andReturn($bookings);
        
        // Mock booked attractions
        $bookedAttraction1 = new Attraction();
        $bookedAttraction1->id = 1;
        $bookedAttraction1->tags = ['tourism', 'adventure'];
        $bookedAttraction1->loc = 'Jakarta';
        
        $bookedAttraction2 = new Attraction();
        $bookedAttraction2->id = 2;
        $bookedAttraction2->tags = ['family', 'outdoor'];
        $bookedAttraction2->loc = 'Bandung';
        
        $bookedAttractions = collect([$bookedAttraction1, $bookedAttraction2]);
        
        // Mock available attractions
        $availableAttraction1 = new Attraction();
        $availableAttraction1->id = 3;
        $availableAttraction1->tags = ['tourism', 'adventure', 'family'];
        $availableAttraction1->loc = 'Jakarta';
        
        $availableAttraction2 = new Attraction();
        $availableAttraction2->id = 4;
        $availableAttraction2->tags = ['cultural', 'historical'];
        $availableAttraction2->loc = 'Surabaya';
        
        $availableAttractions = collect([$availableAttraction1, $availableAttraction2]);
        
        Attraction::shouldReceive('whereIn')
            ->with('id', [1, 2])
            ->andReturnSelf();
        Attraction::shouldReceive('get')
            ->andReturn($bookedAttractions);
        
        Attraction::shouldReceive('whereNotIn')
            ->with('id', [1, 2])
            ->andReturnSelf();
        Attraction::shouldReceive('get')
            ->andReturn($availableAttractions);
        
        $recommendations = $user->recommendedAttractions(2);
        
        // Should return attractions sorted by similarity score
        $this->assertCount(2, $recommendations);
    }

    /** @test */
    public function it_calculates_similarity_score_correctly()
    {
        $attraction1 = new Attraction();
        $attraction1->tags = ['tourism', 'adventure', 'family'];
        $attraction1->loc = 'Jakarta';

        $attraction2 = new Attraction();
        $attraction2->tags = ['tourism', 'adventure', 'outdoor'];
        $attraction2->loc = 'Jakarta';

        $similarity = $attraction1->similarityTo($attraction2);
        
        // Tags similarity: intersection = 2, union = 4, score = 2/4 = 0.5
        // Location similarity: same location = 1
        // Weighted: 0.7 * 0.5 + 0.3 * 1 = 0.35 + 0.3 = 0.65
        $this->assertEquals(0.65, $similarity);
    }

    /** @test */
    public function it_prioritizes_high_similarity_attractions()
    {
        $user = new User();
        $user->id = 1;
        
        // Mock the bookings relationship
        $bookings = Mockery::mock();
        $bookings->shouldReceive('pluck')
            ->with('attraction_id')
            ->andReturn(collect([1]));
        
        $user->shouldReceive('bookings')
            ->andReturn($bookings);
        
        // Mock booked attraction
        $bookedAttraction = new Attraction();
        $bookedAttraction->id = 1;
        $bookedAttraction->tags = ['tourism', 'adventure'];
        $bookedAttraction->loc = 'Jakarta';
        
        $bookedAttractions = collect([$bookedAttraction]);
        
        // Mock available attractions with different similarity scores
        $highSimilarityAttraction = new Attraction();
        $highSimilarityAttraction->id = 2;
        $highSimilarityAttraction->tags = ['tourism', 'adventure', 'family'];
        $highSimilarityAttraction->loc = 'Jakarta';
        
        $lowSimilarityAttraction = new Attraction();
        $lowSimilarityAttraction->id = 3;
        $lowSimilarityAttraction->tags = ['cultural', 'historical'];
        $lowSimilarityAttraction->loc = 'Surabaya';
        
        $availableAttractions = collect([$lowSimilarityAttraction, $highSimilarityAttraction]);
        
        Attraction::shouldReceive('whereIn')
            ->with('id', [1])
            ->andReturnSelf();
        Attraction::shouldReceive('get')
            ->andReturn($bookedAttractions);
        
        Attraction::shouldReceive('whereNotIn')
            ->with('id', [1])
            ->andReturnSelf();
        Attraction::shouldReceive('get')
            ->andReturn($availableAttractions);
        
        $recommendations = $user->recommendedAttractions(1);
        
        // Should return the attraction with highest similarity
        $this->assertCount(1, $recommendations);
    }

    /** @test */
    public function it_handles_empty_tags_in_similarity_calculation()
    {
        $attraction1 = new Attraction();
        $attraction1->tags = [];
        $attraction1->loc = 'Jakarta';

        $attraction2 = new Attraction();
        $attraction2->tags = ['tourism', 'adventure'];
        $attraction2->loc = 'Jakarta';

        $similarity = $attraction1->similarityTo($attraction2);
        
        // Tags similarity: intersection = 0, union = 2, score = 0/2 = 0
        // Location similarity: same location = 1
        // Weighted: 0.7 * 0 + 0.3 * 1 = 0.3
        $this->assertEquals(0.3, $similarity);
    }

    /** @test */
    public function it_handles_null_tags_in_similarity_calculation()
    {
        $attraction1 = new Attraction();
        $attraction1->tags = null;
        $attraction1->loc = 'Jakarta';

        $attraction2 = new Attraction();
        $attraction2->tags = ['tourism', 'adventure'];
        $attraction2->loc = 'Jakarta';

        $similarity = $attraction1->similarityTo($attraction2);
        
        // Tags similarity: intersection = 0, union = 2, score = 0/2 = 0
        // Location similarity: same location = 1
        // Weighted: 0.7 * 0 + 0.3 * 1 = 0.3
        $this->assertEquals(0.3, $similarity);
    }

    /** @test */
    public function it_handles_different_locations_in_similarity_calculation()
    {
        $attraction1 = new Attraction();
        $attraction1->tags = ['tourism', 'adventure', 'family'];
        $attraction1->loc = 'Jakarta';

        $attraction2 = new Attraction();
        $attraction2->tags = ['tourism', 'adventure', 'outdoor'];
        $attraction2->loc = 'Bandung';

        $similarity = $attraction1->similarityTo($attraction2);
        
        // Tags similarity: intersection = 2, union = 4, score = 2/4 = 0.5
        // Location similarity: different location = 0
        // Weighted: 0.7 * 0.5 + 0.3 * 0 = 0.35
        $this->assertEquals(0.35, $similarity);
    }

    /** @test */
    public function it_handles_identical_attractions()
    {
        $attraction1 = new Attraction();
        $attraction1->tags = ['tourism', 'adventure', 'family'];
        $attraction1->loc = 'Jakarta';

        $attraction2 = new Attraction();
        $attraction2->tags = ['tourism', 'adventure', 'family'];
        $attraction2->loc = 'Jakarta';

        $similarity = $attraction1->similarityTo($attraction2);
        
        // Tags similarity: intersection = 3, union = 3, score = 3/3 = 1
        // Location similarity: same location = 1
        // Weighted: 0.7 * 1 + 0.3 * 1 = 1.0
        $this->assertEquals(1.0, $similarity);
    }

    /** @test */
    public function it_limits_recommendations_correctly()
    {
        $user = new User();
        $user->id = 1;
        
        // Mock the bookings relationship
        $bookings = Mockery::mock();
        $bookings->shouldReceive('pluck')
            ->with('attraction_id')
            ->andReturn(collect([]));
        
        $user->shouldReceive('bookings')
            ->andReturn($bookings);
        
        // Mock Attraction model
        $attractions = collect([
            (object) ['id' => 1, 'name' => 'Beach Resort'],
            (object) ['id' => 2, 'name' => 'Mountain View'],
            (object) ['id' => 3, 'name' => 'Cultural Center'],
            (object) ['id' => 4, 'name' => 'Adventure Park'],
            (object) ['id' => 5, 'name' => 'Historical Museum']
        ]);
        
        Attraction::shouldReceive('inRandomOrder')
            ->andReturnSelf();
        Attraction::shouldReceive('limit')
            ->with(3)
            ->andReturnSelf();
        Attraction::shouldReceive('get')
            ->andReturn($attractions->take(3));
        
        $recommendations = $user->recommendedAttractions(3);
        
        $this->assertCount(3, $recommendations);
    }
} 