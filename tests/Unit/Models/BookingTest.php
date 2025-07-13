<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Booking;
use App\Models\User;
use App\Models\Attraction;
use Mockery;

class BookingTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_has_correct_fillable_fields()
    {
        $booking = new Booking();
        $fillable = $booking->getFillable();
        
        $expectedFillable = [
            'user_id', 'attraction_id', 'name', 'email', 'phone', 
            'date', 'quantity', 'total', 'payment_method', 'status', 'payment_proof'
        ];
        
        $this->assertEquals($expectedFillable, $fillable);
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $booking = new Booking();
        $casts = $booking->getCasts();
        
        $this->assertArrayHasKey('date', $casts);
        $this->assertEquals('date', $casts['date']);
        
        $this->assertArrayHasKey('total', $casts);
        $this->assertEquals('decimal:2', $casts['total']);
    }

    /** @test */
    public function it_belongs_to_user()
    {
        $booking = new Booking();
        $user = Mockery::mock(User::class);
        
        // Mock the relationship
        $booking->shouldReceive('belongsTo')
            ->with(User::class)
            ->andReturn($user);
        
        $this->assertInstanceOf(User::class, $booking->user());
    }

    /** @test */
    public function it_belongs_to_attraction()
    {
        $booking = new Booking();
        $attraction = Mockery::mock(Attraction::class);
        
        // Mock the relationship
        $booking->shouldReceive('belongsTo')
            ->with(Attraction::class)
            ->andReturn($attraction);
        
        $this->assertInstanceOf(Attraction::class, $booking->attraction());
    }

    /** @test */
    public function it_returns_correct_status_color_for_pending()
    {
        $booking = new Booking();
        $booking->status = 'pending';
        
        $this->assertEquals('warning', $booking->status_color);
    }

    /** @test */
    public function it_returns_correct_status_color_for_paid()
    {
        $booking = new Booking();
        $booking->status = 'paid';
        
        $this->assertEquals('success', $booking->status_color);
    }

    /** @test */
    public function it_returns_correct_status_color_for_cancelled()
    {
        $booking = new Booking();
        $booking->status = 'cancelled';
        
        $this->assertEquals('danger', $booking->status_color);
    }

    /** @test */
    public function it_returns_correct_status_color_for_refunded()
    {
        $booking = new Booking();
        $booking->status = 'refunded';
        
        $this->assertEquals('info', $booking->status_color);
    }

    /** @test */
    public function it_returns_default_status_color_for_unknown_status()
    {
        $booking = new Booking();
        $booking->status = 'unknown';
        
        $this->assertEquals('secondary', $booking->status_color);
    }

    /** @test */
    public function it_returns_correct_payment_method_color_for_visa()
    {
        $booking = new Booking();
        $booking->payment_method = 'visa';
        
        $this->assertEquals('primary', $booking->payment_method_color);
    }

    /** @test */
    public function it_returns_correct_payment_method_color_for_mastercard()
    {
        $booking = new Booking();
        $booking->payment_method = 'mastercard';
        
        $this->assertEquals('secondary', $booking->payment_method_color);
    }

    /** @test */
    public function it_returns_correct_payment_method_color_for_paypal()
    {
        $booking = new Booking();
        $booking->payment_method = 'paypal';
        
        $this->assertEquals('success', $booking->payment_method_color);
    }

    /** @test */
    public function it_returns_correct_payment_method_color_for_bank_transfer()
    {
        $booking = new Booking();
        $booking->payment_method = 'bank_transfer';
        
        $this->assertEquals('warning', $booking->payment_method_color);
    }

    /** @test */
    public function it_returns_correct_payment_method_color_for_cash()
    {
        $booking = new Booking();
        $booking->payment_method = 'cash';
        
        $this->assertEquals('danger', $booking->payment_method_color);
    }

    /** @test */
    public function it_returns_default_payment_method_color_for_unknown_method()
    {
        $booking = new Booking();
        $booking->payment_method = 'unknown';
        
        $this->assertEquals('secondary', $booking->payment_method_color);
    }

    /** @test */
    public function it_handles_date_cast_correctly()
    {
        $booking = new Booking();
        $booking->date = '2024-01-15';
        
        $this->assertInstanceOf(\Carbon\Carbon::class, $booking->date);
    }

    /** @test */
    public function it_handles_total_cast_correctly()
    {
        $booking = new Booking();
        $booking->total = '150000.50';
        
        // The decimal cast should handle this correctly
        $this->assertEquals('150000.50', $booking->total);
    }

    /** @test */
    public function it_can_access_user_relationship()
    {
        $booking = new Booking();
        $booking->user_id = 1;
        
        // Mock the user relationship
        $user = Mockery::mock(User::class);
        $user->id = 1;
        $user->name = 'John Doe';
        
        $booking->setRelation('user', $user);
        
        $this->assertInstanceOf(User::class, $booking->user);
        $this->assertEquals('John Doe', $booking->user->name);
    }

    /** @test */
    public function it_can_access_attraction_relationship()
    {
        $booking = new Booking();
        $booking->attraction_id = 1;
        
        // Mock the attraction relationship
        $attraction = Mockery::mock(Attraction::class);
        $attraction->id = 1;
        $attraction->name = 'Beach Resort';
        
        $booking->setRelation('attraction', $attraction);
        
        $this->assertInstanceOf(Attraction::class, $booking->attraction);
        $this->assertEquals('Beach Resort', $booking->attraction->name);
    }
} 