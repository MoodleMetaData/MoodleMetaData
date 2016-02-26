<?php

 /**
 * Gets IMS information
 */

class IMS {
    private $a_info;
    private $auth_user;
    private $auth_password;
    private $ldap_rh;

    /**
     * Constructor
     * @return
     * @param $uname String
     * @param $pass String
     */
    function __Construct($uname, $pass) {
        $this->auth_user = trim($uname);
        $this->auth_password = trim($pass);
        try {
            $rdn = "";
//            $rdn .= "uid=$ccid,";
            $rdn .= "cn=$this->auth_user,";
//            $rdn .= "ou=people,dc=ualberta,dc=ca";
            $rdn .= "ou=ldapaccess,dc=ualberta,dc=ca";
            $this->ldap_rh = ldap_connect("ldaps://directory.srv.ualberta.ca");
            if (!ldap_bind($this->ldap_rh, $rdn, $this->auth_password)) {
                throw new Exception('LDAP bind failed');
            }
        }
        catch (Exception $e) {
            throw new Exception("IMS Authentication failed: " . $e->getMessage());
        }
    }

    function close() {
        if ($this->ldap_rh) {
            ldap_close($this->ldap_rh);
        }
    }

    /**
     * Gets the user role details for a uid
     * @throws Exception
     * @param  $uid
     * @return IMSRole
     */
    function get_user_info($uid) {
        if ($this->ldap_rh) {

            $rdn = "";
            $rdn .= "uid=$uid,";
            $rdn .= "ou=people,dc=ualberta,dc=ca";
            //                $filter = "(&(employeetype=*))";
            $filter = "(&(uid=*))";
            $scope = array("*");
            if (!($sr = ldap_search($this->ldap_rh, $rdn, $filter, $scope))) {
                throw new Exception('LDAP search failed');
            }
            if (!($info = ldap_get_entries($this->ldap_rh, $sr))) {
                throw new Exception('LDAP get_entries failed');
            }
            return $this->process_results($info);

        }
        else {
            throw new Exception('LDAP Connection handle not initialized.');
        }
    }

    /**
     * Converts the Returned LDAP search result object into a better organized IMSRole object
     * @param  $info
     * @return IMSRole Object
     */
    private function process_results($info) {
        $a_results = new IMSRole();
        foreach ($info as $role) {
            //this check is to avoid non-roles, roles should always be arrays
            if (is_array($role)) {
                //                $a_results = new IMSRole();
                //go through each role
                foreach ($role as $role_attr_key => $role_attr_val) {
                    //if its something we're looking for store it in the normal place
                    if (property_exists($a_results, $role_attr_key)) {
                        //its not a singlular property like a string
                        if (is_array($role_attr_val)) {
                            //if there are multiple values we'll store them as an array for the key.
                            if (isset($role_attr_val['count']) && $role_attr_val['count'] > 1) {
                                unset($role_attr_val['count']);
                                IMS::check_and_combine($role_attr_val, $a_results->$role_attr_key);

                            } //otherwise store the singular value
                            else {
                                IMS::check_and_combine($role_attr_val[0], $a_results->$role_attr_key);
                            }
                        } //just store the singular value
                        else {
                            IMS::check_and_combine($role_attr_val, $a_results->$role_attr_key);
                        }
                    } //store the unknown or wanted attribute in our special field
                    else {
                        $a_results->extra_fields[$role_attr_key] = $role_attr_val;
                    }
                }
            }
        }
        return $a_results;
    }

    /**
     * Checks if the value is equivalent or exists in the property and correctly combines it.
     * @param  $value
     * @param  $property
     * @return void
     */
    private static function check_and_combine($value, &$property) {
        if (!isset($value)) {
            return;
        }
        if (is_array($property)) {
            if (!array_search($value, $property)) {
                $property[] = $value;
            }
        }
        elseif (isset($property)) {
            if ($value != $property) {
                $t_prop = $property;
                $property = array($value, $t_prop);

            }
        }
        else {
            $property = $value;
        }
    }

}


class IMSRole extends stdClass {
    function _Construct() {
        $this->extra_fields = array();
    }

    public $employeetype;
    public $uid;
    public $cn;
    public $sn;
    public $departmentnumber;
    public $givenname;
    public $displayname;
    public $uofarti;
    public $employeenumber;
    public $uofagoogle;
    public $info;
    public $mail;
    /**
     * @var capture the unhandled values
     */
    public $extra_fields;
}

?>