<?php

$string['pluginname'] = 'Moodle Metadata';
$string['menuoption'] = 'Extra Plug-in';
$string['ins_pluginname'] = 'Instructor Moodle Metadata';
$string['admin_pluginname'] = 'Admin Moodle Metadata';
$string['manage_pluginname'] = 'Manage Metadata';

// Error messages
$string['err_alphanumeric']='You must enter only letters or numbers here.';
$string['err_email']='You must enter a valid email address here.';
$string['err_lettersonly']='You must enter only letters here.';
$string['err_maxlength']='You must enter not more than $a->format characters here.';
$string['err_minlength']='You must enter at least $a->format characters here.';
$string['err_nopunctuation']='You must enter no punctuation characters here.';
$string['err_nonzero']='You must enter a number not starting with a 0 here.';
$string['err_numeric']='You must enter a number here.';
$string['err_rangelength']='You must enter between {$a->format[0]} and {$a->format[1]} characters here.';
$string['err_required']='You must supply a value here.';
$string['err_positivenumber']='You must enter a positive number here.';

// General form elements
$string['course_code'] = 'Course short name: ';
$string['course_name'] = 'Course full name: ';
$string['course_description'] = 'Course summary: ';
$string['course_instructor'] = 'Instructor: ';
$string['upload_ctype_file'] = 'Upload course type file: ';
$string['upload_ctype'] = 'Upload';
$string['program_type'] = 'Program type: ';
$string['course_category'] = 'Course category: ';
$string['course_objective'] = 'Course learning objective(s): ';
$string['course_faculty'] = 'Faculty: ';
$string['course_gradAtt'] = 'Attribute {no}: ';
$string['assessment_counter'] = 'Number of assessment: ';
$string['session_counter'] = 'Number of session: ';
$string['teaching_assumption'] = 'Instructor assumption: ';

$string['course_reading_desc'] = 'Required reading: ';
$string['readingname_label'] = 'Title {no}:';
$string['readingurl_label'] = 'Url {no}:';
$string['delete_reading_label'] = "Delete reading {no}";
$string['delete_gradAtt_label'] = "Delete graduate attribute {no}";
$string['knowledge_desc'] = 'Students who successfully complete the course will be able to:';
$string['skill_desc'] = 'Students who successfully complete the course will be able to:';
$string['attitude_desc'] = 'Students who successfully complete this course will:';
$string['knowledge_label'] = 'Knowledge {no}:';
$string['skill_label'] = 'Skill {no}:';
$string['attitude_label'] = 'Attitude {no}:';
$string['add_reading'] = 'Add new reading';
$string['add_knowledge'] = 'Add new knowledge';
$string['add_skill'] = 'Add new skill';
$string['add_attitude'] = 'Add new attitude';
$string['add_gradAtt'] = 'Add new graduate attribute';

$string['upload_reading'] = 'Upload readings';
$string['upload_course_obj'] = 'Upload course objectives';

$string['course_email'] = 'E-mail: ';
$string['course_phone'] = 'Phone: ';
$string['course_office'] = 'Office: ';
$string['course_officeh'] = 'Office hours: ';

$string['obj_knowledge_header'] = 'Course objective: Knowledge';
$string['obj_skill_header'] = 'Course objective: Skill';
$string['obj_attitude_header'] = 'Course objective: Attitude';
$string['course_general_header'] = 'General';
$string['course_contact_header'] = 'Contact information';
$string['upload_reading_header'] = 'Upload required readings';
$string['course_reading_header'] = 'Required readings';
$string['course_desc_header'] = 'Description';
$string['course_format_header'] = 'Course format';
$string['course_gradatt_header'] = 'Graduate attribute';
$string['course_obj_header'] = 'Upload course objectives';
$string['teaching_assumption_header'] = 'Teaching assumption';

$string['course_format_header_help'] = 'By reducing the number, the newest entry or the one with the highest id will be deleted.';
$string['course_reading_header_help'] = 'Leave the TITLE space blank to delete the entry.';
$string['obj_knowledge_header_help'] = 'Leave the space blank to delete the entry.';
$string['obj_skill_header_help'] = 'Leave the space blank to delete the entry.';
$string['obj_attitude_header_help'] = 'Leave the space blank to delete the entry.';
$string['course_obj_header_help'] = 'To upload course objectives, only .csv file is permitted. <br />
									The format is: [column1],[column2],[column3] <br />
									[column1] is the course objective: knowledge. <br />
									[column2] is the course objective: skill. <br />
									[column3] is the course objective: attitude. <br />
									Enter a new line to create a new entry.';
$string['upload_reading_header_help'] = 'To upload course required readings, only .csv file is permitted. <br />
									The format is: [column1],[column2] <br />
									[column1] is the reading title. <br />
									[column2] is the reading url. <br />
									Enter a new line to create a new entry.';
$string['course_data'] = 'Course data';

$string['instructor_heading'] = 'Metadata for %s: %s';

$string['add_session'] = 'Add new session';
$string['session_title'] = 'Title';
$string['session_teaching_strategy'] = 'Teaching Strategy';
$string['session_guest_teacher'] = 'Guest Lecturer';
$string['session_guest_teacher_help'] = 'If there is a different lecuturer, write out their full name. Otherwise, leave blank';
$string['session_type'] = 'Type';
$string['session_length'] = 'Length';
$string['session_date'] = 'Date';
$string['manage_topics'] = 'Topics';
$string['add_topic'] = 'Add';
$string['new_session_header'] = 'New Session';
$string['unnamed_session'] = 'Unnamed Session';

