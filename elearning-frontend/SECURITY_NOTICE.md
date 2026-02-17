# ⚠️ SECURITY NOTICE - IMPORTANT!

## Sensitive Data Removed

The following sensitive information has been removed from the source code:

### 1. ClickPesa API Credentials
**Location:** `src/config/app.config.js`
**Status:** ✅ Replaced with placeholders

Your original credentials were:
```
Client ID: ID00rAuLHbQXnBnQTNYsCCOdWiaOrZpf
API Key: SK8CdsVmhK1NbuqU5lo2P4nbCj5hVboFgthpQVjHlw
```

### 2. Domain Name
**Location:** Multiple files
**Status:** ✅ Replaced with auto-detection

## 🔐 How to Use Your Credentials

### Option 1: Local Configuration (Not Recommended for Production)
Edit `src/config/app.config.js` and add your credentials:

```javascript
const CLICKPESA_CONFIG = {
  development: {
    clientId: 'ID00rAuLHbQXnBnQTNYsCCOdWiaOrZpf',
    apiKey: 'SK8CdsVmhK1NbuqU5lo2P4nbCj5hVboFgthpQVjHlw'
  },
  production: {
    clientId: 'YOUR_PRODUCTION_CLIENT_ID',
    apiKey: 'YOUR_PRODUCTION_API_KEY'
  }
}
```

⚠️ **DO NOT COMMIT THIS FILE TO GITHUB** if it contains real credentials!

### Option 2: Backend Proxy (Recommended) ✅
Store credentials in your Laravel backend `.env` file:

```env
CLICKPESA_CLIENT_ID=ID00rAuLHbQXnBnQTNYsCCOdWiaOrZpf
CLICKPESA_API_KEY=SK8CdsVmhK1NbuqU5lo2P4nbCj5hVboFgthpQVjHlw
```

Frontend should call backend API, which then calls ClickPesa. This is more secure!

## 📝 Before Pushing to GitHub

1. ✅ Make sure `app.config.js` has placeholder values only
2. ✅ Add real credentials to backend `.env` (which is gitignored)
3. ✅ Never commit real API keys to version control
4. ✅ Use environment variables for sensitive data

## 🔄 To Restore Your Setup Locally

After pulling from GitHub, edit `src/config/app.config.js` locally and add your credentials.
This file is still tracked by git but with placeholder values.

## 📚 More Info

- Keep credentials in backend `.env` file
- Frontend should only call backend API endpoints
- Backend handles actual ClickPesa communication
- This prevents credential exposure to users
