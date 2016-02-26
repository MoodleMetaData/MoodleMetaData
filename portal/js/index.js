if (top != self) {
    top.location = window.location;
}

$(document).ready(function () {
    $('#divRss').FeedEk({
        FeedUrl: 'https://support.ctl.ualberta.ca/rss/index.php?/News/Feed/Index/0',
        MaxCount: 5,
        ShowDesc: true,
        ShowPubDate: true,
        TitleLinkTarget: '_blank'
    });
    $('.glyphicons.circle_question_mark').tooltip();
    $("form button[type=submit]").click(function () {
        $("button[type=submit]", $(this).parents("form")).removeAttr("clicked");
        $(this).attr("clicked", "true");
    });


// Portal responsive phone view menu toggle.
    $("#closedicon").click(function () {
        $("#closedicon").removeClass('mobileMenuToggleClosed');
        $("#openicon").addClass('mobileMenuToggleClosed');
        $(".jumbotron .menu").addClass('mobileMenuOpen');
        $("#wrapper").addClass('mobileMenuOpen');
    });

    $("#openicon").click(function () {
        $("#openicon").removeClass("mobileMenuToggleClosed");
        $("#closedicon").addClass("mobileMenuToggleClosed");
        $(".jumbotron .menu").removeClass('mobileMenuOpen');
        $("#wrapper").removeClass('mobileMenuOpen');
    });

    // Mobile swipe controls for opening menu and scrolling.
    $("#wrapper").swipe({
        swipeStatus: function (event, phase, direction, distance, duration, fingers) {
            if (phase == "move" && direction == "right" && distance > $(document).width() * 0.2) {
                $("#openicon").removeClass("mobileMenuToggleClosed");
                $("#closedicon").addClass("mobileMenuToggleClosed");
                $(".jumbotron .menu").removeClass('mobileMenuOpen');
                $("#wrapper").removeClass('mobileMenuOpen');
                return false;
            }
            if (phase == "move" && direction == "left" && distance > $(document).width() * 0.2) {
                $("#closedicon").removeClass('mobileMenuToggleClosed');
                $("#openicon").addClass('mobileMenuToggleClosed');
                $(".jumbotron .menu").addClass('mobileMenuOpen');
                $("#wrapper").addClass('mobileMenuOpen');
                return false;
            }
            if (phase == "move" && direction == "up") {
                window.scrollBy(0, distance);
            }
            if (phase == "move" && direction == "down") {
                window.scrollBy(0, 0 - distance);
            }
        }
    });

    // Mobile help button bubble show/hide.
    $(".creditlogin .help").click(function() {
        $(".creditlogin .help .questionmark").toggleClass("helpVisible");
    });

    $(".externallogin .help").click(function() {
        $(".externallogin .help .questionmark").toggleClass("helpVisible");
    });

    $(".guestlogin .help").click(function() {
        $(".guestlogin .help .questionmark").toggleClass("helpVisible");
    });

    // Browser checker.
    $(".portalBrowserChecker").click(function() {

        if ($.browser.name == 'chrome' && $.browser.versionNumber >= 30 || $.browser.name == 'firefox' && $.browser.versionNumber >= 25) {
            $(".browserCheckerPopupGood").addClass("is-visible");
        } else {
            $(".browserCheckerPopupBad").addClass("is-visible");
        }
    });

    $(".BCPopUp .cd-popup-close").click(function() {
        $(".BCPopUp").removeClass("is-visible");
    });
});

function limitImageHeight(img, maxHeight) {
    if (img.height > maxHeight) {
        img.height = maxHeight;
    }
}

function openHelp() {
    newin = window.open("help.dowebct?tool=entry&screenid=377", "HelpWin", "width=600,height=450,resizable=yes")
}

//<!--
//The following script can be used to check for popup blockers.
//The script will create a test popup and if the popup does not appear,
//a warning message alerts users that they should disable their popup blocker.
//To activate this script, place the following code in the <BODY> tag: onLoad="popupCheck()"
//-->