$string['upload_sessions_header'] = 'Upload All Sessions';
$string['upload_sessions_header_help'] = 'To upload course objectives, only .csv file is permitted. This will overwrite ALL existing sessions.<br />
                                        The format for each line is: title, description, guest teacher, type, length, date, first topic, second topic...<br />
                                        If there is no guest teacher, leave it blank.<br />
                                        The type should be lecture, lab, or seminar. Will default to lecture.<br />
                                        The length should be 50, 80, 110, 140, or 170 followed by minutes. EG: 110 minutes.<br />
                                        The date should be in the form YYYY-MM-DD. EG: 2016-03-17 would be March 17, 2016.';
$string['upload_sessions'] = 'Upload sessions';

$string['learning_objective_Attitude'] = 'Learning Objective: Attitude';
$string['learning_objective_Knowledge'] = 'Learning Objective: Knowledge';
$string['learning_objective_Skill'] = 'Learning Objectives: Skill';
$string['related_assessments'] = 'Related Assessments';
$string['deletesession'] = 'Delete Session';

//assessment strings
$string['assessment_description'] = 'Description: ';
$string['learning_objective_selection_description']= 'Learning Objective(s): ';
$string['assessment_type'] = 'Type of Assessment: ';
$string['grade_weight'] = 'Weight: ';
$string['objective_description'] = 'Description: ';
$string['assessment_type'] = 'Type: ';
$string['assessment_add'] = 'Add Assessment';

$string['assessment_title'] = 'Title: ';
$string['assessment_prof'] = 'Lecturer: ';
$string['assessment_isexam'] = 'Exam? ';
$string['knowledge_header'] = 'Knowledge';
$string['knowledge_text'] = 'At the end of the course the student will be able to: ';
$string['skills_header'] = 'Skills';
$string['attitudes_header'] ='Attitudes';
$string['grading_header'] = 'Grading';
$string['assessment_grading_desc'] = 'Grading Description: ';
$string['assessment_prof_default'] = 'First, Last';
$string['assessment_due'] = 'Date: ';
$string['assessment_duration'] = 'Duration: ';
$string['assessment_due'] = 'Due Date: ';
$string['general_header'] = 'General';
$string['deleteassessment'] = 'Delete Assessment';
$string['assessment_filepicker'] = 'Upload Assessments';
$string['upload_assessments'] = 'Submit';
$string['assessment_grading_upload'] = 'Upload Grading Description';
$string['assessment_grading_upload_submit'] = 'Submit';

// Metadata manager strings
$string['manage_knowledge'] = 'Knowledge Attributes: ';
$string['new_knowledge'] = 'New Learning Objective';
$string['create_knowledge'] = 'Add';
$string['delete_knowledge'] = 'Delete';

$string['manage_skills'] = 'Skills Attributes: ';
$string['new_skills'] = 'New Learning Objective';
$string['create_skills'] = 'Add';
$string['delete_skills'] = 'Delete';

$string['manage_attitudes'] = 'Attitudes Attributes: ';
$string['new_attitudes'] = 'New Learning Objective';
$string['create_attitudes'] = 'Add';
$string['delete_attitudes'] = 'Delete';

$string['course_gradatt'] = 'Graduate Attributes: ';
$string['new_gradatt'] = 'New Graduate Attribute';
$string['create_gradatt'] = 'Add';
$string['delete_gradatt'] = 'Delete';
$string['course_gradatt_help'] = 'By removing a graduate attribute here, 
								 any record corresponding to this graduate attribute will be removed as well.';

$string['policy_editor'] = 'Faculty Policy: ';
$string['submit_policy'] = 'Submit';

$string['university_editor'] = 'University Policy: ';

$string['program_knowledge_header'] = 'Program Objective: Knowledge';
$string['program_skills_header'] = 'Program Objective: Skills';
$string['program_attitudes_header'] = 'Program Objective: Attitudes';
$string['program_obj_header'] = 'Upload Program Objectives';
$string['program_obj_header_help'] = 'To upload program objectives, only .csv file is permitted. <br />
									The format is: [column1],[column2],[column3] <br />
									[column1] is the program objective: knowledge. <br />
									[column2] is the program objective: skill. <br />
									[column3] is the program objective: attitude. <br />
									Enter a new line to create a new entry.';
$string['upload_program_obj'] = 'Upload';

$string['admcourse_select'] = 'Select Course: ';
$string['admselect_course'] = 'Select';
$string['admobj_select'] = 'Learning Objective: ';
$string['admselcourse'] = 'Select';
$string['admpro_select'] = 'Program Objectives: ';
$string['admaddobjective'] = 'Tag Objectives';
$string['admpro_current'] = 'Current Tags: ';
$string['admdelobjective'] = 'Remove Tag';

// Metadata manager errors
$string['mcreate_required'] = 'You must enter something.';
$string['psla_exists'] = 'That learning objective already exists.';

$string['next_page'] = 'Next Page';
$string['previous_page'] = 'Previous Page';
