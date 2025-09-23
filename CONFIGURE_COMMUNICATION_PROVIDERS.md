# How to Configure Jusibe and Bulksmsnigeria Settings

This guide will walk you through configuring the new SMS providers (Jusibe and Bulksmsnigeria) and WhatsApp providers in your Church Dashboard.

## 🚀 Quick Start

1. **Navigate to Communication Settings**
   - Go to Admin → Communication Settings
   - Select your branch (if you're a super admin)

2. **Configure SMS Provider**
   - Choose either "Jusibe" or "Bulksmsnigeria" from the SMS Provider dropdown
   - Fill in the required configuration fields
   - Save settings

3. **Configure WhatsApp (Optional)**
   - Choose either "Twilio WhatsApp" or "Meta WhatsApp Business" from the WhatsApp Provider dropdown
   - Fill in the required configuration fields
   - Save settings

## 📱 Jusibe SMS Configuration

### Step 1: Get Jusibe Credentials
1. Visit [https://jusibe.com](https://jusibe.com)
2. Sign up for an account
3. Go to your dashboard and find "API Keys"
4. Copy your **Public Key** and **Access Token**
5. Set up a **Sender ID** (max 11 characters, alphanumeric only)

### Step 2: Configure in Church Dashboard
1. In Communication Settings, select **"Jusibe"** as SMS Provider
2. Fill in the configuration fields:
   - **Public Key**: Your Jusibe Public Key
   - **Access Token**: Your Jusibe Access Token  
   - **Sender ID**: Your approved sender ID (e.g., "YourChurch")

### Step 3: Test Configuration
1. Click "Test Settings" to verify the configuration
2. Check the test results to ensure everything is working

## 📱 Bulksmsnigeria.com Configuration

### Step 1: Get Bulksmsnigeria Credentials
1. Visit [https://www.bulksmsnigeria.com](https://www.bulksmsnigeria.com)
2. Sign up for an account
3. Go to your dashboard and find API credentials
4. Copy your **Username** and **Password**
5. Set up a **Sender ID**

### Step 2: Configure in Church Dashboard
1. In Communication Settings, select **"Bulksmsnigeria"** as SMS Provider
2. Fill in the configuration fields:
   - **Username**: Your Bulksmsnigeria Username
   - **Password**: Your Bulksmsnigeria Password
   - **Sender ID**: Your sender ID

### Step 3: Test Configuration
1. Click "Test Settings" to verify the configuration
2. Check the test results to ensure everything is working

## 💬 WhatsApp Configuration

### Option 1: Twilio WhatsApp

#### Step 1: Get Twilio WhatsApp Credentials
1. Visit [https://www.twilio.com](https://www.twilio.com)
2. Sign up for a Twilio account
3. Enable WhatsApp Sandbox or get WhatsApp Business approval
4. Get your **Account SID** and **Auth Token**
5. Get your **WhatsApp From Number** (e.g., +1234567890)

#### Step 2: Configure in Church Dashboard
1. In Communication Settings, select **"Twilio WhatsApp"** as WhatsApp Provider
2. Fill in the configuration fields:
   - **Account SID**: Your Twilio Account SID
   - **Auth Token**: Your Twilio Auth Token
   - **WhatsApp From Number**: Your WhatsApp number (e.g., +1234567890)

### Option 2: Meta WhatsApp Business API

#### Step 1: Get Meta WhatsApp Credentials
1. Visit [https://developers.facebook.com](https://developers.facebook.com)
2. Create a Meta Business account
3. Set up a WhatsApp Business API app
4. Get your **Access Token** and **Phone Number ID**
5. Optionally get your **Business Account ID**

#### Step 2: Configure in Church Dashboard
1. In Communication Settings, select **"Meta WhatsApp Business"** as WhatsApp Provider
2. Fill in the configuration fields:
   - **Access Token**: Your Meta Access Token
   - **Phone Number ID**: Your WhatsApp Phone Number ID
   - **Business Account ID**: Your Meta Business Account ID (optional)

## 🔧 Configuration Examples

### Jusibe SMS Configuration
```json
{
  "sms_provider": "jusibe",
  "sms_config": {
    "public_key": "pk_live_1234567890abcdef",
    "access_token": "sk_live_1234567890abcdef",
    "sender_id": "YourChurch"
  }
}
```

### Bulksmsnigeria SMS Configuration
```json
{
  "sms_provider": "bulksmsnigeria",
  "sms_config": {
    "username": "your_username",
    "password": "your_password",
    "sender_id": "YourChurch"
  }
}
```

### Twilio WhatsApp Configuration
```json
{
  "whatsapp_provider": "twilio",
  "whatsapp_config": {
    "account_sid": "AC1234567890abcdef",
    "auth_token": "your_auth_token",
    "from_number": "+1234567890"
  }
}
```

### Meta WhatsApp Configuration
```json
{
  "whatsapp_provider": "meta",
  "whatsapp_config": {
    "access_token": "EAABwzLixnjYBO...",
    "phone_number_id": "123456789012345",
    "business_account_id": "123456789012345"
  }
}
```

## 📋 Configuration Checklist

### For Jusibe SMS:
- [ ] Jusibe account created
- [ ] Public Key obtained
- [ ] Access Token obtained
- [ ] Sender ID approved (max 11 characters)
- [ ] Configuration saved in Church Dashboard
- [ ] Test settings successful

### For Bulksmsnigeria SMS:
- [ ] Bulksmsnigeria account created
- [ ] Username obtained
- [ ] Password obtained
- [ ] Sender ID set up
- [ ] Configuration saved in Church Dashboard
- [ ] Test settings successful

### For Twilio WhatsApp:
- [ ] Twilio account created
- [ ] WhatsApp Sandbox enabled or Business approval obtained
- [ ] Account SID obtained
- [ ] Auth Token obtained
- [ ] WhatsApp From Number obtained
- [ ] Configuration saved in Church Dashboard
- [ ] Test settings successful

### For Meta WhatsApp:
- [ ] Meta Business account created
- [ ] WhatsApp Business API app created
- [ ] Access Token obtained
- [ ] Phone Number ID obtained
- [ ] Business Account ID obtained (optional)
- [ ] Configuration saved in Church Dashboard
- [ ] Test settings successful

## 🧪 Testing Your Configuration

### Test SMS
1. Go to Quick Send in your dashboard
2. Select "SMS" as the message type
3. Choose a recipient with a phone number
4. Send a test message
5. Check if the message is delivered

### Test WhatsApp
1. Go to Quick Send in your dashboard
2. Select "WhatsApp" as the message type
3. Choose a recipient with a phone number
4. Send a test message
5. Check if the message is delivered

## 🔍 Troubleshooting

### Common Issues

#### Jusibe Issues:
- **"Configuration incomplete"**: Check that all three fields are filled
- **"SMS delivery failed"**: Verify sender ID is approved
- **"Invalid credentials"**: Double-check public key and access token

#### Bulksmsnigeria Issues:
- **"Configuration incomplete"**: Check that all three fields are filled
- **"Invalid credentials"**: Verify username and password
- **"SMS delivery failed"**: Check account balance and sender ID

#### WhatsApp Issues:
- **"Configuration incomplete"**: Check that required fields are filled
- **"Invalid phone number"**: Ensure phone numbers are in international format (+234...)
- **"WhatsApp not configured"**: Verify provider is selected and configured

### Debug Steps
1. Check the logs in `storage/logs/laravel.log`
2. Use the "Test Settings" button to verify configuration
3. Check your provider dashboard for delivery status
4. Verify phone number formatting (Nigerian numbers for SMS, international for WhatsApp)

## 📞 Phone Number Formats

### For SMS (Jusibe & Bulksmsnigeria):
- **Input**: `09068719246` or `+2349068719246`
- **Processed**: `09068719246` (Nigerian format)

### For WhatsApp (Twilio & Meta):
- **Input**: `09068719246` or `+2349068719246`
- **Processed**: `+2349068719246` (International format)

## 💡 Tips

1. **Start with Jusibe**: It's easier to set up and test
2. **Test with small amounts**: Don't send bulk messages until you're sure it works
3. **Check provider dashboards**: Monitor delivery status and costs
4. **Keep credentials secure**: Never share your API keys
5. **Monitor logs**: Check Laravel logs for detailed error information

## 🆘 Support

If you encounter issues:
1. Check the troubleshooting section above
2. Review the logs in `storage/logs/laravel.log`
3. Verify your provider account status and balance
4. Contact your provider's support if needed

## 📚 Additional Resources

- [Jusibe Documentation](https://jusibe.com/docs)
- [Bulksmsnigeria API](https://www.bulksmsnigeria.com/api)
- [Twilio WhatsApp API](https://www.twilio.com/docs/whatsapp)
- [Meta WhatsApp Business API](https://developers.facebook.com/docs/whatsapp)


