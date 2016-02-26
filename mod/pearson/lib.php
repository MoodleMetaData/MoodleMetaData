<?php
/**
 * 
 *
 * @package    mod
 * @subpackage pearson
 * @copyright  
 * @author     
 * @license    
 */

defined('MOODLE_INTERNAL') || die;

/**
 * List of features supported in URL module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function pearson_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return true;
        case FEATURE_GRADE_OUTCOMES:          return true;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;
        case FEATURE_MOD_INTRO:               return false;

        default: return null;
    }
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod.html) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $instance An object from the form in mod.html
 * @return int The id of the newly inserted basiclti record
 **/
function pearson_add_instance($pd, $mform) {
    global $DB, $CFG;
    require_once($CFG->dirroot.'/mod/lti/locallib.php');
    require_once($CFG->dirroot.'/course/lib.php');
    require_once($CFG->dirroot.'/blocks/pearson/locallib.php');
    require_once($CFG->dirroot.'/lib/modinfolib.php');
    
    $pd_c = clean_param($pd->pdcourseid, PARAM_INT);
    $pd_s = clean_param($pd->pdsection, PARAM_INT);
    $launchpresentation = 4;
    if (isset($pd->inframe)) {
    	$launchpresentation = 2;
    }

    $templatelink = $DB->get_record('lti_types', array('id' => $pd->selectedlink), '*', MUST_EXIST);
    	
	$ltilink = new stdClass;
	$ltilink->course = $templatelink->course;
	
	$linkname = '';
	if (isset($pd->mediaurltitle) && trim($pd->mediaurltitle)!=='') {
		$linkname = clean_param($pd->mediaurltitle, PARAM_TEXT);
	}
	else if (isset($pd->linktitle) && trim($pd->linktitle)!=='') {
		$linkname = clean_param($pd->linktitle, PARAM_TEXT);
	}
	else {
		$linkname = $templatelink->name;
	}
	
	
	$ltilink->name = format_string($linkname);
	
	$ltilink->timecreated = time();
    $ltilink->timemodified = $ltilink->timecreated;
    $ltilink->typeid = 0;
	$ltilink->toolurl = $templatelink->baseurl;
	$ltilink->instructorchoicesendname = 1;
	$ltilink->instructorchoicesendemailaddr = 1;
	
	$strCustomParams = $DB->get_field('lti_types_config', 'value', array('typeid' => $templatelink->id, 'name' => 'customparameters'), IGNORE_MISSING);
	
	if ($strCustomParams == null) {
		$strCustomParams = '';
	}
	
	$pd_customparams = clean_param($pd->customparams, PARAM_TEXT);
	
	if (isset($pd_customparams) && trim($pd_customparams)!=='') {
		if (strpos($pd_customparams,',') !== false) {
			$cp_array = explode(',',$pd_customparams);
			foreach($cp_array as $cpa) {
				$kv_array = explode('=',$cpa);
				$key = $kv_array[0];
				$length = strlen('custom_');
    			if (substr($key, 0, $length) === 'custom_') {
    				$key = substr($key, $length, strlen($key));
    			}
				
				$strCustomParams .= $key.'='.$kv_array[1]."\n";
			}
		}
		else {
			$kv_array = explode('=',$pd_customparams);
			$key = $kv_array[0];
			$length = strlen('custom_');
    		if (substr($key, 0, $length) === 'custom_') {
    			$key = substr($key, $length, strlen($key));
    		}
			
			$strCustomParams .= $key.'='.$kv_array[1]."\n";
		}
	}
	
	$pd_mediaurl = clean_param($pd->mediaurl, PARAM_URL);	
	if (isset($pd_mediaurl) && trim($pd_mediaurl)!=='') {
		$strCustomParams .= 'genericmediaurl='.$pd_mediaurl."\n";		
	}

	if ($strCustomParams != null) {
		$ltilink->instructorcustomparameters = $strCustomParams;
	}
	
	$ltilink->launchcontainer = $launchpresentation;
	$ltilink->resourcekey = $CFG->pearson_key;
	$ltilink->password = $CFG->pearson_secret;
	$ltilink->debuglaunch = 0;
	$ltilink->showtitlelaunch = 0;
	$ltilink->showdescriptionlaunch = 0;
	
	$use_icons = $CFG->pearson_use_icons;
	if ($use_icons) {
		$ltilink->icon = $CFG->wwwroot.'/blocks/pearson/pix/icon.png';
	}
	
	$ltilink->id = $DB->insert_record('lti', $ltilink);
	
	$cm = new stdClass;
	$cm->course = $pd_c;
	$cm->module = pearsondirect_get_lti_module();
	$cm->instance = $ltilink->id;
	$cm->section = $pd_s;
	$cm->idnumber = $templatelink->tooldomain.':'.$ltilink->id.':c';
	$cm->added = time();
	$cm->score = 0;
	$cm->indent = 0;
	$cm->visible = 1;
	$cm->visibleold = 1;	
	$cm->groupmode = 0;
	$cm->groupingid = 0;
	$cm->groupmembersonly = 0;
	$cm->completion = 0;
	$cm->completionview = 0;
	$cm->completionexpected = 0;
	$cm->showavailability = 0;
	$cm->showdescription = 0;
	$cm->coursemodule = add_course_module($cm);
	
	$sectionid = course_add_cm_to_section($cm->course, $cm->coursemodule, $cm->section, null);
	$DB->set_field("course_modules", "section", $sectionid, array("id" => $cm->coursemodule));
	
	rebuild_course_cache($pd_c);	
	redirect("$CFG->wwwroot/course/view.php?id=$pd_c#section-".$pd_s);				    	
	
    return $ltilink->id;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod.html) this function
 * will update an existing instance with new data.
 *
 * @param object $instance An object from the form in mod.html
 * @return boolean Success/Fail
 **/
