<?php

/**
 * Launch endpoint for AIOS SSO integration.
 * Similar to /admin/tool/mobile/launch.php but for web app SSO.
 *
 * This endpoint handles browser-based SSO flow using OAuth2-style authorization code:
 * 1. Validates user is authenticated
 * 2. Generates a web service token
 * 3. Creates a temporary authorization code (5-minute expiry)
 * 4. Redirects to the application with the code (not the token)
 * 5. Application exchanges code for token via /local/aios/exchange.php
 *
 * Security benefits:
 * - Token never exposed to browser
 * - Code is single-use and short-lived
 * - Code exchange requires server-to-server call
 *
 * @package    local_aios
 * @copyright  https://github.com/hunanjay
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/classes/code_store.php');
require_once(__DIR__ . '/lib.php');

// Get URL parameters.
$servicename = optional_param('service', 'moodle_mobile_app', PARAM_ALPHANUMEXT);
$urlscheme = optional_param('urlscheme', '', PARAM_NOTAGS);
$redirecturl = optional_param('redirect', '', PARAM_URL);
$passport = optional_param('passport', '', PARAM_RAW);

// Set up the page.
$PAGE->set_context(context_system::instance());

// IMPORTANT: Set the page URL with all parameters to preserve them during login redirect.
// This ensures that when users are redirected to login, they return here with the same params.
$pageurl = new moodle_url('/local/aios/launch.php', [
    'service' => $servicename,
    'redirect' => $redirecturl,
]);

// Add optional parameters if present.
if (!empty($urlscheme)) {
    $pageurl->param('urlscheme', $urlscheme);
}
if (!empty($passport)) {
    $pageurl->param('passport', $passport);
}

$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('base');

// Require user to be logged in.
// When user is not logged in, Moodle will redirect to login page
// and then return to $PAGE->url (which includes all our parameters).
require_login(null, false);

// Check user is not guest.
if (isguestuser()) {
    throw new moodle_exception('noguest');
}

// Ensure the user account is not suspended.
if ($USER->suspended) {
    throw new moodle_exception('accountsuspended', 'error');
}

// Check if web services are enabled.
if (empty($CFG->enablewebservices)) {
    throw new moodle_exception('enablewsdescription', 'webservice');
}

// Determine redirect target (either urlscheme or redirect URL).
if (empty($urlscheme) && empty($redirecturl)) {
    throw new moodle_exception('missingredirectparameter', 'local_aios');
}

// Validate the service exists and is enabled.
$service = $DB->get_record('external_services', ['shortname' => $servicename, 'enabled' => 1]);
if (empty($service)) {
    throw new moodle_exception('servicenotavailable', 'webservice');
}

// Validate passport if provided (optional security measure).
// The passport can be used to validate the request came from a trusted source.
// This is similar to tool_mobile's passport validation.
if (!empty($passport)) {
    // Get the site identity from config (you should set this in plugin settings).
    $siteidentity = get_config('local_aios', 'site_identity');
    
    if (!empty($siteidentity)) {
        // Validate passport format: base64(siteid:timestamp:hash).
        $passportdata = @json_decode(base64_decode($passport), true);
        
        if (empty($passportdata) || 
            empty($passportdata['siteid']) || 
            empty($passportdata['timestamp']) || 
            empty($passportdata['hash'])) {
            throw new moodle_exception('invalidpassport', 'local_aios');
        }
        
        // Check site ID matches.
        if ($passportdata['siteid'] !== $siteidentity) {
            throw new moodle_exception('invalidpassport', 'local_aios');
        }
        
        // Check timestamp is recent (within 5 minutes).
        if (abs(time() - $passportdata['timestamp']) > 300) {
            throw new moodle_exception('expiredpassport', 'local_aios');
        }
        
        // Verify hash (you should use a shared secret from config).
        $sharedsecret = get_config('local_aios', 'passport_secret');
        $expectedhash = hash('sha256', $passportdata['siteid'] . $passportdata['timestamp'] . $sharedsecret);
        
        if (!hash_equals($expectedhash, $passportdata['hash'])) {
            throw new moodle_exception('invalidpassport', 'local_aios');
        }
    }
}

// Check if the user has permission to get a token.
$context = context_system::instance();
require_capability('local/aios:gettoken', $context);

try {
    // Reset token on each launch (deletes old token and generates new one).
    // This ensures users always have a fresh token with current settings.
    $token = local_aios_reset_token($USER->id, $servicename);

    // Validate token object.
    if (empty($token) || !is_object($token)) {
        throw new moodle_exception('tokengenerationfailed', 'local_aios', '',
            'Token reset returned invalid object');
    }

    if (empty($token->id)) {
        throw new moodle_exception('tokengenerationfailed', 'local_aios', '',
            'Token object missing id field. Object type: ' . gettype($token));
    }

    // Log the token request event.
    \core_external\util::log_token_request($token);

    // Generate a temporary authorization code (OAuth2-style).
    // Code is valid for 5 minutes and can only be used once.
    $code = \local_aios\code_store::generate_code(
        $USER->id,
        $servicename,
        $token->id
    );

    // Trigger custom event for tracking.
    $event = \local_aios\event\token_generated::create([
        'context' => $context,
        'userid' => $USER->id,
        'other' => [
            'servicename' => $servicename,
            'tokenid' => $token->id,
        ],
    ]);
    $event->trigger();

} catch (Exception $e) {
    // Add debug information if debugging is enabled.
    $debuginfo = debugging() ? ' Debug: ' . $e->getTraceAsString() : '';
    throw new moodle_exception('tokengenerationfailed', 'local_aios', '', $e->getMessage() . $debuginfo);
}

// Build redirect URL with authorization code.
// Note: We now use authorization code instead of direct token.
// The code is short-lived (5 min) and single-use, so it's safe to pass via URL.
if (!empty($urlscheme)) {
    // Custom URL scheme for mobile/desktop apps (e.g., myapp://launch).
    $separator = (strpos($urlscheme, '?') !== false) ? '&' : '?';
    $finalurl = $urlscheme . $separator . 'code=' . urlencode($code);
    
    // Add site URL for app to know which Moodle instance this is.
    $finalurl .= '&siteurl=' . urlencode($CFG->wwwroot);
    
} else {
    // HTTP(S) redirect URL for web apps.
    // Pass authorization code via URL parameter.
    // The code is single-use and expires in 5 minutes, so it's safe.
    $redirectbase = new moodle_url($redirecturl);
    $redirectbase->param('code', $code);
    $redirectbase->param('siteurl', $CFG->wwwroot);
    $finalurl = $redirectbase->out(false);
}

// Redirect to the application with authorization code.
// For custom URL schemes, we need to show an intermediate page
// because direct redirect to custom schemes doesn't always work.
if (!empty($urlscheme)) {
    $PAGE->set_title(get_string('launchapp', 'local_aios'));
    $PAGE->set_heading(get_string('launchapp', 'local_aios'));
    
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('launchingapp', 'local_aios'));
    
    // Auto-redirect using JavaScript.
    echo html_writer::tag('p', get_string('launchingappdescription', 'local_aios'));
    echo html_writer::tag('p', html_writer::link($finalurl, get_string('clickheretolaunch', 'local_aios')));
    
    // JavaScript auto-redirect.
    echo html_writer::script("
        setTimeout(function() {
            window.location.href = " . json_encode($finalurl) . ";
        }, 1000);
    ");
    
    // Fallback link.
    echo html_writer::tag('p', 
        html_writer::link(new moodle_url('/'), get_string('returntomoodle', 'local_aios')),
        ['class' => 'text-muted mt-3']
    );
    
    echo $OUTPUT->footer();
    
} else {
    // Web 应用场景：直接重定向，携带授权码（authorization code）。
    // 授权码是一次性的且 5 分钟过期，因此可以安全地通过 URL 传递。
    // 应用后端需要调用 /local/aios/exchange.php 用 code 换取真正的 token。
    
    redirect($finalurl);
}
