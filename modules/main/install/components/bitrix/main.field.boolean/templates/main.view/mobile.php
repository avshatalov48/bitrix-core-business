<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arResult
 */

$nodes = [$arResult['userField']['~id']];
?>
<label for="<?= $arResult['userField']['~id'] ?>">
	<input
		type="checkbox"
		id="<?= $arResult['userField']['~id'] ?>"
		name="<?= $arResult['fieldName'] ?>"
		value="Y"
		<?= ($arResult['userField']['VALUE'] ? 'checked="checked"' : '') ?>
	>
	<span><?= $arResult['userField']['EDIT_FORM_LABEL'] ?></span>
</label>

<script>
	BX.ready(function ()
	{
		new BX.Mobile.Field.Boolean(
			<?=CUtil::PhpToJSObject([
				'name' => 'BX.Mobile.Field.Boolean',
				'nodes' => $nodes,
				'restrictedMode' => true,
				'formId' => $arParams['additionalParameters']['formId'],
				'gridId' => $arParams['additionalParameters']['gridId']
			])?>
		);
	});
</script>