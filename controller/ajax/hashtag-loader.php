<?php

// Make sure a hashtag and start position is provided
if(!isset($_GET['hashtag']) or !isset($_GET['startPos']))
{
	exit;
}

// Get the Hashtag ID
if(!$hashtagID = AppHashtag::getHashtagID(Sanitize::variable($_GET['hashtag'])))
{
	exit;
}

// Retrieve the submissions
$submissions = AppHashtag::get($hashtagID, (isset($_GET['refine']) ? (int) $_GET['refine'] : "*"), (int) $_GET['startPos']);

// Display the Submissions
AppHashtag::display($submissions);