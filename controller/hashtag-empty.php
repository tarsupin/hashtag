<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// $hashtag
// $hashtagID

// Check if the user is attempting to follow this value
if(isset($_GET['follow']))
{
	if(Me::$loggedIn)
	{
		header("Location: " . Feed::follow($hashtag, "/" . $hashtag)); exit;
	}
	else
	{
		Me::redirectLogin("/" . $hashtag . "?follow=1", "/" . $hashtag);
	}
}

// Search Official Hashtags
WidgetLoader::add("SidePanel", 1, '
<div class="panel-box" style="min-height:30px;">
	<div style="padding:10px; text-align:center;">
		<a class="button" href="' . (Me::$id ? Feed::follow($hashtag, "/" . $hashtag) : "/" . $hashtag . "?follow=1") . '">Follow #' . $hashtag . '</a>
	</div>
</div>');

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

// Load the Page
echo '
<div id="panel-right"></div>
<div id="content">' . Alert::display() . '
	<div class="main-block tagHead">#' . strtoupper($hashtag) . '</div>
	<div class="main-block">No content was found on the hashtag #' . $hashtag . '.</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
