<?php

declare(strict_types=1);

namespace Tests\Unit\Mails;

use Tests\TestCase;
use App\Mails\BizInquiriesMail;

class BizInquiriesMailTest extends TestCase
{
    private $mail;
    private $params;

    protected function setUp(): void
    {
        parent::setUp();

        $this->params = ['subject' => 'test_mail', 'date' => '2021-01-01'];

        $this->mail = new BizInquiriesMail($this->params);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function build()
    {
        $result = $this->mail->build();

        $this->assertSame('お問い合わせがありました。', $result->subject);
        $this->assertSame('mail.contact', $result->view);
        $this->assertSame($this->params, $result->viewData['params']);
    }
}
