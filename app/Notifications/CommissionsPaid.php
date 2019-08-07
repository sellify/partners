<?php

namespace App\Notifications;

use App\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CommissionsPaid extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The data
     *
     * @var array $data
     */
    public $data;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $template = $this->getTemplate($notifiable);

        return (new MailMessage())
            ->subject($template['subject'])
            ->line($template['body'])
            ->line('Receiver: ' . $this->data['receiver'])
            ->line('Total Amount: ' . $this->data['amount'])
            ->line('Total commissions: ' . $this->data['count'])
            ->line('Status: ' . $this->data['transaction_status'])
            ->line('Transaction ID: ' . $this->data['transaction_id'])
            ->line('Note: ' . $this->data['note'])
            ->action('Visit commissions page', url(config('nova.path') . 'resources/commissions'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     *
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
            'subject' => 'Congratulations! Your referral commission paid successfully.',
            'body'    => 'You have received a total payout of ' . $this->data['amount'] . ' for ' . $this->data['count'] . ' eligible commissions.',
        ];

        return $data;
    }
}
