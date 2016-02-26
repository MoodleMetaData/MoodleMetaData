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
require_once $CFG->dirroot. '/mod/lti/locallib.php';
require_once 'Zend/Uri/Http.php';
require_once 'Zend/Exception.php';
require_once 'mediacore_config.class.php';


/**
 * The MediaCore Moodle Client
 * Encapsulated the client access endpoints and lti helpers
 */
class mediacore_client
{
    private $_chooser_js_path = '/api/chooser.js';
    private $_chooser_path = '/chooser';
    private $_config;
    private $_uri;

    /**
     * Constructor
     */
    public function __construct() {
        $this->_config = new mediacore_config();

        // We have to use the fromString method because the 'host' we pass in
        // may actually contain a port (e.g. 'blah.com:8080' not just 'blah.com')
        // so we can't just pass it to Zend_Uri_Http.setHost(), like one might
        // expect
        $url = $this->_config->get_scheme() . '://' .
                $this->_config->get_host();
        $this->_uri = Zend_Uri_Http::fromString($url);
    }

    /**
     * The mediacore_config object
     *
     * @return mediacore_config
     */
    public function get_config() {
        return $this->_config;
    }

    /**
     * Get the mediacore site url scheme
     *
     * @return string|boolean
     */
    public function get_scheme() {
        return $this->_uri->getScheme();
    }

    /**
     * Get the mediacore site host
     * w/o the port
     *
     * @return string
     */
    public function get_host() {
        return $this->_uri->getHost();
    }

    /**
     * Get the mediacore site port
     *
     * @return string|boolean
     */
    public function get_port() {
        return $this->_uri->getPort();
    }

    /**
     * Get the mediacore site url host and port
     *
     * @return string
     */
    public function get_host_and_port() {
        $val = $this->get_host();
        if ($this->get_port()) {
            $val .= ':' . $this->get_port();
        }
        return $val;
    }

    /**
     * Get the mediacore site base url
     *
     * @return string
     */
    public function get_siteurl() {
        return $this->_uri->getUri();
    }

    /**
     * Get an api2 constructed path from supplied api2
     * path segments
     *
     * @param string ...
     * @return string
     */
    public function get_url() {
        $args = func_get_args();
        $url = $this->get_siteurl();
        if (is_array($args) && !empty($args)) {
            $url .= '/' . implode('/', $args);
        }
        return $url;
    }

    /**
     * Urlencode the query params values
     *
     * @param string $params
     * @return array
     */
    public function get_query($params) {
        $encoded_params = '';
        foreach ($params as $k => $v) {
            $encoded_params .= "$k=" . urlencode($v) . "&";
        }
        return substr($encoded_params, 0, -1);
    }

    /**
     * Send a GET curl request
     *
     * @param string $url
     * @param array $options
     * @param array $headers
     * @return mixed
     */
    public function get($url, $options=array(), $headers=array()) {
        return $this->_send($url, 'GET', null, $options, $headers);
    }

    /**
     * Send a POST curl request
     *
     * @param string $url
     * @param array $data
     * @param array $options
     * @param array $headers
     * @return mixed
     */
    public function post($url, $data, $options=array(), $headers=array()) {
        return $this->_send($url, 'POST', $data, $options, $headers);
    }

