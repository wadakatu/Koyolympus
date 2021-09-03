<?php
declare(strict_types=1);

namespace Tests\Unit\Services\ReplaceUuid;

use App\Http\Models\Photo;
use App\Http\Services\ReplaceUuid\BaseService;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Str;
use Tests\TestCase;

class BaseServiceTest extends TestCase
{

    private $photo;
    private $baseService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->photo = Mockery::mock(Photo::class);

        /** @var Mockery\MockInterface|BaseService baseService */
        $this->baseService = Mockery::mock(BaseService::class, [$this->photo])->makePartial();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     * @dataProvider providerIncludeUuidInRecord
     */
    public function includeUuidInRecord($params, $expected)
    {
        $array1 = array_fill(0, $params['uuidPad'], new Photo(['id' => Str::uuid()->toString()]));
        $array2 = array_fill(0, $params['nonUuidPad'], new Photo(['id' => Str::uuid()->toString()]));

        $photoArray = array_merge($array1, $array2);

        $photoList = new Collection($photoArray);

        $this->photo->shouldReceive('all')->once()->andReturn($photoList);

        $this->baseService->includeUuidInRecord();
    }

    public function providerIncludeUuidInRecord(): array
    {
        return [
            'Uuidを含むレコードが一件' => [
                'params' => [
                    'uuidPad' => 1,
                    'nonUuidPad' => 0,
                ],
                'expected' => [

                ],
            ]
        ];
    }


}
