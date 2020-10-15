
create table if not exists naju_blog (
	blog_id int(10) unsigned not null auto_increment,
	blog_title varchar(125) not null,
	blog_group int(10) unsigned not null

	primary key (blog_id),
	foreign key fk_blog_group (blog_group) references naju_local_group(group_id)
);

create table if not exists naju_blog_article (
	article_id int(10) unsigned not null auto_increment,
	article_title varchar(255) not null,
	article_subtitle varchar(255) default '',
	article_blog int(10) unsigned not null,

	-- further reading
	article_link int(10) unsigned,
	article_link_text varchar(255) default '',

	-- image
	article_image varchar(255) default '',

	-- content
	article_intro text not null,
	article_content text not null,

	-- status
	article_updated date not null,
	article_published date not null,
	article_status enum('published', 'archived', 'draft', 'pending') default 'published',

	primary key (article_id),
	foreign key fk_blog_rex_article (article_link) references rex_article(id),
	foreign key fk_blog_article (article_blog) references naju_blog(blog_id)
)
