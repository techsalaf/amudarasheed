# Amuda Rasheed Portfolio

A professional Laravel-based portfolio website showcasing projects, services, and skills.

## Features

- **Dynamic Portfolio Management**: Manage projects, services, and testimonials through an admin panel
- **Blog System**: Share insights and articles with integrated categories and tags
- **Contact Forms**: Receive inquiries and quote requests directly
- **Multi-language Support**: Reach a global audience
- **SEO Optimized**: Built-in SEO tools for better visibility
- **Responsive Design**: Beautiful UI that works on all devices

## Tech Stack

- **Backend**: Laravel 12 (PHP 8.3)
- **Frontend**: Bootstrap 5, jQuery, GSAP animations
- **Database**: MySQL
- **Asset Build**: Laravel Mix (Webpack)

## Quick Start

### Prerequisites
- PHP 8.3+
- Composer
- Node.js 20+
- MySQL

### Installation

```bash
# Clone and navigate
git clone https://github.com/techsalaf/amudarasheed.git
cd amudarasheed

# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Set up database
php artisan migrate
php artisan db:seed

# Build assets
npm run production

# Serve locally
php artisan serve
```

## Development

```bash
# Watch assets during development
npm run dev

# Run tests
php artisan test
```

## Deployment

This project uses GitHub Actions for automated deployment. Push to `main` to trigger deployment to the live server.

## License

© 2026 Amuda Rasheed. All rights reserved.
