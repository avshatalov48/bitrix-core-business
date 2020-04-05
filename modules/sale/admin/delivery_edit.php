<?

/*
 * Deprecated
 * Use delivery_service_edit.php instead this.
 */

use Bitrix\Sale\Location;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => GetMessage("SDEN_TAB_DELIVERY"),
		"ICON" => "sale",
		"TITLE" => GetMessage("SDEN_TAB_DELIVERY_DESCR"),
	),
	array(
		"DIV" => "edit2",
		"TAB" => GetMessage("SDEN_TAB_PAYSYSTEM"),
		"ICON" => "sale",
		"TITLE" => GetMessage("SDEN_TAB_PAYSYSTEM_DESCR"),
	),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);

ClearVars();

$ID = intval($ID);

$strError = "";
$bInitVars = false;

$lpEnabled = !!CSaleLocation::isLocationProEnabled();

if ((strlen($save)>0 || strlen($apply)>0) && $_SERVER['REQUEST_METHOD']=="POST" && $saleModulePermissions=="W" && check_bitrix_sessid())
{
	$store = "";
	if (isset($_POST["STORE"]) && count($_POST["STORE"]) > 0)
		$store = serialize($_POST["STORE"]);

	$LID = Trim($LID);
	if (strlen($LID)<=0)
		$strError .= GetMessage("ERROR_NO_LID")."<br>";

	$NAME = Trim($NAME);
	if (strlen($NAME)<=0)
		$strError .= GetMessage("ERROR_NO_NAME")."<br>";

	$PRICE = str_replace(",", ".", $PRICE);
	$PRICE = DoubleVal($PRICE);
	if ($PRICE<0)
		$strError .= GetMessage("ERROR_NO_PRICE")."<br>";

	$CURRENCY = Trim($CURRENCY);
	if (strlen($CURRENCY)<=0)
		$strError .= GetMessage("ERROR_NO_CURRENCY")."<br>";

	$ORDER_PRICE_FROM = str_replace(",", ".", $ORDER_PRICE_FROM);
	$ORDER_PRICE_TO = str_replace(",", ".", $ORDER_PRICE_TO);
	$ORDER_CURRENCY = Trim($ORDER_CURRENCY);
	if ((DoubleVal($ORDER_PRICE_FROM)>0 || DoubleVal($ORDER_PRICE_TO)>0) && strlen($ORDER_CURRENCY)<=0)
		$strError .= GetMessage("ERROR_PRICE_NO_CUR")."<br>";

	if ($ACTIVE!="Y") $ACTIVE = "N";

	$SORT = intval($SORT);
	if ($SORT<=0) $SORT = 100;

	$arLocation = array();
	if($lpEnabled)
	{
		if(strlen($_REQUEST['LOCATION']['L']))
			$LOCATION1 = explode(':', $_REQUEST['LOCATION']['L']);

		if(strlen($_REQUEST['LOCATION']['G']))
			$LOCATION2 = explode(':', $_REQUEST['LOCATION']['G']);
	}

	if (isset($LOCATION1) && is_array($LOCATION1) && count($LOCATION1)>0)
	{
		$locationCount = count($LOCATION1);
		for ($i = 0; $i<$locationCount; $i++)
		{
			if (strlen($LOCATION1[$i]))
			{
				$arLocation[] = array(
					"LOCATION_ID" => $LOCATION1[$i],
					"LOCATION_TYPE" => "L"
				);
			}
		}
	}

	if (isset($LOCATION2) && is_array($LOCATION2) && count($LOCATION2)>0)
	{
		$locationCount = count($LOCATION2);
		for ($i = 0; $i<$locationCount; $i++)
		{
			if (strlen($LOCATION2[$i])>0)
			{
				$arLocation[] = array(
					"LOCATION_ID" => $LOCATION2[$i],
					"LOCATION_TYPE" => "G"
				);
			}
		}
	}

	if (!is_array($arLocation) || count($arLocation)<=0)
		$strError .= GetMessage("ERROR_NO_LOCATION")."<br>";

	if ($strError == '')
	{
		unset($arFields);

		//add logotip
		$arPicture = array();
		if(array_key_exists("LOGOTIP", $_FILES) && $_FILES["LOGOTIP"]["error"] == 0)
			$arPicture = $_FILES["LOGOTIP"];

		//$arPicture["old_file"] = $arPSAction["LOGOTIP"];
		$arPicture["del"] = trim($_POST["LOGOTIP_del"]);

		$arFields = array(
			"NAME" => $NAME,
			"LID" => $LID,
			"PERIOD_FROM" => $PERIOD_FROM,
			"PERIOD_TO" => $PERIOD_TO,
			"PERIOD_TYPE" => $PERIOD_TYPE,
			"WEIGHT_FROM" => $WEIGHT_FROM,
			"WEIGHT_TO" => $WEIGHT_TO,
			"ORDER_PRICE_FROM" => $ORDER_PRICE_FROM,
			"ORDER_PRICE_TO" => $ORDER_PRICE_TO,
			"ORDER_CURRENCY" => $ORDER_CURRENCY,
			"ACTIVE" => $ACTIVE,
			"PRICE" => $PRICE,
			"CURRENCY" => $CURRENCY,
			"SORT" => $SORT,
			"DESCRIPTION" => $DESCRIPTION,
			"LOGOTIP" => $arPicture,
			"STORE" => $store,

			"LOCATIONS" => $arLocation
			);

		//pay system for delivery
		if (is_set($_POST["PAY_SYSTEM"]) && is_array($_POST["PAY_SYSTEM"]))
		{
			$arFields["PAY_SYSTEM"] = array();
			$arPaySystem = $_POST["PAY_SYSTEM"];

			if ($arPaySystem[0] == "")
				unset($arPaySystem[0]);

			$arFields["PAY_SYSTEM"] = $arPaySystem;
		}

		if ($ID>0)
		{
			$delivery = new CSaleDelivery();

			if (!$delivery->Update($ID, $arFields, array("EXPECT_LOCATION_CODES" => $lpEnabled)))
				$strError .= GetMessage("ERROR_EDIT_DELIVERY")."<br>";
		}
		else
		{
			$ID = CSaleDelivery::Add($arFields, array("EXPECT_LOCATION_CODES" => $lpEnabled));
			if ($ID<=0)
				$strError .= GetMessage("ERROR_ADD_DELIVERY")."<br>";
		}
	}

	if ($strError != '')
	{
		$bInitVars = true;
	}
	else
	{
		if (strlen($apply) > 0)
			LocalRedirect("sale_delivery_edit.php?ID=".$ID."&lang=".LANG."&".$tabControl->ActiveTabParam());
		else
			LocalRedirect("sale_delivery.php?lang=".LANG);
	}
}

