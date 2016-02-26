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
 * External Web Service Template
 *
 * @package    localuarchivist
 * @copyright  2011 Moodle Pty Ltd (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/moodle2/backup_plan_builder.class.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot . '/backup/util/ui/import_extensions.php');

class local_uarchivist_external extends external_api
{

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function copy_content_parameters() {
        return new external_function_parameters(
            array('sourcecourse' => new external_value(PARAM_TEXT, 'Source Course ID', VALUE_DEFAULT, ''),
                'destinationcourse' => new external_value(PARAM_TEXT, 'Destination Course ID', VALUE_DEFAULT, ''),
                'excludequestionbank' => new external_value(PARAM_BOOL, 'Exclude Quetion Bank', VALUE_DEFAULT, false))
        );
    }

    public static function restore_course_parameters() {
        return new external_function_parameters(
            array('emptycourseid' => new external_value(PARAM_TEXT, 'Target Course ID', VALUE_DEFAULT, ''),
                'backupfilename' => new external_value(PARAM_TEXT, 'Backup File Name', VALUE_DEFAULT, ''))
        );
    }

    /**
     * Returns welcome message.
     * @param string $sourcecourse
     * @param string $destinationcourse
     * @param bool $excludequestionbank
     * @return string welcome message.
     */
    public static function copy_content($sourcecourse = '', $destinationcourse = '', $excludequestionbank = false) {
        global $DB;
        try {
            $courseid = $destinationcourse;
            $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

            // The id of the course we are importing FROM (will only be set if past first stage.
            $importcourseid = $sourcecourse;
            // The target method for the restore (adding or deleting).
            $restoretarget = backup::TARGET_EXISTING_DELETING;
            $importcourse = $DB->get_record('course', array('id' => $importcourseid), '*', MUST_EXIST);
            $bc = new backup_controller(backup::TYPE_1COURSE, $importcourse->id, backup::FORMAT_MOODLE,
                backup::INTERACTIVE_NO, backup::MODE_IMPORT, 2);
            $bc->get_plan()->get_setting('users')->set_status(backup_setting::LOCKED_BY_CONFIG);
            $settings = $bc->get_plan()->get_settings();

            // If excludequestionbank is set, do not backup question bank.
            if ($excludequestionbank && $bc->get_plan()->setting_exists('questionbank')) {
                $setting = $bc->get_plan()->get_setting('questionbank');
                $setting->set_value(0);
            }

            // For the initial stage we want to hide all locked settings and if there are.
            // No visible settings move to the next stage.
            foreach ($settings as $setting) {
                if ($setting->get_status() !== backup_setting::NOT_LOCKED) {
                    $setting->set_visibility(backup_setting::HIDDEN);
                }
            }

            $backupid = $bc->get_backupid();
            $bc->execute_plan();

            restore_dbops::delete_course_content($course->id);

            $rc = new restore_controller($backupid, $course->id, backup::INTERACTIVE_NO, backup::MODE_GENERAL, 2, $restoretarget);
            $rc->execute_precheck();

            // If excludequestionbank is set, do not restore quizzes.
            if ($excludequestionbank) {
                $settings = $rc->get_plan()->get_settings();
                foreach ($settings as $setting) {
                    if (preg_match('/(quiz)(_)(\\d+)(_)(included)/', $setting->get_name())) {
                        $rc->get_plan()->get_setting($setting->get_name())->set_value(0);
                    }
                }
            }

            $rc->execute_plan();
            $results = $rc->get_results();

            if (isset($results["file_missing_in_backup"])) {
                if (extension_loaded('newrelic')) {
                    newrelic_notice_error($results["file_missing_in_backup"], new Exception('Missing file in backup'));
                }
            }

            $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

            $importcourse->id = $course->id;
            $importcourse->modinfo = $course->modinfo;
            $importcourse->idnumber = $course->idnumber;
            $importcourse->fullname = $course->fullname;
            $importcourse->shortname = $course->shortname;
            $importcourse->startdate = $course->startdate;
            $importcourse->sectioncache = null;
            $sourceformat = $DB->get_field('course', 'format', array('id' => $importcourseid), MUST_EXIST);
            if (!$coursenumsections = $DB->get_field('course_format_options', 'value', array(
                'courseid' => $importcourseid,
                'name' => 'numsections',
                'format' => $sourceformat
            ))
            ) {
                $coursenumsections = 10;
            }
            if ($courseformatconfig = $DB->get_record('course_format_options', array(
                'courseid' => $course->id,
                'name' => 'numsections',
                'format' => $sourceformat
            ))
            ) {
                $courseformatconfig->value = $coursenumsections;
                $DB->update_record('course_format_options', $courseformatconfig);
            } else {
                $courseformatconfig = new stdClass();
                $courseformatconfig->courseid = $course->id;
                $courseformatconfig->name = 'numsections';
                $courseformatconfig->value = $coursenumsections;
                $courseformatconfig->format = $sourceformat;
                $courseformatconfig->sectionid = 0;
                $DB->insert_record('course_format_options', $courseformatconfig);
            }
            $DB->update_record('course', $importcourse);
            return 'success';
        } catch (Exception $e) {
            if (extension_loaded('newrelic')) {
                newrelic_notice_error($e->getMessage(), $e);
            }
            return 'Exception ' . $e->getMessage();
        }
    }

    public static function restore_course($emptycourseid, $backupfilename) {
        global $DB, $CFG;

        try {
            $courseid = $emptycourseid;
            $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
            $restoretarget = 0;
            $fb = get_file_packer();
            $fb->extract_to_pathname("$CFG->dataroot/temp/backup/" . $backupfilename,
                "$CFG->dataroot/temp/backup/" . md5($backupfilename) . "/");
            restore_dbops::delete_course_content($course->id);
            $rc = new restore_controller(md5($backupfilename), $course->id, backup::INTERACTIVE_NO, backup::MODE_GENERAL, 2,
                $restoretarget);
            $rc->execute_precheck();
            $rc->execute_plan();
            return 'success';
        } catch (Exception $e) {
            if (extension_loaded('newrelic')) {
                newrelic_notice_error($e->getMessage() . "$CFG->dataroot/temp/backup/" . $backupfilename, $e);
            }
            return 'Exception ' . $e->getMessage() . "$CFG->dataroot/temp/backup/" . $backupfilename;
        }
    }

    /**
     * Returns description of method result value.
     * @return external_description.
     */
    public static function copy_content_returns() {
        return new external_value(PARAM_TEXT, 'Returns "success"');
    }

    public static function restore_course_returns() {
        return new external_value(PARAM_TEXT, 'Returns "success"');
    }
}