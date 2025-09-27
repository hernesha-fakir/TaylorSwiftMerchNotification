<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PriceChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Product $product,
        public string $productUrl,
        public float $oldPrice,
        public float $newPrice
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
        $priceChange = $this->newPrice > $this->oldPrice ? 'increased' : 'decreased';
        $changeColor = $this->newPrice > $this->oldPrice ? '📈' : '📉';

        return (new MailMessage)
            ->subject("Taylor Swift Merch Price Change - {$this->product->name}")
            ->greeting('Price Alert!')
            ->line("The price for '{$this->product->name}' has {$priceChange}! {$changeColor}")
            ->line("Old Price: $" . number_format($this->oldPrice, 2))
            ->line("New Price: $" . number_format($this->newPrice, 2))
            ->line("Price Difference: $" . number_format(abs($this->newPrice - $this->oldPrice), 2))
            ->action('View Product', $this->productUrl)
            ->line('Stay on top of Taylor Swift merch pricing!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_url' => $this->productUrl,
            'old_price' => $this->oldPrice,
            'new_price' => $this->newPrice,
            'price_difference' => abs($this->newPrice - $this->oldPrice),
            'price_change_type' => $this->newPrice > $this->oldPrice ? 'increase' : 'decrease',
            'message' => "Price " . ($this->newPrice > $this->oldPrice ? 'increased' : 'decreased') . " from $" . number_format($this->oldPrice, 2) . " to $" . number_format($this->newPrice, 2)
        ];
    }
}
