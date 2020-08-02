# Restaurant-Management

University project - SPA used to manage a restaurant, built using Vue and Laravel.

## Installation

```
composer install
php artisan config:cache # Maybe optional
php artisan migrate # Create database tables
php artisan db:seed # Populate the database
php artisan storage:link # Create a storage link to fix images
php artisan passport:keys # Generate keys, one time only
php artisan passport:install # Generate passport client keys
```
