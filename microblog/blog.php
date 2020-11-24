<?php	
	include("blogconfig.php");
	include("db.php");
		
	function outputBlogPosts(){
		if(isset($_GET[PLS]) || isset($_GET[PLL])){
			outputSpecificBlogPost(getSetPermaLinkID());
		}elseif(isset($_GET["category"])){
			outputCategoryPage();
		}else{
			outputBlogPostsInternal(DB::$obj);
		}
	}

	function outputCategoryPage(){
		$categoryIsList = $_GET["category"]=="list";
		outputHeaderNoNavigation();
		if(isset(DB::$obj["categories"][$_GET["category"]]) || $_GET["category"]=="all"){
			outputCategoryPosts();
		}else if($categoryIsList){
			outputCategoryBadges();
		}
	}

	function outputCategoryPosts(){
		$values["category"] = htmlspecialchars($_GET["category"], ENT_QUOTES, 'UTF-8');
		outputTemplate($values,"categoryfilter");
		foreach (DB::$obj as &$entry) {
			
			if(isset($entry[ID]) && (in_array($entry[ID],DB::$obj["categories"][$_GET["category"]]) || $_GET["category"]==="all") ){
				$values["id"]=$entry[ID];
				$values["title"]=$entry[TITLE];
				$values["href"]=blogURL.blogParameterChar.PLS.'='.$entry[ID];
				outputTemplate($values,"postlink");
			}
		}
	}

	function outputCategoryBadges(){
		$values['category']="all";
		$values["categoryHref"]=blogURL.blogParameterChar.'category=all';
		$values['categoryCount']=" [".(count(DB::$obj)-1)."]";
		outputTemplate($values,"category");
		foreach(array_keys(DB::$obj["categories"]) as $category){
			$values['category']=$category;
			$values["categoryHref"]=blogURL.blogParameterChar.'category='.$category;
			$values['categoryCount']=" [".count(DB::$obj["categories"][$category])."]";
			outputTemplate($values,"category");
		}
	}

	function outputMetaData(){
		if(isset($_GET[PLS]) || isset($_GET[PLL])){
			$permalinkID = getSetPermaLinkID();
			$filePath=dirname(__FILE__).DIRECTORY_SEPARATOR."content".DIRECTORY_SEPARATOR.$permalinkID.'.html';
			foreach (DB::$obj as &$entry) {
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

	function outputBlogPostsInternal(){
		$offset = 0;
		$page = 1;
		if(isset($_GET["page"])){
			$page = intval($_GET["page"]);
			$offset = ($page-1)*blogPostsPerPage;
		}
		$hasMorePages=$page>=ceil((count(DB::$obj)-1)/blogPostsPerPage);
		$values['linkHome']=blogURL;
		$values['pageBackHref']=blogURL.blogParameterChar.'page='.($page-1);
		$values['pageForwardHref']=blogURL.blogParameterChar.'page='.($page+1);
		$values['hasNewerPosts']=($page<=1)?"display:none":"";
		$values['hasOlderPosts']=$hasMorePages?"display:none":"";
		$values['byCategoryHref']=blogURL.blogParameterChar.'category=list';
		$values['showCategoryHref']="";
		outputTemplate($values,"header");

		$index = 0;
		$amountAdded = 0;
		foreach (DB::$obj as &$entry) {
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

	function outputSpecificBlogPost($id){
		outputHeaderNoNavigation();
		foreach (DB::$obj as &$entry) {
			if(isset($entry[ID]) && $entry[ID] == $id){
				$content = loadBlogPostContentWithCache($entry);
				outputBlogPost($entry,$content,true);
			}
		}
	}

	function outputHeaderNoNavigation(){
		$values['linkHome']=blogURL;
		$values['pageBackHref']="#";
		$values['pageForwardHref']="#";
		$values['hasNewerPosts']="display:none";
		$values['hasOlderPosts']="display:none";
		$values['byCategoryHref']=blogURL.blogParameterChar.'category=list';
		$values['showCategoryHref']="display:none;";
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
				$values["categoryHref"]=blogURL.blogParameterChar.'category='.$category;
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

	function sortByOrder($a, $b) {
		return $a[ID] < $b[ID];
	}
	function sortByCount($a, $b) {
		return count($a) < count($b);
	}
?>
