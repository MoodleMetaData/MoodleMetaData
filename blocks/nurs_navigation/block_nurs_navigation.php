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
require_once(dirname(__FILE__) . '/../../config.php');
require_once('edit_navigation_form.class.php');
global $CFG;
require_once($CFG->dirroot.'/blocks/nurs_navigation/locallib.php');
require_once('section_icon.php');

/**
 * Nurs Navigation block class.
 *
 * This is the class definition for the nurs_navigation block.  Most of what follows below is standard
 * Moodle requirements.
 *
 * @package    block_nurs_navigation
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_nurs_navigation extends block_base {

    /** List of section names that should only be visible to users with instructor privileges */
    private $adminsectionnames = array();

    /**
     * This function sets the title of the block.
     *
     */
    public function init() {
        $this->title = get_string('blocktitle', 'block_nurs_navigation');
        $this->adminsectionnames[] = get_string('tutorresources', BNN_LANG_TABLE);
    }

    /**
     * This function tells moodle to process the admin settings.
     *
     */
    public function has_config() {
        return true;
    }

    /**
     * This function restricts the block to only courses and mods, preventing
     * acess to it on the front page.
     *
     */
    public function applicable_formats() {
        return array('course-view' => true,
            'mod' => true,
            'my' => false);
    }

    /**
     * This function draws the block on the screen.  Icons are retrieved from the database and displayed in
     * the block.  The link to edit the page is drawn only if the user has editing rights.
     *
     */
    public function get_content() {
        global $COURSE, $PAGE;

        $PAGE->requires->css('/blocks/nurs_navigation/fade.css');

        if ($this->content !== null) {
            return $this->content;
        }

        $courseid = $COURSE->id;
        $currentsection = $this->get_current_section($courseid);

        $sectionheaders = array();
        $numberofsections = get_section_titles($courseid, $sectionheaders);

        // Note: setting to new class here means that any changes above will be wiped.
        $this->content = new StdClass;
        $this->content->footer = '<ul style="text-align: center; width: 100%; margin: 0; padding: 0; list-style: none;">';

        $fontsize = $this->get_font_size();

        for ($i = 0; $i < $numberofsections; $i++) {
            $this->content->footer .= $this->get_block_icon_link($courseid, $i, $sectionheaders[$i], $currentsection, $fontsize);
        }

        $this->content->footer .= '</ul>';

        $context = context_course::instance($courseid);
        $canmanage = has_capability('block/nurs_navigation:caneditnursnavigation', $context);

        $url = new moodle_url('/course/view.php', array('id' => $COURSE->id, 'section' => 0));
        $this->content->footer .= "<center>".html_writer::link($url, get_string('showallsections', BNN_LANG_TABLE))."</center>";

        if ($canmanage) {
            $url = new moodle_url('/blocks/nurs_navigation/edit_navigation.php',
                                   array('courseid' => $COURSE->id, 'blockid' => $this->instance->id));
            $this->content->footer .= "<center>".html_writer::link($url, get_string('editsettings', BNN_LANG_TABLE))."</center>";
        }

        return $this->content;
    }

    /**
     * This method returns the current section that is displayed by the user
     *
     * @param int $courseid The ID of the course to get the current section of
     * @return int The current section as an integer
     *
     */
    private function get_current_section($courseid) {
        $currentsection = optional_param('section', 0, PARAM_INT);
        if ($currentsection == 0) {
            $format = course_get_format($courseid);
            $currentsection = $format->get_course()->marker;
        }

        return $currentsection;
    }

    /**
     *
     * This function formats and returns a link to the image for a particular section and
     * course.  If a particular section is active, the icons for other sections are faded
     * out.
     *
     */
    private function get_block_icon_link($courseid, $sectionnumber, $sectionheader, $currentsection, $fontsize) {

        if (!$this->verify_visibility($sectionheader)) {
            return;
        }

        $si = new section_icon($courseid, $sectionheader);
        // If icon is set to disable, then skip.
        if ($si->get_icon_disable()) {
            return;
        }

        // Check for custom label text.
        $customlabel = $si->get_custom_label();
        $sectionheader = ($customlabel != null) ? $customlabel : $sectionheader;

        $imagefile = $si->get_image(true);
        $sectionnumberformatted = $sectionnumber + 1;

        // Grab height/width from admin settings.
        $height = get_config('nurs_navigation', 'Image_Height');
        $width = get_config('nurs_navigation', 'Image_Width');

        $outputbuffer = "<li style='display: inline-block; vertical-align: top; width: 100%; margin-bottom: 10px;
                         padding: 0; list-style-type: none;'><span style='font-size: $fontsize;'>";
        $outputbuffer .= "<a title=\"Section: {$sectionheader}
                          \"href='/course/view.php?id={$courseid}&section={$sectionnumberformatted}'>";
        $outputbuffer .= "<img alt=\"$sectionheader\" src='$imagefile' height='$height' width='$width' ";
        if ($currentsection != 0 && $sectionnumberformatted == $currentsection) {
            $outputbuffer .= "class=\"faded\"";
        }
        $outputbuffer .= "/>";
        if (!isset($this->config->disabletext) || (isset($this->config->disabletext) && !$this->config->disabletext)) {
            $outputbuffer .= "<br />$sectionheader";
        }

        $outputbuffer .= "</a></span></li>";

        return $outputbuffer;
    }

    /**
     * This function determines whether a user has permission to view the section.  Nursing uses
     * this to prevent students from seeing some section links that are more administrative in
     * their content.
     *
     * Caution: this does not prevent access to the session, it only hides the icon.
     *
     * @return bool T/F indicating whether the icon should be visible
     */
    private function verify_visibility($sectionheader) {

        global $COURSE;

        // Users with capability can always see the section.
        if (has_capability('block/nurs_navigation:canseeadminsections', context_course::instance($COURSE->id))) {
            return true;
        }

        // User does not have capability.
        if (array_search($sectionheader, $this->adminsectionnames) !== false) {
            return false;
        } else {
            return true;
        }

    }

    /**
     *
     * This function gets the desired font size from the admin setting.  I have put in a
     * default to "small" if the setting is incorrect.
     *
     * @return string HTML code for font size
     *
     */
    private function get_font_size() {
        $fontsizenumber = get_config('nurs_navigation', 'Font_Size');
        switch ($fontsizenumber) {
            case 0:
                $temp = get_string('fontxsmall', BNN_LANG_TABLE);
                break;
            case 1:
                $temp = get_string('fontsmall', BNN_LANG_TABLE);
                break;
            case 2:
                $temp = get_string('fontmedium', BNN_LANG_TABLE);
                break;
            default:
                $temp = get_string('fontsmall', BNN_LANG_TABLE);
        }
        return $temp;
    }

}