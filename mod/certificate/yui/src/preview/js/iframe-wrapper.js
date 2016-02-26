// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * Certificate preview javascript module
 * This handles the update of preview in mod_form.php
 *
 * This specific folder contains iframe_wrapper class to handle dealing with iframes.
 *
 * @package    mod_certificate
 * @copyright  Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @class iframe_wrapper
 *
 * Mainly created to have a cross-browser solution on iframe resizing.
 *
 * @param config @see Y.extend(Y.namespace('M.mod_certificate').iframe_wrapper.initializer
 *        function below for the type of object.
 */
function iframe_wrapper(config) {
    void(config);  // Avoid jslint complaints. Note that all functions have "arguments" object
                   // containing all arguments anyway.
    iframe_wrapper.superclass.constructor.apply(this, arguments);
}

Y.namespace('M.mod_certificate').iframe_wrapper = iframe_wrapper;

Y.extend(Y.namespace('M.mod_certificate').iframe_wrapper, Y.Base, {
    // Prototype properties.

    initializer: function(cfg) {
        this.iframe_node = cfg.iframe_node;
        this.aspect_ratio = cfg.aspect_ratio;
        this.orientation = cfg.orientation;

        this.setup_events();
    },

    /**
     * Setup the events.
     *
     * Events:
     * * load: called when iframe is loaded.
     * * resize: called when iframe is resized.
     */
    setup_events: function() {
        var preview_frame = Y.one("#preview-iframe");

        var thisAnchor = this;

        preview_frame.on("load", function() {
            thisAnchor.fire("load");
        });

        preview_frame.on("windowresize", function(e){
            thisAnchor.fire("resize", e);
        });
    },

    /**
     * Note: The returned "expected width" only makes sense if height is the invariant.
     * @returns {number} Expected width with respect to the aspect ratio and width.
     */
    get_new_width: function() {
        if (this.orientation === Y.namespace('M.mod_certificate').utility.orientation.landscape) {
            return parseInt(this.iframe_node.getComputedStyle("height"), 10) * this.aspect_ratio;
        } else if (this.orientation === Y.namespace('M.mod_certificate').utility.orientation.portrait) {
            return parseInt(this.iframe_node.getComputedStyle("height"), 10) / this.aspect_ratio;
        } else {
            console.error("orientation attribute is invalid.");
        }
    },

    /**
     * Note: The returned "expected width" only makes sense if width is the invariant.
     * @returns {number} Expected height with respect to the aspect ratio and width.
     */
    get_new_height: function() {
        if (this.orientation === Y.namespace('M.mod_certificate').utility.orientation.landscape) {
            return parseInt(this.iframe_node.getComputedStyle("width"), 10) / this.aspect_ratio;
        } else if (this.orientation === Y.namespace('M.mod_certificate').utility.orientation.portrait) {
            return parseInt(this.iframe_node.getComputedStyle("width"), 10) * this.aspect_ratio;
        } else {
            console.error("orientation attribute is invalid.");
        }
    },

    /**
     * Setting width attribute is not enough, width style must also be set.
     * @param new_width {Integer}
     */
    set_width: function (new_width) {
        this.iframe_node.set("width", new_width + "px");
        this.iframe_node.setStyle("width", new_width + "px");
    },

    /**
     * Setting height attribute is not enough, height style must also be set.
     * @param new_height {Integer}
     */
    set_height: function (new_height) {
        this.iframe_node.set("height", new_height + "px");
        this.iframe_node.setStyle("height", new_height + "px");
    },

    /**
     * Updates the size of the
     * @param base_side {string} Either "width" or "height". So if "width" is given then height will
     *        based off width. One must be the invariant or well be in a feedback loop.
     * @param new_side_value {integer} {Optional} Assigns a value to the base_side. E.g. if base_side "width"
     *        is given, and new_side_value is also provided, then iframe's width will be assigned with
     *        new_side_value.
     */
    update_iframe_size: function(base_side, new_side_value) {
        // Check if the new_side_value is set.
        if (typeof(new_side_value) !== "undefined" && new_side_value !== null) {
            if (base_side === "width") {
                this.set_width(new_side_value);
            } else if (base_side === "height") {
                this.set_height(new_side_value);
            } else {
                console.error("base_side argument is invalid.");
                return;
            }
        }

        // Adjust the other side value.
        if (base_side === "width") {
            var new_height = this.get_new_height();
            this.set_height(new_height);
        } else if (base_side === "height") {
            var new_width = this.get_new_width();
            this.set_width(new_width);
        } else {
            console.error("base_side argument is invalid.");
        }
    }
}, {
    // Static properties.

    NAME:'iframe_wrapper',
    ATTRS: {}
});