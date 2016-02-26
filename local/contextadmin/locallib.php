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

defined('MOODLE_INTERNAL') || die();

if (!defined("CONTEXTADMINDEBUG")) {
    define("CONTEXTADMINDEBUG", false);
}

/**
 * If no category root exists creates it and returns it. Otherwise just returns the already created root.
 * @param settings_navigation $nav
 * @return navigation_node
 */
function category_get_root(settings_navigation &$nav) {
    static $categoryroot = null;
    if (!isset($categoryroot)) {
        $categoryroot = $nav->add(get_string('catadmin', 'local_contextadmin')); // Root Node.
    }
    return $categoryroot;
}

/**
 * Returns the path string associated with a groupid
 * Returns "" if groupid not found
 * @param $categoryid id of the category
 * @return string "0" if category not found, path string otherwise eg. "/1114/1046/1038/1129"
 * where 1129 is the most specific context (requested category), and 1038 is its parent.
 */
function get_category_path($categoryid) {
    global $DB;
    $rec = $DB->get_record('course_categories', array('id' => $categoryid), 'path');
    if (empty($rec)) {
        return "0";
    }
    return $rec->path;
}

/**
 * Retrieves a list of all visible blocks in the category context
 * @param $categoryid
 * @return array
 */
function get_all_blocks($categoryid) {
    return get_context_list($categoryid, 'block');
}

/**
 * Retrieves a list of all visible modules in the category context
 * @param $categoryid
 * @return array
 */
function get_context_modules($categoryid) {
    return get_context_list($categoryid, 'modules');
}

/**
 * Returns a list of all objects of type $type in the config tables.
 * The function will climb the list and return only the correct plugin values in the context hierarchy.
 * @param $categoryid
 * @param string $type
 * @return array
 */
function get_context_list($categoryid, $type = 'modules') {
    global $DB;
    if (CONTEXTADMINDEBUG) {
        echo "get_context_obj($categoryid, $type):\n";
    }
    $path        = get_category_path($categoryid);
    $pathstring = ltrim($path, '/');
    $apath      = explode('/', $pathstring);
    $arevpath  = array_reverse($apath);

    // Build on these.
    $siteobjects = $DB->get_records("$type");
    if (!empty($categoryid)) {

        /*
        * go through the categories starting from nearest to top
        * 1. extract records for current category
        * 2. process records and collect up changes first in collection overrides later ones
        * 3. apply collected settings over the site_modules and return
        */

        $objectcollection = array(); // Keys should be module names.

        foreach ($arevpath as $catid) {
            $acur = $DB->get_records("cat_$type", array('category_id' => $catid));
            foreach ($acur as $cur) {
                if (CONTEXTADMINDEBUG) {
                    echo "Found " . $cur->name . " value: " . $cur->visible . " in cat $catid";
                }
                if (!array_key_exists($cur->name, $objectcollection) || $cur->override == true) {
                    $objectcollection[$cur->name] = $cur;
                } else {
                    if (CONTEXTADMINDEBUG) {
                        echo " (preceded by earlier category)";
                    }
                }
                if (CONTEXTADMINDEBUG) {
                    echo "\n";
                }
            }
        }
        foreach ($siteobjects as $smod) {
            if (array_key_exists($smod->name, $objectcollection)) {
                $smod->visible = $objectcollection[$smod->name]->visible;
                if ($type == 'modules') {
                    $smod->search = $objectcollection[$smod->name]->search;
                }

            }
        }
    }
    return $siteobjects;
}

/**
 * @deprecated
 * Returns the content of the 'value' field for the desired plugin setting.
 * Climbs the tree of the categories searching for correct record.
 * @param $categoryid
 * @param $settingname
 * @param null $plugin
 * @return mixed|null
 */
function get_context_config_field($categoryid, $settingname, $plugin = null) {
    global $DB;
    if (CONTEXTADMINDEBUG) {
        echo "get_context_config_field($categoryid,$settingname,$plugin):\n";
    }
    $result = get_context_config($categoryid, $plugin);
    return $result[$settingname];
}


/**
 * This function works in a way similar to get_config except it cycles through the context path to gather settings from
 * multiple categories up the tree to the global setting.
 * If no settings are set at a category level it will fall back to the settings at the global level.
 *
 * @param $categoryid
 * @param null $plugin
 * @return array
 */
