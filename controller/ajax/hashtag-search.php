<?php

if($search = (isset($_POST['search']) ? Sanitize::safeword($_POST['search']) : ""))
{
	// Gather the results for this hashtag list
	$hashtags = OfficialHashtags::search($search);
	
	echo '<ul>';
	
	foreach($hashtags as $hashtag)
	{
		echo '
		<li><a class="searchSel" href="/' . $hashtag['hashtag'] . '" onmousedown="window.location=\'/' . $hashtag['hashtag'] . '\'">' . $hashtag['title'] .  '</a></li>';
	}
	
	echo '</ul>';
}