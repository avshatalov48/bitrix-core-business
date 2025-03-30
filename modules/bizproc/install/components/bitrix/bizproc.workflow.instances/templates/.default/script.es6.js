import { Reflection, Type, Loc, ajax, Runtime } from 'main.core';
import { UI } from 'ui.notification';
import { MessageBox, MessageBoxButtons } from 'ui.dialogs.messagebox';

const namespace = Reflection.namespace('BX.Bizproc.Component');

class WorkflowInstances
{
	#gridId;

	constructor(options)
	{
		if (Type.isPlainObject(options))
		{
			this.#gridId = options.gridId;
		}
	}

	deleteItem(workflowId)
	{
		const messageBox = new MessageBox({
			message: Loc.getMessage('BPWI_DELETE_MESS_CONFIRM'),
			okCaption: Loc.getMessage('BPWI_DELETE_BTN_LABEL'),
			onOk: () => {
				this.#removeGridRow(workflowId);
				this.#showNotification({
					content: Loc.getMessage('BPWIT_DELETE_NOTIFICATION'),
				});

				return true;
			},
			buttons: MessageBoxButtons.OK_CANCEL,
			popupOptions: {
				events: {
					onAfterShow: (event) => {
						const okBtn = event.getTarget().getButton('ok');
						if (okBtn)
						{
							okBtn.getContainer().focus();
						}
					},
				},
			},
		});

		messageBox.show();
	}

	#getGrid()
	{
		if (this.#gridId)
		{
			return BX.Main.gridManager && BX.Main.gridManager.getInstanceById(this.#gridId);
		}

		return null;
	}

	#removeGridRow(workflowId: string): void
	{
		const grid = this.#getGrid();
		if (grid)
		{
			grid.removeRow(workflowId);
		}
	}

	terminateItem(workflowId: string): void
	{
		ajax.runAction('bizproc.workflow.terminate', { data: { workflowId } })
			.then(() => {
				this.#removeGridRow(workflowId);
				this.#showNotification({
					content: Loc.getMessage('BPWIT_TERMINATE_NOTIFICATION'),
				});
			})
			.catch((response) => {
				response.errors.forEach((error) => {
					this.#showNotification({ content: error.message });
				});
			});
	}

	logItem(workflowId: string): void
	{
		Runtime
			.loadExtension('bizproc.router')
			.then(({ Router }) => {
				Router.openWorkflowLog(workflowId);
			})
			.catch((e) => console.error(e));
	}

	#showNotification(notificationOptions: Object): void
	{
		const defaultSettings = { autoHideDelay: 5000 };

		UI.Notification.Center.notify(Object.assign(defaultSettings, notificationOptions));
	}
}

namespace.WorkflowInstances = WorkflowInstances;
