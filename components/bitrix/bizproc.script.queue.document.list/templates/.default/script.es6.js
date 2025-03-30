import { Reflection, Loc, Type, ajax } from 'main.core';
import { Router } from 'bizproc.router';
import { MessageBox } from 'ui.dialogs.messagebox';
import { UI } from 'ui.notification';

const namespace = Reflection.namespace('BX.Bizproc');

class ScriptQueueDocumentListComponent
{
	#gridId;

	constructor(options)
	{
		if (Type.isPlainObject(options))
		{
			this.#gridId = options.gridId;
		}
	}

	openWorkflowLog(workflowId: string)
	{
		Router.openWorkflowLog(workflowId);
	}

	terminateWorkflow(workflowId: string)
	{
		MessageBox.confirm(
			Loc.getMessage('BIZPROC_SCRIPT_QDL_CONFIRM_TERMINATE'),
			() => {
				this.#terminateWorkflowRunAction(workflowId);

				return true;
			},
			Loc.getMessage('BIZPROC_SCRIPT_QDL_BTN_TERMINATE'),
		);
	}

	#terminateWorkflowRunAction(workflowId: string)
	{
		ajax.runAction('bizproc.workflow.terminate', { data: { workflowId } })
			.then(() => {
				this.#reloadGrid();
				UI.Notification.Center.notify({
					content: Loc.getMessage('BIZPROC_SCRIPT_QDL_TERMINATE_SUCCESS'),
					autoHideDelay: 5000,
				});
			})
			.catch((response) => {
				response.errors.forEach((error) => {
					UI.Notification.Center.notify({
						content: error.message,
						autoHideDelay: 5000,
					});
				});
			});
	}

	#reloadGrid()
	{
		if (this.#gridId)
		{
			const grid = BX.Main.gridManager && BX.Main.gridManager.getInstanceById(this.#gridId);
			if (grid)
			{
				grid.reload();
			}
		}
	}
}

namespace.ScriptQueueDocumentListComponent = ScriptQueueDocumentListComponent;
