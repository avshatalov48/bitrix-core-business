<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load([
	'ui.alerts',
	'ui.tooltip',
	'ui.icons',
	'ui.notification',
	'ui.tour',
	'ui.banner-dispatcher',
	'main.core',
	'catalog.document-grid',
	'catalog.store-enable-wizard',
]);

global $APPLICATION;

$title = Loc::getMessage('CATALOG_DOCUMENT_LIST_TITLE_' . mb_strtoupper($arResult['MODE']));
$APPLICATION->SetTitle($title);

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
	if (is_array($arResult['ERROR_MESSAGES'][0]))
	{
		$APPLICATION->IncludeComponent(
			'bitrix:ui.info.error',
			'',
			$arResult['ERROR_MESSAGES'][0]
		);
	}
	else
	{
		$APPLICATION->IncludeComponent(
			'bitrix:ui.info.error',
			'',
			[
				'TITLE' => $arResult['ERROR_MESSAGES'][0],
			],
		);
	}

	return;
}

$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	$arResult['GRID']
);

if ($arResult['OPEN_INVENTORY_MANAGEMENT_SLIDER'])
{
	?>
	<script>
		var currentSlider = BX.SidePanel.Instance.getTopSlider();
		if (!currentSlider || !currentSlider.data.get('preventMasterSlider'))
		{
			(new BX.Catalog.Store.EnableWizardOpener()).open(
				"<?= $arResult['MASTER_SLIDER_URL'] ?>",
				{
					urlParams: {
						<?php if ($arResult['OPEN_INVENTORY_MANAGEMENT_SLIDER_IN_B24_MODE']): ?>
							initEnableMode: BX.Catalog.Store.ModeList.MODE_B24,
							analyticsContextSection: BX.Catalog.Store.AnalyticsContextList.LANDING,
						<?php else:?>
							analyticsContextSection: BX.Catalog.Store.AnalyticsContextList.SECTION,
						<?php endif; ?>
					},
					cacheable: false,
					data: {
						openGridOnDone: false,
					},
					events: {
						onCloseComplete: function(event) {
							let slider = event.getSlider();
							if (!slider)
							{
								return;
							}

							if (slider.getData().get('isInventoryManagementEnabled'))
							{
								BX.Catalog.Store.EnableWizardOpener.saveEnabledFlag();

								if (slider.getData().get('inventoryManagementMode') === BX.Catalog.Store.ModeList.MODE_1C)
								{
									top.document.location = '/crm/';
								}
								else
								{
									document.location.reload();
								}
							}

							return Promise.resolve();
						},
					}
				}
			);
		}
	</script>
	<?php
}
?>

