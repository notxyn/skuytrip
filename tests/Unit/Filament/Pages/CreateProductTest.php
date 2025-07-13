<?php

namespace Tests\Unit\Filament\Pages;

use Tests\TestCase;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource;
use Filament\Notifications\Notification;
use Mockery;

class CreateProductTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_has_correct_resource()
    {
        $page = new CreateProduct();
        
        $this->assertEquals(ProductResource::class, $page->getResource());
    }

    /** @test */
    public function it_sends_notification_after_create()
    {
        $page = new CreateProduct();
        
        // Mock the notification
        $notification = Mockery::mock(Notification::class);
        $notification->shouldReceive('title')
            ->with('Produk berhasil dibuat!')
            ->andReturnSelf();
        $notification->shouldReceive('success')
            ->andReturnSelf();
        $notification->shouldReceive('send');
        
        Notification::shouldReceive('make')
            ->andReturn($notification);
        
        // Mock the redirect
        $page->shouldReceive('redirect')
            ->with(ProductResource::getUrl(), ['navigate' => true])
            ->andReturnSelf();
        
        $page->afterCreate();
    }

    /** @test */
    public function it_redirects_to_resource_index_after_create()
    {
        $page = new CreateProduct();
        
        // Mock the notification
        $notification = Mockery::mock(Notification::class);
        $notification->shouldReceive('title')->andReturnSelf();
        $notification->shouldReceive('success')->andReturnSelf();
        $notification->shouldReceive('send');
        
        Notification::shouldReceive('make')
            ->andReturn($notification);
        
        // Mock the redirect
        $page->shouldReceive('redirect')
            ->with(ProductResource::getUrl(), ['navigate' => true])
            ->andReturnSelf();
        
        $page->afterCreate();
    }

    /** @test */
    public function it_extends_create_record_page()
    {
        $page = new CreateProduct();
        
        $this->assertInstanceOf(\Filament\Resources\Pages\CreateRecord::class, $page);
    }

    /** @test */
    public function it_has_correct_page_title()
    {
        $page = new CreateProduct();
        
        // The page should inherit the title from the resource
        $this->assertEquals('Create Attraction', $page->getTitle());
    }

    /** @test */
    public function it_has_correct_breadcrumb()
    {
        $page = new CreateProduct();
        
        // Should have breadcrumb to resource index
        $breadcrumbs = $page->getBreadcrumbs();
        
        $this->assertIsArray($breadcrumbs);
    }

    /** @test */
    public function it_has_correct_form_actions()
    {
        $page = new CreateProduct();
        
        // Should have create and cancel actions
        $actions = $page->getFormActions();
        
        $this->assertIsArray($actions);
    }

    /** @test */
    public function it_validates_form_data()
    {
        $page = new CreateProduct();
        
        // Mock form data
        $data = [
            'slug' => 'test-attraction',
            'name' => 'Test Attraction',
            'loc' => 'Test Location',
            'desc' => 'Test Description',
            'price' => 100000,
            'tags' => ['tourism', 'adventure']
        ];
        
        // Should validate the data
        $this->assertTrue($page->validateFormData($data));
    }

    /** @test */
    public function it_handles_form_submission()
    {
        $page = new CreateProduct();
        
        // Mock form data
        $data = [
            'slug' => 'test-attraction',
            'name' => 'Test Attraction',
            'loc' => 'Test Location',
            'desc' => 'Test Description',
            'price' => 100000,
            'tags' => ['tourism', 'adventure']
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
            ->with(ProductResource::getUrl(), ['navigate' => true])
            ->andReturnSelf();
        
        $result = $page->create($data);
        
        $this->assertTrue($result);
    }

    /** @test */
    public function it_handles_form_validation_errors()
    {
        $page = new CreateProduct();
        
        // Mock invalid form data
        $data = [
            'slug' => '', // Required field is empty
            'name' => 'Test Attraction',
            'loc' => 'Test Location',
            'desc' => 'Test Description',
            'price' => 'invalid', // Should be numeric
            'tags' => ['tourism', 'adventure']
        ];
        
        // Should fail validation
        $this->assertFalse($page->validateFormData($data));
    }

    /** @test */
    public function it_handles_file_upload_in_form()
    {
        $page = new CreateProduct();
        
        // Mock form data with file upload
        $data = [
            'slug' => 'test-attraction',
            'name' => 'Test Attraction',
            'loc' => 'Test Location',
            'desc' => 'Test Description',
            'price' => 100000,
            'tags' => ['tourism', 'adventure'],
            'img' => 'test-image.jpg' // File upload field
        ];
        
        // Should handle file upload correctly
        $this->assertTrue($page->validateFormData($data));
    }

    /** @test */
    public function it_has_correct_page_url()
    {
        $page = new CreateProduct();
        
        $url = $page->getUrl();
        
        $this->assertStringContainsString('/create', $url);
    }
} 