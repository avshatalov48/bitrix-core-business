<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var string $templateFolder
 *
 */?>
<script>
	BX.message({
		BX_FPD_LINK_1:'<?=(isset($arParams['MPF_DESTINATION_1']) && !empty($arParams['MPF_DESTINATION_1']) ? CUtil::JSEscape($arParams['MPF_DESTINATION_1']) : GetMessageJS("MPF_DESTINATION_1"))?>',
		BX_FPD_LINK_2:'<?=GetMessageJS("MPF_DESTINATION_2")?>',
		TAG_ADD: '<?=GetMessageJS("MPF_ADD_TAG1")?>',
		MPF_IMAGE: '<?=GetMessageJS("MPF_IMAGE_TITLE")?>',
		MPF_FILE: '<?=GetMessageJS("MPF_INSERT_FILE")?>',
		MPF_FILE_INSERT_IN_TEXT: '<?=GetMessageJS("MPF_FILE_INSERT_IN_TEXT")?>',
		MPF_FILE_IN_TEXT: '<?=GetMessageJS("MPF_FILE_IN_TEXT")?>',
		MPF_SMILE_SET : '<?=GetMessageJS("MPF_SMILE_SET")?>',
		MPF_TEMPLATE_FOLDER: '<?=CUtil::JSEscape($templateFolder)?>',
		MPF_NAME_TEMPLATE : '<?=$arParams['NAME_TEMPLATE']?>',
		spoilerText: '<?=GetMessageJS("MPF_SPOILER")?>',
		MPF_PIN_EDITOR_PANNEL: '<?=GetMessageJS("MPF_PIN_EDITOR_PANNEL")?>'
	});
</script>