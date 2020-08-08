<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($arParams["START_VALUE"] <> '')
	require("full.php");
elseif ($arParams["STEP"] == 0)
	require("start.php");
elseif ($arResult["RESULT"]["RESULT"] == "NEXT_STEP")
	require("step.php");
else
{
	if ($arResult["RESULT"]["RESULT"] == "ERROR")
		echo ShowError($arResult["RESULT"]["TEXT"]);
	elseif ($arResult["RESULT"]["RESULT"] == "NOTE")
		echo ShowNote($arResult["RESULT"]["TEXT"]);
	elseif ($arResult["RESULT"]["RESULT"] == "OK")
	{
		?><input type="text" name="<?=$arParams["INPUT_NAME"]?>" value="<?=roundEx($arResult["RESULT"]["VALUE"], SALE_VALUE_PRECISION)?>" <?echo $arParams['INPUT_DISABLED'] == 'Y' ? 'disabled="disabled"' : ''?> <?=$arParams["INPUT_ADDITIONAL"]?> /><?
	}
}
?>