<?php
  include("microblog/blog.php");
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<?php
			outputMetaData();
		?>
	</head>
	<body>
<?php
  renderBlog();
?>
	</body>
</html>
