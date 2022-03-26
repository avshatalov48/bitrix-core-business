<?php
use Bitrix\Main\Loader;
use Bitrix\Catalog;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");
global $APPLICATION;
global $DB;
global $USER;
global $USER_FIELD_MANAGER;

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

$selfFolderUrl = $adminPage->getSelfFolderUrl();
$listUrl = $selfFolderUrl."cat_store_list.php?lang=".LANGUAGE_ID;
$listUrl = $adminSidePanelHelper->editUrlToPublicPage($listUrl);

if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_store')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
Loader::includeModule("catalog");
$bReadOnly = !$USER->CanDoOperation('catalog_store');

if($ex = $APPLICATION->GetException())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$strError = $ex->GetString();
	ShowError($strError);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$id = (int)($_REQUEST['ID'] ?? 0);
if ($id < 0)
{
	$id = 0;
}

if(!Catalog\Config\Feature::isMultiStoresEnabled())
{
	if (Catalog\Config\State::isExceededStoreLimit())
	{
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
		ShowError(GetMessage("CAT_FEATURE_NOT_ALLOW"));
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
		die();
	}
}

IncludeModuleLangFile(__FILE__);
ClearVars();

$errorMessage = '';
$bVarsFromForm = false;

$userId = (int)$USER->GetID();

$entityId = Catalog\StoreTable::getUfId();

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_REQUEST["Update"] <> '' && !$bReadOnly && check_bitrix_sessid())
{
	$adminSidePanelHelper->decodeUriComponent();

	$arPREVIEW_PICTURE = $_FILES["IMAGE_ID"];
	$arPREVIEW_PICTURE["del"] = $IMAGE_ID_del;
	$arPREVIEW_PICTURE["MODULE_ID"] = "catalog";
	$ISSUING_CENTER = ($_POST["ISSUING_CENTER"] == 'Y') ? 'Y' : 'N';
	$SHIPPING_CENTER = ($_POST["SHIPPING_CENTER"] == 'Y') ? 'Y' : 'N';
	$fileId = 0;
	$isImage = CFile::CheckImageFile($arPREVIEW_PICTURE);

	if (trim($ADDRESS) == '')
		$errorMessage .= GetMessage("ADDRESS_EMPTY")."<br>";

	if ($isImage == '' && ($arPREVIEW_PICTURE["name"] <> '' || $arPREVIEW_PICTURE["del"] <> ''))
	{
		$fileId = CFile::SaveFile($arPREVIEW_PICTURE, "catalog");
	}
	elseif ($isImage <> '')
	{
		$errorMessage .= $isImage."<br>";
	}

	$arFields = array(
		"TITLE" => ($_POST['TITLE'] ?? ''),
		"SORT" => (int)($_POST['CSTORE_SORT'] ?? 0),
		"ACTIVE" => (isset($_POST['ACTIVE']) && $_POST['ACTIVE'] == 'Y' ? 'Y' : 'N'),
		"ADDRESS" => ($_POST['ADDRESS'] ?? ''),
		"DESCRIPTION" => ($_POST['DESCRIPTION'] ?? ''),
		"GPS_N" => (isset($_POST['GPS_N']) ? str_replace(',', '.', $_POST['GPS_N']) : ''),
		"GPS_S" => (isset($_POST['GPS_S']) ? str_replace(',', '.', $_POST['GPS_S']) : ''),
		"PHONE" => ($_POST['PHONE'] ?? ''),
		"SCHEDULE" => ($_POST['SCHEDULE'] ?? ''),
		"XML_ID" => ($_POST['XML_ID'] ?? ''),
		"MODIFIED_BY" => $userId,
		"EMAIL" => ($_POST["EMAIL"] ?? ''),
		"ISSUING_CENTER" => $ISSUING_CENTER,
		"SHIPPING_CENTER" => $SHIPPING_CENTER,
		"SITE_ID" => $_POST["SITE_ID"],
		"CODE" => $_POST['CODE'] ?? false
	);

	$USER_FIELD_MANAGER->EditFormAddFields($entityId, $arFields);

	if (intval($fileId) > 0)
		$arFields["IMAGE_ID"] = intval($fileId);
	elseif ($fileId === "NULL")
		$arFields["IMAGE_ID"] = "null";

	$DB->StartTransaction();

	if ($errorMessage == '')
	{
		if ($id > 0)
		{
			$res = CCatalogStore::Update($id, $arFields);
		}
		else
		{
			$arFields['USER_ID'] = $userId;
			$res = CCatalogStore::Add($arFields);
			if ($res)
				$id = (int)$res;
		}
		if (!$res)
		{
			if ($ex = $APPLICATION->GetException())
				$errorMessage .= $ex->GetString()."<br>";
			else
				$errorMessage .= GetMessage('STORE_SAVE_ERROR').'<br>';
		}
		else
		{
			$ufUpdated = $USER_FIELD_MANAGER->Update($entityId, $id, $arFields);
		}
	}
	if ($errorMessage == '')
	{
		$DB->Commit();

		if ($_REQUEST["apply"] == '')
		{
			$adminSidePanelHelper->sendSuccessResponse("base", array("ID" => $id));
			$adminSidePanelHelper->localRedirect($listUrl);
			LocalRedirect($listUrl);
		}
		else
		{
			$applyUrl = $selfFolderUrl."cat_store_edit.php?lang=".LANGUAGE_ID."&ID=".$id;
			$applyUrl = $adminSidePanelHelper->setDefaultQueryParams($applyUrl);
			$adminSidePanelHelper->sendSuccessResponse("apply", array("reloadUrl" => $applyUrl));
			LocalRedirect($applyUrl);
		}
	}
	else
	{
		$bVarsFromForm = true;
		$DB->Rollback();
		$adminSidePanelHelper->sendJsonErrorResponse($errorMessage);
	}
}

