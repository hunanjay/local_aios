# Installation Checklist for local_aios

## Pre-Installation Checklist

### ✅ Required Files Present
- [x] `version.php` - Plugin metadata
- [x] `db/services.php` - Web service definitions
- [x] `db/access.php` - Capability definitions
- [x] `classes/external/get_token.php` - External function implementation
- [x] `lang/en/local_aios.php` - Language strings
- [x] `launch.php` - Launch endpoint (not yet implemented)
- [x] `README.md` - Documentation
- [x] `.gitignore` - Git ignore file

### ✅ Version Configuration
- [x] Component: `local_aios`
- [x] Version: `2026011500`
- [x] Requires: `2024100700` (Moodle 5.0+)
- [x] Maturity: `MATURITY_ALPHA`

### ✅ Security Implementation
- [x] Custom capability `local/aios:gettoken` (not `moodle/webservice:createtoken`)
- [x] Risk level: `RISK_PERSONAL`
- [x] Capability type: `read`
- [x] No private token returned (web app only)
- [x] User can only get token for themselves

### ✅ Web Service Configuration
- [x] Function: `local_aios_get_token`
- [x] Service: `AIOS Web Service` (shortname: `local_aios`)
- [x] AJAX: Disabled (more secure)
- [x] Uses Moodle core token generation API

## Installation Steps

### 1. Upload Plugin to Moodle

**Option A: Via Moodle Admin UI**
1. Zip the `local_aios` directory
2. Go to Site Administration > Plugins > Install plugins
3. Upload the zip file
4. Follow the installation wizard

**Option B: Via File System**
1. Copy the `local_aios` directory to `{moodle}/local/`
2. Navigate to Site Administration > Notifications
3. Click "Upgrade Moodle database now"

### 2. Post-Installation Configuration

#### Enable Web Services
1. Go to Site Administration > Advanced features
2. Check "Enable web services"
3. Save changes

#### Configure OAuth2 (if not already done)
1. Go to Site Administration > Server > OAuth 2 services
2. Configure your OAuth2 provider
3. Enable the OAuth2 authentication plugin

#### Enable the AIOS Service
1. Go to Site Administration > Plugins > Web services > External services
2. Find "AIOS Web Service"
3. Click "Enable"
4. Click "Authorised users" and add users who can use the service (or leave empty for all users)

#### Verify Capability Assignment
1. Go to Site Administration > Users > Permissions > Define roles
2. Check that the "Authenticated user" role has `local/aios:gettoken` capability
3. This should be automatically granted based on `db/access.php`

### 3. Testing

#### Test 1: Check Plugin is Installed
```
Site Administration > Plugins > Plugins overview
Search for: AIOS SSO Integration
Status: Should show as installed
```

#### Test 2: Check Web Service Function
```
Site Administration > Plugins > Web services > API Documentation
Search for: local_aios_get_token
Should appear in the list
```

#### Test 3: Test Token Generation (via API)
```php
// After OAuth authentication
$params = ['servicename' => 'moodle_mobile_app'];
$result = $webservice->call('local_aios_get_token', $params);
// Should return: {"token": "..."}
```

## Known Limitations

### Current Status
- ✅ Web service function `local_aios_get_token` is fully implemented
- ⚠️ Launch endpoint `launch.php` is a skeleton (not yet implemented)

### What Works
- Token generation for authenticated users via web service call
- OAuth2 authentication → Web service token flow
- Security: Users can only get tokens for themselves

### What's Not Implemented Yet
- `launch.php` browser-based SSO launch endpoint
- Redirect to app with token via custom URL scheme
- Passport validation for launch flow

## Troubleshooting

### Error: "servicenotavailable"
**Cause**: The service is not enabled or doesn't exist
**Solution**:
1. Go to Site Administration > Plugins > Web services > External services
2. Enable "AIOS Web Service"

### Error: "enablewsdescription"
**Cause**: Web services are not enabled in Moodle
**Solution**:
1. Go to Site Administration > Advanced features
2. Enable "Enable web services"

### Error: "Required capability not found"
**Cause**: User doesn't have `local/aios:gettoken` capability
**Solution**:
1. Check user's role assignments
2. Verify `local/aios:gettoken` is granted to authenticated users in Define roles

### Error: "usernotfullysetup"
**Cause**: User is not logged in
**Solution**:
1. Ensure user authenticates via OAuth2 first
2. User must have an active Moodle session

## Security Notes

### What This Plugin Does
- Generates web service tokens for authenticated users
- Uses Moodle core token generation API
- Enforces capability checks
- Logs all token generation events

### What This Plugin Does NOT Do
- Does NOT store tokens in custom database tables
- Does NOT expose tokens to frontend JavaScript
- Does NOT allow users to create tokens for other users
- Does NOT bypass Moodle security checks

## Support

For issues or questions:
1. Check IMPLEMENTATION.md for technical details
2. Review Moodle logs: Site Administration > Reports > Logs
3. Check web service logs for debugging

## Next Steps After Installation

1. ✅ Install and verify plugin works
2. ⚠️ Implement `launch.php` for browser-based SSO flow
3. Configure your web app to:
   - Authenticate users via Moodle OAuth2
   - Call `local_aios_get_token` to get web service token
   - Use token for subsequent Moodle API calls

---

**Plugin Version**: 0.1.0 (Alpha)
**Moodle Version**: 5.0.1+
**Last Updated**: 2026-01-15
