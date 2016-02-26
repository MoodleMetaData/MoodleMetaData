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

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_group_setting.class.php');
require_once($CFG->dirroot.'/blocks/skills_group/tests/skillsgroupunittest.php');

/**
 * This is the unittest class for skills_group_setting.class.php.
 *
 * update_record() new | existing
 * exists()
 * get_feedback_id()
 * get_feedback_name()
 * get_grouping_id()
 * get_grouping_name()
 * get_group_size()
 *
 * @package    block_skills_group
 * @group      block_skills_group_tests
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_skillsgroupsetting extends skills_group_unit_test {

    /**
     * This function tests update_record first, since this is how a record
     * is created. If this test fails, all subsequent results are suspect.
     *
     */
    public function test_update_record() {
        $sgs = new skills_group_setting($this->courseid);
        $settings = $this->get_skills_group_settings();

        // Test new record creation.
        $sgs->update_record($settings);
        $this->check_settings_record($this->courseid, $settings);

        // Now toggle fields.
        $settings->feedbacks = 44;
        $sgs->update_record($settings);
        $this->check_settings_record($this->courseid, $settings);

        $settings = $this->get_skills_group_settings();
        $settings->groupings = 55;
        $sgs->update_record($settings);
        $this->check_settings_record($this->courseid, $settings);

        $settings = $this->get_skills_group_settings();
        $settings->maxsize = 66;
        $sgs->update_record($settings);
        $this->check_settings_record($this->courseid, $settings);

        $settings = $this->get_skills_group_settings($threshold = 500);
        $sgs->update_record($settings);
        $this->check_settings_record($this->courseid, $settings);

        $settings = $this->get_skills_group_settings($threshold = 1, time());
        $sgs->update_record($settings);
        $this->check_settings_record($this->courseid, $settings);

        $settings = $this->get_skills_group_settings($threshold = 1, time(), $allownaming = 0);
        $sgs->update_record($settings);
        $this->check_settings_record($this->courseid, $settings);
    }

    /**
     * This function tests the exists() method.  exists() returns T/F depending
     * on if the settings entry has been created.
     *
     */
    public function tests_exists() {

        $sgs = new skills_group_setting($this->courseid);
        $this->assertFalse($sgs->exists());
        $sgs->update_record($this->get_skills_group_settings());
        $this->assertTrue($sgs->exists());
    }

    /**
     * This function tests to see that the feedback ID gets correctly stored and retrieved
     * in the class.
     *
     */
    public function test_get_feedback_id() {

        $sgs = new skills_group_setting($this->courseid);

        // Test retrieval of feedback ID.
        $sgs->update_record($this->get_skills_group_settings());
        $this->assertEquals($sgs->get_feedback_id(), $this->feedbackid);
    }

    /**
     * This function tests to see that the feedback name gets correctly stored and retrieved
     * in the class.
     *
     */
    public function test_get_feedback_name() {

        $sgs = new skills_group_setting($this->courseid);

        // Test retrieval of feedback name.
        $sgs->update_record($this->get_skills_group_settings());
        $this->assertEquals($sgs->get_feedback_name(), $this->feedbackname);
    }

    /**
     * This function tests to see that the grouping ID gets correctly stored and retrieved
     * in the class.
     *
     */
    public function test_get_grouping_id() {

        $sgs = new skills_group_setting($this->courseid);

        // Test retrieval of grouping ID.
        $sgs->update_record($this->get_skills_group_settings());
        $this->assertEquals($sgs->get_grouping_id(), $this->groupingid);
    }

    /**
     * This function tests to see that the grouping name gets correctly stored and retrieved
     * in the class.
     *
     */
    public function test_get_grouping_name() {

        $sgs = new skills_group_setting($this->courseid);

        // Test new record creation.
        $sgs->update_record($this->get_skills_group_settings());
        $this->assertEquals($sgs->get_grouping_name(), $this->groupingname);
    }

    /**
     * This function tests to see that the max group size gets correctly stored and retrieved
     * in the class.
     *
     */
    public function test_get_group_size() {

        $sgs = new skills_group_setting($this->courseid);

        // Test max group size setting.
        $sgs->update_record($this->get_skills_group_settings());
        $this->assertEquals($sgs->get_group_size(), self::MAXGROUPSIZE);
    }

    /**
     * This function tests to see that the threshold gets correctly stored and retrieved
     * in the class.
     */
    public function test_get_threshold() {
        $sgs = new skills_group_setting($this->courseid);

        // Test threshold setting (defaults to 1).
        $sgs->update_record($this->get_skills_group_settings());
        $this->assertEquals($sgs->get_threshold(), 1);
    }

    /**
     * This function tests to see that the date gets correctly stored and retrieved
     * in the class.
     */
    public function test_get_date() {
        $sgs = new skills_group_setting($this->courseid);
        $date = time();

        // Test retrieval of date.
        $sgs->update_record($this->get_skills_group_settings(1, $date));
        $this->assertEquals($sgs->get_date(), $date);
    }

    /**
     * This function tests the date_restriction() method.  The method returns T/F depending
     * on if a date restriction exists.
     */
    public function test_date_restriction() {
        $sgs = new skills_group_setting($this->courseid);

        $sgs->update_record($this->get_skills_group_settings());
        $this->assertFalse((bool)$sgs->date_restriction());
        $sgs->update_record($this->get_skills_group_settings(1, time()));
        $this->assertTrue((bool)$sgs->date_restriction());
    }

    /**********************************************************************
     * Helper functions are below:
     **********************************************************************/
    /**
     * This function is used to check a series of mail map records.
     *
     * @param int $courseid This is the ID of the course for which we should test
     * @param object $settings Record with all skills_group settings
     */
    private function check_settings_record($courseid, $settings) {
        global $DB;

        $record = $DB->get_record('skills_group_settings', array('courseid' => $courseid));
        $this->assertEquals($record->feedbackid, $settings->feedbacks);
        $this->assertEquals($record->groupingid, $settings->groupings);
        $this->assertEquals($record->maxsize, $settings->maxsize);
        $this->assertEquals($record->threshold, $settings->threshold);
        $this->assertEquals($record->date, $settings->date);
        $this->assertEquals($record->allownaming, $settings->allownaming);
    }
}