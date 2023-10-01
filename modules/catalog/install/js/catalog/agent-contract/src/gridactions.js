import {ajax, Loc} from 'main.core';
import {Popup} from "main.popup";
import {Button, ButtonColor} from "ui.buttons";

export class GridActions
{
	grid;

	constructor(options = {})
	{
		this.grid = options.grid || null;

		BX.addCustomEvent('AgentContract:onDocumentSave', () => {
			this.grid?.reload();
		});

		BX.SidePanel.Instance.bindAnchors({
			rules: [
				{
					condition: [
						new RegExp("/agent_contract/details/[0-9]+/"),
						new RegExp("/bitrix/admin/cat_agent_contract.php\\?ID=([0-9]+)"),
					],
					options: {
						allowChangeHistory: false,
						cacheable: false,
						width: 650,
					}
				},
			]
		});
	}

	delete(id)
	{
		let popup = new Popup({
			id: 'catalog_agent_contract_list_delete_popup',
			titleBar: Loc.getMessage('CATALOG_AGENT_CONTRACT_TITLE_DELETE_TITLE'),
			content: Loc.getMessage('CATALOG_AGENT_CONTRACT_TITLE_DELETE_CONTENT'),
			buttons: [
				new Button({
					text:  Loc.getMessage('CATALOG_AGENT_CONTRACT_BUTTON_CONTINUE'),
					color: ButtonColor.SUCCESS,
					onclick: (button, event) => {
						button.setDisabled();

						ajax.runAction(
							'catalog.agentcontract.entity.delete',
							{
								data: {
									id: id
								}
							}
						).then((response) => {
							popup.destroy();
							this.grid?.reload();
						}).catch((response) => {
							if (response.errors)
							{
								BX.UI.Notification.Center.notify({
									content: BX.util.htmlspecialchars(response.errors[0].message),
								});
							}

							popup.destroy();
						});
					},
				}),
				new Button({
					text: Loc.getMessage('CATALOG_AGENT_CONTRACT_BUTTON_CANCEL'),
					color: ButtonColor.DANGER,
					onclick: (button, event) => {
						popup.destroy();
					}
				}),
			],
		});
		popup.show();
	}

	deleteList()
	{
		let ids = this.grid.getRows().getSelectedIds();
		ajax.runAction(
			'catalog.agentcontract.entity.deleteList',
			{
				data: {
					ids: ids,
				}
			}
		).then((response) => {
			this.grid?.reload();
		}).catch((response) => {
			if (response.errors)
			{
				response.errors.forEach((error) => {
					if (error.message)
					{
						BX.UI.Notification.Center.notify({
							content: BX.util.htmlspecialchars(error.message),
						});
					}
				});
			}
			this.grid?.reload();
		});
	}
}