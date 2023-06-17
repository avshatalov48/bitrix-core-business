import {Reflection, Loc} from 'main.core';

class PropertyListGrid
{
	#gridId: String;
	#sliderSettings: Object;

	static Instance: PropertyListGrid = null;

	constructor(options)
	{
		this.#gridId = options.id;

		this.#sliderSettings = {
			width: 900,
			cacheable: false,
			allowChangeHistory: false,
			events: {
				onClose: () => {
					this.#getGrid().reload();
				},
			},
		};
	}

	#getGrid()
	{
		if (this.#gridId)
		{
			const grid = BX.Main.gridManager.getInstanceById(this.#gridId);
			if (grid)
			{
				return grid;
			}
		}

		throw new Error(`Not found grid for property list with id ${this.#gridId}`);
	}

	openDetailSlider(id)
	{
		top.BX.SidePanel.Instance.open(`details/${parseInt(id)}/`, this.#sliderSettings);
	}

	openCreateSlider()
	{
		top.BX.SidePanel.Instance.open('details/0/', this.#sliderSettings);
	}

	static openCreateSliderStatic()
	{
		PropertyListGrid.Instance.openCreateSlider();
	}

	delete(id)
	{
		BX.UI.Dialogs.MessageBox.confirm(
			Loc.getMessage('IBLOCK_PROPERTY_LIST_TEMPLATE_CONFIRM_DELETE'),
			() => {
				// emulate run group action `delete`
				const grid = this.#getGrid();
				const data = {
					'ID': [
						id,
					],
				};
				data[grid.getActionKey()] = 'delete';
				grid.reloadTable('POST', data);

				return true;
			},
		);
	}
}

Reflection.namespace('BX.Iblock').PropertyListGrid = PropertyListGrid;
