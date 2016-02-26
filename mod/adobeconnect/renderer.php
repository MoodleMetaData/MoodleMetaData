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
 * @package mod
 * @subpackage adobeconnect
 * @author Akinsaya Delamarre (adelamarre@remote-learner.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir.'/tablelib.php');

class mod_adobeconnect_renderer extends plugin_renderer_base {

    /**
     * Returns HTML to display the meeting details
     * @param object $meetingdetail
     * @param int  $cmid
     * @param int $groupid
     * @return string
     */
    public function display_meeting_detail ($meetingdetail, $cmid, $groupid = 0) {

        $target = new moodle_url('/mod/adobeconnect/view.php');

        $attributes = array('method' => 'POST', 'target' => $target);

        $html = html_writer::start_tag('form', $attributes);

        // Display the main field set.
        $html .= html_writer::start_tag('div', array('class' => 'aconfldset'));
        // Display the meeting name field and value.
        $html .= html_writer::start_tag('div', array('class' => 'aconmeetinforow'));
        // Print meeting name label.
        $html .= html_writer::start_tag('div', array('class' => 'aconlabeltitle', 'id' => 'aconmeetnametitle'));
        $html .= html_writer::tag('label', get_string('meetingname', 'adobeconnect'), array('for' => 'lblmeetingnametitle'));
        $html .= html_writer::end_tag('div');
        // Print meeting name value.
        $html .= html_writer::start_tag('div', array('class' => 'aconlabeltext', 'id' => 'aconmeetnametxt'));
        $html .= html_writer::tag('label', format_string($meetingdetail->name), array('for' => 'lblmeetingname'));
        $html .= html_writer::end_tag('div');

        $html .= html_writer::end_tag('div');

        // Display the meeting url and port if the user has the capabilities.
        if ( !empty($meetingdetail->url) ) {

            $param = array('class' => 'aconmeetinforow');
            $html .= html_writer::start_tag('div', $param);

            // Print meeting URL label.
            $param = array('class' => 'aconlabeltitle', 'id' => 'aconmeeturltitle');
            $html .= html_writer::start_tag('div', $param);
            $param = array('for' => 'lblmeetingurltitle');
            $html .= html_writer::tag('label', get_string('meeturl', 'adobeconnect'), $param);
            $html .= html_writer::end_tag('div');

            // Print meeting URL value.
            $param = array('class' => 'aconlabeltext', 'id' => 'aconmeeturltext');
            $html .= html_writer::start_tag('div', $param);
            $param = array('for' => 'lblmeetingurl');
            $html .= html_writer::tag('label', $meetingdetail->url, $param);
            $html .= html_writer::end_tag('div');

            $html .= html_writer::end_tag('div');

        }

        if (!empty($meetingdetail->servermeetinginfo)) {
            $param = array('class' => 'aconmeetinforow');
            $html .= html_writer::start_tag('div', $param);

            // Print meeting URL label.
            $param = array('class' => 'aconlabeltitle', 'id' => 'aconmeeturlinfo');
            $html .= html_writer::start_tag('div', $param);
            $param = array('for' => 'lblmeetingurlinfo');
            $html .= html_writer::tag('label', get_string('meetinfo', 'adobeconnect'), $param);
            $html .= html_writer::end_tag('div');

            // Print meeting URL value.
            $param = array('class' => 'aconlabeltext', 'id' => 'aconmeeturlinfotext');
            $html .= html_writer::start_tag('div', $param);
            $param = array('target' => '_blank');

            $html .= html_writer::link($meetingdetail->servermeetinginfo, get_string('meetinfotxt', 'adobeconnect'), $param);
            $html .= html_writer::end_tag('div');

            $html .= html_writer::end_tag('div');

        }

        // Print meeting start time label and value.
        $param = array('class' => 'aconmeetinforow');
        $html .= html_writer::start_tag('div', $param);

        // Print meeting start time label.
        $param = array('class' => 'aconlabeltitle', 'id' => 'aconmeetstarttitle');
        $html .= html_writer::start_tag('div', $param);
        $param = array('for' => 'lblmeetingurl');
        $html .= html_writer::tag('label', get_string('meetingstart', 'adobeconnect'), $param);
        $html .= html_writer::end_tag('div');

        // Print meeting start time value.
        $param = array('class' => 'aconlabeltext', 'id' => 'aconmeetstarttxt');
        $html .= html_writer::start_tag('div', $param);
        $param = array('for' => 'lblmeetingstart');
        $html .= html_writer::tag('label', $meetingdetail->starttime, $param);
        $html .= html_writer::end_tag('div');

        $html .= html_writer::end_tag('div');

        // Print the meeting end time label and value.
        $param = array('class' => 'aconmeetinforow');
        $html .= html_writer::start_tag('div', $param);

        // Print meeting end time label.
        $param = array('class' => 'aconlabeltitle', 'id' => 'aconmeetendtitle');
        $html .= html_writer::start_tag('div', $param);
        $param = array('for' => 'lblmeetingendtitle');
        $html .= html_writer::tag('label', get_string('meetingend', 'adobeconnect'), $param);
        $html .= html_writer::end_tag('div');

        // Print meeting end time value.
        $param = array('class' => 'aconlabeltext', 'id' => 'aconmeetendtxt');
        $html .= html_writer::start_tag('div', $param);
        $param = array('for' => 'lblmeetingend');
        $html .= html_writer::tag('label', $meetingdetail->endtime, $param);
        $html .= html_writer::end_tag('div');

        $html .= html_writer::end_tag('div');

        // Print meeting summary label and value.
        $param = array('class' => 'aconmeetinforow');
        $html .= html_writer::start_tag('div', $param);

        // Print meeting summary label.
        $param = array('class' => 'aconlabeltitle', 'id' => 'aconmeetsummarytitle');
        $html .= html_writer::start_tag('div', $param);
        $param = array('for' => 'lblmeetingsummarytitle');
        $html .= html_writer::tag('label', get_string('meetingintro', 'adobeconnect'), $param);
        $html .= html_writer::end_tag('div');

        // Print meeting summary value.
        $param = array('class' => 'aconlabeltext', 'id' => 'aconmeetsummarytxt');
        $html .= html_writer::start_tag('div', $param);
        $param = array('for' => 'lblmeetingsummary');
        $html .= html_writer::tag('label', format_module_intro('adobeconnect', $meetingdetail, $cmid), $param);
        $html .= html_writer::end_tag('div');

        $html .= html_writer::end_tag('div');

        // Print hidden elements.
        $param = array('type' => 'hidden', 'name' => 'id', 'value' => $cmid);
        $html .= html_writer::empty_tag('input', $param);
        $param = array('type' => 'hidden', 'name' => 'group', 'value' => $groupid);
        $html .= html_writer::empty_tag('input', $param);
        $param = array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey());
        $html .= html_writer::empty_tag('input', $param);

