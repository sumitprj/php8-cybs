<?php
/*
 * Copyright © 2021 CyberSource. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */


namespace CyberSource\Core\StringUtils;

class StringUtils
{

    /**
     * @inheritDoc
     */
    public function str_trim($input)
    {
        return trim($input ?? '');
    }

    /**
     * Split a string by a string
     * 
     * @return string[]|false
     */
    public function str_explode($separator, $str)
    {
        return explode($separator, $str ?? '');
    }

    /**
     * Get string length
     *
     * @param String $str
     * @return int
     */
    public function str_length($str) {
        return strlen($str);
    }

    /**
     * Convert special characters to HTML entities
     * 
     * @param string $str
     * The {@link https://secure.php.net/manual/en/language.types.string.php string} being converted.
     * @param int $flag [optional]
     * @param bool $encode
     * 
     * @return string
     */
    public function str_htmlspecialchars($str, $flag = ENT_QUOTES|ENT_SUBSTITUTE, $encode) {
        return htmlspecialchars($str, $flag, $encode);
    }
}
