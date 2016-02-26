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

require_once($CFG->dirroot.'/grade/export/lib.php');
require_once($CFG->libdir . '/csvlib.class.php');

class grade_export_uag extends grade_export {

    public $plugin = 'uag';

    public $separator; // Default separator.
    public $finalitemid = 0;
    /**
     * Constructor should set up all the private variables ready to be pulled
     * @param object $course
     * @param int $groupid id of selected group, 0 means all
     * @param stdClass $formdata The validated data from the grade export form.
     */
    public function __construct($course, $groupid, $formdata) {
        parent::__construct($course, $groupid, $formdata);
        $this->separator = 'comma';
        if (isset($formdata->whattograde)) {
            $this->finalitemid = $formdata->whattograde;
        }

        // Overrides.
        $this->usercustomfields = true;
        $this->displaytype = 3;
    }

    public function get_export_params() {
        $params = parent::get_export_params();
        $params['separator'] = $this->separator;
        return $params;
    }

    public function print_grades() {
        global $CFG;

        $exporttracking = $this->track_exports();
        $course = $this->course;

        $strgrades = get_string('grades');
        $profilefields = grade_helper::get_user_profile_fields($this->course->id, $this->usercustomfields);
        $courseid = $course->id;
        $shortname = format_string($this->course->shortname, true, array('context' => context_course::instance($this->course->id)));
        $downloadfilename = clean_filename("$shortname $strgrades");
        $csvexport = new csv_export_writer($this->separator);
        $csvexport->filename = clean_filename("$downloadfilename.csv");
        // Print names of all the fields.
        $exporttitle = array();
        $shortname = format_string($this->course->shortname, true, array('context' => context_course::instance($this->course->id)));
        $exporttitle[] = $shortname."";
        $csvexport->add_data($exporttitle);
        $exporttitle = array();
        $exporttitle[] = "TERM";
        $exporttitle[] = "Class Number";
        $exporttitle[] = "ID";
        $exporttitle[] = "Final Grade";
        $exporttitle[] = "".get_string("firstname");
        $exporttitle[] = "".get_string("lastname");
        $exporttitle[] = "".get_string("idnumber");
        $exporttitle[] = "".get_string("institution");
        $exporttitle[] = "".get_string("department");
        $exporttitle[] = "".get_string("email");
        foreach ($this->columns as $gradeitem) {
            $exporttitle[] = trim($gradeitem->get_name());
        }

        $csvexport->add_data($exporttitle);
        $sseat = $this->primcomp ($courseid);
        // Print all the lines of data.
        $geub = new grade_export_update_buffer();
        $gui = new graded_users_iterator($this->course, $this->columns, $this->groupid);
        $gui->require_active_enrolment($this->onlyactive);
        $gui->allow_user_custom_fields($this->usercustomfields);
        $gui->init();

        while ($userdata = $gui->next_user()) {
            $exportdata = array();
            $user = $userdata->user;
            if (!isset($sseat[$user->username])) {
                continue;
            }

            foreach ($userdata->grades as $itemid => $grade) {
                if ($this->finalitemid == $itemid) {
                    $finalletter = $this->format_grade($userdata->grades[$itemid]);
                    if ($this->displaytype == 1) {
                        $userdata->grades[$itemid]->finalgrade = $finalletter;
                    }
                }

            }
            $coarr = explode('.', $sseat[$user->username]);
            $exportdata[] = $coarr[0];
            $exportdata[] = $coarr[1];
            $exportdata[] = $user->username;
            $exportdata[] = $finalletter;
            $exportdata[] = $user->firstname;
            $exportdata[] = $user->lastname;
            $exportdata[] = $user->idnumber;
            $exportdata[] = $user->institution;
            $exportdata[] = $user->department;
            $exportdata[] = $user->email;

            foreach ($userdata->grades as $itemid => $grade) {

                $gradestr = $userdata->grades[$itemid]->finalgrade;
                $exportdata[] = $gradestr;

            }
            $csvexport->add_data($exportdata);
        }
        $gui->close();
        $geub->close();
        $csvexport->download_file();
        exit;
    }

    public function primcomp($courseid) {
        global $DB, $CFG;
        $cohortids = $DB->get_fieldset_sql(
            "SELECT mc.idnumber FROM mdl_enrol me,mdl_cohort mc where me.customint1=mc.id and me.courseid=".$courseid);

        $timestamp = time();
        $result = file_get_contents($CFG->uagurl.'?timestamp='.$timestamp.'&MAC='
            .md5(implode(';', $cohortids).$timestamp.$CFG->uaimssecret.'' ).'&ACTION=CLIST&CLIST='.implode(';', $cohortids));

        if (preg_match('{<primclasses>(.*?)</primclasses>}is', $result, $matches)) {
            $result = $matches[1];
        } else {
            echo $result.'Primary component service error!';
            exit;
        }
        foreach (explode(';', $result) as $int => $seat) {
            $epld = explode('.', $seat);
            $sseat[$epld[0]] = $epld[1].'.'.$epld[2];
        }

        return $sseat;
    }



}
