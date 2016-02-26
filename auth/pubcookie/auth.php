<?php
/**
 * @author Trevor Jones
 * @module_name: pubcookie
 * @created: Mar 15, 2012
 *  This file is part of The Pubcookie Moodle Auth Module.
 *
 *  The Pubcookie Moodle Auth Module is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The Pubcookie Moodle Auth Module is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with The Pubcookie Moodle Auth Module.  If not, see <http://www.gnu.org/licenses/>.
 */


if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); ///  It must be included from a Moodle page
}

// The Posix uid and gid of the 'nobody' account and 'nogroup' group.
if (!defined('AUTH_UID_NOBODY')) {
    define('AUTH_UID_NOBODY', -2);
}
if (!defined('AUTH_GID_NOGROUP')) {
    define('AUTH_GID_NOGROUP', -2);
}


require_once($CFG->libdir . '/authlib.php');
require_once($CFG->libdir . '/ldaplib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/local/eclass/lib/IMS.php');

/**
 * Pubcookie authentication plugin.
 */
class auth_plugin_pubcookie extends auth_plugin_base
{

    /**
     * Init plugin config from database settings depending on the plugin auth type.
     */
    function init_plugin($authtype)
    {
        $this->pluginconfig = 'auth/' . $authtype;
        $this->config = get_config($this->pluginconfig);
    }

    /**
     * Constructor with initialisation.
     */
    function auth_plugin_pubcookie()
    {
        global $CFG;

        $this->authtype = 'pubcookie';
        $this->roleauth = 'auth_pubcookie';
        $this->errorlogtag = '[AUTH PUBCOOKIE] ';
        $this->init_plugin($this->authtype);
        $this->debug_mode = (isset($this->config->pubcookie_debug_mode) && $this->config->pubcookie_debug_mode) ? true
            : false;

    }


    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist.  This Method is being used for users that need to bypass pubcookie and instead use ldap.
     * Examples include respondus, possible mobile access. These methods would use CCID's which need to be translated
     * into employee id's for retrieving user data.
     *
     * @param string $username The username (with system magic quotes)
     * @param string $password The password (with system magic quotes)
     *
     * @return bool Authentication success or failure.
     */
    function user_login($username, $password)
    {
        global $DB, $user;
        // The $username should be coming in as empid.  We need to do a look up for the ccid and authenticate with the given password before we allow the user to login.
        $username = $DB->get_field('user', 'idnumber', array('username' => $username));

        if ($this->config->pubcookie_ldap_enable_lookup) {
            try {
                $ims = new IMS($username, $password, $this->config->pubcookie_user_ldap_search_string, $this->config->pubcookie_user_ldap_rdn, $this->config->pubcookie_ldap_bind_url);
            }
            catch (Exception $e) {
                debugging("IMS authentication failed " . $e->getMessage(), DEBUG_DEVELOPER);
                return false;
            }
            try {
                $user_info = $ims->get_user_info($username, $this->config->pubcookie_user_ldap_search_string, $this->config->pubcookie_user_ldap_rdn, $this->config->pubcookie_user_ldap_filter, array($this->config->pubcookie_user_ldap_scope));


                //if the userinfo is valid standard user data then setup the temp_user
                if ($this->validate_standard_userinfo($user_info)) {
                    //use employeenumber for username
                    list($user, $frm) = $this->process_standard_user_login($user_info, $user_info->employeenumber);
                    $ims->close();
                    return true;
                }
            }
            catch (Exception $e) {
                debugging("Pubcookie: Exception caught " . $e->getMessage(), DEBUG_DEVELOPER);
            }
            //close the ims object
            $ims->close();
        }

        // If no ldap lookup enabled, authentication fails. (May want to seperate ldap lookup from ldap authentication for future update.
        return false;
    }


