YUI.add('moodle-atto_image-button', function (Y, NAME) {

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

/*
 * @package    atto_image
 * @copyright  2015 Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This is where I placed the constants and utility functions so they can be modified easily.
 *
 * @module moodle-atto_image-utility
 */

Y.namespace('M').atto_image = {
    /**
     * A helper function for parsing string to base 10 and avoiding jsling/shifter complains about having no radix.
     * @param val
     * @returns {Number}
     */
    parseInt10: function(val) { return parseInt(val, 10); },

    /**
     * A helper function for getting the natural image size prior to any html attributes and css styling.
     *
     * @param {string} src Source of the image.
     */
    get_natural_image_size: function(src) {
        var img = new Image();
        img.src = src;
        return {width: img.width, height: img.height};
    },

    /**
     * A helper function for getting the approximate aspect ratio.
     *
     * @param {{width: {Number}, height: {Number}} size of the image to acquire aspect ratio of.
     * @returns {number} aspect ratio approximation.
     */
    get_natural_image_aspect_ratio: function(size) {
        return (size.width*Y.M.atto_image.get('image_size_multiplier')) /
            (size.height*Y.M.atto_image.get('image_size_multiplier'));
    },

    /**
     * Sets the position property of a given node. Although one can directly use Y.Node.setStyle(s), this is very
     * self-documenting.
     *
     * @param {Y.Node} node to set position of.
     * @param {{top: *, right: *, bottom: *, left: *} pos
     */
    set_node_pos: function(node, pos){
        if (pos) {
            if (pos.top !== null && typeof pos.top !== "undefined") {
                node.setStyle('top', pos.top);
            }

            if (pos.right !== null && typeof pos.right !== "undefined") {
                node.setStyle('right', pos.right);
            }

            if (pos.bottom !== null && typeof pos.bottom !== "undefined") {
                node.setStyle('bottom', pos.bottom);
            }

            if (pos.left !== null && typeof pos.left !== "undefined") {
                node.setStyle('left', pos.left);
            }
        } else {
            console.error('Given position object is invalid');
        }
    },

    /**
     * @param {Y.Node} node to acquire the total horizontal border.
     * @returns {Number} Total horizontal border in px.
     */
    get_horizontal_border_width: function(node){
        return Y.M.atto_image.parseInt10(node.getComputedStyle("border-left-width")) +
            Y.M.atto_image.parseInt10(node.getComputedStyle("border-right-width"));
    },

    /**
     * @param {Y.Node} node to acquire the total vertical border.
     * @returns {Number} Total vertical border in px.
     */
    get_vertical_border_width: function(node){
        return Y.M.atto_image.parseInt10(node.getComputedStyle("border-top-width")) +
            Y.M.atto_image.parseInt10(node.getComputedStyle("border-bottom-width"));
    },

    /**
     * @param {Y.Node} node to acquire the total horizontal padding.
     * @returns {Number} Total horizontal border in px.
     */
    get_horizontal_padding_width: function(node){
        return Y.M.atto_image.parseInt10(node.getComputedStyle("padding-left")) +
            Y.M.atto_image.parseInt10(node.getComputedStyle("padding-right"));
    },

    /**
     * @param {Y.Node} node to acquire the total vertical padding.
     * @returns {Number} Total vertical border in px.
     */
    get_vertical_padding_width: function(node){
        return Y.M.atto_image.parseInt10(node.getComputedStyle("padding-bottom")) +
            Y.M.atto_image.parseInt10(node.getComputedStyle("padding-top"));
    },

    /**
     * @param {Y.Node} node to acquire the total non-content (border+padding) width .
     * @returns {Number} Total horizontal non-content in px.
     *
     * Note: Margin is not part of this, since by def'n, margin is outside box-model.
     */
    get_horizontal_non_content_width: function(node){
        return this.get_horizontal_border_width(node) + this.get_horizontal_padding_width(node);
    },

    /**
     * @param {Y.Node} node to acquire the total non-content (border+padding) height.
     * @returns {Number} Total vertical non-content in px.
     *
     * Note: Margin is not part of this, since by def'n, margin is outside box-model.
     */
    get_vertical_non_content_width: function(node){
        return this.get_vertical_border_width(node) + this.get_vertical_padding_width(node);
    },

    /**
     * Converts array of nesw (north, east, south, west) coordinates to trbl (top, right, bottom, left).
     *
     * @param {Array} nesw_array.
     * @return {Array} trbl format.
     */
    nesw_to_trbl: function(nesw_array) {
        return nesw_array.map(function(c) { return Y.M.atto_image.get('nesw_to_trbl_map')[c]; });
    },

    /**
     * "Hide until save" simply means the given node is hidden from the user until atto does an autosave/save.
     * @see clean.js of atto
     *
     * @param {Y.Node} node to enable hide until save feature.
     */
    enable_hide_until_save: function(node) {
        node.addClass('Mso-atto-image-resizable-node');
    },

    /**
     * @see enable_hide_until_save, this is simply the opposite.
     *
     * @param {Y.Node} node to enable hide until save feature.
     */
    disable_hide_until_save: function(node) {
        node.removeClass('Mso-atto-image-resizable-node');
    },

    is_mutation_observer_supported: function(){
        return typeof MutationObserver !== "undefined";
    }
};

Y.augment(Y.namespace('M').atto_image, Y.Attribute);
Y.namespace('M').atto_image._attr = {
    /**
     * @see http://ecma262-5.com/ELS5_HTML.htm#Section_8.5 for javascript precision. I'm not going to find the "reasonable"
     * maximum image size, one for its very subjective nature, and two, this is just image resizing, the nth decimal
     * point doesn't matter that match.
     *
     * @property image_size_multiplier
     * @readOnly
     * @public
     */
    image_size_multiplier: {
        value: 100,
        readOnly: true
    },

    // This is to avoid weird css behaviour when using Number.MAX_VALUE. Various browsers have different upper bound
    // in pixel size, thus just to be safe, this value is still exceedingly big, but well below the values of inferior
    // browsers (IEs).
    maximum_pixel_size: {
        value: 1000000,
        readOnly: true
    },

    /**
     * @property default_handle_config
     * @readOnly
     * @public
     */
    default_handle_config: {
        valueFn: function() { return ['nw', 'ne', 'sw', 'se', 'n', 's', 'e', 'w']; },  // redundant, but whatever.
        getter: function() { return ['nw', 'ne', 'sw', 'se', 'n', 's', 'e', 'w']; },  // avoid the accidental modification problem.
        readOnly: true
    },

    /**
     * @property default_min_width
     * @readOnly
     * @public
     */
    default_min_width: {
        value: 100,
        readOnly: true
    },

    /**
     * @property default_min_height
     * @readOnly
     * @public
     */
    default_min_height: {
        value: 100,
        readOnly: true
    },

    /**
     * @property default_max_width
     * @readOnly
     * @public
     */
    default_max_width: {
        valueFn: function() { return this.get("maximum_pixel_size"); },
        readOnly: true
    },

    /**
     * @property default_max_height
     * @readOnly
     * @public
     */
    default_max_height: {
        valueFn: function() { return this.get("maximum_pixel_size"); },
        readOnly: true
    },

    /**
     * @property default_resize_cfg
     * @readOnly
     * @public
     */
    default_resize_cfg: {
        valueFn: function() {
            return  {
                disabled: false,  // Show the resize controls.
                handle_config: this.get("default_handle_config"),

                min_width: this.get("default_min_width"),
                min_height: this.get("default_min_height"),
                max_width: this.get("default_max_width"),
                max_height: this.get("default_max_height")
            };
        },

        readOnly: true
    },

    /**
     * YUI.Overlay acts weird in the textarea since, overlay by itself have a position: absolute. The atto_text area
     * is position: initial/static, which results in YUI.Overlay not scroling in text area, since position: absolute
     * will only respect 'bounding box' of the first parent with position not initial/static. A simple solution is
     * simply to wrap overlays with this, which style.css have set to position: relative.
     *
     * @property option_overlay_node_wrapper_template
     * @type string
     * @readOnly
     * @public
     */
    option_overlay_node_wrapper_template: {
        value: '<div class="atto-image-overlay atto-image-overlay-node-wrapper atto_control {{classes}}"></div>',
        readOnly: true
    },

    /**
     * YUI.Overlay composes a of a Y.Node, thus this template will provide that Y.Node.
     *
     * @property overlay_node_template
     * @type string
     * @readOnly
     * @public
     */
    option_overlay_node_template: {
        value: '<div class="atto-image-overlay atto_control {{classes}}"></div>',
        readOnly: true
    },

    /**
     *
     */
    resize_overlay_node_template: {
        value: '<div class="atto-image-resize-overlay atto_control {{classes}}"></div>',
        readOnly: true
    },

    /**
     * Templates for the resizable control's object. Note the atto_control class. This is so they are filtered out when
     * saving.
     * @property resize_node_container
     * @type {string}
     * @readOnly
     * @public
     */
    resize_node_container: {
        value: '<div class="atto-image-resize-container atto_control {{classes}}" ></div>',
        readOnly: true
    },

    /**
     * Template for the resize handle.
     *
     * @property resize_handle_template
     * @type {string}
     * @public
     * @readonly
     */
    resize_handle_template: {
        value: '<div class="atto-image-resize-handle atto_control {{class_list}}"></div>',
        readOnly: true
    },

    /**
     * Template for the button that pop-ups the panel for setting the spacing (known in dev community as margin).
     *
     * @property float_decorator_button_template
     * @type {string}
     * @public
     * @readonly
     */
    float_decorator_button_template: {
        value: '<button class="atto_control atto_html_button atto-image-float {{class}} atto-image" type="button" title="{{title}}"></button>',
        readOnly: true
    },

    vertical_align_decorator_button_template: {
        value: '<button class="atto_control atto_html_button atto-vertical-align-float {{class}} atto-image" type="button" title="{{title}}"></button>',
        readOnly: true
    },

    /**
     * Template for the button that pop-ups the panel for setting the spacing (known in dev community as margin).
     *
     * @property border_decorator_button_template
     * @type {string}
     * @public
     * @readonly
     */
    border_decorator_button_template: {
        value: '<button class="atto_control atto_html_button atto_html_button atto-image-border atto-image" type="button" title="{{title}}"></button>',
        readOnly: true
    },

    /**
     * Template for the panel in Y.M.atto_image.spacing_decorator. This will contain a form that sets the spacing/margin.
     */
    border_decorator_panel_template: {
        value:
        '<div class="atto_control atto-image-panel atto-image-border-panel atto-image">'+
        '  <div class="yui3-widget-bd">' +
        '    <form>' +
        '      <fieldset>' +
        '        <div class="atto-image-input atto-image-border-top">' +
        '          <label for="top">Top:</label><input type="number" min="0" name="top" value="{{border-top}}"/><span>px</span>' +
        '          <div class="atto-image-illustration"></div>' +
        '        </div>' +
        '        <div class="atto-image-input atto-image-border-right">' +
        '          <label for="right">Right:</label><input type="number" min="0" name="right" value="{{border-right}}"/><span>px</span>' +
        '          <div class="atto-image-illustration"></div>' +
        '        </div>' +
        '        <div class="atto-image-input atto-image-border-bottom">' +
        '          <label for="bottom">Bottom:</label><input type="number" min="0" name="bottom" value="{{border-bottom}}"/><span>px</span>' +
        '          <div class="atto-image-illustration"></div>' +
        '        </div>' +
        '        <div class="atto-image-input atto-image-border-left">' +
        '          <label for="left">Left:</label><input type="number" min="0" name="left" value="{{border-left}}"/><span>px</span>' +
        '          <div class="atto-image-illustration"></div>' +
        '        </div>' +
        '      </fieldset>' +
        '    </form>' +
        '  </div>' +
        '</div>',
        readOnly: true
    },

    /**
     * Template for the button that pop-ups the panel for setting the spacing (known in dev community as margin).
     *
     * @property resizable_handle_constrain_template
     * @type {string}
     * @public
     * @readonly
     */
    spacing_decorator_button_template: {
        value: '<button class="atto_control atto_html_button atto-image-spacing atto-image" type="button" title="{{title}}"></button>',
        readOnly: true
    },

    /**
     * Template for the panel in Y.M.atto_image.spacing_decorator. This will contain a form that sets the spacing/margin.
     * todo: change image-top to image-spacing-top
     */
    spacing_decorator_panel_template: {
        value:
            '<div class="atto_control atto-image-panel atto-image-spacing-panel atto-image">' +
            '  <div class="yui3-widget-bd">' +
            '    <form>' +
            '      <fieldset>' +
            '        <div class="atto-image-input atto-image-spacing-top">' +
            '          <label for="top">Top:</label><input type="number" name="top" min="0" value="{{margin-top}}"/><span>px</span>' +
            '          <div class="atto-image-illustration"></div>' +
            '        </div>' +
            '        <div class="atto-image-input atto-image-spacing-right">' +
            '          <label for="right">Right:</label><input type="number" name="right" min="0" value="{{margin-right}}"/><span>px</span>' +
            '          <div class="atto-image-illustration"></div>' +
            '        </div>' +
            '        <div class="atto-image-input atto-image-spacing-bottom">' +
            '          <label for="bottom">Bottom:</label><input type="number" name="bottom" min="0" value="{{margin-bottom}}"/><span>px</span>' +
            '          <div class="atto-image-illustration"></div>' +
            '        </div>' +
            '        <div class="atto-image-input atto-image-spacing-left">' +
            '          <label for="left">Left:</label><input type="number" name="left" value="{{margin-left}}"/><span>px</span>' +
            '          <div class="atto-image-illustration"></div>' +
            '        </div>' +
            '      </fieldset>' +
            '    </form>' +
            '  </div>' +
            '</div>',
        readOnly: true
    },

    custom_decorator_button_template: {
        value: '<button class="atto_control atto_html_button atto-image-custom atto-image {{classes}} " type="button" title="{{title}}"></button>',
        readOnly: true
    },

    /**
     * Template for the form in custom panel. This contains the form for modifying style sheet.
     *
     * @property panel_template
     * @type {string}
     * @readOnly
     * @public
     */
    custom_decorator_panel_template: {
        value:
        '<div class="atto-image-panel atto-image-custom-panel atto-image">' +
        '  <div class="yui3-widget-bd">' +
        '    <form>' +
        '      <fieldset>' +
        '        <p>' +
        '          <label for="stylesheet">Custom Stylesheet</label><br/>' +
        '            <div class="atto-image-contenteditable-container">' +
        '              <div class="atto-image-contenteditable atto-image-contenteditable-stylesheet" contenteditable="true">' +
        '<pre>{{custom_styles}}</pre>' +
        '              </div>' +
        '            </div>' +
        '        </p>' +
            // Note: sensitive custom_css_class is filtered for both setting the value and acquiring value,
            //       admin can still turn off this feature in settings.php, thus prohibiting the setting
            //       of custom_css_class.
        '        {{#if custom_css_class}}' +
        '        <hr>' +
        '        <p>' +
        '          <label for="classes">Custom CSS Classes</label><br/>' +
        '            <div class="atto-image-contenteditable-container">' +
        '              <div class="atto-image-contenteditable atto-image-contenteditable-classes" contenteditable="true">' +
        '<pre>{{custom_classes}}</pre>' +
        '              </div>' +
        '        </p>' +
        '        {{/if}}' +
        '      </fieldset>' +
        '    </form>' +
        '  </div>' +
        '</div>',

        readOnly: true
    },

    /**
     * These are list of classes that are forbidden to be viewed/introduced by user since they are CRITICAL to proper
     * operations of atto_editor.
     *
     * @property image_forbidden_classes
     * @type {Array}
     * @readOnly
     * @public
     */
    image_forbidden_classes: {
        value: [/atto_control/g, /Mso-.*/g, /atto-.*/g],
        readOnly: true
    },

    /**
     * Converts the nesw (north, east, south, west) to its trbl (top, right, bottom, left) counterpart.
     *
     * @property nesw_to_trbl_map
     * @type {Object}
     * @readOnly
     * @public
     */
    nesw_to_trbl_map: {
        valueFn: function(){
            return {
                'n': 't',
                'e': 'r',
                's': 'b',
                'w': 'l',
                'ne': 'tr',
                'nw': 'tl',
                'se': 'br',
                'sw': 'bl'
            };
        },

        readOnly: true
    },

    /**
     * List of all the easing classes.
     * @see anim-easing.js in YUI.
     *
     * @property easing_classes
     * @type {Array}
     * @readOnly
     * @public
     */
    easing_classes: {
        valueFn: function() {
            return Object.keys(Y.Easing);
        },

        readOnly: true
    }
};
Y.namespace('M').atto_image.addAttrs(Y.namespace('M').atto_image._attr, {});

Y.namespace('M').atto_image.DEBUG = true;

var CSS = {
        RESPONSIVE: 'img-responsive',
        INPUTALIGNMENT: 'atto_image_alignment',
        INPUTALT: 'atto_image_altentry',
        INPUTHEIGHT: 'atto_image_heightentry',
        INPUTSUBMIT: 'atto_image_urlentrysubmit',
        INPUTURL: 'atto_image_urlentry',
        INPUTSIZE: 'atto_image_size',
        INPUTWIDTH: 'atto_image_widthentry',
        IMAGEALTWARNING: 'atto_image_altwarning',
        IMAGEBROWSER: 'openimagebrowser',
        IMAGEPRESENTATION: 'atto_image_presentation',
        INPUTCONSTRAIN: 'atto_image_constrain',
        INPUTCUSTOMSTYLE: 'atto_image_customstyle',
        IMAGEPREVIEW: 'atto_image_preview',
        IMAGEPREVIEWBOX: 'atto_image_preview_box'
    },
    SELECTORS = {
        INPUTURL: '.' + CSS.INPUTURL
    },
    ALIGNMENTS = [
        // Vertical alignment.
        {
            name: 'text-top',
            str: 'alignment_top',
            value: 'vertical-align',
            margin: '0 .5em'
        }, {
            name: 'middle',
            str: 'alignment_middle',
            value: 'vertical-align',
            margin: '0 .5em'
        }, {
            name: 'text-bottom',
            str: 'alignment_bottom',
            value: 'vertical-align',
            margin: '0 .5em',
            isDefault: true
        },

        // Floats.
        {
            name: 'left',
            str: 'alignment_left',
            value: 'float',
            margin: '0 .5em 0 0'
        }, {
            name: 'right',
            str: 'alignment_right',
            value: 'float',
            margin: '0 0 0 .5em'
        }, {
            name: 'customstyle',
            str: 'customstyle',
            value: 'style'
        }
    ],

    REGEX = {
        ISPERCENT: /\d+%/
    },

    COMPONENTNAME = 'atto_image',

    TEMPLATE = '' +
        '<form class="atto_form">' +
        '<label for="{{elementid}}_{{CSS.INPUTURL}}">{{get_string "enterurl" component}}</label>' +
        '<input class="fullwidth {{CSS.INPUTURL}}" type="url" id="{{elementid}}_{{CSS.INPUTURL}}" size="32"/>' +
        '<br/>' +

            // Add the repository browser button.
        '{{#if showFilepicker}}' +
        '<button class="{{CSS.IMAGEBROWSER}}" type="button">{{get_string "browserepositories" component}}</button>' +
        '{{/if}}' +

            // Add the Alt box.
        '<div style="display:none" role="alert" class="warning {{CSS.IMAGEALTWARNING}}">' +
        '{{get_string "presentationoraltrequired" component}}' +
        '</div>' +
        '<label for="{{elementid}}_{{CSS.INPUTALT}}">{{get_string "enteralt" component}}</label>' +
        '<input class="fullwidth {{CSS.INPUTALT}}" type="text" value="" id="{{elementid}}_{{CSS.INPUTALT}}" size="32"/>' +
        '<br/>' +

            // Add the presentation select box.
        '<input type="checkbox" class="{{CSS.IMAGEPRESENTATION}}" id="{{elementid}}_{{CSS.IMAGEPRESENTATION}}"/>' +
        '<label class="sameline" for="{{elementid}}_{{CSS.IMAGEPRESENTATION}}">{{get_string "presentation" component}}</label>' +
        '<br/>' +

            // Add the size entry boxes.
        '<label class="sameline" for="{{elementid}}_{{CSS.INPUTSIZE}}">{{get_string "size" component}}</label>' +
        '<div id="{{elementid}}_{{CSS.INPUTSIZE}}" class="{{CSS.INPUTSIZE}}">' +
        '<label class="accesshide" for="{{elementid}}_{{CSS.INPUTWIDTH}}">{{get_string "width" component}}</label>' +
        '<input type="text" class="{{CSS.INPUTWIDTH}} input-mini" id="{{elementid}}_{{CSS.INPUTWIDTH}}" size="4"/> x ' +

            // Add the height entry box.
        '<label class="accesshide" for="{{elementid}}_{{CSS.INPUTHEIGHT}}">{{get_string "height" component}}</label>' +
        '<input type="text" class="{{CSS.INPUTHEIGHT}} input-mini" id="{{elementid}}_{{CSS.INPUTHEIGHT}}" size="4"/>' +

            // Add the constrain checkbox.
        '<input type="checkbox" class="{{CSS.INPUTCONSTRAIN}} sameline" id="{{elementid}}_{{CSS.INPUTCONSTRAIN}}"/>' +
        '<label for="{{elementid}}_{{CSS.INPUTCONSTRAIN}}">{{get_string "constrain" component}}</label>' +
        '</div>' +

            // Add the alignment selector.
        '<label class="sameline" for="{{elementid}}_{{CSS.INPUTALIGNMENT}}">{{get_string "alignment" component}}</label>' +
        '<select class="{{CSS.INPUTALIGNMENT}}" id="{{elementid}}_{{CSS.INPUTALIGNMENT}}">' +
        '{{#each alignments}}' +
        '<option value="{{value}}:{{name}};">{{get_string str ../component}}</option>' +
        '{{/each}}' +
        '</select>' +
            // Hidden input to store custom styles.
        '<input type="hidden" class="{{CSS.INPUTCUSTOMSTYLE}}"/>' +
        '<br/>' +

            // Add the image preview.
        '<div class="mdl-align">' +
        '<div class="{{CSS.IMAGEPREVIEWBOX}}">' +
        '<img src="#" class="{{CSS.IMAGEPREVIEW}}" alt="" style="display: none;"/>' +
        '</div>' +

            // Add the submit button and close the form.
        '<button class="{{CSS.INPUTSUBMIT}}" type="submit">{{get_string "saveimage" component}}</button>' +
        '</div>' +
        '</form>',

    IMAGETEMPLATE = '' +
        '<img src="{{url}}" alt="{{alt}}" ' +
        '{{#if width}}width="{{width}}" {{/if}}' +
        '{{#if height}}height="{{height}}" {{/if}}' +
        '{{#if presentation}}role="presentation" {{/if}}' +
        'style="{{alignment}}{{margin}}{{customstyle}}"' +
        '{{#if classlist}}class="{{classlist}}" {{/if}}' +
        '/>';// This file is part of Moodle - http://moodle.org/
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
 * atto_image-overlay
 *
 * @package    atto_image
 * @copyright  2015 Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @class overlay
 * @constructor
 */
Y.namespace('M.atto_image').overlay = function(cfg) {
    void(cfg);
    Y.M.atto_image.overlay.superclass.constructor.apply(this, arguments);
};
Y.extend(Y.M.atto_image.overlay, Y.Base, {
    initializer: function(cfg) {
        this.setAttrs(cfg);
        this._setup_overlay_node();
    },

    destructor: function() {
        // Destroy each decorators first.
        this.get("decorator").forEach(function(decorator) {
            decorator.destroy();
        }, this);

        // Destroy the overlay itself.
        if (this.get("overlay")) { this.get("overlay").destroy(); }
        if (this.get("overlay_node")) { this.get("overlay_true").remove(true); }
    },

    /**
     * Uses all the aggregated decorators to decorate this overlay.
     */
    decorate: function() {
        this.get("decorator").forEach(function(decorator) {
            decorator.decorate(this.get("overlay"));
        }, this);
    },

    /**
     * Aligns this overlay to align_node.
     */
    align: function() {
        this._setup_overlay_node();
        this.get("overlay").align();
    },

    /**
     * Display this overlay once and for all.
     */
    render: function() {
        this.get("overlay").render();
        this._setup_overlay_node();
    },

    /******************************************************************************************************************
     * PRIVATE METHODS BELOW. #########################################################################################
     ******************************************************************************************************************/

    /**
     * Routine for setting up overlay node.
     *
     * @private
     */
    _setup_overlay_node: function() {
        // Get align_node and do nothing if the align_node is not set.
        var align_node = this.get("align_node");
        if (align_node === null || typeof align_node === "undefined") {
            console.error('Cannot setup overlay node, align_node is not set.');
            return;
        }

        var align = Y.WidgetPositionAlign;
        this.get("overlay_node").addClass("horizontal-align");
        this.get("overlay").align(this.get("align_node"), [align.TL, align.BL]);
    }
}, {
    NAME: 'overlay',

    ATTRS: {
        /**
         * Reference to the Y.Node we align the overlay object around.
         *
         * @property align_node
         * @type {null|Y.Node}
         * @required
         * @default null
         * @public
         */
        align_node: {
            value: null,

            validator: function(val) {
                var valid = val === null || (val instanceof Y.Node);
                if (!valid) { console.error('Given node to encapsulate is not of appropriate type.'); }
                return valid;
            }
        },

        /**
         * Reference to the Y.Node that wraps overlay_node. This is used by atto editor
         * @see utility.js option_overlay_node_wrapper_template for explanation why we are wrapping the overlay_node.
         *
         * If setter is given a non-null overlay_node_wrapper and overlay_node is also non-null,
         * we wrap overlay_node with overlay_node_wrapper.
         *
         * @property align_node
         * @type {null|Y.Node}
         * @optional
         * @default null
         * @public
         */
        overlay_node_wrapper: {
            value: null,

            setter: function(val) {
                if (val === null) { return val; }

                if (this.get('overlay_node')) {
                    val.appendChild(this.get('overlay_node'));
                }
                return val;
            },

            validator: function(val) {
                var valid = val === null || (val instanceof Y.Node);
                if (!valid) { console.error('Given node to encapsulate is not of appropriate type.'); }
                return valid;
            }
        },

        /**
         * Reference to the Y.Node that the overlay is going to use.
         *
         * @property overlay_node
         * @type {Y.Node}
         * @default
         * @readOnly
         * @private
         */
        overlay_node: {
            valueFn: function() {
                var node_template = Y.Handlebars.compile(Y.M.atto_image.get("option_overlay_node_template"));
                return Y.Node.create(node_template({classes: ''}));
            },
            validator: function(val) {
                var valid = val instanceof Y.Node;
                if (!valid) { console.error('Given overlay is invalid.'); }
                return valid;
            },

            readOnly: true
        },

        /**
         * The overlay object itself. The one responsible for doing overlay related stuff.
         * @see http://yuilibrary.com/yui/docs/overlay/
         *
         * @property overlay
         * @type {Y.Overlay}
         * @required
         * @public
         */
        overlay: {
            valueFn: function() {
                var align = Y.WidgetPositionAlign;
                return (new Y.Overlay({
                    srcNode: this.get("overlay_node"),
                    align: {
                        node: this.get("align_node"),
                        points: [align.BL, align.TL]
                    }
                }));
            },

            validator: function(val) {
                var valid = val instanceof Y.Overlay;
                if (!valid) { console.error('Given overlay is invalid.'); }
                return valid;
            },

            readOnly: true
        },

        /**
         * List of overlay_decorator.
         *
         * @property node
         * @type {Array}
         * @required
         * @private
         */
        decorator: {
            value: [],

            validator: function(val) {
                var valid = val instanceof Array;
                if (!valid) { console.error("Given decorator type is not an Array"); }
                return valid;
            }
        }
    }
});// This file is part of Moodle - http://moodle.org/
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
 * @package    atto_image
 * @copyright  2015 Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module atto_image-overlay-decorator
 *
 * Contains overlay decorators.
 */

/**
 * Abstract class for overlay.
 *
 * @class overlay_decorator
 * @constructor
 */
Y.namespace('M.atto_image').overlay_decorator = function(cfg) {
    void(cfg);
    Y.M.atto_image.overlay_decorator.superclass.constructor.apply(this, arguments);
};
Y.extend(Y.M.atto_image.overlay_decorator, Y.Base, {
    initializer: function (cfg) { this.setAttrs(cfg); },
    destructor: function () {
        // Destroy all components (remove from DOM tree and delete from memory).
        this.get("_overlay_components").forEach(function (node) { node.remove(true); });
    },

    /**
     * Override this to amend/modify overlay, adding more interface.
     * @param {Y.overlay} overlay The overlay object we amend/modify.
     * @virtual
     */
    decorate: function (overlay) { void(overlay); }
}, {
    NAME: 'overlay-decorator',

    ATTRS: {
        /**
         * Reference to the Y.Node we want to modify.
         *
         * @property node
         * @type {null|Y.Node}
         * @required
         * @default null
         * @public
         */
        node: {
            value: null,

            validator: function (val) {
                var valid = val === null || (val instanceof Y.Node);
                if (!valid) {
                    console.error('Given node to encapsulate is not of appropriate type.');
                }
                return valid;
            }
        },

        /**
         * Keeps track of what we added to the overlay so we can delete them when cleaning up.
         *
         * @property _overlay_components
         * @type {Array}
         * @default []
         * @private
         */
        _overlay_components: {
            value: [],
            validator: function (val) {
                var valid = val instanceof Array;
                if (!valid) {
                    console.error('Given _overlay_components is of invalid type.');
                }
                return valid;
            }
        }
    }
});


/**
 * Encapsulates a node (specifically an inline DOM element) and pairs it with a gui interface for assigning
 * float properties.
 *
 * @class float_decorator
 * @constructor
 */
Y.namespace('M.atto_image').float_decorator = function(cfg) {
    void(cfg);
    Y.M.atto_image.float_decorator.superclass.constructor.apply(this, arguments);
};
Y.extend(Y.M.atto_image.float_decorator, Y.M.atto_image.overlay_decorator, {
    /**
     * @override
     */
    decorate: function (overlay) {
        var button_template = Y.M.atto_image.get('float_decorator_button_template');
        var button_compile = Y.Handlebars.compile(button_template);

        var button_left_float = Y.Node.create(button_compile({
            'class': 'atto-image-left',
            title: M.util.get_string('leftaligntooltip', COMPONENTNAME)
        }));
        var button_right_float = Y.Node.create(button_compile({
            'class': 'atto-image-right',
            title: M.util.get_string('rightaligntooltip', COMPONENTNAME)
        }));
        var button_none_float = Y.Node.create(button_compile({
            'class': 'atto-image-none',
            title: M.util.get_string('normalflowtooltip', COMPONENTNAME)
        }));

        // Get current style of node if any and highlight the appropriate button.
        var float_stlye = this.get("node").getStyle("float").toLowerCase();
        switch (float_stlye) {
            case "left":
                this._highlight_button(button_left_float);
                break;
            case "right":
                this._highlight_button(button_right_float);
                break;
            case "initial":
                this._highlight_button(button_none_float);
                break;
            // Ignore other styles.
        }

        // Keep reference to the dom elements we attach to visitor.
        this.set("_overlay_components", [button_left_float, button_right_float, button_none_float]);

        button_left_float.on('click', function (e) {
            void(e);
            Y.log('float: left', 'debug', 'float');
            this.get("node").setStyle("float", "left");
            this._highlight_button(button_left_float);
            overlay.align();
        }, this);
        button_right_float.on('click', function (e) {
            void(e);
            Y.log('float: right', 'debug', 'float');
            this.get("node").setStyle("float", "right");
            this._highlight_button(button_right_float);
            overlay.align();
        }, this);
        button_none_float.on('click', function (e) {
            void(e);
            Y.log('float: initial', 'debug', 'float');
            this.get("node").setStyle("float", "initial");
            this._highlight_button(button_none_float);
            overlay.align();
        }, this);

        overlay.get("srcNode").appendChild(button_left_float);
        overlay.get("srcNode").appendChild(button_right_float);
        overlay.get("srcNode").appendChild(button_none_float);
        overlay.align();
    },

    _highlight_button: function(button) {
        button.addClass('atto-image-highlight');
        this.get("_overlay_components").forEach(function(comp) {
            if (comp !== button) { button.removeClass('atto-image-highlight'); }
        });
    },

    _remove_highlight_on_all_button: function() {
        this.get("_overlay_components").forEach(function(comp) {
            comp.removeClass('atto-image-highlight');
        });
    }
}, {
    NAME: 'float-decorator',
    ATTRS: {}
});


/**
 * Encapsulates a node (specifically an inline DOM element) and pairs it with a gui interface for assigning
 * border properties.
 *
 * @class border_decorator
 * @constructor
 * @deprecated
 */
Y.namespace('M.atto_image').border_decorator = function(cfg) {
    void(cfg);
    Y.M.atto_image.border_decorator.superclass.constructor.apply(this, arguments);
};
Y.extend(Y.M.atto_image.border_decorator, Y.M.atto_image.overlay_decorator, {
    /**
     * @override
     */
    decorate: function (overlay) {
        var button_template = Y.M.atto_image.get('border_decorator_button_template');
        var button_compile = Y.Handlebars.compile(button_template);
        var border_panel_button = Y.Node.create(button_compile({
            title: M.util.get_string('bordertooltip', COMPONENTNAME)
        }));

        border_panel_button.on('click', function(e) {
            void(e);
            var border = {
                left: this.get("node").getComputedStyle("border-left-width"),
                top: this.get("node").getComputedStyle("border-top-width"),
                right: this.get("node").getComputedStyle("border-right-width"),
                bottom: this.get("node").getComputedStyle("border-bottom-width")
            };

            var border_decorator_panel_compile = Y.Handlebars.compile(
                Y.M.atto_image.get('border_decorator_panel_template')
            );
            var panel_div = Y.Node.create(border_decorator_panel_compile({
                'border-top': Y.M.atto_image.parseInt10(border.top),
                'border-right': Y.M.atto_image.parseInt10(border.right),
                'border-bottom': Y.M.atto_image.parseInt10(border.bottom),
                'border-left': Y.M.atto_image.parseInt10(border.left)
            }));

            var self = this;
            var panel = new Y.Panel({
                bodyContent: panel_div,
                headerContent: 'border',
                zIndex     : 99,  /* This is also reinforced in style. This is so it is above the resize handle (50). */
                centered   : true,
                modal      : true,
                render     : true,
                buttons: [
                    {
                        value  : 'Ok',
                        section: Y.WidgetStdMod.FOOTER,
                        action : function (e) {
                            var atto_image_panel = this.get("bodyContent").item(0);
                            var border_config = {};
                            atto_image_panel.all('input').each(function(node) {
                                switch (node.get('name')) {
                                    case 'left':
                                        border_config['border-left'] = node.get('value') + 'px solid black';
                                        break;
                                    case 'top':
                                        border_config['border-top'] = node.get('value') + 'px solid black';
                                        break;
                                    case 'right':
                                        border_config['border-right'] = node.get('value') + 'px solid black';
                                        break;
                                    case 'bottom':
                                        border_config['border-bottom'] = node.get('value') + 'px solid black';
                                        break;
                                }
                            }, this);

                            console.log(border_config);

                            self.get("node").setStyles(border_config);

                            e.preventDefault();
                            panel.hide();
                        }
                    },
                    {
                        value  : 'Cancel',
                        section: Y.WidgetStdMod.FOOTER,
                        action : function (e) {
                            e.preventDefault();
                            panel.hide();
                        }
                    }
                ]
            });

            panel.plug(Y.Plugin.Drag, { handles: ['.yui3-widget-hd'] });
        }, this);

        overlay.get("srcNode").appendChild(border_panel_button);
        this.set("_overlay_components", [border_panel_button]);
        overlay.align();
    }
}, {
    NAME: 'border-decorator',

    ATTRS: {
        /**
         * todo: are we doing this? Not in acceptance criteria atm, but attributes are ready just in case.
         */
        colors: {
            value: [],
            validator: function(val) { void(val); return true; }
        }
    }
});


/**
 * Extends overlay_decorator, specializing in creating interface for adding custom css/styling for img.
 *
 * @class custom_decorator
 * @constructor
 */
Y.namespace('M.atto_image').custom_decorator = function(cfg) {
    void(cfg);
    Y.M.atto_image.custom_decorator.superclass.constructor.apply(this, arguments);
};
Y.extend(Y.M.atto_image.custom_decorator, Y.M.atto_image.overlay_decorator, {
    /**
     * @override
     */
    decorate: function (overlay) {
        var button_template = Y.M.atto_image.get('custom_decorator_button_template');
        var button_compile = Y.Handlebars.compile(button_template);
        var custom_panel_button = Y.Node.create(button_compile({
            classes: '',
            title: M.util.get_string('customcsstooltip', COMPONENTNAME)
        }));

        custom_panel_button.on('click', function (e) {
            void(e);

            var self = this;
            var node = this.get("node");
            var panel_template = Y.Handlebars.compile(Y.M.atto_image.get('custom_decorator_panel_template'));
            var custom_styles = this.split_semi_colon_by_newline(node.getAttribute("style"));
            var custom_classes = this.filter_forbidden_classes(node.getAttribute("class"));
            var panel_compiled = panel_template({
                custom_css_class: this.get('disable_custom_classes') === false,
                // Due to some bug in contenteditable, there are no cursor if there is a <pre><code> tag and no element.
                // Thus there must be at least one space character.
                custom_styles: custom_styles + ' ',
                custom_classes: custom_classes + ' '
            });

            var panel = new Y.Panel({
                bodyContent: Y.Node.create(panel_compiled),
                headerContent: 'Custom Styling and CSS',
                zIndex: 99, /* This is also reinforced in style. This is so it is above the resize handle (50). */
                width: 400,
                centered: true,
                modal: true,
                render: true,
                buttons: [
                    {
                        value: 'Ok',
                        section: Y.WidgetStdMod.FOOTER,
                        action: function (e) {
                            var atto_image_custom_panel = this.get("bodyContent").item(0);

                            //  Set the attribute to node.
                            atto_image_custom_panel.all('div.atto-image-contenteditable').each(function(content_editable) {
                                if (content_editable.hasClass('atto-image-contenteditable-stylesheet')) {
                                    self.get("node").setAttribute('style', self.get_content_editable_content(content_editable));
                                } else if (content_editable.hasClass('atto-image-contenteditable-classes')) {
                                    // Also filter users inputted classes. And individually insert them via YUI's
                                    // addClass method so it takes care of duplicate checking and what not, and also
                                    // avoid override the "required" classes if any.
                                    self.get("node").set('className', '');
                                    self.filter_forbidden_classes_arr(
                                        self.get_content_editable_content(content_editable)).forEach(function(clss) {
                                            self.get("node").addClass(clss);
                                    });
                                }
                            }, this);

                            e.preventDefault();
                            panel.hide();
                        }
                    }, {
                        value: 'Cancel',
                        section: Y.WidgetStdMod.FOOTER,
                        action: function (e) {
                            e.preventDefault();
                            panel.hide();
                        }
                    }
                ]
            });

            panel.plug(Y.Plugin.Drag, { handles: [ '.yui3-widget-hd' ] });  // Make header the handle for dragging.

            panel.getClassName('atto-image');  // Prefix the panel class with atto-image
        }, this);

        overlay.get("srcNode").appendChild(custom_panel_button);
        this.set("_overlay_components", [custom_panel_button]);
        overlay.align();
    },

    get_content_editable_content: function(content_editable){
        var non_filtered_content = content_editable.getHTML();
        return non_filtered_content.replace(/(<([^>]+)>)/ig,"");
    },

    split_semi_colon_by_newline: function(text, trim_each_line) {
        trim_each_line = trim_each_line || true;

        var semi_colon_split_array = text.split(';');
        if (trim_each_line) {
            semi_colon_split_array = semi_colon_split_array.map(function(line) { return line.trim(); });
        }
        return semi_colon_split_array.join(';\n');
    },

    filter_forbidden_classes: function(classes) { return this.filter_forbidden_classes_arr(classes).join(' '); },

    filter_forbidden_classes_arr: function(classes) {
        var class_arr = classes.split(' ');
        var forbidden_classes = Y.M.atto_image.get('image_forbidden_classes');
        return (class_arr.filter(function(clss) {
            var forbidden = false;
            forbidden_classes.forEach(function(fobidden_clss) {
                if (fobidden_clss.test(clss)) {
                    forbidden = true;
                }
            }, this);

            return !forbidden;
        }, this));
    }
}, {
    NAME: 'custom-decorator',
    ATTRS: {
        disable_custom_classes: {
            value: false,

            validator: function(val) {
                var valid = typeof val === "boolean";
                if (!valid) { console.error('Given disable_custom_classes attribute is not a boolean. '); }
                return valid;
            }
        }
    }
});


/**
 * Encapsulates a node (specifically an inline DOM element) and pairs it with a gui interface for assigning
 * vertical-align properties.
 *
 * @class vertical_align_decorator
 * @constructor
 */
Y.namespace('M.atto_image').vertical_align_decorator = function(cfg) {
    void(cfg);
    Y.M.atto_image.vertical_align_decorator.superclass.constructor.apply(this, arguments);
};
Y.extend(Y.M.atto_image.vertical_align_decorator, Y.M.atto_image.overlay_decorator, {
    /**
     * @override
     */
    decorate: function (overlay) {
        var button_template = Y.M.atto_image.get('vertical_align_decorator_button_template');
        var button_compile = Y.Handlebars.compile(button_template);

        var button_01 = Y.Node.create(button_compile({
            'class': 'atto-image-text-top',
            title: M.util.get_string('texttoptooltip', COMPONENTNAME)
        }));
        var button_02 = Y.Node.create(button_compile({
            'class': 'atto-image-baseline',
            title: M.util.get_string('textbaselinetooltip', COMPONENTNAME)
        }));
        var button_03 = Y.Node.create(button_compile({
            'class': 'atto-image-text-bottom',
            title: M.util.get_string('textbottomtooltip', COMPONENTNAME)
        }));

        // Get current style of node if any.
        var vertical_align_style = this.get("node").getStyle("vertical-align").toLowerCase();
        switch (vertical_align_style) {
            case "text-top":
                this._highlight_button(button_01);
                break;
            case "baseline":
                this._highlight_button(button_02);
                break;
            case "text-bottom":
                this._highlight_button(button_03);
                break;
            // Ignore other styles.
        }

        // Keep reference to the dom elements we attach to visitor.
        this.set("_overlay_components", [button_01, button_02, button_03]);

        button_01.on('click', function (e) {
            void(e);
            this.get("node").setStyle("vertical-align", "text-top");
            this._highlight_button(button_01);
            overlay.align();
        }, this);
        button_02.on('click', function (e) {
            void(e);
            this.get("node").setStyle("vertical-align", "baseline");
            this._highlight_button(button_02);
            overlay.align();
        }, this);
        button_03.on('click', function (e) {
            void(e);
            this.get("node").setStyle("vertical-align", "text-bottom");
            this._highlight_button(button_03);
            overlay.align();
        }, this);

        overlay.get("srcNode").appendChild(button_01);
        overlay.get("srcNode").appendChild(button_02);
        overlay.get("srcNode").appendChild(button_03);
        overlay.align();
    },

    _highlight_button: function(button) {
        button.addClass('atto-image-highlight');
        this.get("_overlay_components").forEach(function(comp) {
            if (comp !== button) { button.removeClass('atto-image-highlight'); }
        });
    },

    _remove_highlight_on_all_button: function() {
        this.get("_overlay_components").forEach(function(comp) {
            comp.removeClass('atto-image-highlight');
        });
    }
}, {
    NAME: 'vertical-align-decorator',
    ATTRS: {}
});


/**
 * @class spacing_decorator
 * @brief Encapsulates a node (specifically an inline DOM element) and pairs it with a gui interface for assigning
 *        vertical-align properties.
 */
Y.M.atto_image.spacing_decorator = function(cfg) {
    void(cfg);
    Y.M.atto_image.spacing_decorator.superclass.constructor.apply(this, arguments);
};
Y.extend(Y.M.atto_image.spacing_decorator, Y.M.atto_image.overlay_decorator, {
    /**
     * @override
     */
    decorate: function (overlay) {
        var button_template = Y.M.atto_image.get('spacing_decorator_button_template');
        var button_compile = Y.Handlebars.compile(button_template);
        var spacing_panel_button = Y.Node.create(button_compile({
            title: M.util.get_string('spacingtooltip', COMPONENTNAME)
        }));

        spacing_panel_button.on('click', function(e) {
            void(e);
            var margin = {
                left: this.get("node").getComputedStyle("margin-left"),
                top: this.get("node").getComputedStyle("margin-top"),
                right: this.get("node").getComputedStyle("margin-right"),
                bottom: this.get("node").getComputedStyle("margin-bottom")
            };
            var spacing_decorator_panel_template = Y.Handlebars.compile(
                Y.M.atto_image.get('spacing_decorator_panel_template')
            );
            var spacing_decorator_panel_div = Y.Node.create(spacing_decorator_panel_template({
                'margin-top': Y.M.atto_image.parseInt10(margin.top),
                'margin-right': Y.M.atto_image.parseInt10(margin.right),
                'margin-bottom': Y.M.atto_image.parseInt10(margin.bottom),
                'margin-left': Y.M.atto_image.parseInt10(margin.left)
            }));

            var self = this;
            var panel = new Y.Panel({
                bodyContent: spacing_decorator_panel_div,
                headerContent: 'Spacing',
                zIndex     : 99,  /* This is also reinforced in style. This is so it is above the resize handle (50). */
                centered   : true,
                modal      : true,
                render     : true,
                buttons: [
                    {
                        value  : 'Ok',
                        section: Y.WidgetStdMod.FOOTER,
                        action : function (e) {
                            var atto_image_panel = this.get("bodyContent").item(0);
                            var margin_config = {};
                            atto_image_panel.all('input').each(function(node) {
                                switch (node.get('name')) {
                                    case 'top':
                                        margin_config['margin-top'] = node.get('value') + 'px';
                                        break;
                                    case 'right':
                                        margin_config['margin-right'] = node.get('value') + 'px';
                                        break;
                                    case 'bottom':
                                        margin_config['margin-bottom'] = node.get('value') + 'px';
                                        break;
                                    case 'left':
                                        margin_config['margin-left'] = node.get('value') + 'px';
                                        break;
                                    default:
                                        console.error('Serious error at image_spacing_decorator. Please refer this error to support/developer.');
                                }
                            }, this);

                            self.get("node").setStyles(margin_config);

                            e.preventDefault();
                            panel.hide();
                        }
                    }, {
                        value  : 'Cancel',
                        section: Y.WidgetStdMod.FOOTER,
                        action : function (e) {
                            e.preventDefault();
                            panel.hide();
                        }
                    }
                ]
            });

            panel.plug(Y.Plugin.Drag, {
                handles: [
                    '.yui3-widget-hd'
                ]
            });
        }, this);

        overlay.get("srcNode").appendChild(spacing_panel_button);
        this.set("_overlay_components", [spacing_panel_button]);
        overlay.align();
    }
}, {
    NAME: 'spacing-decorator',
    ATTRS: {}
});// This file is part of Moodle - http://moodle.org/
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
 * resizable module for node in textarea.
 *
 * @package    atto_image
 * @copyright  2015 Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class for making an object resizable. At the moment, this is only specialize for atto environment, textarea environment,
 * with img nodes.
 *
 * **High level documentation:********************************************************************************************
 *
 *   Working with atto_editor which utilizes "contenteditable" property of div instead of iframe for editing like tiny_mce
 *   have one major constraint:
 *   - In iframe (tinymce), we can use absolute position for placing our resize elements (handles and what not).
 *     This is not possible in "contenteditable" div without introducing polling to account for resizing of window,
 *     text editor, ... just so our resize elements can keep track of the nodes being resized.
 *
 *  To solve the constrain the following sequence is done when SELECTING a node (img in our case):
 *  1. We start with a node/img:
 *
 *     +-----------+
 *     | IMG Node  |
 *     |           |
 *     +-----------+
 *
 *  2. Since we want resize components (handles and such) yet we can't just placed it with position: absolute, we have
 *     to use relative position around our IMG Node. The following is then done:
 *
 *     +--------------+
 *     | +------------|      +-----------+
 *     | | IMG Clone |-<-----+ IMG Node  |
 *     | | (Shown)   ||      | (Hidden)  |
 *     | +------------|      +-----------+
 *     +--------------+
 *     |
 *     |
 *     |
 *     +----+resize_container
 *
 *     a.) We clone the IMG Node, copying its styles except the float, vertical-align, margin, ..., then hiding it.
 *     b.) Since we can't place anything inside img clone, we have no where to place the resize handles (n, e, s, w, ...),
 *         thus we have to wrap the img clone with a div so we have somewhere to suspend the resize handles.
 *         (Note: resize handles is handled by YUI's resizable and overlay module, I used to have one, but just for prototype).
 *     c.) Now it makes sense why we placed the styles that affects the "outer" part of the IMG Node in the resize container
 *         instead of IMG Clone. We want our IMG Clone snug in the resize container so it looks pleasing to the eyes.
 *     d.) IMG Clone have "atto_control" class. atto's clean.js ensures that they disappear when autosaving/saving.
 *     e.) IMG Node is not simply "Hidden", it is attached with a class Mso-atto-image-resizable-node, which our style.less/css
 *         have set to display: none. atto's clean.js will get rid of classes that start with "Mso-", thus our IMG Node
 *         is sure to be shown when autosaving/saving.
 *     f.) Dragging event is disabled only when resizing so we always maintain the two nodes beside each other. This is
 *         I think is a small sacrifice in UX. Allowing dragging, will only drag the resize container along with the img
 *         clone. But when it is time to save, IMG Node is still in the same spot.
 *     g.) Sync the size of IMG Clone with IMG Node every after drag:end event of resize handle. Despite IMG Node still
 *         being hidden, its width/height can still be updated.
 *     h.) Since at this point, only resize div is exposed, along with is child IMG Clone, deleting resize div also
 *         deletes IMG Node. This is done via MutationObserver. Just to be safe, resize div and childs are also deleted
 *         when IMG Node is deleted, thus a two way MutationObserver.
 *
 *  3. Last step in ensuring proper UX is allowing the user to preview the size of the IMG while playing with resize handles.
 *     Only having "resize container" will allow for such preview but resizing "resize container" will also cause
 *     scrolling since "resize container" flows with the text editor. You might say, why not have IMG Clone perform
 *     the preview, this is also bad for UX since the user will not have a reference point (size prior to resizing).
 *     To have both reference points and preview: a ghosting feature is introduce, much like tiny mce:
 *
 *     +--------------+
 *     | +------------|      +-----------+
 *     | | IMG Ghost  |-<-----+ IMG Node  |
 *     | | (Shown)   ||      | (Hidden)  |
 *     | +------------|      +-----------+
 *     +--------------+
 *     |
 *     |
 *     |
 *     +----+resize_container
 *
 *     a.) This way, if we resize the ghost, we still have a reference point (IMG Clone), while having a resizing preview.
 *     b.) At drag:end on IMG Ghost, we update IMG Clone, and in turn IMG Node.
 *
 *
 *    PS: If you have read some of node-core.js and clean.js, you might be tempted to just override resize_container div's
 *        Y.Node.remove() method so you unravel the IMG inside when autosave/save, thus allowing the user to drag
 *        img around even while resizing. After all, Y.Node keeps only one instance per node and have a Factory instead
 *        of just a simple constructor, thus ensuring overridden method persist between the same nodes. The problem with
 *        this is clean.js, rightfully so, clone the editor to not disrupt the user. This in turn disregard all the
 *        method overriding, thus farewell to an otherwise gorgeous solution.
 *
 * @class resizable
 * @brief Class for making an object resizable.
 */
