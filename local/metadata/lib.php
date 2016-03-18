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
        $url = new moodle_url('/local/metadata/insview_general.php', array('id' => $PAGE->course->id));

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
    
    return $DB->get_records_list('learningobjectives', 'id', $wantedIds);
}

function get_course_readings() {
    global $DB;

    $courseId = get_course_id();
    $coursereadings = $DB->get_records('coursereadings', array('courseid'=>$courseId), '', 'id');
    
    $wantedIds = array();
    foreach ($coursereadings as $coursereading) {
        $wantedIds[] = $coursereading->id;
    }
    
    return $DB->get_records_list('coursereadings', 'id', $wantedIds);
}

function get_table_data_for_course($table) {
    global $DB;

    $courseId = get_course_id();
    return $DB->get_records($table, array('courseid'=>$courseId));
}

/**
 * Will return the types of learning objectives
 *   May eventually load them from information for the program
 *
 * @return array containing string of all types
 */
function get_learning_objective_types() {
    return array('Attitude', 'Knowledge', 'Skill');
}

function create_insview_url($form, $courseId) {
    return new moodle_url('/local/metadata/insview_'.$form.'.php', array('id' => $courseId));
}

function create_mange_url($courseId, $anchor=null) {
	if ($anchor) {
		return new moodle_url('/local/metadata/manage_psla_form.php', array('id' => $courseId), 'tab='.$anchor);
	}
	return new moodle_url('/local/metadata/manage_psla_form.php', array('id' => $courseId), $anchor);
}

function get_assessment_type($value){
	$assessmentTypeArray = array();
	$assessmentTypeArray[0] = "Exam";
	$assessmentTypeArray[1] = "Assignment";
	$assessmentTypeArray[2] = "Lab";
	$assessmentTypeArray[3] = "Lab Exam";
	
	return $assessmentTypeArray[$value];
}
?>
