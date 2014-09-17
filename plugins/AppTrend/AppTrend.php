<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

---------------------------------------
------ About the AppTrend Plugin ------
---------------------------------------

This plugin will identify trending hashtags, as well as update them when appropriate to.


-------------------------------
------ Methods Available ------
-------------------------------

$trendingPosts = AppTrend::getTrendPosts([$page], [$showNum]);

AppTrend::updateTrending();

$trendingTags = AppTrend::getTrendingTags();

*/

abstract class AppTrend {
	
	
/****** Get Recent Hashtags (the full list) ******/
	public static function getTrendPosts
	(
		$page = 1		// <int> The page to retrieve of trending posts.
	,	$showNum = 10	// <int> The number of trending posts to show on each page.
	)					// RETURNS <int:[str:mixed]> list of the most recent submissions, or empty array if failed.
	
	// $trendingPosts = AppTrend::getTrendPosts([$page], [$showNum]);
	{
		$tSQL = "";
		$tArray = array();
		
		if(!$tags = Database::selectMultiple("SELECT hashtag_id FROM hashtags_trending t LEFT JOIN hashtag_list l ON t.hashtag_id = l.id ORDER BY t.count DESC LIMIT 10", array()))
		{
			return array();
		}
		
		foreach($tags as $tag)
		{
			$tSQL .= ($tSQL == "" ? "" : ", ") . "?";
			$tArray[] = (int) $tag['hashtag_id'];
		}
		
		$tArray[] = Attachment::TYPE_ARTICLE;
		$tArray[] = Attachment::TYPE_IMAGE;
		$tArray[] = Attachment::TYPE_COMMENT;
		$tArray[] = Attachment::TYPE_BLOG;
		$tArray[] = Attachment::TYPE_VIDEO;
		
		return Database::selectMultiple("SELECT p.*, a.title, a.description, a.asset_url, a.source_url, a.params, a.uni_id, u.handle, u.display_name FROM hashtag_posts p INNER JOIN attachment a ON a.id=p.attachment_id INNER JOIN users u ON u.uni_id=a.uni_id WHERE p.hashtag_id IN (" . $tSQL . ") AND p.type IN (?, ?, ?, ?, ?) GROUP BY p.date_posted ORDER BY p.date_posted DESC LIMIT " . (($page - 1) * $showNum) . ", " . ($showNum + 0), $tArray);
	}
	
	
/****** Update Trending Tags ******/
	public static function updateTrending (
	)					// RETURNS <bool> TRUE on success, FALSE if failed.
	
	// AppTrend::updateTrending();
	{
		$timestamp = time();
		
		$lastUpdate = (int) SiteVariable::load("trend-data", "date_trendUpdate");
		
		// If the last trending test was over six minutes ago
		if($lastUpdate < $timestamp - (60 * 6))
		{
			// Cycle through the list and determine what's trending
			// Data is collected from at least last 12 minutes (minimum)
			$trend = Database::selectMultiple("SELECT hashtag_id, COUNT(*) as trendVal FROM hashtags_recent GROUP BY hashtag_id ORDER BY trendVal DESC LIMIT 10", array());
			
			// Add these values to the trending list
			Database::startTransaction();
			
			Database::query("DELETE FROM hashtags_trending", array());
			
			foreach($trend as $t)
			{
				Database::query("INSERT INTO hashtags_trending (count, hashtag_id) VALUES (?, ?)", array((int) $t['trendVal'], (int) $t['hashtag_id']));
			}
			
			Database::endTransaction();
			
			// Get the first 1000 entries by date
			$countNum = (int) Database::selectValue("SELECT COUNT(*) as totalNum FROM hashtags_recent WHERE date_posted > ?", array($timestamp - (60 * 30)));
			
			if($countNum < 1000)
			{
				// Get the last 30 days
				$finalDate = $timestamp - (3600 * 24 * 30);
			}
			else
			{
				$finalDate = $timestamp - (60 * 30);
			}
			
			// Delete recent hashtag lists for any hashtags older than 30 minutes ago
			Database::query("DELETE FROM hashtags_recent WHERE date_posted < ?", array($finalDate));
			
			// Save the last trending update time
			SiteVariable::save("trend-data", "date_trendUpdate", $timestamp);
			
			return true;
		}
		
		return false;
	}
	
	
/****** Get the current trending tags ******/
	public static function getTrendingTags (
	)			// RETURNS <int:str> list of trending hashtags, or empty array if failed.
	
	// $trendingTags = AppTrend::getTrendingTags();
	{
		$tagList = array();
		
		$tags = Database::selectMultiple("SELECT hashtag FROM hashtags_trending t LEFT JOIN hashtag_list l ON t.hashtag_id = l.id ORDER BY t.count DESC LIMIT 10", array());
		
		foreach($tags as $tag)
		{
			$tagList[] = $tag['hashtag'];
		}
		
		return $tagList;
	}
	
}