Y.namespace('M.atto_image').resizable = function (cfg) {
    void(cfg);
    Y.M.atto_image.resizable.superclass.constructor.apply(this, arguments);
};
Y.extend(Y.namespace('M.atto_image').resizable, Y.Base, {
    initializer: function (cfg) {
        this.setAttrs(cfg);

        Y.log('resizable constructor is given disabled property: ' + this.get("disabled"), 'debug', 'resizable');
        if (this.get("disabled") === false) {
            this._enable();
        }
        this._handle_node_removal_handler();

        /**
         * @event resizable:click Fired when at least one of the nodes inside resize div is clicked. (Or resize obj is clicked).
         */
        this.publish('resizable:click', {
            emitFacade: true,
            broadcast: 2  // Global broadcast, just like button clicks.
        }, this);

        /**
         * @event resizable:resize:start Fired before resizing.
         */
        this.publish('resizable:resize:start', {
            emitFacade: true,
            broadcast: 2  // Global broadcast, just like button clicks.
        }, this);

        /**
         * @event resizable:resize:resize Fired during resizing.
         */
        this.publish('resizable:resize:resize', {
            emitFacade: true,
            broadcast: 2  // Global broadcast, just like button clicks.
        }, this);

        /**
         * @event resizable:resize:end Fired after resizing.
         */
        this.publish('resizable:resize:end', {
            emitFacade: true,
            broadcast: 2  // Global broadcast, just like button clicks.
        }, this);

        /**
         * @resizable:tween Fired during the resizing animation.
         */
        this.publish('resizable:tween', {
            emitFacade: true,
            broadcast: 2  // Global broadcast, just like button clicks.
        }, this);

        /**
         * @event resizable:init Fired once at the beginning. Due to some bug in YUI.
         */
        this.publish('resizable:init', {
            emitFacade: true,
            broadcast: 2, // Global broadcast, just like button clicks.
            context: this
        }, this);
    },

    destructor: function () {
        this.disable();
    },

    /**
     * Unlike the destructor in which gets rid of the resize components and make node visible again, delete_all,
     * deletes both resize components and image itself. This also fires a destroy event.
     */
    delete_all: function () {
        this.disable();  // Remove resize handles.

        // Remove the node itself.
        if (this.get("node") && this.get("node").getDOMNode()) { this.get("node").remove(true); }

        // Do the rest of the destructor stuff. (calls this.disable, but sanity check wont allow things twice).
        this.destroy();  // Proceed with death cycle.
    },

    /**
     * Call to show the resize handles.
     */
    enable: function () {
        if (this.get("disabled")) {
            this.set("disabled", false);
            this._enable();
        }
    },

    /**
     * Call to remove resize handles.
     */
    disable: function () {
        if (!this.get("disabled")) {
            this._disable();
            this.set("disabled", true);
        }
    },

    /******************************************************************************************************************
     * PRIVATE METHODS BELOW. #########################################################################################
     ******************************************************************************************************************/

    /**
     * Called by this.enable to show the handle controls.
     * @private
     */
    _enable: function () {
        var preview_node = this._create_and_setup_preview_node();
        var container_node = this._create_and_setup_node_container();

        // This simply ensures that the node is hidden, except when being save/autosave. This is done here, otherwise,
        // _create_and_setup_node_container will get some unwanted properties (e.g. display: none).
        Y.M.atto_image.enable_hide_until_save(this.get("node"));

        // Finally append the preview_node to the container.
        container_node.appendChild(preview_node);

        // Now that preview and container is created, create resize overlay, so we can actually resize something.
        this._setup_resize_overlay();

        // Handle events when the container is removed.
        this._handle_node_container_removal_handler();

        // Now that all children are created. Handle the click events, and ensure they all bubble up appropriately.
        this._setup_click_event_handler();
    },

    /**
     * Called by this.disable to destroy the handle controls.
     * @private
     */
    _disable: function () {
        // Could place this under the if statement below, but just in case
        // resize_overlay is created not under container. Nothing harm is done eitherway;
        this._destroy_resize_overlay();

        if (this.get("container")) {
            this.set("disable_called", true);

            this.get("container").remove(true);
            this.set("container", null);
        }

        // Show the original node again (if it still exist).
        if (this.get("node") && this.get("node").getDOMNode()) {
            Y.M.atto_image.disable_hide_until_save(this.get("node"));
        }
    },

    /**
     * Create and setup attribute container. Specifically, it tries to replicate all styling that only applies
     * from node's border outward, e.g. position, margin, or otherwise. The rest of the styling is nullified.
     *
     * TODO: With respect to img, this is sufficient, but wrt to other elements, this is unknown. This section will
     *       be in continuous development with other HTML element types since there are bound to be idiosyncrasies.
     *       (if there are demand for other things to resize other than img).
     *
     * Currently: The following is the summary of the algorithm:
     * 1. Create the container.
     * 2. Copy all the styles from node to container.
     * 3. Setup some quirks, i.e. Although they are styling that applies outside the border, we don't want some
     *    values of them since they can disrupt proper operation. The following are some of the list and explanations:
     *    a.) display: We want initial|display|inline -> inline-block to respect the margins.
     *    4.) position: We want initial|static -> relative so child with position: absolute, respect container.
     * 4. Reset all styling that applies from border within, the following is the list of such, and explanation why:
     *    a.) width/height: We want the container to hug preview_node
     *    b.) padding: We want the container to hug preview_node
     *    c.) border-width: We want the container to hug preview_node, plus, preview_node already have this (copied from node).
     *    d.) (... Insert something due to a bug ...)
     *
     * @returns {Y.Node} The node container itself.
     * @private
     */
    _create_and_setup_node_container: function () {
        var container_template = Y.Handlebars.compile(Y.M.atto_image.get('resize_node_container'));
        var container = Y.Node.create(container_template({classes: ''}));
        this.set("container", container);

        // Copy all styling from node to container.
        container.getDOMNode().style.cssText = this.get("node").getDOMNode().style.cssText;

        // Display quirk. See method comment.
        var node_display_style = this.get("node").getComputedStyle("display") || 'inline-block';
        if (node_display_style.toLowerCase() === "inline") { node_display_style = "inline-block"; }

        // Position quirk. See position comment.
        var node_position_style = this.get("node").getComputedStyle("position") || 'relative';
        if (node_position_style.toLowerCase() === "static" || node_position_style.toLowerCase() === "initial") {
            node_position_style = "relative";
        }

        container.setStyles({
            // (3) Quirks.
            display: node_display_style,
            position: node_position_style,

            // (4) Reset all styling that applies from border within.
            width: 'initial',
            height: 'initial',
            padding: '0px',
            'border-width': '0px'
        });

        this.get("node").insert(container, "before");

        return container;
    },

    /**
     * Create and setup attribute preview_node. preview_node  rest right inside container. Setting this up requires
     * us to copy the styling from node that affects from border within (opposite of the role of the container).
     *
     * The algorithm is similar to @see _create_and_setup_node_container.
     * We copy the style (done via cloneNode), and we deal with quirks, and nullify styles that applies outside the border.
     *
     * Note: we don't do all of the nullifying since some of the styles such as vertical-align have no effect in a case
     * of preview_node just sitting by itself (or with overlay which is position: absolute) in resize container.
     *
     * @returns {Y.Node} preview_node itself.
     * @private
     */
    _create_and_setup_preview_node: function () {
        var preview_node = this.get("node").cloneNode(true);
        this.set("preview_node", preview_node);

        // Remove id attribute on preview_node for sanity.
        preview_node.removeAttribute("id").generateID();

        preview_node.setStyles({
            margin: "0px",
            top: '0px',
            left: '0px'
        }, this);

        return preview_node;
    },

    /**
     * The goal is to ensure only one resizable:click event reaches the YUI's global space. This is done by each
     * click event handler stopping further propagation and then bubbling the event directly to the resizable class via
     * this.fire('resizable:click', ...). This way, we ensure that a single click in the resizable object represents one
     * event.
     *
     * @private
     */
    _setup_click_event_handler: function () {
        // Bubble up the click event from container to this resizable object.
        this.get("container").on("click", function (e) {
            e.stopPropagation();
            this.fire('resizable:click', {item: this});
        }, this);

        // Bubble up the click event from container's children to this resizable object.
        // todo: Make this all descendant that is not draggable. That criteria would be more robust.
        this.get("container").get("children").each(function (child) {
            child.on('click', function (e) {
                e.stopPropagation();
                this.fire('resizable:click', {item: this});
            }, this);
        }, this);
    },

    /**
     * Sets up the resize_overlay, the one responsible for image resizing.
     * @private
     */
    _setup_resize_overlay: function () {
        var resizable_overlay_template = Y.Handlebars.compile(Y.M.atto_image.get('resize_overlay_node_template'));
        var resizable_overlay_node = Y.Node.create(resizable_overlay_template({classes: ''}));
        var ghost_node = this.get("preview_node").cloneNode(true);
        ghost_node.removeAttribute("id").generateID();
        this.set("ghost_node", ghost_node);
        resizable_overlay_node.appendChild(ghost_node);

        ghost_node.set('className', "");  // Reset the classes.
        ghost_node.addClass('atto-image-ghost-node');

        this.get("container").appendChild(resizable_overlay_node);
        var resize_overlay = new Y.Overlay({
            srcNode: resizable_overlay_node,
            width: this.get("preview_node").getDOMNode().getBoundingClientRect().width,
            height: this.get("preview_node").getDOMNode().getBoundingClientRect().height,
            visible: true,
            render: true,
            zIndex: 1000,

            // Place overlay on top of each other.
            align: {node: this.get("container"), points: ["tl", "tl"]}
        });
        this.set("resize_overlay", resize_overlay);

        // Unlike other settings, we will not update this on the fly. There is no point of changing the available handles
        // while resizing. This differs to the min/max width/height settings.
        resize_overlay.plug(Y.Plugin.Resize, {
            handles: Y.M.atto_image.nesw_to_trbl(this.get('handle_config'))
        });

        resize_overlay.resize.plug(Y.Plugin.ResizeConstrained, {
            minWidth: this.get("min_width") + Y.M.atto_image.get_horizontal_non_content_width(ghost_node),
            minHeight: this.get("min_height") + Y.M.atto_image.get_vertical_non_content_width(ghost_node),
            maxWidth: this.get("max_width") + Y.M.atto_image.get_horizontal_non_content_width(ghost_node),
            maxHeight: this.get("max_height") + Y.M.atto_image.get_vertical_non_content_width(ghost_node),
            preserveRatio: this.get('preserve_ratio')
        }, this);

        // Ensure that resize events bubble up to the resizable object.
        this._set_resize_overlay_event_handlers();
    },

    /**
     * Sets up the overlay event handlers.
     * @private
     */
    _set_resize_overlay_event_handlers: function () {
        this.get('resize_overlay').resize.on('resize:start', function (e) {
            this.fire("resizable:resize:start", e);
            this.get("ghost_node").addClass('atto-image-ghost-node-active');
        }, this);

        this.get('resize_overlay').resize.on('resize:resize', function (e) {
            this.fire("resizable:resize:resize", e);
        }, this);

        this.get('resize_overlay').resize.on('drag:end', function (e) {
            this.get('resize_overlay').align();
            this.get("ghost_node").removeClass('atto-image-ghost-node-active');  // todo: Make this a method.

            var new_width =
                Y.M.atto_image.parseInt10(this.get('ghost_node').getComputedStyle("width")) -
                Y.M.atto_image.get_horizontal_non_content_width(this.get('ghost_node'));
            var new_height =
                Y.M.atto_image.parseInt10(this.get('ghost_node').getComputedStyle("height")) -
                Y.M.atto_image.get_vertical_non_content_width(this.get('ghost_node'));
            this.get('node').setStyles({ width: new_width + 'px', height: new_height + 'px'});

            var animate_enabled = this.get("animate") !== null;
            if (animate_enabled) {
                var resize_animation = new Y.Anim({
                    node: this.get("preview_node"),
                    to: { width: new_width + 'px', height: new_height + 'px' },
                    duration: this.get("animate").duration,
                    easing: this.get("animate").easing
                }, this);

                resize_animation.run();

                resize_animation.on('tween', function (e) { this.fire('resizable:tween', e); }, this);
            } else {
                this.get("preview_node").setStyles({ width: new_width + 'px', height: new_height + 'px'});
            }

            this.fire("resizable:resize:end", e);
        }, this);
    },

    /**
     * Removes the resize_overlay
     * @private
     */
    _destroy_resize_overlay: function () {
        if (this.get("resize_overlay")) {
            this.get("resize_overlay").destroy(true);  // Destroy all child nodes.
            this.set("resize_overlay", null);  // nullify.
        }
    },

    /**
     * Event handler for when the this.get("node") is deleted in the DOM Document. The callback in the handler
     * will also delete the resize handle controls, specifically, this.get("container").
     * @private
     */
    _handle_node_removal_handler: function () {
        var self = this;
        var mutation_observer = new MutationObserver(function (mutations) {

            if (typeof self.get("node") === "undefined" || self.get("node") === null) {
                return;
            }

            mutations.forEach(function (mutation) {
                if (mutation.removedNodes.length === 0) { return; }  // No point.
                if (typeof self.get("node") === "undefined" || self.get("node") === null) { return; }  // Sanity check.

                var mutation_index = [].indexOf.call(mutation.removedNodes, self.get("node").getDOMNode());
                if (mutation_index >= 0) {
                    // Only proceed if the event is being called for the this.get("node")_container not
                    // some of children elements.
                    if (self.get("node").getDOMNode() &&
                        mutation.removedNodes[mutation_index] !== self.get("node").getDOMNode()) {
                        return;
                    }

                    // Don't proceed if disable() function have just been called. If it was called, acknowledge
                    // (by setting this.disable_called = false) and exit.
                    // @see disable_called for explanation on why.
                    if (self.get("disable_called")) {
                        Y.log('Node is removed, so is container', 'debug', 'resizable::_handle_node_removal');
                        self.set("disable_called", false);
                        return;
                    }

                    self.delete_all();
                }
            });
        });

        var config = {childList: true, subtree: true};
        mutation_observer.observe(self.get("node").ancestor().getDOMNode(), config);
    },

    /**
     * When r.enable() is called on r (r is resizable object), the original Y.Node is hidden
     * and a resize-container is shown. Thus deleting delete at that point will delete resize-container
     * but not the r.node. To fix this, a mutation listener is established to listen for deletion.
     *
     * @private
     */
    _handle_node_container_removal_handler: function() {
        var self = this;
        var config = {childList: true, subtree: true};

        /**
         * In this MutationObserver, we monitor the deletion of the container itself. In order to do that, we monitor
         * the parent, to monitor if we are deleted. For some reason, the root is not included, just all of its children.
         */
        (new MutationObserver(function (mutations) {
            // Don't proceed if disable() function have just been called. If it was called, acknowledge
            // (by setting this.disable_called = false) and exit.
            // @see disable_called for explanation on why.
            if (self.get("disable_called")) { return; }

            mutations.forEach(function (mutation) {
                if (mutation.removedNodes.length === 0) { return; }  // No point.
                if (typeof self.get("container") === "undefined" || self.get("container") === null) { return; }  // Sanity check.

                var mutation_index = [].indexOf.call(mutation.removedNodes, self.get("container").getDOMNode());
                if (mutation_index >= 0) {
                    // Only proceed if the event is being called for the this.get("node")_container not
                    // some of children elements.
                    if (self.get("container").getDOMNode() &&
                        mutation.removedNodes[mutation_index] !== self.get("container").getDOMNode()) {
                        return;
                    }

                    // Don't proceed if disable() function have just been called. If it was called, acknowledge
                    // (by setting this.disable_called = false) and exit.
                    // @see disable_called for explanation on why.
                    if (self.get("disable_called")) {
                        self.set("disable_called", false);
                        return;
                    }

                    self.delete_all();
                }
            });

            // Don't proceed if disable() function have just been called. If it was called, acknowledge
            // (by setting this.disable_called = false) and exit.
            // @see disable_called for explanation on why.
            if (self.get("disable_called")) { self.set("disable_called", false); }
        })).observe(self.get("container").ancestor().getDOMNode(), config);

        /**
         * True if both node_container and node are deleted. We don't really need one since delete_all have sanity
         * checks, but mutation observer is called for each of resize container's children. It's good to just do it once.
         *
         * @type {boolean}
         */
        var delete_all_called = false;

        /**
         * Monitor the deletion of all the containers descendants. This is different from the first MutationObserver
         * in we don't monitor the container itself. We only monitor the Container's child.
         */
        (new MutationObserver(function (mutations) {
            // Don't proceed if disable() function have just been called. If it was called, acknowledge
            // (by setting this.disable_called = false) and exit.
            // @see disable_called for explanation on why.
            if (self.get("disable_called")) {
                return;
            }

            mutations.forEach(function (mutation) {
                if (mutation.removedNodes.length === 0) { return; }  // No point.

                if (!delete_all_called) {
                    self.delete_all();
                    delete_all_called = true;
                }
            });

            // Don't proceed if disable() function have just been called. If it was called, acknowledge
            // (by setting this.disable_called = false) and exit.
            // @see disable_called for explanation on why.
            if (self.get("disable_called")) { self.set("disable_called", false); }
        })).observe(self.get("container").getDOMNode(), config);
    }
}, {
    NAME: "resizable",

    ATTRS: {
        /**
         * Reference to the Y.Node being resized. Becomes null when the node_cache and/or node is deleted in the
         * document.
         *
         * @property node
         * @type {null|Y.Node}
         * @required
         * @default null
         * @writeOnce
         * @public
         */
        node: {
            value: null,

            validator: function (val) {
                var valid = val === null || (val instanceof Y.Node);
                if (!valid) {
                    console.error('Given node to encapsulate is not of appropriate type.');
                }
                return valid;
            },
            writeOnce: true
        },

        /**
         * Reference to the "node"'s clone to be shown inside this.get("container") when this.enable() is
         * called.
         *
         * @property _node_copy
         * @type {null|Y.Node}
         * @default null
         * @private
         */
        preview_node: {
            value: null,

            validator: function (val) {
                var valid = val === null || (val instanceof Y.Node);
                if (!valid) {
                    console.error("Given _node_copy is not of appropriate type.");
                }
                return valid;
            }
        },

        /**
         * This is also a clone of "node"'s, but this is shown inside the overlay to preview the possible dimension
         * while reszing.
         */
        ghost_node: {
            value: null,

            validator: function (val) {
                var valid = val === null || (val instanceof Y.Node);
                if (!valid) {
                    console.error("Given _ghost_copy is not of appropriate type.");
                }
                return valid;
            }
        },

        /**
         * Null when this.enable() is not called, or this.disable() is called. Otherwise, represents the
         * this.get("node") when this.enable() is called (at this time this.get("node") is hidden).
         * Specifically, this encapsulates this.get("preview_node"), so we can also position the handles (you can't
         * place anything inside img tag).
         *
         * @property container
         * @type {null|Y.Node}
         * @default null
         * @private
         */
        container: {
            value: null,

            validator: function (val) {
                var valid = val === null || (val instanceof Y.Node);
                if (!valid) {
                    console.error('Given container is not of valid type');
                }
                return valid;
            }
        },

        /**
         * A helper property, which is set to true when this._disable() (Note, not this.disable()) was just called.
         * To document this state variable properly, consider the Problem and Solution section below.
         *
         * Problem:
         * this.get("node") is invisible when resizing. What is shown instead is this.get("container") and
         * this.get("preview_node"). When deleting this.get("container") (via delete key in keyboard),
         * MutationObserver, is used to listen to the deletion, so we can delete this.get("node") in the process.
         *
         * Another instance in which this.get("node") is deleted is when this.disable (and in turn this._disable)
         * is called, in which case, in which case we don't want to delete the this.get("node"). But this very same
         * event still calls the callback given to MutationObserver. What we need is a way to know whether this.disable
         * was just called in order to know whether we want to delete this.get("node") or simply want to get rid of
         * this.yui_container since we are done resizing.
         *
         * Solution:
         * To avoid this, _disabled_called is set to true when this.disable (and in turn this._disable) is called, thus
         * we have a way in MutationObserver to know if we just called this.disable, in which case we don't delete
         * this.get("node").
         *
         * @property disable_called
         * @type boolean
         * @default false
         * @private
         */
        disable_called: {
            value: false,
            validator: function (val) {
                return typeof val === "boolean";
            }
        },

        /**
         * A state variable that is true when this.disable() is called, and false when this.enable is called. This is
         * different from this.get("_disabled_called") because this doesn't indicate whether this.disabled was just
         * called. The purpose of this is so this.disable won't call this._disable twice and this.enable won't call
         * this._enable twice.
         *
         * Note: Although initializer calls this.enable by default, this.enable calls this._enable if and only if
         *       disabled is true (this is so it won't call this._enable again if it knows resize controls are
         *       already shown or _disable is false).
         *
         * @property disabled
         * @type boolean
         * @optional
         * @private
         */
        disabled: {
            value: true,

            validator: function (val) {
                var valid = typeof val === "boolean";
                if (!valid) {
                    console.error("disabled property is given non boolean value.");
                }
                return valid;
            }
        },

        /**
         * The main tool for resizing the node to be resized.
         * @see http://yuilibrary.com/yui/docs/resize/simple-resize-plugin.html
         *
         * @property resize_overlay
         * @type {null|Y.Overlay}
         * @public
         */
        resize_overlay: {
            value: null,

            validator: function (val) {
                var valid = val === null || val instanceof Y.Overlay;
                if (!valid) {
                    console.error("Given resize_overlay is invalid.");
                }
                return valid;
            }
        },

        /**
         * A readonly attribute that shows available handles to be used. To use a subset of this, set handles property.
         *
         * @property available_handles
         * @type Array
         * @readOnly
         * @public
         */
        available_handles: {
            value: ['nw', 'ne', 'sw', 'se', 'n', 's', 'e', 'w'],
            readOnly: true
        },

        /**
         * An array containing the handle position to activate. By default, all of them are activated.
         *
         * @property handles
         * @type {Array}
         * @optional
         * @default ['nw', 'ne', 'sw', 'se', 'n', 's', 'e', 'w']
         * @public
         */
        handle_config: {
            valueFn: function () {
                return this.get("available_handles");
            },

            setter: function (val) {
                if (typeof val === "string") {
                    val = val.split(",").map(function (handle) {
                        return handle.trim();
                    });
                }

                return val;
            },

            validator: function (val) {
                var self = this;

                if (typeof val === "string") {
                    if (val.split(",").length < 1) {
                        console.error('Please specify at least one handle.');
                        return false;
                    }

                    val.split(",").forEach(function (handle) {
                        handle = handle.trim();
                        if (self.get("available_handles").indexOf(handle) === -1) {
                            console.error('One of the given handle, ' + handle + 'is not valid. ' +
                                'Call resizable.get("available_handles") for list of available handles.');
                            return false;
                        }
                    });
                } else if (val instanceof Array) {
                    if (val.length < 1) {
                        console.error('Please specify at least one handle.');
                        return false;
                    }

                    val.forEach(function (handle) {
                        handle = handle.trim();
                        if (self.get("available_handles").indexOf(handle) === -1) {
                            console.error('One of the given handle, ' + handle + 'is not valid. ' +
                                'Call resizable.get("available_handles") for list of available handles.');
                            return false;
                        }
                    });
                } else {
                    console.error('Given handles is not an instance of Array or string.');
                    return false;
                }

                return true;
            }
        },

        /**
         * NOTE: For all min/max width/height, I'm not doing validation. This is because, min width/height will
         * check max width/height, and by doing so max width/height also initialize min width/height.
         * These causes a cyclic-dependency problem. Sure I could have some state variable to keep track
         * of things, but that is too much for this simple thing.
         */

        /**
         * Minimum width of node.
         * @property min_width
         * @type Number
         * @public
         */
        min_width: {
            value: Y.M.atto_image.get("default_min_width"),

            setter: function (min_width) {
                // If resize overlay exist already, set its min width. It will be set later otherwise.
                if (this.get("resize_overlay")) { this.get("resize_overlay").resize.con.set("minWidth", min_width); }
                return min_width;
            }
        },

        /**
         * Minimum height of node.
         * @property min_height
         * @type Number
         * @public
         */
        min_height: {
            value: Y.M.atto_image.get("default_min_height"),

            setter: function (min_height) {
                // If resize overlay exist already, set its min height. It will be set later otherwise.
                if (this.get("resize_overlay")) { this.get("resize_overlay").resize.con.set("minHeight", min_height); }
                return min_height;
            }
        },

        /**
         * Maximum width of node. Defaults to null, meaning infinite.
         *
         * @property max_width
         * @type Number
         * @public
         */
        max_width: {
            value: Y.M.atto_image.get("default_max_width"),

            setter: function (max_width) {
                // If resize overlay exist already, set its max width. It will be set later otherwise.
                if (this.get("resize_overlay")) { this.get("resize_overlay").resize.con.set("maxWidth", max_width); }
                return max_width;
            }
        },

        /**
         * Maximum height of node. Defaults to null, meaning infinite.
         *
         * @property max_height
         * @type Number
         * @public
         */
        max_height: {
            value: Y.M.atto_image.get("default_max_height"),

            setter: function (max_height) {
                // If resize overlay exist already, set its max height. It will be set later otherwise.
                if (this.get("resize_overlay")) { this.get("resize_overlay").resize.con.set("maxHeight", max_height); }
                return max_height;
            }
        },

        /**
         * If preserve_aspect_ratio is set, this must also be set. From this data we will gather the aspect ratio that
         * will have to be maintained when preserve_aspect_ratio is set. Although there is a proper way of acquiring
         * the size of img elements, some other elements like div don't. Since the aim of this plugin is for all elements
         * (although img is first in mind), this must be manually set.
         *
         * Note: We assume that the unit is in px. So omit the unit.
         *
         * @property node_size
         * @type {{width: , height: }}
         * @optional
         * @default null
         * @public
         */
        node_size: {
            value: null,

            /**
             * node_size is either all null, or contains width/height.
             */
            validate: function (val) {
                // Not checking for null since I'm just trying to avoid error in Y.Lang.isNumber being passed undefined.
                var valid = ((val === null) ||
                (typeof val.width !== "undefined" &&
                typeof val.height !== "undefined" &&
                Y.Lang.isNumber(val.width) &&
                Y.Lang.isNumber(val.height)));
                if (!valid) {
                    console.error('Given node_size is invalid. Please check the passed object.');
                }
                return valid;
            }
        },


        /**
         * Defaults to false, allowing user to set the size freely. Set this to true to restrict the aspect ratio of the
         * node.
         *
         * @property preserve_aspect_ratio
         * @type {boolean}
         * @optional
         * @default false
         * @public
         */
        preserve_aspect_ratio: {
            value: false,
            // I'm not gonna validate this. I'll go with truthiness value, or the bool value when !! is applied.

            setter: function (preserve_aspect_ratio) {
                preserve_aspect_ratio = !!preserve_aspect_ratio;  // Get truthiness val.

                if (this.get("resize_overlay")) {
                    this.get("resize_overlay").resize.con.set("preserveRatio", preserve_aspect_ratio);
                }

                return preserve_aspect_ratio;
            }
        },

        /**
         * Set to null to disable animation.
         *
         * @property animate
         * @type {null|object}
         * @optional
         * @default { duration: 0.4, easing: 'easingBoth' }
         * @public
         */
        animate: {
            valueFn: function() {
                return {
                    duration: 0.4,
                    easing: 'easeBoth'
                };
            },

            validator: function(val) {
                var valid = val === null || (typeof val === "object" && val.duration && val.easing);
                if (!valid) { console.error('Given animate object is invalid.'); }
                return valid;
            }
        }
    }
});

