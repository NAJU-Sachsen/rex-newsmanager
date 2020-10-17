
<?php

$redactor_plugins = <<<EOL
bold,
italic,
underline,
deleted,
sub,sup,
groupheading[3|4|5|6],
grouplink[email|external|internal|media|telephone],
grouplist[unorderedlist|orderedlist|indent|outdent],
blockquote,

alignment,

horizontalrule,
media,
paragraph,
styles[code=Code|kbd=Shortcut|mark=Markiert|samp=Sample|var=Variable]

properties,
undo,redo,
cleaner,
fullscreen,
EOL;

$redactor_settings = <<<EOL
pastePlainText: true
pasteImages: false
EOL;

redactor2::insertProfile('naju_newsmanager', 'Profil für Blog-Texte', '300', '800', 'relative', 0, 0, 0, 1, $redactor_plugins, $redactor_settings);
redactor2::createJavascriptFile();
