<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * English language strings for local_aios.
 *
 * @package    local_aios
 * @copyright  2026 Your Organization
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'AIOS SSO Integration';
$string['aios:gettoken'] = 'Generate web service token via SSO';
$string['privacy:metadata'] = 'The AIOS SSO Integration plugin does not store any personal data.';

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


