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
 * The module forums external functions unit tests
 *
 * @package    local/ws_update_course_category
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/local/ws_update_course_category/externallib.php');

class ws_update_course_category_testcase extends externallib_advanced_testcase {

    public function setUp() {
        // Calling parent is good, always.
        parent::setUp();

        // We always need enabled WS for this testcase.
        set_config('enablewebservices', '1');
    }

    /**
     * Test web service
     */
    public function test_ws_update_course_category() {
        global $USER, $CFG, $DB;

        $this->resetAfterTest(true);

        // Create a user.
        $user = self::getDataGenerator()->create_user();

        $externalserviceid = $DB->get_field('external_services', 'id', array('name' => 'Move Course to Category Service'));

        $_POST['wstoken'] = 'testtoken';
        $externaltoken = new stdClass();
        $externaltoken->token = 'testtoken';
        $externaltoken->tokentype = 0;
        $externaltoken->userid = $USER->id;
        $externaltoken->externalserviceid = $externalserviceid;
        $externaltoken->contextid = 1;
        $externaltoken->creatorid = $USER->id;
        $externaltoken->timecreated = time();
        $DB->insert_record('external_tokens', $externaltoken);

        // Set to the user.
        self::setUser($user);

        // Create courses and categories.
        $course1 = self::getDataGenerator()->create_course();
        $course2 = self::getDataGenerator()->create_course();
        $categoryvisible = self::getDataGenerator()->create_category();
        $categoryhidden = self::getDataGenerator()->create_category();
        $DB->set_field('course_categories', 'visible', '0', array('id' => $categoryhidden->id));
        $this->assertEquals(0, $DB->get_field('course_categories', 'visible', array('id' => $categoryhidden->id)));

        // Give user capability to use webservice.
        $context = context_user::instance($user->id);
        $newrole = create_role('WS Test Role', 'wstestrole', 'WS Unit Tester');
        $roleid = $this->assignUserCapability('local/webservice:local_ws_update_course_category', $context->id, $newrole);

        // Move courses into categories.  Check they get moved and visibility.
        $this->assertEquals(1, local_ws_update_course_category_external::update_course_category($course1->id, $categoryvisible->id));
        $this->assertEquals($categoryvisible->id, $DB->get_field('course', 'category', array('id' => $course1->id)));
        $this->assertEquals(1, $DB->get_field('course', 'visible', array('id' => $course1->id)));

        $this->assertEquals(1, local_ws_update_course_category_external::update_course_category($course1->id, $categoryhidden->id));
        $this->assertEquals($categoryhidden->id, $DB->get_field('course', 'category', array('id' => $course1->id)));
        $this->assertEquals(0, $DB->get_field('course', 'visible', array('id' => $course1->id)));

        // Test moving to non-existent category.
        $this->assertEquals(0, local_ws_update_course_category_external::update_course_category($course1->id, -1));
   }
}
