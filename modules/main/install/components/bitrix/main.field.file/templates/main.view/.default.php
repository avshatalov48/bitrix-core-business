<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;
?>

<span class="fields file field-wrap">
	<?php
	foreach($arResult['value'] as $fileInfo)
	{
		?>
		<span class="fields file field-item">
			<?php
			if (!is_array($fileInfo))
			{
				continue;
			}
			if(\CFile::IsImage($fileInfo['SRC'], $fileInfo['CONTENT_TYPE']))
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
				if(!empty($arResult['additionalParameters']['URL_TEMPLATE']))
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
			?>
		</span>
	<?php
	}
	?>
</span>