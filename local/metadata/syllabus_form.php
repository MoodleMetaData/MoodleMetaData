<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';
require_once $CFG->dirroot.'\lib\tcpdf\tcpdf.php';
require_once 'lib.php';

/**
 * The form to display the tab for general information.
 */
class syllabus_form extends moodleform {
	
	function definition() {
		global $CFG, $DB, $USER; //Declare our globals for use
		global $course, $courseId;
		
		// initialize the form.
		$mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.
		
		
		$mform->registerNoSubmitButton('syllubusgenerate');

 		$mform->addElement('html','<form> generate syllabus: <input type="submit" 
				name="syllubusgenerate" value="generate"/></form>'); 
		
		
		if(isset($_POST['syllubusgenerate'])){

			$filename = $this->do_generate();
			
/* 			$mform->addElement('html','<form> 					
					click following link for download: <br>
					<a href="#" download="'.$filename.'">download</a></form>'); */
			
		}

		
		$this->add_action_buttons(true, "done generation");
	}

	//If you need to validate your form information, you can override  the parent's validation method and write your own.	
	function validation($data, $files) {
		$errors = parent::validation($data, $files);
		global $DB, $CFG, $USER; //Declare them if you need them
		
		return $errors;
    }
	
	public static function save_data($data) {
		global $CFG, $DB, $USER; //Declare our globals for use
		global $course, $courseId;
	}
	
	function do_generate(){
		global $CFG, $DB, $USER;
		global $course;
		$sessionnumber = 0;
		$instructoremail = '';
		$officehours = '';
		$officelocation = '';
		//$coursesessions = array();
		if($existCourseInfo = $DB->get_record('courseinfo', array('courseid'=>$course->id))){
			$coursetopic = $existCourseInfo->coursetopic;
			$coursedescription = $existCourseInfo->coursedescription;
			$courseInstructor = $USER->lastname.', '.$USER->firstname;
			if($existInstructorInfo = $DB->get_record('courseinstructors', array('courseid'=>$existCourseInfo->id, 'userid'=>$USER->id))){
				$officelocation = $existInstructorInfo->officelocation;
				$officehours = $existInstructorInfo->officehours;
				$instructoremail =  $existInstructorInfo->email;
			}
			$sessionnumber = $DB->count_records('coursesession', array('courseid'=>$existCourseInfo->courseid));
			//echo "<script type='text/javascript'>alert('$sessionnumber');</script>";
			if($sessionnumber>0){
				$coursesessions = $DB->get_records('coursesession', array('courseid'=>$existCourseInfo->courseid));			
			}		
		}
		
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Olaf Lederer');
		$pdf->SetTitle('TCPDF Example');
		$pdf->SetSubject('TCPDF Tutorial');
		$pdf->SetKeywords('TCPDF, PDF, example, tutorial');
		 

		// set default header data
		$pdf->SetHeaderData('',0, $course->shortname, 
				$courseInstructor, array(0,0,0), array(10,164,228));
		
	// set font
	$pdf->SetFont('times', 'BI', 10);
	
	// add a page
	$pdf->AddPage();
	
//put logistics information into the pdf------------------------------------------
	$courselogistics = <<<EOD
	Logistics	
EOD;
	$pdf->SetFont('times', 'BI', 15);
	$pdf->Write(5, $courselogistics, '', 0, 'L', true, 2, false, false, 0);
	$courselogistics = <<<EOD
	Instructor: $courseInstructor ($instructoremail)
	TAs: 
	Web Page: 
	Lecture Room & Time: 
	Office Hours:
	$officehours at $officelocation	
EOD;
	$pdf->SetFont('times', '', 10);
	$pdf->Write(0, $courselogistics, '', 0, 'L', true, 0, false, false, 0);
	
//put course overview information into the pdf------------------------------------------	
	$courseoverview = <<<EOD
	Course Overview
EOD;
	$pdf->SetFont('times', 'BI', 15);
	$pdf->Write(5, $courseoverview, '', 0, 'L', true, 0, false, false, 0);	
	$courseoverview = <<<EOD
	Course Description: $coursedescription
	Prerequsite: 
EOD;
	$pdf->SetFont('times', '', 10);
	$pdf->Write(0, $courseoverview, '', 0, 'L', true, 0, false, false, 0);
	
//put course session information into the pdf------------------------------------------
	$courselectures = <<<EOD
	Course Sessions
EOD;
	$pdf->SetFont('times', 'BI', 15);
	$pdf->Write(5, $courselectures, '', 0, 'L', true, 0, false, false, 0);
	if($sessionnumber>0){
		$pdf->SetFont('times', '', 10);
		$sessionno = 1;
		foreach ($coursesessions as $coursesession) {
			$courselectures = <<<EOD
			$sessionno: $coursesession->sessiontitle
EOD;
			$pdf->Write(0, $courselectures, '', 0, 'L', true, 0, false, false, 0);
			$sessionno ++;
		}
	}


		
	# terminat$courseInstructorur file with TCPDF output
	$pdf->Output('syllubusgenerate.pdf', 'I'); 
	$filename= "syllubusgenerate.pdf";
	return $filename;
	}
}

?>