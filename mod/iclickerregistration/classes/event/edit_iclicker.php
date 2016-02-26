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

namespace mod_iclickerregistration\event;

/**
 * Class register_iclicker
 * @package mod_iclickerregistration\event
 *
 * Event when user registers an iClicker ID.
 */
class edit_iclicker extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'iclickerregistration_users';
    }

    public static function get_name() {
        return get_string('eventediticlicker', 'mod_iclickerregistration');
    }

    public function get_description() {
        global $DB;
        $iclickerregistrationuser = $DB->get_record($this->objecttable, array('id' => $this->objectid));
        return "The user with id {$this->userid} change iClicker ID to: {$iclickerregistrationuser->iclickerid}.";
    }
}