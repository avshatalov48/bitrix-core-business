<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Component\BaseUfComponent;
use Bitrix\Main\UserField\Types\DateType;
use Bitrix\Main\Context;

/**
 * Class DateUfComponent
 */
class DateUfComponent extends BaseUfComponent
{
	protected static function getUserTypeId(): string
	{
		return DateType::USER_TYPE_ID;
	}

	/**
	 * @return array
	 */
	protected function getFieldValue(): array
	{
		return self::normalizeFieldValue(
			DateType::getFieldValue(($this->userField ?: []), $this->additionalParameters)
		);
	}
}