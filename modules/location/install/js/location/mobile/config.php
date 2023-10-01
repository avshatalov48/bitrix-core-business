<?php

use Bitrix\Location\Service\FormatService;
use Bitrix\Location\Service\SourceService;
use Bitrix\Main\SystemException;
use Bitrix\Main\Loader;
use Bitrix\Location\Infrastructure\UserLocation;
use Bitrix\Main\Web\Json;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/mobile.bundle.css',
	'js' => 'dist/mobile.bundle.js',
	'rel' => [
		'location.core',
		'location.source',
		'main.loader',
		'main.core',
		'ui.design-tokens',
	],
	'skip_core' => false,
	'oninit' => static function()
	{
		if (!Loader::includeModule('location'))
		{
			throw new SystemException('Module Location have not been installed');
		}

		$sourceCode = '';
		$sourceParams = [];
		$sourceLanguageId = LANGUAGE_ID;

		if ($source = SourceService::getInstance()->getSource())
		{
			$sourceCode = $source->getCode();
			$sourceParams = $source->getJSParams();
			$sourceLanguageId = $source->convertLang(LANGUAGE_ID);
			$sourceParams['autocompleteReplacements'] = $source->getAutocompleteReplacements(LANGUAGE_ID);
		}

		$format = FormatService::getInstance()->findDefault(LANGUAGE_ID);
		$format  = $format ? $format->toJson() : '';

		$defaultLocationPoint = UserLocation::getPoint();

		return [
			'lang_additional' => [
				'LOCATION_MOBILE_SOURCE_CODE' => $sourceCode,
				'LOCATION_MOBILE_SOURCE_PARAMS' => $sourceParams,
				'LOCATION_WIDGET_DEFAULT_FORMAT' => $format,
				'LOCATION_MOBILE_LANGUAGE_ID' => LANGUAGE_ID,
				'LOCATION_MOBILE_SOURCE_LANGUAGE_ID' => $sourceLanguageId,
				'LOCATION_MOBILE_DEFAULT_LOCATION_POINT' => Json::encode([
					'latitude' => $defaultLocationPoint->getLat(),
					'longitude' => $defaultLocationPoint->getLng()
				]),
			]
		];
	}
];
