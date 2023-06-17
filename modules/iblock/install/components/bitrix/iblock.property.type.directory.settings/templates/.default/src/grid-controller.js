import { Type } from 'main.core';
import { EventEmitter } from 'main.core.events';

export class GridController
{
	grid: BX.Main.Grid;

	constructor(options)
	{
		this.grid = BX.Main.gridManager.getInstanceById(options.gridId);

		this.initGrid();
	}

	getGridBodyRows(): Array
	{
		return this.grid.getRows().getBodyChild();
	}

	initGrid(): void
	{
		EventEmitter.subscribe('Grid::updated', (event) => {
			const grid = event.getCompatData()[0];
			if (grid && grid.getId() === this.grid.getId())
			{
				const delayToExitStream = 10;
				setTimeout(this.initGridRows.bind(this), delayToExitStream);
			}
		});

		this.initGridRows();
	}

	initGridRows(): void
	{
		const bodyRows = this.getGridBodyRows();
		if (bodyRows.length === 0)
		{
			for (let i = 0; i < 5; i++)
			{
				this.prependRowEditor();
			}
		}
		else
		{
			bodyRows.forEach((row) => {
				row.edit();
			});
		}
	}

	prependRowEditor()
	{
		const newRow = this.grid.prependRowEditor();
		newRow.setId('');
		newRow.unselect();
	}

	removeGridSelectedRows()
	{
		const rows = this.grid.getRows().getSelected(false);
		if (Type.isArray(rows))
		{
			rows.forEach((row) => {
				row.hide();
			});

			this.grid.getRows().reset();
		}
	}
}
