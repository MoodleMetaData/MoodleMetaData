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
 * This file outputs the html for the inbox iframe.
 *
 * This file writes out the full inbox iframe.  The iframe is broken into
 * two parts: leftContent and rightContent, while rightContent is further
 * subdivided into the compose view, inbox view, sent view, and settings
 * view.
 *
 * @package    block_course_message
 * @category   block
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../config.php');
require_once('attachment_form.class.php');
require_once('contact_list.class.php');
require_once('rich_text_form.class.php');
global $PAGE, $OUTPUT, $CFG, $USER, $COURSE;
require_once($CFG->dirroot.'/blocks/course_message/locallib.php');
require_once($CFG->dirroot.'/local/yuigallerylibs/module_info.php');
require_once($CFG->libdir.'/formslib.php');
// Login -> no check for sesskey, since you cannot do anything further without it.
$courseid = required_param("courseid", PARAM_INT);
require_login($courseid, false);

if (block_course_message_get_display_preference($USER->id) == BLOCK_CM_ON_PAGE) {
    $PAGE->set_pagelayout('base');
    $title = get_string('pluginname', BLOCK_CM_LANG_TABLE).": ".$COURSE->fullname;
    $PAGE->set_title($title);
    $PAGE->navbar->add($title, new moodle_url('/blocks/course_message/inbox.php', array('courseid' => $courseid)));
    $PAGE->set_url(new moodle_url('/blocks/course_message/inbox.php', array('courseid' => $courseid)));
} else {
    $PAGE->set_pagelayout('popup');
}
set_header();
echo $OUTPUT->header();

echo '<div id="fullWindow">';

echo '<div id="leftContent">';
display_left_menu();
echo '</div>';

echo '<div id="rightContent">';
$composedraftid = display_compose_view($courseid);
display_inbox_view();
display_sent_view();
$replydraftid = display_view_message($courseid);
display_settings();
draw_send_dialog();
draw_alert_dialog();
load_yui_modules($composedraftid, $replydraftid);
echo '</div>';

echo '</div>';

echo $OUTPUT->footer();

/**
 * This function set the page header -> JS/CSS includes.
 *
 */
function set_header() {
    global $PAGE,
    $COURSE,
    $USER;

    // Css.
    $PAGE->requires->css('/blocks/course_message/css/inbox.css');
    $PAGE->requires->css('/local/yuigallerylibs/gallery-multivalue-input/assets/skins/sam/gallery-multivalue-input.css');
    // Js files.
    $PAGE->requires->js_module(get_js_module_info('gallery-multivalue-input'));
    $PAGE->requires->js_module(get_js_module_info('gallery-datatable-selection'));
    $PAGE->requires->js_module(get_js_module_info('gallery-datatable-paginator'));
    $PAGE->requires->js_module(get_js_module_info('gallery-paginator-view'));
    // Language strings.
    $PAGE->requires->strings_for_js(array('nocontactswarning', 'discardmessagewarning', 'nosubjectwarning',
            'nomessagewarning', 'deletewarning', 'inboxlabel', 'fromlabel', 'subjectlabel', 'datelabel', 'tolabel',
            'viewmailerror', 'mailnotsent', 'deletemailerror', 'loadsettingserror', 'updatesettingserror',
            'allstudents', 'contactslabel', 'folderloading', 'emptyfolder', 'mailerror'), BLOCK_CM_LANG_TABLE);
}

/**
 * This function displays the left menu (compose|inbox|sent|settings).  It also
 * writes a few strings to the page so that we can use them in javascript.
 *
 */
function display_left_menu() {
    // TODO: remove the javascript:void(0) part and put the ID on the <li> directly.
    // TODO: then re-style the <li>'s so that they look like clickable links.
    echo '<button type="button" id="composeli">'.get_string('composelabel', BLOCK_CM_LANG_TABLE).'</button>';
    echo '<ul>';
    echo '<li> <a href="javascript:void(0);" id="inboxli">'.get_string('inboxlabel', BLOCK_CM_LANG_TABLE).'</a></li>';
    echo '<li> <a href="javascript:void(0);" id="sentli">'.get_string('sentlabel', BLOCK_CM_LANG_TABLE).'</a></li>';
    echo '<li> <a href="javascript:void(0);" id="settingsli">'.get_string('settingslabel', BLOCK_CM_LANG_TABLE).'</a></li></ul>';
}

/**
 * This function writes out the compose view by calling several more functions.
 *
 * @param int $courseid This is the course ID.
 * @return int The draft ID for the compose file picker.
 *
 */
