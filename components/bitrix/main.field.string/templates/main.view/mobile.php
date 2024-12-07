<?php

use Bitrix\Main\Web\Json;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var StringUfComponent $component
 * @var array $arResult
 */

$component = $this->getComponent();

$isFirst = true;
?>

<div class="fields string field-wrap">
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
		<span class="field-item">
      			<input
					<?= $component->getHtmlBuilder()->buildTagAttributes($item['attrList'], false) ?>
				>
        		<span
					id="<?= $item['attrList']['id'] ?>_target"
					class="text"
				>
        			<?= $item['value'] ?>
        		</span>
			</span>
		<?php
	}
	?>
</div>

<script>
	BX.ready(function ()
	{
		new BX.Mobile.Field.String(
			<?= Json::encode([
				'name' => 'BX.Mobile.Field.String',
				'nodes' => $nodes,
				'restrictedMode' => true,
				'formId' => $arParams['additionalParameters']['formId'],
				'gridId' => $arParams['additionalParameters']['gridId']
			]) ?>
		);
	});
</script>