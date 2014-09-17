<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class hashtag_posts_schema {
	
	
/****** Plugin Variables ******/
	public $title = "Hashtag Posts";		// <str> The title for this table.
	public $description = "Stores the hashtag posts.";		// <str> The description of this table.
	
	// Table Settings
	public $tableKey = "hashtag_posts";		// <str> The name of the table.
	public $fieldIndex = array("hashtag_id", "type", "date_posted");	// <int:str> The field(s) used for the index (for editing, deleting, row ID, etc).
	public $autoDelete = false;					// <bool> TRUE will delete rows instantly, FALSE will require confirmation.
	
	// Permissions
	// Note: Set a permission value to 11 or higher to disallow it completely.
	public $permissionView = 6;			// <int> The clearance level required to view this table.
	public $permissionSearch = 6;		// <int> The clearance level required to search this table.
	public $permissionCreate = 11;		// <int> The clearance level required to create an entry on this table.
	public $permissionEdit = 11;		// <int> The clearance level required to edit an entry on this table.
	public $permissionDelete = 11;		// <int> The clearance level required to delete an entry on this table.
	
	
/****** Install the table ******/
	public function install (
	)			// RETURNS <bool> TRUE if the installation was success, FALSE if not.
	
	// $schema->install();
	{
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
			`message`				varchar(255)				NOT NULL	DEFAULT '',
			
			`date_posted`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`attachment_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			INDEX (`hashtag_id`, `type`, `date_posted`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(hashtag_id) PARTITIONS 63;
		");
		
		return DatabaseAdmin::tableExists($this->tableKey);
	}
	
	
/****** Build the schema for the table ******/
	public function buildSchema (
	)			// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $schema->buildSchema();
	{
		Database::startTransaction();
		
		// Create Schmea
		$define = new SchemaDefine($this->tableKey, true);
		
		$define->set("hashtag_id")->title("Hashtag ID")->description("The ID of the hashtag the posts are in.")->isUnique()->isReadonly();
		$define->set("type")->description("The type of post.");
		$define->set("message")->description("The message for the post.");
		$define->set("date_posted")->description("The timestamp of when this post was created.")->fieldType("timestamp");
		$define->set("attachment_id")->title("Attachment ID")->description("The ID of the attachment related to this post.");
		
		Database::endTransaction();
		
		return true;
	}
	
	
/****** Set the rules for interacting with this table ******/
	public function __call
	(
		$name		// <str> The name of the method being called ("view", "search", "create", "delete")
	,	$args		// <mixed> The args sent with the function call (generaly the schema object)
	)				// RETURNS <mixed> The resulting schema object.
	
	// $schema->view($schema);		// Set the "view" options
	// $schema->search($schema);	// Set the "search" options
	{
		// Make sure that the appropriate schema object was sent
		if(!isset($args[0])) { return; }
		
		// Set the schema object
		$schema = $args[0];
		
		switch($name)
		{
			case "view":
				$schema->addFields("hashtag_id", "type", "message", "attachment_id", "date_posted");
				$schema->sort("hashtag_id");
				$schema->sort("type");
				break;
				
			case "search":
				$schema->addFields("hashtag_id", "type", "date_posted");
				break;
				
			case "create":
			case "edit":
				break;
		}
		
		return $schema;
	}
	
}