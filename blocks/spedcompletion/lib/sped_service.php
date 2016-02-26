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

namespace block_spedcompletion;

/**
 * Class Sped
 */
class Sped
{
    /**
     * @param $version String Version of service we're updating
     * @param $presharedkey String The preshared key for the service
     * @param $url String URL of the service endpoint
     */
    public function __construct($version, $presharedkey, $url) {
        $this->version = $version;
        $this->key = $presharedkey;
        $this->url = $url;
        $this->message = '';
        $this->status = false;
    }

    /**
     * Posts the update to the central service for the provided ccid
     * @param $ccid String CCID of the user to update
     * @return boolean true if update successful, false otherwise
     */
    public function post_update($ccid) {
        $maskedtime = time() >> 4;
        $hash = sha1($ccid . $maskedtime . $this->key);
        $data = array('ccid' => $ccid, 'version' => $this->version, 'hash' => $hash);

        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data),
            ),
        );
        $context = stream_context_create($options);
        $response = file_get_contents($this->url, false, $context);

        if ($response === false) {
            $this->message = "Unable to contact web service\n";
            return $this->status = false;
        }
        $parsedresponse = json_decode($response);

        $this->message = $parsedresponse->message;
        return $this->status = $parsedresponse->{'completed'};
    }

    public function set_version($version) {
        $this->version = $version;
    }

    public function get_version() {
        return $this->version;
    }

    public function get_message() {
        return $this->message;
    }

    public function get_status() {
        return $this->status;
    }
}