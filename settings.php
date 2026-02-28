<?php

/**
 * Plugin settings for local_aios.
 *
 * @package    local_aios
 * @copyright  https://github.com/hunanjay
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_aios', get_string('pluginname', 'local_aios'));
    $ADMIN->add('localplugins', $settings);

    // Token IP restriction setting.
    $settings->add(new admin_setting_configtext(
        'local_aios/token_ip',
        get_string('token_ip', 'local_aios'),
        get_string('token_ip_desc', 'local_aios'),
        '',  // Default: no IP restriction
        PARAM_RAW
    ));

    // Token TTL setting.
    $settings->add(new admin_setting_configtext(
        'local_aios/token_ttl',
        get_string('token_ttl', 'local_aios'),
        get_string('token_ttl_desc', 'local_aios'),
        '0',  // Default: permanent token
        PARAM_INT
    ));

    // Service shortname setting.
    $settings->add(new admin_setting_configtext(
        'local_aios/service_shortname',
        get_string('service_shortname', 'local_aios'),
        get_string('service_shortname_desc', 'local_aios'),
        'moodle_mobile_app',  // Default service shortname
        PARAM_ALPHANUMEXT
    ));

    $settings->add(new admin_setting_configtextarea(
        'local_aios/allowed_redirects',
        get_string('allowedredirects', 'local_aios'),
        get_string('allowedredirects_desc', 'local_aios'),
        '',
        PARAM_RAW
    ));
}
