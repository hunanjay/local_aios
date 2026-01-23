# AIOS Plugin Settings Guide

## Overview
Version 0.5.0 introduces configurable settings and automatic token reset functionality.

## New Features

### 1. Plugin Settings
Configure token generation behavior via Moodle admin panel:
**Site Administration > Plugins > Local plugins > AIOS SSO Integration**

#### Available Settings:

| Setting | Description | Default | Example |
|---------|-------------|---------|---------|
| **Token IP Restriction** | IP address or CIDR allowed to use tokens | Empty (no restriction) | `127.0.0.1` or `172.16.0.0/12` |
| **Token TTL (seconds)** | Token validity period | 0 (permanent) | `86400` (24 hours) |
| **Service Shortname** | Web service to use | `moodle_mobile_app` | `local_aios` |

### 2. Automatic Token Reset
Every time a user launches SSO via `/local/aios/launch.php`:
- ✅ Old token is deleted
- ✅ New token is generated with current settings
- ✅ User always has a fresh token

### 3. Manual Token Reset API
New endpoint for testing or administrative use:
```
GET/POST /local/aios/reset_token.php
```

## Configuration Examples

### Example 1: 24-Hour Tokens with IP Restriction
```php
// Site Administration > Plugins > Local plugins > AIOS SSO Integration
Token IP Restriction: 10.0.0.0/8
Token TTL: 86400
Service Shortname: moodle_mobile_app
```

### Example 2: Permanent Tokens, No IP Restriction
```php
Token IP Restriction: (empty)
Token TTL: 0
Service Shortname: local_aios
```

### Example 3: Development Environment (localhost only)
```php
Token IP Restriction: 127.0.0.1
Token TTL: 3600
Service Shortname: moodle_mobile_app
```

## API Usage

### 1. Standard SSO Flow (Automatic Reset)
```
1. User navigates to:
   https://moodle.site.com/local/aios/launch.php?redirect=https://app.site.com/callback

2. Token is automatically reset (old deleted, new generated)

3. User redirected with authorization code:
   https://app.site.com/callback?code=ABC123

4. App backend exchanges code for fresh token:
   POST https://moodle.site.com/local/aios/exchange.php
   Body: {"code": "ABC123"}

5. Response includes new token:
   {"token": "xyz...", "validuntil": 1234567890, ...}
```

### 2. Direct Token Reset (Testing/Admin)
```bash
# Reset token for logged-in user
curl -X POST 'https://moodle.site.com/local/aios/reset_token.php' \
  -H 'Cookie: MoodleSession=...' \
  -H 'Accept: application/json'

# Response:
{
  "success": true,
  "token": "abc123...",
  "userid": 42,
  "username": "student1",
  "service": "moodle_mobile_app",
  "validuntil": 1234567890,
  "settings": {
    "service_shortname": "moodle_mobile_app",
    "token_ttl": 86400,
    "token_ip": "127.0.0.1"
  },
  "message": "Token has been reset successfully"
}
```

## Security Considerations

### Token Reset on Launch
**Benefits:**
- ✅ Users always have tokens with current security settings
- ✅ Old/compromised tokens are automatically invalidated
- ✅ Simplifies token lifecycle management

**Trade-offs:**
- ⚠️ Multiple concurrent launches may cause race conditions
- ⚠️ Old tokens immediately become invalid

### IP Restriction
When configured:
- Tokens can only be used from specified IP addresses
- Useful for restricting to internal networks
- CIDR notation supported: `192.168.0.0/16`

### Token Expiration
- `0` = Permanent tokens (until reset)
- `3600` = 1 hour
- `86400` = 24 hours
- `604800` = 7 days

## Library Functions

### `local_aios_reset_token($userid, $servicename = null)`
Deletes old token and generates new one.

```php
require_once($CFG->dirroot . '/local/aios/lib.php');

// Reset token for current user
$token = local_aios_reset_token($USER->id);

// Reset token for specific service
$token = local_aios_reset_token($USER->id, 'local_aios');
```

### `local_aios_get_or_create_token($userid, $servicename = null)`
Gets existing token or creates new one (doesn't reset).

```php
// Get existing token or create new if none exists
$token = local_aios_get_or_create_token($USER->id);
```

## Upgrade Notes

### From v0.4.0 to v0.5.0
1. New files added:
   - `settings.php` - Plugin configuration page
   - `lib.php` - Core library functions
   - `reset_token.php` - Manual reset endpoint

2. Modified files:
   - `launch.php` - Now uses `local_aios_reset_token()`
   - `lang/en/local_aios.php` - New language strings
   - `version.php` - Updated to v0.5.0

3. Database changes:
   - None (uses existing Moodle tables)

4. After upgrade:
   - Visit **Site Administration > Notifications** to install
   - Configure settings in **Site Administration > Plugins > Local plugins > AIOS**
   - Test token reset functionality

## Troubleshooting

### "Service not available" error
- Ensure web service is enabled in settings
- Check service exists: `Site Administration > Server > Web services > External services`
- Verify service shortname matches plugin setting

### Token reset fails
- Check user has `local/aios:gettoken` capability
- Verify web services are enabled globally
- Check Moodle logs for detailed error messages

### IP restriction not working
- Verify CIDR notation is correct
- Check user's actual IP address in logs
- Remember: empty = no restriction

## Production Checklist

Before deploying to production:

- [ ] Configure appropriate Token TTL for your security requirements
- [ ] Set IP restrictions if needed (e.g., internal network only)
- [ ] Choose correct service shortname (verify service is enabled)
- [ ] Test token reset flow with actual users
- [ ] Monitor token generation events in Moodle logs
- [ ] Document token lifecycle for your team
- [ ] Consider impact of automatic token reset on existing integrations

## Support

For issues or questions:
- GitHub: https://github.com/hunanjay
- Check Moodle logs: `Site Administration > Reports > Logs`
- Enable debugging: `Site Administration > Development > Debugging`
