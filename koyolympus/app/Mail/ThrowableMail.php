<?php
declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class ThrowableMail extends Mailable
{
    use Queueable, SerializesModels;

    private $_params;

    /**
     * Create a new message instance.
     *
     * @param array $params
     */
    public function __construct(array $params)
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
        return $this->subject($this->_params['subject'])
            ->with('params', $this->_params)
            ->view('mail.exception');
    }
}
