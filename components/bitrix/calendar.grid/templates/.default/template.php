<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @var CBitrixComponent $component */

use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'ui.icons.b24',
	'ui.avatar',
	'ui.navigationpanel',
	'calendar.sectioninterface',
	'calendar.settingsinterface',
	'calendar.rooms',
]);

if (($arResult['IS_TOOL_AVAILABLE'] ?? null) === false)
{
	$componentParameters = [
		'LIMIT_CODE' => 'limit_office_calendar_off',
		'MODULE' => 'calendar',
		'SOURCE' => 'grid',
	];

	$APPLICATION->IncludeComponent(
		"bitrix:ui.sidepanel.wrapper",
		"",
		[
			'POPUP_COMPONENT_NAME' => 'bitrix:intranet.settings.tool.stub',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '',
			'POPUP_COMPONENT_PARAMS' => $componentParameters,
		],
	);

	return;
}

$arResult['CALENDAR']->checkViewPermissions();

if ($ex = $APPLICATION->GetException())
{
	if ($ex->GetID() === 'calendar_wrong_type')
	{
		return CCalendarSceleton::showCalendarGridError(
			Loc::getMessage("EC_CALENDAR_NOT_PERMISSIONS_TO_VIEW_GRID_TITLE"),
			Loc::getMessage("EC_CALENDAR_NOT_PERMISSIONS_TO_VIEW_GRID_CONTENT")
		);
	}

	return CCalendarSceleton::showCalendarGridError($ex->GetString());
}

//Toolbar buttons and filter
require_once __DIR__.'/header.php';

$isCollab = $arResult['IS_COLLAB'];

if (!$arParams['SHOW_FILTER'])
{
	$shouldShowCounterContainer = false;
}
else if (
	$arParams['CALENDAR_TYPE'] === Dictionary::CALENDAR_TYPE['user']
	&& (int)$arParams['OWNER_ID'] === (int)$arParams['USER_ID']
)
{
	$shouldShowCounterContainer = true;
}
elseif ($arParams['CALENDAR_TYPE'] === Dictionary::CALENDAR_TYPE['group'])
{
	$shouldShowCounterContainer = true;
}
else
{
	$shouldShowCounterContainer = false;
}

$isBitrix24Template = (SITE_TEMPLATE_ID === 'bitrix24');

if ($isBitrix24Template)
{
	$this->SetViewTarget("below_pagetitle");
}
?>
<div class="ui-actions-bar">
	<div class="ui-actions-bar__panel" id="<?= $arResult['ID']?>-view-switcher-container"></div>

	<?php if ($shouldShowCounterContainer):?>
		<div class="ui-actions-bar__panel" id="<?= $arResult['ID']?>-counter-container"></div>
	<?php endif;?>

	<div class="ui-actions-bar__buttons" id="calendar-toolbar__buttons">
		<div id="<?= $arResult['ID']?>-sync-container"></div>
		<div id="<?= $arResult['ID']?>-sharing-container"></div>
	</div>
</div>

<?php if($isBitrix24Template)
{
	$this->EndViewTarget();
}

$arResult['CALENDAR']->Show();
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] ?? null) === "Y" ? "Y" : "N";
$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] ?? null) === "Y" ? "Y" : "N"; //Turn OFF by default

if (($arParams["STR_TITLE"] ?? null))
{
	$arParams["STR_TITLE"] = trim($arParams["STR_TITLE"]);
}
else
{
	if (!($arParams['OWNER_ID'] ?? null) && $arParams['CALENDAR_TYPE'] === "group")
	{
		return CCalendarSceleton::showCalendarGridError(Loc::getMessage('EC_GROUP_ID_NOT_FOUND'));
	}
	if (!($arParams['OWNER_ID'] ?? null) && $arParams['CALENDAR_TYPE'] === "user")
	{
		return CCalendarSceleton::showCalendarGridError(Loc::getMessage('EC_USER_ID_NOT_FOUND'));
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
		$strFeatureTitle = ((array_key_exists($feature, $arEntityActiveFeatures) && $arEntityActiveFeatures[$feature] <> '') ? $arEntityActiveFeatures[$feature] : Loc::getMessage("EC_SONET_CALENDAR"));
		$arParams["STR_TITLE"] = $strFeatureTitle;
	}
	else
	{
		$arParams["STR_TITLE"] = Loc::getMessage("EC_SONET_CALENDAR");
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

	if($arParams["SET_TITLE"] === "Y")
	{
		$title_short = (empty($arParams["STR_TITLE"]) ? Loc::getMessage("WD_TITLE") : $arParams["STR_TITLE"]);
		$title = $title_short . ($ownerName ? ': '. $ownerName : '');

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

	$APPLICATION->SetPageProperty('BodyClass', $APPLICATION->GetPageProperty('BodyClass').' no-background');
}

if ($arResult['IS_COLLAB'])
{?>
	<script>
		BX.ready(() => {
			const collabImagePath = "<?=$arResult['COLLAB_IMAGE']?>" || null;
			const collabName = "<?=HtmlFilter::encode($arResult['COLLAB_NAME'])?>";
			const ownerId = "<?=HtmlFilter::encode($arParams['OWNER_ID'])?>";
			const avatar = new BX.UI.AvatarHexagonGuest({
				size: 42,
				userName: collabName.toUpperCase(),
				baseColor: '#19CC45',
				userpicPath: collabImagePath,
			});
			avatar.renderTo(BX('calendar-collab-icon-' + ownerId));
		});
	</script>
<?php
}
?>

<script>
	(new BX.UI.ActionsBar.RightButtons({
		buttonsContainer: BX('calendar-toolbar__buttons'),
		collapsable: false,
	})).init();
</script>

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
