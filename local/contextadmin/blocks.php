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

// Allows the admin to manage activity modules.
global $CFG;
global $PAGE;
global $OUTPUT;
global $DB;
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/local/contextadmin/locallib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');

define('MODULE_TABLE', 'module_administration_table');

require_login();

$contextid = required_param("contextid", PARAM_INT);
$catid     = required_param("catid", PARAM_INT);

// Get the context that was passed (verify it is course category or system context).
$context = context::instance_by_id($contextid, MUST_EXIST);


// TODO: Exit cleanly...fix this later
// If we do not belong here.....

// Setup Page (not admin setup).
$PAGE->set_url("/local/contextadmin/blocks.php", array("contextid" => $contextid, "catid" => $catid));
$PAGE->set_category_by_id($catid);

$paramvisible    = optional_param('visible', null, PARAM_BOOL);
$paramclear      = optional_param('clear', null, PARAM_BOOL);
$paramdelete     = optional_param('delete', null, PARAM_BOOL);
$paramconfirm    = optional_param('confirm', null, PARAM_BOOL);
$paramoverride   = optional_param('override', null, PARAM_BOOL);
$paramlocked     = optional_param('locked', null, PARAM_BOOL);
$paramblockname = optional_param('block_name', '', PARAM_SAFEDIR);
$parammodulename  = optional_param('module_name', '', PARAM_SAFEDIR);

// Print headings.
$strmanageblocks           = get_string('manageblocks');
$strhide                   = get_string('hide');
$strshow                   = get_string('show');
$strsettings               = get_string('settings');
$strname                   = get_string('name');
$strshowblockcourse        = get_string('showblockcourse');
$strclearheading          = get_string('clear_title', 'local_contextadmin');
$stroverrideheading       = get_string('override_title', 'local_contextadmin');
$stroverridecalueheading = get_string('override_value_title', 'local_contextadmin');
$strlockedheading         = get_string('locked_title', 'local_contextadmin');
$strblocks                 = get_string("blocks");
$stractivitymodule         = get_string("activitymodule");
$strshowmodulecourse       = get_string('showmodulecourse');

$haseditsettingscapability   = has_capability('local/contextadmin:editowncatsettings', $context);
$haseditvisibilitycapability = has_capability('local/contextadmin:changevisibilty', $context);

// If data submitted, then process and store.
if ((!empty($paramblockname)) and confirm_sesskey() && ($haseditvisibilitycapability or $haseditsettingscapability) &&
    !is_plugin_locked($catid, $parammodulename, 'modules')) {

    if ($DB->get_record("block", array("name" => $paramblockname))) {
        if (!is_plugin_locked($catid, $paramblockname, 'block')) {
            if ($paramvisible !== null) {

                set_context_block_settings($catid, $paramblockname, array('visible' => $paramvisible, 'search' => ''));
            }
            if ($paramoverride !== null) {
                set_context_block_settings($catid, $paramblockname, array('override' => $paramoverride, 'search' => ''));
            }
            if ($paramlocked !== null) {
                set_context_block_settings($catid, $paramblockname, array('locked' => $paramlocked, 'search' => ''));
            }
            if ($paramclear !== null) {
                remove_category_block_values($catid, $paramblockname);
            }
        } else {
            print_error('blocklocked', 'local_contextadmin');
        }
    } else {
        print_error('noblocks', 'error');
    }
}

// Category is our primary source of context.  This is important.
// Setup the PAGE object.
$category = $PAGE->category;
$site     = get_site();
$PAGE->set_title("$site->shortname: $category->name");
$PAGE->set_heading($site->fullname);
$PAGE->set_pagelayout('coursecategory');
echo $OUTPUT->header();
echo $OUTPUT->heading($category->name . ': ' . $strmanageblocks);

// Main display starts here.
// Get and sort the existing blocks.
if (!$blocks = $DB->get_records('block', array(), 'name ASC')) {
    print_error('noblocks', 'error'); // Should never happen.
}

$incompatible = array();

