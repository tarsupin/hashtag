<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Search Official Hashtags
WidgetLoader::add("SidePanel", 10, '
<div class="panel-box">
	<div style="padding:10px;">
		<div><strong>Search Hashtags:</strong></div>
		<div style="padding-top:4px;">' . Search::searchBar("hOfficial", "", "hashtag-search", "search hashtags . . .", "", "", "") . '</div>
	</div>
</div>');

// Trending Tags
$trendingTags = AppTrend::getTrendingTags();

$html = '
<div class="panel-box">
	<a class="panel-head" href="#">Trending Tags<span class="icon-circle-right nav-arrow"></a>
	<ul class="panel-notes">';
	
	foreach($trendingTags as $tags)
	{
		$html .= '
		<li class="nav-note"><a href="/' . $tags . '">#' . $tags . '</a></li>';
	}
	
$html .= '
	</ul>
</div>';

WidgetLoader::add("SidePanel", 20, $html);
