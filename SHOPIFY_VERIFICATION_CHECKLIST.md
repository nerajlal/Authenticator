# Shopify Biometric Authentication - Quick Start Checklist

Use this checklist to verify your Shopify integration is working correctly.

## ✅ Pre-Integration Checklist

- [ ] Laravel backend is running and accessible via HTTPS
- [ ] Shopify store is accessible
- [ ] You have access to edit Shopify theme files
- [ ] Modern browser with WebAuthn support (Chrome, Safari, Edge, Firefox)
- [ ] Device with biometric capability (iPhone, Android, Mac, Windows)

## 📝 Integration Steps

### Step 1: Backend Configuration

- [ ] Update `.env` file with correct `APP_URL`
- [ ] Run database migrations: `php artisan migrate`
- [ ] Verify API endpoints are accessible:
  - [ ] Test: `curl https://your-domain.com/auth/api/biometric/login-options -X POST`
  - [ ] Should return JSON (not 404 error)

### Step 2: Shopify Theme Integration

- [ ] Open Shopify Admin → Online Store → Themes → Edit code
- [ ] Open `Layout/theme.liquid`
- [ ] Add configuration script before `</head>` (see `shopify-examples/theme.liquid.example`)
- [ ] Update `window.biometricApiBase` with your actual API URL
- [ ] Add biometric script tag
- [ ] Save theme

### Step 3: Verify Script Loading

- [ ] Visit your Shopify store
- [ ] Open browser console (F12)
- [ ] Look for message: `Biometric Script Loaded`
- [ ] Look for message: `[Biometric Auth] Initializing biometric authentication...`
- [ ] No JavaScript errors in console

## 🧪 Testing Checklist

### Test 1: Login Page Button Injection

- [ ] **Logout** from Shopify store (if logged in)
- [ ] **Navigate** to `/account/login`
- [ ] **Verify**: Biometric button appears ABOVE the email/password form
- [ ] **Verify**: Button shows device-specific text:
  - iOS: "Login with Face ID or Touch ID"
  - Android: "Login with Fingerprint"
  - Mac: "Login with Touch ID"
  - Windows: "Login with Windows Hello"
- [ ] **Verify**: "or use password" divider appears below button
- [ ] **Verify**: Standard email/password form is below divider

**✅ PASS** if button appears with correct text  
**❌ FAIL** if no button appears → Check console for errors

### Test 2: Account Page Enrollment (First-Time User)

**Prerequisites**: Create a new test customer account

- [ ] **Login** to Shopify with test customer credentials
- [ ] **Navigate** to `/account`
- [ ] **Verify**: "Biometric Login" section appears on account page
- [ ] **Verify**: Section shows "Enable Biometric Login" button
- [ ] **Verify**: Explanation text appears
- [ ] **Click**: "Enable Biometric Login" button
- [ ] **Verify**: Device biometric prompt appears (Face ID/Touch ID/Fingerprint)
- [ ] **Complete**: Biometric scan
- [ ] **Verify**: Success message or confirmation appears
- [ ] **Refresh**: Page
- [ ] **Verify**: Section now shows registered device instead of enable button

**✅ PASS** if enrollment completes successfully  
**❌ FAIL** if errors occur → Check Laravel logs and browser console

### Test 3: Biometric Login (Returning User)

**Prerequisites**: Complete Test 2 first

- [ ] **Logout** from Shopify store
- [ ] **Navigate** to `/account/login`
- [ ] **Verify**: Biometric button appears
- [ ] **DO NOT** enter email or password
- [ ] **Click**: Biometric login button
- [ ] **Verify**: Device biometric prompt appears
- [ ] **Complete**: Biometric scan
- [ ] **Verify**: Successfully logged in
- [ ] **Verify**: Redirected to account page
- [ ] **Verify**: Can access account pages (authenticated)

**✅ PASS** if login works without email/password  
**❌ FAIL** if authentication fails → Check Laravel logs

### Test 4: Fallback to Password Login

**Prerequisites**: Have biometric enrolled

- [ ] **Logout** from Shopify store
- [ ] **Navigate** to `/account/login`
- [ ] **Click**: Biometric button
- [ ] **Cancel**: Biometric prompt (or let it fail)
- [ ] **Verify**: Error message appears on button
- [ ] **Verify**: Button resets after 3 seconds
- [ ] **Enter**: Email and password in form below
- [ ] **Click**: "Sign In" button
- [ ] **Verify**: Successfully logged in with password

