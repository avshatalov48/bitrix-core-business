<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Component\BaseUfComponent;
use Bitrix\Main\UserField\Types\DateTimeType;

/**
 * Class DateTimeUfComponent
 */
class DateTimeUfComponent extends BaseUfComponent
{
	protected static function getUserTypeId(): string
	{
		return DateTimeType::USER_TYPE_ID;
	}

	/**
	 * @return array
	 */
	protected function getFieldValue(): array
	{
		return self::normalizeFieldValue(
			DateTimeType::getFieldValue(($this->userField ?: []), $this->additionalParameters)
		);
	}
}