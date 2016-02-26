<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *       __  _____________   _______   __________  ____  ______
 *      /  |/  / ____/ __ \ /  _/   | / ____/ __ \/ __ \/ ____/
 *     / /|_/ / __/ / / / / / // /| |/ /   / / / / /_/ / __/
 *    / /  / / /___/ /_/ /_/ // ___ / /___/ /_/ / _, _/ /___
 *   /_/  /_/_____/_____//___/_/  |_\____/\____/_/ |_/_____/
 *
 * MediaCore's local plugin settings
 *
 * @package    local
 * @subpackage mediacore
 * @copyright  2012 MediaCore Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die('Invalid access');

global $CFG;
require_once $CFG->dirroot. '/local/mediacore/lib.php';

if ($hassiteconfig) {
    $settings = new admin_settingpage(
            LOCAL_MEDIACORE_PLUGIN_NAME,
            get_string('pluginname', LOCAL_MEDIACORE_PLUGIN_NAME)
        );

    //heading
    $setting = new admin_setting_heading(
            LOCAL_MEDIACORE_PLUGIN_NAME . '/heading',
            '', get_string('setting_heading_desc', LOCAL_MEDIACORE_PLUGIN_NAME)
        );
    $setting->plugin = LOCAL_MEDIACORE_PLUGIN_NAME;
    $settings->add($setting);

    //host
    $host_re = '/^(([a-z0-9-]+\.)+([a-z0-9-]{2,})|localhost)(:[0-9]{1,5})*$/';
    $setting = new admin_setting_configtext(
            LOCAL_MEDIACORE_PLUGIN_NAME . '/host',
            get_string('setting_host_label', LOCAL_MEDIACORE_PLUGIN_NAME),
            get_string('setting_host_desc', LOCAL_MEDIACORE_PLUGIN_NAME),
            '', $host_re
        );
    $setting->plugin = LOCAL_MEDIACORE_PLUGIN_NAME;
    $settings->add($setting);

    //scheme
    $setting = new admin_setting_configcheckbox(
            LOCAL_MEDIACORE_PLUGIN_NAME . '/scheme',
            get_string('setting_scheme_label', LOCAL_MEDIACORE_PLUGIN_NAME),
            get_string('setting_scheme_desc', LOCAL_MEDIACORE_PLUGIN_NAME),
            LOCAL_MEDIACORE_DEFAULT_SCHEME, 'https', 'http'
        );
    $setting->plugin = LOCAL_MEDIACORE_PLUGIN_NAME;
    $settings->add($setting);

    //use auth?
    $setting = new admin_setting_configcheckbox(
            LOCAL_MEDIACORE_PLUGIN_NAME . '/use_lti_auth',
            get_string('setting_use_lti_auth_label', LOCAL_MEDIACORE_PLUGIN_NAME),
            get_string('setting_use_lti_auth_desc', LOCAL_MEDIACORE_PLUGIN_NAME),
            LOCAL_MEDIACORE_DEFAULT_USE_LTI_AUTH, true, false
        );
    $setting->plugin = LOCAL_MEDIACORE_PLUGIN_NAME;
    $settings->add($setting);

    //consumer_key
    $setting = new admin_setting_configtext(
            LOCAL_MEDIACORE_PLUGIN_NAME . '/consumer_key',
            get_string('setting_consumer_key_label', LOCAL_MEDIACORE_PLUGIN_NAME),
            get_string('setting_consumer_key_desc', LOCAL_MEDIACORE_PLUGIN_NAME),
            '', PARAM_TEXT
        );
    $setting->plugin = LOCAL_MEDIACORE_PLUGIN_NAME;
    $settings->add($setting);

    //shared_secret
    $setting = new admin_setting_configtext(
            LOCAL_MEDIACORE_PLUGIN_NAME . '/shared_secret',
            get_string('setting_shared_secret_label', LOCAL_MEDIACORE_PLUGIN_NAME),
            get_string('setting_shared_secret_desc', LOCAL_MEDIACORE_PLUGIN_NAME),
            '', PARAM_TEXT
        );
    $setting->plugin = LOCAL_MEDIACORE_PLUGIN_NAME;
    $settings->add($setting);

    //trusted embeds
    $setting = new admin_setting_configcheckbox(
            LOCAL_MEDIACORE_PLUGIN_NAME . '/use_trusted_embeds',
            get_string('setting_use_trusted_embeds_label', LOCAL_MEDIACORE_PLUGIN_NAME),
            get_string('setting_use_trusted_embeds_desc', LOCAL_MEDIACORE_PLUGIN_NAME),
            LOCAL_MEDIACORE_DEFAULT_USE_TRUSTED_EMBEDS, true, false
        );
    $setting->plugin = LOCAL_MEDIACORE_PLUGIN_NAME;
    $settings->add($setting);

    $ADMIN->add('localplugins', $settings);
}
