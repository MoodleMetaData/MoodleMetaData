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
 * This is the english language file for the project.
 *
 * @package    block_skills_group
 * @category   block
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Group Sign-up';

// Capability labels.
$string['skills_group:canmanageskillsgroups'] = 'Manage/Configure skills group settings';
$string['skills_group:cancreateorjoinskillsgroups'] = 'Create or join a skills group';
$string['skills_group:addinstance'] = 'Add a new skills group block';

// Block labels.
$string['editgroupsettings'] = 'Edit skills group settings';
$string['createskillsgroup'] = 'Create/Edit a group';
$string['joinskillsgroup'] = 'Join existing group';
$string['notconfigured'] = 'Instructor has not yet configured settings.  Please check back later.';
$string['notconfiguredleft'] = 'Settings not yet configured.';
$string['notconfiguredright'] = 'Please check later.';
$string['dateexpired'] = 'Date for creating groups expired.  Please contact your instructor for assistance';
$string['dateexpiredleft'] = 'Date expired.';
$string['dateexpiredright'] = 'Please contact your instructor for assistance in joining a group';
$string['groupexpired'] = 'Group creation expired';

// Labels/Strings for Settings Edit page.
$string['editsettingstitle'] = 'Edit Skills Group Settings';
$string['inputsheader'] = 'Inputs';
$string['note'] = 'Note:';
$string['inputextrahelp'] = 'By default, all students are permitted to use the block (no setup needed).  Optionally, select a feedback below to pull data from.';
$string['feedback'] = 'Choose a feedback activity';
$string['feedbackerror'] = 'No feedback activities found, please create one first';
$string['threshold'] = 'Score threshold:';
$string['outputsheader'] = 'Outputs';
$string['outputextrahelp'] = 'All student created groups are placed in this grouping.  Attach the grouping to other activities in your course as needed.';
$string['groupings'] = 'Choose a grouping';
$string['groupingerror'] = 'No groupings found, please create one';
$string['none'] = 'None';
$string['settingsheader'] = 'Additional Settings';
$string['maxsize'] = 'Maximum group size:';
$string['allowchanges'] = 'Allow group changes until:';
$string['allownaming'] = 'Naming:';
$string['allownamingright'] = 'Students able to name their own groups';
$string['enabled'] = 'Enabled';

// Help bubbles for Settings Edit page.
$string['feedback_help'] = 'Select the feedback activity to pull student data from (optional).';
$string['groupings_help'] = 'Students and their groups will be automatically placed in this grouping for you.';
$string['maxsize_help'] = 'The maximum size for groups in your course.';
$string['threshold_help'] = 'If using a feedback activity for data, any number ABOVE this will be considered a high score.';
$string['allowchanges_help'] = 'Select a date here to have the group changes no longer avaiable after this date.';
$string['allownaming_help'] = 'Permit students to name their own groups.';

// Create group page.
$string['creategrouptitle'] = 'Create/Edit Group';
$string['creategroupheader'] = 'Create/Edit group';
$string['existinggroup'] = 'Existing group:';
$string['editmembers'] = 'Edit group members:';
$string['leavegroup'] = 'Leave group:';
$string['nogroup'] = 'None';
$string['creategroup'] = 'Create group:';
$string['groupsearchable'] = 'Allow classmates to search for group:';
$string['groupautoname'] = 'Team';

// Lock choice page.
$string['warning'] = 'Warning:';
$string['lockchoicewarning'] = 'Locking your choice will prevent YOU from making any further changes.  All group members must individually lock-in.  This is considered your FINAL acceptance.';
$string['lockchoiceheader'] = 'Lock Group Choice';
$string['lockchoicetitle'] = 'Lock Group Choice';
$string['lockgrouplink'] = 'Lock my group choice';
$string['lockchoice'] = 'Consent to choice';
$string['status'] = 'Status:';
$string['choicelocked'] = 'You have already locked in your group selection.';

// Add users page.
$string['adduserstogroup'] = 'Add Users to Group';
$string['groupmembers'] = 'Group members:';
$string['lockedmembers'] = 'Locked members:';
$string['groupplaceholder'] = 'Type a classmate\'s name';
$string['allowuserstojoin'] = 'Display group in searchable results for classmates to join';
$string['submitbutton'] = 'Submit';
$string['returnbutton'] = 'Back to Course';

// Javascript -> edit_skills_group.php.
$string['nomembers'] = 'ERROR: No group members added';
$string['nologin'] = 'ERROR: You are not logged in or your session has timed out.  Please refresh and login again.';
$string['badsesskey'] = 'ERROR: invalid sesskey (session idle too long).';
$string['groupupdatesuccess'] = 'Group successfully updated.';
$string['groupupdateerror'] = 'ERROR: Failure updating group.  Please refresh page.';
$string['notingroup'] = 'ERROR: You are not part of this group.  Return to course and attempt again.';
$string['toomanymembers'] = 'ERROR: Too many members in group';
$string['nogrouperror'] = 'ERROR: You are not part of a group';

// Join group page.
$string['joingroup'] = 'Join a Group';
$string['joingroupbutton'] = 'Join Group';
$string['refreshgroupsbutton'] = 'Refresh Groups';

// View group page.
$string['viewskillsgroup'] = 'View group';
$string['skillheader'] = 'Skill';
$string['skillcount'] = 'Team (Count of Strengths)';
$string['incomplete'] = 'Incomplete';

// Javascript -> join_skills_group.php.
$string['groupsloading'] = "Loading...";
$string['emptygroups'] = "No groups available to join.";
$string['groupsloaderror'] = "Error retrieving groups.";
$string['groupjoinsuccess'] = 'Successfully joined group.';
$string['groupjoinerror'] = 'ERROR: Failure joining group.  Please refresh page.';
$string['alreadyingroup'] = 'ERROR: You are already in a group.';

// Error messages.
$string['loginrequired'] = 'You must be logged in to use this page.';
$string['dberror'] = 'Error accessing the database';
$string['noaccess'] = 'You have no access to this page.  Please contact a system administrator if you believe this is an error.';
$string['groupingmissing'] = 'ERROR: No grouping was specified.';

// Logging URLS.
$string['skillsgroupcreated'] = 'create group';
$string['creategroupinfo'] = 'created group, groupid: ';
$string['skillsgroupleft'] = 'leave group';
$string['leavegroupinfo'] = 'leaving group, groupid: ';
$string['skillsgroupjoined'] = 'join group';
$string['joingroupinfo'] = 'joining group, groupid: ';
$string['skillsgroupedited'] = 'edit group';
$string['editgroupinfo'] = 'edit group, groupid: ';