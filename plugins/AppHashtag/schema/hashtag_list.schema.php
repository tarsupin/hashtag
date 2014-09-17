<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class hashtag_list_schema {
	
	
/****** Plugin Variables ******/
	public $title = "Trending Hashtags";		// <str> The title for this table.
	public $description = "Tracks the hashtags that are most popular / trending.";		// <str> The description of this table.
	
	// Table Settings
	public $tableKey = "hashtag_list";		// <str> The name of the table.
	public $fieldIndex = array("id");	// <int:str> The field(s) used for the index (for editing, deleting, row ID, etc).
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
		Database::exec("
		CREATE TABLE IF NOT EXISTS `hashtag_list`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			`hashtag`				varchar(22)					NOT NULL	DEFAULT '',
			
			PRIMARY KEY (`id`)
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
		
		$define->set("id")->title("Hashtag ID")->description("The ID of the hashtag.")->isUnique()->isReadonly();
		$define->set("hashtag")->description("The hashtag.")->isUnique()->isReadonly();
		
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
				$schema->addFields("id", "hashtag");
				$schema->sort("id");
				break;
				
			case "search":
				$schema->addFields("id", "hashtag");
				break;
				
			case "create":
			case "edit":
				break;
		}
		
		return $schema;
	}
	
}