<?php

class XmlDataType
{
    private static $xmlStr  = array('string', 'token', 'normalizedString');
    private static $xmlNum  = array(
        'byte',
        'decimal',
        'int',
        'integer',
        'negativeInteger',
        'nonNegativeInteger',
        'nonPositiveInteger',
        'positiveInteger',
        'short',
        'unsignedLong',
        'unsignedInt',
        'unsignedShort',
        'unsignedByte'
    );
    private static $xmlDate = array(
        'date',
        'time',
        'dateTime',
        'duration',
        'gDay',
        'gMonth',
        'gMonthDay',
        'gYear',
        'gYearMonth'
    );
    private static $xmlBool = array('boolean');

    private static $xmlOther = array('anyURI', 'base64Binary');

    private static $prefix = 'xsd:';

    /**
     * Check is a value is any of the Xml Data Types
     * @param type $type
     * @return True if the type is found, False otherwise
     */
    public static function isXmlDataType($type)
    {
        $simple = str_ireplace(self::$prefix, '', $type);
        $arrAll = array_merge(self::$xmlStr, self::$xmlNum, self::$xmlDate, self::$xmlOther);

        return in_array($simple, $arrAll);
    }

    public static function isHtmlEquivalent($type)
    {
        $arr = array('text', 'number', 'date', 'boolean');
        $arrFull = array_merge($arr, self::$xmlDate);

        return in_array($type, $arrFull);
    }

    public static function getHtmlEquivalent($type)
    {
        if (self::isString($type)) {
            return 'text';
        }

        if (self::isNumeric($type)) {
            return 'number';
        }

        if (self::isDate($type)) {
            return str_replace('xsd:', '', $type);
        }

        if (self::isOther($type)) {
            return 'text';
        }

        if (self::isBoolean($type)) {
            return 'boolean';
        }

        return $type;
    }

    /**
     * Check is a value is a Xml String Type
     * @param type $type
     * @return True if the type is found, False otherwise
     */
    public static function isString($type)
    {
        $type = str_ireplace(self::$prefix, '', $type);

        return in_array($type, self::$xmlStr);
    }

    /**
     * Check is a value is a Xml Numeric Type
     * @param type $type
     * @return True if the type is found, False otherwise
     */
    public static function isNumeric($type)
    {
        $type = str_ireplace(self::$prefix, '', $type);

        return in_array($type, self::$xmlNum);
    }

    /**
     * Check is a value is a Xml Date Type
     * @param type $type
     * @return True if the type is found, False otherwise
     */
    public static function isDate($type)
    {
        $type = str_ireplace(self::$prefix, '', $type);

        return in_array($type, self::$xmlDate);
    }

    /**
     * Check is a value is a Xml Type not considered in previous functions
     * @param type $type
     * @return True if the type is found, False otherwise
     */

    public static function isOther($type)
    {
        $type = str_ireplace(self::$prefix, '', $type);

        return in_array($type, self::$xmlOther);
    }

    /**
     * Check is a value is a Xml Type Boolean
     * @param type $type
     * @return True if the type is found, False otherwise
     */

    public static function isBoolean($type)
    {
        $type = str_ireplace(self::$prefix, '', $type);

        return in_array($type, self::$xmlBool);
    }
}


?>