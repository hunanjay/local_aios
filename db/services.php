<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Web service definitions for local_aios.
 *
 * @package    local_aios
 * @copyright  2026 Your Organization
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
