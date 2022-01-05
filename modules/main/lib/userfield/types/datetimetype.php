<?php

namespace Bitrix\Main\UserField\Types;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use CLang;
use CUserTypeManager;
use Bitrix\Main;
use Bitrix\Main\Type;
use Bitrix\Main\Context;

Loc::loadMessages(__FILE__);

/**
 * Class DateTimeType
 * @package Bitrix\Main\UserField\Types
 */
class DateTimeType extends DateType
{
	public const
		USER_TYPE_ID = 'datetime',
		RENDER_COMPONENT = 'bitrix:main.field.datetime';

	/**
	 * @return array
	 */
	public static function getDescription(): array
	{
		return [
			'DESCRIPTION' => Loc::GetMessage('USER_TYPE_DT_DESCRIPTION'),
			'BASE_TYPE' => CUserTypeManager::BASE_TYPE_DATETIME,
		];
	}

	/**
	 * @return string
	 */
	public static function getDbColumnType(): string
	{
		return 'datetime';
	}

	/**
	 * @param array $userField
	 * @return array
	 */
	public static function prepareSettings(array $userField): array
	{
		$def = $userField['SETTINGS']['DEFAULT_VALUE'];
		$value = '';

		if(!is_array($def))
		{
			$def = ['TYPE' => static::TYPE_NONE, 'VALUE' => $value];
		}
		elseif($def['TYPE'] === static::TYPE_FIXED)
		{
			$def['VALUE'] = \CDatabase::FormatDate(
				$def['VALUE'],
				CLang::GetDateFormat(static::FORMAT_TYPE_FULL),
				'YYYY-MM-DD HH:MI:SS'
			);
		}
		elseif($def['TYPE'] === static::TYPE_NOW)
		{
			$def['VALUE'] = $value;
		}
		else
		{
			$def = ['TYPE' => static::TYPE_NONE, 'VALUE' => $value];
		}

		return [
			'DEFAULT_VALUE' => $def,
			'USE_SECOND' => ($userField['SETTINGS']['USE_SECOND'] === 'N' ? 'N' : 'Y'),
			'USE_TIMEZONE' => ($userField['SETTINGS']['USE_TIMEZONE'] === 'Y' ? 'Y' : 'N'),
		];
	}

	/**
	 * @param array|null $userField
	 * @param array $additionalParameters
	 * @return array
	 */
	public static function getFilterData(?array $userField, array $additionalParameters): array
	{
		return [
			'id' => $additionalParameters['ID'],
			'name' => $additionalParameters['NAME'],
			'type' => 'date',
			'time' => true
		];
	}

	/**
	 * @param array $userField
	 * @param string|array $value
	 * @return array
	 */
	public static function checkFields(array $userField, $value): array
	{
		$msg = [];
		$value = (string)$value;
		if($value !== '')
		{
			try
			{
				Type\DateTime::createFromUserTime($value);
			} catch(Main\ObjectException $e)
			{
				$msg[] = [
					'id' => $userField['FIELD_NAME'],
					'text' => Loc::GetMessage('USER_TYPE_DT_ERROR',
						[
							'#FIELD_NAME#' => HtmlFilter::encode(
								$userField['EDIT_FORM_LABEL'] <> ''
									? $userField['EDIT_FORM_LABEL'] : $userField['FIELD_NAME']
							),
						]
					),
				];
			}
		}
		return $msg;
	}

	/**
	 * Returns string time in user timezone
	 * @param array $userField
	 * @param array $fetched
	 * @return string|null
	 */
	public static function onAfterFetch(array $userField, array $fetched): string
	{
		$value = $fetched['VALUE'];

		if($userField['MULTIPLE'] === 'Y' && !($value instanceof Type\DateTime))
		{
			//Invalid value
			if(mb_strlen($value) <= 1)
			{
				//will be ignored by the caller
				return '';
			}

			try
			{
				//try new independent datetime format
				$value = new Type\DateTime(
					$value,
					\Bitrix\Main\UserFieldTable::MULTIPLE_DATETIME_FORMAT
				);
			} catch(Main\ObjectException $e)
			{
				//try site format
				try
				{
					$value = new Type\DateTime($value);
				} catch(Main\ObjectException $e)
				{
					//try short format
					$value = Type\DateTime::createFromUserTime($value);
				}
			}
		}

		$isFieldWithoutTimeZone = static::isFieldWithoutTimeZone($userField);

		if ($isFieldWithoutTimeZone)
		{
			\CTimeZone::Disable();
		}

		$value = (string)$value;

		if ($isFieldWithoutTimeZone)
		{
			\CTimeZone::Enable();
		}

		return $value;
	}


