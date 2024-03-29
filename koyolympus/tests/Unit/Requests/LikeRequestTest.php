<?php

declare(strict_types=1);

namespace Tests\Unit\Requests;

use App\Http\Requests\LikeRequest;
use Exception;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;
use Validator;

class LikeRequestTest extends TestCase
{
    private LikeRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new LikeRequest();
    }

    /**
     * @test
     */
    public function authorize()
    {
        $this->assertTrue($this->request->authorize());
    }

    /**
     * @test
     *
     * @throws Exception
     */
    public function validationSuccess()
    {
        $data = ['id' => Uuid::uuid4()->toString()];
        $rule = $this->request->rules();

        $validation = Validator::make($data, $rule);

        $result   = $validation->passes();
        $messages = $validation->messages();

        $this->assertTrue($result);
        $this->assertEmpty($messages->messages());
    }

    /**
     * @test
     * @dataProvider providerValidationError
     *
     * @param $data
     * @param $expected
     */
    public function validationError($data, $expected)
    {
        $rules = $this->request->rules();

        $validator = Validator::make($data, $rules);

        $result   = $validator->passes();
        $messages = $validator->messages();

        $this->assertFalse($result);
        $this->assertSame($expected['message'], $messages->get($expected['messageKey'])[0]);
    }

    public function providerValidationError(): array
    {
        return [
            'idがnull' => [
                'data'   => ['id' => null],
                'expect' => [
                    'messageKey' => 'id',
                    'message'    => 'The id field is required.',
                ],
            ],
            'idが数字' => [
                'data'   => ['id' => 1],
                'expect' => [
                    'messageKey' => 'id',
                    'message'    => 'The id must be a string.',
                ],
            ],
            'idが文字列だがUUIDじゃない' => [
                'data'   => ['id' => 'abc'],
                'expect' => [
                    'messageKey' => 'id',
                    'message'    => 'The id must be a valid UUID.',
                ],
            ],
        ];
    }
}
