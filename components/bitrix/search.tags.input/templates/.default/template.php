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
			new JsTc(input, '<?php echo $arParams['ADDITIONAL_VALUES']?>');
	});
</script>
<?php
if (isset($arParams['SILENT']) && $arParams['SILENT'] == 'Y')
{
	return;
}
?><input
	name="<?=$arResult['NAME']?>"
	id="<?=$arResult['ID']?>"
	value="<?=$arResult['VALUE']?>"
	class="search-tags"
	type="text"
	autocomplete="off"
	<?=$arResult['TEXT']?>
/>
