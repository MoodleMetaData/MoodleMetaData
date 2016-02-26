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
};