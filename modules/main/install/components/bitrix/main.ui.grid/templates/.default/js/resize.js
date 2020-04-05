;(function() {
	'use strict';

	BX.namespace('BX.Grid');

	BX.Grid.Resize = function(parent)
	{
		this.parent = null;
		this.lastRegisterButtons = null;
		this.init(parent);
	};

	BX.Grid.Resize.prototype = {
		init: function(parent)
		{
			this.parent = parent;

			BX.addCustomEvent(window, 'Grid::updated', BX.proxy(this.registerTableButtons, this));
			BX.addCustomEvent(window, 'Grid::headerUpdated', BX.proxy(this.registerPinnedTableButtons, this));

			this.registerTableButtons();
			this.registerPinnedTableButtons();
		},

		destroy: function()
		{
			BX.removeCustomEvent(window, 'Grid::updated', BX.proxy(this.registerTableButtons, this));
			BX.removeCustomEvent(window, 'Grid::headerUpdated', BX.proxy(this.registerPinnedTableButtons, this));
			BX.type.isArray(this.lastRegisterButtons) && this.lastRegisterButtons.forEach(jsDD.unregisterObject);
			(this.getButtons() || []).forEach(jsDD.unregisterObject);
		},

		registerTableButtons: function()
		{
			(this.getButtons() || []).forEach(this.register, this);
			this.registerPinnedTableButtons();
		},

		register: function(item)
		{
			if (BX.type.isDomNode(item))
			{
				item.onbxdragstart = BX.delegate(this._onDragStart, this);
				item.onbxdragstop = BX.delegate(this._onDragEnd, this);
				item.onbxdrag = BX.delegate(this._onDrag, this);
				jsDD.registerObject(item);
			}
		},

		registerPinnedTableButtons: function()
		{
			if (this.parent.getParam('ALLOW_PIN_HEADER'))
			{
				var pinnedTableButtons = this.getPinnedTableButtons();

				if (BX.type.isArray(this.lastRegisterButtons) && this.lastRegisterButtons.length)
				{
					this.lastRegisterButtons.forEach(jsDD.unregisterObject);
				}

				this.lastRegisterButtons = pinnedTableButtons;

				(this.getPinnedTableButtons() || []).forEach(this.register, this);
			}
		},

		getButtons: function()
		{
			return BX.Grid.Utils.getByClass(this.parent.getRows().getHeadFirstChild().getNode(), this.parent.settings.get('classResizeButton'));
		},

		getPinnedTableButtons: function()
		{
			return BX.Grid.Utils.getByClass(this.parent.getPinHeader().getFixedTable(), this.parent.settings.get('classResizeButton'));
		},

		_onDragStart: function()
		{
			var cell = BX.findParent(jsDD.current_node, {className: this.parent.settings.get('classHeadCell')});
			var cells = this.parent.getRows().getHeadFirstChild().getCells();
			var cellsKeys = Object.keys(cells);
			var cellContainer;

			this.__overlay = BX.create('div', {props: {className: 'main-grid-cell-overlay'}});
			BX.append(this.__overlay, cell);
			this.__resizeCell = cell.cellIndex;

			cellsKeys.forEach(function(key) {
				if (BX.hasClass(cells[key], 'main-grid-special-empty'))
				{
					BX.style(cells[key], 'width', '100%');
				}
				else
				{
					BX.width(cells[key], BX.width(cells[key]));
					cellContainer = BX.firstChild(cells[key]);
					BX.width(cellContainer, BX.width(cells[key]));
				}
			});
		},

		_onDrag: function(x)
		{
			var table = this.parent.getTable();
			var fixedTable = this.parent.getParam('ALLOW_PIN_HEADER') ? this.parent.getPinHeader().getFixedTable() : null;
			var cell = table.rows[0].cells[this.__resizeCell];
			var fixedCell, fixedCellContainer;

			var cpos = BX.pos(cell);
			var cellAttrWidth = parseFloat(cell.style.width);
			var sX;

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
				var fixedCells = this.parent.getAllRows()[0]
					.querySelectorAll('.main-grid-fixed-column').length;
				var column = this.parent.getColumnByIndex(this.__resizeCell - fixedCells);

				// Resize current column
				column.forEach(function(item) {
					item.style.width = x+'px';
					item.style.minWidth = x+'px';
					item.style.maxWidth = x+'px';
				});

				// Resize false columns
				if (column[0].classList.contains('main-grid-fixed-column'))
				{
					column = this.parent.getColumnByIndex(this.__resizeCell - fixedCells + 1);

					column.forEach(function(item) {
						item.style.width = x+'px';
						item.style.minWidth = x+'px';
						item.style.maxWidth = x+'px';
					});
				}

				this.parent.adjustFixedColumnsPosition();
				this.parent.adjustFadePosition(this.parent.getFadeOffset());

				if (BX.type.isDomNode(fixedTable) && BX.type.isDomNode(fixedTable.rows[0]))
				{
					fixedCell = fixedTable.rows[0].cells[this.__resizeCell];
					fixedCellContainer = BX.firstChild(fixedCell);
					fixedCellContainer.style.width = x+'px';
					fixedCellContainer.style.minWidth = x+'px';
					fixedCell.style.width = x+'px';
					fixedCell.style.minWidth = x+'px';
				}
			}

			BX.onCustomEvent(window, 'Grid::columnResize', []);
		},

		_onDragEnd: function()
		{
			this.saveSizes();
		},

		getColumnSizes: function()
		{
			var cells = this.parent.getRows().getHeadFirstChild().getCells();
			var columns = {};
			var name;

			[].forEach.call(cells, function(current) {
				name = BX.data(current, 'name');

				if (BX.type.isNotEmptyString(name))
				{
					columns[name] = BX.width(current);
				}
			}, this);

			return columns;
		},

		saveSizes: function()
		{
			this.parent.getUserOptions().setColumnSizes(this.getColumnSizes(), 1);
		}
	};
})();