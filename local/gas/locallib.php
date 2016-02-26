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
//
// Author: Behdad Bakhshinategh!

defined('MOODLE_INTERNAL') || die();
function local_gas_page($url, $pagetitle, $pageheading, $context) {
    global $CFG, $PAGE;
    $PAGE->set_url($CFG->pluginlocalwww . $url);
    $PAGE->set_title($pagetitle);
    $PAGE->set_heading($pageheading);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_context($context);
    $PAGE->requires->jquery();
    $PAGE->requires->jquery_plugin('ui');
    $PAGE->requires->jquery_plugin('ui-css');
    $PAGE->requires->css($CFG->pluginlocalstyle . "gas.css");
    $PAGE->requires->css($CFG->pluginlocalscript . "select2/select2.css");
    $PAGE->requires->js($CFG->pluginlocalscript . "select2/select2.min.js");
    $PAGE->requires->js($CFG->pluginlocalscript . "d3.js");
    $PAGE->requires->js($CFG->pluginlocalscript . "tab.js");
    $PAGE->requires->js($CFG->pluginlocalscript . "RadarChart.js");
}