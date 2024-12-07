<?php

use Bitrix\Main\Web\Json;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var DateTimeUfComponent $component
 * @var array $arResult
 */

$component = $this->getComponent();

$isFirst = true;
?>

<div class="fields datetime field-wrap">
	<?php
	$nodes = [];
	foreach($arResult['value'] as $item)
	{
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
		<span class="fields datetime field-item">
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

<script>
	BX.ready(function ()
	{
		new BX.Mobile.Field.Datetime(
			<?= Json::encode([
				'name' => 'BX.Mobile.Field.Datetime',
				'nodes' => $nodes,
				'restrictedMode' => true,
				'formId' => $arParams['additionalParameters']['formId'],
				'gridId' => $arParams['additionalParameters']['gridId']
			]) ?>
		);
	});
</script>