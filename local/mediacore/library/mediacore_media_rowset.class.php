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
require_once 'mediacore_media_row.class.php';


/**
 * An iteratable rowset class that will return a custom row object
 */
class mediacore_media_rowset implements Iterator, Countable
{
    private $_mcore_client;
    private $_count;
    private $_position;
    private $_media;
    private $_rows;

    public function __construct($client, $media) {
        $this->_count = count($media);
        $this->_mcore_client = $client;
        $this->_position = 0;
        $this->_media = $media;
        $this->_rows = array();
    }

    /**
     */
    public function rewind() {
        $this->position = 0;
    }

    /**
     */
    public function current() {
        if ($this->valid() === false) {
            return null;
        }
        if (empty($this->_rows[$this->_position])) {
            $this->_rows[$this->_position] = new mediacore_media_row(
                $this->_mcore_client, $this->_media[$this->_position]);
        }
        return $this->_rows[$this->_position];
    }

    /**
     */
    public function key() {
        return $this->_position;
    }

    /**
     */
    public function next() {
        ++$this->_position;
    }

    /**
     */
    public function valid() {
        return isset($this->_media[$this->_position]);
    }

    /**
     */
    public function count() {
        return $this->_count;
    }
}
