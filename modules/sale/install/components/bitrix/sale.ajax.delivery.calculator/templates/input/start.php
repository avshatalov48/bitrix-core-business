<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<?
if ($arResult["B_ADMIN"] == "Y")
{
	$APPLICATION->AddHeadScript("/bitrix/js/main/cphttprequest.js");
	$APPLICATION->AddHeadScript($templateFolder."/proceed.js");
}
?>
<div id="delivery_info_<?=$arParams["INPUT_NAME"]?>"><a href="javascript:void(0)" onClick="deliveryCalcProceed(<?=htmlspecialcharsbx($arResult["JS_PARAMS"])?>)"><?=GetMessage('SADC_DOCALC')?></a></div><div id="wait_container_<?=$arParams["INPUT_NAME"]?>" style="display: none;"></div>