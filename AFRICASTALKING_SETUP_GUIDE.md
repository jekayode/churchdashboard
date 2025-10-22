# Africa's Talking SMS Setup Guide

## Current Issue
Your SMS is failing with "HTTP request returned status code 401: The supplied authentication is invalid" because the current configuration has mixed credentials from different providers.

## How to Fix

### 1. Get Your Africa's Talking Credentials

1. **Go to Africa's Talking Dashboard**: https://account.africastalking.com/
2. **Login** to your account
3. **Navigate to SMS**: Go to "SMS" → "Settings" or "API" section
4. **Copy your credentials**:
   - **API Key**: This is different from your Resend API key
   - **Username**: Your Africa's Talking username (usually your account username)

### 2. Update Your Configuration

#### Option A: Via the Web Interface
1. Go to `/pastor/communication/settings` in your application
2. Select "Africa's Talking" as SMS Provider
3. Enter the correct credentials:
   - **API Key**: Your Africa's Talking API key (not the Resend one)
   - **Username**: Your Africa's Talking username
   - **Sender ID**: "LifePointe" (or your preferred sender name)

#### Option B: Via Database (if needed)
```bash
php artisan tinker
```

```php
$setting = App\Models\CommunicationSetting::where('branch_id', 1)->first();
$setting->sms_config = [
    'api_key' => 'YOUR_ACTUAL_AFRICAS_TALKING_API_KEY',
    'username' => 'YOUR_ACTUAL_AFRICAS_TALKING_USERNAME', 
    'sender_id' => 'LifePointe'
];
$setting->save();
```

### 3. Test the Configuration

After updating the credentials:

1. **Test via Web Interface**: Use the "Test Settings" button
2. **Test via Quick Send**: Try sending an SMS to a test number
3. **Check Logs**: Monitor `storage/logs/laravel.log` for any errors

### 4. Common Issues

#### Wrong API Key
- **Problem**: Using Resend API key instead of Africa's Talking API key
- **Solution**: Get the correct API key from Africa's Talking dashboard

#### Wrong Username
- **Problem**: Using email address instead of username
- **Solution**: Use your Africa's Talking username (not email)

#### Sender ID Issues
- **Problem**: Sender ID not approved
- **Solution**: Use "LifePointe" or get your sender ID approved

#### Phone Number Format
- **Problem**: Wrong phone number format
- **Solution**: Ensure numbers start with +234 for Nigeria

### 5. Verification

After configuration, you should see in the logs:
```
[INFO] Sending SMS via Africa's Talking
[INFO] SMS sent successfully
```

Instead of:
```
[ERROR] SMS sending failed: HTTP request returned status code 401
```

## Next Steps

1. **Get your Africa's Talking credentials** from the dashboard
2. **Update the configuration** with the correct credentials
3. **Test sending an SMS** to verify it works
4. **Monitor the logs** to ensure successful delivery

## Support

If you continue to have issues:
1. Check your Africa's Talking account balance
2. Verify your sender ID is approved
3. Ensure phone numbers are in correct format (+234...)
4. Check the Africa's Talking dashboard for any account restrictions



















