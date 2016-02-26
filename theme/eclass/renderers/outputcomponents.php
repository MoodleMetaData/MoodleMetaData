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

/**
 * @package eclass-theme-bootstrap-uofa
 * @author joshstagg
 * @copyright Josh Stagg
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/lib/outputcomponents.php');

/**
 * Custom menu class
 *
 * This class is used to operate a custom menu that can be rendered for the page.
 * The custom menu is built using $CFG->custommenuitems and is a structured collection
 * of custom_menu_item nodes that can be rendered by the core renderer.
 *
 * To configure the custom menu:
 *     Settings: Administration > Appearance > Themes > Theme settings
 *
 * @copyright 2010 Sam Hemelryk
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.0
 * @package core
 * @category output
 */
// Copied from core and modified to support custom icons.
class custom_icon_menu extends custom_menu {
    /**
     * @var string The language we should render for, null disables multilang support.
     */
    protected $currentlanguage = null;

    /**
     * Creates the custom menu
     *
     * @param string $definition the menu items definition in syntax required by {@link convert_text_to_menu_nodes()}
     * @param string $currentlanguage the current language code, null disables multilang support
     */
    public function __construct($definition = '', $currentlanguage = null) {
        $this->currentlanguage = $currentlanguage;
        parent::__construct('root'); // Create virtual root element of the menu.
        if (!empty($definition)) {
            $this->override_children(self::convert_text_to_menu_nodes($definition, $currentlanguage));
        }
    }

    /**
     * Overrides the children of this custom menu. Useful when getting children
     * from $CFG->custommenuitems
     *
     * @param array $children
     */
    public function override_children(array $children) {
        $this->children = array();
        foreach ($children as $child) {
            if ($child instanceof custom_icon_menu_item) {
                $this->children[] = $child;
            }
        }
    }

    /**
     * Converts a string into a structured array of custom_menu_items which can
     * then be added to a custom menu.
     *
     * Structure:
     *     text|url|title|langs|icon
     * The number of hyphens at the start determines the depth of the item. The
     * languages are optional, comma separated list of languages the line is for.
     *
     * Example structure:
     *     First level first item|http://www.moodle.com/
     *     -Second level first item|http://www.moodle.com/partners/
     *     -Second level second item|http://www.moodle.com/hq/
     *     --Third level first item|http://www.moodle.com/jobs/
     *     -Second level third item|http://www.moodle.com/development/
     *     First level second item|http://www.moodle.com/feedback/
     *     First level third item
     *     English only|http://moodle.com|English only item|en
     *     German only|http://moodle.de|Deutsch|de,de_du,de_kids
     *
     *
     * @static
     * @param string $text the menu items definition
     * @param string $language the language code, null disables multilang support
     * @return array
     */
    public static function convert_text_to_menu_nodes($text, $language = null) {
        $lines = explode("\n", $text);
        $children = array();
        $lastchild = null;
        $lastdepth = null;
        $lastsort = 0;
        $icon = null;
        foreach ($lines as $line) {
            $line = trim($line);
            $bits = explode('|', $line, 5);    // Expects name|url|title|langs|icon.
            if (!array_key_exists(0, $bits) or empty($bits[0])) {
                // Every item must have a name to be valid.
                continue;
            } else {
                $bits[0] = ltrim($bits[0], '-');
            }
            if (!array_key_exists(1, $bits) or empty($bits[1])) {
                // Set the url to null.
                $bits[1] = null;
            } else {
                // Make sure the url is a moodle url.
                $bits[1] = new moodle_url(trim($bits[1]));
            }
            if (!array_key_exists(2, $bits) or empty($bits[2])) {
                // Set the title to null seeing as there isn't one.
                $bits[2] = $bits[0];
            }
            if (!array_key_exists(3, $bits) or empty($bits[3])) {
                // The item is valid for all languages.
                $itemlangs = null;
            } else {
                $itemlangs = array_map('trim', explode(',', $bits[3]));
            }
            if (!empty($language) and !empty($itemlangs)) {
                // Check that the item is intended for the current language.
                if (!in_array($language, $itemlangs)) {
                    continue;
                }
            }
            if (array_key_exists(4, $bits) and !empty($bits[1])) {
                $icon = $bits[4];
            }

            // Set an incremental sort order to keep it simple.
            $lastsort++;
            if (preg_match('/^(\-*)/', $line, $match) && $lastchild != null && $lastdepth !== null) {
                $depth = strlen($match[1]);
                if ($depth < $lastdepth) {
                    $difference = $lastdepth - $depth;
                    if ($lastdepth > 1 && $lastdepth != $difference) {
                        $tempchild = $lastchild->get_parent();
                        for ($i = 0; $i < $difference; $i++) {
                            $tempchild = $tempchild->get_parent();
                        }
                        $lastchild = $tempchild->add($bits[0], $bits[1], $bits[2], $lastsort, $icon);
                    } else {
                        $depth = 0;
                        $lastchild = new custom_icon_menu_item($bits[0], $bits[1], $bits[2], $lastsort, $icon);
                        $children[] = $lastchild;
                    }
                } else if ($depth > $lastdepth) {
                    $depth = $lastdepth + 1;
                    $lastchild = $lastchild->add($bits[0], $bits[1], $bits[2], $lastsort, $icon);
                } else {
                    if ($depth == 0) {
                        $lastchild = new custom_icon_menu_item($bits[0], $bits[1], $bits[2], $lastsort, $icon);
                        $children[] = $lastchild;
                    } else {
                        $lastchild = $lastchild->get_parent()->add($bits[0], $bits[1], $bits[2], $lastsort, $icon);
                    }
                }
            } else {
                $depth = 0;
                $lastchild = new custom_icon_menu_item($bits[0], $bits[1], $bits[2], $lastsort, $icon);
                $children[] = $lastchild;
            }
            $lastdepth = $depth;
        }
        return $children;
    }
}

