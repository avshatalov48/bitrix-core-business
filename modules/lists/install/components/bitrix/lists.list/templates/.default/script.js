BX.namespace("BX.Lists");
BX.Lists.ListClass = (function ()
{
	var ListClass = function (parameters)
	{
		this.iblockTypeId = parameters.iblockTypeId;
		this.iblockId = parameters.iblockId;
		this.sectionId = parameters.sectionId;
		this.randomString = parameters.randomString;
		this.socnetGroupId = parameters.socnetGroupId;
		this.listAction = parameters.listAction;
		this.listActionAdd = parameters.listActionAdd;
		this.gridId = parameters.gridId;

		this.init();
	};

	ListClass.prototype.init = function ()
	{
		this.ajaxUrl = '/bitrix/components/bitrix/lists.list/ajax.php';
		this.actionButton = BX('lists-title-action');
		this.addButton = BX('lists-title-action-add');
		this.selectAddButton = BX('lists-title-action-select-add');

		this.addPopupItems = [];
		this.addPopupObject = null;
		this.addPopupId = 'lists-title-add';

		this.actionPopupItems = [];
		this.actionPopupObject = null;
		this.actionPopupId = 'lists-title-action';
		this.actionItemChanges = false;

		BX.bind(this.actionButton, 'click', BX.delegate(this.showListAction, this));
		BX.bind(this.selectAddButton, 'click', BX.delegate(this.showListAdd, this));
	};

	ListClass.prototype.showListAction = function ()
	{
		if(!this.actionPopupItems.length)
		{
			for(var k = 0; k < this.listAction.length; k++)
			{
				var popupItems = {
					id: this.listAction[k].hasOwnProperty('id') ? this.listAction[k].id : '',
					text : this.listAction[k].text,
					onclick : this.listAction[k].action
				};
				if(this.listAction[k].hasOwnProperty('items'))
				{
					popupItems.items = [];
					for(var i = 0; i < this.listAction[k].items.length; i++)
					{
						popupItems.items.push({
							text: this.listAction[k].items[i].text,
							onclick: this.listAction[k].items[i].action
						});
					}
				}
				this.actionPopupItems.push(popupItems);
			}
		}
		if(this.actionItemChanges)
		{
			if(this.actionPopupObject)
			{
				this.actionPopupObject.popupWindow.destroy();
				BX.PopupMenu.destroy(this.actionPopupId);
			}
		}
		if(!BX.PopupMenu.getMenuById(this.actionPopupId))
		{
			var buttonRect = this.actionButton.getBoundingClientRect();
			this.actionPopupObject = BX.PopupMenu.create(
				this.actionPopupId,
				this.actionButton,
				this.actionPopupItems,
				{
					closeByEsc : true,
					angle: true,
					offsetLeft: buttonRect.width/2,
					events: {
						onPopupShow: BX.proxy(function () {
							BX.addClass(this.actionButton, 'webform-button-active');
						}, this),
						onPopupClose: BX.proxy(function () {
							BX.removeClass(this.actionButton, 'webform-button-active');

						}, this)
					}
				}
			);
			this.actionItemChanges = false;
		}
		if(this.actionPopupObject) this.actionPopupObject.popupWindow.show();
	};

	ListClass.prototype.showListAdd = function ()
	{
		if(!this.addPopupItems.length)
		{
			for(var k = 0; k < this.listActionAdd.length; k++)
			{
				this.addPopupItems.push({
					text : this.listActionAdd[k].text,
					onclick : this.listActionAdd[k].action
				});
			}
		}
		if(!BX.PopupMenu.getMenuById(this.addPopupId))
		{
			var buttonRect = this.addButton.getBoundingClientRect();
			this.addPopupObject = BX.PopupMenu.create(
				this.addPopupId,
				this.addButton,
				this.addPopupItems,
				{
					closeByEsc : true,
					angle: true,
					offsetLeft: buttonRect.width/2
				}
			);
		}
		if(this.addPopupObject) this.addPopupObject.popupWindow.show();
	};

	ListClass.prototype.addSection = function ()
	{
		BX.Lists.modalWindow({
			modalId: 'bx-lists-add-section',
			title: BX.message('CT_BLL_ADD_SECTION_POPUP_TITLE'),
			draggable: true,
			contentClassName: '',
			contentStyle: {
				width: '400px',
				padding: '25px 25px 50px 25px'
			},
			events: {
				onPopupClose : function() {
					this.destroy();
				}
			},
			content: [
				BX.create('span', {
					props: {
						id: 'lists-popup-error',
						className: 'bx-lists-popup-error'
					}
				}),
				BX.create('label', {
					props: {
						className: 'bx-lists-popup-label',
						"for": 'lists-section-name-input'
					},
					children: [
						BX.create('span', {
							props: {
								className: 'req'
							},
							text: '*'
						}),
						BX.message('CT_BLL_ADD_SECTION_POPUP_INPUT_NAME')
					]
				}),
				BX.create('input', {
					props: {
						id: 'lists-section-name-input',
						className: 'bx-lists-popup-input',
						type: 'text',
						value: ''
					},
					style: {
						fontSize: '16px',
						marginTop: '10px'
					}
				})
			],
			buttons: [
				BX.create('span', {
					text : BX.message("CT_BLL_ADD_SECTION_POPUP_BUTTON_ADD"),
					props: {
						className: 'webform-small-button webform-small-button-accept'
					},
					events : {
						click : BX.delegate(function() {
							if(!BX('lists-section-name-input').value)
							{
								BX('lists-popup-error').innerHTML = BX.message("CT_BLL_ADD_SECTION_POPUP_ERROR_NAME");
								BX.show(BX('lists-popup-error'));
								return false;
							}
							BX.hide(BX('lists-popup-error'));
							BX.Lists.ajax({
								method: 'POST',
								dataType: 'json',
								url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'addSection'),
								data: {
									iblockTypeId: this.iblockTypeId,
									iblockId: this.iblockId,
									sectionId: this.sectionId,
									sectionName: BX('lists-section-name-input').value,
									socnetGroupId: this.socnetGroupId
								},
								onsuccess: BX.delegate(function(result) {
									if(result.status == 'success')
									{
										BX.PopupWindowManager.getCurrentPopup().close();
										var reloadParams = {}, gridObject;
										gridObject = BX.Main.gridManager.getById(this.gridId);
										if(gridObject.hasOwnProperty('instance'))
											gridObject.instance.reloadTable('POST', reloadParams);
									}
									else
									{
										result.errors = result.errors || [{}];
										BX.Lists.showModalWithStatusAction({
											status: 'error',
											message: result.errors.pop().message
										});
									}
								}, this)
							});
						}, this)
					}
				}),
				BX.create('span', {
					text : BX.message("CT_BLL_ADD_SECTION_POPUP_BUTTON_CLOSE"),
					props: {
						className: 'webform-small-button webform-button-cancel'
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

	ListClass.prototype.performActionBp = function (workflowId, elementId, action)
	{
		BX.Lists.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'performActionBp'),
			data: {
				iblockTypeId: this.iblockTypeId,
				iblockId: this.iblockId,
				sectionId: this.sectionId,
				workflowId: workflowId,
				elementId: elementId,
				action: action,
				socnetGroupId: this.socnetGroupId,
				sessid: BX.bitrix_sessid()
			},
			onsuccess: BX.delegate(function (result)
			{
				if(result.status == 'success')
				{
					BX.Lists.showModalWithStatusAction({
						status: 'success',
						message: result.message
					});
					setTimeout('location.reload()', 1000)
				}
				else
				{
					result.errors = result.errors || [{}];
					BX.Lists.showModalWithStatusAction({
						status: 'error',
						message: result.errors.pop().message
					});
				}
			}, this)
		});
	};

	ListClass.prototype.editSection = function (currentSectionId)
	{
		if(!currentSectionId) return false;

		BX.Lists.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'getSection'),
			data: {
				iblockTypeId: this.iblockTypeId,
				iblockId: this.iblockId,
				sectionId: this.sectionId,
				socnetGroupId: this.socnetGroupId,
				currentSectionId: currentSectionId
			},
			onsuccess: BX.delegate(function(result) {
				if(result.status == 'success')
				{
					var sectionData = result.data;
					BX.Lists.modalWindow({
						modalId: 'bx-lists-add-section',
						title: BX.message('CT_BLL_EDIT_SECTION_POPUP_TITLE'),
						draggable: true,
						contentClassName: '',
						contentStyle: {
							width: '400px',
							padding: '25px 25px 50px 25px'
						},
						events: {
							onPopupClose : function() {
								this.destroy();
							}
						},
						content: [
							BX.create('span', {
								props: {
									id: 'lists-popup-error',
									className: 'bx-lists-popup-error'
								}
							}),
							BX.create('label', {
								props: {
									className: 'bx-lists-popup-label',
									"for": 'lists-section-name-input'
								},
								children: [
									BX.create('span', {
										props: {
											className: 'req'
										},
										text: '*'
									}),
									BX.message('CT_BLL_ADD_SECTION_POPUP_INPUT_NAME')
								]
							}),
							BX.create('input', {
								props: {
									id: 'lists-section-name-input',
									className: 'bx-lists-popup-input',
									type: 'text',
									value: sectionData.NAME
								},
								style: {
									fontSize: '16px',
									marginTop: '10px'
								}
							})
						],
						buttons: [
							BX.create('span', {
								text : BX.message("CT_BLL_ADD_SECTION_POPUP_BUTTON_EDIT"),
								props: {
									className: 'webform-small-button webform-small-button-accept'
								},
								events : {
									click : BX.delegate(function() {
										if(!BX('lists-section-name-input').value)
										{
											BX('lists-popup-error').innerHTML =
												BX.message("CT_BLL_ADD_SECTION_POPUP_ERROR_NAME");
											BX.show(BX('lists-popup-error'));
											return false;
										}
										BX.hide(BX('lists-popup-error'));
										BX.Lists.ajax({
											method: 'POST',
											dataType: 'json',
											url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'editSection'),
											data: {
												iblockTypeId: this.iblockTypeId,
												iblockId: this.iblockId,
												sectionId: this.sectionId,
												sectionName: BX('lists-section-name-input').value,
												socnetGroupId: this.socnetGroupId,
												currentSectionId: currentSectionId
											},
											onsuccess: BX.delegate(function(result) {
												if(result.status == 'success')
												{
													BX.PopupWindowManager.getCurrentPopup().close();
													var reloadParams = {}, gridObject;
													gridObject = BX.Main.gridManager.getById(this.gridId);
													if(gridObject.hasOwnProperty('instance'))
														gridObject.instance.reloadTable('POST', reloadParams);
												}
												else
												{
													result.errors = result.errors || [{}];
													BX.Lists.showModalWithStatusAction({
														status: 'error',
														message: result.errors.pop().message
													});
												}
											}, this)
										});
									}, this)
								}
							}),
							BX.create('span', {
								text : BX.message("CT_BLL_ADD_SECTION_POPUP_BUTTON_CLOSE"),
								props: {
									className: 'webform-small-button webform-button-cancel'
								},
								events : {
									click : BX.delegate(function() {
										BX.PopupWindowManager.getCurrentPopup().close();
									}, this)
								}
							})
						]
					});
				}
				else
				{
					result.errors = result.errors || [{}];
					BX.Lists.showModalWithStatusAction({
						status: 'error',
						message: result.errors.pop().message
					});
				}
			}, this)
		});
	};

	ListClass.prototype.deleteSection = function (gridId, sectionId)
	{
		BX.Lists.modalWindow({
			modalId: 'bx-lists-migrate-list',
			title: BX.message('CT_BLL_DELETE_POPUP_TITLE'),
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
			content: BX.message('CT_BLL_TOOLBAR_SECTION_DELETE_WARNING'),
			buttons: [
				BX.create('span', {
					text : BX.message("CT_BLL_DELETE_POPUP_ACCEPT_BUTTON"),
					props: {
						className: 'webform-small-button webform-small-button-accept'
					},
					events : {
						click : BX.delegate(function() {
							BX.Lists.ajax({
								method: 'POST',
								dataType: 'json',
								url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'deleteSection'),
								data: {
									iblockTypeId: this.iblockTypeId,
									iblockId: this.iblockId,
									sectionId: this.sectionId,
									socnetGroupId: this.socnetGroupId,
									sectionIdForDelete: sectionId
								},
								onsuccess: BX.delegate(function(result) {
									if(result.status == 'success')
									{
										BX.Lists.showModalWithStatusAction({
											status: 'success',
											message: result.message
										});
										var reloadParams = {}, gridObject;
										gridObject = BX.Main.gridManager.getById(gridId);
										if(gridObject.hasOwnProperty('instance'))
										{
											gridObject.instance.reloadTable('POST', reloadParams);
											var rowObject = gridObject.instance.getRows().getById(sectionId);
											if(rowObject) rowObject.closeActionsMenu();
										}
									}
									else
									{
										result.errors = result.errors || [{}];
										BX.Lists.showModalWithStatusAction({
											status: 'error',
											message: result.errors.pop().message
										});
									}
								}, this)
							});
							BX.PopupWindowManager.getCurrentPopup().close();
						}, this)
					}
				}),
				BX.create('span', {
					text : BX.message("CT_BLL_DELETE_POPUP_CANCEL_BUTTON"),
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

	ListClass.prototype.deleteElement = function (gridId, elementId)
	{
		BX.Lists.modalWindow({
			modalId: 'bx-lists-migrate-list',
			title: BX.message('CT_BLL_DELETE_POPUP_TITLE'),
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
			content: BX.message('CT_BLL_TOOLBAR_ELEMENT_DELETE_WARNING'),
			buttons: [
				BX.create('span', {
					text : BX.message("CT_BLL_DELETE_POPUP_ACCEPT_BUTTON"),
					props: {
						className: 'webform-small-button webform-small-button-accept'
					},
					events : {
						click : BX.delegate(function() {
							var reloadParams = {}, gridObject;
							reloadParams['action_button_'+gridId] = 'delete';
							reloadParams['ID'] = [elementId];

							gridObject = BX.Main.gridManager.getById(gridId);
							if(gridObject.hasOwnProperty('instance'))
							{
								gridObject.instance.reloadTable('POST', reloadParams);
								var rowObject = gridObject.instance.getRows().getById(elementId);
								if(rowObject) rowObject.closeActionsMenu();
							}
							BX.PopupWindowManager.getCurrentPopup().close();
						}, this)
					}
				}),
				BX.create('span', {
					text : BX.message("CT_BLL_DELETE_POPUP_CANCEL_BUTTON"),
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

	ListClass.prototype.toogleSectionGrid = function()
	{
		BX.Lists.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'toogleSectionGrid'),
			data: {
				gridId: this.gridId
			},
			onsuccess: BX.delegate(function(result) {
				if(result.status == 'success')
				{
					var text = BX.message('CT_BLL_SHOW_SECTION_GRID');
					if(result.currentValue == 'Y')
					{
						text = BX.message('CT_BLL_HIDE_SECTION_GRID');
					}
					for(var k = 0; k < this.actionPopupItems.length; k++)
					{
						if(this.actionPopupItems[k].hasOwnProperty('id') &&
							this.actionPopupItems[k].id == 'showSectionGrid')
						{
							this.actionPopupItems[k].text = text;
							this.actionItemChanges = true;
						}
					}
					if(this.actionPopupObject) this.actionPopupObject.popupWindow.close();
					if(BX.Main.gridManager.getById(this.gridId))
					{
						BX.Main.gridManager.getById(this.gridId).instance.reload();
					}
				}
				else
				{
					result.errors = result.errors || [{}];
					BX.Lists.showModalWithStatusAction({
						status: 'error',
						message: result.errors.pop().message
					});
				}
			}, this)
		});
	};

	return ListClass;

})();
