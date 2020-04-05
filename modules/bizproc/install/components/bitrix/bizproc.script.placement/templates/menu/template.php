<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 */
\CJSCore::init("sidepanel");
?>
<script>
	BX.ready(function()
	{

		BX.SidePanel.Instance.bindAnchors({
			rules:
				[
					{
						condition: [
							"/bizproc/script/",
						]
					}
				]
		});

		BX.Bizproc.ScriptPlacementMenu.scriptList = <?=\Bitrix\Main\Web\Json::encode(
				\Bitrix\Bizproc\Automation\Script\Manager::getListByPlacement($arParams['PLACEMENT'])
		)?>
	});
</script>