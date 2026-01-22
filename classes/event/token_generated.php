<?php

/**
 * Token generated event.
 *
 * @package    local_aios
 * @copyright  2026 hunanjay (https://github.com/hunanjay)
 */

namespace local_aios\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Token generated event class.
 *
 * @package    local_aios
 * @copyright  2026 hunanjay (https://github.com/hunanjay)
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
