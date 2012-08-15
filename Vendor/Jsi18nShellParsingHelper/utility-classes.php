<?php

/*
|------------------------------------------------
| parse-js.php Utilities
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

// ----------------------------------------------------------------------------
//  Token class

class JS_Token {
	public $type  = null;
	public $value = null;
	public $line  = null;
	public $col   = null;
	public $pos   = null;
	public $nlb   = null;
	public $comments_before = null;
	public function __construct($type, $value, $line, $col, $pos, $nlb) {
		$this->type  = $type;
		$this->value = $value;
		$this->line  = $line;
		$this->col   = $col;
		$this->pos   = $pos;
		$this->nlb   = $nlb;
	}
}

// ----------------------------------------------------------------------------
//  NodeWithToken class

class NodeWithToken {
	public $name = null;
	public $start = null;
	public $end = null;
	public function __construct($name, $start, $end) {
		$this->name = $name;
		$this->start = $start;
		$this->end = $end;
	}
	public function __toString() {
		return $this->name;
	}
}

// ----------------------------------------------------------------------------
//  EOF exception class

class JS_EOF extends Exception { }

// ----------------------------------------------------------------------------
//  JavaScript parse error class

class JS_Parse_Error extends Exception {
	
	public $js_message  = null;
	public $js_line     = null;
	public $js_col      = null;
	public $js_pos      = null;

	public function __construct($msg, $line, $col, $pos) {
		$this->js_message  = $msg;
		$this->js_line     = $line;
		$this->js_col      = $col;
		$this->js_pos      = $pos;
		parent::__construct($this->as_string(false), E_USER_WARNING);
	}

	public function __toString() {
		return $this->as_string(true);
	}

	public function as_string($with_stack = false) {
		$ret = $this->js_message.' (line: '.$this->js_line.', col: '.$this->js_col.', pos: '.$this->js_pos.')';
		if ($with_stack) {
			$ret .= "\n\n".$this->getTraceAsString();
		}
		return $ret;
	}
	
}

/* End of file utility-classes.php */
