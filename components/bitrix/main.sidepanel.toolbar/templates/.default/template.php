<?php

use Bitrix\Main\Web\Json;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var CBitrixComponentTemplate $this
 * @var array $arResult
 */
$frame = $this->createFrame()->begin('');
?>

<script>
	(function() {
		const toolbar = BX.SidePanel.Instance.createToolbar(
			<?= Json::encode($arResult['options']) ?>
		);

		if (toolbar.getItems().length > 0)
		{
			toolbar.show();
			if (!toolbar.canShowOnTop())
			{
				toolbar.mute();
			}
		}
	})();
</script>

<?
$frame->end();
