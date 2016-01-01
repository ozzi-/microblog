<?php
	include("blogconfig.php");

	echo('<link rel="stylesheet" type="text/css" href="'.blogCSS.'">');
	echo("<div class='blogTitle'>".blogName."</div><br>");

	$totalPosts=count(glob(blogContentSearchPath));
	$totalPages=ceil($totalPosts/postsPerPage);	
	$permaLink=0;

	if (isset($_GET['page'])) {
		$listPosts=false;
		$page="1";
		if($_GET['page']!=''){
			if(is_numeric($_GET['page'])){
				$page=htmlspecialchars($_GET['page']);
			}
		}
		$firstPost=($totalPosts)-($page*postsPerPage)+postsPerPage;
		$currentPost=$firstPost;
	}elseif(isset($_GET['permalink'])){
		if(is_numeric($_GET['permalink'])){
			$permaLink=htmlspecialchars($_GET['permalink']);
		}else{
			$permaLink="1";
		}
		$firstPost=$permaLink+postsPerPage-1;
		$currentPost=$permaLink;
		$listPosts=false;
	}else{
		$page="1";
		$currentPost=$totalPosts;
		$listPosts=true;
	}
	
	if ($totalPosts==0) {
		echo (noPostsYet);
	}

	outputPageNav($totalPages,$page,$permaLink);
	echo("<br>");

	while( ($listPosts==false && $currentPost > $firstPost-postsPerPage) || ($listPosts==true && $currentPost > 0) ) {
		$filename=blogContentPath.$currentPost.'.html';
		if (file_exists($filename)) {
			if($listPosts==true){
				if(($pc) % 3 == 0 && $pc!=0){
					$page=$page+1;
				}
				$pc++;
				echo ('<div class="blogListPostTitle"> <a href="'.blogPath.parameterChar.'page='.($page).'#entry'.$currentPost.'">['.$currentPost.'] - '.file_get_contents(blogContentPath.'dates/'.$currentPost.'.html')."</a> </div><br>");
			}else{
				outputBlogPost($currentPost,$filename);
			}
		}else{
			echo("Error: Missing blog content!");
		}
		$currentPost--;
	}
	echo('<span class="blogFooterSpacer"></span>');
	echo('<div class="blogFooter"><hr>');
	outputPageNav($totalPages,$page,$permaLink);
	echo('</div>');


	// -----------------------------------
	// ---------- Functions --------------
	// -----------------------------------

	function outputPageNav($totalPages,$navPage,$permaLink){
		if($permaLink==0){
			echo('<center><a href="'.blogPath.parameterChar.'page=1" class="blogPageLink"><<</a>|');
			if($totalPages==1){
				echo('&nbsp;<a href="'.blogPath.parameterChar.'page='.($navPage).'" class="blogPageLink"># '.($navPage).'</a>|');
			}else{
				if ($navPage-1>=1){
					if(!($navPage+1<=$totalPages) && $navPage==$totalPages && ($navPage-2)>=1){
						outputPageLink($navPage-2);
					}
					outputPageLink($navPage-1);
				}
				outputPageLink($navPage,true&&$listPosts==false);
				if ($navPage+1<=$totalPages){
					outputPageLink($navPage+1);
					if(!($navPage-1>=1)&&$navPage==1&&($navPage+2<=$totalPages)){
						outputPageLink($navPage+2);
					}
				}
			}
			echo('<a href="'.blogPath.parameterChar.'page='.$totalPages.'" class="blogPageLink">>> </a> </center>');
		}
	}

	function outputBlogPost($currentPost,$filename){
		echo ('<p id="entry'.$currentPost.'"></p><br><hr><div class="blogPostTitle">['.$currentPost.'] - '.file_get_contents(blogContentPath.'dates/'.$currentPost.'.html')."</div>");
		echo ('<a href="'.blogPath.parameterChar.'permalink='.($currentPost).'" class="blogPermanentLink">Permanent link</a>');
		echo("<hr><br>");
		$blogContent = file_get_contents($filename);
		echo ('<div class="blogPostContent">'.$blogContent.'</div>');
	}

	function outputPageLink($i,$bold=false){
		if($bold==false){
			echo('&nbsp;<a href="'.blogPath.parameterChar.'page='.($i).'" class="blogPageLink"># '.($i).'</a>|');	
		}else{
			echo('&nbsp;<a href="'.blogPath.parameterChar.'page='.($i).'" class="blogCurrentPageLink"><b># '.($i).'</b></a>|');
		}
	}
	
?>