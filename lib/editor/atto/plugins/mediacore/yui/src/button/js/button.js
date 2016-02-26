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
 * The MediaCore Chooser Atto editor button implementation
 *
 * @package    atto_mediacore
 * @copyright  2014 MediaCore <info@mediacore.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module moodle-atto_mediacore-button
 */

/**
 * Atto text editor mediacore plugin.
 *
 * @namespace M.atto_mediacore
 * @class button
 * @extends M.editor_atto.EditorPlugin
 */
Y.namespace('M.atto_mediacore').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {

    /**
     * A reference to the MediaCore Chooser
     */
    _chooser: null,


    /**
     * Init
     */
    initializer: function() {

        if (this.get('disabled')){
            return;
        }

        if (!this.get('chooser_js_url') ||
            !this.get('url') ||
            !this.get('mode')) {
            var msg = M.util.get_string('noparamserror', 'atto_mediacore');
            throw new Error(msg);
        }

        // Load the chooser js
        this._loadScript(this.get('chooser_js_url'));

        // Add the MediaCore Chooser button
        this.addButton({
            icon: 'icon',
            iconComponent: 'atto_mediacore',
            callback: this._initChooser
        });
    },

    /**
     * Init the Chooser
     */
    _initChooser: function() {

        if (!mediacore) {
            var msg = M.util.get_string('nochooserjserror', 'atto_mediacore');
            throw new Error(msg);
        }
        if (!this._chooser) {
            // Init the chooser with the following params
            var params = {
                'url': this.get('url'),
                'mode': this.get('mode')
            };
            this._chooser = mediacore.chooser.init(params);

            // Listen and handle the Chooser "media" event
            this._chooser.on('media', Y.bind(this._insertContent, this));

            // Listen and handle the Chooser "error" event
            this._chooser.on('error', function(err) {
                throw err;
            });
        }
        this._chooser.open();
    },

    /**
     * Create <a> > <img> elements and inject them into
     * the editor content
     */
    _insertContent: function(media) {
        console.log('insertContent', media, this);

        // Add an a > img element with the media attributes
        var aElem = Y.Node.create('<a></a>').setAttrs({
            'href': media.embed_url,
            'data-media-id': media.id
        });
        var imgElem = Y.Node.create('<img/>').setAttrs({
            'src':  media.thumb_url,
            'width': '400',
            'height': '225',
            'alt': media.title,
            'title': media.title
        });
        aElem.appendChild(imgElem);

        // Insert the html into the editor
        this.editor.focus();
        var html = aElem.getDOMNode().outerHTML;
        this.get('host').insertContentAtFocusPoint(html);
        this.markUpdated();
    },

    /**
     * Load a script url and append to the document.body
     * @param {string} url
     */
    _loadScript: function(url) {
        var script = document.createElement('script');
        script.src = url;
        (document.body || document.head || document.documentElement).appendChild(script);
    }

}, { ATTRS: {
        disabled: {
            value: false
        },
        chooser_js_url: {
            value: null
        },
        url: {
            value: null
        },
        mode: {
            value: 'popup'
        }
    }
});
