<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Fileman\UserField\Types\AddressType;
use Bitrix\Main\Page\Asset;

CJSCore::init(['uf', 'userfield_address', 'google_map']);

$arResult['canUseMap'] = AddressType::canUseMap();
$arResult['useRestriction'] = AddressType::useRestriction();
$arResult['checkRestriction'] = AddressType::checkRestriction();
$arResult['apiKey'] = AddressType::getApiKey();

/**
 * @var $component AddressUfComponent
 */
$component = $this->getComponent();
if ($component->isDefaultMode()){
	Asset::getInstance()->addJs(
		'/bitrix/components/bitrix/fileman.field.address/templates/main.edit/default.js'
	);
}