<?php

/**
 * English language strings for local_aios.
 *
 * @package    local_aios
 * @copyright  https://github.com/hunanjay
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'AIOS SSO Integration';
$string['aios:gettoken'] = 'Generate web service token via SSO';
$string['privacy:metadata'] = 'The AIOS SSO Integration plugin does not store any personal data.';

// Settings.
$string['token_ip'] = 'Token IP Restriction';
$string['token_ip_desc'] = 'IP address or CIDR that is allowed to generate tokens, e.g., 127.0.0.1 or 172.16.0.0/12. Leave empty for no IP restriction.';
$string['token_ttl'] = 'Token TTL (seconds)';
$string['token_ttl_desc'] = 'Token validity period in seconds, e.g., 86400 for 24 hours. Set to 0 for permanent tokens.';
$string['service_shortname'] = 'Service Shortname';
$string['service_shortname_desc'] = 'Web Service shortname to use for token generation, e.g., moodle_mobile_app or local_aios.';

// Events.
$string['eventtokengenerated'] = 'Web service token generated';

// Launch endpoint strings.
$string['launchapp'] = 'Launch Application';
$string['launchingapp'] = 'Launching your application...';
$string['launchingappdescription'] = 'You will be redirected to your application in a moment. If the redirect does not happen automatically, please click the link below.';
$string['clickheretolaunch'] = 'Click here to launch the application';
$string['returntomoodle'] = 'Return to Moodle';

// Error messages.
$string['missingredirectparameter'] = 'Missing redirect parameter. Either urlscheme or redirect URL must be provided.';
$string['tokengenerationfailed'] = 'Failed to generate web service token: {$a}';
$string['invalidpassport'] = 'Invalid passport provided. Please try again.';
$string['expiredpassport'] = 'Passport has expired. Please request a new launch URL.';

// Exchange endpoint messages.
$string['invalidcode'] = 'Invalid, expired, or already used authorization code';
$string['missingcode'] = 'Authorization code is required';
$string['tokennotfound'] = 'Associated token not found';
$string['tokenexpired'] = 'Token has expired';

// Reset token messages.
$string['tokenresetsuccessful'] = 'Token has been reset successfully';
$string['tokenresetfailed'] = 'Failed to reset token: {$a}';


