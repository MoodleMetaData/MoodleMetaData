// taken from http://yuilibrary.com/yui/docs/history/history-tabview.html#

YUI().use('history', 'tabview', function (Y) {
  var history = new Y.HistoryHash(),
      tabview = new Y.TabView({srcNode: '#demo'});

  // Render the TabView widget to turn the static markup into an
  // interactive TabView.
  tabview.render();

  // Set the selected tab to the bookmarked history state, or to
  // the first tab if there's no bookmarked state.
  tabview.selectChild(history.get('tab') || 0);

  // Store a new history state when the user selects a tab.
  tabview.after('selectionChange', function (e) {
    // If the new tab index is greater than 0, set the "tab"
    // state value to the index. Otherwise, remove the "tab"
    // state value by setting it to null (this reverts to the
    // default state of selecting the first tab).
    history.addValue('tab', e.newVal.get('index') || null);
  });

  // Listen for history changes from back/forward navigation or
  // URL changes, and update the tab selection when necessary.
  Y.on('history:change', function (e) {
    // Ignore changes we make ourselves, since we don't need
    // to update the selection state for those. We're only
    // interested in outside changes, such as the ones generated
    // when the user clicks the browser's back or forward buttons.
    if (e.src === Y.HistoryHash.SRC_HASH) {

      if (e.changed.tab) {
        // The new state contains a different tab selection, so
        // change the selected tab.
        tabview.selectChild(e.changed.tab.newVal);
      } else if (e.removed.tab) {
        // The tab selection was removed in the new state, so
        // select the first tab by default.
        tabview.selectChild(0);
      }

    }
  });
});