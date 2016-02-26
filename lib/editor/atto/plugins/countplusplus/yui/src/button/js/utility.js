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
 * utility component for 'atto_countplusplus'. Here belongs functions
 * that is not big/important enough to deserve its own category.
 *
 * @package    atto_countplusplus
 * @copyright  2015 Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

var UTILITY = {};

/**
 * @param element {Javascript Native Node}
 * @return returns the display copmuted display property
 *                 (after applying css) of the given element.
 */
UTILITY.getDisplayType = function (element) {
    var cStyle = element.currentStyle || window.getComputedStyle(element, "");
    return cStyle.display;
};

/**
 * @param nodes {Array {Javascript node}} Array of javascript nodes.
 * @returns {Array {Boolean}} which indicates if the i'th node is a block.
 */
UTILITY.buildIsBlockArray = function (nodes) {
    var isBlockArr = [];
    nodes.forEach(function (node) {
        isBlockArr[isBlockArr.length] =
            UTILITY.getDisplayType(node).toLowerCase() === 'block' ||
            node.tagName.toLowerCase() === 'br';
    });

    return isBlockArr;
};