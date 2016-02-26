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
});