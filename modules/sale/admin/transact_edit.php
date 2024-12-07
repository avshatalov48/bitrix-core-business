<?php

/** @global CMain $APPLICATION */
use Bitrix\Main\Context;
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$selfFolderUrl = $adminPage->getSelfFolderUrl();
$listUrl = $selfFolderUrl."sale_transact_admin.php?lang=".LANGUAGE_ID;
$listUrl = $adminSidePanelHelper->editUrlToPublicPage($listUrl);

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

Loader::includeModule('sale');

IncludeModuleLangFile(__FILE__);

$request = Context::getCurrent()->getRequest();

$errorMessage = "";
$bVarsFromForm = false;

ClearVars();

$ID = (int)($ID ?? 0);

if ($request->isPost() && $request->getPost('Update') !== null && $saleModulePermissions >= "U" && check_bitrix_sessid())
{
	$adminSidePanelHelper->decodeUriComponent();

	$USER_ID = intval($USER_ID);
	if ($USER_ID <= 0)
		$errorMessage .= GetMessage("STE_EMPTY_USER").".<br>";

	$AMOUNT = str_replace(",", ".", $AMOUNT);
	$AMOUNT = DoubleVal($AMOUNT);
	if ($AMOUNT <= 0)
		$errorMessage .= GetMessage("STE_EMPTY_SUM").".<br>";

	$CURRENCY = Trim($CURRENCY);
	if ($CURRENCY == '')
		$errorMessage .= GetMessage("STE_EMPTY_CURRENCY").".<br>";

	$DEBIT = (($DEBIT == "Y") ? "Y" : "N");

	if ($errorMessage == '')
	{
		if (!CSaleUserAccount::UpdateAccount($USER_ID, (($DEBIT == "Y") ? $AMOUNT : -$AMOUNT), $CURRENCY, "MANUAL", intval($ORDER_ID), $NOTES))
		{
			if ($ex = $APPLICATION->GetException())
				$errorMessage .= $ex->GetString().".<br>";
			else
				$errorMessage .= GetMessage("STE_ERROR_SAVE_ACCOUNT").".<br>";
		}
	}

	if ($errorMessage == '')
	{
		$adminSidePanelHelper->sendSuccessResponse("base");
		$adminSidePanelHelper->localRedirect($listUrl);
		LocalRedirect($listUrl);
	}
	else
	{
		$adminSidePanelHelper->sendJsonErrorResponse($errorMessage);
		$bVarsFromForm = true;
	}
}

if ($bVarsFromForm)
{
	$DB->InitTableVarsForEdit("b_sale_user_transact", "", "str_");
}
else
{
	$columns = $DB->GetTableFieldsList("b_sale_user_transact");
	foreach ($columns as $column)
	{
		${"str_{$column}"} ??= null;
	}

	$str_USER_LOGIN ??= null;
	$str_USER_NAME ??= null;
	$str_USER_LAST_NAME ??= null;
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(GetMessage("STE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT" => GetMessage("STEN_2FLIST"),
		"TITLE" => GetMessage("STEN_2FLIST_TITLE"),
		"LINK" => $listUrl,
		"ICON" => "btn_list"
	)
);
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?if($errorMessage <> '')
	CAdminMessage::ShowMessage(Array("DETAILS"=>$errorMessage, "TYPE"=>"ERROR", "MESSAGE"=>GetMessage("STE_ERROR"), "HTML"=>true));?>

<?
$actionUrl = $APPLICATION->GetCurPage();
$actionUrl = $adminSidePanelHelper->setDefaultQueryParams($actionUrl);
?>
<form method="POST" action="<?=$actionUrl?>" name="form1">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<?=bitrix_sessid_post()?>

<?
$aTabs = array(array("DIV" => "edit1", "TAB" => GetMessage("STEN_TAB_TRANSACT"), "ICON" => "sale",
	"TITLE" => GetMessage("STEN_TAB_TRANSACT_DESCR")));

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?echo GetMessage("STE_USER")?></td>
		<td width="60%"><?
			$user_name = "";
			if ($ID > 0)
			{
				$urlToUser = $selfFolderUrl."user_edit.php?ID=".$str_USER_ID."&lang=".LANGUAGE_ID;
				if (!empty($publicMode))
				{
					$urlToUser = $selfFolderUrl."sale_buyers_profile.php?USER_ID=".$str_USER_ID."&lang=".LANGUAGE_ID;
					$urlToUser = $adminSidePanelHelper->editUrlToPublicPage($urlToUser);
				}
				$user_name = "[<a title=\"".GetMessage("STE_USER_PROFILE")."\" href=\"".$urlToUser."\">".$str_USER_ID.
					"</a>] (".$str_USER_LOGIN.") ".$str_USER_NAME." ".$str_USER_LAST_NAME;
			}

			echo FindUserID("USER_ID", $str_USER_ID, $user_name);
			?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("STE_SUM")?></td>
		<td>
			<input type="text" name="AMOUNT" size="10" maxlength="20" value="<?= roundEx($str_AMOUNT, SALE_VALUE_PRECISION) ?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("STE_CURRENCY")?></td>
		<td>
			<?echo CCurrency::SelectBox("CURRENCY", $str_CURRENCY, "", false, "", "")?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("STE_TYPE")?></td>
		<td>
			<select name="DEBIT">
				<option value="Y"<?if ($str_DEBIT == "Y") echo " selected";?>><?echo GetMessage("STE_DEBET")?></option>
				<option value="N"<?if ($str_DEBIT == "N") echo " selected";?>><?echo GetMessage("STE_KREDIT")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("STE_ORDER_ID")?></td>
		<td valign="top">
			<input type="text" name="ORDER_ID" size="5" maxlength="20" value="<?= $str_ORDER_ID ?>">
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("STE_NOTES")?></td>
		<td valign="top">
			<textarea name="NOTES" rows="3" cols="40"><?= $str_NOTES ?></textarea>
		</td>
	</tr>

<?
$tabControl->EndTab();
$tabControl->Buttons();
$tabControl->Buttons(array("disabled" => ($saleModulePermissions < "U"), "back_url" => $listUrl));
$tabControl->End();
?>
</form>
<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");

