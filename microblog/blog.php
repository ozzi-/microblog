<?php
	include("blogconfig.php");

	function renderBlog(){
		$totalPosts=count(glob(dirname(__FILE__)."/content/*"));
		$totalPages=ceil($totalPosts/blogPostsPerPage);	
		$permaLink=false;
		
		echo('<style>'.file_get_contents(dirname(__FILE__)."/blog.css").'</style>');
		$valueArray=[];
		$valueArray['linkHome']=blogURL;
		echo("<span class=\"blogMain\">");

		echo(injectTemplate($valueArray,file_get_contents(dirname(__FILE__)."/templates/header.html")));
		
		if (isset($_GET['page'])) {
			$listPosts=false;
			$page=ctype_digit($_GET['page'])?htmlspecialchars($_GET['page']):1;
			$page=($page<1||$page>10000)?1:$page;
			$firstPost=($totalPosts)-($page*blogPostsPerPage)+blogPostsPerPage;
			$currentPost=$firstPost;
		}elseif(isset($_GET['permalink'])){
			$permaLink=ctype_digit($_GET['permalink'])?htmlspecialchars($_GET['permalink']):1;
			$firstPost=$permaLink+blogPostsPerPage-1;
			$currentPost=$permaLink;
			$listPosts=false;
		}else{
			$page="1";
			$currentPost=$totalPosts;
			$listPosts=true;
		}

		if ($totalPosts===0) {
			echo (noPostsYet);
		}else{
			if(!$permaLink && !$listPosts){
				outputPageNav($totalPages,$page,$listPosts);
			}
			echo("<br>");
			$postCounter=0;
			while( (!$listPosts && $currentPost > $firstPost-blogPostsPerPage && $currentPost>0) || ($listPosts && $currentPost > 0) ){
				$filename=dirname(__FILE__)."/content/".$currentPost.'.html';
				$content = file_get_contents($filename);
				$title=strtok($content, "\n");
				if($permaLink!==false){
					echo("<meta itemprop=\"name\" content=\"$title\">");
					echo("<meta name=\"description\" content=\"$title\">");
					echo("<meta name=\"title\" content=\"".blogName."\">");
				}
				$content=substr($content, strpos($content, "\n") + 1);
				if($listPosts){
					$lpage=floor(($totalPosts-$currentPost) / blogPostsPerPage)+1;
					$valueArray['postId']=$currentPost;
					$valueArray['title']=$title;
					$valueArray['postURL']=blogURL.parameterChar.'page='.($lpage);
					echo(injectTemplate($valueArray,file_get_contents(dirname(__FILE__)."/templates/listtitle.html")));
				}else{
					outputBlogPost($currentPost,$title,$content,$permaLink);
				}
				$currentPost--;
			}
			echo(injectTemplate($valueArray,file_get_contents(dirname(__FILE__)."/templates/spacer.html")));
			if(!$permaLink && !$listPosts){
				outputPageNav($totalPages,$page,$listPosts);
			}
		}
		echo("</span>");
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

	function outputBlogPost($currentPost,$title,$content,$permaLink){
		$valueArray['postId']=$currentPost;
		$valueArray['title']=$title;
		echo(injectTemplate($valueArray,file_get_contents(dirname(__FILE__)."/templates/title.html")));
		if(!$permaLink){
			$titleSafe = str_replace(" ", "_", $title);
			$valueArray['permaLink']=blogURL.parameterChar.'permalink='.$currentPost.'#'.$titleSafe;
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
