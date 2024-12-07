(function() {
	'use strict';

	BX.namespace('BX.Grid');

	BX.Grid.Resize = function(parent)
	{
		this.parent = null;
		this.lastRegisterButtons = null;
		this.init(parent);
	};

	BX.Grid.Resize.prototype = {
		init(parent)
		{
			this.parent = parent;

			BX.addCustomEvent(window, 'Grid::updated', BX.proxy(this.registerTableButtons, this));
			BX.addCustomEvent(window, 'Grid::headerUpdated', BX.proxy(this.registerPinnedTableButtons, this));

			this.registerTableButtons();
			this.registerPinnedTableButtons();
		},

		destroy()
		{
			BX.removeCustomEvent(window, 'Grid::updated', BX.proxy(this.registerTableButtons, this));
			BX.removeCustomEvent(window, 'Grid::headerUpdated', BX.proxy(this.registerPinnedTableButtons, this));
			BX.type.isArray(this.lastRegisterButtons) && this.lastRegisterButtons.forEach(jsDD.unregisterObject);
			(this.getButtons() || []).forEach(jsDD.unregisterObject);
		},

		registerTableButtons()
		{
			(this.getButtons() || []).forEach(this.register, this);
			this.registerPinnedTableButtons();
		},

		register(item)
		{
			if (BX.type.isDomNode(item))
			{
				item.onbxdragstart = BX.delegate(this._onDragStart, this);
				item.onbxdragstop = BX.delegate(this._onDragEnd, this);
				item.onbxdrag = BX.delegate(this._onDrag, this);
				jsDD.registerObject(item);
			}
		},

		registerPinnedTableButtons()
		{
			if (this.parent.getParam('ALLOW_PIN_HEADER'))
			{
				const pinnedTableButtons = this.getPinnedTableButtons();

				if (BX.type.isArray(this.lastRegisterButtons) && this.lastRegisterButtons.length > 0)
				{
					this.lastRegisterButtons.forEach(jsDD.unregisterObject);
				}

				this.lastRegisterButtons = pinnedTableButtons;

				(this.getPinnedTableButtons() || []).forEach(this.register, this);
			}
		},

		getButtons()
		{
			return BX.Grid.Utils.getByClass(this.parent.getRows().getHeadFirstChild().getNode(), this.parent.settings.get('classResizeButton'));
		},

		getPinnedTableButtons()
		{
			return BX.Grid.Utils.getByClass(this.parent.getPinHeader().getFixedTable(), this.parent.settings.get('classResizeButton'));
		},

		_onDragStart()
		{
			const cell = BX.findParent(jsDD.current_node, { className: this.parent.settings.get('classHeadCell') });
			const cells = this.parent.getRows().getHeadFirstChild().getCells();
			const cellsKeys = Object.keys(cells);
			let cellContainer;

			this.__overlay = BX.create('div', { props: { className: 'main-grid-cell-overlay' } });
			BX.append(this.__overlay, cell);
			this.__resizeCell = cell.cellIndex;

			cellsKeys.forEach((key) => {
				if (!BX.hasClass(cells[key], 'main-grid-special-empty'))
				{
					let width = BX.width(cells[key]);

					if (key > 0)
					{
						width -= parseInt(BX.style(cells[key], 'border-left-width'));
						width -= parseInt(BX.style(cells[key], 'border-right-width'));
					}

					BX.width(cells[key], width);
					cellContainer = BX.firstChild(cells[key]);
					BX.width(cellContainer, width);
				}
			});
		},

		_onDrag(x)
		{
			const table = this.parent.getTable();
			const fixedTable = this.parent.getParam('ALLOW_PIN_HEADER') ? this.parent.getPinHeader().getFixedTable() : null;
			const cell = table.rows[0].cells[this.__resizeCell];
			let fixedCell; let
				fixedCellContainer;

			const cpos = BX.pos(cell);
			const cellAttrWidth = parseFloat(cell.style.width);
			let sX;

			x -= cpos.left;
			sX = x;

			if (cpos.width > cellAttrWidth)
			{
				x = cpos.width;
			}

			x = sX > x ? sX : x;

			x = Math.max(x, 80);

			if (x !== cpos.width)
			{
				const fixedCells = this.parent.getAllRows()[0]
					.querySelectorAll('.main-grid-fixed-column').length;
				let column = this.parent.getColumnByIndex(this.__resizeCell - fixedCells);

				// Resize current column
				column.forEach((item) => {
					item.style.width = `${x}px`;
					item.style.minWidth = `${x}px`;
					item.style.maxWidth = `${x}px`;
					BX.Dom.style(item.firstElementChild, 'width', `${x}px`);
				});

				// Resize false columns
				if (column[0].classList.contains('main-grid-fixed-column'))
				{
					column = this.parent.getColumnByIndex(this.__resizeCell - fixedCells + 1);

					column.forEach((item) => {
						item.style.width = `${x}px`;
						item.style.minWidth = `${x}px`;
						item.style.maxWidth = `${x}px`;
					});
				}

				this.parent.adjustFixedColumnsPosition();
				this.parent.adjustFadePosition(this.parent.getFadeOffset());

				if (BX.type.isDomNode(fixedTable) && BX.type.isDomNode(fixedTable.rows[0]))
				{
					fixedCell = fixedTable.rows[0].cells[this.__resizeCell];
					fixedCellContainer = BX.firstChild(fixedCell);
					fixedCellContainer.style.width = `${x}px`;
					fixedCellContainer.style.minWidth = `${x}px`;
					fixedCell.style.width = `${x}px`;
					fixedCell.style.minWidth = `${x}px`;
				}
			}

			BX.onCustomEvent(window, 'Grid::columnResize', []);
		},

		_onDragEnd()
		{
			this.saveSizes();
			const cell = BX.findParent(jsDD.current_node, { className: this.parent.settings.get('classHeadCell') });
			const overlay = cell.querySelector('.main-grid-cell-overlay');
			if (overlay)
			{
				BX.Dom.remove(overlay);
			}
		},

		getColumnSizes()
		{
			const cells = this.parent.getRows().getHeadFirstChild().getCells();
			const columns = {};
			let name;

			[].forEach.call(cells, (current) => {
				name = BX.data(current, 'name');

				if (BX.type.isNotEmptyString(name))
				{
					columns[name] = BX.width(current);
				}
			}, this);

			return columns;
		},

		saveSizes()
		{
			this.parent.getUserOptions().setColumnSizes(this.getColumnSizes(), 1);
		},
	};
})();
