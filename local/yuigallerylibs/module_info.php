<?php

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
* This is a small helper file to allow for easier inclusion of the
* javascript files.
*
* @package    yuigallerylibs
* @category   local
* @copyright  2012 Craig Jamieson
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    //  It must be included from a Moodle page
}

function get_js_module_info($modulename)
{
    switch($modulename)
    {
        case 'gallery-multivalue-input':
            $module = array('name' => 'gallery-multivalue-input',
                            'fullpath' => new moodle_url('/local/yuigallerylibs/gallery-multivalue-input/gallery-multivalue-input-min.js'),
                            'path' => '/local/yuigallerylibs/gallery-multivalue-input/gallery-multivalue-input-min.js',
                            'requires' => array('plugin', 'substitute', 'node', 'classnamemanager'));
            break;
        case 'gallery-datatable-selection':
            $module = array('name' => 'gallery-datatable-selection',
                            'fullpath' => new moodle_url('/local/yuigallerylibs/gallery-datatable-selection/gallery-datatable-selection-min.js'),
                            'path' => '/local/yuigallerylibs/gallery-datatable-selection/gallery-datatable-selection-min.js',
                            'requires' => array("base-build", "datatable-base", "event-custom"));
            break;
        case 'gallery-datatable-paginator':
            $module = array('name' => 'gallery-datatable-paginator',
                            'fullpath' => new moodle_url('/local/yuigallerylibs/gallery-datatable-paginator/gallery-datatable-paginator-min.js'),
                            'path' => '/local/yuigallerylibs/gallery-datatable-paginator/gallery-datatable-paginator-min.js',
                            'requires' => array("datatable-base", "base-build", "datatype", "json"));
            break;
        case 'gallery-paginator-view':
            $module = array('name' => 'gallery-paginator-view',
                            'fullpath' => new moodle_url('/local/yuigallerylibs/gallery-paginator-view/gallery-paginator-view-min.js'),
                            'path' => '/local/yuigallerylibs/gallery-paginator-view/gallery-paginator-view-min.js',
                            'requires' => array("model", "view", "substitute"));
            break;
    }

    return $module;
}