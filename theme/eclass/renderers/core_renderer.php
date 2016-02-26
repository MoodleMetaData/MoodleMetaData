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
require_once("outputcomponents.php");

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_eclass
 * @copyright  2014
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class theme_eclass_core_renderer extends theme_bootstrapbase_core_renderer {


    public function eclass_header($columns) {
        global $CFG, $SITE;
        $output = html_writer::start_tag('header', array("role" => "banner",
            "class" => "navbar navbar-fixed-top moodle-has-zindex"));
        $output .= html_writer::start_tag('nav', array("role" => "navigation", "class" => "navbar-inner"));
        $output .= html_writer::start_div("container-fluid");
        $output .= html_writer::tag('img', '',
            array("src" => $this->pix_url('ua-logo', 'theme'), "class" => "uofa-logo", "height" => "40px"));
        $output .= html_writer::div(html_writer::link($CFG->wwwroot, $SITE->shortname, array("class" => "brand")), 'nav-brand');
        $output .= html_writer::start_div("pull-right");
        $output .= $this->custom_menu();
        $headingmenu = $this->page_heading_menu();
        $output .= html_writer::alist(array($headingmenu), array("class" => "nav pull-right"));
        $output .= html_writer::div(html_writer::span('', "glyphicons collapse_top"), "scroll-top pull-right",
            array("title" => "Scroll to top"));
        $output .= $this->login_info();
        $output .= html_writer::end_div();
        $output .= html_writer::end_div();
        $output .= html_writer::end_tag('nav');
        // Mobile stuff.
        $toggleleft   = '<li><label class="toggle toggle-navigation"   for="toggle-left" onclick>'
            .'<div class="mobile-nav-icon glyphicons list"></div><span>Navigation</span></label></li>';

        $togglecenter = '<li><label class="toggle toggle-content" for="toggle-center" onclick>'
            .'<div class="mobile-nav-icon glyphicons book"></div><span>Content</span></label></li>';
        $toggleright = '';
        if ($this->page->blocks->region_has_content('side-post', $this)) {
            $toggleright  = '<li><label class="toggle toggle-blocks"  for="toggle-right" onclick>'
                .'<div class="mobile-nav-icon glyphicons show_big_thumbnails"></div><span>Blocks</span></label></li>';
        }

        $toggleprofile  = '<li><label class="toggle toggle-profile"  for="toggle-profile" onclick>'
            .'<div class="mobile-nav-icon glyphicons user"></div><span>Profile</span></label></li>';

        switch ($columns) {
            default:
            case 1:
                $togglelist = $togglecenter.$toggleprofile;
                break;
            case 2:
                $togglelist = $toggleleft.$togglecenter.$toggleprofile;
                break;
            case 3:
                $togglelist = $toggleleft.$togglecenter.$toggleright.$toggleprofile;
                break;
        }
        $togglelist .= html_writer::div('', 'active-indicator');
        $output .= html_writer::start_tag('nav', array("class" => "mobile-nav"));
        $output .= html_writer::tag('ul', $togglelist, array("class" => "view-selector"));
        $output .= html_writer::end_tag('nav');
        $output .= html_writer::end_tag('header');
        return $output;
    }


    public function eclass_profile($class = '') {
        $output  = html_writer::start_tag('aside', array("id" => "region-profile", "class" => $class));
        $output .= $this->login_info();
        $output .= $this->render_userprofile($class);
        $output .= html_writer::end_tag('aside');
        return $output;
    }

    /**
     * Returns course-specific information to be output immediately above content on any course page
     * (for the current course)
     *
     * @param bool $onlyifnotcalledbefore output content only if it has not been output before
     * @return string
     */
    public function course_content_header($onlyifnotcalledbefore = false) {
        global $CFG;
        if ($this->page->course->id == SITEID) {
            // Return immediately and do not include /course/lib.php if not necessary.
            return '';
        }
        static $functioncalled = false;
        if ($functioncalled && $onlyifnotcalledbefore) {
            // We have already output the content header.
            return '';
        }
        require_once($CFG->dirroot.'/course/lib.php');
        $functioncalled = true;

        $contentimages = '';

        // TODO load in a course specific image here...

        $courseurl = html_writer::link(new moodle_url('/course/view.php?id='.$this->page->course->id), $this->page->heading);
        $output = html_writer::tag('h1', $courseurl, array('class' => 'course-header-link'));

        $courseformat = course_get_format($this->page->course);
        if (($obj = $courseformat->course_content_header()) !== null) {
            $output .= html_writer::div($courseformat->get_renderer($this->page)->render($obj), 'course-content-header');
        }
        return $output;
    }
    /**
     * Return the standard string that says whether you are logged in (and switched
     * roles/logged in as another user).
     * @param bool $withlinks if false, then don't include any links in the HTML produced.
     * If not set, the default is the nologinlinks option from the theme config.php file,
     * and if that is not set, then links are included.
     * @return string HTML fragment.
     */
    public function login_info($withlinks = null, $asmenu = false) {
        global $USER, $CFG, $DB, $SESSION;

        if (during_initial_install()) {
            return '';
        }

        if (is_null($withlinks)) {
            $withlinks = empty($this->page->layout_options['nologinlinks']);
        }

        $loginpage = ((string)$this->page->url === get_login_url());
        $course = $this->page->course;
        if (\core\session\manager::is_loggedinas()) {
            $realuser = \core\session\manager::get_realuser();
            $fullname = fullname($realuser, true);
            if ($withlinks) {
                $loginastitle = get_string('loginas');
                $realuserinfo = " <small>[</small><a href=\"$CFG->wwwroot/course/loginas.php?id=$course->id&amp;sesskey=".
                    sesskey()."\"";
                $realuserinfo .= "title =\"".$loginastitle."\">$fullname</a> <small>]</small> ";
            } else {
                $realuserinfo = " <small>[</small> $fullname <small>]</small> ";
            }
        } else {
            $realuserinfo = '';
        }

        $loginurl = get_login_url();
        $loggedinas = '';

        if (empty($course->id)) {
            // The $course->id is not defined during installation.
            return '';
        } else if (isloggedin()) {
            $context = context_course::instance($course->id);
            $fullname = fullname($USER, true);
            $linktitle = get_string('viewprofile');
            $userpicture = '';
            if (!empty($USER->id)) {
                $userpicture = $this->user_picture($USER, array('size' => 35, 'link' => false, 'class' => 'nav_userpicture'));
            }
            $username = "<a href=\"$CFG->wwwroot/user/profile.php?id=$USER->id\" ".
                "title=\"$linktitle\" class='userloginprofile'>$userpicture$fullname</a>";
            if (is_mnet_remote_user($USER) and $idprovider = $DB->get_record('mnet_host', array('id' => $USER->mnethostid))) {
                $username .= " from <a href=\"{$idprovider->wwwroot}\">{$idprovider->name}</a>";
            }
            $loggedinas = $username;
            if (isguestuser()) {
                $loggedinas = $realuserinfo.get_string('loggedinasguest');
                if (!$loginpage) {
                    $loggedinas .= " <small>(</small> <a href=\"$loginurl\">".get_string('login').'</a> <small>)</small>';
                }
            } else if (is_role_switched($course->id)) { // Has switched roles.
                $rolename = '';
                if ($role = $DB->get_record('role', array('id' => $USER->access['rsw'][$context->path]))) {
                    $rolename = '<span class="role-name">: '.role_get_name($role, $context).'</span>';
                }
                $loggedinas .= $rolename;
                $url = new moodle_url('/course/switchrole.php',
                    array('id' => $course->id, 'sesskey' => sesskey(), 'switchrole' => 0,
                        'returnurl' => $this->page->url->out_as_local_url(false)));
                $loggedinas .= html_writer::tag('a', get_string('switchrolereturn'), array('href' => $url));
            } else {
                $loggedinas .= " <small>(</small> <a href=\"$CFG->wwwroot/login/logout.php?sesskey=".sesskey()."\">"
                    .get_string('logout').'</a> <small>)</small>';
            }
        } else {
            if (!$loginpage) {
                $loggedinas = " <small>(</small> <a href=\"$loginurl\">".get_string('login').'</a> <small>)</small>';
            }
        }

        $loggedinas = '<div class="logininfo">'.$loggedinas.'</div>';
        if (isset($SESSION->justloggedin)) {
            unset($SESSION->justloggedin);
            if (!empty($CFG->displayloginfailures)) {
                if (!isguestuser()) {
                    if ($count = count_login_failures($CFG->displayloginfailures, $USER->username, $USER->lastlogin)) {
                        $loggedinas .= '&nbsp;<div class="loginfailures">';
                        if (empty($count->accounts)) {
                            $loggedinas .= get_string('failedloginattempts', '', $count);
                        } else {
                            $loggedinas .= get_string('failedloginattemptsall', '', $count);
                        }
                        if (file_exists("$CFG->dirroot/report/log/index.php") and
                            has_capability('report/log:view', context_system::instance())) {
                            $loggedinas .= ' <a href="'.$CFG->wwwroot.'/report/log/index.php'.
                                '?chooselog=1&amp;id=1&amp;modid=site_errors">'.get_string('logs').'</a>';
                        }
                        $loggedinas .= '</div>';
                    }
                }
            }
        }

        return $loggedinas;
    }

    public function render_userprofile($class = '', $submenu = array()) {
        if (!isloggedin()) {
            return '';
        }
        // Find profile links and render theme.
        $this->page->navigation->initialise();
        $this->page->settingsnav->initialise();

        $navigation = clone($this->page->navigation);
        $settings = clone($this->page->settingsnav);
        $navprofile = array();
        $setprofile = array();
        // Navigation links.
        foreach (array($navigation) as $item) {
            if (!$item->display && !$item->contains_active_node() ||
                $item->type != navigation_node::TYPE_SYSTEM || empty($item->action)) {
                continue;
            }
            $my = $item->get('myprofile');
            if (!empty($my) && $my->children) {
                $collection = $my->find_all_of_type(navigation_node::TYPE_CUSTOM);
                foreach ($collection as $node) {
                    if (!empty($node->children)) {
                        $children = array();
                        $childrennodes = $node->find_all_of_type(navigation_node::TYPE_CUSTOM);
                        foreach ($childrennodes as $child) {
                            $children[] = html_writer::link($child->action, $child->text);
                        }
                        if (empty($node->action)) {
                            $link = html_writer::tag('span', $node->text, $submenu);
                        } else {
                            $link = html_writer::link($node->action, $node->text, $submenu);
                        }
                        $navprofile[] = $link.html_writer::alist($children, array('class' => 'profile-child'));
                    } else {
                        $navprofile[] = html_writer::link($node->action, $node->text);;
                    }
                }
                break;
            }
        }
        if (isloggedin() && !isguestuser()) {
            // Settings links...
            foreach (array($settings->get('usercurrentsettings')) as $item) {
                $collection = $item->find_all_of_type(navigation_node::TYPE_SETTING);
                foreach ($collection as $node) {

                    if (!empty($node->children)) {
                        $children = array();
                        $childrennodes = $node->find_all_of_type(navigation_node::TYPE_SETTING);
                        foreach ($childrennodes as $child) {
                            $children[] = html_writer::link($child->action, $child->text);
                        }
                        if (empty($node->action)) {
                            $link = html_writer::tag('span', $node->text, $submenu);
                        } else {
                            $link = html_writer::link($node->action, $node->text, $submenu);
                        }
                        $setprofile[] = $link.html_writer::alist($children, array('class' => 'profile-child'));
                    } else {
                        $setprofile[] = html_writer::link($node->action, $node->text);;
                    }
                }
                break;
            }
        }
        $nav = html_writer::alist($navprofile, array('class' => 'profile-nav'));
        $set = html_writer::alist($setprofile, array('class' => 'profile-set'));
        return html_writer::alist(array($nav, $set), array('class' => $class));
    }

    /**
     * Get the HTML for blocks in the given region.
     *
     * @since 2.5.1 2.6
     * @param string $region The region to get HTML for.
     * @return string HTML.
     */
    public function blocks($region, $classes = array(), $tag = 'aside', $precontent = '') {
        $displayregion = $this->page->apply_theme_region_manipulations($region);
        $classes = (array)$classes;
        $classes[] = 'block-region';
        $attributes = array(
            'id' => 'block-region-'.preg_replace('#[^a-zA-Z0-9_\-]+#', '-', $displayregion),
            'class' => join(' ', $classes),
            'data-blockregion' => $displayregion,
            'data-droptarget' => '1'
        );
        if ($this->page->blocks->region_has_content($displayregion, $this)) {
            $content = $this->blocks_for_region($displayregion);
        } else {
            $content = '';
        }
        if (!empty($precontent)) {
            $content = $precontent.$content;
        }
        return html_writer::tag($tag, $content, $attributes);
    }

    /*
    * Overriding the custom_menu function ensures the custom menu is
    * always shown, even if no menu items are configured in the global
    * theme settings page.
    */
    public function custom_menu($custommenuitems = '') {
        global $CFG;

        if (!empty($CFG->custommenuitems)) {
            $custommenuitems .= $CFG->custommenuitems;
        }
        $custommenu = new custom_icon_menu($custommenuitems, current_language());
        return $this->render_custom_icon_menu($custommenu);
    }

    /*
     * This renders the bootstrap top menu.
     *
     * This renderer is needed to enable the Bootstrap style navigation.
     */
    protected function render_custom_icon_menu(custom_icon_menu $menu) {
        global $CFG;
        $addlangmenu = false;

        if ($menu) {
            $addlangmenu = true;
        }
        $langs = get_string_manager()->get_list_of_translations();
        if (count($langs) < 2
            or empty($CFG->langmenu)
            or ($this->page->course != SITEID and !empty($this->page->course->lang))) {
            $addlangmenu = false;
        }

        if (!$menu->has_children() && $addlangmenu === false) {
            return '';
        }

        if ($addlangmenu) {
            $strlang = get_string('language');
            $currentlang = current_language();
            if (isset($langs[$currentlang])) {
                $currentlang = $langs[$currentlang];
            } else {
                $currentlang = $strlang;
            }
            $this->language = $menu->add($currentlang, new moodle_url('#'), $strlang, 10000);
            foreach ($langs as $langtype => $langname) {
                $this->language->add($langname, new moodle_url($this->page->url, array('lang' => $langtype)), $langname);
            }
        }

        $content = '<ul class="nav">';
        foreach ($menu->get_children() as $item) {
            if ($item instanceof custom_icon_menu_item) {
                $content .= $this->render_custom_icon_menu_item($item, 1);
            }
        }

        return $content.'</ul>';
    }

    /*
     * This code renders the custom menu items for the
     * bootstrap dropdown menu.
     */
    protected function render_custom_icon_menu_item(custom_icon_menu_item $menunode, $level = 0 ) {
        global $PAGE, $CFG;
        static $submenucount = 0;

        if ($menunode->has_children()) {

            $active = '';
            // The node doesn't have children so produce a final menuitem.
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
                if ($PAGE->url->get_path(false) == $url->get_path(false)) {
                    $active = 'active';
                }
            } else {
                $url = '#';
            }
            if ($level == 1) {
                $class = 'nav-menu dropdown '.$active;
            } else {
                $class = 'nav-menu dropdown-submenu '.$active;
            }

            if ($menunode === $this->language) {
                $class .= ' langmenu';
            }
            $content = html_writer::start_tag('li', array('class' => $class));
            // If the child has menus render it as a sub menu.
            $submenucount++;
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#cm_submenu_'.$submenucount;
            }

            $target = '_parent';
            if (strpos(trim($url), $CFG->wwwroot) === false) {
                $target = '_blank';
            }

            $content .= html_writer::start_tag('a',
                array('href' => $url, 'class' => 'dropdown-toggle',
                    'data-toggle' => 'dropdown', 'title' => $menunode->get_title(), 'target' => $target));
            if ($menunode->get_icon()) {
                $icon = "glyphicons ".$menunode->get_icon();
            } else {
                $icon = "";
            }
            $content .= "<span><div class='nav-icon $icon'></div><span>".$menunode->get_text()."</span></span>";
            if ($level == 1) {
                $content .= '<b class="caret"></b>';
            }
            $content .= '</a>';
            $content .= '<ul class="dropdown-menu">';
            foreach ($menunode->get_children() as $menunode) {
                $content .= $this->render_custom_icon_menu_item($menunode, 0);
            }
            $content .= '</ul>';
        } else {
            $active = '';
            // The node doesn't have children so produce a final menuitem.
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
                if ($PAGE->url->get_path(false) == $url->get_path(false)) {
                    $active = 'active';
                }
            } else {
                $url = '#';
            }
            if ($menunode->get_icon()) {
                $icon = "glyphicons ".$menunode->get_icon();
            } else {
                $icon = "";
            }

            $target = '_parent';
            if (strpos(trim($url), $CFG->wwwroot) === false) {
                $target = '_blank';
            }
            $content = "<li class='nav-menu $active'>";
            $content .= html_writer::link($url,
                "<span><div class='nav-icon $icon'></div><span>".$menunode->get_text()."</span></span>",
                array('title' => $menunode->get_title(), 'target' => $target));
        }
        return $content;
    }
}
