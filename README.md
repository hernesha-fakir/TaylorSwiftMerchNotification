# 🎵 Taylor Swift Merch Notification

A Laravel application that automatically tracks Taylor Swift merchandise availability and price changes, sending notifications when items come back in stock or prices change.

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Filament](https://img.shields.io/badge/Filament-4.x-F59E0B?style=for-the-badge&logo=laravel&logoColor=white)

## 📋 Context & Limitations

**This project was built as a skill demonstration rather than for production use.** It showcases proficiency in modern Laravel development practices, including:

- Laravel 12 with latest framework features
- Filament 4.x for rapid admin panel development
- Modern PHP patterns (Actions, typed properties, enums)
- Code quality tooling (PHPStan, Laravel Pint)
- Queue-based background processing
- Headless browser automation

### Limitations
- **Single-user application**: Designed for personal use, not multi-tenant
- **Development environment**: Uses SQLite and Mailtrap (not production-ready)
- **Limited error handling**: Basic retry logic, not enterprise-grade resilience
- **No rate limiting**: Web scraping without throttling considerations
- **Simplified authentication**: Basic Filament auth, no advanced user management

## ✨ Features

### 📧 Automatic Notifications and Monitoring
- **Stock Availability**: Get notified when out-of-stock items become available
- **Price Changes**: Receive alerts when product prices increase or decrease
- **Database Notifications**: In-app notification history
- **Hourly Checks**: Automatically monitors all tracked products every hour
- **Background Processing**: Queue-based notification system
- **Error Handling**: Robust error handling with retry logic

### 🎛️ Admin Dashboard
- **Filament Admin Panel**: Modern, responsive admin interface
- **Availability History**: Complete table of all availability checks with filters
- **Real-time Data**: Latest stock status and pricing information


## 🚀 Quick Start

### Prerequisites
- PHP 8.2 or higher
- Composer
- SQLite (default) or MySQL/PostgreSQL
- Node.js & NPM (for asset compilation)

### Installation

1. **Clone the repository**
```bash
git clone <repository-url>
cd TaylorSwiftMerchNotification
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Setup environment**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure database**
```bash
# Default uses SQLite (no additional setup needed)
touch database/database.sqlite
php artisan migrate
```

5. **Configure Mailtrap (for email notifications)**
- Sign up at [mailtrap.io](https://mailtrap.io)
- Update `.env` with your Mailtrap credentials:
```env
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
```

6. **Install frontend dependencies**
```bash
npm install
npm run build
```

7. **Create admin user**
```bash
php artisan make:filament-user
```

8. **Start the application**
```bash
# For development
composer run dev

# Or individually:
php artisan serve
php artisan queue:work
npm run dev
```

## 🛠️ Development

### Code Quality Tools

The project includes Laravel Pint and PHPStan for code quality:

```bash
# Format code
composer pint

# Check formatting (dry run)
composer pint-test

# Run static analysis
composer stan

# Run both
composer analyze
```

### Testing

```bash
# Run tests
composer test

# Test specific notification
php artisan test:price-change --old-price=25.00 --new-price=30.00
```

### Architecture

#### Key Components

- **Models**
  - `Product`: Tracked merchandise items
  - `AvailabilityCheck`: Historical availability and pricing data
  - `User`: Admin users

- **Actions** (using [Laravel Actions](https://laravelactions.com/))
  - `CheckAvailabilityForProduct`: Main availability checking logic
  - `ScrapeProductAvailability`: Headless Chrome scraping
  - `ScrapeProductData`: Product data extraction
  - `CreateProduct`: Product creation logic

- **Notifications**
  - `StockAvailableNotification`: Stock availability alerts
  - `PriceChangedNotification`: Price change alerts

- **Widgets**
  - `AvailabilityChecksTableWidget`: Main dashboard table

#### Design Decisions

**Laravel Actions Pattern**
This project uses [Laravel Actions](https://laravelactions.com/) to organize business logic into single-purpose, reusable classes. Since Filament doesn't use traditional controllers, Actions provide a consistent way to execute business logic across different contexts:

```php
// In Filament Pages (app/Filament/Resources/Products/Pages/ListProducts.php:122)
$product = \App\Actions\Product\CreateProduct::run($data['url'], $productData, $variantId);

// In Filament Actions (app/Filament/Resources/Products/Pages/ViewProduct.php:23)
CheckAvailabilityForProduct::run($this->record);

// In Console Commands (app/Console/Commands/CheckAllProductsCommand.php:44)
CheckAvailabilityForProduct::run($product);
```

**Price Handling (Division by 100)**
Shopify stores prices in cents (e.g., $29.99 is stored as 2999). The application converts these to decimal format:
```php
$price = $selectedVariant['price'] / 100; // 2999 becomes 29.99
```
This ensures accurate price comparisons and proper display formatting while maintaining precision in monetary calculations.


## 🔧 Troubleshooting

### Common Issues

**Filament admin not accessible**
```bash
# Create admin user
php artisan make:filament-user
```

**Chrome/scraping issues**
- Ensure Chrome is installed and accessible
- Check `chrome-php/chrome` package is properly installed

**Email notifications not sending**
- Verify Mailtrap credentials in `.env`
- Check queue worker is running
- Check `storage/logs/laravel.log` for errors

### Logs

View application logs:
```bash
tail -f storage/logs/laravel.log
```

View scheduler logs:
```bash
tail -f storage/logs/scheduler.log
```


## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Built with [Laravel 12](https://laravel.com)
- Admin panel powered by [Filament](https://filamentphp.com)
- Web scraping via [Chrome PHP](https://github.com/chrome-php/chrome)
- Styled with [Tailwind CSS](https://tailwindcss.com)

---

**Made with ❤️ for Taylor Swift fans by [Hernesha Fakir]()**

*"It's a love story, baby just say yes" - to automated merch notifications! 🎵*
