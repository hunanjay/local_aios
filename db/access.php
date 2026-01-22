<?php

/**
 * Capability definitions for local_aios.
 *
 * @package    local_aios
 * @copyright  2026 hunanjay (https://github.com/hunanjay)
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/aios:gettoken' => [
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'user' => CAP_ALLOW,
        ],
    ],
];
