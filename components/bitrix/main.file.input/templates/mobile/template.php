<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

/**
 * @var array $arParams
 * @var array $arResult
 */

CJSCore::Init(['uploader']);

$request = Context::getCurrent()->getRequest();

if($arParams['ALLOW_UPLOAD'] === 'N' && empty($arResult['FILES']))
{
	return '';
}

$cnt = count($arResult['FILES']);
$id = CUtil::JSEscape($arParams['CONTROL_ID']);

if($arParams['MULTIPLE'] === 'Y' && mb_substr($arParams['INPUT_NAME'], -2) !== '[]')
{
	$arParams['INPUT_NAME'] .= '[]';
}

$thumbForUploaded = <<<HTML
<div class="webform-field-item-wrap">
	<span class="webform-field-upload-icon webform-field-upload-icon-#ext#">
		<img alt="" src="#preview_url#" onerror="BX.remove(this);">
	</span>
	<a href="#url#" target="_blank" data-bx-role="file-name" class="upload-file-name">#name#</a>
	<span class="upload-file-size" data-bx-role="file-size">#size#</span>
	<i></i>
	<del data-bx-role="file-delete"></del>
	<input id="file-#file_id#" data-bx-role="file-id" type="hidden" name="#input_name#" value="#file_id#">
</div>
HTML;

$thumb = <<<HTML
<div class="webform-field-item-wrap">
	<span class="webform-field-upload-icon webform-field-upload-icon-#ext#" data-bx-role="file-preview"></span>
	<a href="#" target="_blank" data-bx-role="file-name" class="upload-file-name">#name#</a>
	<span class="upload-file-size" data-bx-role="file-size">#size#</span>
	<i></i>
	<del data-bx-role="file-delete"></del>
</div>
HTML;

$listClass = ($arParams['MULTIPLE'] === 'Y' ? 'multiple' : 'single');
?>

<div class="file-input">
	<ol
		class="webform-field-upload-list webform-field-upload-list-<?= $listClass ?>"
		id="mfi-<?= $arParams['CONTROL_ID'] ?>"
	>
		<?php
		$i = 0;
		foreach($arResult['FILES'] as $file)
		{
			$isImage = CFile::IsImage($file['ORIGINAL_NAME'], $file['CONTENT_TYPE']);
			$t = (
			$isImage ? CFile::ResizeImageGet(
				$file,
				['width' => 100, 'height' => 100],
				BX_RESIZE_IMAGE_EXACT,
				false
			)
				: ['src' => '/bitrix/images/1.gif']
			);
			?>
			<li class="saved">
				<?= str_replace(
					['#input_name#', '#file_id#', '#name#', '#size#', '#url#', '#url_delete#', '#preview_url#', '#ext#'],
					[
						str_replace('[]', '[' . $i++ . ']', $arParams['INPUT_NAME']),
						(int)$file['ID'],
						htmlspecialcharsEx($file['ORIGINAL_NAME']),
						CFile::FormatSize($file['FILE_SIZE']),
						$file['URL'],
						$file['URL_DELETE'],
						$t['src'],
						GetFileExtension($file['ORIGINAL_NAME'])
					],
					$thumbForUploaded
				) ?>
			</li>
			<?php
		}
		?>
	</ol>

	<?php
	if($arParams['ALLOW_UPLOAD'] !== 'N')
	{
		?>
		<div
			class="webform-field-upload"
			id="mfi-<?= $arParams['CONTROL_ID'] ?>-button"
		>
			<?php
			if(!empty($arParams['INPUT_CAPTION']))
			{
				$inputCaption = $arParams['INPUT_CAPTION'];
			}
			else
			{
				$inputCaption = (
				$arParams['ALLOW_UPLOAD'] === 'I'
					? Loc::getMessage('MFI_INPUT_CAPTION_ADD_IMAGE')
					: Loc::getMessage('MFI_INPUT_CAPTION_ADD')
				);
			}
			?>

			<span class="webform-small-button webform-button-upload add-field-button">
				<?= $inputCaption ?>
			</span>

			<?php
			if($arParams['MULTIPLE'] === 'N')
			{
				?>
				<span class="webform-small-button webform-button-replace add-field-button">
					<?= ($arParams['ALLOW_UPLOAD'] === 'I'
						? Loc::getMessage('MFI_INPUT_CAPTION_REPLACE_IMAGE')
						: Loc::getMessage('MFI_INPUT_CAPTION_REPLACE')
					) ?>
				</span>
				<?php
			}

			if($arParams['SHOW_AVATAR_EDITOR'] === 'Y' && $arParams['ALLOW_UPLOAD'] === 'I')
			{
				?>
				<input
					type="button"
					id="mfi-<?= $arParams['CONTROL_ID'] ?>-editor"
				>
				<?php
			}
			else
			{
				?>
				<input
					type="file"
					id="file_input_<?= $arParams['CONTROL_ID'] ?>"
					<?= ($arParams['MULTIPLE'] === 'Y' ? ' multiple="multiple"' : '') ?>
				>
				<?php
			}
			?>
		</div>
		<?php
		if(!empty($arParams['ALLOW_UPLOAD_EXT']) || $arParams['MAX_FILE_SIZE'] > 0)
		{
			$message = (
			(!empty($arParams['ALLOW_UPLOAD_EXT']) && $arParams['MAX_FILE_SIZE'] > 0)
				? Loc::getMessage('MFI_NOTICE_1')
				: (
			!empty($arParams['ALLOW_UPLOAD_EXT'])
				? Loc::getMessage('MFI_NOTICE_2')
				: Loc::getMessage('MFI_NOTICE_3')
			)
			);
			?>
			<div class='webform-field-upload-notice'>
				<?= str_replace(
					['#ext#', '#size#'],
					[HtmlFilter::encode($arParams['ALLOW_UPLOAD_EXT']), CFile::FormatSize($arParams['MAX_FILE_SIZE'])],
					$message
				) ?>
			</div>
			<?php
		}
	}
	?>

	<script type='text/javascript'>
		BX.message(<?=CUtil::PhpToJSObject([
			'MFI_THUMB' => $thumb,
			'MFI_THUMB2' => $thumbForUploaded,
			'MFI_UPLOADING_ERROR' => Loc::getMessage('MFI_UPLOADING_ERROR')
		])?>);
		BX.ready(function ()
		{
			BX.MFInput.init(<?=CUtil::PhpToJSObject([
				'controlId' => $arParams['CONTROL_ID'],
				'controlUid' => $arParams['CONTROL_UID'],
				'controlSign' => $arParams['CONTROL_SIGN'],
				'inputName' => $arParams['INPUT_NAME'],
				'maxCount' => ($arParams['MULTIPLE'] === 'N' ? 1 : 0),
				'moduleId' => $arParams['MODULE_ID'],
				'forceMd5' => $arParams['FORCE_MD5'],
				'allowUpload' => $arParams['ALLOW_UPLOAD'],
				'allowUploadExt' => $arParams['ALLOW_UPLOAD_EXT'],
				'uploadMaxFilesize' => $arParams['MAX_FILE_SIZE'],
				'enableCamera' => ($arParams['ENABLE_CAMERA'] !== 'N'),
				'urlUpload' => $arParams['URL_TO_UPLOAD']
			])?>);
		});
	</script>
</div>