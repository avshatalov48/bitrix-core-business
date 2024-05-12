<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<div class="bx-desktop-placeholder" id="workarea-content"></div>
<script>
	document.title = '<?=GetMessage('IM_FULLSCREEN_TITLE_2')?>';
	<?=CIMMessenger::GetTemplateJS(Array(), $arResult)?>
</script>