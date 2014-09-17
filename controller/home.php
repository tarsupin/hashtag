<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Update the trending list
AppTrend::updateTrending();

// Get trending posts
$submissions = AppTrend::getTrendPosts();

// Prepare the Metadata
Metadata::addHeader('<script src="' . CDN . '/scripts/autoscroll.js"></script>');

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
	urlToLoad = "/ajax/trend-loader";
	elementIDToAutoScroll = "trend-feed";
	startPos = 2;
	entriesToReturn = 1;
	maxEntriesAllowed = 20;
	waitDuration = 1200;
	appendURL = "";
</script>';

// Load the Page
echo '
<div id="panel-right"></div>
<div id="content">
<div id="trend-feed">';

// Display the hashtags
AppHashtag::display($submissions);

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
