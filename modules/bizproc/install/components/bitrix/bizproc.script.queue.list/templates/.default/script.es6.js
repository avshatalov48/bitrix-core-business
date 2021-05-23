import {Reflection, Type, Event, Tag, Dom, Loc} from 'main.core';
import {MessageBox} from 'ui.dialogs.messagebox';
import {Script} from 'bizproc.script';

const namespace = Reflection.namespace('BX.Bizproc');

class ScriptQueueListComponent
{
	gridId;

	constructor(options)
	{
		if(Type.isPlainObject(options))
		{
			this.gridId = options.gridId;
		}
	}

	deleteQueue(queueId: number)
	{
		MessageBox.confirm(
			Loc.getMessage('BIZPROC_SCRIPT_QUEUE_LIST_CONFIRM_DELETE'),
			() => {
				Script.Manager.Instance.deleteScriptQueue(queueId);
				this.reloadGrid();
				return true;
			},
			Loc.getMessage('BIZPROC_SCRIPT_QUEUE_LIST_BTN_DELETE')
		);
	}

	terminateQueue(queueId: number)
	{
		MessageBox.confirm(
			Loc.getMessage('BIZPROC_SCRIPT_QUEUE_LIST_CONFIRM_TERMINATE'),
			() => {
				Script.Manager.Instance.terminateScriptQueue(queueId);
				this.reloadGrid();
				return true;
			},
			Loc.getMessage('BIZPROC_SCRIPT_QUEUE_LIST_BTN_TERMINATE')
		);
	}

	reloadGrid()
	{
		if (this.gridId)
		{
			const grid = BX.Main.gridManager && BX.Main.gridManager.getInstanceById(this.gridId);
			if (grid)
			{
				grid.reload();
			}
		}
	}
}

namespace.ScriptQueueListComponent = ScriptQueueListComponent;