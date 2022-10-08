<?php

$location_default_option = [
	'address_format_code' => getDefaultAddressFormatCode(),
	'log_level' => 400 // Error
];

/**
 * Copy & paste from \Bitrix\Location\Infrastructure\FormatCode::getDefault()
 * The reason is cycling during the module installation
 * @param mixed|string $languageId
 * @return string
 */
function getDefaultAddressFormatCode(string $languageId = LANGUAGE_ID): string
{
	switch ($languageId)
	{
		case 'kz':
			$result = 'RU_2';
			break;

		case 'de':
			$result = 'DE';
			break;

		case 'en':
			$result = 'US';
			break;

		//case 'ru':
		//case 'by':
		//case 'ua':
		default:
			$result = 'RU';
	}

	return $result;
}