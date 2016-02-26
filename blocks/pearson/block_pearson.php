<?php

/**
 * Pearson block code.
 *
 * @package    blocks_pearson
 * @copyright  2012-2013 Pearson Education
 * @license
 */
class block_pearson extends block_base {
    public function init() {
        $this->title = get_string('pearson', 'block_pearson');
    }

    public function get_content() {
	    global $CFG, $COURSE, $USER, $PAGE, $DB;
	    require_once($CFG->dirroot.'/blocks/pearson/locallib.php');
	    require_once($CFG->dirroot.'/lib/modinfolib.php');
	   	$this->content = new stdClass;
	   	$this->content->text = '';
	   	$strHTML = '';

	    $courseid = $COURSE->id;

	    // check capabilities and throw error if needed
	    has_capability('block/pearson:view', context_course::instance($courseid));

	    if (pearsondirect_is_global_configured()) {
	    // this means that the base url, key and secret are configured correctly

	    	if (pearsondirect_is_block_configured($this)) {
	    	// this means there is a product code associated with this block
	    			$strInstHTML = '';
					$strStuHTML = '';
					$strPlatform = '';
					$code = '';
	    			$codes = $this->config->codes;
	    			$customcodes = $this->config->customcodes;

	    			if (isset($customcodes) && trim($customcodes)!=='') {
	    				$code = $customcodes;
	    			}
	    			else {
	    				$code = $codes;
	    			}

	    			$parameters = $this->config->parameters;

					if ($courseconfig = pearsondirect_course_has_config($courseid)) {
		    			// this means we've loaded and associated this link bundle to this course before

		    			$strPlatform = $courseconfig->platform;
		    			// check to see if the config has changed
		    			$currentcode = $courseconfig->code;
						$currentparams = $courseconfig->parameters;

						$codediff = strcasecmp($currentcode,$code);
						$paramdiff = strcasecmp($currentparams,$parameters);

		    			if ($codediff != 0) {
		    				pearsondirect_handle_code_change($courseconfig,$code,$parameters);

		    				$product = pearsondirect_get_product(trim($code), trim($parameters));
		    				if ($product) {
				    		// this means the code is valid
				    			$strIds = '';
					    		$links = $product->bundle->links;
						    	foreach ($links as $link) {
						    		if ($strIds != '') {$strIds .= ',';}
						    		$strIds .= $link->id;
						    		$isinstructorlink = pearsondirect_is_instructor_link($link);

						    		if (pearsondirect_is_content_placement($link) && !$isinstructorlink) {
						    			pearsondirect_create_lti_type($link,$courseid,$USER->id);
						    		}
						    		else {
						    			pearsondirect_create_lti($link,$courseid);
						    		}
						    	}

						    	$pearsonmmconfig = new stdClass;
						    	$pearsonmmconfig->id = $courseconfig->id;
						    	$pearsonmmconfig->course = $courseid;
						    	$pearsonmmconfig->code = $code;
						    	$pearsonmmconfig->parameters = $parameters;
						    	$pearsonmmconfig->platform = $product->platform;
						    	$pearsonmmconfig->links = $strIds;
						    	pearsondirect_update_pearson_mm_config($pearsonmmconfig);
			    				rebuild_course_cache($courseid);
						    	redirect($PAGE->url);
						    }
		    			}

		    			if ($paramdiff != 0) {
		    				pearsondirect_handle_param_change($courseconfig,$parameters);
		    			}

		    			$highlander_ids = explode(',',$courseconfig->links);

						$coursemodules = $DB->get_records('course_modules', array('course' => $courseid, 'module' => pearsondirect_get_lti_module()));

						foreach ($highlander_ids as $highlanderid) {
							foreach ($coursemodules as $coursemodule) {
								if (!strncmp($coursemodule->idnumber, pearsondirect_construct_id_for_ltitypes($highlanderid,$courseid),
										strlen(pearsondirect_construct_id_for_ltitypes($highlanderid,$courseid)))) {
									if (substr( $coursemodule->idnumber, strlen( $coursemodule->idnumber ) - strlen( 't' ) ) == 't') {
							    		$isinstructorlink = ($coursemodule->visible == 0);
							    		$link = $DB->get_record('lti', array('id'=>$coursemodule->instance));
					    				$title = trim(str_replace($strPlatform, '', $link->name));
							    		$title = trim(str_replace('Mastering', '', $title));
							    		$url = $CFG->wwwroot."/mod/lti/view.php?l=".$link->id;

							    		if ($isinstructorlink) {
							    			$strInstHTML .= '<li>';
							    			$strInstHTML .= '<a href="'.$url.'" >';
							    			$strInstHTML .= $title;
							    			$strInstHTML .= '</a>';
							    			$strInstHTML .= '</li>';
							    		}
							    		else {
							    			$strStuHTML .= '<li>';
							    			$strStuHTML .= '<a href="'.$url.'" >';
							    			$strStuHTML .= $title;
							    			$strStuHTML .= '</a>';
							    			$strStuHTML .= '</li>';
							    		}
									}
								}
					    	}
						}
		    		}
	    			else {
	    				$product = pearsondirect_get_product(trim($code), trim($parameters));
	    				$strPlatform = $product->platform;
	    				if ($product) {
			    		// this means the code is valid
				    		$links = $product->bundle->links;
					    	$strIds = '';
					    	foreach ($links as $link) {
					    		if ($strIds != '') {$strIds .= ',';}
					    		$strIds .= $link->id;
					    		$isinstructorlink = pearsondirect_is_instructor_link($link);

					    		if (pearsondirect_is_content_placement($link) && !$isinstructorlink) {
					    			pearsondirect_updateexistinglink($courseid,$link,'c');
					    			pearsondirect_create_lti_type($link,$courseid,$USER->id);
					    		}
					    		else {
					    			$id = NULL;
					    			$id = pearsondirect_updateexistinglink($courseid,$link,'t');
					    			if (!isset($id)) {
					    				$id = pearsondirect_create_lti($link,$courseid);
					    			}

						    		$title = trim(str_replace($product->platform, '', $link->title));
						    		$title = trim(str_replace('Mastering', '', $title));
						    		$url = $CFG->wwwroot."/mod/lti/view.php?l=".$id;

						    		if ($isinstructorlink) {
						    			$strInstHTML .= '<li>';
						    			$strInstHTML .= '<a href="'.$url.'" >';
						    			$strInstHTML .= $title;
						    			$strInstHTML .= '</a>';
						    			$strInstHTML .= '</li>';
						    		}
						    		else {
						    			$strStuHTML .= '<li>';
						    			$strStuHTML .= '<a href="'.$url.'" >';
						    			$strStuHTML .= $title;
						    			$strStuHTML .= '</a>';
						    			$strStuHTML .= '</li>';
						    		}
					    		}
					    	}

					    	$pearsonmmconfig = new stdClass;
					    	$pearsonmmconfig->course = $courseid;
					    	$pearsonmmconfig->code = $code;
					    	$pearsonmmconfig->parameters = $parameters;
					    	$pearsonmmconfig->platform = $product->platform;
					    	$pearsonmmconfig->links = $strIds;
					    	pearsondirect_create_pearson_mm_config($pearsonmmconfig);

					    	rebuild_course_cache($courseid);
					    	redirect($PAGE->url);
				    	}
				    	else {
				    		$strHTML = get_string('pearson_couldnotaccessproduct', 'block_pearson');
				    	}
	    			}

			    	$strHTML .= '<div id="'.'block_pearson_tree'.'" >';
				    $strHTML .= '<ul style="'.'list-style-type: none;margin: 0;padding:2px 0px 0px 2px;'.'">';
				    $strHTML .= '<li>'.$strPlatform;
		    		if (!pearsondirect_is_student($USER, $courseid) && $strInstHTML != '') {
				    	$strHTML .= '<ul style="'.'list-style-type: none;margin: 0;padding:2px 0px 0px 4px;'.'"><li>Instructor links';
				    	$strHTML .= '<ul style="'.'list-style-type: none;margin: 0;padding:2px 0px 0px 6px;'.'">'.$strInstHTML.'</ul>';
				    	$strHTML .= '</li></ul>';
			    	}
			    	if ($strStuHTML != '') {
					    $strHTML .= '<ul style="'.'list-style-type: none;margin: 0;padding:2px 0px 0px 4px;'.'"><li>Student links';
				    	$strHTML .= '<ul style="'.'list-style-type: none;margin: 0;padding:2px 0px 0px 6px;'.'">'.$strStuHTML.'</ul>';
				    	$strHTML .= '</li></ul>';
				    }

			    	$strHTML .= '</li></ul>';
			    	$strHTML .= '</div>';

	    	}
	    	else {
	    	// this means there is no product code associated with this block
	    		$strHTML = get_string('pearson_blocknotconfigured', 'block_pearson');
	    	}
	    }
	    else {
	    // this means that the base url, key and secret are not configured correctly
	    	$strHTML = get_string('pearson_notconfigured', 'block_pearson');
	    }
	    $this->content->text = format_text($strHTML, FORMAT_HTML);
	    $this->content->footer = '';

	    return $this->content;
  }

  public function specialization() {
	  if (!empty($this->config->title)) {
	    $this->title = $this->config->title;
	  } else {
	    $this->title = get_string('pearson', 'block_pearson');
	  }
  }

  public function applicable_formats() {
	  return array('course-view' => true);
  }
}   // Here's the closing bracket for the class definition