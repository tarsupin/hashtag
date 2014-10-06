<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------------
------ About the AppHashtag Plugin ------
-----------------------------------------

This plugin will retrieve and interact with hashtags.


---------------------------
------ Hashtag Types ------
---------------------------



-------------------------------
------ Methods Available ------
-------------------------------

$posts = AppHashtag::get($hashtagID, [$type], [$page], [$showNum]);

$hashtagID = AppHashtag::getHashtagID($hashtag);

$hashtagID = AppHashtag::create($hashtag);

*/

abstract class AppHashtag {
	
	
/****** Get Hashtags ******/
	public static function get
	(
		$hashtagID		// <int> Choose the hashtag to retrieve (by ID).
	,	$type = "*"		// <mixed> The type of hashtags to refine the search by (* is all).
	,	$page = 1		// <int> The page that you're going to retrieve submissions of.
	,	$showNum = 10	// <int> The number of submissions to show on each page.
	)					// RETURNS <int:[str:mixed]> list of the most recent submissions, array() if failed.
	
	// $posts = AppHashtag::get($hashtagID, [$type], [$page], [$showNum]);
	{
		// Prepare Values
		$start = ($page - 1) * $showNum;
		$showNum += 0;
		
		// Prepare SQL
		if($type == "*")
		{
			list($sqlWhere, $sqlArray) = Database::sqlFilters(array("p.type" => array(Attachment::TYPE_ARTICLE, Attachment::TYPE_IMAGE, Attachment::TYPE_COMMENT, Attachment::TYPE_BLOG, Attachment::TYPE_VIDEO)));
		}
		else
		{
			$sqlWhere = "p.type=?";
			
			$sqlArray = array($type);
		}
		
		array_unshift($sqlArray, $hashtagID);
		
		// Retrieve the hashtags
		if($posts = Database::selectMultiple("SELECT p.type, p.date_posted, a.title, a.description, a.asset_url, a.source_url, a.params, a.uni_id, u.handle, u.display_name FROM hashtag_posts p INNER JOIN attachment a ON a.id=p.attachment_id INNER JOIN users u ON u.uni_id=a.uni_id WHERE p.hashtag_id=? AND " . $sqlWhere . " ORDER BY p.date_posted DESC LIMIT " . $start . ', ' . $showNum, $sqlArray))
		{
			return $posts;
		}
		
		return array();
	}
	
	
/****** Get the hashtag ID ******/
	public static function getHashtagID
	(
		$hashtag			// <str> The hashtag (as a string).
	,	$create = false		// <bool> TRUE if you want to create the hashtag if it doesn't exist yet.
	)						// RETURNS <int> ID of the hashtag if discovered (or created successfully), 0 otherwise.
	
	// $hashtagID = AppHashtag::getHashtagID($hashtag);
	{
		if($tagID = (int) Database::selectValue("SELECT hashtag_id FROM hashtag_list_by_name WHERE hashtag=? LIMIT 1", array($hashtag)))
		{
			return $tagID;
		}
		
		// Attempt to create the hashtag
		return ($create ? self::create($hashtag) : 0);
	}
	
	
/****** Create a Hashtag ******/
	public static function create
	(
		$hashtag		// <str> The hashtag (as a string).
	)					// RETURNS <int> ID of the hashtag if created successfully (or exists), 0 otherwise.
	
	// $hashtagID = AppHashtag::create($hashtag);
	{
		$hashtag = Sanitize::word($hashtag, "0123456789");
		
		// Retrieve the existing hashtag if available
		if($tagID = (int) Database::selectValue("SELECT hashtag_id FROM hashtag_list_by_name WHERE hashtag=? LIMIT 1", array($hashtag)))
		{
			return $tagID;
		}
		
		// Insert the Hashtag
		Database::startTransaction();
		
		if($pass = Database::query("INSERT INTO hashtag_list (hashtag) VALUES (?)", array($hashtag)))
		{
			$lastID = Database::$lastID;
			
			$pass = Database::query("INSERT INTO hashtag_list_by_name (hashtag, hashtag_id) VALUES (?, ?)", array($hashtag, $lastID));
			
			if(Database::endTransaction($pass))
			{
				return $lastID;
			}
		}
		
		Database::endTransaction(false);
		return 0;
	}
	
	
/****** Output a list of hashtag submissions ******/
	public static function display
	(
		$submissions	// <int:[str:mixed]> The full submission list to display.
	)					// RETURNS <void> OUTPUTS the content.
	
