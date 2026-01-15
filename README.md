# Local AIOS - SSO Web Service Token Integration

## Overview
This Moodle local plugin provides SSO integration for web applications by generating web service tokens after OAuth2 authentication.

## Features
- Secure token generation for authenticated users
- Compatible with Moodle OAuth2 providers
- Follows Moodle security best practices
- No token persistence in database

## Installation
1. Copy the `local_aios` directory to `{moodle}/local/`
2. Visit Site Administration > Notifications to install
3. Configure web services and enable the AIOS service

## Requirements
- Moodle 4.0 or higher
- OAuth2 provider configured
- Web services enabled

## Usage
After OAuth2 authentication, users can obtain web service tokens via:
- Launch endpoint: `/local/aios/launch.php`
- Web service function: `local_aios_get_token`

## Security
- Tokens are generated using Moodle core APIs
- Capability checks enforced
- Requires authenticated user session
- Tokens not stored in database
- Tokens not exposed to frontend JavaScript

## License
GNU GPL v3 or later
