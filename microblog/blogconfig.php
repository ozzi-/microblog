<?php
	// Posts displayed per page
	define ( "blogPostsPerPage", 3 );
	
	// Link to the blog page itself
	define ( "blogURL", "index.php");

	// The character appended after 'blogContentPath'.
	// Use either ? or &. 
	// Use ? if your path doesn't have a url parameter itself (index.php)
	// Use & if your path already has a url paramter (index.php?view=blog)
	define ( "parameterChar","?"); 
?>