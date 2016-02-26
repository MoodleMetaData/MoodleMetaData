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

require_once($CFG->dirroot . '/group/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->dirroot . '/enrol/cohort/locallib.php');
require_once($CFG->dirroot . '/group/lib.php');
require_once($CFG->dirroot . '/enrol/uaims/eclass_course_manager.php');

class enrol_uaims_plugin extends enrol_plugin
{

    public function process_imsdoc($imsdoc) {
        $doc = new DOMDocument();
        $doc->loadXML($imsdoc);
        $xpath = new DOMXpath($doc);

        $persons = $xpath->query("/enterprise/person");

        foreach ($persons as $xmlperson) {

            $this->process_person_node($xmlperson, $xpath);
        }

        $groups = $xpath->query("/enterprise/group");

        foreach ($groups as $xmlgroup) {
            $grouptype = $xpath->evaluate("grouptype/typevalue", $xmlgroup)->item(0);
            if ($grouptype->nodeValue == 'Category') {
                $this->process_category_group_node($xmlgroup, $xpath);
            }

        }

        foreach ($groups as $xmlgroup) {
            $grouptype = $xpath->evaluate("grouptype/typevalue", $xmlgroup)->item(0);
            if ($grouptype->nodeValue == 'Course') {
                $this->process_course_group_node($xmlgroup, $xpath);
            }

        }

        foreach ($groups as $xmlgroup) {
            $grouptype = $xpath->evaluate("grouptype/typevalue", $xmlgroup)->item(0);
            if ($grouptype->nodeValue == 'Cohort') {
                $this->process_cohort_group_node($xmlgroup, $xpath);
            }

        }

        foreach ($groups as $xmlgroup) {
            $grouptype = $xpath->evaluate("grouptype/typevalue", $xmlgroup)->item(0);
            if ($grouptype->nodeValue == 'Group') {
                $this->process_group_group_node($xmlgroup, $xpath);
            }

        }

        $memberships = $xpath->query("/enterprise/membership");

        foreach ($memberships as $xmlmembership) {
            $membershiptype = $xpath->evaluate("comments", $xmlmembership)->item(0);
            if ($membershiptype) {
                if ($membershiptype->nodeValue == 'Cohort') {
                    $this->process_cohort_membership_node($xmlmembership, $xpath);
                } else if ($membershiptype->nodeValue == 'CohortSubtractive') {
                    $this->process_cohort_membership_node($xmlmembership, $xpath, 1);
                } else if ($membershiptype->nodeValue == 'CourseCohort') {
                    $this->process_course_cohort_membership_node($xmlmembership, $xpath);
                } else {
                    $this->process_membership_node($xmlmembership, $xpath);
                }
            } else {
                $this->process_membership_node($xmlmembership, $xpath);
            }
        }
    }

    // Process Person Node.
    private function process_person_node($personnode, $xpath) {
        global $DB, $CFG;
        $person = new stdClass();

        $idnumber = $xpath->evaluate("sourcedid/id", $personnode)->item(0);
        if ($idnumber) {
            $person->idnumber = $idnumber->nodeValue;
        }

        $firstname = $xpath->evaluate("name/n/given", $personnode)->item(0);
        if ($firstname) {
            $person->firstname = $firstname->nodeValue;
        }

        $lastname = $xpath->evaluate("name/n/family", $personnode)->item(0);
        if ($lastname) {
            $person->lastname = $lastname->nodeValue;
        }

        $username = $xpath->evaluate("userid", $personnode)->item(0);
        if ($username) {
            $person->username = $username->nodeValue;
        } else {
            $person->username = $person->idnumber;
        }

        $email = $xpath->evaluate("email", $personnode)->item(0);
        if ($email) {
            $person->email = $email->nodeValue;
        }

        $city = $xpath->evaluate("adr/locality", $personnode)->item(0);
        if ($city) {
            $person->city = $city->nodeValue;
        }

        $country = $xpath->evaluate("adr/country", $personnode)->item(0);
        if ($country) {
            $person->country = $country->nodeValue;
        }

        $person->lang = 'manual';
        $person->auth = 'manual';
        $person->confirmed = 1;
        $person->deleted = 0;
        $person->timemodified = time();
        $person->mnethostid = $CFG->mnet_localhost_id;
        $settings = $xpath->evaluate("extension/settings", $personnode);

        foreach ($settings as $setting) {
            $key = $xpath->evaluate("setting", $setting)->item(0)->nodeValue;
            $val = $xpath->evaluate("value", $setting)->item(0)->nodeValue;
            $person->$key = $val;
        }
        if ($personnode->hasAttribute("recstatus")) {
            $recstatus = $personnode->getAttribute("recstatus");
        } else {
            $recstatus = '1';
        }

        if ($recstatus == 3) {
            $DB->set_field('user', 'deleted', 1, array('idnumber' => $person->idnumber));
        } else { // Add or update record.
            // If the user exists (matching sourcedid) then we don't need to do anything.
            if (!$DB->get_field('user', 'id', array('idnumber' => $person->idnumber))) {
                // If they don't exist and haven't a defined username,  we log this as a potential problem.
                if (isset($person->username) && strlen($person->username) > 0) {
                    $id = $DB->insert_record('user', $person);
                } else if ($DB->get_field('user', 'id', array('username' => $person->username))) {
                    // If their idnumber is not registered but their user ID is,  then add their idnumber to their record.
                    $DB->set_field('user', 'idnumber', $person->idnumber, array('username' => $person->username));
                }

            } else {

                $id = $DB->get_field('user', 'id', array('idnumber' => $person->idnumber));
                $euser = $DB->get_records('user', array('idnumber' => $person->idnumber));
                $euser = $this->extend($euser[$id], $person);
                $DB->update_record('user', $euser);
            }
        }
    }

    // End Process Person Node.
    private function process_group_group_node($groupnode, $xpath) {
        global $DB, $CFG;
    }

    private function process_cohort_group_node($groupnode, $xpath) {
        global $DB, $CFG;
        $cohort = new stdClass();

        if ($groupnode->getAttribute("recstatus") != 3) {

            $groupname = $xpath->evaluate("description/short", $groupnode)->item(0);
            if ($groupname) {
                $cohort->name = $groupname->nodeValue;
            }
            $longdesc = $xpath->evaluate("description/long", $groupnode)->item(0);
            if ($longdesc) {
                $cohort->description = $longdesc->nodeValue;
            }

            $idnumber = $xpath->evaluate("sourcedid/id", $groupnode)->item(0);
            if ($idnumber) {
                $cohort->idnumber = htmlspecialchars_decode($idnumber->nodeValue);
            }
            $cohort->descriptionformat = FORMAT_HTML;
            $cohort->component = '';
            $cohort->visible = 0;
            $parentgroup = $xpath->evaluate("relationship/sourcedid/id", $groupnode)->item(0);

            if ($parentgroup) {
                $parentid = $DB->get_field_select('course_categories', 'id', 'description =\''
                    . htmlspecialchars_decode($parentgroup->nodeValue) . '\'');
                if ($parentid) {
                    $parentcontext = $DB->get_field_select('context', 'id', 'contextlevel=40 and instanceid=' . $parentid . '');
                    if ($parentcontext) {
                        $cohort->contextid = $parentcontext;
                    } else {
                        $cohort->contextid = 1;
                    }

                } else {
                    $cohort->contextid = 1;
                }

            } else {
                $cohort->contextid = 1;
            }

            if (!$DB->get_field('cohort', 'id', array('idnumber' => $cohort->idnumber))) {

                $cohort->timecreated = time();
                $cohort->timemodified = $cohort->timecreated;
                $cohort->id = $DB->insert_record('cohort', $cohort);
            } else {

                $id = $DB->get_field('cohort', 'id', array('idnumber' => $cohort->idnumber));
                $cohort->id = $id;
                $DB->update_record('cohort', $cohort);

            }
        } else {
            $idnumber = $xpath->evaluate("sourcedid/id", $groupnode)->item(0);
            if ($idnumber) {
                $cohort->idnumber = htmlspecialchars_decode($idnumber->nodeValue);
            }

            if ($cohortid = $DB->get_field('cohort', 'id', array('idnumber' => $cohort->idnumber))) {
                $DB->delete_records('cohort_members', array('cohortid' => $cohortid));
                $DB->delete_records('cohort', array('id' => $cohortid));
            }

        }

    }

    private function process_category_group_node($groupnode, $xpath) {

        global $DB, $CFG;
        $group = new stdClass();

        if ($groupnode->getAttribute("recstatus") != 3) {
            $groupname = $xpath->evaluate("description/short", $groupnode)->item(0);

            if ($groupname) {
                $group->name = $groupname->nodeValue;
            }

            $groupdescription = $xpath->evaluate("sourcedid/id", $groupnode)->item(0);
            if ($groupdescription) {
                $group->description = htmlspecialchars_decode($groupdescription->nodeValue);
            }

            $parentgroup = $xpath->evaluate("relationship/sourcedid/id", $groupnode)->item(0);

            if ($parentgroup) {
                $parentid = $DB->get_field_select('course_categories', 'id', 'description =\'
                    ' . htmlspecialchars_decode($parentgroup->nodeValue) . '\'');
                if ($parentid) {
                    $group->parent = $parentid;
                }
            } else {
                $group->parent = 0;
            }

            $id = $DB->get_record_select('course_categories', 'description=\'' . $group->description . '\'');

            if (!$id) {
                $group->id = $DB->insert_record('course_categories', $group);
                $classname = context_helper::get_class_for_level(CONTEXT_COURSECAT);
                $group->context = $classname::instance($group->id, IGNORE_MISSING);
                mark_context_dirty($group->context->path);
                $DB->update_record('course_categories', $group);
                fix_course_sortorder();
            }

        } else {

            $groupname = $xpath->evaluate("description/short", $groupnode)->item(0);

            if ($groupname) {
                $group->name = $groupname->nodeValue;
            }

            $groupdescription = $xpath->evaluate("sourcedid/id", $groupnode)->item(0);
            if ($groupdescription) {
                $group->description = htmlspecialchars_decode($groupdescription->nodeValue);
            }

            $parentgroup = $xpath->evaluate("relationship/sourcedid/id", $groupnode)->item(0);

            if ($parentgroup) {
                $parentid = $DB->get_field_select('course_categories', 'id', 'description =\'
                    ' . htmlspecialchars_decode($parentgroup->nodeValue) . '\'');
                if ($parentid) {
                    $group->parent = $parentid;
                }
            } else {
                $group->parent = 0;
            }

            $id = $DB->get_record_select('course_categories', 'description=\'' . $group->description . '\'');
            if ($id) {
                if ($children = $DB->get_records('course_categories', array('parent' => $id->id), 'sortorder ASC')) {
                    echo 'has cats!';
                } else {
                    if ($courses = $DB->get_records('course', array('category' => $id->id), 'sortorder ASC')) {
                        echo 'has courses!';

                    } else {
                        $DB->delete_records('course_categories', array('id' => $id->id));
                        delete_context(CONTEXT_COURSECAT, $id->id);
                    }

                }

            }

        }

    }

    /**
     * Process the course data in the xml
     *
     * @param $groupnode
     * @param $xpath
     * @throws Exception
     * @throws coding_exception
     * @throws dml_missing_record_exception
     */
    private function process_course_group_node($groupnode, $xpath) {
        global $DB, $CFG;
        $course = new stdClass();
        if ($groupnode->getAttribute("recstatus") != 3) {
            $shortdesc = $xpath->evaluate("description/short", $groupnode)->item(0);
            if ($shortdesc) {
                $course->shortname = $shortdesc->nodeValue;
            }

            $longdesc = $xpath->evaluate("description/long", $groupnode)->item(0);
            if ($longdesc) {
                $course->fullname = $longdesc->nodeValue;
            }

            $idnumber = $xpath->evaluate("sourcedid/id", $groupnode)->item(0);
            if ($idnumber) {
                $course->idnumber = htmlspecialchars_decode($idnumber->nodeValue);
            }

            $course->format = 'topics';
            $course->visible = 0;
            $course->timecreated = time();
            $course->startdate = time();
            $course->sortorder = 0;

            $parentgroup = $xpath->evaluate("relationship/sourcedid/id", $groupnode)->item(0);

            if ($parentgroup) {
                $parentid = $DB->get_field_select('course_categories', 'id',
                    'description=\'' . htmlspecialchars_decode($parentgroup->nodeValue) . '\'');
                if ($parentid) {
                    $course->category = $parentid;
                } else {
                    $course->category = 4;
                }
            } else {
                $course->category = 4;
            }

            $settings = $xpath->evaluate("extension/settings", $groupnode);

            foreach ($settings as $setting) {
                $key = $xpath->evaluate("setting", $setting)->item(0)->nodeValue;
                $val = $xpath->evaluate("value", $setting)->item(0)->nodeValue;
                $course->$key = $val;
            }

            if (!$DB->get_field('course', 'id', array('idnumber' => $course->idnumber))) {
                // New Course.
                if (!isset($course->numsections)) {
                    $course->numsections = 10;
                }

                $courseid = $DB->insert_record('course', $course);
                // Setup the blocks.
                $course = $DB->get_record('course', array('id' => $courseid));
                blocks_add_default_course_blocks($course);

                $section = new stdClass();
                $section->course = $course->id;  // Create a default section.
                $section->section = 0;
                $section->summaryformat = FORMAT_HTML;
                $section->id = $DB->insert_record("course_sections", $section);
                $enrol = enrol_get_plugin('manual');
                if ($audroleid = $DB->get_field('role', 'id', array('shortname' => 'auditor'))) {
                    $enrol->add_instance($course, array('roleid' => $audroleid));
                } else {
                    $enrol->add_instance($course);
                }

                $coursemanagement = new stdClass();
                // Get the start and end date for the course.
                $begin = $xpath->evaluate("timeframe/begin", $groupnode)->item(0);
                if ($begin) {
                    $coursemanagement->startdate = $begin->nodeValue;
                }

                $end = $xpath->evaluate("timeframe/end", $groupnode)->item(0);
                if ($end) {
                    $coursemanagement->enddate = $end->nodeValue;
                }

                if (isset($coursemanagement->startdate) && $coursemanagement->startdate > 0 && isset($coursemanagement->enddate) &&
                    $coursemanagement->enddate > 0
                ) {
                    // The course validly has both start and end dates.
                    $coursemanagement->courseid = $course->id;
                    $coursemanagement->timemodified = time();
                    $DB->insert_record("eclass_course_management", $coursemanagement);
                } else if (isset($coursemanagement->startdate) || isset($coursemanagement->enddate)) {
                    // Something isn't right with the start or end date.
                    throw new Exception('UAIMS: Course Creation without valid start or end date');
                } // No else needed. No actions required if the course is validly lacking both start and end dates.
            } else {
                // Update existing Course, if corresponding eclass_course_management exist. Otherwise, create a
                // corresponding eclass_course_management entry.
                $ecourse = $DB->get_record('course', array('idnumber' => $course->idnumber), '*', MUST_EXIST);
                $enableqrtoggle = $this->get_config('enableqrvisibilitytoggle');

                // Disable or enable QR toggling.
                if (!isset($enableqrtoggle) || !$enableqrtoggle) {
                    unset($course->visible);
                } else {
                    // Otherwise update the startend dates in the eclass_course_management table from uaims documents.
                    $coursemanagement = $DB->get_record('eclass_course_management', array('courseid' => $ecourse->id),
                        $fields = 'id',
                        $strictness = IGNORE_MISSING);

                    // Get the start and end date for the course.
                    $begin = $xpath->evaluate("timeframe/begin", $groupnode)->item(0);
                    $end = $xpath->evaluate("timeframe/end", $groupnode)->item(0);

                    // To avoid errors when course is created outside of uaims (doesn't have eclass_course_management entry),
                    // create and set appropriate attributes of $coursemanagement that matches eclass_course_management
                    // database schema (or atleast NOT NULL fields).
                    $coursemanagementexist = $coursemanagement != false;
                    if (!$coursemanagementexist) {
                        $enrol = enrol_get_plugin('manual');
                        if ($audroleid = $DB->get_field('role', 'id', array('shortname' => 'auditor'))) {
                            $enrol->add_instance($ecourse, array('roleid' => $audroleid));
                        } else {
                            $enrol->add_instance($ecourse);
                        }

                        $coursemanagement = new stdClass();

                        // The test process_imsdoc_test::test_process_imsdoc_should_update_course_with_minimal_imsdoc
                        // implies that one of the requirement of this module is to ignore updates/insert in the case where
                        // course exist, yet no start/end date. (Note this differ from the case where the course don't exist
                        // in which case the requirements of this module requires it to throw an exception.
                        if ($begin and $end) {
                            // Set appropriate eclass_course_management row attributes and finally insert it to db.
                            $coursemanagement->courseid = $ecourse->id;
                            $coursemanagement->startdate = $begin->nodeValue;
                            $coursemanagement->enddate = $end->nodeValue;
                            $coursemanagement->timemodified = time();
                            $DB->insert_record("eclass_course_management", $coursemanagement);
                        }
                    } else {
                        // Since corresponding eclass_course_management entry exist, only we don't need all parameters
                        // to be present. Either/both $begin/$end dates is/are sufficient.
                        if ($begin) {
                            $coursemanagement->startdate = $begin->nodeValue;
                        }

                        if ($end) {
                            $coursemanagement->enddate = $end->nodeValue;
                        }

                        if (isset($coursemanagement->startdate) || isset($coursemanagement->enddate)) {
                            // If one of them is there then do the update.
                            if ($coursemanagement->startdate || $coursemanagement->enddate) {
                                $coursemanagement->timemodified = time();
                                $DB->update_record("eclass_course_management", $coursemanagement);
                            }
                        }
                    }
                }

                // The $course var should never have an 'id' attribute, but lets make sure.
                if (isset($course->id)) {
                    unset($course->id);
                }

                $ecourse = $this->extend($ecourse, $course);
                $DB->update_record('course', $ecourse);
                $classname = context_helper::get_class_for_level(CONTEXT_COURSE);
                $context = $classname::instance($ecourse->id, IGNORE_MISSING);
                $classname = context_helper::get_class_for_level(CONTEXT_COURSECAT);
                $newparent = $classname::instance($ecourse->category, IGNORE_MISSING);
                $context->update_moved($newparent);
                if (!$instanceid = $DB->get_field('enrol', 'id', array('enrol' => 'manual', 'courseid' => $ecourse->id))) {
                    $enrol = enrol_get_plugin('manual');
                    if ($audroleid = $DB->get_field('role', 'id', array('shortname' => 'auditor'))) {
                        $enrol->add_instance($ecourse, array('roleid' => $audroleid));
                    } else {
                        $enrol->add_instance($$ecourse);
                    }
                }
            }
        } else {
            $idnumber = $xpath->evaluate("sourcedid/id", $groupnode)->item(0);
            if ($idnumber) {
                $idnumber = htmlspecialchars_decode($idnumber->nodeValue);
                $course = $DB->get_record('course', array('idnumber' => $idnumber));
                delete_course($course, false);
            }
        }
    }

    private function process_cohort_membership_node($membershipnode, $xpath, $subtractive = 0) {
        global $DB, $CFG;
        $members = $xpath->evaluate("member", $membershipnode);

        $idnumber = $xpath->evaluate("sourcedid/id", $membershipnode)->item(0);
        if ($idnumber) {
            $ship = new stdClass();
            $ship->coursecode = $idnumber->nodeValue;
            if ($ship->courseid = $DB->get_field('cohort', 'id', array('idnumber' => $ship->coursecode))) {
                $cohortrec = $DB->get_fieldset_select('cohort_members', 'userid', 'cohortid=' . $ship->courseid);
                $curlist = array_flip($cohortrec);
                foreach ($members as $mmember) {
                    $midnumber = new stdClass();
                    $midnumber = $xpath->evaluate("sourcedid/id", $mmember)->item(0);
                    if ($midnumber) {
                        $member = new stdClass();
                        $member->idnumber = $midnumber->nodeValue;
                        $userid = $DB->get_field('user', 'id', array('idnumber' => $member->idnumber));
                        $latestlist[$userid] = $userid;
                        if (!isset($curlist[$userid])) {
                            if ($userid != 0 && $userid != '') {
                                cohort_add_member($ship->courseid, $userid);
                            }
                        }
                    }
                }

                if ($subtractive == 1) {
                    foreach ($cohortrec as $curmember) {
                        if (!isset($latestlist[$curmember])) {
                            cohort_remove_member($ship->courseid, $curmember);
                        }
                    }

                }
            }
        }
    }

    private function process_course_cohort_membership_node($membershipnode, $xpath, $role = '01') {
        global $DB, $CFG;
        $idnumber = $xpath->evaluate("sourcedid/id", $membershipnode)->item(0);

        $course = $DB->get_record('course', array('idnumber' => $idnumber->nodeValue), '*', MUST_EXIST);

        $members = $xpath->evaluate("member", $membershipnode);
        foreach ($members as $mmember) {
            $midnumber = $xpath->evaluate("sourcedid/id", $mmember)->item(0);
            $idtype = $xpath->evaluate("idtype", $mmember)->item(0);
            if ($midnumber) {
                $member = new stdClass();
                $member->idnumber = $midnumber->nodeValue;
                $cohortid = $DB->get_field('cohort', 'id', array('idnumber' => $member->idnumber));
                $cohortname = $DB->get_field('cohort', 'name', array('idnumber' => $member->idnumber));
                if ($idtype->nodeValue == 'Add' && $cohortid) {
                    if (!$cohortinstanceid = $DB->get_field('enrol', 'id', array('enrol' => 'cohort',
                        'customint1' => $cohortid, 'courseid' => $course->id))
                    ) {
                        $enrol = enrol_get_plugin('cohort');
                        $enrol->add_instance($course, array('name' => $cohortname, 'status' => 0,
                            'customint1' => $cohortid, 'roleid' => 5, 'customint2' => 0));
                        $trace = new null_progress_trace();
                        enrol_cohort_sync($trace, $course->id);
                        $trace->finished();
                    }
                    if (substr($idnumber->nodeValue, 0, 8) == 'UOFAB-XL') {
                        $data = new stdClass();
                        if (!$data->id = $DB->get_field('groups', 'id',
                            array('courseid' => $course->id, 'name' => $cohortname))
                        ) {
                            $data->timecreated = time();
                            $data->timemodified = time();
                            $data->name = trim($cohortname);
                            $data->description = '';
                            $data->descriptionformat = '1';
                            $data->courseid = $course->id;
                            $data->id = groups_create_group($data);
                        }
                        $cohortrec = $DB->get_fieldset_select('cohort_members', 'userid', 'cohortid=' . $cohortid);
                        foreach ($cohortrec as $curmember) {
                            $latestlist[$curmember] = $curmember;
                            groups_add_member($data->id, $curmember);
                        }
                        $grouprec = $DB->get_fieldset_select('groups_members', 'userid', 'groupid=' . $data->id);
                        foreach ($grouprec as $grpmember) {
                            if (!isset($latestlist[$grpmember])) {
                                groups_remove_member($data->id, $grpmember);
                            }
                        }
                    }

                } else if ($idtype->nodeValue == 'Delete') {
                    if ($cohortinstance = $DB->get_record('enrol', array('enrol' => 'cohort',
                        'customint1' => $cohortid, 'courseid' => $course->id))
                    ) {
                        $enrol = enrol_get_plugin('cohort');
                        $enrol->delete_instance($cohortinstance);

                    }
                }
            }
        }
    }

    private function process_membership_node($membershipnode, $xpath) {
        $this->load_role_mappings();
        global $DB, $CFG;
        $idnumber = $xpath->evaluate("sourcedid/id", $membershipnode)->item(0);
        $ship = new stdClass();
        $ship->coursecode = $idnumber->nodeValue;
        $ship->courseid = $DB->get_field('course', 'id', array('idnumber' => $ship->coursecode));
        $courseobj = new stdClass();
        $courseobj->id = $ship->courseid;
        $einstance = $DB->get_record('enrol',
            array('courseid' => $courseobj->id, 'enrol' => $this->get_name()));

        if (empty($einstance)) {
            $enrolid = $this->add_instance($courseobj);
            $einstance = $DB->get_record('enrol', array('id' => $enrolid));
        }
        $members = $xpath->evaluate("member", $membershipnode);

        foreach ($members as $mmember) {
            unset($member);
            unset($memberstoreobj);
            $midnumber = new stdClass();
            $midnumber = $xpath->evaluate("sourcedid/id", $mmember)->item(0);
            if ($midnumber) {
                $member = new stdClass();
                $member->idnumber = $midnumber->nodeValue;
            }
            $userid = $DB->get_field('user', 'id', array('idnumber' => $member->idnumber));
            $mroles = $xpath->evaluate("role", $mmember);

            foreach ($mroles as $role) {
                $recstatus = $role->getAttribute("recstatus");
                $role = $role->getAttribute("roletype");
                $moodleroleid = $this->rolemappings[$role];
                if ($recstatus == "3") {
                    $einstances = $DB->get_records('enrol',
                        array('enrol' => $this->get_name(), 'courseid' => $courseobj->id));
                    foreach ($einstances as $einstance1) {
                        $this->unenrol_user($einstance1, $userid);
                    }
                } else {
                    $this->enrol_user($einstance, $userid, $moodleroleid, 0, 0);
                }
            }
        }
    }

    /**
     * Copies the properties of one object onto the other overwriting if they already exist
     * @param $dest mixed Object to which properties are written.
     * @param $source mixed Object from which properties are sourced.
     * @return mixed
     */
    private function extend($dest, $source) {
        $vars = get_object_vars($source);
        foreach ($vars as $var => $value) {
            $dest->$var = $value;
        }
        return $dest;
    }

    private function load_role_mappings() {
        require_once('locallib.php');
        global $DB;

        $imsroles = new uaims_roles();
        $imsroles = $imsroles->get_imsroles();

        $this->rolemappings = array();
        foreach ($imsroles as $imsrolenum => $imsrolename) {
            $this->rolemappings[$imsrolenum] = $this->rolemappings[$imsrolename] = $this->get_config('imsrolemap' . $imsrolenum);
        }
    }

    private function generate_xml_from_array($array, $nodename) {
        $xml = '';

        if (is_array($array) || is_object($array)) {
            foreach ($array as $key => $value) {
                if (is_numeric($key)) {
                    $key = $nodename;
                }
                $xml .= '<' . $key . '>' . "" . generate_xml_from_array($value, $nodename) . '</' . $key . '>' . "\n";
            }
        } else {
            $xml = htmlspecialchars($array, ENT_QUOTES) . "";
        }
        return $xml;
    }

    private function generate_valid_xml_from_array($array, $nodeblock = 'nodes', $nodename = 'node') {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";

        $xml .= '<' . $nodeblock . '>' . "\n";
        $xml .= generate_xml_from_array($array, $nodename);
        $xml .= '</' . $nodeblock . '>' . "\n";

        return $xml;
    }

    public function cron() {
        global $CFG, $DB;

        $starttime = time();
        mtrace('----------------------------------------------------------------------');
        mtrace('enrol-uaims cron cron process launched at ' . userdate(time()));

        $dbtables = $DB->get_tables(false);
        if (!array_key_exists('eclass_course_management', $dbtables)) {
            mtrace('Automatic course opening/closing disabled. (eclass_course_management table does not exist.)');
        } else {
            $autoopenclose = $this->get_config('enableautocourseopenclose');

            if (isset($autoopenclose) && $autoopenclose) {
                $ecm = new EclassCourseManager($starttime);
                $coursesopened = $ecm->auto_open_courses();
                $courseidstrings = (($coursesopened == false) ? array('none') : array_map('strval', $coursesopened));
                mtrace('Courses auto-opened: ' . implode(',', $courseidstrings));
                $coursesclosed = $ecm->auto_close_courses();
                $courseidstrings = (($coursesclosed == false) ? array('none') : array_map('strval', $coursesclosed));
                mtrace('Courses auto-closed: ' . implode(',', $courseidstrings));
            } else {
                mtrace("Automatic course opening/closing disabled! (enrol_uaims/enableautocourseopenclose = $autoopenclose)");
            }
        }

        $timeelapsed = time() - $starttime;
        mtrace('enrol-uaims cron process has completed. Time taken: ' . $timeelapsed . ' seconds.');
    }

    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param object $instance
     * @return bool
     */
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/uaims:config', $context);
    }

    /**
     * Is it possible to hide/show enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/uaims:config', $context);
    }
}

