<?php
namespace Bitrix\Sale\Archive\Recovery;

use Bitrix\Main\Result;

/**
 * @package Bitrix\Sale\Archive\Recovery
 */
abstract class PackedField
{
	protected $packedValue = '';

	public function __construct($packedValue = '')
	{
		$this->packedValue = $packedValue;
	}

	public function getPackedValue()
	{
		return $this->packedValue;
	}

	/**
	 * @return Result
	 */
	abstract public function tryUnpack();
	abstract public function unpack();
}