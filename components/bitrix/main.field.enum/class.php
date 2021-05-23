<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Component\BaseUfComponent;
use Bitrix\Main\UserField\Types\EnumType;
use Bitrix\Main\Context;

/**
 * Class EnumUfComponent
 */
class EnumUfComponent extends BaseUfComponent
{
	public const MAX_OPTION_LENGTH = 40;

	protected static function getUserTypeId(): string
	{
		return EnumType::USER_TYPE_ID;
	}

	/**
	 * @return array
	 */
	protected function getFieldValue(): array
	{
		return self::normalizeFieldValue(
			EnumType::getFieldValue(($this->userField ?: []), $this->additionalParameters)
		);
	}
}