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
 *       __  _____________   _______   __________  ____  ______
 *      /  |/  / ____/ __ \ /  _/   | / ____/ __ \/ __ \/ ____/
 *     / /|_/ / __/ / / / / / // /| |/ /   / / / / /_/ / __/
 *    / /  / / /___/ /_/ /_/ // ___ / /___/ /_/ / _, _/ /___
 *   /_/  /_/_____/_____//___/_/  |_\____/\____/_/ |_/_____/
 *
 * MediaCore's local plugin
 *
 * @package    local
 * @subpackage mediacore
 * @copyright  2012 MediaCore Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die('Invalid access');

global $CFG;
require_once $CFG->dirroot . '/lib/filelib.php';
require_once 'mediacore_client.class.php';


/**
 * A class that encapsulated a media row object
 */
class mediacore_media_row
{
    private $_mcore_client;
    private $_data;
    private $_thumb_height = 120;
    private $_thumb_width = 90;

    /**
     * Constructor
     *
     * @param mediacore_client $client
     * @param object $media
     */
    public function __construct($client, $data) {
        $this->_mcore_client = $client;
        $this->_data = $data;
    }

    /**
     * Get the media object
     *
     * @return object
     */
    public function get_data() {
        return $this->_data;
    }

    /**
     * Get the author
     *
     * @return string
     */
    public function get_author() {
        if (!empty($this->_data->byline)) {
            return $this->_data->byline;
        } else {
            return $this->_data->user->display_name;
        }
    }

    /**
     * Get the date created
     *
     * @return string
     */
    public function get_datecreated() {
        return strtotime($this->_data->created_on);
    }

    /**
     * Get the date modified
     *
     * @return string
     */
    public function get_datemodified() {
        return strtotime($this->_data->modified_on);

    }

    /**
     * Get the media id
     *
     * @return int
     */
    public function get_id() {
        return $this->_data->id;
    }

    /**
     * Get the media mimetype
     *
     * Uses a built-in moodle mimetype helper
     * @return string
     */
    public function get_mimetype() {
        if (function_exists('get_mimetypes_array')) {
            $mimetypes = &get_mimetypes_array();
            $container = $this->get_primary_file_container();
            if (isset($mimetypes[$container])) {
                return $mimetypes[$container]['type'];
            }
        }
        return 'video/mp4';
    }

    /**
     * Get the media shorttitle
     *
     * @return string
     */
    public function get_shorttitle() {
        return $this->_truncate_text($this->_data->title, 25);
    }

    /**
     * Get the size
     * TODO implement. 0 is ok for now.
     *
     * @return int
     */
    public function get_size() {
        return 0;
    }

    /**
     * Get the view url
     *
     * @return string
     */
    public function get_view_url() {
        return $this->_data->links->view;
    }

    /**
     * Get the source link
     *
     * @return string
     */
    public function get_source() {
        return $this->_mcore_client->get_siteurl() .
            $this->get_view_url() . '#' . $this->get_title();
    }

    /**
     * Get the thumbnail url
     *
     * @return string
     */
    public function get_thumbnail_url() {
        $thumb_url = $this->_data->joins->thumbs->sizes->s_4x3;
        if (strpos($thumb_url, 'http') === false) {
            return $this->_mcore_client->get_siteurl() .
                $thumb_url;
        }
        return $thumb_url;
    }

    /**
     * Get the hardcoded thumb height
     * TODO get the actual image height
     *
     * @return int
     */
    public function get_thumbnail_height() {
        return $this->_thumb_height;
    }

    /**
     * Get the hardcoded thumb width
     * TODO get the actual image width
     *
     * @return int
     */
    public function get_thumbnail_width() {
        return $this->_thumb_width;
    }

    /**
     * Get the thumb title
     *
     * @return string
     */
    public function get_thumbnail_title() {
        return $this->get_title();
    }

    /**
     * Get the media title with extension
     *
     * @return string
     */
    public function get_title() {
        return $this->_data->title . '.' .
            $this->get_primary_file_container();
    }

    /**
     * Get the file extension
     *
     * @return string
     */
    public function get_primary_file_container() {
        $container = $this->_data->primary_file_container;
        if (empty($container) || strpos($container, 'embed') !== false) {
            $container = 'mp4';
        }
        return $container;
    }

    /**
     * Get an array of the file data for the repository
     *
     * @return array
     */
    public function get_repository_file_array() {
        return array(
            'author' => $this->get_author(),
            'datecreated' => $this->get_datecreated(),
            'datemodified' => $this->get_datemodified(),
            'id' => $this->get_id(),
            'mimetype' => $this->get_mimetype(),
            'shorttitle' => $this->get_shorttitle(),
            'size' => $this->get_size(),
            'source' => $this->get_source(),
            'thumbnail' => $this->get_thumbnail_url(),
            'thumbnail_height' => $this->get_thumbnail_height(),
            'thumbnail_title' => $this->get_thumbnail_title(),
            'thumbnail_width' => $this->get_thumbnail_width(),
            'title' => $this->get_title(),
        );
    }

    /**
     * Truncate string helper
     *
     * @param string $text
     * @param int $chars
     * @param string $pad
     * @return string
     */
    private function _truncate_text($text, $chars = 20, $pad = '...') {
        if (strlen($text) <= $chars) {
            return $text;
        }
        $result = ''; $count = 0;
        $words = explode(' ', $text);
        foreach ($words as $w) {
            $wcount = strlen($w);
            if (($count + $wcount) <= $chars) {
                $result .= $w . ' ';
                $count += $wcount + 1;
            } else {
                break;
            }
        }
        return rtrim($result, ' ') . $pad;
    }
}
