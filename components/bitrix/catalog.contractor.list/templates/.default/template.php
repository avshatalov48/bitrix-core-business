<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

global $APPLICATION;

$APPLICATION->SetTitle(Loc::getMessage('CATALOG_CONTRACTOR_LIST_TITLE'));

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
	function openContractorCreation(event)
	{
		openContractorSlider();
	}

	function openContractorSlider(id = 0)
	{
		var url = '/shop/settings/cat_contractor_edit/?publicSidePanel=Y&IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER';

		if (id && parseInt(id))
		{
			url += '&ID=' + id;
		}

		BX.SidePanel.Instance.open(
			url,
			{
				allowChangeHistory: false,
				events: {
					onDestroy: function(event)
					{
						var grid = BX.Main.gridManager.getInstanceById('catalog_contractor');
						if(grid)
						{
							grid.reload();
						}
					}
				}
			}
		);
	}
</script>
