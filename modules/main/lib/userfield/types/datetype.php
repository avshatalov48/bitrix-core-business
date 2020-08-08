<?php

namespace Bitrix\Main\UserField\Types;

use Bitrix\Main\Localization\Loc;
use CUserTypeManager;
use Bitrix\Main;
use Bitrix\Main\Type;
use CLang;
use CDatabase;
use Bitrix\Main\Text\HtmlFilter;

Loc::loadMessages(__FILE__);

/**
 * Class DateType
 * @package Bitrix\Main\UserField\Types
 */
class DateType extends BaseType
{
	public const
		USER_TYPE_ID = 'date',
		RENDER_COMPONENT = 'bitrix:main.field.date';

	public const
		TYPE_NONE = 'NONE',
		TYPE_FIXED = 'FIXED',
		TYPE_NOW = 'NOW';

	public const
		FORMAT_TYPE_FULL = 'FULL',
		FORMAT_TYPE_SHORT = 'SHORT';

	/**
	 * @return array
	 */
	public static function getDescription(): array
	{
		return [
			'DESCRIPTION' => Loc::getMessage('USER_TYPE_D_DESCRIPTION'),
			'BASE_TYPE' => CUserTypeManager::BASE_TYPE_DATETIME,
		];
	}

	/**
	 * @return string
	 */
	public static function getDbColumnType(): string
	{
		return 'date';
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
			$def['VALUE'] = CDatabase::FormatDate(
				$def['VALUE'],
				CLang::GetDateFormat(static::FORMAT_TYPE_SHORT),
				'YYYY-MM-DD'
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
			'type' => 'date'
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
		if(is_string($value) && $value !== '' && !CheckDateTime($value, FORMAT_DATE))
		{
			$msg[] = [
				'id' => $userField['FIELD_NAME'],
				'text' => Loc::getMessage('USER_TYPE_D_ERROR',
					[
						'#FIELD_NAME#' => HtmlFilter::encode(
							$userField['EDIT_FORM_LABEL'] <> ''
                                ? $userField['EDIT_FORM_LABEL'] : $userField['FIELD_NAME']
						),
					]
				),
			];
		}
		return $msg;
	}

	/**
	 * @param array $userField
	 * @param array $fetched
	 * @return string
	 * @throws Main\ObjectException
	 */
	public static function onAfterFetch(array $userField, array $fetched): string
	{
		$value = $fetched['VALUE'];

		if($userField['MULTIPLE'] === 'Y' && !($value instanceof Type\Date))
		{
			try
			{
				//try new independent date format
				$value = new Type\Date(
					$value,
					\Bitrix\Main\UserFieldTable::MULTIPLE_DATE_FORMAT
				);
			} catch(Main\ObjectException $e)
			{
				// try site format (sometimes it can be full site format)
				try
				{
					$value = new Type\Date($value);
				} catch(Main\ObjectException $e)
				{
					$value = new Type\Date($value, Type\DateTime::getFormat());
				}
			}
		}

		return (string)$value;
	}

	/**
	 * @param array $userField
	 * @param $value
	 * @return Type\Date
	 * @throws Main\ObjectException
	 */
	public static function onBeforeSave(?array $userField, $value)
	{
		if($value != '' && !($value instanceof Type\Date))
		{
			// try both site's format - short and full
			try
			{
				$value = new Type\Date($value);
			} catch(Main\ObjectException $e)
			{
				$value = new Type\Date($value, Type\DateTime::getFormat());
			}
		}

		return $value;
	}

	/**
	 * @param array|null $userField
	 * @param string $fieldName
	 * @return string
	 */
	public static function formatField(?array $userField, string $fieldName): string
	{
		global $DB;
		return $DB->DateToCharFunction($fieldName, static::FORMAT_TYPE_SHORT);
	}
}