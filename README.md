# µblog
Easy to setup, integrate into existing pages and customize micro blog.

Requires no database, only PHP.

## 30 Second Installation
Animated Installation Guide:
https://asciinema.org/a/fk6GCh7YaCtUagTrRy2PgkM3G

wget URL can be found in this repository under "Releases"

## Adding content
Blog posts are stored as html files - you can use whatever mark-up you like.

The first line of the file is interpreted as the post title.

The order of the posts is managed by the file names, that's why all files should be named like the following:
```
{incrementing number}.html
```

## Configuration
The file "blogconfig.php" contains three variables, thats all it takes to configure the your blog.

## Changing the design
In the templates folder you can tweak µblog design with ease.

Terms in dobule curly brackets will be replaced by µblog automagically.

Example of header.html:

```
<div class='blogTitle'>
	<a href="{{linkHome}}" class="linkNoDecoration">A new &#181;blog instance</a>
</div><br>
```

# Live demo
Everybody likes live demos! 
https://zgheb.com/i?v=blog
