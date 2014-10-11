<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Prepare a response
header('Access-Control-Allow-Origin: *');

$search = (isset($_POST['search']) ? Sanitize::variable($_POST['search']) : "");
$hashtagURL = URL::hashtag_unifaction_com();

// Gather the results for this search
$hashtags = Database::selectMultiple("SELECT hashtag FROM hashtag_official WHERE hashtag LIKE ? ORDER BY hashtag LIMIT 5", array($search . "%"));

echo '<ul>';

foreach($hashtags as $hashtag)
{
	echo '
	<li><a class="searchSel" href="' . $hashtagURL . "/" . $hashtag['hashtag'] . '">#' . $hashtag['hashtag'] .  '</a></li>';
}

echo '</ul>';