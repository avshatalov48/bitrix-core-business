<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$ID = IntVal($ID);

/// redirect to newer version
if(CSaleLocation::isLocationProEnabled())
	LocalRedirect('/bitrix/admin/sale_location_node_edit.php'.($ID ? '?id='.$ID : ''));

ClearVars();

$langCount = 0;
$arSysLangs = Array();
$arSysLangNames = Array();
$db_lang = CLangAdmin::GetList(($b="sort"), ($o="asc"), array("ACTIVE" => "Y"));
while ($arLang = $db_lang->Fetch())
{
	$arSysLangs[$langCount] = $arLang["LID"];
	$arSysLangNames[$langCount] = htmlspecialcharsbx($arLang["NAME"]);
	$langCount++;
}

$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("SLN_TAB_LOCATION"), "ICON" => "sale", "TITLE" => GetMessage("SLN_TAB_LOCATION_DESCR")),
		array("DIV" => "edit2", "TAB" => GetMessage("SLN_TAB_LOCATION_ZIP"), "ICON" => "sale", "TITLE" => GetMessage("SLN_TAB_LOCATION_ZIP_DESCR"))
	);

$tabControl = new CAdminTabControl("tabControl", $aTabs);

$strError = "";
$bInitVars = false;
if ((strlen($save)>0 || strlen($apply)>0) && $REQUEST_METHOD=="POST" && $saleModulePermissions=="W" && check_bitrix_sessid())
{
	$SORT = IntVal($SORT);
	if ($SORT<=0) $SORT = 100;

	//$COUNTRY_ID = IntVal($COUNTRY_ID);

	if ($CHANGE_COUNTRY!="Y") $CHANGE_COUNTRY = "N";
	if ($WITHOUT_CITY!="Y") $WITHOUT_CITY = "N";

	if ($ID>0 && $COUNTRY_ID<=0 && $CHANGE_COUNTRY=="Y")
		$strError .= GetMessage("ERROR_SELECT_COUNTRY")."<br>";

	if (($COUNTRY_ID<=0 || $ID>0 && $COUNTRY_ID>0 && $CHANGE_COUNTRY=="Y") && $COUNTRY_ID != "")
	{
		$COUNTRY_NAME = Trim($COUNTRY_NAME);
		if (strlen($COUNTRY_NAME)<=0)
			$strError .= GetMessage("ERROR_COUNTRY_NAME")."<br>";

		for ($i = 0, $max = count($arSysLangs); $i < $max; $i++)
		{
			${"COUNTRY_NAME_".$arSysLangs[$i]} = Trim(${"COUNTRY_NAME_".$arSysLangs[$i]});
			if (strlen(${"COUNTRY_NAME_".$arSysLangs[$i]})<=0)
				$strError .= GetMessage("ERROR_COUNTRY_NAME_LANG")." [".$arSysLangs[$i]."] ".$arSysLangNames[$i].".<br>";
		}
	}

	if ($WITHOUT_CITY!="Y")
	{
		$CITY_NAME = Trim($CITY_NAME);
		if (strlen($CITY_NAME)<=0)
			$strError .= GetMessage("ERROR_CITY_NAME")."<br>";

		for ($i = 0, $max = count($arSysLangs); $i < $max; $i++)
		{
			${"CITY_NAME_".$arSysLangs[$i]} = Trim(${"CITY_NAME_".$arSysLangs[$i]});
			if (strlen(${"CITY_NAME_".$arSysLangs[$i]})<=0)
				$strError .= GetMessage("ERROR_CITY_NAME_LANG")." [".$arSysLangs[$i]."] ".$arSysLangNames[$i].".<br>";
		}
	}

	//isset region
	if (isset($_POST["REGION_ID"]) && $_POST["REGION_ID"] != "")
	{
		$REGION_ID = Trim($REGION_ID);

		for ($i = 0, $max = count($arSysLangs); $i < $max; $i++)
		{
			${"REGION_NAME_".$arSysLangs[$i]} = Trim(${"REGION_NAME_".$arSysLangs[$i]});
			if (strlen(${"REGION_NAME_".$arSysLangs[$i]})<=0 && $_POST["REGION_ID"] == 0)
				$strError .= GetMessage("ERROR_REGION_NAME_LANG")." [".$arSysLangs[$i]."] ".$arSysLangNames[$i].".<br>";
		}
	}

	if (strlen($strError)<=0)
	{
		$arFields = array(
			"SORT" => $SORT,
			"COUNTRY_ID" => $COUNTRY_ID,
			"CHANGE_COUNTRY" => (($CHANGE_COUNTRY=="Y")?"Y":"N"),
			"WITHOUT_CITY" => (($WITHOUT_CITY=="Y")?"Y":"N"),
			"REGION_ID" => $REGION_ID
			);

		if ($COUNTRY_ID<=0 || $ID>0 && $COUNTRY_ID>0 && $CHANGE_COUNTRY=="Y")
		{
			$arCountry = array(
				"NAME" => $COUNTRY_NAME,
				"SHORT_NAME" => $COUNTRY_SHORT_NAME
				);

			for ($i = 0, $max = count($arSysLangs); $i < $max; $i++)
			{
				$arCountry[$arSysLangs[$i]] = array(
						"LID" => $arSysLangs[$i],
						"NAME" => ${"COUNTRY_NAME_".$arSysLangs[$i]},
						"SHORT_NAME" => ${"COUNTRY_SHORT_NAME_".$arSysLangs[$i]}
					);
			}

			$arFields["COUNTRY"] = $arCountry;
		}

		if ($WITHOUT_CITY!="Y")
		{
			$arCity = array(
				"NAME" => $CITY_NAME,
				"SHORT_NAME" => $CITY_SHORT_NAME
				);
			if ($REGION_ID > 0)
				$regionTmp = $REGION_ID;
			else
				$regionTmp = '';

			$arCity["REGION_ID"] = $regionTmp;

			for ($i = 0, $max = count($arSysLangs); $i < $max; $i++)
			{
				$arCity[$arSysLangs[$i]] = array(
						"LID" => $arSysLangs[$i],
						"NAME" => ${"CITY_NAME_".$arSysLangs[$i]},
						"SHORT_NAME" => ${"CITY_SHORT_NAME_".$arSysLangs[$i]},
					);
			}

			$arFields["CITY"] = $arCity;
		}

		//region
		if (isset($_POST["REGION_ID"]) && $_POST["REGION_ID"] != "")
		{
			$arRegion = array(
				"NAME" => $REGION_NAME,
				"SHORT_NAME" => $REGION_SHORT_NAME
				);

			for ($i = 0, $max = count($arSysLangs); $i < $max; $i++)
			{
				$arRegion[$arSysLangs[$i]] = array(
						"LID" => $arSysLangs[$i],
						"NAME" => ${"REGION_NAME_".$arSysLangs[$i]},
						"SHORT_NAME" => ${"REGION_SHORT_NAME_".$arSysLangs[$i]}
					);
			}

			$arFields["REGION"] = $arRegion;
		}

		$arFields["LOC_DEFAULT"] = "N";
		if (strlen($LOC_DEFAULT) > 0)
			$arFields["LOC_DEFAULT"] = $LOC_DEFAULT;

		if ($ID>0)
		{
			if (!CSaleLocation::Update($ID, $arFields))
				$strError .= GetMessage("ERROR_EDIT_LOCAT")."<br>";
		}
		else
		{
			$ID = CSaleLocation::Add($arFields);
			if (IntVal($ID)<=0)
				$strError .= GetMessage("ERROR_ADD_LOCAT")."<br>";
		}

		if ($ID > 0 && strlen($strError) <= 0)
		{
			$arZipList = $_REQUEST["ZIP"];
			CSaleLocation::SetLocationZIP($ID, $arZipList);
		}
	}

	if (strlen($strError)>0) $bInitVars = True;
	else
	{
		if (strlen($save)>0)
			LocalRedirect("sale_location_admin.php?lang=".LANG.GetFilterParams("filter_", false));
		else
			LocalRedirect("sale_location_edit.php?lang=".LANG."&ID=".$ID.GetFilterParams("filter_", false));
	}
}

