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
 *
 *
 * @version $Id$
 * @copyright 2011
 */


require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

$site = get_site();

global $CFG, $DB;
require_once('lib.php');

$entity = $_POST['entity'];
$where = $_POST['where'];

if (md5($entity.$where.$_POST['timestamp'].$CFG->uaimssecret) == $_POST['mac']) {
    echo generate_valid_xml_from_array($DB->get_records_select($entity, $where), $entity.'s', $entity);
} else {
    echo 'Invalid mac';
}

function generate_xml_from_array($array, $nodename) {
    $xml = '';

    if (is_array($array) || is_object($array)) {
        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $key = $nodename;
            }
            $xml .= '<' . $key . '>' . "" . generate_xml_from_array($value, $nodename) . '</' . $key . '>' . "\n";
        }
    } else {
        $xml = htmlspecialchars($array, ENT_QUOTES) . "";
    }
    return $xml;
}

function generate_valid_xml_from_array($array, $nodeblock='nodes', $nodename='node') {
    $xml = '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
    $xml .= '<' . $nodeblock . '>' . "\n";
    $xml .= generate_xml_from_array($array, $nodename);
    $xml .= '</' . $nodeblock . '>' . "\n";

    return $xml;
}


