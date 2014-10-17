<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

class AppTrend_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "AppTrend";
	public $title = "Trending Hashtag Handler";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides functionality for trending hashtags.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `hashtags_trending`
		(
			`hashtag_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`count`					mediumint(8)	unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`hashtag_id`),
			INDEX (`count`, `hashtag_id`)
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
		return DatabaseAdmin::columnsExist("hashtags_trending", array("hashtag_id", "count"));
	}
	
}