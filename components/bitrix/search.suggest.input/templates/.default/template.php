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
$this->setFrameMode(true);

CJSCore::Init(['ajax']);
?>
<script>
	BX.ready(function(){
		var input = BX("<?php echo $arResult['ID']?>");
		if (input)
			new JsSuggest(input, '<?php echo $arResult['ADDITIONAL_VALUES']?>');
	});
</script>
<IFRAME
	style="width:0px; height:0px; border: 0px;"
	src="javascript:''"
	name="<?php echo $arResult['ID']?>_div_frame"
	id="<?php echo $arResult['ID']?>_div_frame"
></IFRAME><input
	<?php if ($arParams['INPUT_SIZE'] > 0):?>
		size="<?php echo $arParams['INPUT_SIZE']?>"
	<?php endif?>
	name="<?php echo $arParams['NAME']?>"
	id="<?php echo $arResult['ID']?>"
	value="<?php echo $arParams['VALUE']?>"
	class="search-suggest"
	type="text"
	autocomplete="off"
/>