/**
 * @return true if compatible, false if maybe or no.
 */
Y.namespace('M.atto_image').resizable.is_compatible = function() {
    /**
     * The following are considered:
     * @see https://developer.mozilla.org/en/docs/Web/API/MutationObserver
     */
    return Y.M.atto_image.is_mutation_observer_supported();
};// This file is part of Moodle - http://moodle.org/
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

/*
 * @package    atto_image
 * @copyright  2013 Damyon Wiese  <damyon@moodle.com>
 * @copyright  2015 Joey Andres  <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module moodle-atto_image_alignment-button
 */

/**
 * Atto image selection tool.
 *
 * @namespace M.atto_image
 * @class Button
 * @extends M.editor_atto.EditorPlugin
 */

/**
 * Declare available keys.
 */
Y.Node.DOM_EVENTS.key.eventDef.KEY_MAP.shift = 16;
Y.Node.DOM_EVENTS.key.eventDef.KEY_MAP.ctrl = 17;
Y.Node.DOM_EVENTS.key.eventDef.KEY_MAP.alt = 18;

Y.namespace('M.atto_image').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {
    /**
     * A reference to the current selection at the time that the dialogue
     * was opened.
     *
     * @property _currentSelection
     * @type Range
     * @private
     */
    _currentSelection: null,

    /**
     * The most recently selected image.
     *
     * @param _selectedImage
     * @type Node
     * @private
     */
    _selectedImage: null,

    /**
     * A reference to the currently open form.
     *
     * @param _form
     * @type Node
     * @private
     */
    _form: null,

    /**
     * The dimensions of the raw image before we manipulate it.
     *
     * @param _rawImageDimensions
     * @type Object
     * @private
     */
    _rawImageDimensions: null,

    /**
     * Reference to the resizable object. Null if nothing is selected. Otherwise, contains the resizable object
     * of the currently selected.
     *
     * @param _resizable
     * @type {null|Y.atto_image.resizable}
     */
    _resizable: null,

    /**
     * Reference to the option_overlay_node_wrapper. Null if no option_overlay.
     *
     * @param _option_overlay_node_wrapper
     * @type {null|Y.atto_image.resizable}
     */
    _option_overlay_node_wrapper: null,

    initializer: function() {
        this.addButton({
            icon: 'e/insert_edit_image',
            callback: this._displayDialogue,
            tags: 'img',
            tagMatchRequiresAll: false
        });
        
        this.editor.delegate('dblclick', this._displayDialogue, 'img', this);
        this.editor.delegate('click', this._handleClick, 'img', this);

        // Deselect the currently selected when something not an image is selected.
        // Cases when other image (not currently selected) is selected are handled in _handleClick too.
        this.editor.delegate('click', this._handleDeselect, ':not(img)', this);

        // Handle what to do when copying. This is to catch the cases in which there are resizable handles on the
        // editor.
        this.editor.before(['copy', 'cut'], this._before_copy_cut_handler, this);

        Y.on('resizable:click', this._resizable_click_handler, this);
        Y.on('resizable:init', this._resizable_init_handler, this);

        var toggle_key_preserve_aspect_ratio = this.get('toggle_key_preserve_aspect_ratio');
        this.editor.delegate('key', this._toggle_key_down_preserve_aspect_ratio,
            'down:' + toggle_key_preserve_aspect_ratio, null, this);
        this.editor.delegate('key', this._toggle_key_up_preserve_aspect_ratio,
            'up:' + toggle_key_preserve_aspect_ratio, null, this);
    },

    /**
     * Handle a click on an image.
     * Note: Precondition: e.target is 'img'.
     *
     * @method _handleClick
     * @param {EventFacade} e
     * @private
     */
    _handleClick: function(e) {
        var image = e.target;

        this._ensure_only_one_selected(image);

        var resize_cfg = this._assemble_resize_config();
        resize_cfg.node = image;  // Node to be selected.
        resize_cfg.disabled = false;  // Enable/show resize controls after constructor.
        resize_cfg.node_size = Y.M.atto_image.get_natural_image_size(image.get('src'));

        // Pardon this ugly sight: A bug in YUI. @see https://github.com/yui/yui3/issues/1043
        // Should've been inside resizable if not to this bug.
        resize_cfg.after = { init: function() { this.fire('resizable:init', {}); } };

        this._resizable = new Y.M.atto_image.resizable(resize_cfg);

        // Disable dragging since the "real" node is hidden, which can't be drag (dragging results in erroneous
        // situation). What we would've been dragging is the container that contains the handles and other resizing
        // elements.
        // Note: You can still drag, just not while resizing.  A bit of a bummer, but good enough for now.
        this._resizable.get("container").before("dragstart", function(d){ d.halt(true); }, this);

        // Prevent further bubbling the DOM tree.
        // @see http://yuilibrary.com/yui/docs/event/#facade-properties
        // Without this, this will propagate up (bubble) and will hit the textarea, thus calling _handleDeselect,
        // immediately deselecting anything.
        Y.log("KILLING PROPAGATION", 'debug', 'resizable');
        e.halt(true);
    },

    /**
     * Deselect event handler.
     *
     * @param {EventTarget} e
     * @private
     */
    _handleDeselect: function(e) {
        void(e);
        if (this._resizable) {
            // Select nothing, destroy options overlay, and destroy the resizable object.
            this.get('host').setSelection([]);
            this._destroy_options_overlay();
            this._resizable.destroy();
            this._resizable = null;
        }
    },

    /**
     * Resizable click handler.
     *
     * @param {EventTarget} e
     * @private
     */
    _resizable_click_handler: function(e) {
        // If something was selected, deselect it. (Both range and resizable selection).
        if (e.target !== this._resizable) {
            if (this._resizable) {
                this.get('host').setSelection([]);  // Clear selection (rangy).
                this._resizable.destroy();
                this._resizable = null;
                this._destroy_options_overlay();
            }

            this._resizable = e.target;
            this._resizable.enable();
            this.get('host').setSelection([]);  // Clear selection (rangy).
            var container_selection = this.get('host').getSelectionFromNode(this._resizable.get("container"));
            this.get('host').setSelection(container_selection);  // Set selection (rangy).
            this._setup_options_overlay();

            // Remove options overlay when resizable is destroyed.
            this._resizable.after("destroy", function(e) {
                void(e);
                if (this._resizable) {
                    this.get('host').setSelection([]);
                    this._destroy_options_overlay();
                    this._resizable.destroy();
                    this._resizable = null;
                }
            }, this);
        }
    },

    /**
     * Resizable init handler. Just aggregates resizable click handler.
     *
     * @param {EventTarget} e
     * @private
     */
    _resizable_init_handler: function(e) {
        this._resizable_click_handler(e);
    },

    /**
     * Event handler before copy event occurs. This is need to modify atto's content editable, getting rid of
     * resize handles DOM elements.
     *
     * Algorithm Summary:
     *
     * 1. Get the current selection. This will be in array of rangy objects.
     * 2. For each of the selection/rangy object, see if resizable container is in it.
     *    i.) If resizable container is in it: See if resizable container is in the start,end, both, or middle of selection/rangy.
     *        a.) start only: Get rid of the resize container, change the start of selection to the img being resized.
     *        b.) end only: Get rid of the resize container, change the end of selection to the img being resized.
     *        c.) start and end (both): Get rid of the resize container.
     *            x.) if only resize container is really selected, set selection to the image only.
     *            xx.) otherwise, copy start and end container (the same anyway), but end container with x less offset
     *                 to account for deleted components.
     *            Note: xx only happens at firefox.
     *        d.) Middle: Get rid of the resize container. Create a new rangy, copy the start and end selection from before.
     *    ii.) Resizable container is NOT in it: Keep the old rangy/selection.
     * 3. Go back to (2) until no more rangy/selection.
     * 4. The new array of selection from (2) will now be set as the new selection.
     *
     * Note: I scanned through the rangy API, I can't believe this functionality is not there. I thought that modifying
     *       the DOMNode, and update the selection should be have a written routine already.
     *
     * @param {EventTarget} e
     * @private
     */
    _before_copy_cut_handler: function(e) {
        void(e);
        if (this._resizable) {
            var old_rangy_arr = this.get('host').getSelection();

            // See if the start/end node contains _resizable stuff.
            var new_range_arr = old_rangy_arr.map(function (old_range) {
                // If resizable exist and selection contains the resizable, we must modify that selection to
                // not include the resizable.
                // Otherwise, just return the unmodified range.
                if (this._resizable &&
                    this.get('host').selectionContainsNode(this._resizable.get('container'))) {

                    // See if resize container is start_node or contains start_node.
                    var start = Y.one(old_range.startContainer).compareTo(this._resizable.get("container")) ||
                        this._resizable.get("container").contains(Y.one(old_range.startContainer)) ||
                        Y.one(old_range.startContainer).contains(this._resizable.get("container"));
                    var end = Y.one(old_range.endContainer).compareTo(this._resizable.get("container")) ||
                        this._resizable.get("container").contains(Y.one(old_range.endContainer)) ||
                        Y.one(old_range.endContainer).contains(this._resizable.get("container"));

                    var pre_start_children_count = Y.one(old_range.startContainer).get("children").size();
                    var node = this._resizable.get("node");
                    this._resizable.destroy();
                    this._resizable = null;

                    var new_range = rangy.createRange();
                    if (start && end) {
                        var only_resizable_selected = Math.abs(old_range.endOffset - old_range.startOffset) === 1;
                        if (only_resizable_selected) {
                            new_range = this.get('host').getSelectionFromNode(node)[0];
                        } else {
                            /**
                             * (The following is just for the next developer not convinced).
                             * Proof that if start and end contains container node, then start = end.
                             *
                             * (proof by contradiction).
                             * Suppose start and end contains container node but start != end, then
                             * container is a text node, or container belongs to two element (start and end).
                             *
                             * Since container != text node, nor can any div belong to two parent (same level in DOM),
                             * a contradiction. Therefore, start = end.
                             */

                            var post_start_children_count = Y.one(old_range.startContainer).get("children").size();
                            var diff = Math.abs(post_start_children_count - pre_start_children_count);

                            new_range.setStart(old_range.startContainer, old_range.startOffset);
                            new_range.setEnd(old_range.endContainer, old_range.endOffset-diff);
                        }
                    } else if (start) {
                        new_range.setStartBefore(node.getDOMNode());
                        new_range.setEnd(old_range.endContainer, old_range.endOffset);
                    } else if (end) {
                        new_range.setStart(old_range.startContainer, old_range.startOffset);
                        new_range.setEndAfter(node.getDOMNode());
                    } else {
                        new_range.setStart(old_range.startContainer, old_range.startOffset);
                        new_range.setEnd(old_range.endContainer, old_range.endOffset);
                    }

                    return new_range;
                }

                return old_range;
            }, this);

            this.get('host').setSelection(new_range_arr);  // Set selection (rangy).
        }
    },

    /**
     * Preserve aspect ratio key down event handler.
     *
     * @param {EventTarget} e
     * @private
     */
    _toggle_key_down_preserve_aspect_ratio: function(e) {
        void(e);
        if (this._resizable) { this._resizable.set('preserve_aspect_ratio', true); }
    },

    /**
     * Preserve aspect ratio key up event handler.
     *
     * @param {EventTarget} e
     * @private
     */
    _toggle_key_up_preserve_aspect_ratio: function(e) {
        void(e);
        if (this._resizable) { this._resizable.set('preserve_aspect_ratio', false); }
    },

    /**
     * Assemble the resize configuration to be passed to the _handleClick. Note some of these settings are set
     * from settings.php via plugin settings page.
     *
     * @returns {{handle_config: *, min_width: *, min_height: *, max_width: *, max_height: *}}
     * @private
     */
    _assemble_resize_config: function() {
        var animate = null;
        if (this.get('resize_animation_enable')) {
            animate = {
                duration: this.get('resize_animation_duration'),
                easing: this.get('resize_animation_easing')
            };
        }

        return {
            handle_config: this.get("handle_config"),
            min_width: this.get("minmaxwidthheight").min_width,
            min_height: this.get("minmaxwidthheight").min_height,
            max_width: this.get("minmaxwidthheight").max_width,
            max_height: this.get("minmaxwidthheight").max_height,
            animate: animate
        };
    },

    /**
     * @param {Y.Node} image Image to made sure the only one selected.
     * @private
     */
    _ensure_only_one_selected: function (image) {
        var img_selection_obj = this.get('host').getSelectionFromNode(image);
        this.get('host').setSelection(img_selection_obj);
    },

    /**
     * @param {Y.Node} image
     * @returns {boolean} true if the given image node is the only one selected.
     * @private
     */
    _is_only_image_selected: function (image) {
        var img_selection_obj = this.get('host').getSelectionFromNode(image);
        return this.get('host').getSelection() === img_selection_obj;
    },

    /**
     * Called to create the options overlay. It is assumed that this._resizable is not null.
     * @returns {null|Y.atto_image.resizable}
     * @private
     */
    _setup_options_overlay: function() {
        // It is assumed that resizable is already created.
        if (this._resizable === null) { return; }

        var overlay = new Y.M.atto_image.overlay({
            align_node: this._resizable.get("container"),
            decorator: [
                new Y.M.atto_image.vertical_align_decorator({ 'node': this._resizable.get("node") }),
                new Y.M.atto_image.float_decorator({ 'node': this._resizable.get("node") }),
                new Y.M.atto_image.border_decorator({ 'node': this._resizable.get("node") }),
                new Y.M.atto_image.spacing_decorator({ 'node': this._resizable.get("node") }),
                new Y.M.atto_image.custom_decorator({
                    'node': this._resizable.get("node"),
                    'disable_custom_classes': this.get("disable_custom_classes")
                })
            ]
        }, this);
        overlay.decorate();

        // @see utility.js overlay_node_wrapper for explanation why we are wrapping the overlay.
        this._option_overlay_node_wrapper = Y.Node.create(
            Y.Handlebars.compile(Y.M.atto_image.get('option_overlay_node_wrapper_template'))({classes: ''}));
        this.get('host').editor.appendChild(this._option_overlay_node_wrapper);  // So _option_overlay_node_wrapper scrolls with atto.
        overlay.set('overlay_node_wrapper', this._option_overlay_node_wrapper);

        // Make overlay align every after resizable:resize:end since the node we are aligned to only change every after resize-end.
        this._resizable.after(["resizable:tween", "resizable:resize:end"], function(e) {
            void(e);
            overlay.align();
        }, this);

        Y.on("windowresize", function(e) {
            void(e);
            overlay.align();
        }, this);

        overlay.render();  // Show overlay.

        return this._option_overlay_node_wrapper;
    },

    _destroy_options_overlay: function() {
        if (this._option_overlay_node_wrapper) {
            this._option_overlay_node_wrapper.remove(true);
            this._option_overlay_node_wrapper = null;
        }
    },

    /**
     * Display the image editing tool.
     *
     * @method _displayDialogue
     * @private
     */
    _displayDialogue: function() {
        if (this._resizable) {
            var node_selection = this.get('host').getSelectionFromNode(this._resizable.get("node"));
            this.get('host').setSelection(node_selection);
        }

        // Store the current selection.
        this._currentSelection = this.get('host').getSelection();
        if (this._currentSelection === false) {
            return;
        }

        // Reset the image dimensions.
        this._rawImageDimensions = null;

        var dialogue = this.getDialogue({
            headerContent: M.util.get_string('imageproperties', COMPONENTNAME),
            width: '480px',
            focusAfterHide: true,
            focusOnShowSelector: SELECTORS.INPUTURL
        });

        // Set the dialogue content, and then show the dialogue.
        dialogue.set('bodyContent', this._getDialogueContent())
                .show();
    },

    /**
     * Set the inputs for width and height if they are not set, and calculate
     * if the constrain checkbox should be checked or not.
     *
     * @method _loadPreviewImage
     * @param {String} url
     * @private
     */
    _loadPreviewImage: function(url) {
        var image = new Image(), self = this;

        image.onerror = function() {
            var preview = self._form.one('.' + CSS.IMAGEPREVIEW);
            preview.setStyles({
                'display': 'none'
            });

            // Centre the dialogue when clearing the image preview.
            self.getDialogue().centerDialogue();
        };

        image.onload = function() {
            var input, currentwidth, currentheight, widthRatio, heightRatio;

            self._rawImageDimensions = {
                width: this.width,
                height: this.height
            };

            input = self._form.one('.' + CSS.INPUTWIDTH);
            currentwidth = input.get('value');
            if (currentwidth === '') {
                input.set('value', this.width);
                currentwidth = "" + this.width;
            }
            input = self._form.one('.' + CSS.INPUTHEIGHT);
            currentheight = input.get('value');
            if (currentheight === '') {
                input.set('value', this.height);
                currentheight = "" + this.height;
            }
            input = self._form.one('.' + CSS.IMAGEPREVIEW);
            input.setAttribute('src', this.src);
            input.setStyles({
                'display': 'inline'
            });

            input = self._form.one('.' + CSS.INPUTCONSTRAIN);
            if (currentwidth.match(REGEX.ISPERCENT) && currentheight.match(REGEX.ISPERCENT)) {
                input.set('checked', currentwidth === currentheight);
            } else {
                if (this.width === 0) {
                    this.width = 1;
                }
                if (this.height === 0) {
                    this.height = 1;
                }
                // This is the same as comparing to 3 decimal places.
                widthRatio = Math.round(1000*parseInt(currentwidth, 10) / this.width);
                heightRatio = Math.round(1000*parseInt(currentheight, 10) / this.height);
                input.set('checked', widthRatio === heightRatio);
            }

            // Apply the image sizing.
            self._autoAdjustSize(self);

            // Centre the dialogue once the preview image has loaded.
            self.getDialogue().centerDialogue();
        };

        image.src = url;
    },

    /**
     * Return the dialogue content for the tool, attaching any required
     * events.
     *
     * @method _getDialogueContent
     * @return {Node} The content to place in the dialogue.
     * @private
     */
    _getDialogueContent: function() {
        var template = Y.Handlebars.compile(TEMPLATE),
            canShowFilepicker = this.get('host').canShowFilepicker('image'),
            content = Y.Node.create(template({
                elementid: this.get('host').get('elementid'),
                CSS: CSS,
                component: COMPONENTNAME,
                showFilepicker: canShowFilepicker,
                alignments: ALIGNMENTS
            }));

        this._form = content;

        // Configure the view of the current image.
        this._applyImageProperties(this._form);

        this._form.one('.' + CSS.INPUTURL).on('blur', this._urlChanged, this);
        this._form.one('.' + CSS.IMAGEPRESENTATION).on('change', this._updateWarning, this);
        this._form.one('.' + CSS.INPUTALT).on('change', this._updateWarning, this);
        this._form.one('.' + CSS.INPUTWIDTH).on('blur', this._autoAdjustSize, this);
        this._form.one('.' + CSS.INPUTHEIGHT).on('blur', this._autoAdjustSize, this, true);
        this._form.one('.' + CSS.INPUTCONSTRAIN).on('change', function(event) {
            if (event.target.get('checked')) {
                this._autoAdjustSize(event);
            }
        }, this);
        this._form.one('.' + CSS.INPUTURL).on('blur', this._urlChanged, this);
        this._form.one('.' + CSS.INPUTSUBMIT).on('click', this._setImage, this);

        if (canShowFilepicker) {
            this._form.one('.' + CSS.IMAGEBROWSER).on('click', function() {
                    this.get('host').showFilepicker('image', this._filepickerCallback, this);
            }, this);
        }

        return content;
    },

    _autoAdjustSize: function(e, forceHeight) {
        forceHeight = forceHeight || false;

        var keyField = this._form.one('.' + CSS.INPUTWIDTH),
            keyFieldType = 'width',
            subField = this._form.one('.' + CSS.INPUTHEIGHT),
            subFieldType = 'height',
            constrainField = this._form.one('.' + CSS.INPUTCONSTRAIN),
            keyFieldValue = keyField.get('value'),
            subFieldValue = subField.get('value'),
            imagePreview = this._form.one('.' + CSS.IMAGEPREVIEW),
            rawPercentage,
            rawSize;

        // If we do not know the image size, do not do anything.
        if (!this._rawImageDimensions) {
            return;
        }

        // Set the width back to default if it is empty.
        if (keyFieldValue === '') {
            keyFieldValue = this._rawImageDimensions[keyFieldType];
            keyField.set('value', keyFieldValue);
            keyFieldValue = keyField.get('value');
        }

        // Clear the existing preview sizes.
        imagePreview.setStyles({
            width: null,
            height: null
        });

        // Now update with the new values.
        if (!constrainField.get('checked')) {
            // We are not keeping the image proportion - update the preview accordingly.

            // Width.
            if (keyFieldValue.match(REGEX.ISPERCENT)) {
                rawPercentage = parseInt(keyFieldValue, 10);
                rawSize = this._rawImageDimensions.width / 100 * rawPercentage;
                imagePreview.setStyle('width', rawSize + 'px');
            } else {
                imagePreview.setStyle('width', keyFieldValue + 'px');
            }

            // Height.
            if (subFieldValue.match(REGEX.ISPERCENT)) {
                rawPercentage = parseInt(subFieldValue, 10);
                rawSize = this._rawImageDimensions.height / 100 * rawPercentage;
                imagePreview.setStyle('height', rawSize + 'px');
            } else {
                imagePreview.setStyle('height', subFieldValue + 'px');
            }
        } else {
            // We are keeping the image in proportion.
            if (forceHeight) {
                // By default we update based on width. Swap the key and sub fields around to achieve a height-based scale.
                var _temporaryValue;
                _temporaryValue = keyField;
                keyField = subField;
                subField = _temporaryValue;

                _temporaryValue = keyFieldType;
                keyFieldType = subFieldType;
                subFieldType = _temporaryValue;

                _temporaryValue = keyFieldValue;
                keyFieldValue = subFieldValue;
                subFieldValue = _temporaryValue;
            }

            if (keyFieldValue.match(REGEX.ISPERCENT)) {
                // This is a percentage based change. Copy it verbatim.
                subFieldValue = keyFieldValue;

                // Set the width to the calculated pixel width.
                rawPercentage = parseInt(keyFieldValue, 10);
                rawSize = this._rawImageDimensions.width / 100 * rawPercentage;

                // And apply the width/height to the container.
                imagePreview.setStyle('width', rawSize);
                rawSize = this._rawImageDimensions.height / 100 * rawPercentage;
                imagePreview.setStyle('height', rawSize);
            } else {
                // Calculate the scaled subFieldValue from the keyFieldValue.
                subFieldValue = Math.round((keyFieldValue / this._rawImageDimensions[keyFieldType]) *
                        this._rawImageDimensions[subFieldType]);

                if (forceHeight) {
                    imagePreview.setStyles({
                        'width': subFieldValue,
                        'height': keyFieldValue
                    });
                } else {
                    imagePreview.setStyles({
                        'width': keyFieldValue,
                        'height': subFieldValue
                    });
                }
            }

            // Update the subField's value within the form to reflect the changes.
            subField.set('value', subFieldValue);
        }
    },

    /**
     * Update the dialogue after an image was selected in the File Picker.
     *
     * @method _filepickerCallback
     * @param {object} params The parameters provided by the filepicker
     * containing information about the image.
     * @private
     */
    _filepickerCallback: function(params) {
        if (params.url !== '') {
            var input = this._form.one('.' + CSS.INPUTURL);
            input.set('value', params.url);

            // Auto set the width and height.
            this._form.one('.' + CSS.INPUTWIDTH).set('value', '');
            this._form.one('.' + CSS.INPUTHEIGHT).set('value', '');

            // Load the preview image.
            this._loadPreviewImage(params.url);
        }
    },

    /**
     * Applies properties of an existing image to the image dialogue for editing.
     *
     * @method _applyImageProperties
     * @param {Node} form
     * @private
     */
    _applyImageProperties: function(form) {
        var properties = this._getSelectedImageProperties(),
            img = form.one('.' + CSS.IMAGEPREVIEW),
            i;

        if (properties === false) {
            img.setStyle('display', 'none');
            // Set the default alignment.
            for (i in ALIGNMENTS) {
                if (ALIGNMENTS[i].isDefault === true) {
                    css = ALIGNMENTS[i].value + ':' + ALIGNMENTS[i].name + ';';
                    form.one('.' + CSS.INPUTALIGNMENT).set('value', css);
                }
            }
            // Remove the custom style option if this is a new image.
            form.one('.' + CSS.INPUTALIGNMENT).getDOMNode().options.remove(ALIGNMENTS.length - 1);
            return;
        }

        if (properties.align) {
            form.one('.' + CSS.INPUTALIGNMENT).set('value', properties.align);
            // Remove the custom style option if we have a standard alignment.
            form.one('.' + CSS.INPUTALIGNMENT).getDOMNode().options.remove(ALIGNMENTS.length - 1);
        } else {
            form.one('.' + CSS.INPUTALIGNMENT).set('value', 'style:customstyle;');
        }
        if (properties.customstyle) {
            form.one('.' + CSS.INPUTCUSTOMSTYLE).set('value', properties.customstyle);
        }
        if (properties.width) {
            form.one('.' + CSS.INPUTWIDTH).set('value', properties.width);
        }
        if (properties.height) {
            form.one('.' + CSS.INPUTHEIGHT).set('value', properties.height);
        }
        if (properties.alt) {
            form.one('.' + CSS.INPUTALT).set('value', properties.alt);
        }
        if (properties.src) {
            form.one('.' + CSS.INPUTURL).set('value', properties.src);
            this._loadPreviewImage(properties.src);
        }
        if (properties.presentation) {
            form.one('.' + CSS.IMAGEPRESENTATION).set('checked', 'checked');
        }

        // Update the image preview based on the form properties.
        this._autoAdjustSize();
    },

    /**
     * Gets the properties of the currently selected image.
     *
     * The first image only if multiple images are selected.
     *
     * @method _getSelectedImageProperties
     * @return {object}
     * @private
     */
    _getSelectedImageProperties: function() {
        var properties = {
                src: null,
                alt :null,
                width: null,
                height: null,
                align: '',
                presentation: false
            },

            // Get the current selection.
            images = this.get('host').getSelectedNodes(),
            i, width, height, style, css;

        if (images) {
            images = images.filter('img');
        }

        if (images && images.size()) {
            image = images.item(0);
            this._selectedImage = image;

            style = image.getAttribute('style');
            properties.customstyle = style;
            style = style.replace(/ /g, '');

            width = image.getAttribute('width');
            if (!width.match(REGEX.ISPERCENT)) {
                width = parseInt(width, 10);
            }
            height = image.getAttribute('height');
            if (!height.match(REGEX.ISPERCENT)) {
                height = parseInt(height, 10);
            }

            if (width !== 0) {
                properties.width = width;
            }
            if (height !== 0) {
                properties.height = height;
            }
            for (i in ALIGNMENTS) {
                css = ALIGNMENTS[i].value + ':' + ALIGNMENTS[i].name + ';';
                if (style.indexOf(css) !== -1) {
                    margin = 'margin:' + ALIGNMENTS[i].margin + ';';
                    margin = margin.replace(/ /g, '');
                    // Must match alignment and margins - otherwise custom style is selected.
                    if (style.indexOf(margin) !== -1) {
                        properties.align = css;
                        break;
                    }
                }
            }
            properties.src = image.getAttribute('src');
            properties.alt = image.getAttribute('alt') || '';
            properties.presentation = (image.get('role') === 'presentation');
            return properties;
        }

        // No image selected - clean up.
        this._selectedImage = null;
        return false;
    },

    /**
     * Update the form when the URL was changed. This includes updating the
     * height, width, and image preview.
     *
     * @method _urlChanged
     * @private
     */
    _urlChanged: function() {
        var input = this._form.one('.' + CSS.INPUTURL);

        if (input.get('value') !== '') {
            // Load the preview image.
            this._loadPreviewImage(input.get('value'));
        }
    },

    /**
     * Update the image in the contenteditable.
     *
     * @method _setImage
     * @param {EventFacade} e
     * @private
     */
    _setImage: function(e) {
        var form = this._form,
            url = form.one('.' + CSS.INPUTURL).get('value'),
            alt = form.one('.' + CSS.INPUTALT).get('value'),
            width = form.one('.' + CSS.INPUTWIDTH).get('value'),
            height = form.one('.' + CSS.INPUTHEIGHT).get('value'),
            alignment = form.one('.' + CSS.INPUTALIGNMENT).get('value'),
            margin = '',
            presentation = form.one('.' + CSS.IMAGEPRESENTATION).get('checked'),
            constrain = form.one('.' + CSS.INPUTCONSTRAIN).get('checked'),
            imagehtml,
            customstyle = '',
            i,
            classlist = [],
            host = this.get('host');

        e.preventDefault();

        // Check if there are any accessibility issues.
        if (this._updateWarning()) {
            return;
        }

        // Focus on the editor in preparation for inserting the image.
        host.focus();
        if (url !== '') {
            if (this._selectedImage) {
                host.setSelection(host.getSelectionFromNode(this._selectedImage));
            } else {
                host.setSelection(this._currentSelection);
            }

            if (alignment === 'style:customstyle;') {
                alignment = '';
                customstyle = form.one('.' + CSS.INPUTCUSTOMSTYLE).get('value');
            } else {
                for (i in ALIGNMENTS) {
                    css = ALIGNMENTS[i].value + ':' + ALIGNMENTS[i].name + ';';
                    if (alignment === css) {
                        margin = ' margin: ' + ALIGNMENTS[i].margin + ';';
                    }
                }
            }

            if (constrain) {
                classlist.push(CSS.RESPONSIVE);
            }

            if (!width.match(REGEX.ISPERCENT) && isNaN(parseInt(width, 10))) {
                form.one('.' + CSS.INPUTWIDTH).focus();
                return;
            }
            if (!height.match(REGEX.ISPERCENT) && isNaN(parseInt(height, 10))) {
                form.one('.' + CSS.INPUTHEIGHT).focus();
                return;
            }

            template = Y.Handlebars.compile(IMAGETEMPLATE);
            imagehtml = template({
                url: url,
                alt: alt,
                width: width,
                height: height,
                presentation: presentation,
                alignment: alignment,
                margin: margin,
                customstyle: customstyle,
                classlist: classlist.join(' ')
            });

            this.get('host').insertContentAtFocusPoint(imagehtml);

            this.markUpdated();
        }

        this.getDialogue({
            focusAfterHide: null
        }).hide();

    },

    /**
     * Update the alt text warning live.
     *
     * @method _updateWarning
     * @return {boolean} whether a warning should be displayed.
     * @private
     */
    _updateWarning: function() {
        var form = this._form,
            state = true,
            alt = form.one('.' + CSS.INPUTALT).get('value'),
            presentation = form.one('.' + CSS.IMAGEPRESENTATION).get('checked');
        if (alt === '' && !presentation) {
            form.one('.' + CSS.IMAGEALTWARNING).setStyle('display', 'block');
            form.one('.' + CSS.INPUTALT).setAttribute('aria-invalid', true);
            form.one('.' + CSS.IMAGEPRESENTATION).setAttribute('aria-invalid', true);
            state = true;
        } else {
            form.one('.' + CSS.IMAGEALTWARNING).setStyle('display', 'none');
            form.one('.' + CSS.INPUTALT).setAttribute('aria-invalid', false);
            form.one('.' + CSS.IMAGEPRESENTATION).setAttribute('aria-invalid', false);
            state = false;
        }
        this.getDialogue().centerDialogue();
        return state;
    }
}, {
    NAME: 'atto-image',

    ATTRS: {
        /**
         * An array containing the handle position to activate. By default, all of them are activated.
         *
         * @property handles
         * @type {Array}
         * @optional
         * @default ['nw', 'ne', 'sw', 'se', 'n', 's', 'e', 'w']
         * @public
         */
        handle_config: {
            valueFn: function(){ return ['nw', 'ne', 'sw', 'se', 'n', 's', 'e', 'w']; },
            setter: function(val) {
                if (typeof val === "string") {
                    val = val.split(",").map(function(handle) { return handle.trim(); });
                }

                return val;
            }
        },

        /**
         * Min-max setting for both width/height.
         *
         * @property minmaxwidthheight
         * @type {{min_width: {Number}, min_height: {Number}, max_width: {Number}, max_height: {Number}}}
         * @default 'ctrl'
         * @public
         */
        minmaxwidthheight: {
            value: {
                min_width: Y.M.atto_image.get("default_min_width"),
                min_height: Y.M.atto_image.get("default_max_height"),
                max_width: Y.M.atto_image.get("default_max_width"),
                max_height: Y.M.atto_image.get("default_max_height")
            },

            setter: function(val) {
                if (typeof val === "string") {
                    try {
                        // This is guaranteed to cast JSON number to  javascript Number.
                        // @see http://www.ecma-international.org/ecma-262/5.1/#sec-15.12.2
                        // Specifically the following is mentioned:
                        // "JSON strings, numbers, booleans, and null are realized as ECMAScript Strings, Numbers, Booleans, and null."
                        val = JSON.parse(val);
                    } catch(error){
                        console.error('Parsing minmaxwidth to JSON throws: ' + error + '. ' +
                            'Contact the support or developers about the problem.');
                        return Y.Attribute.INVALID_VALUE;
                    }
                }

                return val;
            },

            validator: function(val) {
                if (typeof val === "string") {
                    return true;  // We can't validate further for performance reason. This is done in setter instead.
                }

                if (typeof val !== "object") {
                    console.error("minmaxwidthheight attribute is given an invalid type.");
                    return false;
                }

                // Ensure that each argument is an Number.
                Object.keys(val).forEach(function(val_index) {
                    if (!Y.Lang.isNumber(val[val_index])) {
                        return console.errror("One of the minmaxwidthheight field is not an integer.");
                    }
                });

                return true;
            }
        },

        /**
         * Toggle key for preserving aspect ratio when resizing the image.
         *
         * @property toggle_key_preserve_aspect_ratio
         * @type {string}
         * @default 'ctrl'
         * @public
         */
        toggle_key_preserve_aspect_ratio: {
            value: 'ctrl',

            validator: function(key) {
                key = key.trim();  // Probably not necessary, but couldn't hurt.
                Y.log('toggle_key_preserve_aspect_ratio: ' + key, 'debug', 'resizable');

                var available_keys = ['ctrl', 'alt', 'shift'];
                var toggle_key_is_invalid = available_keys.indexOf(key) === -1;

                if (toggle_key_is_invalid) {
                    console.error('Given toggle key is invalid');
                    return false;
                }

                return true;
            }
        },

        /**
         * Set to true in order to disable the custom_classes section of the custom_decorator overlay.
         *
         * @property disable_custom_classes
         * @type {boolean}
         * @default 'false'
         * @public
         */
        disable_custom_classes: {
            value: false,

            setter: function(disable) {
                if (typeof disable !== "boolean") { disable = disable !== '0'; }
                disable = !!disable;  // Get truthiness value, just in case garbage.
                return disable;
            }

            // I'm not validating. I assume the "truthiness" value is sufficient.
        },

        /**
         * Set to true to enable resize animation.
         *
         * @property resize_animation_enable
         * @type {boolean}
         * @default 'true'
         * @public
         */
        resize_animation_enable: {
            value: true,

            setter: function(enable) {
                if (typeof enable !== "boolean") { enable = enable === '1'; }
                enable = !!enable;  // Get truthiness value, just in case garbage.
                return enable;
            }
        },

        /**
         * Duration in which the resizing animation takes. In seconds.
         *
         * @property resize_animation_duration
         * @type {Number}
         * @default '0.4'
         * @public
         */
        resize_animation_duration: {
            value: 0.4,

            setter: function(val) { return parseFloat(val); },

            validator: function(val){
                var valid = Y.Lang.isNumber(parseFloat(val));
                if (!valid) { console.error('Given resize_animation_duration is not a number.'); }
                return valid;
            }
        },

        /**
         * Type of easing on resize animatio
         *
         * @property resize_animation_easing
         * @type {Number}
         * @default 'easingBoth'
         * @public
         */
        resize_animation_easing: {
            value: 'easeBoth',

            validator: function(easing) {
                var valid = Y.M.atto_image.get('easing_classes').indexOf(easing) !== -1;
                if (!valid) { console.error('Given easing function is not known.'); }
                return valid;
            }
        }
    }
});


}, '@VERSION@', {
    "requires": [
        "moodle-editor_atto-rangy",
        "moodle-editor_atto-plugin",
        "resize",
        "resize-plugin",
        "dd-plugin",
        "dd-constrain",
        "graphics",
        "transition",
        "event-custom",
        "event-focus",
        "anim"
    ]
});
