<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Employee;

class NewAccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $employee;
    public $temporaryPassword;

    /**
     * Create a new message instance.
     */
    public function __construct(Employee $employee, string $temporaryPassword)
    {
        $this->employee = $employee;
        $this->temporaryPassword = $temporaryPassword;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to Vedica ERP - Your Account is Ready',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.new-account',
            with: [
                'name' => $this->employee->name,
                'username' => $this->employee->username,
                'temporaryPassword' => $this->temporaryPassword,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
