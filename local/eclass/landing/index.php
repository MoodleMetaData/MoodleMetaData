<?php

// Moodle settings and security based on capability
define("MOODLE_INTERNAL", TRUE);
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_login();
require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));

echo("<html>");
echo("<head>");
echo("<title>eClass Landing Pages</title>");
echo("</head>");
echo("<body>");
$links = <<<HTML

<h3>eClass Landing Pages</h3>
<h4>/local/eclass</h4>
<ul>
<li><a href='/local/eclass/landing/bad_cohort_view.php'>bad cohort view</a></li>
<li><a href='/local/eclass/landing/check_courses.php'>check courses</a></li>
<li><a href='/local/eclass/landing/course_grade_categories_edit.php'>course grade categories edit</a></li>
<li><a href='/local/eclass/landing/course_grade_categories_view.php'>course grade categories view</a></li>
<li><a href='/local/eclass/landing/course_grade_item_edit.php'>course grade item edit</a></li>
<li><a href='/local/eclass/landing/course_grade_item_view.php'>course grade item view</a></li>
<li><a href='/local/eclass/landing/course_modules_view.php'>course modules view</a></li>
<li><a href='/local/eclass/landing/duplicate_grade_categories_histories_view.php'>duplicate grade categories histories view</a></li>
<li><a href='/local/eclass/landing/duplicate_grade_categories_view.php'>duplicate grade categories view</a></li>
<li><a href='/local/eclass/landing/duplicate_grade_item_coursetype_view.php'>duplicate grade item coursetype view</a></li>
<li><a href='/local/eclass/landing/enrol_fix.php'>enrol fix</a></li>
<li><a href='/local/eclass/landing/enrol_view.php'>enrol view</a></li>
<li><a href='/local/eclass/landing/guestlink.php'>guestlink</a></li>
<li><a href='/local/eclass/landing/serverinfo.php'>serverinfo</a></li>
<li><a href='/local/eclass/landing/user_creation_form.php'>user creation form</a></li>
<li><a href='/local/eclass/landing/user_creation.php'>user creation</a></li>
</ul>

<h3>/blocks/eclass_course_overview</h3>
<ul>
<li><a href='/blocks/eclass_course_overview/admin_test/test_course_active.php'>test if a course is active</a></li>
</ul>

HTML;
echo("$links\n\n");
echo("</body>");
echo("</html>");
