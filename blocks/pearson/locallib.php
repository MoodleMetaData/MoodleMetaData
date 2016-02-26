<?php

/**
 * Pearson library code.
 *
 * @package    blocks_pearson
 * @copyright  
 * @license    
 */

defined('MOODLE_INTERNAL') || die;

function pearsondirect_is_global_configured() {
	global $CFG;
	$configured = true;
	$pearsonUrl = $CFG->pearson_url;
	$pearsonKey = $CFG->pearson_key;
	$pearsonSecret = $CFG->pearson_secret;
	
	if ((!isset($pearsonUrl) || trim($pearsonUrl)==='')
		|| (!isset($pearsonKey) || trim($pearsonKey)==='')
		|| (!isset($pearsonSecret) || trim($pearsonSecret)==='')) {
		$configured = false;
	}
	
	// @todo - test url
	
	return $configured;
}

function pearsondirect_is_block_configured($block) {
	$configured = true;
	
	if ($block->config) {
		$codes = $block->config->codes;
		$customcodes = $block->config->customcodes;
		if ((!isset($codes) || trim($codes)==='')
			&& (!isset($customcodes) || trim($customcodes)==='')) {
			$configured = false;
		}		
	}
	else {
		$configured = false;
	}
	
	return $configured;
}

function pearsondirect_get_product($code, $params) {
	global $CFG;
		
	$curl = curl_init();
	
	$url = $CFG->pearson_url;
	$url .= "/highlander/api/v1/bundles/".$code;
	$url .= '?direct=true';
	//$url .= "&custom_context_start_date=".date("Y-m-d");
	//$url .= "&custom_context_end_date=".date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y")+2));
	
	$strValidatedParams = '';
	if (isset($params) && trim($params)!='') {
		if (strpos($params, ',')) {			
			$kvs = explode(',',$params);
			foreach ($kvs as $param) {
				$strValidatedParams .= '&';
				$kv = explode('=', $param);
		  		$k = $kv[0]; 		
				if(!(!strncmp($k, 'custom_', strlen('custom_')))) {
					$strValidatedParams .= 'custom_';
				  	$strValidatedParams .= $k;
				}
				else {
				  	$strValidatedParams .= $k;
				}
				  
				$strValidatedParams .= '=';
				$strValidatedParams .= $kv[1];			
			}
		
		}
		else {
		  //assume we've only got one param
		  $kv = explode('=', $params);
		  $strValidatedParams .= '&';
		  $k = $kv[0];
		  if(!(!strncmp($k, 'custom_', strlen('custom_')))) {
		  	$strValidatedParams .= 'custom_';
		  	$strValidatedParams .= $k;
		  }
		  else {
		  	$strValidatedParams .= $k;
		  }
		  
		  $strValidatedParams .= '=';
		  $strValidatedParams .= $kv[1];
		}
	}
	
	$url .= $strValidatedParams;
	//echo $url;
	
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	
	$response = curl_exec($curl);
	
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);	
	
	$retval = NULL;
	
	if (!curl_errno($curl) && ($status === 200)) {
		$json = json_decode($response);
		$product = new stdClass;
		$product->bundle = $json->basicLtiLinkBundle;
		$product->platform = $json->platform;
		$product->description = $json->basicLtiLinkBundle->description;
		$retval = $product;
	}	
	
	curl_close($curl);
	return $retval;
}

function pearsondirect_is_content_placement($link) {
	$content_placement = false;
	$exts = $link->extensions;
	$params = $exts[0]->parameters;
	foreach ($params as $param) {
		$n = $param->name;
		if (strcasecmp($n, 'placement') == 0) {
			$v = $param->value;
			if (strcasecmp($v, 'content') == 0) {				
				$content_placement = true;
			}
		}
	}
	return $content_placement;
}