$oldLogo = 0;
if ($ID>0)
{
	$db_delivery = CSaleDelivery::GetList(Array("SORT"=>"ASC"), Array("ID"=>$ID));
	$db_delivery->ExtractFields("str_");
	$oldLogo = (int)$str_LOGOTIP;
	$arDeliveryDescription = CSaleDelivery::GetByID($ID);
	$str_DESCRIPTION = htmlspecialcharsbx($arDeliveryDescription["DESCRIPTION"]);
}
else
{
	$str_ACTIVE = 'Y';
}

if ($bInitVars)
{
	$DB->InitTableVarsForEdit("b_sale_delivery", "", "str_");
	$str_LOGOTIP = $oldLogo;
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
				"TEXT" => GetMessage("SDEN_2FLIST"),
				"LINK" => "/bitrix/admin/sale_delivery.php?lang=".LANGUAGE_ID.GetFilterParams("filter_"),
				"ICON" => "btn_list"
			)
	);

if ($ID > 0 && $saleModulePermissions >= "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
			"TEXT" => GetMessage("SDEN_NEW_DELIVERY"),
			"LINK" => "/bitrix/admin/sale_delivery_edit.php?lang=".LANGUAGE_ID.GetFilterParams("filter_"),
			"ICON" => "btn_new"
		);

	$aMenu[] = array(
			"TEXT" => GetMessage("SDEN_DELETE_DELIVERY"),
			"LINK" => "javascript:if(confirm('".GetMessage("SDEN_DELETE_DELIVERY_CONFIRM")."')) window.location='/bitrix/admin/sale_delivery.php?ID=".$ID."&action=delete&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."#tb';",
			"ICON" => "btn_delete"
		);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?if(strlen($strError)>0)
	echo CAdminMessage::ShowMessage(Array("DETAILS"=>$strError, "TYPE"=>"ERROR", "MESSAGE"=>GetMessage("SDEN_ERROR"), "HTML"=>true));?>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="form1" enctype="multipart/form-data">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANGUAGE_ID; ?>">
