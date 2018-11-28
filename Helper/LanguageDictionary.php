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

namespace SmartCat\Connector\Magento\Helper;


class LanguageDictionary
{
    /**
     * @return array
     */
    private static function getLanguages()
    {
       return [
           ['name' => __('Undefined Language'), 'code' => ''],
           ['name' => __('Abkhaz'), 'code' => 'ab'],
           ['name' => __('Avar'), 'code' => 'av'],
           ['name' => __('Azeri (Cyrillic)'), 'code' => 'az-Cyrl'],
           ['name' => __('Azeri (Latin)'), 'code' => 'az-Latn'],
           ['name' => __('Akan'), 'code' => 'ak'],
           ['name' => __('Albanian'), 'code' => 'sq'],
           ['name' => __('Amharic'), 'code' => 'am'],
           ['name' => __('English'), 'code' => 'en'],
           ['name' => __('English (Australia)'), 'code' => 'en-AU'],
           ['name' => __('English (United Kindom)'), 'code' => 'en-GB'],
           ['name' => __('English (USA)'), 'code' => 'en-US'],
           ['name' => __('Arabic'), 'code' => 'ar'],
           ['name' => __('Armenian'), 'code' => 'hy'],
           ['name' => __('Armenian (Eastern)'), 'code' => 'hy-arevela'],
           ['name' => __('Armenian (Western)'), 'code' => 'hy-arevmda'],
           ['name' => __('Assamese'), 'code' => 'as'],
           ['name' => __('Afar'), 'code' => 'aa'],
           ['name' => __('Afrikaans'), 'code' => 'af'],
           ['name' => __('Bambara'), 'code' => 'bm'],
           ['name' => __('Basque'), 'code' => 'eu'],
           ['name' => __('Bashkir'), 'code' => 'ba'],
           ['name' => __('Belarusian'), 'code' => 'be'],
           ['name' => __('Balochi (southern)'), 'code' => 'bcc'],
           ['name' => __('Bengali'), 'code' => 'bn'],
           ['name' => __('Burmese'), 'code' => 'my'],
           ['name' => __('Bihari'), 'code' => 'bh'],
           ['name' => __('Bulgarian'), 'code' => 'bg'],
           ['name' => __('Bosnian'), 'code' => 'bs'],
           ['name' => __('Hungarian'), 'code' => 'hu'],
           ['name' => __('Wolof'), 'code' => 'wo'],
           ['name' => __('Vietnamese'), 'code' => 'vi'],
           ['name' => __('Haitian Creole'), 'code' => 'ht'],
           ['name' => __('Galician'), 'code' => 'gl'],
           ['name' => __('Greek'), 'code' => 'el'],
           ['name' => __('Georgian'), 'code' => 'ka'],
           ['name' => __('Guarani'), 'code' => 'gn'],
           ['name' => __('Gujarati'), 'code' => 'gu'],
           ['name' => __('Danish'), 'code' => 'da'],
           ['name' => __('Zulu'), 'code' => 'zu'],
           ['name' => __('Hebrew'), 'code' => 'he'],
           ['name' => __('Yiddish'), 'code' => 'yi'],
           ['name' => __('Indonesian'), 'code' => 'id'],
           ['name' => __('Irish'), 'code' => 'ga'],
           ['name' => __('Icelandic'), 'code' => 'is'],
           ['name' => __('Spanish'), 'code' => 'es'],
           ['name' => __('Spanish (Argentina)'), 'code' => 'es-AR'],
           ['name' => __('Spanish (Spain)'), 'code' => 'es-ES'],
           ['name' => __('Spanish (Mexico)'), 'code' => 'es-MX'],
           ['name' => __('Italian'), 'code' => 'it'],
           ['name' => __('Yoruba'), 'code' => 'yo'],
           ['name' => __('Kabyle'), 'code' => 'kab'],
           ['name' => __('Kazakh'), 'code' => 'kk'],
           ['name' => __('Kannada'), 'code' => 'kn'],
           ['name' => __('Catalan'), 'code' => 'ca'],
           ['name' => __('Kinyarwanda'), 'code' => 'rw'],
           ['name' => __('Kyrgyz'), 'code' => 'ky'],
           ['name' => __('Chinese (Hong Kong SAR)'), 'code' => 'zh-Hant-HK'],
           ['name' => __('Chinese (Cantonese)'), 'code' => 'yue'],
           ['name' => __('Chinese (PRC)'), 'code' => 'zh-Hans'],
           ['name' => __('Chinese (Macau SAR)'), 'code' => 'zh-Hant-MO'],
           ['name' => __('Chinese (Malaysia)'), 'code' => 'zh-Hans-MY'],
           ['name' => __('Chinese (Singapore)'), 'code' => 'zh-Hans-SG'],
           ['name' => __('Chinese (Taiwan)'), 'code' => 'zh-Hant-TW'],
           ['name' => __('Komi'), 'code' => 'kv'],
           ['name' => __('Korean'), 'code' => 'ko'],
           ['name' => __('Cornish'), 'code' => 'kw'],
           ['name' => __('Kurdish (Sorani)'), 'code' => 'ckb-Arab'],
           ['name' => __('Kurdish (Kurmandji)'), 'code' => 'kmr-Latn'],
           ['name' => __('Kurdish (Palewani)'), 'code' => 'sdh-Arab'],
           ['name' => __('Khmer'), 'code' => 'km'],
           ['name' => __('Lao'), 'code' => 'lo'],
           ['name' => __('Latin'), 'code' => 'la'],
           ['name' => __('Latvian'), 'code' => 'lv'],
           ['name' => __('Limburgish'), 'code' => 'li'],
           ['name' => __('Lingala'), 'code' => 'ln'],
           ['name' => __('Lithuanian'), 'code' => 'lt'],
           ['name' => __('Luxembourgish'), 'code' => 'lb'],
           ['name' => __('Macedonian'), 'code' => 'mk'],
           ['name' => __('Malagasy'), 'code' => 'mg'],
           ['name' => __('Malay'), 'code' => 'ms'],
           ['name' => __('Malay (Malaysia)'), 'code' => 'ms-MY'],
           ['name' => __('Malay (Singapore)'), 'code' => 'ms-SG'],
           ['name' => __('Malayalam'), 'code' => 'ml'],
           ['name' => __('Marathi'), 'code' => 'mr'],
           ['name' => __('Mari'), 'code' => 'chm'],
           ['name' => __('Mongolian'), 'code' => 'mn'],
           ['name' => __('German'), 'code' => 'de'],
           ['name' => __('German (Austria)'), 'code' => 'de-AT'],
           ['name' => __('German (Germany)'), 'code' => 'de-DE'],
           ['name' => __('German (Switzerland)'), 'code' => 'de-CH'],
           ['name' => __('Nepali'), 'code' => 'ne'],
           ['name' => __('Dutch'), 'code' => 'nl'],
           ['name' => __('Norwegian'), 'code' => 'no'],
           ['name' => __('Norwegian (Bokmål)'), 'code' => 'nb'],
           ['name' => __('Norwegian (Nynorsk)'), 'code' => 'nn'],
           ['name' => __('Occitan'), 'code' => 'oc'],
           ['name' => __('Oria'), 'code' => 'or'],
           ['name' => __('Ossetian'), 'code' => 'os'],
           ['name' => __('Punjabi'), 'code' => 'pa'],
           ['name' => __('Polish'), 'code' => 'pl'],
           ['name' => __('Portuguese'), 'code' => 'pt'],
           ['name' => __('Portuguese (Brazil)'), 'code' => 'pt-BR'],
           ['name' => __('Portuguese (Portugal)'), 'code' => 'pt-PT'],
           ['name' => __('Pashto'), 'code' => 'ps'],
           ['name' => __('Rohingya (Latin)'), 'code' => 'rhg-Latn'],
           ['name' => __('Romanian'), 'code' => 'ro'],
           ['name' => __('Romanian (Moldova)'), 'code' => 'ro-MD'],
           ['name' => __('Romanian (Romania)'), 'code' => 'ro-RO'],
           ['name' => __('Rundi'), 'code' => 'rn'],
           ['name' => __('Russian'), 'code' => 'ru'],
           ['name' => __('Samoan'), 'code' => 'sm'],
           ['name' => __('Sango'), 'code' => 'sg'],
           ['name' => __('Sanskrit'), 'code' => 'sa'],
           ['name' => __('Sardinian'), 'code' => 'sc'],
           ['name' => __('Sakha'), 'code' => 'sah'],
           ['name' => __('Serbian (Cyrillic)'), 'code' => 'sr-Cyrl'],
           ['name' => __('Serbian (Latin)'), 'code' => 'sr-Latn'],
           ['name' => __('Sinhalese'), 'code' => 'si'],
           ['name' => __('Sindhi'), 'code' => 'sd'],
           ['name' => __('Slovak'), 'code' => 'sk'],
           ['name' => __('Slovenian'), 'code' => 'sl'],
           ['name' => __('Somali'), 'code' => 'so'],
           ['name' => __('Swahili'), 'code' => 'sw'],
           ['name' => __('Sundanese'), 'code' => 'su'],
           ['name' => __('Tagalog'), 'code' => 'tl'],
           ['name' => __('Tajik'), 'code' => 'tg'],
           ['name' => __('Thai'), 'code' => 'th'],
           ['name' => __('Tamil'), 'code' => 'ta'],
           ['name' => __('Tatar'), 'code' => 'tt'],
           ['name' => __('Telugu'), 'code' => 'te'],
           ['name' => __('Tibetan'), 'code' => 'bo'],
           ['name' => __('Tigrinya'), 'code' => 'ti'],
           ['name' => __('Tongan'), 'code' => 'to'],
           ['name' => __('Tswana'), 'code' => 'tn'],
           ['name' => __('Turkish'), 'code' => 'tr'],
           ['name' => __('Turkmen'), 'code' => 'tk'],
           ['name' => __('Udmurt'), 'code' => 'udm'],
           ['name' => __('Uzbek'), 'code' => 'uz-Latn'],
           ['name' => __('Uigur'), 'code' => 'ug'],
           ['name' => __('Ukrainian'), 'code' => 'uk'],
           ['name' => __('Urdu'), 'code' => 'ur'],
           ['name' => __('Farsi'), 'code' => 'fa'],
           ['name' => __('Filipino'), 'code' => 'fil'],
           ['name' => __('Finnish'), 'code' => 'fi'],
           ['name' => __('French'), 'code' => 'fr'],
           ['name' => __('French (Canada)'), 'code' => 'fr-CA'],
           ['name' => __('French (France)'), 'code' => 'fr-FR'],
           ['name' => __('Fulah'), 'code' => 'ff'],
           ['name' => __('Hazaragi'), 'code' => 'haz'],
           ['name' => __('Hausa (Latin)'), 'code' => 'ha-Latn'],
           ['name' => __('Hindi'), 'code' => 'hi'],
           ['name' => __('Hmong'), 'code' => 'hmn'],
           ['name' => __('Croatian'), 'code' => 'hr'],
           ['name' => __('Chechen'), 'code' => 'ce'],
           ['name' => __('Czech'), 'code' => 'cs'],
           ['name' => __('Chuvash'), 'code' => 'cv'],
           ['name' => __('Swedish'), 'code' => 'sv'],
           ['name' => __('Shona'), 'code' => 'sn'],
           ['name' => __('Esperanto'), 'code' => 'eo'],
           ['name' => __('Estonian'), 'code' => 'et'],
           ['name' => __('Javanese'), 'code' => 'jv'],
           ['name' => __('Japanese'), 'code' => 'ja'],
       ];
    }

    /**
     * @param $code
     * @return string
     */
    public static function getNameByCode($code)
    {
        $index = array_search($code, array_column(self::getLanguages(), 'code'));

        return self::getLanguages()[$index]['name'];
    }
}
