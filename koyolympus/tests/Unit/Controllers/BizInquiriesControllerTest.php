<?php
declare(strict_types=1);

namespace Tests\Unit\Controllers;

use App\Http\Controllers\v1\BizInquiriesController;
use App\Http\Requests\BizInquiriesRequest;
use App\Mail\BizInquiriesMail;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BizInquiriesControllerTest extends TestCase
{

    private $bizInquiriesController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bizInquiriesController = new BizInquiriesController();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function sendBizInquiries()
    {
        $request = new BizInquiriesRequest;
        $request->merge([
            'name' => 'test',
            'email' => 'test@gmail.com',
            'opinion' => 'test,test,test',
        ]);

        Mail::fake();

        $this->bizInquiriesController->sendBizInquiries($request);

        Mail::assertSent(BizInquiriesMail::class, function ($mail) {
            return $mail->hasTo('wadakatukoyo330@gmail.com');
        });

        Mail::assertSent(BizInquiriesMail::class, 1);
    }
}