<input type="hidden" name="ID" value="<?echo $ID ?>">
<?=bitrix_sessid_post()?>

<?
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
	if ($ID>0):?>
		<tr>
			<td width="40%">ID:</td>
			<td width="60%"><?echo $ID ?></td>
		</tr>
	<?endif;?>

	<tr class="adm-detail-required-field">
		<td width="40%"><?echo GetMessage("F_NAME") ?>:</td>
		<td width="60%"><input type="text" name="NAME" value="<?echo $str_NAME ?>" size="40"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td width="40%"><?echo GetMessage("F_LANG") ?>:</td>
		<td width="60%"><?echo CLang::SelectBox("LID", $str_LID, "")?></td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("F_PERIOD_FROM") ?>:</td>
		<td width="60%">
			<?echo GetMessage("SALE_FROM")?>
			<input type="text" name="PERIOD_FROM" value="<?echo $str_PERIOD_FROM ?>" size="3">
			<?echo GetMessage("SALE_TO")?>
			<input type="text" name="PERIOD_TO" value="<?echo $str_PERIOD_TO ?>" size="3">
			<?
			$arPerType = array(
				"D" => GetMessage("PER_DAY"),
				"H" => GetMessage("PER_HOUR"),
				"M" => GetMessage("PER_MONTH")
				);
			?>
			<select name="PERIOD_TYPE">
				<?foreach ($arPerType as $key => $value):?>
					<option value="<?echo $key ?>" <?if ($key==$str_PERIOD_TYPE) echo "selected"?>><?echo $value ?></option>
				<?endforeach;?>
			</select>

		</td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("F_WEIGHT")?> (<?echo GetMessage('WEIGHT_G')?>):</td>
		<td width="60%">
			<?echo GetMessage("SALE_FROM")?>
			<input type="text" name="WEIGHT_FROM" value="<?echo $str_WEIGHT_FROM ?>" size="7">
			<?echo GetMessage("SALE_TO")?>
			<input type="text" name="WEIGHT_TO" value="<?echo $str_WEIGHT_TO ?>" size="7">
		</td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("F_ORDER_PRICE")?>:</td>
		<td width="60%">
			<?echo GetMessage("SALE_FROM")?>
			<input type="text" name="ORDER_PRICE_FROM" value="<?echo $str_ORDER_PRICE_FROM ?>" size="10">
			<?echo GetMessage("SALE_TO")?>
			<input type="text" name="ORDER_PRICE_TO" value="<?echo $str_ORDER_PRICE_TO ?>" size="10">
			<?echo CCurrency::SelectBox("ORDER_CURRENCY", $str_ORDER_CURRENCY, "", false, "", "")?>
		</td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("F_ACTIVE")?>:</td>
		<td width="60%">
			<input type="checkbox" name="ACTIVE" value="Y" <?if ($str_ACTIVE=="Y") echo "checked";?>>
		</td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("F_PRICE");?>:</td>
		<td width="60%">
			<input type="text" name="PRICE" value="<?echo $str_PRICE ?>" size="10">
			<?echo CCurrency::SelectBox("CURRENCY", $str_CURRENCY, "", false, "", "")?>
		</td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("F_SORT") ?>:</td>
		<td width="60%">
			<input type="text" name="SORT" value="<?echo $str_SORT ?>" size="40">
		</td>
	</tr>
	<tr>
		<td width="40%" valign="top"><?echo GetMessage("F_DESCRIPTION");?>:</td>
		<td width="60%" valign="top">
			<textarea rows="3" cols="40" name="DESCRIPTION"><?echo $str_DESCRIPTION;?></textarea>
		</td>
	</tr>

