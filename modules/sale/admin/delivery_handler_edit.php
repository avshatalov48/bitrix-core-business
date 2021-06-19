<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

\Bitrix\Main\Loader::includeModule('sale');

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

IncludeModuleLangFile(__FILE__);

$lheStyle = '
<style type="text/css">
	.bxlhe_frame_hndl_dscr {
		-moz-border-bottom-colors: none;
		-moz-border-left-colors: none;
		-moz-border-right-colors: none;
		-moz-border-top-colors: none;
		background: none repeat scroll 0 0 #FFFFFF;
		border-color: #87919C #959EA9 #9EA7B1;
		border-image: none;
		border-radius: 4px 4px 4px 4px;
		border-style: solid;
		border-width: 1px;
		box-shadow: 0 1px 0 0 rgba(255, 255, 255, 0.3), 0 2px 2px -1px rgba(180, 188, 191, 0.7) inset;
		color: #000000;
		display: inline-block;
		outline: medium none;
		vertical-align: middle;
		!important;
	}
</style>';

$APPLICATION->AddHeadString($lheStyle, true, true);

$SID = $_REQUEST["SID"];

//$bInstall = strlen($handlerPath) > 0 && strlen($SID) <= 0;

$errorsList = "";

//$obDelivery = new CSaleDeliveryHandler();

if (CModule::IncludeModule("fileman"))
	$bFilemanModuleInst = true;

$siteList = array();
$rsSites = CSite::GetList();
$i = 0;
while($arRes = $rsSites->Fetch())
{
	$siteList[] = array(
		'ID' => $arRes['ID'],
		'NAME' => $arRes['NAME'],
	);
}
$siteCount = count($siteList);

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_REQUEST["Update"]) && check_bitrix_sessid())
{
	$arHandlersData = isset($_POST["STRUCTURE"]) ? urldecode($_POST["STRUCTURE"]) : '';

	$arHandlersData = json_decode($arHandlersData, true);

	if('utf-8' != mb_strtolower(SITE_CHARSET))
		$arHandlersData = $APPLICATION->ConvertCharsetArray($arHandlersData, 'utf-8', SITE_CHARSET);

	if ($arHandlersData)
	{

		if ($_REQUEST["USE_DIFF_SITES_SETTINGS"] != "Y")
		{
			$curSITE_ID = $_REQUEST["current_site"];
			$arHandlersData = array("ALL" => $arHandlersData[$curSITE_ID]);
			$arHandlersData["ALL"]["LID"] = false;
		}


		foreach ($arHandlersData as $siteID => $arHandler)
		{
			foreach ($arHandlersData[$siteID]["PROFILES"] as $profile_id => $arProfile)
			{
				if (is_array($arProfile["RESTRICTIONS_SUM"]))
				{
					$currency = array_shift($arProfile["RESTRICTIONS_SUM"]);
					foreach ($arProfile["RESTRICTIONS_SUM"] as $key => $value)
					{
						$arProfile["RESTRICTIONS_SUM"][$key] = CCurrencyRates::ConvertCurrency($value, $currency, $arHandlersData[$siteID]["BASE_CURRENCY"]);
					}

					$arHandlersData[$siteID]["PROFILES"][$profile_id] = $arProfile;
				}
			}
			$arConfig = array();
			foreach ($arHandlersData[$siteID]["CONFIG"]["CONFIG"] as $configID => $arHandlerConfig)
			{
				if(isset($arHandlerConfig["CHECK_FORMAT"]))
				{
					$formatError = CSaleDeliveryHelper::getFormatError($arHandlerConfig["VALUE"], $arHandlerConfig["CHECK_FORMAT"], $arHandlerConfig["TITLE"]);

					if(!is_null($formatError))
						$errorsList .= $formatError;
				}

				$arConfig[$configID] = $arHandlerConfig["VALUE"];
			}
			$arHandlersData[$siteID]["CONFIG"] = $arConfig;
			//$arHandlersData[$siteID]["HANDLER"] = $handlerPath;
		}

		//add logotip
		$arPicture = array();
		if(array_key_exists("LOGOTIP", $_FILES) && $_FILES["LOGOTIP"]["error"] == 0)
			$arPicture = $_FILES["LOGOTIP"];
		if($_POST["LOGOTIP_del"] == "Y")
			$arPicture["del"] = trim($_POST["LOGOTIP_del"]);

		if(!empty($arPicture))
			$arHandlersData["ALL"]["LOGOTIP"] = $arPicture;

		foreach ($arHandlersData as $SITE_ID => $arHandlerData)
		{
			$APPLICATION->ResetException();
			$arHandlerData["PROFILE_USE_DEFAULT"] == "N";
			CSaleDeliveryHandler::Set($SID, $arHandlerData, $SITE_ID == "ALL" ? false : $SITE_ID);

			if ($ex = $APPLICATION->GetException())
			{
				$errorsList .= $ex->GetString()."<br />";
			}
		}

		//pay system for delivery
		if (is_set($_POST["PAY_SYSTEM"]) && is_array($_POST["PAY_SYSTEM"]))
		{
			foreach ($_POST["PAY_SYSTEM"] as $profileName => $arPSIds)
				CSaleDelivery2PaySystem::UpdateDelivery($SID, array(
																"PAYSYSTEM_ID" => $arPSIds,
																"DELIVERY_PROFILE_ID" => $profileName
																)
					);
		}

		if ($errorsList == '')
		{
			if ($_REQUEST["apply"] <> '')
				LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANG."&SID=".urlencode($SID));
			else
				LocalRedirect('/bitrix/admin/sale_delivery_handlers.php?lang='.LANG);

			die();
		}
	}
	else
	{
		$errorsList .= GetMessage('SALE_DH_ERROR_UNRECOGNIZED')."<br />";
	}
}

