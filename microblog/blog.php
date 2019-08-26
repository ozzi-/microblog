<?php
	include("blogconfig.php");

	function renderBlog(){
		$totalPosts=count(glob(dirname(__FILE__)."/content/*"));
		$totalPages=ceil($totalPosts/blogPostsPerPage);
		$permaLink=false;

		echo('<style>'.file_get_contents(dirname(__FILE__)."/blog.css").'</style>');
		$valueArray=[];
		$valueArray['linkHome']=blogURL;
		echo("<div class=\"blogMain\">");

		if (isset($_GET['page'])) {
			$listPosts=false;
			$page=ctype_digit($_GET['page'])?htmlspecialchars($_GET['page']):1;
			$page=($page<1||$page>10000)?1:$page;
			$firstPost=($totalPosts)-($page*blogPostsPerPage)+blogPostsPerPage;
			$currentPost=$firstPost;
		}elseif(isset($_GET['pl'])||isset($_GET['permalink'])){
			if(isset($_GET['pl'])){
				$permaLink=ctype_digit($_GET['pl'])?htmlspecialchars($_GET['pl']):1;
				$permaLink=($permaLink<1||$permaLink>10000)?1:$permaLink;
			}else{
				$permaLink=ctype_digit($_GET['permalink'])?htmlspecialchars($_GET['permalink']):1;
			}
			$firstPost=$permaLink+blogPostsPerPage-1;
			$currentPost=$permaLink;
			$listPosts=false;
		}else{
			echo(injectTemplate($valueArray,file_get_contents(dirname(__FILE__)."/templates/header.html")));
			$page="1";
			$currentPost=$totalPosts;
			$listPosts=true;
		}

		if ($totalPosts===0) {
			echo ("Nothing here yet, make sure to come back and check again soon!");
		}else{
			if(!$permaLink && !$listPosts){
				outputPageNav($totalPages,$page,$listPosts);
			}
			$postCounter=0;
			echo("<br>");

			while( (!$listPosts && $currentPost > $firstPost-blogPostsPerPage && $currentPost>0) || ($listPosts && $currentPost > 0) ){
				$filePath=dirname(__FILE__)."/content/".$currentPost.'.html';
				$blogEntry = loadBlogEntry($filePath);

				if($permaLink!==false){
					renderMetaData($blogEntry,$currentPost);
				}

				if($listPosts){
					$lpage=floor(($totalPosts-$currentPost) / blogPostsPerPage)+1;
					$valueArray['postId']=$currentPost;
					$valueArray['title']=$blogEntry["title"];
					$valueArray['postURL']=blogURL.parameterChar.'page='.($lpage);
					echo(injectTemplate($valueArray,file_get_contents(dirname(__FILE__)."/templates/listtitle.html")));
				}else{
					outputBlogPost($currentPost,$blogEntry["title"],$blogEntry["content"],$permaLink);
				}
				$currentPost--;
			}
			echo(injectTemplate($valueArray,file_get_contents(dirname(__FILE__)."/templates/spacer.html")));
			if(!$permaLink && !$listPosts){
				outputPageNav($totalPages,$page,$listPosts);
			}
		}
		echo("</div>");
	}

	function outputPageNav($totalPages,$navPage,$listPosts){
		echo('<center><a href="'.blogURL.parameterChar.'page=1" class="blogPageLink">&lt;&lt;</a>|');
		if($totalPages==1){
			echo('&nbsp;<a href="'.blogURL.parameterChar.'page='.($navPage).'" class="blogPageLink"># '.($navPage).'</a>|');
		}else{
			if ($navPage-1>=1){
				if(!($navPage+1<=$totalPages) && $navPage==$totalPages && ($navPage-2)>=1){
					outputPageLink($navPage-2);
				}
				outputPageLink($navPage-1);
			}
			outputPageLink($navPage,true&&!$listPosts);
			if ($navPage+1<=$totalPages){
				outputPageLink($navPage+1);
				if(!($navPage-1>=1)&&$navPage==1&&($navPage+2<=$totalPages)){
					outputPageLink($navPage+2);
				}
			}
		}
		echo('<a href="'.blogURL.parameterChar.'page='.$totalPages.'" class="blogPageLink">&gt;&gt; </a> </center>');
	}

	function renderMetaData($blogEntry,$currentPost){
		$title =  !isset($blogEntry["json"]["og:title"])?$blogEntry["title"]:$blogEntry["json"]["og:title"];
		$preview = strip_tags($blogEntry["content"]);
		$description = !isset($blogEntry["json"]["og:description"])?$preview:$blogEntry["json"]["og:description"];
		$titleSafe = str_replace(" ", "_", $title);
		$url = !isset($blogEntry["json"]["og:url"])?blogURL.parameterChar.'pl='.$currentPost.'#'.$titleSafe:$blogEntry["json"]["og:url"];
		$type = !isset($blogEntry["json"]["og:type"])?"article":$blogEntry["json"]["type"];
		if(pathToDefaultOpenGraphImage!=null){
			$image = !isset($blogEntry["json"]["og:image"])?pathToDefaultOpenGraphImage:$blogEntry["json"]["og:image"];
		}
		?>
		<meta name="description" content="<?= $description?>">
		<meta name="title" content="<?= $title ?>">
		<meta property="og:type" content="<?= $type ?>"/>
		<meta property="og:title" content=" <?= $title ?>"/>
		<meta property="og:description" content="<?= $description?>"/>
		<meta name="twitter:card" content="summary"/>
		<meta name="twitter:title" content="<?= $title ?>" />
		<meta name="twitter:description" content="<?= $description?>" />
		<?php
		if(pathToDefaultOpenGraphImage!=null){
			?><meta property="og:image" content="<?= $image ?>"/><?php
		}
	}

	function loadBlogEntry($filePath){
		$marker = "# Start Content #";
		$blogEntry = array();
		$rawData = @file_get_contents($filePath);
		$rawData = $rawData == null? "" : $rawData;
		$posEndJson = strpos($rawData,$marker);
		$posStartJson = strpos($rawData, "\n");
		$blogEntry['rawData'] = $rawData;
		$blogEntry['title'] = strtok($rawData, "\n");
		$blogEntry['json'] = json_decode(substr($rawData, $posStartJson, $posEndJson-$posStartJson),true);
		$blogEntry['content'] = substr($rawData,$posEndJson+strlen($marker));
		return $blogEntry;
	}

	function outputBlogPost($currentPost,$title,$content,$permaLink){
		$titleSafe = str_replace(" ", "_", $title);
		$valueArray['postId']=$currentPost;
		$valueArray['title']=$title;

		$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
		$base = $protocol.$_SERVER['HTTP_HOST'];
		$permaLinkAbsolute = $base."/".urlencode(blogURL.parameterChar.'pl='.$currentPost.'#'.$titleSafe);
		$imgLinkAbsolute = $base.strtok($_SERVER['REQUEST_URI'],'?')."/templates/img/";
		$socialArray['socialbuttonURL']=$permaLinkAbsolute;
		$socialArray['socialbuttonText']=urlencode($title);
		$svgs = glob(dirname(__FILE__)."/templates/img/*.{svg}", GLOB_BRACE);
		foreach($svgs as $svg) {
			$socialArray['socialButtonImg_'.basename($svg)]=file_get_contents($svg);
		}
		$valueArray['socialbuttons']=injectTemplate($socialArray,file_get_contents(dirname(__FILE__)."/templates/socialbuttons.html"));
		
		echo(injectTemplate($valueArray,file_get_contents(dirname(__FILE__)."/templates/title.html")));
		if(!$permaLink){
			$valueArray['pl']=blogURL.parameterChar.'pl='.$currentPost.'#'.$titleSafe;
			echo(injectTemplate($valueArray,file_get_contents(dirname(__FILE__)."/templates/permalink.html")));
		}
		$valueArray['content']=$content;
		echo(injectTemplate($valueArray,file_get_contents(dirname(__FILE__)."/templates/content.html")));
	}

	function outputPageLink($i,$active=false){
		$boldStart=$active?"<b>":'';
		$boldEnd=$active?"</b>":'';
		echo('&nbsp;<a href="'.blogURL.parameterChar.'page='.$i.'" class="blogCurrentPageLink">'.$boldStart.'# '.($i).$boldEnd.'</a>|');
	}

	function injectTemplate($valueArray,$template){
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
				if(!isset($valueArray[$var])){
					die("invalid variable '$var' in template");
				}
				$variables[$index]=$var;
				$index++;
			}else{
				die("error in template, missing ".$needleEnd);
			}
			$lastPos=$lastPos + $varLength;
		}
		foreach ($variables as $variable) {
			$template=str_replace($needle.$variable.$needleEnd,$valueArray[$variable],$template);
		}
		return $template;
	}
?>
