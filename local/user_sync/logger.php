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

class mail_logger {

    private $log;
    private $postmessage;
    private $starttime;
    private $endtime;
    private $status;

    private $subject = 'User Management: Error log for ';

    public function __construct($caller) {
        $this->subject .= $caller;
    }

    public function setstarttime($time = -1) {
        if ($time == -1) {
            $this->starttime = time();
        } else {
            $this->starttime = $time;
        }
    }

    public function setendtime($time = -1) {
        if ($time == -1) {
            $this->endtime = time();
        } else {
            $this->endtime = $time;
        }
    }

    public function setstatus($status) {
        $this->status = $status;
    }

    public function log($message) {
        $this->log .= $message."\n";
    }

    public function postmessage($message) {
        $this->postmessage .= $message."\n";
    }

    // Mail the recorded log entries.
    public function mail() {
        $success = false;
        global $CFG;

        if (empty($CFG->user_sync_receiver)) {
            $CFG->user_sync_receiver = 'ctl-tech@mailman.srv.ualberta.ca';
        }
        if (empty($CFG->user_sync_subject)) {
            $CFG->user_sync_subject = 'User Management: Error log for ';
        }

        if (!empty($this->log)) {
            $this->log = str_replace("\n", "\r\n", $this->log );
            $success = mail($CFG->user_sync_receiver, $CFG->user_sync_subject, $this->log);
            if (!$success) {
                debugging("Message delivery failed\n", DEBUG_ALL);
            }
        }
        return $success;
    }

    public function post() {
        global $CFG;
        $success = false;

        if (!empty($CFG->enable_user_sync_post)) {
            require_once(dirname(__FILE__) . '/lib/curl.php');
            $curl = new curl();
            $curl->setHeader('x-access-token: ' . $CFG->user_sync_post_token);
            $parameters = array(
                    'starttime' => $this->starttime * 1000,
                    'endtime' => $this->endtime * 1000,
                    'status' => $this->status,
                    'message' => $this->postmessage . "\n" .
                    ($this->log ? $this->subject . "\n" . $this->log : '')
            );
            $result = $curl->post("http://mcp.srv.ualberta.ca/user/sync/send", $parameters);

            if ($curl->error) {
                echo 'curl error! ' . var_export($curl->info, true);
                echo 'POST result:' . $result;
                $success = false;
            } else {
                $success = true;
            }
        }

        return $success;
    }

    public function mailandpost() {
        $success = $this->mail();
        $success &= $this->post();
        return $success;
    }

    public function getlog() {
        return $this->log;
    }
}
