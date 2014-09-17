<?php

// Make sure a start position is provided
if(!isset($_GET['startPos']))
{
	exit;
}

// Retrieve the submissions
$submissions = AppTrend::getTrendPosts((int) $_GET['startPos']);

// Display the Submissions
AppHashtag::display($submissions);