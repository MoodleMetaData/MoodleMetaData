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


/**
 * A class that encapsulated the MediaCore Moodle Config
 * Config values in config_plugins table as local_mediacore
 */
class mediacore_config
{
    private $_consumer_key;
    private $_host = 'demo.mediacore.tv';
    private $_shared_secret;
    private $_version;
    private $_webroot;
    private static $_stored_members = array(
        // These members are populated from the DB
        '_consumer_key',
        '_host',
        '_scheme',
        '_shared_secret',
        '_use_lti_auth',
        '_use_trusted_embeds',
        '_version',
    );

    /**
     * Constructor
     */
    public function __construct() {
        global $CFG, $DB;

        $this->_webroot = $CFG->wwwroot;

        $records = $DB->get_records('config_plugins',
            array('plugin' => LOCAL_MEDIACORE_PLUGIN_NAME));

        if (!empty($records)) {
            foreach ($records as $r) {
                $member_name = '_' . $r->name;

                if (in_array($member_name, self::$_stored_members)) {
                    $value = $r->value;

                    if (!empty($value)) {
                        $this->{$member_name} = $value;
                    }
                } else {
                    // TODO: Report unexpected key found in config?
                }
            }
        }
    }

    /**
     * Whether lti is configured
     * @return boolean
     */
    public function has_lti_config() {
        return (!empty($this->_host) &&
                !empty($this->_consumer_key) &&
                !empty($this->_shared_secret) &&
                !empty($this->_use_lti_auth));
    }

    /**
     * Get the local_media plugin version
     * @return string
     */
    public function get_version() {
        return $this->_version;
    }

    /**
     * Get the mediacore host (may contain a port num)
     * @return string
     */
    public function get_host() {
        return rtrim($this->_host, '/');
    }

    /**
     * Get the mediacore host scheme
     * @return string
     */
    public function get_scheme() {
        if (empty($this->_scheme)) {
            return LOCAL_MEDIACORE_DEFAULT_SCHEME;
        }
        return $this->_scheme;
    }

    /**
     * Get the lti consumer key
     * @return string
     */
    public function get_consumer_key() {
        return $this->_consumer_key;
    }

    /**
     * Get the lti consumer shared secret
     * @return string
     */
    public function get_shared_secret() {
        return $this->_shared_secret;
    }

    /**
     * Get whether we're using trusted embeds
     * @return boolean
     */
    public function get_use_trusted_embeds() {
        if (empty($this->_use_trusted_embeds)) {
            return LOCAL_MEDIACORE_DEFAULT_USE_TRUSTED_EMBEDS;
        }
        return (boolean)$this->_use_trusted_embeds;
    }

    /**
     * Get the moodle webroot
     * @return string
     */
    public function get_webroot() {
        return rtrim($this->_webroot, '/');
    }

    /**
     * Get the plugin version info
     * @return string
     */
    public function get_plugin_info() {
        return 'mediacore-moodle-chooser-' . $this->get_version();
    }

}
