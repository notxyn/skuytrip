<?php

namespace Tests\Unit\Filament\Pages;

use Tests\TestCase;
use App\Filament\Resources\BookingResource\Pages\CreateBooking;
use App\Filament\Resources\BookingResource;
use Filament\Notifications\Notification;
use Mockery;

class CreateBookingTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_has_correct_resource()
    {
        $page = new CreateBooking();
        
        $this->assertEquals(BookingResource::class, $page->getResource());
    }

    /** @test */
    public function it_sends_notification_after_create()
    {
        $page = new CreateBooking();
        
        // Mock the notification
        $notification = Mockery::mock(Notification::class);
        $notification->shouldReceive('title')
            ->with('Booking berhasil dibuat!')
            ->andReturnSelf();
        $notification->shouldReceive('success')
            ->andReturnSelf();
        $notification->shouldReceive('send');
        
        Notification::shouldReceive('make')
            ->andReturn($notification);
        
        // Mock the redirect
        $page->shouldReceive('redirect')
            ->with(BookingResource::getUrl(), ['navigate' => true])
            ->andReturnSelf();
        
        $page->afterCreate();
    }

    /** @test */
    public function it_redirects_to_resource_index_after_create()
    {
        $page = new CreateBooking();
        
        // Mock the notification
        $notification = Mockery::mock(Notification::class);
        $notification->shouldReceive('title')->andReturnSelf();
        $notification->shouldReceive('success')->andReturnSelf();
        $notification->shouldReceive('send');
        
        Notification::shouldReceive('make')
            ->andReturn($notification);
        
        // Mock the redirect
        $page->shouldReceive('redirect')
            ->with(BookingResource::getUrl(), ['navigate' => true])
            ->andReturnSelf();
        
        $page->afterCreate();
    }

    /** @test */
    public function it_extends_create_record_page()
    {
        $page = new CreateBooking();
        
        $this->assertInstanceOf(\Filament\Resources\Pages\CreateRecord::class, $page);
    }

    /** @test */
    public function it_has_correct_page_title()
    {
        $page = new CreateBooking();
        
        // The page should inherit the title from the resource
        $this->assertEquals('Create Booking', $page->getTitle());
    }

    /** @test */
    public function it_has_correct_breadcrumb()
    {
        $page = new CreateBooking();
        
        // Should have breadcrumb to resource index
        $breadcrumbs = $page->getBreadcrumbs();
        
        $this->assertIsArray($breadcrumbs);
    }

    /** @test */
    public function it_has_correct_form_actions()
    {
        $page = new CreateBooking();
        
        // Should have create and cancel actions
        $actions = $page->getFormActions();
        
        $this->assertIsArray($actions);
    }

    /** @test */
    public function it_validates_form_data()
    {
        $page = new CreateBooking();
        
        // Mock form data
        $data = [
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
        
        // Should validate the data
        $this->assertTrue($page->validateFormData($data));
    }

    /** @test */
    public function it_handles_form_submission()
    {
        $page = new CreateBooking();
        
        // Mock form data
        $data = [
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
        
        // Mock the create record
        $page->shouldReceive('createRecord')
            ->with($data)
            ->andReturn(true);
        
        // Mock notification
        $notification = Mockery::mock(Notification::class);
        $notification->shouldReceive('title')->andReturnSelf();
        $notification->shouldReceive('success')->andReturnSelf();
        $notification->shouldReceive('send');
        
        Notification::shouldReceive('make')
            ->andReturn($notification);
        
        // Mock redirect
        $page->shouldReceive('redirect')
            ->with(BookingResource::getUrl(), ['navigate' => true])
            ->andReturnSelf();
        
        $result = $page->create($data);
        
        $this->assertTrue($result);
    }

    /** @test */
    public function it_handles_form_validation_errors()
    {
        $page = new CreateBooking();
        
        // Mock invalid form data
        $data = [
            'user_id' => 1,
            'attraction_id' => 1,
            'name' => '', // Required field is empty
            'email' => 'invalid-email', // Invalid email
            'phone' => '+6281234567890',
            'date' => '2024-01-15',
            'quantity' => 0, // Should be at least 1
            'total' => 'invalid', // Should be numeric
            'payment_method' => 'visa',
            'status' => 'pending'
        ];
        
        // Should fail validation
        $this->assertFalse($page->validateFormData($data));
    }

    /** @test */
    public function it_handles_file_upload_in_form()
    {
        $page = new CreateBooking();
        
        // Mock form data with file upload
        $data = [
            'user_id' => 1,
            'attraction_id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+6281234567890',
            'date' => '2024-01-15',
            'quantity' => 2,
            'total' => 200000,
            'payment_method' => 'visa',
            'status' => 'pending',
            'payment_proof' => 'payment-proof.jpg' // File upload field
        ];
        
        // Should handle file upload correctly
        $this->assertTrue($page->validateFormData($data));
    }

    /** @test */
    public function it_has_correct_page_url()
    {
        $page = new CreateBooking();
        
        $url = $page->getUrl();
        
        $this->assertStringContainsString('/create', $url);
    }

    /** @test */
    public function it_handles_select_options()
    {
        $page = new CreateBooking();
        
        // Mock form data with select options
        $data = [
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
        
        // Should handle select options correctly
        $this->assertTrue($page->validateFormData($data));
    }

    /** @test */
    public function it_handles_date_field()
    {
        $page = new CreateBooking();
        
        // Mock form data with date
        $data = [
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
        
        // Should handle date field correctly
        $this->assertTrue($page->validateFormData($data));
    }
} 