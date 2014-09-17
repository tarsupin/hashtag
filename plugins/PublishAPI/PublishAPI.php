<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------
------ About this API ------
----------------------------

This API allows you to submit content to the hashtag system.

This API can only be used by sites with appropriate clearance.


------------------------------
------ Calling this API ------
------------------------------
	
	This API can be called using:
		Hashtag::submitImage($uniID, $photoURL, $message, $hashtags, $url);
		Hashtag::submitArticle(...);
		Hashtag::submitVideo(...);
		Hashtag::submitComment(...);
	
	
	// The full API call
	$packet = array(
		'uni_id'		=> $uniID		// the UniID that is publishing the content
	,	'type'			=> $type		// the type of attachment ('article', 'blog')
	,	'image_url'		=> $imageURL	// the URL of the image to publish
	,	'mobile_url'	=> $mobileURL	// the URL of the mobile version of the image to publish
	,	'video_url'		=> $videoURL	// the URL of the video to publish
	,	'attach_title'	=> $title		// the title of the attachment, if applicable
	,	'attach_desc'	=> $desc		// the description of the attachment, if applicable
	,	'message'		=> $message		// the message to include
	,	'hashtags'		=> $hashtags	// an array of hashtags that the content was linking to
	,	'source'		=> $sourceURL	// the source of the hashtag ("read more" link)
	,	'resubmitted'	=> $resub		// set to TRUE if this content was a resubmission of an earlier post
	);
	
	$response = Connect::to("hashtag", "PublishAPI", $packet);
	
	
[ Possible Responses ]
	TRUE		// If the content was submitted.
	FALSE		// If the content was NOT submitted.

*/

class PublishAPI extends API {
	
	
/****** API Variables ******/
	public $isPrivate = true;			// <bool> TRUE if this API is private (requires an API Key), FALSE if not.
	public $encryptType = "";			// <str> The encryption algorithm to use for response, or "" for no encryption.
	public $allowedSites = array();		// <int:str> the sites to allow the API to connect with. Default is all sites.
	public $microCredits = 2;			// <int> The cost in microcredits (1/10000 of a credit) to access this API.
	public $minClearance = 6;			// <int> The minimum level of clearance required to access this API.
	
	
/****** Run the API ******/
	public function runAPI (
	)					// RETURNS <bool> TRUE if the submission succeeded, FALSE if not.
	
