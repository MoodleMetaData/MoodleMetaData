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
});