        // Print buttons.
        $param = array('class' => 'aconbtnrow');
        $html .= html_writer::start_tag('div', $param);

        if ( $meetingdetail->canjoin ) {
            $html .= html_writer::start_tag('div', array('class' => 'aconbtnjoin'));

            $targetparam = array('id' => $cmid, 'sesskey' => sesskey(), 'groupid' => $groupid);
            $target = new moodle_url('/mod/adobeconnect/join.php', $targetparam);

            $param = array( 'type' => 'button', 'value' => get_string('joinmeeting', 'adobeconnect'),
                'name' => 'btnname',
                'onclick' => 'window.open(\''.$target->out(false).'\', \'btnname\',
                        \'menubar=0,location=0,scrollbars=0,resizable=0,width=1024,height=900\', 0);');
            $html .= html_writer::empty_tag('input', $param);
            $html .= html_writer::end_tag('div');
        }

        if ($meetingdetail->ishost) {
            $param = array('class' => 'aconbtnroles');
            $html .= html_writer::start_tag('div', $param);
            $param = array('type' => 'submit',
                'value' => get_string('selectparticipants', 'adobeconnect'),
                'name' => 'participants'
            );
            $html .= html_writer::empty_tag('input', $param);
            $html .= html_writer::end_tag('div');
        }

        $html .= html_writer::end_tag('div');

        $html .= html_writer::end_tag('div');
        $html .= html_writer::end_tag('form');

