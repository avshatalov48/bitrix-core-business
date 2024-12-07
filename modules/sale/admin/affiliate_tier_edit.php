<?php

/** @global CMain $APPLICATION */
use Bitrix\Main;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

Loader::includeModule('sale');

if(!CBXFeatures::IsFeatureEnabled('SaleAffiliate'))
{
	require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_admin_after.php");

	ShowError(GetMessage("SALE_FEATURE_NOT_ALLOW"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$request = Context::getCurrent()->getRequest();

ClearVars();

$errorMessage = "";
$bVarsFromForm = false;

$ID = (int)$request->get('ID');

if ($request->isPost() && $request->getPost('Update') !== null && $saleModulePermissions>="W" && check_bitrix_sessid())
{
	if ($SITE_ID == '')
		$errorMessage .= GetMessage("SATE1_NO_SITE").".<br>";

	$RATE1 = str_replace(",", ".", $RATE1);
	$RATE1 = DoubleVal($RATE1);

	$RATE2 = str_replace(",", ".", $RATE2);
	$RATE2 = DoubleVal($RATE2);

	$RATE3 = str_replace(",", ".", $RATE3);
	$RATE3 = DoubleVal($RATE3);

	$RATE4 = str_replace(",", ".", $RATE4);
	$RATE4 = DoubleVal($RATE4);

	$RATE5 = str_replace(",", ".", $RATE5);
	$RATE5 = DoubleVal($RATE5);

	if ($errorMessage == '')
	{
		$dbAffiliateTier = CSaleAffiliateTier::GetList(array(), array("SITE_ID" => $SITE_ID, "!ID" => $ID));
		if ($dbAffiliateTier->Fetch())
			$errorMessage .= str_replace("#SITE_ID#", $SITE_ID, GetMessage("SATE1_EXISTS")).".<br>";
	}

	if ($errorMessage == '')
	{
		$arFields = array(
			"SITE_ID" => $SITE_ID,
			"RATE1" => $RATE1,
			"RATE2" => $RATE2,
			"RATE3" => $RATE3,
			"RATE4" => $RATE4,
			"RATE5" => $RATE5,
		);

		if ($ID > 0)
		{
			if (!CSaleAffiliateTier::Update($ID, $arFields))
			{
				if ($ex = $APPLICATION->GetException())
					$errorMessage .= $ex->GetString().".<br>";
				else
					$errorMessage .= GetMessage("SATE1_ERROR_SAVE").".<br>";
			}
		}
		else
		{
			$ID = CSaleAffiliateTier::Add($arFields);
			$ID = intval($ID);
			if ($ID <= 0)
			{
				if ($ex = $APPLICATION->GetException())
					$errorMessage .= $ex->GetString().".<br>";
				else
					$errorMessage .= GetMessage("SATE1_ERROR_SAVE").".<br>";
			}
		}
	}

	if ($errorMessage == '')
	{
		if ($apply == '')
			LocalRedirect("/bitrix/admin/sale_affiliate_tier.php?lang=" . LANGUAGE_ID . GetFilterParams("filter_", false));
	}
	else
	{
		$bVarsFromForm = true;
	}
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

if ($ID > 0)
	$APPLICATION->SetTitle(str_replace("#ID#", $ID, GetMessage("SATE1_TITLE_UPDATE")));
else
	$APPLICATION->SetTitle(GetMessage("SATE1_TITLE_ADD"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$dbAffiliateTier = CSaleAffiliateTier::GetList(array(), array("ID" => $ID));
if (!$dbAffiliateTier->ExtractFields("str_"))
	$ID = 0;

if ($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_sale_affiliate_tier", "", "str_");
?>

<?
$aMenu = array(
		array(
				"TEXT" => GetMessage("SATE1_LIST"),
				"LINK" => "/bitrix/admin/sale_affiliate_tier.php?lang=" . LANGUAGE_ID . GetFilterParams("filter_"),
				"ICON" => "btn_list"
			)
	);

if ($ID > 0)
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
			"TEXT" => GetMessage("SATE1_ADD"),
			"LINK" => "/bitrix/admin/sale_affiliate_tier_edit.php?lang=" . LANGUAGE_ID . GetFilterParams("filter_"),
			"ICON" => "btn_new"
		);

	if ($saleModulePermissions >= "W")
	{
		$aMenu[] = array(
				"TEXT" => GetMessage("SATE1_DELETE"),
				"LINK" => "javascript:if(confirm('".GetMessage("SATE1_DELETE_CONF")."')) window.location='/bitrix/admin/sale_affiliate_tier.php?ID=".$ID."&action=delete&lang=" . LANGUAGE_ID . "&".bitrix_sessid_get()."#tb';",
				"WARNING" => "Y",
				"ICON" => "btn_delete"
			);
	}
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?if($errorMessage <> '')
	CAdminMessage::ShowMessage(Array("DETAILS"=>$errorMessage, "TYPE"=>"ERROR", "MESSAGE"=>GetMessage("SATE1_ERROR_SAVE"), "HTML"=>true));?>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="form1">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<input type="hidden" name="ID" value="<?echo $ID ?>">
<?=bitrix_sessid_post()?>

<?
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("SATE1_TIER"), "ICON" => "sale", "TITLE" => GetMessage("SATE1_TIER_ALT")),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>

	<?if ($ID > 0):?>
		<tr>
			<td width="40%">ID:</td>
			<td width="60%"><?=$ID?></td>
		</tr>
	<?endif;?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?echo GetMessage("SATE1_SITE")?></td>
		<td width="60%">
			<?echo CSite::SelectBox("SITE_ID", $str_SITE_ID, "", "");?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SATE1_RATE1")?></td>
		<td>
			<input type="text" name="RATE1" value="<?= $str_RATE1 ?>" size="10" maxlength="10">%
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SATE1_RATE2")?></td>
		<td>
			<input type="text" name="RATE2" value="<?= $str_RATE2 ?>" size="10" maxlength="10">%
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SATE1_RATE3")?></td>
		<td>
			<input type="text" name="RATE3" value="<?= $str_RATE3 ?>" size="10" maxlength="10">%
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SATE1_RATE4")?></td>
		<td>
			<input type="text" name="RATE4" value="<?= $str_RATE4 ?>" size="10" maxlength="10">%
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SATE1_RATE5")?></td>
		<td>
			<input type="text" name="RATE5" value="<?= $str_RATE5 ?>" size="10" maxlength="10">%
		</td>
	</tr>
<?
$tabControl->EndTab();
?>

<?
$tabControl->Buttons(
	array(
		"disabled" => ($saleModulePermissions < "W"),
		"back_url" => "/bitrix/admin/sale_affiliate_plan.php?lang=" . LANGUAGE_ID . GetFilterParams("filter_")
	)
);
?>

<?
$tabControl->End();
?>
</form>
<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
