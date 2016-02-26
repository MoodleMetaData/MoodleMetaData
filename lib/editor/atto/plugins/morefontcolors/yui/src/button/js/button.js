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
 * Atto text editor integration version file.
 *
 * @package    atto_morefontcolors
 * @copyright  2015 University of Alberta
 * @author     Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module moodle-atto_morefontcolors-button
 */

/**
 * Atto text editor morefontcolors plugin.
 *
 * @namespace M.atto_morefontcolors
 * @class button
 * @extends M.editor_atto.EditorPlugin
 */

var MIN_SQUARE_SIDE_LENGTH = 1;
var MAX_SQUARE_SIDE_LENGTH = 20;

var DISABLED = 'disabled';

Y.namespace('M.atto_morefontcolors').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {

    initializer: function() {
        this.thisIsZeroIfFirstTime = 0;
        var items = [];
        var colors = this.get('colors');
        thisAnchor2 = this;
        Y.Array.each(colors, function(color) {
            items.push({
                color: color,
                callbackArgs: color,
                thisAnchor: thisAnchor2,
                callback: thisAnchor2._changeStyle
            });
        });

        var desiredRowCount = this._getDesiredRowCount();
        this.addToolbarMenu({
            icon: 'e/text_color',
            overlayWidth: '4',
            menuColor: '#333333',
            globalItemConfig: {
                callback: this._changeStyle
            },
            rowSize: desiredRowCount,
            items: items
        });
    },

    /**
     * Display a toolbar menu.
     *
     * @method _showToolbarMenu
     * @param {EventFacade} e
     * @param {object} config The configuration for the whole toolbar.
     * @param {Number} [config.overlayWidth=14] The width of the menu
     * @override
     * @private
     */
    _showToolbarMenu: function(e, config) {
        // Prevent default primarily to prevent arrow press changes.
        e.preventDefault();

        if (!this.isEnabled()) {
            // Exit early if the plugin is disabled.
            return;
        }

        if (e.currentTarget.ancestor('button', true).hasAttribute(DISABLED)) {
            // Exit early if the clicked button was disabled.
            return;
        }

        var menuDialogue;

        if (!this.menus[config.buttonClass]) {
            if (!config.overlayWidth) {
                config.overlayWidth = '14';
            }

            if (!config.innerOverlayWidth) {
                config.innerOverlayWidth = parseInt(config.overlayWidth, 10) - 2 + 'em';
            }
            config.overlayWidth = parseInt(config.overlayWidth, 10) + 'em';

            this.menus[config.buttonClass] = new Y.M.atto_morefontcolors.MenuExt(config);
            //this.menus[config.buttonClass] = new Y.M.editor_atto.Menu(config);

            this.menus[config.buttonClass].get('contentBox').delegate('click',
                this._chooseMenuItem, '.atto_menuentry a', this, config);
        }

        // Clear the focusAfterHide for any other menus which may be open.
        Y.Array.each(this.get('host').openMenus, function(menu) {
            menu.set('focusAfterHide', null);
        });

        // Ensure that we focus on this button next time.
        var creatorButton = this.buttons[config.buttonName];
        creatorButton.focus();
        this.get('host')._setTabFocus(creatorButton);

        // Get a reference to the menu dialogue.
        menuDialogue = this.menus[config.buttonClass];

        // Focus on the button by default after hiding this menu.
        menuDialogue.set('focusAfterHide', creatorButton);

        // Display the menu.
        var contentBox = menuDialogue.get('contentBox');
        var contentBoxStyleCheck = contentBox.getStyle("display").toLowerCase();
        if (contentBoxStyleCheck === "none" || contentBoxStyleCheck === null || typeof contentBoxStyleCheck === "undefined") {
            contentBox.setStyle("display", "block");
            menuDialogue.show();
            this.thisIsZeroIfFirstTime = 1;
        } else {
            if (this.thisIsZeroIfFirstTime === 0) {
                contentBox.setStyle("display", "block");
                menuDialogue.show();
                this.thisIsZeroIfFirstTime = 1;
            } else {
                contentBox.setStyle("display", "none");
                menuDialogue.hide();
                this.thisIsZeroIfFirstTime = 1;
            }
        }
        //menuDialogue.setStyle("display", "block");

        // Position it next to the button which opened it.
        menuDialogue.align(this.buttons[config.buttonName], [Y.WidgetPositionAlign.TL, Y.WidgetPositionAlign.BL]);

        this.get('host').openMenus = [menuDialogue];
    },

    /**
     * We want the smallest square that can fit ALL of our colors in. For every
     * number x, there are 2 closest square with side s,
     * s^2 > x > (s-1)^2, we want square s^2 with side s, since square (s-1)^2
     * won't fit all of our colors.
     *
     * @return The appropriate row count with respect to the number of colors
     *         that will approximate a square the closest.
     * @private
     */
    _getDesiredRowCount: function() {
        var colors = this.get('colors');
        var colorCount = colors.length;

        /**
         * Note that:
         * (rowCount-1)^2 <= colorCount <= rowCount^2, Therefore,
         * (rowCount-1) <= sqrt(colorCount) <= rowCount.
         *
         * By observation, (rowCount-1)^2 is undesriable (overflow), thus rowCount^2
         * is what we want. With sides rowCount. X
         */
        var rowCount = Math.ceil(Math.sqrt(colorCount));

        if (rowCount < MIN_SQUARE_SIDE_LENGTH) {
            rowCount = MIN_SQUARE_SIDE_LENGTH;
        } else if (rowCount > MAX_SQUARE_SIDE_LENGTH) {
            rowCount = MAX_SQUARE_SIDE_LENGTH;
        }

        return rowCount;
    },

    /**
     * Change the font color to the specified color.
     *
     * @method _changeStyle
     * @param {EventFacade} e
     * @param {string} color The new font color
     * @private
     */
    _changeStyle: function(e, color) {
        this.get('host').formatSelectionInlineStyle({
            color: color
        });
    }
}, {
    ATTRS: {
        /**
         * The list of available colors
         *
         * @attribute colors
         * @type array
         * @default {}
         */
        colors: {
            value: {}
        }
    }
});