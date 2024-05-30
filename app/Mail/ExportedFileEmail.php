<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExportedFileEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $fileName;
    public $filePath;

    public function __construct($fileName, $filePath)
    {
        $this->fileName = $fileName;
        $this->filePath = $filePath;
    }

    public function build()
    {
        return $this->subject('Employee Export')->view('emails.exported_file')->attach($this->filePath, [
            'as' => $this->fileName,
            'mime' => 'text/csv',
        ]);
    }
}
