<?php
function get_remote_coursemodule_from_instance($modulename, $instance, $location, $courseid=0, $sectionnum=false, $strictness=IGNORE_MISSING) {
    global $DB, $CFG, $USER;


    $eCache = new EclassCache();
    //$eCache->expire("events".$SESSION->cal_show_global.$SESSION->cal_show_groups.$SESSION->cal_show_course.$SESSION->cal_show_user);
    $data = $eCache->getData($modulename.$instance.$location);

    if($data != ECLASS_CACHE_EXPIRED){
        return $data;
    }

    require_once($CFG->dirroot.'/local/eclass/lib/session_storage.php');

    $satellites = eclass_getUserInstances($USER->username);
    //set web service url server => token need to be passed in the url
    $response = false;

    foreach($satellites as $satellite) {
        if($location == $satellite->url) {
            $serverurl = $satellite->url . "/webservice/xmlrpc/server.php" . '?wstoken=' . $satellite->token;

            //create the xmlrpc client instance
            require_once 'Zend/XmlRpc/Client.php';
            $xmlrpcclient = new Zend_XmlRpc_Client($serverurl);

            $params = array('modulename' => $modulename,
                            'instance' => $instance,
            );

            //make the web service call
            $function = 'eclass_get_cm';
            try {
                $response = $xmlrpcclient->call($function, $params);

            } catch (Exception $e) {
                var_dump($e);
            }

        }

    }
    $response = (object) $response;

    $eCache = new EclassCache();
    $eCache->setData($modulename.$instance.$location, 5, $response);
    return $response;
}

/**
 * Get remote calendar events
 * @param int $tstart Start time of time range for events
 * @param int $tend   End time of time range for events
 * @param array/int/boolean $users array of users, user id or boolean for all/no user events
 * @param array/int/boolean $groups array of groups, group id or boolean for all/no group events
 * @param array/int/boolean $courses array of courses, course id or boolean for all/no course events
 *
 * @return array of selected remote events + local events or return the events passed to this function (local)
 */
function get_remote_events($tstart, $tend, $users, $event_index) {
    global $DB, $USER, $CFG, $SESSION;
    $events = array();

    if(is_string($users) || is_int($users)) {
        // get instances
        require_once($CFG->dirroot.'/local/eclass/lib/session_storage.php');

        $instances = eclass_getUserInstances($USER->username);

        foreach($instances as $instance) {

            $serverurl = $instance->url . "/webservice/xmlrpc/server.php" . '?wstoken=' . $instance->token;

            require_once 'Zend/XmlRpc/Client.php';
            $xmlrpcclient = new Zend_XmlRpc_Client($serverurl);

            //make the web service call
            $function = 'eclass_get_events';

            $params = array('userid' => $USER->username,'start' => $tstart, 'end' => $tend, 'cal_global' => (int)$SESSION->cal_show_global, 'cal_groups' => (int)$SESSION->cal_show_groups, 'cal_course' => (int)$SESSION->cal_show_course, 'cal_user' => (int)$SESSION->cal_show_user);

            try {

                $remote_events = $xmlrpcclient->call($function, $params);
                foreach($remote_events as $anevent) {
                    $anevent = (object)$anevent;
                    $events[$event_index] = $anevent;
                    $events[$event_index]->id = $event_index;
                    $event_index++;
                }

            } catch (Exception $e) {
                var_dump($e);
            }
        }

    }
    if(!empty($events)) {
        $event_cache = new StdClass();


        $event_cache->events = $events;
        $event_cache->s_time = $tstart;
        $event_cache->e_time = $tend;

        $eCache = new EclassCache();
        $eCache->setData("events".$SESSION->cal_show_global.$SESSION->cal_show_groups.$SESSION->cal_show_course.$SESSION->cal_show_user, 5, $event_cache);

    }
    return $events;
}