function get_context_config($categoryid, $plugin = null) {
    global $DB;
    if (CONTEXTADMINDEBUG) {
        echo "get_context_config($categoryid,$plugin):\n";
    }
    $iscore = false;
    if ($plugin === 'moodle' || $plugin === 'core' || empty($plugin)) {
        $forced =& $CFG->config_php_settings;
        $iscore = true;
        $plugin = 'core';
    }

    /*
     * Now you might be thinking that instead of climbing the context ladder everytime, we should
     * instead cache the result of climbing the ladder for the context being requested.
     * But in doing so we introduce a large amount of complexity in the cache invalidation for this
     * context, which we would likely lead to errors if we miss some corner case, or even
     * innefficiency when changing top level contexts as we'd need to bulk invalidate
     * all sub-context caches (which will hopefully be large). So we'll instead climb it each time and rely on the caching of
     * the individual contexts being incredibly fast.
     */

    /*
     * Algorithm:
     * 1. Initialize an empty config collection object
     * 2. Fetch the context path
     * 3. Ascend through path contexts starting at the most specific through the parents
     *      1. Retrieve Context cache, initialize cache if required
     *      2. For each stored setting
     *          1. If the setting doesn't exist, store the setting
     *          2. If it does exist, ignore unless 'overridden' value is set
     * 4. Fetch core settings, initialize cache if required
     * 5. Overwrite each core setting with setting from collection object
     */

    $path = get_category_path($categoryid);
    $pathstring = ltrim($path, '/');
    // Reverse so we climb from specific to less specific.
    $reversepathlist = array_reverse(explode('/', $pathstring));
    // This will keep configuration state as we climb.
    $configcollection = array();

    $cacheprefix = ($iscore) ? 'context_config_specific_' : 'context_config_plugins_specific_';

    foreach ($reversepathlist as $curcategoryid) {
        // Individual category config.
        $cache = cache::make('core', $cacheprefix.$curcategoryid);
        $categoryconf = $cache->get($plugin);
        if ($categoryconf === false) {
            if (!$iscore) {
                $categoryconf = $DB->get_records('cat_config_plugins', array('category_id' => $curcategoryid, 'plugin' => $plugin),
                                                 '', 'name,value,override,locked');
            } else {
                // This part is not really used any more, but anyway...(this comment is a lie, but anyway...).
                $categoryconf = $DB->get_records('cat_config', array('category_id' => $curcategoryid), '',
                                                 'name,value,override,locked');
            }
            $cache->set($plugin, $categoryconf);
        }
        // Category config accumulation.
        foreach ($categoryconf as $conf) {
            if (array_key_exists($conf->name, $configcollection)) {
                if ($conf->override) {
                    $configcollection[$conf->name] = $conf->value;
                }
            } else {
                // We store the conf object to match expected return format of 'get_config'.
                $configcollection[$conf->name] = $conf->value;
            }
        }
    }

     // We're doing this last instead of initializing the collection to keep the accumulation logic
     // ...clean. Otherwise we'd have to make a special exception for the first pass of any specific
     // ...config setting. If a config is make anywhere in the context heirarchy it should take precedence over
     // ...the core configuration value.
     // Initialize the core settings cache (we can't rely that this is cached yet).
    $cache  = cache::make('core', 'config');
    $result = $cache->get($plugin);
    if ($result === false) {
        if (!$iscore) {
            $result = $DB->get_records_menu('config_plugins', array('plugin' => $plugin), '', 'name,value');
        } else {
            $result = $DB->get_records_menu('config', array(), '', 'name,value');;
        }
        $cache->set($plugin, $result);
    }
    return array_merge($sitesettings, $setcollection);
}

/**
 * Returns whether the userid has the contextadmin category view capability at the system context level.
 * @param int $userid
 */
function has_category_view_capability($userid) {
    global $DB;
    if (is_siteadmin()) {
        return true;
    }

    $sql    = "select *
            from {role_assignments} ra join {role_capabilities} rc ON(ra.roleid=rc.roleid)
            where capability = :capability and ra.userid = :userid and rc.contextid = :ctx";
    $params = array('capability' => 'local/contextadmin:viewcategories', 'userid' => $userid, 'ctx' => 1);
    $result = $DB->get_records_sql($sql, $params, 0, 1);
    return !empty($result);
}

function eclass_debug($msg) {
    if (defined(CONTEXTADMINDEBUG)) {
        echo $msg;
    }
}

// CATEGORY DISPLAY (Functions that list the Categories that are Managed by the current User).

