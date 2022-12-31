import {Dom, Type} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events';

import {Pin} from './pin';

import './css/grid.css';

export class Grid
{
	static get class()
	{
		return {
			highlighted: 'sonet-ui-grid-row-highlighted',
		};
	}

	constructor(options)
	{
		this.grid = BX.Main.gridManager.getInstanceById(options.id);
		this.sort = options.sort;
		this.pageSize = parseInt(options.pageSize);
		this.stub = options.gridStub;

		this.items = new Map();
		this.fillItems(options.items);

		this.pinController = new Pin({
			gridInstance: this.getGrid(),
		});

		this.init();
		this.bindEvents();
	}

	init()
	{
	}

	bindEvents()
	{
		EventEmitter.subscribe('BX.Main.grid:sort', this.onColumnSort.bind(this));
	}

	onColumnSort(event: BaseEvent)
	{
		const data = event.getData();
		const grid = data[1];
		const column = data[0];

		if (grid === this.getGrid())
		{
			this.sort = {};
			this.sort[column.sort_by] = column.sort_order;
		}
	}

	getGrid()
	{
		return this.grid;
	}

	getPinController()
	{
		return this.pinController;
	}

	getSort()
	{
		return this.sort;
	}

	addRow(id, data, params)
	{
		const options = {
			id: id,
			columns: data.columns,
			actions: data.actions,
			cellActions: data.cellActions,
//			counters: data.counters,
		};

		const moveParams = params.moveParams || {};

		if (moveParams.rowBefore)
		{
			options.insertAfter = moveParams.rowBefore;
		}
		else if (moveParams.rowAfter)
		{
			options.insertBefore = moveParams.rowAfter;
		}
		else
		{
			options.append = true;
		}

		if (this.items.size > this.getCurrentPage() * this.pageSize)
		{
			const lastRowId = this.getLastRowId();

			this.removeItem(lastRowId);
			Dom.remove(this.getRowNodeById(lastRowId));
			this.showMoreButton();
		}

		this.hideStub();
		this.getRealtime().addRow(options);
		this.getPinController().colorPinnedRows();

		EventEmitter.emit('SocialNetwork.Projects.Grid:RowAdd', {id});
	}

	updateRow(id, data, params)
	{
		const row = this.getRowById(id);

		if (Type.isPlainObject(data))
		{
			if (!Type.isUndefined(data.columns))
			{
				row.setCellsContent(data.columns);
			}

			if (!Type.isUndefined(data.actions))
			{
				row.setActions(data.actions);
			}

			if (!Type.isUndefined(data.cellActions))
			{
				row.setCellActions(data.cellActions);
			}

			if (!Type.isUndefined(data.counters))
			{
				row.setCounters(data.counters);
			}
		}

		this.resetRows();
		this.moveRow(id, (params.moveParams || {}));
		this.highlightRow(id, (params.highlightParams || {}))
			.then(() => this.getPinController().colorPinnedRows(), () => {});

		this.getGrid().bindOnRowEvents();
	}

	resetRows()
	{
		this.getRows().reset();
	}

	removeRow(rowId)
	{
		if (!this.isRowExist(rowId))
		{
			return;
		}

		this.removeItem(rowId);
		this.grid.removeRow(rowId);
	}

	moveRow(rowId, params): void
	{
		if (params.skip)
		{
			return;
		}

		const rowBefore = params.rowBefore || 0;
		const rowAfter = params.rowAfter || 0;

		if (rowBefore)
		{
			this.getRows().insertAfter(rowId, rowBefore);
		}
		else if (rowAfter)
		{
			this.getRows().insertBefore(rowId, rowAfter);
		}
	}

	highlightRow(rowId, params): Promise
	{
		params = params || {};

		return new Promise((resolve, reject) => {
			if (!this.isRowExist(rowId))
			{
				reject();
				return;
			}

			if (params.skip)
			{
				resolve();
				return;
			}

			const node = this.getRowNodeById(rowId);
			const isPinned = Dom.hasClass(node, Pin.class.pinned);

			if (isPinned)
			{
				Dom.removeClass(node, Pin.class.pinned);
			}

			Dom.addClass(node, Grid.class.highlighted);
			setTimeout(() => {
				Dom.removeClass(node, Grid.class.highlighted);
				if (isPinned)
				{
					Dom.addClass(node, Pin.class.pinned);
				}
				resolve();
			}, 900);
		});
	}

	isRowExist(rowId)
	{
		return this.getRowById(rowId) !== null;
	}

	getRows()
	{
		return this.getGrid().getRows();
	}

	getRowById(rowId)
	{
		return this.getRows().getById(rowId);
	}

	getRowNodeById(id)
	{
		return this.getRowById(id).getNode();
	}

	getFirstRowId()
	{
		const firstRow = this.getRows().getBodyFirstChild();
		return (firstRow ? this.getRowProperty(firstRow, 'id') : 0);
	}

	getLastRowId()
	{
		const lastRow = this.getRows().getBodyLastChild();
		return (lastRow ? this.getRowProperty(lastRow, 'id') : 0);
	}

	getRowProperty(row, propertyName)
	{
		return BX.data(row.getNode(), propertyName);
	}

	getCurrentPage()
	{
		return this.getGrid().getCurrentPage();
	}

	fillItems(items)
	{
		Object.keys(items).forEach(id => this.addItem(id));
	}

	getItems()
	{
		return Array.from(this.items.keys());
	}

	hasItem(id)
	{
		return this.items.has(parseInt(id));
	}

	addItem(id)
	{
		this.items.set(parseInt(id));
	}

	removeItem(id)
	{
		this.items.delete(parseInt(id));
	}

	clearItems()
	{
		this.items.clear();
	}

	getRealtime()
	{
		return this.getGrid().getRealtime();
	}

	showStub()
	{
		if (this.stub)
		{
			this.getRealtime().showStub({
				content: this.stub,
			});
		}
	}

	hideStub()
	{
		this.getGrid().hideEmptyStub();
	}

	showMoreButton()
	{
		this.getGrid().getMoreButton().getNode().style.display = 'inline-block';
	}

	hideMoreButton()
	{
		this.getGrid().getMoreButton().getNode().style.display = 'none';
	}

}