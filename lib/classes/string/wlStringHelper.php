<?php
require_once dirname(__FILE__) . '/wlStringConfig.php';

if(class_exists('wlStringHelper')) {
    return;
}

/**
 * WiseLoop String Helper class definition<br/>
 * This static class consists of many useful methods for handling strings that are mainly used by the wlString class definition.<br/>
 * This class provides a lots of very useful multi-byte compatible and binary safe methods to handle strings objects such as:
 * - data type validations: empty, whitespace, alpha, numeric, alphanumeric, date, integer, float, IP address, email address, URL, HTML hex color, casing;
 * - string casing: uppercase, lowercase, sentence case, random case, capital case;
 * - string effects: shorten, emphasize, character shuffling, word scrambling, reversing, URL SEO friendly;
 * - string altering and modification: inserting, appending, replacing, padding, trimming, splitting, wrapping;
 * - string generators: password, GUIDs, repeater, number spelling;
 * - string checking: methods like endsWith, startsWith, compare, inRange, contains;
 * - string data extracting: characters, unique characters, words, unique words, numbers, searching;
 * - statistics: string length, character counting, unique character counting, word counting, unique word counting;
 * - HTML string processing: converting to HTML compatible, tag enclosing;
 * - string encoding: base64, rot13, Uu, HTML;
 * - string encrypting: md5, sha1, crc32;
 * - computing: soundex, soundex similarity percent, metaphone, metaphone similarity percent, intersection, levenshtein, levenshtein similarity percent, sounds like similarity;
 * - dictionary based string censoring;
 *
 * @author WiseLoop
 * @see wlString
 */
class wlStringHelper {
    // --------------------------------------------------------------------------------- Binary-safe PHP clone functions
    /**
     * Binary safe, multibyte support, case sensitive PHP strpos equivalent function.<br/>
     * Returns the numeric position of the first occurrence of $needle in the $haystack string.
     * @param string $haystack the string to search in
     * @param string $needle the string to search for
     * @param int $offset specifies from which character in $haystack to start searching
     * @param bool $caseSensitive specifies if the search will be case sensitive or not
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return int
     */
    public static function strpos($haystack, $needle, $offset = null, $caseSensitive = false,  $encoding = null) {
        if(!isset($offset)) {
            $offset = 0;
        }
        if(isset($encoding)) {
            if($caseSensitive) {
                return mb_strpos($haystack, $needle, $offset, $encoding);
            }else {
                return mb_stripos($haystack, $needle, $offset, $encoding);
            }
        }else {
            if($caseSensitive) {
                return strpos($haystack, $needle, $offset);
            }else {
                return stripos($haystack, $needle, $offset);
            }
        }
    }

    /**
     * Binary safe, multibyte support, case sensitive PHP strrpos equivalent function.<br/>
     * Returns the numeric position of the last occurrence of $needle in the $haystack string.
     * @param string $haystack the string to search in
     * @param string $needle the string to search for
     * @param int $offset specifies from which character in $haystack to start searching
     * @param bool $caseSensitive specifies if the search will be case sensitive or not
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return int
     */
    public static function strrpos($haystack, $needle, $offset = null, $caseSensitive = false,  $encoding = null) {
        if(!isset($offset)) {
            $offset = 0;
        }
        if(isset($encoding)) {
            if($caseSensitive) {
                return mb_strrpos($haystack, $needle, $offset, $encoding);
            }else {
                return mb_strripos($haystack, $needle, $offset, $encoding);
            }
        }else {
            if($caseSensitive) {
                return strrpos($haystack, $needle, $offset);
            }else {
                return strripos($haystack, $needle, $offset);
            }
        }
    }

    /**
     * Binary safe, multibyte support, case sensitive PHP substr equivalent function.<br/>
     * Returns the segment of $string starting from index $start parameter having the length specified by $length parameter.
     * @param string $string the input string
     * @param int $start the starting index
     * @param int $length the returned length
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string
     */
    public static function substr($string, $start, $length = null, $encoding = null) {
        if(isset($encoding)) {
            if(!isset($length)) {
                $length = self::strlen($string, $encoding) - $start;
            }
            return mb_substr($string, $start, $length, $encoding);
        }else {
            if(isset($length)) {
                return substr($string, $start, $length);
            }else {
                return substr($string, $start);
            }
        }
    }

    /**
     * Binary safe, multibyte support, case sensitive PHP strlen equivalent function.<br/>
     * Returns the length of a string.
     * @param string $string the input string
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return int
     */
    public static function strlen($string, $encoding = null) {
        if(isset($encoding)) {
            return mb_strlen($string, $encoding);
        }else {
            return strlen($string);
        }
    }

    /**
     * Binary safe, multibyte support, case sensitive PHP strtoupper equivalent function.<br/>
     * Converts a string to UPPERCASE.
     * @param string $string the input string
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string
     */
    public static function strtoupper($string, $encoding = null) {
        if(isset($encoding)) {
            return mb_strtoupper($string, $encoding);
        }else {
            return strtoupper($string);
        }
    }

    /**
     * Binary safe, multibyte support, case sensitive PHP strtolower equivalent function.<br/>
     * Converts a string to lowercase.
     * @param string $string the input string
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string
     */
    public static function strtolower($string, $encoding = null) {
        if(isset($encoding)) {
            return mb_strtolower($string, $encoding);
        }else {
            return strtolower($string);
        }
    }

    /**
     * Binary safe, multibyte support case sensitive PHP ucwords equivalent function.<br/>
     * Converts a string to Title Case.
     * @param string $string the input string
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string
     */
    public static function ucwords($string, $encoding = null) {
        if(isset($encoding)) {
            return mb_convert_case($string, MB_CASE_TITLE, $encoding);
        }else {
            return ucwords($string);
        }
    }

    /**
     * Binary safe, multibyte support, case sensitive PHP strrev equivalent function.<br/>
     * Reverses a string.
     * @param string $string the input string
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string the reversed string
     */
    public static function strrev($string, $encoding = null) {
        if(isset($encoding)) {
            $split = self::split($string, 1, $encoding);
            $len = count($split);
            $ret = array();
            for($i = $len - 1; $i >= 0; $i--) {
                $ret[] = $split[$i];
            }
            return implode('', $ret);
        }else {
            return strrev($string);
        }
    }

    /**
     * Binary safe, multibyte support, case sensitive PHP lcfirst equivalent function.<br/>
     * Returns a string with its first letter converted to lovewcase.
     * @param string $string the input string
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string
     */
    public static function lcfirst($string, $encoding = null) {
        if(isset($encoding)) {
            self::strtolower(self::substr($string, 0, 1, $encoding), $encoding) . self::substr($string, 1, null, $encoding);
        }else {
            return lcfirst($string);
        }
    }

    /**
     * Binary safe, multibyte support, case sensitive PHP ucfirst equivalent function.<br/>
     * Returns a string with its first letter converted to UPPERCASE.
     * @param string $string the input string
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string
     */
    public static function ucfirst($string, $encoding = null) {
        if(isset($encoding)) {
            self::strtoupper(self::substr($string, 0, 1, $encoding), $encoding) . self::substr($string, 1, null, $encoding);
        }else {
            return ucfirst($string);
        }
    }


