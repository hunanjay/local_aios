<?php

/**
 * Library functions for local_aios plugin.
 *
 * @package    local_aios
 * @copyright  https://github.com/hunanjay
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Reset user's web service token on each launch.
 * Deletes existing token and generates a new one with configured settings.
 *
 * @param int $userid User ID
 * @param string|null $servicename Service shortname (optional, uses plugin setting if not provided)
 * @return object Token object from database (includes id, token, userid, etc.)
 * @throws moodle_exception
 */
function local_aios_reset_token($userid, $servicename = null) {
    global $DB, $CFG, $USER;
    require_once($CFG->libdir . '/externallib.php');

    // Read plugin settings.
    if ($servicename === null) {
        $servicename = get_config('local_aios', 'service_shortname') ?: 'moodle_mobile_app';
    }
    $allowedip = get_config('local_aios', 'token_ip') ?: '';
    $ttl = (int)get_config('local_aios', 'token_ttl');

    // Validate service exists and is enabled.
    $service = $DB->get_record('external_services', [
        'shortname' => $servicename,
        'enabled' => 1
    ]);

    if (!$service) {
        throw new moodle_exception('servicenotavailable', 'webservice');
    }

    $serviceid = $service->id;

    // Delete existing tokens for this user and service.
    $DB->delete_records('external_tokens', [
        'userid' => $userid,
        'externalserviceid' => $serviceid
    ]);

    // Calculate valid until timestamp.
    $validuntil = 0;
    if ($ttl > 0) {
        $validuntil = time() + $ttl;
    }

    // Check if we're generating token for current user.
    if ($userid == $USER->id) {
        // Use Moodle's core method for current user (most reliable).
        $token = \core_external\util::generate_token_for_current_user($service);

        // Update validuntil and iprestriction if needed.
        if ($validuntil > 0 || !empty($allowedip)) {
            $token->validuntil = $validuntil;
            $token->iprestriction = $allowedip;
            $DB->update_record('external_tokens', $token);
            // Re-fetch to get updated record.
            $token = $DB->get_record('external_tokens', ['id' => $token->id], '*', MUST_EXIST);
        }

        return $token;
    }

    // For other users, use external_generate_token.
    $tokenvalue = external_generate_token(
        EXTERNAL_TOKEN_PERMANENT,
        $serviceid,
        $userid,
        context_system::instance(),
        $validuntil,
        $allowedip
    );

    // Fetch the created token from database.
    $token = $DB->get_record('external_tokens', [
        'userid' => $userid,
        'externalserviceid' => $serviceid
    ], '*', MUST_EXIST);

    return $token;
}

/**
 * Get or create a web service token for a user without resetting.
 * This is a helper function that doesn't delete existing tokens.
 *
 * @param int $userid User ID
 * @param string|null $servicename Service shortname (optional)
 * @return string Token string
 * @throws moodle_exception
 */
function local_aios_get_or_create_token($userid, $servicename = null) {
    global $DB, $CFG;
    require_once($CFG->libdir . '/externallib.php');

    // Read plugin settings.
    if ($servicename === null) {
        $servicename = get_config('local_aios', 'service_shortname') ?: 'moodle_mobile_app';
    }
    $allowedip = get_config('local_aios', 'token_ip') ?: '';
    $ttl = (int)get_config('local_aios', 'token_ttl');

    // Validate service exists and is enabled.
    $service = $DB->get_record('external_services', [
        'shortname' => $servicename,
        'enabled' => 1
    ]);

    if (!$service) {
        throw new moodle_exception('servicenotavailable', 'webservice');
    }

    $serviceid = $service->id;

    // Check if token already exists.
    $existingtoken = $DB->get_record('external_tokens', [
        'userid' => $userid,
        'externalserviceid' => $serviceid
    ]);

    if ($existingtoken) {
        // Check if token is still valid.
        if (empty($existingtoken->validuntil) || $existingtoken->validuntil > time()) {
            return $existingtoken->token;
        } else {
            // Token expired, delete it.
            $DB->delete_records('external_tokens', ['id' => $existingtoken->id]);
        }
    }

    // Generate new token.
    $validuntil = 0;
    if ($ttl > 0) {
        $validuntil = time() + $ttl;
    }

    $tokenobject = external_generate_token(
        EXTERNAL_TOKEN_PERMANENT,
        $serviceid,
        $userid,
        context_system::instance(),
        $validuntil,
        $allowedip
    );

    // Get the complete token record from database.
    $token = $DB->get_record('external_tokens', [
        'token' => $tokenobject->token,
        'userid' => $userid,
        'externalserviceid' => $serviceid
    ], '*', MUST_EXIST);

    return $token->token;
}
