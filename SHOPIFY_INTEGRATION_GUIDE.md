# Shopify Biometric Login Integration Guide

## 🎯 Overview

This guide shows you how to integrate biometric authentication (Face ID, Touch ID, Fingerprint, Windows Hello) into your Shopify store. Your customers will be able to:

1. **First-time users**: Enable biometric login from their account page
2. **Returning users**: Login with biometrics directly from the login page (no email/password needed)
3. **Fallback**: Use email/password if biometric fails

---

## 📋 Prerequisites

- Shopify store (any plan)
- Access to edit theme files
- Laravel backend hosted and accessible (this authenticator app)
- HTTPS enabled (required for WebAuthn)

---

## 🚀 Quick Start (5 Steps)

### Step 1: Host Your Laravel Backend

Your Laravel authenticator app needs to be accessible from your Shopify store.

**Option A: Same Domain (Recommended)**
- Host at `https://yourstore.com/auth` using a subdirectory or proxy

**Option B: Subdomain**
- Host at `https://auth.yourstore.com`
- Requires CORS configuration (already included in the app)

**Option C: Separate Domain**
- Host at `https://biometric-auth.yourdomain.com`
- Requires CORS configuration

### Step 2: Configure Environment Variables

Edit your `.env` file in the Laravel app:

```env
APP_URL=https://yourstore.com/auth
# OR
APP_URL=https://auth.yourstore.com

# Add your Shopify store domain
SHOPIFY_STORE_DOMAIN=yourstore.myshopify.com

# Enable CORS for Shopify
CORS_ALLOWED_ORIGINS=https://yourstore.com,https://yourstore.myshopify.com
```

### Step 3: Add Script to Shopify Theme

1. **Go to**: Shopify Admin → Online Store → Themes → Actions → Edit code
2. **Open**: `Layout/theme.liquid`
3. **Add before `</head>`**:

```liquid
<!-- Biometric Authentication Script -->
<script>
  // Configure API endpoint
  window.biometricApiBase = 'https://yourstore.com/auth/api/biometric';
  // OR if using subdomain:
  // window.biometricApiBase = 'https://auth.yourstore.com/api/biometric';
  
  {% if customer %}
    // Pass customer data to biometric script
    window.shopifyCustomerId = '{{ customer.id }}';
    window.shopifyCustomerEmail = '{{ customer.email }}';
    window.isShopifyStore = true;
  {% endif %}
</script>
<script src="https://yourstore.com/auth/js/biometric-login.js"></script>
<!-- OR if using subdomain: -->
<!-- <script src="https://auth.yourstore.com/js/biometric-login.js"></script> -->
```

### Step 4: Customize Login Page (Optional)

The script automatically injects the biometric button, but you can customize the login page template for better styling.

**File**: `Templates/customers/login.liquid`

The biometric button will automatically appear above your login form. No changes needed!

### Step 5: Customize Account Page (Optional)

The script automatically adds biometric settings to the account page.

**File**: `Templates/customers/account.liquid`

The biometric enrollment section will automatically appear. No changes needed!

---

## 🎨 Customization

### Customize Button Styling

Add this CSS to your theme's stylesheet or in `theme.liquid`:

```liquid
<style>
  /* Customize biometric login button */
  .biometric-login-button {
    background: linear-gradient(135deg, #your-brand-color 0%, #your-brand-color-dark 100%) !important;
    border-radius: 4px !important;
    /* Add more custom styles */
  }
  
  /* Customize enrollment settings on account page */
  .biometric-settings-container {
    background: #f9f9f9 !important;
    border: 1px solid #e0e0e0 !important;
    /* Add more custom styles */
  }
</style>
```

### Change Button Text

Edit `public/js/biometric-login.js` and modify the `getDeviceCapabilityText()` function (lines 62-97).

---

## 🔄 Customer Flow

### First-Time User Flow

```
1. Customer creates account or logs in with password
   ↓
2. Redirected to account page (/account)
   ↓
3. Sees "Biometric Login Settings" section
   ↓
4. Clicks "Enable Biometric Login"
   ↓
5. Device prompts for Face ID/Touch ID/Fingerprint
   ↓
6. Biometric registered ✓
   ↓
7. Can now use biometric login
```

### Returning User Flow

```
1. Customer visits login page (/account/login)
   ↓
2. Sees biometric button above login form
   ↓
3. Clicks biometric button (NO email/password needed)
   ↓
4. Device prompts for Face ID/Touch ID/Fingerprint
   ↓
5. Authenticated and logged in ✓
   ↓
6. Redirected to account page
```

### Fallback Flow (If Biometric Fails)

```
1. Customer clicks biometric button
   ↓
2. Biometric authentication fails or is cancelled
   ↓
3. Error message shows on button
   ↓
4. Button resets after 3 seconds
   ↓
5. Customer can use email/password form below
```

---

## 🔧 Advanced Configuration

### Custom API Endpoint

If your API is at a different path, update the configuration:

```liquid
<script>
  window.biometricApiBase = 'https://custom-domain.com/custom-path/api/biometric';
</script>
```