**✅ PASS** if password login works after biometric fails  
**❌ FAIL** if cannot login with password

### Test 5: Multiple Devices

**Prerequisites**: Have enrollment on Device A (e.g., iPhone)

- [ ] **Login** on Device B (e.g., MacBook)
- [ ] **Navigate** to `/account`
- [ ] **Verify**: Existing device (Device A) is listed
- [ ] **Click**: "Add Another Device" button (if available)
- [ ] **Complete**: Biometric enrollment on Device B
- [ ] **Verify**: Both devices now listed
- [ ] **Logout**
- [ ] **Login** with biometric on Device B
- [ ] **Verify**: Login successful

**✅ PASS** if multiple devices work  
**❌ FAIL** if second device enrollment fails

### Test 6: Device Management

**Prerequisites**: Have 2+ devices enrolled

- [ ] **Login** and navigate to `/account`
- [ ] **Verify**: All registered devices are listed
- [ ] **Click**: "Remove" button on one device
- [ ] **Verify**: Confirmation prompt (if any)
- [ ] **Confirm**: Removal
- [ ] **Verify**: Device removed from list
- [ ] **Logout**
- [ ] **Try**: Login with removed device
- [ ] **Verify**: Biometric fails (device not recognized)

**✅ PASS** if device removal works  
**❌ FAIL** if removed device still works

## 🔍 Troubleshooting

### Issue: Biometric button not appearing on login page

**Check**:
1. Browser console for errors
2. Script loaded: `console.log(window.initBiometricLogin)`
3. WebAuthn supported: `console.log(window.PublicKeyCredential)`
4. Login form detected: Look for `[Biometric Auth] Login form or email input found`

**Fix**:
- Ensure script is loaded (check Network tab)
- Verify HTTPS is enabled
- Check Shopify theme structure

### Issue: Enrollment section not appearing on account page

**Check**:
1. Customer is logged in: `console.log(window.shopifyCustomerId)`
2. Script detected account page: Look for `[Biometric Auth] Account page detected`
3. Account page has `.account-details` container

**Fix**:
- Add manual container to account template (see `shopify-examples/account.liquid.example`)
- Check browser console for detection messages

### Issue: API errors (CORS)

**Check**:
1. Browser console shows CORS errors
2. Network tab shows failed requests

**Fix**:
- Update Laravel CORS configuration
- Ensure `APP_URL` matches Shopify domain
- Check `AllowShopifyEmbedding` middleware is active

### Issue: Customer not syncing

**Check**:
1. Laravel logs: `tail -f storage/logs/laravel.log`
2. Shopify customer ID is passed: `console.log(window.shopifyCustomerId)`

**Fix**:
- Verify theme.liquid has customer data script
- Manually sync customer via API
- Check database for user record

## 📊 Success Criteria

Your integration is successful when:

- ✅ Biometric button appears on login page automatically
- ✅ Enrollment section appears on account page automatically
- ✅ First-time users can enroll from account page
- ✅ Returning users can login with biometric (no email/password)
- ✅ Fallback to password works if biometric fails
- ✅ Multiple devices can be registered and managed
- ✅ No console errors or API failures

## 🎉 Next Steps

Once all tests pass:

1. **Test with real customers** (beta group)
2. **Monitor Laravel logs** for errors
3. **Track enrollment rate** (how many customers enable it)
4. **Customize styling** to match your brand
5. **Add analytics** to measure usage
6. **Document for customers** (help page explaining biometric login)

## 📞 Support

If you encounter issues:

1. **Check Laravel logs**: `storage/logs/laravel.log`
2. **Check browser console**: F12 → Console tab
3. **Enable debug mode**: Set `debug: true` in biometric-login.js
4. **Review integration guide**: `SHOPIFY_INTEGRATION_GUIDE.md`
5. **Check example templates**: `shopify-examples/` directory

## 🔐 Security Verification

- [ ] HTTPS enabled on both Shopify and Laravel backend
- [ ] CORS configured correctly (only your domains allowed)
- [ ] API endpoints require authentication where needed
- [ ] Customer data is synced securely
- [ ] Biometric data never leaves device (WebAuthn standard)
- [ ] Public keys stored securely in database
- [ ] Session management working correctly

---

**Last Updated**: 2026-02-10  
**Version**: 1.0
