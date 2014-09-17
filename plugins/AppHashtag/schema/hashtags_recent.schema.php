<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class hashtags_recent_schema {
	
	
/****** Plugin Variables ******/
	public $title = "Recent Hashtags";		// <str> The title for this table.
	public $description = "Tracks the list of recent hashtags created.";		// <str> The description of this table.
	
	// Table Settings
	public $tableKey = "hashtags_recent";		// <str> The name of the table.
	public $fieldIndex = array("hashtag_id");	// <int:str> The field(s) used for the index (for editing, deleting, row ID, etc).
	public $autoDelete = true;					// <bool> TRUE will delete rows instantly, FALSE will require confirmation.
	
	// Permissions
	// Note: Set a permission value to 11 or higher to disallow it completely.
	public $permissionView = 6;			// <int> The clearance level required to view this table.
	public $permissionSearch = 6;		// <int> The clearance level required to search this table.
	public $permissionCreate = 9;		// <int> The clearance level required to create an entry on this table.
	public $permissionEdit = 11;		// <int> The clearance level required to edit an entry on this table.
	public $permissionDelete = 9;		// <int> The clearance level required to delete an entry on this table.
	
	
/****** Install the table ******/
	public function install (
	)			// RETURNS <bool> TRUE if the installation was success, FALSE if not.
	
	// $schema->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `hashtags_recent`
		(
			`hashtag_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`date_posted`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			INDEX (`hashtag_id`),
			INDEX (`date_posted`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
		
		$define->set("hashtag_id")->title("Hashtag ID")->description("The ID of the hashtag.");
		$define->set("date_posted")->description("The timestamp of when this hashtag was posted.")->fieldType("timestamp");
		
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
				$schema->addFields("hashtag_id", "type", "submission_id", "date_posted");
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