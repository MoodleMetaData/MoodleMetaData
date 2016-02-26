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
 * Strings for component 'atto_countplusplus', language 'en'.
 *
 * @package    atto_countplusplus
 * @copyright  2015 Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$pluginname = new lang_string('pluginname', 'atto_countplusplus');

$ADMIN->add('editoratto', new admin_category('atto_countplusplus', $pluginname));

$settings = new admin_settingpage('atto_countplusplus_settings', new lang_string('settings', 'atto_countplusplus'));
if ($ADMIN->fulltree) {
    $name = new lang_string('statlayout', 'atto_countplusplus');
    $description =
        '<br/>This sets the layout of the <b>'.$pluginname.'</b> plugin.<br/>' .
        'Patterns:' .
        '<ul>' .
        '  <li><b>%lc</b> is replaced with current letter count.</li>' .
        '  <li><b>%wc</b> is replaced with current word count.</li>' .
        '  <li><b>|</b> is replaced with a separator.</li>' .
        '</ul><br/>' .
        'For instance, the default layout is displayed as: "Word count: 23 <b>|</b> Letter count: 45" ' .
        '(without quotes) in the status bar at the bottom.';
    $default = 'Word count: %wc | Letter count: %lc';

    $setting = new admin_setting_configtextarea('atto_countplusplus/statlayout', $name, $description, $default, PARAM_RAW);

    $settings->add($setting);
}