if ($ID>0)
{
	$db_location = CSaleLocation::GetList(Array("SORT"=>"ASC"), Array("ID"=>$ID), LANG);
	if (!$db_location->ExtractFields("str_"))
	{
		$ID = 0;
	}

	$arZipList = array();
	$rsZipList = CSaleLocation::GetLocationZIP($ID);
	while ($arZip = $rsZipList->Fetch())
	{
		$arZipList[] = $arZip;
	}
}

if ($bInitVars)
{
	$DB->InitTableVarsForEdit("b_sale_location", "", "str_");
	if (is_array($_REQUEST['ZIP']))
	{
		$arZipList = array();
		foreach ($_REQUEST['ZIP'] as $zip)
		{
			$arZipList[] = array('ZIP' => $zip);
		}
	}
}

if (!is_array($arZipList))
	$arZipList = array();

$sDocTitle = ($ID>0) ? str_replace("#ID#", $ID, GetMessage("SALE_EDIT_RECORD")) : GetMessage("SALE_NEW_RECORD");
$APPLICATION->SetTitle($sDocTitle);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
?>

<?
$aMenu = array(
		array(
				"TEXT" => GetMessage("SLN_2FLIST"),
				"ICON" => "btn_list",
				"LINK" => "/bitrix/admin/sale_location_admin.php?lang=".LANG.GetFilterParams("filter_")
			)
	);

