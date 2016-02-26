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
 * main js component for 'atto_countplusplus'.
 *
 * @package    atto_countplusplus
 * @copyright  2015 Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

var DEFAULT_LAYOUT = 'Word count: %wc | Letter count: %lc';

/**
 * Enum for SPAN_TYPE which corresponds to settings.php
 * @type {{line: number, column: number, wordCount: number, totalLine: number}}
 */
SPAN_TYPE = {
    wordCount: 0,
    letterCount: 1
};
Object.freeze(SPAN_TYPE);

/**
 * Map from layout pattern to SPAN_TYPE.
 * @type {{%l: number, %c: number, %wc: number, %tl: number}}
 */
SPAN_STR_TO_TYPE_MAP = {
    '%wc': SPAN_TYPE.wordCount,
    '%lc': SPAN_TYPE.letterCount
};
Object.freeze(SPAN_STR_TO_TYPE_MAP);

var PATTERN_REGEX = /(%wc|%lc|\|)/g;

/**
 * Atto text editor countplusplus plugin.
 *
 * @namespace M.atto_countplusplus
 * @class button
 * @extends M.editor_atto.EditorPlugin
 */
var COMPONENTNAME = 'atto_countplusplus';

var STAT_SPAN_TEMPLATE = '' +
    '<span id="{{id}}">0</span>';

