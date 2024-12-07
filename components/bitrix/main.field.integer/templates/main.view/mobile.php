<?php

use Bitrix\Main\Web\Json;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var IntegerUfComponent $component
 * @var array $arResult
 */

$component = $this->getComponent();

$isFirst = true;
?>

<div class="mobile-grid-data-string-container">
	<div class="fields integer field-wrap">
		<?php
		$nodes = [];
		foreach($arResult['value'] as $item):
			$nodes[] = $item['attrList']['id'];
			if($isFirst)
			{
				$isFirst = false;
			}
			else
			{
				print $component->getHtmlBuilder()->getMultipleValuesSeparator();
			}
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
		<?php endforeach; ?>
	</div>
</div>

<script>
	BX.ready(function () {
		new BX.Mobile.Field.Integer(
			<?= Json::encode([
				'name' => 'BX.Mobile.Field.Integer',
				'nodes' => $nodes,
				'restrictedMode' => true,
				'formId' => $arParams['additionalParameters']['formId'],
				'gridId' => $arParams['additionalParameters']['gridId']
			]) ?>
		);
	});
</script>