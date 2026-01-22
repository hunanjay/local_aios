<?php

/**
 * English language strings for local_aios.
 *
 * @package    local_aios
 * @copyright  2026 hunanjay (https://github.com/hunanjay)
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'AIOS SSO Integration';
$string['aios:gettoken'] = 'Generate web service token via SSO';
$string['privacy:metadata'] = 'The AIOS SSO Integration plugin does not store any personal data.';

// Settings.
$string['defaultservice'] = 'Default web service';
$string['defaultservice_desc'] = 'Default web service shortname to use when service parameter is not provided in the launch URL. Default: moodle_mobile_app';
$string['allowedservices'] = 'Allowed web services';
$string['allowedservices_desc'] = 'Comma-separated list of web service shortnames that are allowed to be used with SSO. Leave empty to allow any enabled service. Example: moodle_mobile_app,local_aios';
$string['siteidentity'] = 'Site identity';
$string['siteidentity_desc'] = 'Unique identifier for this Moodle site. Used for passport validation (optional). Leave empty to disable passport validation.';
$string['passportsecret'] = 'Passport secret';
$string['passportsecret_desc'] = 'Shared secret for passport validation (optional). Generate a random string for enhanced security.';

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
$string['servicenotallowed'] = 'The requested web service "{$a}" is not allowed for SSO authentication.';