/**
 * Recursive function to print out all the categories in a nice format
 * with or without courses included
 *
 * @param null $category current category
 * @param null $displaylist display list
 * @param null $parentslist list of parent categories
 * @param $depth depth of the category for indentation purposes
 * @param bool $showcourses determines if we display courses and course information
 * @param string $branch current output that is stored until visibilty is true. Otherwise this branch output gets destroyed
 * @param bool $visible maintains the visibility state of the navigation branch
 * @param $output variable used to output html during recursion
 * @return mixed
 */
function print_whole_category_manager_list($category = null, $displaylist = null, $parentslist = null, $depth = -1,
                                           $showcourses = true, $branch = '', $visible = false, &$output) {
    global $CFG;

    // Note: maxcategorydepth == 0 meant no limit.
    if (!empty($CFG->maxcategorydepth) && $depth >= $CFG->maxcategorydepth) {
        return;
    }

    if (!$displaylist) {
        make_categories_manager_list($displaylist, $parentslist);
    }

    if ($category) {

        if (!has_capability('moodle/category:viewhiddencategories',
                            context_coursecat::instance($category->id)) && $visible
        ) {
            $visible = false;
            $branch  = print_category_manager_info($category, $depth, $showcourses, $visible);
        } else if ($visible) {
            $branch .= print_category_manager_info($category, $depth, $showcourses, $visible);
            $output .= $branch;
            $branch = '';
        } else {
            $branch .= print_category_manager_info($category, $depth, $showcourses, $visible);
        }

    } else {
        $category = new stdClass();
        $category->id = "0";
    }

    if ($categories = get_child_manager_categories($category->id)) { // Print all the children recursively.
        $countcats = count($categories);
        $count     = 0;
        $first     = true;
        $last      = false;

        foreach ($categories as $cat) {
            if (has_capability('moodle/category:viewhiddencategories',
                               context_coursecat::instance($cat->id)) && !$visible
            ) {
                $visible = true;
            }
            $count++;
            if ($count == $countcats) {
                $last = true;
            }
            $up    = $first ? false : true;
            $down  = $last ? false : true;
            $first = false;

            print_whole_category_manager_list($cat, $displaylist, $parentslist, $depth + 1, $showcourses, $branch, $visible,
                                              $output);
        }
    } else {
        $visible = false;
        $depth   = 0;
        $branch  = '';
    }
}

/**
 * @param $list
 * @param $parents
 * @param string $requiredcapability
 * @param int $excludeid
 * @param null $category
 * @param string $path
 * @return mixed
 */
function make_categories_manager_list(&$list, &$parents, $requiredcapability = '', $excludeid = 0, $category = null, $path = "") {
    $requiredcapability = '';

    // Initialize the arrays if needed.
    if (!is_array($list)) {
        $list = array();
    }
    if (!is_array($parents)) {
        $parents = array();
    }

    if (empty($category)) {
        // Start at the top level.
        $category     = new stdClass;
        $category->id = 0;
    } else {
        // This is the excluded category, don't include it.
        if ($excludeid > 0 && $excludeid == $category->id) {
            return;
        }

        $context      = context_coursecat::instance($category->id);
        $categoryname = format_string($category->name, true, array('context' => $context));

        // Update $path.
        if ($path) {
            $path = $path . ' / ' . $categoryname;
        } else {
            $path = $categoryname;
        }

        // Add this category to $list, if the permissions check out.
        if (empty($requiredcapability)) {
            $list[$category->id] = $path;

        } else {
            $requiredcapability = (array)$requiredcapability;
            if (has_all_capabilities($requiredcapability, $context)) {
                $list[$category->id] = $path;
            }
        }
    }

    // Add all the children recursively, while updating the parents array.
    if ($categories = get_child_manager_categories($category->id)) {
        foreach ($categories as $cat) {
            if (!empty($category->id)) {
                if (isset($parents[$category->id])) {
                    $parents[$cat->id] = $parents[$category->id];
                }
                $parents[$cat->id][] = $category->id;
            }
            make_categories_manager_list($list, $parents, $requiredcapability, $excludeid, $cat, $path);
        }
    }
}


/**
 * Get the children categories of a given parent
 *
 * @param $parentid
 * @return array
 */