$rsDeliveryInfo = CSaleDeliveryHandler::GetBySID($SID);

if ($rsDeliveryInfo->SelectedRowsCount() <= 0)
{
	echo 'error';
	die();
}

while ($arHandler = $rsDeliveryInfo->Fetch())
{
	$bInstall = $arHandler["INSTALLED"] == "N";

	unset($arHandler["DBGETSETTINGS"]);
	unset($arHandler["DBSETSETTINGS"]);
	unset($arHandler["GETCONFIG"]);
	unset($arHandler["COMPABILITY"]);
	unset($arHandler["CALCULATOR"]);

	if ($arHandler["LID"] <> '')
		$arDeliveryInfo[$arHandler["LID"]] = $arHandler;
	else
	{
		$arDeliveryInfo = array("ALL" => $arHandler);
		break;
	}
}

if (count($arDeliveryInfo) > 0 && !isset($arDeliveryInfo['ALL']) && count($arDeliveryInfo) != count($siteList))
{
	$tmp = array_values($arDeliveryInfo);
	$ar = $tmp[0];
	foreach ($siteList as $arSite)
	{
		if (!isset($arDeliveryInfo[$arSite['ID']]))
		{
			$arDeliveryInfo[$arSite['ID']] = $ar;
			$arDeliveryInfo[$arSite['ID']]['ACTIVE'] = 'N';
			unset($arDeliveryInfo[$arSite['ID']]["ID"]);
		}
	}
}

if (!$bInstall)
{
	if (count($arDeliveryInfo) > 0)
	{
		$arSitesConfigured = array_keys($arDeliveryInfo);
		$bSites = $arSitesConfigured[0] != "ALL";

		if (!$bSites)
		{
			foreach ($siteList as $arSite)
			{
				$arDeliveryInfo[$arSite["ID"]] = $arDeliveryInfo["ALL"];
				$arDeliveryInfo[$arSite["ID"]]["LID"] = $arSite["ID"];
			}

			unset($arDeliveryInfo["ALL"]);
		}

		$handlerPath = $arDeliveryInfo[$siteList[0]["ID"]]["HANDLER"];
		$deliveryHint = $arDeliveryInfo[$siteList[0]["ID"]]['DESCRIPTION_INNER'];
		$deliveryName = $arDeliveryInfo[$siteList[0]["ID"]]['NAME'];
	}
	else
	{
		$bInstall = true;
	}
}
else if(isset($arDeliveryInfo["ALL"]))
{
	$arDeliveryInfoTmp = $arDeliveryInfo;
	$arDeliveryInfoTmp["ALL"]["ACTIVE"] = 'N';
	$arDeliveryInfoTmp["ALL"]["SORT"] = '100';

	$arDeliveryInfo = array();

	foreach ($siteList as $arSite)
	{
		$arDeliveryInfo[$arSite["ID"]] = $arDeliveryInfoTmp["ALL"];
		$arDeliveryInfo[$arSite["ID"]]["LID"] = $arSite["ID"];
	}

	unset($arDeliveryInfoTmp);

	$handlerPath = $arDeliveryInfo[$siteList[0]["ID"]]["HANDLER"];
	$deliveryHint = $arDeliveryInfo[$siteList[0]["ID"]]['DESCRIPTION_INNER'];
	$deliveryName = $arDeliveryInfo[$siteList[0]["ID"]]['NAME'];

	$bSites = false;
}
else
{
	$bSites = true;

	foreach ($siteList as $arSite)
	{
		$arDeliveryInfo[$arSite["ID"]]["ACTIVE"] = 'N';
		$arDeliveryInfo[$arSite["ID"]]["SORT"] = '100';
	}

	$handlerPath = $arDeliveryInfo[$siteList[0]["ID"]]["HANDLER"];
	$deliveryHint = $arDeliveryInfo[$siteList[0]["ID"]]['DESCRIPTION_INNER'];
	$deliveryName = $arDeliveryInfo[$siteList[0]["ID"]]['NAME'];
}

