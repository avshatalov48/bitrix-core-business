<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/*
?>
<div id="delivery_info_<?=$arParams["DELIVERY"]?>_<?=$arParams["PROFILE"]?>"><a href="javascript:" onClick="deliveryCalcProceed('<?=$arParams["STEP"]+1?>', '<?=$arParams["DELIVERY"]?>', '<?=$arParams["PROFILE"]?>', '<?=$arParams["ORDER_WEIGHT"]?>', '<?=$arParams["ORDER_PRICE"]?>', '<?=$arParams["LOCATION_TO"]?>', '<?=$arParams["CURRENCY"]?>')"><?=GetMessage('SADC_DOCALC')?></a></div><div id="wait_container_<?=$arParams["DELIVERY"]?>_<?=$arParams["PROFILE"]?>" style="display: none;"></div>
*/
//echo "<pre>Next step: ".print_r($arResult, true)."</pre>";
?>
<?=ShowNote($arResult["RESULT"]["TEXT"])?>
<script>deliveryCalcProceed(<?=htmlspecialcharsbx($arResult["JS_PARAMS"])?>);</script>