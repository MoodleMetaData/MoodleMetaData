YUI.add('moodle-mod_certificate-preview', function (Y, NAME) {

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
 * Just some miscellaneous javascript codes.
 *
 * @package    mod_certificate
 * @copyright  Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

Y.namespace('M.mod_certificate').utility = {
    /**
     * Aspect ratio enumeration.
     */
    aspect_ratio: {
        a4_aspect_ratio: 1.414,
        letter_aspect_ratio: Math.sqrt(2)
    },

    /**
     * Orientation enumeration.
     */
    orientation: {
        landscape: 0,
        portrait: 1
    }
};

Object.freeze(Y.namespace('M.mod_certificate').utility.aspect_ratio);
Object.freeze(Y.namespace('M.mod_certificate').utility.orientation);// This file is part of the Certificate module for Moodle - http://moodle.org/
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
});// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * This specific javascript file handles form specific problems.
 *
 * @package    mod_certificate
 * @copyright  Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @class form_wrapper
 *
 * Makes the form easier to handle.
 */
function form_wrapper(config) {
    void(config);

    form_wrapper.superclass.constructor.apply(this, arguments);
}

Y.namespace('M.mod_certificate').form_wrapper = form_wrapper;

Y.extend(Y.namespace('M.mod_certificate').form_wrapper, Y.Base, {
    // Prototype properties.

    initializer: function(cfg) {
        this.preview_link = cfg.preview_link;
        this.course_module_id = cfg.course_module_id;

        this.setup_events();
    },

    /**
     * Setup the event.
     *
     * Events:
     * * field-change: called when one of the form's field change.
     */
    setup_events: function() {
        var thisAnchor = this;

        Y.all("form *").each(function(node){
            node.on("change", function(e){
                thisAnchor.fire("field-change");

                // Stop the event from bubbling up the DOM tree. We only need one
                // callback per data change.
                e.stopPropagation();
            });
        });
    },

    /**
     * Toggle preview section.
     */
    toggle_preview_section: function() {
        Y.one("#id_previewsection").removeClass("collapsed");
    },

    /**
     * @param name {string}
     * @return value of the field with respect to name.
     */
    get_field_value: function(name) {
        return Y.one("[name=\"" + name + "\"]").get("value");
    },

    /**
     * Base on the "certificatetype" field, returns the corresponding
     * aspect ratio.
     * @return aspect ratio
     */
    get_aspect_ratio: function() {
        var a4_regex = /.*a4.*/i;
        var letter_regex = /.*letter.*/i;

        var certificatetypeval =
            this.get_field_value("certificatetype");

        if (certificatetypeval.search(a4_regex)) {
            return Y.namespace('M.mod_certificate').utility.aspect_ratio.a4_aspect_ratio;
        } else if (certificatetypeval.search(letter_regex)) {
            return Y.namespace('M.mod_certificate').utility.aspect_ratio.letter_aspect_ratio;
        } else {
            console.error("Recent changes on certificatetype must've occured. Change " +
                "Y.namespace('M.mod_certificate').get_aspect_ratio function accordingly");
        }
    },

    /**
     * Send a get request to render a preview.
     */
    render_preview: function() {
        var preview_frame = Y.one("#preview-iframe");
        var get_field_value =this.get_field_value;
        var preview_request = this.preview_link + "?" +
            "id=" + this.course_module_id + "&" +
            "name=" + get_field_value("name") + "&" +
            "orientation=" + get_field_value("orientation") + "&" +
            "bordercolor=" + get_field_value("bordercolor") + "&" +
            "borderstyle=" + get_field_value("borderstyle") + "&" +
            "printseal=" + get_field_value("printseal") + "&" +
            "printsignature=" + get_field_value("printsignature") + "&" +
            "printwmark=" + get_field_value("printwmark") + "&" +
            "printdate=" + get_field_value("printdate") + "&" +
            "printteacher=" + get_field_value("printteacher") + "&" +
            "datefmt=" + get_field_value("datefmt") + "&" +
            "printgrade=" + get_field_value("printgrade") + "&" +
            "gradefmt=" + get_field_value("gradefmt") + "&" +
            "printoutcome=" + get_field_value("printoutcome") + "&" +
            "printhours=" + get_field_value("printhours") + "&" +
            "printnumber=" + get_field_value("printnumber") + "&" +
            "certificatetype=" + get_field_value("certificatetype") + "&" +
            "customtext=" + get_field_value("customtext");
        preview_frame.set("src", preview_request);
    }
}, {
    // Static properties.

    NAME:'form_wrapper',
    ATTRS: {}
});// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * @package    mod_certificate
 * @copyright  Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Entry point function.
 * @param preview_link {string} link of the preview page.
 */
Y.namespace('M.mod_certificate').init = function(preview_link, cmid) {
    var form_wrapper_instance = new Y.M.mod_certificate.form_wrapper({
        preview_link: preview_link,
        course_module_id: cmid
    });

    // toggle preview by default. There is no point on hiding it.
    form_wrapper_instance.toggle_preview_section();

    // Render preview immediately.
    form_wrapper_instance.render_preview();

    // Wrap the iframe in a wrapper to make it easier to handle.
    var preview_frame = Y.one("#preview-iframe");
    var iframe_wrapper = new Y.M.mod_certificate.iframe_wrapper({
        iframe_node: preview_frame,
        aspect_ratio: Y.namespace('M.mod_certificate').utility.aspect_ratio.a4_aspect_ratio,
        orientation: Y.namespace('M.mod_certificate').utility.orientation.landscape
    });

    // Handle form change event.
    var update_iframe = function(){
        iframe_wrapper.aspect_ratio = form_wrapper_instance.get_aspect_ratio();

        var is_portrait = form_wrapper_instance.get_field_value("orientation") === 'P';
        var is_landscape = form_wrapper_instance.get_field_value("orientation") === 'L';

        if (is_portrait) {
            iframe_wrapper.orientation = Y.namespace('M.mod_certificate').utility.orientation.portrait;
        } else if (is_landscape) {
            iframe_wrapper.orientation = Y.namespace('M.mod_certificate').utility.orientation.landscape;
        } else {
            console.error("Orientation not recognized.");
        }

        iframe_wrapper.update_iframe_size("width");
        form_wrapper_instance.render_preview();
    };

    form_wrapper_instance.on("field-change", function(){
        update_iframe();
    });

    // Handle iframe preview load event.
    iframe_wrapper.once("load", function(){
        update_iframe();
    });

    // Handle iframe resize event.
    iframe_wrapper.on('resize', function(){
        // no need to rerender iframe content, we just want to adjust the size of the iframe.
        iframe_wrapper.update_iframe_size("width");
    });
};

}, '@VERSION@', {"requires": ["base", "node", "event", "event-key", "io"]});
