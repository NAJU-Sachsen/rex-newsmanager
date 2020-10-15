<?php

define('ROWS_PER_PAGE', 25);
define('PAGER_PARAM', 'offset');

$fragment = new rex_fragment();
$pager = new rex_pager(ROWS_PER_PAGE, PAGER_PARAM);
$sql = rex_sql::factory();

// determine the total number of blog articles
$query = 'SELECT COUNT(*) AS count FROM naju_blog_article';
$sql->setQuery($query);
$n_rows = $sql->getArray()[0]['count'];

// prepare paged query
$query = 'SELECT article_id, article_title, article_updated, article_status, blog_name, article_blog
          FROM naju_blog_article JOIN naju_blog
          ON article_blog = blog_id ';
$query .= 'LIMIT ' . ROWS_PER_PAGE;
$offset = rex_get(PAGER_PARAM, 'int', 0);
if ($offset) {
    $query .= ' OFFSET ' . $offset;
}

// fetch query result and prepare pagination
$sql->setQuery($query);
$res = $sql->getArray();
$pager->setRowCount($n_rows);
$pagination = new rex_fragment();
$pagination->setVar('pager', $pager, false);
$pagination->setVar('urlprovider', rex_url::currentBackendPage());

// prepare page output
$content = '';
$content .= '<table class="table">
                <thead>
                    <tr>
                        <th>Artikel</th>
                        <th>Blog</th>
                        <th>Aktualisiert</th>
                        <th>Status</th>
                        <th>Anpassen</th>
                    </tr>
                </thead>
                <tbody>';

foreach ($res as $article) {
    $content .= '<tr>';
    $content .= '<td>' . rex_escape($article['article_title']) . '</td>';
    $content .= '<td>' . rex_escape($article['blog_name']) . '</td>';
    $content .= '<td>' . rex_escape($article['article_updated']) . '</td>';
    $content .= '<td>';
    switch ($article['article_status']) {
        case 'published':
            $content .= 'online';
            break;
        case 'archived':
            $content .= 'archiviert';
            break;
        case 'draft':
            $content .= 'Entwurf';
            break;
        case 'pending':
            $content .= 'wartet auf Veröffentlichung';
            break;
    }
    $content .= '</td>';
    $content .= '<td></td>'; // TODO: edit options
    $content .= '</tr>';
}

$content .= '</tbody></table>';

// show output
$content .= $pagination->parse('core/navigations/pagination.php');
$fragment->setVar('content', $content);
echo $fragment->parse('core/page/section.php');