    /**
     * Send a curl GET or POST request
     *
     * @param string $url
     * @param string $method
     * @param array $data
     * @param array $options
     * @param array $headers
     * @return string|boolean
     */
    private function _send($url, $method='GET', $data=null, $options=array(),
        $headers=array()) {

        global $CFG;

        // Set the curl options
        $default_options = array(
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 4,
            CURLOPT_URL => $url,
        );
        if ((boolean)$CFG->debugdisplay) {
            $default_options[CURLOPT_SSL_VERIFYHOST] = false;
            $default_options[CURLOPT_SSL_VERIFYPEER] = false;
        }
        $options = array_replace($default_options, (array)$options);

        // Set POST request opts if necessary
        $options[CURLOPT_POST] = false;
        unset($options[CURLOPT_POSTFIELDS]);
        if ($method == 'POST' && !empty($data)) {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $data;
        }

        // Build the curl headers
        // Disallow passing headers in the options arg
        unset($options[CURLOPT_HTTPHEADER]);
        if (!empty($headers)) {
            $options[CURLOPT_HTTPHEADER] = (array)$headers;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * Get the cookie string from the response header of
     * the authtkt api endpoint using lti
     *
     * @param int $courseid
     * @return string
     */
    public function get_auth_cookie($courseid) {
        global $CFG;

        $authtkt_url = $this->get_url('api2', 'lti', 'authtkt');
        $signed_lti_params = $this->get_signed_lti_params(
            $authtkt_url, 'POST', $courseid);

        $options = array(
            CURLOPT_HEADER => true,
        );
        $result = $this->_send($authtkt_url, 'POST', $signed_lti_params, $options);

        if (empty($result)) {
            return $result;
        }
        // parse the cookie from the header
        $cookie_str = '';
        preg_match('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
        if (isset($matches[1])) {
            $cookie_str = rtrim($matches[1]);
        }
        return $cookie_str;
    }

    /**
     * Get the chooser js url
     *
     * @return string
     */
    public function get_chooser_js_url() {
        return $this->get_siteurl() . $this->_chooser_js_path;
    }

    /**
     * Sign and return the LTI-signed chooser js endpoint
     *
     * @param string|int $courseid
     * @param array $lti_params
     * @return string
     */
    public function get_signed_chooser_js_url($courseid, $lti_params=array()) {
        $url = $this->get_chooser_js_url();
        return $url . '?' . $this->get_query(
            $this->get_signed_lti_params($url, 'GET', $courseid, $lti_params)
        );
    }

    /**
     * Get the chooser url
     *
     * @return string
     */
    public function get_chooser_url() {
        return  $this->get_siteurl() . $this->_chooser_path;
    }

    /**
     * Get the unsigned chooser Urlencode
     * NOTE When using trusted embeds without LTI, we
     *      append a use_trusted_embed query param here.
     *
     * @return string
     */
    public function get_unsigned_chooser_url() {
        $url = $this->get_chooser_url();
        if ($this->_config->get_use_trusted_embeds()) {
            $url .= '?use_trusted_embed=true';
        }
        return $url;
    }

    /**
     * Sign and return the LTI-signed chooser endpoint
     * NOTE When using trusted embeds with LTI, we
     *      send a custom_use_trusted_embed param,
     *      added in `get_lti_params` method
     *
     * @param string|int $courseid
     * @param array $lti_params
     * @return string
     */
    public function get_signed_chooser_url($courseid, $lti_params) {
        $url = $this->get_chooser_url();
        return $url . '?' . $this->get_query(
            $this->get_signed_lti_params($url, 'GET', $courseid, $lti_params)
        );
    }

    /**
     * Get the moodle webroot
     *
     * @return string
     */
    public function get_webroot() {
        return $this->_config->get_webroot();
    }

    /**
     * Get the base lti request params
     *
     * @param object $course
     * @return array
     */
    public function get_lti_params($course) {
        global $USER, $CFG;

        $user_given = (isset($USER->firstname)) ? $USER->firstname : '';
        $user_family = (isset($USER->lastname)) ? $USER->lastname : '';
        $user_full = trim($user_given . ' ' . $user_family);
        $user_email = (isset($USER->email)) ? $USER->email: '';

        if (strpos($CFG->release, '2.8') === false) {
            $roles = lti_get_ims_role($USER, 0, $course->id);
        } else {
            // NOTE: Moodle 2.8 adds support for specifying whether this is
            //       an LTI 2.0 launch.
            $roles = lti_get_ims_role($USER, 0, $course->id, false);
        }

        $params = array(
            'context_id' => $course->id,
            'context_label' => $course->shortname,
            'context_title' => $course->fullname,
            'ext_lms' => 'moodle-2',
            'lis_person_name_family' => $user_family,
            'lis_person_name_full' => $user_full,
            'lis_person_name_given' => $user_given,
            'lis_person_contact_email_primary' => $user_email,
            'lti_message_type' => 'basic-lti-launch-request',
            'lti_version' => 'LTI-1p0',
            'roles' => $roles,
            'tool_consumer_info_product_family_code' => 'moodle',
            'tool_consumer_info_version' => (string)$CFG->version,
            'user_id' => $USER->id,
            'custom_context_id' => $course->idnumber,
            'custom_plugin_info' => $this->_config->get_plugin_info(),
        );

        // NOTE: For LTI launches we use a custom_use_trusted_embed
        //       param. For non-LTI launches we append use_trusted_embed
        //       as a query param. See `get_signed_chooser_url`
        if ($this->_config->get_use_trusted_embeds()) {
            $params['custom_use_trusted_embed'] = 'true';
        }

        // Add debug flag for local testing.
        if ((boolean)$CFG->debugdisplay) {
            $params['debug'] = 'true';
        }
        return $params;
    }

    /**
     * Get the signed lti parameters
     * uses Oauth-1x
     *
     * @param string $endpoint
     * @param string $method
     * @param int $courseid
     * @param array $params
     * @return array
     */
    public function get_signed_lti_params($endpoint, $method='GET',
        $courseid=null, $params=array()) {

        global $DB;

        if (empty($courseid)) {
            throw new Zend_Exception(get_string('no_course_id',
                LOCAL_MEDIACORE_PLUGIN_NAME), E_USER_ERROR);
        }
        if (!$this->_config->has_lti_config()) {
            throw new Zend_Exception(get_string('no_lti_config',
                LOCAL_MEDIACORE_PLUGIN_NAME), E_USER_ERROR);
        }
        $course = $DB->get_record('course', array('id' => (int)$courseid), '*',
            MUST_EXIST);
        $key = $this->_config->get_consumer_key();
        $secret = $this->_config->get_shared_secret();
        $query_params = $this->get_lti_params($course);
        return lti_sign_parameters(array_replace($query_params, $params),
            $endpoint, $method, $key, $secret);
    }

    /**
     * Whether the config is setup for lti
     *
     * @return boolean
     */
    public function has_lti_config() {
        return $this->_config->has_lti_config();
    }

    /**
     * Get the custom atto/tinymce params
     *
     * @return array
     */
    public function get_texteditor_params() {
        global $COURSE;

        //default non-lti urls
        $chooser_js_url = $this->get_chooser_js_url();
        $chooser_url = $this->get_unsigned_chooser_url();

        if ($this->has_lti_config() && isset($COURSE->id)) {
            //NOTE: Sign the chooser js endpoint only so that the oauth values
            //      are not regenerated.
            $chooser_js_url = $this->get_signed_chooser_js_url($COURSE->id);
            // append the context_id to the chooser endpoint
            $chooser_url .= (strpos($chooser_url, '?') === false) ? '?' : '&';
            $chooser_url .= 'context_id=' . $COURSE->id;
        }
        $params['mcore_chooser_js_url'] = $chooser_js_url;
        $params['mcore_chooser_url'] = $chooser_url;

        return $params;
    }

    /**
     * Method for hooking into the Moodle 2.3 Tinymce plugin lib.php
     * file
     *
     * Moodle 2.4+ uses different logic -- see MediaCore plugin
     * installation instructions for details.
     *
     * @param array $filters
     * @param array $params
     * @return array
     */
    public function configure_tinymce_lib_params($filters, $params) {
        if (!function_exists('filter_get_active_in_context')) {
            throw new Zend_Exception('This class can only be called ' .
                'from within the tinymce/lib.php file');
        }
        if (!isset($filters)) {
            $filters = filter_get_active_in_context($context);
        }
        if (array_key_exists('filter/mediacore', $filters)) {
            $params = $params + $this->get_texteditor_params();
            $params['plugins'] .= ',mediacore';
            if (isset($params['theme_advanced_buttons3_add'])) {
                $params['theme_advanced_buttons3_add'] .= ",|,mediacore";
            } else {
                $params['theme_advanced_buttons3_add'] = ",|,mediacore";
            }
        }
        return $params;
    }
}
