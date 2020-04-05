<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if ($arParams["ALLOW_CREATE_GROUP"] == "Y")
{
	$popupName = randString(6);
	$APPLICATION->IncludeComponent(
		"bitrix:socialnetwork.group_create.popup",
		".default",
		array(
			"NAME" => $popupName,
			"PATH_TO_GROUP_EDIT" => (strlen($arParams["PATH_TO_GROUP_CREATE"]) > 0 
				? htmlspecialcharsback($arParams["PATH_TO_GROUP_CREATE"])
				: ""
			)
		),
		null,
		array("HIDE_ICONS" => "Y")
	);

	$strOnClick = "if (BX.SGCP) { BX.SGCP.ShowForm('create', '".$popupName."', event); } else { return false; }";
	?><div class="sonet-add-group-button">
		<a onclick="<?=$strOnClick?>" class="sonet-add-group-button-left" href="<?=$arParams["~HREF"]?>" title="<?= GetMessage("SONET_C36_T_CREATE") ?>"></a>
		<div class="sonet-add-group-button-fill"><a onclick="<?=$strOnClick?>" href="<?=$arParams["~HREF"]?>" class="sonet-add-group-button-fill-text"><?= GetMessage("SONET_C36_T_CREATE") ?></a></div>
		<a onclick="<?=$strOnClick?>" class="sonet-add-group-button-right" href="<?=$arParams["~HREF"]?>" title="<?= GetMessage("SONET_C36_T_CREATE") ?>"></a>
		<div class="sonet-add-group-button-clear"></div>
	</div><?
}
?>