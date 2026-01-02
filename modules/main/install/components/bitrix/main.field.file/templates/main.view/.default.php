<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Text\UtfSafeString;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\UI\FileInputUtility;
use Bitrix\Main\UI\Viewer\ItemAttributes;
use Bitrix\Main\UserField\File\UploaderContextGenerator;
use Bitrix\Main\UserField\Types\FileType;
use Bitrix\Main\Web\Json;

/**
 * @var array $arResult
 */

Extension::load([
	'main.uf.file.uploader.selectable-view-widget',
	'ui.viewer',
]);

$defaultView = $arResult['userField']['SETTINGS']['DEFAULT_VIEW'] ?? null;
$defaultView = FileType::isAvailableDefaultView($defaultView) ? $defaultView : null;

$fileInputUtility = FileInputUtility::instance();
$uploaderContextGenerator = (new UploaderContextGenerator($fileInputUtility, $arResult['userField']));

$controlId = $uploaderContextGenerator->getControlId();

$urlTemplate = CComponentEngine::makePathFromTemplate(
	'/bitrix/services/main/ajax.php'
	. '?action=ui.fileuploader.preview'
	. '&SITE_ID=#SITE#'
	. '&controller=main.fileUploader.fieldFileUploaderController'
	. '&controllerOptions=#CONTEXT#'
	. '&fileId=#FILE_ID#'
);

$fileItems = [];
foreach($arResult['value'] as $file)
{
	if (!is_array($file))
	{
		continue;
	}

	$fileId = (int)$file['ID'];
	$fileName = $file['ORIGINAL_NAME'];

	$fileUrlForViewer = $file['SRC'] ?? null;
	if (!empty($arResult['additionalParameters']['URL_TEMPLATE']))
	{
		$fileUrlForViewer = \CComponentEngine::MakePathFromTemplate(
			$arResult['additionalParameters']['URL_TEMPLATE'],
			['file_id' => $fileId]
		);
	}

	$viewerAttributes = ItemAttributes::tryBuildByFileId($fileId, $fileUrlForViewer);
	$viewerAttributes->setTitle($fileName);
	$viewerAttributes->setGroupBy(md5($controlId));

	$fileExtensionPosition = UtfSafeString::getLastPosition($fileName, '.');
	$fileExtension = $fileExtensionPosition === false ? '' : mb_substr($fileName, $fileExtensionPosition + 1);

	$fileContext = $uploaderContextGenerator->getContextForFileInViewMode($fileId);
	$fileUrl = CComponentEngine::makePathFromTemplate($urlTemplate, [
		'FILE_ID' => $fileId,
		'CONTEXT' => urlencode(json_encode($fileContext)),
	]);

	$fileItems[] = [
		'name' => $fileName,
		'extension' => $fileExtension,
		'isImage' => $viewerAttributes->getViewerType() === 'image',
		'url' => $fileUrl,
		'urlForViewer' => $fileUrlForViewer,
		'attributes' => $viewerAttributes->toVueBind(),
	];
}

$widgetOptions = [
	'fileItems' => $fileItems,
	'viewId' => $defaultView,
];

$containerId = $controlId . '_' . $this->randString();

?>
<span class="fields file field-wrap --ui-context-content-light">
	<div id="<?= htmlspecialcharsbx($containerId) ?>"></div>
</span>

<script>
	BX.ready(() => {
		const widget = new BX.Main.UF.File.Uploader.SelectableViewWidget(<?= Json::encode($widgetOptions) ?>);
		widget.mount(document.querySelector('#<?= CUtil::JSEscape($containerId) ?>'));
	});
</script>
