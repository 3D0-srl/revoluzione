<?php
require_once dirname(__FILE__) . '/wlSpellChecker.php';

/**
 * WiseLoop Inline Spell Checker class definition<br/>
 * This spell checker class is derived from wlSpellChecker abstract class and it defines its dictionary in the form of an array consisting of the valid acceptable words.<br/>
 * Any spell checking will be made against that dictionary given as parameter to the constructor.
 * @author WiseLoop
 * @see wlSpellChecker
 */
class wlSpellCheckerInline extends wlSpellChecker {

    /**
     * @var array - the dictionary containing all the valid words
     */
    protected $_dictionary;

    /**
     * Constructor.<br/>
     * Creates an inline spell checker object.
     * @param wlString|string $wlString the string or the wlString object to check
     * @param array|string $dictionary the dictionary containing all the valid words; if string is given, the words must be separated using one of the following characters: whitespace, comma, semicolon, slash, bar
     * @return wlSpellCheckerInline
     */
    public function __construct($wlString, $dictionary = null) {
        parent::__construct($wlString);
        $this->_wlString = $wlString;
        if(!isset($dictionary)) {
            $dictionary = array();
        } elseif(is_string($dictionary)) {
            $dictionary = str_replace(array(
                ' ',
                ',',
                ', ',
                ';',
                '; ',
                '/',
                '/ ',
                '|',
                '| '
            ), ' ', $dictionary);
            $dictionary = explode(' ', $dictionary);
        }
        $this->_dictionary = $dictionary;
    }

    public function getClosestMatch($word = null) {
        if(!isset($word)) {
            $word = $this->_wlString->get();
        }
        $closest = $word;
        $max = -1;
        foreach ($this->_dictionary as $dictionaryWord) {
            if(wlStringHelper::equals($word, $dictionaryWord, false)) {
                return $word;
            }
            $percent = wlStringHelper::matchPercent($word, $dictionaryWord, true, true, true, true);

            if ($percent >= $max) {
                $closest  = $dictionaryWord;
                $max = $percent;
            }
        }
        return $closest;
    }

    public function getMatches($minMatchPercent = null, $count = null, $word = null) {
        if(!isset($word)) {
            $word = $this->_wlString->get();
        }
        if(!isset($minMatchPercent)) {
            $minMatchPercent = 50;
        }
        $ret = array();
        $words = array();
        $percents = array();
        foreach ($this->_dictionary as $dictionaryWord) {
            if(wlStringHelper::equals($word, $dictionaryWord, false)) {
                $words[] = $dictionaryWord;
                $percents[] = 100;
                $ret[] = array('word' => $dictionaryWord, 'percent' => 100);
            }
            $percent = wlStringHelper::matchPercent($word, $dictionaryWord, true, true, true, true);

            if ($percent >= $minMatchPercent) {
                $words[] = $dictionaryWord;
                $percents[] = $percent;
                $ret[] = array('word' => $dictionaryWord, 'percent' => $percent);
            }
        }

        array_multisort($percents, SORT_NUMERIC | SORT_DESC, $words, SORT_ASC, $ret);

        if(isset($count)) {
            return array_slice($ret, 0, $count, true);
        }else {
            return $ret;
        }
    }

    /**
     * Adds a valid word to the dictionary array.<br/>
     * Returns true if the word was added or false if the word allready existed and it was not added.
     * @param string $word the word to add
     * @return bool
     */
    public function addDictionaryEntry($word) {
        if(!in_array($word, $this->_dictionary)) {
            $this->_dictionary[] = $word;
            return true;
        }
        return false;
    }
}
