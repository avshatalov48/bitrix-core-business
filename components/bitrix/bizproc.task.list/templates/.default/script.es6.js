import {Reflection, Type} from "main.core";
import {TagSelector} from 'ui.entity-selector';

const namespace = Reflection.namespace('BX.Bizproc.Component');

class TaskList
{
	#gridId: string;
	#delegateToSelector: TagSelector;
	#delegateToUserId: number = 0;

	constructor(options: {gridId: string})
	{
		this.#gridId = options.gridId;

		this.#initSelectors();
	}

	#initSelectors(): void
	{
		const self = this;

		this.#delegateToSelector = new TagSelector({
			multiple: false,
			tagMaxWidth: 180,
			events: {
				onTagAdd(event)
				{
					self.#delegateToUserId = parseInt(event.getData().tag.getId());

					if (!Type.isInteger(self.#delegateToUserId))
					{
						self.#delegateToUserId = 0;
					}
				},
				onTagRemove()
				{
					self.#delegateToUserId = 0;
				},
			},
			dialogOptions: {
				entities: [
					{
						id: 'user',
						options: {
							intranetUsersOnly: true,
							inviteEmployeeLink: false,
						},
					},
				],
			}
		});
	}

	init(): void
	{
		const delegateToWrapper = document.getElementById('ACTION_DELEGATE_TO_WRAPPER');
		if (delegateToWrapper)
		{
			this.#delegateToSelector.renderTo(delegateToWrapper);
		}
	}

	applyActionPanelValues(): void
	{
		const grid = this.getGrid()
		const actionsPanel = grid?.getActionsPanel();

		if (grid && actionsPanel)
		{
			const data = {
				['action_all_rows_' + this.#gridId]: actionsPanel.getForAllCheckbox()?.checked ? 'Y' : 'N',
				ACTION_DELEGATE_TO_ID: this.#delegateToUserId,
				ID: grid.getRows().getSelectedIds(),
			};
			for (const [key, value] of Object.entries(actionsPanel.getValues()))
			{
				data[key] = Type.isString(value) ? value.trim().replace(/^['"]+|['"]+$/g, '') : value;
			}

			this.getGrid()?.reloadTable('POST', data, () => this.init());
		}
	}

	reloadGrid()
	{
		const grid = this.getGrid();
		if (grid)
		{
			grid.reload();
		}
	}

	getGrid(): ?BX.Main.grid
	{
		if (this.#gridId)
		{
			return BX.Main.gridManager && BX.Main.gridManager.getInstanceById(this.#gridId);
		}

		return null;
	}
}

namespace.TaskList = TaskList;