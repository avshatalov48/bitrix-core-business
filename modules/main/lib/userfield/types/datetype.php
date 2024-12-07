<?php

namespace Bitrix\Main\UserField\Types;

use Bitrix\Main;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Type;
use CDatabase;
use CLang;
use CUserTypeManager;

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
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		return $helper->getColumnTypeByField(new \Bitrix\Main\ORM\Fields\DateField('x'));
	}

	/**
	 * @param array $userField
	 * @return array
	 */
	public static function prepareSettings(array $userField): array
	{
		$def = ($userField['SETTINGS']['DEFAULT_VALUE'] ?? null);
		$value = '';

		if(!is_array($def))
		{
			$def = ['TYPE' => static::TYPE_NONE, 'VALUE' => $value];
		}
		elseif(isset($def['TYPE']) && $def['TYPE'] === static::TYPE_FIXED)
		{
			$dateObject = \DateTime::createFromFormat('Y-m-d', $def['VALUE']);
			if (!$dateObject || $dateObject->format('Y-m-d') !== $def['VALUE'])
			{
				$def['VALUE'] = CDatabase::FormatDate(
					$def['VALUE'],
					CLang::GetDateFormat(static::FORMAT_TYPE_SHORT),
					'YYYY-MM-DD'
				);
			}
		}
		elseif(isset($def['TYPE']) && $def['TYPE'] === static::TYPE_NOW)
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
		if(is_string($value) && $value !== '' && !Type\Date::isCorrect($value))
		{
			$msg[] = [
				'id' => $userField['FIELD_NAME'],
				'text' => Loc::getMessage('USER_TYPE_D_ERROR',
					[
						'#FIELD_NAME#' => HtmlFilter::encode(
							$userField['EDIT_FORM_LABEL'] !== ''
								? $userField['EDIT_FORM_LABEL']
								: $userField['FIELD_NAME']
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
	 * @param array|null $userField
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

		return $fieldName . ', ' . $DB->DateToCharFunction($fieldName, static::FORMAT_TYPE_SHORT);
	}

	/**
	 * @param array $userField
	 * @param array $additionalParameters
	 * @return mixed
	 */
	public static function getDefaultValue(array $userField, array $additionalParameters = [])
	{
		$userField['ENTITY_VALUE_ID'] = 0;
		$value = static::getFieldValue($userField, $additionalParameters);
		return ($userField['MULTIPLE'] === 'Y' ? [$value] : $value);
	}

	/**
	 * @param array $userField
	 * @param array $additionalParameters
	 * @return mixed
	 */
	public static function getFieldValue(array $userField, array $additionalParameters = [])
	{
		$bVarsFromForm = ($additionalParameters['bVarsFromForm'] ?? false);
		if(!$bVarsFromForm)
		{
			if(
				isset($userField['ENTITY_VALUE_ID'])
				&& $userField['ENTITY_VALUE_ID'] <= 0
			)
			{
				if($userField['SETTINGS']['DEFAULT_VALUE']['TYPE'] === self::TYPE_NOW)
				{
					$value = \ConvertTimeStamp(time(), self::FORMAT_TYPE_SHORT);
				}
				else
				{
					$value = \CDatabase::formatDate(
						$userField['SETTINGS']['DEFAULT_VALUE']['VALUE'],
						'YYYY-MM-DD',
						\CLang::getDateFormat(self::FORMAT_TYPE_SHORT)
					);
				}
			} else {
				$value = $userField['VALUE'] ?? null;
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
