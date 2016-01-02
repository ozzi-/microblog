<?php
	// Title shown on top of the page
	define ( "blogName", "A few thoughts and tricks" );

	// Posts displayed per page
	define ( "blogPostsPerPage", "3" );
	
	// Link to the blog page itself
	define ( "blogURL", "index.php?view=blog");

	// The character appended after 'blogContentPath'.
	// Use either ? or &. 
	// Use ? if your path doesn't have a url parameter itself (index.php)
	// Use & if your path already has a u url paramter (index.php?view=blog)
	define ( "parameterChar","&"); 
	
	// Path where the blog content lies, should end with /
	define ( "blogContentPath", "./blogcontent/");

	// Path where the blog titles lie, should end with /
	define ( "blogContentTitlesPath" ,"./blongcontent/titles/");

	// Do not change
	define ( "blogContentSearchPath", blogContentPath."*.*" );
	
	// Path where the css lies
	define ( "blogCSSPath", "css/blog.css" );

	// Message that is displayed if no content was found
	define ( "noPostsYet","<hr>There aren't any Posts yet. Make sure to come back soon!<hr>");
?>
