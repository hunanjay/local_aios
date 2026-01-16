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
 * Token generated event.
 *
 * @package    local_aios
 * @copyright  2026 Your Organization
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_aios\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Token generated event class.
 *
 * @package    local_aios
 * @copyright  2026 Your Organization
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class token_generated extends \core\event\base {

    /**
     * Initialize the event.
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Return the event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventtokengenerated', 'local_aios');
    }

    /**
     * Return the event description.
     *
     * @return string
     */
    public function get_description() {
        return "User {$this->userid} generated a web service token for service '{$this->other['servicename']}' via AIOS SSO.";
    }

    /**
     * Return the legacy log data.
     *
     * @return array
     */
    protected function get_legacy_logdata() {
        return [$this->courseid, 'local_aios', 'token generated', '', $this->objectid, 0, $this->userid];
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['servicename'])) {
            throw new \coding_exception('The \'servicename\' value must be set in other.');
        }

        if (!isset($this->other['tokenid'])) {
            throw new \coding_exception('The \'tokenid\' value must be set in other.');
        }
    }
}