### Disable Enrollment Popup (Use Only Account Page)

The app is already configured to use account page enrollment for Shopify. The popup only appears when using the Laravel-hosted login pages.

### Multiple Devices

Customers can register multiple devices (iPhone, MacBook, etc.):
1. Login on each device
2. Go to account page
3. Click "Add Another Device"
4. Complete biometric enrollment

---

## 🧪 Testing

### Test Enrollment

1. **Create test customer** in Shopify Admin
2. **Login** to your store with test customer credentials
3. **Navigate** to `/account`
4. **Verify**: "Biometric Login Settings" section appears
5. **Click**: "Enable Biometric Login"
6. **Complete**: Biometric scan on your device
7. **Verify**: Success message appears
8. **Refresh**: Page should show registered device

### Test Login

1. **Logout** from your store
2. **Navigate** to `/account/login`
3. **Verify**: Biometric button appears above login form
4. **Click**: Biometric button (don't enter email/password)
5. **Complete**: Biometric scan
6. **Verify**: Logged in and redirected to account page

### Test Fallback

1. **Logout** from your store
2. **Navigate** to `/account/login`
3. **Click**: Biometric button
4. **Cancel**: The biometric prompt
5. **Verify**: Error message appears, button resets
6. **Enter**: Email and password in form
7. **Verify**: Can still login with password

---

## 🐛 Troubleshooting

### Biometric Button Not Appearing

**Check**:
1. Script is loaded (check browser console for errors)
2. WebAuthn is supported (modern browser required)
3. HTTPS is enabled (required for WebAuthn)
4. Login form is detected (check console logs)

**Solution**:
- Open browser console (F12)
- Look for `[Biometric Auth]` messages
- Check if script loaded: `console.log(window.initBiometricLogin)`

### API Errors (CORS)

**Symptom**: Console shows CORS errors

**Solution**: Update Laravel CORS configuration

File: `config/cors.php` (or create if doesn't exist)

```php
<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://yourstore.com',
        'https://yourstore.myshopify.com',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

### Customer Not Syncing

**Symptom**: Enrollment fails with "User not found"

**Solution**: The app auto-syncs customers, but you can manually sync:

```bash
# In Laravel app directory
php artisan tinker

# Sync a customer
$customer = [
    'shopify_customer_id' => '123456789',
    'email' => 'customer@example.com',
    'first_name' => 'John',
    'last_name' => 'Doe'
];

$response = app('App\Http\Controllers\ShopifyController')->syncCustomer(
    new \Illuminate\Http\Request($customer)
);
```

### Enrollment Section Not Showing on Account Page

**Check**:
1. Customer is logged in
2. Script is loaded
3. Account page has standard Shopify structure

**Solution**: Add a container manually to your account template:

```liquid
<!-- In Templates/customers/account.liquid -->
<div class="account-details">
  <!-- Existing account content -->
  
  <!-- Biometric settings will inject here -->
</div>
```

---

## 🔐 Security Notes

1. **HTTPS Required**: WebAuthn only works over HTTPS
2. **Private Keys Never Leave Device**: Biometric data stays on the device
3. **Public Key Cryptography**: Server only stores public keys
4. **Replay Protection**: Built-in counter prevents replay attacks
5. **Origin Verification**: Requests are validated against your domain

---

## 📱 Supported Devices & Browsers

### ✅ Supported

| Device | Biometric | Browser |
|--------|-----------|---------|
| iPhone/iPad | Face ID, Touch ID | Safari, Chrome |
| Mac | Touch ID | Safari, Chrome, Edge |
| Android | Fingerprint | Chrome, Edge, Firefox |
| Windows | Windows Hello | Chrome, Edge, Firefox |

### ❌ Not Supported

- Internet Explorer
- Older browsers without WebAuthn support
- HTTP (non-HTTPS) sites

---

## 🆘 Support

### Check Logs

**Laravel Logs**:
```bash
tail -f storage/logs/laravel.log
```

**Browser Console**:
- Open Developer Tools (F12)
- Check Console tab for `[Biometric Auth]` messages

### Debug Mode

Enable debug mode in the script:

```javascript
// In biometric-login.js, line 13
const CONFIG = {
    apiBase: window.biometricApiBase || window.location.origin + '/api/biometric',
    debug: true  // Set to true for detailed logs
};
```

### Common Issues

1. **"WebAuthn not supported"**: Update browser or use supported device
2. **"No login form found"**: Check Shopify theme structure
3. **"API request failed"**: Check CORS and API endpoint configuration
4. **"Origin mismatch"**: Ensure APP_URL matches your Shopify domain

---

## 🎉 You're Done!

Your Shopify store now has biometric authentication! Customers can:
- ✅ Enable biometric login from their account page
- ✅ Login with Face ID/Touch ID/Fingerprint
- ✅ Manage multiple devices
- ✅ Fallback to password if needed

**Next Steps**:
- Test with real customers
- Monitor Laravel logs for issues
- Customize button styling to match your brand
- Add analytics to track enrollment rates
