<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @var CBitrixComponent $component */

use Bitrix\Main\Text\HtmlFilter;

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'ui.icons.b24',
	'ui.avatar',
	'ui.navigationpanel',
]);

$isBitrix24Template = (SITE_TEMPLATE_ID === "bitrix24");

require_once __DIR__.'/header.php';

if(!isset($arParams['ID']))
{
	$arParams['ID'] = 'ECGrid-' . mt_rand();
}
$arParams['ID'] = preg_replace("/[^a-zA-Z0-9_-]/i", "", $arParams['ID']);

if ($isBitrix24Template)
{
	$this->SetViewTarget("below_pagetitle");
}
?>
<?php if ($arParams["SHOW_TOP_VIEW_SWITCHER"]):?>
	<div class="ui-actions-bar">
		<div class="ui-actions-bar__panel" id="<?= $arResult['ID']?>-view-switcher-container"></div>

		<?php if ($arParams["SHOW_FILTER"]):?>
			<div class="ui-actions-bar__panel" id="<?= $arResult['ID']?>-counter-container"></div>
		<?php endif;?>
	</div>
<?php endif;?>

<?php
if ($isBitrix24Template)
{
	$this->EndViewTarget();
}

$currentUserId = CCalendar::GetCurUserId();
$config = [
	'id' => $arParams['ID'],
	'externalDataHandleMode' => $arParams["EXTERNAL_DATA_HANDLE_MODE"],
	'entityType' => $arParams["ENTITY_TYPE"] ?? '',
	'newEntryName' => $arParams["NEW_ENTRY_NAME"] ?? '',
	'collapsedLabelMessage' => $arParams["COLLAPSED_ENTRIES_NAME"] ?? '',
	'showSectionSelector' => $arParams["SHOW_SECTION_SELECTOR"],
	'showSettingsButton' => $arParams["SHOW_SETTINGS_BUTTON"],
	'userSettings' => \Bitrix\Calendar\UserSettings::get(),
	'user' => [
		'id' => $currentUserId,
		'name' => CCalendar::GetUserName($currentUserId),
		'url' => CCalendar::GetUserUrl($currentUserId),
		'avatar' => CCalendar::GetUserAvatarSrc($currentUserId),
		'smallAvatar' => CCalendar::GetUserAvatarSrc($currentUserId, ['AVATAR_SIZE' => 18])
	],
];

if (isset($arParams['READONLY']))
{
	$config['readOnly'] = $arParams['READONLY'];
}

if (is_array($arParams['AVILABLE_VIEWS']))
{
	$config['avilableViews'] = $arParams['AVILABLE_VIEWS'];
}

if (is_array($arParams['ADDITIONAL_VIEW_MODES'] ?? null))
{
	$config['additionalViewModes'] = $arParams['ADDITIONAL_VIEW_MODES'];
}

$data = [
	'sections' => [
		[
			'ID' => 1,
			'COLOR' => isset($arParams['DEFAULT_SECTION_COLOR']) ? $arParams["DEFAULT_SECTION_COLOR"] : '#FFA900',
			'TEXT_COLOR' => isset($arParams['DEFAULT_SECTION_TEXT_COLOR']) ? $arParams["DEFAULT_SECTION_TEXT_COLOR"] : '#000',
			'NAME' => $arParams["DEFAULT_SECTION_NAME"],
			'PERM' => [
				'view_full' => true,
				'view_time' => true,
				'view_title' => true,
			]
		],
	]
];
$additionalParams = [];

CCalendarSceleton::InitJS(
	$config,
	$data,
	$additionalParams
);

if ($ex = $APPLICATION->GetException())
{
	ShowError($ex->GetString());

	return;
}

// Set title and navigation
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] ?? null) === "Y" ? "Y" : "N";
$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] ?? null) === "Y" ? "Y" : "N"; //Turn OFF by default

