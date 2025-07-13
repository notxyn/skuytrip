<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Attraction;
use Mockery;

class AttractionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_has_correct_fillable_fields()
    {
        $attraction = new Attraction();
        $fillable = $attraction->getFillable();
        
        $expectedFillable = ['slug', 'name', 'img', 'loc', 'desc', 'rate', 'price', 'tags'];
        
        $this->assertEquals($expectedFillable, $fillable);
    }

    /** @test */
    public function it_casts_tags_to_array()
    {
        $attraction = new Attraction();
        $casts = $attraction->getCasts();
        
        $this->assertArrayHasKey('tags', $casts);
        $this->assertEquals('array', $casts['tags']);
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
    public function it_calculates_similarity_with_different_locations()
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
    public function it_calculates_similarity_with_no_common_tags()
    {
        $attraction1 = new Attraction();
        $attraction1->tags = ['tourism', 'adventure', 'family'];
        $attraction1->loc = 'Jakarta';

        $attraction2 = new Attraction();
        $attraction2->tags = ['cultural', 'historical', 'museum'];
        $attraction2->loc = 'Jakarta';

        $similarity = $attraction1->similarityTo($attraction2);
        
        // Tags similarity: intersection = 0, union = 6, score = 0/6 = 0
        // Location similarity: same location = 1
        // Weighted: 0.7 * 0 + 0.3 * 1 = 0.3
        $this->assertEquals(0.3, $similarity);
    }

    /** @test */
    public function it_calculates_similarity_with_empty_tags()
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
    public function it_calculates_similarity_with_null_tags()
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
    public function it_calculates_similarity_with_identical_attractions()
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
    public function it_handles_tags_as_array_correctly()
    {
        $attraction = new Attraction();
        $attraction->tags = ['tourism', 'adventure', 'family'];
        
        $this->assertIsArray($attraction->tags);
        $this->assertEquals(['tourism', 'adventure', 'family'], $attraction->tags);
    }

    /** @test */
    public function it_handles_tags_as_string_correctly()
    {
        $attraction = new Attraction();
        $attraction->tags = 'tourism,adventure,family';
        
        // The cast should convert string to array
        $this->assertIsArray($attraction->tags);
    }

    /** @test */
    public function it_uses_has_factory_trait()
    {
        $attraction = new Attraction();
        
        $this->assertTrue(method_exists($attraction, 'factory'));
    }
} 