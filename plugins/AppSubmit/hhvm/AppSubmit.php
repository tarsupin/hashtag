<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

---------------------------------------
------ About the AppSubmit Plugin ------
---------------------------------------

This plugin will identify trending hashtags, as well as update them when appropriate to.


-------------------------------
------ Methods Available ------
-------------------------------

// Submit a Hashtag Post
AppSubmit::run($uniID, $type, $attachmentID, $hashtags);

// Private Helpers
self::submitBase($hashtags, $siteHandle, $uniID, $json, $type);

*/

abstract class AppSubmit {
	
	
/****** Run the Hashtag Submission ******/
	public static function run
	(
		int $uniID					// <int> The uniID that set the hashtag post.
	,	int $type					// <int> The type of submission.
	,	int $attachmentID			// <int> The attachment ID for this hashtag post.
	,	array <int, str> $hashtags				// <int:str> An array of hashtags that were listed for the submission.
	,	bool $resubmission = false	// <bool> TRUE means this is a resubmission - it's just adding additional hashtags.
	): bool							// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// AppSubmit::run($uniID, $type, $attachmentID, $hashtags, [$resubmission]);
	{
		// Prepare Values
		$timestamp = time();
		
		// Cycle through each hashtag and post the content there
		Database::startTransaction();
		
		foreach($hashtags as $hashtag)
		{
			// Get the Hashtag ID for this hashtag (and create it if it doesn't exist)
			if($hashtagID = AppHashtag::getHashtagID($hashtag, true))
			{
				$hashtagIDs[] = $hashtagID;
				
				$pass = Database::query("INSERT INTO hashtag_posts (hashtag_id, type, date_posted, attachment_id) VALUES (?, ?, ?, ?)", array($hashtagID, $type, $timestamp, $attachmentID));
			}
		}
		
		// Update the attachment with the appropriate UniID that submitted it
		if($pass and !$resubmission)
		{
			$pass = Database::query("UPDATE attachment SET uni_id=? WHERE id=? LIMIT 1", array($uniID, $attachmentID));
		}
		
		// Commit the transaction (or rollback on any failures)
		if(!Database::endTransaction($pass))
		{
			return false;
		}
		
		return self::setRecentEntries($hashtagIDs);
	}
	
	
/****** Set Recent Entries ******/
	public static function setRecentEntries
	(
		array <int, int> $hashtagIDs		// <int:int> The IDs of each hashtag to add to the recent entries.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// AppSubmit::setRecentEntries($hashtagIDs);
	{
		// Prepare Values
		$timestamp = time();
		
		// Add provided hashtags as recent entries (to provide trending options)
		$hID = array();
		
		$hQuery = "";
		$hSQL = array();
		
		foreach($hashtagIDs as $hID)
		{
			$hQuery .= ($hQuery == "" ? "" : ", ") . "(?, ?)";
			
			$hSQL[] = $hID;
			$hSQL[] = $timestamp;
		}
		
		Database::query("INSERT INTO hashtags_recent (hashtag_id, date_posted) VALUES " . $hQuery, $hSQL);
		
		// Run Trending Update
		AppTrend::updateTrending();
		
		return true;
	}
	
}