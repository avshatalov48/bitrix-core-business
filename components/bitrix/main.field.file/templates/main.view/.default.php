<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Text\UtfSafeString;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\UI\FileInputUtility;
use Bitrix\Main\UI\Viewer\ItemAttributes;

/**
 * @var array $arResult
 */

Extension::load(
	[
		'ui.uploader.tile-widget',
		'ui.viewer',
		'ui.icons.generator',
	]
);

$fileInputUtility = FileInputUtility::instance();
$uploaderContextGenerator = (new \Bitrix\Main\UserField\File\UploaderContextGenerator($fileInputUtility, $arResult['userField']));

$controlId = $uploaderContextGenerator->getControlId();

?>
<span class="fields file field-wrap">
<div>
<div class="ui-tile-uploader">
	<div class="ui-tile-uploader-items">
	<?php
	$urlTemplate = CComponentEngine::makePathFromTemplate(
		'/bitrix/services/main/ajax.php'
		. '?action=ui.fileuploader.preview'
		. '&SITE_ID=#SITE#'
		. '&controller=main.fileUploader.fieldFileUploaderController'
		. '&controllerOptions=#CONTEXT#'
		. '&fileId=#FILE_ID#'
	);
	foreach($arResult['value'] as $fileInfo)
	{
		if (!is_array($fileInfo))
		{
			continue;
		}

		$fileId = (int)$fileInfo['ID'];
		$iconId = $controlId . '-icon-' . $fileId;

		if(!empty($arResult['additionalParameters']['URL_TEMPLATE']))
		{
			$fileUrlForViewer = \CComponentEngine::MakePathFromTemplate(
				$arResult['additionalParameters']['URL_TEMPLATE'],
				['file_id' => $fileId]
			);
		}
		else
		{
			$fileUrlForViewer = $fileInfo['SRC'];
		}

		$fileName = $fileInfo['ORIGINAL_NAME'];

		$viewerAttributes = ItemAttributes::tryBuildByFileId($fileId, $fileUrlForViewer);
		$viewerAttributes->setTitle($fileName);
		$viewerAttributes->setGroupBy(md5($controlId));

		$fileExtensionPosition = UtfSafeString::getLastPosition($fileName, '.');
		$fileExtension = $fileExtensionPosition === false ? '' : mb_substr($fileName, $fileExtensionPosition);
		$fileNameLength = mb_strlen($fileName);
		$fileExtensionLength = mb_strlen($fileExtension);
		$fileNameWithoutExtensionLength = $fileNameLength - $fileExtensionLength;
		$fileNameWithoutExtension =
			$fileExtensionLength > 0
				? mb_substr($fileName, 0, $fileNameLength - $fileExtensionLength)
				: $fileName
		;
		$fileName = $fileNameWithoutExtension . $fileExtension;
		if ($fileNameWithoutExtensionLength > 27)
		{
			$clampedFileName =
				mb_substr($fileNameWithoutExtension, 0, 17)
				. '...'
				. mb_substr($fileNameWithoutExtension, -5)
			;
		}
		else
		{
			$clampedFileName = $fileNameWithoutExtension;
		}
		unset($fileExtensionPosition, $fileNameLength, $fileExtensionLength);
		$fileExtension = mb_strtolower(trim($fileExtension));
		$fileExtensionLength = mb_strlen($fileExtension);
		$fileExtensionForIcon = $fileExtensionLength > 1 ? mb_substr($fileExtension, 1) : '...';
		$url = CComponentEngine::makePathFromTemplate($urlTemplate, [
			'FILE_ID' => (int)$fileInfo['ID'],
			'CONTEXT' => urlencode(
				json_encode($uploaderContextGenerator->getContextForFileInViewMode((int)$fileInfo['ID']))
			)
		]);

		if ($viewerAttributes->getViewerType() === 'image')
		{
			?><a href="<?= htmlspecialcharsbx($fileUrlForViewer) ?>"
				class="ui-tile-uploader-item ui-tile-uploader-item--complete --image" <?= $viewerAttributes ?>>
				<div class="ui-tile-uploader-item-content">
					<div class="ui-tile-uploader-item-preview">
						<div class="ui-tile-uploader-item-image"
							 style="background-image: url('<?= htmlspecialcharsbx($url) ?>');"></div>
					</div>
					<?php
					if ($fileName !== '')
					{
						?><div class="ui-tile-uploader-item-name-box" title="<?= htmlspecialcharsbx($fileName) ?>">
							<div class="ui-tile-uploader-item-name"><span
								class="ui-tile-uploader-item-name-title"><?php
									echo htmlspecialcharsbx(getFileName($clampedFileName));
								?></span><span
								class="ui-tile-uploader-item-name-extension"><?php
									echo htmlspecialcharsbx($fileExtension);
								?></span>
							</div>
						</div><?php
					}
					?>
				</div>
			</a><?php
		}
		elseif ($fileName !== '')
		{
			?><a href="<?= htmlspecialcharsbx($fileUrlForViewer) ?>"
				class="ui-tile-uploader-item ui-tile-uploader-item--complete" <?= $viewerAttributes ?>>
				<div class="ui-tile-uploader-item-content">
					<div class="ui-tile-uploader-item-preview">
						<div class="ui-tile-uploader-item-file-icon"><div id="<?= $iconId ?>"></div></div>
					</div>
					<div class="ui-tile-uploader-item-name-box" title="<?= htmlspecialcharsbx($fileName) ?>">
						<div class="ui-tile-uploader-item-name"><span
								class="ui-tile-uploader-item-name-title"><?php
									echo htmlspecialcharsbx(getFileName($clampedFileName));
								?></span><span
								class="ui-tile-uploader-item-name-extension"><?php
									echo htmlspecialcharsbx($fileExtension);
								?></span></div>
					</div>
				</div>
			</a>
			<script>
				BX.ready(function() {
					const iconGenerator = new BX.UI.Icons.Generator.FileIcon({
						name: "<?= CUtil::JSEscape($fileExtensionForIcon) ?>",
						size: 36
					});
					iconGenerator.renderTo(BX("<?= CUtil::JSEscape($iconId) ?>"));
				});
			</script><?php
		}
		else
		{
			?><a href="<?= htmlspecialcharsbx($fileUrlForViewer) ?>"
				class="ui-tile-uploader-item ui-tile-uploader-item--complete" <?= $viewerAttributes ?>>
				<div class="ui-tile-uploader-item-content">
					<div class="ui-tile-uploader-item-preview">
						<div class="ui-tile-uploader-item-file-default"><span id="<?= $iconId ?>"></span></div>
					</div>
				</div>
			</a>
			<script>
				BX.ready(function() {
					const iconGenerator = new BX.UI.Icons.Generator.FileIcon({
						name: "<?= CUtil::JSEscape($fileExtensionForIcon) ?>",
						size: 36
					});
					iconGenerator.renderTo(BX("<?= CUtil::JSEscape($iconId) ?>"));
				});
			</script><?php
		}
	}
	?>
	</div>
</div>
</div>
</span>