function pearsondirect_is_instructor_link($link) {
	$instructorLink = true;
	$exts = $link->extensions;
	$params = $exts[0]->parameters;
	foreach ($params as $param) {
		$n = $param->name;
		if (strcasecmp($n, 'isinstructoronly') == 0) {
			$v = $param->value;
			if (strcasecmp($v, 'false') == 0) {				
				$instructorLink = false;
			}
		}
	}
	return $instructorLink;
}

function pearsondirect_create_lti_type($link, $courseid, $userid) {
	global $DB, $CFG;
	$ltitype = new stdClass;
	$ltitype->name = $link->title;
	$ltitype->baseurl = $link->launchUrl;
	$ltitype->tooldomain = pearsondirect_construct_id_for_ltitypes($link->id,$courseid);
	$ltitype->state = 1;
	$ltitype->course = $courseid;
	$ltitype->coursevisible = 1;
	$ltitype->createdby = $userid;
	$ltitype->timecreated = time();
	$ltitype->timemodified = $ltitype->timecreated;
	
	$ltitype->id = $DB->insert_record('lti_types', $ltitype);
	
	$ltitypeconfig = new stdClass;
	$ltitypeconfig->resourcekey = $CFG->pearson_key;
	$ltitypeconfig->password = $CFG->pearson_secret;
	
	$strCustomParams = '';
	
	$cps = $link->customParameters;
	
	foreach ($cps as $cp) {
		$n = $cp->name;
		
		// @todo fix
		if ($n == 'target_function') {
			$n = 'targetId';
		}
		
		$v = $cp->value;
		
		if ($strCustomParams == NULL) {
			$strCustomParams = '';
		}
		$strCustomParams .= $n.'='.$v."\n";
	}
	
	if ($strCustomParams != null) {
		$ltitypeconfig->customparameters = $strCustomParams;
	}
	
	$ltitypeconfig->launchcontainer = 4;
	$ltitypeconfig->sendname = 1;
	$ltitypeconfig->sendemailaddr = 1;
	$ltitypeconfig->acceptgrades = 2;
	//$ltitypeconfig->organizationid = ;
	//$ltitypeconfig->organizationurl = ;
	$ltitypeconfig->coursevisible = 1;
	$ltitypeconfig->forcessl = 0;
	$ltitypeconfig->servicesalt = 'pearson.salt';
	$ltitypeconfig->icon = $CFG->wwwroot.'/blocks/pearson/pix/icon.png';
	
	
	if ($ltitype->id) {
        foreach ($ltitypeconfig as $key => $value) {
            $record = new StdClass();
            $record->typeid = $ltitype->id;
            $record->name = $key;
            $record->value = $value;

            $DB->insert_record('lti_types_config', $record);
        }
    }	
}

function pearsondirect_create_lti($ltilink, $courseid) {
	global $DB, $CFG;
	require_once($CFG->dirroot.'/course/lib.php');
	$lti = new stdClass;
	$lti->course = $courseid;
	$lti->name = $ltilink->title;
	$lti->timecreated = time();
    $lti->timemodified = $lti->timecreated;
    $lti->typeid = 0;
	$lti->toolurl = $ltilink->launchUrl;
	$lti->instructorchoicesendname = 1;
	$lti->instructorchoicesendemailaddr = 1;
	
	$strCustomParams = '';
	
	$cps = $ltilink->customParameters;
	
	foreach ($cps as $cp) {
		$n = $cp->name;
		// @todo fix
		if ($n == 'target_function') {
			$n = 'targetId';
		}
		
		$v = $cp->value;
		
		if ($strCustomParams == NULL) {
			$strCustomParams = '';
		}
		$strCustomParams .= $n.'='.$v."\n";
	}
	
	if ($strCustomParams != null) {
		$lti->instructorcustomparameters = $strCustomParams;
	}
	
	$lti->launchcontainer = 4;
	$lti->resourcekey = $CFG->pearson_key;
	$lti->password = $CFG->pearson_secret;
	$lti->debuglaunch = 0;
	$lti->showtitlelaunch = 0;
	$lti->showdescriptionlaunch = 0;
	
	$use_icons = $CFG->pearson_use_icons;
	if ($use_icons) {
		$lti->icon = $CFG->wwwroot.'/blocks/pearson/pix/icon.png';
	}
	
	$lti->id = $DB->insert_record('lti', $lti);
	
	$cm = new stdClass;
	$cm->course = $courseid;
	$cm->module = pearsondirect_get_lti_module();
	$cm->instance = $lti->id;
	$cm->section = 0;
	$cm->idnumber = pearsondirect_construct_id_for_lti($ltilink->id,$courseid,$lti->id,'t');
	$cm->added = time();
	$cm->score = 0;
	$cm->indent = 0;
	
	$instructoronly = pearsondirect_is_instructor_link($ltilink);
	
	if ($instructoronly) {
		$cm->visible = 0;
		$cm->visibleold = 0;	
	}
	else {
		$cm->visible = 1;
		$cm->visibleold = 1;	
	}
	
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
	
	return $lti->id;
}