function get_child_manager_categories($parentid) {
    static $allcategories = null;

    // Only fill in this variable the first time.
    if (null == $allcategories) {
        $allcategories = array();

        $categories = get_manager_categories();
        foreach ($categories as $category) {
            if (empty($allcategories[$category->parent])) {
                $allcategories[$category->parent] = array();
            }
            $allcategories[$category->parent][] = $category;
        }
    }

    if (empty($allcategories[$parentid])) {
        return array();
    } else {
        return $allcategories[$parentid];
    }
}

/**
 * Be careful with this function, there is no check for the capapbility viewhiddencategory This check needs to be done
 * elsewhere if you need this kind of restriction. (This function returns all categories regardless of viewhidden capability.
 * This is needed to show the root of the category you DO have permission to see.
 * This gives manager context to where their category resides in the hierarchy if the parent category is hidden).
 *
 * @param string $parent
 * @param null $sort
 * @param bool $shallow
 * @return array
 */
function get_manager_categories($parent = 'none', $sort = null, $shallow = true) {
    global $DB;

    if ($sort === null) {
        $sort = 'ORDER BY cc.sortorder ASC';
    } else if ($sort === '') {
        $sort = '';
    } else {
        $sort = "ORDER BY $sort";
    }

    $ccselect = ", " . context_helper::get_preload_record_columns_sql('ctx');
    $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = cc.id AND ctx.contextlevel = ".CONTEXT_COURSECAT.")";

    if ($parent === 'none') {
        $sql    = "SELECT cc.* $ccselect
                  FROM {course_categories} cc
               $ccjoin
                $sort";
        $params = array();

    } else if ($shallow) {
        $sql    = "SELECT cc.* $ccselect
                  FROM {course_categories} cc
               $ccjoin
                 WHERE cc.parent=?
                $sort";
        $params = array($parent);

    } else {
        $sql    = "SELECT cc.* $ccselect
                  FROM {course_categories} cc
               $ccjoin
                  JOIN {course_categories} ccp
                       ON ((cc.parent = ccp.id) OR (cc.path LIKE " . $DB->sql_concat('ccp.path', "'/%'") . "))
                 WHERE ccp.id=?
                $sort";
        $params = array($parent);
    }
    $categories = array();

    $rs = $DB->get_recordset_sql($sql, $params);
    foreach ($rs as $cat) {
        context_helper::preload_from_record($cat);
        $catcontext           = context_coursecat::instance($cat->id);
        $categories[$cat->id] = $cat;
    }
    $rs->close();
    return $categories;
}

/**
 *  Prints the category info in indented fashion
 *  There are two display possibilities.
 *    1. Display categories without courses ($showcourses = false)
 *    2. Display categories with courses ($showcategories = true)
 *
 *  This function is only used by print_whole_manager_category_list() above
 */
