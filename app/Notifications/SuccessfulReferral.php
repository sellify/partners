<?php

namespace App\Notifications;

use App\Email;
use App\Shop;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SuccessfulReferral extends Notification
{
    use Queueable;

    /**
     * The refferred shop
     *
     * @var Shop $shop
     */
    public $shop;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $template = $this->getTemplate($notifiable);

        return (new MailMessage())
            ->subject($template['subject'])
            ->line($template['body']);
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
            //
        ];
    }

    /**
     * Get email template
     * @return mixed
     */
    private function getTemplate($user)
    {
        $data = [
            'subject' => 'Congratulations! Someone you referred just signed up',
            'body'    => $this->shop->shopify_domain . ' has installed "' . $this->shop->app->name . '" and you will earn your ' . $user->commission . '% commission when they\'re charged.',
        ];

        return $data;
    }
}