function display_compose_view($courseid) {
    global $CFG,
    $USER;

    echo '<div id="composeview" style="display:none">';
    display_contacts($courseid);
    display_subject();
    display_send();
    $composedraftid = display_add_attachment($courseid, 'compose');
    display_body($courseid, 'compose');
    echo '</div>';

    return $composedraftid;
}

/**
 * This function is responsible for writing out the contacts selector.
 *
 * @param int $courseid This is the course ID.
 *
 */
function display_contacts($courseid) {
    global $USER;

    echo '<h2>'.get_string('composeheading', BLOCK_CM_LANG_TABLE).'</h2>';
    echo '<div id="composing" class="compose_bar">';
    echo '<span class="label">'.get_string('tolabel', BLOCK_CM_LANG_TABLE).':</span>';
    echo '<input type="text" id="contactfromcompose" placeholder="'.get_string('contactslabel', BLOCK_CM_LANG_TABLE).'">';
    echo '<div class="clear"></div>';
    echo '<span class="label">' . get_string('cclabel', BLOCK_CM_LANG_TABLE).':</span>';
    echo '<input type="text" id="ccfromcompose" placeholder="'.get_string('contactslabel', BLOCK_CM_LANG_TABLE).'">';
    echo '<div class="clear"></div>';
    echo '</div>';

}

/**
 * This function displays the subject bar on the compose form.
 *
 */
function display_subject() {
    echo '<div class="compose_bar">';
    echo '<span class="label">'.get_string('subjectlabel', BLOCK_CM_LANG_TABLE).':</span>';
    echo '<div class="yui3-multivalueinput-content">';
    echo '<input id="subjectfromcompose" class="yui3-input-adjust" type="text" placeholder="enter subject here" /></div>';
    echo '<div class="clear"></div>';
    echo '</div>';
}

/**
 * This function displays the send button on the compose form.
 *
 */
function display_send() {
    echo '<button type="button" id="sendmailfromcompose">'.get_string('sendbutton', BLOCK_CM_LANG_TABLE).'</button><br>';
}

/**
 * This function display the attachment information div information.  A hidden form
 * is created containing the draft ID so that it can be sent down with the message.
 *
 * @param int $courseid This is the course ID.
 * @return int The draft ID for the file picker that was added.
 *
 */
function display_add_attachment($courseid, $idmodifier) {
    global $PAGE;

    echo "<div  class='compose_bar'>
                <div id='openattachmentdivfrom$idmodifier'>";
    echo "		<a id='openattachmentfrom$idmodifier'>".get_string('addattachlabel', BLOCK_CM_LANG_TABLE)."</a>";
    echo "	</div>
                <div id='closeattachmentdivfrom$idmodifier' style='display:none'>";
    echo "		<a id='closeattachmentfrom$idmodifier'>".get_string('cancellabel', BLOCK_CM_LANG_TABLE)."</a>";
    echo "	</div>
                <div id='mformattachmentfrom$idmodifier' style='display:none'>";

    $context = context_course::instance($courseid);
    $af = new attachment_form($context, $idmodifier);
    echo $af->display(true);
    echo "	</div>
                <div class='clear'></div>
            </div>";

    return $af->get_draft_id();
}

/**
 * This function displays the message body area on the compose form.
 *
 */
function display_body($courseid, $idmodifier) {
    echo "<div style='float: left'>";
    $context = context_course::instance($courseid);
    $rtf = new rich_text_form($context, $idmodifier);
    echo $rtf->display(true);
    echo "</div>";
}

/**
 * This function sets up the inbox view.  Most of the work (i.e., the data gathering)
 * is done from within javascript.
 *
 */
function display_inbox_view() {
    echo '<div id="inboxview"><h2>'.get_string('inboxheading', BLOCK_CM_LANG_TABLE).'</h2>';
    echo '<p class="cachehelp">'.get_string('cachewarning', BLOCK_CM_LANG_TABLE).'</p>';
    echo '<div id="inboxmail"></div><div id="inboxpaginator" class="yui3-pagview-bar"></div></div>';
    echo '<script type="text/x-template" id="paginator-template">
                <button data-pglink="first" class="{pageLinkClass}" title="First Page">First</button>
                <button data-pglink="prev" class="{pageLinkClass}" title="Prior Page">Prev</button>
                {pageLinks}
                <button data-pglink="next" class="{pageLinkClass}" title="Next Page">Next</button>
                <button data-pglink="last" class="{pageLinkClass}" title="Last Page">Last</button>
            </script>';
}

