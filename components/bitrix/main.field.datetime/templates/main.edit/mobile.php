<?php

use Bitrix\Main\Web\Json;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var DateTimeUfComponent $component
 * @var array $arResult
 */
$component = $this->getComponent();

$nodes = [];
foreach($arResult['value'] as $item)
{
	$nodes[] = $item['attrList']['id'];
	?>
	<span
		class="mobile-grid-data-span
		<?= ($arResult['userField']['MULTIPLE'] === 'Y' ? ' multiple' : '') ?>"
	>
		<div
			name="<?= $item['attrList']['name'] ?>"
			class="mobile-grid-datetime"
		>
  		<input
			<?= $component->getHtmlBuilder()->buildTagAttributes($item['attrList']) ?>
		>
    	<div
			placeholder="<?= $item['attrList']['placeholder'] ?>"
			id="<?= $item['attrList']['id'] ?>_container"
		>
			<?= ($item['value'] ?: $item['attrList']['placeholder']) ?>
		</div>
    	<?php
			if($arParams['additionalParameters']['canDrop'] !== false)
			{
				?>
				<del
					id="<?= $item['attrList']['id'] ?>_del"
					<?= ($item['value'] ? '' : ' style="display:none"') ?>
				>
				</del>
				<?php
			}
			?>
		</div>
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
?>

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