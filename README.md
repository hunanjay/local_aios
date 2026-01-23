# Local AIOS - SSO Web Service Token Integration

## Overview
This Moodle local plugin provides SSO integration for web applications using OAuth2-style authorization code flow for secure token exchange.

## Features
- 🔐 OAuth2 authorization code flow (industry standard)
- 🔒 Token never exposed to browser
- ⏱️ Short-lived single-use authorization codes (5 minutes)
- ✅ Compatible with Moodle OAuth2 providers
- 🛡️ Follows Moodle security best practices
- 💾 No token persistence in database (uses Moodle core APIs)
- 🔄 Automatic token reset on each launch (v0.5.0+)
- ⚙️ Configurable token settings: IP restriction, TTL, service (v0.5.0+)

## Installation
1. Copy the `local_aios` directory to `{moodle}/local/`
2. Visit Site Administration > Notifications to install
3. Configure web services and enable the AIOS service
4. Configure plugin settings (optional): Site Administration > Plugins > Local plugins > AIOS SSO Integration

## Requirements
- Moodle 5.0 or higher
- OAuth2 provider configured
- Web services enabled

## Configuration

Navigate to: **Site Administration > Plugins > Local plugins > AIOS SSO Integration**

### Plugin Settings (v0.5.0+)

| Setting | Description | Default |
|---------|-------------|---------|
| **Token IP Restriction** | IP/CIDR allowed to use tokens | No restriction |
| **Token TTL** | Token validity in seconds (0 = permanent) | 0 |
| **Service Shortname** | Web service name for token generation | `moodle_mobile_app` |

**Example Configurations:**
- Production: IP = `10.0.0.0/8`, TTL = `86400` (24 hours)
- Development: IP = `127.0.0.1`, TTL = `3600` (1 hour)
- No restrictions: IP = empty, TTL = `0`

See [SETTINGS_GUIDE.md](SETTINGS_GUIDE.md) for detailed configuration instructions.

## Usage

### Authorization Code Flow (Recommended)

After OAuth2 authentication, the secure token exchange flow:

1. **Launch SSO**: `/local/aios/launch.php?redirect=<callback_url>&service=moodle_mobile_app`
   - User authenticates via OAuth2/password
   - Moodle generates authorization code
   - Redirects to callback with code (5-min expiry, single-use)

2. **Exchange Code**: POST `/local/aios/exchange.php`
   - Application backend exchanges code for token
   - Server-to-server communication only
   - Returns web service token

3. **Use Token**: Store in backend session, never expose to frontend

### Alternative: Direct Web Service Call

- Web service function: `local_aios_get_token` (requires authenticated session)

### Automatic Token Reset (v0.5.0+)

- Every SSO launch automatically resets the user's token
- Old token is deleted, new token generated with current settings
- Ensures users always have fresh tokens

## Security

### Authorization Code Flow Benefits
- ✅ **Token never in browser**: Code is exchanged server-to-server
- ✅ **Single-use codes**: Each code can only be used once
- ✅ **Short-lived**: Codes expire after 5 minutes
- ✅ **OAuth2 standard**: Industry-proven security pattern
- ✅ **Replay attack prevention**: Used codes are immediately invalidated

### Additional Security Features
- Tokens are generated using Moodle core APIs
- Capability checks enforced (`local/aios:gettoken`)
- Requires authenticated user session
- Tokens stored only in Moodle database (core managed)
- HttpOnly cookies on application side
- Event logging for audit trails

