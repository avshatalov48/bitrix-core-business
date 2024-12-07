<?php
namespace Bitrix\Bizproc\BaseType;

use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\FieldType;

Loc::loadMessages(__FILE__);

/**
 * Class Datetime
 * @package Bitrix\Bizproc\BaseType
 */
class Datetime extends Date
{
	/**
	 * @return string
	 */
	public static function getType()
	{
		return FieldType::DATETIME;
	}

	public static function compareValues($valueA, $valueB)
	{
		$valueA = \CBPHelper::makeTimestamp($valueA, true);
		$valueB = \CBPHelper::makeTimestamp($valueB, true);

		return Base::compareValues($valueA, $valueB);
	}
}