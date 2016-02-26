<?php
/**
 * Blackboard is the class used to communicate with vista through web services and sso functions
 *
 */
require_once(dirname(dirname(__FILE__)) . "/config.php"); //("/var/www/moodle/moodle_central/portal/config.php");

class BlackBoard {
    // This array holds the different institutions that can be used with web services
    private $institution_glcid = array('slpd'=>'URN:X-WEBCT-VISTA-V1:098f65e3-8180-aabb-0151-a1d5e587b2a9','uofa'=>'URN:X-WEBCT-VISTA-V1:bb3dfef8-8180-aabb-017f-98034a3fb5c9');
    // Currently the same as the user's ccid but we still retrieve this in case that is not the case
    private $consortia_id = null;
    // Vista host
    private $webct_hostname = 'vista4.srv.ualberta.ca';
    private $port = '443';
    private $protocol = 'https';
    private $query_path = '/webct/systemIntegrationApi.dowebct?';

    // Secret Key
    private $secret = '';
    // GLCID - set on instantiation
    private $glcid = null;
    // User's ccid
    private $userid;

    /**
     * Constructor - sets the glcid and user
     *
     * @param  $userid ccid of the user
     * @param  $institution institution to communicate with @see $institution_glcid
     */
    function __construct($userid, $institution) {
        global $CFG;

        $this->userid = $userid;
        $this->glcid = $this->institution_glcid[$institution];
        if(empty($CFG->bbsecret)) {
            $this->secret = '';
        } else {
            $this->secret = $CFG->bbsecret;
        }
    }

    /**
     * @param  $userid ccid of the user to set
     * @return void
     */
    function set_webct_id($userid) {
        $this->userid = $userid;
    }

    /**
     * Get webct_id
     *
     * @return ccid
     */
    function get_webct_id() {
        return $this->userid;
    }

    /**
     * Retrieves the vista courses that user is enrolled in and gives the html code needed to generate a course list in moodle
     *
     * @return array
     */
    function getMyWebtCt() {
        require_once 'Zend/XmlRpc/Client.php';

        global $CFG;
        require_once($CFG->dirroot . '/eclass/logger/Logger.php');
        if (!isset($CFG->logger)) {
            $CFG->logger = new Logger('dummylogger');
        }
        if ($CFG->logger->isDebugEnabled()) {
            $CFG->logger->debug("Entered getMyWebCt()");
        }
        //            var_dump($instances);

        $b_found_extra = FALSE; // flag for testing if we find extra courses so we can set the more flag.

        $query_params = array(
            'adapter' => 'standard',
            'action' => 'get',
            'option' => 'mywebct_xml',
            'webctid' => $this->userid,
            'timestamp' => '' . time(),
            'auth' => '', //<32_byte_MAC>
            'glcid' => 'URN:X-WEBCT-VISTA-V1:bb3dfef8-8180-aabb-017f-98034a3fb5c9',
        );

        $mac = $this->calculateMac(array($query_params['action'], $query_params['option'], $query_params['webctid'], $query_params['timestamp'], $query_params['glcid']), $this->secret);

        $query_params['auth'] = $mac;
        $query_string = '';
        foreach ($query_params as $param => $value) {
            $query_string .= '&' . $param . '=' . $value;
        }
        $server = $this->protocol . "://" . $this->webct_hostname;
        $full_url = $server . $this->query_path . $query_string;
        //            echo $server_url;
        if ($CFG->logger->isDebugEnabled()) {
            $CFG->logger->debug("Full fetch URL: " . $full_url);
        }
        $config = array('ssltransport' => 'tls');
        $client = new Zend_Http_Client($full_url, $config);
        $a_eclass_courses_html = array();

        try {
            $response = $client->request();
            //get rid of newlines
            $response = preg_replace('/\n/', '', $response);
            //strip bad stuff before the xml
            $response = preg_replace('/.*<homearea/', '<homearea', $response, -1, $count);
            //escape the &s as they cause parsing problems
            $response = preg_replace('/&/', '&amp;', $response);
            //parse the xml
            $xml_response = new SimpleXMLElement($response);
            $enrollments = $xml_response->xpath('//enrollment/lctxt');
            //create course html
            foreach ($enrollments as $enrollment) {
                //                    var_dump($enrollment);
                $href = $enrollment->href1;
                $href = preg_replace('/&amp;/', '&', $href);
                $sso_link = $this->getSSOLink($server . $href);
                $sso_link = preg_replace('/&/', '&amp;', $sso_link);

                if ($CFG->logger->isDebugEnabled()) {
                    $CFG->logger->debug("Vista Enrollment URL: " . $server . $href);
                    $CFG->logger->debug("Vista Enrollment SSO URL: " . $sso_link);
                }
                $html = "<div class='box coursebox'>";
                //                $html .= "<h3 class='main'><a href='" . ${server} .$enrollment->href1 ."'>$enrollment->text1</a></h3>";
                $html .= "<h3 class='main'><a href='" . $sso_link . "' target='vista_courses'>WebCT - $enrollment->text1</a></h3>";
                $html .= "</div>";
                $a_eclass_courses_html[] = $html;
            }

            return $a_eclass_courses_html;
        } catch (Exception $e) {
            if ($CFG->logger->isDebugEnabled()) {
                $CFG->logger->debug("Exceptions caught: " . $e->getMessage());
            }
        }
    }

