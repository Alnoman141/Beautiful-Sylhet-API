<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordSentNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $data;
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $name = array_key_exists('name', $this->data) ? $this->data['name'] : '';
        $subject = array_key_exists('subject', $this->data) ? "Hi $name, ". $this->data['subject'] : ' '. config('app.name') .'  Password Reset';
        $type = array_key_exists('type', $this->data) ? $this->data['type'] : 'reset';
        $body = array_key_exists('body', $this->data) ? $this->data['body'] : 'You are receiving this email because we received a password reset request for your account.';
        if ($type == 'register') {
            $this->data['body'] = $body;
            return (new MailMessage)
                ->subject($subject)
                ->from(config('fa.global.email'), config('fa.global.name'))
                ->view('notifications.mail-welcome', ['data' => $this->data]);

        } else {
            $this->data['url']= config('fa.server') . 'reset-password?email=' . $this->data['email'] . '&token=' . $this->data['password'];
            return (new MailMessage)
                ->subject($subject)
                ->from(config('fa.global.email'), config('fa.global.name'))
                ->view('notifications.reset-password', ['data' => $this->data]);
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
