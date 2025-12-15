<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JobsMasseage extends Mailable
{
    use Queueable, SerializesModels;

    public $mailTitle;
    public $bladeView;
    public $mailInfo;
    public $attachments;

    /**
     * Create a new message instance.
     */
    public function __construct($mailTitle, $bladeView, $mailInfo, $attachments = [])
    {
        $this->mailTitle = $mailTitle;
        $this->bladeView = $bladeView;
        $this->mailInfo = $mailInfo;
        $this->attachments = $attachments;
    }

    public function build()
    {
        $email = $this->subject($this->mailTitle)
            ->view($this->bladeView)
            ->with(['mailInfo' => $this->mailInfo]);

        if (!empty($this->attachments) && count($this->attachments) > 0) {
            foreach ($this->attachments as $attachment) {
                if (file_exists($attachment['path'])) {
                    $email->attach($attachment['path'], [
                        'as' => $attachment['name'],
                        // 'mime' => 'application/pdf',
                    ]);
                }
            }
        }

        return $email;
    }
}