    /**
     * Sets the consortia id of the user by calling the web service function.  Currently Vista stores the consortia id
     * as the ccid of the user.  This function is used to avoid corner cases where this is not true.
     *
     * @return bool returns true if the consortia id was returned and set, false otherwise
     */
    function setConsortiaId() {
        // Retrieve the consortia id which will be used for wrapping links in order to SSO the links for the logged in user.
        //$this->webct_hostname='ssl://' . $this->webct_hostname;
        if ($this->protocol == 'https') {
            $webct_hostname = 'ssl://' . $this->webct_hostname;
        }
        $GET_CID = fsockopen($webct_hostname, $this->port, $errCode, $errStr, 30);
        if (!$GET_CID) {
            return false;
        }

        $now = time();
        $mac = $this->calculateMac(array('get', 'consortia_id', $this->userid, $now, $this->glcid), $this->secret);

        $postUrl = '/webct/systemIntegrationApi.dowebct?adapter=standard' .
                   '&operation=get' .
                   '&option=consortia_id' .
                   '&webctid=' . $this->userid .
                   '&timestamp=' . $now .
                   '&glcId=' . $this->glcid .
                   '&auth=' . $mac;

        $requestStr = "POST $postUrl HTTP/1.1\r\n" .
                      "Host: $webct_hostname\r\n" .
                      "Content-type: application/x-www-form-urlencoded\r\n" .
                      "Connection: Close\r\n" .
                      "\r\n";
        fputs($GET_CID, $requestStr);
        // get the response headers
        $headerStr = '';
        while ($str = trim(fgets($GET_CID, 4096)))
        {
            $headerStr .= $str . "\n";
        }
        $headers = explode("\n", $headerStr);

        // get the response body
        $body = '';
        while (!feof($GET_CID))
        {
            $body .= fread($GET_CID, 4096);
        }
        fclose($GET_CID);
        // strip the XML tags
        $count = preg_match('/<consortiaid>(.*)<\/consortiaid>/', $body, $found);

        if ($count == false || $count == 0) {
            $this->consortia_id = null;
            return false;
        }
        else {
            $this->consortia_id = $found[1];
            return true;
        }
    }

    /**
     * Get the SSO link of a target link. Takes the given link and wraps it with an sso link.
     *
     * @param  $url the target url
     * @return string the sso'd link
     */
    function getSSOLink($url) {
        if ($this->consortia_id == null) {
            //$this->consortia_id = $this->userid;
            $this->consortia_id = $this->setConsortiaId();// (Should set the consortiaID but since userid is set to the same thing it doesn't matter
            if(!$this->setConsortiaId()) {
                return 'index.php?err=1';
            }
        }

        // GLCID is NOT included in the mac for this scenario...SSO
        $now = time();
        $sso_url = $this->protocol . '://' . $this->webct_hostname .
                   '/webct/public/autosignon?' . 'wuui=' . $this->consortia_id .
                   '&timestamp=' . $now . '&url=' . urlencode($url) .
                   '&glcid=' . $this->glcid . '&mac=' . $this->calculateMac(array($this->consortia_id, $now, $url), $this->secret);
        return $sso_url;
    }

    /**
     * Calculates the MAC needed to generate the sso link and web service calls
     *
     * @param  $params the list of parameters that needs to be included in the mac
     * @param  $secret super secret password for mac
     * @return string the mac
     */
    function calculateMac($params, $secret) {
        // get ascii of all param values
        $data = implode('', $params);
        $asciivalue = 0;
        $size = strlen($data);
        for ($i = 0; $i < $size; $i++)
        {
            $asciivalue += ord(substr($data, $i, 1));
        }
        // get md5 of ascii value and secret
        $mac = md5($asciivalue . $secret);
        return $mac;
    }

    /**
     * GLCID override in case the institution is not in the list.
     *
     * @param  $glcid glcid to set
     * @return void
     */
    function setGlcid($glcid) {
        $this->glcid = $glcid;
    }
}
