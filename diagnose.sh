#!/bin/bash

echo "=== Biometric Authentication Diagnostics ==="
echo ""

echo "1. Checking current directory..."
pwd
echo ""

echo "2. Checking Laravel version..."
php artisan --version
echo ""

echo "3. Listing customer routes..."
php artisan route:list --name=customer
echo ""

echo "4. Clearing all caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo "✓ Caches cleared"
echo ""

echo "5. Checking file permissions..."
ls -la public/js/biometric-login.js
echo ""

echo "6. Checking if CustomerAuthController exists..."
ls -la app/Http/Controllers/CustomerAuthController.php
echo ""

echo "7. Testing route registration..."
php artisan route:list | grep customer
echo ""

echo "8. Restarting PHP-FPM..."
sudo systemctl restart php8.3-fpm
echo "✓ PHP-FPM restarted"
echo ""

echo "=== Diagnostics Complete ==="
echo ""
echo "Now try accessing:"
echo "https://authenticator.task19.com/customer/login"
echo "https://authenticator.task19.com/customer/register"