if ($ID > 0 && $saleModulePermissions >= "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
			"TEXT" => GetMessage("SLN_NEW_LOCATION"),
			"ICON" => "btn_new",
			"LINK" => "/bitrix/admin/sale_location_edit.php?lang=".LANG.GetFilterParams("filter_")
		);

	$aMenu[] = array(
			"TEXT" => GetMessage("SLN_DELETE_LOCATION"),
			"ICON" => "btn_delete",
			"LINK" => "javascript:if(confirm('".GetMessage("SLN_DELETE_LOCATION_CONFIRM")."')) window.location='/bitrix/admin/sale_location_admin.php?action=delete&ID[]=".$ID."&lang=".LANG."&".bitrix_sessid_get()."#tb';",
		);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?
CAdminMessage::ShowMessage($strError);
?>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="fform">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<input type="hidden" name="ID" value="<?echo $ID ?>">
<?=bitrix_sessid_post()?>

<?
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>

	<tr>
		<td width="40%">ID:</td>
		<td width="60%">
			<?if ($ID>0):?><?echo $ID ?><?else:?><?echo GetMessage("SALE_NEW")?><?endif;?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SALE_SORT")?>:</td>
		<td>
			<input type="text" name="SORT" value="<?echo $str_SORT ?>" size="10">
		</td>
	</tr>


	<tr>
		<td><?echo GetMessage("SALE_DEFAULT_LOC")?>:</td>
		<td>
			<input type="checkbox" name="LOC_DEFAULT" value="Y" <?if ($str_LOC_DEFAULT=="Y") echo "checked";?>>
		</td>
	</tr>


	<tr class="heading">
		<td colspan="2"><?echo GetMessage("F_COUNTRY")?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td>
			<?echo GetMessage("F_COUNTRY") ?>:
		</td>
		<td>
			<script language="JavaScript">
			function SetEnabled(enabled)
			{
				if ('['+document.fform.COUNTRY_NAME.type+']'=="[undefined]")
					document.fform.COUNTRY_NAME[document.fform.COUNTRY_NAME.length-1].disabled = !enabled;
				else
					document.fform.COUNTRY_NAME.disabled = !enabled;

				if ('['+document.fform.COUNTRY_SHORT_NAME.type+']'=="[undefined]")
					document.fform.COUNTRY_SHORT_NAME[document.fform.COUNTRY_SHORT_NAME.length-1].disabled = !enabled;
				else
					document.fform.COUNTRY_SHORT_NAME.disabled = !enabled;

<?
				for ($i = 0, $max = count($arSysLangs); $i < $max; $i++):
					$l = CUtil::JSEscape($arSysLangs[$i])
?>
					if ('['+document.fform.COUNTRY_NAME_<?=$l?>.type+']'=="[undefined]")
						document.fform.COUNTRY_NAME_<?=$l?>[document.fform.COUNTRY_NAME_<?=$l?>.length-1].disabled = !enabled;
					else
						document.fform.COUNTRY_NAME_<?=$l?>.disabled = !enabled;

					if ('['+document.fform.COUNTRY_SHORT_NAME_<?=$l?>.type+']'=="[undefined]")
						document.fform.COUNTRY_SHORT_NAME_<?=$l?>[document.fform.COUNTRY_SHORT_NAME_<?=$l?>.length-1].disabled = !enabled;
					else
						document.fform.COUNTRY_SHORT_NAME_<?=$l?>.disabled = !enabled;
				<?endfor;?>
			}

			function SetContact()
			{
				COUNTRY_LIST = eval("document.fform.COUNTRY_ID");
				CHANGE_COUNTRY = eval("document.fform.CHANGE_COUNTRY");

				<?if ($ID>0):?>
				if (parseInt(COUNTRY_LIST.selectedIndex)==0)
				{
					CHANGE_COUNTRY.checked = false;
				}
				<?endif;?>

				if (parseInt(COUNTRY_LIST.selectedIndex)==0 <?if ($ID>0) echo "|| CHANGE_COUNTRY.checked";?>)
				{
					SetEnabled(true);
				}
				else
				{
					SetEnabled(false);
				}
			}
			</script>

			<select name="COUNTRY_ID" OnChange="SetContact()">
				<option value="0"><?echo GetMessage("NEW_COUNTRY")?></option>
				<option value="" <?if ((isset($COUNTRY_ID) && $COUNTRY_ID == "") || $str_COUNTRY_ID == "0") echo " selected";?>><?echo GetMessage("WITHOUT_COUNTRY")?></option>
				<?
				$db_contList = CSaleLocation::GetCountryList(Array("NAME"=>"ASC"), Array(), LANG);
				while ($arContList = $db_contList->Fetch())
				{
					?><option value="<?echo $arContList["ID"] ?>"<?if (IntVal($arContList["ID"])==IntVal($str_COUNTRY_ID)) echo " selected";?>><?echo htmlspecialcharsbx($arContList["NAME_ORIG"]) ?> [<?echo htmlspecialcharsbx($arContList["NAME_LANG"]) ?>]</option><?
				}
				?>
			</select>

		</td>
	</tr>
	<tr>
		<td colspan="2" align="center"><b><?echo GetMessage("F_OR") ?></b></td>
	</tr>
	<tr>
		<td colspan="2" align="center"><?echo GetMessage("SALE_NEW_CNTR") ?></td>
	</tr>
	<?if ($ID>0):?>
		<tr>
			<td>
				<?echo GetMessage("SALE_CHANGE_CNTR")?>:
			</td>
			<td>
				<input type="checkbox" name="CHANGE_COUNTRY" value="Y" <?if ($CHANGE_COUNTRY=="Y") echo "checked";?> OnClick="SetContact()">
			</td>
		</tr>
	<?endif;?>
	<?
	$arCountry = CSaleLocation::GetCountryByID($str_COUNTRY_ID);
	$str_COUNTRY_NAME = htmlspecialcharbx($arCountry["NAME"]);
	$str_COUNTRY_SHORT_NAME = htmlspecialcharbx($arCountry["SHORT_NAME"]);
	if ($bInitVars && $CHANGE_COUNTRY == 'Y')
	{
		$str_COUNTRY_NAME = htmlspecialcharbx($COUNTRY_NAME);
		$str_COUNTRY_SHORT_NAME = htmlspecialcharbx($COUNTRY_SHORT_NAME);
	}
	?>
	<tr class="adm-detail-required-field">
		<td>
			<?echo GetMessage("SALE_FULL_NAME")?>:
		</td>
		<td>
			<input type="text" name="COUNTRY_NAME" value="<?echo $str_COUNTRY_NAME ?>" size="30">
		</td>
	</tr>
	<tr>
		<td>
			<?echo GetMessage("SALE_SHORT_NAME")?>:
		</td>
		<td>
			<input type="text" name="COUNTRY_SHORT_NAME" value="<?echo $str_COUNTRY_SHORT_NAME ?>" size="30">
		</td>
	</tr>
	<?
	for ($i = 0, $max = count($arSysLangs); $i < $max; $i++):
		$arCountry = CSaleLocation::GetCountryLangByID($str_COUNTRY_ID, $arSysLangs[$i]);
		$str_COUNTRY_NAME = htmlspecialcharbx($arCountry["NAME"]);
		$str_COUNTRY_SHORT_NAME = htmlspecialcharbx($arCountry["SHORT_NAME"]);
		if ($bInitVars && $CHANGE_COUNTRY == 'Y')
		{
			$str_COUNTRY_NAME = htmlspecialcharbx(${"COUNTRY_NAME_".$arSysLangs[$i]});
			$str_COUNTRY_SHORT_NAME = htmlspecialcharbx(${"COUNTRY_SHORT_NAME_".$arSysLangs[$i]});
		}
		?>
		<tr>
			<td valign="top" align="center" colspan="2">
				<b>[<?echo $arSysLangs[$i];?>] <?echo $arSysLangNames[$i];?></b>
			</td>
		</tr>
		<tr class="adm-detail-required-field">
			<td>
				<?echo GetMessage("SALE_FULL_NAME")?>:
			</td>
			<td>
				<input type="text" name="COUNTRY_NAME_<?=$arSysLangs[$i]?>" value="<?echo $str_COUNTRY_NAME ?>" size="30">
			</td>
		</tr>
		<tr>
			<td>
				<?echo GetMessage("SALE_SHORT_NAME")?>:
			</td>
			<td>
				<input type="text" name="COUNTRY_SHORT_NAME_<?=$arSysLangs[$i]?>" value="<?echo $str_COUNTRY_SHORT_NAME ?>" size="30">
			</td>
		</tr>
		<?
	endfor;
	?>

	<tr class="heading">
		<td colspan="2"><?echo GetMessage("SALE_REGION")?>
			<script>
				function ResetRegion(woutCity)
				{
					var regonVal = BX('REGION_ID').value;


					if ((regonVal == "" || regonVal > 0) && woutCity != 'Y')
					{
						enabled = false;
					}
					else
					{
						enabled = true;
					}

					if ('['+document.fform.REGION_NAME.type+']'=="[undefined]")
						document.fform.REGION_NAME[document.fform.REGION_NAME.length-1].disabled = !enabled;
					else
						document.fform.REGION_NAME.disabled = !enabled;

					if ('['+document.fform.REGION_SHORT_NAME.type+']'=="[undefined]")
						document.fform.REGION_SHORT_NAME[document.fform.REGION_SHORT_NAME.length-1].disabled = !enabled;
					else
						document.fform.REGION_SHORT_NAME.disabled = !enabled;

					<?
					for ($i = 0, $max = count($arSysLangs); $i < $max; $i++):
						$l = CUtil::JSEscape($arSysLangs[$i])
					?>
						if ('['+document.fform.REGION_NAME_<?=$l?>.type+']'=="[undefined]")
							document.fform.REGION_NAME_<?=$l?>[document.fform.REGION_NAME_<?=$l?>.length-1].disabled = !enabled;
						else
							document.fform.REGION_NAME_<?=$l?>.disabled = !enabled;

						if ('['+document.fform.REGION_SHORT_NAME_<?=$l?>.type+']'=="[undefined]")
							document.fform.REGION_SHORT_NAME_<?=$l?>[document.fform.REGION_SHORT_NAME_<?=$l?>.length-1].disabled = !enabled;
						else
							document.fform.REGION_SHORT_NAME_<?=$l?>.disabled = !enabled;
					<?endfor;?>
				}
			</script>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td>
			<?echo GetMessage("F_REGION")?>:
		</td>
		<td>
			<select name="REGION_ID" id="REGION_ID" OnChange="ResetRegion()">
				<option value="0"><?echo GetMessage("NEW_REGION")?></option>
				<option value="" <?if ((isset($REGION_ID) && $REGION_ID == "") || $str_REGION_ID == "" || $str_REGION_ID == "0") echo " selected";?> ><?echo GetMessage("WITHOUT_REGION")?></option>
				<?
				$arFilterRegion = array();
				if (isset($str_COUNTRY_ID) && $str_COUNTRY_ID > 0)
					$arFilterRegion["COUNTRY_ID"] = $str_COUNTRY_ID;

				$dbRegionList = CSaleLocation::GetRegionList(array("NAME"=>"ASC"), $arFilterRegion, LANG);
				while ($arRegionList = $dbRegionList->Fetch())
				{
					?><option value="<?echo $arRegionList["ID"] ?>"<?if (IntVal($arRegionList["ID"])==IntVal($str_REGION_ID)) echo " selected";?>><?echo htmlspecialcharsbx($arRegionList["NAME_ORIG"]) ?> [<?echo htmlspecialcharsbx($arRegionList["NAME_LANG"]) ?>]</option><?
				}
				?>
			</select>
		</td>
	</tr>
	<?
	$arRegion = CSaleLocation::GetRegionByID($str_REGION_ID);
	$str_REGION_NAME = htmlspecialcharbx($arRegion["NAME"]);
	$str_REGION_SHORT_NAME = htmlspecialcharbx($arRegion["SHORT_NAME"]);


	if ($arCity = CSaleLocation::GetCityByID($str_CITY_ID))
	{
		$str_CITY_NAME = htmlspecialcharbx($arCity["NAME"]);
		$str_CITY_SHORT_NAME = htmlspecialcharbx($arCity["SHORT_NAME"]);
		$str_WITHOUT_CITY = "N";
	}
	else
	{
		if ($ID>0)
			$str_WITHOUT_CITY = "Y";
		else
			$str_WITHOUT_CITY = "N";
	}
	if ($bInitVars)
	{
		$str_CITY_NAME = htmlspecialcharbx($CITY_NAME);
		$str_CITY_SHORT_NAME = htmlspecialcharbx($CITY_SHORT_NAME);
		$str_WITHOUT_CITY = (($WITHOUT_CITY=="Y") ? "Y" : "N");
	}

	?>
	<tr class="adm-detail-required-field">
		<td>
			<?echo GetMessage("SALE_FULL_NAME")?>:
		</td>
		<td>
			<input type="text" name="REGION_NAME" value="<?echo $str_REGION_NAME ?>" size="30">
		</td>
	</tr>
	<tr>
		<td>
			<?echo GetMessage("SALE_SHORT_NAME")?>:
		</td>
		<td>
			<input type="text" name="REGION_SHORT_NAME" value="<?echo $str_REGION_SHORT_NAME ?>" size="30">
		</td>
	</tr>
	<?
	for ($i = 0, $max = count($arSysLangs); $i < $max; $i++):
		$arRegion = CSaleLocation::GetRegionLangByID($str_REGION_ID, $arSysLangs[$i]);
		$str_REGION_NAME = htmlspecialcharbx($arRegion["NAME"]);
		$str_REGION_SHORT_NAME = htmlspecialcharbx($arRegion["SHORT_NAME"]);
		if ($bInitVars && $str_WITHOUT_CITY == "Y" && IntVal($str_REGION_ID) > 0)
		{
			$str_REGION_NAME = htmlspecialcharbx(${"REGION_NAME_".$arSysLangs[$i]});
			$str_REGION_SHORT_NAME = htmlspecialcharbx(${"REGION_SHORT_NAME_".$arSysLangs[$i]});
		}
		?>
		<tr>
			<td align="center" colspan="2">
				<b>[<?=$arSysLangs[$i]?>] <?echo $arSysLangNames[$i] ?></b>
			</td>
		</tr>
		<tr class="adm-detail-required-field">
			<td>
				<?echo GetMessage("SALE_FULL_NAME")?>:
			</td>
			<td>
				<input type="text" name="REGION_NAME_<?=$arSysLangs[$i]?>" value="<?echo $str_REGION_NAME ?>" size="30">
			</td>
		</tr>
		<tr>
			<td>
				<?echo GetMessage("SALE_SHORT_NAME")?>:
			</td>
			<td>
				<input type="text" name="REGION_SHORT_NAME_<?=$arSysLangs[$i]?>" value="<?echo $str_REGION_SHORT_NAME ?>" size="30">
			</td>
		</tr>
		<?
	endfor;
	?>

	<tr class="heading">
		<td colspan="2"><?echo GetMessage("SALE_CITY")?></td>
	</tr>

	<tr>
		<td>
			<?echo GetMessage("SALE_WITHOUT_CITY")?>:
		</td>
		<td>
			<input type="checkbox" name="WITHOUT_CITY" id="WITHOUT_CITY" value="Y" <?if ($str_WITHOUT_CITY=="Y") echo "checked";?>>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td>
			<?echo GetMessage("SALE_FULL_NAME")?>:
		</td>
		<td>
			<input type="text" name="CITY_NAME" value="<?echo $str_CITY_NAME ?>" size="30">
		</td>
	</tr>
	<tr>
		<td>
			<?echo GetMessage("SALE_SHORT_NAME")?>:
		</td>
		<td>
			<input type="text" name="CITY_SHORT_NAME" value="<?echo $str_CITY_SHORT_NAME ?>" size="30">
		</td>
	</tr>
	<?
	for ($i = 0, $max = count($arSysLangs); $i < $max; $i++):
		$arCity = CSaleLocation::GetCityLangByID($str_CITY_ID, $arSysLangs[$i]);
		$str_CITY_NAME = htmlspecialcharbx($arCity["NAME"]);
		$str_CITY_SHORT_NAME = htmlspecialcharbx($arCity["SHORT_NAME"]);
		if ($bInitVars)
		{
			$str_CITY_NAME = htmlspecialcharbx(${"CITY_NAME_".$arSysLangs[$i]});
			$str_CITY_SHORT_NAME = htmlspecialcharbx(${"CITY_SHORT_NAME_".$arSysLangs[$i]});
		}
		?>
		<tr>
			<td align="center" colspan="2">
				<b>[<?=$arSysLangs[$i]?>] <?echo $arSysLangNames[$i] ?></b>
			</td>
		</tr>
		<tr class="adm-detail-required-field">
			<td>
				<?echo GetMessage("SALE_FULL_NAME")?>:
			</td>
			<td>
				<input type="text" name="CITY_NAME_<?=$arSysLangs[$i]?>" value="<?echo $str_CITY_NAME ?>" size="30">
			</td>
		</tr>
		<tr>
			<td>
				<?echo GetMessage("SALE_SHORT_NAME")?>:
			</td>
			<td>
				<input type="text" name="CITY_SHORT_NAME_<?=$arSysLangs[$i]?>" value="<?echo $str_CITY_SHORT_NAME ?>" size="30">
			</td>
		</tr>
		<?
	endfor;
	?>
	<script language="JavaScript">
		SetContact();
		ResetRegion('<?=$str_WITHOUT_CITY?>');
	</script>
