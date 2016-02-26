Statusbar

QUICK INSTALL
=============
Put this entire directory at:

PATHTOMOODLE/lib/editors/atto/plugins/statusbar

Visit your site notifications page to install the new plugins.

After installing the plugin, it will still not show up. To make it show up, go
to **atto toolbar settings** and go to the _toolbar config_ textbox. At the
very bottom, add **statusbar = statusbar**. This will load statusbar plugin
during startup, and also makes statusbar its own group.

## Basic Usage
Suppose you have an atto plugin, with the following initialization function:

```Javascript
initializer: function () {
  var host = this.get('host');  // Access Editor object.

  // Call to ensure initialization (don't worry about calling it again).
  host.setupStatusbar();

  // Add Y.Node's
  var exampleNode = Y.Node.create("<p>Hello World</p>");
  host.addStatusbarNode(exampleNode);

  // In case you want to delete.
  host.removeStatusbarNode(exampleNode);
}
```

In real world, you probably want to have a reference on exampleNode, especially
if you are going to delete it again.

That's it! YUI handles the rest. For instance:

```Javascript
// status node is updated.
exampleNode.setHTML("<p>Goodbye World</p>");
```

## Installing Statusbar Plugin
To install plugin meant for statusbar (e.g. Count++ or countplusplus), install
it like other atto plugin. Thus copy the main plugin folder to the atto's plugin
directory.

After installing the plugin, it will still not show up. To make it show up, go
to **atto toolbar settings** and go to the _toolbar config_ textbox. If you
have followed the statusbar installation instruction above, there should be
a statusbar group. Simply append the plugin there. For instance, for the
_Count++_ plugin:

statusbar = statusbar, countplusplus