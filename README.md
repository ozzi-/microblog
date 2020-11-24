# µblog
A micro blog that is easy to setup, integrate and customize.

**Requires no database, only a webserver & PHP.**

A blog post:

![screenshot blog post](https://i.imgur.com/2Zd67zR.png)

Filtering by category:

![screenshot blog category list](https://i.imgur.com/wpaxhjr.png)

All categories:

![screenshot blog all categories](https://i.imgur.com/xGRJ8b6.png)


## Live demo
You can find µblog in action on the authors blog:
https://zgheb.com/i?v=blog

## The 30 Second Installation
The following video shows how simple the "installation" is: https://vimeo.com/483067404

Basically clone the git repo into your htdocs folder, assuming you have PHP and Apache running, you are already done.

The file "index.php" is but a skeleton which shows how you could include µblog onto your existing page or might serve as a starting point when building a blog from scratch.

## Adding content
Blog posts are stored as html files in the folder "content".

The following video shows how content is added: https://vimeo.com/483067702

When adding a new blog post, add the required description into db.json as such:
```json
{
	"title": "23.11.2020 - Hello World",
	"id": 1,
}
```
The following example shows all additional information which can be passed to µblog:
```json
{
	"title": "23.11.2020 - Hello World",
	"id": 1,
	"categories": ["microblog","hello world"],
	"og:title": "Hello World",
	"og:description": "This is the first post",
	"og:image": "https://zgheb.com/templates/img/og-71.png",
	"og:type": "article"
}
```

If not defined, og:title will default to the blog title in the first line.

If not defined, og:description will default to the blog content with its HTML tags stripped.

If not defined, og:image will either be the default defined in blogconfig.php or omitted.

If not defined, og:type will be "article".

## Changing the design

µblog allows fully customizable templates, which can be found in the "templates" folder.

Terms in dobule curly brackets will be replaced by µblog automagically.

Let us assume you wish to change the blog header, the according file is called "header.html":
```html
<div class='blogTitle'>
	<a href="{{linkHome}}" class="linkNoDecoration">A new &#181;blog instance</a>
</div>

<div class="d-flex justify-content-between bd-highlight mb-3">
	<a style="{{hasNewerPosts}}" href="{{pageBackHref}}">&#8592; New posts</a>
	<a style="{{showCategoryHref}}" href="{{byCategoryHref}}">By Category</a>
	<a style="{{hasOlderPosts}}" href="{{pageForwardHref}}">Older posts &#8594;</a>
</div>
```
Edit this HTML as you wish.

A video on how to do customization can be found here: https://vimeo.com/483067522

Furthermore, many CSS classes for all elements are defined in blog.css.

## Configuration
The file "blogconfig.php" contains all that is required to configure your µblog instance.
```php
// Posts displayed per page
define ( "blogPostsPerPage", 3 );

// Link to the blog page itself
define ( "blogURL", "index.php");

// The character appended after 'blogContentPath'.
// Use either ? or &.
// Use ? if your path doesn't have a url parameter itself (index.php)
// Use & if your path already has a url paramter (index.php?view=blog)
define ( "blogParameterChar","?");

// Set to 'null' to have no graph image thus the target page picking its own
define ( "blogDefaultOpenGraphImagePath", null);
```
