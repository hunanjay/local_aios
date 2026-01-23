<?php


/**
 * Cache definitions for local_aios.
 *
 * @package    local_aios
 * @copyright  https://github.com/hunanjay
 */

defined('MOODLE_INTERNAL') || die();

$definitions = [
    'authcodes' => [
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,
        'simpledata' => false,
        'ttl' => 300, // 5 minutes
        'staticacceleration' => false,
    ],
];
