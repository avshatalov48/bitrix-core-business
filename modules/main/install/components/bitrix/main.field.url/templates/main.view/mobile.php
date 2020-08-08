<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var StringUfComponent $component
 * @var array $arResult
 */

$component = $this->getComponent();

$isFirst = true;
?>

<div class="fields url field-wrap">
	<?php
	$nodes = [];
	foreach($arResult['value'] as $item)
	{
		$nodes[] = $item['attrList']['id'];
		?>
		<div class="field-item">
			<input
				type="hidden"
				value="<?= $item['attrList']['value'] ?>"
				name="<?= $item['attrList']['name'] ?>"
				placeholder="<?= $item['attrList']['placeholder'] ?>"
			>
			<a
				class="<?= $item['attrList']['class'] ?>"
				id="<?= $item['attrList']['id'] ?>"
				target="_blank"
				href="<?= $item['attrList']['href'] ?>"
				data-bx-type="text"
			>
				<?= $item['value'] ?>
			</a>
		</div>
		<?php
	}
	?>
</div>


<script>
	BX.ready(function ()
	{
		new BX.Mobile.Field.Url(
			<?=CUtil::PhpToJSObject([
				'name' => 'BX.Mobile.Field.Url',
				'nodes' => $nodes,
				'restrictedMode' => true,
				'formId' => $arParams['additionalParameters']['formId'],
				'gridId' => $arParams['additionalParameters']['gridId']
			])?>
		);
	});
</script>