// Print the table of all blocks.
$table = new flexible_table('admin-blocks-compatible');
// TODO: tying the capability to hide/show blocks to the same one for hide/show modules. Might need it's own in the future.
if ($haseditsettingscapability) {
    $table->define_columns(array('name', 'override_value', 'hideshow', 'override', 'lock', 'clear', 'settings'));
    $table->define_headers(array($stractivitymodule, $stroverridecalueheading, "$strhide/$strshow", $stroverrideheading,
                               $strlockedheading,
                               $strclearheading, $strsettings));
} else if ($haseditvisibilitycapability) {
    // User can not edit settings for modules but can hide/show.
    $table->define_columns(array('name', 'override_value', 'hideshow', 'override', 'lock', 'clear'));
    $table->define_headers(array($stractivitymodule, $stroverridecalueheading, "$strhide/$strshow", $stroverrideheading,
                               $strlockedheading,
                               $strclearheading));
} else {
    $table->define_columns(array('name'));
    $table->define_headers(array($stractivitymodule));
}

$table->define_baseurl($CFG->wwwroot . '/' . $CFG->admin . '/blocks.php');
$table->set_attribute('class', 'compatibleblockstable blockstable generaltable');
$table->setup();
$tablerows = array();

foreach ($blocks as $blockid => $currentblock) {
    $visibletd        = '';
    $cleartd          = '';
    $overridetd       = '';
    $overridevaluetd = '';
    $lockedtd         = '';
    $settingstd       = '';

    // Get current block settings (hidden/shown, etc..) for current category.
    $categoryblock = get_context_block_settings($catid, $currentblock->name, false);
    $blockname      = $currentblock->name;

    if (!file_exists("$CFG->dirroot/blocks/$blockname/block_$blockname.php")) {
        $strblockname = '<span class="notifyproblem">' . $blockname . ' (' . get_string('missingfromdisk') . ')</span>';
        $plugin       = new stdClass();
    } else {
        $strblockname = get_string('pluginname', 'block_' . $blockname);
    }

    $class = ''; // Nothing fancy, by default.

    if ($haseditvisibilitycapability or $haseditsettingscapability) {
        $selfpath        = "blocks.php?contextid=$contextid&catid=$catid";
        $isoverridden    = is_block_overridden($catid, $blockname);
        $islocked        = is_block_locked($catid, $blockname);
        $lockedimagetag = create_image_tag($OUTPUT->pix_url('i/hierarchylock'), 'locked in parent category');
        // Representation of the block if we were to climb the tree.
        $blockrepresentation = get_context_block_settings($catid, $blockname);

        // For each section provide a form if it is not locked. If it is locked only show icons.
        // If the block is overridden then show the overridden value in front of the category's value.
        if ($isoverridden) {
            if ($blockrepresentation->visible) {
                $overridevaluetd .= create_image_tag($OUTPUT->pix_url('i/hide'), get_string('visible_alt',
                                                                                              'local_contextadmin'), 'overridden');
            } else {
                $overridevaluetd .= create_image_tag($OUTPUT->pix_url('i/show'), get_string('not_visible_alt',
                                                                                              'local_contextadmin'), 'overridden');
            }
        }

        // Test for existence of this category's module.
        if ($categoryblock) {

            if ($categoryblock->visible) {
                if ($islocked) {
                    $visibletd .= create_image_tag($OUTPUT->pix_url('i/hide'), 'hidden');
                    $visibletd .= $lockedimagetag;

                } else {
                    $visibletd .= create_form($OUTPUT, $currentblock->name . "_visible_form", $selfpath, $strhide, 'hide',
                                               array('block_name' => $currentblock->name, 'visible' => 'false',
                                                     'sesskey'    => sesskey()));
                }
            } else {
                if ($islocked) {
                    $visibletd .= create_image_tag($OUTPUT->pix_url('i/show'), 'visible');
                    $visibletd .= $lockedimagetag;
                    $class = ' class="dimmed_text"';
                } else {
                    $visibletd .= create_form($OUTPUT, $currentblock->name . "_visible_form", $selfpath, $strhide, 'show',
                                               array('block_name' => $currentblock->name, 'visible' => 'true',
                                                     'sesskey'    => sesskey()));
                    $class = ' class="dimmed_text"';
                }
            }

            if (!$islocked) {
                $cleartd = create_form($OUTPUT, $currentblock->name . "_clear_form", $selfpath, $strhide, 'cross_red_big',
                                        array('block_name' => $currentblock->name, 'clear' => 'true', 'sesskey' => sesskey()));
            }

            if ($categoryblock->override) {

                if (!$islocked) {
                    $overridetd =
                        create_form($OUTPUT, $currentblock->name . "_override_form", $selfpath, $strhide, 'completion-manual-y',
                                    array('block_name' => $currentblock->name, 'override' => 'false', 'sesskey' => sesskey()));

                } else {
                    $overridetd = create_image_tag($OUTPUT->pix_url('i/completion-manual-y'), 'locked in parent category');
                }
            } else {
                if (!$islocked) {
                    $overridetd =
                        create_form($OUTPUT, $currentblock->name . "_override_form", $selfpath, $strhide, 'completion-manual-n',
                                    array('block_name' => $currentblock->name, 'override' => 'true', 'sesskey' => sesskey()));
                } else {
                    $overridetd = create_image_tag($OUTPUT->pix_url('i/completion-manual-n'), 'locked in parent category');
                }
            }

            if ($categoryblock->locked) {
                if (!$islocked) {
                    $lockedtd =
                        create_form($OUTPUT, $currentblock->name . "_locked_form", $selfpath, $strhide, 'completion-manual-y',
                                    array('block_name' => $currentblock->name, 'locked' => 'false', 'sesskey' => sesskey()));

                } else {
                    $lockedtd = create_image_tag($OUTPUT->pix_url('i/completion-manual-y'), 'locked in parent category');
                }
            } else {
                if (!$islocked) {
                    $lockedtd =
                        create_form($OUTPUT, $currentblock->name . "_locked_form", $selfpath, $strhide, 'completion-manual-n',
                                    array('block_name' => $currentblock->name, 'locked' => 'true', 'sesskey' => sesskey()));
                } else {
                    $lockedtd = create_image_tag($OUTPUT->pix_url('i/completion-manual-n'), 'locked in parent category');
                }
            }
        } else { // Nothing set at this category so lets show the current representation instead.

            if ($blockrepresentation->visible) {
                if ($islocked) {
                    $visibletd .= create_image_tag($OUTPUT->pix_url('i/hide'), 'hidden');
                    $visibletd .= $lockedimagetag;

                } else {
                    $visibletd .= create_form($OUTPUT, $currentblock->name . "_visible_form", $selfpath, $strhide, 'hide',
                                               array('block_name' => $currentblock->name, 'visible' => 'false',
                                                     'sesskey'    => sesskey()));
                }
            } else {
                if ($islocked) {
                    $visibletd .= create_image_tag($OUTPUT->pix_url('i/show'), 'visible');
                    $visibletd .= $lockedimagetag;
                } else {
                    $visibletd .= create_form($OUTPUT, $currentblock->name . "_visible_form", $selfpath, $strhide, 'show',
                                               array('block_name' => $currentblock->name, 'visible' => 'true',
                                                     'sesskey'    => sesskey()));
                    $class = ' class="dimmed_text"';
                }
            }
        }
    }

    $tabledata = array('<span' . $class . '>' . $blockname . '</span>');
    if ($haseditvisibilitycapability or $haseditsettingscapability) {
        $tabledata[] = $overridevaluetd;
        $tabledata[] = $visibletd;
        $tabledata[] = $overridetd;
        $tabledata[] = $lockedtd;
        $tabledata[] = $cleartd;
    }
    if ($haseditsettingscapability) {
        $tabledata[] = $settingstd;
    }

    $table->add_data($tabledata);
}

$table->finish_html();
echo $OUTPUT->footer();
