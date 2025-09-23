# Quick Configuration Reference

## 🚀 Jusibe SMS Setup

### 1. Get Credentials from Jusibe.com
- **Public Key**: Found in API Keys section
- **Access Token**: Found in API Keys section  
- **Sender ID**: Your approved sender name (max 11 chars)

### 2. Configure in Church Dashboard
```
SMS Provider: Jusibe
Public Key: pk_live_1234567890abcdef
Access Token: sk_live_1234567890abcdef
Sender ID: YourChurch
```

---

## 🚀 Bulksmsnigeria SMS Setup

### 1. Get Credentials from Bulksmsnigeria.com
- **Username**: Your account username
- **Password**: Your account password
- **Sender ID**: Your sender name

### 2. Configure in Church Dashboard
```
SMS Provider: Bulksmsnigeria
Username: your_username
Password: your_password
Sender ID: YourChurch
```

---

## 🚀 Twilio WhatsApp Setup

### 1. Get Credentials from Twilio.com
- **Account SID**: From Twilio Console
- **Auth Token**: From Twilio Console
- **WhatsApp From Number**: Your WhatsApp number (+1234567890)

### 2. Configure in Church Dashboard
```
WhatsApp Provider: Twilio WhatsApp
Account SID: AC1234567890abcdef
Auth Token: your_auth_token
WhatsApp From Number: +1234567890
```

---

## 🚀 Meta WhatsApp Setup

### 1. Get Credentials from Meta Developers
- **Access Token**: From WhatsApp Business API
- **Phone Number ID**: Your WhatsApp phone number ID
- **Business Account ID**: Optional

### 2. Configure in Church Dashboard
```
WhatsApp Provider: Meta WhatsApp Business
Access Token: EAABwzLixnjYBO...
Phone Number ID: 123456789012345
Business Account ID: 123456789012345 (optional)
```

---

## 📱 Phone Number Formats

| Provider | Input Format | Processed Format |
|----------|-------------|------------------|
| Jusibe | `09068719246` or `+2349068719246` | `09068719246` |
| Bulksmsnigeria | `09068719246` or `+2349068719246` | `09068719246` |
| Twilio WhatsApp | `09068719246` or `+2349068719246` | `+2349068719246` |
| Meta WhatsApp | `09068719246` or `+2349068719246` | `+2349068719246` |

---

## ✅ Testing Checklist

- [ ] Configuration saved successfully
- [ ] "Test Settings" button shows success
- [ ] Quick Send works with test message
- [ ] Message appears in provider dashboard
- [ ] Recipient receives the message

---

## 🔧 Quick Fixes

### "Configuration incomplete" Error
- Check all required fields are filled
- Verify no typos in credentials

### "SMS delivery failed" Error  
- Check sender ID is approved
- Verify account has sufficient balance
- Check phone number format

### "WhatsApp not configured" Error
- Ensure WhatsApp provider is selected
- Verify all required fields are filled
- Check phone number is in international format

---

## 📞 Support Links

- [Jusibe Support](https://jusibe.com/support)
- [Bulksmsnigeria Support](https://www.bulksmsnigeria.com/support)
- [Twilio Support](https://support.twilio.com)
- [Meta WhatsApp Support](https://developers.facebook.com/support)


