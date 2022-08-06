<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\UserField\Types\StringFormattedType;

CJSCore::init(['uf']);

/**
 * @var $arResult array
 */

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
						'SETTINGS' => $arResult['userField']['SETTINGS'],
					],
					[
						'NAME' => $name,
						'VALUE' => $value,
					]
				),
			],
			$arResult['userField']['SETTINGS']['PATTERN']
		);
	}
}
