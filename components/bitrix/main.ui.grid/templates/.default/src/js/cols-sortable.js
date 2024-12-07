(function() {
	'use strict';

	BX.namespace('BX.Grid');

	/**
	 * BX.Grid.ColsSortable
	 * @param {BX.Main.grid} parent
	 * @constructor
	 */
	BX.Grid.ColsSortable = function(parent)
	{
		this.parent = null;
		this.dragItem = null;
		this.targetItem = null;
		this.rowsList = null;
		this.colsList = null;
		this.dragRect = null;
		this.offset = null;
		this.startDragOffset = null;
		this.dragColumn = null;
		this.targetColumn = null;
		this.isDrag = null;
		this.init(parent);
	};

	BX.Grid.ColsSortable.prototype = {
		init(parent)
		{
			this.parent = parent;
			this.colsList = this.getColsList();
			this.rowsList = this.getRowsList();

			if (!this.inited)
			{
				this.inited = true;
				BX.addCustomEvent('Grid::updated', BX.proxy(this.reinit, this));
				BX.addCustomEvent('Grid::headerUpdated', BX.proxy(this.reinit, this));
			}

			this.registerObjects();
		},

		destroy()
		{
			BX.removeCustomEvent('Grid::updated', BX.proxy(this.reinit, this));
			this.unregisterObjects();
		},

		reinit()
		{
			this.unregisterObjects();
			this.reset();
			this.init(this.parent);
		},

		reset()
		{
			this.dragItem = null;
			this.targetItem = null;
			this.rowsList = null;
			this.colsList = null;
			this.dragRect = null;
			this.offset = null;
			this.startDragOffset = null;
			this.dragColumn = null;
			this.targetColumn = null;
			this.isDrag = null;
			this.fixedTableColsList = null;
		},

		isActive()
		{
			return this.isDrag;
		},

		registerObjects()
		{
			this.unregisterObjects();
			this.getColsList().forEach(this.register, this);
			this.getFixedHeaderColsList().forEach(this.register, this);
		},

		unregisterObjects()
		{
			this.getColsList().forEach(this.unregister, this);
			this.getFixedHeaderColsList().forEach(this.unregister, this);
		},

		unregister(column)
		{
			jsDD.unregisterObject(column);
		},

		register(column)
		{
			column.onbxdragstart = BX.proxy(this._onDragStart, this);
			column.onbxdrag = BX.proxy(this._onDrag, this);
			column.onbxdragstop = BX.proxy(this._onDragEnd, this);
			jsDD.registerObject(column);
		},

		getColsList()
		{
			if (!this.colsList)
			{
				this.colsList = BX.Grid.Utils.getByTag(this.parent.getRows().getHeadFirstChild().getNode(), 'th');
				this.colsList = this.colsList.filter(function(current) {
					return !this.isStatic(current);
				}, this);
			}

			return this.colsList;
		},

		getFixedHeaderColsList()
		{
			if (!this.fixedTableColsList && this.parent.getParam('ALLOW_PIN_HEADER'))
			{
				this.fixedTableColsList = BX.Grid.Utils.getByTag(this.parent.getPinHeader().getFixedTable(), 'th');
				this.fixedTableColsList = this.fixedTableColsList.filter(function(current) {
					return !this.isStatic(current);
				}, this);
			}

			return this.fixedTableColsList || [];
		},

		getRowsList()
		{
			let rowsList = this.parent.getRows().getSourceRows();

			if (this.parent.getParam('ALLOW_PIN_HEADER'))
			{
				rowsList = rowsList.concat(BX.Grid.Utils.getByTag(this.parent.getPinHeader().getFixedTable(), 'tr'));
			}

			return rowsList;
		},

		isStatic(item)
		{
			return (
				BX.hasClass(item, this.parent.settings.get('classCellStatic'))
				&& !BX.hasClass(item, 'main-grid-fixed-column')
			);
		},

		getDragOffset()
		{
			const offset = this.parent.getScrollContainer().scrollLeft - this.startScrollOffset;

			return ((jsDD.x - this.startDragOffset - this.dragRect.left) + offset);
		},

		getColumn(cell)
		{
			let column = [];

			if (cell instanceof HTMLTableCellElement)
			{
				column = this.rowsList.map((row) => {
					return row.cells[cell.cellIndex];
				});
			}

			return column;
		},

		_onDragStart()
		{
			if (this.parent.getParam('ALLOW_PIN_HEADER') && this.parent.getPinHeader().isPinned())
			{
				this.colsList = this.getFixedHeaderColsList();
			}
			else
			{
				this.colsList = this.getColsList();
			}

			this.startScrollOffset = this.parent.getScrollContainer().scrollLeft;
			this.isDrag = true;
			this.dragItem = jsDD.current_node;
			this.dragRect = this.dragItem.getBoundingClientRect();
			this.offset = Math.ceil(this.dragRect.width);
			this.startDragOffset = jsDD.start_x - this.dragRect.left;
			this.dragColumn = this.getColumn(this.dragItem);
			this.dragIndex = BX.Grid.Utils.getIndex(this.colsList, this.dragItem);
			this.parent.preventSortableClick = true;
		},

		isDragToRight(node, index)
		{
			const nodeClientRect = node.getBoundingClientRect();
			const nodeCenter = Math.ceil(nodeClientRect.left + (nodeClientRect.width / 2) + BX.scrollLeft(window));
			const dragIndex = this.dragIndex;
			const x = jsDD.x;

			return index > dragIndex && x > nodeCenter;
		},

		isDragToLeft(node, index)
		{
			const nodeClientRect = node.getBoundingClientRect();
			const nodeCenter = Math.ceil(nodeClientRect.left + (nodeClientRect.width / 2) + BX.scrollLeft(window));
			const dragIndex = this.dragIndex;
			const x = jsDD.x;

			return index < dragIndex && x < nodeCenter;
		},

		isDragToBack(node, index)
		{
			const nodeClientRect = node.getBoundingClientRect();
			const nodeCenter = Math.ceil(nodeClientRect.left + (nodeClientRect.width / 2) + BX.scrollLeft(window));
			const dragIndex = this.dragIndex;
			const x = jsDD.x;

			return (index > dragIndex && x < nodeCenter) || (index < dragIndex && x > nodeCenter);
		},

		isMovedToRight(node)
		{
			return node.style.transform === `translate3d(${-this.offset}px, 0px, 0px)`;
		},

		isMovedToLeft(node)
		{
			return node.style.transform === `translate3d(${this.offset}px, 0px, 0px)`;
		},

		isMoved(node)
		{
			return (node.style.transform !== 'translate3d(0px, 0px, 0px)' && node.style.transform !== '');
		},

		/**
		 * Moves grid column by offset
		 * @param {array} column - Array cells of column
		 * @param {int} offset - Pixels offset
		 * @param {int} [transition = 300] - Transition duration in milliseconds
		 */
		moveColumn(column, offset, transition)
		{
			transition = BX.type.isNumber(transition) ? transition : 300;
			BX.Grid.Utils.styleForEach(column, {
				transition: `${transition}ms`,
				transform: `translate3d(${offset}px, 0px, 0px)`,
			});
		},

		_onDrag()
		{
			this.dragOffset = this.getDragOffset();
			this.targetItem = this.targetItem || this.dragItem;
			this.targetColumn = this.targetColumn || this.dragColumn;

			const leftOffset = -this.offset;
			const rightOffset = this.offset;
			const defaultOffset = 0;
			const dragTransitionDuration = 0;

			this.moveColumn(this.dragColumn, this.dragOffset, dragTransitionDuration);

			[].forEach.call(this.colsList, function(current, index) {
				if (current && !current.classList.contains('main-grid-cell-static'))
				{
					if (this.isDragToRight(current, index) && !this.isMovedToRight(current))
					{
						this.targetColumn = this.getColumn(current);
						this.moveColumn(this.targetColumn, leftOffset);
					}

					if (this.isDragToLeft(current, index) && !this.isMovedToLeft(current))
					{
						this.targetColumn = this.getColumn(current);
						this.moveColumn(this.targetColumn, rightOffset);
					}

					if (this.isDragToBack(current, index) && this.isMoved(current))
					{
						this.targetColumn = this.getColumn(current);
						this.moveColumn(this.targetColumn, defaultOffset);
					}
				}
			}, this);
		},

		_onDragEnd()
		{
			[].forEach.call(this.dragColumn, function(current, index) {
				BX.Grid.Utils.collectionSort(current, this.targetColumn[index]);
			}, this);

			this.rowsList.forEach((current) => {
				BX.Grid.Utils.styleForEach(current.cells, {
					transition: '',
					transform: '',
				});
			});

			this.reinit();

			const columns = this.colsList.map((current) => {
				return BX.data(current, 'name');
			});

			this.parent.getUserOptions().setColumns(columns);
			BX.onCustomEvent(this.parent.getContainer(), 'Grid::columnMoved', [this.parent]);

			setTimeout(() => {
				this.parent.preventSortableClick = false;
			}, 10);
		},
	};
})();
