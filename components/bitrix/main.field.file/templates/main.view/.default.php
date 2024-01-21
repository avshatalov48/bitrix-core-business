<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Text\HtmlFilter;

/**
 * @var array $arResult
 */
?>

<span class="fields file field-wrap">
	<?php
	foreach($arResult['value'] as $fileInfo)
	{
		$dataId = '';
		if (isset($fileInfo['ID']))
		{
			$dataId = 'data-id="' . (int)$fileInfo['ID'] . '"';
		}
		?>
		<span class="fields file field-item" <?= $dataId ?>>
			<?php
			if (!is_array($fileInfo))
			{
				continue;
			}
			if(\CFile::IsImage($fileInfo['ORIGINAL_NAME'], $fileInfo['CONTENT_TYPE']))
			{
				print CFile::ShowImage(
					$fileInfo,
					$arResult['additionalParameters']['FILE_MAX_WIDTH'] ?? 0,
					$arResult['additionalParameters']['FILE_MAX_HEIGHT'] ?? 0,
					'',
					'',
					(isset($arResult['additionalParameters']['FILE_SHOW_POPUP']) && $arResult['additionalParameters']['FILE_SHOW_POPUP'] === 'Y'),
					false,
					0,
					0,
					$arResult['additionalParameters']['URL_TEMPLATE'] ?? ''
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
				<span>
					<a
						href="<?= HtmlFilter::encode($src) ?>"
						<?= ($arResult['targetBlank'] === 'Y' ? 'target="_blank"' : '') ?>
					>
						<?= HtmlFilter::encode($fileInfo['ORIGINAL_NAME']) ?>
					</a>
					(<?= \CFile::formatSize($fileInfo['FILE_SIZE']) ?>)
				</span>
			<?php
			}
			?>
		</span>
	<?php
	}
	?>
</span>