<?php


/**
 * Token exchange endpoint for AIOS SSO integration.
 *
 * This endpoint exchanges authorization codes for web service tokens.
 * It follows the OAuth2 authorization code flow pattern.
 *
 * Security features:
 * - Codes are single-use only
 * - Codes expire after 5 minutes
 * - Server-to-server communication only (no browser involved)
 * - Token never exposed to browser
 *
 * @package    local_aios
 * @copyright  https://github.com/hunanjay
 */

define('AJAX_SCRIPT', true);
define('NO_MOODLE_COOKIES', true); // This is a stateless API endpoint.

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/classes/code_store.php');

// Set JSON response headers.
header('Content-Type: application/json');

// Only allow POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'error' => 'method_not_allowed',
        'message' => 'Only POST requests are allowed'
    ]);
    exit;
}

// Get POST data (support both form-data and JSON).
$code = optional_param('code', '', PARAM_ALPHANUMEXT);
if (empty($code)) {
    // Try to parse JSON body.
    $json = file_get_contents('php://input');
    if (!empty($json)) {
        $data = json_decode($json, true);
        if (isset($data['code'])) {
            $code = clean_param($data['code'], PARAM_ALPHANUMEXT);
        }
    }
}

// Validate code parameter.
if (empty($code)) {
    http_response_code(400);
    echo json_encode([
        'error' => 'missing_code',
        'message' => 'Authorization code is required'
    ]);
    exit;
}

try {
    // Validate and consume the authorization code.
    $codedata = \local_aios\code_store::validate_and_consume_code($code);

    if ($codedata === false) {
        http_response_code(400);
        echo json_encode([
            'error' => 'invalid_code',
            'message' => 'Invalid, expired, or already used authorization code'
        ]);
        exit;
    }

    // Extract code data.
    $userid = $codedata['userid'];
    $servicename = $codedata['servicename'];
    $tokenid = $codedata['tokenid'];

    // Get the token from database.
    $token = $DB->get_record('external_tokens', [
        'id' => $tokenid,
        'userid' => $userid,
    ]);

    if (!$token) {
        http_response_code(500);
        echo json_encode([
            'error' => 'token_not_found',
            'message' => 'Associated token not found'
        ]);
        exit;
    }

    // Verify token is still valid.
    if (!empty($token->validuntil) && $token->validuntil < time()) {
        http_response_code(400);
        echo json_encode([
            'error' => 'token_expired',
            'message' => 'Token has expired'
        ]);
        exit;
    }

    // Get site info for additional context.
    $siteinfo = [
        'sitename' => $SITE->fullname,
        'siteurl' => $CFG->wwwroot,
        'moodleversion' => $CFG->version,
    ];

    // Return the token and user information.
    http_response_code(200);
    echo json_encode([
        'token' => $token->token,
        'privatetoken' => $token->privatetoken ?? null,
        'userid' => (int)$userid,
        'validuntil' => $token->validuntil ? (int)$token->validuntil : null,
        'site' => $siteinfo,
    ]);

    // Log successful token exchange.
    $logger = get_log_manager();
    if ($logger) {
        $event = \core\event\webservice_token_sent::create([
            'objectid' => $token->id,
            'context' => context_system::instance(),
            'userid' => $userid,
            'other' => [
                'method' => 'code_exchange',
                'service' => $servicename,
            ]
        ]);
        $event->trigger();
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'internal_error',
        'message' => 'Failed to exchange authorization code',
        'debug' => debugging() ? $e->getMessage() : null,
    ]);
    exit;
}
