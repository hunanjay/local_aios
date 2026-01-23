<?php


/**
 * Web service definitions for local_aios.
 *
 * @package    local_aios
 * @copyright  https://github.com/hunanjay
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_aios_get_token' => [
        'classname'   => 'local_aios\external\get_token',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Generate web service token for authenticated user',
        'type'        => 'write',
        'ajax'        => false,
        'capabilities' => '',
        'services'    => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
];

$services = [
    'AIOS Web Service' => [
        'functions' => ['local_aios_get_token'],
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'local_aios',
        'downloadfiles' => 0,
        'uploadfiles' => 0,
    ],
];
