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
