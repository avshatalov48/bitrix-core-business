<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
use \Bitrix\Main\Localization\Loc;

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'ui.icons.b24',
]);

$APPLICATION->SetPageProperty('BodyClass', $APPLICATION->GetPageProperty('BodyClass').' pagetitle-toolbar-field-view calendar-pagetitle-view no-background');

$isBitrix24Template = (SITE_TEMPLATE_ID === "bitrix24");
if($isBitrix24Template)
{
$this->SetViewTarget("inside_pagetitle");
}
?>
<div id="<?= $arResult['ID']?>-add-button-container" class="pagetitle-container" style="margin-right: 12px"></div>

<? if ($arParams["SHOW_FILTER"]):?>
	<div id="<?= $arResult['ID']?>-search-container" class="pagetitle-container pagetitle-flexible-space<?= $isBitrix24Template ? '' : ' calendar-default-search-wrap' ?>">
	<?
	// Reset filter to default state
	$filterOption = new \Bitrix\Main\UI\Filter\Options($arParams["FILTER_ID"]);
	$filterOption->reset();

	$APPLICATION->IncludeComponent(
		"bitrix:main.ui.filter",
		"",
		array(
			"FILTER_ID" => $arParams["FILTER_ID"],
			"FILTER" => $arParams["FILTER"],
			"FILTER_PRESETS" => $arParams["FILTER_PRESETS"],
			'ENABLE_LIVE_SEARCH' => true,
			'THEME' => Bitrix\Main\UI\Filter\Theme::MUTED,
		),
		$component,
		array("HIDE_ICONS" => true)
	);
	?>
</div>
<? endif;?>
<div id="<?= $arResult['ID']?>-buttons-container" class="pagetitle-container pagetitle-align-right-container<?= $isBitrix24Template ? '' : ' calendar-default-buttons-container' ?>"></div>
<?
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

	<? if (
		$arParams["SHOW_FILTER"]
		&& $arParams['CALENDAR_TYPE'] === 'user'
		&& (int)$arParams['OWNER_ID'] === (int)$arParams['USER_ID']
	):?>
		<div id="<?= $arResult['ID']?>-counter-container" class="pagetitle-container calendar-counter"></div>
	<? endif;?>

	<div id="<?= $arResult['ID']?>-sync-container" style="margin: auto 0 auto auto"></div>
	<div id="<?= $arResult['ID']?>-sharing-container" style="margin: auto 0 auto 5px"></div>
</div>
<?
if($isBitrix24Template)
{
	$this->EndViewTarget();
}
?>

<?
$arResult['CALENDAR']->Show();

if($ex = $APPLICATION->GetException())
{
	if ($ex->GetID() === 'calendar_wrong_type')
	{
		return CCalendarSceleton::showCalendarGridError(GetMessage("EC_CALENDAR_NOT_PERMISSIONS_TO_VIEW_GRID_TITLE"), GetMessage("EC_CALENDAR_NOT_PERMISSIONS_TO_VIEW_GRID_CONTENT"));
	}

	return CCalendarSceleton::showCalendarGridError($ex->GetString());
}

// Set title and navigation
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] ?? null) === "Y" ? "Y" : "N";
$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] ?? null) === "Y" ? "Y" : "N"; //Turn OFF by default

if (($arParams["STR_TITLE"] ?? null))
{
	$arParams["STR_TITLE"] = trim($arParams["STR_TITLE"]);
}
else
{
	if (!($arParams['OWNER_ID'] ?? null) && $arParams['CALENDAR_TYPE'] === "group")
		return CCalendarSceleton::showCalendarGridError(GetMessage('EC_GROUP_ID_NOT_FOUND'));
	if (!($arParams['OWNER_ID'] ?? null) && $arParams['CALENDAR_TYPE'] === "user")
		return CCalendarSceleton::showCalendarGridError(GetMessage('EC_USER_ID_NOT_FOUND'));

	if ($arParams['CALENDAR_TYPE'] === "group" || $arParams['CALENDAR_TYPE'] === "user")
	{
		$feature = "calendar";
		$arEntityActiveFeatures = CSocNetFeatures::GetActiveFeaturesNames((($arParams['CALENDAR_TYPE'] === "group") ? SONET_ENTITY_GROUP : SONET_ENTITY_USER), $arParams['OWNER_ID']);
		$strFeatureTitle = ((array_key_exists($feature, $arEntityActiveFeatures) && $arEntityActiveFeatures[$feature] <> '') ? $arEntityActiveFeatures[$feature] : GetMessage("EC_SONET_CALENDAR"));
		$arParams["STR_TITLE"] = $strFeatureTitle;
	}
	else
		$arParams["STR_TITLE"] = GetMessage("EC_SONET_CALENDAR");
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

	$APPLICATION->SetPageProperty('BodyClass', $APPLICATION->GetPageProperty('BodyClass').' no-background');
}
?>

<?$spotlight = new \Bitrix\Main\UI\Spotlight("CALENDAR_NEW_SYNC");?>
<?if(!$spotlight->isViewed(CCalendar::GetCurUserId()))
{
	CJSCore::init("spotlight");
	?>
	<script type="text/javascript">
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
		<script type="text/javascript">
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
?>