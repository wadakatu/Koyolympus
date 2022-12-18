<?php

declare(strict_types=1);

namespace Tests\Unit\Mails;

use App\Mails\BizInquiriesMail;
use Tests\TestCase;

class BizInquiriesMailTest extends TestCase
{
    private BizInquiriesMail $mail;

    private array $params;

    protected function setUp(): void
    {
        parent::setUp();

        $this->params = ['subject' => 'test_mail', 'date' => '2021-01-01'];

        $this->mail = new BizInquiriesMail($this->params);
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
