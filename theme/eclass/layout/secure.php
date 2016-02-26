<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

// Get the HTML for the settings bits.
$html = theme_eclass_get_html_for_settings($OUTPUT, $PAGE);

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
</head>

<body <?php echo $OUTPUT->body_attributes(); ?>>

<?php echo $OUTPUT->standard_top_of_body_html() ?>


<?php echo $OUTPUT->eclass_header(3); ?>

<div id="page" class="container-fluid" data-page-count="4">

    <header id="page-header" class="clearfix">
        <div id="course-header">
            <?php echo $OUTPUT->course_header(); ?>
        </div>
    </header>

    <div id="page-content" class="row-fluid">
        <input id="toggle-left" name="toggle" type="radio" hidden="true">
        <input id="toggle-center" name="toggle" type="radio" checked hidden="true">
        <input id="toggle-right" name="toggle" type="radio" hidden="true">
        <input id="toggle-profile" name="toggle" type="radio" hidden="true">
        <?php echo $OUTPUT->blocks('side-pre', 'span3 pull-left desktop-first-column columns3'); ?>
        <section id="region-main" class="span6 column3">
            <?php
            echo $OUTPUT->course_content_header();
            echo $OUTPUT->main_content();
            echo $OUTPUT->course_content_footer();
            ?>
        </section>
        <div class="span3 pull-right heading-button"><?php echo $OUTPUT->page_heading_button(); ?></div>
        <?php echo $OUTPUT->blocks('side-post', 'span3 columns3'); ?>
        <?php echo $OUTPUT->eclass_profile('column3'); ?>
    </div>

    <?php echo $OUTPUT->standard_end_of_body_html() ?>

</div>
</body>
</html>