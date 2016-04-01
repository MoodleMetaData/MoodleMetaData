<?php
function local_metadata_extends_settings_navigation($settingsnav, $context) {
    global $CFG, $PAGE, $USER;
 	
    // Setup navigation for Admin metadata
    if (is_null($PAGE->course)) {
    	//return;
    } else  {
    if($categorynode = $settingsnav->find('categorysettings', null)) {
	    $url = new moodle_url('/local/metadata/admview_knowledge.php', array('categoryid' => $PAGE->category->id));
	    	
	    $foonode = navigation_node::create(
	    		get_string('manage_pluginname', 'local_metadata'),
	    		$url,
	    		navigation_node::NODETYPE_LEAF,
	    		'metadata',
	    		'metadata',
	    		new pix_icon('i/report', ''));
	    if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
	    	$foonode->make_active();
	    }
	    $categorynode->add_node($foonode);
    	//$categorynode->add(get_string('manage_pluginname', 'local_metadata'), $url, self::TYPE_SETTING, null, 'permissions', new pix_icon('i/permissions', ''));
	}
    }
    
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

/**
 * Will return the paramater for course id
 *
 * @return int course id
 */
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

/**
 * Will return the days
 *
 * @return array containing string of all days
 */
function get_days() {
    return array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
}

function create_insview_url($form, $courseId) {
    return new moodle_url('/local/metadata/insview_'.$form.'.php', array('id' => $courseId));
}

function create_manage_url($form, $categoryId) {
	return new moodle_url('/local/metadata/admview_'.$form.'.php', array('categoryid' => $categoryId));
}

function get_teaching_strategies() {
    return array('Direct Lecture', 'Active Learning', 'Problem Based Learning', 'Team Based Learning', 'Blended Learning', 'Other');
}

/**
 * Will return all of the type options
 *
 * @return array containing string of all types
 */
function get_session_types() {
    return array('lecture', 'lab', 'seminar');
}

/**
 * Will return all of the length options
 *
 * @return array containing string of all types
 */
function get_session_lengths() {
    return array('50 minutes', '80 minutes', '110 minutes', '140 minutes', '170 minutes');
}

function get_assessment_types() {
    return array('Exam', 'Assignment', 'Participation', 'Other');
}

function get_exam_types() {
    return array('Multiple choice', 'Written', 'Written and multiple choice', 'Other');
}

/**
 * Will return the paramater for objective id
 * 
 * @return int objective id
 */
function get_objective_id() {
	return optional_param('obj', -1, PARAM_INT);
}

/**
 * Will return the paramater for group id
 *
 * @return int group id
 */
function get_group_id() {
	return optional_param('grp', 1, PARAM_INT);
}

/**
 * Will return the paramater for program id
 *
 * @return int program id
 */
function get_program_id() {
	return required_param('program', PARAM_INT);
}

/**
 * Redirects the page to the new added element in repeating element.
 * Will be used while "add button" is clicked.
 * @param string $anchorname 	the name of an anchor
 * @param string $nextelement	the id of the next element in the form
 * @param integer $Y			the offset in pixels to scroll vertically	
 * @return void
 */
function redirect_to_anchor($anchorname, $nextelement, $Y){
	echo ' <script type="text/javascript">
			window.onload = function load(){
				var newAnchor = document.createElement("a");
				newAnchor.setAttribute("name", "'.$anchorname.'");
				var getNextElement = document.getElementById("'.$nextelement.'");
				getNextElement.parentNode.insertBefore(newAnchor, getNextElement);
				
				window.location.hash="'.$anchorname.'";
				window.scrollBy(0, '.$Y.');
			};
			</script>';
}

function get_category_id() {
	return required_param('categoryid', PARAM_INT);
}
?>