function print_category_manager_info($category, $depth = 0, $showcourses = false, $visible) {
    global $CFG, $DB, $OUTPUT;
    $output = '';

    $strsummary = get_string('summary');

    $catlinkcss = null;
    if (!$category->visible) {
        $catlinkcss = array('class' => 'dimmed');
    }
    static $coursecount = null;
    if (null === $coursecount) {
        // Only need to check this once.
        $coursecount = $DB->count_records('course') <= $CFG->frontpagecourselimit;
    }

    if ($visible) {
        $catimage = '<img src="' . $OUTPUT->pix_url('i/course') . '" alt="" />&nbsp;';
    } else {
        $catimage = '<img src="' . $OUTPUT->pix_url('courseclosed', 'local_contextadmin') . '" alt="" />&nbsp;';
    }

    $courses  = get_courses($category->id, 'c.sortorder ASC', 'c.id,c.sortorder,c.visible,c.fullname,c.shortname,c.summary');
    $context  = context_coursecat::instance($category->id);
    $fullname = format_string($category->name, true, array('context' => $context));

    $output .= '<div class="categorylist clearfix">';
    $cat = '';
    $cat .= html_writer::tag('div', $catimage, array('class' => 'image'));
    if ($visible) {
        $catlink = html_writer::link(new moodle_url('/course/index.php',
                array('categoryid' => $category->id, 'categoryedit' => true)),
            $fullname, $catlinkcss);
        $cat .= html_writer::tag('div', $catlink, array('class' => 'name'));
    } else {
        $cat .= html_writer::tag('div', $fullname, array('class' => 'name'));
    }

    $html = '';
    if ($depth > 0) {
        for ($i = 0; $i < $depth; $i++) {
            $html = html_writer::tag('div', $html . $cat, array('class' => 'indentation'));
            $cat  = '';
        }
    } else {
        $html = $cat;
    }
    $output .= html_writer::tag('div', $html, array('class' => 'category'));
    $output .= html_writer::tag('div', '', array('class' => 'clearfloat'));

    // Does the depth exceed maxcategorydepth.
    // Note: maxcategorydepth == 0 or unset meant no limit.
    $limit = !(isset($CFG->maxcategorydepth) && ($depth >= $CFG->maxcategorydepth - 1));
    if ($courses && ($limit || $CFG->maxcategorydepth == 0) && $showcourses) {
        $output .= '<br>';
        foreach ($courses as $course) {
            $linkcss = null;
            if (!$course->visible) {
                $linkcss = array('class' => 'dimmed');
            }

            $courselink = html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)),
                                            format_string($course->fullname), $linkcss);

            // Print enrol info.
            $courseicon = '';
            if ($icons = enrol_get_course_info_icons($course)) {
                foreach ($icons as $pixicon) {
                    $courseicon = $OUTPUT->render($pixicon) . ' ';
                }
            }

            $coursecontent = html_writer::tag('div', $courseicon . $courselink, array('class' => 'name'));

            if ($course->summary) {
                $link       = new moodle_url('/course/info.php?id=' . $course->id);
                $actionlink = $OUTPUT->action_link($link,
                                                   '<img alt="' . $strsummary . '" src="' . $OUTPUT->pix_url('i/info') . '" />',
                                                   new popup_action('click', $link, 'courseinfo', array('height' => 400,
                                                                                                        'width'  => 500)),
                                                   array('title' => $strsummary));

                $coursecontent .= html_writer::tag('div', $actionlink, array('class' => 'info'));
            }

            $html = '';
            for ($i = 0; $i <= $depth; $i++) {
                $html          = html_writer::tag('div', $html . $coursecontent, array('class' => 'indentation'));
                $coursecontent = '';
            }
            $output .= html_writer::tag('div', $html, array('class' => 'course clearfloat'));
        }
    }
    $output .= '</div>';
    return $output;
}

/**
 * Convenience method to retrieve settings for module
 * @param $categoryid
 * @param $pluginname
 * @param bool $climb
 * @return mixed|string
 */
function get_context_module_settings($categoryid, $pluginname, $climb = true) {
    return get_category_plugin_values($categoryid, $pluginname, 'modules', $climb);
}

/**
 * Convenience method to set settings for module
 * @param $categoryid
 * @param $pluginname
 * @param $values
 */
function set_context_module_settings($categoryid, $pluginname, $values) {
    set_category_plugin_values($categoryid, $pluginname, 'modules', $values);
}

/**
 * Convenience method to retrieve settings for block
 * @param $categoryid
 * @param $pluginname
 * @param bool $climb
 * @return mixed|string
 */
function get_context_block_settings($categoryid, $pluginname, $climb = true) {
    return get_category_plugin_values($categoryid, $pluginname, 'block', $climb);
}

/**
 * Convenience method to set settings for block
 * @param $categoryid
 * @param $pluginname
 * @param $values
 */
function set_context_block_settings($categoryid, $pluginname, $values) {
    set_category_plugin_values($categoryid, $pluginname, 'block', $values);
}

/**
 * Retrieves the record object for a plugin by climbing the category tree.
 * @param $categoryid
 * @param $pluginname
 * @param $plugintype
 * @return stdclass record, false if not found
 *
 */
/**
 * Retrieves the record object for a plugin by climbing the category tree.
 * If $climb is false then just returns the record of the provided category id
 * @param $categoryid
 * @param $pluginname
 * @param $plugintype
 * @param bool $climb
 * @return stdclass record, false if not found
 * @throws Exception
 */