function pearsondirect_create_pearson_mm_config($config) {
	global $DB;
	$DB->insert_record('pearson',$config);
}

function pearsondirect_update_pearson_mm_config($config) {
	global $DB;
	$DB->update_record('pearson',$config);
}

function pearsondirect_get_lti_module() {
	global $DB;
	$module = $DB->get_record('modules', array('name' => 'lti'), '*', MUST_EXIST);
	return $module->id;
}

function pearsondirect_is_student($USER, $courseid) {
	global $USER,$CFG;
	$student = false;
	require_once($CFG->dirroot.'/mod/lti/locallib.php');
	
	$role = lti_get_ims_role($USER,'',$courseid);
	
	if (strcasecmp($role, 'learner') == 0) {
		$student = true;
	}
	
	return $student;
}

function pearsondirect_course_has_config($courseid) {
	global $DB;
	return $DB->get_record('pearson', array('course' => $courseid), '*', IGNORE_MISSING);
}

function pearsondirect_handle_param_change($courseconfig,$newparams) {
	global $DB;
	
	$product = pearsondirect_get_product($courseconfig->code, $newparams);
	if ($product) {
		$links = $product->bundle->links;
		$coursemodules = $DB->get_records('course_modules', array('course' => $courseconfig->course, 'module' => pearsondirect_get_lti_module()));
    	foreach ($links as $link) {
			$strCustomParams = '';

			$cps = $link->customParameters;
			
			foreach ($cps as $cp) {
				$n = $cp->name;
				$v = $cp->value;
				
				if ($strCustomParams == NULL) {
					$strCustomParams = '';
				}
				$strCustomParams .= $n.'='.$v."\n";
			}
    	
    		$isinstructorlink = pearsondirect_is_instructor_link($link);
													    	
    		if (pearsondirect_is_content_placement($link) && !$isinstructorlink) {
    			$type = $DB->get_record('lti_types', array('tooldomain' => pearsondirect_construct_id_for_ltitypes($link->id,$courseconfig->course)), '*', IGNORE_MISSING);
    			if ($type) {
    				$DB->set_field("lti_types_config", "value", $strCustomParams, array('typeid'=>$type->id,'name'=>'customparameters'));
    			}    			
    		}									

			if ($coursemodules) {
				foreach($coursemodules as $coursemodule) {
					if (!strncmp($coursemodule->idnumber, pearsondirect_construct_id_for_ltitypes($link->id,$courseconfig->course), 
						strlen(pearsondirect_construct_id_for_ltitypes($link->id,$courseconfig->course)))) {
						$lti = $DB->get_record('lti', array('id' => $coursemodule->instance), '*', IGNORE_MISSING);
						if ($lti) {
							$str_current_params = $lti->instructorcustomparameters;
							if (isset($str_current_params) && trim($str_current_params) != '') {
								$explodeParams = explode("\n",$str_current_params);
								if ($explodeParams) {
									foreach ($explodeParams as $xp) {
										if (trim($xp) != '') {
											$kv = explode('=',$xp);
											if (!strstr($strCustomParams,$kv[0])) {
												$strCustomParams .= $xp."\n";
											}
										}
									}
								}
							}
							$DB->set_field("lti", "instructorcustomparameters", $strCustomParams, array("id" => $coursemodule->instance));														
						}
					}
				}
			}	    			    		
    	}
		
		$DB->set_field("pearson", "parameters", $newparams, array("id" => $courseconfig->id));
	}
}

