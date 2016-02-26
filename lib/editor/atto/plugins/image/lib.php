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
 * Atto text editor integration version file.
 *
 * @package    atto_image
 * @copyright  2013 Damyon Wiese  <damyon@moodle.com>
 * @copyright  2015 Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Initialise the strings required for js
 */
function atto_image_strings_for_js() {
    global $PAGE;

    $strings = array(
        'alignment',
        'alignment_bottom',
        'alignment_left',
        'alignment_middle',
        'alignment_right',
        'alignment_top',
        'browserepositories',
        'constrain',
        'saveimage',
        'imageproperties',
        'customstyle',
        'enterurl',
        'enteralt',
        'height',
        'presentation',
        'presentationoraltrequired',
        'size',
        'width',

        'customcsstooltip',
        'bordertooltip',
        'spacingtooltip',
        'texttoptooltip',
        'textbaselinetooltip',
        'textbottomtooltip',
        'leftaligntooltip',
        'rightaligntooltip',
        'normalflowtooltip'
    );

    $PAGE->requires->strings_for_js($strings, 'atto_image');
}

/**
 * Make some strings (from settings.php) available in javascript.
 */
function atto_image_params_for_js($elementid, $options, $foptions) {
    // Pass the number of visible groups as a param.
    $params = array(
        'handle_config' => get_config('atto_image', 'availableresizehandle'),
        'minmaxwidthheight' => get_config('atto_image', 'minmaxwidthheight'),
        'toggle_key_preserve_aspect_ratio' => get_config('atto_image', 'togglekeypreserveaspectratio'),

        'disable_custom_classes' => get_config('atto_image', 'disablecustomclasses'),

        'resize_animation_enable' => get_config('atto_image', 'resizeanimationenable'),
        'resize_animation_duration' => get_config('atto_image', 'resizeanimationduration'),
        'resize_animation_easing' => get_config('atto_image', 'resizeeasing')
    );
    return $params;
}
