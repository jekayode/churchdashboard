# New Communication Providers

This document outlines the newly added communication providers for the Church Dashboard system.

## 🆕 New SMS Providers

### 1. Jusibe SMS
- **Website**: [https://jusibe.com](https://jusibe.com)
- **Documentation**: [https://jusibe.com/docs](https://jusibe.com/docs)
- **Provider ID**: `jusibe`
- **Configuration Fields**:
  - `public_key`: Your Jusibe Public Key
  - `access_token`: Your Jusibe Access Token
  - `sender_id`: Sender ID (max 11 characters)

**API Details**:
- **Endpoint**: `https://jusibe.com/smsapi/send_sms`
- **Method**: POST
- **Authentication**: HTTP Basic Auth (public_key as username, access_token as password)
- **Phone Format**: Nigerian numbers only (e.g., `09068719246`)
- **Response**: Returns `message_id` for tracking

### 2. Bulksmsnigeria.com
- **Website**: [https://www.bulksmsnigeria.com](https://www.bulksmsnigeria.com)
- **Provider ID**: `bulksmsnigeria`
- **Configuration Fields**:
  - `username`: Your Bulksmsnigeria Username
  - `password`: Your Bulksmsnigeria Password
  - `sender_id`: Sender ID

**API Details**:
- **Endpoint**: `https://www.bulksmsnigeria.com/api/v1/sms/create`
- **Method**: POST
- **Authentication**: HTTP Basic Auth (username, password)
- **Phone Format**: Nigerian numbers (e.g., `09068719246`)
- **Response**: Returns message ID in `data.id`

## 🆕 New WhatsApp Providers

### 1. Twilio WhatsApp
- **Provider ID**: `twilio`
- **Configuration Fields**:
  - `account_sid`: Your Twilio Account SID
  - `auth_token`: Your Twilio Auth Token
  - `from_number`: Your Twilio WhatsApp From Number

**API Details**:
- **Endpoint**: `https://api.twilio.com/2010-04-01/Accounts/{sid}/Messages.json`
- **Method**: POST
- **Authentication**: HTTP Basic Auth (account_sid, auth_token)
- **Phone Format**: International format (e.g., `+2349068719246`)
- **From Format**: `whatsapp:{from_number}`
- **To Format**: `whatsapp:{recipient_number}`

### 2. Meta WhatsApp Business API
- **Provider ID**: `meta`
- **Configuration Fields**:
  - `access_token`: Your Meta Access Token
  - `phone_number_id`: Your WhatsApp Phone Number ID
  - `business_account_id`: Your Meta Business Account ID (optional)

**API Details**:
- **Endpoint**: `https://graph.facebook.com/v18.0/{phone_number_id}/messages`
- **Method**: POST
- **Authentication**: Bearer Token (access_token)
- **Phone Format**: International format (e.g., `+2349068719246`)
- **Message Type**: Text messages only (can be extended for media)

## 🔧 Implementation Details

### Database Changes
- Added `whatsapp_provider` and `whatsapp_config` columns to `communication_settings` table
- Updated `CommunicationSetting` model to include new fields
- Added proper validation for all new providers

### API Endpoints
- **Communication Settings**: Updated to support new providers
- **Quick Send**: Now supports WhatsApp messaging
- **Provider Templates**: Added configuration templates for all new providers

### Phone Number Formatting
- **Jusibe & Bulksmsnigeria**: Converts `+2349068719246` → `09068719246`
- **Twilio & Meta WhatsApp**: Converts `09068719246` → `+2349068719246`
- **Africa's Talking**: Converts `09068719246` → `+2349068719246`

## 🧪 Testing

### Test Command
```bash
php artisan sms:test-new-providers "09068719246" "Test message" --branch=1
```

### Manual Testing
1. Configure a new provider in Communication Settings
2. Use Quick Send to test individual messages
3. Check logs for detailed API responses
4. Verify message delivery in provider dashboards

## 📊 Logging

All new providers include comprehensive logging:
- **Request Details**: Phone formatting, content length, provider config
- **API Responses**: Full response bodies for debugging
- **Error Handling**: Detailed error messages with context
- **Performance**: Execution time tracking

## 🔒 Security

- All API keys and tokens are stored securely in the database
- Sensitive fields are marked as password type in the UI
- No credentials are logged in plain text
- Proper validation prevents configuration errors

## 🚀 Usage Examples

### Configure Jusibe SMS
```json
{
  "sms_provider": "jusibe",
  "sms_config": {
    "public_key": "your_public_key",
    "access_token": "your_access_token",
    "sender_id": "YourChurch"
  }
}
```

### Configure Meta WhatsApp
```json
{
  "whatsapp_provider": "meta",
  "whatsapp_config": {
    "access_token": "your_meta_token",
    "phone_number_id": "123456789012345",
    "business_account_id": "optional_business_id"
  }
}
```

### Send WhatsApp Message
```php
$communicationService->sendWhatsApp(
    $branch,
    '+2349068719246',
    'Hello from Church Dashboard!',
    $template,
    $user,
    $variables
);
```

## 📈 Performance

- All new providers include retry logic (3 attempts with 1s delay)
- 30-second timeout for all API calls
- Asynchronous processing support via queue jobs
- Comprehensive error handling and logging

## 🔄 Migration

The new providers are backward compatible:
- Existing SMS providers continue to work
- New WhatsApp functionality is optional
- Database migration adds new columns safely
- No breaking changes to existing APIs

## 📝 Notes

- **Jusibe**: Nigerian SMS only, requires sender ID approval
- **Bulksmsnigeria**: Nigerian SMS only, has DND filtering
- **Twilio WhatsApp**: Requires WhatsApp Business account setup
- **Meta WhatsApp**: Requires Meta Business account and app approval

## 🆘 Troubleshooting

### Common Issues
1. **Phone Format**: Ensure correct phone number formatting for each provider
2. **API Keys**: Verify all credentials are correct and active
3. **Sender ID**: Some providers require sender ID approval
4. **Rate Limits**: Check provider-specific rate limits

### Debug Commands
```bash
# Check recent communication logs
php artisan tinker --execute="App\Models\CommunicationLog::latest()->limit(10)->get(['type', 'recipient', 'status', 'provider_message_id', 'created_at']);"

# Test specific provider
php artisan sms:test-new-providers "09068719246" "Test" --branch=1
```

## 📚 References

- [Jusibe API Documentation](https://jusibe.com/docs)
- [Twilio WhatsApp API](https://www.twilio.com/docs/whatsapp)
- [Meta WhatsApp Business API](https://developers.facebook.com/docs/whatsapp)
- [Bulksmsnigeria API](https://www.bulksmsnigeria.com/api)


