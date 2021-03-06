<?php
	// Link to the blog page itself
	define ( "blogURL", "index.php");

	// The character appended after 'blogContentPath'.
	// Use either ? or &.
	// Use ? if your path doesn't have a url parameter itself (index.php)
	// Use & if your path already has a url paramter (index.php?view=blog)
	define ( "blogParameterChar","?");

	// Set to 'null' to have no graph image thus the target page picking its own
	define ( "blogDefaultOpenGraphImagePath", null);


	// magic constants - do not change unless you know what you are doing
	define("TITLE","title");
	define("PLS","pl");
	define("PLL","permalink");
	define("ID","id");
	// name of the db file
	define ("blogDBName", "db.json");
	// Posts displayed per page
	define ( "blogPostsPerPage", 1 );
?>
