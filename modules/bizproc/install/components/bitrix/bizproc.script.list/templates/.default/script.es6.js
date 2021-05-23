import {Reflection, Type, Event, Tag, Dom, Loc} from 'main.core';
import {MessageBox} from 'ui.dialogs.messagebox';
import {Script} from 'bizproc.script';

const namespace = Reflection.namespace('BX.Bizproc');

class ScriptListComponent
{
	gridId;
	createScriptButton;
	documentType;

	constructor(options)
	{
		if(Type.isPlainObject(options))
		{
			this.gridId = options.gridId;
			this.createScriptButton = options.createScriptButton
			this.documentType = options.documentType
		}
	}

	init()
	{
		if (this.createScriptButton)
		{
			Event.bind(this.createScriptButton, 'click', () => {
				Script.Manager.Instance.createScript(this.documentType).then(() => this.reloadGrid())
			});
		}
	}

	deleteScript(scriptId: number)
	{
		MessageBox.confirm(
			Loc.getMessage('BIZPROC_SCRIPT_LIST_CONFIRM_DELETE'),
			() => {
				Script.Manager.Instance.deleteScript(scriptId).then((response) =>
				{
					if (response.data && response.data.error)
					{
						MessageBox.alert(response.data.error);
					}
					else
					{
						this.reloadGrid()
					}
				});
				return true;
			},
			Loc.getMessage('BIZPROC_SCRIPT_LIST_BTN_DELETE')
		);
	}

	activateScript(scriptId: number)
	{
		Script.Manager.Instance.activateScript(scriptId).then(() => this.reloadGrid());
	}

	deactivateScript(scriptId: number)
	{
		Script.Manager.Instance.deactivateScript(scriptId).then(() => this.reloadGrid());
	}

	editScript(scriptId: number)
	{
		Script.Manager.Instance.editScript(scriptId).then(() => this.reloadGrid());
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

namespace.ScriptListComponent = ScriptListComponent;