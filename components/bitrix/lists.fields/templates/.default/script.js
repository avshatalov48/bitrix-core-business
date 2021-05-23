BX.namespace("BX.Lists");
BX.Lists.FieldsClass = (function ()
{
	var FieldsClass = function (parameters)
	{
		this.ajaxUrl = '/bitrix/components/bitrix/lists.fields/ajax.php';
		this.iblockTypeId = parameters.iblockTypeId;
		this.iblockId = parameters.iblockId;
		this.randomString = parameters.randomString;
		this.socnetGroupId = parameters.socnetGroupId;
	};

	FieldsClass.prototype.deleteRow = function (gridId, rowId)
	{
		BX.Lists.modalWindow({
			modalId: 'bx-lists-migrate-list',
			title: BX.message('CT_BLF_DELETE_POPUP_TITLE'),
			draggable: true,
			contentClassName: '',
			contentStyle: {
				width: '400px',
				padding: '20px 20px 20px 20px'
			},
			events: {
				onPopupClose : function() {
					this.destroy();
				}
			},
			content: BX.message("CT_BLF_TOOLBAR_ELEMENT_DELETE_WARNING"),
			buttons: [
				BX.create('span', {
					text : BX.message("CT_BLF_DELETE_POPUP_ACCEPT_BUTTON"),
					props: {
						className: 'webform-small-button webform-small-button-accept'
					},
					events : {
						click : BX.delegate(function() {
							var reloadParams = {}, gridObject;
							reloadParams['action_button_'+gridId] = 'delete';
							reloadParams['ID'] = [rowId];

							gridObject = BX.Main.gridManager.getById(gridId);
							if(gridObject.hasOwnProperty('instance'))
							{
								gridObject.instance.reloadTable('POST', reloadParams);
								var rowObject = gridObject.instance.getRows().getById(rowId);
								if(rowObject) rowObject.closeActionsMenu();
							}
							BX.PopupWindowManager.getCurrentPopup().close();
						}, this)
					}
				}),
				BX.create('span', {
					text : BX.message("CT_BLF_DELETE_POPUP_CANCEL_BUTTON"),
					props: {
						className: 'popup-window-button popup-window-button-link popup-window-button-link-cancel'
					},
					events : {
						click : BX.delegate(function() {
							BX.PopupWindowManager.getCurrentPopup().close();
						}, this)
					}
				})
			]
		});
	};

	return FieldsClass;

})();
