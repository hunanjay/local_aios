# Implementation Summary: get_token.php

## Overview
The `classes/external/get_token.php` file has been implemented following Moodle Web Service best practices and the `tool_mobile` token generation pattern.

## Implementation Details

### Key Features Implemented

1. **Parameter Validation**
   - Accepts `servicename` parameter (defaults to 'moodle_mobile_app')
   - Uses Moodle's `validate_parameters()` for input sanitization

2. **Security Checks** (Following Moodle Best Practices)
   - ✅ Validates user is logged in (`isloggedin()`)
   - ✅ Ensures user is not guest (`isguestuser()`)
   - ✅ Checks user account is not suspended (`$USER->suspended`)
   - ✅ Validates system context (`context_system::instance()`)
   - ✅ Verifies web services are enabled (`$CFG->enablewebservices`)
   - ✅ Confirms service exists and is enabled (database query)
   - ✅ Requires capability (`local/aios:gettoken`) - custom capability to prevent users from creating tokens for others

3. **Token Generation**
   - Uses `\core_external\util::generate_token_for_current_user($service)`
   - This is the same function used by `tool_mobile/launch.php`
   - Generates new token OR returns existing valid token
   - Does NOT persist tokens in custom database tables (follows requirements)

4. **Token Security**
   - Only generates tokens for the currently authenticated user
   - Uses custom `local/aios:gettoken` capability (not `moodle/webservice:createtoken`)
   - Prevents users from creating tokens for other users
   - No private token returned (not needed for web apps)

5. **Event Logging**
   - Logs token request using `\core_external\util::log_token_request($token)`
   - Triggers `core\event\webservice_token_sent` event
   - Maintains audit trail for security

6. **Return Structure**
   - Returns web service token only
   - No private token (not needed for web apps)
   - Uses `external_single_structure` for proper typing

## Security Compliance

✅ **Requirement**: Follow Moodle official security model
- Uses Moodle's core APIs exclusively
- All security checks implemented

✅ **Requirement**: Do NOT store Web Service tokens in database
- Uses `\core_external\util::generate_token_for_current_user()`
- Moodle core handles token storage in `mdl_external_tokens` table

✅ **Requirement**: Do NOT expose tokens to frontend JavaScript
- This is a web service function (not AJAX-enabled in services.php)
- Tokens only returned via authenticated web service calls

✅ **Requirement**: Token generation must occur in logged-in session context
- `isloggedin()` check enforced
- `require_capability()` enforced
- Context validation performed

✅ **Requirement**: Use external_generate_token_for_current_user() or equivalent
- Uses `\core_external\util::generate_token_for_current_user()`

✅ **Requirement**: Token must be bound to specific external service
- Service validated against `mdl_external_services` table
- Token bound to service via core API

✅ **Requirement**: Capability checks must be enforced
- Requires custom `local/aios:gettoken` capability
- **Important**: Does NOT use `moodle/webservice:createtoken` to prevent users from creating tokens for others
- Only allows users to get tokens for themselves

## Code Structure

```php
namespace local_aios\external;

class get_token extends external_api {

    // Define input parameters
    public static function execute_parameters()

    // Main execution logic
    public static function execute($servicename)

    // Define return structure
    public static function execute_returns()
}
```

## Usage Example

### Via Web Service Call

```php
// Client code (after OAuth authentication)
$params = [
    'servicename' => 'moodle_mobile_app'
];

$result = $client->call('local_aios_get_token', $params);

// Returns:
// {
//     "token": "abc123def456..."
// }
```

### Prerequisites for Use

1. User must be authenticated (logged in via OAuth or other method)
2. User must have `local/aios:gettoken` capability (granted to all users by default)
3. Web services must be enabled in Moodle
4. The specified service must exist and be enabled
5. User account must not be suspended

## Error Handling

The function throws `moodle_exception` for:
- User not logged in → `usernotfullysetup`
- Guest user → `noguest`
- Suspended account → `accountsuspended`
- Web services disabled → `enablewsdescription`
- Service not found/disabled → `servicenotavailable`
- Missing capability → Standard Moodle capability error

## References

Based on research of:
- `/admin/tool/mobile/launch.php` - Moodle's official mobile app launch endpoint
- `\core_external\util::generate_token_for_current_user()` - Core token generation
- Moodle Web Services API documentation
- External services security best practices

## Sources

- [Web service API functions - MoodleDocs](https://docs.moodle.org/dev/Web_service_API_functions)
- [External services security - MoodleDocs](https://docs.moodle.org/dev/External_services_security)
- [Moodle tool_mobile launch.php source](https://github.com/moodle/moodle/blob/main/admin/tool/mobile/launch.php)
- [Moodle externallib.php source](https://github.com/moodle/moodle/blob/main/lib/externallib.php)

## Next Steps

The `launch.php` endpoint implementation is pending. It will:
1. Handle browser-based SSO flow
2. Accept parameters (service, passport, urlscheme)
3. Validate user session
4. Generate token using this external function
5. Redirect to app with token via custom URL scheme
