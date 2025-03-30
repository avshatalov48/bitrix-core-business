<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\UI\Extension;
use Bitrix\Main\UI\FileInputUtility;
use Bitrix\Main\Web\Json;

/**
 * @var $arResult array
 * @var $component FileUfComponent
 */

Extension::load(
	[
		'main.core',
		'ui.vue3',
		'ui.uploader.tile-widget',
	]
);

$postfix = $this->randString();
if ($component->isAjaxRequest())
{
	$postfix .= time();
}

$fileInputUtility = FileInputUtility::instance();
$uploaderContextGenerator = (new \Bitrix\Main\UserField\File\UploaderContextGenerator($fileInputUtility, $arResult['userField']));

$controlId = $uploaderContextGenerator->getControlId();
$containerId = 'field-item-' . $controlId . '-' . $postfix;

if (isset($arResult['value']) && is_array($arResult['value']))
{
	$arResult['value'] = array_filter(
		$arResult['value'],
		fn($key) => ($arResult['value'][$key] > 0),
		ARRAY_FILTER_USE_KEY
	);
}

$cid = $fileInputUtility->registerControl("", $controlId);

$context = $uploaderContextGenerator->getContextInEditMode($cid);

$fileIds = $arResult['value'] ?? [];
foreach ($fileIds as $fileId)
{
	$fileId = (int)$fileId;
	if ($fileId > 0)
	{
		$fileInputUtility->registerFile($cid, $fileId);
	}
}
unset($fileId);

?>

<span class='field-wrap'>
	<span id="<?=$containerId?>" class='field-item'>
	</span>
</span>

<script>
	BX.ready(() => {
		const app = new BX.Main.Field.File.App(<?= Json::encode([
			'controlId' => $controlId,
			'containerId' => $containerId,
			'context'=> $context,
			'value'=> $fileIds,
		]) ?>);

		app.start();
	});
</script>
