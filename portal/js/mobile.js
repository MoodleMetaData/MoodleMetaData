
// Mobile detection script to load the mobile css 
// Will be most notable on 

if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
    loadMobile();
}

function loadDesktop() {
    $("#mobilecss").html('');
    $("#switchlayoutlink").remove();
    $('#mobileswitch').ready(function () {
        $('#mobileswitch').append($('<a>',{
        id: 'switchlayoutlink',
        text: 'Switch to mobile view',
        href: 'javascript:loadMobile()',
        }));
    });

}

function loadMobile() {
    $("#switchlayoutlink").remove();
    $.get("css/mobile.css", function(css) {
        $('#mobilecss')
          .html(css)
          .appendTo("head");
    });
    $('#mobileswitch').ready(function () {
        $('#mobileswitch').append($('<a>',{
        id: 'switchlayoutlink',
        text: 'Switch to desktop view',
        href: 'javascript:loadDesktop()',
        }));
    });
}