    /**
     * Will get called before the login page is shown.
     *
     */
    function loginpage_hook()
    {
        //$user, $frm, $errormsg defined in /login/index.php for overriding by loginpage_hooks for SSO or providing fake info
        global $CFG, $SESSION, $user, $frm, $DB;

        if (!isloggedin() or isguestuser()) { // guestuser or not-logged-in users

            // First, let's remember where we were trying to get to before we got here
            $this->set_wants_URL();

            //for debugging purposes
            if ($this->debug_mode) {
                if (!empty($_GET['username'])) {
                    $_SERVER["REMOTE_USER"] = $_GET['username'];
                }
            }
            /*
             * If we have a remote user value then pubcookie authenticated successfully.
             * We should process the REMOTE_USER
             */
            if(!empty($_SERVER["REDIRECT_REMOTE_USER"])) {
                $_SERVER["REMOTE_USER"] = $_SERVER["REDIRECT_REMOTE_USER"];
            }
            if(!empty($_SERVER["REMOTE_USER"])) {
                $username = $_SERVER["REMOTE_USER"];

                //does the username need proccessing?
                if ($this->config->pubcookie_ldap_enable_lookup) {
                    try {
                        $ims = new IMS($this->config->pubcookie_ldap_bind_user, $this->config->pubcookie_ldap_bind_password, $this->config->pubcookie_ldap_bind_cn, $this->config->pubcookie_ldap_bind_rdn, $this->config->pubcookie_ldap_bind_url);
                    }
                    catch (Exception $e) {
                        debugging("IMS authentication failed " . $e->getMessage(), DEBUG_DEVELOPER);
                        return;
                    }
                    try {
                        $user_info = $ims->get_user_info($username, $this->config->pubcookie_user_ldap_search_string, $this->config->pubcookie_user_ldap_rdn, $this->config->pubcookie_user_ldap_filter, array($this->config->pubcookie_user_ldap_scope));


                        //if the userinfo is valid standard user data then setup the temp_user
                        if ($this->validate_standard_userinfo($user_info)) {
                            //use employeenumber for username
                            list($user, $frm) = $this->process_standard_user_login($user_info, $user_info->employeenumber);
                        }
                        else {
                            //if the userinfo isn't valid standard user data
                            if ($this->config->pubcookie_secondary_user_ldap_enable_lookup) { //check for valid secondary user data, uses a second query because we allow separate search settings for secondary users
                                $user_info = $ims->get_user_info($username, $this->config->pubcookie_secondary_user_ldap_search_string, $this->config->pubcookie_secondary_user_ldap_rdn, $this->config->pubcookie_secondary_user_ldap_filter, array($this->config->pubcookie_secondary_user_ldap_scope));

                                if ($this->validate_secondary_userinfo($user_info)) {
                                    //check if the user exists in system. if so create the temp_user
                                    list($user, $frm) = $this->process_secondary_user_login($user_info, $user_info->uid);
                                }
                            }
                            else {
                                debugging("Secondary user ldap lookup not enabled", DEBUG_DEVELOPER);
                            }
                        }

                    }
                    catch (Exception $e) {
                        debugging("Pubcookie: Exception caught " . $e->getMessage(), DEBUG_DEVELOPER);
                    }
                    //close the ims object
                    $ims->close();
                }
                else {
                    //do login w/o ldap
                    $user_info = new IMSRole();
                    //we require at a minimum a uid
                    $user_info->uid = $username;
                    //use secondary as we cannot create a new user without info
                    list($user, $frm) = $this->process_secondary_user_login($user_info, $username, false);
                }

            }
        }
    }

    /**
     * Processes the user_info object as a standard user type. Standard users may or maynot be created on system if they do not exist.
     * @param $user_info
     * @param $username value to use as username
     * @param bool $update
     * @return array
     */
    private function process_standard_user_login($user_info, $username, $update = true)
    {
        global $CFG, $DB;
        IMS::preprocess_user_info($user_info);
        //overrides global $user object in /login/index.php which completes the user login
        //we use empid as username now
        $user_temp = get_complete_user_data('username', $username);

        //if the user doesn't exist
        if (empty($user_temp)) {
            if (!empty($this->config->pubcookie_user_create)) {
                $user = new stdClass();
                $user->auth = 'pubcookie';
                $user->confirmed = 1;
                $user->username = $username;
//                $user->password = 'temp';
                $user->idnumber = $user_info->uid;

                $firstname = self::generate_desired_firstname($user_info->givenname, $user_info->displayname);
                $user->firstname = $firstname;
                $user->lastname = $user_info->sn;
                $user->email = $user_info->mail;
                $user->mnethostid = $CFG->mnet_localhost_id;
                $user->lang = "en";
                $user->autosubscribe = 0;
                $user->trackforums = 1;

                $user->id = user_create_user($user);
                //run this to set the password to not cached
//                update_internal_user_password($user, '');
                //populate $frm to cause login/index.php to honor wantsurl
                $frm = new StdClass();
                $frm->username = $user_info->uid;
                $user = get_complete_user_data('username', $username);
                return array($user, $frm);
            }
            return array(null, null);
        }
        else {
            $user = $user_temp;
            $frm = new StdClass();
            $frm->username = $user_info->uid;
            if ($update) {
                $user = $this->update_user_from_user_info($user_info, $user);
            }
            return array($user, $frm);
        }
    }

