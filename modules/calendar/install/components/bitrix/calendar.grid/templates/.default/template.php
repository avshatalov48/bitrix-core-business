<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
use \Bitrix\Main\Localization\Loc;

$APPLICATION->SetPageProperty('BodyClass', $APPLICATION->GetPageProperty('BodyClass').' pagetitle-toolbar-field-view calendar-pagetitle-view');

$isBitrix24Template = (SITE_TEMPLATE_ID == "bitrix24");
if($isBitrix24Template)
{
$this->SetViewTarget("inside_pagetitle");
}
?>
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
			"ENABLE_LABEL" => true,
			'ENABLE_LIVE_SEARCH' => true,
			"RESET_TO_DEFAULT_MODE" => true,
			"THEME" => $isBitrix24Template ? "DEFAULT" : "BORDER"
		),
		$component,
		array("HIDE_ICONS" => true)
	);


	foreach($arParams["FILTER"] as $filterField)
	{
		if (
			$filterField['type'] == 'custom_entity'
			&& $filterField['selector']['TYPE'] == 'user'
		)
		{
			$userSelector = $filterField['selector']['DATA'];
			$selectorID = $userSelector['ID'];
			$fieldID = $userSelector['FIELD_ID'];

			$APPLICATION->IncludeComponent(
				"bitrix:main.ui.selector",
				".default",
				array(
					'ID' => $selectorID,
					'ITEMS_SELECTED' => array(),
					'CALLBACK' => array(
						'select' => 'CalendarFilterUserSelectorManager.onSelect',
						'unSelect' => '',
						'openDialog' => '',
						'closeDialog' => '',
						'openSearch' => ''
					),
					'OPTIONS' => array(
						'eventInit' => 'BX.SonetGroupList.Filter:openInit',
						'eventOpen' => 'BX.SonetGroupList.Filter:open',
						'context' => 'SONET_GROUP_LIST_FILTER_MEMBER',
						'contextCode' => 'U',
						'useSearch' => 'N',
						'userNameTemplate' => CUtil::JSEscape(CSite::GetNameFormat()),
						'useClientDatabase' => 'Y',
						'allowEmailInvitation' => 'N',
						'enableDepartments' => 'Y',
						'enableSonetgroups' => 'N',
						'departmentSelectDisable' => 'Y',
						'allowAddUser' => 'N',
						'allowAddCrmContact' => 'N',
						'allowAddSocNetGroup' => 'N',
						'allowSearchEmailUsers' => 'N',
						'allowSearchCrmEmailUsers' => 'N',
						'allowSearchNetworkUsers' => 'N',
						'allowSonetGroupsAjaxSearchFeatures' => 'N'
					)
				),
				false,
				array("HIDE_ICONS" => "Y")
			);
			?>
			<script>
				BX.ready(
					function()
					{
						CalendarFilterUserSelector.create(
							"<?=CUtil::JSEscape($selectorID)?>",
							{
								filterId: "<?=CUtil::JSEscape($filterID)?>",
								fieldId: "<?=CUtil::JSEscape($fieldID)?>"
							}
						);
					}
				);
			</script>
			<?
		}
	}
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

<? if ($arParams["SHOW_FILTER"]):?>
	<div id="<?= $arResult['ID']?>-counter-container" class="pagetitle-container" style="overflow: hidden;"></div>
<? endif;?>
<div id="<?= $arResult['ID']?>-view-switcher-container" class="calendar-view-switcher pagetitle-align-right-container"></div>
<?
if($isBitrix24Template)
{
	$this->EndViewTarget();
}
?>

<?
$stepperHtml = \Bitrix\Main\Update\Stepper::getHtml(array("calendar" => array('Bitrix\Calendar\Update\IndexCalendar')),\Bitrix\Main\Localization\Loc::getMessage("EC_CALENDAR_INDEX"));
if ($stepperHtml)
{
	echo '<div class="calendar-stepper-block">'.$stepperHtml.'</div>';
}
?>

<?
$arResult['CALENDAR']->Show();

if($ex = $APPLICATION->GetException())
	return ShowError($ex->GetString());

// Set title and navigation
$arParams["SET_TITLE"] = $arParams["SET_TITLE"] == "Y" ? "Y" : "N";
$arParams["SET_NAV_CHAIN"] = $arParams["SET_NAV_CHAIN"] == "Y" ? "Y" : "N"; //Turn OFF by default

if ($arParams["STR_TITLE"])
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
		$strFeatureTitle = ((array_key_exists($feature, $arEntityActiveFeatures) && StrLen($arEntityActiveFeatures[$feature]) > 0) ? $arEntityActiveFeatures[$feature] : GetMessage("EC_SONET_CALENDAR"));
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