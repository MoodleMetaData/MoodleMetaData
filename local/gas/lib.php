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
//
// Author: Behdad Bakhshinategh!

use GAAT\functions as G;

require_once(dirname(__FILE__) . '/lib/functions.php');
require_once(dirname(__FILE__) . '/config.php');

function local_gas_extends_navigation(global_navigation $navigation) {
    global $DB, $USER, $COURSE, $SESSION;

    $prconfig = $DB->count_records('config_plugins', array('plugin' => 'local_gas'));
    if ($prconfig > 0 && G\isuservalid($USER->id)) {
        $node = $navigation->find('local_gas', navigation_node::TYPE_CONTAINER);
        if (!$node) {
            $node = $navigation->add(get_string('pluginname', 'local_gas'), null, navigation_node::TYPE_CONTAINER,
                    get_string('pluginname', 'local_gas'), 'local_gas');
        }

        $node->add(get_string('generalInfo', 'local_gas'), new moodle_url('/local/gas/index.php'), null, null, "generalInfo");

        if (G\isstudent($USER->id)) {
            $node->add(get_string('assessment', 'local_gas'), new moodle_url('/local/gas/student.php'), null, null, "assessment");
        }
        if (G\isteacher($USER->id)) {
            $node->add(get_string('courseAssessment', 'local_gas'), new moodle_url('/local/gas/teacherassessment.php'),
                    null, null, "courseAssessment");
            $node->add(get_string('courseAssessmentReport', 'local_gas'), new moodle_url('/local/gas/coursereport.php'),
                    null, null, "courseAssessmentReport");
        }
        if (has_capability('local/gas:administrator', context_course::instance($COURSE->id))) {
            $node->add(get_string('attributeManagement', 'local_gas'), new moodle_url('/local/gas/attributemanagement.php'),
                    null, null, "attributeManagement");
        }
    }
}
