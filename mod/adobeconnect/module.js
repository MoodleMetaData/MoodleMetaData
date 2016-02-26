M.mod_adobeconnect = {};

M.mod_adobeconnect.init = function(Y, args) {
};

M.mod_adobeconnect.testconnection = function (Y, args) {
};

M.mod_adobeconnect.checkbox = function (Y) {
    var tables = Y.all('table[name^="recordingtable_"]');

    tables.each( function (table) {
        var thead = table.one('thead');
        var tbody = table.one('tbody');
        var checkboxes =  tbody.all('input[name^="scoid[]"]');

        var all = thead.one('input[name^="sco-all-"]');
        if (all == null) {
            return;
        }
        all.on('click', function (){
            var check = this.get('checked');
            checkboxes.each(function (box){
                box.set('checked', check);
            });
        });
    });
};

M.mod_adobeconnect.recording = function (Y) {
    var formObj = Y.one("#recEditForm");
    // this is the callback for the form's submit event
    formObj.on('submit', function (e) {
        // prevent default form submission
        e.preventDefault();

        Y.use("io-form", function(Y) {

            var cfg = {
                method: formObj.method,
                form: {
                    id: formObj,
                    useDisabled: true
                },
                timeout:30000
            };

            // Define a function to handle the response data.
            function onStart(id, arguments) {
                Y.one("#recResponse").removeClass('rec_error');
                document.getElementById('recResponse').innerHTML = 'Saving ...';
                Y.one("#loading").removeClass('loading_hidden');
            };

            function onSuccess(transactionid, o, arguments) {
                // JSON.parse throws a SyntaxError when passed invalid JSON
                Y.one("#loading").addClass('loading_hidden');
                try {
                    var json = JSON.parse(o.responseText);
                    if(json._success) {
                        var data = json.data;
                        formObj.one("#name").set('value', data.name);
                        formObj.one("#description").set('value', data.description);
                        document.title = "Edit recording - "+ data.name;
                        document.getElementById('recResponse').innerHTML = "Saved!";
                        window.setTimeout(function(){document.getElementById('recResponse').innerHTML = '';},3000);
                    } else {
                        Y.one("#recResponse").addClass('rec_error');
                        document.getElementById('recResponse').innerHTML = "Error saving changes.";
                    }
                } catch (e) {
                    Y.one("#recResponse").addClass('rec_error');
                    document.getElementById('recResponse').innerHTML = "Invalid data returned.";
                }
            }

            function onFailure(transactionid, response, arguments) {
                Y.one("#loading").addClass('loading_hidden');
                Y.one("#recResponse").addClass('rec_error');
                document.getElementById('recResponse').innerHTML = 'Error saving changes.';
            }

            // Subscribe to "io.success".
            Y.on('io:success', onSuccess, Y, true);
            // Subscribe to "io.failure".
            Y.on('io:failure', onFailure, Y, 'Transaction Failed');
            Y.on('io:start', onStart, Y, true);

            // Start the transaction.
            var request = Y.io('/mod/adobeconnect/recording.php', cfg);
        });
    });
    // call our submit function when the submit event occurs

}