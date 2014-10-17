<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Hashtag Installation
abstract class Install extends Installation {
	
	
/****** Plugin Variables ******/
	
	// These addon plugins will be selected for installation during the "addon" installation process:
	public static array <str, bool> $addonPlugins = array(	// <str:bool>
		"Attachment"		=> true
	,	"Notifications"		=> true
	);
	
	
/****** App-Specific Installation Processes ******/
	public static function setup(
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	{
		// Update attachments to include a UniID associated with them
		DatabaseAdmin::addColumn("attachment", "uni_id", "int(10) unsigned not null", 0);
		
		return true;
	}
}