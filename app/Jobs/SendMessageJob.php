<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\JobsMasseage;
use Illuminate\Support\Facades\Log;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $to;
    protected $mailTitle;
    protected $bladeView;
    protected $mailInfo;
    protected $attachments;
    protected $cc;

    /**
     * Create a new job instance.
     */
    public function __construct($to, $mailTitle, $bladeView, $mailInfo, $attachments = [], $cc = null)
    {
        $this->to = $to;
        $this->mailTitle = $mailTitle;
        $this->bladeView = $bladeView;
        $this->mailInfo = $mailInfo;
        $this->attachments = $attachments;
        $this->cc = $cc;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if ($this->cc) {
                Mail::to($this->to)->cc($this->cc)->send(new JobsMasseage($this->mailTitle, $this->bladeView, $this->mailInfo, $this->attachments));
            } else {
                Mail::to($this->to)->send(new JobsMasseage($this->mailTitle, $this->bladeView, $this->mailInfo, $this->attachments));
            }
        } catch (\Exception $e) {
            Log::error('SendMessageJob Error: ' . $e->getMessage());
            throw $e;
        }
    }
}