Y.namespace('M.atto_countplusplus').Button = // Misleading, not a button at all.
    Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {

        /**
         * @inherited
         */
        initializer: function () {
            this._stat = new Stat();  // State object. Utility for basic word statistics.

            /*
             * Aquire the instance counter and integrate it countplusplus id. This
             * will ensure that our id is unique since INSTANCE_COUNTER is global
             * in Y.M.att_countplusplus namespace.
             */
            this._instance_count = Y.M.atto_countplusplus.Button.INSTANCE_COUNTER++;
            this._id = COMPONENTNAME + "-" + this._instance_count;

            this.spanId = [];  // Represents the array of spanIDs.

            /*
             * For each spanID element, here contains its corresponding StatType.
             */
            this.spanStatType = [];

            /*
             * No word count is calculated until no change event occurs for the next 350ms.
             */
            this.delay = 350;

            /*
             * This will be the layout. For further documentation, see settings.php.
             * This is set to some value, just in case of php errors, or server side
             * update.
             */
            this.layout = DEFAULT_LAYOUT;

            /*
             * Pointer to the current update event. This is so we can cancel update
             * if there is an activity, thus avoiding update.
             *
             * To avoid error in the very first call, have a dummy function.
             */
            this.updateEvent = function () {
            };

            // Acquire statlayout from settings.php
            this.layout = this.get('statlayout');
            this.setupStatusbar();
            this._update();  // Sets the word stats prior to any events.
            this._setupEventHandlers();
        },

        _setupEventHandlers: function () {
            var thisAnchor = this;

            // See editor.js in main atto directory publishEvents for list of
            // published events.
            Y.on('change', function (e) {
                void(e);  // Avoid lint complains.
                // If there is an activity for the next thisAnchor.minDelay seconds, cancel
                // current pending update, and renew timeout.
                window.clearTimeout(thisAnchor.updateEvent);
                thisAnchor.updateEvent = setTimeout(thisAnchor._getUpdateWithThisClosure(), thisAnchor.delay);
            });
        },

        _getUpdateWithThisClosure: function () {
            var thisAnchor = this;
            return function () {
                thisAnchor._update();
            };
        },

        /**
         * Update the contents of the stats.
         * @private
         */
        _update: function () {
            var nodeList = this.get('host').editor.getDOMNode().querySelectorAll("*");
            var nodes = [];
            [].forEach.call(nodeList, function (node) {
                nodes[nodes.length] = node;
            });
            var wordCount = this._stat.getWordCount(this._getEditorText(),
                nodes, UTILITY.buildIsBlockArray(nodes));
            var letterCount = this._stat.getLetterCount(this._getEditorText());

            this._updateComponents(wordCount, letterCount);
        },

        /**
         * Updates the compoents, specifically, spans.
         * @param wordCount {int} word count.
         * @param letterCount {int} letter count.
         * @private
         */
        _updateComponents: function (wordCount, letterCount) {
            /*
             * Iterate each this.spanId, and set the corresponding span with the
             * appropriate value depending on its corresponding
             * this.spanStatType
             */
            var thisAnchor = this;
            this.spanId
                .forEach(function (currentId, currentIdIndex) {
                    var value = 0;
                    switch (thisAnchor.spanStatType[currentIdIndex]) {
                        case SPAN_TYPE.wordCount :
                            value = wordCount;
                            break;
                        case SPAN_TYPE.letterCount :
                            value = letterCount;
                            break;
                        default :
                            console.log("Fatal Error: Unrecognized span type.");
                            value = -1;
                            break;
                    }
                    thisAnchor._setSpanValue(value, currentIdIndex);
                });
        },

        /**
         * Setup the statusbar. In editor.js, setupPlugins() is called at the
         * very bottom, so all elements added here are naturally going to be in
         * the bottom.
         */
        setupStatusbar: function () {
            var host = this.get('host');
            host.setupStatusbar();  // Don't worry, this is a "singleton".
            host.addStatusbarNode(this._generateStatNode());
        },

        /**
         * This sets the value of a span given a spanIndex, which is in range
         * [0, this.spanIndex.length]. Note that these spans are generated by
         * this._generateStatNode during initialization.
         * @param value New value of the index.
         * @param spanIndex {Number} id of the index to be set.
         * @private
         */
        _setSpanValue: function (value, spanIndex) {
            var spanElem = Y.DOM.byId(this.spanId[spanIndex]);
            Y.DOM.setText(spanElem, value);
        },

        /**
         * @returns {*|string} html text in the body of the editor (this
         *                     includes tags if any.
         * @private
         */
        _getEditorText: function () {
            return this.get('host').getCleanHTML();
        },

        /**
         * Generate the span for displaying a stat element. For instance in a
         * layout of "Word count: %lc", the %lc ill be replaced with a span.
         * @param spanId Id of the span. Ensure this is unique.
         * @returns {string} The proper span html string.
         * @private
         */
        _getStatSpan: function (spanId) {
            var stat_span_template = Y.Handlebars.compile(STAT_SPAN_TEMPLATE);
            return stat_span_template({
                id: spanId
            });
        },

        /**
         * Generate the span id given a spanCounter. Use this to avoid id
         * namespace collision.
         * @param spanCounter This should be unique.
         * @returns {string} proper span id.
         * @private
         */
        _getSpanId: function (spanCounter) {
            return this._id + "-" + "span-" + spanCounter;
        },

        /**
         * Generates the n'th span.
         * @param n {Number} This should be unique everytime, to generate unique
         *                   id.
         * @param patternSig {string} Pattern Signature, e.g. '%lc', '%wc', '|'
         * @returns {*|string} A span html string.
         * @private
         */
        _generateNthSpan: function (n, patternSig) {
            var spanId = this._getSpanId(n);
            var spanElem = this._getStatSpan(spanId);

            /*
             * Build span array for future reference to both spanId and
             * its corresponding spanStatType.
             */
            this.spanId[this.spanId.length] = spanId;
            this.spanStatType[this.spanStatType.length] =
                SPAN_STR_TO_TYPE_MAP[patternSig];
            return spanElem;
        },

        /**
         * @return Generated YUI.Node stat element.
         * @private
         */
        _generateStatNode: function () {
            /*
             * Keeps track of the number of span to generate unique id.
             * This is utilized in replacer function.
             */
            var spanCounter = 0;
            var thisAnchor = this;
            var host = this.get("host");

            /**
             * Used by layout.replace below.
             * @param match {string} matched string.
             * @returns {string} replacement for matched string.
             */
            function replacer(match) {
                var spanElem = "";

                if (match === "|") {
                    spanElem = host.getSeparator();
                } else {
                    spanElem = thisAnchor._generateNthSpan(spanCounter, match);
                    spanCounter++;
                }
                return spanElem;
            }

            /*
             * Pattern for extracting %wc or %lc.
             */
            var htmlStr = this.layout.replace(PATTERN_REGEX, replacer);

            var statusBarDiv = Y.Node.create("<div/>");
            statusBarDiv.appendChild(Y.Node.create(htmlStr));
            return statusBarDiv;
        }
    }, {
        INSTANCE_COUNTER: 0,

        ATTRS: {
            /**
             * Status layout. See settings.php for more info.
             *
             * @attribute statlayout
             * @type String.
             * @see settings.php
             * @default 'Word count: %wc | Letter count: %lc'
             */
            statlayout: {
                value: DEFAULT_LAYOUT
            }
        }
    });