	// $this->runAPI()
	{
		// Make sure the appropriate data was sent
		if(!isset($this->data['uni_id']) or !isset($this->data['hashtags']))
		{
			return false;
		}
		
		// Prepare Values
		$sourceURL = (isset($this->data['source']) ? Sanitize::url($this->data['source']) : "");
		$message = (isset($this->data['message']) ? Sanitize::text($this->data['message']) : "");
		
		$hashtags = $this->data['hashtags'];
		$uniID = (int) $this->data['uni_id'];
		
		$attachTitle = (isset($this->data['attach_title']) ? Sanitize::safeword($this->data['attach_title'], " !?") : "");
		$attachDesc = (isset($this->data['attach_desc']) ? Sanitize::safeword($this->data['attach_desc'], " !?") : "");
		
		$imageURL = (isset($this->data['image_url']) ? Sanitize::url($this->data['image_url']) : "");
		$mobileURL = (isset($this->data['mobile_url']) ? Sanitize::url($this->data['mobile_url']) : "");
		$videoURL = (isset($this->data['video_url']) ? Sanitize::url($this->data['video_url']) : "");
		
		if(!is_array($hashtags) or $hashtags == array()) { return 0; }
		
		$hashtagIDs = array();
		
		// Make sure this user is registered
		if(!$exists = User::get($uniID))
		{
			if(!User::silentRegister($uniID))
			{
				return false;
			}
		}
		
		// Determine a pre-defined or special attachment type
		$attachType = 0;
		
		if(isset($this->data['type']))
		{
			switch($this->data['type'])
			{
				case "article":			$attachType = Attachment::TYPE_ARTICLE;		break;
				case "blog":			$attachType = Attachment::TYPE_BLOG;		break;
			}
		}
		
		// If this is a re-submission, attempt the resubmission first
		if(isset($this->data['resubmitted']))
		{
			// Attempt to resubmit the content (reuses the attachment)
			if($this->resubmit($hashtags, $imageURL, $videoURL, $sourceURL))
			{
				return true;
			}
		}
		
		// If we're publishing an Image
		if($imageURL)
		{
			// Prepare the Attachment
			$attachType = ($attachType ? $attachType : Attachment::TYPE_IMAGE);
			
			// Create the attachment
			$attachment = new Attachment($attachType, $imageURL);
			
			// Update the attachment's important settings
			$attachment->setSource($sourceURL);
			
			if($attachTitle) { $attachment->setTitle($attachTitle); }
			if($attachDesc) { $attachment->setDescription($attachDesc); }
			
			if($mobileURL)
			{
				// Set the mobile attachment value
				$attachment->setMobileImage($mobileURL);
			}
			
			// Save the attachment into the database
			$attachment->save();
			
			// Create the hashtag post
			return AppSubmit::run($this->data['uni_id'], $attachType, $attachment->id, $hashtags, $message);
		}
		
		// If we're publishing an Image
		else if($videoURL)
		{
			// Prepare the Attachment
			$attachType = ($attachType ? $attachType : Attachment::TYPE_VIDEO);
			
			// Get the Embed
			if(!$embed = Attachment::getVideoEmbedFromURL($videoURL))
			{
				return false;
			}
			
			// Create the attachment
			$attachment = new Attachment($attachType, $videoURL);
			
			// Update the attachment's important settings
			$attachment->setSource($sourceURL);
			
			if($attachTitle) { $attachment->setTitle($attachTitle); }
			if($attachDesc) { $attachment->setDescription($attachDesc); }
			
			// Add important data
			$attachment->setEmbed($embed);
			
			// Save the attachment into the database
			$attachment->save();
			
			// Create the hashtag post
			return AppSubmit::run($this->data['uni_id'], $attachType, $attachment->id, $hashtags, $message);
		}
		
		// If we're publishing a message
		else if($message != "" or $attachDesc)
		{
			// Prepare Values
			if(!$message)
			{
				$message = $attachDesc;
			}
			
			$attachType = ($attachType ? $attachType : Attachment::TYPE_COMMENT);
			
			// Create the attachment
			$attachment = new Attachment($attachType);
			
			// Update the attachment's important settings
			$attachment->setSource($sourceURL);
			$attachment->setTitle($attachTitle);
			$attachment->setDescription($message);
			
			// Save the attachment into the database
			$attachment->save();
			
			// Create the hashtag post
			return AppSubmit::run($this->data['uni_id'], $attachType, $attachment->id, $hashtags, $message);
		}
		
		return false;
	}
	
	
/****** Run the re-submission API ******/
	private function resubmit
	(
		$hashtags		// <int:str> The list of hashtags being submitted.
	,	$imageURL		// <str> The URL of the image being sent ("" if none).
	,	$videoURL		// <str> The URL of the video that was used in the original ("" if none).
	,	$sourceURL		// <str> The URL that was used as the original source to return to.
	)					// RETURNS <bool> TRUE if the resubmission succeeded, FALSE if not.
	
	// $this->resubmit($hashtags, $imageURL, $videoURL, $sourceURL)
	{
		// Prepare Values
		$attachmentID = 0;
		
		// Retrieve the attachment from the original source, if it was an image
		if($imageURL)
		{
			$attachmentID = Attachment::findAttachmentID($imageURL, $sourceURL);
		}
		
		// Retrieve the attachment from the original source, if it was a video
		else if($videoURL)
		{
			$attachmentID = Attachment::findAttachmentID($videoURL, $sourceURL);
		}
		
		// Make sure the attachment was located
		if(!$attachmentID)
		{
			return false;
		}
		
		// Retrieve the attachment data
		$attachment = Attachment::get($attachmentID);
		
		// Resubmit to the appropriate hashtags
		return AppSubmit::run($attachment['uni_id'], $attachment['type'], $attachmentID, $hashtags, "", true);
	}
	
}
