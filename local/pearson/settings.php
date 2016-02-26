<?php

/**
 * Add page to menu.
 *
 * @package    local_pearson
 * @copyright  
 * @license    
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) { // needs this condition or there is error on login page
	require_once($CFG->dirroot.'/local/pearson/locallib.php');
	$settings = new admin_settingpage(
	        'local_pearson',
	        get_string('pearson_config_title', 'local_pearson')
	    );
	    
	$strConfigPageText = get_string('pearson_config_text', 'local_pearson');
	
	$html = '<p>'.$strConfigPageText.'</p>';
	if (pearsondirect_showkeymasterlink()) {	
		$html .= '</br>';
		$html .= '<a href="'.pearsondirect_getkeymasterlink().'" target="_blank">'.get_string('pearson_keymaster_link', 'local_pearson').'</a>';
	}
	    
    $settings->add(new admin_setting_heading('pearson_config_heading', get_string('pearson_config_heading', 'local_pearson'), format_text($html, FORMAT_HTML)));
    $settings->add(new admin_setting_configtext('pearson_url', get_string('pearson_url_label', 'local_pearson'), get_string('pearson_url_desc', 'local_pearson'), '', PARAM_TEXT));
    $settings->add(new admin_setting_configtext('pearson_key', get_string('pearson_key_label', 'local_pearson'), get_string('pearson_key_desc', 'local_pearson'), '', PARAM_TEXT));
    $settings->add(new admin_setting_configtext('pearson_secret', get_string('pearson_secret_label', 'local_pearson'), get_string('pearson_secret_desc', 'local_pearson'), '', PARAM_TEXT));
    $settings->add(new admin_setting_configcheckbox('pearson_use_icons', get_string('pearson_use_icons_label', 'local_pearson'),
                    get_string('pearson_use_icons_desc', 'local_pearson'), 1));
	$ADMIN->add('localplugins', $settings);
}