$arParams['OWNER_ID'] ??= null;
$arParams['CALENDAR_TYPE'] ??= null;
if ($arParams["STR_TITLE"] ?? false)
{
	$arParams["STR_TITLE"] = trim($arParams["STR_TITLE"]);
}
else
{
	if (!$arParams['OWNER_ID'] && $arParams['CALENDAR_TYPE'] === "group")
	{
		ShowError(GetMessage('EC_GROUP_ID_NOT_FOUND'));
		return;
	}
	if (!$arParams['OWNER_ID'] && $arParams['CALENDAR_TYPE'] === "user")
	{
		ShowError(GetMessage('EC_USER_ID_NOT_FOUND'));
		return;
	}

	if ($arParams['CALENDAR_TYPE'] === "group" || $arParams['CALENDAR_TYPE'] === "user")
	{
		$feature = "calendar";
		$arEntityActiveFeatures = [];

		if (\Bitrix\Main\Loader::includeModule('socialnetwork'))
		{
			$arEntityActiveFeatures = CSocNetFeatures::GetActiveFeaturesNames(
				($arParams['CALENDAR_TYPE'] === 'group') ? SONET_ENTITY_GROUP : SONET_ENTITY_USER,
				$arParams['OWNER_ID']
			);
		}

		$strFeatureTitle = ((array_key_exists($feature, $arEntityActiveFeatures) && $arEntityActiveFeatures[$feature] <> '') ? $arEntityActiveFeatures[$feature] : GetMessage("EC_SONET_CALENDAR"));
		$arParams["STR_TITLE"] = $strFeatureTitle;
	}
	else
	{
		$arParams["STR_TITLE"] = GetMessage("EC_SONET_CALENDAR");
	}
}

$bOwner = $arParams["CALENDAR_TYPE"] === 'user' || $arParams["CALENDAR_TYPE"] === 'group';
if ($arParams["SET_TITLE"] === "Y" || ($bOwner && $arParams["SET_NAV_CHAIN"] === "Y"))
{
	$ownerName = '';
	if ($bOwner)
	{
		$ownerName = CCalendar::GetOwnerName($arParams["CALENDAR_TYPE"], $arParams["OWNER_ID"]);
	}

	if ($arParams["SET_TITLE"] === "Y")
	{
		$title_short = (empty($arParams["STR_TITLE"]) ? GetMessage("WD_TITLE") : $arParams["STR_TITLE"]);
		$title = ($ownerName ? $ownerName.': ' : '').$title_short;

		if ($arParams["HIDE_OWNER_IN_TITLE"] === "Y")
		{
			$APPLICATION->SetPageProperty("title", $title);
			$APPLICATION->SetTitle($title_short);
		}
		else
		{
			$APPLICATION->SetTitle($title);
		}
	}

	if ($bOwner && $arParams["SET_NAV_CHAIN"] === "Y")
	{
		$set = CCalendar::GetSettings();
		if($arParams["CALENDAR_TYPE"] === 'group')
		{
			$APPLICATION->AddChainItem($ownerName, CComponentEngine::MakePathFromTemplate($set['path_to_group'], array("group_id" => $arParams["OWNER_ID"])));
			$APPLICATION->AddChainItem($arParams["STR_TITLE"], CComponentEngine::MakePathFromTemplate($set['path_to_group_calendar'], array("group_id" => $arParams["OWNER_ID"], "path" => "")));
		}
		else
		{
			$APPLICATION->AddChainItem(htmlspecialcharsEx($ownerName), CComponentEngine::MakePathFromTemplate($set['path_to_user'], array("user_id" => $arParams["OWNER_ID"])));
			$APPLICATION->AddChainItem($arParams["STR_TITLE"], CComponentEngine::MakePathFromTemplate($set['path_to_user_calendar'], array("user_id" => $arParams["OWNER_ID"], "path" => "")));
		}
	}
}
?>

<script>
	function openEventCreateSlider()
	{
		const type = "<?=HtmlFilter::encode($arParams['CALENDAR_TYPE'])?>";
		const userId = <?=HtmlFilter::encode($arParams['USER_ID'] ?? 0)?>;
		const ownerId = <?=HtmlFilter::encode($arParams['OWNER_ID'] ?? 0)?>;
		const isLocationCalendar = type === 'location';

		if (isLocationCalendar)
		{
			BX.Calendar.EntryManager.openEditSlider({
				type: 'user',
				isLocationCalendar: true,
				userId: parseInt(userId),
				ownerId: parseInt(ownerId),
			});
		}
		else
		{
			BX.Calendar.EntryManager.openEditSlider({
				type,
				isLocationCalendar: false,
				userId: parseInt(userId),
				ownerId: parseInt(ownerId),
			});
		}
	}

	function openCalendarSectionList()
	{
		BX.Calendar.EntryManager.openSectionsSlider();
	}

	function openCalendarRoomsList()
	{
		BX.Calendar.EntryManager.openRoomsSlider();
	}

	function openCalendarSettings()
	{
		BX.Calendar.EntryManager.openSettingsSlider();
	}
</script>