foreach ($siteList as $arSite)
{
	$curSITE_ID = $arSite["ID"];
	unset($arDeliveryInfo[$curSITE_ID]["SETTINGS"]);

	if (!is_array($arDeliveryInfo[$curSITE_ID]["CONFIG"]["CONFIG_GROUPS"]))
	{
		$arDeliveryInfo[$curSITE_ID]["CONFIG"]["CONFIG"] = array();
		foreach ($arDeliveryInfo[$curSITE_ID]["CONFIG"] as $key => $arConfig)
		{
			if ($key != "CONFIG")
			{
				$arConfig["GROUP"] = "none";
				$arDeliveryInfo[$curSITE_ID]["CONFIG"]["CONFIG"] = $arConfig;
				unset($arDeliveryInfo[$curSITE_ID]["CONFIG"][$key]);
			}
		}

		$arDeliveryInfo[$curSITE_ID]["CONFIG"]["CONFIG_GROUPS"] = array("none" => "");
	}

	foreach ($arDeliveryInfo[$curSITE_ID]['PROFILES'] as $key => $arProfile)
	{
		if (!is_set($arProfile['ACTIVE'])) $arProfile['ACTIVE'] = "Y";

		if (!is_set($arProfile['TAX_RATE'])) $arProfile['TAX_RATE'] = "0";
		if (!is_set($arProfile['RESTRICTIONS_DIMENSIONS_SUM'])) $arProfile['RESTRICTIONS_DIMENSIONS_SUM'] = "0";
		if (!is_set($arProfile['RESTRICTIONS_MAX_SIZE'])) $arProfile['RESTRICTIONS_MAX_SIZE'] = "0";

		if (!is_array($arProfile["RESTRICTIONS_WEIGHT"]) || count($arProfile["RESTRICTIONS_WEIGHT"]) <= 0)
			$arProfile["RESTRICTIONS_WEIGHT"] = array(0);

		if (!is_array($arProfile["RESTRICTIONS_DIMENSIONS"]) || count($arProfile["RESTRICTIONS_DIMENSIONS"]) <= 0)
			$arProfile["RESTRICTIONS_DIMENSIONS"] = array(0);

		if (!is_array($arProfile["RESTRICTIONS_SUM"]) || count($arProfile["RESTRICTIONS_SUM"]) <= 0)
			$arProfile["RESTRICTIONS_SUM"] = array(0);
		else
			array_unshift($arProfile["RESTRICTIONS_SUM"], $arDeliveryInfo[$curSITE_ID]['BASE_CURRENCY']);

		foreach ($arProfile["RESTRICTIONS_WEIGHT"] as $pkey => $value)
			$arProfile["RESTRICTIONS_WEIGHT"][$pkey] = number_format(doubleval($value), 2, '.', '');
		foreach ($arProfile["RESTRICTIONS_SUM"] as $pkey => $value)
			$arProfile["RESTRICTIONS_SUM"][$pkey] = $pkey > 0 ? number_format(doubleval($value), 2, '.', '') : $value;
		if (count($arProfile["RESTRICTIONS_SUM"]) < 3)
			$arProfile["RESTRICTIONS_SUM"][] = "0.00";
		foreach ($arProfile["RESTRICTIONS_DIMENSIONS"] as $pkey => $value)
			$arProfile["RESTRICTIONS_DIMENSIONS"][$pkey] = number_format(doubleval($value), 2, '.', '');

		$arDeliveryInfo[$curSITE_ID]['PROFILES'][$key] = $arProfile;
	}
}

$APPLICATION->SetTitle(GetMessage("SALE_DH_TITLE_EDIT").": (".htmlspecialcharsEx($SID).") ".$deliveryName);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aTabs = array(
	array("DIV" => "editbase", "TAB" => GetMessage("SALE_DH_EDIT_BASECONFIG"), "TITLE" => GetMessage("SALE_DH_EDIT_BASECONFIG_DESCR"))
);

$SITE_ID = $siteList[0]['ID'];
if (is_array($arDeliveryInfo[$SITE_ID]["CONFIG"]))
{
	if (is_array($arDeliveryInfo[$SITE_ID]["CONFIG"]["CONFIG_GROUPS"]))
	{
		foreach ($arDeliveryInfo[$SITE_ID]["CONFIG"]["CONFIG_GROUPS"] as $group => $title)
		{
			$configTabsCount++;
			$aTabs[] = array("DIV" => "edit_".htmlspecialcharsbx($group), "TAB" => htmlspecialcharsbx($title), "TITLE" => htmlspecialcharsbx($title));
		}
	}
	else
	{
		$configTabsCount++;
		$aTabs[] = array("DIV" => "edit_config", "TAB" => GetMessage('SALE_DH_EDIT_CONFIG'), "TITLE" => GetMessage('SALE_DH_EDIT_CONFIG_DESCR'));
	}
}

if(is_array($arDeliveryInfo[$SITE_ID]["PROFILES"]))
{
	foreach ($arDeliveryInfo[$SITE_ID]["PROFILES"] as $profileId => $arProfile)
	{
		if(!array_key_exists($profileId, $arDeliveryInfo[$SITE_ID]["CONFIG"]["CONFIG_GROUPS"]))
		{
			$configTabsCount++;
			$aTabs[] = array("DIV" => "edit_".htmlspecialcharsbx($profileId), "TAB" => htmlspecialcharsbx($arProfile["TITLE"]), "TITLE" => htmlspecialcharsbx($arProfile["TITLE"]));
		}
	}
}
//$aTabs[] = array("DIV" => "editbase_profiles", "TAB" => GetMessage('SALE_DH_EDIT_PROFILES'), "TITLE" => GetMessage('SALE_DH_EDIT_PROFILES_DESCR'));
//$aTabs[] = array("DIV" => "delivery2pay", "TAB" => GetMessage('SALE_TAB_DELIVERY_PAY'), "TITLE" => GetMessage('SALE_TAB_DELIVERY_PAY_DESC'));

