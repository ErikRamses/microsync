<?php

namespace App\Mail;

use App\Models\Config;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Notification extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $mail;

    /**
     * Create a new message instance.
     */
    public function __construct($mail)
    {
        $this->mail = $mail;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        foreach ($this->mail->attach as $attachment) {
            $this->attach($attachment);
        }
        $email = $this->from(Config::getSenderEmail(), Config::getSenderName());

        $email = $email->with([
            'subject' => $this->mail->subject,
            'content' => $this->mail->message,
            'name' => $this->mail->investor_name,
            ])
            ->subject($this->mail->subject)
            ->view('emails.notification');

        if ($this->mail->monthly_cut_id && $this->mail->mailing_id && $this->mail->investor_id) {
            $email->withSwiftMessage(function ($message) {
                $message->getHeaders()
                ->addTextHeader('X-Mailgun-Variables', '{"monthly_cut_id": "'.$this->mail->monthly_cut_id.'","mailing_id": "'.$this->mail->mailing_id.'", "investor_id": "'.$this->mail->investor_id.'"}');
            });
        }

        return $email;
    }
}
