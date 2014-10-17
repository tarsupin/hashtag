<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

class AppHashtag_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "AppHashtag";
	public $title = "Hashtag System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides the system to view and interact with hashtags.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `hashtag_list`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			`hashtag`				varchar(22)					NOT NULL	DEFAULT '',
			
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		// Vertical partitioning of the hashtag table.
		Database::exec("
		CREATE TABLE IF NOT EXISTS `hashtag_list_by_name`
		(
			`hashtag`				varchar(22)					NOT NULL	DEFAULT '',
			`hashtag_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`hashtag`, `hashtag_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8
		
		PARTITION BY RANGE COLUMNS(hashtag) (
			PARTITION p0 VALUES LESS THAN ('a'),
			PARTITION p1 VALUES LESS THAN ('e'),
			PARTITION p2 VALUES LESS THAN ('i'),
			PARTITION p3 VALUES LESS THAN ('m'),
			PARTITION p4 VALUES LESS THAN ('q'),
			PARTITION p5 VALUES LESS THAN ('u'),
			PARTITION p6 VALUES LESS THAN MAXVALUE
		);
		");
		
		/*
			The hashtag_posts tables track all posts made to hashtag, but not necessarily the entirety
			of their content since some content is posted to multiple hashtags.
			
			To reduce the amount of duplicated content, we join that content with this table. This also
			allows for faster sorting.
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `hashtag_posts`
		(
			`hashtag_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`type`					tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			`date_posted`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`attachment_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			INDEX (`hashtag_id`, `type`, `date_posted`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(hashtag_id) PARTITIONS 63;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `hashtags_recent`
		(
			`hashtag_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`date_posted`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			INDEX (`hashtag_id`),
			INDEX (`date_posted`)
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
		$pass1 = DatabaseAdmin::columnsExist("hashtag_list", array("id", "hashtag"));
		$pass2 = DatabaseAdmin::columnsExist("hashtag_list_by_name", array("hashtag", "hashtag_id"));
		$pass3 = DatabaseAdmin::columnsExist("hashtag_posts", array("hashtag_id", "type"));
		$pass4 = DatabaseAdmin::columnsExist("hashtags_recent", array("hashtag_id", "date_posted"));
		
		return ($pass1 and $pass2 and $pass3 and $pass4);
	}
	
}