    /**
     * Processes the user_info as a secondary user type. Secondary users are only able to login if the account has already been created on the system.
     * @param $user_info
     * @param $username
     * @param bool $update
     * @return array
     */
    private function process_secondary_user_login($user_info, $username, $update = true)
    {
        IMS::preprocess_user_info($user_info);
        //overrides global $user object in /login/index.php which completes the user login
        //we use empid as username now
        $user_temp = get_complete_user_data('username', $username); //based on username instead of empid, ccids can be mapped to multiple secondary ccids, but only one secondary exists.
        if (empty($user_temp)) {
            //only allow secondary CCIDs that are in system already
            return array(null, null);
        }

        //if the user doesn't exist
        if (empty($user_temp)) {
            return array(null, null);
        }
        else {
            $user = $user_temp;
            //to cause login/index.php to honor wantsurl
            $frm = new StdClass();
            $frm->username = $username;
            if ($update) {
                $user = $this->update_user_from_user_info($user_info, $user);
            }
            return array($user, $frm);

        }
    }

    static function generate_desired_firstname($givenname, $displayname)
    {
        // Return displayname, unless it is null, empty, or white, in which case givenname will do.
        return (!isset($displayname) || trim($displayname)==='') ? $givenname : $displayname;
    }

    /**
     * Updates the user with user_info data
     * @param $user_info
     * @param $user
     * @param $username
     * @param $DB
     * @return array user
     */
    private function update_user_from_user_info($user_info, $user)
    { //check for changed values in username, firstname, lastname, email and update if they've changed.
        global $DB;
        $firstname = self::generate_desired_firstname($user_info->givenname, $user_info->displayname);
        if ($user->firstname != $firstname || $user->lastname != $user_info->sn || $user->email != $user_info->mail || $user->idnumber != $user_info->uid) {
            $user->firstname = $firstname;
            $user->lastname = $user_info->sn;
            $user->email = $user_info->mail;
            $user->idnumber = $user_info->uid;

            $user->timemodified = time();
            $DB->update_record('user', $user);
        }

        return $user;
    }

    /**
     * Verifies the user_info meets the requirements for a standard user
     * @param $user_info
     * @return bool
     */
    private function validate_standard_userinfo($user_info)
    {
        if (!empty($user_info->employeenumber) && !empty($user_info->uid) && preg_match("/\\d{7,8}/", $user_info->employeenumber)) {
            return true;
        }
        return false;
    }

    /**
     * Verifies the user_info meets the requirements for a secondary user.
     * @param $user_info
     * @return bool
     */
    private function validate_secondary_userinfo($user_info)
    {
        if (!empty($user_info->employeenumber) && !empty($user_info->uid) && !empty($user_info->mail) && !empty($user_info->cn) && !empty($user_info->sn)) {
            return true;
        }
        return false;
    }

    /**
     * Sets the wants url and prevents certain special urls from being set
     * @return void
     */
    private function set_wants_URL()
    {
        global $SESSION, $CFG;

        if (empty($SESSION->wantsurl)) {
            $SESSION->wantsurl = (array_key_exists('HTTP_REFERER', $_SERVER) &&
                !empty($_SERVER['HTTP_REFERER']) &&
                preg_match("{((" . $CFG->wwwroot . ')|(' . $CFG->httpswwwroot . '))/login/.+php.*}', $_SERVER['HTTP_REFERER']) == 0
            ) ? $_SERVER['HTTP_REFERER'] : $CFG->wwwroot;
        }
        else {
            //check that the url wasn't set to something silly
            $SESSION->wantsurl = (
                preg_match("{((" . $CFG->wwwroot . ')|(' . $CFG->httpswwwroot . '))/login/.+php.*}', $SESSION->wantsurl) == 0)
                ? $SESSION->wantsurl : $CFG->wwwroot . '';
        }
    }

