<?php

declare(strict_types=1);

namespace Tests\Unit\Requests;

use Validator;
use Tests\TestCase;
use App\Http\Requests\BizInquiriesRequest;

class BizInquiriesRequestTest extends TestCase
{
    private BizInquiriesRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new BizInquiriesRequest();
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
     */
    public function validationSuccess()
    {
        $dataList = [
            'name' => 'test',
            'email' => 'test@test.com',
            'opinion' => 'hello',
        ];

        $rules = $this->request->rules();

        $validator = Validator::make($dataList, $rules);

        $result = $validator->passes();
        $messages = $validator->messages();

        $this->assertTrue($result);
        $this->assertEmpty($messages->messages());
    }

    /**
     * @test
     * @dataProvider providerValidationError
     * @param $data
     * @param $expected
     */
    public function validationError($data, $expected)
    {
        $rules = $this->request->rules();

        $validator = Validator::make($data, $rules);

        $result = $validator->passes();
        $messages = $validator->messages();

        $this->assertFalse($result);
        $this->assertSame($expected['message'], $messages->get($expected['messageKey'])[0]);
    }

    public function providerValidationError(): array
    {
        return [
            '名前が未入力' => [
                'data' => [
                    'name' => '',
                    'email' => 'test@test.com',
                    'opinion' => 'hello',
                ],
                'expect' => [
                    'messageKey' => 'name',
                    'message' => "The name field is required.",
                ],
            ],
            'メール未入力' => [
                'data' => [
                    'name' => 'test',
                    'email' => '',
                    'opinion' => 'hello',
                ],
                'expect' => [
                    'messageKey' => 'email',
                    'message' => "The email field is required.",
                ],
            ],
            '意見未入力' => [
                'data' => [
                    'name' => 'test',
                    'email' => 'test@test.com',
                    'opinion' => '',
                ],
                'expect' => [
                    'messageKey' => 'opinion',
                    'message' => "The opinion field is required.",
                ],
            ],
            '名前が数字' => [
                'data' => [
                    'name' => 1,
                    'email' => 'test@test.com',
                    'opinion' => 'hello',
                ],
                'expect' => [
                    'messageKey' => 'name',
                    'message' => "The name must be a string.",
                ],
            ],
            'メールの形式エラー' => [
                'data' => [
                    'name' => 'test',
                    'email' => 'test',
                    'opinion' => 'hello',
                ],
                'expect' => [
                    'messageKey' => 'email',
                    'message' => "The email must be a valid email address.",
                ],
            ],
            '意見が数字' => [
                'data' => [
                    'name' => 'test',
                    'email' => 'test@test.com',
                    'opinion' => 1,
                ],
                'expect' => [
                    'messageKey' => 'opinion',
                    'message' => "The opinion must be a string.",
                ],
            ],
            '名前が21文字以上' => [
                'data' => [
                    'name' => str_repeat('a', 21),
                    'email' => 'test@test.com',
                    'opinion' => 'hello',
                ],
                'expect' => [
                    'messageKey' => 'name',
                    'message' => "The name may not be greater than 20 characters.",
                ],
            ],
            'メールが256文字以上' => [
                'data' => [
                    'name' => 'hello',
                    'email' => str_repeat('a', 256) . '@test.com',
                    'opinion' => 'hello',
                ],
                'expect' => [
                    'messageKey' => 'email',
                    'message' => "The email may not be greater than 255 characters.",
                ],
            ],
            '意見が1000文字以上' => [
                'data' => [
                    'name' => 'hello',
                    'email' => 'test@test.com',
                    'opinion' => str_repeat('a', 1001),
                ],
                'expect' => [
                    'messageKey' => 'opinion',
                    'message' => "The opinion may not be greater than 1000 characters.",
                ],
            ]
        ];
    }
}
