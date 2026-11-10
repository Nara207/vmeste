<?php

namespace App\Mail;

use App\Models\Room;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

// Письмо другу: «Анна приглашает вас читать книгу вместе»
class RoomInvitation extends Mailable
{
    public function __construct(public Room $room, public User $inviter)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->inviter->name . ' приглашает вас читать «' . $this->room->book->title . '» вместе',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.room-invitation');
    }
}
