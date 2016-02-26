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
 * @package eclass-local-contextadmin
 * @author joshstagg
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/coursecatlib.php');
require_once('categoryroles_form.php');

require_login();

$id = required_param('id', PARAM_INT);

$forum   = optional_param('rolerenaming', 0, PARAM_INT);
$itemid = 0; // Initalise itemid, as all files in category description has item id 0.

$PAGE->set_url('/local/contextadmin/categoryroles.php', array('id' => $id));
$categorycontext = context_coursecat::instance($id);
$PAGE->set_context($categorycontext);

require_capability('moodle/category:manage', $categorycontext);
if (!$category = $DB->get_record('course_categories', array('id' => $id))) {
    print_error('unknowcategory');
}
$strtitle = get_string('roleedit_title', 'local_contextadmin');
$editorcontext = $categorycontext;
$title = $strtitle;
$fullname = $category->name;

$PAGE->set_pagelayout('admin');

$mform = new categoryroles_form('categoryroles.php', array('category' => $category));

if ($mform->is_cancelled()) {
    if ($id) {
        redirect($CFG->wwwroot . '/course/index.php?categoryid=' . $id . '&categoryedit=on');
    } else if ($parent) {
        redirect($CFG->wwwroot .'/course/index.php?categoryid=' . $parent . '&categoryedit=on');
    } else {
        redirect($CFG->wwwroot .'/course/index.php?categoryedit=on');
    }
} else if ($data = $mform->get_data()) {

    $newrolenames = array();

    foreach ($data as $fieldname => $value) {
        if (strpos($fieldname, 'role_') !== 0) {
            continue;
        }
        list($ignored, $roleid) = explode('_', $fieldname);

        $rolename = new stdClass;
        $rolename->catid = $id;
        $rolename->roleid = $roleid;
        $rolename->name = $value;
        $newrolenames[] = $rolename;
    }

    // This is for updating all categories and courses.
    $cats = array(coursecat::get($id));
    $courses = array();

    if ($categories = coursecat::get($id)->get_children()) {
        foreach ($categories as $cat) {
            array_push($cats, $cat);
            $cats = array_merge($cats, coursecat::get($cat->id)->get_children());
        }
    }

    // Update all the category's.
    foreach ($cats as $coursecat) {
        $courses = array_merge($courses, get_courses($coursecat->id));
        foreach ($newrolenames as $role) {
            if (!$role->name) {
                $DB->delete_records('cat_role_names', array('catid' => $coursecat->id, 'roleid' => $role->roleid));
            } else if ($rolename = $DB->get_record('cat_role_names', array('catid' => $coursecat->id, 'roleid' => $role->roleid))) {
                $rolename->name = $role->name;
                $DB->update_record('cat_role_names', $rolename);
            } else {
                $rolename = new stdClass;
                $rolename->catid = $coursecat->id;
                $rolename->roleid = $role->roleid;
                $rolename->name = $role->name;
                $DB->insert_record('cat_role_names', $rolename);
            }
        }
    }
    // Update all courses.
    foreach ($courses as $course) {
        $context = context_course::instance($course->id);

        foreach ($newrolenames as $role) {
            if (!$role->name) {
                $DB->delete_records('role_names', array('contextid' => $context->id, 'roleid' => $role->roleid));
            } else if ($rolename = $DB->get_record('role_names', array('contextid' => $context->id, 'roleid' => $role->roleid))) {
                $rolename->name = $role->name;
                $DB->update_record('role_names', $rolename);
            } else {
                $rolename = new stdClass;
                $rolename->contextid = $context->id;
                $rolename->roleid = $role->roleid;
                $rolename->name = $role->name;
                $DB->insert_record('role_names', $rolename);
            }
        }
    }

    redirect('/course/index.php?categoryid='.$id.'&categoryedit=on');
}

// We need to check parent contexts for any values.

$rolenames = new stdClass();
$aliases = $DB->get_records("cat_role_names", array("catid" => $id));
foreach ($aliases as $alias) {
    $rolenames->{'role_'.$alias->roleid} = $alias->name;
}

$mform->set_data($rolenames);

$PAGE->set_title($title);
$PAGE->set_heading($fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading($strtitle.': '.$fullname);
$mform->display();
echo $OUTPUT->footer();