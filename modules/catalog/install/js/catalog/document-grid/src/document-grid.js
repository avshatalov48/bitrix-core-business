import {ajax, Loc} from 'main.core';
import { Popup } from 'main.popup';
import {Button, ButtonColor} from 'ui.buttons';
import {Slider} from 'catalog.store-use'

export class DocumentGridManager
{
	constructor(options)
	{
		this.gridId = options.gridId;
		this.filterId = options.filterId;
		this.grid = BX.Main.gridManager.getInstanceById(this.gridId);
		this.isConductDisabled = options.isConductDisabled;
		this.masterSliderUrl = options.masterSliderUrl;
		this.inventoryManagementSource = options.inventoryManagementSource;
	}

	getSelectedIds()
	{
		return this.grid.getRows().getSelectedIds()
	}

	deleteDocument(documentId)
	{
		let popup = new Popup({
			id: 'catalog_delete_document_popup',
			titleBar: Loc.getMessage('DOCUMENT_GRID_DOCUMENT_DELETE_TITLE'),
			content: Loc.getMessage('DOCUMENT_GRID_DOCUMENT_DELETE_CONTENT'),
			buttons: [
				new Button({
					text:  Loc.getMessage('DOCUMENT_GRID_CONTINUE'),
					color: ButtonColor.SUCCESS,
					onclick: (button, event) => {
						button.setDisabled();
						ajax.runAction(
							'catalog.document.deleteList',
							{
								data: {
									documentIds: [documentId],
								},
								analyticsLabel: {
									inventoryManagementSource: this.inventoryManagementSource,
								},
							}
						).then((response) => {
							popup.destroy();
							this.grid.reload();
						}).catch((response) => {
							if (response.errors)
							{
								BX.UI.Notification.Center.notify({
									content: response.errors[0].message,
								});
							}
							popup.destroy();
						});
					},
				}),
				new Button({
					text: Loc.getMessage('DOCUMENT_GRID_CANCEL'),
					color: ButtonColor.DANGER,
					onclick: (button, event) => {
						popup.destroy();
					}
				}),
			],
		});
		popup.show();
	}

	conductDocument(documentId, documentType = '')
	{
		if (this.isConductDisabled)
		{
			this.openStoreMasterSlider();
			return;
		}

		let actionConfig = {
			data: {
				documentIds: [documentId],
			}
		};
		if (documentType !== '')
		{
			actionConfig.analyticsLabel = {
				documentType,
			}
		}

		actionConfig.analyticsLabel.inventoryManagementSource = this.inventoryManagementSource;

		actionConfig.analyticsLabel.mode = 'single';

		let popup = new Popup({
			id: 'catalog_delete_document_popup',
			titleBar: Loc.getMessage('DOCUMENT_GRID_DOCUMENT_CONDUCT_TITLE'),
			content: Loc.getMessage('DOCUMENT_GRID_DOCUMENT_CONDUCT_CONTENT'),
			buttons: [
				new Button({
					text:  Loc.getMessage('DOCUMENT_GRID_CONTINUE'),
					color: ButtonColor.SUCCESS,
					onclick: (button, event) => {
						button.setDisabled();
						ajax.runAction(
							'catalog.document.conductList',
							actionConfig
						).then((response) => {
							popup.destroy();
							this.grid.reload();
						}).catch((response) => {
							if (response.errors)
							{
								BX.UI.Notification.Center.notify({
									content: response.errors[0].message,
								});
							}
							popup.destroy();
						});
					},
				}),
				new Button({
					text: Loc.getMessage('DOCUMENT_GRID_CANCEL'),
					color: ButtonColor.DANGER,
					onclick: (button, event) => {
						popup.destroy();
					}
				}),
			],
		});
		popup.show();
	}

