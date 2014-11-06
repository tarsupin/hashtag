<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Get the active hashtag of the page
$activeHashtag = isset($_POST['activeHashtag']) ? Sanitize::variable($_POST['activeHashtag']) : '';


// Display a Chat Widget
if($activeHashtag)
{
	$chatWidget = new ChatWidget($activeHashtag);
	echo $chatWidget->get();
}



// Prepare the Featured Widget Data
$categories = array("articles", "people");

// Create a new featured content widget
$featuredWidget = new FeaturedWidget($activeHashtag, $categories);

// If you want to display the FeaturedWidget by itself:
echo $featuredWidget->get();