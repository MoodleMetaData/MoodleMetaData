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

namespace block_skills_group\event;
defined('MOODLE_INTERNAL') || die();

/**
 * This is the skillsgroup joined event class.  I have largely followed the guide at:
 * https://docs.moodle.org/dev/Migrating_logging_calls_in_plugins
 *
 * @package    block_skills_group
 * @copyright  2015 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class skillsgroup_joined extends \core\event\base {

    /**
     * init() method sets up basic information
     *
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'groups_members';
    }

    /**
     * Returns name of event.
     *
     * @return string Name of event
     *
     */
    public static function get_name() {
        return get_string('skillsgroupjoined', 'block_skills_group');
    }

    /**
     * Returns description of event.
     *
     * @return string Human-readable description of event
     *
     */
    public function get_description() {
        return "User {$this->userid} " . get_string('joingroupinfo', 'block_skills_group') . $this->objectid;
    }

    /**
     * Returns url for event -> unknown usage.
     *
     * @return object URL for event.
     *
     */
    public function get_url() {
        return new \moodle_url('/group/members.php', array('group' => $this->objectid));
    }

    /**
     * Returns legacy log information for legacy log -> mirrors old add_to_log()
     *
     * @return array Array containing old add_to_log() parameters
     *
     */
    public function get_legacy_logdata() {
        $info = "User {$this->userid} " . get_string('joingroupinfo', 'block_skills_group') . $this->objectid;
        $url = new \moodle_url('/group/members.php', array('group' => $this->objectid));
        return array($this->courseid, get_string('pluginname', BLOCK_SG_LANG_TABLE),
                     get_string('skillsgroupjoined', 'block_skills_group'), $url,
                     $info, $this->contextinstanceid);
    }
}
