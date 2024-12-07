<?php

use Bitrix\Main\Web\Json;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var DoubleUfComponent $component
 * @var array $arResult
 */

$component = $this->getComponent();
?>

<div class="mobile-grid-data-string-container">
	<div class="fields double field-wrap">
		<?php
		$isFirst = true;
		$nodes = [];
		foreach($arResult['value'] as $item)
		{
			$nodes[] = $item['attrList']['id'];
			if(!$isFirst)
			{
				print $component->getHtmlBuilder()->getMultipleValuesSeparator();
			}
			$isFirst = false;
			?>
			<span class="field-item">
				<input
					<?= $component->getHtmlBuilder()->buildTagAttributes($item['attrList']) ?>
				>
				<span
					placeholder="<?= $item['placeholder'] ?>"
					id="<?= $item['attrList']['id'] ?>_container"
				>
					<?= ($item['value'] ?: $item['placeholder']) ?>
				</span>
			</span>
			<?php
		}
		?>
	</div>
</div>

<script>
	BX.ready(function ()
	{
		new BX.Mobile.Field.Double(
			<?= Json::encode([
				'name' => 'BX.Mobile.Field.Double',
				'nodes' => $nodes,
				'restrictedMode' => true,
				'formId' => $arParams['additionalParameters']['formId'],
				'gridId' => $arParams['additionalParameters']['gridId']
			]) ?>
		);
	});
</script>