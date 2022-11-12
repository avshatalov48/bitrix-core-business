<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;

foreach($arResult['value'] as $fileInfo)
{
	if(\CFile::IsImage($fileInfo['ORIGINAL_NAME'], $fileInfo['CONTENT_TYPE']))
	{
		print CFile::ShowImage(
			$fileInfo,
			$arResult['additionalParameters']['FILE_MAX_WIDTH'],
			$arResult['additionalParameters']['FILE_MAX_HEIGHT'],
			'',
			'',
			($arResult['additionalParameters']['FILE_SHOW_POPUP'] === 'Y'),
			false,
			0,
			0,
			$arResult['additionalParameters']['URL_TEMPLATE']
		);
	}
	else
	{
		if($arResult['additionalParameters']['URL_TEMPLATE'] !== '')
		{
			$src = \CComponentEngine::MakePathFromTemplate(
				$arResult['additionalParameters']['URL_TEMPLATE'],
				['file_id' => $fileInfo['ID']]
			);
		}
		else
		{
			$src = $fileInfo['SRC'];
		}
		?>
		<a
			href="<?= HtmlFilter::encode($src) ?>"
			<?= ($arResult['targetBlank'] === 'Y' ? 'target="_blank"' : '') ?>
		>
			<?= HtmlFilter::encode($fileInfo['ORIGINAL_NAME']) ?>
		</a> ( <?= \CFile::formatSize($fileInfo['FILE_SIZE']) ?>)
		<?php
	}
}