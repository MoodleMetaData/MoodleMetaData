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

$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-pre', $OUTPUT));
$hassidepost = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-post', $OUTPUT));
$haslogininfo = (empty($PAGE->layout_options['nologininfo']));

$showsidepre = ($hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT));
$showsidepost = ($hassidepost && !$PAGE->blocks->region_completely_docked('side-post', $OUTPUT));

$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));

$bodyclasses = array();
if ($showsidepre && !$showsidepost) {
    $bodyclasses[] = 'side-pre-only';
} else if ($showsidepost && !$showsidepre) {
    $bodyclasses[] = 'side-post-only';
} else if (!$showsidepost && !$showsidepre) {
    $bodyclasses[] = 'content-only';
}
if ($hascustommenu) {
    $bodyclasses[] = 'has_custom_menu';
}

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <title><?php echo $PAGE->title ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
</head>

<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>">
<?php echo $OUTPUT->standard_top_of_body_html() ?>
<div id="page-wrapper">
  <div id="page">
   <?php
if ($hasheading || $hasnavbar) { ?>
    <div id="page-header">
        <div id="page-header-bg">
        <?php
    if ($hasheading) { ?>
         <div id="logo">
         	<a href="http://ualberta.ca/" alt="University of Alberta" class="ualink" target="_blank"></a>
         	<a href="/" alt="eClass External powered by Moodle" class="eclasslink"></a>
         </div>

        <div class="headermenu"><?php
        if ($haslogininfo) {
            echo $OUTPUT->login_info();
        }
        if (!empty($PAGE->layout_options['langmenu'])) {
            echo $OUTPUT->lang_menu();
        }
        echo $PAGE->headingmenu
        ?></div>
        <?php
    } ?>
        </div>
    </div>
	<?php
} ?>
<!-- END OF HEADER -->
<!-- START CUSTOMMENU AND NAVBAR -->
    <div id="navcontainer">
        <?php
if ($hascustommenu) { ?>
                <div id="custommenu" class="javascript-disabled">
                	<?php echo $custommenu; ?>
                </div>
        <?php
} ?>
    </div>

        <?php
if ($hasnavbar) { ?>
            <div class="navbar clearfix">
                <div class="breadcrumb"><?php echo $OUTPUT->navbar(); ?></div>
                <div class="navbutton"> <?php echo $PAGE->button; ?></div>
            </div>
        <?php
} ?>

<!-- END OF CUSTOMMENU AND NAVBAR -->
    <div id="page-content">
       <div id="region-main-box">
           <div id="region-post-box">
              <div id="region-main-wrap">
                 <div id="region-main-pad">
                   <div id="region-main">
                     <div class="region-content">
                     		<h1><?php echo $PAGE->heading ?></h1>
                            <?php echo core_renderer::MAIN_CONTENT_TOKEN ?>
                     </div>
                   </div>
                 </div>
               </div>

                <?php
if ($hassidepre) { ?>
                <div id="region-pre" class="block-region">
                   <div class="region-content">
                        <?php echo $OUTPUT->blocks_for_region('side-pre') ?>
                   </div>
                </div>
                <?php
} ?>

                <?php
if ($hassidepost) { ?>
                <div id="region-post" class="block-region">
                   <div class="region-content">
                        <?php echo $OUTPUT->blocks_for_region('side-post') ?>
                   </div>
                </div>
                <?php
} ?>

            </div>
        </div>
    </div>

    <!-- START OF FOOTER -->
    <?php
if ($hasfooter) { ?>
    <div id="page-footer" class="clearfix">

        <div class="footer-left">
            <a href="http://moodle.org" title="Moodle" target="_blank">
                <img src="<?php echo $OUTPUT->pix_url('footer/moodle-mlogo', 'theme')?>" alt="Moodle logo" />
            </a>
        </div>
        <div class="footer-left">
            <ul>
				<li><a href="http://ctl.ualberta.ca/" target="_blank">Centre for Teaching and Learning</a></li>
				<li><a href="https://support.ctl.ualberta.ca/index.php?/Knowledgebase/List" target="_blank">Knowledge Base FAQs</a></li>
            </ul>
        </div>
        <div class="footer-right">
            <?php echo $OUTPUT->login_info();?>
        </div>

        <?php echo $OUTPUT->standard_footer_html(); ?>
    </div>
    <?php
} ?>
    <div class="clearfix"></div>
</div>
</div>
<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>