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
		?>
		<span>
			<?= HtmlFilter::encode($fileInfo['ORIGINAL_NAME']) ?>
		</span> ( <?= \CFile::formatSize($fileInfo['FILE_SIZE']) ?>)
		<?php
	}
}