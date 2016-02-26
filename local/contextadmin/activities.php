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


// Defines.
define('MODULE_TABLE', 'module_administration_table');

require_login();

$contextid = required_param("contextid", PARAM_INT);
$catid     = required_param("catid", PARAM_INT);

// Get the context that was passed (verify it is course category or system context).
$context = context::instance_by_id($contextid, MUST_EXIST);


// TODO: Exit cleanly...fix this later.
// If we do not belong here.....
// Setup Page (not admin setup).
$PAGE->set_url("/local/contextadmin/activities.php", array("contextid" => $contextid, "catid" => $catid));
$PAGE->set_category_by_id($catid);

$paramvisible     = optional_param('visible', null, PARAM_BOOL);
$paramclear       = optional_param('clear', null, PARAM_BOOL);
$paramdelete      = optional_param('delete', null, PARAM_BOOL);
$paramconfirm     = optional_param('confirm', null, PARAM_BOOL);
$paramoverride    = optional_param('override', null, PARAM_BOOL);
$paramlocked      = optional_param('locked', null, PARAM_BOOL);
$parammodulename  = optional_param('module_name', '', PARAM_SAFEDIR);


// Print headings.
$stractivities             = get_string("activities");
$strhide                   = get_string("hide");
$strshow                   = get_string("show");
$strclearheading          = get_string('clear_title', 'local_contextadmin');
$stroverrideheading       = get_string('override_title', 'local_contextadmin');
$strlockedheading         = get_string('locked_title', 'local_contextadmin');
$stroverridevalueheading = get_string('override_value_title', 'local_contextadmin');
$strsettings               = get_string("settings");
$stractivitymodule         = get_string("activitymodule");
$strshowmodulecourse       = get_string('showmodulecourse');

$haseditsettingscapability   = has_capability('local/contextadmin:editowncatsettings', $context);
$haseditvisibilitycapability = has_capability('local/contextadmin:changevisibilty', $context);
// If data submitted, then process and store.
if ((!empty($parammodulename)) and confirm_sesskey() && ($haseditvisibilitycapability or $haseditsettingscapability) &&
    !is_plugin_locked($catid, $parammodulename, 'modules')) {

    if ($DB->record_exists("modules", array("name" => $parammodulename))) {
        if (!is_plugin_locked($catid, $parammodulename, 'modules')) {
            if ($paramvisible !== null) {

                set_context_module_settings($catid, $parammodulename, array('visible' => $paramvisible, 'search' => ''));
            }
            if ($paramoverride !== null) {
                set_context_module_settings($catid, $parammodulename, array('override' => $paramoverride, 'search' => ''));
            }
            if ($paramlocked !== null) {
                set_context_module_settings($catid, $parammodulename, array('locked' => $paramlocked, 'search' => ''));
            }
            if ($paramclear !== null) {
                remove_category_module_values($catid, $parammodulename);
            }
        } else {
            print_error('modulelocked', 'local_contextadmin');
        }
    } else {
        print_error('moduledoesnotexist', 'error');
    }
}

// Category is our primary source of context.  This is important.
$category = $PAGE->category;
$site     = get_site();
$PAGE->set_title("$site->shortname: $category->name");
$PAGE->set_heading($site->fullname);
$PAGE->set_pagelayout('coursecategory');
echo $OUTPUT->header();
echo $OUTPUT->heading($category->name . ': ' . $stractivities);

// Get and sort the existing modules.

// Modules are retrieved from main mdl_modules table and NOT mdl_cat_modules since at most.

// The mdl_cat_modules is a subset of modules that exist in mdl_modules.
if (!$modules = $DB->get_records('modules', array(), 'name ASC')) {
    print_error('moduledoesnotexist', 'error');
}

