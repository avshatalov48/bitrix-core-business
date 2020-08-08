<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule("socialnetwork"))
	return false;

if (intval($arGadgetParams["ID"]) <= 0)
	return false;

if (
		!array_key_exists("NAME", $arGadgetParams)
		|| !array_key_exists("DESCRIPTION", $arGadgetParams)
		|| !array_key_exists("CLOSED", $arGadgetParams)
		|| !array_key_exists("OPENED", $arGadgetParams)
		|| !array_key_exists("VISIBLE", $arGadgetParams)
		|| !array_key_exists("SUBJECT_NAME", $arGadgetParams)
		|| !array_key_exists("DATE_CREATE", $arGadgetParams)
		|| !array_key_exists("NUMBER_OF_MEMBERS", $arGadgetParams)
	)
	$arGadgetParams = CSocNetGroup::GetByID($arGadgetParams["ID"]);

$arGadgetParams["NAME"] = (isset($arGadgetParams["NAME"]) ? $arGadgetParams["NAME"] : "");
$arGadgetParams["DESCRIPTION"] = (isset($arGadgetParams["DESCRIPTION"]) ? $arGadgetParams["DESCRIPTION"] : "");
$arGadgetParams["CLOSED"] = (isset($arGadgetParams["CLOSED"]) ? $arGadgetParams["CLOSED"] : "N");
$arGadgetParams["OPENED"] = (isset($arGadgetParams["OPENED"]) ? $arGadgetParams["OPENED"] : "Y");
$arGadgetParams["VISIBLE"] = (isset($arGadgetParams["VISIBLE"]) ? $arGadgetParams["VISIBLE"] : "Y");
$arGadgetParams["SUBJECT_NAME"] = (isset($arGadgetParams["SUBJECT_NAME"]) ? $arGadgetParams["SUBJECT_NAME"] : "");
$arGadgetParams["DATE_CREATE"] = (isset($arGadgetParams["DATE_CREATE"]) ? $arGadgetParams["DATE_CREATE"] : "");
$arGadgetParams["NUMBER_OF_MEMBERS"] = (isset($arGadgetParams["NUMBER_OF_MEMBERS"]) ? $arGadgetParams["NUMBER_OF_MEMBERS"] : "");
?>
<h4><?=$arGadgetParams["NAME"]?></h4>
<table width="100%" cellspacing="2" cellpadding="2">
<?if($arGadgetParams["CLOSED"] == "Y"):?>
	<tr>
		<td colspan="2"><b><?= GetMessage("GD_SONET_GROUP_DESC_ARCHIVE") ?></b></td>
	</tr>
<?endif;?>
<?if($arGadgetParams["SUBJECT_NAME"] <> ''):?>
	<tr>
		<td width="25%"><?= GetMessage("GD_SONET_GROUP_DESC_SUBJECT_NAME") ?>:</td>
		<td width="75%"><?=$arGadgetParams["SUBJECT_NAME"]?></td>
	</tr>
<?endif;?>
<?if($arGadgetParams["DESCRIPTION"] <> ''):?>
	<tr>
		<td width="25%" valign="top"><?= GetMessage('GD_SONET_GROUP_DESC_DESCRIPTION') ?>:</td>
		<td valign="top" width="75%"><?=$arGadgetParams["DESCRIPTION"]?></td>
	</tr>
<?endif;?>
<tr>
	<td width="25%"><?= GetMessage("GD_SONET_GROUP_DESC_CREATED") ?>:</td>
	<td width="75%"><?=$arGadgetParams["DATE_CREATE"]?></td>
</tr>
<tr>
	<td width="25%"><?= GetMessage("GD_SONET_GROUP_DESC_NMEM") ?>:</td>
	<td width="75%"><?=$arGadgetParams["NUMBER_OF_MEMBERS"]?></td>
</tr>
<tr>
	<td width="25%" valign="top"><?= GetMessage("GD_SONET_GROUP_DESC_TYPE") ?>:</td>
	<td valign="top" width="75%">
	<?=($arGadgetParams["OPENED"] == "Y" ? GetMessage("GD_SONET_GROUP_DESC_TYPE_O1") : GetMessage("GD_SONET_GROUP_DESC_TYPE_O2"))?><br>
	<?=($arGadgetParams["VISIBLE"] == "Y" ? GetMessage("GD_SONET_GROUP_DESC_TYPE_V1") : GetMessage("GD_SONET_GROUP_DESC_TYPE_V2"))?>
	</td>
</tr>
<?
if (array_key_exists("PROPERTIES_SHOW", $arGadgetParams) && $arGadgetParams["PROPERTIES_SHOW"] == "Y"):
	foreach ($arGadgetParams["PROPERTIES_DATA"] as $fieldName => $arUserField):
		if (is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0 || !is_array($arUserField["VALUE"]) && $arUserField["VALUE"] <> ''):
			?><tr>
				<td width="25%"><?=$arUserField["EDIT_FORM_LABEL"]?>:</td>
				<td width="75%"><?
				$GLOBALS["APPLICATION"]->IncludeComponent(
					"bitrix:system.field.view", 
					$arUserField["USER_TYPE"]["USER_TYPE_ID"], 
					array("arUserField" => $arUserField),
					null,
					array("HIDE_ICONS"=>"Y")
				);
				?></td>
			</tr><?
		endif;
	endforeach;
endif;

if (array_key_exists("G_SONET_GROUP_DESC_REQUEST_SENT", $arParams)):
		
	if ($arParams["G_SONET_GROUP_DESC_REQUEST_SENT"] == "U"):
?>
		<tr>
			<td width="25%" valign="top">
			<td width="75%"><strong><?= GetMessage("GD_SONET_GROUP_DESC_REQUEST_SENT_MESSAGE");?></strong></td>
		</tr>
	<? elseif ($arParams["G_SONET_GROUP_DESC_REQUEST_SENT"] == "G"): 

		global $USER;
		$url = str_replace("#user_id#", $USER->GetID(), COption::GetOptionString("socialnetwork", "user_request_page", 
			(IsModuleInstalled("intranet")) ? "/company/personal/user/#user_id#/requests/" : "/club/user/#user_id#/requests/", SITE_ID));
	?>
		<tr>
			<td width="25%" valign="top">
			<td width="75%"><strong><?=GetMessage("GD_SONET_GROUP_DESC_REQUEST_SENT_MESSAGE_BY_GROUP", array("#LINK#" => $url))?></strong></td>
		</tr>
	<? endif; ?>
<? endif; ?>
</table>