<?
$tabControl->BeginNextTab();
?>
		<tr>
			<td width="40%" valign="top"><?=GetMessage('SALE_ZIP_LIST')?>:</td>
			<td width="60%" valign="top">
			<script language="JavaScript">
			function zip_add()
			{
				var obContainer = document.getElementById('zip_list');
				var obInput = document.createElement('INPUT');
				obInput.type = 'text';
				obInput.name = 'ZIP[]';
				obInput.size = 10;

				obContainer.appendChild(obInput);
				obContainer.appendChild(document.createElement('BR'));

				if (obInput.form && obInput.form.BXAUTOSAVE)
					obInput.form.BXAUTOSAVE.RegisterInput(obInput);

				return false;
			}
			</script>
			<div id="zip_list"><?
			$cnt = count($arZipList);
			for ($i = 0; $i < $cnt; $i++):
?>
				<input type="text" name="ZIP[]" value="<?=htmlspecialcharsbx($arZipList[$i]["ZIP"])?>" size="10" /><br />
<?
			endfor;
			?>
			<input type="text" name="ZIP[]" value="" size="10" id="" /><br />
			</div>
			<button onClick='return zip_add()'><?=GetMessage('SALE_ADD_ZIP')?></button>
			</td>
<script type="text/javascript">
BX.ready(function() {
	BX.addCustomEvent(document.forms.fform, 'onAutoSaveRestore', function(ob, data) {
		if (data['ZIP[]'] && BX.type.isArray(data['ZIP[]']) && data['ZIP[]'].length > <?=$cnt?>)
		{
			for (var i=<?=$cnt?>; i<data['ZIP[]'].length; i++)
				zip_add();
		}
	})
})
</script>
		</tr>
<?
$tabControl->EndTab();
?>

<?
$tabControl->Buttons(
		array(
				"disabled" => ($saleModulePermissions < "W"),
				"back_url" => "/bitrix/admin/sale_location_admin.php?lang=".LANG.GetFilterParams("filter_")
			)
	);
?>

<?
$tabControl->End();
?>

</form>
<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>