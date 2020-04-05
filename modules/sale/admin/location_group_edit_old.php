<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$ID = IntVal($ID);

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

$strError = "";
$bInitVars = false;
if ((strlen($save)>0 || strlen($apply)>0) && $REQUEST_METHOD=="POST" && $saleModulePermissions=="W" && check_bitrix_sessid())
{
	$adminSidePanelHelper->decodeUriComponent();
	$SORT = IntVal($SORT);
	if ($SORT<=0) $SORT = 100;

	if (!is_array($LOCATION_ID) || count($LOCATION_ID)<=0)
		$strError .= GetMessage("ERROR_EMPTY_LOCATION")."<br>";

	$langCnt = count($arSysLangs);
	for ($i = 0; $i<$langCnt; $i++)
	{
		${"NAME_".$arSysLangs[$i]} = Trim(${"NAME_".$arSysLangs[$i]});
		if (strlen(${"NAME_".$arSysLangs[$i]})<=0)
			$strError .= GetMessage("ERROR_EMPTY_NAME")." [".$arSysLangs[$i]."] ".$arSysLangNames[$i].".<br>";
	}

	if (strlen($strError)<=0)
	{
		$arFields = array(
			"SORT" => $SORT,
			"LOCATION_ID" => $LOCATION_ID
			);

		$langCnt = count($arSysLangs);
		for ($i = 0; $i<$langCnt; $i++)
		{
			$arFields["LANG"][] = array(
					"LID" => $arSysLangs[$i],
					"NAME" => ${"NAME_".$arSysLangs[$i]}
				);
		}

		if ($ID>0)
		{
			if (!CSaleLocationGroup::Update($ID, $arFields))
				$strError .= GetMessage("ERROR_EDIT_GROUP")."<br>";
		}
		else
		{
			$ID = CSaleLocationGroup::Add($arFields);
			if (IntVal($ID)<=0)
				$strError .= GetMessage("ERROR_ADD_GROUP")."<br>";
		}
	}

	if (strlen($strError) > 0)
	{
		$adminSidePanelHelper->sendJsonErrorResponse($strError);
		$bInitVars = true;
	}

	$adminSidePanelHelper->sendSuccessResponse("base");

	if (strlen($save)>0 && strlen($strError)<=0)
		LocalRedirect("sale_location_group_admin.php?lang=".LANG.GetFilterParams("filter_", false));
}

if ($ID>0)
{
	$db_location = CSaleLocationGroup::GetList(Array("NAME"=>"ASC"), Array("ID"=>$ID));
	if (!$db_location->ExtractFields("str_"))
	{
		$ID = 0;
	}
}

if ($bInitVars)
{
	$DB->InitTableVarsForEdit("b_sale_location_group", "", "str_");
}

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
				"TEXT" => GetMessage("SLGEN_2FLIST"),
				"ICON" => "btn_list",
				"LINK" => "/bitrix/admin/sale_location_group_admin.php?lang=".LANG.GetFilterParams("filter_")
			)
	);

if ($ID > 0 && $saleModulePermissions >= "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
			"TEXT" => GetMessage("SLGEN_NEW_LGROUP"),
			"ICON" => "btn_new",
			"LINK" => "/bitrix/admin/sale_location_group_edit.php?lang=".LANG.GetFilterParams("filter_")
		);

	$aMenu[] = array(
			"TEXT" => GetMessage("SLGEN_DELETE_LGROUP"),
			"ICON" => "btn_delete",
			"LINK" => "javascript:if(confirm('".GetMessage("SLGEN_DELETE_LGROUP_CONFIRM")."')) window.location='/bitrix/admin/sale_location_group_admin.php?action=delete&ID[]=".$ID."&lang=".LANG."&".bitrix_sessid_get()."#tb';",
		);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?CAdminMessage::ShowMessage($strError);?>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="fform">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<input type="hidden" name="ID" value="<?echo $ID ?>">
<?=bitrix_sessid_post()?>

<?
$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("SLGEN_TAB_LGROUP"), "ICON" => "sale", "TITLE" => GetMessage("SLGEN_TAB_LGROUP_DESCR"))
	);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>

	<tr>
		<td width="40%">
			ID:
		</td>
		<td width="60%">
			<?if ($ID>0):?><?echo $ID ?><?else:?><?echo GetMessage("SALE_NEW")?><?endif;?>
		</td>
	</tr>

	<tr>
		<td>
			<?echo GetMessage("SALE_SORT")?>:
		</td>
		<td>
			<input type="text" name="SORT" value="<?echo $str_SORT ?>" size="10">
		</td>
	</tr>

	<tr class="adm-detail-required-field">
		<td valign="top">
			<?echo GetMessage("SALE_LOCATIONS")?>:<br><img src="/bitrix/images/sale/mouse.gif" width="44" height="21" border="0" alt="">
		</td>
		<td valign="top">

			<?
			$db_locs = CSaleLocationGroup::GetLocationList(Array("LOCATION_GROUP_ID"=>$ID));
			$db_vars = CSaleLocation::GetList(Array("COUNTRY_NAME_LANG"=>"ASC", "REGION_NAME_LANG"=>"ASC", "CITY_NAME_LANG"=>"ASC"), array(), LANG);
			?>

			<select name="LOCATION_ID[]" size="10" multiple>
				<?
				$arCurLocs = array();
				while ($arLocs = $db_locs->Fetch())
					$arCurLocs[] = IntVal($arLocs["LOCATION_ID"]);
				if ($bInitVars && is_array($LOCATION_ID)) $arCurLocs = $LOCATION_ID;
				?>
				<?while ($vars = $db_vars->Fetch()):
					$locationName = $vars["COUNTRY_NAME"];

					if (strlen($vars["REGION_NAME"]) > 0)
					{
						if (strlen($locationName) > 0)
							$locationName .= " - ";
						$locationName .= $vars["REGION_NAME"];
					}
					if (strlen($vars["CITY_NAME"]) > 0)
					{
						if (strlen($locationName) > 0)
							$locationName .= " - ";
						$locationName .= $vars["CITY_NAME"];
					}
					?>
					<option value="<?echo $vars["ID"]?>"<?if (in_array(IntVal($vars["ID"]), $arCurLocs)) echo " selected"?>><?echo htmlspecialcharsbx($locationName)?></option>
				<?endwhile;?>
			</select>
		</td>
	</tr>
	<?
	$langCnt = count($arSysLangs);
	for ($i = 0; $i<$langCnt; $i++):
		$arGroupLang = CSaleLocationGroup::GetGroupLangByID($ID, $arSysLangs[$i]);
		$str_NAME = htmlspecialcharsEx($arGroupLang["NAME"]);
		if ($bInitVars)
		{
			$str_NAME = htmlspecialcharsEx(${"NAME_".$arSysLangs[$i]});
		}
		?>
		<tr class="heading">
			<td colspan="2">
				[<?echo $arSysLangs[$i];?>] <?echo $arSysLangNames[$i];?>:
			</td>
		</tr>
		<tr class="adm-detail-required-field">
			<td><?echo GetMessage("SALE_NAME")?>:</td>
			<td>
				<input type="text" name="NAME_<?echo $arSysLangs[$i] ?>" value="<?echo $str_NAME ?>" size="30">
			</td>
		</tr>
	<?endfor;?>

<?
$tabControl->EndTab();
?>

<?
$tabControl->Buttons(
		array(
				"disabled" => ($saleModulePermissions < "W"),
				"back_url" => "/bitrix/admin/sale_location_group_admin.php?lang=".LANG.GetFilterParams("filter_")
			)
	);
?>

<?
$tabControl->End();
?>
</form>
<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>