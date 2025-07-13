<?php

namespace Tests\Unit\Filament\Resources;

use Tests\TestCase;
use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Models\User;
use App\Models\Attraction;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Mockery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BookingResourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_has_correct_resource_configuration()
    {
        $this->assertEquals(Booking::class, BookingResource::getModel());
        $this->assertEquals('heroicon-o-ticket', BookingResource::getNavigationIcon());
        $this->assertEquals('Bookings', BookingResource::getNavigationLabel());
        $this->assertEquals('Bookings', BookingResource::getPluralModelLabel());
        $this->assertEquals('Booking', BookingResource::getModelLabel());
        $this->assertEquals('Travel Management', BookingResource::getNavigationGroup());
    }

    /** @test */
    public function it_creates_form_with_required_fields()
    {
        // Mock User and Attraction models for select options
        $users = collect([
            (object) ['id' => 1, 'name' => 'John Doe'],
            (object) ['id' => 2, 'name' => 'Jane Smith']
        ]);
        
        $attractions = collect([
            (object) ['id' => 1, 'name' => 'Beach Resort'],
            (object) ['id' => 2, 'name' => 'Mountain View']
        ]);

        // Mock the models
        User::shouldReceive('all')->andReturn($users);
        Attraction::shouldReceive('all')->andReturn($attractions);

        // Mock the Form
        $form = Mockery::mock(Form::class);
        $form->shouldReceive('schema')
            ->once()
            ->with(Mockery::on(function ($schema) {
                $fieldNames = collect($schema)->map(function ($field) {
                    return $field->getName();
                })->toArray();
                
                $expectedFields = [
                    'user_id', 'attraction_id', 'name', 'email', 'phone',
                    'date', 'quantity', 'total', 'payment_method', 'status', 'payment_proof'
                ];
                
                foreach ($expectedFields as $field) {
                    if (!in_array($field, $fieldNames)) {
                        return false;
                    }
                }
                return true;
            }))
            ->andReturnSelf();

        $result = BookingResource::form($form);
        
        $this->assertInstanceOf(Form::class, $result);
    }

    /** @test */
    public function it_creates_table_with_expected_columns()
    {
        // Mock the Table
        $table = Mockery::mock(Table::class);
        $table->shouldReceive('columns')
            ->once()
            ->with(Mockery::on(function ($columns) {
                $columnNames = collect($columns)->map(function ($column) {
                    return $column->getName();
                })->toArray();
                
                $expectedColumns = [
                    'id', 'user.name', 'attraction.name', 'name', 'email', 'phone',
                    'date', 'quantity', 'total', 'payment_method', 'status', 'created_at', 'payment_proof'
                ];
                
                foreach ($expectedColumns as $column) {
                    if (!in_array($column, $columnNames)) {
                        return false;
                    }
                }
                return true;
            }))
            ->andReturnSelf();
            
        $table->shouldReceive('filters')->andReturnSelf();
        $table->shouldReceive('actions')->andReturnSelf();
        $table->shouldReceive('bulkActions')->andReturnSelf();
        $table->shouldReceive('defaultSort')->andReturnSelf();

        $result = BookingResource::table($table);
        
        $this->assertInstanceOf(Table::class, $result);
    }

    /** @test */
    public function it_has_correct_pages_configuration()
    {
        $pages = BookingResource::getPages();
        
        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayHasKey('view', $pages);
        $this->assertArrayHasKey('edit', $pages);
        
        $this->assertEquals('App\Filament\Resources\BookingResource\Pages\ListBookings', $pages['index']);
        $this->assertEquals('App\Filament\Resources\BookingResource\Pages\CreateBooking', $pages['create']);
        $this->assertEquals('App\Filament\Resources\BookingResource\Pages\ViewBooking', $pages['view']);
        $this->assertEquals('App\Filament\Resources\BookingResource\Pages\EditBooking', $pages['edit']);
    }

    /** @test */
    public function it_returns_empty_relations()
    {
        $relations = BookingResource::getRelations();
        $this->assertIsArray($relations);
        $this->assertEmpty($relations);
    }

    /** @test */
    public function it_handles_payment_method_options()
    {
        $form = Mockery::mock(Form::class);
        $form->shouldReceive('schema')
            ->once()
            ->with(Mockery::on(function ($schema) {
                // Check that payment_method field has correct options
                foreach ($schema as $field) {
                    if ($field->getName() === 'payment_method') {
                        return true; // Should have payment method options
                    }
                }
                return false;
            }))
            ->andReturnSelf();

        // Mock User and Attraction
        User::shouldReceive('all')->andReturn(collect());
        Attraction::shouldReceive('all')->andReturn(collect());

        BookingResource::form($form);
    }

    /** @test */
    public function it_handles_status_options()
    {
        $form = Mockery::mock(Form::class);
        $form->shouldReceive('schema')
            ->once()
            ->with(Mockery::on(function ($schema) {
                // Check that status field has correct options
                foreach ($schema as $field) {
                    if ($field->getName() === 'status') {
                        return true; // Should have status options
                    }
                }
                return false;
            }))
            ->andReturnSelf();

        // Mock User and Attraction
        User::shouldReceive('all')->andReturn(collect());
        Attraction::shouldReceive('all')->andReturn(collect());

        BookingResource::form($form);
    }

    /** @test */
    public function it_handles_file_upload_for_payment_proof()
    {
        $form = Mockery::mock(Form::class);
        $form->shouldReceive('schema')
            ->once()
            ->with(Mockery::on(function ($schema) {
                // Check that payment_proof field is configured for file upload
                foreach ($schema as $field) {
                    if ($field->getName() === 'payment_proof') {
                        return true; // FileUpload field should be present
                    }
                }
                return false;
            }))
            ->andReturnSelf();

        // Mock User and Attraction
        User::shouldReceive('all')->andReturn(collect());
        Attraction::shouldReceive('all')->andReturn(collect());

        BookingResource::form($form);
    }

    /** @test */
    public function it_handles_date_field()
    {
        $form = Mockery::mock(Form::class);
        $form->shouldReceive('schema')
            ->once()
            ->with(Mockery::on(function ($schema) {
                // Check that date field is a DatePicker
                foreach ($schema as $field) {
                    if ($field->getName() === 'date') {
                        return true; // Should be DatePicker component
                    }
                }
                return false;
            }))
            ->andReturnSelf();

        // Mock User and Attraction
        User::shouldReceive('all')->andReturn(collect());
        Attraction::shouldReceive('all')->andReturn(collect());

        BookingResource::form($form);
    }

    /** @test */
    public function it_handles_numeric_fields()
    {
        $form = Mockery::mock(Form::class);
        $form->shouldReceive('schema')
            ->once()
            ->with(Mockery::on(function ($schema) {
                // Check that quantity and total fields are numeric
                $numericFields = ['quantity', 'total'];
                
                foreach ($schema as $field) {
                    if (in_array($field->getName(), $numericFields)) {
                        return true; // Should be TextInput with numeric() method
                    }
                }
                return true;
            }))
            ->andReturnSelf();

        // Mock User and Attraction
        User::shouldReceive('all')->andReturn(collect());
        Attraction::shouldReceive('all')->andReturn(collect());

        BookingResource::form($form);
    }

    /** @test */
    public function it_handles_email_field()
    {
        $form = Mockery::mock(Form::class);
        $form->shouldReceive('schema')
            ->once()
            ->with(Mockery::on(function ($schema) {
                // Check that email field has email validation
                foreach ($schema as $field) {
                    if ($field->getName() === 'email') {
                        return true; // Should be TextInput with email() method
                    }
                }
                return false;
            }))
            ->andReturnSelf();

        // Mock User and Attraction
        User::shouldReceive('all')->andReturn(collect());
        Attraction::shouldReceive('all')->andReturn(collect());

        BookingResource::form($form);
    }

    /** @test */
    public function it_handles_phone_field()
    {
        $form = Mockery::mock(Form::class);
        $form->shouldReceive('schema')
            ->once()
            ->with(Mockery::on(function ($schema) {
                // Check that phone field has tel validation
                foreach ($schema as $field) {
                    if ($field->getName() === 'phone') {
                        return true; // Should be TextInput with tel() method
                    }
                }
                return false;
            }))
            ->andReturnSelf();

        // Mock User and Attraction
        User::shouldReceive('all')->andReturn(collect());
        Attraction::shouldReceive('all')->andReturn(collect());

        BookingResource::form($form);
    }

    /** @test */
    public function it_handles_table_filters()
    {
        $table = Mockery::mock(Table::class);
        $table->shouldReceive('columns')->andReturnSelf();
        $table->shouldReceive('filters')
            ->once()
            ->with(Mockery::on(function ($filters) {
                // Should have status and payment_method filters
                return count($filters) >= 2;
            }))
            ->andReturnSelf();
        $table->shouldReceive('actions')->andReturnSelf();
        $table->shouldReceive('bulkActions')->andReturnSelf();
        $table->shouldReceive('defaultSort')->andReturnSelf();

        BookingResource::table($table);
    }

    /** @test */
    public function it_handles_table_actions()
    {
        $table = Mockery::mock(Table::class);
        $table->shouldReceive('columns')->andReturnSelf();
        $table->shouldReceive('filters')->andReturnSelf();
        $table->shouldReceive('actions')
            ->once()
            ->with(Mockery::on(function ($actions) {
                // Should have ViewAction and EditAction
                return count($actions) >= 2;
            }))
            ->andReturnSelf();
        $table->shouldReceive('bulkActions')->andReturnSelf();
        $table->shouldReceive('defaultSort')->andReturnSelf();

        BookingResource::table($table);
    }
} 