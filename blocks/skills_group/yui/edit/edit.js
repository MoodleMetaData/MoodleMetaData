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
 * This file contains the YUI module for the edit_group page that lets a user
 * move classmates in and out of their group.
 *
 * @package    block_skills_group
 * @category   block
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
YUI.add('moodle-block_skills_group-edit', function (Y) {
    // **********************************************************************
    // define vars and objects here
    // **********************************************************************
    var EDITNAME = 'edit';
    var BLOCKSGLANGTABLE = 'block_skills_group';
    var ANCHORTAG = '<a href';
    var courseid;
    var groupid;
    var maxgroupsize;
    var allnames = new Array();
    var allids = new Array();

    // block constructor
    var EDIT = function () {
        EDIT.superclass.constructor.apply(this, arguments);
    };

    /**
     * This function creates an array with a list of user ids from the
     * multivalue-input control that is one the page.
     *
     *  @param {String} membersdiv String identifying the ID of the div containing the members.
     *  @param {Array} userto Array that will be populated with the user/group IDs to send to.
     *
     */
    function get_userids(membersdiv, userto) {
        var node = Y.one(membersdiv);
        var memberslist = node.mvi.get('values');

        for (i = 0; i < memberslist.length; i++) {
            var index = Y.Array.indexOf(allnames, memberslist[i]);
            userto.push(allids[index]);
        }
    }

    /**
     * This function updates the list of potential selections to remove those that have already
     * been selected.
     *
     *  @param {String} container String that identifies the ID of the HTML container that will display the list of members.
     *
     */
    function update_mvi_source(container) {
        var node = Y.one(container);
        var sourcearray = allnames.slice();
        var memberslist = node.mvi.get('values');
        for (i = 0; i < memberslist.length; i++) {
            var index = Y.Array.indexOf(sourcearray, memberslist[i]);
            if (index > -1) {
                sourcearray.splice(index, 1);
            }
        }
        node.ac.set('source', sourcearray);
    }

    /**
     * This function updates the status text at the top of the form to display messages
     * to the user.  One of two types of classes is added {'success', 'error'}, while the
     * other is removed.
     *
     *  @param {String} text Text to display on the screen.
     *  @param {String} type Type of message -> {'success'|'error'}.
     *
     */
    function set_status_text(text, type) {
        var node = Y.one('#statustext');
        node.setHTML('<p>' + text + '</p>');
        if(type == 'success') {
            node.addClass('success');
            if(node.hasClass('error')) {
                node.removeClass('error');
            }
        } else {
            node.addClass('error');
            if(node.hasClass('success')) {
                node.removeClass('success');
            }
        }
    }

    // **********************************************************************
    // Extend Definition Starts Here
    // **********************************************************************
    Y.extend(EDIT, Y.Base, {
        /**
         * Initializer -> store a few variables + load the inbox + subscribe to events
         *
         *  @param  {Object} params The set of parameters passed to javascript from PHP.
         *
         */
        initializer : function (params) {
            courseid = params.courseid;
            groupid = params.groupid;
            maxgroupsize = params.maxgroupsize;
            if(params.errorstring == null) {
                this.store_members(params);
                this.load_member_selector('#groupmembers');
                this.pre_fill_group_members('#groupmembers', params.groupmembernames);
                update_mvi_source('#groupmembers');
            } else {
                set_status_text(params.errorstring, 'error');
            }

            // subscribe to events here
            Y.on('click', this.submit, '#id_submitbutton');
            Y.on('click', this.return_to_course, '#return');
        },

        /**
         * This function sets up the allnames and allids variables.
         *
         *  @param {Object} params The set of parameters passed to javascript from PHP.
         *
         */
        store_members : function (params) {
            allnames = allnames.concat(params.groupmembernames, params.availablenames);
            allids = allids.concat(params.groupmemberids, params.availableids);
        },

        /**
         * This function sets up the multivalue-input selector by giving it the list of
         * group members.  The syntax is a bit awkward (double plug), but it appears to be the
         * only way that it can work.
         *
         *  @param {String} container String that identifies the ID of the HTML container that will display the list of members.
         *
         */
        load_member_selector : function (container) {
            var membernode = Y.one(container);
            membernode.plug(Y.Plugin.AutoComplete, {
                resultFilters : 'phraseMatch',
                resultHighlighter : 'phraseMatch',
                minQueryLength : 0,
                source : allnames
            })
            membernode.plug(Y.MultiValueInput, {
                placeholder : M.util.get_string('groupplaceholder', BLOCKSGLANGTABLE)
            });

            // This displays the entire list when the box is clicked
            membernode.on('focus', function () {
                membernode.ac.sendRequest(membernode.ac.get('value'));
            });
            // This event fixes a subtle bug with the multivalue-input: the value field doesn't get wiped properly after a selection
            membernode.ac.after('select', function () {
                membernode.ac.set('value', '');
                // This could also fire a sendRequest to make the box pop back up
                update_mvi_source(container);
            });
            // Also update the master list when the user removes a member
            var listcontainer = Y.one(".yui3-multivalueinput-content");
            listcontainer.after('click', function () {
                update_mvi_source(container);
            });
        },

        /**
         * This function pre-fills the multi-value input with existing group members.
         *
         *  @param {String} container String that identifies the ID of the HTML container that will display the list of members.
         *  @param {Array} members Array consisting of names of existing group members.
         *
         */
        pre_fill_group_members : function(container, members) {
            var node = Y.one(container);
            for(i = 0; i < members.length; i++) {
                node.ac.set('value', members[i]);
                // _appendItem() takes the current autocomplete "value" and adds to list
                node.mvi._appendItem();
                node.ac.set('value', '');
            }
        },

        /**
         * This function submits the user's group members/settings to be updated.
         *
         * Note: the list of users may NOT be unique.  I have chosen to clean this up
         * in php afterwards.
         *
         *  @param {Object} e Event properties.
         *
         */
        submit : function (e) {
            e.preventDefault();

            var allowjoin = Y.one('#allowuserstojoin').get('checked');
            var userto = new Array();
            get_userids('#groupmembers', userto);
            // check for too many members
            if (userto.length > (maxgroupsize - 1)) {
                set_status_text(M.util.get_string('toomanymembers', BLOCKSGLANGTABLE), 'error');
                return 0;
            }
            var encodedmembers = Y.JSON.stringify(userto);

            Y.one('#id_submitbutton').setAttribute("disabled", "disabled");
            // Blank out status text while the AJAX request happens.
            set_status_text(' ', 'success');

            Y.io(M.cfg.wwwroot + '/blocks/skills_group/ajax_request.php', {
                method : 'POST',
                data : 'courseid=' + courseid + '&request=add_members' + '&groupid=' + groupid + '&members=' + encodedmembers + '&allowjoin=' + allowjoin + '&sesskey=' + M.cfg.sesskey,
                on : {
                    success : function (id, data) {
                        Y.one('#id_submitbutton').removeAttribute("disabled");
                        var phpdata = Y.JSON.parse(data.response);
                        if (phpdata.result == "true") {
                            set_status_text(M.util.get_string('groupupdatesuccess', BLOCKSGLANGTABLE), 'success');
                        } else {
                            // On error: force page reload.
                            window.onbeforeunload = null;
                            location.reload(true);
                        }
                    },
                    failure : function (id, data) {
                        Y.one('#id_submitbutton').removeAttribute("disabled");
                        set_status_text(M.util.get_string('groupupdateerror', BLOCKSGLANGTABLE), 'error');
                    }
                }
            });
        },

        /**
         * This function returns the user back to the main course page.
         *
         *  @param {Object} e Event properties.
         *
         */
        return_to_course : function (e) {
            e.preventDefault();
            window.location = M.cfg.wwwroot + '/course/view.php?id=' + courseid;
        }

        //--> Event Handlers End Here
    }, {
        NAME : EDITNAME,
        ATTRS : {
            courseid : {},
            groupid : {},
            availablenames : {},
            availableids : {},
            groupmembernames : {},
            groupmemberids : {},
            errorstring: {}
        }
    });
    M.block_skills_group = M.block_skills_group || {};
    M.block_skills_group.init_edit = function (params) {
        return new EDIT(params);
    };
}, '@VERSION@', {
    requires : ['base', 'io-base', 'autocomplete', 'autocomplete-filters', 'autocomplete-highlighters', "gallery-multivalue-input", 'json-stringify', 'node-event-delegate']
});