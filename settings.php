<?php


/**
 * Settings for local_aios plugin.
 *
 * @package    local_aios
 * @copyright  2026 hunanjay (https://github.com/hunanjay)
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_aios', get_string('pluginname', 'local_aios'));

    // Default service shortname setting
    $settings->add(new admin_setting_configtext(
        'local_aios/default_service',
        get_string('defaultservice', 'local_aios'),
        get_string('defaultservice_desc', 'local_aios'),
        'moodle_mobile_app',
        PARAM_ALPHANUMEXT
    ));

    // Allowed services (comma-separated list)
    $settings->add(new admin_setting_configtext(
        'local_aios/allowed_services',
        get_string('allowedservices', 'local_aios'),
        get_string('allowedservices_desc', 'local_aios'),
        'moodle_mobile_app',
        PARAM_TEXT
    ));

    // Site identity for passport validation (optional)
    $settings->add(new admin_setting_configtext(
        'local_aios/site_identity',
        get_string('siteidentity', 'local_aios'),
        get_string('siteidentity_desc', 'local_aios'),
        '',
        PARAM_TEXT
    ));

    // Passport secret for validation (optional)
    $settings->add(new admin_setting_configpasswordunmask(
        'local_aios/passport_secret',
        get_string('passportsecret', 'local_aios'),
        get_string('passportsecret_desc', 'local_aios'),
        ''
    ));

    $ADMIN->add('localplugins', $settings);
}
