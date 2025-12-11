<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StockAvailableNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Product $product,
        public string $productUrl
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Taylor Swift Merch Back in Stock!')
            ->greeting('Great news!')
            ->line("The item '{$this->product->name}' is now back in stock!")
            ->line('Price: $'.number_format((float) $this->product->price, 2))
            ->action('Buy Now', $this->productUrl)
            ->line('Don\'t wait - Taylor Swift merch sells out fast!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'stock_available', // Discriminant for TypeScript union
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_price' => (float) $this->product->price, // Ensure numeric type
            'product_url' => $this->productUrl,
            'message' => "The item '{$this->product->name}' is now back in stock!",
        ];
    }
}