	// AppHashtag::display($submissions);
	{
		// Get Site URL's
		$social = URL::unifaction_social();
		$fastchat = URL::fastchat_social();
		
		foreach($submissions as $submission)
		{
			// Recognize Integers
			$submission['uni_id'] = (int) $submission['uni_id'];
			$submission['date_posted'] = (int) $submission['date_posted'];
			
			$details = json_decode($submission['params'], true);
			
			// Comments
			if($submission['type'] == Attachment::TYPE_COMMENT)
			{
				echo '
				<div class="main-block">
					<div class="status-left"><a href="' . $social . '/' . $submission['handle'] . '"><img class="circimg" src="' . ProfilePic::image($submission['uni_id'], "medium") . '" /></a></div>
					<div class="status-right">
						<div class="block-date">' . Time::fuzzy($submission['date_posted']) . '</div>
						<div><a href="' . $social . '/' . $submission['handle'] . '"><span class="h4">' . $submission['display_name'] . '</span></a> <a href="' . $fastchat . '/' . $submission['handle'] . '"><span class="com-handle">@' . $submission['handle'] . '</span></a></div>
						<p>' . Comment::showSyntax($submission['description']) . '</p>
						<div class="extralinks"><a href="' . $submission['source_url'] . '">Link to Comment</a></div>
					</div>
				</div>';
			}
			
			// Articles and Blogs
			else if($submission['type'] == Attachment::TYPE_ARTICLE or $submission['type'] == Attachment::TYPE_BLOG)
			{
				// Prepare Values
				$mobileURL = (isset($submission['mobile-url']) ? $submission['mobile-url'] : "");
				$class = ($mobileURL ? "post-image" : "post-image-mini");
				
				echo '
				<div class="main-block">
					<div class="block-date">' . Time::fuzzy($submission['date_posted']) . '</div>
					<div style="float:left; width:30%; text-align:center;">';
					
					if($submission['asset_url'])
					{
						echo ($submission['source_url'] ? '<a href="' . $submission['source_url'] . '">' : '') . Photo::responsive($submission['asset_url'], $mobileURL, 950, "", 950, $class) . ($submission['source_url'] != '' ? '</a>' : '');
					}
					
					echo '
					</div>
					<div style="margin-left:31%;">
						<span class="icon-image"></span> By <a href="' . $social . '/' . $submission['handle'] . '">' . $submission['display_name'] . '</a>
						<div style="margin-top:14px;">';
				
				// Display the title, if provided
				if($submission['title'] != '')
				{
					echo '<div><strong>' . $submission['title'] . '</strong></div>';
				}
				
				// Display the description, if provided
				if($submission['description'] != '')
				{
					echo '<div>' . $submission['description'] . '</div>';
				}
				
				echo '
						<p style="margin-bottom:0px;"><a href="' . $submission['source_url'] . '">... Read Full Article</a></p>
						</div>
					</div>
				</div>';
			}
			
			// Images
			else if($submission['type'] == Attachment::TYPE_IMAGE)
			{
				$mobileURL = (isset($details['mobile-url']) ? $details['mobile-url'] : '');
				$class = ($mobileURL != "" ? "post-image" : "post-image-mini");
				
				echo '
				<div class="photo-block">
					<div class="photo-upperbar">
						<div class="block-date">' . Time::fuzzy($submission['date_posted']) . '</div>
						<span class="icon-image"></span> &nbsp; <a href="' . $social . '/' . $submission['handle'] . '">' . $submission['display_name'] . '</a> posted an image
					</div>
					<div>' . ($submission['source_url'] != "" ? '<a href="' . $submission['source_url'] . '">' : '') . Photo::responsive($submission['asset_url'], $mobileURL, 450, "", 450, $class) . ($submission['source_url'] != '' ? '</a>' : '') . '</div>
					<div class="photo-com">
						<div class="photo-linkbar"><div class="extralinks"><a href="' . $submission['source_url'] . '">Link to Source</a> <span class="icon-comment"></span> Chat</a></div></div>';
				
				// Show the message if there is one
				if($submission['description'])
				{
					echo '
						<div class="status-left"><a href="' . $social . '/' . $submission['handle'] . '"><img class="circimg" src="' . ProfilePic::image(1, "medium") . '" /></a></div>
						<div class="status-right">
							<div><a href="' . $social . '/' . $submission['handle'] . '"><span class="h4">' . $submission['display_name'] . '</span></a> <a href="' . $fastchat . '/' . $submission['handle'] . '"><span class="com-handle">@' . $submission['handle'] . '</span></a></div>
							<p>' . Comment::showSyntax($submission['description']) . '</p>
						</div>';
				}
				
				echo '
					</div>
				</div>';
			}
			
			// Videos
			else if($submission['type'] == Attachment::TYPE_VIDEO)
			{
				echo '
				<div class="photo-block">
					<div class="photo-upperbar">
						<div class="block-date">' . Time::fuzzy($submission['date_posted']) . '</div>
						<span class="icon-image"></span> &nbsp; <a href="' . $social . '/' . $submission['handle'] . '">' . $submission['display_name'] . '</a> posted a video
					</div>
					' . $details['embed'] . '
					<div class="photo-com">
						<div class="photo-linkbar"><div class="extralinks"><a href="' . $submission['source_url'] . '">Link to Source</a></div></div>';
				
				// Show the message if there is one
				if($submission['description'])
				{
					echo '
						<div class="status-left"><a href="' . $social . '/' . $submission['handle'] . '"><img class="circimg" src="' . ProfilePic::image(1, "medium") . '" /></a></div>
						<div class="status-right">
							<div><a href="' . $social . '/' . $submission['handle'] . '"><span class="h4">' . $submission['display_name'] . '</span></a> <a href="' . $fastchat . '/' . $submission['handle'] . '"><span class="com-handle">@' . $submission['handle'] . '</span></a></div>
							<p>' . Comment::showSyntax($submission['description']) . '</p>
						</div>';
				}
				
				echo '
					</div>
				</div>';
			}
		}
	}
	
}
