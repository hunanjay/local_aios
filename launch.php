<?php
/**
 * Launch endpoint for AIOS SSO integration.
 * Similar to /admin/tool/mobile/launch.php but for web app SSO.
 *
 * This endpoint handles browser-based SSO flow:
 * 1. Validates user is authenticated
 * 2. Generates a web service token
 * 3. Redirects to the application with the token
 *
 * @package    local_aios
 * @copyright  2026 hunanjay (https://github.com/hunanjay)
 */

require_once(__DIR__ . '/../../config.php');

// Get URL parameters.
// Use configured default service if not provided
$defaultservice = get_config('local_aios', 'default_service') ?: 'moodle_mobile_app';
$servicename = optional_param('service', $defaultservice, PARAM_ALPHANUMEXT);
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

// Check if service is in allowed list (if configured)
$allowedservices = get_config('local_aios', 'allowed_services');
if (!empty($allowedservices)) {
    // Parse comma-separated list and trim whitespace
    $allowedlist = array_map('trim', explode(',', $allowedservices));

    if (!in_array($servicename, $allowedlist)) {
        throw new moodle_exception('servicenotallowed', 'local_aios', '', $servicename);
    }
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
    // Generate or retrieve existing token for current user.
    // This uses Moodle's core API which handles token generation securely.
    $token = \core_external\util::generate_token_for_current_user($service);
    
    // Log the token request event.
    \core_external\util::log_token_request($token);
    
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
    throw new moodle_exception('tokengenerationfailed', 'local_aios', '', $e->getMessage());
}

// Build redirect URL with token.
if (!empty($urlscheme)) {
    // Custom URL scheme for mobile/desktop apps (e.g., myapp://launch).
    $separator = (strpos($urlscheme, '?') !== false) ? '&' : '?';
    $finalurl = $urlscheme . $separator . 'token=' . urlencode($token->token) . 
                '&privatetoken=' . urlencode($token->privatetoken ?? '');
    
    // Add site URL for app to know which Moodle instance this is.
    $finalurl .= '&siteurl=' . urlencode($CFG->wwwroot);
    
} else {
    // HTTP(S) redirect URL for web apps.
    $redirectbase = new moodle_url($redirecturl);
    $redirectbase->param('token', $token->token);
    
    // Optionally include private token for enhanced security.
    if (!empty($token->privatetoken)) {
        $redirectbase->param('privatetoken', $token->privatetoken);
    }
    
    // Add site URL for backend to know which Moodle instance this is.
    // This is crucial for validating the token against the correct Moodle instance.
    $redirectbase->param('siteurl', $CFG->wwwroot);
    
    $finalurl = $redirectbase->out(false);
}

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
    // Direct HTTP redirect for web apps.
    redirect($finalurl);
}