// Print the table of all modules.
// Construct the flexible table ready to display.
$table = new flexible_table(MODULE_TABLE);
// User can edit settings for modules within this category.
if ($haseditsettingscapability) {
    $table->define_columns(array('name', 'override_value', 'hideshow', 'override', 'lock', 'clear', 'settings'));
    $table->define_headers(array($stractivitymodule, $stroverridevalueheading, "$strhide/$strshow", $stroverrideheading,
                               $strlockedheading,
                               $strclearheading, $strsettings));
} else if ($haseditvisibilitycapability) { // User can not edit settings for modules but can hide/show.
    $table->define_columns(array('name', 'override_value', 'hideshow', 'override', 'lock', 'clear'));
    $table->define_headers(array($stractivitymodule, $stroverridevalueheading, "$strhide/$strshow", $stroverrideheading,
                               $strlockedheading,
                               $strclearheading));
} else {
    $table->define_columns(array('name'));
    $table->define_headers(array($stractivitymodule));
}

$table->define_baseurl($CFG->wwwroot . '/' . $CFG->admin . '/modules.php');
$table->set_attribute('id', 'modules');
$table->set_attribute('class', 'generaltable');
$table->setup();

foreach ($modules as $currentmodule) {
    $visibletd        = '';
    $overridevaluetd = '';
    $cleartd          = '';
    $overridetd       = '';
    $lockedtd         = '';
    $settingstd       = '';

    // TODO: make a more efficient way to grab initial category modules instead of site level then overriding.
    $categorymodule = get_context_module_settings($catid, $currentmodule->name, false);
    if (!file_exists("$CFG->dirroot/mod/$currentmodule->name/lib.php")) {
        $strmodulename = '<span class="notifyproblem">' . $currentmodule->name . ' (' . get_string('missingfromdisk') . ')</span>';
    } else {
        // Took out hspace="\10\", because it does not validate. don't know what to replace with.
        $icon          = "<img src=\"" . $OUTPUT->pix_url('icon', $currentmodule->name) . "\" class=\"icon\" alt=\"\" />";
        $strmodulename = $icon . ' ' . get_string('modulename', $currentmodule->name);
    }

    if (file_exists("$CFG->dirroot/local/contextadmin/mod/$currentmodule->name/cat_settings.php") &&
        $haseditsettingscapability
    ) {
        $settingstd =
            "<a href=\"cat_settings.php?section=modsetting$currentmodule->name&name=$currentmodule->name&contextid=$contextid\">
            $strsettings</a>";
    } else {
        $settingstd = "";
    }

    $class = '';

    // If we can hide/show then create the icons/links.
    // Do not show these for forum, changing visibility breaks announcement tool.
    if (($haseditvisibilitycapability or $haseditsettingscapability) and $currentmodule->name != "forum") {
        $selfpath        = "activities.php?contextid=$contextid&catid=$catid";
        $islocked        = is_module_locked($catid, $currentmodule->name);
        $isoverridden    = is_module_overridden($catid, $currentmodule->name);
        $lockedimagetag = create_image_tag($OUTPUT->pix_url('i/hierarchylock'), 'locked in parent category');
        // Representation of the module if we were to climb the tree.
        $modulerepresentation = get_context_module_settings($catid, $currentmodule->name);


        // For each section provide a form if it is not locked. If it is locked only show icons.
        // If the module is overridden then show the overridden value in front of the category's value.
        if ($isoverridden) {
            if ($modulerepresentation->visible) {
                $overridevaluetd .= create_image_tag($OUTPUT->pix_url('i/hide'), get_string('visible_alt',
                                                                                              'local_contextadmin'), 'overridden');
            } else {
                $overridevaluetd .= create_image_tag($OUTPUT->pix_url('i/show'), get_string('not_visible_alt',
                                                                                              'local_contextadmin'), 'overridden');
            }
        }

        // Test for existence of this category's module.
        if ($categorymodule) {

            if ($categorymodule->visible) {
                if ($islocked) {
                    $visibletd .= create_image_tag($OUTPUT->pix_url('i/hide'), 'hidden');
                    $visibletd .= $lockedimagetag;

                } else {
                    $visibletd .= create_form($OUTPUT, $currentmodule->name . "_visible_form", $selfpath, $strhide, 'hide',
                                               array('module_name' => $currentmodule->name, 'visible' => 'false',
                                                     'sesskey'     => sesskey()));
                }
            } else {
                if ($islocked) {
                    $visibletd .= create_image_tag($OUTPUT->pix_url('i/show'), 'visible');
                    $visibletd .= $lockedimagetag;
                    $class = ' class="dimmed_text"';
                } else {
                    $visibletd .= create_form($OUTPUT, $currentmodule->name . "_visible_form", $selfpath, $strhide, 'show',
                                               array('module_name' => $currentmodule->name, 'visible' => 'true',
                                                     'sesskey'     => sesskey()));
                    $class = ' class="dimmed_text"';
                }
            }

            if (!$islocked) {
                $cleartd = create_form($OUTPUT, $currentmodule->name . "_clear_form", $selfpath, $strhide, 'cross_red_big',
                                        array('module_name' => $currentmodule->name, 'clear' => 'true', 'sesskey' => sesskey()));
            }

            if ($categorymodule->override) {

                if (!$islocked) {
                    $overridetd =
                        create_form($OUTPUT, $currentmodule->name . "_override_form", $selfpath, $strhide, 'completion-manual-y',
                                    array('module_name' => $currentmodule->name, 'override' => 'false', 'sesskey' => sesskey()));

                } else {
                    $overridetd = create_image_tag($OUTPUT->pix_url('i/completion-manual-y'), 'locked in parent category');
                }
            } else {
                if (!$islocked) {
                    $overridetd =
                        create_form($OUTPUT, $currentmodule->name . "_override_form", $selfpath, $strhide, 'completion-manual-n',
                                    array('module_name' => $currentmodule->name, 'override' => 'true', 'sesskey' => sesskey()));
                } else {
                    $overridetd = create_image_tag($OUTPUT->pix_url('i/completion-manual-n'), 'locked in parent category');
                }
            }

            if ($categorymodule->locked) {
                if (!$islocked) {
                    $lockedtd =
                        create_form($OUTPUT, $currentmodule->name . "_locked_form", $selfpath, $strhide, 'completion-manual-y',
                                    array('module_name' => $currentmodule->name, 'locked' => 'false', 'sesskey' => sesskey()));

                } else {
                    $lockedtd = create_image_tag($OUTPUT->pix_url('i/completion-manual-y'), 'locked in parent category');
                }
            } else {
                if (!$islocked) {
                    $lockedtd =
                        create_form($OUTPUT, $currentmodule->name . "_locked_form", $selfpath, $strhide, 'completion-manual-n',
                                    array('module_name' => $currentmodule->name, 'locked' => 'true', 'sesskey' => sesskey()));
                } else {
                    $lockedtd = create_image_tag($OUTPUT->pix_url('i/completion-manual-n'), 'locked in parent category');
                }
            }
        } else { // Nothing set at this category so lets show the current representation instead.

            if ($modulerepresentation->visible) {
                if ($islocked) {
                    $visibletd .= create_image_tag($OUTPUT->pix_url('i/hide'), 'hidden');
                    $visibletd .= $lockedimagetag;

                } else {
                    $visibletd .= create_form($OUTPUT, $currentmodule->name . "_visible_form", $selfpath, $strhide, 'hide',
                                               array('module_name' => $currentmodule->name, 'visible' => 'false',
                                                     'sesskey'     => sesskey()));
                }
            } else {
                if ($islocked) {
                    $visibletd .= create_image_tag($OUTPUT->pix_url('i/show'), 'visible');
                    $visibletd .= $lockedimagetag;
                } else {
                    $visibletd .= create_form($OUTPUT, $currentmodule->name . "_visible_form", $selfpath, $strhide, 'show',
                                               array('module_name' => $currentmodule->name, 'visible' => 'true',
                                                     'sesskey'     => sesskey()));
                    $class = ' class="dimmed_text"';
                }
            }
        }
    }

    $tabledata = array('<span' . $class . '>' . $strmodulename . '</span>');
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
