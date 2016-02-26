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
 * This file contains the YUI module for the datatable that lets a user join a
 * particular group.
 *
 * @package    block_skills_group
 * @category   block
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
YUI.add('moodle-block_skills_group-join', function (Y) {
    // **********************************************************************
    // define vars and objects here
    // **********************************************************************
    var JOINNAME = 'join';
    var BLOCKSGLANGTABLE = 'block_skills_group';
    var courseid;
    var groupingid;
    var scorestable;
    var selectedrow;
    var maxwidth = 900;

    // block constructor
    var JOIN = function () {
        JOIN.superclass.constructor.apply(this, arguments);
    };

    /**
     * This method loads the main datatable.  The available groups are retrieved via
     * an AJAX call, loaded into a DataSource, and then loaded into the DataTable.
     *
     */
    function load_data() {
        var viewcontainer = '#availablegroups';
        var viewpaginator = '#groupspaginator';

        // Startup -> always grab messages from folder
        var scoresdatasource = new Y.DataSource.IO({
                source : M.cfg.wwwroot + '/blocks/skills_group/ajax_request.php?courseid=' + courseid + '&request=get_group_stats' + '&sesskey=' + M.cfg.sesskey
            });
        scoresdatasource.plug(Y.Plugin.DataSourceJSONSchema, {
            schema : {
                resultListLocator : "rows"
            }
        });
        // Setup the Datatable
        scorestable = new Y.DataTable
            ({
                columns : [{key: 'name', label: 'Name', width: '225px'}],
                scrollable : 'y',
                sortable : true,
                // Selection options
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
        scorestable.set('strings.emptyMessage', M.util.get_string('groupsloading', BLOCKSGLANGTABLE));
        scorestable.plug(Y.Plugin.DataTableDataSource, {
            datasource : scoresdatasource
        });
        scorestable.render(viewcontainer);
        // Load the data source
        scorestable.datasource.load({
            callback : {
                success : function (e) {
                    add_skills_columns(e.data.response);
                    scorestable.set('strings.emptyMessage', M.util.get_string('emptygroups', BLOCKSGLANGTABLE));
                },
                failure : function (e) {
                    // output custom message if there is one
                    var response = Y.JSON.parse(e.data.response);
                    set_status_text(response.text, 'error');
                    scorestable.set('strings.emptyMessage', M.util.get_string('groupsloaderror', BLOCKSGLANGTABLE));
                }
            }
        });
        // Save row that is clicked on.
        scorestable.on("selected", function (obj) {
            selectedrow = obj.record.get('id');
        });
    }

    /**
     * This method adds a column for each skill to the datatable.
     *
     *  @param {object} response Returned JSON response from datatable loading.
     *
     */
    function add_skills_columns(response) {
        var parsedresponse = Y.JSON.parse(response);
        var skillslist = parsedresponse.skills;
        var width = maxwidth / skillslist.length;
        for(i = 0; i < skillslist.length; i++) {
            scorestable.addColumn({key: i.toString(), label: skillslist[i], width: width.toString() + 'px'});
        }
    }

    /**
     * This method makes an ajax call to reload the contents of a datatable.
     *
     */
    function refresh_table() {
        var handler = scorestable;

        if(handler) {
            // placeholder "loading..." string just in case their connection is slow
            handler.set('strings.emptyMessage', M.util.get_string('groupsloading', BLOCKSGLANGTABLE));
            handler.datasource.load({
                callback : {
                    success : function (e) {
                        handler.set('strings.emptyMessage', M.util.get_string('emptygroups', BLOCKSGLANGTABLE));
                    },
                    failure : function (e) {
                        // output custom message if there is one
                        var response = Y.JSON.parse(e.data.response);
                        set_status_text(response.text, 'error');
                        handler.set('strings.emptyMessage', M.util.get_string('groupsloaderror', BLOCKSGLANGTABLE));
                    }
                }
            });
        }
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
    Y.extend(JOIN, Y.Base, {

        /**
         * Initializer -> store a few variables + load the inbox + subscribe to events
         *
         *  @param  {Object} params The set of parameters passed to javascript from PHP.
         *
         */
        initializer : function (params) {
            courseid = params.courseid;
            groupingid = params.groupingid;
            if(params.errorstring == null) {
                load_data();
            } else {
                set_status_text(params.errorstring, 'error');
            }

            // subscribe to events here
            Y.on('click', this.submit, '#id_submitbutton');
            Y.on('click', this.refresh, '#refresh');
            Y.on('click', this.return_to_course, '#return');
            // refresh page every 3 minutes (5th parameter = periodic)
            this.timer = Y.later(3 * 60 * 1000, this, this.refresh, null, true);
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

            var groupid = selectedrow;

            Y.one('#id_submitbutton').setAttribute("disabled", "disabled");
            // Blank out status text while the AJAX request happens.
            set_status_text(' ', 'success');

            Y.io(M.cfg.wwwroot + '/blocks/skills_group/ajax_request.php', {
                method : 'POST',
                data : 'courseid=' + courseid + '&request=join_group' + '&groupid=' + groupid + '&groupingid=' + groupingid + '&sesskey=' + M.cfg.sesskey,
                on : {
                    success : function (id, result) {
                        var response = Y.JSON.parse(result.response);
                        if(response.result == "true") {
                            set_status_text(response.text, 'success');
                        } else {
                            set_status_text(response.text, 'error');
                        }
                    },
                    failure : function (id, result) {
                        Y.one('#id_submitbutton').removeAttribute("disabled");
                        set_status_text(M.util.get_string('groupjoinerror', BLOCKSGLANGTABLE), 'error');
                    }
                }
            });
        },

        /**
         * This function refreshes the data table.
         *
         */
        refresh : function () {
            refresh_table();
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
        NAME : JOINNAME,
        ATTRS : {
            courseid : {},
            groupingid : {},
            errorstring : {}
        }
    });
    M.block_skills_group = M.block_skills_group || {};
    M.block_skills_group.init_join = function (params) {
        return new JOIN(params);
    };
}, '@VERSION@', {
    requires : ['base', 'io-base', "datatable", "datatable-mutable", "datasource-io", "datasource-jsonschema", "datatable-datasource", 'datatable-column-widths', 'datatable-sort', 'datatype-date', 'gallery-datatable-selection', "gallery-datatable-paginator", 'gallery-paginator-view', 'json-stringify', 'datatable-message', 'node-event-delegate', 'json-parse']
});