<?php

/** @global CMain $APPLICATION */
use Bitrix\Main\Context;
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$selfFolderUrl = $adminPage->getSelfFolderUrl();
$listUrl = $selfFolderUrl."sale_tax.php?lang=".LANGUAGE_ID;
$listUrl = $adminSidePanelHelper->editUrlToPublicPage($listUrl);

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

Loader::includeModule('sale');

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$request = Context::getCurrent()->getRequest();

$ID = (int)$request->get('ID');

ClearVars();

$strError = "";
$bInitVars = false;
$save ??= null;
$apply ??= null;
$NAME ??= null;
$LID ??= null;
$CODE ??= null;
$DESCRIPTION ??= null;
$str_TIMESTAMP_X ??= null;
$str_LID ??= null;
$str_NAME ??= null;
$str_CODE ??= null;
$str_DESCRIPTION ??= null;

if (($save <> '' || $apply <> '') && $request->isPost() && $saleModulePermissions=="W" && check_bitrix_sessid())
{
	$adminSidePanelHelper->decodeUriComponent();
	if ($NAME == '') $strError .= GetMessage("ERROR_EMPTY_NAME")."<br>";
	if ($LID == '') $strError .= GetMessage("ERROR_EMPTY_LANG")."<br>";

	if ($strError == '')
	{
		$arFields = [
			'LID' => $LID,
			'NAME' => $NAME ? trim($NAME) : '',
			'CODE' => ($CODE == '') ? false : $CODE,
			'DESCRIPTION' => $DESCRIPTION
		];

		if (intval($ID)>0)
		{
			if (!CSaleTax::Update($ID, $arFields))
				$strError .= GetMessage("ERROR_EDIT_TAX")."<br>";
		}
		else
		{
			$ID = CSaleTax::Add($arFields);
			if (intval($ID)<=0)
				$strError .= GetMessage("ERROR_ADD_TAX")."<br>";
		}
	}

	if ($strError <> '')
	{
		$adminSidePanelHelper->sendJsonErrorResponse($strError);
		$bInitVars = True;
	}

	$adminSidePanelHelper->sendSuccessResponse("base");

	if ($save <> '' && $strError == '')
	{
		$adminSidePanelHelper->localRedirect($listUrl);
		LocalRedirect($listUrl);
	}
}

if ($ID <> '')
{
	$db_tax = CSaleTax::GetList(Array(), Array("ID" => $ID));
	$db_tax->ExtractFields("str_");
}

if ($bInitVars)
{
	$DB->InitTableVarsForEdit("b_sale_tax", "", "str_");
}

if($ID > 0)
	$sDocTitle = GetMessage("SALE_EDIT_RECORD", array("#ID#"=>$ID));
else
	$sDocTitle = GetMessage("SALE_NEW_RECORD");
$APPLICATION->SetTitle($sDocTitle);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
?>

<?
$aMenu = array(
	array(
		"TEXT" => GetMessage("STEN_2FLIST"),
		"ICON" => "btn_list",
		"LINK" => $listUrl
	)
);

if ($ID > 0 && $saleModulePermissions >= "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");
	$addUrl = $selfFolderUrl."sale_tax_edit.php?lang=".LANGUAGE_ID;
	$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
	$aMenu[] = array(
		"TEXT" => GetMessage("STEN_NEW_TAX"),
		"ICON" => "btn_new",
		"LINK" => $addUrl
	);
	$deleteUrl = $selfFolderUrl."sale_tax.php?action=delete&ID[]=".$ID."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."#tb";
	$buttonAction = "LINK";
	if ($adminSidePanelHelper->isPublicFrame())
	{
		$deleteUrl = $adminSidePanelHelper->editUrlToPublicPage($deleteUrl);
		$buttonAction = "ONCLICK";
	}
	$aMenu[] = array(
		"TEXT" => GetMessage("STEN_DELETE_TAX"),
		"ICON" => "btn_delete",
		$buttonAction => "javascript:if(confirm('".GetMessage("STEN_DELETE_TAX_CONFIRM")."')) top.window.location.href='".$deleteUrl."';",
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?CAdminMessage::ShowMessage($strError);?>

<?
$actionUrl = $APPLICATION->GetCurPage();
$actionUrl = $adminSidePanelHelper->setDefaultQueryParams($actionUrl);
?>
<form method="POST" action="<?=$actionUrl?>" name="fform">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<input type="hidden" name="ID" value="<?echo $ID ?>">
<?=bitrix_sessid_post()?>

<?
$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("STEN_TAB_TAX"), "ICON" => "sale", "TITLE" => GetMessage("STEN_TAB_TAX_DESCR"))
	);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>

	<?if ($ID>0):?>
		<tr>
			<td width="40%"><?echo GetMessage("TAX_ID")?>:</td>
			<td width="60%"><b><?echo $ID ?></b></td>
		</tr>
		<tr>
			<td><?echo GetMessage("TAX_TIMESTAMP")?>:</td>
			<td><b><?echo $str_TIMESTAMP_X ?></b></td>
		</tr>
	<?endif;?>

	<tr>
		<td width="40%"><?echo GetMessage("TAX_LID")?>:</td>
		<td width="60%"><?echo CLang::SelectBox("LID", $str_LID, "")?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("TAX_NAME")?>:</td>
		<td>
			<input type="text" name="NAME" value="<?echo $str_NAME ?>" size="50" maxlength="250">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("TAX_FCODE")?>:</td>
		<td>
			<input type="text" name="CODE" value="<?echo $str_CODE?>" size="25" maxlength="50">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("TAX_DESCRIPTION")?>:</td>
		<td>
			<input type="text" name="DESCRIPTION" value="<?echo $str_DESCRIPTION ?>" size="50" maxlength="250">
		</td>
	</tr>

<?
$tabControl->EndTab();
$tabControl->Buttons(array("disabled" => ($saleModulePermissions < "W"), "back_url" => $listUrl));
$tabControl->End();
?>

</form>
<?php
require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/epilog_admin.php");
