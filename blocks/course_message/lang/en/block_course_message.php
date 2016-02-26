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
 * @package    block_course_message
 * @category   block
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Course Mail';

// Capability labels.
$string['course_message:sendmail'] = 'Send course mail';
$string['course_message:viewmail'] = 'View course mail';
$string['course_message:receiveallinstructorcoursemail'] = 'Receive course mail sent to all instructors';
$string['course_message:receiveallstudentcoursemail'] = 'Receive course mail sent to all students';
$string['course_message:addinstance'] = 'Add a new course mail block';

// Events.
$string['eventmaildeleted'] = 'course_message mail deleted';
$string['eventmailsent'] = 'course_message mail sent';
$string['eventmailviewed'] = 'course_message mail viewed';

// Headings.
$string['composeheading'] = 'Compose Mail';
$string['inboxheading'] = 'Inbox';
$string['sentheading'] = 'Sent Mail';

// Labels.
$string['inboxlabel'] = 'Inbox';
$string['sentlabel'] = 'Sent';
$string['composelabel'] = 'Compose';
$string['settingslabel'] = 'Settings';
$string['subjectlabel'] = 'Subject';
$string['contactslabel'] = 'Type a contact or group';
$string['addcontactslabel'] = 'Add Contacts...';
$string['addattachlabel'] = 'Add An Attachment...';
$string['cancellabel'] = 'Cancel...';
$string['emailnewmail'] = 'Receive external email when new course mail received';
$string['copyofownmail'] = 'Receive external email copies of the mail I send';
$string['displaypreferencesetting'] = 'Load mail client on a new page';
$string['allinstructors'] = 'All instructors';
$string['allstudents'] = 'All students';
$string['fromlabel'] = 'From';
$string['datelabel'] = 'Date';
$string['tolabel'] = 'To';
$string['cclabel'] = 'CC';
$string['addcclabel'] = 'Add CC';

// Buttons.
$string['openinboxbutton'] = 'Inbox';
$string['addcontactsbutton'] = 'Add';
$string['submitbutton'] = 'Submit';
$string['replybutton'] = 'Reply';
$string['replyallbutton'] = 'Reply All';
$string['cancelbutton'] = 'Cancel';
$string['sendbutton'] = 'Send';
$string['viewthreadbutton'] = 'View Thread';
$string['closethreadbutton'] = 'Close Thread';
$string['deletebutton'] = 'Delete';

// Various strings used.
$string['unreadbartext'] = 'You have unread mail';
$string['discardmessagewarning'] = 'Are you sure you want to discard this mail?';
$string['nocontactswarning'] = 'You must add a contact or invalid contact.';
$string['nosubjectwarning'] = 'Are you sure you want to send this mail without a subject?';
$string['nomessagewarning'] = 'Are you sure you want to send this mail without a body?';
$string['errortext'] = 'There was an unknown error.';
$string['mailnologin'] = 'ERROR: You are not logged in or your session has timed out.  Please refresh and login again.';
$string['mailbadsesskey'] = 'ERROR: invalid sesskey (session idle too long).  Please refresh the page.';
$string['mailsuccess'] = 'Your mail was sent successfully.';
$string['mailnotsent'] = 'Mail failed to send.';
$string['maildberror'] = 'Mail could not be added to the database.';
$string['mailnoparent'] = 'Cannot find mail you are replying to.';
$string['deletewarning'] = 'Do you really want to delete this mail?';
$string['baduseridentification'] = 'Incorrect user identification please contact a system administrator.';
$string['guestnomail'] = 'Guest users cannot send mail.';
$string['usercannotsendmail'] = 'You are not allowed to send mail in this course.';
$string['usercannotviewmail'] = 'You are not allowed to view mail in this course.';
$string['sendingtitle'] = 'Sending mail';
$string['viewmailerror'] = 'Unable to load mail.';
$string['deletemailerror'] = 'Unable to delete mail.';
$string['loadsettingserror'] = 'Unable to load settings.';
$string['updatesettings'] = 'Email settings updated.';
$string['updatesettingserror'] = 'Unable to update settings.';
$string['cachewarning'] = 'Note: if this page does not load, please <a href = "http://www.wikihow.com/Clear-Your-Browser%27s-Cache">clear your cache</a>.';
$string['useinbox'] = 'Please click the "Inbox" button for additional functionality.';
$string['folderloading'] = "Loading...";
$string['emptyfolder'] = "No mail to display.";
$string['mailerror'] = "Error retrieving mail.";

// Mail footer.
$string['emailnotification'] = 'This is a notification email from eClass at the University of Alberta, please do not reply to this email.';
$string['emaillogin'] = 'To log into eClass at the UofA visit ';

// Setttings.
$string['attachmentsizelabel'] = 'Mail Attachment Size';
$string['attachmentsizedescription'] = 'Maximum size for attachments used in the mail tool';

