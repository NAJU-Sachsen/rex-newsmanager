
<?php

class naju_article_publish_cronjob extends rex_cronjob {

    public function execute()
    {
        $sql = rex_sql::factory();
        $sql->setTable('naju_blog_article')
            ->setWhere('article_status = :status and article_published <= :date', ['status' => 'pending', 'date' => date('Y-m-d')])
            ->setValue('article_status', 'published')
            ->update();
    }

    public function getTypeName()
    {
        return 'Blogbeiträge veröffentlichen';
    }

    public function getParamFields()
    {
        return [];
    }

}
