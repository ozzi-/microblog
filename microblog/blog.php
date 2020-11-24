<?php
	include("blogconfig.php");

	$db = loadDB();

	function outputBlogPosts(){
		global $db;
		if(isset($_GET[PLS]) || isset($_GET[PLL])){
			outputSpecificBlogPost($db,getSetPermaLinkID());
		}elseif(isset($_GET["category"])){
			outputCategoryPage();
		}else{
			outputBlogPostsInternal($db);
		}
	}

	function outputCategoryPage(){
		global $db;
		$categoryIsList = $_GET["category"]=="list";
		outputHeaderNoNavigation(!$categoryIsList);
		if(isset($db["categories"][$_GET["category"]])){
			outputCategoryPosts();
		}else if($categoryIsList){
			outputCategoryBadges();
		}
	}

	function outputCategoryPosts(){
		global $db;
		$values["category"] = htmlspecialchars($_GET["category"], ENT_QUOTES, 'UTF-8');
		outputTemplate($values,"categoryfilter");
		foreach ($db as &$entry) {
			if(isset($entry[ID]) && in_array($entry[ID],$db["categories"][$_GET["category"]])){
				$values["id"]=$entry[ID];
				$values["title"]=$entry[TITLE];
				$values["href"]=blogParameterChar.PLS.'='.$entry[ID];
				outputTemplate($values,"postlink");
			}
		}
	}

	function outputCategoryBadges(){
		global $db;
		foreach(array_keys($db["categories"]) as $category){
			$values['category']=$category;
			$values["categoryHref"]=blogParameterChar.'category='.$category;
			$values['categoryCount']=" [".count($db["categories"][$category])."]";
			outputTemplate($values,"category");
		}
	}

	function outputMetaData(){
		global $db;
		if(isset($_GET[PLS]) || isset($_GET[PLL])){
			$permalinkID = getSetPermaLinkID();
			$filePath=dirname(__FILE__).DIRECTORY_SEPARATOR."content".DIRECTORY_SEPARATOR.$permalinkID.'.html';
			foreach ($db as &$entry) {
				if(isset($entry[ID]) && $entry[ID] == $permalinkID){
					$content = loadBlogPostContentWithCache($entry);
					outputMetaDataInternal($entry, $content);
				}
			}
		}
	}

	$contentCache=null;
	function loadBlogPostContentWithCache($entry){
		global $contentCache;
		$contentCache = ($contentCache==null) ? loadBlogPostContent($entry) :  $contentCache;
		return $contentCache;
	}

	function outputMetaDataInternal($blogEntry, $content){
		$title =  $blogEntry["og:title"];
		$preview = addslashes(strip_tags($content));
		$description = !isset($blogEntry["og:description"])?$preview:$blogEntry["og:description"];
		$titleSafe = str_replace(" ", "_", $title);
		$url = !isset($blogEntry["og:url"])?blogURL.blogParameterChar.PLS.'='.$blogEntry[ID].'#'.$titleSafe:$blogEntry["og:url"];
		$type = !isset($blogEntry["og:type"])?"article":$blogEntry["type"];
		if(blogDefaultOpenGraphImagePath!=null){
			$image = !isset($blogEntry["og:image"])?blogDefaultOpenGraphImagePath:$blogEntry["og:image"];
		}
		?>
		<meta name="description" content="<?= $description?>">
		<meta name="title" content="<?= $title ?>">
		<meta property="og:type" content="<?= $type ?>"/>
		<meta property="og:title" content="<?= $title ?>"/>
		<meta property="og:description" content="<?= $description?>"/>
		<meta name="twitter:card" content="summary"/>
		<meta name="twitter:title" content="<?= $title ?>" />
		<meta name="twitter:description" content="<?= $description?>" />
		<?php
		if(blogDefaultOpenGraphImagePath!=null){
			?><meta property="og:image" content="<?= $image ?>"/><?php
		}
	}

	function outputBlogPostsInternal($db){
		$offset = 0;
		$page = 1;
		if(isset($_GET["page"])){
			$page = intval($_GET["page"]);
			$offset = ($page-1)*blogPostsPerPage;
		}
		$hasMorePages=$page>=ceil((count($db)-1)/blogPostsPerPage);
		$values['linkHome']=blogURL;
		$values['pageBackHref']=blogParameterChar.'page='.($page-1);
		$values['pageForwardHref']=blogParameterChar.'page='.($page+1);
		$values['hasNewerPosts']=($page<=1)?"display:none":"";
		$values['hasOlderPosts']=$hasMorePages?"display:none":"";
		$values['byCategoryHref']=blogParameterChar.'category=list';
		$values['showCategoryHref']="";
		outputTemplate($values,"header");

		$index = 0;
		$amountAdded = 0;
		foreach ($db as &$entry) {
			if(isset($entry[ID])){
				$index++;
				if($index>$offset && $amountAdded<blogPostsPerPage){
					$amountAdded ++;
					$content = loadBlogPostContent($entry);
					outputBlogPost($entry, $content,false);
				}
			}
		}
		outputTemplate($values,"footer");
	}

	function outputSpecificBlogPost($db,$id){
		outputHeaderNoNavigation(true);
		foreach ($db as &$entry) {
			if(isset($entry[ID]) && $entry[ID] == $id){
				$content = loadBlogPostContentWithCache($entry);
				outputBlogPost($entry,$content,true);
			}
		}
	}

	function outputHeaderNoNavigation($showCategoryHref){
		$values['linkHome']=blogURL;
		$values['pageBackHref']="#";
		$values['pageForwardHref']="#";
		$values['hasNewerPosts']="display:none";
		$values['hasOlderPosts']="display:none";
		$values['byCategoryHref']=blogParameterChar.'category=list';
		$values['showCategoryHref']=$showCategoryHref?"":"display:none;";
		outputTemplate($values,"header");
	}

	function outputBlogPost($blogPost, $content, $isPermaLink){
		$values['postId']=$blogPost[ID];
		$values['title']=$blogPost[TITLE];
		$values['content']=$content;
		$titleSafe = str_replace(" ", "_", $blogPost[TITLE]);
		$values['socialbuttons']=buildSocialButtons($blogPost,$titleSafe);
		
		outputTemplate($values,"title");
		if(!$isPermaLink){
			$values['pl']=blogURL.blogParameterChar.PLS.'='.$blogPost[ID].'#'.$titleSafe;
			outputTemplate($values,"permalink");
		}
		$values['categories']="";
		if(isset($blogPost["categories"])){
			$categories="";
			foreach($blogPost["categories"] as $category) {
				$values['category']=$category;
				$values["categoryHref"]=blogParameterChar.'category='.$category;
				$values["categoryCount"]="";
				$categories.=(injectTemplate($values,file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."category.html")));
			}	
			$values['categories']=$categories;
		}
		outputTemplate($values,"content");
		echo("<br><br>");
	}

	function injectTemplate($values,$template){
		$needle = "{{";
		$needleLength=strlen($needle);
		$needleEnd = "}}";
		$lastPos = 0;
		$variables = array();
		$index=0;

		while (($lastPos = strpos($template, $needle, $lastPos))!== false) {
			if(($lastPosEnd = strpos($template, $needleEnd, $lastPos))!== false){
				$varLength=$lastPosEnd-$lastPos-$needleLength;
				$var=substr($template,$lastPos+$needleLength,$varLength);
				if(!isset($values[$var])){
					die("Invalid variable '$var' in template");
				}
				$variables[$index]=$var;
				$index++;
			}else{
				die("Error in template, missing '$needleEnd'");
			}
			$lastPos=$lastPos + $varLength;
		}

		foreach ($variables as $variable) {
			$template=str_replace($needle.$variable.$needleEnd,$values[$variable],$template);
		}
		return $template;
	}

	function buildSocialButtons($blogPost,$titleSafe){
		$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
		$base = $protocol.$_SERVER['HTTP_HOST'];
		$permaLinkAbsolute = $base."/".urlencode(blogURL.blogParameterChar.PLS.'='.$blogPost[ID].'#'.$titleSafe);
		$socialArray['socialbuttonURL']=$permaLinkAbsolute;
		$socialArray['socialbuttonText']=urlencode($blogPost[TITLE]);
		$socialArray['socialbuttonTextRaw']=$blogPost[TITLE];
		$svgs = glob(dirname(__FILE__).DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."img".DIRECTORY_SEPARATOR."*.{svg}", GLOB_BRACE);
		foreach($svgs as $svg) {
			$socialArray['socialButtonImg_'.basename($svg)]=file_get_contents($svg);
		}
		return injectTemplate($socialArray,file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."socialbuttons.html"));
	}

	function loadBlogPostContent($blogPost){
		$path = dirname(__FILE__).DIRECTORY_SEPARATOR."content".DIRECTORY_SEPARATOR.intval($blogPost[ID]).'.html';
		$content = @file_get_contents($path);
		if($content === FALSE){
			die("Could not load blog post content ".$path);
		}
		return $content;
	}

	function outputTemplate($values,$template){
		echo(injectTemplate($values,file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR.$template.".html")));		
	}

	function getSetPermaLinkID(){
		return intval(isset($_GET[PLS]) ? $_GET[PLS] : $_GET[PLL]);
	}

	// **********
	// *** DB ***
	// **********

	function loadDB(){
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
		return validateAndBuild($dbObj);
	}

	function validateAndBuild($db){
		// validate
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

	function sortByOrder($a, $b) {
		return $a[ID] < $b[ID];
	}
	function sortByCount($a, $b) {
		return count($a) < count($b);
	}
?>