$tabControl = new CAdminViewTabControl("tabControl", $aTabs, true, false);
$parentTabControl = new CAdminTabControl('parentTabControl', array(
	array("DIV" => "edit_main", "TAB" => GetMessage('SALE_DH_TAB_TITLE_EDIT'), "ICON" => "sale", "TITLE" => GetMessage('SALE_DH_TAB_TITLE_EDIT_ALT'))
), true, true);

$aContext = array(
	array(
		"TEXT" => GetMessage("SALE_DH_LIST"),
		"LINK" => "sale_delivery_handlers.php?lang=".LANG,
		"TITLE" => GetMessage("SALE_DH_LIST_ALT"),
		"ICON" => "btn_list"
	),
);

$obContextMenu = new CAdminContextMenu($aContext);
$obContextMenu->Show();

$arConfigValues = array();
foreach ($arDeliveryInfo[$SITE_ID]["CONFIG"]["CONFIG"] as $config_id => $arConfig)
{
	if ($arConfig["TYPE"] != "MULTISELECT")
		$arConfigValues[$config_id] = $arConfig["VALUE"] <> '' ? $arConfig["VALUE"] : $arConfig["DEFAULT"];
	else
	{
		if (is_set($arConfig["VALUE"]) && !is_array($arConfig["VALUE"]))
			$arConfig["VALUE"] = array("0" => $arConfig["VALUE"]);

		if (!is_set($arConfig["VALUE"]) && is_set($arConfig["DEFAULT"]) && !is_array($arConfig["DEFAULT"]))
			$arConfig["DEFAULT"] = array("0" => $arConfig["DEFAULT"]);

		$arConfigValues[$config_id] = count($arConfig["VALUE"]) > 0 ? $arConfig["VALUE"] : $arConfig["DEFAULT"];
	}
}
?>
<script language="JavaScript">
var arStructure = <?=CUtil::PhpToJSObject($arDeliveryInfo)?>;

</script>
<script language="javascript">
var cur_site = '<?=htmlspecialcharsbx(CUtil::JSEscape($siteList[0]["ID"]))?>';
function changeSiteList(value)
{
	var SLHandler = document.getElementById('site_id');
	SLHandler.disabled = value;
}

function selectSite(current)
{
	if (current == cur_site) return;

	ShowWaitWindow();

	var CSHandler = document.getElementById('current_site');
	var FormHandler = document.forms.form1;

	for (var i in arStructure[cur_site])
	{
		if (i == 'CONFIG')
		{
			for (var j in arStructure[cur_site]['CONFIG']['CONFIG'])
			{

				var obElement = FormHandler['HANDLER[CONFIG][' + j + ']'];

				if (obElement)
				{
					try
					{
						if (obElement.type == 'checkbox')
						{
							arStructure[cur_site]['CONFIG']['CONFIG'][j]['VALUE'] = obElement.checked ? 'Y' : 'N';

							if (current != null)
							{
								if (arStructure[current]['CONFIG']['CONFIG'][j]['VALUE'] && arStructure[current]['CONFIG']['CONFIG'][j]['VALUE'].length > 0)
									obElement.checked = arStructure[current]['CONFIG']['CONFIG'][j]['VALUE'] == 'Y';
								else
									obElement.checked = arStructure[current]['CONFIG']['CONFIG'][j]['DEFAULT'] == 'Y';
							}
						}
						else if (obElement.type == "select-multiple")
						{
							var selectVal = '';
							var arSelectVal = [];

							for (x=0;x<=obElement.length-1;x++)
							{
								if(obElement.options[x].selected)
								{
									if (selectVal.length > 0)
										selectVal += ",";

									arSelectVal[obElement.options[x].value] = obElement.options[x].value;
									selectVal += obElement.options[x].value;
								}
							}

							arStructure[cur_site]['CONFIG']['CONFIG'][j]['VALUE'] = arSelectVal;
						}
						else
						{
							if (obElement.length > 0)
							{
								if (obElement.type == "select-one")
									arStructure[cur_site]['CONFIG']['CONFIG'][j]['VALUE'] = obElement.value;
								else
								{
									for(i=0;i<obElement.length;i++)
									{
										if (obElement[i].type == 'radio' && obElement[i].checked)
											arStructure[cur_site]['CONFIG']['CONFIG'][j]['VALUE'] = obElement[i].value;
									}
								}
							}
							else
							{
								arStructure[cur_site]['CONFIG']['CONFIG'][j]['VALUE'] = obElement.value;
							}

							if (current != null)
								if (arStructure[current]['CONFIG']['CONFIG'][j]['VALUE'] && arStructure[current]['CONFIG']['CONFIG'][j]['VALUE'].length > 0)
									obElement.value = arStructure[current]['CONFIG']['CONFIG'][j]['VALUE'];
								else
									obElement.value = arStructure[current]['CONFIG']['CONFIG'][j]['DEFAULT'];
						}
					}
					catch (e)
					{
						alert('Error in config');
					}
				}
			}
		}
		else if (i == 'PROFILES')
		{
			for (var j in arStructure[cur_site]['PROFILES'])
			{
				for (var k in arStructure[cur_site]['PROFILES'][j])
				{
					if (k == 'RESTRICTIONS_WEIGHT' || k == 'RESTRICTIONS_SUM' || k == 'RESTRICTIONS_DIMENSIONS')
					{
						if (arStructure[cur_site]['PROFILES'][j][k].length <= 1)
						{
							if(k == 'RESTRICTIONS_SUM')
								arStructure[cur_site]['PROFILES'][j][k] = {0:0,1:0,2:0};
							else if(k == 'RESTRICTIONS_DIMENSIONS')
								arStructure[cur_site]['PROFILES'][j][k] = {0:0,1:0,2:0};
							else
								arStructure[cur_site]['PROFILES'][j][k] = {0:0,1:0};
						}

						for (var l in arStructure[cur_site]['PROFILES'][j][k])
						{
							var obElement = FormHandler['HANDLER[PROFILES][' + j + '][' + k + '][' + l + ']'];
							if (obElement)
							{
								try
								{
									arStructure[cur_site]['PROFILES'][j][k][l] = obElement.value;

									if (current != null)
										obElement.value = arStructure[current]['PROFILES'][j][k][l];
								}
								catch (e)
								{
									alert('Error in config');
								}
							}

						}
					}
					else
					{
						var obElement = FormHandler['HANDLER[PROFILES][' + j + '][' + k + ']'];
						if (obElement)
						{
							try
							{
								if (obElement.type == 'checkbox')
								{
									arStructure[cur_site]['PROFILES'][j][k] = obElement.checked ? 'Y' : 'N';
									if (current != null)
										obElement.checked = arStructure[current]['PROFILES'][j][k] == 'Y';
								}
								else
								{
									arStructure[cur_site]['PROFILES'][j][k] = obElement.value;
									if (current != null)
										obElement.value = arStructure[current]['PROFILES'][j][k];
								}
							}
							catch (e)
							{
								alert('Error in config');
							}
						}
					}
				}
			}
		}
		else
		{
			var obElement = FormHandler['HANDLER['+ i + ']']
			if (obElement)
			{
				try
				{
					if (obElement.type == 'checkbox')
					{
						arStructure[cur_site][i] = obElement.checked ? 'Y' : 'N';
						if (current != null)
						{
							obElement.checked = arStructure[current][i] == 'Y';
							if (i == 'PROFILE_USE_DEFAULT')
								changeProfiles(obElement.checked);
						}
					}
					else
					{
						arStructure[cur_site][i] = obElement.value;

						if (current != null)
							obElement.value = arStructure[current][i];
					}
				}
				catch (e)
				{
					alert('Error');
				}
			}
		}
	}

	if (current != null)
	{
		cur_site = current;
		CSHandler.value = current;
	}

	CloseWaitWindow();

	return;
}

