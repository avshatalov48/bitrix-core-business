<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2020 Bitrix
 */

namespace Bitrix\Main\File\Image;

class Mask implements \ArrayAccess
{
	protected $mask = [
		[1, 1, 1],
		[1, 1, 1],
		[1, 1, 1],
	];

	/**
	 * Mask constructor.
	 * @param array|null $mask
	 */
	public function __construct(array $mask = null)
	{
		if($mask !== null)
		{
			$this->mask = $mask;
		}
	}

	/**
	 * @return array
	 */
	public function getValue()
	{
		return $this->mask;
	}

	/**
	 * @param int $precision
	 * @return static
	 */
	public static function createSharpen($precision)
	{
		$mask = null;
		if($precision > 0)
		{
			$k = 1.0/((int)$precision);
			$mask = [
				[-$k, -$k, -$k],
				[-$k, 1+8*$k, -$k],
				[-$k, -$k, -$k],
			];
		}
		return new static($mask);
	}

	public function offsetSet($offset, $value)
	{
		if(is_null($offset))
		{
			$this->mask[] = $value;
		}
		else
		{
			$this->mask[$offset] = $value;
		}
	}

	public function offsetExists($offset)
	{
		return isset($this->mask[$offset]);
	}

	public function offsetUnset($offset)
	{
		unset($this->mask[$offset]);
	}

	public function offsetGet($offset)
	{
		return (isset($this->mask[$offset])? $this->mask[$offset] : null);
	}
}
