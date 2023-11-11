<?php

namespace Bitrix\Main\UserField\Types;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use CUserTypeManager;

Loc::loadMessages(__FILE__);

/**
 * Class DoubleType
 * @package Bitrix\Main\UserField\Types
 */
class DoubleType extends BaseType
{
	public const
		USER_TYPE_ID = 'double',
		RENDER_COMPONENT = 'bitrix:main.field.double';

	/**
	 * @return array
	 */
	public static function getDescription(): array
	{
		return [
			'DESCRIPTION' => GetMessage('USER_TYPE_DOUBLE_DESCRIPTION'),
			'BASE_TYPE' => CUserTypeManager::BASE_TYPE_DOUBLE,
		];
	}

	/**
	 * @return string
	 */
	public static function getDbColumnType(): string
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		return $helper->getColumnTypeByField(new \Bitrix\Main\ORM\Fields\FloatField('x'));
	}

	/**
	 * @param array $userField
	 * @return array
	 */
	public static function prepareSettings(array $userField): array
	{
		$prec = (int)$userField['SETTINGS']['PRECISION'];
		$size = (int)$userField['SETTINGS']['SIZE'];
		$min = (double)$userField['SETTINGS']['MIN_VALUE'];
		$max = (double)$userField['SETTINGS']['MAX_VALUE'];

		return [
			'PRECISION' => ($prec < 0 ? 0 : ($prec > 12 ? 12 : $prec)),
			'SIZE' => ($size <= 1 ? 20 : ($size > 255 ? 225 : $size)),
			'MIN_VALUE' => $min,
			'MAX_VALUE' => $max,
			'DEFAULT_VALUE' => $userField['SETTINGS']['DEFAULT_VALUE'] <> ''
				? (double)$userField['SETTINGS']['DEFAULT_VALUE']
				: null
			,
		];
	}

	/**
	 * @param array|null $userField
	 * @param array $additionalSettings
	 * @return array
	 */
	public static function getFilterData(?array $userField, array $additionalSettings): array
	{
		return [
			'id' => $additionalSettings['ID'],
			'name' => $additionalSettings['NAME'],
			'type' => 'number',
			'filterable' => ''
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

		$value = str_replace([',', ' '], ['.', ''], $value);

		$fieldName = HtmlFilter::encode(
			$userField['EDIT_FORM_LABEL'] <> ''
				? $userField['EDIT_FORM_LABEL'] : $userField['FIELD_NAME']
		);

		if(
			mb_strlen($value)
			&& $userField['SETTINGS']['MIN_VALUE'] != 0
			&& (double)$value < $userField['SETTINGS']['MIN_VALUE']
		)
		{
			$msg[] = [
				'id' => $userField['FIELD_NAME'],
				'text' => Loc::getMessage(
					'USER_TYPE_DOUBLE_MIN_VALUE_ERROR',
					[
						'#FIELD_NAME#' => $fieldName,
						'#MIN_VALUE#' => $userField['SETTINGS']['MIN_VALUE']
					]
				)
			];
		}
		if(
			mb_strlen($value)
			&& $userField['SETTINGS']['MAX_VALUE'] != 0
			&& (double)$value > $userField['SETTINGS']['MAX_VALUE']
		)
		{
			$msg[] = [
				'id' => $userField['FIELD_NAME'],
				'text' => Loc::getMessage(
					'USER_TYPE_DOUBLE_MAX_VALUE_ERROR',
					[
						'#FIELD_NAME#' => $fieldName,
						'#MAX_VALUE#' => $userField['SETTINGS']['MAX_VALUE']
					]
				),
			];
		}
		if(
			$value != ''
			&& !preg_match('/^[-+]?\d*[.,]?\d+?$/', $value)
		)
		{
			$msg[] = [
				'id' => $userField['FIELD_NAME'],
				'text' => Loc::getMessage('USER_TYPE_DOUBLE_TYPE_ERROR',
					[
						'#FIELD_NAME#' => $fieldName
					]
				),
			];
		}
		return $msg;
	}

	/**
	 * @param array $userField
	 * @return string|null
	 */
	public static function onSearchIndex(array $userField): ?string
	{
		if(is_array($userField['VALUE']))
		{
			$result = implode('\r\n', $userField['VALUE']);
		}
		else
		{
			$result = $userField['VALUE'];
		}
		return $result;
	}

	/**
	 * @param array $userField
	 * @param $value
	 * @return string|null
	 */
	public static function onBeforeSave(array $userField, $value)
	{
		$value = str_replace([',', ' '], ['.', ''], $value);
		if($value <> '')
		{
			return (string) round((double)$value, $userField['SETTINGS']['PRECISION']);
		}
		return null;
	}
}