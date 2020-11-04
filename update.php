<?php

// update to 0.2.0
if (rex_version::compare($this->getVersion(), '0.2', '<')) {
    rex_sql_table::get('naju_blog')
        ->ensureColumn(new rex_sql_column('blog_page', 'int(10) unsigned not null'))
        ->ensureForeignKey(new rex_sql_foreign_key('fk_blog_page', 'rex_article', ['id']))
        ->alter();
}
