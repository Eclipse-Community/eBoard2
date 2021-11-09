<?php

$language_data = array (
    'LANG_NAME' => 'IRC',
    'KEYWORDS' => array(),
    'SYMBOLS' => array(),
    'QUOTEMARKS' => array(),
    'COMMENT_SINGLE' => array(
        1 =>'* ',
        2 =>'*** ',
        ),
    'COMMENT_MULTI' => array('<' => '>'),
    'REGEXPS' => array(),
    /*
    'COMMENT_REGEXP' => array(
        1 => "/(?:--\s).*?$/",                           double dash followed by any whitespace
        ),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,             @@@ would be nice if this could be defined per group!
    'ESCAPE_CHAR' => '\\',                               by default only, can be specified
    'ESCAPE_REGEXP' => array(
        1 => "/[_%]/",                                   search wildcards
        ),
    'NUMBERS' =>
        GESHI_NUMBER_INT_BASIC |
        GESHI_NUMBER_OCT_PREFIX |
        GESHI_NUMBER_HEX_PREFIX |
        GESHI_NUMBER_FLT_NONSCI |
        GESHI_NUMBER_FLT_SCI_SHORT |
        GESHI_NUMBER_FLT_SCI_ZERO,
    'KEYWORDS' => array(
        1 => array(
             Mix: statement keywords and keywords that don't fit in any other
             category, or have multiple usage/meanings
            'ACTION','ADD','AFTER','ALGORITHM','ALL','ALTER','ANALYZE','ANY',
            ),
        ),
    'SYMBOLS' => array(
        1 => array(
            '=', ':=',                                       assignment operators
            '||', '&&', '!',                                 locical operators
            '=', '<=>', '>=', '>', '<=', '<', '<>', '!=',   // comparison operators
            '|', '&', '^', '~', '<<', '>>',                  bitwise operators
            '-', '+', '*', '/', '%',                         numerical operators
            ),
        2 => array(
            '(', ')',
            ',', ';',
            ),
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => false,
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #990099; font-weight: bold;',       mix
            ),
        'COMMENTS' => array(
            'MULTI' => 'color: #808000; font-style: italic;',
            1 => 'color: #808080; font-style: italic;',
            2 => 'color: #808080; font-style: italic;'
            ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #004000; font-weight: bold;',
            1 => 'color: #008080; font-weight: bold;'        search wildcards
            ),
        'BRACKETS' => array(
            0 => 'color: #FF00FF;'
            ),
        'STRINGS' => array(
            0 => 'color: #008000;'
            ),
        'NUMBERS' => array(
            0 => 'color: #008080;'
            ),
        'METHODS' => array(
            ),
        'SYMBOLS' => array(
            1 => 'color: #CC0099;',          operators
            2 => 'color: #000033;',          syntax
            ),
        'SCRIPT' => array(
            ),
        'REGEXPS' => array(
            )
        ),
    'OOLANG' => false,
    'OBJECT_SPLITTERS' => array(
        ),
    'REGEXPS' => array(
        ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
        ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
        ),
    'TAB_WIDTH' => 4,
    'PARSER_CONTROL' => array(
        'KEYWORDS' => array(
            2 => array(
                'DISALLOWED_AFTER' => '(?![\(\w])'
                ),
            5 => array(
                'DISALLOWED_AFTER' => '(?![\(\w])'
                ),
            7 => array(
                'DISALLOWED_AFTER' => '(?![\(\w])'
                ),
            9 => array(
                'DISALLOWED_AFTER' => '(?![\(\w])'
                ),
            11 => array(
                'DISALLOWED_AFTER' => '(?![\(\w])'
                ),

            14 => array(
                'DISALLOWED_AFTER' => '(?=\()'
                ),
            16 => array(
                'DISALLOWED_AFTER' => '(?=\()'
                ),
            18 => array(
                'DISALLOWED_AFTER' => '(?=\()'
                ),
            20 => array(
                'DISALLOWED_AFTER' => '(?=\()'
                ),
            24 => array(
                'DISALLOWED_AFTER' => '(?=\()'
                ),
            26 => array(
                'DISALLOWED_AFTER' => '(?=\()'
                )
            )
        )
        */
);

?>