function changeProfiles(flag)
{
	obElement = document.getElementById('PROFILES_DIV');
	obElement.style.display = flag ? 'none' : 'block';
}

function prepareData()
{
	selectSite();

	var structure = JSON.stringify(arStructure);
	BX('STRUCTURE').value = encodeURIComponent(structure);

	return true;
}

function setLHEClass(lheDivId)
{
	BX.ready(
		function(){
			var lheDivObj = BX(lheDivId);

			if(lheDivObj)
				BX.addClass(lheDivObj, 'bxlhe_frame_hndl_dscr');
	});
}

function hideFormElementsByNames(cbObject, aElNames)
{
	if(!aElNames || !cbObject)
		return;

	for (var i = aElNames.length - 1; i >= 0; i--)
	{
		var elName = "HANDLER[CONFIG]["+aElNames[i]+"]";

		if(!document.forms["form1"] || !document.forms["form1"][elName])
			return;

		var elObj = document.forms["form1"][elName];

		if(elObj.parentNode.parentNode)
			elObj.parentNode.parentNode.style.display = cbObject.checked ? "" : "none";
	}
}
</script>

<?CAdminMessage::ShowMessage($errorsList);?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>" name="form1" onSubmit='return prepareData()' enctype="multipart/form-data">

	<input type="hidden" name="lang" value="<?echo LANG ?>" />
	<input type="hidden" name="Update" value="Y" />
	<input type="hidden" name="SID" value="<?echo htmlspecialcharsbx($SID) ?>" />
	<input type="hidden" name="STRUCTURE" id="STRUCTURE" value="" />
	<?=bitrix_sessid_post()?>
<?
$parentTabControl->Begin();
$parentTabControl->BeginNextTab();
?>
	<tr>
		<td width="50%">
			<label for="USE_DIFF_SITES_SETTINGS"><?=GetMessage('SALE_DH_USE_DIFF_SITES_SETTINGS')?>:</label>
		</td>
		<td width="50%">
			<input type="checkbox" name="USE_DIFF_SITES_SETTINGS" id="USE_DIFF_SITES_SETTINGS"<?=$bSites ? " checked=\"checked\"" : ""?> onclick="changeSiteList(!this.checked)" value="Y" />
		</td>
	</tr>
	<tr>
		<td>
			<?=GetMessage("SALE_DH_SITES_LIST")?>:
		</td>
		<td><select name="site" id="site_id"<? if(!$bSites) echo " disabled=\"disabled\""; ?> onChange="selectSite(this.value)">
			<?
				for($i = 0; $i < $siteCount; $i++)
					echo "<option value=\"".htmlspecialcharsbx($siteList[$i]["ID"])."\" ".($i == 0 ? "selected=\"selected\"" : "").">".htmlspecialcharsbx($siteList[$i]["NAME"])."</option>";
			?></select><input type="hidden" name="current_site" id="current_site" value="<?=htmlspecialcharsbx($siteList[0]["ID"]);?>" /></td>
	</tr>
	<tr>
		<td colspan="2">
