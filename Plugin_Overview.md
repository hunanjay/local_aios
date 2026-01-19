## Plugin Overview

**Name:** local_aios(https://github.com/hunanjay/local_aios)
**Purpose:** Enable SSO authentication for AIOS to access Moodle Web Services
**Moodle Version:** 5.0+
**Key Feature:** Secure token generation without database storage

---

## Installation Steps

### 1. Install Plugin
### 2. Enable Web Services
### 3. Set Permissions
```
Users → Permissions → Define roles
→ Edit "Authenticated user" role
→ Allow capability: local/aios:gettoken
```
### 4. Configure OAuth2 (https://moodle.org/plugins/local_oauth2)
```
Site administration → Server → OAuth 2 services
→ Add Microsoft/Google provider
→ Enable OAuth2 authentication
```
---
## SSO Login Flow

```
1. User clicks "Login to Moodle" in AIOS application
   ↓
2. AIOS backend generates Moodle SSO URL with redirect parameter
   → Example: https://moodle.edu/local/aios/launch.php?
               service=moodle_mobile_app&
               redirect=https://aios-backend.com/callback
   ↓
3. User redirects to Moodle
   → If not logged in: Moodle login page appears (OAuth2/password)
   → If already logged in: Proceeds directly
   ↓
4. Moodle authenticates user via OAuth2 (hku.hku)
   ↓
5. Moodle generates Web Service Token (via core API)
   ↓
6. Moodle redirects back to AIOS callback URL with token
   ↓
7. AIOS backend validates token and stores in session
   ↓
8. User can now access Moodle data in AIOS
```

---

## Token Storage & Security

### Where is the token stored?

**On Moodle side:**
- ✅ Token is stored in Moodle database (`mdl_external_tokens` table)
- ✅ Generated using Moodle's core API `generate_token_for_current_user()`
- ✅ Token is permanent (no expiration) but can be regenerated
- ✅ One token per user per service (reuses existing if available)
- ✅ Token managed by Moodle's built-in security mechanisms

**On AIOS side:**
- ✅ Token stored in **backend memory session** 
- ✅ **NOT stored in AIOS database**
- ✅ Session linked to user via **HttpOnly cookie** (invisible to JavaScript)
- ✅ Token **never exposed to browser/frontend**

### Security Features

- OAuth2 authentication (Microsoft/Google/HKU)
- Moodle handles token storage and validation
- HttpOnly cookies prevent XSS attacks
- Capability-based access control (`local/aios:gettoken`)
- Automatic session expiration on AIOS side
- Token can only be used for authorized Web Service functions

---