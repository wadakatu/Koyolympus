<?php

declare(strict_types=1);

namespace Tests\Unit\Mails;

use App\Mails\ThrowableMail;
use Tests\TestCase;

class ThrowableMailTest extends TestCase
{
    private ThrowableMail $mail;

    private array $params;

    protected function setUp(): void
    {
        parent::setUp();

        $this->params = ['subject' => 'test_mail', 'date' => '2021-01-01'];

        $this->mail = new ThrowableMail($this->params);
    }

    /**
     * @test
     */
    public function build()
    {
        $result = $this->mail->build();

        $this->assertSame('test_mail', $result->subject);
        $this->assertSame('mail.exception', $result->view);
        $this->assertSame($this->params, $result->viewData['params']);
    }
}
