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
 * PHPUnit data generator tests
 *
 * @package    mod_anonforum
 * @category   phpunit
 * @copyright  2012 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * PHPUnit data generator testcase
 *
 * @package    mod_anonforum
 * @category   phpunit
 * @copyright  2012 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_anonforum_generator_testcase extends advanced_testcase {
    public function test_generator() {
        global $DB;

        $this->resetAfterTest(true);

        $this->assertEquals(0, $DB->count_records('anonforum'));

        $course = $this->getDataGenerator()->create_course();

        /** @var mod_anonforum_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_anonforum');
        $this->assertInstanceOf('mod_anonforum_generator', $generator);
        $this->assertEquals('anonforum', $generator->get_modulename());

        $generator->create_instance(array('course'=>$course->id));
        $generator->create_instance(array('course'=>$course->id));
        $anonforum = $generator->create_instance(array('course'=>$course->id));
        $this->assertEquals(3, $DB->count_records('anonforum'));

        $cm = get_coursemodule_from_instance('anonforum', $anonforum->id);
        $this->assertEquals($anonforum->id, $cm->instance);
        $this->assertEquals('anonforum', $cm->modname);
        $this->assertEquals($course->id, $cm->course);

        $context = context_module::instance($cm->id);
        $this->assertEquals($anonforum->cmid, $context->instanceid);

        // test gradebook integration using low level DB access - DO NOT USE IN PLUGIN CODE!
        $anonforum = $generator->create_instance(array('course'=>$course->id, 'assessed'=>1, 'scale'=>100));
        $gitem = $DB->get_record('grade_items', array('courseid'=>$course->id, 'itemtype'=>'mod', 'itemmodule'=>'anonforum', 'iteminstance'=>$anonforum->id));
        $this->assertNotEmpty($gitem);
        $this->assertEquals(100, $gitem->grademax);
        $this->assertEquals(0, $gitem->grademin);
        $this->assertEquals(GRADE_TYPE_VALUE, $gitem->gradetype);
    }

    /**
     * Test create_discussion.
     */
    public function test_create_discussion() {
        global $DB;

        $this->resetAfterTest(true);

        // User that will create the forum.
        $user = self::getDataGenerator()->create_user();

        // Create course to add the forum to.
        $course = self::getDataGenerator()->create_course();

        // The forum.
        $record = new stdClass();
        $record->course = $course->id;
        $anonforum = self::getDataGenerator()->create_module('anonforum', $record);

        // Add a few discussions.
        $record = array();
        $record['course'] = $course->id;
        $record['anonforum'] = $anonforum->id;
        $record['userid'] = $user->id;
        self::getDataGenerator()->get_plugin_generator('mod_anonforum')->create_discussion($record);
        self::getDataGenerator()->get_plugin_generator('mod_anonforum')->create_discussion($record);
        self::getDataGenerator()->get_plugin_generator('mod_anonforum')->create_discussion($record);

        // Check the discussions were correctly created.
        $this->assertEquals(3, $DB->count_records_select('anonforum_discussions', 'anonforum = :anonforum',
            array('anonforum' => $anonforum->id)));
    }

    /**
     * Test create_post.
     */
    public function test_create_post() {
        global $DB;

        $this->resetAfterTest(true);

        // Create a bunch of users
        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();
        $user3 = self::getDataGenerator()->create_user();
        $user4 = self::getDataGenerator()->create_user();

        // Create course to add the forum.
        $course = self::getDataGenerator()->create_course();

        // The forum.
        $record = new stdClass();
        $record->course = $course->id;
        $anonforum = self::getDataGenerator()->create_module('anonforum', $record);

        // Add a discussion.
        $record->anonforum = $anonforum->id;
        $record->userid = $user1->id;
        $discussion = self::getDataGenerator()->get_plugin_generator('mod_anonforum')->create_discussion($record);

        // Add a bunch of replies, changing the userid.
        $record = new stdClass();
        $record->discussion = $discussion->id;
        $record->userid = $user2->id;
        self::getDataGenerator()->get_plugin_generator('mod_anonforum')->create_post($record);
        $record->userid = $user3->id;
        self::getDataGenerator()->get_plugin_generator('mod_anonforum')->create_post($record);
        $record->userid = $user4->id;
        self::getDataGenerator()->get_plugin_generator('mod_anonforum')->create_post($record);

        // Check the posts were correctly created, remember, when creating a discussion a post
        // is generated as well, so we should have 4 posts, not 3.
        $this->assertEquals(4, $DB->count_records_select('anonforum_posts', 'discussion = :discussion',
            array('discussion' => $discussion->id)));
    }

    public function test_create_content() {
        global $DB;

        $this->resetAfterTest(true);

        // Create a bunch of users
        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();
        $user3 = self::getDataGenerator()->create_user();
        $user4 = self::getDataGenerator()->create_user();

        $this->setAdminUser();

        // Create course and forum.
        $course = self::getDataGenerator()->create_course();
        $anonforum = self::getDataGenerator()->create_module('anonforum', array('course' => $course));

        $generator = self::getDataGenerator()->get_plugin_generator('mod_anonforum');
        // This should create discussion.
        $post1 = $generator->create_content($anonforum);
        // This should create posts in the discussion.
        $post2 = $generator->create_content($anonforum, array('parent' => $post1->id));
        $post3 = $generator->create_content($anonforum, array('discussion' => $post1->discussion));
        // This should create posts answering another post.
        $post4 = $generator->create_content($anonforum, array('parent' => $post2->id));

        $discussionrecords = $DB->get_records('anonforum_discussions', array('anonforum' => $anonforum->id));
        $postrecords = $DB->get_records('anonforum_posts');
        $postrecords2 = $DB->get_records('anonforum_posts', array('discussion' => $post1->discussion));
        $this->assertEquals(1, count($discussionrecords));
        $this->assertEquals(4, count($postrecords));
        $this->assertEquals(4, count($postrecords2));
        $this->assertEquals($post1->id, $discussionrecords[$post1->discussion]->firstpost);
        $this->assertEquals($post1->id, $postrecords[$post2->id]->parent);
        $this->assertEquals($post1->id, $postrecords[$post3->id]->parent);
        $this->assertEquals($post2->id, $postrecords[$post4->id]->parent);
    }
}