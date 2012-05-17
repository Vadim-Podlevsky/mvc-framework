<?php

class highlightSQL {

    /*
     * The colors array
     */
    private $colors;

    /*
     * The highlight words
     */
    private $words;

    /**
     *
     * @constructor Set a few variables
     *
     * @access public
     *
     * @return void
     *
     */
    public function __construct()
    {
        $this->setColors();
        $this->setWords();
    }

    /**
    *
    * @set the array of words
    *
    * @access private
    *
    * @return void
    *
    */
    private function setWords()
    {
        $words = array(
            array(
            'AND',
            'IS',
                        '&&',
            'LOG',
                        'NOT',
            'NOW',
            'MIN',
                        '!',
                        '||',
                        'OR',
            'OCT',
            'TAN',
            'STD',
            'SHA',
            'ORD',
                        'XOR'),

            array(
                        'SELECT',
                        'UPDATE',
                        'INSERT',
                        'DELETE',
            'USING',
            'LIMIT',
            'OFFSET',
                        'SET'),

        array(
            'DATE',
                        'INTO',
                        'FROM',
                        'THEN',
                        'WHEN',
            'WHERE',
            'LEFT',
            'RIGHT',
            'INNER',
            'GROUP BY',
            'ORDER BY',
            'JOIN',
                        'ELSE'
                        ),

        array (
            'ABS',
            'ACOS',
            'ADDDATE',
            'ADDTIME',
            'AES_DECRYPT',
            'AES_ENCRYPT',
            '&&',
            'ASCII',
            'ASIN',
            'ATAN2',
            'ATAN',
            'AVG',
            'BETWEEN',
            'BIN',
            'BINARY',
            'BIT_AND',
            'BIT_LENGTH',
            'BIT_OR',
            'BIT_XOR',
            'CASE',
            'CAST',
            'CEIL',
            'CEILING',
            'CHAR_LENGTH',
            'CHAR',
            'CHARACTER_LENGTH',
            'CHARSET',
            'COALESCE',
            'COERCIBILITY',
                        'COLLATION',
                        'COMPRESS',
                        'CONCAT_WS',
                        'CONCAT',
                        'CONNECTION_ID',
                        'CONV',
                        'CONVERT_TZ',
                        'Convert',
                        'COS',
                        'COT',
                        'COUNT',
                        'COUNT',
                        'COUNT(DISTINCT)',
                        'CRC32',
                        'CURDATE',
                        'CURRENT_DATE',
                        'CURRENT_TIME',
                        'CURRENT_TIMESTAMP',
                        'CURRENT_USER',
                        'CURTIME',
                        'DATABASE',
                        'DATE_ADD',
                        'DATE_FORMAT',
                        'DATE_SUB',
                        'DATEDIFF',
                        'DAY ',
                        'DAYNAME',
                        'DAYOFMONTH',
                        'DAYOFWEEK',
                        'DAYOFYEAR',
                        'DECODE',
                        'DEFAULT',
                        'DEGREES',
                        'DES_DECRYPT',
                        'DES_ENCRYPT',
                        'DIV',
                        'ELT',
                        'ENCODE',
                        'ENCRYPT',
                        '<=>',
                        'EXP()',
                        'EXPORT_SET',
                        'EXTRACT',
                        'FIELD',
                        'FIND_IN_SET',
                        'FLOOR',
                        'FORMAT',
                        'SQL_CALC_FOUND_ROWS',
                        'FOUND_ROWS',
                        'FROM_DAYS',
                        'FROM_UNIXTIME',
                        'GET_FORMAT',
                        'GET_LOCK',
                        'GREATEST',
                        'GROUP_CONCAT',
                        'HEX ',
                        'HOUR',
                        ' IF ',
                        'IFNULL',
                        ' IN ',
                        'INET_ATON',
                        'INET_NTOA',
                        'INSTR',
                        'IS_FREE_LOCK',
                        'IS NOT NULL',
                        'IS NOT',
                        'IS NULL',
                        'IS_USED_LOCK',
                        'ISNULL',
                        'LAST_DAY',
                        'LAST_INSERT_ID',
                        'LCASE',
                        'LEAST',
                        '<<',
                        'LENGTH',
                        'LIKE',
                        'LN',
                        'LOAD_FILE',
                        'LOCALTIME',
                        'LOCALTIMESTAMP',
                        'LOCATE',
                        'LOG10',
                        'LOG2',
                        'LOWER',
                        'LPAD',
                        'LTRIM',
                        'MAKE_SET',
                        'MAKEDATE',
                        'MAKETIME',
                        'MASTER_POS_WAIT',
                        'MATCH',
                        'MAX',
                        'MD5',
                        'MICROSECOND',
                        'MID',
                        'MINUTE',
                        'MOD',
                        '%',
                        'MONTH',
                        'MONTHNAME',
                        'NOT BETWEEN',
                        '!=',
                        'NOT IN',
                        'NOT LIKE',
                        'NOT REGEXP',
                        'NULLIF',
                        'OCTET_LENGTH',
                        'OLD_PASSWORD',
                        'ORD',
                        'PASSWORD',
                        'PERIOD_ADD',
                        'PERIOD_DIFF',
                        'PI',
                        '+',
                        'POSITION',
                        'POW',
                        'POWER',
                        'PROCEDURE ANALYSE',
                        'QUARTER',
                        'QUOTE',
                        'RADIANS',
                        'RAND',
                        'REGEXP',
                        'RELEASE_LOCK',
                        'REPEAT',
                        'REPLACE',
                        'REVERSE',
                        '>>',
                        'RIGHT',
                        'RLIKE',
                        'ROUND',
                        'ROW_COUN',
                        'RPAD',
                        'RTRIM',
                        'SCHEMA',
                        'SEC_TO_TIME',
                        'SECOND',
                        'SESSION_USER',
                        'SHA1',
                        'SIGN',
            'SLEEP',
            'SOUNDEX',
            'SOUNDS LIKE',
            'SPACE',
            'SQRT',
            'STDDEV_POP',
            'STDDEV_SAMP',
            'STDDEV',
            'STR_TO_DATE',
            'SUBDATE',
            'SUBSTR',
            'SUBSTRING_INDEX',
            'SUBSTRING',
            'SUBTIME',
            'SUM',
            'SYSDATE',
            'SYSTEM_USER',
            'TIME_FORMAT',
            'TIME_TO_SEC',
            'TIME',
            'TIMEDIFF',
            '*',
            'TIMESTAMP',
                        'TIMESTAMPADD',
                        'TIMESTAMPDIFF',
                        'TO_DAYS',
                        'TRIM',
                        'TRUNCATE',
                        'UCASE',
                        'UNCOMPRESS',
                        'UNCOMPRESSED_LENGTH',
                        'UNHEX',
                        'UNIX_TIMESTAMP',
            'UPPER',
                        'USER',
                        'UTC_DATE',
                        'UTC_TIME',
                        'UTC_TIMESTAMP',
                        'UUID',
                        'VALUES',
                        'VAR_POP',
                        'VAR_SAMP',
                        'VARIANCE',
                        'VERSION',
                        'WEEK',
            'WEEKDAY',
            'WEEKOFYEAR',
            'YEAR',
            'YEARWEE'));
    $this->words = $words;
    }

