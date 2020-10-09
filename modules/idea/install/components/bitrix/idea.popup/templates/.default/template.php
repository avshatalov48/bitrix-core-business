<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$APPLICATION->setAdditionalCSS('/bitrix/components/bitrix/idea/templates/.default/style.css');
?>
<div class="idea-side-button-wrapper" id="idea-side-button">
	<div class="idea-side-button-inner" id="idea-side-button-inner">
		<img src="<?=$this->__folder?>/images/idea<?=LANGUAGE_ID=='ru'?'':'_lang'?>.png">
	</div>
	<div class="idea-side-button-t"></div>
	<div class="idea-side-button-b"></div>
</div>
<script type="text/javascript">
	BX.message({IDEA_POPUP_LEAVE_IDEA: '<?=GetMessageJS("IDEA_POPUP_LEAVE_IDEA")?>', IDEA_POPUP_WAIT : '<?=GetMessageJS("IDEA_POPUP_WAIT")?>', IDEA_POPUP_APPLY : '<?=GetMessageJS("IDEA_POPUP_APPLY")?>', IDEA_POPUP_CANCEL : '<?=GetMessageJS("IDEA_POPUP_CANCEL")?>'});
	<?if($arParams["BUTTON_COLOR"] <> ''):?>BX('idea-side-button-inner').style.backgroundColor = '<?=$arParams["BUTTON_COLOR"];?>';<?endif;?>
</script>