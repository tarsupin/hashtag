<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------
------ About this API ------
----------------------------

This API allows other sites to connect and pull the current "Trending Tags".


------------------------------
------ Calling this API ------
------------------------------
	
	// Prepare the API Packet
	$packet = array(
		"count"		=> 6
	);
	
	// Connect to the API and pull the response
	$apiData = Connect::to("hashtag", "TrendingWidgetAPI", $packet);
	
	
[ Possible Responses ]
	???

*/

class TrendingWidgetAPI extends API {
	
	
/****** API Variables ******/
	public $isPrivate = true;			// <bool> TRUE if this API is private (requires an API Key), FALSE if not.
	public $encryptType = "";			// <str> The encryption algorithm to use for response, or "" for no encryption.
	public $allowedSites = array();		// <int:str> the sites to allow the API to connect with. Default is all sites.
	public $microCredits = 100;			// <int> The cost in microcredits (1/10000 of a credit) to access this API.
	public $minClearance = 0;			// <int> The clearance level required to use this API.
	
	
/****** Run the API ******/
	public function runAPI (
	)					// RETURNS <str:mixed>
	
	// $this->runAPI()
	{
		$count = isset($this->data['count']) ? (int) $this->data['count'] : 6;
		$count = min(10, $count);
		
		$tagList = AppTrend::getTrendingTags();
		
		array_splice($tagList, $count);
		
		return $tagList;
	}
	
}
