
create table if not exists naju_news_article (
	id int(10) unsigned not null auto_increment,
	title varchar(125) not null,
	subtitle varchar(125) default '',
	group_id int(10) unsigned not null,
	
	published date default null,
	updated date default null,
	status varchar(30) not null,

	image varchar(75) default '',
	article int(10) unsigned,
	intro_text mediumtext default '',

	primary key (id),
	foreign key fk_news_article_id (article) references rex_article(id)
)
