# Shopify Liquid Template Examples

This directory contains example Shopify Liquid templates showing how the biometric authentication integrates with your Shopify theme.

## Files

### 1. `theme.liquid.example`
**What it does**: Main theme file configuration
**Where to add**: `Layout/theme.liquid` (before `</head>`)
**Required**: ✅ Yes - This is the main integration point

**Contains**:
- API endpoint configuration
- Shopify customer data passing
- Script loading
- Optional custom styling

### 2. `login.liquid.example`
**What it does**: Shows how biometric button appears on login page
**Where to add**: `Templates/customers/login.liquid`
**Required**: ❌ No - Script auto-injects, this is just an example

**Shows**:
- Where biometric button appears (above login form)
- How it works with existing email/password form
- Device-specific button text

### 3. `account.liquid.example`
**What it does**: Shows how biometric settings appear on account page
**Where to add**: `Templates/customers/account.liquid`
**Required**: ❌ No - Script auto-injects, this is just an example

**Shows**:
- Where biometric settings section appears
- First-time user experience (enable button)
- Returning user experience (device list)
- Multi-device management

## Quick Start

**Minimum Required Integration**:

1. Copy code from `theme.liquid.example`
2. Paste into your `Layout/theme.liquid` before `</head>`
3. Update the API endpoint URL to match your backend
4. Save and test!

That's it! The script will automatically:
- Inject biometric button on login page
- Inject settings section on account page
- Handle all biometric operations

## Customization

### Change API Endpoint

Edit this line in `theme.liquid`:
```liquid
window.biometricApiBase = 'https://YOUR-DOMAIN.com/auth/api/biometric';
```

### Customize Button Style

Add CSS in `theme.liquid` or your theme's stylesheet:
```css
.biometric-login-button {
  background: #your-color !important;
  border-radius: 8px !important;
}
```

### Manual Container (If Auto-Detection Fails)

Add this to your account template:
```liquid
<div class="account-details">
  <!-- Biometric settings will inject here -->
</div>
```

## Testing

1. **Add code to theme.liquid**
2. **Save theme**
3. **Test login page**: Visit `/account/login` - should see biometric button
4. **Test account page**: Login and visit `/account` - should see settings section

## Support

See `SHOPIFY_INTEGRATION_GUIDE.md` for:
- Detailed setup instructions
- Troubleshooting guide
- Testing procedures
- Security notes
