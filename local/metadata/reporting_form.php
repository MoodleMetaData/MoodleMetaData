<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';
require_once $CFG->dirroot.'/lib/tcpdf/tcpdf.php';
require_once 'lib.php';

/**
 * The form to display the tab for report information.
 * which will allow the user to preview or download the pdf
 * format of generated program objective report
 * Or
 * allow the user to download the csv
 * format of generated program objective report
 * and course general information(plus all the related learning objectives) report
 */
class reporting_form extends moodleform {
	/**
	 * Will set up the form elements
	 * @see lib/moodleform#definition()
	 */
	function definition() {
		global $CFG, $DB, $USER; //Declare our globals for use
		global $course, $courseId;
		
		// initialize the form.
		$mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.
 		$mform->addElement('html','<form> <b>generate report:</b><br><br>
 				<ul>
 				<li><b>Program objective report:</b>
				<br><br>In pdf format:
 				<input type="submit" name="poreportdisplay" value="preview"/>
 				<input type="submit" name="poreportdownload" value="download"/>
				<br>In csv format:
 				<input type="submit" name="poreportcsv" value="download"/>
 				</li>
 				 <li><b>Course report:</b>
				<br><br>In csv format:
 				<input type="submit" name="coursereportcsv" value="download"/>
				</form>'); 
		
		
		if(isset($_POST['poreportdisplay'])){

			$this->generatepdf(1);
		}
		if(isset($_POST['poreportdownload'])){
		
			$this->generatepdf(2);
		}
		if(isset($_POST['poreportcsv'])){
		
			$this->generatepocsv();
		}
		if(isset($_POST['coursereportcsv'])){
		
			$this->generatecocsv();
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
     * Used to get how many sessions that one program objective tags to 
     *
     * @param object $programobjective database records that contains the
     * full information of one progeam objective
     *
     * @return integer $sessionno the number of sessions the program objective tags to
     */
	function get_session_time($programobjective){
		$sessionno = 0;
		$taginfos = $DB->get_records('programpolicytag', array('objectiveid'=>$programobjective->id));
		foreach ($taginfos as $taginfo){
			$courseobjid = $taginfo->tagid;
			$objid = $DB->get_record('learningobjectives', array('id'=>$courseobjid->objectiveid));
			break;
		}
		$sessionno = $DB->count_records('sessionobjectives', array('objectiveid'=>$objid));
		return $sessionno;
	}
	
	/**
	 * Used to get how many courses that one program objective tags to
	 *
	 * @param object $programobjective database records that contains the
	 * full information of one progeam objective
	 *
	 * @return integer $sessionno the number of courses the program objective tags to
	 */
	function get_course_time($programobjective){
		$courseno = 0;
		$courseno = $DB->count_records('programpolicytag', array('objectiveid'=>$programobjective->id));
		return $courseno;
	}
	
	/**
	 * Used to get how many assessments that one program objective tags to
	 *
	 * @param object $programobjective database records that contains the
	 * full information of one progeam objective
	 *
	 * @return integer $sessionno the number of assessments the program objective tags to
	 */
	function get_assessment_time($programobjective){
		$assessmentno = 0;
		$taginfos = $DB->get_records('programpolicytag', array('objectiveid'=>$programobjective->id));
		foreach ($taginfos as $taginfo){
			$courseobjid = $taginfo->tagid;
			$objid = $DB->get_record('learningobjectives', array('id'=>$courseobjid->objectiveid));
			break;
		}
		$assessmentno = $DB->count_records('courseassessment', array('objectiveid'=>$objid));
		return $assessmentno;
	}
	
	/**
	 * Used to generate the pdf format of the program objective report
	 *
	 * @param  integer $optionno the option to choose wether(1) display the generated report in the current window
	 * or(2) showing the user a download window directly for downloading the pdf file.
	 *
	 */
	function generatepdf($optionno){
		global $CFG, $DB, $USER;
		global $course;
		//get data
		$programobjectives = $DB->get_records('programobjectives');

		//start pdf generation===============================================================================		
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		// remove default header/footer
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		// add a page
		$pdf->AddPage();
		//generate table for report
		$reporthtml = '<b><h1 align="center">Program Objective Report</h1></b><font size="11%">';
		$reporthtml .= '
		<table border="0.1" cellspacing="0.1" cellpadding="0.1" id="gradingtable">
		<tr>
			<th width="25%" align="center"><b>objective name</b></th>
			<th width="25%" align="center"><b>tag times for course</b></th>
			<th width="25%" align="center"><b>tag times for session</b></th>
			<th width="25%" align="center"><b>tag times for assessment</b></th>
		</tr>';
		if($programobjectives){
			foreach ($programobjectives as $programobjective) {
				$objname = '';
				$courseno = $this->get_session_time($programobjective);
				$sessionno = $this->get_course_time($programobjective);
				$assessmentno = $this->get_assessment_time($programobjective);
				$objname = $programobjective->objectivename;
				$reporthtml .= '<tr>
			<th width="25%" align="center">'.$objname.'</th>
			<th width="25%" align="center">'.$courseno.'</th>
			<th width="25%" align="center">'.$sessionno.'</th>
			<th width="25%" align="center">'.$assessmentno.'</th>
						</tr>';
			}
		}
		$reporthtml .= '</table></font>';
		$pdf->writeHTML($reporthtml, true, false, true, false, '');
		
		// terminate with TCPDF output------------------------------------------
		if ($optionno == 1){
			$pdf->Output('syllubus.pdf', 'I'); 
		}else if ($optionno == 2){
			$pdf->Output('syllubus.pdf', 'D');
		}
	}
	
	
	/**
	 * Used to generate the csv format of the program project report
	 *
	 */
	function generatepocsv(){
		global $CFG, $DB, $USER;
		global $course;
		//get data
		$programobjectives = $DB->get_records('programobjectives');
		//set a clean html
		ob_start();
		ob_end_clean();
		$file = fopen("php://output", "w");
		// send the column headers
		fputcsv($file, array('name', 'course', 'session', 'assessment'));
		//generate table for report
		foreach ($programobjectives as $programobjective)
		{
			$courseno = $this->get_session_time($programobjective);
			$sessionno = $this->get_course_time($programobjective);
			$assessmentno = $this->get_assessment_time($programobjective);
			$row = array($programobjective->objectivename,$courseno,$sessionno,$assessmentno);
			fputcsv($file, $row);
		}
		// output headers so that the file is downloaded rather than displayed
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=reports.csv');
		// do not cache the file
		header('Pragma: no-cache');
		header('Expires: 0');
		exit();
		fclose($file);
	}
	
	/**
	 * Used to generate the csv format of the course general information report
	 *
	 */
	function generatecocsv(){
		global $CFG, $DB, $USER;
		global $course;
		//get data
		$courseinfos = $DB->get_records('courseinfo');
		//set a clean html
		ob_start();
		ob_end_clean();
		$file = fopen("php://output", "w");
		// send the column headers
		fputcsv($file, array('Short Name', 'Full Name', 'Faculty', 'Category', 'Instructor', 'All Program Objectives'));
		//generate table for report
		$instructor = 'to be assigned';
		foreach ($courseinfos as $courseinfo)
		{
			$generalinfo = $DB->get_record('course', array('id'=>$courseinfo->courseid));
			//get course name
			$shortname = $generalinfo->shortname;
			$fullname = $generalinfo->fullname;
			//get faculty name
			$facultyinfo = $DB->get_record('course_categories', array('id'=>$courseinfo->facultyid));
			$faculty = $facultyinfo->name;
			//get category name
			$category = $courseinfo->coursecategory;
			//get instructor name
			if($instructorinfo = $DB->get_record('courseinstructors', array('courseid'=>$courseinfo->id))){
				$instructor = $instructorinfo->name;
			}	
			//get objectives
			$objectives = array();
			$courseobjs = $DB->get_records('courseobjectives', array('courseid'=>$courseinfo->courseid));
			//insert into csv file
			$row = array($shortname,$fullname,$faculty,$category,$instructor);
			foreach ($courseobjs as $courseobj)
			{
				$objinfo = $DB->get_record('learningobjectives', array('id'=>$courseobj->objectiveid));
				array_push($row, $objinfo->objectivename);
			}
			fputcsv($file, $row);
		}
		// output headers so that the file is downloaded rather than displayed
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=coursereports.csv');
		// do not cache the file
		header('Pragma: no-cache');
		header('Expires: 0');
		exit();
		fclose($file);
	}
}

?>