<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Calendar\Integration\Bitrix24\FeatureDictionary;
use Bitrix\Calendar\Integration\Bitrix24Manager;
use Bitrix\Calendar\Util;
use Bitrix\Main\Localization\Loc;
use Bitrix\UI\Buttons;
use Bitrix\UI\Toolbar\ButtonLocation;

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/calendar/classes/general/calendar_js.php");

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @var CBitrixComponent $component */

$APPLICATION->SetPageProperty('BodyClass', $APPLICATION->GetPageProperty('BodyClass').' pagetitle-toolbar-field-view calendar-pagetitle-view');

$isBitrix24Template = (SITE_TEMPLATE_ID === 'bitrix24');
if (!$isBitrix24Template)
{
	\Bitrix\UI\Toolbar\Facade\Toolbar::hideTitle();
	$APPLICATION->IncludeComponent('bitrix:ui.toolbar', '', []);
}

if ($arParams['SHOW_FILTER'])
{
	$filterOption = new \Bitrix\Main\UI\Filter\Options($arParams["FILTER_ID"]);
	$filterOption->reset();

	\Bitrix\UI\Toolbar\Facade\Toolbar::addFilter([
		"FILTER_ID" => $arParams["FILTER_ID"],
		"FILTER" => $arParams["FILTER"],
		"FILTER_PRESETS" => $arParams["FILTER_PRESETS"],
		"ENABLE_LABEL" => true,
		'ENABLE_LIVE_SEARCH' => true,
		"RESET_TO_DEFAULT_MODE" => true,
		"THEME" => $isBitrix24Template ? "DEFAULT" : "BORDER"
	]);
}

if ($arParams["EXTERNAL_DATA_HANDLE_MODE"] ?? null)
{
	return;
}

$userSettings = \Bitrix\Calendar\UserSettings::get();
$isCollaber = Util::isCollabUser($arParams['USER_ID']);

$type = $arParams['CALENDAR_TYPE'];
$locationFeatureEnabled = !$isCollaber && Bitrix24Manager::isFeatureEnabled(FeatureDictionary::CALENDAR_LOCATION);
$isLocationDisabled = !$locationFeatureEnabled && $type === 'location';

if ($isLocationDisabled)
{
	return;
}

[$sectionList, $collabSectionList, $followedSectionList, $roomsList] = \CCalendar::getSectionsInfo($isCollaber);

if ($type === 'location' || !\CCalendar::isReadOnly($sectionList, $collabSectionList))
{
	$addButton = Buttons\CreateButton::create([
		'text' => $type === 'location'
			? Loc::getMessage('EC_RESERVE')
			: Loc::getMessage('EC_CREATE')
		,
		'onclick' => 'openEventCreateSlider',
		'dataset' => [
			'toolbar-collapsed-icon' => \Bitrix\UI\Buttons\Icon::ADD,
		],
	]);

	\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($addButton, ButtonLocation::AFTER_TITLE);
}

if ($type === 'location')
{
	$calendarButton = Buttons\Button::create([
		'size' => Buttons\Size::MEDIUM,
		'text' => Loc::getMessage('EC_SECTION_ROOMS_LIST'),
		'color' => Buttons\Color::LIGHT_BORDER,
		'onclick' => 'openCalendarRoomsList',
		'dataset' => [
			'toolbar-collapsed-icon' => Buttons\Icon::INFO
		],
	]);
}
else
{
	$calendarButton = Buttons\Button::create([
		'size' => Buttons\Size::MEDIUM,
		'text' => Loc::getMessage('EC_SECTION_BUTTON'),
		'color' => Buttons\Color::LIGHT_BORDER,
		'onclick' => 'openCalendarSectionList',
		'dataset' => [
			'toolbar-collapsed-icon' => Buttons\Icon::INFO
		],
	]);
}

\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($calendarButton);

$isOwner = $type === 'user' && $arParams['USER_ID'] === ($arParams['OWNER_ID'] ?? null);

if ($isOwner || \CCalendar::hasTypeAccess())
{
	$settingsButton = Buttons\Button::create([
		'size' => Buttons\Size::MEDIUM,
		'color' => Buttons\Color::LIGHT_BORDER,
		'icon' => Buttons\Icon::SETTINGS,
		'onclick' => 'openCalendarSettings',
		'dataset' => [
			'toolbar-collapsed-icon' => Buttons\Icon::SETTINGS
		],
	]);

	\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($settingsButton);
}
