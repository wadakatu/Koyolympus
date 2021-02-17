<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BizInquiriesMail extends Mailable
{
    use Queueable, SerializesModels;

    private $_params = [];

    /**
     * Create a new message instance.
     *
     * @param $params
     */
    public function __construct($params)
    {
        $this->_params = $params;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('お問い合わせがありました。')
            ->with('params', $this->_params)
            ->view('mail.contact');
    }
}
