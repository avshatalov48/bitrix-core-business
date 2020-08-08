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
			"PATH_TO_GROUP_EDIT" => ($arParams["PATH_TO_GROUP_CREATE"] <> ''
				? htmlspecialcharsback($arParams["PATH_TO_GROUP_CREATE"])
				: ""
			)
		),
		null,
		array("HIDE_ICONS" => "Y")
	);

	?><div class="sonet-add-group-button">
		<a class="sonet-add-group-button-left" href="<?=$arParams["~HREF"]?>" title="<?= GetMessage("SONET_C36_T_CREATE") ?>"></a>
		<div class="sonet-add-group-button-fill"><a href="<?=$arParams["~HREF"]?>" class="sonet-add-group-button-fill-text"><?= GetMessage("SONET_C36_T_CREATE") ?></a></div>
		<a class="sonet-add-group-button-right" href="<?=$arParams["~HREF"]?>" title="<?= GetMessage("SONET_C36_T_CREATE") ?>"></a>
		<div class="sonet-add-group-button-clear"></div>
	</div><?
}
?>