<?
$arStoreList = array();
if (CModule::IncludeModule('catalog'))
{
	$dbList = CCatalogStore::GetList(
		array("SORT" => "DESC", "ID" => "DESC"),
		array("ACTIVE" => "Y"),
		false,
		false,
		array("ID", "SITE_ID", "TITLE", "ADDRESS", "DESCRIPTION", "IMAGE_ID", "PHONE", "SCHEDULE", "LOCATION_ID", "GPS_N", "GPS_S")
	);
	while ($arList = $dbList->Fetch())
		$arStoreList[] = $arList;
}

if (!empty($arStoreList))
{
	$dbList = CSaleDelivery::GetList(array(), array("ID" => $str_ID), false, false,	array("STORE"));
	$arList = $dbList->Fetch();
	$str_STORE = $arList["STORE"];

	$arStore = array();
	if (strlen($str_STORE) > 0)
		$arStore = unserialize($str_STORE);
?>
	<tr>
		<td width="40%" valign="top"><?echo GetMessage("SDEN_STORE");?>:</td>
		<td width="60%" valign="top">
			<select name="STORE[]" size="5" multiple>
			<?
			foreach ($arStoreList as $items):

				$siteInfo = (strlen($items["SITE_ID"]) > 0) ? " [".$items["SITE_ID"]."]" : "";
			?>
				<option value="<?=$items["ID"]?>" <?=(in_array($items["ID"], $arStore)?"selected":"")?> ><?=htmlspecialcharsbx($items["TITLE"].$siteInfo)?></option>
			<?
			endforeach
			?>
			</select>
		</td>
	</tr>
<?
}
?>
	<tr>
		<td width="40%"><?echo GetMessage("SDEN_LOGOTIP");?>:</td>
		<td width="60%">
			<div><input type="file" name="LOGOTIP"></div>
			<?if ($str_LOGOTIP > 0):?>
				<br>
				<?
				$arLogotip = CFile::GetFileArray($str_LOGOTIP);
				echo CFile::ShowImage($arLogotip, 150, 150, "border=0", "", false);
				?>
				<br />
				<div>
					<input type="checkbox" name="LOGOTIP_del" value="Y" id="LOGOTIP_del" >
					<label for="LOGOTIP_del"><?=GetMessage("SPS_LOGOTIP_DEL");?></label>
				</div>
			<?endif;?>
		</td>
	</tr>
	<?if($lpEnabled):?>

		<tr class="heading">
			<td colspan="2">
				<?=GetMessage('F_LOCATION1')?>
			</td>
		</tr>

		<tr class="adm-detail-required-field">
			<td colspan="2">

				<?$APPLICATION->IncludeComponent("bitrix:sale.location.selector.system", "", array(
						"ENTITY_PRIMARY" => $ID,
						"LINK_ENTITY_NAME" => CSaleDelivery::CONN_ENTITY_NAME,
						"INPUT_NAME" => 'LOCATION',
						"SELECTED_IN_REQUEST" => array(
							'L' => isset($_REQUEST['LOCATION']['L']) ? explode(':', $_REQUEST['LOCATION']['L']) : false,
							'G' => isset($_REQUEST['LOCATION']['G']) ? explode(':', $_REQUEST['LOCATION']['G']) : false
						)
					),
					false
				);?>

			</td>
		</tr>

	<?else:?>
		<tr class="adm-detail-required-field">
			<td width="40%" valign="top"><?echo GetMessage("F_LOCATION1");?>:</td>
			<td width="60%" valign="top">

				<?$db_vars = CSaleLocation::GetList(Array("COUNTRY_NAME_LANG"=>"ASC", "REGION_NAME_LANG"=>"ASC", "CITY_NAME_LANG"=>"ASC"), array(), LANG);?>

				<select name="LOCATION1[]" size="5" multiple>
					<?
					$arLOCATION1 = array();
					if ($bInitVars)
					{
						$arLOCATION1 = $LOCATION1;
					}
					else
					{
						$db_location = CSaleDelivery::GetLocationList(Array("DELIVERY_ID" => $ID, "LOCATION_TYPE" => "L"));
						while ($arLocation = $db_location->Fetch())
						{
							$arLOCATION1[] = $arLocation["LOCATION_ID"];
						}
					}
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
						<option value="<?echo $vars["ID"]?>"<?if (is_array($arLOCATION1) && in_array(IntVal($vars["ID"]), $arLOCATION1)) echo " selected"?>><?echo htmlspecialcharsbx($locationName)?></option>
					<?endwhile;?>
				</select>
			</td>
		</tr>
		<tr class="adm-detail-required-field">
			<td width="40%" valign="top"><?echo GetMessage("F_LOCATION2");?>:</td>
			<td width="60%" valign="top">

				<?$db_vars = CSaleLocationGroup::GetList(Array("NAME"=>"ASC"), array(), LANG);?>

				<select name="LOCATION2[]" size="5" multiple>
					<?
					$arLOCATION2 = array();
					if ($bInitVars)
					{
						$arLOCATION2 = $LOCATION2;
					}
					else
					{
						$db_location = CSaleDelivery::GetLocationList(Array("DELIVERY_ID" => $ID, "LOCATION_TYPE" => "G"));
						while ($arLocation = $db_location->Fetch())
						{
							$arLOCATION2[] = $arLocation["LOCATION_ID"];
						}
					}
					?>
					<?while ($vars = $db_vars->Fetch()):?>
						<option value="<?echo $vars["ID"]?>"<?if (is_array($arLOCATION2) && in_array(IntVal($vars["ID"]), $arLOCATION2)) echo " selected"?>><?echo htmlspecialcharsbx($vars["NAME"])?></option>
					<?endwhile;?>
				</select>
			</td>
		</tr>

	<?endif?>

