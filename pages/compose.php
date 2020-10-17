<?php

// Fetch available blogs

if (rex::getUser()->isAdmin()) {
    $blogs = rex_sql::factory()->getArray('SELECT blog_id, blog_title, group_name FROM naju_blog JOIN naju_local_group ON blog_group = group_id');
} else {
    $user_id = rex::getUser()->getId();
    $query = 'SELECT b.blog_id, b.blog_title, g.group_name
              FROM naju_blog b JOIN naju_local_group g JOIN naju_group_account a
              ON b.blog_group = g.group_id AND g.group_id = a.group_id
              WHERE a.account_id = :id';
    $blogs = rex_sql::factory()->getArray($query, ['id' => $user_id]);
}

// Setup form

$func = rex_get('func', 'string', 'add');
$article_id = rex_get('article_id', 'int', -1);
$sort = rex_get('sort', 'string', '');
$sorttype = rex_get('sorttype', 'string', '');
$start = rex_get('start', 'int', 0);

if ($func === 'unarchive') {
    $sql = rex_sql::factory();
    $sql->setTable('naju_blog_article')
        ->setWhere('article_id = :id', ['id' => $article_id])
        ->setValue('article_status', 'published')
        ->update();
}

if ($func === 'edit') {
    $general_fieldset = 'Beitrag bearbeiten:';
} else {
    $general_fieldset = 'Neuer Beitrag:';
}
$form = naju_news_form::factory('naju_blog_article', $general_fieldset, 'article_id = ' . rex_get('article_id', 'int', -1));
$form->setGeneralFieldset($general_fieldset);
$form->setApplyUrl(rex_url::backendController(['page' => 'naju_newsmanager/list']));

$form->addParam('article_id', $article_id);
$form->addParam('sort', $sort);
$form->addParam('sorttype', $sorttype);
$form->addParam('start', $start);

if ($func === 'edit') {
    $article = rex_sql::factory()->getArray('SELECT article_status FROM naju_blog_article WHERE article_id = :id',
        ['id' => rex_get('article_id', 'int', -1)])[0];
    if ($article['article_status'] === 'draft') {
        $form->addRawField('<div class="alert alert-info">Dieser Beitrag ist ein Entwurf!</div>');
    } elseif ($article['article_status'] === 'archived') {
        $unarchive_url = rex_url::currentBackendPage(['func' => 'unarchive', 'article_id' => $article_id, 'sort' => $sort,
            'sorttype' => $sorttype, 'start' => $start]);
        $archived_warning = '<div class="alert alert-warning">Dieser Beitrag ist archiviert. ';
        $archived_warning .= ' <a href="' . $unarchive_url . '">Jetzt de-archivieren</a>';
        $archived_warning .= '</div>';
        $form->addRawField($archived_warning);
    }
}

// Article meta data

$field = $form->addSelectField('article_blog');
$field->setLabel('Blog');
$select = $field->getSelect();
foreach ($blogs as $blog) {
    $select->addOption($blog['blog_title'] . " ({$blog['group_name']})", $blog['blog_id']);
}

$field = $form->addInputField('date', 'article_published');
$field->setLabel('Veröffentlichungsdatum');
$field->setNotice('Optional. Wenn gesetzt, wird der Artikel erst ab dem ausgewählten Datum angezeigt');
$field->setAttribute('class', 'form-control');

$field = $form->addTextField('article_title');
$field->setLabel('Titel');
$field->setAttribute('autocomplete', 'off');

$field = $form->addHiddenField('article_status', 'published');
$field = $form->addCheckboxField('article_status');
$field->setLabel('Entwurf');
$field->addOption('Beitrag ist ein Entwurf', 'draft');
$field->setNotice('Entwürfe werden niemals in den Ausgabe-Modulen im Frontend angezeigt');

// Article contents

$form->addFieldset('Inhalt');

$field = $form->addTextField('article_subtitle');
$field->setLabel('Untertitel');
$field->setAttribute('autocomplete', 'off');
$field->setNotice('optional');

$field = $form->addTextAreaField('article_content');
$field->setLabel('Inhalt');
$field->setAttribute('class', 'redactorEditor2-naju_newsmanager');

$field = $form->addTextAreaField('article_intro');
$field->setLabel('Introtext');
$field->setNotice('optional');

$field = $form->addMediaField('article_image');
$field->setLabel('Titelbild');
$field->setNotice('optional');
$field->setDefaultSaveValue(null);
$field->setPreview(1);
$field->setTypes(naju_image::ALLOWED_TYPES);

$field = $form->addLinkmapField('article_link');
$field->setLabel('Weiterführender Artikel');
$field->setNotice('optional');
$field->setDefaultSaveValue(null);

$field = $form->addTextField('article_link_text');
$field->setLabel('Linktext zum weiterführenden Artikel');
$field->setNotice('Optional, nur genutzt falls ein weiterführender Artikel ausgewählt wurde');

$field = $form->addHiddenField('article_updated');
$field->setDefaultSaveValue(time());

// Render form

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', 'Verfassen', false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');
