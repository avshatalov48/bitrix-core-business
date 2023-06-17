<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
use \Bitrix\Main\Localization\Loc;

$APPLICATION->SetPageProperty('BodyClass', $APPLICATION->GetPageProperty('BodyClass').' pagetitle-toolbar-field-view calendar-pagetitle-view');

$isBitrix24Template = (SITE_TEMPLATE_ID == "bitrix24");
$isUseViewTarget = !(isset($arParams['USE_VIEW_TARGET']) && $arParams['USE_VIEW_TARGET'] === 'N');
if($isBitrix24Template && $isUseViewTarget)
{
	$this->SetViewTarget("inside_pagetitle");
}

if(!isset($arParams['ID']))
{
	$arParams['ID'] = 'ECGrid-'.rand();
}
$arParams['ID'] = preg_replace("/[^a-zA-Z0-9_-]/i", "", $arParams['ID']);
?>

<? if ($arParams["SHOW_FILTER"]):?>
	<div id="<?= $arParams['ID']?>-search-container" class="pagetitle-container pagetitle-flexible-space<?= $isBitrix24Template ? '' : ' calendar-default-search-wrap' ?>">
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
			"ENABLE_LABEL" => true,
			'ENABLE_LIVE_SEARCH' => true,
			"RESET_TO_DEFAULT_MODE" => true,
			"THEME" => $isBitrix24Template ? "DEFAULT" : "BORDER"
		),
		$component,
		array("HIDE_ICONS" => true)
	);
	?>
</div>
<? endif;?>
<div id="<?= $arParams['ID']?>-buttons-container" class="pagetitle-container pagetitle-align-right-container<?= $isBitrix24Template ? '' : ' calendar-default-buttons-container' ?>"></div>
<?
if($isBitrix24Template)
{
	if($isUseViewTarget)
	{
		$this->EndViewTarget();
	}
	$this->SetViewTarget("below_pagetitle");
}
?>
<? if ($arParams["SHOW_TOP_VIEW_SWITCHER"]):?>
	<div id="<?= $arParams['ID']?>-view-switcher-container" class="calendar-view-switcher-list"></div>
<? endif;?>

<? if ($arParams["SHOW_FILTER"]):?>
	<div id="<?= $arParams['ID']?>-counter-container" class="pagetitle-container" style="overflow: hidden;"></div>
<? endif;?>

<?
if($isBitrix24Template)
{
	$this->EndViewTarget();
}
?>

<?
$currentUserId = CCalendar::GetCurUserId();
$config = array(
	'id' => $arParams['ID'],
	'externalDataHandleMode' => $arParams["EXTERNAL_DATA_HANDLE_MODE"],
	'entityType' => isset($arParams["ENTITY_TYPE"]) ? $arParams["ENTITY_TYPE"] : '',
	'newEntryName' => isset($arParams["NEW_ENTRY_NAME"]) ? $arParams["NEW_ENTRY_NAME"] : '',
	'collapsedLabelMessage' => isset($arParams["COLLAPSED_ENTRIES_NAME"]) ? $arParams["COLLAPSED_ENTRIES_NAME"] : '',
	'showSectionSelector' => $arParams["SHOW_SECTION_SELECTOR"],
	'showSettingsButton' => $arParams["SHOW_SETTINGS_BUTTON"],
	'userSettings' => \Bitrix\Calendar\UserSettings::get(),
	'user' => array(
		'id' => $currentUserId,
		'name' => CCalendar::GetUserName($currentUserId),
		'url' => CCalendar::GetUserUrl($currentUserId),
		'avatar' => CCalendar::GetUserAvatarSrc($currentUserId),
		'smallAvatar' => CCalendar::GetUserAvatarSrc($currentUserId, array('AVATAR_SIZE' => 18))
	)
);

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

