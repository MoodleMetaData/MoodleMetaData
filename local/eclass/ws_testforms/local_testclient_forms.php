<?php
/**
 * User: ggibeau
 * Date: 11-03-22
 * Time: 1:35 PM
 *
 * This file is intended for web service test forms which are associated with web services created in the
 * local directory.  Any function which needs to be included in the moodle testclient needs to have a class defined
 * below. This file is included by:
 *
 * /admin/webservices/testclient_forms.php
 */



/*
  *  instance_lookup - local/instance_lookup/*
  */

class eclass_instance_lookup_form extends moodleform {
    public function definition() {
        global $CFG;

        $mform = $this->_form;


        $mform->addElement('header', 'wstestclienthdr', get_string('testclient', 'webservice'));

        //note: these values are intentionally PARAM_RAW - we want users to test any rubbish as parameters
        $data = $this->_customdata;
        if ($data['authmethod'] == 'simple') {
            $mform->addElement('text', 'wsusername', 'wsusername');
            $mform->addElement('text', 'wspassword', 'wspassword');
        } else  if ($data['authmethod'] == 'token') {
            $mform->addElement('text', 'token', 'token');
        }

        $mform->addElement('hidden', 'authmethod', $data['authmethod']);
        $mform->setType('authmethod', PARAM_SAFEDIR);

        /// specific to the create users function
        $mform->addElement('text', 'sourceid', 'sourceid');
        $mform->addElement('text', 'ccid1', 'ccid1');
        $mform->addElement('text', 'ccid2', 'ccid2');
        $mform->addElement('text', 'ccid3', 'ccid3');

        $mform->addElement('hidden', 'function');
        $mform->setType('function', PARAM_SAFEDIR);

        $mform->addElement('hidden', 'protocol');
        $mform->setType('protocol', PARAM_SAFEDIR);



        $mform->addElement('static', 'warning', '', get_string('executewarnign', 'webservice'));

        $this->add_action_buttons(true, get_string('execute', 'webservice'));
    }

    public function get_params() {
        if (!$data = $this->get_data()) {
            return null;
        }

        // remove unused from form data
        unset($data->submitbutton);
        unset($data->protocol);
        unset($data->function);
        unset($data->wsusername);
        unset($data->wspassword);
        unset($data->token);
        unset($data->authmethod);
        unset($data->customfieldtype);
        unset($data->customfieldvalue);

        $ccids = array();

        if($data->ccid1 != '') {
            $ccids[] = $data->ccid1;
        }
        if($data->ccid2 != '') {
            $ccids[] = $data->ccid2;
        }
        if($data->ccid3 != '') {
            $ccids[] = $data->ccid3;
        }


        $params = array();
        $params['source'] = $data->sourceid;
        $params['userids'] = $ccids;

        return $params;
    }
}