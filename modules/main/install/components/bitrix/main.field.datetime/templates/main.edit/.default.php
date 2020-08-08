<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var DateTimeUfComponent $component
 * @var array $arResult
 */
$component = $this->getComponent();
?>

<span class="fields datetime field-wrap">
	<?php foreach($arResult['value'] as $value): ?>
		<span class="fields datetime field-item">
			<input
				<?= $component->getHtmlBuilder()->buildTagAttributes($value['attrList']) ?>
			>
			<i <?= $component->getHtmlBuilder()->buildTagAttributes([
				'class' => $component->getHtmlBuilder()->getCssClassName() . ' icon',
				'onclick' => 'BX.calendar({
					node: this.previousElementSibling, 
					field: this.previousElementSibling, 
					bTime: true, 
					bSetFocus: false,
					bUseSecond: '.$arResult['useSecond'].'
				})'
			]) ?>>
			</i>
		</span>
	<?php endforeach; ?>

	<?php
	if(
		$arResult['userField']['MULTIPLE'] === 'Y'
		&&
		$arResult['additionalParameters']['SHOW_BUTTON'] !== 'N'
	)
	{
		print $component->getHtmlBuilder()->getCloneButton($arResult['fieldName']);
	}
	?>
</span>