function pearson_update_instance($lti, $mform) {
	// this should never be called
    return true;
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 **/
function pearson_delete_instance($id) {
	// this should never be called
    return true;
}

/**
 * Given a coursemodule object, this function returns the extra
 * information needed to print this activity in various places.
 * For this module we just need to support external urls as
 * activity icons
 *
 * @param cm_info $coursemodule
 * @return cached_cm_info info
 */
function pearson_get_coursemodule_info($coursemodule) {
    global $DB, $CFG;
    require_once($CFG->dirroot.'/mod/lti/locallib.php');

    if (!$lti = $DB->get_record('lti', array('id' => $coursemodule->instance),
            'icon, secureicon, intro, introformat, name')) {
        return null;
    }

    $info = new cached_cm_info();

    // We want to use the right icon based on whether the
    // current page is being requested over http or https.
    if (lti_request_is_using_ssl() && !empty($lti->secureicon)) {
        $info->iconurl = new moodle_url($lti->secureicon);
    } else if (!empty($lti->icon)) {
        $info->iconurl = new moodle_url($lti->icon);
    }

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $info->content = format_module_intro('lti', $lti, $coursemodule->id, false);
    }

    $info->name = $lti->name;

    return $info;
}

/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @TODO: implement this moodle function (if needed)
 **/
function pearson_user_outline($course, $user, $mod, $basiclti) {
    return null;
}

/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @TODO: implement this moodle function (if needed)
 **/
function pearson_user_complete($course, $user, $mod, $basiclti) {
    return true;
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in basiclti activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @uses $CFG
 * @return boolean
 * @TODO: implement this moodle function
 **/
function pearson_print_recent_activity($course, $isteacher, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @uses $CFG
 * @return boolean
 **/
function pearson_cron () {
    return true;
}

/**
 * Must return an array of grades for a given instance of this module,
 * indexed by user.  It also returns a maximum allowed grade.
 *
 * Example:
 *    $return->grades = array of grades;
 *    $return->maxgrade = maximum allowed grade;
 *
 *    return $return;
 *
 * @param int $basicltiid ID of an instance of this module
 * @return mixed Null or object with an array of grades and with the maximum grade
 *
 * @TODO: implement this moodle function (if needed)
 **/
function pearson_grades($basicltiid) {
    return null;
}

/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of basiclti. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $basicltiid ID of an instance of this module
 * @return mixed boolean/array of students
 *
 * @TODO: implement this moodle function
 **/
function pearson_get_participants($basicltiid) {
    return false;
}

/**
 * This function returns if a scale is being used by one basiclti
 * it it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $basicltiid ID of an instance of this module
 * @return mixed
 *
 * @TODO: implement this moodle function (if needed)
 **/
function pearson_scale_used ($basicltiid, $scaleid) {
    $return = false;

    return $return;
}

/**
 * Checks if scale is being used by any instance of basiclti.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any basiclti
 *
 */
function pearson_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('lti', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Execute post-install custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function pearson_install() {
     return true;
}

/**
 * Execute post-uninstall custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function pearson_uninstall() {
    return true;
}


