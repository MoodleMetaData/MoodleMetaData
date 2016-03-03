<?php
global $PAGE, $CFG, $DB;
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';
require_once 'lib.php';


class learningobjective_form extends moodleform {

    function definition() {
        global $CFG, $USER, $PAGE; //Declare our globals for use
        $mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.
        // Form elements
        //Assumes it has the data added

	$editid = optional_param('editid', 0, PARAM_INT);

	
	$currentcourseid = get_course_id();
	if($editid >0){
           $mform->addElement('html', '<p>current objective id: '.$editid.'</p>');
           echo '<script type="text/javascript">', 'alert("'.$editid.'");' , '</script>';
	}else{ 
//	   echo '<script type="text/javascript">', 'alert("'.$editid.'");' , '</script>';
	   $mform->addElement('html', '<p>current course id: '.$currentcourseid.'</p>');
	}

        $mform->addElement('html', '<p>current objective id: '.$editid.'</p>');

        $mainobjList = learningobjective_form::load_main_page($currentcourseid);

	//if click load => load all the sub objective from database
        if(isset($_POST['load_existed'])){
                $currentname = $_POST['mainobjname'];
		$subobjlist = learningobjective_form::get_existing_subobj($currentname);
        	$subobjeditgroup = array();
        	$mform->registerNoSubmitButton('edit_subobj');
        	$subobjeditgroup[] =& $mform->createElement('submit', 'edit_subobj', get_string('objedit','local_metadata'));
		$subobjeditgroup[] =& $mform->createElement('select', 'subobjlist', get_string('subobj_name', 'local_metadata'), $subobjlist);
		$mform->addGroup($subobjeditgroup, 'subobjedit', get_string('subobj_list','local_metadata'), array(' '), false);
		$mform->addElement('html', '<hr>');
        }
	//change the name of the current learning objective
	if(isset($_POST['change_obj_name'])){
		$currentname = $_POST['mainobjname'];
		learningobjective_form::save_name_changed($_POST['loadedname'],$currentname);
	 }
	//if click edit button => load the page with the chosen sub ohjective name 
        if(isset($_POST['edit_subobj'])){            
		$selected = $_POST['subobjlist'];
		$_POST['mainobjname'] = $selected;
	//	$_POST['loadedname']=$selected;
        }  
	//delete the current objective based on the input objective name
        if(isset($_POST['delete_obj'])){
		learningobjective_form::delete_learning_objective($_POST['loadedname']);
                $empty = '';
                $_POST['mainobjname'] = $empty;
	//	$_POST['loadedname']='name to be loaded';
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
/*
        //set up the main learning objective and relative buttons
        $mform->registerNoSubmitButton('change_obj_name');
        $mform->registerNoSubmitButton('load_existed');
        $mainobjarray=array();
	$mainobjarray[] =& $mform->createElement('text', 'mainobjname', get_string('mainobjtext','local_metadata'));
        $mainobjarray[] =& $mform->createElement('submit', 'load_existed', get_string('objload','local_metadata'));
        $mainobjarray[] =& $mform->createElement('button', 'add_subobj', get_string('objadd','local_metadata'),'onclick="addsubobjfunction()"');
        $mainobjarray[] =& $mform->createElement('submit', 'change_obj_name', get_string('objsave','local_metadata'));
       	$mform->addGroup($mainobjarray, 'mainobjarray', get_string('subobjective_name','local_metadata'), array(' '), false);

*/
    }


    function load_main_page($courseid){  
        global $DB;
        $mform =& $this->_form;
	$mycourseid = get_course_id();
        $mainlearningObjectives = get_course_learning_objectives();
        $mainobjList = array();
        foreach ($mainlearningObjectives as $mainlearningObjective) {
            $mainobjList[$mainlearningObjective->id] = $mainlearningObjective->objectivename;
        }

        //set up the main learning objective and relative buttons
        $newobjgrp=array();
//        $mform->registerNoSubmitButton('add_newobj');
       	$newobjgrp[] =& $mform->createElement('text', 'newobjname');
        $newobjgrp[] =& $mform->createElement('submit', 'add_newobj', get_string('objadd','local_metadata')); 
        //add basic element to the page
        $mform->addGroup($newobjgrp, 'newobjgrp', get_string('newobj_add_grp','local_metadata'), array(' '), false);
        $mform->addElement('html', '<div id="mainobjdiv">');


        foreach ($mainobjList as $objid=>$mainobjele) {
		$url='';
		$url = new moodle_url('/local/metadata/insview.php', array('id'=>$mycourseid));
//	      echo '<script type="text/javascript">', 'alert("url:'.$url.'");' , '</script>';

	    $mform->addElement('html','<div>   '.$mainobjele.'  <a href="'.$url.'?editid='.$objid.'#tab=3">edit</a>
	    <a href="#?deleteid='.$objid.'">delete</a></div>');
        }


        if(isset($_POST['add_newobj'])){
               $newobjname = $_POST['newobjname'];

 	       $ifexisted = $DB->count_records_sql('SELECT count(*) FROM {learningobjectives} WHERE objectivename = ?', 
                       array($newobjname));
	       if ($ifexisted>0) {
	       	       $ifassoexisted = $DB->count_records_sql('SELECT count(*) FROM {learningobjectives} lo,{courseobjectives} co WHERE lo.objectivename = ?AND lo.id = co.objectiveid',array($newobjname));
		       if($ifassoexisted>0){
				echo '<script type="text/javascript">', 'alert("objective alreay existed!");' , '</script>';
			}else{
				echo '<script type="text/javascript">', 'alert("'.$ifassoexisted.'");' , '</script>';
			}
       	       }else{
//	       	   echo '<script type="text/javascript">', 'alert("objective not existed!");' , '</script>';
		   
                    $newobj = new stdClass();
                    $newobj->objectivename = $newobjname;
                    //$newLink->parentid = $objectiveId;
                    $newobjid = $DB->insert_record('learningobjectives', $newobj);
		    echo '<script type="text/javascript">', 'alert("newid:'.$newobjid.'");' , '</script>';

                    $newcourseobj = new stdClass();
                    $newcourseobj->objectiveid = $newobjid;
		    $newcourseobj->courseid = $courseid;
                    //$newLink->parentid = $objectiveId;
                    $newcourseobjid=$DB->insert_record('courseobjectives', $newcourseobj);


	       }
      }


  	return $mainobjList;
  }

  function recursively_delete_records($deleteId){
  	   /*
	   $childnum = $DB->count_records_sql('SELECT count(*) FROM {learningobjectives} WHERE parentid = ?',
	   if($childnum > 0){
	        //learningobjective_form::recursively_delete_records($
	   }else{
		//return;
	   }
*/

  }
    
    public static function get_existing_subobj($parentobjname){
    	$subobjlist = array();
	$subobjlist = array('programming','algorithm','framework',$parentobjname);
        return $subobjlist;
    }

    public static function save_name_changed($oldname, $newname){
    	echo '<script type="text/javascript">', 'alert("new objective name: ('.$newname.') saved");' , '</script>';
 //       $_POST['loadedname']=$newname;
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
