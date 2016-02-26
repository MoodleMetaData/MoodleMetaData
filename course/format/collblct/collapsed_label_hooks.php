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
 * This file contains the hooks that are tied into the regular collapsed
 * sections code to try and keep everything clean.
 *
 * @package    format_collblct
 * @category   course/format
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once(dirname(__FILE__).'/../../../config.php');
global $CFG;
require_once('collapsed_menu.class.php');
require_once($CFG->dirroot.'/course/format/collblct/locallib.php');
require_once('course_color_record.class.php');
require_once('course_section_record.class.php');

/**
 * This function is a hook that contains all of the extra initialization code
 * for the collapsed labels.
 *
 */
function init_collapsed_labels() {
    global $PAGE, $COURSE;

    $PAGE->requires->js('/course/format/collblct/js/jquery.nestedAccordion.js');
    $PAGE->requires->js('/course/format/collblct/js/init_accordion.js');
    $PAGE->requires->js('/course/format/collblct/js/setup_nested_rev07252013.js');

    $colorrecord = new course_color_record($COURSE->id);
    $PAGE->requires->js_function_call('color_init', array($colorrecord->get_background_color(),
                                      $colorrecord->get_foreground_color()), false);
}

/**
 * This function draws the link to edit the color/section settings for the course.
 * The link is only added to the page if the user has the proper capability.
 *
 */
function add_edit_color_link(&$controls, $image) {
    global $COURSE;

    $coursecontext = context_course::instance($COURSE->id);
    $canmanage = has_capability('format/collblct:caneditcollapsedlabelcolors', $coursecontext);
    if ($canmanage) {
        $url = new moodle_url('/course/format/collblct/edit_course_settings.php', array('courseid' => $COURSE->id));
        $controls[] = html_writer::link($url, html_writer::empty_tag('img', array('src' => $image,
                        'class' => 'icon ', 'alt' => get_string('editsettingslink', FORMAT_CTWCL_LANG_TABLE))),
                array('title' => get_string('editsettingslink', FORMAT_CTWCL_LANG_TABLE)));

    }
}

/**
 * This function checks the course table for this section and determines whether
 * collapsed labels should be used.
 *
 * @param int $sectionid This is the section number.
 * @returns bool T/F indicating whether the nested menus should be used (T)
 * or should not be used (F).
 *
 */
function check_display($sectionid) {
    global $COURSE;

    $csr = new course_section_record($COURSE->id);
    return $csr->get_section_status($sectionid);
}

/**
 * This function draws the current section and sets up the collapsed labels.
 *
 * @param object $course This is the course object.
 * @param int $displaysection The numerical value of the section
 *
 */
function display_collapsed($course, $displaysection) {
    global $PAGE;

    if (check_display($displaysection) && !$PAGE->user_is_editing()) {
        $cm = new collapsed_menu($course, $displaysection, true);
        $cm->render_menu();
        return $cm;
    }
}

/**
 * This function is responsible for closing the collasped menu.
 *
 * @param object $cm The collapsed menu to close
 * @param int $displaysection This numerical section to close
 *
 */
function close_collapsed($cm, $displaysection) {
    global $PAGE;

    if (check_display($displaysection) && !$PAGE->user_is_editing()) {
        $cm->close_menu();
    }
}