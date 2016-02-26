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
* This file contains the YUI module for the block that exists within a course for
* sending mail messages.
*
* @package    block_course_message
* @category   block
* @copyright  2013 Craig Jamieson
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
YUI.add('moodle-block_course_message-send_from_block', function (Y) {

    // define vars and objects here
    var SENDFROMBLOCKNAME = 'send_from_block';
    var BLOCKCMLANGTABLE = 'block_course_message';
    var allcontacts = new Array();
    var allids = new Array();
    var courseid;

    // block constructor
    var SEND_FROM_BLOCK = function () {
        SEND_FROM_BLOCK.superclass.constructor.apply(this, arguments);
    };

    // extend -> module functions go here
    Y.extend(SEND_FROM_BLOCK, Y.Base, {
        /**
         * Initializer -> setup the autocomplete module + store contacts in memory
         *
         */
        initializer : function (params) {

            allcontacts[0] = 'All Students';
            allids[0] = 's1';
            allcontacts = allcontacts.concat(params.groups, params.contacts);
            allids = allids.concat(params.groupids, params.contactids);
            courseid = params.courseid;

            var contactnode = Y.one('#contactfromblock');
            contactnode.plug(Y.Plugin.AutoComplete, {
                resultFilters : 'phraseMatch',
                resultHighlighter : 'phraseMatch',
                minQueryLength : 0,
                maxResults : 8,
                source : allcontacts
            });

            // This displays the entire list when the box is clicked
            contactnode.on('focus', function () {
                contactnode.ac.sendRequest(contactnode.ac.get('value'));
            });

            M.block_course_message.setup_panels();

            // subscribe to events here
            Y.on('click', this.block_send_mail, '#sendmailfromblock');
            Y.on('click', this.open_inbox, '#bar');
        },

        /**
         * Message sending handler: validate user input and send the mail.
         *
         */
        block_send_mail : function (e) {
            e.preventDefault();

            var userto = new Array();
            var index = Y.Array.indexOf(allcontacts, Y.one("#contactfromblock").get('value'));
            userto.push(allids[index]);
            var encodedcontact = Y.JSON.stringify(userto);

            var subject = Y.one('#subjectfromblock').get('value');
            var message = Y.one('#messagefromblock').get('value');

            // check for no or invalid contact
            if ((typeof userto[0] == "undefined") || (userto[0] == 0)) {
                M.block_course_message.alerttext(M.util.get_string('nocontactswarning', BLOCKCMLANGTABLE));
                return 0;
            }
            // validate
            if (!M.block_course_message.validate_message(userto, subject, message)) {
                return 0;
            }
            Y.one('#sendmailfromblock').setAttribute("disabled", "disabled");

            Y.io(M.cfg.wwwroot + '/blocks/course_message/ajax_request.php', {
                method : 'POST',
                data : 'request=send' + '&mailTo=' + encodedcontact + '&subject=' + subject + '&message=' + message + '&courseid=' + courseid + '&sesskey=' + M.cfg.sesskey,
                on : {
                    success : function (id, result) {
                        M.block_course_message.check_return_result(result.response);
                        // clear out the mail fields
                        Y.one('#subjectfromblock').set('value', '');
                        Y.one('#messagefromblock').set('value', '');
                        Y.one("#contactfromblock").set('value', '');
                        Y.one('#contactfromblock').ac.set('value', '');
                        Y.one('#sendmailfromblock').removeAttribute("disabled");
                    },
                    failure : function (id, result) {
                        M.block_course_message.alerttext(M.util.get_string('mailnotsent', BLOCKCMLANGTABLE));
                        Y.one('#sendmailfromblock').removeAttribute("disabled");
                    }
                }
            });
        },

        /**
         * This function opens the user's inbox in a new window when they
         * click on the new mail indicator.
         *
         */
        open_inbox : function (e) {
            e.preventDefault();

            var displaypreference = Y.one('#displaypreference').getAttribute('msg');
            if (displaypreference == 'new_page') {
                var str = M.cfg.wwwroot + '/blocks/course_message/inbox.php?courseid=' + courseid;
                window.open(str);
            } else {
                // YUI modal implementation not investigated
                // $("#inboxIframe").dialog("open").css("width", "100%");
            }
        }

    }, {
        NAME : SENDFROMBLOCKNAME,
        ATTRS : {
            courseid : {},
            contacts : {},
            contactids : {},
            groups : {},
            groupids : {}
        }
    });
    M.block_course_message = M.block_course_message || {};
    M.block_course_message.init_send_from_block = function (params) {
        return new SEND_FROM_BLOCK(params);
    };
}, '@VERSION@', {
    requires : ['base', 'autocomplete', 'autocomplete-filters', 'autocomplete-highlighters', 'json-stringify']
});