    /**
     * Run on logout.php page
     * Called by /login/logout.php on each enabled auth plugin in squence
     */
    function logoutpage_hook()
    {
        /**
         * $redirect global is used as a hack way of setting the pubcookie logout url without stopping the logoutpage_hook sequences of the other enabled auth plugins
         */
        global $redirect;
        global $CFG;
        $pubcookie_confirm = optional_param('auth_pub_confirm', '0', PARAM_BOOL);
        $sesskey = optional_param('sesskey', '__notpresent__', PARAM_RAW);
        if (isset($this->config->pubcookie_logout_url)) {
            if (!empty($this->config->pubcookie_logout_url)) {

                //HACK - This relies on $redirect being defined in the calling context in /login/logout.php
                //                $redirect = $this->config->pubcookie_logout_url;
                if (!$pubcookie_confirm || !confirm_sesskey($sesskey)) {
                    $this->do_confirmation_page();
                }
                else {
                    $redirect = $this->config->pubcookie_logout_url;
                }
            }
        }
    }

    /**
     * Any cleanup to do before the logout happens? called from require_logout();
     * @return void
     */
    function prelogout_hook()
    {
        global $CFG;
        if (isset($_COOKIE)) {
            //            setcookie('testcookie', 'value', time() + 3600, '/mssodev/moodlecentral/');
            $keys = array_keys($_COOKIE);
            //search cookies for the pubcookie cookie
            foreach ($keys as $key) {
                //                if (preg_match('/MOODLEID.+/', $key, $match)) {
                if (preg_match('/pubcookie_s.+/', $key, $match)) {
                    preg_match('{https?://[^/]+(/.+)$}', $CFG->wwwroot, $a_matches);
                    $path = empty($a_matches[1]) ? '/' : $a_matches[1];
                    if ($path[strlen($path) - 1] != '/') {
                        $path .= '/';
                    }
                    //sets the cookie
                    $result = setcookie($match[0], "", 1, $path);
                }
            }
        }

    }

    function do_confirmation_page()
    {
        global $CFG, $PAGE, $SITE, $OUTPUT;

        $PAGE->set_title($SITE->fullname);
        $PAGE->set_heading("Logout Notice");
        echo $OUTPUT->header();
        echo $OUTPUT->confirm(get_string('auth_pubcookie_logoutconfirmmessage', 'auth_pubcookie'), new moodle_url($PAGE->url, array('sesskey' => sesskey(), 'auth_pub_confirm' => 1)), $CFG->wwwroot . '/');
        echo $OUTPUT->footer();
        die;
    }


    /**
     * Prints a form for configuring this authentication plugin.
     *
     * This function is called from admin/auth.php, and outputs a full page with
     * a form for configuring this plugin.
     *
     * @param array $page An object containing all the data for this page.
     */
    function config_form($config, $err, $user_fields)
    {
        global $CFG, $OUTPUT;

        include($CFG->dirroot . '/auth/pubcookie/config.html');
    }

