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
 * This file contains a couple of functions that are shared between both the block code
 * and the inbox code.
 *
 * @package    block_course_message
 * @category   block
 * @copyright  2013 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
YUI.add('moodle-block_course_message-mail_lib', function (Y) {

    // define vars and objects here
    var BLOCKCMLANGTABLE = 'block_course_message';

    M.block_course_message = M.block_course_message || {};
    // global library functions
    M.block_course_message = {

        alertpanel: new Y.Panel(),

        /**
         * This function sets up the needed YUI panels.  Currently that is
         * just a panel used for alerts, but a confirm panel should also be
         * added.
         *
         */
        setup_panels : function () {
            // I've just added the alert panel for now and hope to add a confirm panel.
            this.setup_alertpanel();
        },

        /**
         * This function sets up a modal alert panel that is used to display
         * information back to the user.
         *
         */
        setup_alertpanel : function () {
            Y.one(document.body).append(Y.one('#alertpanel'));

            M.block_course_message.alertpanel = new Y.Panel({
                srcNode: '#alertpanel',
                width: 250,
                zIndex: 5,
                centered: true,
                modal: true,
                visible: false,
                render: true,
                plugins: [Y.Plugin.Drag],
                buttons: [
                    {
                        value: 'OK',
                        section: Y.WidgetStdMod.FOOTER,
                        action: function (e) {
                            e.preventDefault();
                            M.block_course_message.alertpanel.hide();
                            if (this.callback) {
                                this.callback();
                            }
                            // remove callback so it does not persist
                            this.callback = false;
                        }
                    }
                ]
            });
        },

        /**
        * This function does some simple validation on the mail from javascript.
        * More thorough validation should be done in the php files.
        *
        */
        validate_message : function (userto, subject, message) {
            if (!M.block_course_message.validate_contacts(userto)) {
                return false;
            }
            if (!M.block_course_message.validate_body(message)) {
                return false;
            }
            if (!M.block_course_message.validate_subject(subject)) {
                return false;
            }

            return true;
        },

        /**
         * This function checks for at least one recipient.
         *
         */
        validate_contacts : function (userto) {
            if (userto.length < 1) {
                M.block_course_message.alerttext(M.util.get_string('nocontactswarning', BLOCKCMLANGTABLE));
                return false;
            }
            return true;
        },

        /**
         * This function checks a non-empty message body.
         *
         */
        validate_body : function (message) {
            if (message == "") {
                var isok = confirm(M.util.get_string('nomessagewarning', BLOCKCMLANGTABLE));
                if (isok) {
                    return true;
                } else {
                    return false;
                }
            }
            return true;
        },

        /**
         * This function checks for a blank subject.
         *
         */
        validate_subject : function (subject) {
            if (subject == "") {
                var isok = confirm(M.util.get_string('nosubjectwarning', BLOCKCMLANGTABLE));
                if (isok) {
                    return true;
                } else {
                    return false;
                }
            }
            return true;
        },

        /**
         * This method parses the passed string and sees if the result was sucessful.
         * On success, the success message (held in text) is displayed in an alert.
         *
         */
        check_return_result : function (data, reload) {
            var object = Y.JSON.parse(data);
            if (object.result) {
                M.block_course_message.alerttext(object.text);
            }
            if (reload == true) {
                M.block_course_message.alertpanel.callback = function () {
                    // this is a moodle 2.4+ fix to unbind the unload handler
                    window.onbeforeunload = null;
                    // reload the page altogether so that we get a new attachment session
                    location.reload(true);
                }
            }
        },

        /**
         * This method displays the alert panel (modal dialog) and changes the body
         * portion to the text passed as a parameter.
         *
         *  @param  {String} text String indicating text to show to user
         *
         */
        alerttext: function(text) {
            Y.one("#alerttext").setHTML(text);
            M.block_course_message.alertpanel.show();
        }

    }

}, '@VERSION@', {
    requires : ['base', 'json-parse', 'panel']
});