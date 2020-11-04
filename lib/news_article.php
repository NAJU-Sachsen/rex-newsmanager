<?php

class naju_news_article
{
    public const ARTICLE_STATUS = ['published', 'archived', 'draft', 'pending'];
    public const ARTICLE_INTRO_CONTENT_RATIO = 0.5;

    public static function fromData($data)
    {
        $article = new self;
        foreach ($data as $attr => $val) {
            if (str_starts_with($attr, 'article_')) {
                $attr = substr($attr, strlen('article_'));
            }
            if (property_exists($article, $attr)) {
                $article->{$attr} = $val;
            }
        }
        return $article;
    }

    public static function featuresRealIntro($article)
    {
        $intro = explode(' ', $article['article_intro']);
        $intro_characters = count($intro);
        $text = explode(' ', $article['article_content']);

        $equal_words = 0;
        for ($i = 0; $i < $intro_characters; $i++) {
            if ($intro[$i] == $text[$i]) {
                $equal_words++;
            }
        }

        $actual_ratio = $equal_words / $intro_characters;
        return $actual_ratio < self::ARTICLE_INTRO_CONTENT_RATIO;
    }

    private $title;
    private $subtitle;
    private $local_group;
    
    private $content;
    private $published;
    private $updated;
    private $status;

    private $image;
    private $link;
    private $link_text;
    private $intro;

    public function title()
    {
        return $this->title;
    }

    public function subtitle()
    {
        return $this->subtitle;
    }

    public function localGroup()
    {
        return $this->local_group;
    }

    public function content()
    {
        return $this->content;
    }

    public function publishedDate()
    {
        return $this->published;
    }

    public function updatedDate()
    {
        return $this->updated;
    }

    public function status()
    {
        return $this->status;
    }

    public function image()
    {
        return $this->image;
    }

    public function furtherReading()
    {
        return $this->link;
    }

    public function furtherReadingText()
    {
        return $this->link_text;
    }

    public function introText()
    {
        return $this->intro;
    }

    public function hasRealIntro() {
        return self::featuresRealIntro(['article_content' => $this->content, 'article_intro' => $this->intro]);
    }

}