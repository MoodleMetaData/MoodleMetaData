<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';
require_once $CFG->dirroot.'/lib/tcpdf/tcpdf.php';
require_once 'lib.php';

/**
 * The form to display the tab for syllubus information.
 * which will allow the user to preview or download the pdf
 * format of generated syllabus
 */
class syllabus_form extends moodleform {
	/**
	 * Will set up the form elements
	 * @see lib/moodleform#definition()
	 */
	function definition() {
		global $CFG, $DB, $USER; //Declare our globals for use
		global $course, $courseId;
		
		// initialize the form.
		$mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.

 		$mform->addElement('html','<form> generate syllabus: 
 				<input type="submit" name="syllubusdisplay" value="preview"/>
 				<input type="submit" name="syllubusdownload" value="download"/></form>'); 
		
		
		if(isset($_POST['syllubusdownload'])){

			$this->do_generate(2);
		}
		if(isset($_POST['syllubusdisplay'])){
		
			$this->do_generate(1);
		}

	}

    /**
     * Ensure that the data the user entered is valid
     *
     * @see lib/moodleform#validation()
     */
	function validation($data, $files) {
		$errors = parent::validation($data, $files);
		global $DB, $CFG, $USER; //Declare them if you need them
		
		return $errors;
    }
		
	/**
	 * Used to decoding the Unix stemp format of the date type stored in the database
	 *
	 * @param  string $timestamp the Unixstemp format of the date to be transfered
	 * 
	 * @return string $displaydate the final format of the date that can be displayed in the syllabus
	 */
	function GetTimeStamp($timestamp)
	{
		$displaydate = gmdate("Y-m-d", $timestamp);
		return  $displaydate;
	}
	
	
    /**
     * Used to generate the course general description area of the syllabus
     *
     * @param string $coursedescription description string to be shown in the syllabus
     *
     * @return string $decripthtml the corresponding html format of 
     * desctiption area to be shown in the syllabus
     */
	function description_part_generation($coursedescription){
		$decripthtml =  '<h1>Course description</h1>';
		if($coursedescription != ''){
			$decripthtml.='<font size="11%">'.$coursedescription.'<font>';
		}
		return $decripthtml;
	}
	
	/**
	 * Used to generate the reading material area of the syllabus
	 *
	 * @param integer $readingnumber variable to determine whether the reading 
	 * information is empty for this course
	 *
	 * @return string $readinghtml the corresponding html format of
	 * reading  area to be shown in the syllabus
	 */
	function reading_part_generation($readingnumber){
		global $CFG, $DB, $USER;
		global $course;
		$readinghtml = '<b><h1>Course Readings</h1></b>';
		if($readingnumber>0){
			$coursereadings = $DB->get_records('coursereadings', array('courseid'=>$course->id));
			$readinghtml.='<ul>';
			foreach ($coursereadings as $coursereading) {
				$readinghtml.= '<font size="11%"><li>
					'.$coursereading->readingname.'<br>
					<a href="url">'.$coursereading->readingurl.'</a></li></font><br>';
			}
			$readinghtml.='</ul>';
		}	
		return $readinghtml;
	}
	
	/**
	 * Used to generate the sub category part of the learning objective area of the syllabus
	 *
	 * @param string $objhtml previous html information for the learning objective area in the syllabus
	 * @param string $subcategory variable to determine which category it is
	 * @param object $courseobjectives database records that contains all the learning objectives of this course
	 * 
	 * @return string $objhtml the corresponding html format of
	 * sub category part of the learning objective area to be shown in the syllabus
	 */
	function sub_learning_objective_part($objhtml,$subcategory,$courseobjectives){
		global $CFG, $DB, $USER;
		global $course;
		$count =0;
		$subobj = '<b><h3>'.$subcategory.'</h3><br>
						<h4>Students who successfully complete this course will be able to:</h4></b>
						<br><font size="11%"><ul>';
		foreach ($courseobjectives as $courseobjective) {
			if($thisobj = $DB->get_record('learningobjectives', array('id'=>$courseobjective->objectiveid))){
				if($thisobj->objectivetype == $subcategory){
					$count++ ;
					$subobj.='<li>'.$thisobj->objectivename.'</li>';
				}
			}
		}
		if($count>0){
			$objhtml.=$subobj;
			$objhtml.='</ul><font><br>';
		}
		return $objhtml;
	}
	
	/**
	 * Used to generate the learing objective area of the syllabus
	 *
	 * @param integer $objectivegnumber variable to determine whether the learing objective
	 * information is empty for this course
	 *
	 * @return string $objhtml the corresponding html format of
	 * learing objective area to be shown in the syllabus
	 */
	function learning_objective_part_generation($objectivegnumber){
		global $CFG, $DB, $USER;
		global $course;
		$objhtml = '<b><h1>Course Objectives</h1></b>';
		if($objectivegnumber>0){
			$courseobjectives = $DB->get_records('courseobjectives', array('courseid'=>$course->id));
			$objhtml .= '<font size="11%">The course is designed to develop the following knowledge, skills and attitudes:</font>';		
			//Knowledge learning objectives ===========================================================================================
			$objhtml = $this->sub_learning_objective_part($objhtml,'Knowledge',$courseobjectives);
			//Skill learning objectives ===========================================================================================
			$objhtml = $this->sub_learning_objective_part($objhtml,'Skill',$courseobjectives);
			//attitude learning objectives ===========================================================================================
			$objhtml = $this->sub_learning_objective_part($objhtml,'Attitude',$courseobjectives);
		}
		return $objhtml;
	}
	
	/**
	 * Used to generate the assessment grading area of the syllabus
	 *
	 * @param integer $assessmentnumber variable to determine whether the assessment
	 * information is empty for this course
	 *
	 * @return string $assessmenthtml the corresponding html format of
	 * assessment grading area to be shown in the syllabus
	 */
	function grading_part_generation($assessmentnumber){
		global $CFG, $DB, $USER;
		global $course;
		$assessmenthtml = '<b><h1>Grading</h1></b><br>';
		if ($assessmentnumber>0){
			if($assessmentnumber>0){
				$courseassessments = $DB->get_records('courseassessment', array('courseid'=>$course->id), $sort='assessmentduedate');
			}
			$asstype = array('Exam','Assignment','Lab','Lab Exam');
			$assessmenthtml .='<font size="11%"><ul>';	
			$assdescription = '<b><h3>Specifications</h3></b><font size="11%">';
			$assessmenthtml .= '
		<table border="0.1" cellspacing="0.1" cellpadding="0.1" id="gradingtable">
		<tr>
			<th width="30%" align="center"><b>Title</b></th>
			<th width="20%" align="center"><b>Weight</b></th>
			<th width="30%" align="center"><b>Date</b></th>
			<th width="20%" align="center"><b>Type</b></th>
		</tr>';
			foreach ($courseassessments as $courseassessment) {
				$assessmentduedate = $this->GetTimeStamp($courseassessment->assessmentduedate);
				$assessmenthtml .= '<tr>
						<td width="30%" align="center">'.$courseassessment->assessmentname.'</td>
						<td width="20%" align="center">'.$courseassessment->assessmentweight.'%</td>
						<td width="30%" align="center">'.$assessmentduedate.'</td>
						<td width="20%" align="center">'.$asstype[(int)$courseassessment->type].'</td>
						</tr>';
				if($courseassessment->description != ''){
					$assdescription .= '<li><b><h3>'.$courseassessment->assessmentname.'
							</h3>Description</b>:'.$courseassessment->description.'<br>';
				}
				if($courseassessment->gdescription != ''){
					$assdescription .= '<b>Grading</b>:'.$courseassessment->gdescription.'';
				}
				$assdescription .= '</li>';
			}
			$assdescription .='</ul><font>';
			$assessmenthtml .= '</table></font>';
			$assessmenthtml .= '<br>'.$assdescription.'';
		}
		return $assessmenthtml;
	}
	
	/**
	 * Used to generate the session area of the syllabus
	 *
	 * @param integer $sessionnumber variable to determine whether the session
	 * information is empty for this course
	 *
	 * @return string $sessionhtml the corresponding html format of
	 * session area to be shown in the syllabus
	 */
	function session_part_generation($sessionnumber){
		global $CFG, $DB, $USER;
		global $course;
		$sessionhtml = '<b><h1>Course Sessions</h1></b><font size="11%">';
		if ($sessionnumber>0){
			$coursesessions = $DB->get_records('coursesession', array('courseid'=>$course->id), $sort='sessiondate');		
			$sessionhtml .= '
		<table border="0.1" cellspacing="0.1" cellpadding="0.1" id="gradingtable">
		<tr>
			<th width="15%" align="center"><b>Title</b></th>
			<th width="15%" align="center"><b>Date</b></th>
			<th width="10%" align="center"><b>Length</b></th>
			<th width="10%" align="center"><b>Type</b></th>
			<th width="10%" align="center"><b>Instructor</b></th>
			<th width="40%" align="center"><b>Topic</b></th>
		</tr>';
			foreach ($coursesessions as $coursesession) {
				$guestteacher = '';
				$guestteacher = $coursesession->sessionguestteacher;
				if($guestteacher == ''){
					$guestteacher = $USER->lastname.', '.$USER->firstname;
				}
				$topic = '';
				$sessiontopics = $DB->get_records('sessiontopics', array('sessionid'=>$coursesession->id));
				foreach ($sessiontopics as $sessiontopic) {
					if($sessiontopic->topicname!=''){
						$topic .= $sessiontopic->topicname.', ';
					}
				}
				$sessiondate = $this->GetTimeStamp($coursesession->sessiondate);
				$sessionhtml .= '<tr>
						<td width="15%" align="center">'.$coursesession->sessiontitle.'</td>
						<td width="15%" align="center">'.$sessiondate.'</td>
						<td width="10%" align="center">'.$coursesession->sessionlength.'</td>
						<td width="10%" align="center">'.$coursesession->sessiontype.'</td>
						<td width="10%" align="center">'.$guestteacher.'</td>
						<td width="40%" align="center">'.$topic.'</td>
						</tr>';
			}
			$sessionhtml .= '</table></font>';
		}
		return $sessionhtml;
	}	
	
	/**
	 * Used to generate the policy area of the syllabus
	 *
	 * @return string $policyhtml the corresponding html format of
	 * policy area to be shown in the syllabus
	 */
	function policy_part_generation(){
		global $CFG, $DB, $USER;
		global $course;	
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
		return $policyhtml;
	}
	
	
	/**
	 * Used to generate the pdf format of the syllabus
	 *
	 * @param  integer $optionno the option to choose wether(1) display the generated syllabus in the current window
	 * or(2) showing the user a download window directly for downloading the pdf file.
	 * 
	 */
	function do_generate($optionno){
		global $CFG, $DB, $USER;
		global $course;
		$sessionnumber = 0;
		$readingnumber = 0;
		$objectivegnumber = 0;
		$assessmentnumber = 0;
		$instructoremail = 'To be assigned';
		$officehours = 'By appointment';
		$officelocation = 'To be assigned';
		$courseInstructor = 'To be assigned';
		$phonenumber = 0;
		$instructoremail = 'To be assigned';
		$coursedescription = '';
		$courseterm = 'Fall/Winter/Spring/Summer';$courseyear='';
//collecting relative data from database===============================================================================		
		if($existCourseInfo = $DB->get_record('courseinfo', array('courseid'=>$course->id))){
			//$coursetopic = $existCourseInfo->coursetopic;
			$coursedescription = $existCourseInfo->coursedescription;
			$courseterm = $existCourseInfo->courseterm;
			$termarray = array("Spring","Summer","Fall","Winter");
			$courseterm = $termarray[$courseterm];
			$courseyear = $existCourseInfo->courseyear;
			$courseInstructor = $USER->lastname.', '.$USER->firstname;
			if($existInstructorInfo = $DB->get_record('courseinstructors', array('courseid'=>$existCourseInfo->id, 'userid'=>$USER->id))){
				$officelocation = $existInstructorInfo->officelocation;
				$officehours = $existInstructorInfo->officehours;
				$instructoremail =  $existInstructorInfo->email;
				$phonenumber = $existInstructorInfo->phonenumber;
			}
		}
		$sessionnumber = $DB->count_records('coursesession', array('courseid'=>$course->id));
		$readingnumber = $DB->count_records('coursereadings', array('courseid'=>$course->id));	
		$objectivegnumber = $DB->count_records('courseobjectives', array('courseid'=>$course->id));	
		$assessmentnumber = $DB->count_records('courseassessment', array('courseid'=>$course->id));

		
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
		$courseterm $courseyear<br>
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
	
	
//put course description information into the pdf------------------------------------------
		$decripthtml = $this->description_part_generation($coursedescription);
		$pdf->writeHTML($decripthtml, true, false, true, false, '');
		$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
		$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();

//put course reading information into the pdf------------------------------------------
		$readinghtml = $this->reading_part_generation($readingnumber);
		$pdf->writeHTML($readinghtml, true, false, true, false, '');
		$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
		$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
	
	
//put course objective information into the pdf------------------------------------------
		$objhtml = $this->learning_objective_part_generation($objectivegnumber);
		$pdf->writeHTML($objhtml, true, false, true, false, '');
		$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
		$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
	
	
//put course grading information into the pdf------------------------------------------
		$assessmenthtml = $this->grading_part_generation($assessmentnumber);
		$pdf->writeHTML($assessmenthtml, true, false, true, false, '');
		$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
		$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
	
	
//put course session information into the pdf------------------------------------------
		$sessionhtml = $this->session_part_generation($sessionnumber);
		$pdf->writeHTML($sessionhtml, true, false, true, false, '');
		$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
		$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
	
	
	
//put course policy information into the pdf------------------------------------------
		$policyhtml=$this->policy_part_generation();
		$pdf->writeHTML($policyhtml, true, false, true, false, '');
		$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();
		$pdf->Cell(0, 0, '', 0, 0, 'C');	$pdf->Ln();

	
// terminate with TCPDF output------------------------------------------
		if ($optionno == 1){
			$pdf->Output('syllubus.pdf', 'I'); 
		}else if ($optionno == 2){
			$pdf->Output('syllubus.pdf', 'D');
		}
	}
	
}

?>
