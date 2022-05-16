<?php

declare(strict_types=1);

namespace App\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BizInquiriesMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    private $params;

    /**
     * Create a new message instance.
     *
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): BizInquiriesMail
    {
        return $this->subject('お問い合わせがありました。')
            ->with('params', $this->params)
            ->view('mail.contact');
    }
}
