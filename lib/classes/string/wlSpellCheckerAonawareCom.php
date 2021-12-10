<?php
require_once dirname(__FILE__) . '/wlSpellCheckerLocal.php';

/**
 * WiseLoop Aonaware.Com Spell Checker class definition<br/>
 * This spell checker class is derived from wlSpellCheckerLocal class and uses as dictionary base the aonaware.com services.<br/>
 * In order to improve speed, save bandwidth and optimize the service, this spell checker caches the results obtained from aonaware to the default local spell checker object:
 * - 1. it checks first if the tested word have a match on the default local spell checker;
 * - 2. if a local match is found, that match is returned without making any subsequent calls to the aonaware service;
 * - 3. if no local match is found, the spell checker makes a request to aonaware.com and retrieves the match from there;
 * - 4. the retrieved match is added to the default local spell checker dictionary, so any future spell checks of the tested word will be retrieved locally (steps 1 and 2).
 *
 * @author WiseLoop
 * @see wlSpellCheckerLocal
 * @note This class can be used as a very good example for how to make your own more sophisticated web service / web page spell checker class.<br/>
 * You only need to identify the url that retrieves a match for a specific tested word and check the resulted response.
 * @warning WiseLoop assumes no responsibility for any abusive use of this class or any user developed similar classes that might lead to the violation of any terms of usage of the accessed web services or pages.<br/>
 * If you decide to use this class or any similar classes, do it with responsibility and make sure that you are allowed use the desired web service or web page by checking its terms of usage.<br/>
 */
class wlSpellCheckerAonawareCom extends wlSpellCheckerLocal {

    /**
     * Constructor.<br/>
     * Creates a aonaware.com spell checker object.
     * @param wlString|string $wlString the string or the wlString object to check
     * @return wlSpellCheckerAonawareCom
     */
    public function __construct($wlString) {
        parent::__construct($wlString, null);
    }

    /**
     * Returns the closest dictionary word match of a given word by making a cURL request to the aonaware.com service.
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

        $url = 'http://services.aonaware.com/DictService/Default.aspx?action=define&dict=*&query=' . $word;
        $contents = wlStringHelper::getUrlContents($url);
        $closest = wlStringHelper::between($contents, 'class="definition">', '</a>', 'Perhaps you meant');
        if(!$closest) {
            $closest = $word;
            if(!wlStringHelper::contains($contents, 'no definitions found for', false)) {
                parent::addDictionaryEntry($closest);
            }
        }else {
            parent::addDictionaryEntry($closest);
        }
        return $closest;
    }
}
