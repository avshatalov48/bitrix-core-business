<?php

namespace Bitrix\Main\UserField\Types;

use Bitrix\Main\Localization\Loc;
use CUserTypeManager;

Loc::loadMessages(__FILE__);

/**
 * Class UrlType
 * @package Bitrix\Main\UserField\Types
 */
class UrlType extends StringType
{
	public const
		USER_TYPE_ID = 'url',
		RENDER_COMPONENT = 'bitrix:main.field.url';

	public static function getDescription(): array
	{
		return array(
			'DESCRIPTION' => GetMessage('USER_TYPE_URL_DESCRIPTION'),
			'BASE_TYPE' => CUserTypeManager::BASE_TYPE_STRING
		);
	}

	/**
	 * @param array $userField
	 * @return array
	 */
	public static function prepareSettings(array $userField): array
	{
		$popup = ($userField['SETTINGS']['POPUP'] === 'N' ? 'N' : 'Y');
		$size = (int)$userField['SETTINGS']['SIZE'];
		$min = (int)$userField['SETTINGS']['MIN_LENGTH'];
		$max = (int)$userField['SETTINGS']['MAX_LENGTH'];

		return [
			'POPUP' => $popup,
			'SIZE' => ($size <= 1 ? 20 : ($size > 255 ? 225 : $size)),
			'MIN_LENGTH' => $min,
			'MAX_LENGTH' => $max,
			'DEFAULT_VALUE' => $userField['SETTINGS']['DEFAULT_VALUE'],
		];
	}

	/**
	 * @param array|null $userField
	 * @param $value
	 * @return string
	 */
	public static function onBeforeSave(?array $userField, $value)
	{
		$value = (string)$value;
		return ($value !== '' ? trim($value) : $value);
	}
}