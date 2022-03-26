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

if (!empty($arResult['ERROR_MESSAGES']) && is_array($arResult['ERROR_MESSAGES'])): ?>
	<?php foreach($arResult['ERROR_MESSAGES'] as $error):?>
		<div class="ui-alert ui-alert-danger" style="margin-bottom: 0px;">
			<span class="ui-alert-message"><?= htmlspecialcharsbx($error) ?></span>
		</div>
	<?php endforeach;?>
	<?php
	return;
endif;

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
		var url = '/shop/settings/cat_store_edit/?publicSidePanel=Y&IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER';

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
						var grid = BX.Main.gridManager.getInstanceById('catalog_store');
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
		var tariff = '<?php echo (!empty($arResult['TARIFF_HELP_LINK']['FEATURE_CODE'])
			? CUtil::JSEscape($arResult['TARIFF_HELP_LINK']['FEATURE_CODE'])
			: '' ); ?>';
		if (tariff !== '')
		{
			BX.UI.InfoHelper.show(tariff);
		}
	}
</script>
