import {Reflection, Type, Loc} from 'main.core';
import {MessageBox, MessageBoxButtons} from "ui.dialogs.messagebox";

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
				const grid = this.#getGrid();
				if (grid)
				{
					grid.removeRow(workflowId);
				}

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
					}
				}
			}
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
}

namespace.WorkflowInstances = WorkflowInstances;