function get_category_plugin_values($categoryid, $pluginname, $plugintype, $climb = true) {
    global $DB;
    if (CONTEXTADMINDEBUG) {
        echo "get_category_plugin_values($categoryid,$pluginname, $plugintype):\n";
    }

    if (empty($pluginname)) {
        throw new Exception("Missing pluginname in get_category_plugin_values");
    }

    $validplugins = array('modules', 'block'); // Valid types.
    if (!in_array($plugintype, $validplugins)) {
        throw new Exception("Invalid plugintype in get_category_plugin_values");
    }

    $path        = get_category_path($categoryid);
    $pathstring = ltrim($path, '/');
    $apath      = explode('/', $pathstring);
    $arevpath  = array_reverse($apath);

    /*
    * go through the categories starting from nearest to top
    * 1. extract records for current category
    * 2. process records and collect up changes first in collection overrides later ones
    * 3. apply collected settings over the site_modules and return
    */
    $returnvalue = null;

    if ($climb) {
        // Use site if no context level exists.

        foreach ($arevpath as $catid) {
            $acur = $DB->get_record("cat_" . $plugintype, array('name' => $pluginname, 'category_id' => $catid));
            if (!empty($acur) && ($acur->override || empty($returnvalue))) {
                $returnvalue = $acur;
            }
        }

        $sitesettings = $DB->get_record($plugintype, array('name' => $pluginname));
        // Merge the objects together so that the extra fields in the site_settings record exist in the returned object.
        $returnvalue = (object)array_merge((array)$sitesettings, (array)$returnvalue);
        return $returnvalue;
    } else {
        // Don't climb the tree, just return the record.
        $catid = array_shift($arevpath);
        return $DB->get_record("cat_" . $plugintype, array('name' => $pluginname, 'category_id' => $catid));

    }
}

/**
 * Sets the settings for a module or block at the category level
 * @param $categoryid
 * @param $pluginname
 * @param $plugintype string the type of plugin to set (currently valid values are: modules, blocks)
 * @param $values array settings in the modules record (visible or search)
 */
function set_category_plugin_values($categoryid, $pluginname, $plugintype, $values) {
    global $DB;

    // Todo need to check rest of tree for locks above it.

    $validplugins = array('modules', 'block'); // Valid types.
    if (!in_array($plugintype, $validplugins)) {
        debugging('Invalid plugintype passed to set_category_plugin_values in local/contextadmin/locallib.php', DEBUG_DEVELOPER);
        return;
    }

    if (CONTEXTADMINDEBUG) {
        echo "set_category_plugin_values($categoryid,$pluginname,$plugintype, $values):\n";
    }

    if (!empty($pluginname) && !empty($categoryid)) {
        if ($record = $DB->get_record('cat_' . $plugintype, array('category_id' => $categoryid, 'name' => $pluginname))) {
            // Update.
            foreach ($values as $key => $value) {
                $record->$key = $value;
            }
            // Update db.
            $DB->update_record('cat_' . $plugintype, $record);
        } else {
            // Create.
            $record              = new stdClass();
            $record->name        = $pluginname;
            $record->category_id = $categoryid;
            $record              = (object)array_merge((array)$record, (array)$values);
            // Insert into db.
            $DB->insert_record('cat_' . $plugintype, $record);
        }
    } else {
        throw new Exception("set_category_plugin_values missing arguments ($categoryid, $pluginname)");
    }
}

function remove_category_module_values($categoryid, $pluginname) {
    remove_category_plugin_values($categoryid, $pluginname, 'modules');
}

function remove_category_block_values($categoryid, $pluginname) {
    remove_category_plugin_values($categoryid, $pluginname, 'block');
}

/**
 * Removes the settings for a module or block at the category level
 * @param $categoryid
 * @param $pluginname
 * @param $plugintype string the type of plugin to set (currently valid values are: modules, blocks)
 */
function remove_category_plugin_values($categoryid, $pluginname, $plugintype) {
    global $DB;

    // Todo need to check rest of tree for locks above it.

    $validplugins = array('modules', 'block'); // Valid types.
    if (!in_array($plugintype, $validplugins)) {
        debugging('Invalid plugintype passed to set_category_plugin_values in local/contextadmin/locallib.php', DEBUG_DEVELOPER);
        return;
    }

    if (CONTEXTADMINDEBUG) {
        echo "remove_category_plugin_values($categoryid,$pluginname,$plugintype):\n";
    }

    if (!empty($pluginname) && !empty($categoryid)) {
        $DB->delete_records('cat_' . $plugintype, array('category_id' => $categoryid, 'name' => $pluginname));

    } else {
        throw new Exception("set_category_plugin_values missing arguments ($categoryid, $pluginname)");
    }
}


// CTL-307 Moodle Backup version not set
// This function provides a list to check if the configuration key should be
// excluded from category administration. Doing this restores normal moodle
// set_config functionality.

/**
 * Checks if the config key should be excluded from category administration.
 * @param string $name the key to be checked
 * @return bool True if the key is to be excluded category administration
 */
function is_cat_config_excluded($name) {
    $excluded = array(
        'backup_version',
        'backup_release'
    );
    return in_array($name, $excluded);
}