/**
 * This function sets up the sent messages view.  Most of the work (i.e., the data
 * gathering) is done from within javascript.
 *
 */
function display_sent_view() {
    echo '<div id="sentview" style="display:none"><h2>'.get_string('sentheading', BLOCK_CM_LANG_TABLE).
         '</h2><div id="sentmail"></div><div id="sentpaginator" class="yui3-pagview-bar"></div></div>';
}

/**
 * This function sets up the view message frame.  The content of the view message
 * frame is loaded dynamically, but the reply tools (initially hidden) are static
 * and setup below.
 *
 * @return int The draft ID for the compose file picker.
 *
 */
function display_view_message($courseid) {
    echo '<div id="viewmail"></div>';
    // Reply field is beneath.
    echo '<div id="newreply" style="display:none">';
    echo '<div class="compose_bar"><span class="label">'.get_string('addcclabel', BLOCK_CM_LANG_TABLE).':</span>';
    echo '<input type="text" id="ccfromreply" placeholder="'.get_string('contactslabel', BLOCK_CM_LANG_TABLE).'">';
    echo '<div class="clear"></div></div>';
    $replydraftid = display_add_attachment($courseid, 'reply');
    display_body($courseid, 'reply');
    echo '</div>';

    return $replydraftid;
}

/**
 * This function draws the settings view.  I think I should make it a little
 * bigger.
 *
 */
function display_settings() {
    global $USER;

    echo '<div id="settingsview" style="display:none">';
    echo '<h2>'.get_string('settingslabel', BLOCK_CM_LANG_TABLE).'</h2>';
    echo '<div id="sendemailsetting">';
    // Incoming Mail Setting.
    echo "<input type='checkbox' id='inboxemailsetting'";
    if (block_course_message_get_mail_preference('inbox', $USER->id)) {
        echo "checked='checked' /> &nbsp";
    } else {
        echo " /> &nbsp";
    }
    echo get_string('emailnewmail', BLOCK_CM_LANG_TABLE),
    ' ';
    echo '<br>';
    // Outgoing Mail Setting.
    echo "<input type='checkbox' id='sentemailsetting'";
    if (block_course_message_get_mail_preference('sent', $USER->id)) {
        echo "checked='checked' /> &nbsp";
    } else {
        echo " /> &nbsp";
    }
    echo get_string('copyofownmail', BLOCK_CM_LANG_TABLE),
    ' ';
    echo '<br><br>'.'<button id="submitsettings" type="button">'.get_string('submitbutton', BLOCK_CM_LANG_TABLE).'</button>';
    echo '</div></div>';
}

/**
 * This function draws the send box that comes up to let the user know
 * that their message is being processed.  Sometimes messages with attached
 * files take awhile, so this is quite helpful.
 *
 */
function draw_send_dialog() {
    echo '<div style="display: none" id="senddialog">';
    echo get_string('sendingtitle', BLOCK_CM_LANG_TABLE).'...';
    echo '</div>';
}

/**
 * This function draws the YUI alert modal dialog that displays feedback to
 * the user.
 *
 */
function draw_alert_dialog() {
    echo '<div id="alertpanel"><div id="alerttext"></div></div>';
}

/**
 * This function loads the YUI modules that I have written.  I've elected to
 * load these last so that the draftids can be passed directly to JS.
 *
 */
function load_yui_modules($composedraftid, $replydraftid) {
    global $PAGE,
    $COURSE,
    $USER;

    // TODO: it would be nice to put this code in its own function.
    $contactlist = new contact_list($COURSE->id, $USER->id);
    $groups = array();
    $groupids = array();
    $contacts = array();
    $contactids = array();
    $contactlist->contacts_as_array($contacts, $contactids, $groups, $groupids);
    $editor = block_course_message_get_editor_preference($USER->id);

    $PAGE->requires->yui_module('moodle-block_course_message-mail_lib', '(function(){})');
    $PAGE->requires->yui_module('moodle-block_course_message-inbox', 'M.block_course_message.init_inbox',
                                array(array('courseid' => $COURSE->id,
                                            'contacts' => $contacts,
                                            'contactids' => $contactids,
                                            'groups' => $groups,
                                            'groupids' => $groupids,
                                            'folder' => 'inbox',
                                            'editor' => $editor,
                                            'composedraftid' => $composedraftid,
                                            'replydraftid' => $replydraftid)));
}