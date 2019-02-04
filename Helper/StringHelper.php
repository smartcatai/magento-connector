<?php
/**
 * SmartCat Translate Connector
 * Copyright (C) 2017 SmartCat
 *
 * This file is part of SmartCat/Connector.
 *
 * SmartCat/Connector is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace SmartCat\Connector\Helper;

class StringHelper
{
    private static $specCharsArray = ['*', '|', '\\', ':', '"', '<', '>', '?', '/'];

    /**
     * @param $string
     * @return mixed
     */
    public static function whitespaceSpecChars($string)
    {
        return self::replaceSpecChars($string, ' ');
    }

    /**
     * @param $string
     * @return mixed
     */
    public static function cropSpecChars($string)
    {
        return self::replaceSpecChars($string, '');
    }

    /**
     * @param $string
     * @param string $replace
     * @return mixed
     */
    public static function replaceSpecChars($string, $replace = '_')
    {
        return str_replace(self::$specCharsArray, $replace, $string);
    }

    /**
     * @param array $strings
     * @param int $limit
     * @param string $glue
     * @return bool|string
     */
    public static function limitImplode(array $strings, $limit = 94, $glue = ', ')
    {
        return substr(implode($glue, $strings), 0, $limit);
    }
}
