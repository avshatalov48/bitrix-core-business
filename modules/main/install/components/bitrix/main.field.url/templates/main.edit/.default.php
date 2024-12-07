<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var UrlUfComponent $component
 * @var array $arResult
 */
$component = $this->getComponent();
?>

<span class="fields url field-wrap">
	<?php
	foreach($arResult['value'] as $value)
	{
		?>
		<span class="fields url field-item">
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