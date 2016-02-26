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