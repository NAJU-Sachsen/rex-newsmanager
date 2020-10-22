<?php

class naju_news_manager
{
    public static function fetchArticle($article_id)
    {
        $sql = rex_sql::factory();
        $sql->setTable('naju_blog_article')
            ->setWhere('article_id = :id', ['id' => $article_id])
            ->select('article_title, article_subtitle, article_link, article_link_text, article_image, article_published, article_updated');
        $article = $sql->getArray();
        return $article ? $article[0] : null;
    }

}