    /**
     * A chance to validate form data, and last chance to
     * do stuff before it is inserted in config_plugin
     * @param object object with submitted configuration settings (without system magic quotes)
     * @param array $err array of error messages
     */
    function validate_form($form, &$err)
    {
        $error_flag = false;
        if (!isset($form->pubcookie_ldap_enable_lookup)) {
            $form->pubcookie_ldap_enable_lookup = '0';
            //            $err['pubcookie_ldap_enable_lookup'] = get_string('auth_pubcookie_missing_required_field', 'auth_pubcookie');
            //            $error_flag = true;
            //            return $error_flag;
        }
        elseif (!empty($form->pubcookie_ldap_enable_lookup) && !$this->validate_needed_primary_ldap_form($form)) {
            $err['pubcookie_ldap_enable_lookup'] = get_string('auth_pubcookie_missing_depedent_fields', 'auth_pubcookie');
            $error_flag = true;
        }
        if (!isset($form->pubcookie_secondary_user_ldap_enable_lookup)) {
            $form->pubcookie_secondary_user_ldap_enable_lookup = '0';
            //            $err['pubcookie_secondary_user_ldap_enable_lookup'] = get_string('auth_pubcookie_missing_required_field', 'auth_pubcookie');
            //            $error_flag = true;
            //            return $error_flag;
        }
        elseif (!empty($form->pubcookie_secondary_user_ldap_enable_lookup) && (empty($form->pubcookie_ldap_enable_lookup) || !$this->validate_needed_secondary_ldap_form($form))) {
            $err['pubcookie_secondary_user_ldap_enable_lookup'] = get_string('auth_pubcookie_missing_depedent_fields', 'auth_pubcookie');
            $error_flag = true;
        }
        if (empty($form->pubcookie_ldap_bind_user) && $form->pubcookie_ldap_enable_lookup) {
            $err['pubcookie_ldap_bind_user'] = get_string('auth_pubcookie_missing_required_field', 'auth_pubcookie');
            $error_flag = true;
        }
        if (empty($form->pubcookie_ldap_bind_password) && $form->pubcookie_ldap_enable_lookup) {
            $err['pubcookie_ldap_bind_password'] = get_string('auth_pubcookie_missing_required_field', 'auth_pubcookie');
            $error_flag = true;
        }
        if (empty($form->pubcookie_ldap_bind_url) && $form->pubcookie_ldap_enable_lookup) {
            $err['pubcookie_ldap_bind_url'] = get_string('auth_pubcookie_missing_required_field', 'auth_pubcookie');
            $error_flag = true;
        }
        if (empty($form->pubcookie_ldap_bind_rdn) && $form->pubcookie_ldap_enable_lookup) {
            $err['pubcookie_ldap_bind_rdn'] = get_string('auth_pubcookie_missing_required_field', 'auth_pubcookie');
            $error_flag = true;
        }
        if (empty($form->pubcookie_ldap_bind_cn) && $form->pubcookie_ldap_enable_lookup) {
            $err['pubcookie_ldap_bind_cn'] = get_string('auth_pubcookie_missing_required_field', 'auth_pubcookie');
            $error_flag = true;
        }
        if (empty($form->pubcookie_user_ldap_search_string) && $form->pubcookie_ldap_enable_lookup) {
            $err['pubcookie_user_ldap_search_string'] = get_string('auth_pubcookie_missing_required_field', 'auth_pubcookie');
            $error_flag = true;
        }
        if (empty($form->pubcookie_user_ldap_rdn) && $form->pubcookie_ldap_enable_lookup) {
            $err['pubcookie_user_ldap_rdn'] = get_string('auth_pubcookie_missing_required_field', 'auth_pubcookie');
            $error_flag = true;
        }
        if (empty($form->pubcookie_user_ldap_filter) && $form->pubcookie_ldap_enable_lookup) {
            $err['pubcookie_user_ldap_filter'] = get_string('auth_pubcookie_missing_required_field', 'auth_pubcookie');
            $error_flag = true;
        }
        if (empty($form->pubcookie_user_ldap_scope) && $form->pubcookie_ldap_enable_lookup) {
            $err['pubcookie_user_ldap_scope'] = get_string('auth_pubcookie_missing_required_field', 'auth_pubcookie');
            $error_flag = true;
        }
        if (empty($form->pubcookie_secondary_user_ldap_search_string) && $form->pubcookie_ldap_enable_lookup && $form->pubcookie_secondary_user_ldap_enable_lookup) {
            $err['pubcookie_secondary_user_ldap_search_string'] = get_string('auth_pubcookie_missing_required_field', 'auth_pubcookie');
            $error_flag = true;
        }
        if (empty($form->pubcookie_secondary_user_ldap_rdn) && $form->pubcookie_ldap_enable_lookup && $form->pubcookie_secondary_user_ldap_enable_lookup) {
            $err['pubcookie_secondary_user_ldap_rdn'] = get_string('auth_pubcookie_missing_required_field', 'auth_pubcookie');
            $error_flag = true;
        }
        if (empty($form->pubcookie_secondary_user_ldap_filter) && $form->pubcookie_ldap_enable_lookup && $form->pubcookie_secondary_user_ldap_enable_lookup) {
            $err['pubcookie_secondary_user_ldap_filter'] = get_string('auth_pubcookie_missing_required_field', 'auth_pubcookie');
            $error_flag = true;
        }
        if (empty($form->pubcookie_secondary_user_ldap_scope) && $form->pubcookie_ldap_enable_lookup && $form->pubcookie_secondary_user_ldap_enable_lookup) {
            $err['pubcookie_secondary_user_ldap_scope'] = get_string('auth_pubcookie_missing_required_field', 'auth_pubcookie');
            $error_flag = true;
        }

        if ($error_flag) {
            return false;
        }
        else {
            return true;
        }

    }

