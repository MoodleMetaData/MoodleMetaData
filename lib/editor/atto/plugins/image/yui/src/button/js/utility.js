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
        '/>';