    /**
    *
    * @set the array of colors to highlight
    *
    * @access private
    *
    * @return void
    *
    */
    private function setColors()
    {
        /** the array of colors ***/
        $this->colors = array('orange', 'blue', 'green', 'brown');
    }

    /**
    *
    * @Highlight an sql string
    *
    * @access public
    *
    * @param string $sql The SQL string
    *
    * @param string $string The string to replace
    *
    * @param The highlight color
    *
    */
    public function addStyle($sql, $string, $color)
    {
//    	dump($string);
    	return preg_replace("/\b".preg_quote($string)."\b/i", '<span style="font-family: sans-serif; font-weight:bold; color:'.$color.';">\\1'.$string.' </span>', $sql);
//        return str_ireplace(' '.$string.' ', '<span style="font-family: sans-serif; font-weight:bold; color:'.$color.';">&nbsp;'.$string.'&nbsp;</span>', $sql);
    }


	public function highlight($sql)
    {
        $i = 0;
        foreach($this->colors as $color)
        {
            foreach($this->words[$i] as $word)
            {
                $sql = $this->addStyle( $sql, $word, $color);
            }
            $i++;
        }
        return '<div style="font:weight:bold; color:#ff3b0f;">'.$sql.'</div>';
    }

} /*** end of class ***/
?>