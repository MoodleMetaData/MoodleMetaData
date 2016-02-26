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
 * The mod_anonforum discussion created event.
 *
 * @package    mod_anonforum
 * @copyright  2015 Anthony Radziszewski <radzisze@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_anonforum\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_anonforum discussion created event class.
 *
 * @property-read array $other {
 *      Extra information about the event.
 *
 *      - int anonforumid: The id of the forum the discussion is in.
 * }
 *
 * @since      Moodle 2.8
 * @package    mod_anonforum
 * @copyright  2015 Anthony Radziszewski <radzisze@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class discussion_created extends \core\event\base {
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'anonforum_discussions';
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' has created the discussion with id '$this->objectid' in the anonforum " .
            "with course module id '$this->contextinstanceid'.";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventdiscussioncreated', 'mod_anonforum');
    }

    /**
     * Get URL related to the action
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/anonforum/discuss.php', array('d' => $this->objectid));
    }

    /**
     * Return the legacy event log data.
     *
     * @return array|null
     */
    protected function get_legacy_logdata() {

        // The legacy log table expects a relative path to /mod/anonforum/.
        $logurl = substr($this->get_url()->out_as_local_url(), strlen('/mod/anonforum/'));

        return array($this->courseid, 'anonforum', 'add discussion', $logurl, $this->objectid, $this->contextinstanceid);
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['anonforumid'])) {
            throw new \coding_exception('The \'anonforumid\' value must be set in other.');
        }

        if ($this->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }
}
