<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);?>
<?php
$INPUT_ID = trim($arParams['~INPUT_ID']);
if ($INPUT_ID == '')
{
	$INPUT_ID = 'title-search-input';
}
$INPUT_ID = CUtil::JSEscape($INPUT_ID);

$CONTAINER_ID = trim($arParams['~CONTAINER_ID']);
if ($CONTAINER_ID == '')
{
	$CONTAINER_ID = 'title-search';
}
$CONTAINER_ID = CUtil::JSEscape($CONTAINER_ID);

if ($arParams['SHOW_INPUT'] !== 'N'):?>
	<div id="<?php echo $CONTAINER_ID?>">
	<form action="<?php echo $arResult['FORM_ACTION']?>">
		<input id="<?php echo $INPUT_ID?>" type="text" name="q" value="" size="40" maxlength="50" autocomplete="off" />&nbsp;<input name="s" type="submit" value="<?=GetMessage('CT_BST_SEARCH_BUTTON');?>" />
	</form>
	</div>
<?php endif?>
<script>
	BX.ready(function(){
		new JCTitleSearch({
			'AJAX_PAGE' : '<?php echo CUtil::JSEscape(POST_FORM_ACTION_URI)?>',
			'CONTAINER_ID': '<?php echo $CONTAINER_ID?>',
			'INPUT_ID': '<?php echo $INPUT_ID?>',
			'MIN_QUERY_LEN': 2
		});
	});
</script>
