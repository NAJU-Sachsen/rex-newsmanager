
<?php

class rex_api_naju_article_search extends rex_api_function
{

    public function execute()
    {
        $blog_id = rex_request('blog_id', 'string', '0');
        $blog_id = ctype_digit($blog_id) ? intval($blog_id) : 0;
        $article_search = rex_request('query', 'string', '');
        if (rex::getUser()->isAdmin()) {
            $query = 'SELECT article_id, article_title, article_updated, article_published, blog_title
                    FROM naju_blog_article JOIN naju_blog ON article_blog = blog_id';
            if ($blog_id) {
                $params = ['id' => $blog_id, 'query' => $article_search];
                $query .= ' WHERE article_blog = :id AND ';
            } else {
                $params = ['query' => $article_search];
                $query .= ' WHERE ';
            }
            $query .= 'article_title LIKE CONCAT("%", :query, "%") AND article_status = "published"
                ORDER BY blog_title, article_published, article_updated, article_title';
        } else {
            $user_id = rex::getUser()->getId();
            $query = 'SELECT a.article_id, a.article_title, a.article_updated, a.article_published, b.blog_title
                    FROM naju_blog_article a JOIN naju_blog b JOIN naju_group_account ga
                    ON a.article_blog = b.blog_id AND b.blog_group = ga.group_id';
            if ($blog_id) {
                $params = ['id' => $blog_id, 'query' => $article_search, 'user' => $user_id];
                $query .= ' WHERE a.article_blog = :id AND ';
            } else {
                $params = ['query' => $article_search, 'user' => $user_id];
                $query .= ' WHERE ';
            }
            $query .= 'a.article_title LIKE CONCAT("%", :query, "%") AND a.article_status = "published" AND ga.account_id = :user';
            $query .= ' ORDER BY b.blog_title, a.article_published, a.article_updated, a.article_title';
        }

        $sql = rex_sql::factory()->setQuery($query, $params);

        header('HTTP/1.1 200 OK');
        header('Content-Type: application/json; charset=UTF-8');
        $resp = ['sql' => $query, 'result' => $sql->getArray(), 'admin' => rex::getUser()->isAdmin(), 'query' => $article_search, 'blog' => $blog_id];
        echo json_encode($resp);
        exit;
    }

}
