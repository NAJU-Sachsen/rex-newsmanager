<?php

class naju_news_article
{
    public const ARTICLE_STATUS = ['published', 'archived', 'draft', 'pending'];

    public static function fromData($data)
    {
        $article = new self;
        foreach ($data as $attr => $val) {
            if (property_exists($article, $attr)) {
                $article->{$attr} = $val;
            }
        }
        return $article;
    }

    private $title;
    private $subtitle;
    private $local_group;
    
    private $published;
    private $updated;
    private $status;

    private $image;
    private $article_id;
    private $intro_text;

}