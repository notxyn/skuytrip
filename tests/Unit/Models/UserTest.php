<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;
use App\Models\Attraction;
use Mockery;

class UserTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_has_correct_fillable_fields()
    {
        $user = new User();
        $fillable = $user->getFillable();
        
        $expectedFillable = ['name', 'email', 'password', 'is_admin'];
        
        $this->assertEquals($expectedFillable, $fillable);
    }

    /** @test */
    public function it_has_correct_hidden_fields()
    {
        $user = new User();
        $hidden = $user->getHidden();
        
        $expectedHidden = ['password', 'remember_token'];
        
        $this->assertEquals($expectedHidden, $hidden);
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $user = new User();
        $casts = $user->getCasts();
        
        $this->assertArrayHasKey('email_verified_at', $casts);
        $this->assertEquals('datetime', $casts['email_verified_at']);
        
        $this->assertArrayHasKey('password', $casts);
        $this->assertEquals('hashed', $casts['password']);
    }

    /** @test */
    public function it_has_many_bookings()
    {
        $user = new User();
        $booking = Mockery::mock(Booking::class);
        
        // Mock the relationship
        $user->shouldReceive('hasMany')
            ->with(Booking::class)
            ->andReturn($booking);
        
        $this->assertInstanceOf(Booking::class, $user->bookings());
    }

    /** @test */
    public function it_uses_has_factory_trait()
    {
        $user = new User();
        
        $this->assertTrue(method_exists($user, 'factory'));
    }

    /** @test */
    public function it_uses_notifiable_trait()
    {
        $user = new User();
        
        $this->assertTrue(method_exists($user, 'notify'));
    }

    /** @test */
    public function it_returns_true_for_admin_user()
    {
        $user = new User();
        $user->is_admin = true;
        
        $this->assertTrue($user->isAdmin());
    }

    /** @test */
    public function it_returns_false_for_non_admin_user()
    {
        $user = new User();
        $user->is_admin = false;
        
        $this->assertFalse($user->isAdmin());
    }

    /** @test */
    public function it_returns_false_for_null_admin_status()
    {
        $user = new User();
        $user->is_admin = null;
        
        $this->assertFalse($user->isAdmin());
    }

    /** @test */
    public function it_recommends_random_attractions_when_no_bookings()
    {
        $user = new User();
        
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
            (object) ['id' => 2, 'name' => 'Mountain View']
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
    }

    /** @test */
    public function it_recommends_attractions_based_on_booking_history()
    {
        $user = new User();
        
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
    public function it_handles_similarity_calculation_in_recommendations()
    {
        $user = new User();
        
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
        
        // Mock available attraction with high similarity
        $availableAttraction = new Attraction();
        $availableAttraction->id = 2;
        $availableAttraction->tags = ['tourism', 'adventure', 'family'];
        $availableAttraction->loc = 'Jakarta';
        
        $availableAttractions = collect([$availableAttraction]);
        
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
    public function it_can_access_bookings_relationship()
    {
        $user = new User();
        $user->id = 1;
        
        // Mock the bookings relationship
        $booking1 = Mockery::mock(Booking::class);
        $booking1->id = 1;
        $booking1->attraction_id = 1;
        
        $booking2 = Mockery::mock(Booking::class);
        $booking2->id = 2;
        $booking2->attraction_id = 2;
        
        $bookings = collect([$booking1, $booking2]);
        
        $user->setRelation('bookings', $bookings);
        
        $this->assertCount(2, $user->bookings);
        $this->assertInstanceOf(Booking::class, $user->bookings->first());
    }
} 