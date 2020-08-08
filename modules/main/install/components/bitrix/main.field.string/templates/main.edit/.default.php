<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;

/**
 * @var StringUfComponent $component
 * @var array $arResult
 */

$component = $this->getComponent();
?>

<span class='field-wrap'>
	<?php
	foreach($arResult['fieldValues'] as $value)
	{
		?>
		<span class='field-item'>
			<?php if($value['tag'] === 'input'): ?>
				<input
					<?= $component->getHtmlBuilder()->buildTagAttributes($value['attrList']) ?>
				>
			<?php else: ?>
				<textarea
					<?= $component->getHtmlBuilder()->buildTagAttributes($value['attrList']) ?>
				><?= HtmlFilter::encode($value['attrList']['value']) ?></textarea>
			<?php endif; ?>
		</span>
		<?php
	}

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