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
YUI.add('moodle-block_course_message-inbox', function (Y) {
    // **********************************************************************
    // define vars and objects here
    // **********************************************************************
    var INBOXNAME = 'inbox';
    var BLOCKCMLANGTABLE = 'block_course_message';
    var ANCHORTAG = '<a href';
    var allcontacts = new Array();
    var allids = new Array();
    var courseid;
    var editor;
    var composedraftid;
    var replydraftid;
    var currentpage;
    var replytype;
    var replydelegate;
    var sentdatatable;
    var inboxdatatable;

    // block constructor
    var INBOX = function () {
        INBOX.superclass.constructor.apply(this, arguments);
    };

    // Note: this module works quite a bit different than most other YUI modules.  That is,
    // it isn't really a class or a self-contained plugin.  It's structured moreso as a set
    // of functions to accomplish a series of tasks, but put into a YUI module so that it
    // gets loaded automatically by Moodle.
    //
    // The structure is setup so that most of the functions are declared here, while the base
    // event handlers that are loaded initially are within the Y.extend() definition below.
    //
    // They could all be moved into the Y.extend() area, but this requires that Y.bind(some_fn, this)
    // be used quite heavily in order to preserve the context.  I did do this initially, but
    // this caused some errors to get thrown in Firefox (but not Chrome) that weren't noticeable
    // to the end user, but I'd rather play it safe.

    /**
     * This function strips out the extra formatting that is used to remove an <li>
     * from the multivalue-input.  It looks for the start of the anchor tag and then
     * removes everything after that.
     *
     * Note: this function is no longer needed.
     *
     */
    var strip_multivalue_list = function (multivalueli) {
        var anchorindex = multivalueli.indexOf(ANCHORTAG);
        return multivalueli.slice(0, anchorindex);
    };

    /**
     * This method is responsible for checking for new messages.  It updates the
     * inbox text to reflect whether there are new messages.
     *
     */
    function check_for_new_messages() {
        Y.io(M.cfg.wwwroot + '/blocks/course_message/ajax_request.php', {
            method : 'POST',
            data : 'request=' + 'check_message' + '&courseid=' + courseid + '&sesskey=' + M.cfg.sesskey,
            on : {
                // TODO: add session handling for no login, bad sesskey, etc, here.
                success : function (id, result) {
                    if (result.response > 0) {
                        var inboxlabel = M.util.get_string('inboxlabel', BLOCKCMLANGTABLE);
                        Y.one('#inboxli').setHTML('<b>' + inboxlabel + ' (' + result.response + ')</b>');
                    }
                },
                failure : function (id, result) {}
            }
        });
    }

    /**
     * This method loads a folder on the dipslay.  The messages are retrieved via
     * an AJAX call, loaded into a DataSource, which is then loaded into a DataTable.
     *
     *  @param  {String} folder String identifying the folder to load ('inbox'|'sent').
     *
     */
    function load_folder(folder) {
        var viewcontainer = '#' + folder + 'mail';
        var viewpaginator = '#' + folder + 'paginator';

        // Startup -> always grab messages from folder
        var folderdatasource = new Y.DataSource.IO({
                source : M.cfg.wwwroot + '/blocks/course_message/ajax_request.php?courseid=' + courseid + '&request=' + folder + '&sesskey=' + M.cfg.sesskey
            });
        folderdatasource.plug(Y.Plugin.DataSourceJSONSchema, {
            schema : {
                resultListLocator : "rows"
            }
        });
        // Setup the Datatable
        var cols = setup_data_table_columns(folder);
        var folderdatatable = new Y.DataTable
            ({
                columns : cols,
                scrollable : 'y',
                sortable : true,
                // selection options
                highlightModle : 'cell',
                selectionMode : 'row',
                selectionMulti : true,
                // Paginator options
                paginator : new Y.PaginatorView
                ({
                    model : new Y.PaginatorModel({
                        itemsPerPage : 20
                    }),
                    container : viewpaginator,
                    paginatorTemplate : '#paginator-template',
                    maxPageLinks : 6,
                    pageLinkFiller : '...',
                    linkListOffset : 1,
                    pageLinkTemplate : '<button data-pglink="{page}" class="yui3-button {pageLinkClass}" title="Page No. {page}">{page}</button>'
                }),
            });
        folderdatatable.set('strings.emptyMessage', M.util.get_string('folderloading', BLOCKCMLANGTABLE));
        folderdatatable.plug(Y.Plugin.DataTableDataSource, {
            datasource : folderdatasource
        });
        folderdatatable.render(viewcontainer);
        // Load the data source
        folderdatatable.datasource.load({
            callback : {
                success : function (e) {
                    folderdatatable.set('strings.emptyMessage', M.util.get_string('emptyfolder', BLOCKCMLANGTABLE));
                },
                failure : function (e) {
                    // output custom message if there is one
                    M.block_course_message.check_return_result(e.data.response);
                    folderdatatable.set('strings.emptyMessage', M.util.get_string('mailerror', BLOCKCMLANGTABLE));
                }
            }
        });
        // the event handler should be in here so that a different one is created for each folder
        folderdatatable.on("selected", function (obj) {
            mailid = obj.record.get('id');
            open_mail(mailid, folder);
        });
        // save the folder YUI container for later -> this can be cleaned up
        if (folder == 'inbox') {
            inboxdatatable = folderdatatable;
        } else {
            sentdatatable = folderdatatable;
        }
    }

    /**
     * Small helper method that sets up the column data for the DataTable that
     * displays mail back to the user.
     *
     *  @param  {String} folder String identifying the folder to load ('inbox'|'sent').
     *
     *  @return {Object} Full column information for the datatable.
     *
     */
    function setup_data_table_columns(folder) {
        if (folder == 'sent') {
            column1key = "to";
            column1label = M.util.get_string('tolabel', BLOCKCMLANGTABLE);
            column3key = "sent";
        } else {
            column1key = "from";
            column1label = M.util.get_string('fromlabel', BLOCKCMLANGTABLE);
            column3key = "received";
        }

        var cols = [{
                key : column1key,
                label : column1label,
                width : '225px',
                nodeFormatter : function (o) {
                    if (o.data.status == 0 && folder != 'sent') {
                        o.cell.addClass('unread');
                    }
                    o.cell.set('text', o.value);
                }
            }, {
                key : "subject",
                label : M.util.get_string('subjectlabel', BLOCKCMLANGTABLE),
                width : '450px',
                nodeFormatter : function (o) {
                    if (o.data.status == 0 && folder != 'sent') {
                        o.cell.addClass('unread');
                    }
                    o.cell.set('text', o.value);
                }
            }, {
                key : column3key,
                label : M.util.get_string('datelabel', BLOCKCMLANGTABLE),
                width : '200px',
                className : 'align-right',
                nodeFormatter : function (o) {
                    if (o.data.status == 0 && folder != 'sent') {
                        o.cell.addClass('unread');
                    }
                    // multiplied by 1000 so that the argument is in milliseconds, not seconds
                    var date = Y.Date.parse(o.value * 1000);
                    var temp = Y.Date.format(date, {
                            format : "%m/%d/%Y %H:%M"
                        });
                    o.cell.set('text', temp);
                    // o.cell.set('text', o.value);
                }
            }
        ];

        return cols;
    }

    /**
     * This function opens the mail that it is passed.  While the function looks simple,
     * there's a considerable amount of code in the add_reply_handlers() function.
     *
     *  @param  {Integer} mailid The ID of the mail to open.
     *  @param  {String} folder String identifying the folder to load ('inbox'|'sent').
     *
     */
    function open_mail(mailid, folder) {
        // alert('opening mail with id: ' + mailid + ' from folder: ' + folder);
        Y.io(M.cfg.wwwroot + '/blocks/course_message/ajax_request.php', {
            method : 'POST',
            data : 'request=view' + '&courseid=' + courseid + '&id=' + mailid + '&folder=' + folder + '&sesskey=' + M.cfg.sesskey,
            on : {
                success : function (id, result) {
                    disable_all_content();
                    Y.one("#viewmail").setHTML(result.response);
                    Y.one("#viewmail").show();
                    check_for_new_messages();
                    replydelegate = Y.one('#viewmail').delegate('click', handle_reply_click, 'button', null, mailid);
                },
                failure : function (id, result) {
                    M.block_course_message.alerttext(M.util.get_string('viewmailerror', BLOCKCMLANGTABLE));
                }
            }
        });
    }

    /**
     * This function contains the event handler for the inline reply feature.  This is not
     * constructed intially because none of the id tags exist.  It is called by the
     * open_mail() function above.
     *
     *  @param  {Object} e This is the event object (a YUI default).
     *  @param  {Integer} mailid The ID of the mail to open.
     *
     */
    function handle_reply_click(e, mailid) {
        switch (this.get('id')) {
        case 'inlinereply':
            setup_reply('single');
            break;
        case 'inlinereplyall':
            setup_reply('all');
            break;
        case 'cancelreply':
            Y.one('#newreply').hide();
            if (editor == 'atto') {
                Y.one('#id_replyeditable').setHTML('');
            } else {
                tinyMCE.get('id_reply').setContent('');
            }
            Y.one('#sendmailfromreply').hide();
            Y.one('#inlinereply').show();
            Y.one('#inlinereplyall').show();
            Y.one('#cancelreply').hide();
            break;
        case 'sendmailfromreply':
            send_mail('reply', mailid, null, '#ccfromreply');
            break;
        case 'showthread':
            Y.one("#topthread").show();
            Y.one("#bottomthread").show();
            Y.one("#showthread").hide()
            Y.one("#closethread").show();
            break;
        case 'closethread':
            close_thread_display();
            break;
        case 'deletemail':
            delete_mail();
            break;
        }
    }

    /**
     * This function sets up the reply controls and stores the type of reply
     * that the user has requested (single or all).
     *
     *  @param  {String} folder String identifying the type of reply the user is composing ('single'|'all').
     *
     */
    function setup_reply(selectedreplytype) {
        // store the reply type
        replytype = selectedreplytype;
        Y.one('#inlinereply').hide();
        Y.one('#inlinereplyall').hide();
        Y.one('#sendmailfromreply').show();
        Y.one('#newreply').show();
        Y.one('#cancelreply').show();
        close_thread_display();
        adjust_rich_text('reply');
    }

    /**
     * This function sends a mail.  I've set it up so that now both the compose view
     * and the inline reply use this function.
     *
     *  @param  {String} sendtype The place where the user is sending from ('compose'|'reply').
     *  @param  {String} subjectorparent Based on the type of send, this identifies either the subject ['compose'] or parent ['reply'].
     *  @param  {Array} userto Array containing information on users to send mail to.  This is only required for a 'compose' send.
     *
     */
    function send_mail(sendtype, subjectorparent, userto, carboncopycontainer) {

        var usercarboncopy = new Array();
        get_send_userids(carboncopycontainer, usercarboncopy);
        if (editor == 'atto') {
            var node = Y.one('#id_' + sendtype + 'editable');
            var message = get_clean_HTML(node);
        } else {
            var message = tinyMCE.get('id_' + sendtype).getContent();
        }
        var encodedcontact = Y.JSON.stringify(userto);

        var draftid = 0;
        if (Y.one('#mformattachmentfrom' + sendtype).getStyle('display') != 'none') {
            draftid = (sendtype == 'compose') ? composedraftid : replydraftid;
        }
        // compose type has more detailed error checking
        if (sendtype == 'compose') {
            if (!M.block_course_message.validate_message(userto, subjectorparent, message)) {
                return 0;
            }

        } else {
            if (!M.block_course_message.validate_body(message)) {
                return 0;
            }
        }
        Y.one('#sendmailfrom' + sendtype).setAttribute("disabled", "disabled");
        var poststring = 'request=send' + '&message=' + message + '&courseid=' + courseid + '&attachment=' + draftid + '&sesskey=' + M.cfg.sesskey;
        if (usercarboncopy.length > 0) {
            poststring = poststring + '&cc=' + Y.JSON.stringify(usercarboncopy);
        }
        if (sendtype == 'compose') {
            poststring = poststring + '&mailTo=' + encodedcontact + '&subject=' + subjectorparent;
        } else {
            poststring = poststring + '&parent=' + subjectorparent + '&replytype=' + replytype;
        }
        Y.one("#senddialog").show();
        Y.io(M.cfg.wwwroot + '/blocks/course_message/ajax_request.php', {
            method : 'POST',
            data : poststring,
            on : {
                success : function (id, result) {
                    Y.one('#sendmailfrom' + sendtype).removeAttribute("disabled");
                    Y.one("#senddialog").hide();
                    M.block_course_message.check_return_result(result.response, true);
                },
                failure : function (id, result) {
                    Y.one('#sendmailfrom' + sendtype).removeAttribute("disabled");
                    Y.one("#senddialog").hide();
                    M.block_course_message.alerttext(M.util.get_string('mailnotsent', BLOCKCMLANGTABLE));
                }
            }
        });
    }

    /**
     * Clean the generated HTML content without modifying the editor content.
     * Note: This was taken from the ATTO editor code to mirror the cleaning.
     *
     * This includes removing all YUI ids from the generated content.
     *
     * @return {string} The cleaned HTML content.
     */
    function get_clean_HTML(node) {

        // Remove all YUI IDs.
        Y.each(node.all('[id^="yui"]'), function(node) {
            node.removeAttribute('id');
        });

        node.all('.atto_control').remove(true);
        html = node.get('innerHTML');

        // Revert untouched editor contents to an empty string.
        if (html === '<p></p>' || html === '<p><br></p>') {
            return '';
        }

        // Remove any and all nasties from source.
       return clean_HTML(html);
    }

    /**
     * Clean the specified HTML content and remove any content which could cause issues.
     * Note: This was taken from the ATTO editor code to mirror the cleaning.
     *
     * @method clean_HTML
     * @param {string} content The content to clean
     * @return {string} The cleaned HTML
     */
    function clean_HTML(content) {
        // Removing limited things that can break the page or are disallowed, like unclosed comments, style blocks, etc.

        var rules = [
            // Remove any style blocks. Some browsers do not work well with them in a contenteditable.
            // Plus style blocks are not allowed in body html, except with "scoped", which most browsers don't support as of 2015.
            // Reference: "http://stackoverflow.com/questions/1068280/javascript-regex-multiline-flag-doesnt-work"
            {regex: /<style[^>]*>[\s\S]*?<\/style>/gi, replace: ""},

            // Remove any open HTML comment opens that are not followed by a close. This can completely break page layout.
            {regex: /<!--(?![\s\S]*?-->)/gi, replace: ""},

            // Source: "http://www.codinghorror.com/blog/2006/01/cleaning-words-nasty-html.html"
            // Remove forbidden tags for content, title, meta, style, st0-9, head, font, html, body, link.
            {regex: /<\/?(?:title|meta|style|st\d|head|font|html|body|link)[^>]*?>/gi, replace: ""},

            // Craig added removal of &nbsp;.
            {regex: /&nbsp;/g, replace: " "}
        ];

        return filter_content_with_rules(content, rules);
    }

    /**
     * Take the supplied content and run on the supplied regex rules.
     * Note: This was taken from the ATTO editor code to mirror the cleaning.
     *
     * @method filter_content_with_rules
     * @param {string} content The content to clean
     * @param {array} rules An array of structures: [ {regex: /something/, replace: "something"}, {...}, ...]
     * @return {string} The cleaned content
     */
    function filter_content_with_rules(content, rules) {
        var i = 0;
        for (i = 0; i < rules.length; i++) {
            content = content.replace(rules[i].regex, rules[i].replace);
        }

        return content;
    }

    /**
     * This function closes the threaded display.
     *
     */
    function close_thread_display() {
        if (Y.one("#showthread") != null) {
            Y.one("#topthread").hide();
            Y.one("#bottomthread").hide();
            Y.one("#closethread").hide();
            Y.one("#showthread").show();
        }
    }

    /**
     * This function handles deleting a mail.  The event that fires when the
     * user clicks the delete button calls this function.
     *
     */
    function delete_mail() {
        var messageid = Y.one("#replymailid").get('value');
        var isok = confirm(M.util.get_string('deletewarning', BLOCKCMLANGTABLE));
        if (!isok) {
            return;
        }

        Y.one('#deletemail').setAttribute("disabled", "disabled");

        Y.io(M.cfg.wwwroot + '/blocks/course_message/ajax_request.php', {
            method : 'POST',
            data : 'request=delete' + '&messageid=' + messageid + '&courseid=' + courseid + '&sesskey=' + M.cfg.sesskey,
            on : {
                success : function (id, result) {
                    Y.one('#deletemail').removeAttribute("disabled");
                    display_inbox();
                },
                failure : function (id, result) {
                    Y.one('#deletemail').removeAttribute("disabled");
                    M.block_course_message.alerttext(M.util.get_string('deletemailerror', BLOCKCMLANGTABLE));
                }
            }
        });
    }

    /**
     * This method hides the children on the inbox iframe (resets the frame) and
     * then empties the view_message display.
     *
     */
    function disable_all_content() {
        Y.one('#rightContent').get('children').hide();
        Y.one('#viewmail').empty();
        if (Y.one('#new_reply') != null) {
            Y.one("#new_reply").hide();
        }
    }

    /**
     * This method is a bit of a hack.  When moodle renders the html editors on the
     * page, they are displayed very small if their outer div is hidden.  Since I must
     * hide them initially, I have to dynamically resize them after using this
     * method.
     *
     *  @param {String} fieldid String indentifying the rich text container to adjust.
     *
     */
    function adjust_rich_text(fieldid) {
        var suffix = (editor == 'atto') ? 'editable' : '_tbl';
        if (Y.one('#id_' + fieldid + suffix) != null) {
            Y.one('#id_' + fieldid + suffix).setStyle('width', '600');
            Y.one('#id_' + fieldid + suffix).setStyle('height', '300');
            // Tinymce editor requires an adjustment to the iframe as well.
            if (editor != 'atto') {
                Y.one('#id_' + fieldid + '_ifr').setStyle('width', '600');
                Y.one('#id_' + fieldid + '_ifr').setStyle('height', '300');
            }
        }
    }

    /**
     * This function checks whether the user has a message partially completed.
     *
     */
    function check_compose() {
        var subject = Y.one("#subjectfromcompose").get('value');
        var message = "";
        if (editor == 'atto') {
            // TODO: check and see if getHTML will work here.
        } else {
            if (typeof tinyMCE.get('id_compose') != 'undefined') {
                message = tinyMCE.get('id_compose').getContent();
            }
        }

        // if we move away from compose and either the subject or message field contains content, then ask the user if they want to flush it
        if ((currentpage != "compose") && ((subject != "") || (message != ""))) {
            var is_compose = confirm(M.util.get_string('discardmessagewarning', BLOCKCMLANGTABLE));
            if (!is_compose) {
                Y.one("#rightContent").get('children').hide();
                Y.one("#composeview").show();
                currentpage = "compose";
                return true;
            }
            reset_compose();
        }
        return false;
    }

    /**
     * This function wipes out the content on the compose page (to, subject, message).
     * Important Note: I have to wipe out both the HTML and reset the internal list of
     * the multivalue-input plugin.  There's no direct facing function to do this.
     *
     */
    function reset_compose() {
        Y.one('#subjectfromcompose').set('value', '');
        if (editor == 'atto') {
            Y.one('#id_composeeditable').setHTML('');
        } else {
            tinyMCE.get('id_compose').setContent('');
        }

        // strip out the HTML from the display
        var node = Y.one('#contactfromcompose').ancestor('ul');
        var lis = node.all('li');
        lis.each(function () {
            if (this.hasClass('yui3-multivalueinput-listitem')) {
                this.remove();
            }
        });
        // manually reset the list, note that it must be set to a valid array
        var node = Y.one('#contactfromcompose');
        node.mvi.set('values', []);
    }

    /**
     * This method checks to see if the reply delegate is current bound and
     * if so, detaches it.
     *
     */
    function check_for_reply_handler_detach() {
        if (typeof replydelegate != 'undefined') {
            replydelegate.detach();
        }
    }

    /**
     * This method adjusts the visibility of a folder on the page.
     *
     *  @param {String} folder String indentifying the folder to hide ('inbox'|'sent').
     *  @param {String} status String indentifying the proper status ('show'|'hide').
     *
     */
    function set_folder_visibility(folder, status) {
        var viewcontainer = '#' + folder + 'mail';
        var viewpaginator = '#' + folder + 'paginator';

        if (status == 'hide') {
            Y.one(viewcontainer).hide();
            Y.one(viewpaginator).hide();
        } else {
            Y.one(viewcontainer).show();
            Y.one(viewpaginator).show();
        }
    }

    /**
     * This method makes an ajax call to reload the contents of a folder.
     *
     *  @param {String} folder String indentifying the folder to hide ('inbox'|'sent').
     *  @param {String} status String indentifying the proper status ('show'|'hide').
     *
     */
    function refresh_folder(folder) {
        var handler;
        if (folder == 'inbox') {
            handler = inboxdatatable;
        } else {
            handler = sentdatatable;
        }

        // placeholder "loading..." string just in case their connection is slow
        handler.set('strings.emptyMessage', M.util.get_string('folderloading', BLOCKCMLANGTABLE));
        // Load the data source
        handler.datasource.load({
            callback : {
                success : function (e) {
                    handler.set('strings.emptyMessage', M.util.get_string('emptyfolder', BLOCKCMLANGTABLE));
                },
                failure : function (e) {
                    // output custom message if there is one
                    M.block_course_message.check_return_result(e.data.response);
                    handler.set('strings.emptyMessage', M.util.get_string('mailerror', BLOCKCMLANGTABLE));
                }
            }
        });

        set_folder_visibility(folder, 'show');
    }

    /**
     * This method handles displaying the inbox.  This is called in a two places,
     * (clicking inbox button + deleting mail) so I made it a function.
     *
     */
    function display_inbox() {
        currentpage = "inbox";

        disable_all_content();
        refresh_folder('inbox');
        Y.one("#inboxview").show();
        check_for_new_messages();
    }

    /**
     * This function handles updating the users settings.
     *
     *  @param {String} data Post response (JSON) that will be parsed and used to update the settings on the page.
     *
     */
    function display_settings(data) {
        var object = Y.JSON.parse(data);
        if (object.inbox) {
            Y.one("#inboxemailsetting").set('checked', true);
        } else {
            Y.one("#inboxemailsetting").set('checked', false);
        }
        if (object.sent) {
            Y.one("#sentemailsetting").set('checked', true);
        } else {
            Y.one("#sentemailsetting").set('checked', false);
        }
    }

    /**
     * This function creates an array with a list of user ids from the
     * multivalue-input control that is one the page.
     *
     *  @param {String} contactsdiv String identifying the ID of the div containing the contacts.
     *  @param {Array} userto Array that will be populated with the user/group IDs to send to.
     *
     */
    function get_send_userids(contactsdiv, userto) {
        var node = Y.one(contactsdiv);
        var contactslist = node.mvi.get('values');

        for (i = 0; i < contactslist.length; i++) {
            var index = Y.Array.indexOf(allcontacts, contactslist[i]);
            userto.push(allids[index]);
        }
    }

    // **********************************************************************
    // Extend Definition Starts Here
    // **********************************************************************
    Y.extend(INBOX, Y.Base, {
        // NOTE: setting up events can be done in two ways, 1) by simply giving the
        // event handler the function, or 2) by using Y.bind().  The second approach can
        // be used to set the context of the function.  That is, what the hidden "this"
        // pointer points to.  The second parameter in Y.bind() is used to set the context.

        /**
         * Initializer -> store a few variables + load the inbox + subscribe to events
         *
         *  @param  {Object} params The set of parameters passed to javascript from PHP.
         *
         */
        initializer : function (params) {
            courseid = params.courseid;
            composedraftid = params.composedraftid;
            replydraftid = params.replydraftid;
            editor = params.editor;
            currentpage = "inbox";

            check_for_new_messages();
            load_folder(params.folder);
            load_folder('sent');
            this.store_contacts(params);
            this.load_contacts_selector('#contactfromcompose');
            this.load_contacts_selector('#ccfromcompose');
            this.load_contacts_selector('#ccfromreply');
            M.block_course_message.setup_panels();

            if (editor == 'atto') {
                // wipe editor contents
                Y.one('#id_composeeditable').setHTML('');
                Y.one('#id_replyeditable').setHTML('');
            }

            // subscribe to events here
            Y.on('click', this.open_compose, '#composeli');
            Y.on('click', this.open_inbox, '#inboxli');
            Y.on('click', this.open_sent, '#sentli');
            Y.on('click', this.open_settings, '#settingsli');
            Y.on('click', this.submit_settings, '#submitsettings');
            Y.on('click', this.inbox_send_mail, '#sendmailfromcompose');
            Y.on('click', this.compose_open_attachment, '#openattachmentfromcompose');
            Y.on('click', this.compose_close_attachment, '#closeattachmentfromcompose');
            Y.on('click', this.reply_open_attachment, '#openattachmentfromreply');
            Y.on('click', this.reply_close_attachment, '#closeattachmentfromreply');
            this.timer = Y.later(15 * 60 * 1000, this, this.refresh_inbox);
            // click events for viewing message are in the add_reply_handlers() function
        },

        /**
         * This function sets up the allcontacts and allids variables.  It concatenates the list
         * of users and groups all into one big array.
         *
         *  @param {Object} params The set of parameters passed to javascript from PHP.
         *
         */
        store_contacts : function (params) {
            allcontacts[0] = M.util.get_string('allstudents', BLOCKCMLANGTABLE);
            allids[0] = 's1';
            allcontacts = allcontacts.concat(params.groups, params.contacts);
            allids = allids.concat(params.groupids, params.contactids);
        },

        /**
         * This function sets up the multivalue-input selector by giving it the list of
         * contacts.  The syntax is a bit awkward (double plug), but it appears to be the
         * only way that it can work.
         *
         *  @param {String} contactscontainer String that identifies the ID of the HTML container that will display the list of contacts.
         *
         */
        load_contacts_selector : function (contactcontainer) {
            var contactnode = Y.one(contactcontainer);
            contactnode.plug(Y.Plugin.AutoComplete, {
                resultFilters : 'phraseMatch',
                resultHighlighter : 'phraseMatch',
                minQueryLength : 0,
                source : allcontacts
            })
            contactnode.plug(Y.MultiValueInput, {
                placeholder : M.util.get_string('contactslabel', BLOCKCMLANGTABLE)
            });

            // This displays the entire list when the box is clicked
            contactnode.on('focus', function () {
                contactnode.ac.sendRequest(contactnode.ac.get('value'));
            });
            // This event fixes a subtle bug with the multivalue-input: the value field doesn't get wiped properly after a selection
            contactnode.ac.after('select', function () {
                contactnode.ac.set('value', '');
                // This could also fire a sendRequest to make the box pop back up
            });
        },

        /**
         * This function displays the compose pane.
         *
         *  @param {Object} e Event properties.
         *
         */
        open_compose : function (e) {
            e.preventDefault();
            adjust_rich_text('compose');
            currentpage = "compose";
            if (check_compose()) {
                return 0;
            }
            disable_all_content();
            check_for_new_messages();
            check_for_reply_handler_detach();
            Y.one('#composeview').show();
        },

        /**
         * This function opens the user's inbox when the <li> element is clicked.
         *
         *  @param {Object} e Event properties.
         *
         */
        open_inbox : function (e) {
            e.preventDefault();
            currentpage = "inbox";
            if (check_compose()) {
                return 0;
            }
            display_inbox();
            check_for_reply_handler_detach();
        },

        /**
         * This function opens the user's sent mail when the <li> element is clicked.
         *
         *  @param {Object} e Event properties.
         *
         */
        open_sent : function (e) {
            e.preventDefault();
            currentpage = "sent";
            if (check_compose()) {
                return 0;
            }
            disable_all_content();
            refresh_folder('sent');
            check_for_new_messages();
            Y.one("#sentview").show();
            check_for_reply_handler_detach();
        },

        /**
         * This function opens the user's settings page.
         *
         *  @param {Object} e Event properties.
         *
         */
        open_settings : function (e) {
            e.preventDefault();
            currentpage = "settings";
            if (check_compose()) {
                return 0;
            }
            check_for_reply_handler_detach();
            disable_all_content();
            check_for_new_messages();
            Y.one("#settingsview").show();

            Y.io(M.cfg.wwwroot + '/blocks/course_message/ajax_request.php', {
                method : 'POST',
                data : 'courseid=' + courseid + '&request=' + 'view_settings' + '&sesskey=' + M.cfg.sesskey,
                on : {
                    success : function (id, result) {
                        M.block_course_message.check_return_result(result.response);
                        display_settings(result.response);
                    },
                    failure : function (id, result) {
                        M.block_course_message.alerttext(M.util.get_string('loadsettingserror', BLOCKCMLANGTABLE));
                    }
                }
            });

        },

        /**
         * This function submits the user's settings for update.
         *
         *  @param {Object} e Event properties.
         *
         */
        submit_settings : function (e) {
            e.preventDefault();
            var inboxsetting = Y.one('#inboxemailsetting').get('checked');
            var sentsetting = Y.one('#sentemailsetting').get('checked');
            var displayonpagesetting = 'new_page';

            Y.one('#submitsettings').setAttribute("disabled", "disabled");

            Y.io(M.cfg.wwwroot + '/blocks/course_message/ajax_request.php', {
                method : 'POST',
                data : 'courseid=' + courseid + '&inboxsetting=' + inboxsetting + '&sentsetting=' + sentsetting + '&displayonpagesetting=' + displayonpagesetting + '&request=' + 'edit_settings' + '&sesskey=' + M.cfg.sesskey,
                on : {
                    success : function (id, result) {
                        Y.one('#submitsettings').removeAttribute("disabled");
                        M.block_course_message.check_return_result(result.response);
                    },
                    failure : function (id, result) {
                        Y.one('#submitsettings').removeAttribute("disabled");
                        M.block_course_message.alerttext(M.util.get_string('updatesettingserror', BLOCKCMLANGTABLE));
                    }
                }
            });
        },

        /**
         * This function sends the user's mail from the compose view.
         *
         *  @param {Object} e Event properties.
         *
         */
        inbox_send_mail : function (e) {
            e.preventDefault();

            var subject = Y.one("#subjectfromcompose").get('value');
            var userto = new Array();
            get_send_userids('#contactfromcompose', userto);
            // check for no or invalid contact
            if ((typeof userto[0] == "undefined") || (userto[0] == 0)) {
                M.block_course_message.alerttext(M.util.get_string('nocontactswarning', BLOCKCMLANGTABLE));
                return 0;
            }
            send_mail('compose', subject, userto, '#ccfromcompose');
        },

        /**
         * This function opens the attachment handler from the compose view.
         *
         *  @param {Object} e Event properties.
         *
         */
        compose_open_attachment : function (e) {
            e.preventDefault();
            Y.one("#openattachmentfromcompose").hide();
            Y.one("#closeattachmentfromcompose").show();
            Y.one("#mformattachmentfromcompose").show();
        },

        /**
         * This function closes the attachment handler from the compose view.
         *
         *  @param {Object} e Event properties.
         *
         */
        compose_close_attachment : function (e) {
            e.preventDefault();
            Y.one("#openattachmentfromcompose").show();
            Y.one("#closeattachmentfromcompose").hide();
            Y.one("#mformattachmentfromcompose").hide();
        },

        /**
         * This function opens the attachment handler from the reply view.
         * I'd like to collapse the two open handlers, but I have to learn
         * more about event delegation.
         *
         *  @param {Object} e Event properties.
         *
         */
        reply_open_attachment : function (e) {
            e.preventDefault();
            Y.one("#openattachmentfromreply").hide();
            Y.one("#closeattachmentfromreply").show();
            Y.one("#mformattachmentfromreply").show();
        },

        /**
         * This function closes the attachment handler from the reply view.
         * Similar to the note above, I'd like to collapse the event handlers
         * onto fewer functions if possible.
         *
         *  @param {Object} e Event properties.
         *
         */
        reply_close_attachment : function (e) {
            e.preventDefault();
            Y.one("#openattachmentfromreply").show();
            Y.one("#closeattachmentfromreply").hide();
            Y.one("#mformattachmentfromreply").hide();
        },

        /**
         * This function checks for new messages and re-displays the inbox if the
         * user was already on this page.
         *
         *  @param {Object} e Event properties.
         *
         */
        refresh_inbox : function (e) {
            check_for_new_messages();
            if (currentpage == "inbox") {
                display_inbox();
            }
        }

        //--> Event Handlers End Here
    }, {
        NAME : INBOXNAME,
        ATTRS : {
            courseid : {},
            contacts : {},
            contactids : {},
            groups : {},
            groupids : {},
            folder : {},
            editor : {},
            composedraftid : {},
            replydraftid : {}
        }
    });
    M.block_course_message = M.block_course_message || {};
    M.block_course_message.init_inbox = function (params) {
        return new INBOX(params);
    };
}, '@VERSION@', {
    requires : ['base', 'io-base', "datatable", "datasource-io", "datasource-jsonschema", "datatable-datasource", 'datatable-column-widths', 'datatable-sort', 'datatype-date', 'gallery-datatable-selection', "gallery-datatable-paginator", 'gallery-paginator-view', 'autocomplete', 'autocomplete-filters', 'autocomplete-highlighters', "gallery-multivalue-input", 'json-stringify', 'datatable-message', 'node-event-delegate']
});