<?php

require_once($CFG->dirroot . "/config.php");

class BlackBoard {

    private $consortia_id;

    private $webct_hostname = 'vista4.srv.ualberta.ca';
    private $port = '443';
    private $protocol = 'https';
    private $query_path = '/webct/systemIntegrationApi.dowebct?';

    private $glcid = 'URN:X-WEBCT-VISTA-V1:bb3dfef8-8180-aabb-017f-98034a3fb5c9';

    private $secret = '';

    private $userid;

    function __construct($userid) {
        global $CFG;

        $this->userid = $userid;
        if(empty($CFG->bbsecret)) {
            $this->secret = '';
            debugging("Blackboard Vista Authentication Failed: NO SECRET",DEBUG_DEVELOPER);
        } else {
            $this->secret = $CFG->bbsecret;
        }
    }

    function set_webct_id($userid) {
        $this->userid = $userid;
        $this->consortia_id = null;
    }

    function get_webct_id() {
        return $this->userid;
    }

    /**
     * Retrieves
     * @return array
     */
    function getMyWebtCtHTML() {
        $a_eclass_courses_html = array();
        $server = $this->protocol . "://" . $this->webct_hostname;
        $links = $this->getMyWebctCourseLinks();
        foreach ($links as $link) {
            $dyn_link = $this->getDynamicSSOLink($server . $link->href);
//            $dyn_link = preg_replace('/&/', '&amp;', $dyn_link);
            $html = "<div class='box coursebox'>";
            $html .= "<h3 class='main'><a target='eclass_vista' href='" . $dyn_link . "'>$link->text</a></h3>";
            $html .= "</div>";
            $a_eclass_courses_html[] = $html;
        }
        return $a_eclass_courses_html;
    }

    /**
     * Takes a url and returns a url for dynamic sso linking
     * @param $url target url to redirect to
     * @return string url
     */
    function getDynamicSSOLink($url){
        global $CFG;
        if(!is_string($url)){
            return '';
        }
        $safe_url = urlencode($url);
        return $CFG->wwwroot . "/blocks/eclass_course_overview/sso_to_vista.php?target=". $safe_url;
    }

    /**
     * Retrieves the MyWebct page from vista. returns the naked hrefs and associated information.
     * Uses BBLink object for standard interface.
     * @return BBLink[]
     */
    function getMyWebctCourseLinks() {
        global $CFG;

        if(empty($CFG->bbsecret)) {
            $link = new BBLink();
            $link->href = '';
            $link->text = 'Vista Authentication Error';
            return array($link);
        }
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
        debugging("Full fetch URL: " . $full_url, DEBUG_DEVELOPER);

        $a_eclass_course_links = array();

        // Always show a link to the eClass Blackboard Vista Homepage
        $link = new BBLink();
        $link->href = '/webct/urw/lc5122011.tp0/cobaltMainFrame.dowebct';
        $link->text = 'eClass Blackboard Vista Homepage';
        $a_eclass_course_links[] = $link;

        try {
            // Use CURL to retrieve the vista course list data (XML)
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $full_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            //get rid of newlines
            $response = preg_replace('/[\n\r]+/', '', $response, -1, $count);
            //VISTA uses this as a sudo newline character for breaking up text even in the middle of a xml tag
            //$response = preg_replace('/0f[0-9a-f][0-9a-f]/', '', $response);
            //$response = preg_replace('/--/', '', $response);
            //strip bad stuff before the xml
            $response = preg_replace('/.*<homearea/', '<homearea', $response, -1, $count);
            $response = preg_replace('/<\/homearea>.*/', '</homearea>', $response, -1, $count);
            //escape the &s as they cause parsing problems
            $response = preg_replace('/&/', '&amp;', $response);

            //if no xml present we don't want to try to process it.
            if(preg_match('{<.*>.*</.*>}',$response)){
                debugging($response,DEBUG_DEVELOPER);
                //            parse the xml
                $xml_response = new SimpleXMLElement($response);
                $enrollments = $xml_response->xpath('//enrollment/lctxt');
                //create course html
                foreach ($enrollments as $enrollment) {
                    $href = $enrollment->href1;
                    //replace the &'s
                    $href = preg_replace('/&amp;/', '&', $href);
                    $link = new BBLink();
                    $link->href = $href;
                    $link->text = $enrollment->text1;
                    $a_eclass_course_links[] = $link;

                }
            }

            //return $a_eclass_course_links;
        } catch (Exception $e) {
            debugging("Exceptions caught: " . $e->getMessage(), DEBUG_DEVELOPER);

        }
        // If there the user has no vista courses or vista XML parse failed - give an SSO link into vista anyways.

        return $a_eclass_course_links;
    }

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

    function getSSOLink($url) {
        global $CFG;
        if(empty($CFG->bbsecret)) {
            return '';
        }
        if ($this->consortia_id == null) {
            $this->setConsortiaId();
        }

        // GLCID is NOT included in the mac for this scenario...SSO
        $now = time();
        $sso_url = $this->protocol . '://' . $this->webct_hostname .
                   '/webct/public/autosignon?' . 'wuui=' . $this->consortia_id .
                   '&timestamp=' . $now . '&url=' . urlencode($url) .
                   '&glcid=' . $this->glcid . '&mac=' . $this->calculateMac(array($this->consortia_id, $now, $url), $this->secret);
        return $sso_url;
    }

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


}

class BBLink extends StdClass {
    public $href;
    public $text;
}
