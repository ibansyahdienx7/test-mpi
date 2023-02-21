<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Jenssegers\Agent\Facades\Agent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SentsAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        if ($this->data['title_mail'] == 'regist') {
            return $this->subject($this->data['subject'])->view('mails.authmailer');
        } else if ($this->data['title_mail'] == 'verify') {
            return $this->subject($this->data['subject'])->view('mails.authmailer');
        } else if ($this->data['title_mail'] == 'subscribe') {
            return $this->subject($this->data['subject'])->view('mails.sentmailsubs');
        } else if ($this->data['title_mail'] == 'review') {
            return $this->subject($this->data['subject'])->view('mails.sendmail_review');
        } else if ($this->data['title_mail'] == 'transaction') {
            return $this->subject($this->data['subject'])->view('mails.sendmail_trx');
        }
    }
}
