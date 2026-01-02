<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Calendar\Integration\Bitrix24\FeatureDictionary;
use Bitrix\Calendar\Integration\Bitrix24Manager;
use Bitrix\Calendar\Util;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Web\Uri;
use Bitrix\UI\Buttons;
use Bitrix\UI\Toolbar\ButtonLocation;

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/calendar/classes/general/calendar_js.php");

/** @var $APPLICATION CMain */
/** @var array $arResult */
/** @var array $arParams */
/** @var CBitrixComponent $component */

$isBitrix24Template = (SITE_TEMPLATE_ID === 'bitrix24');
if (!$isBitrix24Template)
{
	\Bitrix\UI\Toolbar\Facade\Toolbar::hideTitle();
	$APPLICATION->IncludeComponent('bitrix:ui.toolbar', '', []);
}

$isCollab = $arResult['IS_COLLAB'];
if ($isCollab)
{
	$collabPostfix = 'calendar-collab-calendar__wrapper';
}

$bodyClass = $APPLICATION->GetPageProperty('BodyClass') . ' pagetitle-toolbar-field-view calendar-pagetitle-view no-background';
if ($isCollab)
{
	$bodyClass .= ' ' . $collabPostfix;
}

$APPLICATION->SetPageProperty('BodyClass', $bodyClass);

$userSettings = Bitrix\Calendar\UserSettings::get();
$isCollaber = Util::isCollabUser($arParams['USER_ID']);

$type = $arParams['CALENDAR_TYPE'];
$locationFeatureEnabled = !$isCollaber && Bitrix24Manager::isFeatureEnabled(FeatureDictionary::CALENDAR_LOCATION);
$isLocationDisabled = !$locationFeatureEnabled && $type === 'location';
$ownerId = (int)($arParams['OWNER_ID'] ?? null);

if ($isLocationDisabled)
{
	return;
}

$showTasks = ($type === 'user' || $type === 'group')
	&& !$isCollaber
	&& $arParams['VIEW_TASK_PATH']
	&& Loader::includeModule('tasks')
	&& $userSettings['showTasks'] !== 'N'
;

[$sectionList, $collabSectionList, $followedSectionList, $roomsList] = \CCalendar::getSectionsInfo($isCollaber);
if ($type === 'location' || !\CCalendar::isReadOnly($sectionList, $collabSectionList))
{
	if ($showTasks)
	{
		$menuItems = [
			[
				'text' => Loc::getMessage('EC_EVENT_BUTTON'),
				'onclick' => new \Bitrix\UI\Buttons\JsHandler('openEventCreateSlider'),
				'dataset' => [
					'id' => 'calendar_menu_event_add',
				],
			],
			[
				'text' => Loc::getMessage('EC_TASK_BUTTON'),
				'href' => (new Uri($arParams['EDIT_TASK_PATH']))->addParams([
					'ta_sec' => 'calendar',
					'ta_el' => 'create_button',
				]),
				'dataset' => [
					'id' => 'calendar_menu_task_add',
				],
			],
		];

		$addButton = Buttons\Split\CreateButton::create([
			'mainButton' => [
				'onclick' => new \Bitrix\UI\Buttons\JsHandler('openEventCreateSlider'),
				'dataset' => [
					'id' => 'calendar_add_btn',
				],
			],
			'menuButton' => [
				'dataset' => [
					'id' => 'calendar_menu_btn',
				],
			],
			'text' => Loc::getMessage('EC_CREATE'),
			'dataset' => [
				'toolbar-collapsed-icon' => \Bitrix\UI\Buttons\Icon::ADD,
			],
		]);

		$addButton->setMenu([
			'items' => $menuItems,
			'closeByEsc' => true,
			'angle' => true,
			'offsetLeft' => 20,
		]);

		$addButton->getAttributeCollection()->addJsonOption(
			'menuTarget',
			\Bitrix\UI\Buttons\Split\Type::MENU
		);
	}
	else
	{
		$addButton = Buttons\CreateButton::create([
			'text' => $type === 'location'
				? Loc::getMessage('EC_RESERVE')
				: Loc::getMessage('EC_CREATE')
			,
			'onclick' => 'openEventCreateSlider',
			'dataset' => [
				'toolbar-collapsed-icon' => \Bitrix\UI\Buttons\Icon::ADD,
				'id' => 'calendar_add_btn',
			],
		]);
	}

	\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($addButton, ButtonLocation::AFTER_TITLE);
}

if ($arParams['SHOW_FILTER'])
{
	\Bitrix\UI\Toolbar\Facade\Toolbar::addFilter([
		'FILTER_ID' => $arParams['FILTER_ID'],
		'FILTER' => $arParams["FILTER"],
		'FILTER_PRESETS' => $arParams["FILTER_PRESETS"],
		'ENABLE_LIVE_SEARCH' => true,
		'ENABLE_LABEL' => true,
		'THEME' => Bitrix\Main\UI\Filter\Theme::MUTED,
	]);
}

if ($type === 'location')
{
	$calendarButton = Buttons\Button::create([
		'size' => Buttons\Size::MEDIUM,
		'text' => Loc::getMessage('EC_SECTION_ROOMS_LIST'),
		'color' => Buttons\Color::LIGHT_BORDER,
		'onclick' => 'openCalendarRoomsList',
		'dataset' => [
			'toolbar-collapsed-icon' => Buttons\Icon::INFO,
			'id' => 'calendar_rooms_btn',
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
			'toolbar-collapsed-icon' => Buttons\Icon::INFO,
			'id' => 'calendar_sections_btn',
		],
	]);
}

\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($calendarButton);

$isOwner = $type === 'user' && (int)$arParams['USER_ID'] === $ownerId;

if ($isOwner || \CCalendar::hasTypeAccess())
{
	$settingsButton = Buttons\Button::create([
		'size' => Buttons\Size::MEDIUM,
		'color' => Buttons\Color::LIGHT_BORDER,
		'icon' => Buttons\Icon::SETTINGS,
		'onclick' => 'openCalendarSettings',
		'dataset' => [
			'toolbar-collapsed-icon' => Buttons\Icon::SETTINGS,
			'id' => 'calendar_settings_btn',
		],
	]);

	\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($settingsButton);
}


// Set title and navigation
if ($isCollab)
{
	\Bitrix\UI\Toolbar\Facade\Toolbar::deleteFavoriteStar();

	$collabName = HtmlFilter::encode($arResult['COLLAB_NAME']);

	\Bitrix\UI\Toolbar\Facade\Toolbar::addBeforeTitleBoxHtml(<<<HTML
		<div class="calendar-collab-icon__wrapper">
			<div id="calendar-collab-icon-$ownerId" class="calendar-collab-icon__hexagon-bg"></div>
		</div>
	HTML);

	\Bitrix\UI\Toolbar\Facade\Toolbar::addUnderTitleHtml(<<<HTML
		<div class="calendar-collab__subtitle" title="$collabName">$collabName</div>
	HTML);
}
