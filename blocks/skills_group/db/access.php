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
 * This is the capabilities file for the skills_group block.
 *
 * Contains one standard block capability (addinstance), one capbility to allow
 * users to "manage" the block, and a student-based capability to create or join
 * a group.
 *
 * @package    block_skills_group
 * @category   block
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'block/skills_group:canmanageskillsgroups' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'student' => CAP_PROHIBIT,
            'guest' => CAP_PROHIBIT
            )
        ),

    'block/skills_group:cancreateorjoinskillsgroups' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_PROHIBIT,
            'editingteacher' => CAP_PROHIBIT,
            'teacher' => CAP_PROHIBIT,
            'student' => CAP_ALLOW,
            'guest' => CAP_PROHIBIT
            )
        ),

    'block/skills_group:addinstance' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
            ),
            'clonepermissionsfrom' => 'moodle/site:manageblocks'
        ),
);