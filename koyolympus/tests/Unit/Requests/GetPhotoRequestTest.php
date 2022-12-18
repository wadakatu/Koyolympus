<?php

declare(strict_types=1);

namespace Tests\Unit\Requests;

use App\Http\Requests\GetPhotoRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GetPhotoRequestTest extends TestCase
{
    /**
     * @test
     * @dataProvider providerValidation
     */
    public function validation($item, $data, $expect)
    {
        $dataList = [$item => $data];

        $request = new GetPhotoRequest();

        $rules = $request->rules();

        $validator = Validator::make($dataList, $rules);

        $result = $validator->passes();

        $this->assertSame($expect, $result);
    }

    public function providerValidation(): array
    {
        return [
            '正常' => [
                'genre',
                1,
                true,
            ],
            '数値以外' => [
                'genre',
                'aaaa',
                false,
            ],
        ];
    }
}