/**
 * Custom menu item
 *
 * This class is used to represent one item within a custom menu that may or may
 * not have children.
 *
 * @copyright 2010 Sam Hemelryk
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.0
 * @package core
 * @category output
 */
class custom_icon_menu_item extends custom_menu_item {
    /**
     * @var string The icon to show for the item
     */
    protected $icon;
    /**
     * Constructs the new custom menu item
     *
     * @param string $text
     * @param string $icon
     * @param moodle_url $url A moodle url to apply as the link for this item [Optional]
     * @param string $title A title to apply to this item [Optional]
     * @param int $sort A sort or to use if we need to sort differently [Optional]
     * @param custom_menu_item $parent A reference to the parent custom_menu_item this child
     *        belongs to, only if the child has a parent. [Optional]
     */
    public function __construct($text, moodle_url $url=null, $title=null,
                                $sort = null, $icon = null, custom_icon_menu_item $parent = null) {
        $this->text = $text;
        $this->icon = $icon;
        $this->url = $url;
        $this->title = $title;
        $this->sort = (int)$sort;
        $this->parent = $parent;
    }

    /**
     * Adds a custom menu item as a child of this node given its properties.
     *
     * @param string $text
     * @param moodle_url $url
     * @param string $title
     * @param int $sort
     * @return custom_menu_item
     */
    public function add($text, moodle_url $url = null, $title = null, $sort = null, $icon = null) {
        $key = count($this->children);
        if (empty($sort)) {
            $sort = $this->lastsort + 1;
        }
        $this->children[$key] = new custom_icon_menu_item($text, $url, $title, $sort, $this, $icon);
        $this->lastsort = (int)$sort;
        return $this->children[$key];
    }

    /**
     * Returns the icon for this item
     * @return string
     */
    public function get_icon() {
        return $this->icon;
    }
    /**
     * Sets the icon for the node
     * @param string $icon
     */
    public function set_icon($icon) {
        $this->icon = (string)$icon;
    }
}

