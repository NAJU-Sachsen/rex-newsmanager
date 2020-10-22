
<?php

class rex_api_naju_article_search extends rex_api_function
{

    public function execute()
    {
        $blog_id = rex_request('blog_id', 'string', '0');
        $blog_id = ctype_digit($blog_id) ? intval($blog_id) : 0;
        $article_search = rex_request('query', 'string', '');
        $query = 'SELECT article_id, article_title, blog_title FROM naju_blog_article JOIN naju_blog ON article_blog = blog_id';
        if ($blog_id) {
            $params = ['id' => $blog_id, 'query' => $article_search];
            $query .= ' WHERE article_blog = :id AND article_title LIKE CONCAT("%", :query), "%") ORDER BY blog_title, article_title';
        } else {
            $params = ['query' => $article_search];
            $query .= ' WHERE article_title LIKE CONCAT("%", :query, "%") ORDER BY blog_title, article_title';
        }
        $sql = rex_sql::factory()->setQuery($query, $params);

        header('HTTP/1.1 200 OK');
        header('Content-Type: application/json; charset=UTF-8');
        $resp = ['query' => $query, 'result' => $sql->getArray()];
        echo json_encode($resp);
        exit;
    }

}
