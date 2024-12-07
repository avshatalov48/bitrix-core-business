<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var $component HighloadblockElementUfComponent
 * @var array $arResult
 */

use Bitrix\Main\Context;

$component = $this->getComponent();

$multiple = $component->isMultiple();
$settingsName = (string)$component->getAdditionalParameter('NAME');

$settings = [];
if ($component->getAdditionalParameter('bVarsFromForm') ?? false)
{
	$request = Context::getCurrent()->getRequest();
	if ($settingsName !== '')
	{
		$settings = $request->get($settingsName);
	}
	unset($request);
}
else
{
	$userField = $component->getUserField();
	$settings = $userField['SETTINGS'] ?? [];
	unset($userField);
}
if (!is_array($settings))
{
	$settings = [];
}
$settings = \CUserTypeHlblock::verifySettings($settings, $multiple);

$arResult['settingsName'] = $settingsName;
$arResult['multiple'] = $multiple;
$arResult['settings'] = [
	'display' => $settings['DISPLAY'],
	'listHeight' => $settings['LIST_HEIGHT'],
	'hlblockId' => $settings['HLBLOCK_ID'],
	'hlfieldId' => $settings['HLFIELD_ID'],
	'defaultValue' => $settings['DEFAULT_VALUE'],
];
