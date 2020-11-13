
<?php

$fragment = new rex_fragment();
$content = '';

if (rex::getUser()->isAdmin()) {
    $local_groups = rex_sql::factory()->getArray('SELECT group_id, group_name FROM naju_local_group');
} else {
    $user_id = rex::getUser()->getId();
    $query = 'SELECT g.group_id, g.group_name FROM naju_local_group g JOIN naju_group_account a ON a.group_id = g.group_id WHERE a.account_id = :id';
    $local_groups = rex_sql::factory()->getArray($query, ['id' => $user_id]);
}

$func = rex_get('func', 'string', 'list');

if (in_array($func, ['add', 'edit'])) {
    $form = rex_form::factory('naju_blog', 'Blog erstellen', 'blog_id=' . rex_get('blog_id', 'int', -1));
    $form->addParam('blog_id', rex_get('blog_id', 'int', -1));

    $form->addParam('sort', rex_get('sort', 'string', ''));
    $form->addParam('sorttype', rex_get('sorttype', 'string', ''));
    $form->addParam('start', rex_get('start', 'int', 0));

    $field = $form->addTextField('blog_title', null, ['autocomplete' => 'off']);
    $field->setLabel('Titel');
    $field->setAttribute('required', 'true');

    $field = $form->addSelectField('blog_group');
    $field->setLabel('Zugehörige Ortsgruppe');
    $select = $field->getSelect();
    foreach ($local_groups as $group) {
        $select->addOption($group['group_name'], $group['group_id']);
    }
    $field->setAttribute('required', 'true');

    $field = $form->addLinkmapField('blog_page');
    $field->setLabel('Blog Seite');
    $field->setAttribute('required', 'true');
    $field->setNotice('Auf der Seite muss ein Modul <code>[Blog] Beitragsliste</code> vorhanden sein. ' .
        'Der "Weiterlesen" Link für <code>[Blog] Teaser</code> Module verweist dann auf diese Seite und zeigt den entsprechenden Artikel an.');

    $content = $form->get();
} else {
    if (rex::getUser()->isAdmin()) {
        $query = 'SELECT blog_id, blog_title, blog_page, group_name
            FROM naju_blog JOIN naju_local_group ON blog_group = group_id
            ORDER BY group_name, blog_title';
    } else {
        $user_id = rex::getUser()->getId();
        $query = "SELECT b.blog_id, b.blog_title, b.blog_page, g.group_name FROM naju_blog b JOIN naju_local_group g JOIN naju_group_account a
            ON b.blog_group = g.group_id AND g.group_id = a.group_id WHERE a.account_id = $user_id ORDER BY group_name, blog_title";
    }
    $list = rex_list::factory($query, 10, 'blogs');
    $list->addTableAttribute('class', 'table-striped');
    $list->setNoRowsMessage('Bisher wurden keine Blogs erstellt!');

    $th_icon = '<a href="' . $list->getUrl(['func' => 'add']) . '" title="' . rex_i18n::msg('add') . '">';
    $th_icon .= '<i class="rex-icon rex-icon-add-action"></i></a>';

    $td_icon = '<i class="rex-icon fa-pencil-square-o"></i>';

    $actions = 'Aktionen';

    $list->addColumn($th_icon, $td_icon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($th_icon, ['func' => 'edit', 'blog_id' => '###blog_id###', 'start' => rex_get('start', 'int', 0)]);
    $list->removeColumn('blog_id');
    $list->setColumnLabel('blog_title', 'Blog');
    $list->setColumnLabel('group_name', 'Ortsgruppe');
    $list->setColumnLabel('blog_page', 'Artikel');
    $list->addColumn($actions, '', -1, ['<th>###VALUE###</th>', '<td>###VALUE###</td>']);

    $list->setColumnFormat('blog_page', 'custom', function ($params) {
        $page = $params['list']->getValue('blog_page');
        $article = rex_article::get($page);
        $content = '<a href="' . rex_url::backendPage('content/edit', ['mode' => 'edit', 'article_id' => $page]) . '">';
        $content .=     '<i class="rex-icon fa-file-text-o"></i> ';
        $content .=     rex_escape($article->getName());
        $content .= '</a>';
        return $content;
    });

    $list ->setColumnFormat($actions, 'custom', function ($params) {
        $blog = $params['list']->getValue('blog_id');
        $content = '<a href="' . rex_url::currentBackendPage(['func' => 'edit', 'blog_id' => $blog]) . '">';
        $content .= '   <i class="rex-icon fa-pencil-square-o"></i> bearbeiten';
        $content .= '</a>';
        return $content;
    });

    $content = $list->get();
}

$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', 'Blogs', false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
