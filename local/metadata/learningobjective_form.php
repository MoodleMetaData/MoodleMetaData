<?php
global $PAGE, $CFG, $DB;
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';
require_once 'lib.php';

class learningobjective_form extends moodleform {

    function definition() {
        global $CFG, $USER; //Declare our globals for use
        $mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.
	$objnum=0;
        // Form elements
        //$context = context_course::instance($csid);
        //Assumes it has the data added
        $lobjectives = get_table_data_for_course('courseobjectives');

	//set up the main learning objective and relative buttons
	$mform->registerNoSubmitButton('change_obj_name');
	$mform->registerNoSubmitButton('load_existed');
	$mform->registerNoSubmitButton('delete_obj');
	$mainobjarray=array();
        $mainobjarray[] =& $mform->createElement('text', 'mainobjname', get_string('mainobjtext','local_metadata'));
 	$mainobjarray[] =& $mform->createElement('submit', 'load_existed', get_string('objload','local_metadata'));
	$mainobjarray[] =& $mform->createElement('button', 'add_subobj', get_string('objadd','local_metadata'),'onclick="addsubobjfunction()"');
	$mainobjarray[] =& $mform->createElement('submit', 'change_obj_name', get_string('objsave','local_metadata'));
	$mainobjarray[] =& $mform->createElement('submit', 'delete_obj', get_string('objdelete','local_metadata'));


	//set up the loading elements
        $subobjeditgroup = array();
        $mform->registerNoSubmitButton('edit_subobj');
	$subobjeditgroup[] =& $mform->createElement('submit', 'edit_subobj', get_string('objedit',,'local_metadata'));

	//add basic element to the page
	$mform->addGroup($mainobjarray, 'mainobj', get_string('mainobjective_name','local_metadata'), array(' '), false);      
        $mform->addElement('html', '<div id="mainobjdiv">');
 	
	//if click load => load all the sub objective from database
        if(isset($_POST['load_existed'])){
                $currentname = $_POST['mainobjname'];
		$subobjlist = learningobjective_form::get_existing_subobj($currentname);
		$subobjeditgroup[] =& $mform->createElement('select', 'subobjlist', get_string('subobj_name', 'local_metadata'), $subobjlist);
		$mform->addGroup($subobjeditgroup, 'subobjedit', get_string('subobj_list','local_metadata'), array(' '), false);
        	$mform->addElement('html', '<hr>');
        }
	//change the name of the current learning objective
	if(isset($_POST['change_obj_name'])){
		$currentname = $_POST['mainobjname'];
		learningobjective_form::save_name_changed();
		echo '<script type="text/javascript">', 'alert("new objective name: ('.$currentname.') saved");' , '</script>';
	 }
	//if click edit button => load the page with the chosen sub ohjective name 
        if(isset($_POST['edit_subobj'])){            
		$selected = $_POST['subobjlist'];
		$_POST['mainobjname'] = $selected;
        }  
	//delete the current objective based on the input objective name
        if(isset($_POST['delete_obj'])){
		learningobjective_form::delete_learning_objective($_POST['mainobjname']);
                $empty = '';
                $_POST['mainobjname'] = $empty;
        }


?>
<script type="text/javascript">
var subnum = 0;
function addsubobjfunction() {
    var subobjelement = document.createElement("input");
 
    //Assign different attributes to the element.
    subobjelement.setAttribute("type", "text");
    subobjelement.setAttribute("name", "subobj");
    subobjelement.setAttribute("value", "subobj"+subnum);
    subobjelement.setAttribute("id", "subobj"+subnum);

    var mainobjsubdiv = document.getElementById("mainobjdiv");
    var text = document.createTextNode('sub objective to be added: ');
    var newptag = document.createElement("p");
    newptag.setAttribute("id","subobjptag"+subnum);
    newptag.appendChild(subobjelement);    
    subobjelement.parentNode.insertBefore(text, subobjelement);


    //Append the element in page (in span).
    mainobjsubdiv.appendChild(newptag);
    mainobjsubdiv.appendChild(document.createElement('hr'));
    subnum += 1;
};

</script>
<?php


        $this->add_action_buttons();
    }


    function definition_after_data() {
        parent::definition_after_data();
        $mform =& $this->_form;        
//	$objnum= 0;
//      if(isset($_POST['add_subobj'])){
//		$mform->registerNoSubmitButton('addsubobj'.$objnum);
// 	      	$mainobjarray=array();
//        	$mainobjarray[] =& $mform->createElement('text', 'objectivename', get_string('lobjective_name','local_metadata'));
//        	$mainobjarray[] =& $mform->createElement('submit', 'addsubobj'.$objnum, get_string('add'));
//        	$mform->addGroup($mainobjarray, 'mainobj', get_string('lobjective_name','local_metadata'), array(' '), false);	
//		$mform->addElement('html', '<br><br><hr>');
//          	$objunm +=1;
//      }
    }
    
    public static function get_existing_subobj($parentobjname){
    	$subobjlist = array();
	$subobjlist = array('programming','algorithm','framework',$parentobjname);
        return $subobjlist;
    }

    public static function save_name_changed(){
    	   
    }


    public static function delete_learning_objective($deleteobjname){
    	   echo '<script type="text/javascript">', 'alert("objective: ('.$deleteobjname.') deleted!");' , '</script>';
    }


    //If you need to validate your form information, you can override  the parent's validation method and write your own.	
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        global $DB, $CFG, $USER; //Declare them if you need them

        //if ($data['data_name'] Some condition here)  {
        //	$errors['element_to_display_error'] = get_string('error', 'local_demo_plug-in');
        //}
        return $errors;
    }
}

?>
