/**
 * Create upload course type button event.
 */
YUI().use('node', 'button', function (Y) {
    var upload_ctype = Y.one('#upload_ctype');
    upload_ctype.on('click', function(e){
        var list = Y.one('#id_course_type');

        // TODO: read the file, then manipulate the list.
        list.append("<option>TEST</option>");    
    })
});

YUI.add('ctype_module', function (Y) {
    Y.CTypeModule = {
        addCType: function () {
            //alert('Hello!');
            var list = Y.one('#id_course_type');
            var option = document.createElement("option");
            option.text = "Kiwi";
            x.add(option);
        }
    };
});
