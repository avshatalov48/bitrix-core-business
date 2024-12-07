<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var DoubleUfComponent $component
 * @var array $arResult
 */
$component = $this->getComponent();
?>

<span class='fields integer field-wrap'>
	<?php
	foreach($arResult['value'] as $value)
	{
		?>
		<span class='fields integer field-item'>
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
		print $component->getHtmlBuilder()->getCloneButton($arResult['fieldName']);
	}
	?>
</span>