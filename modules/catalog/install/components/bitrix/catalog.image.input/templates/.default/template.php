<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
?>
<div id="<?=$arResult['BLOCK_ID']?>" class="catalog-image-input-wrapper">
	<?php
	$GLOBALS['APPLICATION']->includeComponent(
		'bitrix:ui.image.input',
		'',
		$arResult['UI_PARAMS']
	);
	?>
</div>

<script>
	if (!BX.Catalog.ImageInput.getById('<?=htmlspecialcharsbx(\CUtil::JSEscape($arResult['BLOCK_ID']))?>'))
	{
		new BX.Catalog.ImageInput(
			'<?=htmlspecialcharsbx(\CUtil::JSEscape($arResult['BLOCK_ID']))?>',
			<?=CUtil::PhpToJSObject($arResult['JS_PARAMS'])?>
		);
	}
</script>