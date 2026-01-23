<?php

/**
 * External web service function to generate token for authenticated user.
 *
 * @package    local_aios
 * @copyright  https://github.com/hunanjay
 */

namespace local_aios\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;

/**
 * External function to generate web service token.
 *
 * @package    local_aios
 * @copyright  https://github.com/hunanjay
 */
class get_token extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'servicename' => new external_value(PARAM_ALPHANUMEXT, 'Service name', VALUE_DEFAULT, 'moodle_mobile_app'),
        ]);
    }

    /**
     * Generate token for current authenticated user.
     *
     * @param string $servicename Name of the service
     * @return array Token and related information
     * @throws moodle_exception
     */
    public static function execute($servicename = 'moodle_mobile_app') {
        global $CFG, $USER, $DB;

        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), [
            'servicename' => $servicename,
        ]);
        $servicename = $params['servicename'];

        // Check user is logged in.
        if (!isloggedin()) {
            throw new \moodle_exception('usernotfullysetup', 'moodle');
        }

        // Check user is not guest.
        if (isguestuser()) {
            throw new \moodle_exception('noguest');
        }

        // Ensure the user account is not suspended.
        if ($USER->suspended) {
            throw new \moodle_exception('accountsuspended', 'error');
        }

        // Validate context (system level for web services).
        $context = \context_system::instance();
        self::validate_context($context);

        // Check if web services are enabled.
        if (empty($CFG->enablewebservices)) {
            throw new \moodle_exception('enablewsdescription', 'webservice');
        }

        // Validate the service exists and is enabled.
        $service = $DB->get_record('external_services', ['shortname' => $servicename, 'enabled' => 1]);
        if (empty($service)) {
            throw new \moodle_exception('servicenotavailable', 'webservice');
        }

        // Check if the user has the local_aios specific capability.
        // We use a custom capability instead of moodle/webservice:createtoken
        // to prevent users from creating tokens for other users.
        require_capability('local/aios:gettoken', $context);

        // Generate or retrieve existing token for current user ONLY.
        // This uses Moodle's core API which handles token generation securely.
        $token = \core_external\util::generate_token_for_current_user($service);

        // Log the token request event.
        \core_external\util::log_token_request($token);

        // Return only the web service token.
        // Private token is not needed for web apps.
        return [
            'token' => $token->token,
        ];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'token' => new external_value(PARAM_ALPHANUMEXT, 'Web service token'),
        ]);
    }
}