	cancelDocument(documentId, documentType = '')
	{
		if (this.isConductDisabled)
		{
			this.openStoreMasterSlider();
			return;
		}

		let actionConfig = {
			data: {
				documentIds: [documentId],
			}
		};
		if (documentType !== '')
		{
			actionConfig.analyticsLabel = {
				documentType,
			}
		}

		actionConfig.analyticsLabel.mode = 'single';

		actionConfig.analyticsLabel.inventoryManagementSource = this.inventoryManagementSource;

		let popup = new Popup({
			id: 'catalog_delete_document_popup',
			titleBar: Loc.getMessage('DOCUMENT_GRID_DOCUMENT_CANCEL_TITLE'),
			content: Loc.getMessage('DOCUMENT_GRID_DOCUMENT_CANCEL_CONTENT'),
			buttons: [
				new Button({
					text:  Loc.getMessage('DOCUMENT_GRID_CONTINUE'),
					color: ButtonColor.SUCCESS,
					onclick: (button, event) => {
						button.setDisabled();
						ajax.runAction(
							'catalog.document.cancelList',
							actionConfig
						).then((response) => {
							popup.destroy();
							this.grid.reload();
						}).catch((response) => {
							if (response.errors)
							{
								BX.UI.Notification.Center.notify({
									content: response.errors[0].message,
								});
							}
							popup.destroy();
						});
					},
				}),
				new Button({
					text: Loc.getMessage('DOCUMENT_GRID_CANCEL'),
					color: ButtonColor.DANGER,
					onclick: (button, event) => {
						popup.destroy();
					}
				}),
			],
		});
		popup.show();
	}

	deleteSelectedDocuments()
	{
		let documentIds = this.getSelectedIds();
		ajax.runAction(
			'catalog.document.deleteList',
			{
				data: {
					documentIds
				},
				analyticsLabel: {
					inventoryManagementSource: this.inventoryManagementSource,
				},
			}
		).then((response) => {
			this.grid.reload();
		}).catch((response) => {
			if (response.errors)
			{
				response.errors.forEach((error) => {
					if (error.message)
					{
						BX.UI.Notification.Center.notify({
							content: error.message,
						});
					}
				});
			}
			this.grid.reload();
		});
	}

	conductSelectedDocuments()
	{
		if (this.isConductDisabled)
		{
			this.openStoreMasterSlider();
			return;
		}
		let documentIds = this.getSelectedIds();
		ajax.runAction(
			'catalog.document.conductList',
			{
				data: {
					documentIds
				},
				analyticsLabel: {
					mode: 'list',
					inventoryManagementSource: this.inventoryManagementSource,
				},
			}
		).then((response) => {
			this.grid.reload();
		}).catch((response) => {
			if (response.errors)
			{
				response.errors.forEach((error) => {
					if (error.message)
					{
						BX.UI.Notification.Center.notify({
							content: error.message,
						});
					}
				});
			}
			this.grid.reload();
		});
	}

	cancelSelectedDocuments()
	{
		if (this.isConductDisabled)
		{
			this.openStoreMasterSlider();
			return;
		}
		let documentIds = this.getSelectedIds();
		ajax.runAction(
			'catalog.document.cancelList',
			{
				data: {
					documentIds
				},
				analyticsLabel: {
					mode: 'list',
					inventoryManagementSource: this.inventoryManagementSource,
				},
			}
		).then((response) => {
			this.grid.reload();
		}).catch((response) => {
			if (response.errors)
			{
				response.errors.forEach((error) => {
					if (error.message)
					{
						BX.UI.Notification.Center.notify({
							content: error.message,
						});
					}
				});
			}
			this.grid.reload();
		});
	}

	processApplyButtonClick()
	{
		let actionValues = this.grid.getActionsPanel().getValues();
		let selectedAction = actionValues['action_button_' + this.gridId];

		if (selectedAction === 'conduct')
		{
			this.conductSelectedDocuments();
		}
		if (selectedAction === 'cancel')
		{
			this.cancelSelectedDocuments();
		}
	}

	applyFilter(options)
	{
		let filterManager = BX.Main.filterManager.getById(this.filterId);
		if (!filterManager)
		{
			return;
		}

		filterManager.getApi().extendFilter(options);
	}

	openHowToStart()
	{
		if(top.BX.Helper)
		{
			top.BX.Helper.show("redirect=detail&code=14566618");
			event.preventDefault();
		}
	}

	openHowToTransfer()
	{
		if(top.BX.Helper)
		{
			top.BX.Helper.show("redirect=detail&code=14566610");
			event.preventDefault();
		}
	}

	openHowToControlGoodsMovement()
	{
		if(top.BX.Helper)
		{
			top.BX.Helper.show("redirect=detail&code=14566670");
			event.preventDefault();
		}
	}

	openHowToAccountForLosses()
	{
		if(top.BX.Helper)
		{
			top.BX.Helper.show("redirect=detail&code=14566652");
			event.preventDefault();
		}
	}

	openStoreMasterSlider()
	{
		new Slider().open(
			this.masterSliderUrl,
			{
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
							document.location.reload();
						}
					}
				}
			}
		);
	}
}
