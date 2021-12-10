<?php
/**
 * WiseLoop String Configuration class<br/>
 * This is the default configuration for english language.
 * If your language is different and the alphabet doesn't match the english alphabet, you should update the ALPHABET constant to define your language letters.
 * If necesarry, maybe you will need to update the DIGITS constant also to match your digits used in numbers. 
 * @author WiseLoop
 * @see ALPHABET, DIGITS
 */

if(class_exists('wlStringConfig')) {
    return;
}

class wlStringConfig {
    /**
     * The default bad words file name.
     */
    const BAD_WORDS_FILE_NAME = 'badwords-default.txt';

    /**
     * The alphabet.<br/>
     * Edit this constant in order to define your language alphabet.
     */
    const ALPHABET = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * The digits.<br/>
     * Edit this constant in order to define your language digits.
     */
    const DIGITS = '0123456789';

    /**
     * The empty string.
     */
    const EMPTY_STRING = '';

    /*
     * The whitespace character;
     */
    const WHITE_SPACE = ' ';

    /**
     * Carriage return and line feed.
     */
    const CRLF = "\r\n";

    /**
     * Carriage return.
     */
    const CR = "\r";

    /**
     * Line feed.
     */
    const LF = "\n";

    /**
     * Tab.
     */
    const TAB = "\t";

    /**
     * Null byte.
     */
    const NUL = "\0";
}
