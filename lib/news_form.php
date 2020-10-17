
<?php

class naju_news_form extends rex_form {

    private $general_fieldset = '';
    private $intro_character_limit = 200;

    public function setIntroCharacterLimit($limit)
    {
        $this->intro_character_limit = $limit;
    }

    public function setGeneralFieldset($fieldset)
    {
        $this->general_fieldset = $fieldset;
    }


    public function preSave($fieldsetName, $fieldName, $fieldValue, rex_sql $saveSql)
    {
        if ($fieldName === 'article_published' && !$fieldValue) {
            // if the article did not specify a published date, just publish it now
            return rex_sql::datetime();
        } elseif ($fieldName === 'article_intro' && !$fieldValue) {
            // if the article did not specify an intro text, generate one based on the
            // actual content
            $raw_text = $this->getElement($fieldsetName, 'article_content')->getValue();
            $raw_text = strip_tags($raw_text);
            $intro_text = array();
            $n_characters = 0;
            foreach (explode(' ', $raw_text) as $word) {
                if (strlen($word) + $n_characters + 1> $this->intro_character_limit) { // +1 due to whitespace
                    break;
                }
                $intro_text[] = $word;
                $n_characters += strlen($word);
            }
            return implode(' ', $intro_text);
        } elseif ($fieldName === 'article_updated') {
            // set the last update timestamp to now
            return rex_sql::datetime();
        } elseif ($fieldName === 'article_status') {
            // it's a checkbox => value needs to be extracted first
            $fieldValue = explode('|', $fieldValue);
            
            // if it is a draft, we do not need to do further adaptations
            if (in_array('draft', $fieldValue)) {
                return 'draft';
            }

            // if the publish date is in the future, the status is acutally 'pending'
            $publish_date = $this->getElement($this->general_fieldset, 'article_published')->getValue();
            if ($publish_date) {
                $publish_date = date_create_from_format('!Y-m-d', $publish_date);
                $now = new DateTime();
                if ($now < $publish_date) {
                    return 'pending';
                }
            }

            return 'published';
        } else {
            return $fieldValue;
        }
    }

}
