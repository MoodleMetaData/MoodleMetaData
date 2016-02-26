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
});