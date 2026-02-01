<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProjectReportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $projectName,
        public string $title,
        public ?string $message,
        public string $pdfBytes
    ) {}

    public function build()
    {
        $mail = $this->subject($this->title)
            ->view('emails.project-report')
            ->with([
                'projectName' => $this->projectName,
                'title' => $this->title,
                'customMessage' => $this->message,
            ])
            ->attachData($this->pdfBytes, 'BuildFlow-Report.pdf', [
                'mime' => 'application/pdf',
            ]);

        return $mail;
    }
}
