<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class FacebookEmailShipped extends Mailable
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
            case 'illegal_user':
                return $this->markdown('email.check_illegal_user_mail')
                    ->with([
                        'content' => $this->content,
                    ])
                    ->subject($this->title ?: 'Facebook提醒');
                break;
            case 'protect_waring':
                return $this->markdown('email.protect_warning_mail')
                    ->with([
                        'content' => $this->content,
                    ])
                    ->subject($this->title ?: 'Facebook投放邮件提醒');
                break;
            default:
                break;
        }
    }
}