<?
if ($deliveryHint <> '')
{
	echo BeginNote();
	echo $deliveryHint;
	echo EndNote();
}

$tabControl->Begin();

// base config tab
$tabControl->BeginNextTab();
?>
<table border="0" cellspacing="0" cellpadding="0" width="100%" class="edit-table" id="base_params_table">
<tr>
	<td width="40%" class="field-name"><?=GetMessage('SALE_DH_HANDLER_PATH')?></td>
	<td width="60%"><b><?=htmlspecialcharsbx($handlerPath)?></b></td>
</tr>
<tr>
	<td class="field-name"><?=GetMessage('SALE_DH_HANDLER_ACTIVE')?></td>
	<td><input type="checkbox" name="HANDLER[ACTIVE]" value="Y" <?=($arDeliveryInfo[$SITE_ID]["ACTIVE"] == "Y" ? "checked=\"checked\"" : "")?> /></td>
</tr>
<tr>
	<td class="field-name"><?=GetMessage('SALE_DH_HANDLER_SORT')?></td>
	<td><input type="text" name="HANDLER[SORT]" value="<?=intval($arDeliveryInfo[$SITE_ID]["SORT"])?>" size="3" /></td>
</tr>
<tr>
	<td class="field-name"><?=GetMessage('SALE_DH_HANDLER_NAME')?></td>
	<td><input type="text" name="HANDLER[NAME]" value="<?=htmlspecialcharsbx($arDeliveryInfo[$SITE_ID]["NAME"])?>" /></td>
</tr>
<tr>
	<td class="field-name"><?=GetMessage('SALE_DH_HANDLER_CURRENCY')?></td>
	<td><?=CCurrency::SelectBox('HANDLER[BASE_CURRENCY]', htmlspecialcharsbx($arDeliveryInfo[$SITE_ID]["BASE_CURRENCY"]))?></td>
</tr>
<tr>
	<td valign="top" class="field-name"><?=GetMessage('SALE_DH_HANDLER_DESCRIPTION')?></td>
	<td valign="top">
		<?=wrapDescrLHE(
			'HANDLER[DESCRIPTION]',
			isset($arDeliveryInfo[$SITE_ID]["DESCRIPTION"]) ? $arDeliveryInfo[$SITE_ID]["DESCRIPTION"] : '',
			'hndl_dscr');?>
		<script language="JavaScript">setLHEClass('bxlhe_frame_hndl_dscr'); </script>
	</td>
</tr>
<tr>
	<td class="field-name"><?=GetMessage('SALE_DH_HANDLER_TAX_RATE')?> %:</td>
	<td><input type="text" name="HANDLER[TAX_RATE]" value="<?=doubleval($arDeliveryInfo[$SITE_ID]["TAX_RATE"])?>" size="3" /></td>
</tr>
<tr>
	<td width="40%"><?=GetMessage('SDEN_LOGOTIP');?></td>
	<td width="60%">
		<div><input type="file" name="LOGOTIP"></div>
		<?if (count($arDeliveryInfo[$SITE_ID]["LOGOTIP"]) > 0):?>
			<br>
			<?
			echo CFile::ShowImage($arDeliveryInfo[$SITE_ID]["LOGOTIP"], 150, 150, "border=0", "", false);
			?>
			<br />
			<div>
				<input type="checkbox" name="LOGOTIP_del" value="Y" id="LOGOTIP_del" >
				<label for="LOGOTIP_del"><?=GetMessage("SDEN_LOGOTIP_DEL");?></label>
			</div>
		<?endif;?>
	</td>
</tr>
</table>

<?

CModule::IncludeModule('currency');

// config tabs
foreach ($arDeliveryInfo[$SITE_ID]["CONFIG"]["CONFIG_GROUPS"] as $group => $arConfigGroup)
{
	$tabControl->BeginNextTab();
	?><table border="0" cellspacing="0" cellpadding="0" width="100%" class="edit-table" id="params_<?=htmlspecialcharsbx($group)?>_table"><?

	//if exist profile witch named such as config group
	if(isset($arDeliveryInfo[$SITE_ID]["PROFILES"][$group]))
	{
		printProfileInfo($SID, $group, $arDeliveryInfo[$SITE_ID]["PROFILES"][$group], $arDeliveryInfo[$SITE_ID]["BASE_CURRENCY"]);
		$arDeliveryInfo[$SITE_ID]["PROFILES"][$group]["TABBED"] = true;
	}

	$arMultiControlQuery = array();

	foreach ($arDeliveryInfo[$SITE_ID]["CONFIG"]["CONFIG"] as $config_id => $arConfig)
	{
		if ($arConfig["GROUP"] == $group)
		{
			if(!empty($arMultiControlQuery)
				&& (
					!isset($arConfig['MCS_ID'])
					|| !array_key_exists($arConfig['MCS_ID'], $arMultiControlQuery)
					)
			)
			{
				echo CSaleHelper::getAdminMultilineControl($arMultiControlQuery);
				$arMultiControlQuery = array();
			}

			$controlHtml = CSaleHelper::getAdminHtml($config_id, $arConfig, "HANDLER[CONFIG]", "form1");

			if($arConfig["TYPE"] == 'MULTI_CONTROL_STRING')
			{
				$arMultiControlQuery[$arConfig['MCS_ID']]['CONFIG'] = $arConfig;
				continue;
			}
			elseif(isset($arConfig['MCS_ID']))
			{
				$arMultiControlQuery[$arConfig['MCS_ID']]['ITEMS'][] = $controlHtml;
				continue;
			}

			echo CSaleHelper::wrapAdminHtml($controlHtml, $arConfig);
		}
	}
	echo CSaleHelper::getAdminMultilineControl($arMultiControlQuery);
	?></table><?
}