var popper = false;
var popCheck = "";
var alertMessage = "It appears you are using a popup blocker. Because this application uses popup windows, we recommend you disable your popup blocker before continuing.";
function popupCheck() {
    popCheck = window.open("popcheck.html", "blank", "width=300,height=300");
    setTimeout("alertPop()", 1000)
}

function alertPop() {
    if (!popCheck) {
        alert(alertMessage);
    } else {
        if (popper) {
            popCheck.close();
        }
        else alert(alertMessage);
    }
}


function getCheckedValue(radioObj) {

    if (!radioObj) {
        return "";
    }

    var radioLength = radioObj.length;

    if (radioLength == undefined) {
        if (radioObj.checked) {
            return radioObj.value;
        } else {
            return "";
        }
    }

    for (var i = 0; i < radioLength; i++) {
        if (radioObj[i].checked) {
            return radioObj[i].value;
        }
    }
    return "";

}

function submitLogon() {
    var selected = $("button[type=submit][clicked=true]").val();
    switch (selected) {
        case "1":
            submitProductionUofA();
            break;
        case "2":
            submitMoodle_CPD();
            break;
        case "5":
            submitGuestAccess();
            break;
        default:
            alert("Invalid selection");
    }
}

// Moodle eClass - U of A
function submitProductionUofA() {
    document.vistaInsEntryForm.action = "credit.php";
    document.vistaInsEntryForm.glcid.value = "URN:X-WEBCT-VISTA-V1:bb3dfef8-8180-aabb-017f-98034a3fb5c9";
    document.vistaInsEntryForm.insId.value = "5122011";
    document.vistaInsEntryForm.insName.value = "University of Alberta";
}

// Moodle - eClass External (New CPD)
function submitMoodle_CPD() {
    document.vistaInsEntryForm.action = "https://eclass-cpd.srv.ualberta.ca";
}

// Guest Access
function submitGuestAccess() {
    document.vistaInsEntryForm.action = "/local/eclass/landing/guestlink.php?cid=1";
}

function openBox(url, title, width, height) {

    title = title + getUniquePopupId();

    var screenX = Math.floor((screen.width) / 2) - Math.floor(width / 2);

    var screenY = Math.floor((screen.height) / 2) - Math.floor(height / 2) - 20;

    var top = screenY;

    var left = screenX;

    var features = "'" + 'toolbar=no,scrollbars=yes,status=yes,resizable=yes,top=' + top + ',left=' + left + ',screenX=' + screenX + ',screenY=' + screenY + ',width=' + width + ',height=' + height + "'";

    newWindow = window.open(url, title, features);

    newWindow.focus();

}

function getUniquePopupId() {

    var uid = getCookie("JSESSIONID");

    if (uid != null) {

        return uid.substring(0, 13);

    }

    return "";

}

function getCookie(name) {

    var crumbs = document.cookie.split("; ");

    for (var i = 0; i < crumbs.length; i++) {

        var crumb = crumbs[i].split("=");

        if (name == crumb[0]) {

            if (crumb[1]) {

                return unescape(crumb[1]);

            } else {

                return "";

            }

        }

    }

    return null;

}


function checkError() {

    if (location.href.indexOf("error") != -1) {
        document.getElementById("login_error").style.display = 'block';
    }
}


sfHover = function () {
    var sfEls = document.getElementById("nav").getElementsByTagName("LI");
    for (var i = 0; i < sfEls.length; i++) {
        sfEls[i].onmouseover = function () {
            this.className += " sfhover";
        }
        sfEls[i].onmouseout = function () {
            this.className = this.className.replace(new RegExp(" sfhover\\b"), "");
        }
    }
}
if (window.attachEvent) window.attachEvent("onload", sfHover);


function changeLocation(menuObj) {
    var i = menuObj.selectedIndex;

    if (i > 0) {
        window.location = menuObj.options[i].value;
    }
}

<!-- End JavaScript. -->

