<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
?>
<script>
jsColorPickerMess = window.jsColorPickerMess = {
	DefaultColor: '<?echo CUtil::JSEscape(GetMessage('DefaultColor'));?>'
}
</script>
<?
if ($arParams['SHOW_BUTTON'] == 'Y')
{
		$ID = $arParams['ID'] ? $arParams['ID'] : RandString();
?>
<span id="bx_colorpicker_<?echo $ID?>"></span>
<style>#bx_btn_<?echo $ID?>{background-position: -280px -21px;}</style>
<script>
var CP_<?echo CUtil::JSEscape($ID)?> = new window.BXColorPicker({
	'id':'<?echo CUtil::JSEscape($ID)?>'<?if ($arParams['NAME']):?>,'name':'<?echo CUtil::JSEscape($arParams['~NAME']);?>'<?endif;if ($arParams['ONSELECT']):?>,'OnSelect':<?echo $arParams['ONSELECT'];endif;?>
});
BX.ready(function () {document.getElementById('bx_colorpicker_<?echo CUtil::JSEscape($ID)?>').appendChild(CP_<?echo CUtil::JSEscape($ID)?>.pCont)});
</script>
<?
}
?>