import {ajax, Loc} from 'main.core';
import { MessageBox } from 'ui.dialogs.messagebox';

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
		MessageBox.confirm(
			Loc.getMessage('CATALOG_AGENT_CONTRACT_TITLE_DELETE_CONTENT'),
			(messageBox, button) => {
				button.setWaiting();

				ajax.runAction(
					'catalog.agentcontract.entity.delete',
					{
						data: {
							id,
						},
					},
				).then(() => {
					messageBox.close();
					this.grid?.reload();
				}).catch((response) => {
					if (response.errors)
					{
						BX.UI.Notification.Center.notify({
							content: BX.util.htmlspecialchars(response.errors[0].message),
						});
					}

					messageBox.close();
				});
			},
			Loc.getMessage('CATALOG_AGENT_CONTRACT_BUTTON_CONFIRM'),
			(messageBox) => messageBox.close(),
			Loc.getMessage('CATALOG_AGENT_CONTRACT_BUTTON_BACK'),
		);
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
