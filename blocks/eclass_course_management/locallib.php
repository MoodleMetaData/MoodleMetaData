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
 * @param {Booelan} $oldvisibility visibility prior to the change.
 * @param {Boolean} $newvisibility visibility after the change.
 * @param {mixed} $record @see eclass_course_management table.
 * @throws coding_exception
 */
function log_visibility_changes($oldvisibility, $newvisibility, $record) {
    global $PAGE;

    $isvisibilitychanged = $oldvisibility !== $newvisibility;

    if ($isvisibilitychanged) {
        $courseclosed = $oldvisibility === 1 && $newvisibility === 0;

        if ($courseclosed) {
            $event = \block_eclass_course_management\event\course_closed::create(array(
                'objectid' => $record->id,
                'context' => $PAGE->context
            ));
            $event->add_record_snapshot('course', $PAGE->course);
            $event->trigger();
        } else {
            // Course opened.
            $event = \block_eclass_course_management\event\course_opened::create(array(
                'objectid' => $record->id,
                'context' => $PAGE->context
            ));
            $event->add_record_snapshot('course', $PAGE->course);
            $event->trigger();
        }
    }
}