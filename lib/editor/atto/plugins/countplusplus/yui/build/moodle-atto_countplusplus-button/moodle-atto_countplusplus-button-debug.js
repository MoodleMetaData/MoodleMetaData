YUI.add('moodle-atto_countplusplus-button', function (Y, NAME) {

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
 * utility component for 'atto_countplusplus'. Here belongs functions
 * that is not big/important enough to deserve its own category.
 *
 * @package    atto_countplusplus
 * @copyright  2015 Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

var UTILITY = {};

/**
 * @param element {Javascript Native Node}
 * @return returns the display copmuted display property
 *                 (after applying css) of the given element.
 */
UTILITY.getDisplayType = function (element) {
    var cStyle = element.currentStyle || window.getComputedStyle(element, "");
    return cStyle.display;
};

/**
 * @param nodes {Array {Javascript node}} Array of javascript nodes.
 * @returns {Array {Boolean}} which indicates if the i'th node is a block.
 */
UTILITY.buildIsBlockArray = function (nodes) {
    var isBlockArr = [];
    nodes.forEach(function (node) {
        isBlockArr[isBlockArr.length] =
            UTILITY.getDisplayType(node).toLowerCase() === 'block' ||
            node.tagName.toLowerCase() === 'br';
    });

    return isBlockArr;
};/**
 * word stat component for 'atto_countplusplus', language 'en'.
 *
 * @package    atto_countplusplus
 * @copyright  2015 Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Pattern for some possible printable html entitity.
 *
 * @type {RegExp}
 */
var HTML_ENTITY_REGEX = /&[a-zA-Z\d]{1,10};/g;

/**
 * @class Stat
 * @brief Basic word statistics.
 * @constructor
 */
function Stat() {
    /**
     * @param {string} alltext
     * @returns {string} alltext without any whitespace.
     *                   (Does not accout for &nbsp;)
     * @private
     */
    var _removeWhiteSpace = function (alltext) {
        return alltext.replace(/\s+/g, '');
    };

    /**
     * @param alltext
     * @param replacement
     * @returns Replace &lt; and other html entity with their corresponding
     *          single character.
     * Note: I assume that all characters are inserted via charmap plugin, thus
     *       this is actually not needed since charmap inserts the actual
     *       unicode character, not some html entity.
     * @private
     */
    var _replaceHTMLEntity = function (alltext, replacement) {
        if (typeof replacement === "undefined" ||
            replacement === null) {
            replacement = '*';
        }
        alltext = alltext.replace(/&nbsp;/g, ' ');
        alltext = alltext.replace(HTML_ENTITY_REGEX, replacement);
        return alltext;
    };

    /**
     * @param {string} input
     * @param {Array {Javascript Node}} array of Javascript nodes.
     * @param {Array {Boolean}} boolean array of blocks.
     * @return input with all tag.display == 'block' replaced with space.
     * @private
     */
    var _replaceLineBreaks = function (input, nodes, isBlockArr) {
        var tagRegex = /<([a-z][a-z0-9]*)\b[^>]*>/gi;
        var result;

        // Keeps track of the index of the current node in nodes and isBlockArr
        // array.
        var index = 0;

        while ((result = tagRegex.exec(input)) !== null) {
            // If current element is a block, do some more line break
            // processing.
            var curNode = nodes[index];

            // priorTagStr contains the start to the end of the current tag.
            // afterTagStr contains all the string after the current tag.
            var priorTagStr = input.slice(0, result.index + result[0].length);
            var afterTagStr = input.slice(result.index + result[0].length,
                input.length);

            if (isBlockArr[index]) {
                /*
                 * Note that result[0] correspond to the opening tag of
                 * node[index].
                 *
                 * priorInnerHTMLAfterTag contains the string after the opening
                 * tag to the end of node[index].innerHTML. We then sorround
                 * this with spaces.
                 *
                 * afterInnerHTMLAfterTag contains the rest of the string.
                 */
                var priorInnerHTMLAfterTag = afterTagStr.slice(0,
                    curNode.innerHTML.length);
                var afterInnerHTMLAfterTag = afterTagStr.slice(
                    curNode.innerHTML.length, afterTagStr.length);
                afterTagStr =
                    ' ' + priorInnerHTMLAfterTag + ' ' +
                    afterInnerHTMLAfterTag;
                input = priorTagStr + afterTagStr;
            }

            index++;
        }

        return input;
    };

    /**
     * http://phpjs.org/functions/strip_tags/
     * @param {string} input
     * @param allowed
     * @returns input without html tags.
     * @private
     */
    var _stripTags = function (input, allowed) {
        //  discuss at: http://phpjs.org/functions/strip_tags/
        // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // improved by: Luke Godfrey
        // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        //    input by: Pul
        //    input by: Alex
        //    input by: Marc Palau
        //    input by: Brett Zamir (http://brett-zamir.me)
        //    input by: Bobby Drake
        //    input by: Evertjan Garretsen
        // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // bugfixed by: Onno Marsman
        // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // bugfixed by: Eric Nagel
        // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // bugfixed by: Tomasz Wesolowski
        //  revised by: Rafał Kukawski (http://blog.kukawski.pl/)
        //   example 1: strip_tags('<p>Kevin</p> <br /><b>van</b> <i>Zonneveld</i>', '<i><b>');
        //   returns 1: 'Kevin <b>van</b> <i>Zonneveld</i>'
        //   example 2: strip_tags('<p>Kevin <img src="someimage.png" onmouseover="someFunction()">van <i>Zonneveld</i></p>', '<p>');
        //   returns 2: '<p>Kevin van Zonneveld</p>'
        //   example 3: strip_tags("<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>", "<a>");
        //   returns 3: "<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>"
        //   example 4: strip_tags('1 < 5 5 > 1');
        //   returns 4: '1 < 5 5 > 1'
        //   example 5: strip_tags('1 <br/> 1');
        //   returns 5: '1  1'
        //   example 6: strip_tags('1 <br/> 1', '<br>');
        //   returns 6: '1 <br/> 1'
        //   example 7: strip_tags('1 <br/> 1', '<br><br/>');
        //   returns 7: '1 <br/> 1'

        allowed = (((allowed || '') + '')
            .toLowerCase()
            .match(/<[a-z][a-z0-9]*>/g) || [])
            .join(''); // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
        var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
            commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
        return input.replace(commentsAndPhpTags, '')
            .replace(tags, function ($0, $1) {
                return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
            });

    };

    /**
     * @param alltext
     * @param nodes
     * @returns {Number} word count.
     */
    this.getWordCount = function (alltext, nodes, isBlockArray) {
        var processedAllText = alltext;
        processedAllText = _replaceLineBreaks(processedAllText, nodes,
            isBlockArray);
        processedAllText = _replaceHTMLEntity(processedAllText);
        processedAllText = _stripTags(processedAllText).trim();

        var counter = 0;
        var isLastWordDashed = false;
        processedAllText.split(/\s+/g).forEach(function (word) {
            if (word.length > 0 && isLastWordDashed === false) {
                counter++;
            }

            /*
             * See if the last character is a single dash. n-dash,
             * n>1, don't count.
             */
            var trimmedWord = word.trim();
            isLastWordDashed = /[^\-]-$/.test(trimmedWord);
        });

        return counter;
    };

    /**
     * @param {string} alltext
     * @return {int} letter count.
     * @public
     */
    this.getLetterCount = function (alltext) {
        // Remove whitespace and return length.
        var processedAllText = alltext;
        processedAllText = _replaceHTMLEntity(processedAllText);
        processedAllText = _stripTags(processedAllText);
        processedAllText = _removeWhiteSpace(processedAllText);
        return processedAllText.length;
    };
}
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


}, '@VERSION@', {"requires": ["moodle-editor_atto-plugin"]});
