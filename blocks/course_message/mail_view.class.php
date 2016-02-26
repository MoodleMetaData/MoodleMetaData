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

defined('MOODLE_INTERNAL') || die();
require_once(dirname(__FILE__).'/../../config.php');
require_once('display_mail.class.php');
global $CFG;
require_once($CFG->dirroot.'/blocks/course_message/locallib.php');

/**
 * This is the view class that builds the mail view page.  There still is not
 * quite a perfect division between this and the model class.  Should be revisited
 * when possible.
 *
 * @package    block_course_message
 * @copyright  2013 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mail_view{
    /** This is the mail/thread to display */
    private $mail;
    /** ID of the mail */
    private $mailid;
    /** Folder the mail sits in (depends on user) */
    private $folder;

    public function __construct($mailtodisplay, $mailid, $folder) {
        $this->mail = $mailtodisplay;
        $this->mailid = $mailid;
        $this->folder = $folder;
    }

    public function display_mail() {
        // Write to the buffer, then echo at the end.
        $outputbuffer = '';
        // Write message id so we have access to it.
        $outputbuffer .= '<input type="hidden" id="replymailid" value="'.$this->mailid.'"/>';
        $outputbuffer .= "<div class='message_bar_subject'><h3><span class='label'>Message:
                          </span> {$this->mail->viewrecord->subject}</h3></div>";

        if ($this->mail->is_thread()) {
            $outputbuffer .= "<div id='topthread' style='display:none'>";
            foreach ($this->mail->threadmails as $threadmail) {
                if ($threadmail->id == $this->mailid) {
                    $outputbuffer .= "</div>";
                    $this->write_message_div($threadmail, $outputbuffer);
                    $outputbuffer .= "<div id='bottomthread' style='display:none'>";
                } else {
                    $this->write_message_div($threadmail, $outputbuffer);
                }
            }
            $outputbuffer .= "</div>";
        } else {
            $this->write_message_div($this->mail->viewrecord, $outputbuffer);
        }

        $this->display_bottom_buttons($this->mail->is_thread(), $outputbuffer);
        $this->mail->update_time_read();

        echo $outputbuffer;
    }

    /**
     * This function writes out the message div so the message can be viewed.
     *
     * @param object $record This is the DB record for the message to write out
     * @param string $outputbuffer The output html code will be written here.
     *
     */
    private function write_message_div($record,  & $outputbuffer) {
        global $DB, $USER;

        $timesent = $record->timesent;
        $time = userdate($timesent, '%H:%M');
        $dates = usergetdate($timesent);
        $userfrom = block_course_message_map_ids_to_names($record->useridfrom, 'inbox');
        $userto = block_course_message_map_ids_to_names($record->recipients, 'sent');

        $outputbuffer .= "<div class='message_div_bar'><div class='message_bar_to'><span class='label'>To:</span>
                          <span class='field'> $userto</span></div>";
        if ($record->carboncopy !== null) {
            $carboncopy = block_course_message_map_ids_to_names($record->carboncopy, 'sent');
            $outputbuffer .= "<div class='message_bar_to'><span class='label'>CC:</span>
                              <span class='field'> $carboncopy</span></div>";
        }
        $outputbuffer .= "<div class='message_bar_from'><span class='label'>From:</span><span class='field'> $userfrom</span>
                          </div><div class='message_bar_time'>$time $dates[month] $dates[mday]</div>";
        $outputbuffer .= "<div class='clear'></div></div>";

        $outputbuffer .= "<div class='message_container";
        if ($USER->id == $record->useridfrom) {
            $outputbuffer .= " usersent'>";
        } else {
            $outputbuffer .= " userrecieved'>";
        }
        // Message has already been cleaned when sent.
        $outputbuffer .= "<div class='message_div_body'>$record->message</div>";
        $this->process_attachment($record, $outputbuffer);
        // Close the message container div.
        $outputbuffer .= "</div>";
    }

    /**
     * This function displays any attachments for a message with corresponding DB record $record.
     *
     * This code comes from -> http://docs.moodle.org/dev/Using_the_File_API, go to the section
     * titled "Getting files from the user" and the "List area files" example.  I've made one
     * small change so the null files are not displayed.  This may break folders.
     *
     * @param object $record DB record corresponding to the message
     * @param string $outputbuffer The output html code will be written here.
     *
     */
    private function process_attachment($record,  & $outputbuffer) {
        global $CFG;

        if ($record->attachment != 0) {
            $context = context_course::instance($record->courseid);
            $out = array();
            $fs = get_file_storage();
            $files = $fs->get_area_files($context->id, BLOCK_CM_COMPONENT_NAME, BLOCK_CM_FILE_AREA_NAME, $record->attachment);

            foreach ($files as $file) {
                $filename = $file->get_filename();
                // Skip displaying the null files.
                if ($filename != '.') {
                    $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                            $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename(),
                            $forcedownload = true);
                    $out[] = html_writer::link($fileurl, $filename);
                }
            }

            // Strip out any <br> tags?
            $br = html_writer::empty_tag('br');
            $outputbuffer .= "<div id='attachments_in_message'>".implode($br, $out)."</div>";
        }
    }

    /**
     * This function displays the appropriate buttons at the bottom of the message
     *
     * @param int $parent denotes the parent of the current message (if non-zero, then threaded buttons are displayed)
     * @param string $outputbuffer The output html code will be written here.
     *
     */
    private function display_bottom_buttons($parent, &$outputbuffer) {

        $outputbuffer .= "<div class='message_div_buttons_bottom'>";

        if ($this->folder == 'inbox') {
            $outputbuffer .= "<button id='sendmailfromreply' type='button' class = 'inbox_button' style='display:none'>".
                              get_string('sendbutton', BLOCK_CM_LANG_TABLE) . "</button>
                              <button class='inbox_button' id='inlinereply' type='button'>".
                              get_string('replybutton', BLOCK_CM_LANG_TABLE) . "</button>
                              <button class='inbox_button' id='inlinereplyall' type='button'>".
                              get_string('replyallbutton', BLOCK_CM_LANG_TABLE) . "</button>
                              <button id='cancelreply' type='button' class = 'inbox_button' style='display:none'>".
                              get_string('cancelbutton', BLOCK_CM_LANG_TABLE) . "</button>";
        }
        if ($parent) {
            $outputbuffer .= "<button id='showthread' type='button' class='inbox_button'>".
                              get_string('viewthreadbutton', BLOCK_CM_LANG_TABLE) . "</button>
                              <button id='closethread' style='display:none' type='button' class = 'inbox_button'>".
                              get_string('closethreadbutton', BLOCK_CM_LANG_TABLE)."</button><div class='divider'></div>";
        }

        $outputbuffer .= "<button id='deletemail' type='button' class = 'inbox_button' style='display:show' float='right'>".
                          get_string('deletebutton', BLOCK_CM_LANG_TABLE)."</button>";
        $outputbuffer .= "<div class='clear'></div></div>";
    }
}