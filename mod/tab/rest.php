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
 * REST interface.
 */

require("../../config.php");
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once($CFG->dirroot . '/local/slim/Slim/Slim.php');
require_once($CFG->dirroot . '/lib/resourcelib.php');
require_once($CFG->dirroot . '/lib/completionlib.php');

// BEGIN: COURSE_MODULE_ID PARAMETER EXTRACTION.
$id = optional_param('course_module_id', 0, PARAM_INT); // Course Module ID, or.

if ($id) {
    if (!$cm = get_coursemodule_from_id("tab", $id)) {
        print_error("Course Module ID was incorrect");
    }

    if (!$tab = $DB->get_record("tab", array("id" => $cm->instance))) {
        print_error("Course module is incorrect");
    }

} else {
    error("Course Module ID was not provided");
}

$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
$coursecontext = context_course::instance($course->id);

require_capability('mod/tab:view', $context);
\mod_tab\event\course_module_viewed::create_from_tab($tab, $context)->trigger();

// Update 'viewed' state if required by completion system.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);
// END: COURSE_MODULE_ID PARAMETER EXTRACTION.

// Routing.
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
$appresponse = $app->response();
$app->get('/tab/course_tabs_metadata', 'get_tab_sets_metadata');
$app->get('/tab/metadata/:cmid', 'get_tabs_metadata_with_cmid');
$app->get('/tab/is_tab_menu_enabled', 'is_tab_menu_enabled');
$app->get('/tab/can_edit', 'can_current_user_edit');
$app->get('/tab/:id', 'get_tab_content');  // Place this it doesnt handle 'can_edit' and etc.
$app->run();

/**
 * Sends back true if tab menu is enabled. Otherwise false.
 */
function is_tab_menu_enabled() {
    global $tab, $appresponse;
    $appresponse->header('Content-Type', 'text/plain');
    echo "$tab->displaymenu";
}

/**
 * Acquire metadata for each mod_tab in a course.
 */
function get_tab_sets_metadata() {
    global $DB, $tab, $course, $cm, $appresponse;
    $appresponse->header('Content-Type', 'application/json');

    if (intval($tab->displaymenu) === 1) {
        // Gather all tabsets.
        $tabsets = $DB->get_records_sql('SELECT {course_modules}.id as cmid,{course_modules}.visible as visible, {tab}.name as name,
                                        {tab}.taborder as taborder, {tab}.menuname as menuname
                                     FROM ({modules}
                                     INNER JOIN
                                        {course_modules} ON {modules}.id = {course_modules}.module)
                                     INNER JOIN {tab} ON
                                        {course_modules}.instance = {tab}.id
                                     WHERE ((({modules}.name)=\'tab\') AND
                                        (({course_modules}.course)=' . $course->id . ')) AND
                                        {tab}.menuname=\'' . $tab->menuname . '\'
                                     ORDER BY taborder;');
        $tabsets = array_values($tabsets);
        echo json_encode($tabsets);
    } else {
        // Else return an array containing just one entry (cmid of current course module).
        echo json_encode(array(
            array('cmid' => $cm->id,
                'visible' => $cm->visible,
                'name' => $tab->name,
                'taborder' => $tab->taborder,
                'menuname' => $tab->menuname)
        ));
    }
}

/**
 * Given a cmid, we acquire tab->id, which we can retrieve all corresponding tabs metadata.
 *
 * @param $id tab_content.id @see tab_content table in install.xml
 */
function get_tabs_metadata_with_cmid($cmid) {
    global $DB, $appresponse;
    $appresponse->header('Content-Type', 'application/json');

    $cm = get_coursemodule_from_id("tab", $cmid);
    $tab = $DB->get_record("tab", array("id" => $cm->instance));

    $options = $DB->get_records('tab_content', array('tabid' => $tab->id), 'tabcontentorder', 'id, tabname');
    $options = array_values($options);

    echo json_encode($options);
}

/**
 * @param $id
 * @throws coding_exception
 */
function get_tab_content($id) {
    global $DB, $CFG, $tab, $context, $appresponse;
    $appresponse->header('Content-Type', 'application/json');

    $editoroptions = array('subdirs' => 1, 'maxbytes' => $CFG->maxbytes, 'maxfiles' => -1, 'changeformat' => 1,
        'context' => $context, 'noclean' => 1, 'trusttext' => true);
    $content = $DB->get_record('tab_content', array('id' => $id));

    $externalurl = $content->externalurl;
    $fileattachment = $content->fileattachment;

    // DR: we need to know if there are files associated with [$index]th tab.
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_tab', 'fileattachment', $content->id, false, false);
    $file = reset($files);

    if (!empty($externalurl)) {
        if (!preg_match('{https?:\/\/}', $externalurl)) {
            $externalurl = 'http://' . $externalurl;
        }
    } else if ($file) {
        $filepath = $file->get_filepath();
        $filename = $file->get_filename();
        $fileurlfrewrite = file_rewrite_pluginfile_urls("@@PLUGINFILE@@/" . $file->get_filename(),
            'pluginfile.php', $context->id, 'mod_tab', 'fileattachment', $content->id);
        $externalurl = $fileurlfrewrite;
    } else {
        if (empty($content->format)) {
            $content->format = 1;
        }
        $contenttemp = file_rewrite_pluginfile_urls($content->tabcontent, 'pluginfile.php', $context->id,
            'mod_tab', 'content', $content->id);
        $content = format_text($contenttemp, $content->format, $editoroptions, $context);
    }
    // Enter into proper div.
    if (!empty($externalurl)) {
        $htmlcontent = tab_embed_general(process_urls($externalurl), '',
            get_string('embed_fail_msg', 'tab').
            "<a href='$externalurl' target='_blank' >".
            get_string('embed_fail_link_text', 'tab') . '</a>',
            $file ? ($file->get_mimetype()) : 'text/html');
    } else {
        $htmlcontent = $content;
    }

    echo json_encode(array("content" => base64_encode($htmlcontent)));
}

/**
 * Determines if the client user can edit. If so, the editing button(s) will be displayed.
 * @throws coding_exception
 */
function can_current_user_edit() {
    global $cm, $appresponse;
    $appresponse->header('Content-Type', 'text/plain');

    if (!has_capability('mod/tab:addinstance', context_module::instance($cm->id))) {
        echo "0";
        exit();
    }

    echo "1";
}