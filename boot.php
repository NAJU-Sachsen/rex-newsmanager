
<?php

if (rex_addon::get('cronjob')->isAvailable() && !rex::isSafeMode()) {
    rex_cronjob_manager::registerType('naju_article_publish_cronjob');
}

if (rex::isBackend()) {
    rex_extension::register('REX_FORM_SAVED', function ($ep) {
        naju_kvs::invalidate('naju.blogs.*.count');
    });

    if (rex_plugin::get('structure', 'content')->isAvailable()) {
        rex_view::addCssFile($this->getAssetsUrl('style.css'));
    }
}
