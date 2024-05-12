<?
if(!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$name_x = CUtil::JSEscape($arParams['NAME']);
$arParams['INPUT_NAME'] = CUtil::JSEscape($arParams['INPUT_NAME']);

if($arParams['SHOW_INPUT'] == 'Y'):?>
	<input type="text" id="<?echo $arParams['INPUT_NAME']?>" name="<?echo $arParams['INPUT_NAME']?>" value="<?echo $arParams['INPUT_VALUE']?>" size="3" />
<?endif;?>

<?if($arParams['SHOW_BUTTON'] == 'Y'):?>
	<input type="button" onclick="<?echo $name_x?>.Show()" value="<?echo $arParams['BUTTON_CAPTION'] ? $arParams['BUTTON_CAPTION'] : '...'?>" />
<?endif;?>

<script>
<?if($arParams['INPUT_NAME'] && !$arParams['ONSELECT']):?>
	function OnSelect_<?=$name_x?>(value){
		document.getElementById('<?=$arParams['INPUT_NAME']?>').value = value;
	}
	<?
	$arParams['ONSELECT'] = 'OnSelect_'.$name_x;
endif;?>

var <?=$name_x?> = new JCTreeSelectControl({
	'AJAX_PAGE' : '<?echo CUtil::JSEscape($this->GetFolder()."/ajax.php")?>',
	'AJAX_PARAMS' : <?echo CUtil::PhpToJsObject(array(
		"IBLOCK_TYPE_ID" => $arParams["IBLOCK_TYPE_ID"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"LANGUAGE_ID" => LANGUAGE_ID,
		"SOCNET_GROUP_ID" => $arParams["SOCNET_GROUP_ID"],
	))?>,
	'MULTIPLE' : <?echo $arParams['MULTIPLE'] == 'Y' ? 'true' : 'false'?>,
	'GET_FULL_INFO': <?echo $arParams['GET_FULL_INFO'] == 'Y' ? 'true' : 'false'?>,
	'ONSELECT' : function(v){<?echo $arParams['ONSELECT']?>(v)},
	'START_TEXT' : '<?echo CUtil::JSEscape($arParams["START_TEXT"])?>',
	'NO_SEARCH_RESULT_TEXT' : '<?echo CUtil::JSEscape($arParams["NO_SEARCH_RESULT_TEXT"])?>',
	'INPUT_NAME' : '<?echo CUtil::JSEscape($arParams["INPUT_NAME"])?>'
});

<?if($arParams['INPUT_NAME']):?>
//BX.ready(function() {
//	<?echo $name_x?>.SetValueFromInput('<?echo $arParams['INPUT_NAME']?>');
//	var inp = document.getElementById('<?echo $arParams['INPUT_NAME']?>');
//	if(inp)
//		inp.onchange = function () {<?echo $name_x?>.SetValue(this.value)}
//});
<?endif;?>
</script>