        return $html;
    }


    public function display_meeting_help($meetingdetail) {
        global $OUTPUT;
        $html = '';

        $links = array();

        if ($meetingdetail->ishost) {
            // Information and help links for adobe connect.
            if ($link1 = get_config('mod_adobeconnect', 'gettingstarted_host')) {
                $links[] = html_writer::link($link1, get_string('info_gettingstarted_host', 'adobeconnect'));
            }
            if ($link2 = get_config('mod_adobeconnect', 'troubleshooting_host')) {
                $links[] = html_writer::link($link2, get_string('info_troubleshooting_host', 'adobeconnect'));
            }
            if ($link3 = get_config('mod_adobeconnect', 'upcomingtraining_host')) {
                $links[] = html_writer::link($link3, get_string('info_upcomingtraining_host', 'adobeconnect'));
            }
            if ($link4 = get_config('mod_adobeconnect', 'outageschedule_host')) {
                $links[] = html_writer::link($link4, get_string('info_outageschedule_host', 'adobeconnect'));
            }

        } else if ($meetingdetail->canjoin) {
            if ($link5 = get_config('mod_adobeconnect', 'gettingstarted_particpant')) {
                $links[] = html_writer::link($link5, get_string('info_gettingstarted_particpant', 'adobeconnect'));
            }
            if ($link6 = get_config('mod_adobeconnect', 'troubleshooting_particpant')) {
                $links[] = html_writer::link($link6, get_string('info_troubleshooting_particpant', 'adobeconnect'));
            }
        }

        if (!empty($links)) {
            $html .= $OUTPUT->box_start('generalbox', 'links');
            $html .= html_writer::tag('h3', get_string('info_hdr', 'adobeconnect')) . html_writer::alist($links);
            $html .= $OUTPUT->box_end();
        }
        return $html;
    }

    /**
     * @param $cmid int Course module id
     * @param $groupid int Current group id
     * @param $recording_scoid int Adobe connect recording sco-id
     * @param $mode string Recording mode: edit, offline
     * @return string  html string of the button
     */
    private function recording_button($value, $cmid, $groupid, $recordingscoid, $mode, $small=false) {
        // Build edit button.
        $button = html_writer::start_tag('div', array('class' => 'aconbtnjoin'));

        $targetparam = array( 'id' => $cmid,
            'sesskey' => sesskey(),
            'groupid' => $groupid,
            'recording' => $recordingscoid,
            'mode' => $mode);

        $sizeparam = 'width=1200,height=900';

        if ($small) {
            $sizeparam = 'width=960,height=800';
        }

        $target = new moodle_url('/mod/adobeconnect/recording.php', $targetparam);

        $buttonparam = array('type' => 'button',
            'value' => $value,
            'name' => 'btnname',
            'onclick' => 'window.open(\''.$target->out(false).'\', \'btnname\',
                          \'menubar=0,location=0,toolbar=0,status=0,scrollbars=0,resizable=0,'.$sizeparam.'\', 0);',
        );
        $button .= html_writer::empty_tag('input', $buttonparam);
        $button .= html_writer::end_tag('div');
        return $button;
    }

    /** This function outpus HTML markup with links to Connect meeting recordings.
     * If a valid groupid is passed it will only display recordings that
     * are a part of the group
     *
     * @param object - $meetingdetail
     * @param array - 2d array of recorded meeting and meeting details
     * @param int - course module id
     * @param int - group id
     * @param int - source sco id, used to filter meetings
     *
     * @return string - HTML markup, links to recorded meetings
     */
    public function display_meeting_recording($meetingdetail, $recordings, $cmid, $groupid, $sourcescoid) {
        global $CFG, $DB, $PAGE, $OUTPUT;
        $PAGE->requires->js_init_call('M.mod_adobeconnect.checkbox');
        $html       = '';
        $protocol   = 'http://';
        $port       = ''; // Include the port number only if it is a port other than 80 or 443.

        if (!empty($CFG->adobeconnect_port) and 80 != $CFG->adobeconnect_port and 443 != $CFG->adobeconnect_port) {
            $port = ':' . $CFG->adobeconnect_port;
        }

        if (isset($CFG->adobeconnect_https) and (!empty($CFG->adobeconnect_https))) {
            $protocol = 'https://';
        }

        // Display the meeting name field and value.
        $param = array('id' => 'aconfldset2', 'class' => 'aconfldset');
        $html .= html_writer::start_tag('div', $param);

        $html .= html_writer::tag('h3', get_string('recordinghdr', 'adobeconnect'), $param);

        $param = array('class' => 'aconrecording');
        $html .= html_writer::start_tag('div', $param);

        $hidden = $DB->get_fieldset_select('adobeconnect_recordings', 'recordingid', ' hidden = ? ', array(1));

        if (! $cm = get_coursemodule_from_id('adobeconnect', $cmid)) {
            print_error('Course Module ID was incorrect');
        }
        if (! $course = $DB->get_record('course',  array('id' => $cm->course))) {
            print_error('Course is misconfigured');
        }

        $contextcourse = context_course::instance($course->id);

        foreach ($recordings as $key => $recordinggrp) {
            if ( !empty($recordinggrp) ) {

                $table = new html_table();
                $table->attributes = array( 'name' => 'recordingtable_'.$key, 'class' => 'generaltable aconrecordingtable');
                $table->head = array(
                    'name' => get_string('recordingname', 'adobeconnect'),
                    'date' => get_string('recordingdate', 'adobeconnect'),
                    'duration' => get_string('recordingduration', 'adobeconnect'),
                );

                if ($meetingdetail->ishost) {
                    $checkbox = html_writer::tag('input', null,
                        array( 'type' => 'checkbox',
                            'name' => 'sco-all-'.$key,
                            'value' => 'all')
                    );
                    array_splice($table->head, 0 , 0, array('checkbox' => $checkbox));
                    array_splice($table->head, 2 , 0, array('url' => get_string('recordingurl', 'adobeconnect'),
                        'access' => get_string('recordingaccess', 'adobeconnect'),
                        'edit' => get_string('recordingedit', 'adobeconnect'),
                        'offline' => get_string('recordingoffline', 'adobeconnect'),
                        ));
                }

                if (has_capability('report/log:view', $contextcourse)) {
                    array_splice($table->head, 8 , 0, array('viewed' => get_string('recordingviewed', 'adobeconnect')));
                }

                foreach ($recordinggrp as $recordingscoid => $recording) {

                    $hiddenlink = '';
                    if ($recording->sourcesco != $sourcescoid || empty($recording->enddate) ||
                        empty($recording->startdate) || empty($recording->name) || empty($recording->duration)) {
                        continue;
                    }

                    if (in_array($recordingscoid , $hidden)) {
                        if ($meetingdetail->ishost) {
                            $hiddenlink = 'recording_hidden';
                        } else {
                            continue;
                        }
                    }

                    $row = array( 'name' => '', 'date' => '', 'duration' => '' );

                    // Host controls and info.
                    if ($meetingdetail->ishost) {
                        $rowcheckbox = html_writer::tag('input', null, array( 'type' => 'checkbox',
                            'name' => 'scoid[]',
                            'value' => $recordingscoid));

                        array_splice($row, 0 , 0, array('checkbox' => $rowcheckbox));
                        $access = get_string('recordingaccess_private', 'adobeconnect');
                        $url = '';
                        // Build permissions button.
                        if (!empty($recording->public)) {
                            $access = get_string('recordingaccess_public', 'adobeconnect');
                            $link = $protocol.$CFG->adobeconnect_meethost.$port.$recording->url;
                            $url = html_writer::tag('label', $link);

                            $recording->description;
                        }

                        array_splice($row, 2 , 0, array('url' => $url,
                            'access' => $access,
                            'edit' => $this->recording_button(get_string('recordingeditbutton', 'adobeconnect'),
                                            $cmid, $groupid, $recordingscoid, 'edit_recording', true),
                            'offline' => $this->recording_button(get_string('recordingofflinebutton', 'adobeconnect'),
                                $cmid, $groupid, $recordingscoid, 'offline'),
                            ));
                    }

                    if (has_capability('report/log:view', $contextcourse)) {
                        array_splice($row, 8 , 0, array(
                            'viewed' => $this->recording_button(get_string('recordingviewedbutton', 'adobeconnect'),
                            $cmid, $groupid, $recordingscoid, 'log_recording', true)));
                    }

                    $name = html_entity_decode($recording->name);
                    $targetparam = array( 'id' => $cmid,
                        'sesskey' => sesskey(),
                        'groupid' => $groupid,
                        'recording' => $recordingscoid,
                        'mode' => 'normal' );
                    $target = new moodle_url('/mod/adobeconnect/recording.php', $targetparam);
                    $param = array('target' => '_blank',
                                    'onclick' => 'window.open(\''.$target->out(false).'\', \'btnname\',
                                       \'menubar=0,location=0,toolbar=0,scrollbars=0,resizable=0,width=1024,height=900\', 0);',
                                    'class' => $hiddenlink,
                                    'title' => $recording->description);
                    $row['name'] = html_writer::link('', format_string($name), $param);

                    // Handle empty start date.
                    if (empty($recording->startdate)) {
                        $row['date'] = get_string('recordingemptyvalue', 'adobeconnect');
                    } else {
                        $row['date'] = userdate(strtotime($recording->startdate));
                    }
                    // Handle empty duration.
                    if (empty($recording->duration)) {
                        $row['duration'] = get_string('recordingemptyvalue', 'adobeconnect');
                    } else {
                        $row['duration'] = gmdate("H:i:s", $recording->duration);
                    }

                    $table->data[] = $row;
                }

                // Output the table.
                if ( !empty($table->data) ) {
                    $target = new moodle_url('/mod/adobeconnect/view.php', array('id' => $cmid));

                    $attributes = array('method' => 'POST', 'action' => $target);

                    $html .= html_writer::start_tag('form', $attributes);

                    // Show host editing controls.
                    if ($meetingdetail->ishost) {

                        $pribtnparam = array('type' => 'submit',
                            'value' => get_string('recordingprivatebutton', 'adobeconnect'),
                            'name' => 'btnprivate',
                        );
                        $privatebtn = html_writer::tag('div', html_writer::empty_tag('input', $pribtnparam),
                            array('class' => 'aconbtn'));

                        $pubbtnparam = array('type' => 'submit',
                            'value' => get_string('recordingpublicbutton', 'adobeconnect'),
                            'name' => 'btnpublic',
                        );

                        $publicbtn = html_writer::tag('div', html_writer::empty_tag('input', $pubbtnparam),
                            array('class' => 'aconbtn'));

                        $hidebtnparam = array('type' => 'submit',
                            'value' => get_string('recordinghidebutton', 'adobeconnect'),
                            'name' => 'btnhide',
                        );

                        $hidebtn = html_writer::tag('div', html_writer::empty_tag('input', $hidebtnparam),
                            array('class' => 'aconbtn'));

                        $showbtnparam = array('type' => 'submit',
                            'value' => get_string('recordingshowbutton', 'adobeconnect'),
                            'name' => 'btnshow',
                        );

                        $showbtn = html_writer::tag('div', html_writer::empty_tag('input', $showbtnparam),
                            array('class' => 'aconbtn'));

                        $helpicon = $OUTPUT->help_icon('recording', 'adobeconnect',
                            get_string('recordinghelpbutton', 'adobeconnect'));

                        $html .= html_writer::tag('div', $privatebtn.$publicbtn.$hidebtn.$showbtn.$helpicon,
                            array('class' => 'aconbtnrow aconrecordingbtnrow'));
                    }
                    $html .= html_writer::table($table);
                    // Print hidden elements.
                    $html .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $cmid));
                    $html .= html_writer::empty_tag('input',  array('type' => 'hidden', 'name' => 'group', 'value' => $groupid));
                    $html .= html_writer::empty_tag('input',  array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));

                    $html .= html_writer::end_tag('form');
                }
            }
        }
        $html .= html_writer::end_tag('div'); // DIV aconrecording.
        $html .= html_writer::end_tag('div'); // DIV aconfldset2.
        return $html;
    }

    public function display_no_groups_message() {
        $html = html_writer::tag('p', get_string('usergrouprequired', 'adobeconnect'));
        return $html;
    }

    public function display_edit_recording($recscoid, $recording, $cmid, $groupid, $meetingname) {
        global $PAGE;
        $PAGE->requires->js_init_call('M.mod_adobeconnect.recording');

        if (empty($recording->startdate)) {
            $startdate = get_string('recordingemptyvalue', 'adobeconnect');
        } else {
            $startdate = userdate(strtotime($recording->startdate), get_string('strftimedatetimeshort', 'langconfig'));
        }

        if (empty($recording->enddate)) {
            $enddate = get_string('recordingemptyvalue', 'adobeconnect');
        } else {
            $enddate = userdate(strtotime($recording->enddate), get_string('strftimedatetimeshort', 'langconfig'));
        }
        $html  = html_writer::start_tag('div', array('id' => 'recEditArea'));
        $html .= html_writer::tag('h3', get_string('recordingedit_title', 'adobeconnect'));

        // Print meeting name label.
        $html .= html_writer::tag('label', get_string('meetingname', 'adobeconnect'), array('class' => 'rectitle'));
        // Print meeting name value.
        $html .= html_writer::tag('span', format_string($meetingname));
        $html .= html_writer::empty_tag('br');
        // Print recording dates label.
        $html .= html_writer::tag('label', get_string('recordingedit_dates', 'adobeconnect'),  array('class' => 'rectitle'));
        // Print recording dates value.
        $html .= html_writer::tag('span', $startdate.' - '.$enddate, array( 'id' => 'recDates'));
        $html .= html_writer::empty_tag('br');

        // Print form.
        $target = new moodle_url('/mod/adobeconnect/recording.php');
        $attributes = array('id' => 'recEditForm', 'method' => 'POST', 'action' => $target);
        $html .= html_writer::start_tag('form', $attributes);
        // Print recording name label.
        $html .= html_writer::tag('label', get_string('recordingedit_name', 'adobeconnect'), array('class' => 'rectitle'));
        // Print recording name value.
        $html .= html_writer::empty_tag('input', array('type' => 'text', 'id' => 'name',
            'name' => 'name', 'value' => $recording->name));
        $html .= html_writer::empty_tag('br');
        // Print recording description label.
        $html .= html_writer::tag('label', get_string('recordingedit_description', 'adobeconnect'), array('class' => 'rectitle'));
        // Print recording description value.
        $html .= html_writer::tag('textarea', $recording->description, array('type' => '', 'id' => 'description',
            'name' => 'description', 'rows' => 10));

        // Hidden info for post requests.
        $html .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $cmid));
        $html .= html_writer::empty_tag('input',  array('type' => 'hidden', 'name' => 'groupid', 'value' => $groupid));
        $html .= html_writer::empty_tag('input',  array('type' => 'hidden', 'name' => 'recording', 'value' => $recscoid));
        $html .= html_writer::empty_tag('input',  array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
        $html .= html_writer::empty_tag('input',  array('type' => 'hidden', 'name' => 'mode', 'value' => 'update_recording'));

        // Button.
        $html .= html_writer::empty_tag('br');
        $html .= $this->recording_button(get_string('recordingeditadobebutton', 'adobeconnect'),
            $cmid, $groupid, $recscoid, 'edit');

        // Print the response message.
        $html .= html_writer::start_tag('div', array('class' => 'recResponseArea'));
        $html .= html_writer::empty_tag('img', array('id' => 'loading',
                'class' => 'loader loading_hidden',
                'src' => new moodle_url('/mod/adobeconnect/pix/loader.gif'))
        );
        $html .= html_writer::tag('span', '', array( 'id' => 'recResponse'));
        $html .= html_writer::end_tag('div');

        $html .= html_writer::start_tag('div', array('class' => 'aconbtn', 'style' => 'float:right;'));
        $savebtnparam = array('type' => 'submit',
                            'value' => get_string('recordingsavebutton', 'adobeconnect'),
                            'name' => 'btnshow',
        );
        $html .= html_writer::empty_tag('input', $savebtnparam);
        $cancelbtnparam = array('type' => 'button',
            'value' => get_string('recordingcancelbutton', 'adobeconnect'),
            'onclick' => 'window.close();',
            'name' => 'btnshow',
        );
        $html .= html_writer::empty_tag('input', $cancelbtnparam);
        $html .= html_writer::end_tag('div');

        $html .= html_writer::end_tag('form');
        $html .= html_writer::end_tag('div');
        return $html;
    }


    public function display_usersviewed_recording($recscoid, $cmid, $context, $recording) {
        global $DB, $OUTPUT, $USER, $CFG;

        if (!empty($recscoid)) {
            if (! $cm = get_coursemodule_from_id('adobeconnect', $cmid)) {
                print_error('Course Module ID was incorrect');
            }
            if (! $course = $DB->get_record('course',  array('id' => $cm->course))) {
                print_error('Course is misconfigured');
            }

            list($esql, $params) = get_enrolled_sql($context);
            $joins = array("FROM {user} u");
            $wheres = array();

            $logsql = "SELECT DISTINCT userid  FROM {adobeconnect_watched} where instanceid = $cmid and scoid = $recscoid";

            $select = "SELECT u.id, u.username, u.firstname, u.lastname,
                      u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename,
                      u.email, u.picture,
                      u.lang, u.maildisplay, u.imagealt";
            $joins[] = "JOIN ($esql) e ON e.id = u.id"; // Course enrolled users only.
            $joins[] = "JOIN ($logsql) r ON r.userid = u.id"; // Course enrolled users only.
            $params['courseid'] = $course->id;

            // Performance hacks - we preload user contexts together with accounts.
                $tablealias = 'ctx';
                $contextlevel = CONTEXT_USER;
                $joinon = 'u.id';
                $ccselect = ", " . context_helper::get_preload_record_columns_sql($tablealias);
                $ccjoin = "LEFT JOIN {context} $tablealias ON ($tablealias.instanceid = $joinon
                          AND $tablealias.contextlevel = $contextlevel)";

            $select .= $ccselect;
            $joins[] = $ccjoin;

            $table = new flexible_table('user-recordings-viewed'.$cmid);

            $tablecolumns = array('userpic', 'fullname', 'username');
            $tableheaders = array(get_string('userpic'), get_string('fullnameuser'), get_string('username'));

            $table->define_columns($tablecolumns);
            $table->define_headers($tableheaders);

            $table->set_attribute('cellspacing', '0');
            $table->set_attribute('class', 'generaltable generalbox');

            $table->define_baseurl('');
            $table->setup();

            $from = implode("\n", $joins);
            if ($wheres) {
                $where = "WHERE " . implode(" AND ", $wheres);
            } else {
                $where = "";
            }
            $sort = ' ORDER BY u.firstname, u.lastname, u.username';

            $userrows = $DB->get_recordset_sql("$select $from $where $sort", $params);

            echo html_writer::start_tag('div', array('id' => 'recEditArea'));
            echo html_writer::tag('h3', get_string('recordinglog_title', 'adobeconnect'));
            echo html_writer::tag('p', get_string('recordinglog_summary', 'adobeconnect', $recording->name));
            echo html_writer::start_tag('div', array('class' => 'aconbtn', 'style' => ''));
            $cancelbtnparam = array('type' => 'button',
                'value' => get_string('recordingcancelbutton', 'adobeconnect'),
                'onclick' => 'window.close();',
                'name' => 'btnshow',
            );
            echo html_writer::empty_tag('input', $cancelbtnparam);
            echo html_writer::end_tag('div');
            if (count($userrows) > 0) {
                $usersprinted = array();
                foreach ($userrows as $user) {
                    if (in_array($user->id, $usersprinted)) { // Prevent duplicates by r.hidden - MDL-13935.
                        continue;
                    }
                    $usersprinted[] = $user->id; // Add new user to the array of users printed.

                    context_helper::preload_from_record($user);

                    $usercontext = context_user::instance($user->id);

                    if ($piclink = ($USER->id == $user->id || has_capability('moodle/user:viewdetails', $context) ||
                        has_capability('moodle/user:viewdetails', $usercontext))) {
                        $profilelink = '<strong><a href="'.$CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.
                            $course->id.'">'.fullname($user).'</a></strong>';
                    } else {
                        $profilelink = '<strong>'.fullname($user).'</strong>';
                    }

                    $data = array ($OUTPUT->user_picture($user, array('size' => 35, 'courseid' => $course->id)), $profilelink);

                    if (!isset($hiddenfields['username'])) {
                        $data[] = $user->username;
                    }
                    $table->add_data($data);
                }
                echo $table->print_html();
            } else {
                echo $OUTPUT->heading(get_string('nothingtodisplay'));
            }
            echo html_writer::end_tag('div');
        }
    }

}

