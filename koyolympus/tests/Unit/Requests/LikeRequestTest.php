<?php
declare(strict_types=1);

namespace Tests\Unit\Requests;

use Validator;
use Exception;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;
use App\Http\Requests\LikeRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LikeRequestTest extends TestCase
{

    private $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new LikeRequest();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
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
     * @throws Exception
     */
    public function validation_success()
    {
        $data = ['id' => Uuid::uuid4()->toString()];
        $rule = $this->request->rules();

        $validation = Validator::make($data, $rule);

        $result = $validation->passes();
        $messages = $validation->messages();

        $this->assertTrue($result);
        $this->assertEmpty($messages->messages());
    }

    /**
     * @test
     * @dataProvider providerValidation_error
     * @param $data
     * @param $expected
     */
    public function validation_error($data, $expected)
    {
        $rules = $this->request->rules();

        $validator = Validator::make($data, $rules);

        $result = $validator->passes();
        $messages = $validator->messages();

        $this->assertFalse($result);
        $this->assertSame($expected['message'], $messages->get($expected['messageKey'])[0]);
    }

    public function providerValidation_error(): array
    {
        return [
            'idがnull' => [
                'data' => ['id' => null],
                'expect' => [
                    'messageKey' => 'id',
                    'message' => "The id field is required.",
                ],
            ],
            'idが数字' => [
                'data' => ['id' => 1],
                'expect' => [
                    'messageKey' => 'id',
                    'message' => "The id must be a string.",
                ],
            ],
            'idが文字列だがUUIDじゃない' => [
                'data' => ['id' => 'abc'],
                'expect' => [
                    'messageKey' => 'id',
                    'message' => "The id must be a valid UUID.",
                ],
            ],
        ];
    }
}
