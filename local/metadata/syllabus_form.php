<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';
require_once $CFG->dirroot.'/lib/tcpdf/tcpdf.php';
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

 		$mform->addElement('html','<form> generate syllabus: 
 				<input type="submit" name="syllubusdisplay" value="preview"/>
 				<input type="submit" name="syllubusdownload" value="download"/></form>'); 
		
		
		if(isset($_POST['syllubusdownload'])){

			$filename = $this->do_generate(2);
		}
		if(isset($_POST['syllubusdisplay'])){
		
			$filename = $this->do_generate(1);
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
	
	function GetTimeStamp($timestamp)
	{
		return  gmdate("Y-m-d", $timestamp);
	}
	
	function do_generate($optionno){
		global $CFG, $DB, $USER;
		global $course;
		$sessionnumber = 0;
		$readingnumber = 0;
		$objectivegnumber = 0;
		$assessmentnumber = 0;
		$instructoremail = 'To be assigned';
		$officehours = 'To be assigned';
		$officelocation = 'To be assigned';
		$courseInstructor = 'To be assigned';
		$phonenumber = 0;
		$instructoremail = 'To be assigned';
		$coursedescription = '';
//collecting relative data from database===============================================================================		
		if($existCourseInfo = $DB->get_record('courseinfo', array('courseid'=>$course->id))){
			//$coursetopic = $existCourseInfo->coursetopic;
			$coursedescription = $existCourseInfo->coursedescription;
			$courseInstructor = $USER->lastname.', '.$USER->firstname;
			if($existInstructorInfo = $DB->get_record('courseinstructors', array('courseid'=>$existCourseInfo->id, 'userid'=>$USER->id))){
				$officelocation = $existInstructorInfo->officelocation;
				$officehours = $existInstructorInfo->officehours;
				$instructoremail =  $existInstructorInfo->email;
				$phonenumber = $existInstructorInfo->phonenumber;
			}
		}
		$sessionnumber = $DB->count_records('coursesession', array('courseid'=>$course->id));
		if($sessionnumber>0){
			$coursesessions = $DB->get_records('coursesession', array('courseid'=>$course->id), $sort='sessiondate');
		}
			
		$readingnumber = $DB->count_records('coursereadings', array('courseid'=>$course->id));
		if($readingnumber>0){
			$coursereadings = $DB->get_records('coursereadings', array('courseid'=>$course->id));
		}
			
		$objectivegnumber = $DB->count_records('courseobjectives', array('courseid'=>$course->id));
		if($objectivegnumber>0){
			$courseobjectives = $DB->get_records('courseobjectives', array('courseid'=>$course->id));
		}
			
		$assessmentnumber = $DB->count_records('courseassessment', array('courseid'=>$course->id));
		if($assessmentnumber>0){
			$courseassessments = $DB->get_records('courseassessment', array('courseid'=>$course->id), $sort='assessmentduedate');
		}
		

		
//start pdf generation===============================================================================		
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
/* 		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Olaf Lederer');
		$pdf->SetTitle('Course Syllabus');
		$pdf->SetSubject('Syllabus PDF');
		$pdf->SetKeywords('TCPDF, PDF, Course, Syllubs'); */
		 
		// remove default header/footer
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		
		// set font
		$pdf->SetFont('times', 'B', 20);
		// add a page
		$pdf->AddPage();
		$logo = '<p><img src="ualbertalogo.jpg" alt="test alt attribute" border="0" /></p>';
		$pdf->writeHTMLCell(0, 0, '', '', $logo, 0, 1, 0, true, 'L', true);
		$coursefullname = $course->shortname.': '.$course->fullname;
		$pdf->Cell(0, 0, '', 0, 0, 'C');
		$pdf->Ln();
		$pdf->Cell(0, 0, '', 0, 0, 'C');
		$pdf->Ln();
		$pdf->Cell(0, 0, '', 0, 0, 'C');
		$pdf->Ln();
		$pdf->Cell(0, 0, '', 0, 0, 'C');
		$pdf->Ln();
		$pdf->Cell(0, 0, $coursefullname, 0, 1, 'C');
		$pdf->Cell(0, 0, '', 0, 0, 'C');
		$pdf->Ln();
//put general course information(instructor,officehour,location) into the pdf------------------------------------------
		$courselogistics = <<<EOD
		Fall/Winter/Spring/Summer<br>
		<b>Course Weight</b>: *3
EOD;
		$pdf->SetFont('times', '', 12);
		$pdf->writeHTMLCell(0, 0, '', '', $courselogistics, 0, 1, 0, true, 'C', true);
		$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
		$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
		$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
		$courselogistics = <<<EOD
		<b>Coordinator</b>: $courseInstructor<br>
		<b>Office</b>: $officelocation, <b>phone</b>: $phonenumber<br>
		<b>Email</b>: $instructoremail<br>
		<b>Office Hours</b>: $officehours 
EOD;
		$pdf->SetFont('times', '', 12);
		$pdf->writeHTMLCell(0, 0, '', '', $courselogistics, 0, 1, 0, true, 'C', true);
		$pdf->AddPage();
	
	
	
//put course overview information into the pdf------------------------------------------
		if($coursedescription != ''){
			$decripthtml =  '<h1>Course description</h1>
					<font size="11%">'.$coursedescription.'<font>';
			$pdf->writeHTML($decripthtml, true, false, true, false, '');
			$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
			$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
		}

//put course reading information into the pdf------------------------------------------
		if($readingnumber>0){
			$readinghtml = '<b><h1>Course Readings</h1></b><ul>';
			foreach ($coursereadings as $coursereading) {
				$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
				$readinghtml.= '<font size="11%"><li>
				'.$coursereading->readingname.'<br>
				<a href="url">'.$coursereading->readingurl.'</a></li></font>';
			}
			$pdf->writeHTML($readinghtml, true, false, true, false, '');
			$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
			$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
		}

	
	
//put course objective information into the pdf------------------------------------------
		if(isset($courseobjectives)){
			$objhtml = '<b><h1>Course Objectives</h1></b>
					<font size="11%">The course is designed to develop the following knowledge, skills and attitudes:</font>';
			
			
	//Knowledge learning objectives ===========================================================================================
			$count =0;
			$kobj = '<b><h3>Knowledge</h3><br>
					<h4>Students who successfully complete this course will be able to:</h4></b>
					<br><font size="11%"><ul>';
			foreach ($courseobjectives as $courseobjective) {
				if($thisobj = $DB->get_record('learningobjectives', array('id'=>$courseobjective->objectiveid))){
					if($thisobj->objectivetype == 'Knowledge'){
						$count++ ;
						$kobj.='<li>'.$thisobj->objectivename.'</li>';
					}
				}
			}
			if($count>0){
				$objhtml.=$kobj;
				$objhtml.='</ul><font><br>';
			}

	
	//Skill learning objectives ===========================================================================================
			$count =0;
			$sobj = '<b><h3>Skill</h3><br>
					<h4>Students who successfully complete this course will be able to:</h4></b>
					<br><font size="11%"><ul>';
			foreach ($courseobjectives as $courseobjective) {
				if($thisobj = $DB->get_record('learningobjectives', array('id'=>$courseobjective->objectiveid))){
					if($thisobj->objectivetype == 'Skill'){
						$count++ ;
						$sobj.='<li>'.$thisobj->objectivename.'</li>';
					}
				}
			}
			if($count>0){
				$objhtml.=$sobj;
				$objhtml.='</ul><font><br>';
			}

	
	
	//attitude learning objectives ===========================================================================================
			$count =0;
			$aobj = '<b><h3>Atittude</h3><br>
					<h4>Students who successfully complete this course will be able to:</h4></b>
					<br><font size="11%"><ul>';
			foreach ($courseobjectives as $courseobjective) {
				if($thisobj = $DB->get_record('learningobjectives', array('id'=>$courseobjective->objectiveid))){
					if($thisobj->objectivetype == 'Atittude'){
						$count++ ;
						$aobj.='<li>'.$thisobj->objectivename.'</li>';
					}
				}
			}
			if($count>0){
					$objhtml.=$aobj;
					$objhtml.='</ul><font>';
			}
			$pdf->writeHTML($objhtml, true, false, true, false, '');
			$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
			$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
		}
	
	
//put course grading information into the pdf------------------------------------------
		if (isset($courseassessments)){
			$asstype = array('Exam','Assignment','Lab','Lab Exam');
			$assessmenthtml = '<b><h1>Grading</h1></b><br><font size="11%">';
			$assessmenthtml .= '
		<table border="0.1" cellspacing="0.1" cellpadding="0.1" id="gradingtable">
		<tr>
			<th width="17%" align="center"><b>Title</b></th>
			<th width="10%" align="center"><b>Weight</b></th>
			<th width="16%" align="center"><b>Date</b></th>
			<th width="17%" align="center"><b>Type</b></th>
			<th width="40%" align="center"><b>Description</b></th>
		</tr>';	
			foreach ($courseassessments as $courseassessment) {
				$assessmentduedate = $this->GetTimeStamp($courseassessment->assessmentduedate);
				$assessmenthtml .= '<tr>
						<td width="17%">'.$courseassessment->assessmentname.'</td>
						<td width="10%" align="center">'.$courseassessment->assessmentweight.'%</td>
						<td width="16%" align="center">'.$assessmentduedate.'</td>
						<td width="17%" align="center">'.$asstype[(int)$courseassessment->type].'</td>	
						<td width="40%">'.$courseassessment->description.'</td>	
						</tr>';
			}
			$assessmenthtml .= '</table></font>';
			$pdf->writeHTML($assessmenthtml, true, false, true, false, '');
			$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
			$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
		}
	
	
//put course session information into the pdf------------------------------------------
		if($sessionnumber>0){
			$sessionhtml = '<b><h1>Course Sessions</h1></b>';
			$sessionno = 1;
			foreach ($coursesessions as $coursesession) {
				$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
				$guestteacher = '';
				$guestteacher = $coursesession->sessionguestteacher;
				if($guestteacher == ''){
					$guestteacher = 'to be assigned';
				}
				$sessiondate = $this->GetTimeStamp($coursesession->sessiondate);
				$sessionhtml.= '
				<font size="12%"><b>'.$sessionno.'</b>:'. $coursesession->sessiontitle.'</font>
				<hr>
				<font size="11%"><b>Date</b>:'. $sessiondate.' <br>
				<b>Length</b>:'. $coursesession->sessionlength .'<br>
				<b>Type</b>:'. $coursesession->sessiontype .'<br>
				<b>Description</b>:<br>'. $coursesession->sessiondescription .'<br>
				<b>Guest teacher</b>:'. $guestteacher .'<hr></font>';
				$sessionno ++;
			}
			$pdf->writeHTML($sessionhtml, true, false, true, false, '');
			$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
			$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
		}
	
	
	
//put course policy information into the pdf------------------------------------------
		$policyhtml = '<b><h1>Policy</h1></b>';
		//add school policy	
		if($Universitypolicy = $DB->get_record('syllabuspolicy', array('category'=>-1))){
			$policyhtml.='<h3>School Policy</h3><br><font size="10%">'.$Universitypolicy->policy.'<font>';
		}
		
		$coursemaininfo = $DB->get_record('course', array('id'=>$course->id));
		if($facultypolicy = $DB->get_record('syllabuspolicy', array('category'=>$coursemaininfo->category))){
		//add faculty policy	
			 $policyhtml.='<br><h3>Faculty Policy</h3><br>
			 <font size="11%">'.$facultypolicy->policy.'<font>';
		}
		
		$pdf->writeHTML($policyhtml, true, false, true, false, '');
		$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
		$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
	
	

	
// terminat$courseInstructorur file with TCPDF output------------------------------------------
		if ($optionno == 1){
			$pdf->Output('syllubus.pdf', 'I'); 
		}else if ($optionno == 2){
			$pdf->Output('syllubus.pdf', 'D');
		}
	}
	
}

?>
