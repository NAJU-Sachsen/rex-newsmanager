<?php

$func = rex_get('func', 'string', '');
if ($func === 'archive') {
    $article_id = rex_get('article_id', 'int', -1);
    $sql = rex_sql::factory();
    $sql->setTable('naju_blog_article')
        ->setWhere('article_id = :id', ['id' => $article_id])
        ->setValue('article_status', 'archived')
        ->update();
} elseif ($func === 'publish') {
    $article_id = rex_get('article_id', 'int', -1);
    $sql = rex_sql::factory();
    $sql->setTable('naju_blog_article')
        ->setWhere('article_id = :id', ['id' => $article_id])
        ->setValue('article_status', 'published')
        ->setDateTimeValue('article_published', time())
        ->update();
}

$fragment = new rex_fragment();
$content = '';

if (rex::getUser()->isAdmin()) {
    $query = 'SELECT article_id, article_title, article_status, article_updated, article_published, blog_title, article_blog
              FROM naju_blog_article JOIN naju_blog
              ON article_blog = blog_id
              ORDER BY article_updated DESC, article_published DESC, blog_title ASC, article_title ASC';
} else {
    $user_id = rex::getUser()->getId();
    $query = 'SELECT a.article_id, a.article_title, a.article_updated, a.article_published, a.article_status, b.blog_title, a.article_blog
              FROM naju_blog_article a JOIN naju_blog b JOIN naju_group_account acc
              ON a.article_blog = b.blog_id AND b.blog_group = acc.group_id
              WHERE acc.account_id = ' . rex_sql::factory()->escape($user_id) . '
              ORDER BY a.article_updated DESC, a.article_published DESC, b.blog_title ASC, a.article_title ASC';
}
$list = rex_list::factory($query, 25, 'articles');

$th_icon = '<a href="' . rex_url::backendController(['page' => 'naju_newsmanager/compose']) . '" title="' . rex_i18n::msg('add') . '">';
$th_icon .= '<i class="rex-icon rex-icon-add-action"></i></a>';
$td_icon = '<i class="rex-icon fa-file-text-o"></i>';
$actions = 'Aktionen';

$list->addColumn($th_icon, $td_icon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
$list->setColumnParams($th_icon, ['page' => 'naju_newsmanager/compose', 'func' => 'edit', 'article_id' => '###article_id###', 'start' => rex_get('start', 'int', 0)]);
$list->addColumn($actions, '', -1, ['<th>###VALUE###</th>', '<td>###VALUE###</td>']);

$list->removeColumn('article_id');
$list->removeColumn('article_blog');

$list->setColumnLabel('article_title', 'Titel');
$list->setColumnLabel('article_status', 'Status');
$list->setColumnLabel('article_updated', 'Aktualisiert');
$list->setColumnLabel('article_published', 'Veröffentlicht');
$list->setColumnLabel('blog_title', 'Blog');

$list->setColumnFormat('article_status', 'custom', function($col_data) {
    $status = $col_data['value'];
    if ($status === 'published') {
        return '<span class="rex-online"><i class="rex-icon rex-icon-online"></i> online</span>';
    } elseif ($status === 'pending') {
        return '<span class="text text-warning"><i class="rex-icon fa-clock-o"></i> geplant</span>';
    } elseif ($status === 'draft') {
        return '<i class="rex-icon fa-pencil"></i> Entwurf';
    } elseif ($status === 'archived') {
        return '<span class="text text-danger"><i class="rex-icon fa-archive"></i> archiviert</span>';
    }
    return $status;
});

$list->setColumnFormat($actions, 'custom', static function($params) {
    $list = $params['list'];
    $content = '';

    $article_id = $list->getValue('article_id');
    $article_status = $list->getValue('article_status');

    $edit_url = rex_url::currentBackendPage(['page' => 'naju_newsmanager/compose', 'func' => 'edit', 'article_id' => $article_id, 'start' => rex_get('start', 'int', 0)]);
    $content .= '<a href="' . $edit_url . '" class="text text-primary"><i class="rex-icon fa-pencil-square-o"></i> bearbeiten</a>' ;
    $content .= '&nbsp;';

    if (in_array($article_status, ['pending', 'draft'])) {
        $content .= '<a href="' . rex_url::currentBackendPage(['func' => 'publish', 'article_id' => $article_id]) . '" class="text text-success"><i class="rex-icon fa-laptop"></i> jetzt veröffentlichen</a>';
    } else {
        $content .= '<span class="text text-muted"><i class="rex-icon fa-laptop"></i> jetzt veröffentlichen</span>';
    }

    $content .= '&nbsp;';

    if ($article_status !== 'archived') {
        $content .= '<a href="' . rex_url::currentBackendPage(['func' => 'archive', 'article_id' => $article_id]) . '"class="text text-danger" onclick="return confirm(\'Wirklich archivieren?\')"><i class="rex-icon fa-archive"></i> archivieren</a>';
    } else {
        $content .= '<span class="text text-muted"><i class="rex-icon fa-archive"></i> archivieren</span>';
    }

    return $content;
});

$list->setColumnSortable('article_updated');
$list->setColumnSortable('article_published');
$list->setColumnSortable('article_status');
$list->setColumnSortable('blog_title');

// generate output
$content = $list->get();
$fragment->setVar('content', $content, false);
echo $fragment->parse('core/page/section.php');
