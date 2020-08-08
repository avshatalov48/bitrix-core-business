<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UserField\Types\StringFormattedType;

CJSCore::init(['uf']);

foreach($arResult['value'] as $key => $value)
{
	$name = str_replace('[]', '['.$key.']', $arResult['userField']['FIELD_NAME']);
	if($value !== '')
	{
		$arResult['value'][$key] = str_replace(
			['#VALUE#'],
			[
				StringFormattedType::getPublicViewHtml(
					[
						'SETTINGS' => $arResult['userField']['SETTINGS']
					],
					[
						'NAME' => $name,
						'VALUE' => HtmlFilter::encode($value)
					]
				)
			],
			$arResult['userField']['SETTINGS']['PATTERN']
		);
	}
}
