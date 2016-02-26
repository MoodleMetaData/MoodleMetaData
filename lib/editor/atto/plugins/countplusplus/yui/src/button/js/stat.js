/**
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
