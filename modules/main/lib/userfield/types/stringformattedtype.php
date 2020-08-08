<?php

namespace Bitrix\Main\UserField\Types;

use Bitrix\Main\Localization\Loc;
use CUserTypeManager;

Loc::loadMessages(__FILE__);

/**
 * Class StringFormattedType
 * @package Bitrix\Main\UserField\Types
 */
class StringFormattedType extends StringType
{
	public const
		USER_TYPE_ID = 'string_formatted',
		RENDER_COMPONENT = 'bitrix:main.field.stringformatted';

	public static function getDescription(): array
	{
		return [
			'DESCRIPTION' => Loc::getMessage('USER_TYPE_STRINGFMT_DESCRIPTION'),
			'BASE_TYPE' => CUserTypeManager::BASE_TYPE_STRING,
		];
	}

	/**
	 * @param array $userField
	 * @return array
	 */
	public static function prepareSettings(array $userField): array
	{
		$size = (int)$userField['SETTINGS']['SIZE'];
		$rows = (int)$userField['SETTINGS']['ROWS'];
		$min = (int)$userField['SETTINGS']['MIN_LENGTH'];
		$max = (int)$userField['SETTINGS']['MAX_LENGTH'];

		return [
			'SIZE' => ($size <= 1 ? 20 : ($size > 255 ? 225 : $size)),
			'ROWS' => ($rows <= 1 ? 1 : ($rows > 50 ? 50 : $rows)),
			'REGEXP' => $userField['SETTINGS']['REGEXP'],
			'MIN_LENGTH' => $min,
			'MAX_LENGTH' => $max,
			'DEFAULT_VALUE' => $userField['SETTINGS']['DEFAULT_VALUE'],
			'PATTERN' => $userField['SETTINGS']['PATTERN'],
		];
	}

	/**
	 * @param array|null $userField
	 * @param array|null $additionalParameters
	 * @return string|null
	 */
	public static function getPublicViewHtml(?array $userField, ?array $additionalParameters = []): ?string
	{
		$val = $additionalParameters['VALUE'];
		if (trim($val) === '')
		{
			return null;
		}

		return htmlspecialcharsEx($val);
	}
}