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
 * Launch endpoint for AIOS SSO integration.
 * Similar to /admin/tool/mobile/launch.php but for web app SSO.
 *
 * @package    local_aios
 * @copyright  2026 Your Organization
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

// TODO: Implement launch endpoint logic
// 1. Check user is logged in (require_login)
// 2. Validate request parameters (service, passport, urlscheme)
// 3. Generate token using external_generate_token_for_current_user()
// 4. Redirect to app with token
