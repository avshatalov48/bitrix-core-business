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
		this.filterId = parameters.filterId;

		this.init();
	};

	ListClass.prototype.init = function ()
	{
		this.ajaxUrl = '/bitrix/components/bitrix/lists.list/ajax.php';
	};

	ListClass.prototype.getTotalCount = function ()
	{
		this.showCountLoader(BX('lists-list-row-count-wrapper'));

		BX.ajax({
			url: window.location.href,
			method: 'POST',
			dataType: 'json',
			data: {
				'action': 'getTotalCount',
			},
			onsuccess: BX.proxy(function(response) {
				this.hideCountLoader(BX('lists-list-row-count-wrapper'));
				this.appendCount(BX('lists-list-row-count-wrapper'), parseInt(response, 10));
			}, this)
		});
	};

	ListClass.prototype.showCountLoader = function(container)
	{
		container.querySelector('a').style.display = 'none';
		container.querySelector('.lists-circle-loader-circular').style.display = 'inline';
	};

	ListClass.prototype.hideCountLoader = function(container)
	{
		container.querySelector('.lists-circle-loader-circular').style.display = 'none';
	};

	ListClass.prototype.appendCount = function(container, count)
	{
		container.querySelector('a').remove();
		container.append(count);
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

	ListClass.prototype.unLock = function (elementId)
	{
		BX.ajax.runAction("lists.controller.lock.unLock", {
			data: {
				element_id: elementId,
				iblock_type_id: this.iblockTypeId,
				iblock_id: this.iblockId,
				socnet_group_id: this.socnetGroupId
			}
		}).then(function (response) {
			this.reloadGrid();
		}.bind(this), function (response) {});
	};

	ListClass.prototype.reloadGrid = function ()
	{
		var reloadParams = {}, gridObject;
		gridObject = BX.Main.gridManager.getById(this.gridId);
		if(gridObject.hasOwnProperty("instance"))
			gridObject.instance.reloadTable("POST", reloadParams);
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
		BX.UI.Dialogs.MessageBox.confirm(
			BX.Loc.getMessage('CT_BLL_TOOLBAR_SECTION_DELETE_WARNING'),
			BX.Loc.getMessage('CT_BLL_DELETE_POPUP_TITLE'),
			() =>
			{
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
					onsuccess: (result) =>
					{
						if (result.status === 'success')
						{
							BX.Lists.showModalWithStatusAction({
								status: 'success',
								message: result.message
							});
							const reloadParams = {};
							const gridObject = BX.Main.gridManager.getById(gridId);
							if (gridObject.hasOwnProperty('instance'))
							{
								gridObject.instance.reloadTable('POST', reloadParams);
								const rowObject = gridObject.instance.getRows().getById(sectionId);
								if (rowObject)
								{
									rowObject.closeActionsMenu();
								}
							}
						}
						else if (BX.Type.isArrayFilled(result.errors))
						{
							BX.Lists.showModalWithStatusAction({
								status: 'error',
								message: result.errors.pop().message
							});
						}
					}
				});

				return true;
			},
			BX.Loc.getMessage("CT_BLL_DELETE_POPUP_ACCEPT_BUTTON"),
		);
	};

	ListClass.prototype.deleteElement = function (gridId, elementId)
	{
		BX.UI.Dialogs.MessageBox.confirm(
			BX.Loc.getMessage('CT_BLL_TOOLBAR_ELEMENT_DELETE_WARNING'),
			BX.Loc.getMessage('CT_BLL_DELETE_POPUP_TITLE'),
			() =>
			{
				const reloadParams = {};
				reloadParams['action_button_'+gridId] = 'delete';
				reloadParams['ID'] = [elementId];

				const gridObject = BX.Main.gridManager.getById(gridId);
				if(gridObject.hasOwnProperty('instance'))
				{
					gridObject.instance.reloadTable('POST', reloadParams);
					var rowObject = gridObject.instance.getRows().getById(elementId);
					if (rowObject)
					{
						rowObject.closeActionsMenu();
					}
				}

				return true;
			},
			BX.Loc.getMessage("CT_BLL_DELETE_POPUP_ACCEPT_BUTTON"),
		);
	};

	ListClass.prototype.toogleSectionGrid = function(event, menuItem)
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

					if (menuItem instanceof BX.Main.MenuItem)
					{
						menuItem.setText(text);
					}

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