/**
 * Checks if there exists a record above that has the locked flag set to true
 * @param $categoryid
 * @param $pluginname
 * @return bool true if locked, false if not locked
 *
 */
function is_module_locked($categoryid, $pluginname) {
    return is_plugin_locked($categoryid, $pluginname, 'modules');
}

/**
 * Checks if there exists a record above that has the locked flag set to true
 * @param $categoryid
 * @param $pluginname
 * @return bool true if locked, false if not locked
 *
 */
function is_block_locked($categoryid, $pluginname) {
    return is_plugin_locked($categoryid, $pluginname, 'block');
}

/**
 * Checks if there exists a record above that has the locked flag set to true
 * @param $categoryid
 * @param $pluginname
 * @param $plugintype
 * @return bool true if locked, false if not locked
 *
 */
function is_plugin_locked($categoryid, $pluginname, $plugintype) {
    global $DB;
    if (CONTEXTADMINDEBUG) {
        echo "is_plugin_locked($categoryid,$pluginname, $plugintype):\n";
    }

    if (empty($pluginname)) {
        return null;
    }

    $validplugins = array('modules', 'block'); // Valid types.
    if (!in_array($plugintype, $validplugins)) {
        debugging('Invalid plugintype passed to is_plugin_locked in local/contextadmin/locallib.php', DEBUG_DEVELOPER);
        return null;
    }

    $path        = get_category_path($categoryid);
    $pathstring = ltrim($path, '/');
    $apath      = explode('/', $pathstring);
    $arevpath  = array_reverse($apath);

    // Remove the first element. We can't lock ourselves.
    array_shift($arevpath);

    /*
    * go through the categories starting from nearest to top
    * 1. extract records for current category
    * 2. process records and collect up changes first in collection overrides later ones
    * 3. apply collected settings over the site_modules and return
    */
    if (!empty($pluginname)) {
        // Use site if no context level exists.

        foreach ($arevpath as $catid) {

            if ($DB->get_field("cat_" . $plugintype, 'locked',
                               array('name' => $pluginname, 'category_id' => $catid, 'locked' => 1))
            ) {
                return true;
            }
        }
        return false;
    }
}

/**
 * Checks if there exists a record above that has the override flag set to true
 * @param $categoryid
 * @param $pluginname
 * @return bool true if locked, false if not locked
 *
 */
function is_module_overridden($categoryid, $pluginname) {
    return is_plugin_overridden($categoryid, $pluginname, 'modules');
}

/**
 * Checks if there exists a record above that has the override flag set to true
 * @param $categoryid
 * @param $pluginname
 * @return bool true if locked, false if not locked
 *
 */
function is_block_overridden($categoryid, $pluginname) {
    return is_plugin_overridden($categoryid, $pluginname, 'block');
}

/**
 * Checks if there exists a record above that has the override flag set to true
 * @param $categoryid
 * @param $pluginname
 * @param $plugintype
 * @return bool true if locked, false if not locked
 *
 */
function is_plugin_overridden($categoryid, $pluginname, $plugintype) {
    global $DB;
    if (CONTEXTADMINDEBUG) {
        echo "is_plugin_locked($categoryid,$pluginname, $plugintype):\n";
    }

    if (empty($pluginname)) {
        return null;
    }

    $validplugins = array('modules', 'block'); // Valid types.
    if (!in_array($plugintype, $validplugins)) {
        debugging('Invalid plugintype passed to is_plugin_overridden in local/contextadmin/locallib.php', DEBUG_DEVELOPER);
        return null;
    }

    $path        = get_category_path($categoryid);
    $pathstring = ltrim($path, '/');
    $apath      = explode('/', $pathstring);
    $arevpath  = array_reverse($apath);

    // Remove the first element. We can't override ourselves.
    array_shift($arevpath);

    /*
    * go through the categories starting from nearest to top
    * 1. extract records for current category
    * 2. process records and collect up changes first in collection overrides later ones
    * 3. apply collected settings over the site_modules and return
    */
    if (!empty($pluginname)) {

        foreach ($arevpath as $catid) {

            if ($DB->get_field("cat_" . $plugintype, 'override',
                               array('name' => $pluginname, 'category_id' => $catid, 'override' => 1))
            ) {
                return true;
            }
        }
        return false;
    }
}

function category_module_exists($categoryid, $pluginname) {
    return category_plugin_exists($categoryid, $pluginname, 'modules');
}

