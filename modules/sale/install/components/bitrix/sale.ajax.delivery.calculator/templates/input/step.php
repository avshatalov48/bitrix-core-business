<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<?=ShowNote($arResult["RESULT"]["TEXT"])?>
<script>deliveryCalcProceed(<?=htmlspecialcharsbx($arResult["JS_PARAMS"])?>);</script>