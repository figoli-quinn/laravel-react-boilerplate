<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Http\Resources\TootResource;
use App\Models\Toot;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TootReplyNotification extends Notification
{
    use Queueable;

    protected $toot;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Toot $toot)
    {
        $this->toot = $toot;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'toot' => new TootResource($this->toot),
        ];
    }
}
