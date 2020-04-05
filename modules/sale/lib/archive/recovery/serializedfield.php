<?php
namespace Bitrix\Sale\Archive\Recovery;

use Bitrix\Main,
	Bitrix\Sale,
	Bitrix\Sale\Archive,
	Bitrix\Sale\Internals;

/**
 * @package Bitrix\Sale\Archive\Recovery
 */
class SerializedField extends PackedField
{
	public function tryUnpack()
	{
		$result = new Main\Result();
		if (!unserialize($this->packedValue))
		{
			$result->addError(new Main\Error('Unavailable value for unpacking'));
		}
		return $result;
	}

	public function unpack()
	{
		$value = unserialize($this->packedValue);
		if (!$value)
			return null;

		return $value;
	}
}