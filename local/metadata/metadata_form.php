<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';

require_once 'lib.php';


/**
 * The form to display the tab for sessions
 *
 * Requires the argument 'sessions', which should be the array of sessions
 *   for the current course loaded from the database
 *
 * For an example, see how it is instantiated in insview.php
 *
 * To look at how deleting a recurring element is done, see definition_after_data and save_data.
 *   As well, see the elements was_deleted and deleteSession (the delete button) in add_session_repeat_template
 *
 *
 */
 
class metadata_form extends moodleform {
    
    protected $_recurring_nosubmit_buttons;
    
    
    function definition() {
        $this->_recurring_nosubmit_buttons = array();
    }
    
    
    /**
     * Checks if button pressed is not for submitting the form
     *   This overrides moodleform, and is a hack to fix the issue where recurring element buttons
     *     will be stored as an array, rather than a single item, in the url
     *     which causes a warning and causes our tests to fail
     *
     * @staticvar bool $nosubmit keeps track of no submit button
     * @return bool
     */
    function no_submit_button_pressed(){
        static $nosubmit = null; // one check is enough
        if (!is_null($nosubmit)){
            return $nosubmit;
        }
        $mform =& $this->_form;
        $nosubmit = false;
        if (!$this->is_submitted()){
            return false;
        }
        foreach ($mform->_noSubmitButtons as $nosubmitbutton){
            // Need to handle this specially, since will be an array
            if (in_array($nosubmitbutton, $this->_recurring_nosubmit_buttons)) {
                if (optional_param_array($nosubmitbutton, 0, PARAM_RAW)){
                    $nosubmit = true;
                    break;
                }

            } else {
                if (optional_param($nosubmitbutton, 0, PARAM_RAW)){
                    $nosubmit = true;
                    break;
                }
            }
        }
        return $nosubmit;
    }
}