	/**
	 * Returns  time object in server timezone
	 * @param array|null $userField
	 * @param $value
	 * @return Type\Date|Type\DateTime
	 */
	public static function onBeforeSave(?array $userField, $value)
	{
		if($value != '' && !($value instanceof Type\DateTime))
		{
			$isFieldWithoutTimeZone = static::isFieldWithoutTimeZone($userField);

			if ($isFieldWithoutTimeZone)
			{
				\CTimeZone::Disable();
			}

			$value = Type\DateTime::createFromUserTime($value);

			if ($isFieldWithoutTimeZone)
			{
				\CTimeZone::Enable();
			}
		}

		return $value;
	}

	/**
	 * @param string $value
	 * @param array $userField
	 * @return string
	 */
	public static function getFormat(string $value, array $userField): string
	{
		$format = CLang::GetDateFormat(static::FORMAT_TYPE_FULL);

		if($userField['SETTINGS']['USE_SECOND'] === 'N' && MakeTimeStamp($value) % 60 <= 0)
		{
			$format = str_replace(':SS', '', $format);
		}

		return $format;
	}

	/**
	 * @param array|null $userField
	 * @param string $fieldName
	 * @return string
	 */
	public static function formatField(?array $userField, string $fieldName): string
	{
		$isFieldWithoutTimeZone = static::isFieldWithoutTimeZone($userField);

		if ($isFieldWithoutTimeZone)
		{
			\CTimeZone::Disable();
		}

		global $DB;
		$date = $DB->dateToCharFunction($fieldName, static::FORMAT_TYPE_FULL);

		if ($isFieldWithoutTimeZone)
		{
			\CTimeZone::Enable();
		}

		return $date;
	}

	/**
	 * @param array|null $userField
	 * @return bool
	 */
	protected static function isFieldWithoutTimeZone(?array $userField): bool
	{
		return (
			isset($userField['SETTINGS']['USE_TIMEZONE'])
			&&
			$userField['SETTINGS']['USE_TIMEZONE']==='N'
			&&
			\CTimeZone::Enabled()
		);
	}

	/**
	 * @param array $userField
	 * @param Type\DateTime $dateTime
	 * @return string
	 */
	public static function charToDate(array $userField, Type\DateTime $dateTime): string
	{
		$isFieldWithoutTimeZone = static::isFieldWithoutTimeZone($userField);

		if ($isFieldWithoutTimeZone)
		{
			\CTimeZone::Disable();
		}

		global $DB;
		$value = $DB->CharToDateFunction($dateTime);

		if ($isFieldWithoutTimeZone)
		{
			\CTimeZone::Enable();
		}

		return (string) $value;
	}

	public static function getFieldValue(array $userField, array $additionalParameters = [])
	{
		if(!$additionalParameters['bVarsFromForm'])
		{
			if(
				isset($userField['ENTITY_VALUE_ID'])
				&&
				$userField['ENTITY_VALUE_ID'] <= 0
			)
			{
				if($userField['SETTINGS']['DEFAULT_VALUE']['TYPE'] === self::TYPE_NOW)
				{
					$value = \ConvertTimeStamp(
						time() + \CTimeZone::getOffset(),
						self::FORMAT_TYPE_FULL
					);
				}
				else
				{
					$value = str_replace(
						' 00:00:00',
						'',
						\CDatabase::formatDate(
							$userField['SETTINGS']['DEFAULT_VALUE']['VALUE'],
							'YYYY-MM-DD HH:MI:SS',
							\CLang::getDateFormat(self::FORMAT_TYPE_FULL)
						)
					);
				}
			}
			else
			{
				$value = $userField['VALUE'];
			}
		}
		elseif(isset($additionalParameters['VALUE']))
		{
			$value = $additionalParameters['VALUE'];
		}
		else
		{
			$value = Context::getCurrent()->getRequest()->get($userField['FIELD_NAME']);
		}

		return $value;
	}
}
