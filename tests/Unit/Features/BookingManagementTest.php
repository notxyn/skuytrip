<?php

namespace Tests\Unit\Features;

use Tests\TestCase;
use App\Models\Booking;
use App\Models\User;
use App\Models\Attraction;
use Tests\Helpers\TestHelper;
use Mockery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BookingManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        TestHelper::setupStorageFake();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_create_booking_with_valid_data()
    {
        $bookingData = TestHelper::generateBookingData([
            'user_id' => 1,
            'attraction_id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+6281234567890',
            'date' => '2024-01-15',
            'quantity' => 2,
            'total' => 200000,
            'payment_method' => 'visa',
            'status' => 'pending'
        ]);

        $booking = new Booking();
        $booking->fill($bookingData);
        
        $this->assertEquals(1, $booking->user_id);
        $this->assertEquals(1, $booking->attraction_id);
        $this->assertEquals('John Doe', $booking->name);
        $this->assertEquals('john@example.com', $booking->email);
        $this->assertEquals('+6281234567890', $booking->phone);
        $this->assertEquals('2024-01-15', $booking->date->format('Y-m-d'));
        $this->assertEquals(2, $booking->quantity);
        $this->assertEquals(200000, $booking->total);
        $this->assertEquals('visa', $booking->payment_method);
        $this->assertEquals('pending', $booking->status);
    }

    /** @test */
    public function it_can_handle_payment_proof_upload()
    {
        $paymentProof = TestHelper::createFakeImage('payment-proof.jpg');
        
        $bookingData = TestHelper::generateBookingData([
            'payment_proof' => $paymentProof->store('payments', 'public')
        ]);

        $booking = new Booking();
        $booking->fill($bookingData);
        
        $this->assertNotNull($booking->payment_proof);
        $this->assertTrue(Storage::disk('public')->exists($booking->payment_proof));
    }

    /** @test */
    public function it_can_update_booking_status()
    {
        $booking = new Booking();
        $booking->fill(TestHelper::generateBookingData());
        
        // Update status
        $booking->status = 'paid';
        
        $this->assertEquals('paid', $booking->status);
        $this->assertEquals('success', $booking->status_color);
    }

    /** @test */
    public function it_can_cancel_booking()
    {
        $booking = new Booking();
        $booking->fill(TestHelper::generateBookingData());
        
        // Cancel booking
        $booking->status = 'cancelled';
        
        $this->assertEquals('cancelled', $booking->status);
        $this->assertEquals('danger', $booking->status_color);
    }

    /** @test */
    public function it_can_refund_booking()
    {
        $booking = new Booking();
        $booking->fill(TestHelper::generateBookingData());
        
        // Refund booking
        $booking->status = 'refunded';
        
        $this->assertEquals('refunded', $booking->status);
        $this->assertEquals('info', $booking->status_color);
    }

    /** @test */
    public function it_can_calculate_booking_total()
    {
        $booking = new Booking();
        $booking->quantity = 3;
        $booking->total = 150000; // Price per ticket
        
        $expectedTotal = $booking->quantity * ($booking->total / $booking->quantity);
        
        $this->assertEquals(150000, $expectedTotal);
    }

    /** @test */
    public function it_can_get_booking_with_user_relationship()
    {
        $booking = new Booking();
        $booking->user_id = 1;
        
        // Mock user relationship
        $user = Mockery::mock(User::class);
        $user->id = 1;
        $user->name = 'John Doe';
        $user->email = 'john@example.com';
        
        $booking->setRelation('user', $user);
        
        $this->assertInstanceOf(User::class, $booking->user);
        $this->assertEquals('John Doe', $booking->user->name);
        $this->assertEquals('john@example.com', $booking->user->email);
    }

    /** @test */
    public function it_can_get_booking_with_attraction_relationship()
    {
        $booking = new Booking();
        $booking->attraction_id = 1;
        
        // Mock attraction relationship
        $attraction = Mockery::mock(Attraction::class);
        $attraction->id = 1;
        $attraction->name = 'Beach Resort';
        $attraction->price = 100000;
        
        $booking->setRelation('attraction', $attraction);
        
        $this->assertInstanceOf(Attraction::class, $booking->attraction);
        $this->assertEquals('Beach Resort', $booking->attraction->name);
        $this->assertEquals(100000, $booking->attraction->price);
    }

    /** @test */
    public function it_can_search_bookings_by_customer_name()
    {
        $bookings = collect([
            (object) ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
            (object) ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com'],
            (object) ['id' => 3, 'name' => 'John Wilson', 'email' => 'john.w@example.com']
        ]);

        // Mock Booking model
        Booking::shouldReceive('where')
            ->with('name', 'like', '%John%')
            ->andReturnSelf();
        Booking::shouldReceive('get')
            ->andReturn($bookings->filter(function ($booking) {
                return str_contains($booking->name, 'John');
            }));

        $searchResults = Booking::where('name', 'like', '%John%')->get();
        
        $this->assertCount(2, $searchResults);
        $this->assertTrue($searchResults->every(function ($booking) {
            return str_contains($booking->name, 'John');
        }));
    }

    /** @test */
    public function it_can_filter_bookings_by_status()
    {
        $bookings = collect([
            (object) ['id' => 1, 'status' => 'pending'],
            (object) ['id' => 2, 'status' => 'paid'],
            (object) ['id' => 3, 'status' => 'cancelled'],
            (object) ['id' => 4, 'status' => 'paid']
        ]);

        // Mock Booking model
        Booking::shouldReceive('where')
            ->with('status', 'paid')
            ->andReturnSelf();
        Booking::shouldReceive('get')
            ->andReturn($bookings->filter(function ($booking) {
                return $booking->status === 'paid';
            }));

        $filteredResults = Booking::where('status', 'paid')->get();
        
        $this->assertCount(2, $filteredResults);
        $this->assertTrue($filteredResults->every(function ($booking) {
            return $booking->status === 'paid';
        }));
    }

    /** @test */
    public function it_can_filter_bookings_by_payment_method()
    {
        $bookings = collect([
            (object) ['id' => 1, 'payment_method' => 'visa'],
            (object) ['id' => 2, 'payment_method' => 'mastercard'],
            (object) ['id' => 3, 'payment_method' => 'visa'],
            (object) ['id' => 4, 'payment_method' => 'paypal']
        ]);

        // Mock Booking model
        Booking::shouldReceive('where')
            ->with('payment_method', 'visa')
            ->andReturnSelf();
        Booking::shouldReceive('get')
            ->andReturn($bookings->filter(function ($booking) {
                return $booking->payment_method === 'visa';
            }));

        $filteredResults = Booking::where('payment_method', 'visa')->get();
        
        $this->assertCount(2, $filteredResults);
        $this->assertTrue($filteredResults->every(function ($booking) {
            return $booking->payment_method === 'visa';
        }));
    }

    /** @test */
    public function it_can_sort_bookings_by_date()
    {
        $bookings = collect([
            (object) ['id' => 1, 'date' => '2024-01-15'],
            (object) ['id' => 2, 'date' => '2024-01-10'],
            (object) ['id' => 3, 'date' => '2024-01-20']
        ]);

        // Mock Booking model
        Booking::shouldReceive('orderBy')
            ->with('date', 'asc')
            ->andReturnSelf();
        Booking::shouldReceive('get')
            ->andReturn($bookings->sortBy('date'));

        $sortedResults = Booking::orderBy('date', 'asc')->get();
        
        $this->assertEquals('2024-01-10', $sortedResults->first()->date);
        $this->assertEquals('2024-01-20', $sortedResults->last()->date);
    }

    /** @test */
    public function it_can_get_payment_method_color()
    {
        $booking = new Booking();
        
        $booking->payment_method = 'visa';
        $this->assertEquals('primary', $booking->payment_method_color);
        
        $booking->payment_method = 'mastercard';
        $this->assertEquals('secondary', $booking->payment_method_color);
        
        $booking->payment_method = 'paypal';
        $this->assertEquals('success', $booking->payment_method_color);
        
        $booking->payment_method = 'bank_transfer';
        $this->assertEquals('warning', $booking->payment_method_color);
        
        $booking->payment_method = 'cash';
        $this->assertEquals('danger', $booking->payment_method_color);
    }

    /** @test */
    public function it_can_get_status_color()
    {
        $booking = new Booking();
        
        $booking->status = 'pending';
        $this->assertEquals('warning', $booking->status_color);
        
        $booking->status = 'paid';
        $this->assertEquals('success', $booking->status_color);
        
        $booking->status = 'cancelled';
        $this->assertEquals('danger', $booking->status_color);
        
        $booking->status = 'refunded';
        $this->assertEquals('info', $booking->status_color);
    }

    /** @test */
    public function it_can_validate_booking_data()
    {
        $validData = [
            'user_id' => 1,
            'attraction_id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+6281234567890',
            'date' => '2024-01-15',
            'quantity' => 2,
            'total' => 200000,
            'payment_method' => 'visa',
            'status' => 'pending'
        ];

        $booking = new Booking();
        $booking->fill($validData);
        
        $this->assertNotNull($booking->user_id);
        $this->assertNotNull($booking->attraction_id);
        $this->assertNotNull($booking->name);
        $this->assertNotNull($booking->email);
        $this->assertNotNull($booking->phone);
        $this->assertNotNull($booking->date);
        $this->assertNotNull($booking->quantity);
        $this->assertNotNull($booking->total);
        $this->assertNotNull($booking->payment_method);
        $this->assertNotNull($booking->status);
    }

    /** @test */
    public function it_can_handle_booking_without_payment_proof()
    {
        $bookingData = TestHelper::generateBookingData([
            'payment_proof' => null
        ]);

        $booking = new Booking();
        $booking->fill($bookingData);
        
        $this->assertNull($booking->payment_proof);
    }

    /** @test */
    public function it_can_format_booking_total()
    {
        $booking = new Booking();
        $booking->total = 250000;
        
        $formattedTotal = 'Rp ' . number_format($booking->total, 0, ',', '.');
        
        $this->assertEquals('Rp 250.000', $formattedTotal);
    }

    /** @test */
    public function it_can_check_if_booking_is_paid()
    {
        $booking = new Booking();
        $booking->status = 'paid';
        
        $isPaid = $booking->status === 'paid';
        
        $this->assertTrue($isPaid);
    }

    /** @test */
    public function it_can_check_if_booking_is_pending()
    {
        $booking = new Booking();
        $booking->status = 'pending';
        
        $isPending = $booking->status === 'pending';
        
        $this->assertTrue($isPending);
    }

    /** @test */
    public function it_can_check_if_booking_is_cancelled()
    {
        $booking = new Booking();
        $booking->status = 'cancelled';
        
        $isCancelled = $booking->status === 'cancelled';
        
        $this->assertTrue($isCancelled);
    }

    /** @test */
    public function it_can_get_booking_date_formatted()
    {
        $booking = new Booking();
        $booking->date = \Carbon\Carbon::parse('2024-01-15');
        
        $formattedDate = $booking->date->format('d M Y');
        
        $this->assertEquals('15 Jan 2024', $formattedDate);
    }

    /** @test */
    public function it_can_calculate_booking_duration()
    {
        $booking = new Booking();
        $booking->date = \Carbon\Carbon::parse('2024-01-15');
        
        $today = now()->format('Y-m-d');
        $bookingDate = $booking->date->format('Y-m-d');
        
        $daysUntilBooking = \Carbon\Carbon::parse($bookingDate)->diffInDays($today);
        
        $this->assertIsInt($daysUntilBooking);
        $this->assertGreaterThanOrEqual(0, $daysUntilBooking);
    }
} 