<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Page\Asset;

/**
 * @var $component EnumUfComponent
 */

$component = $this->getComponent();

CJSCore::init(['uf']);

$arResult['isEnabled'] = ($arResult['userField']['EDIT_IN_LIST'] === 'Y');

if($component->isMobileMode())
{
	Asset::getInstance()->addJs(
		'/bitrix/js/mobile/userfield/mobile_field.js'
	);
	Asset::getInstance()->addJs(
		'/bitrix/components/bitrix/main.field.enum/templates/main.view/mobile.js'
	);
}