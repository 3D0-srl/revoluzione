<?php
require_once dirname(__FILE__) . '/wlSpellCheckerInline.php';

/**
 * WiseLoop Local Spell Checker class definition<br/>
 * This spell checker class is derived from wlSpellCheckerInline class and loads its dictionary array from an URL given as parameter at contruction time.
 * The dictionary URL can be a remote URL or a local file, and must contain all the valid words, one word per each line.<br/>
 * If no dictionary URL is specified, a default local file will be used.<br/>
 * Any spell checking will be made against that dictionary loaded from that url or local file.
 * @author WiseLoop
 * @see wlSpellCheckerInline
 */
class wlSpellCheckerLocal extends wlSpellCheckerInline {

    /**
     * Default dictionary file name: this file must exists in the same directory as this script file
     */
    const DEFAULT_DICTIONARY_FILE_NAME = 'dictionary-default.txt';

    /**
     * @var string - the dictionary URL: it can be a remote url or a local file; the dictionary file must contain all the valid words one per each line
     */
    private $_dictionaryUrl;

    /**
     * Constructor.<br/>
     * Creates a local spell checker object.
     * @param wlString|string $wlString the string or the wlString object to check
     * @param string $dictionaryUrl the dictionary URL; if not specified, the default dictionary local file will be used
     * @return wlSpellCheckerLocal
     */
    public function __construct($wlString, $dictionaryUrl = null) {
        parent::__construct($wlString, null);
        if(!isset($dictionaryUrl)) {
            $dictionaryUrl = dirname(__FILE__) . '/' . self::DEFAULT_DICTIONARY_FILE_NAME;
        }
        $this->_dictionaryUrl = $dictionaryUrl;
        $content = wlStringHelper::getUrlContents($this->_dictionaryUrl);
        $this->_dictionary = wlStringHelper::words($content);
    }

    /**
     * Adds a valid word to the dictionary file.<br/>
     * Returns true if the word was added successfully or false if the word already existed or the dictionary URL is not writable and so, the new word was not added.
     * @param string $word the word to add
     * @return bool
     */
    public function addDictionaryEntry($word) {
        if(parent::addDictionaryEntry($word)) {
            if(is_writable($this->_dictionaryUrl)) {
                $fh = @fopen($this->_dictionaryUrl, "w");
                if (!$fh) {
                    return;
                }
                fwrite($fh, implode($this->_dictionary, "\r\n"));
                @fclose($fh);
            }
        }
    }
}
