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
		if (!unserialize($this->packedValue, ['allowed_classes' => false]))
		{
			$result->addError(new Main\Error('Unavailable value for unpacking'));
		}
		return $result;
	}

	public function unpack()
	{
		$value = unserialize($this->packedValue, ['allowed_classes' => [
			\Bitrix\Main\Type\DateTime::class,
			\Bitrix\Main\Type\Date::class,
			\DateTime::class,
			\DateTimeZone::class,
		]]);

		if (!$value)
		{
			return null;
		}

		return $value;
	}
}