function pearsondirect_handle_code_change($courseconfig,$newcode,$newparams) {
	global $DB,$CFG;
	require_once($CFG->dirroot.'/mod/lti/locallib.php');
	require_once($CFG->dirroot.'/course/lib.php');
	
	$types = $DB->get_records('lti_types', array('course' => $courseconfig->course));
	if($types) {
		foreach ($types as $type) {
			if (!strncmp($type->tooldomain, 'pearson:', strlen('pearson:'))) {
				lti_delete_type($type->id);
			}
		}
	}
	
	$coursemodules = $DB->get_records('course_modules', array('course' => $courseconfig->course, 'module' => pearsondirect_get_lti_module()));
	if($coursemodules) {
		foreach ($coursemodules as $cm) {
			if (!strncmp($cm->idnumber, 'pearson:', strlen('pearson:'))) {
				course_delete_module($cm->id);
			}
		}
	}
}


function pearsondirect_construct_id_for_ltitypes($highlanderid,$courseid) {
	return 'pearson:'.$highlanderid.':'.$courseid;
}

function pearsondirect_construct_id_for_lti($highlanderid,$courseid,$localid,$placement) {
	return 'pearson:'.$highlanderid.':'.$courseid.':'.$localid.':'.$placement;
}

function pearsondirect_getavailableproducts($pearson_url) {
	global $CFG;
	$availableproducts = NULL;
    $curl = curl_init();		
	$url = $pearson_url;
	$url .= '/highlander/api/v1/dashboard';
    
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	
	$response = curl_exec($curl);
	
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);	
	
	if (!curl_errno($curl) && ($status === 200)) {
		$json = json_decode($response);
		$providers = $json->providers;
		$availableproducts = array();
		$availableproducts['none'] = '';
		foreach ($providers as $provider) {
			$availableproducts[$provider->code] = $provider->name;
		} 
	}	
	curl_close($curl);
	return $availableproducts;
}

function pearsondirect_updateexistinglink($courseid, $link, $placement) {
	global $CFG,$DB;
	$retval = NULL;
	$coursemodules = $DB->get_records('course_modules', array('course' => $courseid, 'module' => pearsondirect_get_lti_module()));
	if ($coursemodules) {
		foreach($coursemodules as $coursemodule) {
			if (!strncmp($coursemodule->idnumber, 'pearson:'.$link->id, 
				strlen('pearson:'.$link->id))) {
				$lti = $DB->get_record('lti', array('id' => $coursemodule->instance), '*', IGNORE_MISSING);
				if ($lti) {
					$DB->set_field("lti", "resourcekey", $CFG->pearson_key, array("id" => $coursemodule->instance));
					$DB->set_field("lti", "password", $CFG->pearson_secret, array("id" => $coursemodule->instance));
					
					$use_icons = $CFG->pearson_use_icons;
					if ($use_icons) {
						$DB->set_field("lti", "icon", $CFG->wwwroot.'/blocks/pearson/pix/icon.png', array("id" => $coursemodule->instance));
					}
					
					$DB->set_field("lti", "toolurl", $link->launchUrl, array("id" => $coursemodule->instance));
					$DB->set_field("course_modules","idnumber",pearsondirect_construct_id_for_lti($link->id,$courseid,$lti->id,$placement),array("id" => $coursemodule->id));
					$retval = $lti->id;														
				}				 
			}
		}
	}	    			    		
	return $retval;
}

