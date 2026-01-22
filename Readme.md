# AIOS SSO Integration Plugin
A Moodle local plugin that implements single sign-on (SSO) between the AIOS system and Moodle.

## Feature Overview
This plugin provides a secure SSO authentication entry point for external applications, supporting both web and mobile app access to Moodle.

### Core Features
1. **SSO Launch Endpoint** (`/local/aios/launch.php`)
   - Verifies user identity (requires prior login to Moodle)
   - Generates a Web Service Token
   - Redirects back to the application carrying the token
2. **Token Generation**
   - Securely generates tokens based on Moodle core API
   - Supports token reuse (same user + same service)
   - Automatically logs token generation events
3. **Security Validation**
   - User identity verification (guest users prohibited)
   - Service availability check
   - Optional Passport verification mechanism (prevents unauthorized requests)

## Usage Instructions

### 1. Install the Plugin
Place the plugin in Moodle's `local/aios/` directory, then visit the Moodle admin page to complete installation.

```bash
cd /path/to/moodle/local/
git clone <repository-url> aios
```

Visit `Site Administration > Notifications` to finish installation.

### 2. Enable Web Services
Enable Web Services in Moodle:
1. `Site Administration > Advanced features`
2. Check "Enable web services"
3. Save changes

### 3. Configure External Service
Ensure the `moodle_mobile_app` service is enabled (or create a custom service):
1. `Site Administration > Server > Web services > External services`
2. Locate or create the service
3. Set `Enabled` to `Yes`

### 4. Configure Plugin Settings
Go to the plugin settings page:
1. `Site Administration > Plugins > Local plugins > AIOS SSO Integration`
2. Configure the following options:

| Setting                  | Description                                                                 | Default Value          |
|--------------------------|-----------------------------------------------------------------------------|------------------------|
| **Default web service**  | Default Web Service short name used                                        | `moodle_mobile_app`    |
| **Allowed web services** | Comma-separated list of allowed Web Services<br>Leave blank to allow all enabled services | `moodle_mobile_app`    |
| **Site identity**        | Site unique identifier (used for Passport verification)                    | Empty (optional)       |
| **Passport secret**      | Passport verification secret key                                            | Empty (optional)       |

**Recommended configuration example**:
```
Default web service: moodle_mobile_app
Allowed web services: moodle_mobile_app,local_aios
```

### 5. Assign Permissions
Assign the required permission to roles that need to use SSO:
1. `Site Administration > Users > Permissions > Define roles`
2. Edit the target role (e.g., Student, Teacher)
3. Search for and enable the `local/aios:gettoken` capability

## SSO Flow

### Web Application Integration
```
1. Application initiates SSO request
   ↓
2. User is redirected to Moodle launch.php
   URL: https://moodle.example.com/local/aios/launch.php?service=moodle_mobile_app&redirect=https://app.example.com/callback
   ↓
3. Moodle verifies user identity (redirects to login page if not logged in)
   ↓
4. Generates Web Service Token
   ↓
5. Redirects back to the application
   URL: https://app.example.com/callback?token=xxx&siteurl=https://moodle.example.com
   ↓
6. Application uses the token to call Moodle Web Service APIs
```

### Parameter Description

| Parameter   | Type   | Required | Description                                                                                          |
|-------------|--------|----------|------------------------------------------------------------------------------------------------------|
| `service`   | string | No       | External Service short name<br>**Default**: "Default web service" from plugin settings<br>**Allowed values**: Any service in "Allowed web services" list |
| `redirect`  | URL    | Yes*     | Callback URL (for web applications)                                                                  |
| `urlscheme` | string | Yes*     | Custom URL Scheme (for mobile applications)                                                          |
| `passport`  | string | No       | Security verification parameter (optional)                                                           |

*Note: Either `redirect` or `urlscheme` is required (one of the two must be provided)

### Returned Parameters
The redirect URL will include the following parameters:
- `token`: Web Service Token (used for API calls)
- `privatetoken`: Private Token (optional, for enhanced security)
- `siteurl`: Moodle site URL

## Example Code

### Frontend Initiating SSO

```javascript
const moodleUrl = 'https://moodle.example.com';
const redirectUrl = 'https://app.example.com/auth/callback';
// Optional: specify service (if omitted, uses the default from plugin settings)
const service = 'moodle_mobile_app';
const ssoUrl = `${moodleUrl}/local/aios/launch.php?service=${service}&redirect=${encodeURIComponent(redirectUrl)}`;
// Or use default service (omit service parameter)
// const ssoUrl = `${moodleUrl}/local/aios/launch.php?redirect=${encodeURIComponent(redirectUrl)}`;
// Redirect user to Moodle SSO
window.location.href = ssoUrl;
```

### Backend Token Verification

```python
import requests

def verify_moodle_token(token, moodle_url):
    response = requests.post(
        f"{moodle_url}/webservice/rest/server.php",
        data={
            "wstoken": token,
            "wsfunction": "core_webservice_get_site_info",
            "moodlewsrestformat": "json"
        }
    )
    if response.status_code == 200:
        result = response.json()
        if "exception" not in result:
            return result  # Token is valid
    return None  # Token is invalid
```

## Security Considerations
1. **HTTPS Required**
   - Production environment must use HTTPS
   - Tokens are transmitted in URL → encryption is essential
2. **Token Protection**
   - Token carries full user privileges
   - Should be received and validated on the backend
   - Do not store token long-term in the frontend
3. **Same-Origin Policy**
   - Callback URLs should be whitelisted
   - Recommended to implement Passport verification mechanism
4. **User Session**
   - Token is bound to user session
   - Clear token when user logs out

## Troubleshooting

### Error: `servicenotavailable`
- **Cause**: Web Service not enabled or does not exist
- **Solution**:
  1. Check if the service is enabled in `Site Administration > Server > Web services > External services`
  2. Confirm the service short name matches the URL parameter or plugin default

### Error: `servicenotallowed`
- **Cause**: Requested Web Service not in allowed list
- **Solution**:
  1. Go to `Site Administration > Plugins > Local plugins > AIOS SSO Integration`
  2. Add the service name to "Allowed web services"
  3. Example: `moodle_mobile_app,local_aios,my_custom_service`

### Error: `tokengenerationfailed`
- **Cause**: Insufficient user permissions or system configuration error
- **Solution**: Ensure the user has the `local/aios:gettoken` capability

### Error: `invalidtoken` (after callback)
- **Cause**: Token validation failed
- **Solution**:
  1. Verify `siteurl` parameter matches
  2. Confirm Web Services are enabled
  3. Check service name is correct

### Token Validation Failure (manual test)

```bash
# Manually test token
curl -X POST "https://moodle.example.com/webservice/rest/server.php" \
  -d "wstoken=YOUR_TOKEN" \
  -d "wsfunction=core_webservice_get_site_info" \
  -d "moodlewsrestformat=json"
```

## Technical Specifications
- **Moodle version**: 5.0+
- **PHP version**: 7.4+
- **Dependencies**: Moodle Web Services core functionality
- **Plugin type**: Local Plugin

## Version History

### v0.3.0 (2026-01-16)
- ✨ **Added**: Admin settings page
- ✨ **Added**: Configurable default Web Service
- ✨ **Added**: Web Service whitelist validation
- 🔒 **Security**: Support for service access control

### v0.2.0 (2026-01-15)
- Initial version
- Supports Web and mobile app SSO
- Implements Passport security verification
- Adds event logging

## Support
For questions or suggestions, please contact: [https://github.com/hunanjay]  
(Repository: https://github.com/hunanjay/local_aios)