<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

global $APPLICATION;

$APPLICATION->SetTitle(Loc::getMessage('CATALOG_STORE_LIST_TITLE'));

$this->setViewTarget('above_pagetitle');
$APPLICATION->IncludeComponent(
	'bitrix:catalog.store.document.control_panel',
	'',
	[
		'PATH_TO' => $arResult['PATH_TO'],
	]
);
$this->endViewTarget();

if (!empty($arResult['ERROR_MESSAGES']) && is_array($arResult['ERROR_MESSAGES']))
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.info.error',
		'',
		[
			'TITLE' => $arResult['ERROR_MESSAGES'][0],
			'DESCRIPTION' => Loc::getMessage('CATALOG_STORE_ADMIN_LIST_ACCESS_DENIED_DESCRIPTION'),
		]
	);

	return;
}

$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	$arResult['GRID']
);
?>

<script>
	function openStoreCreation(event)
	{
		openStoreSlider();
	}

	function openStoreSlider(id = 0)
	{
		var url = '/shop/documents-stores/details/'+parseInt(id)+'/';

		BX.SidePanel.Instance.open(
			url,
			{
				allowChangeHistory: true,
				cacheable: false,
				width: 500,
				events: {
					onClose: function(event)
					{
						var grid = BX.Main.gridManager.getInstanceById('<?= CUtil::JSEscape($arResult['GRID']['GRID_ID']) ?>');
						if(grid)
						{
							grid.reload();
						}
					}
				}
			}
		);
	}

	function openTariffHelp()
	{
		var tariff = '<?= CUtil::JSEscape($arResult['TARIFF_HELP_LINK']['FEATURE_CODE'] ?? '') ?>';
		if (tariff !== '')
		{
			BX.UI.InfoHelper.show(tariff);
		}
	}
</script>
