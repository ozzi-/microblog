<?php

	class DB{
		public static $obj;
		public static function loadDB() {
			$db = @file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.blogDBName);
			if($db === FALSE) {
			  die("Could not open DB from ".$dbJsonPath);
			}
			$dbObj = json_decode($db,true);
			if($dbObj==NULL){
				switch (json_last_error()) {
					case JSON_ERROR_DEPTH:
						die('Could not parse DB - Maximum stack depth exceeded');
					case JSON_ERROR_STATE_MISMATCH:
						die('Could not parse DB - Underflow or the modes mismatch');
					case JSON_ERROR_CTRL_CHAR:
						die('Could not parse DB - Unexpected control character found');
					case JSON_ERROR_SYNTAX:
						die('Could not parse DB - Syntax error, malformed JSON');
					case JSON_ERROR_UTF8:
						die('Could not parse DB - Malformed UTF-8 characters, possibly incorrectly encoded');
					default:
						die('Could not parse DB - Unknown error');
				}
			}
			self::$obj = self::validateAndBuild($dbObj);
		}
		public static function validateAndBuild($db){
			$ids = array();
			foreach ($db as &$entry) {
				if(!(isset($entry[TITLE]) && isset($entry[ID]))){
					die("DB contains entry without 'title' or 'id'");
				}
				if(in_array($entry[ID],$ids)){
					die("DB contains duplicate id (".$entry[ID].")");
				}
				array_push($ids, $entry[ID]);
			}
			// sort
			usort($db, "sortByOrder");
			// build
			foreach ($db as &$entry) {
				if(isset($entry["categories"])){
					foreach ($entry["categories"] as &$category) {
						if(!isset($db["categories"][$category])){
							$db["categories"][$category]=array();
						}
						array_push($db["categories"][$category],$entry[ID]);
					}
				}
			}
			uasort($db["categories"], "sortByCount");
			return $db;
		}
	}
	DB::loadDB();
?>