<script>
	function reloadGrid()
	{
		try
		{
			var grid = BX.Main.gridManager.getInstanceById('<?= CUtil::JSEscape($arResult['GRID']['GRID_ID']) ?>');
			if (grid)
			{
				grid.reload();
			}
		}
		catch (e)
		{
		}
	}

	function openInventoryMarketplaceSlider()
	{
		var url = '/marketplace/?tag[0]=migrator&tag[1]=inventory'
		var rule = BX.SidePanel.Instance.getUrlRule(url);
		var options = (rule && BX.type.isPlainObject(rule.options)) ? rule.options : {};
		options["cacheable"] = false;
		options["allowChangeHistory"] = false;
		options["requestMethod"] = "post";
		options["requestParams"] = { sessid: BX.bitrix_sessid() };
		BX.SidePanel.Instance.open(url, options);
	}

	function showAddDocumentGuide()
	{
		const buttonUID = <?= CUtil::PhpToJSObject($arResult['ADD_DOCUMENT_BTN_ID']) ?>;
		const addDocumentButton = BX.UI.ButtonManager.getByUniqid(buttonUID);

		BX.Runtime.loadExtension('ui.tour').then((exports) => {
			const { Guide } = exports;
			const guide = new Guide({
				steps: [
					{
						target: addDocumentButton.getContainer(),
						title: <?= CUtil::PhpToJSObject(Loc::getMessage('DOC_CREATE_FIRST_TIME_GUIDE_TEXT')) ?>,
						text: '',
						events: {
							onClose: () => {
								BX.userOptions.save('catalog', 'document-list', 'isDocumentCreateGuideOver', true);
							},
						}
					},
				],
				onEvents: true,
			});
			BX.UI.BannerDispatcher.high.toQueue(() => {
				guide.showNextStep();
			});
		});
	}

	function showProductBatchMethodPopup()
	{
		BX.Runtime.loadExtension('ui.dialogs.messagebox').then((exports) => {
			const { MessageBox } = exports;

			MessageBox.confirm(
				BX.Tag.render`
					<div>
						<p>${<?= CUtil::PhpToJSObject(Loc::getMessage('BATCH_METHOD_POPUP_TEXT_1_MSGVER_1')) ?>}</p>
						<p>
							${<?= CUtil::PhpToJSObject(Loc::getMessage(
								'BATCH_METHOD_POPUP_TEXT_2_MSGVER_1',
								[
									'[link]' => '<a href="#" onclick="openHelpdesk()">',
									'[/link]' => '</a>',
								],
							)) ?>}
						</p>
					</div>
				`,
				<?= CUtil::PhpToJSObject(Loc::getMessage('BATCH_METHOD_POPUP_TITLE')) ?>,
				(messageBox) => {
					BX.Runtime.loadExtension('catalog.config.settings').then((exports) => {
						const { Slider } = exports;
						Slider.open(<?= CUtil::PhpToJSObject($arResult['INVENTORY_MANAGEMENT_SOURCE']) ?>);
					});
					messageBox.close();
					BX.userOptions.save('catalog', 'document-list', 'was_batch_method_popup_shown', 'Y');
				},
				<?= CUtil::PhpToJSObject(Loc::getMessage('BATCH_METHOD_POPUP_GO_TO_SETTINGS')) ?>,
				(messageBox) => {
					messageBox.close();
					BX.userOptions.save('catalog', 'document-list', 'was_batch_method_popup_shown', 'Y');
				},
				<?= CUtil::PhpToJSObject(Loc::getMessage('BATCH_METHOD_POPUP_LATER')) ?>,
			);
		});
	}

	function openHelpdesk(event)
	{
		if (top.BX.Helper)
		{
			top.BX.Helper.show("redirect=detail&code=17858278");
		}
	}

	function resetAddDocumentButton()
	{
		const addDocumentButton = document.querySelector('.add-document-button');

		if (!addDocumentButton)
		{
			return;
		}

		let link = addDocumentButton.getAttribute('href');
		const uri = new BX.Uri(link);
		if (uri.getQueryParam('DOCUMENT_TYPE') === 'S')
		{
			uri.removeQueryParam(['firstTime']);
			uri.setQueryParam('DOCUMENT_TYPE', 'A');
			addDocumentButton.href = uri.toString();
		}
	}

	BX.ready(function() {
		const isShowGuide = <?= CUtil::PhpToJSObject($arResult['IS_SHOW_GUIDE']) ?>;
		if (isShowGuide)
		{
			showAddDocumentGuide();
		}

		const isShowProductBatchMethodPopup = <?= CUtil::PhpToJSObject($arResult['IS_SHOW_PRODUCT_BATCH_METHOD_POPUP']) ?>;
		if (isShowProductBatchMethodPopup)
		{
			showProductBatchMethodPopup();
		}

		BX.Catalog.DocumentGridManager.Instance = new BX.Catalog.DocumentGridManager({
			gridId: '<?= $arResult['GRID']['GRID_ID'] ?>',
			filterId: '<?= $arResult['FILTER_ID'] ?>',
			isConductDisabled: <?= $arResult['OPEN_INVENTORY_MANAGEMENT_SLIDER_ON_ACTION'] ? 'true' : 'false' ?>,
			masterSliderUrl: <?= CUtil::PhpToJSObject($arResult['MASTER_SLIDER_URL']) ?>,
			inventoryManagementSource: <?= CUtil::PhpToJSObject($arResult['INVENTORY_MANAGEMENT_SOURCE']) ?>,
			isInventoryManagementDisabled: <?= CUtil::PhpToJSObject($arResult['IS_INVENTORY_MANAGEMENT_DISABLED']) ?>,
			inventoryManagementFeatureCode: <?= CUtil::PhpToJSObject($arResult['INVENTORY_MANAGEMENT_FEATURE_SLIDER_CODE']) ?>,
		});
	});

	BX.addCustomEvent('DocumentCard:onDocumentCardSave', function(event) {
		reloadGrid();
	});

	BX.addCustomEvent('DocumentCard:onEntityCreate', function(event) {
		resetAddDocumentButton();
	});

	BX.addCustomEvent('SidePanel.Slider:onMessage', function (event) {
		var reloadActions = ['save_document', 'cancellation', 'save_and_conduct'];
		if (reloadActions.includes(event.eventId))
		{
			var slider = event.getSender();
			if (slider)
			{
				// I'm very sorry but I had to do it
				var reloadUrl = BX.prop.getString(BX.prop.getObject(event.data, 'formParams', {}), 'reloadUrl', '');
				if (reloadUrl)
				{
					slider.url = reloadUrl;
					slider.setFrameSrc();
				}
				if (event.eventId === 'save_and_conduct')
				{
					var notifyParams = {
						content: <?= CUtil::PhpToJSObject(Loc::getMessage('DOCUMENT_CONDUCT_SUCCESSFUL')) ?>,
					};
					var documentUrl = BX.prop.getString(BX.prop.getObject(event.data, 'formParams', {}), 'documentUrl', '');
					if (documentUrl)
					{
						notifyParams.actions = [
							{
								title: <?= CUtil::PhpToJSObject(Loc::getMessage('DOCUMENT_CONDUCT_SUCCESSFUL_VIEW')) ?>,
								href: documentUrl,
								events: {
									click: function(event, balloon, action) {
										balloon.close();
									}
								}
							}
						];
					}
					window.top.BX.UI.Notification.Center.notify(notifyParams);
					slider.close(true);
				}
			}

			reloadGrid();
		}
	});

</script>

<?php
\Bitrix\Catalog\Store\EnableWizard\Manager::showEnabledJsNotificationIfNeeded();
?>

