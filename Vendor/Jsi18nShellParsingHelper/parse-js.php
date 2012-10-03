<?php

/*
|------------------------------------------------
| parse-js.php
|------------------------------------------------
|
| A JavaScript parser ported from the UglifyJS [1] JavaScript
| parser which was itself a port of parse-js [2], a JavaScript
| parser by Marijn Haverbeke.
|
| [1] https://github.com/mishoo/UglifyJS/
| [2] http://marijn.haverbeke.nl/parse-js/
|
|------------------------------------------------
|
| @author     James Brumond
| @version    0.1.1-dev
| @copyright  Copyright 2011 James Brumond
| @license    Dual licensed under MIT and GPL
|
*/


// Contains extra sub-classes
require_once 'utility-classes.php';

// Contains the JavaScript tokenizer class
require_once 'javascript-tokenizer.php';

// Contains the JavaScript parser class
//require_once 'javascript-parser.php';


// ----------------------------------------------------------------------------
//  Core parse-js class

class ParseJS {

	public static function tokenizer($input) {
		return new JavaScript_Tokenizer($input);
	}
	
	// public static function parse($input, $exigent_mode = true, $embed_tokens = true) {
	// 	$parser = new JavaScript_Parser($input, $exigent_mode, $embed_tokens);
	// 	return $parser->run();
	// }

// ----------------------------------------------------------------------------
//  Constants

	public static $KEYWORDS = array(
		"break", "case", "catch", "const", "continue", "default", "delete",
		"do", "else", "finally", "for", "function", "if", "in", "instanceof",
		"new", "return", "switch", "throw", "try", "typeof", "var", "void",
		"while", "with"
	);

	public static $RESERVED_WORDS = array(
		"abstract", "boolean", "byte", "char", "class", "debugger", "double",
		"enum", "export", "extends", "final", "float", "goto", "implements",
		"import", "int", "interface", "long", "native", "package", "private",
		"public", "public", "short", "static", "super", "synchronized",
		"throws", "transient", "volatile"
	);

	public static $KEYWORDS_BEFORE_EXPRESSION = array(
		"return", "new", "delete", "throw", "else", "case"
	);

	public static $KEYWORDS_ATOM = array(
		"false", "null", "true", "undefined"
	);

	public static $OPERATOR_CHARS = array(
		"+", "-", "*", "&", "%", "=", "<", ">", "!", "?", "|", "~", "^"
	);

	public static $RE_HEX_NUMBER = '/^0x[0-9a-f]+$/i';
	public static $RE_OCT_NUMBER = '/^0[0-7]+$/';
	public static $RE_DEC_NUMBER = '/^\d*\.?\d*(?:e[+-]?\d*(?:\d\.?|\.?\d)\d*)?$/i';

	public static $OPERATORS = array(
		"in", "instanceof", "typeof", "new", "void", "delete", "++", "--", "+",
		"-", "!", "~", "&", "|", "^", "*", "/", "%", ">>", "<<", ">>>", "<",
		">", "<=", ">=", "==", "===", "!=", "!==", "?", "=", "+=", "-=", "/=",
		"*=", "%=", ">>=", "<<=", ">>>=", "|=", "^=", "&=", "&&", "||"
	);

	public static $WHITESPACE_CHARS = array(
		" ", "\n", "\r", "\t", "\u200b"
	);

	public static $PUNC_BEFORE_EXPRESSION = array(
		"[", "{", "}", "(", ",", ".", ";", ":"
	);

	public static $PUNC_CHARS = array(
		"[", "]", "{", "}", "(", ")", ",", ";", ":"
	);

	public static $REGEXP_MODIFIERS = array(
		"g", "m", "s", "i", "y"
	);

	// regexps adapted from http://xregexp.com/plugins/#unicode
	public static $UNICODE = array(
		'letter'                => '/[\p{L}]/u',
		'non_spacing_mark'      => '/[\p{Mn}]/u',
		'space_combining_mark'  => '/[\p{Mc}]/u',
		'connector_punctuation' => '/[\p{Pc}]/u'
	);

	public static $UNARY_PREFIX = array(
		"typeof", "void", "delete", "--", "++", "!", "~", "-", "+"
	);

	public static $UNARY_POSTFIX = array(
		"--", "++"
	);

