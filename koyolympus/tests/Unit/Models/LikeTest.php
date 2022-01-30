<?php
declare(strict_types=1);

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Http\Models\Like;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    private $like;

    protected function setUp(): void
    {
        parent::setUp();

        $this->like = new Like();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getAllLike_first()
    {
        $allLikes = 500;
        $target = factory(Like::class)->create([
            'all_likes' => $allLikes
        ]);

        $this->assertSame($allLikes, $this->like->getAllLike($target->photo_id));
    }

    /**
     * @test
     */
    public function getAllLike_create()
    {
        $likeRecord = factory(Like::class)->create([
            'photo_id' => 'abc',
            'likes' => 100,
        ]);

        $this->assertSame(0, $this->like->getAllLike('def'));
        $this->assertDatabaseHas('likes',
            ['photo_id' => 'def', 'likes' => 0, 'week_likes' => 0, 'month_likes' => 0, 'all_likes' => 0]);
        $this->assertDatabaseHas('likes', $likeRecord->getAttributes());
    }

    /**
     * @test
     */
    public function addLike_singleRequest()
    {
        $target = factory(Like::class)->create([
            'likes' => 10,
            'all_likes' => 11,
        ]);
        $notTarget = factory(Like::class)->create([
            'likes' => 100,
            'all_likes' => 110,
        ]);

        $this->like->addLike($target->photo_id);

        $expectedLikes = 11;
        $expectedAllLikes = 12;
        $this->assertDatabaseHas('likes',
            ['photo_id' => $target->photo_id, 'likes' => $expectedLikes, 'all_likes' => $expectedAllLikes]);
        $this->assertDatabaseHas('likes', $notTarget->getAttributes());
    }

    /**
     * @test
     */
    public function addLike_multipleRequest()
    {
        $target = factory(Like::class)->create([
            'likes' => 10,
            'all_likes' => 11,
        ]);
        $notTarget = factory(Like::class)->create([
            'likes' => 100,
            'all_likes' => 110,
        ]);

        $this->like->addLike($target->photo_id);
        $this->like->addLike($target->photo_id);
        $this->like->addLike($target->photo_id);

        $expectedLikes = 13;
        $expectedAllLikes = 14;
        $this->assertDatabaseHas('likes',
            ['photo_id' => $target->photo_id, 'likes' => $expectedLikes, 'all_likes' => $expectedAllLikes]);
        $this->assertDatabaseHas('likes', $notTarget->getAttributes());
    }

    /**
     * @test
     */
    public function subLike_singleRequest()
    {
        $target = factory(Like::class)->create([
            'likes' => 11,
            'all_likes' => 12,
        ]);
        $notTarget = factory(Like::class)->create([
            'likes' => 100,
            'all_likes' => 110,
        ]);

        $this->like->subLike($target->photo_id);

        $expectedLikes = 10;
        $expectedAllLikes = 11;
        $this->assertDatabaseHas('likes',
            ['photo_id' => $target->photo_id, 'likes' => $expectedLikes, 'all_likes' => $expectedAllLikes]);
        $this->assertDatabaseHas('likes', $notTarget->getAttributes());
    }

    /**
     * @test
     */
    public function subLike_multipleRequest()
    {
        $target = factory(Like::class)->create([
            'likes' => 13,
            'all_likes' => 14,
        ]);
        $notTarget = factory(Like::class)->create([
            'likes' => 100,
            'all_likes' => 110,
        ]);

        $this->like->subLike($target->photo_id);
        $this->like->subLike($target->photo_id);
        $this->like->subLike($target->photo_id);

        $expectedLikes = 10;
        $expectedAllLikes = 11;
        $this->assertDatabaseHas('likes',
            ['photo_id' => $target->photo_id, 'likes' => $expectedLikes, 'all_likes' => $expectedAllLikes]);
        $this->assertDatabaseHas('likes', $notTarget->getAttributes());
    }

    /**
     * @test
     */
    public function subLike_ifLikesZero()
    {
        $target = factory(Like::class)->create([
            'likes' => 0,
            'all_likes' => 0,
        ]);
        $notTarget = factory(Like::class)->create([
            'likes' => 100,
            'all_likes' => 110,
        ]);

        $this->like->subLike($target->photo_id);

        $expectedLikes = 0;
        $expectedAllLikes = -1;
        $this->assertDatabaseHas('likes',
            ['photo_id' => $target->photo_id, 'likes' => $expectedLikes, 'all_likes' => $expectedAllLikes]);
        $this->assertDatabaseHas('likes', $notTarget->getAttributes());
    }
}
