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
 * English strings for iclickerregistration
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_iclickerregistration
 * @copyright  2015 Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'iClicker Registration';
$string['modulenameplural'] = 'iClicker Registrations';
$string['modulenameformatted'] = 'iClicker Registration';
$string['modulename_help'] = "The iClicker Registration activity module facilitates the process for linking specific ".
    "iClicker responses to the students in your course (this activity is not required for anonymous iClicker polling).

Students can register the iClicker remote ID they will be using in this course. Instructors are able to more easily ".
    "create the roster file required for synching with the iClicker software and are able to view a list of all ".
    "registered students in the course.

Please note that only one instance of this activity is required within a course to allow students to register their iClickers.";

$string['iclickerregistrationname'] = 'iClicker Registration activity name';
$string['iclickerregistrationname_help'] = 'Provide a name for your iClicker Registration activity here. Please note '.
    'that only one instance of this module is required within a course to allow student access to iClicker Registration.';
$string['iclickerregistration'] = 'iclickerregistration';
$string['pluginadministration'] = 'iclickerregistration administration';
$string['pluginname'] = 'iclickerregistration';

// Frontend texts.
$string['editbuttontext'] = 'Edit';
$string['updateinputplaceholdertext'] = 'iClicker ID';
$string['updatebuttontext'] = 'Update';
$string['cancelbuttontext'] = 'Cancel';
$string['instructionheadingtext'] = 'How to find your iClicker ID';
$string['instructiontext'] = 'For iClicker v1, find the iClicker ID behind the battery. For iClicker v2, find the iClicker ID '.
    'at the back. If you cannot see your clicker ID (ie. you have purchased a used clicker and the ID number has been rubbed off), '.
    'please come visit us in room 1-56 of the General Services Building so we can run a quick utility to determine the clicker ID';
$string['registrationinputplaceholdertext'] = 'iClicker ID';
$string['registrationbuttontext'] = 'Register';
$string['youriclickeridis'] = 'Your iClicker ID is:';
$string['noiclickeridregistered'] = 'No iClicker ID Registered';
$string['noregisterediclickerprompt'] = "You don't have a registered iClicker.";
$string['orderby'] = 'Order by:';
$string['idnumber'] = 'CCID';
$string['name'] = 'Name';
$string['iclickerid'] = 'iClicker ID';
$string['hideunregistered'] = 'Hide Unregistered';
$string['paginationnext'] = 'Next';
$string['paginationprevious'] = 'Previous';
$string['paginationfirst'] = 'First';
$string['paginationlast'] = 'Last';
$string['operations'] = 'Operations';
$string['deletebuttontext'] = 'Delete';
$string['unregistered'] = 'Unregistered';
$string['accessdeniedheadertext'] = 'Access Denied';
$string['accessdeniedmessage'] = "You have been prevented from doing action(s) that is/are not within your role.";
$string['registrationsuccess'] = 'iClicker ID registration success.';
$string['duplicateiclickeridinsamecourse'] = 'Your iClicker ID is registered to another user in this course. In courses '.
    'where iClicker registration is required, 2 students cannot use the same iClicker. Please ensure you have registered '.
    'the correct ID.';
$string['duplicateincourselabelglobal'] = 'Duplicate(s) in course(s): ';
$string['duplicateincourselabelcourse'] = 'Duplicate(s) in current course: ';
$string['enrolledusersiclickerinfo'] = 'Enrolled users iClicker information';
$string['invalidiclickerid'] = 'Invalid iClicker ID. Please check again.';
$string['courseconflictlistheader'] = 'iClicker ID is in conflict in the following course(s):';
$string['youraccountismanuallyenrolled'] = 'Your account is manually enrolled and cannot register an iClicker. '.
    'Contact eclass team via phone at (780) 492-9372 or email at eclass@ualberta.ca';
$string['noiclickerregistrations'] = "No iClicker registrations in the given course ID.";
$string['iclickerlegendheadertext'] = 'Legend(s):';
$string['iclickeridconflictlegendtext'] = 'Two or more students have registered the same iClicker ID';
$string['searchplaceholder'] = 'Enter Name, ccid, or iClicker ID to search';

$string['filterconflitcs'] = 'Filter Conflicts';
$string['teachertoolsheadertext'] = 'Tools';
$string['generateclassrosterbuttontext'] = 'Generate Class Roster';
$string['generateclassrotsterhelp'] = "For instructions on using this roster file, see the ".
"<a href='https://support.ctl.ualberta.ca/index.php?/Knowledgebase/Article/View/185/19/linking-iclicker-responses-to-students-registration' target='_blank''>following article</a>";

// Events.
$string['eventregistericlicker'] = 'iClicker ID registered.';
$string['eventediticlicker'] = 'iClicker ID edited.';
$string['eventdeleteiclicker'] = 'iClicker ID deleted.';
$string['eventaccessdenied'] = 'Access Denied.';
$string['unregisterbuttontext'] = 'Unregister';

// Delete confirmation dialog.
$string['deleteiclickerconfirmationdialogheader'] = 'Reset your iClicker ID';
$string['deleteiclickerconfirmationdialogbody'] = 'Are you sure you want to delete your iClicker ID? This action will '.
    'unassign your current iClicker ID from your CCID account.';