import { ajax, Dom, Event, Extension, Loc, Tag } from 'main.core';
import { PopupManager } from 'main.popup';
import { MessageBox } from 'ui.dialogs.messagebox';
import { EnableWizardOpener, AnalyticsContextList } from 'catalog.store-enable-wizard';

export class DocumentGridManager
{
	constructor(options)
	{
		this.gridId = options.gridId;
		this.filterId = options.filterId;
		this.grid = BX.Main.gridManager.getInstanceById(this.gridId);
		this.isConductDisabled = options.isConductDisabled;
		this.masterSliderUrl = options.masterSliderUrl;
		this.isInventoryManagementDisabled = options.isInventoryManagementDisabled;
		this.inventoryManagementFeatureCode = options.inventoryManagementFeatureCode;
		this.inventoryManagementSource = options.inventoryManagementSource;
	}

	getSelectedIds()
	{
		return this.grid.getRows().getSelectedIds();
	}

	static hideSettingsMenu()
	{
		PopupManager.getPopupById('docFieldsSettingsMenu').close();
	}

	deleteDocument(documentId)
	{
		if (this.isInventoryManagementDisabled && this.inventoryManagementFeatureCode)
		{
			top.BX.UI.InfoHelper.show(this.inventoryManagementFeatureCode);

			return;
		}

		MessageBox.confirm(
			Loc.getMessage('DOCUMENT_GRID_DOCUMENT_DELETE_CONTENT_2'),
			(messageBox, button) => {
				button.setWaiting();
				ajax.runAction(
					'catalog.document.deleteList',
					{
						data: {
							documentIds: [documentId],
						},
						analyticsLabel: {
							inventoryManagementSource: this.inventoryManagementSource,
						},
					},
				).then(() => {
					messageBox.close();
					this.grid.reload();
				}).catch((response) => {
					if (response.errors)
					{
						BX.UI.Notification.Center.notify({
							content: response.errors[0].message,
						});
					}
					messageBox.close();
				});
			},
			Loc.getMessage('DOCUMENT_GRID_DOCUMENT_DELETE_BUTTON_CONFIRM'),
			(messageBox) => messageBox.close(),
			Loc.getMessage('DOCUMENT_GRID_BUTTON_BACK'),
		);
	}

	conductDocument(documentId, documentType = '')
	{
		if (this.isInventoryManagementDisabled && this.inventoryManagementFeatureCode)
		{
			top.BX.UI.InfoHelper.show(this.inventoryManagementFeatureCode);

			return;
		}

		if (this.isConductDisabled)
		{
			this.openStoreMasterSlider();

			return;
		}

		const actionConfig = {
			data: {
				documentIds: [documentId],
			},
		};
		if (documentType !== '')
		{
			actionConfig.analyticsLabel = {
				documentType,
			};
		}

		actionConfig.analyticsLabel.inventoryManagementSource = this.inventoryManagementSource;

		actionConfig.analyticsLabel.mode = 'single';

		MessageBox.confirm(
			Loc.getMessage('DOCUMENT_GRID_DOCUMENT_CONDUCT_CONTENT_2'),
			(messageBox, button) => {
				button.setWaiting();
				ajax.runAction(
					'catalog.document.conductList',
					actionConfig,
				).then(() => {
					messageBox.close();
					this.grid.reload();
				}).catch((response) => {
					if (response.errors)
					{
						BX.UI.Notification.Center.notify({
							content: response.errors[0].message,
						});
					}
					messageBox.close();
				});
			},
			Loc.getMessage('DOCUMENT_GRID_DOCUMENT_CONDUCT_BUTTON_CONFIRM'),
			(messageBox) => messageBox.close(),
			Loc.getMessage('DOCUMENT_GRID_BUTTON_BACK'),
		);
	}

