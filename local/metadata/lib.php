<?php
function local_metadata_extends_settings_navigation($settingsnav, $context) {
    global $CFG, $PAGE, $USER;
 
    // Only add this settings item on non-site course pages.
    if (!$PAGE->course or $PAGE->course->id == 1) {
        return;
    }
 
    // TODO: Only let users with the appropriate capability see this settings item.
    //if (!has_capability('local/metadata:ins_view', context_course::instance($PAGE->course->id))) {
    //    return;
    //}
 
    if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {
        $url = new moodle_url('/local/metadata/insview.php', array('id' => $PAGE->course->id));
        // TODO: Should change the name to something more descriptive
        $foonode = navigation_node::create(
            get_string('ins_pluginname', 'local_metadata'),
            $url,
            navigation_node::NODETYPE_LEAF,
            'metadata',
            'metadata',
            new pix_icon('i/report', '')
        );
        if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
            $foonode->make_active();
        }
        $settingnode->add_node($foonode);
    }
}
function get_course_id() {
    return required_param('id', PARAM_INT);
}
function get_course_learning_objectives() {
    global $DB;
    $courseId = get_course_id();
    $courseobjectives = $DB->get_records('courseobjectives', array('courseid'=>$courseId), '', 'objectiveid');
    
    $wantedIds = array();
    foreach ($courseobjectives as $courseobjective) {
        $wantedIds[] = $courseobjective->objectiveid;
    }
    
    return $DB->get_records_list('learningobjectives', 'id', $wantedIds, '', 'id,objectivename');
}
function get_table_data_for_course($table) {
    global $DB;
    $courseId = get_course_id();
    return $DB->get_records($table, array('courseid'=>$courseId));
}
function create_insview_url($courseId, $anchor=null) {
    if ($anchor) {
        return new moodle_url('/local/metadata/insview.php', array('id' => $courseId), 'tab='.$anchor);
    }
    return new moodle_url('/local/metadata/insview.php', array('id' => $courseId), $anchor);
}
?>