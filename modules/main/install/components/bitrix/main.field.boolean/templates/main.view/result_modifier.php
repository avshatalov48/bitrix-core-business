<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Page\Asset;
use Bitrix\Main\UserField\Types\BooleanType;
use Bitrix\Main\Text\HtmlFilter;

/**
 * @var $component BooleanUfComponent
 */

$label = BooleanType::getLabels($arResult['userField']);

$value = (int)$arResult['userField']['VALUE'];
$valueTitle = HtmlFilter::encode($value ? $label[1] : $label[0]);

$arResult['value'] = $value;
$arResult['valueTitle'] = $valueTitle;

CJSCore::init(['uf']);

$component = $this->getComponent();
if($component->isMobileMode())
{
	Asset::getInstance()->addJs(
		'/bitrix/js/mobile/userfield/mobile_field.js'
	);
	Asset::getInstance()->addJs(
		'/bitrix/components/bitrix/main.field.boolean/templates/main.view/mobile.js'
	);
}