// if stayed unprinted profiles
foreach ($arDeliveryInfo[$SITE_ID]["PROFILES"] as $profileId => $arProfile)
{
	if(!isset($arProfile["TABBED"]))
	{
		$tabControl->BeginNextTab();
		?><table border="0" cellspacing="0" cellpadding="0" width="100%" class="edit-table" id="params_<?=htmlspecialcharsbx($profileId)?>_table"><?
			printProfileInfo($SID, $profileId, $arProfile, $arDeliveryInfo[$SITE_ID]["BASE_CURRENCY"]);
		?></table><?
	}
}

$tabControl->End();
?>
	</td>
</tr>
<?
$parentTabControl->Buttons(
		array(
				"disabled" => ($saleModulePermissions < "W"),
				"back_url" => "/bitrix/admin/sale_delivery_handlers.php?lang=".LANG,
			)
	);

$parentTabControl->End();
?>
</form>

<?
require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");

function wrapDescrLHE($inputName, $content = '', $divId = false)
{
	ob_start();
	$ar = array(
		'inputName' => $inputName,
		'height' => '160',
		'width' => '320',
		'content' => $content,
		'bResizable' => true,
		'bManualResize' => true,
		'bUseFileDialogs' => false,
		'bFloatingToolbar' => false,
		'bArisingToolbar' => false,
		'bAutoResize' => true,
		'bSaveOnBlur' => true,
		'toolbarConfig' => array(
			'Bold', 'Italic', 'Underline', 'Strike',
			'CreateLink', 'DeleteLink',
			'Source', 'BackColor', 'ForeColor'
		)
	);

	if($divId)
		$ar['id'] = $divId;

	$LHE = new CLightHTMLEditor;
	$LHE->Show($ar);
	$sVal = ob_get_contents();
	ob_end_clean();

	return $sVal;
}

