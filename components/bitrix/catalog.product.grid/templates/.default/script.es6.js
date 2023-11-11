BX.namespace('BX.Catalog');

BX.Catalog.productGridInit = function(grid) {
	var dialogInstance;

	grid.showChangePriceDialog = function() {
		if (!dialogInstance)
		{
			dialogInstance = new top.BX.CAdminDialog({
				content_url: `/bitrix/tools/catalog/iblock_catalog_change_price.php?bxpublic=Y`,
				content_post: `sessid=${BX.bitrix_sessid()}&sTableID=${this.containerId}`,
				width: 800,
				height: 415,
				resizable: false,
				buttons: [
					{
						title: top.BX.message('JS_CORE_WINDOW_SAVE'),
						id: 'savebtn',
						name: 'savebtn',
						className:
							top.BX.browser.IsIE() && top.BX.browser.IsDoctype() && !top.BX.browser.IsIE10()
								? ''
								: 'adm-btn-save'
					},
					top.BX.CAdminDialog.btnCancel
				]
			});
		}

		dialogInstance.Show();
	}

	grid.sendActionWithConfirm = function(action, data, confirmMessage)
	{
		BX.UI.Dialogs.MessageBox.confirm(
			confirmMessage,
			(messageBox) => {
				grid.sendRowAction(action, data);
				messageBox.close();
			},
			BX.Loc.getMessage('UI_MESSAGE_BOX_YES_CAPTION')
		);
	}

	return grid;
}
