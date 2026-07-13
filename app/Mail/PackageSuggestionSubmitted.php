<?php

namespace App\Mail;

use App\Models\PackageSuggestion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class PackageSuggestionSubmitted extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public PackageSuggestion $suggestion)
    {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Laravel Loadout package suggestion: '.$this->suggestion->composer_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.package-suggestion-submitted',
            with: [
                'approveUrl' => $this->moderationUrl('package-suggestions.approve'),
                'ignoreUrl' => $this->moderationUrl('package-suggestions.ignore'),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    private function moderationUrl(string $route): string
    {
        return URL::temporarySignedRoute(
            $route,
            now()->addDays((int) config('loadout.moderation_link_expiration_days', 7)),
            ['package_suggestion' => $this->suggestion],
        );
    }
}
