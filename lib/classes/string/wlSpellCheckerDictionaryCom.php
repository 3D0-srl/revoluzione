<?php
require_once dirname(__FILE__) . '/wlSpellCheckerLocal.php';

class wlSpellCheckerDictionaryCom extends wlSpellCheckerLocal {

    /**
     * Constructor.<br/>
     * Creates a dictionary.com spell checker object.
     * @param wlString|string $wlString the string or the wlString object to check
     * @return wlSpellCheckerDictionaryCom
     */
    public function __construct($wlString) {
        parent::__construct($wlString, null);
    }

    /**
     * Returns the closest dictionary word match of a given word by making a cURL request to the dictionary.com service.
     * @param string $word the word to check; if not specified the given wlString object will be used
     * @return string the closest word match
     */
    public function getClosestMatch($word = null) {
        if(!isset($word)) {
            $word = $this->_wlString->get();
        }

        $matches = parent::getMatches(50, 1, $word);
        if(count($matches)) {
            return $matches[0]['word'];
        }

        $url = 'http://dictionary.reference.com/browse/' . $word;
        $contents = wlStringHelper::getUrlContents($url);
        $closest = wlStringHelper::between($contents, '<a ', '</a>', 'Did you mean');
        if(!$closest) {
            $closest = $word;
            if(!wlStringHelper::contains($contents, 'No results found for', false)) {
                parent::addDictionaryEntry($closest);
            }
        }else {
            $closest = '<a ' . $closest . '</a>';
            $closest = trim(strip_tags($closest));
            parent::addDictionaryEntry($closest);
        }
        return $closest;
    }
}
