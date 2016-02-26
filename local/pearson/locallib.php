<?php

/**
 * Pearson library code.
 *
 * @package    local_pearson
 * @copyright  
 * @license    
 */

defined('MOODLE_INTERNAL') || die;

function pearsondirect_getkeymasterlink() {
	global $CFG;
	$regUrl = NULL;
	
	if (isset($CFG->pearson_regurl)) {
		$regUrl = $CFG->pearson_regurl;
	}
	
//	if (!isset($regUrl) || trim($regUrl)==='') { // default to production
//		$regUrl = 'https://tpi.bb.pearsoncmg.com/keymaster/ui/u/index?consumer=moodleblti';
//	}	   
 	
	return $regUrl;
}

function pearsondirect_showkeymasterlink() {
	$regUrl = pearsondirect_getkeymasterlink();
	
	if (isset($regUrl) && trim($regUrl)!=='') {
		return true;
	}
	else {
		return false;
	}
}