function printProfileInfo($SID, $profileId, $arProfile, $baseCurrency)
{
	$weight_unit = COption::GetOptionString('catalog', 'weight_unit', GetMessage('SALE_DH_WEIGHT_UNIT_DEFAULT'));
	$weight_koef = COption::GetOptionString('catalog', 'weight_koef', 1);

	?>
		<tr>
			<td class="field-name"><?=GetMessage('SALE_DH_PROFILE_ACTIVE')?></td>
			<td valign="top" width="60%"><input type="checkbox" name="HANDLER[PROFILES][<?=htmlspecialcharsbx($profileId)?>][ACTIVE]" value="Y" <?if ($arProfile['ACTIVE'] == 'Y'):?>checked="checked"<?endif?> /></td>
		</tr>
		<tr>
			<td align="right"><?=GetMessage('SALE_DH_PROFILE_TITLE')?></td>
			<td valign="top" width="60%"><input type="text" name="HANDLER[PROFILES][<?=htmlspecialcharsbx($profileId)?>][TITLE]" value="<?=htmlspecialcharsbx($arProfile["TITLE"])?>" size="25" /></td>
		</tr>
		<tr>
			<td align="right" valign="top"><?=GetMessage('SALE_DH_PROFILE_DESCRIPTION')?></td>
			<td valign="top" width="60%">
			<?=wrapDescrLHE(
				'HANDLER[PROFILES]['.htmlspecialcharsbx($profileId).'][DESCRIPTION]',
				isset($arProfile["DESCRIPTION"]) ? $arProfile["DESCRIPTION"] : '',
				'hndl_dscr_'.$profileId);?>
				<script language="JavaScript">setLHEClass('bxlhe_frame_hndl_dscr_<?=$profileId?>'); </script>
			</td>
		</tr>
		<tr>
			<td align="right"><?=GetMessage('SALE_DH_HANDLER_TAX_RATE')?> %:</td>
			<td valign="top" width="60%"><input type="text" name="HANDLER[PROFILES][<?=htmlspecialcharsbx($profileId)?>][TAX_RATE]" value="<?=floatval($arProfile["TAX_RATE"])?>" size="3" /></td>
		</tr>
		<tr>
			<td align="right"><?=GetMessage('SDEN_PAY_NAME')?>:</td>
			<td valign="top" width="60%">
				<select multiple="multiple" size="5" name="PAY_SYSTEM[<?=$profileId?>][]">
				<?
				$arPaySystemIdSID = array();
				$arPaySystemIdProfile = array();
				$dbRes = CSaleDelivery2PaySystem::GetList(
											array("DELIVERY_ID" => $SID));
				while ($arRes = $dbRes->Fetch())
				{
					if($arRes["DELIVERY_PROFILE_ID"] == $profileId || is_null($arRes["DELIVERY_PROFILE_ID"]))
						$arPaySystemIdProfile[] = $arRes["PAYSYSTEM_ID"];
					else
						$arPaySystemIdSID[] = $arRes["PAYSYSTEM_ID"];
				}

				$dbResultList = CSalePaySystem::GetList(
					array("SORT"=>"ASC", "NAME"=>"ASC"),
					array("ACTIVE" => "Y"),
					false,
					false,
					array("ID", "NAME", "ACTIVE", "SORT", "LID")
				);

				while ($arPayType = $dbResultList->Fetch()):?>
					<option value="<?=intval($arPayType["ID"]);?>" <?=(in_array($arPayType["ID"], $arPaySystemIdProfile) || empty($arPaySystemIdProfile) ? " selected" : "")?>>
						<?=htmlspecialcharsbx($arPayType["NAME"].(!is_null($arPayType["LID"]) ? " (".$arPayType["LID"].")" : ""))?>
					</option>
				<?endwhile;?>
				</select>
			</td>
		</tr>

		<tr class="heading">
			<td colspan="2"><?=GetMessage('SDEN_ORDER_RESTRICT')?></td>
		</tr>
		<tr>
			<td class="field-name"><?=GetMessage('SALE_DH_PROFILE_WEIGHT_RESTRICTIONS')?> <?=htmlspecialcharsbx($weight_unit)?>:</td>
			<td valign="top" width="60%">
				<input type="text" name="HANDLER[PROFILES][<?=htmlspecialcharsbx($profileId)?>][RESTRICTIONS_WEIGHT][0]" value="<?=number_format($arProfile['RESTRICTIONS_WEIGHT'][0] / $weight_koef, 2, '.', '')?>" size="8" />&nbsp;
				<input type="text" name="HANDLER[PROFILES][<?=htmlspecialcharsbx($profileId)?>][RESTRICTIONS_WEIGHT][1]" value="<?=number_format($arProfile['RESTRICTIONS_WEIGHT'][1] / $weight_koef, 2, '.', '')?>" size="8" />
			</td>
		</tr>
		<tr>
			<td class="field-name"><?=GetMessage('SALE_DH_PROFILE_SUM_RESTRICTIONS')?></td>
			<td valign="top" width="60%">
				<input type="text" name="HANDLER[PROFILES][<?=htmlspecialcharsbx($profileId)?>][RESTRICTIONS_SUM][1]" value="<?=number_format(floatval($arProfile['RESTRICTIONS_SUM'][1]), 2, '.', '')?>" size="8" />&nbsp;
				<input type="text" name="HANDLER[PROFILES][<?=htmlspecialcharsbx($profileId)?>][RESTRICTIONS_SUM][2]" value="<?=number_format(floatval($arProfile['RESTRICTIONS_SUM'][2]), 2, '.', '')?>" size="8" />&nbsp;
				<input type="hidden" name="HANDLER[PROFILES][<?=htmlspecialcharsbx($profileId)?>][RESTRICTIONS_SUM][0]" value="<?=$baseCurrency?>">
			</td>
		</tr>
		<tr class="heading">
			<td colspan="2"><?=GetMessage('SDEN_PACKAGE_RESTRICT')?></td>
		</tr>
		<tr>
			<td class="field-name"><?=GetMessage('SDEN_PACKAGE_MAX_DIM')?>:</td>
			<td valign="top" width="60%">
					<input type="text" name="HANDLER[PROFILES][<?=htmlspecialcharsbx($profileId)?>][RESTRICTIONS_DIMENSIONS][0]" value="<?=intval($arProfile['RESTRICTIONS_DIMENSIONS'][0])?>" size="8" />&nbsp;
					<input type="text" name="HANDLER[PROFILES][<?=htmlspecialcharsbx($profileId)?>][RESTRICTIONS_DIMENSIONS][1]" value="<?=intval($arProfile['RESTRICTIONS_DIMENSIONS'][1])?>" size="8" />&nbsp;
					<input type="text" name="HANDLER[PROFILES][<?=htmlspecialcharsbx($profileId)?>][RESTRICTIONS_DIMENSIONS][2]" value="<?=intval($arProfile['RESTRICTIONS_DIMENSIONS'][2])?>" size="8" />
			</td>
		</tr>
		<tr>
			<td class="field-name"><?=GetMessage('SDEN_PACKAGE_MAX_SIZE')?>:</td>
			<td valign="top" width="60%">
					<input type="text" name="HANDLER[PROFILES][<?=htmlspecialcharsbx($profileId)?>][RESTRICTIONS_MAX_SIZE]" value="<?=intval($arProfile['RESTRICTIONS_MAX_SIZE'])?>" size="8" />
			</td>
		</tr>
		<tr>
			<td class="field-name"><?=GetMessage('SDEN_PACKAGE_MAX_DSUM')?>:</td>
			<td valign="top" width="60%">
					<input type="text" name="HANDLER[PROFILES][<?=htmlspecialcharsbx($profileId)?>][RESTRICTIONS_DIMENSIONS_SUM]" value="<?=intval($arProfile['RESTRICTIONS_DIMENSIONS_SUM'])?>" size="8" />
			</td>
		</tr>
	<?
}
?>