<?php

/**
 * Pearson block code.
 *
 * @package    blocks_pearson
 * @copyright  2012-2013 Pearson Education
 * @license    
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2013111301;
$plugin->requires  = 2013051400;
$plugin->cron      = 0;
$plugin->component = 'block_pearson';
$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = '1.0';

$plugin->dependencies = array(
		'mod_lti' => ANY_VERSION
);