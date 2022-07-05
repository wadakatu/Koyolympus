<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Like;
use App\Models\Photo;
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
    public function getAllLikeFirst()
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
    public function getAllLikeCreate()
    {
        $likeRecord = factory(Like::class)->create([
            'photo_id' => 'abc',
            'likes' => 100,
        ]);

        $this->assertSame(0, $this->like->getAllLike('def'));
        $this->assertDatabaseHas(
            'likes',
            ['photo_id' => 'def', 'likes' => 0, 'week_likes' => 0, 'month_likes' => 0, 'all_likes' => 0]
        );
        $this->assertDatabaseHas('likes', $likeRecord->getAttributes());
    }

    /**
     * @test
     */
    public function addLikeSingleRequest()
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
        $this->assertDatabaseHas(
            'likes',
            ['photo_id' => $target->photo_id, 'likes' => $expectedLikes, 'all_likes' => $expectedAllLikes]
        );
        $this->assertDatabaseHas('likes', $notTarget->getAttributes());
    }

    /**
     * @test
     */
    public function addLikeMultipleRequest()
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
        $this->assertDatabaseHas(
            'likes',
            ['photo_id' => $target->photo_id, 'likes' => $expectedLikes, 'all_likes' => $expectedAllLikes]
        );
        $this->assertDatabaseHas('likes', $notTarget->getAttributes());
    }

    /**
     * @test
     */
    public function subLikeSingleRequest()
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
        $this->assertDatabaseHas(
            'likes',
            ['photo_id' => $target->photo_id, 'likes' => $expectedLikes, 'all_likes' => $expectedAllLikes]
        );
        $this->assertDatabaseHas('likes', $notTarget->getAttributes());
    }

    /**
     * @test
     */
    public function subLikeMultipleRequest()
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
        $this->assertDatabaseHas(
            'likes',
            ['photo_id' => $target->photo_id, 'likes' => $expectedLikes, 'all_likes' => $expectedAllLikes]
        );
        $this->assertDatabaseHas('likes', $notTarget->getAttributes());
    }

    /**
     * @test
     */
    public function subLikeIfLikesZero()
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
        $this->assertDatabaseHas(
            'likes',
            ['photo_id' => $target->photo_id, 'likes' => $expectedLikes, 'all_likes' => $expectedAllLikes]
        );
        $this->assertDatabaseHas('likes', $notTarget->getAttributes());
    }

    /**
     * @test
     * @dataProvider providerSaveById
     */
    public function saveByPhotoId($column, $result)
    {
        $target = factory(Like::class)->create([
            'likes' => 10,
            'all_likes' => 100,
        ]);
        factory(Like::class)->create([
            'likes' => 20,
            'all_likes' => 200,
        ]);

        $this->like->saveByPhotoId($target->photo_id, $column);

        $this->assertDatabaseMissing('likes', ['likes' => 10, 'all_likes' => 100]);
        $this->assertDatabaseHas('likes', $result);
        $this->assertDatabaseHas('likes', ['likes' => 20, 'all_likes' => 200]);
    }

    public function providerSaveById(): array
    {
        return [
            'singleColumn' => [
                'column' => [
                    'likes' => 100,
                ],
                'result' => [
                    'likes' => 100,
                    'all_likes' => 100,
                ]
            ],
            'multipleColumn' => [
                'column' => [
                    'likes' => 100,
                    'all_likes' => 1000,
                ],
                'result' => [
                    'likes' => 100,
                    'all_likes' => 1000,
                ]
            ],
        ];
    }

    /**
     * @test
     */
    public function deleteByPhotoId()
    {
        $delete = [
            'photo_id' => 'test_001',
            'likes' => 100,
            'all_likes' => 150
        ];

        $notDelete = [
            'photo_id' => 'test_002',
            'likes' => 200,
            'all_likes' => 250
        ];

        factory(Like::class)->create($delete);
        factory(Like::class)->create($notDelete);

        $this->assertDatabaseHas('likes', $delete);
        $this->assertDatabaseHas('likes', $notDelete);

        $this->like->deleteByPhotoId('test_001');

        $this->assertDatabaseMissing('likes', $delete);
        $this->assertDatabaseHas('likes', $notDelete);
    }

    /**
     * @test
     */
    public function getForDailyAggregation()
    {
        $photoId = 'test_photo_id_1';
        $photoId2 = 'test_photo_id_2';

        factory(Photo::class)->create(['id' => $photoId]);
        factory(Like::class)->create(['photo_id' => $photoId, 'likes' => 0]);
        factory(Like::class)->create(['photo_id' => $photoId, 'likes' => 1]);
        factory(Like::class)->create(['photo_id' => $photoId, 'likes' => 1]);
        factory(Like::class)->create(['photo_id' => 'abc', 'likes' => 1]);

        factory(Photo::class)->create(['id' => $photoId2]);
        factory(Like::class)->create(['photo_id' => $photoId2, 'likes' => 10]);

        $result = $this->like->getForDailyAggregation();

        $this->assertSame(2, $result->count());
        $this->assertSame(
            ['photo_id' => $photoId, 'likes' => 1],
            $result->where('photo_id', $photoId)->first()->toArray()
        );
        $this->assertSame(
            ['photo_id' => $photoId2, 'likes' => 10],
            $result->where('photo_id', $photoId2)->first()->toArray()
        );
    }
}
