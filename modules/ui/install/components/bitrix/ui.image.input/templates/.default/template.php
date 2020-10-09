<?php

use Bitrix\Main\UI\Extension;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var \UIImageInput $component
 * @var \CBitrixComponentTemplate $this
 * @var array $arParams
 * @var array $arResult
 */

Extension::load(['loader']);

$instanceId = 'bx_file_'.strtolower(preg_replace('/[^a-z0-9]/i', '_', $arParams['FILE_SETTINGS']['id']));
$containerId = $instanceId.'_input_container';
$loaderContainerId = $instanceId.'_loader_container';
?>
<script>
	BX.ready(function() {
		new BX.UI.ImageInput({
			instanceId: '<?=CUtil::JSEscape($instanceId)?>',
			containerId: '<?=CUtil::JSEscape($containerId)?>',
			loaderContainerId: '<?=CUtil::JSEscape($loaderContainerId)?>',
			settings: <?=CUtil::PhpToJSObject($arParams['FILE_SETTINGS'])?>
		});
	});
</script>
<?php
if (!empty($arParams['LOADER_PREVIEW']))
{
	?>
	<div class="ui-image-input-loader-container" id="<?=$loaderContainerId?>"><?=$arParams['~LOADER_PREVIEW']?></div>
	<?php
}
?>
<div class="ui-image-input-container" id="<?=$containerId?>" style="display: none;">
	<?=$arResult['FILE']->show($arParams['FILE_VALUES'])?>
	<span class="ui-image-input-img-add"
			data-role="image-add-button"
			style="display: none;"
	></span>
</div>