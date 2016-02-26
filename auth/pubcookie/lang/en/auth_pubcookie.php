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

$string['auth_pubcookie_missing_required_field'] = 'Field is required!';
$string['auth_pubcookie_missing_depedent_fields'] = 'Associated fields are required to enable this option.';
$string['auth_pubcookie_ldap_enable_lookup_key'] = 'Enable LDAP lookup';
$string['auth_pubcookie_ldap_enable_lookup'] = 'Causes plugin to perform an LDAP lookup based on pubcookie provided value to search for desired username field. Otherwise uses pubcookie value as is.';
$string['auth_pubcookie_secondary_user_ldap_enable_lookup_key'] = 'Enable LDAP lookup';
$string['auth_pubcookie_secondary_user_ldap_enable_lookup'] = 'Causes plugin to perform an LDAP lookup based on pubcookie provided value to search for desired username field. Otherwise uses pubcookie value as is.';
$string['auth_pubcookie_user_create_key'] = 'Create missing users';
$string['auth_pubcookie_user_create'] = 'Allows pubcookie plugin to create authenticated users if they do not exist. (Requires LDAP lookup to be enabled)';
$string['auth_pubcookie_debug_mode_key'] = 'Enable Debug Mode';
$string['auth_pubcookie_debug_mode'] = 'Allows login via GET parameters for testing. DO NOT ENABLE ON PRODUCTION SYSTEM!';
$string['auth_pubcookiedescription'] = 'This method provides authentication against an external PUBCOOKIE server.
                                  It captures requests to the login/index.php page and checks for a pubcookie variable populated with a studentid.
                                  If the studentid is provided it will perform an LDAP lookup for another student identifier with which to use for
                                  login. A secondary user query setting allows a second lookup for the pubcookie variable in ldap with the condition
                                  that the looked up account will only be logged in if it already exists in moodle.';
$string['auth_pubcookie_logout_url_key'] = 'Logout URL';
$string['auth_pubcookie_logout_url'] = 'Logout URL for your institutions pubcookie authentication';

$string['auth_pubcookie_ldap_bind_title'] = 'Institution Ldap Connection Settings';
$string['auth_pubcookie_ldap_bind_details'] = 'Settings for connecting to institutions Ldap service.';
$string['auth_pubcookie_ldap_bind_user_key'] = 'Ldap bind User';
$string['auth_pubcookie_ldap_bind_user'] = 'User to bind with for LDAP Lookup';

$string['auth_pubcookie_ldap_bind_password_key'] = 'Ldap bind Password';
$string['auth_pubcookie_ldap_bind_password'] = 'Password for LDAP Lookup';
$string['auth_pubcookie_change_password_url_key'] = 'Change Password URL';
$string['auth_pubcookie_change_password_url'] = 'Institution\'s external change password URL';
$string['auth_pubcookie_ldap_bind_url'] = 'ex: "ldaps://directory.srv.ualberta.ca"';
$string['auth_pubcookie_ldap_bind_url_key'] = 'Ldap Bind URL';
$string['auth_pubcookie_ldap_bind_rdn'] = 'Used for binding to ldap service. ex: "ou=ldapaccess,dc=ualberta,dc=ca"';
$string['auth_pubcookie_ldap_bind_rdn_key'] = 'Ldap bind Domain';
$string['auth_pubcookie_ldap_bind_cn'] = 'Used for binding to ldap service ex: "cn="';
$string['auth_pubcookie_ldap_bind_cn_key'] = 'Ldap bind search string';
$string['auth_pubcookie_user_title'] = "Regular User Settings";
$string['auth_pubcookie_user_details'] = "These users are allowed to authenticate if the lookup succeeds and will be created if the 'user create' option is set to 'yes'.";
$string['auth_pubcookie_user_ldap_search_string'] = 'Used for querying users to authenticate. ex: "uid="';
$string['auth_pubcookie_user_ldap_search_string_key'] = 'Ldap user search string';
$string['auth_pubcookie_user_ldap_rdn'] = 'Used for querying users to authenticate. ex: "ou=people,dc=ualberta,dc=ca"';
$string['auth_pubcookie_user_ldap_rdn_key'] = 'Ldap user search domain';
$string['auth_pubcookie_user_ldap_filter'] = 'Used for querying users to authenticate. ex: "(&(uid=*)(ou=ais))"';
$string['auth_pubcookie_user_ldap_filter_key'] = 'Ldap user search filter';
$string['auth_pubcookie_user_ldap_scope'] = 'Used for querying users to authenticate. ex: "*"';
$string['auth_pubcookie_user_ldap_scope_key'] = 'Ldap user search scope';
$string['auth_pubcookie_secondary_user_title'] = "Special secondary user settings";
$string['auth_pubcookie_secondary_user_details'] = "These users are allowed to be authenticated if and only if an account already exists on the system.";
$string['auth_pubcookie_secondary_user_ldap_search_string'] = 'Used for querying special users to authenticate. ex: "uid="';
$string['auth_pubcookie_secondary_user_ldap_search_string_key'] = 'Ldap secondary user search string';
$string['auth_pubcookie_secondary_user_ldap_rdn'] = 'Used for querying special users to authenticate. ex: "ou=people,dc=ualberta,dc=ca"';
$string['auth_pubcookie_secondary_user_ldap_rdn_key'] = 'Ldap secondary user search domain';
$string['auth_pubcookie_secondary_user_ldap_filter'] = 'Used for querying special users to authenticate. ex: "(&(uid=*)(ou=ais))"';
$string['auth_pubcookie_secondary_user_ldap_filter_key'] = 'Ldap secondary user search filter';
$string['auth_pubcookie_secondary_user_ldap_scope'] = 'Used for querying special users to authenticate. ex: "*"';
$string['auth_pubcookie_secondary_user_ldap_scope_key'] = 'Ldap secondary user search scope';


$string['auth_pubcookie_logoutconfirmmessage'] = 'Warning:
Clicking ‘Continue ‘ below will only log you out of eClass - all other university web applications will remain authenticated.
<div style="color:red">Be sure to fully close your browser to log out of all U of A web apps, especially if you are on a public computer in a library or lab.</div>';

$string['pluginname'] = 'Pubcookie';
$string['pluginnotenabled'] = 'Plugin not enabled!';
