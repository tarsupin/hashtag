<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Check for the hashtag
$hashtag = Sanitize::variable($url[0]);

if(!$hashtagID = AppHashtag::getHashtagID($hashtag))
{
	header("Location: /404"); exit;
}

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

// Prepare Values
$_GET['refine'] = isset($_GET['refine']) ? (int) $_GET['refine'] : 0;

// Retrieve the submissions
$submissions = AppHashtag::get($hashtagID, ($_GET['refine'] ? $_GET['refine'] : "*"));

// Search Official Hashtags
WidgetLoader::add("SidePanel", 1, '
<div class="panel-box" style="min-height:30px;">
	<div style="padding:10px; text-align:center;">
		<a class="button" href="' . (Me::$id ? Feed::follow($hashtag, "/" . $hashtag) : "/" . $hashtag . "?follow=1") . '">Follow #' . $hashtag . '</a>
	</div>
</div>');

// Load "Refine Search" Panel
WidgetLoader::add("SidePanel", 5, '
<!-- Refine Search -->
<div class="panel-box">
	<ul class="panel-slots">
		<li class="panel-head">Refine Search</li>
		<li class="nav-slot"><a href="/' . $hashtag . '">All<span class="icon-circle-right nav-arrow"></span></a></li>
		<li class="nav-slot"><a href="/' . $hashtag . '?refine=' . Attachment::TYPE_ARTICLE . '">Articles <span class="icon-circle-right nav-arrow"></span></a></li>
		<li class="nav-slot"><a href="/' . $hashtag . '?refine=' . Attachment::TYPE_BLOG . '">Blogs<span class="icon-circle-right nav-arrow"></span></a></li>
		<li class="nav-slot"><a href="/' . $hashtag . '?refine=' . Attachment::TYPE_COMMENT . '">Comments <span class="icon-circle-right nav-arrow"></span></a></li>
		<li class="nav-slot"><a href="/' . $hashtag . '?refine=' . Attachment::TYPE_IMAGE . '">Images <span class="icon-circle-right nav-arrow"></span></a></li>
		<li class="nav-slot"><a href="/' . $hashtag . '?refine=' . Attachment::TYPE_VIDEO . '">Videos <span class="icon-circle-right nav-arrow"></span></a></li>
	</ul>
</div>');

// Prepare the Header Data
AppHashtag::prepare();

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

// Run the auto-scrolling script
echo '
<script>
	urlToLoad = "/ajax/hashtag-loader";
	elementIDToAutoScroll = "hashtag-feed";
	startPos = 2;
	entriesToReturn = 1;
	maxEntriesAllowed = 20;
	waitDuration = 1200;
	appendURL = "&hashtag=' . $hashtag . '&refine=' . $_GET['refine'] . '";
</script>';

// Load the Page
echo '
<div id="panel-right"></div>
<div id="content">' . Alert::display() . '
	<div class="main-block tagHead">#' . strtoupper($hashtag) . '</div>';

// Begin the Hashtag Feed Div
// This div is important because this is where the infinite scroll loading occurs
echo '
<div id="hashtag-feed">';

// Cycle through each hashtag submission
if($submissions)
{
	AppHashtag::display($submissions);
}

// If there are no entries posted
else
{
	echo '
	<div class="main-block">';
	
	switch($_GET['refine'])
	{
		case Attachment::TYPE_ARTICLE: echo 'No articles have been posted to this hashtag.'; break;
		case Attachment::TYPE_BLOG: echo 'No blogs have been posted to this hashtag.'; break;
		case Attachment::TYPE_COMMENT: echo 'No comments have been posted to this hashtag.'; break;
		case Attachment::TYPE_IMAGE: echo 'No photos have been posted to this hashtag.'; break;
		case Attachment::TYPE_VIDEO: echo 'No videos have been posted to this hashtag.'; break;
		default: echo 'No content was found on this hashtag.'; break;
	}
	
	echo '
	</div>';
}

echo '
</div> <!-- Ends the hashtag feed div -->
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
