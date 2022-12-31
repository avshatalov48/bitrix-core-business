import { Type, Dom, ajax, Loc } from 'main.core';
import { BaseEvent } from 'main.core.events';

export class Actions
{
	static options = {};
	static class = {
		active: 'main-grid-cell-content-action-active',
		showByHover: 'main-grid-cell-content-action-by-hover',
	};
	static actionsPanel = null;

	static setOptions(options)
	{
		Actions.options = options;
	}

	static setActionsPanel(actionsPanel)
	{
		Actions.actionsPanel = actionsPanel;
	}

	static changePin(groupId, event: BaseEvent)
	{
		const { button } = event.getData();

		const action = (
			Dom.hasClass(button, Actions.class.active)
				? 'unpin'
				: 'pin'
		);

		ajax.runAction('socialnetwork.api.workgroup.changePin', {
			data: {
				groupIdList: [ groupId ],
				action: action,
				componentName: Actions.options.componentName,
				signedParameters: Actions.options.signedParameters,
			},
		}).then(
			() => {
				if (action === 'unpin')
				{
					Dom.removeClass(button, Actions.class.active);
					Dom.addClass(button, Actions.class.showByHover);
				}
				else
				{
					Dom.addClass(button, Actions.class.active);
					Dom.removeClass(button, Actions.class.showByHover);
				}
			},
			(response) => {
				const errorMessage = (
					Type.isStringFilled(response.message)
						? response.message
						: Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_ACTION_ERROR')
				);
				BX.UI.Notification.Center.notify({
					content: errorMessage,
				});
			}
		);
	}

	static getActionIds(params)
	{
		if (!Type.isUndefined(params.groupId))
		{
			return [ params.groupId ];
		}

		const selected = Actions.getSelectedRows();
		if (selected.length === 0)
		{
			return [];
		}

		return selected.map((row) => {
			return row.getDataset().id;
		});
	}

	static hideActionsPanel()
	{
		if (!Actions.actionsPanel)
		{
			return;
		}

		Actions.actionsPanel.hidePanel();
	}

	static getSelectedRows()
	{
		return Actions.getGridInstance().getRows().getSelected();
	}

	static unselectRows()
	{
		Actions.getGridInstance().getRows().unselectAll();
	}

	static getGridInstance()
	{
		return Actions.options.gridInstance;
	}
}