    /**
     * Validates the presence of setting values required by primary ldap lookup
     * @param $form
     * @return bool
     */
    private function validate_needed_primary_ldap_form($form)
    {
        $fields = array('pubcookie_ldap_bind_user', 'pubcookie_ldap_bind_password', 'pubcookie_ldap_bind_url', 'pubcookie_ldap_bind_rdn', 'pubcookie_ldap_bind_cn',
            'pubcookie_user_ldap_search_string', 'pubcookie_user_ldap_rdn', 'pubcookie_user_ldap_filter', 'pubcookie_user_ldap_scope');
        foreach ($fields as $field) {
            if (empty($form->$field)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validates the presence of setting values required by secondary ldap lookup
     * @param $form
     * @return bool
     */
    private function validate_needed_secondary_ldap_form($form)
    {
        $fields = array('pubcookie_ldap_bind_user', 'pubcookie_ldap_bind_password', 'pubcookie_ldap_bind_url', 'pubcookie_ldap_bind_rdn', 'pubcookie_ldap_bind_cn',
            'pubcookie_secondary_user_ldap_search_string', 'pubcookie_secondary_user_ldap_rdn', 'pubcookie_secondary_user_ldap_filter', 'pubcookie_secondary_user_ldap_scope');
        foreach ($fields as $field) {
            if (empty($form->$field)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Processes and stores configuration data for this authentication plugin.
     */
    function process_config($config)
    {
        // Set to defaults if undefined
        if (!isset($config->pubcookie_ldap_enable_lookup)) {
            $config->pubcookie_ldap_enable_lookup = '0';
        }
        if (!isset($config->pubcookie_secondary_user_ldap_enable_lookup)) {
            $config->pubcookie_secondary_user_ldap_enable_lookup = '0';
        }
        if (!isset($config->pubcookie_user_create)) {
            $config->pubcookie_user_create = '0';
        }
        if (!isset($config->pubcookie_debug_mode)) {
            $config->pubcookie_debug_mode = '0';
        }
        if (!isset($config->pubcookie_logout_url)) {
            $config->pubcookie_logout_url = '';
        }
        else {
            // if there is a logout url specified then it must begin with http(s) or '/'
            if (!empty($config->pubcookie_logout_url) && (!preg_match("/^https?:\\/\\/.+/", $config->pubcookie_logout_url) &&
                      !preg_match("/^\\/.+/", $config->pubcookie_logout_url))) {
                $config->pubcookie_logout_url = '/' . $config->pubcookie_logout_url;
            }
        }
        if (!isset($config->pubcookie_ldap_bind_user)) {
            $config->pubcookie_ldap_bind_user = '';
        }
        if (!isset($config->pubcookie_ldap_bind_password)) {
            $config->pubcookie_ldap_bind_password = '';
        }
        if (!isset($config->pubcookie_change_password_url)) {
        }
        else {
            if (!empty($config->pubcookie_change_password_url) && (!preg_match("/^https?:\\/\\/.+/", $config->pubcookie_change_password_url) &&
                !preg_match('/^\\/.+/', $config->pubcookie_change_password_url))) {
                $config->pubcookie_change_password_url = '/' . $config->pubcookie_change_password_url;
            }
        }
        if (!isset($config->pubcookie_ldap_bind_url)) {
        }
        else {
            if (!empty($config->pubcookie_ldap_bind_url) && !preg_match("/^\\w+s?:\\/\\/.+/", $config->pubcookie_ldap_bind_url)) {
                $config->pubcookie_ldap_bind_url = 'ldaps://' . $config->pubcookie_ldap_bind_url;
            }
        }
        if (!isset($config->pubcookie_ldap_bind_rdn)) {
            $config->pubcookie_ldap_bind_rdn = '';
        }
        if (!isset($config->pubcookie_ldap_bind_cn)) {
            $config->pubcookie_ldap_bind_cn = '';
        }
        if (!isset($config->pubcookie_user_ldap_search_string)) {
            $config->pubcookie_user_ldap_search_string = '';
        }
        if (!isset($config->pubcookie_user_ldap_rdn)) {
            $config->pubcookie_user_ldap_rdn = '';
        }
        if (!isset($config->pubcookie_user_ldap_filter)) {
            $config->pubcookie_user_ldap_filter = '';
        }
        if (!isset($config->pubcookie_user_ldap_scope)) {
            $config->pubcookie_user_ldap_scope = '';
        }
        if (!isset($config->pubcookie_secondary_user_ldap_search_string)) {
            $config->pubcookie_secondary_user_ldap_search_string = '';
        }
        if (!isset($config->pubcookie_secondary_user_ldap_rdn)) {
            $config->pubcookie_secondary_user_ldap_rdn = '';
        }
        if (!isset($config->pubcookie_secondary_user_ldap_filter)) {
            $config->pubcookie_secondary_user_ldap_filter = '';
        }
        if (!isset($config->pubcookie_secondary_user_ldap_scope)) {
            $config->pubcookie_secondary_user_ldap_scope = '';
        }


        // Save settings
        set_config('pubcookie_ldap_enable_lookup', $config->pubcookie_ldap_enable_lookup, $this->pluginconfig);
        set_config('pubcookie_secondary_user_ldap_enable_lookup', $config->pubcookie_secondary_user_ldap_enable_lookup, $this->pluginconfig);
        set_config('pubcookie_user_create', $config->pubcookie_user_create, $this->pluginconfig);
        set_config('pubcookie_debug_mode', $config->pubcookie_debug_mode, $this->pluginconfig);
        set_config('pubcookie_logout_url', $config->pubcookie_logout_url, $this->pluginconfig);
        //don't save password if its set to our standin password
        if (!preg_match('/currentpassword/', $config->pubcookie_ldap_bind_password)) {
            set_config('pubcookie_ldap_bind_password', $config->pubcookie_ldap_bind_password, $this->pluginconfig);
        }
        set_config('pubcookie_ldap_bind_user', $config->pubcookie_ldap_bind_user, $this->pluginconfig);
        set_config('pubcookie_change_password_url', $config->pubcookie_change_password_url, $this->pluginconfig);
        set_config('pubcookie_ldap_bind_url', $config->pubcookie_ldap_bind_url, $this->pluginconfig);
        set_config('pubcookie_ldap_bind_rdn', $config->pubcookie_ldap_bind_rdn, $this->pluginconfig);
        set_config('pubcookie_ldap_bind_cn', $config->pubcookie_ldap_bind_cn, $this->pluginconfig);
        set_config('pubcookie_user_ldap_search_string', $config->pubcookie_user_ldap_search_string, $this->pluginconfig);
        set_config('pubcookie_user_ldap_rdn', $config->pubcookie_user_ldap_rdn, $this->pluginconfig);
        set_config('pubcookie_user_ldap_filter', $config->pubcookie_user_ldap_filter, $this->pluginconfig);
        set_config('pubcookie_user_ldap_scope', $config->pubcookie_user_ldap_scope, $this->pluginconfig);
        set_config('pubcookie_secondary_user_ldap_search_string', $config->pubcookie_secondary_user_ldap_search_string, $this->pluginconfig);
        set_config('pubcookie_secondary_user_ldap_rdn', $config->pubcookie_secondary_user_ldap_rdn, $this->pluginconfig);
        set_config('pubcookie_secondary_user_ldap_filter', $config->pubcookie_secondary_user_ldap_filter, $this->pluginconfig);
        set_config('pubcookie_secondary_user_ldap_scope', $config->pubcookie_secondary_user_ldap_scope, $this->pluginconfig);

        return true;
    }

    /**
     * Returns the URL for changing the users' passwords, or empty if the default
     * URL can be used.
     *
     * This method is used if can_change_password() returns true.
     * This method is called only when user is logged in, it may use global $USER.
     *
     * @return moodle_url url of the profile page or null if standard used
     */
    function change_password_url()
    {
        //override if needed
        if (!empty($this->config->pubcookie_change_password_url)) {
            return new moodle_url($this->config->pubcookie_change_password_url);
        } else {
            return null;
        }
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    function can_change_password()
    {
        return false;
    }

    /**
     * Returns true if this authentication plugin is "internal".
     *
     * Internal plugins use password hashes from Moodle user table for authentication.
     *
     * @return bool
     */
    function is_internal()
    {
        //override if needed
        return false;
    }

    /**
     * Indicates if password hashes should be stored in local moodle database.
     * @return bool true means md5 password hash stored in user table, false means flag 'not_cached' stored there instead
     */
    function prevent_local_passwords()
    {
        return !$this->is_internal();
    }

} // End of the class
