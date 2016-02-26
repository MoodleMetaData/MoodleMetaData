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

defined('MOODLE_INTERNAL') || die();

$module->version   = 2014052000; // Was 2013111301.
$module->requires  = 2013051400;
$module->cron      = 0;
$module->component = 'mod_pearson';
$module->maturity  = MATURITY_STABLE;
$module->release   = '1.0.1'; // Was '1.0'.

$module->dependencies = array(
		'mod_lti' => ANY_VERSION
);

