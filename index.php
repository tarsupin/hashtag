<?php

/****** Preparation ******/
define("CONF_PATH",		dirname(__FILE__));
define("SYS_PATH", 		dirname(CONF_PATH) . "/system");

// Load phpTesla
require(SYS_PATH . "/phpTesla.php");

// Initialize Active User
Me::initialize();

// Determine which page you should point to, then load it
require(SYS_PATH . "/routes.php");

/****** Dynamic URLs ******/
// If a page hasn't loaded yet, check if there is a dynamic load
if($url[0] != '' && $url[0] != "404")
{
	// Check for the hashtag
	$hashtag = Sanitize::variable($url[0]);
	
	if(!$hashtagID = AppHashtag::getHashtagID($hashtag))
	{
		require(APP_PATH . '/controller/hashtag-empty.php'); exit;
	}
	
	require(APP_PATH . '/controller/hashtag.php'); exit;
}
//*/

/****** 404 Page ******/
// If the routes.php file or dynamic URLs didn't load a page (and thus exit the scripts), run a 404 page.
require(SYS_PATH . "/controller/404.php");