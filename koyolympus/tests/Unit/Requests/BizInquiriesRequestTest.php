<?php
declare(strict_types=1);

namespace Tests\Unit\Requests;

use App\Http\Requests\BizInquiriesRequest;
use Tests\TestCase;
use Validator;

class BizInquiriesRequestTest extends TestCase
{
    /**
     * @test
     * @dataProvider providerValidation
     * @param $data
     * @param $expect
     */
    public function validation($data, $expect)
    {
        $dataList = $data;

        $request = new BizInquiriesRequest();

        $rules = $request->rules();

        $validator = Validator::make($dataList, $rules);

        $result = $validator->passes();
        $messages = $validator->messages();

        $this->assertSame($expect['result'], $result);

        if ($result === false) {
            $this->assertSame($expect['message'], $messages->get($expect['messageKey'])[0]);
        }
    }

    public function providerValidation()
    {
        return [
            '正常' => [
                'data' => [
                    'name' => 'test',
                    'email' => 'test@test.com',
                    'opinion' => 'hello',
                ],
                'expect' => [
                    'result' => true,
                    'messageKey' => null,
                    'message' => null,
                ],
            ],
            '名前が未入力' => [
                'data' => [
                    'name' => '',
                    'email' => 'test@test.com',
                    'opinion' => 'hello',
                ],
                'expect' => [
                    'result' => false,
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
                    'result' => false,
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
                    'result' => false,
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
                    'result' => false,
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
                    'result' => false,
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
                    'result' => false,
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
                    'result' => false,
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
                    'result' => false,
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
                    'result' => false,
                    'messageKey' => 'opinion',
                    'message' => "The opinion may not be greater than 1000 characters.",
                ],
            ]
        ];
    }
}
