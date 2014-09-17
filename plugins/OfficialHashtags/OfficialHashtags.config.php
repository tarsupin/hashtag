<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

class OfficialHashtags_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "OfficialHashtags";
	public $title = "Official Hashtag Tools";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides tools to work with officially recognized hashtags.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `hashtag_official`
		(
			`hashtag`				varchar(22)					NOT NULL	DEFAULT '',
			`title`					varchar(45)					NOT NULL	DEFAULT '',
			`keywords`				varchar(120)				NOT NULL	DEFAULT '',
			
			`primary_url`			varchar(72)					NOT NULL	DEFAULT '',
			`disambiguate`			varchar(45)					NOT NULL	DEFAULT '',
			`description`			varchar(250)				NOT NULL	DEFAULT '',
			
			UNIQUE (`hashtag`),
			FULLTEXT (`hashtag`, `title`, `keywords`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		return DatabaseAdmin::columnsExist("hashtag_official", array("hashtag", "title", "keywords"));
	}
	
}