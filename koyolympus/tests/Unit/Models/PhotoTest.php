<?php
declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Http\Models\Photo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoTest extends TestCase
{
    use RefreshDatabase;

    private $photo;
    public $mockConsoleOutput = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->photo = new Photo([]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function setId()
    {
        $this->photo = \Mockery::mock(Photo::class)->makePartial();

        $this->photo->shouldReceive('getRandomId')
            ->once()
            ->andReturn('uuid');

        $this->photo->setId();

        $attribute = $this->photo->getAttributes();
        $this->assertSame('uuid', $attribute['id']);
    }

    /**
     * @test
     */
    public function getRandomId()
    {
        $uuid = $this->photo->getRandomId();
        $this->assertIsString($uuid);
    }

    /**
     * @test
     */
    public function getUrlAttribute()
    {
        $fakeDisk = Storage::fake('s3');
        $fakeFile = UploadedFile::fake()->create('fake.txt', 100, 'text/html');
        $fakeDisk->put('/fake', $fakeFile);
        $this->photo->setAttribute('file_path', 'fake');
        $actual = $this->photo->getUrlAttribute();

        $this->assertSame('/storage/fake', $actual);
    }

    /**
     * @test
     */
    public function getAllPhoto()
    {
        factory(Photo::class)->create([
            'genre' => 1,
        ]);
        factory(Photo::class, 2)->create([
            'genre' => 2,
        ]);
        factory(Photo::class, 3)->create([
            'genre' => 3,
        ]);

        //ジャンルなし
        $records = $this->photo->getAllPhoto(null);
        $this->assertSame(6, count($records->items()));

        //ジャンル１
        $recordsOfGenre1 = $this->photo->getAllPhoto(1);
        $this->assertSame(1, count($recordsOfGenre1->items()));

        //ジャンル２
        $recordsOfGenre2 = $this->photo->getAllPhoto(2);
        $this->assertSame(2, count($recordsOfGenre2->items()));

        //ジャンル３
        $recordsOfGenre3 = $this->photo->getAllPhoto(3);
        $this->assertSame(3, count($recordsOfGenre3->items()));
    }

    /**
     * @test
     */
    public function createPhotoInfo()
    {
        $uniqueFileName = $this->photo->createPhotoInfo('test.jpeg', '/photo', 1);
        $fileName = explode('.', $uniqueFileName)[1];

        $record = Photo::query()->where('genre', 1)->get();

        $this->assertSame(1, count($record));
        $this->assertTrue(isset($uniqueFileName));
        $this->assertSame('test', $fileName);
    }

    /**
     * @test
     */
    public function deletePhotoInfo()
    {
        $id = 'abcdefghiasw';
        $fileName = 'abcdefghiasw.test.jpeg';
        factory(Photo::class)->create([
            'id' => $id,
            'file_name' => 'test.jpeg',
            'genre' => 1,
        ]);
        factory(Photo::class)->create([
            'id' => 'abcdefghijkl',
            'file_name' => 'test2.jpeg',
            'genre' => 2,
        ]);

        $this->photo->deletePhotoInfo($fileName);

        $recordNull = Photo::query()->where('id', $id)->get();

        $record = Photo::query()->where('id', 'abcdefghijkl')->get();

        $this->assertSame(0, count($recordNull));
        $this->assertSame(1, count($record));
    }

    /**
     * @test
     */
    public function getAllPhotos()
    {
        factory(Photo::class)->create([
            'file_name' => 'test3.jpeg',
            'created_at' => '2021-01-01 00:00:00'
        ]);
        factory(Photo::class)->create([
            'file_name' => 'test2.jpeg',
            'created_at' => '2021-01-02 00:00:00'
        ]);
        factory(Photo::class)->create([
            'file_name' => 'test1.jpeg',
            'created_at' => '2021-01-01 00:00:01'
        ]);

        $photoList = $this->photo->getAllPhotos();

        $this->assertSame('test2.jpeg', $photoList[0]->file_name);
        $this->assertSame('test1.jpeg', $photoList[1]->file_name);
        $this->assertSame('test3.jpeg', $photoList[2]->file_name);

    }

    /**
     * @test
     */
    public function getAllPhotoRandomly()
    {
        factory(Photo::class)->create([
            'file_name' => 'test_1'
        ]);
        factory(Photo::class)->create([
            'file_name' => 'test_2'
        ]);
        factory(Photo::class)->create([
            'file_name' => 'test_3'
        ]);
        factory(Photo::class)->create([
            'file_name' => 'test_4'
        ]);
        factory(Photo::class)->create([
            'file_name' => 'test_5'
        ]);

        $result1 = $this->photo->getAllPhotoRandomly();
        $result2 = $this->photo->getAllPhotoRandomly();

        $this->assertNotSame($result1, $result2);
    }
}
