<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!empty($arResult['ERROR_MESSAGES']) && is_array($arResult['ERROR_MESSAGES'])): ?>
	<?php foreach($arResult['ERROR_MESSAGES'] as $error):?>
		<div class="ui-alert ui-alert-danger" style="margin-bottom: 0px;">
			<span class="ui-alert-message"><?= htmlspecialcharsbx($error) ?></span>
		</div>
	<?php endforeach;?>
	<?php
	return;
endif;

global $APPLICATION;
$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	$arResult['GRID']
);
?>

<script>
	function onBeforeDialogSearch(event)
	{
		const dialog = event.target;
		if (dialog)
		{
			dialog.removeEntityItems('product_variation');
		}
	}
</script>
