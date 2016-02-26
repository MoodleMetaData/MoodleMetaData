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
 * Created by IntelliJ IDEA.
 * User: jandres
 * Date: 02/10/15
 * Time: 2:04 PM
 */
class mod_iclickerregistration_generator_testcase extends advanced_testcase {
    public function test_generator() {
        global $DB;

        $this->resetAfterTest(true);

        $this->assertEquals(0, $DB->count_records('iclickerregistration'));

        $course = $this->getDataGenerator()->create_course();

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_iclickerregistration');
        $this->assertInstanceOf('mod_iclickerregistration_generator', $generator);
        $this->assertEquals('iclickerregistration', $generator->get_modulename());

        $generator->create_instance(array('course' => $course->id));
        $generator->create_instance(array('course' => $course->id));
        $iclickerregistration = $generator->create_instance(array('course' => $course->id));
        $this->assertEquals(3, $DB->count_records('iclickerregistration'));

        $cm = get_coursemodule_from_instance('iclickerregistration', $iclickerregistration->id);
        $this->assertEquals($iclickerregistration->id, $cm->instance);
        $this->assertEquals('iclickerregistration', $cm->modname);
        $this->assertEquals($course->id, $cm->course);

        $context = context_module::instance($cm->id);
        $this->assertEquals($iclickerregistration->cmid, $context->instanceid);
    }
}