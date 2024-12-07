<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
CUtil::InitJSCore("ajax");
?>
<script>

BX.message({
	sonetUSIInputID: '<?=CUtil::JSEscape($arResult["ID"])?>'
});
			
if (typeof oObject != "object")
	window.oObject = {};

function SonetTTTButtonPress(oObj)
{
	window.oObject[oObj.id].oObj.value = '';
	window.oObject[oObj.id].bHint = false;
	if (window.oObject[oObj.id])
		window.oObject[oObj.id].Send('');
}

function SonetSearchButtonClick_<?=$arResult["ID"]?>()
{
	if ('<?=$arResult["FUNCTION"]?>'.length > 0)
	{
		var obj = document.getElementById('<?= $arResult["ID"]; ?>');
		if (obj)
		{
			var name = obj.value;
			<?=$arResult["FUNCTION"]?>(name);
			obj.value = "";
			o1 = <?= (($arResult["FUNCTION"] <> '') ? "document.getElementById('id_".$arResult["NAME"]."_button')" : "null")?>;
			if (o1)
				o1.disabled = true;
		}
	}
}

function SonetStopPost_<?=$arResult["ID"]?>(e)
{
	if (!e)
		e = window.event;

	var kk = e.keyCode;
	if (isNaN(kk) || kk <= 0)
		kk = e.which;

	if (kk == 13)
	{
		SonetSearchButtonClick_<?=$arResult["ID"]?>();

		e.cancelBubble = true;
		if (e.preventDefault)
			e.preventDefault();
		if (e.stopPropagation)
			e.stopPropagation();
		e.returnValue = false;

		return false;
	}
}

function SonetPageLoadUSI()
{
	o1 = <?= (($arResult["FUNCTION"] <> '') ? "document.getElementById('id_".$arResult["NAME"]."_button')" : "null")?>;
	bFirstLoadStateTmpUSI = <?= ($arResult["VALUE"] <> '') ? "false" : "true"?>;
	oObj = document.getElementById('<?=$arResult["ID"]?>');
	if (typeof window.oObject[oObj.id] != 'object')
		window.oObject[oObj.id] = new SonetJsTc(oObj, '<?=$arParams["ADDITIONAL_VALUES"]?>', null, o1, bFirstLoadStateTmpUSI);
}

BX.bind(window, "load", SonetPageLoadUSI);
</script>

<?
if ($arParams["SILENT"] == "Y")
	return;
?>

<nobr><input name="<?=$arResult["NAME"]?>" id="<?=$arResult["ID"]?>" value="<?=($arResult["VALUE"] <> '' ? $arResult["VALUE"] : GetMessage("SONET_T8761_PROMT"))?>" class="search-tags<?=($arParams["CLASS_NAME"] <> '' ? " ".$arParams["CLASS_NAME"] : "")?>" type="text" autocomplete="off" <?=$arResult["TEXT"]?> onkeypress="SonetStopPost_<?=$arResult["ID"]?>(event)" /><input type="button" value="..." onclick="SonetTTTButtonPress(document.getElementById('<?=$arResult["ID"]?>'));"></nobr>
<?if ($arResult["FUNCTION"] <> ''):?>
	<input type="button" name="<?=$arResult["NAME"]?>_button" id="id_<?=$arResult["NAME"]?>_button" value="<?= GetMessage("SONET_T876_SELECT") ?>" <?=($arResult["VALUE"] <> '' ? "" : "disabled")?> onclick="SonetSearchButtonClick_<?=$arResult["ID"]?>()">
<?endif;?>
<?
if (false && $arParams["TMPL_IFRAME"] != "N"):
	?><IFRAME style="width:0px; height:0px; border: 0px;" src="javascript:void(0)" name="<?=$arResult["ID"]?>_div_frame" id="<?=$arResult["ID"]?>_div_frame"></IFRAME><?
endif;
?>