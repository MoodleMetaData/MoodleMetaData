<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';
require_once $CFG->dirroot.'/lib/tcpdf/tcpdf.php';
require_once 'lib.php';

class reporting_form extends moodleform {
	function definition() {
		global $CFG, $DB, $USER; //Declare our globals for use
		global $course, $courseId;
		
		// initialize the form.
		$mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.
 		$mform->addElement('html','<form> <b>generate report:</b><br><br>
				In pdf format:
 				<input type="submit" name="reportdisplay" value="preview"/>
 				<input type="submit" name="reportdownload" value="download"/>
				<br>In csv format:
 				<input type="submit" name="reportcsv" value="download"/>
				</form>'); 
		
		
		if(isset($_POST['reportdisplay'])){

			$this->generatepdf(1);
		}
		if(isset($_POST['reportdownload'])){
		
			$this->generatepdf(2);
		}
		if(isset($_POST['reportcsv'])){
		
			$this->generatecsv();
		}
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
	
	
	function get_session_time($programobjective){
		$sessionno = 0;

		return $sessionno;
	}
	
	function get_course_time($programobjective){
		$courseno = 0;
		
		return $courseno;
	}
	
	
	function get_assessment_time($programobjective){
		$assessmentno = 0;
		
		return $assessmentno;
	}
	
	
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
	
	function generatecsv(){
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
}

?>