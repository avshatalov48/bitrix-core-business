<?php

namespace Bitrix\Main\UserField\Types;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use CUserTypeManager;

Loc::loadMessages(__FILE__);

/**
 * Class IntegerType
 * @package Bitrix\Main\UserField\Types
 */
class IntegerType extends BaseType
{
	public const
		USER_TYPE_ID = 'integer',
		RENDER_COMPONENT = 'bitrix:main.field.integer';

	/**
	 * @return array
	 */
	public static function getDescription(): array
	{
		return [
			'DESCRIPTION' => GetMessage('USER_TYPE_INTEGER_DESCRIPTION'),
			'BASE_TYPE' => CUserTypeManager::BASE_TYPE_INT
		];
	}

	/**
	 * @return string
	 */
	public static function getDbColumnType(): string
	{
		return 'int(18)';
	}

	/**
	 * @param array $userField
	 * @return array
	 */
	public static function prepareSettings(array $userField): array
	{
		$size = (int)$userField['SETTINGS']['SIZE'];
		$min = (int)$userField['SETTINGS']['MIN_VALUE'];
		$max = (int)$userField['SETTINGS']['MAX_VALUE'];
		$default = (
		$userField['SETTINGS']['DEFAULT_VALUE'] !== '' ?
			(int)$userField['SETTINGS']['DEFAULT_VALUE'] : ''
		);

		return [
			'SIZE' => ($size <= 1 ? 20 : ($size > 255 ? 225 : $size)),
			'MIN_VALUE' => $min,
			'MAX_VALUE' => $max,
			'DEFAULT_VALUE' => $default
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
	 * @param $value
	 * @return array
	 */
	public static function checkFields(array $userField, $value): array
	{
		$fieldName = HtmlFilter::encode(
			$userField['EDIT_FORM_LABEL'] <> ''
				? $userField['EDIT_FORM_LABEL'] : $userField['FIELD_NAME']
		);

		$msg = [];
		if(
			$value !== ''
			&& $userField['SETTINGS']['MIN_VALUE'] > 0
			&& (int)$value < $userField['SETTINGS']['MIN_VALUE']
		)
		{
			$msg[] = [
				'id' => $userField['FIELD_NAME'],
				'text' => Loc::getMessage('USER_TYPE_INTEGER_MIN_VALUE_ERROR',
					[
						'#FIELD_NAME#' => $fieldName,
						'#MIN_VALUE#' => $userField['SETTINGS']['MIN_VALUE']
					]
				),
			];
		}
		if(
			$value !== ''
			&& $userField['SETTINGS']['MAX_VALUE'] != 0
			&& (int)$value > $userField['SETTINGS']['MAX_VALUE']
		)
		{
			$msg[] = [
				'id' => $userField['FIELD_NAME'],
				'text' => Loc::getMessage('USER_TYPE_INTEGER_MAX_VALUE_ERROR',
					[
						'#FIELD_NAME#' => $fieldName,
						'#MAX_VALUE#' => $userField['SETTINGS']['MAX_VALUE']
					]
				),
			];
		}
		if(
			$value != ''
			&& !preg_match('/^[-+]?\d+$/', $value)
		)
		{
			$msg[] = [
				'id' => $userField['FIELD_NAME'],
				'text' => Loc::getMessage('USER_TYPE_INTEGER_TYPE_ERROR',
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
}