if ($id > 0)
	$APPLICATION->SetTitle(str_replace("#ID#", $id, GetMessage("STORE_TITLE_UPDATE")));
else
	$APPLICATION->SetTitle(GetMessage("STORE_TITLE_ADD"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
$str_ACTIVE = "Y";

/** @var array $currentValues */
$currentValues = null;
if($id > 0)
{
	$arSelect = array(
		"ID",
		"ACTIVE",
		"TITLE",
		"ADDRESS",
		"DESCRIPTION",
		"GPS_N",
		"GPS_S",
		"IMAGE_ID",
		"LOCATION_ID",
		"PHONE",
		"SCHEDULE",
		"XML_ID",
		"SORT",
		"EMAIL",
		"ISSUING_CENTER",
		"SHIPPING_CENTER",
		"SITE_ID",
		"CODE",
		"IS_DEFAULT",
	);

	$dbResult = CCatalogStore::GetList(array(), array('ID' => $id), false, false, $arSelect);
	$currentValues = $dbResult->ExtractFields();
	if (empty($currentValues))
	{
		$currentValues = null;
		$id = 0;
	}
}

if ($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_catalog_store", "", "str_");

if(isset($str_ADDRESS))
	$str_ADDRESS = (trim($str_ADDRESS) != '') ? $str_ADDRESS : '';

$aMenu = array(
	array(
		"TEXT" => GetMessage("STORE_LIST"),
		"ICON" => "btn_list",
		"LINK" => $listUrl
	)
);

if ($id > 0 && !$bReadOnly)
{
	$aMenu[] = ["SEPARATOR" => "Y"];

	if (Catalog\Config\State::isAllowedNewStore())
	{
		$addUrl = $selfFolderUrl."cat_store_edit.php?lang=".LANGUAGE_ID;
		$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
		$aMenu[] = [
			"TEXT" => GetMessage("STORE_NEW"),
			"ICON" => "btn_new",
			"LINK" => $addUrl
		];
		unset($addUrl);
	}
	else
	{
		$helpLink = Catalog\Config\Feature::getMultiStoresHelpLink();
		if (!empty($helpLink))
		{
			$aMenu[] = [
				"TEXT" => GetMessage("STORE_NEW"),
				"ICON" => "btn_lock",
				$helpLink['TYPE'] => $helpLink['LINK'],
			];
		}
		unset($helpLink);
	}
	if ($currentValues['IS_DEFAULT'] !== 'Y')
	{
		$deleteUrl = $selfFolderUrl
			. "cat_store_list.php?action=delete&ID[]=" . $id
			. "&lang=" . LANGUAGE_ID
			. "&" . bitrix_sessid_get() . "#tb"
		;
		if ($adminSidePanelHelper->isPublicFrame())
		{
			$deleteUrl = $adminSidePanelHelper->editUrlToPublicPage($deleteUrl);
		}
		$aMenu[] = [
			"TEXT" => GetMessage("STORE_DELETE"),
			"ICON" => "btn_delete",
			"LINK" => "javascript:if(confirm('"
				. GetMessage("STORE_DELETE_CONFIRM")
				. "')) top.window.location='"
				. $deleteUrl
				. "';",
			"WARNING" => "Y"
		];
	}
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
$arSitesShop = array();
$arSitesTmp = array();
$rsSites = CSite::GetList("id", "asc", Array("ACTIVE" => "Y"));
while($arSite = $rsSites->GetNext())
{
	$site = COption::GetOptionString("sale", "SHOP_SITE_".$arSite["ID"], "");
	if ($arSite["ID"] == $site)
	{
		$arSitesShop[] = array("ID" => $arSite["ID"], "NAME" => $arSite["NAME"]);
	}
	$arSitesTmp[] = array("ID" => $arSite["ID"], "NAME" => $arSite["NAME"]);
}

$rsCount = count($arSitesShop);
if ($rsCount <= 0)
{
	$arSitesShop = $arSitesTmp;
	$rsCount = count($arSitesShop);
}

CAdminMessage::ShowMessage($errorMessage);

$actionUrl = $APPLICATION->GetCurPage();
$actionUrl = $adminSidePanelHelper->setDefaultQueryParams($actionUrl);

$userFieldUrl = $selfFolderUrl."userfield_edit.php?lang=".LANGUAGE_ID."&ENTITY_ID=".$entityId;
$userFieldUrl = $adminSidePanelHelper->editUrlToPublicPage($userFieldUrl);
$userFieldUrl .= "&back_url=".urlencode($APPLICATION->GetCurPageParam('', array('bxpublic'))."&tabControl_active_tab=user_fields_tab");
?>
<form enctype="multipart/form-data" method="POST" action="<?=$actionUrl?>" name="store_edit">
	<?php
	echo GetFilterHiddens("filter_");?>
	<input type="hidden" name="Update" value="Y">
	<input type="hidden" name="lang" value="<?php
	echo LANGUAGE_ID; ?>">
	<input type="hidden" name="ID" value="<?php
	echo $id ?>">
	<?=bitrix_sessid_post()?><?php
	$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("STORE_TAB"), "ICON" => "catalog", "TITLE" => GetMessage("STORE_TAB_DESCR")),
	);

	$tabControl = new CAdminTabControl("tabControl", $aTabs);
	$tabControl->Begin();

	$tabControl->BeginNextTab();
	?>
	<tr>
		<td align="left" colspan="2">
			<a href="<?=$userFieldUrl?>"><?=GetMessage("STORE_E_USER_FIELDS_ADD_HREF");?></a>
		</td>
	</tr>
	<?php
	if ($id > 0):?>
	<tr>
		<td>ID:</td>
		<td><?= $id ?></td>
	</tr>
	<?php
	endif;
	if ($id > 0): ?>
	<tr>
		<td style="width: 40%;"><?= GetMessage("STORE_FIELD_IS_DEFAULT") ?>:</td>
		<td>
			<?php echo ($currentValues['IS_DEFAULT'] === 'Y' ? GetMessage('STORE_MESS_YES') : GetMessage('STORE_MESS_NO')); ?>
		</td>
	</tr>
	<?php
	endif;
	?>
	<tr>
		<td width="40%"><?= GetMessage("STORE_ACTIVE") ?>:</td>
		<td width="60%">
			<input type="checkbox" name="ACTIVE" value="Y" <?php
			if(($str_ACTIVE == 'Y') || ($id == 0)) echo "checked";?> size="50" />
		</td>
	</tr>
	<tr>
		<td width="40%"><?= GetMessage("ISSUING_CENTER") ?>:</td>
		<td width="60%">
			<input type="checkbox" name="ISSUING_CENTER" value="Y" <?php
			if(($str_ISSUING_CENTER == 'Y') || $id == 0) echo "checked";?> size="50" />
		</td>
	</tr>
	<tr>
		<td width="40%"><?= GetMessage("SHIPPING_CENTER") ?>:</td>
		<td width="60%">
			<input type="checkbox" name="SHIPPING_CENTER" value="Y" <?php
			if(($str_SHIPPING_CENTER == 'Y') || $id == 0) echo "checked";?> size="50" />
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("STORE_SITE_ID") ?>:</td>
		<td>
			<select id="SITE_ID" style="max-width: 300px; width: 300px;" name="SITE_ID" <?=($bReadOnly) ? " disabled" : ""?>>
			<option value=""><?=GetMessage("STORE_SELECT_SITE_ID")?></option>
				<?php
				foreach($arSitesShop as $key => $val)
			{
				$selected = ($val['ID'] == $str_SITE_ID) ? 'selected' : '';
				echo "<option ".$selected." value=".htmlspecialcharsbx($val['ID']).">".htmlspecialcharsbx($val["NAME"]." (".$val["ID"].")")."</option>";
			}
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("STORE_TITLE") ?>:</td>
		<td>
			<input type="text" style="width:300px" name="TITLE" value="<?=$str_TITLE?>" />
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("STORE_CODE") ?>:</td>
		<td>
			<input type="text" style="width:300px" name="CODE" value="<?=$str_CODE?>">
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td  class="adm-detail-valign-top"><?= GetMessage("STORE_ADDRESS") ?>:</td>
		<td>
			<textarea cols="35" rows="3" class="typearea" name="ADDRESS"><?= $str_ADDRESS ?></textarea>
		</td>
	</tr>
	<tr>
		<td  class="adm-detail-valign-top"><?= GetMessage("STORE_DESCR") ?>:</td>
		<td>
			<textarea cols="35" rows="3" class="typearea" name="DESCRIPTION"><?= $str_DESCRIPTION ?></textarea>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("STORE_PHONE") ?>:</td>
		<td>
			<input type="text" name="PHONE" value="<?=$str_PHONE?>" size="45" />
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("STORE_SCHEDULE") ?>:</td>
		<td>
			<input type="text" style="width:300px" name="SCHEDULE" value="<?=$str_SCHEDULE?>"/>
		</td>
	</tr>
	<tr>
		<td><?= "Email" ?>:</td>
		<td>
			<input type="text" style="width:300px" name="EMAIL" value="<?=$str_EMAIL?>"/>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("STORE_GPS_N") ?>:</td>
		<td><input type="text" name="GPS_N" value="<?=$str_GPS_N?>" size="15" />
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("STORE_GPS_S") ?>:</td>
		<td><input type="text" name="GPS_S" value="<?=$str_GPS_S?>" size="15" />
		</td>

	</tr>
	<tr>
		<td><?= GetMessage("STORE_XML_ID") ?>:</td>
		<td><input type="text" name="XML_ID" value="<?=$str_XML_ID?>" size="45" />
		</td>
	</tr>
	<tr>
		<td width="40%"><?= GetMessage("CSTORE_SORT") ?>:</td>
		<td width="60%">
			<input type="text" name="CSTORE_SORT" value="<?=$str_SORT?>" size="5" />
		</td>
	</tr>
	<tr>
		<td><?php
			echo GetMessage("STORE_IMAGE")?>:</td>
		<td>
			<?php
			echo CFile::InputFile("IMAGE_ID", 20, $str_IMAGE_ID, false, 0, "IMAGE", "", 0);?><br>
			<?php
			if($str_IMAGE_ID)
			{
				echo CFile::ShowImage($str_IMAGE_ID, 200, 200, "border=0", "", true);
			}
			?>
		</td>
	</tr>
	<?php
		$arUserFields = $USER_FIELD_MANAGER->GetUserFields($entityId, $id, LANGUAGE_ID);
		foreach($arUserFields as $FIELD_NAME => $arUserField)
		{
			$arUserField["VALUE_ID"] = $id;
			$strLabel = $arUserField["EDIT_FORM_LABEL"]?: $arUserField["FIELD_NAME"];
			$arUserField["EDIT_FORM_LABEL"] = $strLabel;

			echo $USER_FIELD_MANAGER->GetEditFormHTML($bVarsFromForm, $GLOBALS[$FIELD_NAME], $arUserField);

			$form_value = $GLOBALS[$FIELD_NAME];
			if(!$bVarsFromForm)
				$form_value = $arUserField["VALUE"];
			elseif($arUserField["USER_TYPE"]["BASE_TYPE"]=="file")
				$form_value = $GLOBALS[$arUserField["FIELD_NAME"]."_old_id"];
		}

	$tabControl->EndTab();
	$tabControl->Buttons(array("disabled" => $bReadOnly, "back_url" => $listUrl));
	$tabControl->End();
	?>
</form>
<?php
Catalog\Config\Feature::initUiHelpScope();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