<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td width="40%">
			<?=GetMessage("SDEN_PAY_NAME");?>:
		</td>
		<td width="60%">
			<select multiple="multiple" size="5" name="PAY_SYSTEM[]">
			<?
			$arPaySystemId = array();
			$dbRes = CSaleDelivery::GetDelivery2PaySystem(array("DELIVERY_ID" => $ID));
			while ($arRes = $dbRes->Fetch())
				$arPaySystemId[] = $arRes["PAYSYSTEM_ID"];

			$dbResultList = CSalePaySystem::GetList(
				array("SORT"=>"ASC", "NAME"=>"ASC"),
				array("ACTIVE" => "Y"),
				false,
				false,
				array("ID", "NAME", "ACTIVE", "SORT", "LID")
			);
			while ($arPayType = $dbResultList->Fetch()):
				$name = (strlen($arPayType["LID"]) > 0) ? htmlspecialcharsbx($arPayType["NAME"]). " (".$arPayType["LID"].")" : htmlspecialcharsbx($arPayType["NAME"]);
			?>
				<option value="<?=intval($arPayType["ID"]);?>" <?=(in_array($arPayType["ID"], $arPaySystemId) || empty($arPaySystemId) ? "selected":"")?>><?=$name?></option>
			<?endwhile;?>
			</select>
		</td>
	</tr>

<?
$tabControl->EndTab();

$tabControl->Buttons(
	array(
		"disabled" => ($saleModulePermissions < "W"),
		"back_url" => "/bitrix/admin/sale_delivery.php?lang=".LANGUAGE_ID.GetFilterParams("filter_")
	)
);

$tabControl->End();
?>
</form>
<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>