function category_block_exists($categoryid, $pluginname) {
    return category_plugin_exists($categoryid, $pluginname, 'block');
}

/**
 * Tests for existence of a record for the module at the category level.
 * @param $categoryid
 * @param $pluginname
 * @param $plugintype
 * @return bool
 */
function category_plugin_exists($categoryid, $pluginname, $plugintype) {
    global $DB;
    if (CONTEXTADMINDEBUG) {
        echo "category_plugin_exists($categoryid,$pluginname, $plugintype):\n";
    }

    if (empty($pluginname)) {
        return false;
    }

    $validplugins = array('modules', 'block'); // Valid types.
    if (!in_array($plugintype, $validplugins)) {
        debugging('Invalid plugintype passed to category_plugin_exists in local/contextadmin/locallib.php', DEBUG_DEVELOPER);
        return false;
    }

    // Use site if no context level exists.
    return $DB->record_exists("cat_" . $plugintype, array('name' => $pluginname, 'category_id' => $categoryid));
}

function print_cat_course_search($value = "", $return = false, $format = "plain") {
    global $CFG;
    static $count = 0;

    $perpagevalues = array(10, 20, 30, 50, 100);

    $count++;

    $id = 'coursesearch';

    if ($count > 1) {
        $id .= $count;
    }

    $strsearchcourses = get_string("searchcourses");

    if ($format == 'plain') {
        $output = '<form id="' . $id . '" action="' . $CFG->wwwroot . '/local/contextadmin/cat_search.php" method="get">';
        $output .= '<fieldset class="coursesearchbox invisiblefieldset">';
        $output .= '<label for="coursesearchbox">' . $strsearchcourses . ': </label>';
        $output .= '<input type="text" id="coursesearchbox" size="30" name="search" value="' . s($value) . '" />';
        $output .= '<br><label for="perpagebox">Results per page:</label>';
        $output .= '<select name="perpage">';
        foreach ($perpagevalues as $value) {
            $output .= '<option value="' . $value . '">' . $value . '</option>';
        }
        $output .= '</select>';
        $output .= '<input type="submit" value="' . get_string('go') . '" />';
        $output .= '</fieldset></form>';
    } else if ($format == 'short') {
        $output = '<form id="' . $id . '" action="' . $CFG->wwwroot . '/local/contextadmin/cat_search.php" method="get">';
        $output .= '<fieldset class="coursesearchbox invisiblefieldset">';
        $output .= '<label for="shortsearchbox">' . $strsearchcourses . ': </label>';
        $output .= '<input type="text" id="shortsearchbox" size="12" name="search" alt="' . s($strsearchcourses) . '" value="' .
            s($value) . '" />';
        $output .= '<input type="submit" value="' . get_string('go') . '" />';
        $output .= '</fieldset></form>';
    } else if ($format == 'navbar') {
        $output = '<form id="coursesearchnavbar" action="' . $CFG->wwwroot . '/local/contextadmin/cat_search.php" method="get">';
        $output .= '<fieldset class="coursesearchbox invisiblefieldset">';
        $output .= '<label for="navsearchbox">' . $strsearchcourses . ': </label>';
        $output .=
            '<input type="text" id="navsearchbox" size="20" name="search" alt="' . s($strsearchcourses) . '" value="' . s($value) .
                '" />';
        $output .= '<input type="submit" value="' . get_string('go') . '" />';
        $output .= '</fieldset></form>';
    }

    if ($return) {
        return $output;
    }
    echo $output;
}

/**
 * @param $outputobject
 * @param $id
 * @param $target
 * @param $linktitle
 * @param $icon
 * @param $ainputs
 * @return string
 */
function create_form($outputobject, $id, $target, $linktitle, $icon, $ainputs) {
    $form = "<form id=\"$id\" method=\"post\" action=\"$target\">";
    foreach ($ainputs as $name => $value) {
        $form .= "<input type='hidden' name='$name' value='$value'/>";
    }

    $form .= "<a href=\"#\" onclick='document.getElementById(\"$id\").submit();' title=\"$linktitle\">" .
        "<img src=\"" . $outputobject->pix_url("i/$icon") . "\" class=\"icon\" alt=\"$linktitle\" /></a>";

    $form .= '</form>';
    return $form;
}

function create_image_tag($image, $alt, $class = '') {
    return "<img src=\"" . $image . "\" class=\"$class\" alt=\"$alt\" />";
}
