<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------------------
------ About the OfficialHashtags Plugin ------
-----------------------------------------------

This plugin will provide tools for creating and working with officially recognized hashtags.

Officially recognized hashtags are different from common hashtags since we've curated the list. Official hashtags, for example, will have a consistent value for a city, whereas people might hashtag a city very differently on other sites.

An example of an Official Hashtag would be: #MadisonWI

An example of non-official hashtags might be: #MadisonWisconsin, #Madison, #MadTown, #MadCity, #MadCityWI

Since the lack of clarity exists on non-official hashtags, this official list can be used to ensure that people can always find the appropriate content on our system.


-------------------------------
------ Methods Available ------
-------------------------------

$search = OfficialHashtags::search();

*/

abstract class OfficialHashtags {
	
	
/****** Search through the official hashtag list ******/
	public static function search
	(
		string $query		// <str> The text that has been searched for.
	): array <int, array<str, mixed>>				// RETURNS <int:[str:mixed]> list of relevant hashtags, array() if failed.
	
	// $hashtags = OfficialHashtags::search($query);
	{
		// Prepare Values
		$words = StringUtils::getWordList($query, "'");
		$matchQuery = Sanitize::variable($query, " '");
		$fullTextMode = "";
		
		// If the user is typing a query:
		$lastWord = $words[count($words) - 1];
		
		if(strlen($lastWord) > 2)
		{
			$words[count($words) - 1] = $lastWord . '*';
			
			// This allows us to do wildcards on the last keyword
			$fullTextMode = " IN BOOLEAN MODE";
			
			// Prepare the match query
			$matchQuery = implode(" ", $words);
		}
		
		// Retrieve the most relevant search results
		return Database::selectMultiple("SELECT hashtag, title, keywords, primary_url, disambiguate, description, MATCH(hashtag, title, keywords) AGAINST (?" . $fullTextMode . ") as score FROM hashtag_official WHERE MATCH(hashtag, title, keywords) AGAINST (?" . $fullTextMode . ") ORDER BY score DESC LIMIT 5", array($matchQuery, $matchQuery));
	}
	
	
/****** Create (or update) an official Hashtag ******/
	public static function set
	(
		string $hashtag			// <str> The official hashtag to create (or update), e.g. "GBPackers"
	,	string $title				// <str> The title used for this hashtag, e.g. "Green Bay Packers"
	,	string $keywords			// <str> Any additional keywords to apply, e.g. "Lambeau Field"
	,	string $primaryURL			// <str> The primary url that this hashtag is associated with.
	,	string $disambiguate = ""	// <str> If the title has multiple meanings, this can be used to distinguish it.
	,	string $description = ""	// <str> The description of this official hashtag, if applicable.
	): bool						// RETURNS <bool> TRUE if the official hashtag is set, FALSE on error.
	
	// OfficialHashtags::set($hashtag, $title, $keywords, [$disambiguate], [$description]);
	{
		return Database::query("REPLACE INTO hashtag_official (hashtag, title, keywords, primary_url, disambiguate, description) VALUES (?, ?, ?, ?, ?, ?)", array($hashtag, $title, $keywords, $primaryURL, $disambiguate, $description));
	}
}