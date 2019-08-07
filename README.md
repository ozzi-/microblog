# µblog
Easy to setup, integrate into existing pages and customize microblog.

**Requires no database, only a webserver & PHP.**

The overview of all blog posts:
![screenshot overview](https://i.imgur.com/wX2SDOu.png)

A blog post:
![screenshot blog psot](https://i.imgur.com/G0jtHh2.png)


## Live demo
Everybody likes live demos! 
https://zgheb.com/i?v=blog

## The 30 Second Installation
Video Demo of Installation - https://vimeo.com/352504569
ASCII Demo of Installation - https://asciinema.org/a/yNyRUz6pDMRGsOBh2SSCwaDne

## Adding content
Video Demo on how to add content - https://vimeo.com/352504569

Blog posts are stored as html files - you can use whatever mark-up you like.

The first line of the file is interpreted as the post title for the blog itself.
After that a JSON array is followed defining meta data for open graph protocol (http://ogp.me/), this is used for link previews on social media.
Example:
```
Blog Title
{
  "og:title": "Blog Title for Social Media",
  "og:description": "Some elaborate description", 
  "og:image": "https://github.com/fluidicon.png"
}
# Start Content #
The actual blog content that might use <b> html tags </b>.
```
If not defined, og:title will default to the blog title in the first line.

If not defined, og:description will default to the blog content with its HTML tags stripped.

If not defined, og:image will either be the default defined in blogconfig.php or not omitted.

If not defined, og:type will be "article".


The order of the posts is managed by the file names, that's why all files should be named like the following:
```
{incrementing number}.html
```

## Changing the design
Video Demo on how to do customization - https://vimeo.com/352504544

In the templates folder you can tweak µblog layout and design with ease.

Terms in dobule curly brackets will be replaced by µblog automagically.

Example of header.html:

```
<div class='blogTitle'>
	<a href="{{linkHome}}" class="linkNoDecoration">A new &#181;blog instance</a>
</div><br>
```

Furthermore, many CSS classes for all elements are defined in blog.css.

## Configuration
The file "blogconfig.php" contains four variables, thats all it takes to configure the your blog.
