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

	/**
	 * @param bool $withoutEmptyValue
	 * @return array[]
	 */
	public function getItems(bool $withoutEmptyValue = false): array
	{
		$items = [];

		foreach($this->userField['USER_TYPE']['~FIELDS'] as $key => $value)
		{
			if($key === '' && ($this->isMultiple() || $withoutEmptyValue))
			{
				continue;
			}

			$items[] = [
				'NAME' => $value,
				'VALUE' => $key,
				'IS_SELECTED' => in_array($key, $this->arResult['value']),
			];
		}

		return $items;
	}
}
