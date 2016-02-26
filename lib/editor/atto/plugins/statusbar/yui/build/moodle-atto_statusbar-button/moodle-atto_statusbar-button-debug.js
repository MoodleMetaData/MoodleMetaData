YUI.add('moodle-atto_statusbar-button', function (Y, NAME) {

/**
 * Created by jandres on 31/05/15.
 */

var CSS = {
    STATUSBAR: 'editor_atto_statusbar',
    STATUSBAR_ELEM: 'editor_atto_statusbar_elem',
    STATUSBAR_SEPARATOR: 'editor_atto_statusbar_separator'
};

/**
 * @class EditorStatusbarAbstract
 * @brief Abstract class for Atto Editor Statusbar.
 * @constructor
 *
 * This abstract class is created to be completely environment dependent. May
 * it be fully js native, or YUI dependent, this remains constant. The main
 * purpose is to make this testable.
 *
 * Methods to implement in the concrete implementation of
 * EditorStatusbarAbstract.
 * 1. this._createNode(htmlString): Creates a "node" given an htmlString. e.g.
 *      YUI.Node.create(htmlString).
 * 2. this._deleteChildNode(nodeId): Delete the node corresponding to the nodeId
 *      from the statusbar (parent node).
 * 3. this._appendChild(parentNode, node): Append the node to the parentNode.
 * 4. this._generateId(node): Generate DOM id for a given node.
 */
function EditorStatusbarAbstract() {
}

EditorStatusbarAbstract.prototype = {
    /**
     * A reference to the statusbar node. Other plugins might want to use this
     * for full control.
     *
     * @property toolbar
     * @type Node
     */
    statusbar: null,

    /**
     * An object treated as a map from id to Node.
     */
    statusbarId2NodeMap: {},
    statusbarId2NodeWrapMap: {},

    /**
     * Use this to add a node to statusbar.
     * @param node {Y.Node} node to be appended.
     */
    addStatusbarNode: function (node) {
        var nodeId = this._generateId(node);  // Generate id if there is none.
        var nodeWrapHTML = "<div class='" + CSS.STATUSBAR_ELEM + "'/>";
        var nodeWrap = this._createNode(nodeWrapHTML);
        this._appendChild(nodeWrap, node);

        this.statusbarId2NodeMap[nodeId] = node;
        this.statusbarId2NodeWrapMap[nodeId] = nodeWrap;

        this._appendChild(this.statusbar, nodeWrap);
    },

    /**
     * Use this to remove a node from statusbar.
     * @param node {Node} node to be removed. (YUI.Node in this implementation).
     */
    removeStatusbarNode: function (node) {
        var nodeId = this._generateId(node);  // Generate id if there is none.

        /*
         * Check if node is found. If not: Quietly exit.
         * Else: Delete the nodeId on both "map" Object.
         */
        var nodeIdFound =
            typeof this.statusbarId2NodeMap[nodeId] !== "undefined";

        if (nodeIdFound) {
            // Remove both from their corresponding parent.
            this._deleteChildNode(nodeId);

            // Delete the node and nodeWrapper from both from memory.
            delete this.statusbarId2NodeMap[nodeId];
            delete this.statusbarId2NodeWrapMap[nodeId];
        }
    },

    /**
     * This is the initialization function for statusbar. This ensures that
     * statusbar is initialized once.
     */
    setupStatusbar: function () {
        // Check if this is initialized. If so, don't initialize again.
        if (this.isStatusbarInitialized() === false) {
            var statusbarHTML = "<div class='" + CSS.STATUSBAR + "'></div>";
            this.statusbar = this._createNode(statusbarHTML);
            this._appendChild(this._wrapper, this.statusbar);
        }
    },

    /**
     * @return {bool} true if statusbar is initialized.
     */
    isStatusbarInitialized: function () {
        return this.statusbar !== null;
    },

    /**
     * Generate the separator html string.
     * @returns {string} separator html string.
     * @private
     */
    getSeparator: function () {
        return "<div class='" + CSS.STATUSBAR_SEPARATOR + "'></div>";
    }
};/**
 * Created by jandres on 29/05/15.
 */

/**
 * Mixer class for Y.M.editor_atto.Editor. This introduce the status bar.
 * @constructor
 */
function EditorStatusbar() {
}

EditorStatusbar.ATTRS = {};

EditorStatusbar.prototype = {
    /**
     * @param htmlString {string} html string.
     * @returns {htmlString} Library dependent "Node", may it be the native
     *                       javascript's node, or YUI.Node.
     * @private
     */
    _createNode: function (htmlString) {
        return Y.Node.create(htmlString);
    },

    /**
     * @param nodeId {Number} delete the corresponding child node from one of
     *                        statusbar's children.
     * @private
     */
    _deleteChildNode: function (nodeId) {
        this.statusbarId2NodeMap[nodeId].remove(true);
        this.statusbarId2NodeWrapMap[nodeId].remove(true);
    },

    /**
     * @param parentNode {Y.Node}
     * @param node {Y.Node} attach as one of parentNode's children.
     * @private
     */
    _appendChild: function (parentNode, node) {
        parentNode.appendChild(node);
    },

    /**
     * @param node {Y.Node}
     * @returns {*} Id of node if one exist. Else return the generated id.
     * @private
     */
    _generateId: function(node) {
        return node.generateID();
    }
};
Y.Base.mix(EditorStatusbar, [EditorStatusbarAbstract]);

Y.Base.mix(Y.M.editor_atto.Editor, [EditorStatusbar]);

//var COMPONENTNAME = 'atto_statusbar';

/**
 * Since atto_editor seems to require that all plugins be Button, we have no
 * choice but to setup statusbar in a button class, in which we also acquire
 * settings from settings.php.
 *
 * @type {button}
 */
Y.namespace('M.atto_statusbar').Button =
    Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {
        initializer: function () {
            this.setupStatusbar();
        },

        setupStatusbar: function () {
            var host = this.get('host');
            host.setupStatusbar();
        }
    }, {
        ATTRS: {}
    });

}, '@VERSION@', {"requires": ["moodle-editor_atto-plugin"]});
