YUI.add('moodle-atto_morefontcolors-button', function (Y, NAME) {

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
 * A Menu for the Atto More Font Colours.
 *
 * @module     moodle-atto_morefontcolors-button
 * @package    atto_morefontcolors
 * @copyright  2015 Joey Andres <Joey andres>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

var COLOR_PALETTE_TEMPLATE = '' +
    '<div class="open {{config.buttonClass}} atto_menu" ' +
        'style="min-width:{{config.innerOverlayWidth}}; background-color: #FFFFFF; ' +
        'border: 1px solid #CCC; ' +
        'padding: 5px; border-radius: 5px;">' +
    '</div>';

var ROW_WRAPPER_TEMPLATE = '' +
    '<div style="padding: 0px; display: table; margin: 0px; line-height: 0px;"></div>';

var COLOR_BUTTON_TEMPLATE = '' +
    '<a style="width: {{width}}; ' +
        'height: {{height}}; ' +
        'background-color: {{color}}; ' +
    'border: 1px solid #CCC; ' +
        'display: inline-block; ' +
        'margin: 5px 5px 0px 0px;' +
        'padding: 0px;"' +
        'class="atto_menuentry"' +
        'href="#">' +
    '</a>';

/**
 * A Menu for the Atto editor used in Moodle.
 *
 * This is a drop down list of buttons triggered (and aligned to) a
 * location.
 *
 * @namespace M.editor_atto
 * @class Menu
 * @main
 * @constructor
 * @extends M.core.dialogue
 */
MenuExt = function() {
    MenuExt.superclass.constructor.apply(this, arguments);
};

Y.extend(MenuExt, M.core.dialogue, {

    /**
     * A list of the menu handlers which have been attached here.
     *
     * @property _menuHandlers
     * @type Array
     * @private
     */
    _menuHandlers: null,

    initializer: function(config) {
        var headertext,
            bb;

        this._menuHandlers = [];

        var colorElemRows = [];
        var column_size = config.rowSize;  // Note that we display a square, thus column_size = max_row_size.
        var start_index = 0;
        while (config.items.length > 0) {
            colorElemRows.push(this._generateColorRow(config.items.splice(0, column_size), start_index));
            start_index += column_size;
        }

        // Create the actual button.
        var template = Y.Handlebars.compile(COLOR_PALETTE_TEMPLATE),
            menu = Y.Node.create(template({
                config: config
            }));

        Y.Array.each(colorElemRows, function(row) {
            menu.appendChild(row);
        });

        this.set('bodyContent', menu);

        bb = this.get('boundingBox');
        bb.addClass('editor_atto_controlmenu');
        bb.addClass('editor_atto_menu');
        bb.one('.moodle-dialogue-wrap')
            .removeClass('moodle-dialogue-wrap')
            .addClass('moodle-dialogue-content');

        headertext = Y.Node.create('<h3/>')
            .addClass('accesshide')
            .setHTML(this.get('headerText'));
        this.get('bodyContent').prepend(headertext);

        // Hide the header and footer node entirely.
        this.headerNode.hide();
        this.footerNode.hide();

        this._setupHandlers();
    },

    /**
     * Setup the Event handlers.
     *
     * @method _setupHandlers
     * @private
     */
    _setupHandlers: function() {
        var contentBox = this.get('contentBox');
        // Handle menu item selection.
        this._menuHandlers.push(
            // Select the menu item on space, and enter.
            contentBox.delegate('key', this._chooseMenuItem, '32, enter', '.atto_menuentry', this),

            // Move up and down the menu on up/down.
            contentBox.delegate('key', this._handleKeyboardEvent, 'down:38,40', '.dropdown-menu', this),

            // Hide the menu when clicking outside of it.
            contentBox.on('focusoutside', this.hide, this),

            // Hide the menu on left/right, and escape keys.
            contentBox.delegate('key', this.hide, 'down:37,39,esc', '.dropdown-menu', this)
        );
    },

    /**
     * Simulate other types of menu selection.
     *
     * @method _chooseMenuItem
     * @param {EventFacade} e
     */
    _chooseMenuItem: function(e) {
        e.target.simulate('click');
        e.preventDefault();
    },

    /**
     * Hide a menu, removing all of the event handlers which trigger the hide.
     *
     * @method hide
     * @param {EventFacade} e
     */
    hide: function(e) {
        if (this.get('preventHideMenu') === true) {
            return;
        }

        // We must prevent the default action (left/right/escape) because
        // there are other listeners on the toolbar which will focus on the
        // editor.
        if (e) {
            e.preventDefault();
        }

        var contentBox = this.get('contentBox');
        contentBox.setStyle("display", "none");

        return MenuExt.superclass.hide.call(this, arguments);
    },

    /**
     * Implement arrow-key navigation for the items in a toolbar menu.
     *
     * @method _handleKeyboardEvent
     * @param {EventFacade} e The keyboard event.
     * @static
     */
    _handleKeyboardEvent: function(e) {
        // Prevent the default browser behaviour.
        e.preventDefault();

        // Get a list of all buttons in the menu.
        var buttons = e.currentTarget.all('a[role="menuitem"]');

        // On cursor moves we loops through the buttons.
        var found = false,
            index = 0,
            direction = 1,
            checkCount = 0,
            current = e.target.ancestor('a[role="menuitem"]', true);

        // Determine which button is currently selected.
        while (!found && index < buttons.size()) {
            if (buttons.item(index) === current) {
                found = true;
            } else {
                index++;
            }
        }

        if (!found) {
            return;
        }

        if (e.keyCode === 38) {
            // Moving up so reverse the direction.
            direction = -1;
        }

        // Try to find the next
        do {
            index += direction;
            if (index < 0) {
                index = buttons.size() - 1;
            } else if (index >= buttons.size()) {
                // Handle wrapping.
                index = 0;
            }
            next = buttons.item(index);

            // Add a counter to ensure we don't get stuck in a loop if there's only one visible menu item.
            checkCount++;
            // Loop while:
            // * we are not in a loop and have not already checked every button; and
            // * we are on a different button; and
            // * the next menu item is not hidden.
        } while (checkCount < buttons.size() && next !== current && next.hasAttribute('hidden'));

        if (next) {
            next.focus();
        }

        e.preventDefault();
        e.stopImmediatePropagation();
    },
    /**
     *
     * @param colorArray {Array String} array of string of colors.
     */
    _generateColorRow: function(colorItemArr) {
        var colorButtonTemplate = Y.Handlebars.compile(COLOR_BUTTON_TEMPLATE);
        var colorRow = Y.Node.create(ROW_WRAPPER_TEMPLATE);
        Y.Array.each(colorItemArr, function(colorItem) {
            var colorButton = Y.Node.create(colorButtonTemplate({
                width: '20px',
                height: '20px',
                color: colorItem.color
            }));

            colorButton.on("click", colorItem.callback, colorItem.thisAnchor, colorItem.color);
            colorRow.appendChild(colorButton);
        });

        return colorRow;
    }
}, {
    NAME: "menu-ext",
    ATTRS: {
        /**
         * The header for the drop down (only accessible to screen readers).
         *
         * @attribute headerText
         * @type String
         * @default ''
         */
        headerText: {
            value: ''
        }

    }
});

Y.Base.modifyAttrs(MenuExt, {
    /**
     * The width for this menu.
     *
     * @attribute width
     * @default 'auto'
     */
    width: {
        value: 'auto'
    },

    /**
     * When to hide this menu.
     *
     * By default, this attribute consists of:
     * <ul>
     * <li>an object which will cause the menu to hide when the user clicks outside of the menu</li>
     * </ul>
     *
     * @attribute hideOn
     */
    hideOn: {
        value: [
            {
                eventName: 'clickoutside'
            }
        ]
    },

    /**
     * The default list of extra classes for this menu.
     *
     * @attribute extraClasses
     * @type Array
     * @default editor_atto_menu
     */
    extraClasses: {
        value: [
            'editor_atto_menu'
        ]
    },

    /**
     * Override the responsive nature of the core dialogues.
     *
     * @attribute responsive
     * @type boolean
     * @default false
     */
    responsive: {
        value: false
    },

    /**
     * The default visibility of the menu.
     *
     * @attribute visible
     * @type boolean
     * @default false
     */
    visible: {
        value: false
    },

    /**
     * Whether to centre the menu.
     *
     * @attribute center
     * @type boolean
     * @default false
     */
    center: {
        value: false
    },

    /**
     * Hide the close button.
     * @attribute closeButton
     * @type boolean
     * @default false
     */
    closeButton: {
        value: false
    }
});

Y.namespace('M.atto_morefontcolors').MenuExt = MenuExt;

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

}, '@VERSION@', {"requires": ["node", "moodle-core-notification-dialogue", "moodle-editor_atto-menu"]});
