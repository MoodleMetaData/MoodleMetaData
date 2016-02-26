<?php
/**
 *  @author Trevor Jones
 *  @module_name: pubcookie
 *  @created: Mar 15, 2012
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

define("MOODLE_INTERNAL", TRUE);

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
//Require Login to get course list
global $PAGE, $CFG;
$PAGE->set_context(context_system::instance());
require_login();
require_capability('moodle/site:config', context_system::instance());
require_once($CFG->dirroot . '/local/eclass/lib/IMS.php');

if (!empty($_REQUEST['ccid'])) {
    $pluginconfig = 'auth/pubcookie';
    $config = get_config($pluginconfig);
    if (empty($config->pubcookie_ldap_bind_user) || empty($config->pubcookie_ldap_bind_password)) {
        debugging("ims_user or ims_password not set",DEBUG_DEVELOPER,array());
        return;
    }
    else {
        if ($config->pubcookie_ldap_enable_lookup) {
            $ims = new IMS($config->pubcookie_ldap_bind_user, $config->pubcookie_ldap_bind_password, $config->pubcookie_ldap_bind_cn, $config->pubcookie_ldap_bind_rdn, $config->pubcookie_ldap_bind_url);
            $role = $ims->get_user_info($_REQUEST['ccid'], $config->pubcookie_user_ldap_search_string, $config->pubcookie_user_ldap_rdn, $config->pubcookie_user_ldap_filter, array($config->pubcookie_user_ldap_scope));
            var_dump($role);
        }

        if ($config->pubcookie_secondary_user_ldap_enable_lookup) {
            $role = $ims->get_user_info($_REQUEST['ccid'], $config->pubcookie_secondary_user_ldap_search_string, $config->pubcookie_secondary_user_ldap_rdn, $config->pubcookie_secondary_user_ldap_filter, array($config->pubcookie_secondary_user_ldap_scope));
            var_dump($role);
        }
    }

}
else {
    debugging("No user specified",DEBUG_DEVELOPER,array());
}
