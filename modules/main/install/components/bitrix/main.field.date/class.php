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
		if(!$this->additionalParameters['bVarsFromForm'])
		{
			if(
				isset($this->userField['ENTITY_VALUE_ID'])
				&&
				$this->userField['ENTITY_VALUE_ID'] <= 0
			)
			{
				if($this->userField['SETTINGS']['DEFAULT_VALUE']['TYPE'] === DateType::TYPE_NOW)
				{
					$value = \ConvertTimeStamp(time(), DateType::FORMAT_TYPE_SHORT);
				}
				else
				{
					$value = \CDatabase::formatDate(
						$this->userField['SETTINGS']['DEFAULT_VALUE']['VALUE'],
						'YYYY-MM-DD',
						\CLang::getDateFormat(DateType::FORMAT_TYPE_SHORT)
					);
				}
			} else {
				$value = $this->userField['VALUE'];
			}
		}
		elseif(isset($this->additionalParameters['VALUE']))
		{
			$value = $this->additionalParameters['VALUE'];
		}
		else
		{
			$value = Context::getCurrent()->getRequest()->get($this->userField['FIELD_NAME']);
		}

		return self::normalizeFieldValue($value);
	}
}