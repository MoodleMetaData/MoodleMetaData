/**
 * Atto text editor integration version file.
 * @package atto_statusbar
 * @copyright 2015 Joey Andres <jandres@ualberta.ca>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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