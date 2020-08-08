<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var DateUfComponent $component
 * @var array $arResult
 */

$component = $this->getComponent();

$isFirst = true;
?>

<div class="fields date field-wrap">
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

		<span class="fields date field-item">
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
		new BX.Mobile.Field.Date(
			<?=CUtil::PhpToJSObject([
				'name' => 'BX.Mobile.Field.Date',
				'nodes' => $nodes,
				'restrictedMode' => true,
				'formId' => $arParams['additionalParameters']['formId'],
				'gridId' => $arParams['additionalParameters']['gridId']
			])?>
		);
	});
</script>