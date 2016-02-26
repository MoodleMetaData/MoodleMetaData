<?php
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

// Anthony Radziszewski
// radzisze@ualberta.ca
// Created: 07/05/2015.

// From: http://stackoverflow.com/questions/18218643/php-etag-generation-using-php.
$lastmodified = filemtime(__FILE__);
// Get a unique hash of this file (etag).
$etagfile = md5_file(__FILE__);
// Get the HTTP_IF_MODIFIED_SINCE header if set.
$ifmodifiedsince = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false);
// Get the HTTP_IF_NONE_MATCH header if set (etag: unique file hash).
$etagheader = (isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);
// Set last-modified header.
header("Last-Modified: " . gmdate("D, d M Y H:i:s", $lastmodified) . " GMT");
// Set etag-header.
header("Etag: $etagfile");
// Make sure caching is turned on.
// Check if page has changed. If not, send 304 and exit.
if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastmodified || $etagheader == $etagfile) {
    header("HTTP/1.1 304 Not Modified");
    exit;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" dir="ltr" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>eClass Portal</title>
    <!-- Begin Javascript. Do Not Remove. -->
    <script type="text/javascript" src="js/jquery.min.js?v=<?php echo md5_file('js/jquery.min.js') ?>"></script>
    <script type="text/javascript"
            src="js/jquery.touchSwipe.min.js?v=<?php echo md5_file('js/jquery.touchSwipe.min.js') ?>"></script>
    <script type="text/javascript" src="js/jquery.browser.min.js?v=<?php echo md5_file('js/jquery.browser.min.js') ?>"></script>
    <script type="text/javascript" src="js/FeedEk.js?v=<?php echo md5_file('js/FeedEk.js') ?>"></script>
    <script type="text/javascript" src="js/mobile.js?v=<?php echo md5_file('js/mobile.js') ?>"></script>
    <script type="text/javascript" src="js/bootstrap.js?v=<?php echo md5_file('js/bootstrap.js') ?>"></script>
    <script type="text/javascript" src="js/index.js?v=<?php echo md5_file('js/index.js') ?>"></script>
    <!-- Begin Styling. Do Not Remove. -->
    <link href="css/bootstrap.css?v=<?php echo md5_file('css/bootstrap.css') ?>" media="all" rel="stylesheet" type="text/css"/>
    <link href="css/glyphicons.css?v=<?php echo md5_file('css/glyphicons.css') ?>" media="all" rel="stylesheet"
          type="text/css"/>
    <link href="css/global.css?v=<?php echo md5_file('css/global.css') ?>" media="all" rel="stylesheet" type="text/css"/>
    <link href="css/FeedEk.css?v=<?php echo md5_file('css/FeedEk.css') ?>" media="all" rel="stylesheet" type="text/css"/>
    <style id="mobilecss" type="text/css"></style>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
</head>

<body>
<div id="wrapper">
    <header class="navbar-outer">
        <div class="navbar-inner" role="navigation">
            <div class="container">
                <div class="ualogo pull-left">
                    <a href="http://www.ualberta.ca/"><img border="0" alt="University of Alberta"
                                                           src="images/ua-logo.svg"></a>

                    <div class="brand-wrapper">
                        <span class="brand">eClass</span><br/>
                        <span class="powered">Powered by Moodle</span>
                    </div>
                </div>
                <div class="block-menu navbar-right">
                    <ul class="list-inline navbar-collapse collapse">
                        <li><a href="http://webapps.srv.ualberta.ca/search/">Find a Person</a></li>
                        <li><a href="https://www.beartracks.ualberta.ca/">Bear Tracks</a></li>
                        <li><a href="https://sites.google.com/a/ualberta.ca/startpage/home?pli=1">Email &amp; Apps</a>
                        </li>
                        <li><a href="http://www.campusmap.ualberta.ca/">Maps</a></li>
                        <li><a href="http://www.library.ualberta.ca/">Libraries</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <div id="tophalf" class="content">
        <div id="login" class="jumbotron">

            <div class="cd-popup BCPopUp browserCheckerPopupGood" role="alert">
                <div class="cd-popup-container">
                    <p>Your browser is supported by eClass!</p>
                    <button class="btn btn-default cd-popup-close">Close</button>
                </div>
            </div>

            <div class="cd-popup BCPopUp browserCheckerPopupBad" role="alert">
                <div class="cd-popup-container">
                    <p>Your current browser is NOT recommended! <br>
                        Some components of eClass may not be fully functional using this browser.</p>
                    <a href=
                    "https://support.ctl.ualberta.ca/index.php?/IST/Knowledgebase/Article/View/18/4/system-specs-and-browser-setup"
                       target="_blank">Click here for information on supported browsers.</a><br>
                    <button class="btn btn-default cd-popup-close">Close</button>
                </div>
            </div>

            <div class="menu">
                <div class="col-md-3">
                    <h2 class="row">eClass</h2>
                    <h4 class="row">Powered by Moodle</h4>
                    <h6 class="row text-uppercase">Instructors</h6>

                    <div class="row request">
                        <a href="https://cc.elearning.ualberta.ca/entrypage.aspx" target="_blank">Request your
                            course<br/>in eClass <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                    <h5 class="row text-uppercase">Additional Support</h5>

                    <div class="block">
                        <div class="link-sidebar-nav">
                            <a href="https://support.ctl.ualberta.ca/index.php?/IST/Knowledgebase/List/Index/22/eclass">
                                eClass</a><br>
                            <a href="https://support.ctl.ualberta.ca/index.php?/IST/Knowledgebase/List/Index/23/eclasslive">
                                eClassLive</a><br>
                            <a href="https://support.ctl.ualberta.ca/index.php?/IST/Knowledgebase/List/Index/21/eportfolios">
                                ePortfolio</a><br>
                            <a href="https://support.ctl.ualberta.ca/index.php?/IST/Knowledgebase/List/Index/17/iclicker">
                                i&gt;Clicker</a><br>
                            <a href="https://support.ctl.ualberta.ca/index.php?/IST/Knowledgebase/List/Index/1/respondus">
                                Respondus</a><br>
                            <span class="portalBrowserChecker">Browser checker</span>
                        </div>
                    </div>
                    <span class="mobilelinks">
                    <h5 class="row text-uppercase">Quick Links</h5>
                        <div class="block">
                            <div class="link-sidebar-nav">
                                <a href="http://webapps.srv.ualberta.ca/search/">Find a Person</a><br>
                                <a href="https://www.beartracks.ualberta.ca/">Bear Tracks</a><br>
                                <a href="https://sites.google.com/a/ualberta.ca/startpage/home?pli=1">Email &amp;
                                    Apps</a><br>
                                <a href="http://www.campusmap.ualberta.ca/">Maps</a><br>
                                <a href="http://www.library.ualberta.ca/">Libraries</a>
                            </div>
                        </div>
                    </span>
                </div>
            </div>
            <div class="portal">
                <div class="mobiletopbar">
                    <div class="left-side">
                        <h2 class="row">eClass</h2>
                        <h4 class="row">Powered by Moodle</h4>
                    </div>
                    <div class="right-side">
                        <span id="closedicon" class="mobileMenuToggle mobileMenuToggleClosed"><i class="fa fa-bars"></i></span>
                        <span id="openicon" class="mobileMenuToggle"><i class="fa fa-times"></i></span>
                    </div>
                </div>
                <h2>Login to eClass</h2>

                <form name="vistaInsEntryForm" id="vistaInsEntryForm" action="#" class="row" method="post"
                      onSubmit="return submitLogon();">
                    <input type="hidden" name="glcid"/>
                    <input type="hidden" name="insId"/>
                    <noscript><input name="js_enabled" type="hidden" value="1"></noscript>
                    <input type="hidden" name="insName"/>
                    <input type="hidden" name="timeZoneOffset"/>
                    <input type="hidden" name="username" value="guest"/>
                    <input type="hidden" name="password" value="guest"/>
                    <input type="hidden" name="institution" value="1"/>
                    <?php
if (array_key_exists('cid', $_REQUEST)) {
                        echo "<input type='hidden' name='cid' value='{$_REQUEST['cid']}' />";
}
                    ?>

                    <!-- Begin Institution Login Form Table. You may modify this table, but do not remove input fields. -->
                    <?php
if (isset($_GET['err'])) {
                        echo '<div class="alert alert-danger alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert">
                                        <span aria-hidden="true">&times;</span>
                                        <span class="sr-only">Close</span>
                                    </button>
                                    <strong>Login failed</strong> please try again.
                                </div>';
}
                    ?>
                    <div class="portalbuttons">
                        <div class="row">
                            <div class="creditlogin">
                                <button id="uofa" type="submit" class="btn btn-default btn-login btn-uofa main"
                                        value="1">University of Alberta Credit Courses
                                </button>
                                <button id="uofa" type="button" class="btn btn-default btn-login btn-uofa help"
                                        value="6">
                                    <span class="questionmark">

                                        <i class="fa fa-question-circle"><span>Regular Bear Tracks credit courses,
                                                Faculty of Extension courses, and Non-Credit sandbox, training,
                                                or resource courses for CCID-only access</span></i>
                                    </span>
                                </button>
                            </div>
                        </div>
                        <div class="row externalguestbuttons">
                            <div class="externallogin">
                                <button id="external" type="submit" class="btn btn-default btn-login btn-ext main"
                                        value="2">External Courses
                                </button>
                                <button id="external" type="button" class="btn btn-default btn-login btn-ext help"
                                        value="7">
                                    <span class="questionmark">

                                        <i class="fa fa-question-circle"><span>
                                                Non-credit courses allowing access to non-CCID users</span></i>
                                    </span>
                                </button>
                            </div>
                            <div class="guestlogin">
                                <button id="guest" type="submit" class="btn btn-default btn-login btn-guest main"
                                        value="5">Guest Access
                                </button>
                                <button id="guest" type="button" class="btn btn-default btn-login btn-guest help"
                                        value="8">
                                    <span class="questionmark"
                                          data-toggle="tooltip" data-placement="center">
                                        <i class="fa fa-question-circle"><span>Guest access to any public
                                                facing courses on eClass</span></i></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="contact">
                <h5 class="row">eCLASS SUPPORT</h5>

                <div class="left-side">
                    <p class="pull-left">
                        <span class="portaladdress"> 1-56 General Services Building</span><br>
                        University of Alberta<br>
                        Edmonton, Alberta T6G 2G5<br>
                        Canada</p>
                </div>
                <div class="right-side">
                    <p class="pull-left">
                        <i class="fa fa-clock-o"></i> Mon to Fri, 8:30 am - 4:30 pm<br>
                        <i class="fa fa-phone"></i> 1 (780) 492-9372<br>
                        <i class="fa fa-envelope-o"></i> <a href="mailto:eclass@ualberta.ca"> eclass@ualberta.ca</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div id="main">
        <div class="mobileNewsLink"><a href="https://support.ctl.ualberta.ca/index.php?/News/List">
                Click here for latest eClass news</a></div>
        <div class="blog">
            <h3>Support News</h3>

            <div id="divRss"></div>
            <br>
            <a href="https://support.ctl.ualberta.ca/index.php?/News/List">See Support News...</a>
            <br><br><br>
        </div>
    </div>
    <footer class="container footer">
        <p class="row copyright text-center">Copyright &copy; 2015 <b>University of Alberta</b></p>
    </footer>
</div>
</body>

</html>