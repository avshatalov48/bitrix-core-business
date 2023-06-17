<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var $arParams
 * @var $arResult
 */

use Bitrix\Main\UI;
use Bitrix\Main\Web\Uri;

UI\Extension::load([
	'ui.tooltip',
	'ui.icons.b24',
	'ui.urlpreview',
]);

?><div class="blog-post-preview">
	<table class="blog-post-preview-info">
		<tr>
			<td><?php
				$style = (!empty($arResult['arUser']['PERSONAL_PHOTO_resized']['src']) ? 'background-image: url('. Uri::urnEncode($arResult['arUser']['PERSONAL_PHOTO_resized']['src']) .');' : '');
				?><span class="ui-icon ui-icon-common-user blog-post-preview-header-icon" title="<?= htmlspecialcharsbx($arResult['POST']['AUTHOR_FORMATTED_NAME']) ?>">
					<i style="<?= $style ?>"></i>
				</span>
			</td>
			<td>
				<span class="blog-post-preview-header-title">
					<a href="<?= htmlspecialcharsbx($arResult['POST']['AUTHOR_PROFILE']) ?>" bx-tooltip-user-id="<?= htmlspecialcharsbx($arResult['POST']['AUTHOR']) ?>">
						<?= htmlspecialcharsbx($arResult['POST']['AUTHOR_FORMATTED_NAME']) ?>
					</a>
					<span class="urlpreview__time-wrap">
						<a href="<?= htmlspecialcharsbx($arParams['URL']) ?>"><span class="urlpreview__time"><?= htmlspecialcharsbx($arResult['POST']['DATE_FORMATTED']) ?></span></a>
					</span>
				</span>
				<?php
				if ($arResult['POST']['TITLE'])
				{
					?><p><strong><?= htmlspecialcharsbx($arResult['POST']['TITLE']) ?></strong></p><?php
				}
				?>
				<p><?= htmlspecialcharsbx($arResult['POST']['PREVIEW_TEXT']) ?></p>
			</td>
		</tr>
	</table>
</div>