$data = array(
	'sections' => array(array(
		'ID' => 1,
		'COLOR' => isset($arParams['DEFAULT_SECTION_COLOR']) ? $arParams["DEFAULT_SECTION_COLOR"] : '#FFA900',
		'TEXT_COLOR' => isset($arParams['DEFAULT_SECTION_TEXT_COLOR']) ? $arParams["DEFAULT_SECTION_TEXT_COLOR"] : '#000',
		'NAME' => $arParams["DEFAULT_SECTION_NAME"],
		'PERM' => array(
			'view_full' => true,
			'view_time' => true,
			'view_title' => true
		)
	))
);
$additionalParams = array();

CCalendarSceleton::InitJS(
	$config,
	$data,
	$additionalParams
);

if($ex = $APPLICATION->GetException())
	return ShowError($ex->GetString());

// Set title and navigation
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] ?? null) == "Y" ? "Y" : "N";
$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] ?? null) == "Y" ? "Y" : "N"; //Turn OFF by default

$arParams['OWNER_ID'] ??= null;
$arParams['CALENDAR_TYPE'] ??= null;
if ($arParams["STR_TITLE"] ?? false)
{
	$arParams["STR_TITLE"] = trim($arParams["STR_TITLE"]);
}
else
{
	if (!$arParams['OWNER_ID'] && $arParams['CALENDAR_TYPE'] == "group")
		return ShowError(GetMessage('EC_GROUP_ID_NOT_FOUND'));
	if (!$arParams['OWNER_ID'] && $arParams['CALENDAR_TYPE'] == "user")
		return ShowError(GetMessage('EC_USER_ID_NOT_FOUND'));

	if ($arParams['CALENDAR_TYPE'] == "group" || $arParams['CALENDAR_TYPE'] == "user")
	{
		$feature = "calendar";
		$arEntityActiveFeatures = CSocNetFeatures::GetActiveFeaturesNames((($arParams['CALENDAR_TYPE'] == "group") ? SONET_ENTITY_GROUP : SONET_ENTITY_USER), $arParams['OWNER_ID']);
		$strFeatureTitle = ((array_key_exists($feature, $arEntityActiveFeatures) && $arEntityActiveFeatures[$feature] <> '') ? $arEntityActiveFeatures[$feature] : GetMessage("EC_SONET_CALENDAR"));
		$arParams["STR_TITLE"] = $strFeatureTitle;
	}
	else
		$arParams["STR_TITLE"] = GetMessage("EC_SONET_CALENDAR");
}

$bOwner = $arParams["CALENDAR_TYPE"] == 'user' || $arParams["CALENDAR_TYPE"] == 'group';
if ($arParams["SET_TITLE"] == "Y" || ($bOwner && $arParams["SET_NAV_CHAIN"] == "Y"))
{
	$ownerName = '';
	if ($bOwner)
	{
		$ownerName = CCalendar::GetOwnerName($arParams["CALENDAR_TYPE"], $arParams["OWNER_ID"]);
	}

	if($arParams["SET_TITLE"] == "Y")
	{
		$title_short = (empty($arParams["STR_TITLE"]) ? GetMessage("WD_TITLE") : $arParams["STR_TITLE"]);
		$title = ($ownerName ? $ownerName.': ' : '').$title_short;

		if ($arParams["HIDE_OWNER_IN_TITLE"] == "Y")
		{
			$APPLICATION->SetPageProperty("title", $title);
			$APPLICATION->SetTitle($title_short);
		}
		else
		{
			$APPLICATION->SetTitle($title);
		}
	}

	if ($bOwner && $arParams["SET_NAV_CHAIN"] == "Y")
	{
		$set = CCalendar::GetSettings();
		if($arParams["CALENDAR_TYPE"] == 'group')
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

<?$spotlight = new \Bitrix\Main\UI\Spotlight("CALENDAR_NEW_SYNC");?>
<?if(!$spotlight->isViewed(CCalendar::GetCurUserId()))
{
	CJSCore::init("spotlight");
	?>
	<script type="text/javascript">
		BX.ready(function ()
		{
			var target = BX("<?= $arParams['ID']?>-buttons-container");
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
				var target = BX("<?= $arParams['ID']?>-view-switcher-container");
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