	public static $ASSIGNMENT = null;
	public static function _ASSIGNMENT() {
		if (! self::$ASSIGNMENT) {
			$a = array("+=", "-=", "/=", "*=", "%=", ">>=", "<<=", ">>>=", "|=", "^=", "&=");
			$ret = array( '=' => true );
			foreach ($a as $i => $op) {
				$ret[$op] = substr($op, strlen($op) - 1);
			}
			self::$ASSIGNMENT = $ret;
		}
	}

	public static $PRECEDENCE = null;
	public static function _PRECEDENCE() {
		if (! self::$PRECEDENCE) {
			$a = array(
				array("||"),
				array("&&"),
				array("|"),
				array("^"),
				array("&"),
				array("==", "===", "!=", "!=="),
				array("<", ">", "<=", ">=", "in", "instanceof"),
				array(">>", "<<", ">>>"),
				array("+", "-"),
				array("*", "/", "%")
			);
			$ret = array();
			for ($i = 0, $n = 1, $c1 = count($a); $i < $c1; $i++, $n++) {
				$b = $a[$i];
				for ($j = 0, $c2 = count($b); $j < $c2; $j++) {
					$ret[$b[$j]] = $n;
				}
			}
			self::$PRECEDENCE = $ret;
		}
	}

	public static $STATEMENTS_WITH_LABELS = array(
		"for", "do", "while", "switch"
	);

	public static $ATOMIC_START_TOKEN = array(
		"atom", "num", "string", "regexp", "name"
	);

// ----------------------------------------------------------------------------
//  Utilities
	
	public static function is_letter($ch) {
		return preg_match(self::$UNICODE['letter'], $ch);
	}

	public static function is_digit($ch) {
		$ch = ord($ch);
		return ($ch >= 48 && $ch <= 57);
	}

	public static function is_alphanumeric_char($ch) {
		return (self::is_letter($ch) || self::is_digit($ch));
	}

	public static function is_unicode_combining_mark($ch) {
		return (preg_match(self::$UNICODE['space_combining_mark'], $ch) ||
			preg_match(self::$UNICODE['non_spacing_mark'], $ch));
	}

	public static function is_unicode_connector_punctuation($ch) {
		return preg_match(self::$UNICODE['connector_punctuation'], $ch);
	}
	
	public static function is_identifier_start($ch) {
		return ($ch == '$' || $ch == '_' || self::is_letter($ch));
	}

	public static function is_identifier_char($ch) {
		return (
			self::is_identifier_start($ch) ||
			self::is_unicode_combining_mark($ch) ||
			self::is_digit($ch) ||
			self::is_unicode_connector_punctuation($ch) ||
			$ch == "\u200c" || $ch == "\u200d"
		);
	}

	public static function parse_js_number($num) {
		if (preg_match(self::$RE_HEX_NUMBER, $num)) {
			return intval(substring($num, 2), 16);
		} elseif (preg_match(self::$RE_OCT_NUMBER, $num)) {
			return intval(substring($num, 1), 8);
		} elseif (preg_match(self::$RE_DEC_NUMBER, $num)) {
			return floatval($num);
		}
	}

	public static function is_token($token, $type, $value = null) {
		return ($token->type == $type && ($value === null || $token->value == $value));
	}

	public static function unichr($code) {
		return html_entity_decode('&#'.$code.';', ENT_NOQUOTES, 'UTF-8');
	}

	public static function uniord($c) {
		$h = ord($c{0});
		if ($h <= 0x7F) {
			return $h;
		} else if ($h < 0xC2) {
			return false;
		} else if ($h <= 0xDF) {
			return ($h & 0x1F) << 6 | (ord($c{1}) & 0x3F);
		} else if ($h <= 0xEF) {
			return ($h & 0x0F) << 12 | (ord($c{1}) & 0x3F) << 6
			                         | (ord($c{2}) & 0x3F);
		} else if ($h <= 0xF4) {
			return ($h & 0x0F) << 18 | (ord($c{1}) & 0x3F) << 12
			                         | (ord($c{2}) & 0x3F) << 6
			                         | (ord($c{3}) & 0x3F);
		} else {
			return false;
		}
	}

}

// Do some initializing
ParseJS::_PRECEDENCE();
ParseJS::_ASSIGNMENT();

/* End of file parse-js.php */
