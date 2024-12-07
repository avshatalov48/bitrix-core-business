<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Text\HtmlFilter;

/**
 * @var StringUfComponent $component
 * @var array $arResult
 */

$component = $this->getComponent();

foreach($arResult['fieldValues'] as $value)
{
	?>
	<span
		class="mobile-grid-data-span
		<?= ($arResult['userField']['MULTIPLE'] === 'Y' ? ' multiple' : '') ?>"
	>
		<?php
		if($value['tag'] === 'input')
		{
			?>
			<input <?= $component->getHtmlBuilder()->buildTagAttributes($value['attrList']) ?>>
			<?php
		}
		else
		{
			?>
			<textarea
				<?= $component->getHtmlBuilder()->buildTagAttributes($value['attrList']) ?>
			><?= HtmlFilter::encode($value['attrList']['value']) ?></textarea>
			<?php
		}
		?>
	</span>
	<?php
}

if(
	$arResult['userField']['MULTIPLE'] === 'Y'
	&& ($arResult['additionalParameters']['SHOW_BUTTON'] ?? 'Y') !== 'N'
)
{
	print $component->getHtmlBuilder()->getMobileCloneButton($arResult['fieldName']);
}