<?php

/**
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 */

/**
 * ArrayConverter Utility.
 *
 * @package Bancha.libs
 */
class ArrayConverter
{
	
/** @var array */
	private $data;
	
/**
 * Constructor
 *
 * @param array $data Data array
 */
	public function __construct(array $data)
	{
		$this->data = $data;
	}
	
/**
 * Returns the data array.
 *
 * @return array Data array.
 */
	public function getArray()
	{
		return $this->data;
	}
	
/**
 * Renames the key from the given element.
 *
 * @param string $from Current key.
 * @param string $to Key after conversion.
 * @param boolean $overwrite If TRUE renamed even if $to element already exists.
 * @return ArrayConverter
 */
	public function renameElement($from, $to, $overwrite = true)
	{
		if (isset($this->data[$from]) && ($overwrite || !isset($this->data[$to])))
		{
			$this->data[$to] = $this->data[$from];
			unset($this->data[$from]);
		}
		return $this;
	}
	
/**
 * Removes the given element if it exists and returns it value.
 *
 * @param string $key Key of the element.
 * @return mixed Value of the element.
 */
	public function removeElement($key)
	{
		if (isset($this->data[$key]))
		{
			$value = $this->data[$key];
			unset($this->data[$key]);
			return $value;
		}
		return null;
	}

/**
 * Changes the value of an element with the key $key $from to $to.
 *
 * @param string $key Key of the element.
 * @param string $from Change from this value.
 * @param string $to Change to this value.
 * @param boolean $create If TRUE the element is created if it does not exist, defaults to FALSE.
 * @return ArrayConverter
 */
	public function changeValue($key, $from, $to, $create = false)
	{
		if ((isset($this->data[$key]) && $from == $this->data[$key]) || $create)
		{
			$this->data[$key] = $to;
		}
		return $this;
	}

}