    // --------------------------------------------------------------------------------- CURL
    /**
     * Prepares a cURL handle for the current url; the returning cURL handle will behave like a real browser.
     * @param string $url the url
     * @param bool $forHeadersOnly specifies if the returning cURL handler is intended to be used only for getting the current url headers
     * @return bool|resource the cURL handle on success, false on errors
     */
    public static function curlInit($url, $forHeadersOnly = false) {
        $userAgent = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (Windows NT 6.0; rv:2.0.1) Gecko/20100101 Firefox/4.0.1');
        $encoding = (isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : 'gzip, deflate');

        $headers = array();
        $headers[] = 'User-Agent: '.$userAgent;
        $headers[] = 'Accept: '.(isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
        $headers[] = 'Accept-Language: '.(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : 'en-us,en;q=0.5');
        $headers[] = 'Accept-Encoding: '.$encoding;
        $headers[] = 'Accept-Charset: '.(isset($_SERVER['HTTP_ACCEPT_CHARSET']) ? $_SERVER['HTTP_ACCEPT_CHARSET'] : 'ISO-8859-1,utf-8;q=0.7,*;q=0.7');
        $headers[] = 'Keep-Alive: '.(isset($_SERVER['HTTP_KEEP_ALIVE']) ? $_SERVER['HTTP_KEEP_ALIVE'] : '115');
        $headers[] = 'Connection: '.(isset($_SERVER['HTTP_CONNECTION']) ? $_SERVER['HTTP_CONNECTION'] : 'keep-alive');
        $headers[] = 'Cache-Control: max-age=0';
        $headers[] = 'Pragma: ';

        $ch = @curl_init($url);
        if(false !== $ch) {
            @curl_setopt($ch, CURLOPT_URL, $url);
            @curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
            @curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            @curl_setopt($ch, CURLOPT_ENCODING, $encoding);
            @curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            @curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            if($forHeadersOnly) {
                @curl_setopt($ch, CURLOPT_NOBODY, true);
                @curl_setopt($ch, CURLOPT_HEADER, true);
            }
        }
        return $ch;
    }

    /**
     * Reads and return the entire content of the current url.
     * @param string $url the url to be read
     * @return string the $_url contents
     */
    public static function getUrlContents($url) {
        $data = null;

        if ($data === null && is_readable($url)) {
            $data = @file_get_contents($url);
            if(false === $data) {
                $data = null;
            }
        }
        if(null === $data)
        {
            $ch = self::curlInit($url);
            if(false !== $ch) {
                $data = @curl_exec($ch);
                if(false === $data) {
                    $data = null;
                }
                @curl_close($ch);
            }
        }
        if(null === $data) {
            $fh = @fopen($url, "r");
            if (!$fh)
                return null;
            $data = "";
            while (!feof($fh))
                $data .= fgets($fh);
            @fclose($fh);
        }

        if(null === $data) {
            $data = '';
        }

        return $data;
    }


    // --------------------------------------------------------------------------------- Validations
    /**
     * Tests if a string is empty.
     * @param string $string the input string
     * @return bool
     */
    public static function isEmpty($string) {
        return (wlStringConfig::EMPTY_STRING === $string || !isset($string) || !$string);
    }

    /**
     * Tests if a string is empty or consists only of whitespaces.
     * @param string $string the input string
     * @return bool
     */
    public static function isWhite($string) {
        return self::isEmpty(trim($string));
    }

    /**
     * Tests if a string is lower cased.
     * @param string $string the input string
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return bool
     */
    public static function isLowerCase($string, $encoding = null) {
        return (0 === self::compare($string, self::strtolower($string, $encoding), true));
    }

    /**
     * Tests if a string is UPPER CASED.
     * @param string $string the input string
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return bool
     */
    public static function isUpperCase($string, $encoding = null) {
        return (0 === self::compare($string, self::strtoupper($string, $encoding), true));
    }

    /**
     * Tests if a string is Sentence Cased.
     * @param string $string the input string
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return bool
     */
    public static function isSentenceCase($string, $encoding = null) {
        return (0 === self::compare($string, self::ucwords($string, $encoding), true));
    }

    /**
     * Tests if a string is Capital cased: if its fist letter is upper cased.
     * @param string $string the input string
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return bool
     */
    public static function isCapitalCase($string, $encoding = null) {
        return (0 === self::compare($string, self::capitalize($string, $encoding), true));
    }

    /**
     * Validates an email address: tests if a string represents a valid email address.
     * @param string $string the input string
     * @return bool
     */
    public static function isEmail($string) {
        return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $string)) ? false : true;
    }

    /**
     * Validates an IP address: tests if a string represents a valid IP address.
     * @param string $string the input string
     * @return bool
     */
    public static function isIp($string) {
        return (!preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $string)) ? false : true;
    }

    /**
     * Validates an URL address: tests if a string represents a valid URL address.
     * @param string $string the input string
     * @return bool
     */
    public static function isUrl($string) {
        return (!preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $string)) ? false : true;
    }

    /**
     * Validates a calendaristic date: tests if a string represents a valid date.
     * @param string $string the input string
     * @return bool
     */
    public static function isDate($string) {
        $date = strtotime($string);
        if(!is_numeric($date)) {
            return false;
        }
        $month = date('m', $date);
        $day = date('d', $date);
        $year = date('Y', $date);
        if (checkdate($month, $day, $year)) {
            return true;
        }
        return false;
    }

    /**
     * Validates a number: tests if a string represents a valid number (integer or float).
     * @param string $string the input string
     * @return bool
     */
    public static function isNumeric($string) {
        return self::isFloat($string);
    }

    /**
     * Validates an integer number: tests if a string represents a valid integer number.
     * @param string $string the input string
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return bool
     */
    public static function isInteger($string, $encoding = null) {
        return (strspn($string, wlStringConfig::DIGITS) == self::strlen($string, $encoding) && strspn($string, '.') != self::strlen($string, $encoding));
    }

    /**
     * Validates a float number: tests if a string represents a valid float number.
     * @param string $string the input string
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return bool
     */
    public static function isFloat($string, $encoding = null) {
        return (strspn($string, wlStringConfig::DIGITS . '.') == self::strlen($string, $encoding));
    }

    /**
     * Validates an alpha string: tests if a string consists only of alpha letters.
     * @param string $string the input string
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return bool
     * @see wlStringConfig::ALPHABET
     */
    public static function isAlpha($string, $encoding = null) {
        return (strspn($string, ' ' . wlStringConfig::ALPHABET) == self::strlen($string, $encoding));
    }

    /**
     * Validates an alphanumeric string: tests if a string consists only of numbers and alpha letters.
     * @param string $string the input string
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return bool
     * @see wlStringConfig::ALPHABET, wlStringConfig::DIGITS
     */
    public static function isAlphaNumeric($string, $encoding = null) {
        return (strspn($string, ' ' . wlStringConfig::ALPHABET . wlStringConfig::DIGITS) == self::strlen($string, $encoding));
    }

    /**
     * Validates a hexadecimal color code: tests if a string is a valid hexadecimal HTML color representation.
     * @param string $string the input string
     * @return bool
     */
    public static function isHexColor($string) {
        return preg_match('/^#[a-f0-9]{6}$/i', $string);
    }

    /**
     * Tests if the input $string is inside the range limited by $str1 and $str2.<br/>
     * If $string, $str1 and $str2 are numerical, a numerical check will be performed.
     * @param string $string the input string
     * @param string $str1 the lower interval limit
     * @param string $str2 the upper interval limit
     * @param bool $closedInterval specifies a closed interval
     * @param bool $caseSensitive specifies if the comparison should be case sensitive or not
     * @return bool
     */
    public static function inRange($string, $str1, $str2, $closedInterval = true, $caseSensitive = false) {
        if(self::isNumeric($string) && self::isNumeric($str1) && self::isNumeric($str2)) {
            $string = floatval($string);
            $str1 = floatval($str1);
            $str2 = floatval($str2);
            if($closedInterval) {
                return ($str1 <= $string  && $string <= $str2);
            }else {
                return ($str1 < $string  && $string < $str2);
            }
        }else {
            if($closedInterval) {
                return (self::compare($string, $str1 , $caseSensitive) >= 0 && self::compare($string, $str2, $caseSensitive) <= 0);
            }else {
                return (self::compare($string, $str1 , $caseSensitive) > 0 && self::compare($string, $str2, $caseSensitive) < 0);
            }
        }
    }

    // --------------------------------------------------------------------------------- Comparisons
    /**
     * Binary safe, multibyte support, case sensitive string comparison.<br/>
     * Compares two strings.
     * @param string $str1 the first input string
     * @param string $str2 the second input string
     * @param bool $caseSensitive specifies if the comparison should be case sensitive or not
     * @param int $len characters number to use in comparison
     * @return int < 0 if str1 is less than str2, > 0 if str1 is greater than str2, and 0 if they are equal
     */
    public static function compare($str1, $str2, $caseSensitive = false, $len = 0) {
        if($len) {
            if($caseSensitive) {
                return strncmp($str1, $str2, $len);
            }else {
                return strncasecmp($str1, $str2, $len);
            }
        }else {
            if($caseSensitive) {
                return strcmp($str1, $str2);
            }else {
                return strcasecmp($str1, $str2);
            }
        }
    }

    /**
     * Tests if the $haystack string contains the $needle string.
     * @param string $haystack the input string
     * @param string $needle the searched string
     * @param bool $caseSensitive specifies if the search should be case sensitive or not
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return bool
     */
    public static function contains($haystack, $needle, $caseSensitive = false, $encoding = null) {
        return (false !== self::strpos($haystack, $needle, null, $caseSensitive, $encoding));
    }

    /**
     * Tests if the $string ends with the $value string.
     * @param string $string the input string
     * @param string $value the searched string
     * @param bool $caseSensitive specifies if the search should be case sensitive or not
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return bool
     */
    public static function endsWith($string, $value, $caseSensitive = false, $encoding = null) {
        return (0 === self::compare(self::substr($string, -1 * self::strlen($value, $encoding)), $value, $caseSensitive, $encoding));
    }

    /**
     * Tests if the $string starts with the $value string.
     * @param string $string the input string
     * @param string $value the searched string
     * @param bool $caseSensitive specifies if the search should be case sensitive or not
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return bool
     */
    public static function startsWith($string, $value, $caseSensitive = false, $encoding = null) {
        return (0 === self::compare(self::substr($string, 0, self::strlen($value, $encoding)), $value, $caseSensitive, $encoding));
    }

    /**
     * Tests if two strings are identical.
     * @param string $str1 the first input string
     * @param string $str2 the second input string
     * @param bool $caseSensitive specifies if the comparison should be case sensitive or not
     * @return bool
     */
    public static function equals($str1, $str2, $caseSensitive = false) {
        return (0 === self::compare($str1, $str2, $caseSensitive));
    }


    // --------------------------------------------------------------------------------- Altering - Basics

    /**
     * Inserts into the main string specified by $string, the $insert string at position specified by $index.
     * @param string $string the main string
     * @param string $insert the string to be inserted
     * @param int $index index position from the main string at wich the insertion should be made
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string the new string with the insertion made
     */
    public static function insert($string, $insert, $index, $encoding = null) {
        return self::substr($string, 0, $index, $encoding) . $insert . self::substr($string, $index, strlen($string) - $index, $encoding);
    }

    /**
     * Removes a portion of the main string specified by $string starting at position $index having a length specified by $length.
     * @param string $string the main input string
     * @param int $index starting index to begin removal
     * @param int $length length of the portion to be removed
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string the new string with the removal made
     */
    public static function remove($string, $index, $length = 0, $encoding = null) {
        if(!$length) {
            return self::substr($string, 0, $index, $encoding);
        }else {
            return self::substr($string, 0, $index, $encoding) . self::substr($string, $index + $length, self::strlen($string, $encoding) - $index - $length, $encoding);
        }
    }

    /**
     * Replaces a specified number of occurrences of the $search string with the $replace string inside the main $string.
     * @param string $string the main input string
     * @param string $search the search string
     * @param string $replace the replacement string
     * @param int $occurrences the number of replacement to be made (if not specified all occurrences will be replaced)
     * @param bool $caseSensitive specifies if the search should be case sensitive or not
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string the new string with the needed replacements made
     */
    public static function substitute($string, $search, $replace, $occurrences = null, $caseSensitive = false, $encoding = null) {
        $str = $string;
        $all = false;
        if(!$occurrences) {
            $occurrences = 999;
            $all = true;
        }
        $offset = 0;
        $searchLen = self::strlen($search, $encoding);
        for($i = 0; $i < $occurrences; $i++) {
            $k1 = self::firstIndex($str, $search, $offset, $caseSensitive, $encoding);
            if(false === $k1) {
                return $str;
            }
            $str = self::substr($str, 0, $k1, $encoding) . $replace . self::substr($str, $k1 + $searchLen, null, $encoding);
            $offset = $k1 + self::strlen($replace, $encoding);
            if($all) {
                $i = 0;
            }
        }
        return $str;
    }

    /**
     * Replace all occurrences of the search string with the replacement string into the main string.
     * @param string $string the main string
     * @param string $search the search string
     * @param string $replace the replacement string
     * @param bool $caseSensitive specifies if the search should be case sensitive or not
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string the new string with the needed replacements made
     */
    public static function replace($string, $search, $replace, $caseSensitive = false, $encoding = null) {
        if($caseSensitive) {
            return str_replace($search, $replace, $string);
        }else {
            return str_ireplace($search, $replace, $string);
        }
    }

    // --------------------------------------------------------------------------------- Altering - Padding

    /**
     * Pads left the input string.
     * @param string $string the input string
     * @param int $finalWidth the total length of the padded string
     * @param string $with the string used to pad (if not specified, whitespace will be used)
     * @return string the padded string
     */
    public static function padLeft($string, $finalWidth, $with = ' ') {
        return str_pad($string, $finalWidth, $with, STR_PAD_LEFT);
    }

    /**
     * Pads right the input string.
     * @param string $string the input string
     * @param int $finalWidth the total length of the padded string
     * @param string $with the string used to pad (if not specified, whitespace will be used)
     * @return string the padded string
     */
    public static function padRight($string, $finalWidth, $with = ' ') {
        return str_pad($string, $finalWidth, $with, STR_PAD_RIGHT);
    }

    /**
     * Pads left and right the input string.
     * @param string $string the input string
     * @param int $finalWidth the total length of the padded string
     * @param string $with the string used to pad (if not specified, whitespace will be used)
     * @return string the padded string
     */
    public static function padBoth($string, $finalWidth, $with = ' ') {
        return str_pad($string, $finalWidth, $with, STR_PAD_BOTH);
    }

    /**
     * Encloses the input string into other strings specified by $str1 (for left side) and $str2 (for right side).
     * @param string $string the input string
     * @param string $str1 the left string
     * @param string $str2 the input string (if not specified, $str2 will equals the reverse of $str1 or the corresponding closing parenthesis if $str1 is an open parenthesis)
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string the enclosed string
     */
    public static function enclose($string, $str1, $str2 = null, $encoding = null) {
        if(!isset($str2)) {
            switch($str1) {
                case '(': $str2 = ')'; break;
                case '[': $str2 = ']'; break;
                case '{': $str2 = '}'; break;
                case '<': $str2 = '>'; break;
                default: $str2 = self::strrev($str1, $encoding);
            }
        }
        return $str1 . $string . $str2;
    }

    // --------------------------------------------------------------------------------- Altering - Splitting
    /**
     * Wordwraps the input string by specified number of columns and returns an array consisting of the text lines after wrap.
     * @param string $string the input string
     * @param int $columns
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return array the text lines after wrapping
     */
    public static function wordWrap($string, $columns = 64, $encoding = null) {
        if(isset($encoding)) {
            $split = mb_split('([\s\/\(\)\[\]{}<>\r\n"])', $string);
        }else {
            $split = preg_split('/([\s\/\(\)\[\]{}<>\r\n"])/', $string, null, PREG_SPLIT_NO_EMPTY);
        }
        $words = array();
        foreach($split as $word) {
            if(!self::isWhite($word)) {
                $words[] = $word . ' ';
            }
        }

        $ret = array();
        $line = '';
        foreach($words as $word) {
            $wordLen = self::strlen($word, $encoding);
            $lineLen = self::strlen($line, $encoding);
            if($lineLen + $wordLen <= $columns + 1) {
                $line .= $word;
            }else {
                $ret[] = self::substr($line, 0, $lineLen - 1, $encoding);
                $line = $word;
            }
        }
        $ret[] = $line;
        return $ret;
    }

    /**
     * Splits the input string into smaller chunks of a specified length divided by a specified separator.
     * @param string $string the input string
     * @param int $chunkLen the chunk length
     * @param string $separator the chunk separator
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string the chunks-splitted string
     */
    public static function chunkSplit($string, $chunkLen = null, $separator = null, $encoding) {
        if(!isset($chunkLen)) {
            $chunkLen = 76;
        }
        if(!isset($separator)) {
            $separator = wlStringConfig::CRLF;
        }
        if(isset($encoding)) {
            $split = self::split($string, 1, $encoding);
            $n = -1;
            $ret = '';
            foreach ($split as $char) {
                $n++;
                if ($n < $chunkLen) {
                    $ret .= $char;
                }elseif ($n == $chunkLen) {
                    $ret .= $char . $separator;
                    $n = 0;
                }
            }
            return $ret;
        }else {
            return chunk_split($string, $chunkLen, $separator);
        }
    }

    /**
     * Splits the input string into smaller chunks of a specified length and returns the chunks into an array.
     * @param string $string the input string
     * @param int $chunkLen the chunk length
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return array the chunks array
     */
    public static function split($string, $chunkLen = null, $encoding = null) {
        if(!isset($chunkLen)) {
            $chunkLen = 1;
        }
        if(isset($encoding)) {
            $split = array();
            for($i = 0; $i < mb_strlen($string); $i+=$chunkLen) {
                $chr = mb_substr($string, $i, $chunkLen, $encoding);
                if($chr) {
                    $split[] = mb_substr($string, $i, $chunkLen, $encoding);
                }
            }
            return $split;
        }else {
            $split = str_split($string, $chunkLen);
        }
        return $split;
    }

    // --------------------------------------------------------------------------------- Altering - Trimming

    /**
     * Strip characters from the beginning of a string.
     * @param string $string the input string
     * @param string $charList the list of characters to be trimmed (if not specified, a default list will be used consisting of: whitespace, tab, new line, carriage return, null byte, vertical tab)
     * @param bool $caseSensitive specifies if the char comparison with of char list to be stripped should be case sensitive or not
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string the trimmed string
     */
    public static function trimStart($string, $charList = null, $caseSensitive = false, $encoding = null) {
        if(!isset($charList)) {
            return ltrim($string);
        }
        if(!$caseSensitive) {
            $charList = self::strtolower($charList, $encoding) . self::strtoupper($charList, $encoding);
        }

        return ltrim($string, $charList);
    }

    /**
     * Strip characters from the end of a string.
     * @param string $string the input string
     * @param string $charList the list of characters to be trimmed (if not specified, a default list will be used consisting of: whitespace, tab, new line, carriage return, null byte, vertical tab)
     * @param bool $caseSensitive specifies if the char comparison with of char list to be stripped should be case sensitive or not
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string the trimmed string
     */
    public static function trimEnd($string, $charList = null, $caseSensitive = false, $encoding = null) {
        if(!isset($charList)) {
            return rtrim($string);
        }
        if(!$caseSensitive) {
            $charList = self::strtolower($charList, $encoding) . self::strtoupper($charList, $encoding);
        }

        return rtrim($string, $charList);
    }

    /**
     * Strip characters from the beginning and end of a string.
     * @param string $string the input string
     * @param string $charList the list of characters to be trimmed (if not specified, a default list will be used consisting of: whitespace, tab, new line, carriage return, null byte, vertical tab)
     * @param bool $caseSensitive specifies if the char comparison with of char list to be stripped should be case sensitive or not
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string the trimmed string
     */
    public static function trimBoth($string, $charList = null, $caseSensitive = false, $encoding = null) {
        if(!isset($charList)) {
            return ltrim($string);
        }
        if(!$caseSensitive) {
            $charList = self::strtolower($charList, $encoding) . self::strtoupper($charList, $encoding);
        }

        return trim($string, $charList);
    }

    /**
     * Strip all non alpha numeric characters from a string, leaving only letters and numbers.
     * @param string $string the input string
     * @return string the stripped string
     * @see wlStringConfig::ALPHABET, wlStringConfig::DIGITS
     */
    public static function removeNonAlphaNumeric($string) {
        return preg_replace('/[^' . wlStringConfig::ALPHABET . wlStringConfig::DIGITS . '\s]/', '', $string);
    }

    /**
     * Strip all non alpha characters from a string, leaving only letters.
     * @param string $string the input string
     * @return string the stripped string
     * @see wlStringConfig::ALPHABET
     */
    public static function removeNonAlpha($string) {
        return preg_replace('/[^' . wlStringConfig::ALPHABET . '\s]/', '', $string);
    }

    /**
     * Strip all non numeric characters from a string, leaving only digits.
     * @param string $string the input string
     * @return string the stripped string
     * @see wlStringConfig::DIGITS
     */
    public static function removeNonNumeric($string) {
        return preg_replace('/[^' . wlStringConfig::DIGITS . ']/', '', $string);
    }

    // --------------------------------------------------------------------------------- Altering - Casing
    /**
     * Converts a string to Capital case: its fist letter will be upper cased.
     * @param string $string the input string
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string
     */
    public static function capitalize($string, $encoding = null) {
        return self::strtoupper(self::substr($string, 0, 1, $encoding), $encoding) . self::strtolower(self::substr($string, 1, null, $encoding), $encoding);
    }

    /**
     * Randomizes the letter cases of a string: its all letters will have random case.
     * @param string $string the input string
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string
     */
    public static function randomCase($string, $encoding = null) {
        $ret = '';
        $len = self::strlen($string, $encoding);
        for($i = 0; $i < $len; $i++) {
            $rnd = rand(0, 1);
            $char = self::substr($string, $i, 1, $encoding);
            if($rnd) {
                $ret .= self::strtoupper($char, $encoding);
            }else {
                $ret .= self::strtolower($char, $encoding);
            }
        }
        return $ret;
    }

    // ---------------------------------------------------------------------------------Altering - Generators

    /**
     * Generates a general unique identifier.
     * @return string the generated general unique identifier
     */
    public static function generateGUID() {
        return md5(uniqid('wl', true));
    }

    /**
     * Generates a random password of a specified length.
     * @param int $length the password length
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string the generated password
     */
    public static function generatePassword($length = 8, $encoding = null) {
        $alpha = wlStringConfig::ALPHABET . wlStringConfig::DIGITS;
        $alphas = $alpha;
        while(self::strlen($alphas, $encoding) < $length) {
            $alphas .= $alpha;
        }
        return substr(self::shuffle($alphas, false, $encoding), 0, $length);
    }

    /**
     * Converts a number to its literal english representation: spells (english only) a number.
     * @param string $string the input string that represents a number (digits only)
     * @return string
     */
    public static function spellNumber($string) {
        $ilion = array(
            '',
            'thousand',
            'million',
            'billion',
            'trillion',
            'quadrillion',
            'quintillion',
            'sextillion',
            'septillion'
        );

        $een = array(
            '',
            'one',
            'two',
            'three',
            'four',
            'five',
            'six',
            'seven',
            'eight',
            'nine',
            'ten',
            'eleven',
            'twelve',
            'thirteen',
            'fourteen',
            'fifteen',
            'sixteen',
            'seventeen',
            'eighteen',
            'nineteen'
        );

        $ty = array(
            '',
            '',
            'twenty',
            'thirty',
            'fourty',
            'fifty',
            'sixty',
            'seventy',
            'eighty',
            'ninety'
        );

        $hun = 'hundred';
        $end = array();
        $string = strrev($string);
        $final = array();
        for($i=0; $i<strlen($string); $i+=3){
            $end[$i] = strrev(substr($string, $i, 3));
        }
        $end = array_reverse($end);
        for($i=0; $i<sizeof($end); $i++){
            $len = strlen($end[$i]);
            $temp = $end[$i];
            if($len == 3){
                $final[] = $temp{0} != '0' ? $een[$end[$i]{0}] . ' ' . $hun : $een[$end[$i]{0}];
                $end[$i] = substr($end[$i], 1, 2);
            }
            if($len > 1){
                $final[] = array_key_exists($end[$i], $een) ? $een[$end[$i]] : $ty[$end[$i]{0}] . ' ' . $een[$end[$i]{1}];
            }else{
                $final[] = $een[$end[$i]{0}];
            }
            $final[] = $temp != '000' ? $ilion[sizeof($end) - $i - 1] : '';
        }
        return str_replace('  ', ' ', implode(' ',$final));
    }

    /**
     * Spells all the numbers founded into the input string starting from a specified index.
     * @param string $string the input string
     * @param int $startIndex specifies from which number index the spelling should start; meaning that all precedent numbers will be skipped
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string
     */
    public static function spellNumbers($string, $startIndex = 0, $encoding = null) {
        $numbers = self::extractNumbers($string);
        $index = 0;
        foreach($numbers as $num) {
            $index++;
            if($index > $startIndex) {
                $spelled = self::spellNumber($num);
                $string = self::substitute($string, $num, $spelled, 1, false, $encoding);
            }
        }
        return $string;
    }

    /**
     * Converts a number that represents a byte size to a nicer (shorter) byte size format. (ie. 1024 = 1 KB)
     * @param string $string the byte size number (digits only)
     * @return string
     */
    public static function byteSizeNumber($string) {
        if(!is_numeric($string)) {
            return $string;
        }elseif ($string >= 1099511627776) {
            return number_format(($string / 1099511627776) ,2) . ' TB';
        }elseif ($string >= 1073741824) {
            return number_format(($string / 1073741824) ,2) . ' GB';
        }elseif ($string >= 1048576) {
            return number_format(($string / 1048576) ,2) . ' MB';
        }elseif ($string >= 1024) {
            return number_format(($string / 1024) ,2) . ' KB';
        }elseif ($string > 0) {
            return $string . " b";
        }elseif ($string <= 0) {
            return "0 b";
        }
        return $string;
    }

    /**
     * Converts to their nicer byte size representation all the numbers founded into the input string starting from a specified index.
     * @param string $string the input string
     * @param int $startIndex specifies from which number index the conversion should start; meaning that all precedent numbers will be skipped
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string
     */
    public static function byteSizeNumbers($string, $startIndex = 0, $encoding = null) {
        $numbers = self::extractNumbers($string);
        $index = 0;
        foreach($numbers as $num) {
            $index++;
            if($index > $startIndex) {
                $bs = self::byteSizeNumber($num);
                $string = self::substitute($string, $num, $bs, 1, false, $encoding);
            }
        }
        return $string;
    }
    
    // --------------------------------------------------------------------------------- Altering - Special
    /**
     * Applies censor to a full string, by replacing characters with other character.
     * @param string $string the input string
     * @param bool $keepMargins specifies if the first and the last string letter will remain unchanged (will not be replaced with $replaceChar)
     * @param string $replaceChar the replacement character (if not specified, 'x' will be used)
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string the censored string
     */
    public static function censor($string, $keepMargins = true, $replaceChar = null, $encoding = null) {
        if(!isset($replaceChar)) {
            $replaceChar = 'x';
        }
        $replaceChar = substr($replaceChar, 0, 1);

        if(!$keepMargins) {
            return str_repeat($replaceChar, self::strlen($string, $encoding));
        }

        if(self::strlen($string, $encoding) <= 2) {
            return $string;
        }

        return self::substr($string, 0, 1, $encoding) . str_repeat($replaceChar, self::strlen($string, $encoding) - 2) . self::substr($string, -1, 1, $encoding);
    }

    /**
     * Applies censor to a sentence (string) by checking its words against a list of bad words submitted. If no bad words list is specified it will be loaded from a local file.
     * @param string $string the sentence
     * @param array $badWords the bad words list
     * @param bool $keepMargins specifies if the first and the last letter of a censored word will remain unchanged (will not be replaced with $replaceChar)
     * @param string $replaceChar the replacement character (if not specified, 'x' will be used)
     * @param bool|string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string the censored sentence
     * @see wlStringConfig::BAD_WORDS_FILE_NAME
     */
    public static function censorWords($string, $badWords = null, $keepMargins = true, $replaceChar = null, $encoding = false) {
        if(!isset($badWords)) {
            $badWords = self::loadLocalBadWords();
            if(!isset($badWords)) {
                return $string;
            }
        }
        if(!is_array($badWords)) {
            $badWords = array($badWords);
        }

        $words = self::words($string, $encoding);
        foreach($words as $word) {
            foreach($badWords as $badWord) {
                if(0 === self::compare($word, $badWord, false)) {
                    $censored = self::censor($word, $keepMargins, $replaceChar, $encoding);
                    $string = self::substitute($string, $word, $censored, null, false, $encoding);
                }
            }
        }
        return $string;
    }

    /**
     * Loads a bad words list from a file (or url).
     * @param string $url the local filename or url that contains the bad words (one bad word per line); if not specified a default local file will be used
     * @return array the bad words array
     * @see BAD_WORDS_FILE_NAME
     */
    public static function loadLocalBadWords($url = null) {
        if(!isset($url)) {
            $url = dirname(__FILE__) . '/' . wlStringConfig::BAD_WORDS_FILE_NAME;
        }
        $content = self::getUrlContents($url);
        if(!$content) {
            return null;
        }
        return self::words($content);
    }

    /**
     * Shortens a string.
     * @param string $string the input string
     * @param int $length the final length of the string
     * @param float $position the position (in percents) where the glue string should be placed (ex. 0 = maximum left, 0.25 = quite left, 0.5 = middle, 1 = maximum right etc.)
     * @param string $glue the glue string
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string the short string
     */
    public static function shorten($string, $length, $position = 0.5, $glue = null, $encoding = null) {
        if($position > 1) {
            $position = $position / 100;
        }
        if(self::strlen($string, $encoding) > $length){
            if(!isset($glue)) {
                $glue = ' ... ';
            }
            $l1 = $length * $position;
            $l2 = $length * (1 - $position);
            $s1 = self::substr($string, 0, $l1, $encoding);
            $s2 = $l2 !== 0 ? self::substr($string, -1 * $l2, null, $encoding) : '';
            $string = $s1 . $glue . $s2;
        }
        return $string;
    }

    /**
     * Makes the input string seo friendly by replacing invalid url characters with '-'.
     * @param string $string the input string
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string the seo friendly string
     */
    public static function seoFriendly($string, $encoding = null) {
        $string = preg_replace('~[^\\pL0-9_]+~u', '-', $string);
        $string = trim($string, "-");
        $string = iconv($encoding, "us-ascii//TRANSLIT", $string);
        $string = self::strtolower($string, $encoding);
        $string = preg_replace('~[^-a-z0-9_]+~', '', $string);
        $string = preg_replace('/[\t\n\r\s]+/', ' ', $string);
        $string = preg_replace('/[ -]+/', '-', $string);
        return $string;
    }

    /**
     * Emphasizes a string by enclosing search phrases into specified tags. The corresponding closing tags will be automatically calculated.
     * @param string $string the input string
     * @param array|string $phrases search phrases to be emphasized
     * @param bool $caseSensitive specifies if the search for phrases should be case sensitive or not
     * @param string $tag the emphasising tag for word matches
     * @param string $tagFull the emphasising tag for phrase matches
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string the emphasized string
     */
    public static function emphasize($string, $phrases, $caseSensitive = true, $tag = null, $tagFull = null, $encoding = null) {
        if(!is_array($phrases)) {
            $phrases = array($phrases);
        }

        if(!isset($tag)) {
            $tag = '<em>';
        }
        if(!isset($tagFull)) {
            $tagFull = $tag;
        }

        $tagEnd = str_replace(substr($tag, 0, 1), substr($tag, 0, 1) . '/', $tag);
        $fullTagEnd = str_replace(substr($tagFull, 0, 1), substr($tagFull, 0, 1) . '/', $tagFull);

        foreach($phrases as $phrase) {
            $phrase = preg_replace('/\s\s+/', ' ', strip_tags(trim($phrase)));

            $var = '';
            $words = explode(' ', $phrase);
            foreach($words as $word) {
                $replacement = $tag.$word.$tagEnd;
                $var .= $replacement . ' ';
                $string = self::substitute($string, $word, $replacement, null, $caseSensitive, $encoding);
            }
            $string = self::substitute($string, self::trimEnd($var, null, false, $encoding), $tagFull.$phrase.$fullTagEnd, null, $caseSensitive, $encoding);
        }
        return $string;
    }

    /**
     * Shuffles a string by randomizing its the character orders.
     * @param string $string the input string
     * @param bool $keepMargins specifies if the first and the last letter orders should remain unchanged
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string the shuffled string
     */
    public static function shuffle($string, $keepMargins = false, $encoding = null){
        if($keepMargins && self::strlen($string, $encoding) <= 3) {
            return $string;
        }
        if(!isset($encoding) && !$keepMargins) {
            return str_shuffle($string);
        }
        $split = self::split($string, 1, $encoding);
        $len = count($split) - 1;
        $start = 0;
        if($keepMargins) {
            $start = 1;
            $len -= 1;
        }
        for($i = $start; $i <= $len; $i++) {
            $k1 = rand($start, $len);
            $k2 = rand($start, $len);
            while($k2 == $k1) {
                $k2 = rand($start, $len);
            }
            $tmp = $split[$k1];
            $split[$k1] = $split[$k2];
            $split[$k2] = $tmp;
        }
        return implode('', $split);
    }

    /**
     * Scrambles a sentence (string) by shuffling its words.
     * @param string $string the sentence
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string the scrambled sentence
     */
    public static function scramble($string, $encoding = null) {
        $words = self::words($string, $encoding);
        foreach($words as $word) {
            $scrambled = self::shuffle($word, true, $encoding);
            $string = self::substitute($string, $word, $scrambled, 1, true, $encoding);
        }
        return $string;        
    }

    // --------------------------------------------------------------------------------- Extracting - String operations
    /**
     * Returns a substring between two substrings.
     * @param string $haystack the input string
     * @param string $strStart the starting substring
     * @param string $strEnd the end substring
     * @param string $afterStr if specified, the searching will start from this substring
     * @param bool $caseSensitive specifies if the char comparison with of char list to be stripped should be case sensitive or not
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string the substring between $strStart and $strEnd
     */
    public static function between($haystack, $strStart, $strEnd = null, $afterStr = null, $caseSensitive = false, $encoding = null) {
        $ret = wlStringConfig::EMPTY_STRING;

        $k1 = null;
        $k2 = null;
        $from = null;
        $sir2 = wlStringConfig::EMPTY_STRING;

        $from = 0;

        if ($afterStr) {
            $k1 = self::strpos($haystack, $afterStr, null, $caseSensitive, $encoding);
            if ($k1 !== false) {
                $from = $k1 + self::strlen($afterStr, $encoding);
            }else  {
                return wlStringConfig::EMPTY_STRING;
            }
        }

        $k1 = self::strpos($haystack, $strStart, $from, $caseSensitive, $encoding);
        if($k1 === false) {
            return wlStringConfig::EMPTY_STRING;
        }

        if ($k1 !== false) {
            $sir2 = self::substr($haystack, $k1 + self::strlen($strStart, $encoding), self::strlen($haystack, $encoding) - $k1 - self::strlen($strStart, $encoding), $encoding);
            if ($strEnd) {
                $k2 = self::strpos($sir2, $strEnd, null, $caseSensitive, $encoding);
            }else {
                $k2 = self::strlen($sir2, $encoding) + 1;
            }
            if ($k2 !== false) {
                $ret = self::substr($sir2, 0, $k2, $encoding);
            }
        }
        return $ret;
    }

    /**
     * Intersects two strings. The method will return the common portion of the two input strings.
     * @param string $str1 the first input string
     * @param string $str2 the second input string
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string the common portion of the two input strings
     */
    public static function intersect($str1, $str2, $encoding = null) {
        $str1 = self::trimBoth($str1, null, false, $encoding);
        $str2 = self::trimBoth($str2, null, false, $encoding);

        $len1 = self::strlen($str1, $encoding);
        $len2 = self::strlen($str2, $encoding);

        if ($len1 > $len2) {
            $tmp = $str1;
            $str1 = $str2;
            $str2 = $tmp;

            $tmp = $len1;
            $len1 = $len2;
            $len2 = $tmp;
        }

        for ($l = $len1; $l >= 1; --$l) {
            for ($i = 0, $ix = $len1 - $l; $i <= $ix; ++$i) {
                $sub = self::substr($str1, $i, $l, $encoding);
                $k = self::strpos($str2, $sub, null, false, $encoding);
                if ($k !== false) {
                    return self::trimBoth(self::substr($str2, $k, self::strlen($sub, $encoding), $encoding), null, false, $encoding);
                }
            }
        }

        return wlStringConfig::EMPTY_STRING;
    }

    /**
     * Returns the common characters founded inside the two input strings.
     * @param string $str1 the first input string
     * @param string $str2 the second input stirng
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string a string consisting of the common characters from the input strings
     */
    public static function intersectChars($str1, $str2, $encoding = null) {
        $a1 = self::chars($str1, $encoding);
        $a2 = self::chars($str2, $encoding);
        return implode(array_intersect($a1, $a2), '');
    }

    /**
     * Returns the common words founded inside the two input strings.
     * @param string $str1 the first input string
     * @param string $str2 the second input string
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string a string consisting of the common words from the input strings
     */
    public static function intersectWords($str1, $str2, $encoding = null) {
        $a1 = self::words($str1, $encoding);
        $a2 = self::words($str2, $encoding);
        return implode(array_intersect($a1, $a2), ' ');
    }

    // --------------------------------------------------------------------------------- Extracting - Numbers
    /**
     * Extracts all the numbers founded inside a string.
     * @param string $string the input string
     * @return array the extracted numbers
     */
    public static function extractNumbers($string) {
        preg_match_all('/([\d]+).([\d]+)/', $string, $match);
        return $match[0];
    }

    // --- Extracting - Character operations
    /**
     * Extracts a character at a specified index from a string.
     * @param string $string the input string
     * @param int $index character index to extract
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string the extracted character
     */
    public static function char($string, $index, $encoding = null) {
        $split = self::split($string, 1, $encoding);
        return $split[$index];
    }

    /**
     * Splits a string into its characters.
     * @param string $string the input string
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return array the characters array
     */
    public static function chars($string, $encoding = null) {
        $split = self::split($string, 1, $encoding);
        return $split;
    }

    /**
     * Extracts and counts each unique character occurrence of a string.
     * @param string $string
     * @param bool $caseSensitive specifies if character extracting and counting should be case sensitive or not
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return array the unique characters array having as keys the characters and as values the number of occurrences
     */
    public static function uniqueChars($string, $caseSensitive = false, $encoding = null) {
        $split = self::split($string, 1, $encoding);
        $ret = array();
        foreach($split as $char) {
            if(!$caseSensitive) {
                $char = self::strtolower($char, $encoding);
            }
            if(array_key_exists($char, $ret)) {
                $ret[$char]++;
            }else {
                $ret[$char] = 1;
            }
        }
        return $ret;
    }

    /**
     * Returns the ord of each character inside the input string.
     * @param string $string the input string
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return array
     */
    public static function ord($string, $encoding = null) {
        $ret = array();
        $split = self::chars($string, $encoding);
        foreach($split as $char) {
            $ret[] = ord($char);
        }
        if(count($ret) === 1) {
            return $ret[0];
        }
        return $ret;
    }

    // --------------------------------------------------------------------------------- Extracting - Word operations
    /**
     * Extracts a word at a specified index from a string.
     * @param string $string the input string
     * @param int $index word index to extract
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string the extracted word
     */
    public static function word($string, $index, $encoding = null) {
        $words = self::words($string, $encoding);
        return $words[$index];
    }

    /**
     * Extracts all the words founded inside a string.
     * @param string $string the input string
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return array the extracted words array
     */
    public static function words($string, $encoding = null) {
        if(!isset($string)) {
            return array();
        }
        if(isset($encoding)) {
            $split = mb_split('([\s\-_,:;?!\/\(\)\[\]{}<>\r\n"]|(?<!\d)\.(?!\d))', $string);
        }else {
            $split = preg_split('/([\s\-_,:;?!\/\(\)\[\]{}<>\r\n"]|(?<!\d)\.(?!\d))/', $string, null, PREG_SPLIT_NO_EMPTY);
        }
        $ret = array();
        foreach($split as $word) {
            if(!self::isWhite($word)) {
                $ret[] = $word;
            }
        }
        return $ret;
    }

    /**
     * Extracts and counts each unique word occurrence from a sentence (string).
     * @param string $string the input string (sentence)
     * @param bool $caseSensitive specifies if word extracting and counting should be case sensitive or not
     * @param bool|string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return array the unique words array having as keys the words and as values the number of occurrences
     */
    public static function uniqueWords($string, $caseSensitive = false, $encoding = false) {
        $words = self::words($string, $encoding);
        $ret = array();
        foreach($words as $word) {
            if(!$caseSensitive) {
                $word = self::strtolower($word, $encoding);
            }
            if(array_key_exists($word, $ret)) {
                $ret[$word]++;
            }else {
                $ret[$word] = 1;
            }
        }
        return $ret;
    }

    // --------------------------------------------------------------------------------- Extracting - Searching
    /**
     * Returns the numeric position of the first occurrence of $needle in the $haystack string.
     * @param string $haystack the input string to search in
     * @param string $needle the string to search for
     * @param int $offset specifies from which character offset index from the $haystack string to start searching
     * @param bool $caseSensitive specifies if the search will be case sensitive or not
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return int
     */
    public static function firstIndex($haystack, $needle, $offset = 0, $caseSensitive = false, $encoding = null) {
        return self::strpos($haystack, $needle, $offset, $caseSensitive, $encoding);
    }

    /**
     * Returns the numeric position of the last occurrence of $needle in the $haystack string.
     * @param string $haystack the input string to search in
     * @param string $needle the string to search for
     * @param int $offset specifies from which character offset index from the $haystack string to start searching
     * @param bool $caseSensitive specifies if the search will be case sensitive or not
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return int
     */
    public static function lastIndex($haystack, $needle, $offset = 0, $caseSensitive = false, $encoding = null) {
        return self::strrpos($haystack, $needle, $offset, $caseSensitive, $encoding);
    }


    // --------------------------------------------------------------------------------- HTML
    /**
     * Appends an HTML break line (&lt;br/&gt;) at the end of a string.
     * @param string $string the input string
     * @return string
     */
    public static function htmlAppendBreak($string) {
        return $string . '<br/>';
    }

    /**
     * Inserts an HTML break line (&lt;br/&gt;) at the beginning of a string.
     * @param string $string the input string
     * @return string
     */
    public static function htmlInsertBreak($string) {
        return '<br/>'. $string;
    }

    /**
     * Encloses a string into an HTML tag. The corresponding closing tag will be automatically computed.
     * @param string $string the input string
     * @param string $tag the tag; if not specified &lt;div&gt; will be assumed
     * @return string the tag enclosed string
     */
    public static function htmlTagEnclose($string, $tag = null) {
        if(!isset($tag)) {
            $tag = '<div>';
        }
        $tagEnd = str_replace(substr($tag, 0, 1), substr($tag, 0, 1) . '/', $tag);
        return $tag . $string . $tagEnd;
    }

    /**
     * Converts all HTML special characters and entities into displayable elements (like PHP htmlentities).
     * @param string $string the input string
     * @param bool $all specifies if all the entities will be converted or the special chars only
     * @param string $encoding the used character encoding (ie. use default null for plain english, 'UTF-8' for UTF-8 encoded strings or corresponding used character encoding)
     * @return string
     */
    public static function htmlCompatible($string, $all = true, $encoding = null) {
        if(isset($encoding)) {
            if($all) {
                return htmlentities($string, ENT_QUOTES, $encoding);
            }else {
                return htmlspecialchars($string, ENT_QUOTES, $encoding);
            }
        }else {
            if($all) {
                return htmlentities($string, ENT_QUOTES);
            }else {
                return htmlspecialchars($string, ENT_QUOTES);
            }
        }
    }
    
    // --------------------------------------------------------------------------------- Thesaurus
    /**
     * Computes the Levenshtein distance between two strings.
     * @param string $str1 the first string
     * @param string $str2 the second string
     * @return int the computed Levenshtein distance
     */
    public static function levenshtein($str1, $str2) {
        return levenshtein($str1, $str2);
    }

    /**
     * Computes the Levenshtein distance between two strings as a percent between the length of the first string and the actual Levenshtein distance between them.
     * Usefull when computing closest dictionary matches in a check spelling algorithm.
     * @param string $str1 the first string
     * @param string $str2 the second string
     * @return float|int the percent: the greater it is, the most similar the two strings are
     */
    public static function levenshteinPercent($str1, $str2) {
        $slen = strlen($str1);
        if(!$slen) {
            return 0;
        }
        $lLen = self::levenshtein($str1, $str2);
        $ret = 100 - ($lLen * 100 / $slen);
        if($ret < 0) {
            $ret = 0;
        }
        return round($ret, 2); 
    }

    /**
     * Computes the soundex key of a string.
     * @param string $string the input string
     * @return string
     */
    public static function soundex($string) {
        return soundex($string);
    }

    /**
     * Computes the Levenshtein distance percent between the soundex keys of two strings.
     * Useful when computing closest dictionary matches in a check spelling algorithm.
     * @param string $str1 the first string
     * @param string $str2 the second string
     * @return float|int the percent: the greater it is, the most similar the two strings are
     */
    public static function soundexPercent($str1, $str2) {
        return self::levenshteinPercent(self::soundex($str1), self::soundex($str2));
    }

    /**
     * Computes the metaphone key of a string.
     * @param string $string the input string
     * @return string
     */
    public static function metaphone($string) {
        return metaphone($string);
    }

    /**
     * Computes the Levenshtein distance percent between the metaphone keys of two strings.
     * Useful when computing closest dictionary matches in a check spelling algorithm.
     * @param string $str1 the first string
     * @param string $str2 the second string
     * @return float|int the percent: the greater it is, the most similar the two strings are
     */
    public static function metaphonePercent($str1, $str2) {
        return self::levenshteinPercent(self::metaphone($str1), self::metaphone($str2));
    }

    /**
     * Computes the similarity percent between two strings.
     * Useful when computing closest dictionary matches in a check spelling algorithm.
     * @param string $str1 the first string
     * @param string $str2 the second string
     * @return float|int the percent: the greater it is, the most similar the two strings are
     */
    public static function intersectionPercent($str1, $str2) {
        $percent = 0;
        similar_text ($str1, $str2, $percent);
        return round($percent, 2);
    }

    /**
     * Tests if two string sounds similar according to a desired precision offset percent.
     * The test is made using the metaphone key representations of the strings.
     * @param string $str1 the first string
     * @param string $str2 the second string
     * @param int $precisionPercent the precision offset percent
     * @return bool
     */
    public static function soundsLike($str1, $str2, $precisionPercent = null) {
        if(!isset($precisionPercent)) {
            $precisionPercent = 50;
        }
        if($precisionPercent < 0) {
            $precisionPercent = 0;
        }
        if($precisionPercent > 100) {
            $precisionPercent = 100;
        }
        return self::metaphonePercent($str1, $str2) >= $precisionPercent;
    }

    /**
     * Computes the similarity match percent between two strings as an average value of the used single similarity computation functions.
     * Useful when comparing similarity of two strings or when computing closest dictionary matches in a check spelling algorithm.
     * @param string $str1 the first string
     * @param string $str2 the second string
     * @param bool $useMetaphone specifies if the metaphonePercent() function will be used
     * @param bool $useSoundex specifies if the soundexPercent() function will be used
     * @param bool $useIntersection specifies if the intersectionPercent() function will be used
     * @param bool $useLevenshtein specifies if the levenshteinPercent() function will be used
     * @return float|int the percent: the greater it is, the most similar the two strings are
     * @see metaphonePercent(), soundexPercent(), intersectionPercent(), levenshteinPercent()
     */
    public static function matchPercent($str1, $str2, $useMetaphone = true, $useSoundex = true, $useIntersection = true, $useLevenshtein = true) {
        $p1 = 0;
        $p2 = 0;
        $p3 = 0;
        $p4 = 0;
        $count = 0;

        if($useMetaphone) {
            $p1 = self::metaphonePercent($str1, $str2);
            $count ++;
        }
        if($useSoundex) {
            $p2 = self::soundexPercent($str1, $str2);
            $count ++;
        }
        if($useIntersection) {
            $p3 = self::intersectionPercent($str1, $str2);
            $count ++;
        }
        if($useLevenshtein) {
            $p4 = self::levenshteinPercent($str1, $str2);
            $count ++;
        }
        return round(($p1 + $p2 + $p3 + $p4) / $count, 2);
    }
}