	cancelDocument(documentId, documentType = '')
	{
		if (this.isInventoryManagementDisabled && this.inventoryManagementFeatureCode)
		{
			top.BX.UI.InfoHelper.show(this.inventoryManagementFeatureCode);

			return;
		}

		if (this.isConductDisabled)
		{
			this.openStoreMasterSlider();

			return;
		}

		const settings = Extension.getSettings('catalog.document-grid');

		const actionConfig = {
			data: {
				documentIds: [documentId],
			},
		};
		if (documentType !== '')
		{
			actionConfig.analyticsLabel = {
				documentType,
			};
		}

		actionConfig.analyticsLabel.mode = 'single';

		actionConfig.analyticsLabel.inventoryManagementSource = this.inventoryManagementSource;

		let content = Loc.getMessage('DOCUMENT_GRID_DOCUMENT_CANCEL_CONTENT_2');
		if (settings.get('isProductBatchMethodSelected'))
		{
			const text = Loc.getMessage(
				'DOCUMENT_GRID_DOCUMENT_CANCEL_BATCH_SELECTED_CONTENT',
				{
					'#HELP_LINK#': '<help-link></help-link>',
				},
			);

			content = Tag.render`
				<div>
					<div>${content}</div>
					<div>${text}</div>
				</div>
			`;

			const moreLink = Tag.render`
				<a href="#" class="ui-form-link">
					${Loc.getMessage('DOCUMENT_GRID_DOCUMENT_CANCEL_BATCH_SELECTED_CONTENT_LINK')}
				</a>
			`;

			Event.bind(moreLink, 'click', () => {
				const articleId = 17858278;
				top.BX.Helper.show(`redirect=detail&code=${articleId}`);
			});

			Dom.replace(content.querySelector('help-link'), moreLink);
		}

		MessageBox.confirm(
			content,
			(messageBox, button) => {
				button.setWaiting();
				ajax.runAction(
					'catalog.document.cancelList',
					actionConfig,
				).then(() => {
					messageBox.close();
					this.grid.reload();
				}).catch((response) => {
					if (response.errors)
					{
						BX.UI.Notification.Center.notify({
							content: response.errors[0].message,
						});
					}
					messageBox.close();
				});
			},
			Loc.getMessage('DOCUMENT_GRID_DOCUMENT_CANCEL_BUTTON_CONFIRM'),
			(messageBox) => messageBox.close(),
			Loc.getMessage('DOCUMENT_GRID_BUTTON_BACK'),
		);
	}

	deleteSelectedDocuments()
	{
		if (this.isInventoryManagementDisabled && this.inventoryManagementFeatureCode)
		{
			top.BX.UI.InfoHelper.show(this.inventoryManagementFeatureCode);

			return;
		}

		const documentIds = this.getSelectedIds();
		ajax.runAction(
			'catalog.document.deleteList',
			{
				data: {
					documentIds,
				},
				analyticsLabel: {
					inventoryManagementSource: this.inventoryManagementSource,
				},
			},
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
		if (this.isInventoryManagementDisabled && this.inventoryManagementFeatureCode)
		{
			top.BX.UI.InfoHelper.show(this.inventoryManagementFeatureCode);

			return;
		}

		if (this.isConductDisabled)
		{
			this.openStoreMasterSlider();

			return;
		}
		const documentIds = this.getSelectedIds();
		ajax.runAction(
			'catalog.document.conductList',
			{
				data: {
					documentIds,
				},
				analyticsLabel: {
					mode: 'list',
					inventoryManagementSource: this.inventoryManagementSource,
				},
			},
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
		if (this.isInventoryManagementDisabled && this.inventoryManagementFeatureCode)
		{
			top.BX.UI.InfoHelper.show(this.inventoryManagementFeatureCode);

			return;
		}

		if (this.isConductDisabled)
		{
			this.openStoreMasterSlider();

			return;
		}
		const documentIds = this.getSelectedIds();
		ajax.runAction(
			'catalog.document.cancelList',
			{
				data: {
					documentIds,
				},
				analyticsLabel: {
					mode: 'list',
					inventoryManagementSource: this.inventoryManagementSource,
				},
			},
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
		const actionValues = this.grid.getActionsPanel().getValues();
		const selectedAction = actionValues[`action_button_${this.gridId}`];

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
		const filterManager = BX.Main.filterManager.getById(this.filterId);
		if (!filterManager)
		{
			return;
		}

		filterManager.getApi().extendFilter(options);
	}

	openHowToStart()
	{
		if (top.BX.Helper)
		{
			top.BX.Helper.show('redirect=detail&code=14566618');
			event.preventDefault();
		}
	}

	openHowToTransfer()
	{
		if (top.BX.Helper)
		{
			top.BX.Helper.show('redirect=detail&code=14566610');
			event.preventDefault();
		}
	}

	openHowToControlGoodsMovement()
	{
		if (top.BX.Helper)
		{
			top.BX.Helper.show('redirect=detail&code=14566670');
			event.preventDefault();
		}
	}

	openHowToAccountForLosses()
	{
		if (top.BX.Helper)
		{
			top.BX.Helper.show('redirect=detail&code=14566652');
			event.preventDefault();
		}
	}

	openStoreMasterSlider()
	{
		new EnableWizardOpener().open(
			this.masterSliderUrl,
			{
				urlParams: {
					analyticsContextSection: AnalyticsContextList.DOCUMENT_LIST,
				},
				data: {
					openGridOnDone: false,
				},
				events: {
					onCloseComplete: function(event) {
						const slider = event.getSlider();
						if (!slider)
						{
							return;
						}

						if (slider.getData().get('isInventoryManagementEnabled'))
						{
							document.location.reload();
						}
					},
				},
			},
		);
	}

	static openUfSlider(e, item)
	{
		e.preventDefault();

		DocumentGridManager.hideSettingsMenu();
		BX.SidePanel.Instance.open(
			item.options.href,
			{
				allowChangeHistory: false,
				cacheable: false,
			},
		);
	}
}
