# Restaurant-Management

This was a school project which consisted of developing an SPA to manage a restaurant using Vue for the front-end and Laravel for the back-end API.

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
