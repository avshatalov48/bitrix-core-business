import {Reflection, Type, Event, Tag, Dom, Loc} from 'main.core';
import {MessageBox, MessageBoxButtons} from 'ui.dialogs.messagebox';
import {Script} from 'bizproc.script';
import 'sidepanel';

const namespace = Reflection.namespace('BX.Bizproc');

class ScriptListComponent
{
	gridId;
	createScriptButton;
	exportScriptButton;
	documentType;

	constructor(options)
	{
		if(Type.isPlainObject(options))
		{
			this.gridId = options.gridId;
			this.createScriptButton = options.createScriptButton
			this.exportScriptButton = options.exportScriptButton
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

		if (this.exportScriptButton)
		{
			Event.bind(this.exportScriptButton, 'click', (event) => {

				if (!Dom.hasClass(this.exportScriptButton, 'ui-btn-disabled'))
				{
					BX.SidePanel.Instance.open(this.exportScriptButton.getAttribute('data-url'));
				}
			});

			if (!this.hasRows())
			{
				this.#disableExport();
			}
		}

		BX.addCustomEvent('Grid::updated', () => {
			if (!this.hasRows())
			{
				this.#disableExport();
			}
			else
			{
				this.#enableExport();
			}
		});
	}

	deleteScript(scriptId: number)
	{
		const messageBox = new MessageBox({
			message: Loc.getMessage('BIZPROC_SCRIPT_LIST_CONFIRM_DELETE'),
			okCaption: Loc.getMessage('BIZPROC_SCRIPT_LIST_BTN_DELETE'),
			onOk: () => {
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

	activateScript(scriptId: number)
	{
		Script.Manager.Instance.activateScript(scriptId).then(
			(response) => {
				if (response.data.error)
				{
					MessageBox.alert(response.data.error);
				}
				else
				{
					this.reloadGrid()
				}
			}
		);
	}

	deactivateScript(scriptId: number)
	{
		Script.Manager.Instance.deactivateScript(scriptId).then((response) => {
			if (response.data.error)
			{
				MessageBox.alert(response.data.error);
			}
			else
			{
				this.reloadGrid()
			}
		});
	}

	editScript(scriptId: number)
	{
		Script.Manager.Instance.editScript(scriptId).then(() => this.reloadGrid());
	}

	#getGrid()
	{
		if (this.gridId)
		{
			return BX.Main.gridManager && BX.Main.gridManager.getInstanceById(this.gridId);
		}
		return null;
	}

	reloadGrid()
	{
		const grid = this.#getGrid();
		if (grid)
		{
			grid.reload();
		}
	}

	hasRows()
	{
		const grid = this.#getGrid();
		if (grid)
		{
			return grid.getRows().getCountDisplayed() > 0;
		}
		return false;
	}

	#disableExport()
	{
		if (this.exportScriptButton)
		{
			Dom.addClass(this.exportScriptButton, 'ui-btn-disabled');
		}
	}
	#enableExport()
	{
		if (this.exportScriptButton)
		{
			Dom.removeClass(this.exportScriptButton, 'ui-btn-disabled');
		}
	}
}

namespace.ScriptListComponent = ScriptListComponent;