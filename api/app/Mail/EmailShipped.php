<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailShipped extends Mailable
{
    use Queueable, SerializesModels;

    protected $content;
    protected $type;
    protected $title;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($content, $type, $title = '')
    {
        $this->content = $content;
        $this->type = $type;
        $this->title = $title;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        switch ($this->type) {
            case 'tc_change':
                return $this->markdown('email.tc_change_mail')
                    ->with([
                        'content' => $this->content,
                    ])
                    ->subject($this->title);
                break;
            default:
                break;
        }
    }
}
