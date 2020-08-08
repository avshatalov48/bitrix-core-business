<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Component\BaseUfComponent;
use Bitrix\Main\Context;
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
		if(!$this->additionalParameters['bVarsFromForm'])
		{
			if(
				isset($this->userField['ENTITY_VALUE_ID'])
				&&
				$this->userField['ENTITY_VALUE_ID'] <= 0
			)
			{
				if($this->userField['SETTINGS']['DEFAULT_VALUE']['TYPE'] === DateTimeType::TYPE_NOW)
				{
					$value = \ConvertTimeStamp(
						time() + \CTimeZone::getOffset(),
						DateTimeType::FORMAT_TYPE_FULL
					);
				}
				else
				{
					$value = str_replace(
						' 00:00:00',
						'',
						\CDatabase::formatDate(
							$this->userField['SETTINGS']['DEFAULT_VALUE']['VALUE'],
							'YYYY-MM-DD HH:MI:SS',
							\CLang::getDateFormat(DateTimeType::FORMAT_TYPE_FULL)
						)
					);
				}
			}
			else
			{
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