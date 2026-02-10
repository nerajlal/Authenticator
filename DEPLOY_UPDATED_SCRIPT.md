# Quick Deployment Guide - Updated Biometric Script

## What Changed
Updated `public/js/biometric-login.js` with enhanced account page detection that works with your Shopify theme.

## Deploy to Digital Ocean (3 Steps)

### Step 1: Commit Changes Locally
```bash
cd c:\xampp\htdocs\Authenticator

git add public/js/biometric-login.js
git commit -m "Enhanced account page detection for Shopify themes"
git push origin main
```

### Step 2: Deploy to Digital Ocean
```bash
# SSH into your server
ssh your-user@your-droplet-ip

# Navigate to Laravel app
cd /path/to/your/laravel/app

# Pull latest changes
git pull origin main

# Clear cache (important!)
php artisan cache:clear
```

### Step 3: Test
1. **Clear browser cache** (Ctrl+Shift+Delete)
2. **Refresh** your Shopify account page
3. **Look for** "Biometric Login" section

## Alternative: Quick Test Without Deployment

If you want to test immediately without deploying:

1. **Copy** the updated `biometric-login.js` file
2. **Upload** directly to your Digital Ocean server:
   ```bash
   scp c:\xampp\htdocs\Authenticator\public\js\biometric-login.js your-user@your-droplet-ip:/path/to/laravel/public/js/
   ```
3. **Clear browser cache** and refresh

## What to Expect After Deployment

Console should show:
```
[Biometric Auth] Account page detected via heading: Account
```

Page should show:
- **Biometric Login** section
- **Enable Biometric Login** button

---

**Need help with SSH or deployment?** Let me know your Digital Ocean setup!
