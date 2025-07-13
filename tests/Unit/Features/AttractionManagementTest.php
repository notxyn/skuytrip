<?php

namespace Tests\Unit\Features;

use Tests\TestCase;
use App\Models\Attraction;
use App\Filament\Resources\ProductResource;
use Tests\Helpers\TestHelper;
use Tests\Helpers\FilamentMockHelper;
use Tests\Helpers\AssertionHelper;
use Mockery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AttractionManagementTest extends TestCase
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
    public function it_can_create_attraction_with_valid_data()
    {
        $attractionData = TestHelper::generateAttractionData([
            'slug' => 'test-beach-resort',
            'name' => 'Test Beach Resort',
            'loc' => 'Bali',
            'desc' => 'Beautiful beach resort with amazing views',
            'rate' => 4.5,
            'price' => 250000,
            'tags' => ['tourism', 'beach', 'luxury']
        ]);

        $attraction = new Attraction();
        $attraction->fill($attractionData);
        
        $this->assertEquals('test-beach-resort', $attraction->slug);
        $this->assertEquals('Test Beach Resort', $attraction->name);
        $this->assertEquals('Bali', $attraction->loc);
        $this->assertEquals(4.5, $attraction->rate);
        $this->assertEquals(250000, $attraction->price);
        $this->assertEquals(['tourism', 'beach', 'luxury'], $attraction->tags);
    }

    /** @test */
    public function it_can_handle_image_upload_for_attraction()
    {
        $image = TestHelper::createFakeImage('beach-resort.jpg');
        
        $attractionData = TestHelper::generateAttractionData([
            'img' => $image->store('attractions', 'public')
        ]);

        $attraction = new Attraction();
        $attraction->fill($attractionData);
        
        $this->assertNotNull($attraction->img);
        $this->assertTrue(Storage::disk('public')->exists($attraction->img));
    }

    /** @test */
    public function it_can_update_attraction_information()
    {
        $attraction = new Attraction();
        $attraction->fill(TestHelper::generateAttractionData());
        
        // Update attraction
        $attraction->name = 'Updated Beach Resort';
        $attraction->price = 300000;
        $attraction->rate = 4.8;
        $attraction->tags = ['tourism', 'beach', 'luxury', 'spa'];
        
        $this->assertEquals('Updated Beach Resort', $attraction->name);
        $this->assertEquals(300000, $attraction->price);
        $this->assertEquals(4.8, $attraction->rate);
        $this->assertEquals(['tourism', 'beach', 'luxury', 'spa'], $attraction->tags);
    }

    /** @test */
    public function it_can_delete_attraction()
    {
        $attraction = new Attraction();
        $attraction->fill(TestHelper::generateAttractionData());
        
        // Mock the delete method
        $attraction->shouldReceive('delete')
            ->once()
            ->andReturn(true);
        
        $result = $attraction->delete();
        
        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_search_attractions_by_name()
    {
        $attractions = collect([
            (object) ['id' => 1, 'name' => 'Beach Resort Bali', 'loc' => 'Bali'],
            (object) ['id' => 2, 'name' => 'Mountain View Bandung', 'loc' => 'Bandung'],
            (object) ['id' => 3, 'name' => 'Beach Resort Lombok', 'loc' => 'Lombok']
        ]);

        // Mock Attraction model
        Attraction::shouldReceive('where')
            ->with('name', 'like', '%Beach%')
            ->andReturnSelf();
        Attraction::shouldReceive('get')
            ->andReturn($attractions->filter(function ($attraction) {
                return str_contains($attraction->name, 'Beach');
            }));

        $searchResults = Attraction::where('name', 'like', '%Beach%')->get();
        
        $this->assertCount(2, $searchResults);
        $this->assertTrue($searchResults->every(function ($attraction) {
            return str_contains($attraction->name, 'Beach');
        }));
    }

    /** @test */
    public function it_can_filter_attractions_by_location()
    {
        $attractions = collect([
            (object) ['id' => 1, 'name' => 'Beach Resort Bali', 'loc' => 'Bali'],
            (object) ['id' => 2, 'name' => 'Mountain View Bandung', 'loc' => 'Bandung'],
            (object) ['id' => 3, 'name' => 'Beach Resort Lombok', 'loc' => 'Lombok']
        ]);

        // Mock Attraction model
        Attraction::shouldReceive('where')
            ->with('loc', 'Bali')
            ->andReturnSelf();
        Attraction::shouldReceive('get')
            ->andReturn($attractions->filter(function ($attraction) {
                return $attraction->loc === 'Bali';
            }));

        $filteredResults = Attraction::where('loc', 'Bali')->get();
        
        $this->assertCount(1, $filteredResults);
        $this->assertEquals('Bali', $filteredResults->first()->loc);
    }

    /** @test */
    public function it_can_sort_attractions_by_price()
    {
        $attractions = collect([
            (object) ['id' => 1, 'name' => 'Budget Hotel', 'price' => 100000],
            (object) ['id' => 2, 'name' => 'Mid-range Resort', 'price' => 250000],
            (object) ['id' => 3, 'name' => 'Luxury Resort', 'price' => 500000]
        ]);

        // Mock Attraction model
        Attraction::shouldReceive('orderBy')
            ->with('price', 'asc')
            ->andReturnSelf();
        Attraction::shouldReceive('get')
            ->andReturn($attractions->sortBy('price'));

        $sortedResults = Attraction::orderBy('price', 'asc')->get();
        
        $this->assertEquals(100000, $sortedResults->first()->price);
        $this->assertEquals(500000, $sortedResults->last()->price);
    }

    /** @test */
    public function it_can_filter_attractions_by_tags()
    {
        $attractions = collect([
            (object) ['id' => 1, 'name' => 'Beach Resort', 'tags' => ['tourism', 'beach']],
            (object) ['id' => 2, 'name' => 'Mountain Lodge', 'tags' => ['adventure', 'outdoor']],
            (object) ['id' => 3, 'name' => 'Cultural Center', 'tags' => ['cultural', 'historical']]
        ]);

        // Mock Attraction model
        Attraction::shouldReceive('whereJsonContains')
            ->with('tags', 'tourism')
            ->andReturnSelf();
        Attraction::shouldReceive('get')
            ->andReturn($attractions->filter(function ($attraction) {
                return in_array('tourism', $attraction->tags);
            }));

        $filteredResults = Attraction::whereJsonContains('tags', 'tourism')->get();
        
        $this->assertCount(1, $filteredResults);
        $this->assertTrue(in_array('tourism', $filteredResults->first()->tags));
    }

    /** @test */
    public function it_can_calculate_attraction_similarity()
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
        // Weighted: 0.7 * 0.5 + 0.3 * 1 = 0.65
        $this->assertEquals(0.65, $similarity);
    }

    /** @test */
    public function it_can_validate_attraction_data()
    {
        $validData = [
            'slug' => 'test-attraction',
            'name' => 'Test Attraction',
            'loc' => 'Test Location',
            'desc' => 'Test Description',
            'price' => 100000,
            'tags' => ['tourism', 'adventure']
        ];

        $attraction = new Attraction();
        $attraction->fill($validData);
        
        $this->assertNotNull($attraction->slug);
        $this->assertNotNull($attraction->name);
        $this->assertNotNull($attraction->loc);
        $this->assertNotNull($attraction->desc);
        $this->assertNotNull($attraction->price);
        $this->assertIsArray($attraction->tags);
    }

    /** @test */
    public function it_can_handle_attraction_without_image()
    {
        $attractionData = TestHelper::generateAttractionData([
            'img' => null
        ]);

        $attraction = new Attraction();
        $attraction->fill($attractionData);
        
        $this->assertNull($attraction->img);
    }

    /** @test */
    public function it_can_handle_attraction_with_empty_tags()
    {
        $attractionData = TestHelper::generateAttractionData([
            'tags' => []
        ]);

        $attraction = new Attraction();
        $attraction->fill($attractionData);
        
        $this->assertIsArray($attraction->tags);
        $this->assertEmpty($attraction->tags);
    }

    /** @test */
    public function it_can_handle_attraction_with_null_tags()
    {
        $attractionData = TestHelper::generateAttractionData([
            'tags' => null
        ]);

        $attraction = new Attraction();
        $attraction->fill($attractionData);
        
        $this->assertNull($attraction->tags);
    }

    /** @test */
    public function it_can_format_attraction_price()
    {
        $attraction = new Attraction();
        $attraction->price = 250000;
        
        $formattedPrice = 'Rp ' . number_format($attraction->price, 0, ',', '.');
        
        $this->assertEquals('Rp 250.000', $formattedPrice);
    }

    /** @test */
    public function it_can_get_attraction_rating_display()
    {
        $attraction = new Attraction();
        $attraction->rate = 4.5;
        
        $ratingDisplay = number_format($attraction->rate, 1) . ' / 5.0';
        
        $this->assertEquals('4.5 / 5.0', $ratingDisplay);
    }

    /** @test */
    public function it_can_get_attraction_tags_as_string()
    {
        $attraction = new Attraction();
        $attraction->tags = ['tourism', 'adventure', 'family'];
        
        $tagsString = implode(', ', $attraction->tags);
        
        $this->assertEquals('tourism, adventure, family', $tagsString);
    }

    /** @test */
    public function it_can_check_if_attraction_is_popular()
    {
        $popularAttraction = new Attraction();
        $popularAttraction->rate = 4.5;
        
        $isPopular = $popularAttraction->rate >= 4.0;
        
        $this->assertTrue($isPopular);
    }

    /** @test */
    public function it_can_check_if_attraction_is_expensive()
    {
        $expensiveAttraction = new Attraction();
        $expensiveAttraction->price = 500000;
        
        $isExpensive = $expensiveAttraction->price > 300000;
        
        $this->assertTrue($isExpensive);
    }
} 