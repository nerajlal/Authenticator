# Shopify Theme Integration - Redirect-Based Flow

## Overview

This guide shows how to integrate biometric authentication into your Shopify theme using a redirect-based flow.

---

## Step 1: Add Configuration to theme.liquid

Add this code in your `theme.liquid` file, just before the closing `</head>` tag:

```liquid
{% if customer %}
<script>
  // Biometric authentication configuration
  window.shopifyCustomerId = '{{ customer.id }}';
  window.shopifyCustomerEmail = '{{ customer.email }}';
  window.shopifyCustomerFirstName = '{{ customer.first_name }}';
  window.shopifyCustomerLastName = '{{ customer.last_name }}';
</script>
{% endif %}
```

---

## Step 2: Update Account Page

In your account page template (usually `templates/customers/account.liquid` or in a section file), add the biometric enrollment button:

```liquid
<div class="biometric-section" style="margin-top: 2rem; padding: 1.5rem; background: #f7fafc; border-radius: 8px;">
  <h3 style="margin-bottom: 0.5rem;">Biometric Login</h3>
  <p style="color: #718096; margin-bottom: 1rem;">Enable fingerprint or Face ID login for faster access to your account.</p>
  
  {% if customer %}
    <a href="https://authenticator.task19.com/biometric/enroll?customer_id={{ customer.id }}&email={{ customer.email }}&first_name={{ customer.first_name }}&last_name={{ customer.last_name }}&return_url={{ shop.url }}/account" 
       class="button button--primary"
       style="display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">
      🔐 Enable Biometric Login
    </a>
  {% endif %}
</div>
```

---

## Step 3: Update Login Page

In your login page template (usually `templates/customers/login.liquid`), add the biometric login button ABOVE the email/password form:

```liquid
<!-- Biometric Login Button -->
<div class="biometric-login" style="margin-bottom: 2rem; text-align: center;">
  <a href="https://authenticator.task19.com/biometric/auth?return_url={{ shop.url }}/account" 
     class="button button--secondary"
     style="display: inline-block; width: 100%; padding: 16px; background: #f7fafc; color: #2d3748; text-decoration: none; border-radius: 8px; font-weight: 600; border: 2px solid #e2e8f0; transition: all 0.3s ease;">
    <span style="font-size: 24px; vertical-align: middle;">👆</span>
    <span style="vertical-align: middle; margin-left: 8px;">Login with Biometric</span>
  </a>
  <p style="margin-top: 12px; color: #718096; font-size: 14px;">or use email/password below</p>
</div>

<!-- Original Email/Password Form -->
<form method="post" action="/account/login" id="customer_login" accept-charset="UTF-8">
  <!-- Your existing login form fields -->
</form>
```

---

## Important Notes

### URL Configuration

Replace `https://authenticator.task19.com` with your actual Laravel backend URL.

### Return URL

The `return_url` parameter tells the system where to redirect after enrollment/login. You can customize this:

- **Account page**: `{{ shop.url }}/account`
- **Homepage**: `{{ shop.url }}`
- **Specific page**: `{{ shop.url }}/pages/welcome`

### Styling

The inline styles above are basic examples. You should:
1. Match your theme's button styles
2. Use your theme's CSS classes
3. Adjust colors to match your brand

### Testing

1. **Enrollment**: Log in to Shopify → Go to account page → Click "Enable Biometric Login"
2. **Login**: Log out → Go to login page → Click "Login with Biometric"

---

## Example: Dawn Theme Integration

For Shopify's Dawn theme, here's a more integrated example:

### Account Page (sections/main-account.liquid)

```liquid
{%- style -%}
  .biometric-card {
    margin-top: 2rem;
    padding: 2rem;
    background: rgb(var(--color-background));
    border: 1px solid rgba(var(--color-foreground), 0.1);
    border-radius: var(--border-radius);
  }
{%- endstyle -%}

<div class="biometric-card">
  <h2 class="h3">{{ 'customer.account.biometric_title' | t | default: 'Biometric Login' }}</h2>
  <p>{{ 'customer.account.biometric_description' | t | default: 'Enable fingerprint or Face ID for faster login.' }}</p>
  
  <a href="https://authenticator.task19.com/biometric/enroll?customer_id={{ customer.id }}&email={{ customer.email }}&first_name={{ customer.first_name }}&last_name={{ customer.last_name }}&return_url={{ shop.url }}/account" 
     class="button">
    Enable Biometric Login
  </a>
</div>
```

### Login Page (sections/main-login.liquid)

```liquid
<div class="biometric-login-wrapper" style="margin-bottom: 2rem;">
  <a href="https://authenticator.task19.com/biometric/auth?return_url={{ shop.url }}/account" 
     class="button button--secondary button--full-width">
    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor" style="vertical-align: middle; margin-right: 8px;">
      <path d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12z"/>
    </svg>
    Login with Biometric
  </a>
  <p class="caption-large text-center" style="margin-top: 1rem;">
    {{ 'customer.login.biometric_or' | t | default: 'or use email/password below' }}
  </p>
</div>
```

---

## Troubleshooting

### Button doesn't appear
- Check that you've added the code to the correct template file
- Verify the file has been saved and published

### Redirect doesn't work
- Verify the Laravel backend URL is correct
- Check that the return URL is properly encoded
- Ensure HTTPS is used (required for WebAuthn)

### Styling issues
- Inspect the button in browser dev tools
- Adjust inline styles or add CSS classes
- Match your theme's button component structure

---

## Security Notes

1. **HTTPS Required**: Biometric authentication only works over HTTPS
2. **Return URL Validation**: The backend validates return URLs to prevent open redirects
3. **Session Security**: Sessions use secure cookies with proper SameSite settings
