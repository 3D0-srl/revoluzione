<?php
require_once dirname(__FILE__) . '/wlStringHelper.php';

/**
 * WiseLoop Spell Checker class definition<br/>
 * This is an abstract class used to define a general spell checker.<br/>
 * Any child classes must define a dictionary and implement some "must have" methods to retrieve from that dictionary the closest matches for a misspelled word: getClosestMatch and getMatches.<br/>
 * Every spell checker instance will have as inner property a wlString object that will be checked against the defined dictionary.
 * @author WiseLoop
 */
abstract class wlSpellChecker {
    /**
     * @var wlString - the given wlString object to check
     */
    protected $_wlString;

    /**
     * Constructor.<br/>
     * Creates a spell checker object.
     * @param wlString|string $wlString the string or the wlString object to check
     * @return wlSpellChecker
     */
    public function __construct($wlString) {
        if(is_string($wlString)) {
            $wlString = new wlString($wlString);
        }
        $this->_wlString = $wlString;
    }

    /**
     * Spell checks the given string object (sentence).<br/>
     * The method returns an associative array having as keys the misspelled words and as values the corresponding dictionary closest match.<br/>
     * @return array
     */
    public function check() {
        $words = $this->_wlString->words();
        $spellChecked = array();
        foreach($words as $word) {
            $validWord = $this->getClosestMatch($word);
            if(!wlStringHelper::equals($word, $validWord, false)) {
                $spellChecked[$word] = $validWord;
            }
        }
        return $spellChecked;
    }

    /**
     * Returns the closest dictionary word match of a given word.
     * @param string $word the word to check; if not specified the given wlString object will be used
     * @return string the closest word match
     */
    public abstract function getClosestMatch($word = null);

    /**
     * Returns the closest word matches of a word.<br/>
     * The matches will be returned as an array sorted by the matching percent in descending order, containing the words and the corresponding matching percents.<br/>
     * The matches number can be filtered by setting a limit count and a minimal matching percent.<br/>
     * @param int|float $minMatchPercent the minimum matching percent; above this value the corresponding dictionary entry will be included in the resulting array
     * @param int $count the count that limits the length of the resulting array
     * @param string $word the word to check; if not specified the given wlString object will be used
     * @return array the matches array
     */
    public abstract function getMatches($minMatchPercent = null, $count = null, $word = null);
}
