# API Connection Troubleshooting

## The Error
```
Failed to execute 'json' on 'Response': Unexpected end of JSON input
```

This means the API is returning HTML (probably an error page) instead of JSON.

## Quick Diagnostics

### 1. Check API URL in Shopify Theme

Open your Shopify theme.liquid and find this line:
```javascript
window.biometricApiBase = 'https://...';
```

**What should it be?**
- Your Digital Ocean domain + `/api/biometric`
- Example: `https://your-domain.com/api/biometric`

### 2. Test API Endpoint Directly

From your Digital Ocean server, test if the API works:

```bash
# Test the register-options endpoint
curl -X POST https://your-domain.com/api/biometric/register-options \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"test@example.com"}'
```

**Expected response**: JSON with challenge data  
**Bad response**: HTML error page

### 3. Common Issues

**Issue 1: Wrong API URL**
- Check `window.biometricApiBase` in Shopify theme
- Should match your Digital Ocean domain

**Issue 2: CORS Not Configured**
- Laravel needs to allow Shopify domain
- Check `config/cors.php`

**Issue 3: Route Not Found**
- Verify routes exist: `php artisan route:list | grep biometric`

**Issue 4: Customer Not Synced**
- User must exist in Laravel database first
- Check: `SELECT * FROM users WHERE shopify_customer_id = 'YOUR_ID';`

## Quick Fix Steps

### Step 1: Pull Latest Code on Digital Ocean
```bash
cd /var/www/Authenticator
git pull origin main
php artisan cache:clear
php artisan route:clear
```

### Step 2: Check Routes Exist
```bash
php artisan route:list | grep biometric
```

You should see:
- POST api/biometric/register-options
- POST api/biometric/register-verify
- POST api/biometric/login-options
- POST api/biometric/login-verify

### Step 3: Check Laravel Logs
```bash
tail -f /var/www/Authenticator/storage/logs/laravel.log
```

Then try to enable biometric again and watch the logs.

### Step 4: Verify API Base URL

In browser console on Shopify, check:
```javascript
console.log(window.biometricApiBase);
```

Should show your Digital Ocean domain.

## Need More Help?

Share:
1. What `window.biometricApiBase` shows in console
2. Output of `php artisan route:list | grep biometric`
3. Any errors in Laravel logs
