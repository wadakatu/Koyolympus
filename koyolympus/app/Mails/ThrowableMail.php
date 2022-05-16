<?php

declare(strict_types=1);

namespace App\Mails;

use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class ThrowableMail extends Mailable
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
    public function build()
    {
        return $this->subject($this->params['subject'])
            ->with('params', $this->params)
            ->view('mail.exception');
    }
}
