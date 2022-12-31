import {Dom, Type} from 'main.core';
import {EventEmitter} from 'main.core.events';

import './css/pin.css';

export class Pin
{
	static class = {
		pinned: 'sonet-ui-grid-row-pinned',
	};

	constructor(params)
	{
		this.grid = params.gridInstance;

		this.bindEvents();
		this.colorPinnedRows();
	}

	bindEvents()
	{
		EventEmitter.subscribe('BX.Main.grid:paramsUpdated', this.onParamsUpdated.bind(this));
	}

	onParamsUpdated()
	{
		this.colorPinnedRows();
	}

	colorPinnedRows()
	{
		this.getRows().forEach((row) => {
			const node = row.getNode();

			this.getIsPinned(row.getId())
				? Dom.addClass(node, Pin.class.pinned)
				: Dom.removeClass(node, Pin.class.pinned)
			;
		});
	}

	resetRows()
	{
		this.grid.getRows().reset();
	}

	getRows()
	{
		return this.grid.getRows().getBodyChild();
	}

	getLastPinnedRowId()
	{
		const pinnedRows = Object.values(this.getRows()).filter(row => this.getIsPinned(row.getId()));
		const keys = Object.keys(pinnedRows);

		if (keys.length > 0)
		{
			return pinnedRows[keys[keys.length - 1]].getId();
		}

		return 0;
	}

	getIsPinned(rowId)
	{
		return (
			this.isRowExist(rowId)
			&& Type.isDomNode(this.getRowNodeById(rowId).querySelector('.main-grid-cell-content-action-pin.main-grid-cell-content-action-active'))
		);
	}

	getRowNodeById(id)
	{
		return this.getRowById(id).getNode();
	}

	getRowById(id)
	{
		return this.grid.getRows().getById(id);
	}

	isRowExist(id)
	{
		return this.getRowById(id) !== null;
	}
}
