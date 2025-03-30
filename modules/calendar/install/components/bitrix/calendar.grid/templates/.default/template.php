<?php

use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Integration\SocialNetwork\Context\Context;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'ui.icons.b24',
	'ui.avatar',
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

$isCollab = $arResult['IS_COLLAB'];
if ($isCollab)
{
	$collabPostfix = 'calendar-collab-calendar__wrapper';
}

$shouldShowCounterContainer = false;
if (!$arParams['SHOW_FILTER'])
{
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

$bodyClass = $APPLICATION->GetPageProperty('BodyClass') . ' pagetitle-toolbar-field-view calendar-pagetitle-view no-background';
if ($isCollab)
{
	$bodyClass .= ' ' . $collabPostfix;
}

$APPLICATION->SetPageProperty('BodyClass', $bodyClass);

$isBitrix24Template = (SITE_TEMPLATE_ID === "bitrix24");

if($isBitrix24Template)
{
	$this->SetViewTarget("inside_pagetitle");
}
?>

<div id="<?= $arResult['ID']?>-add-button-container" class="pagetitle-container" style="margin-right: 12px"></div>

<?php if ($arParams["SHOW_FILTER"]):?>
<div id="<?= $arResult['ID']?>-search-container" class="pagetitle-container pagetitle-flexible-space<?= $isBitrix24Template ? '' : ' calendar-default-search-wrap' ?>">
	<?php
	// Reset filter to default state
	$filterOption = new \Bitrix\Main\UI\Filter\Options($arParams["FILTER_ID"]);
	$filterOption->reset();

	$APPLICATION->IncludeComponent(
		"bitrix:main.ui.filter",
		"",
		[
			"FILTER_ID" => $arParams['FILTER_ID'],
			"FILTER" => $arParams["FILTER"],
			"FILTER_PRESETS" => $arParams["FILTER_PRESETS"],
			'ENABLE_LIVE_SEARCH' => true,
			"ENABLE_LABEL" => true,
			'THEME' => Bitrix\Main\UI\Filter\Theme::MUTED,
		],
		$component,
		[
				"HIDE_ICONS" => true
		]
	);
	?>
</div>
<?php endif;?>
<div id="<?= $arResult['ID']?>-buttons-container" class="pagetitle-container pagetitle-align-right-container<?= $isBitrix24Template ? '' : ' calendar-default-buttons-container' ?>"></div>
<?php
if($isBitrix24Template)
{
	$this->EndViewTarget();
	$this->SetViewTarget("below_pagetitle");
}
?>
<div class="calendar-interface-toolbar">
	<div class="calendar-view-switcher">
		<div id="<?= $arResult['ID']?>-view-switcher-container"></div>
	</div>

	<?php if ($shouldShowCounterContainer):?>
		<div id="<?= $arResult['ID']?>-counter-container" class="pagetitle-container calendar-counter"></div>
	<?php endif;?>

	<div id="<?= $arResult['ID']?>-sync-container" style="margin: auto 0 auto auto"></div>
	<div id="<?= $arResult['ID']?>-sharing-container" style="margin: auto 0 auto 5px"></div>
</div>

<?php if($isBitrix24Template)
{
	$this->EndViewTarget();
}

$arResult['CALENDAR']->Show();

// Set title and navigation
if ($isCollab)
{
	$collabName = HtmlFilter::encode($arResult['COLLAB_NAME']);
	\Bitrix\UI\Toolbar\Facade\Toolbar::deleteFavoriteStar();

	$this->SetViewTarget('in_pagetitle') ?>

	<div class="calendar-collab-icon__wrapper">
		<div id="calendar-collab-icon-<?=HtmlFilter::encode($arParams['OWNER_ID'])?>" class="calendar-collab-icon__hexagon-bg"></div>
	</div>
	<div class="calendar-collab__subtitle" title="<?=$collabName?>"><?=$collabName?></div>
	<?php $this->EndViewTarget();
}

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
?>

<?$spotlight = new \Bitrix\Main\UI\Spotlight("CALENDAR_NEW_SYNC");?>
<?if(!$spotlight->isViewed(CCalendar::GetCurUserId()))
{
	CJSCore::init("spotlight");
	?>
	<script>
		BX.ready(function ()
		{
			var target = BX("<?= $arResult['ID']?>-buttons-container");
			if (target)
			{
				target =  target.querySelector(".calendar-sync-button");
			}
			if (target && BX.type.isDomNode(target))
			{
				setTimeout(function(){
					var calendarSyncSpotlight = new BX.SpotLight({
						targetElement: target,
						targetVertex: "middle-center",
						content: '<?=Loc::getMessage('EC_CALENDAR_SPOTLIGHT_SYNC')?>',
						id: "CALENDAR_NEW_SYNC",
						autoSave: true
					});
					calendarSyncSpotlight.show();
				}, 2000);
			}
		});
	</script>
	<?
}
else
{
	$spotlightList = new \Bitrix\Main\UI\Spotlight("CALENDAR_NEW_LIST");
	if(!$spotlightList->isViewed(CCalendar::GetCurUserId()))
	{
		CJSCore::init("spotlight");
		?>
		<script>
			//
			BX.ready(function ()
			{
				var target = BX("<?= $arResult['ID']?>-view-switcher-container");
				if (target)
				{
					target = target.querySelectorAll(".calendar-view-switcher-list-item");
					target = target[target.length - 1];
				}

				if (target && BX.type.isDomNode(target))
				{
					setTimeout(function(){
						var calendarListSpotlight = new BX.SpotLight({
							targetElement: target,
							targetVertex: "middle-center",
							content: '<?= Loc::getMessage('EC_CALENDAR_SPOTLIGHT_LIST')?>',
							id: "CALENDAR_NEW_LIST",
							autoSave: true
						});
						calendarListSpotlight.show();
					}, 2000);
				}
			});
		</script>
		<?
	}
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
