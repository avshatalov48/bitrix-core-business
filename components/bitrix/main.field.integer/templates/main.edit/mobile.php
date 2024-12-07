<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var IntegerUfComponent $component
 * @var array $arResult
 */
$component = $this->getComponent();

foreach($arResult['value'] as $value)
{
	$multipleClass = ($arResult['userField']['MULTIPLE'] === 'Y' ? ' multiple' : '');
	?>
	<span
		class="mobile-grid-data-span <?= $multipleClass ?>"
	>
		<input
			<?= $component->getHtmlBuilder()->buildTagAttributes($value['attrList']) ?>
		>
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