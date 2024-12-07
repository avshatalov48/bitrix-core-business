<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Page\Asset;

CJSCore::init(['uf']);

$arResult['targetBlank'] = ($arResult['userField']['SETTINGS']['TARGET_BLANK'] ?? 'Y');

foreach($arResult['value'] as $key => $value)
{
	if($value)
	{
		$value = (int)$value;
		$tag = '';

		$fileInfo = \CFile::GetFileArray($value);
		if($fileInfo)
		{
			$arResult['value'][$key] = $fileInfo;
		}
	}
}

/**
 * @var $component FileUfComponent
 */

$component = $this->getComponent();

if($component->isMobileMode())
{
	Asset::getInstance()->addJs(
		'/bitrix/js/mobile/userfield/mobile_field.js'
	);
	Asset::getInstance()->addJs(
		'/bitrix/components/bitrix/main.field.file/templates/main.view/mobile.js'
	);
}