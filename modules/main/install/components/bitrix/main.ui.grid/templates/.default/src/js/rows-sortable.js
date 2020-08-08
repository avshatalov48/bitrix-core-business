;(function() {
	'use strict';

	BX.namespace('BX.Grid');

	BX.Grid.RowDragEvent = function(eventName)
	{
		this.allowMoveRow = true;
		this.allowInsertBeforeTarget = true;
		this.dragItem = null;
		this.targetItem = null;
		this.eventName = !!eventName ? eventName : '';
		this.errorMessage = '';
	};

	BX.Grid.RowDragEvent.prototype = {
		allowMove: function() { this.allowMoveRow = true; this.errorMessage = ''; },
		allowInsertBefore: function() { this.allowInsertBeforeTarget = true; },
		disallowMove: function(errorMessage) { this.allowMoveRow = false; this.errorMessage = errorMessage || ''; },
		disallowInsertBefore: function() { this.allowInsertBeforeTarget = false; },
		getDragItem: function() { return this.dragItem; },
		getTargetItem: function() { return this.targetItem; },
		getEventName: function() { return this.eventName; },
		setDragItem: function(item) { return this.dragItem = item; },
		setTargetItem: function(item) { return this.targetItem = item; },
		setEventName: function(name) { return this.eventName = name; },
		isAllowedMove: function() { return this.allowMoveRow; },
		isAllowedInsertBefore: function() { return this.allowInsertBeforeTarget; },
		getErrorMessage: function() { return this.errorMessage; }
	};


	BX.Grid.RowsSortable = function(parent)
	{
		this.parent = null;
		this.list = null;
		this.setDefaultProps();
		this.init(parent);
	};

	BX.Grid.RowsSortable.prototype = {
		init: function(parent)
		{
			this.parent = parent;
			this.list = this.getList();
			this.prepareListItems();
			jsDD.Enable();

			if (!this.inited)
			{
				this.inited = true;
				this.onscrollDebounceHandler = BX.debounce(this._onWindowScroll, 300, this);
				BX.addCustomEvent('Grid::thereEditedRows', BX.proxy(this.disable, this));
				BX.addCustomEvent('Grid::noEditedRows', BX.proxy(this.enable, this));
				document.addEventListener('scroll', this.onscrollDebounceHandler, BX.Grid.Utils.listenerParams({passive: true}));
			}
		},

		destroy: function()
		{
			BX.removeCustomEvent('Grid::thereEditedRows', BX.proxy(this.disable, this));
			BX.removeCustomEvent('Grid::noEditedRows', BX.proxy(this.enable, this));
			document.removeEventListener('scroll', this.onscrollDebounceHandler, BX.Grid.Utils.listenerParams({passive: true}));
			this.unregisterObjects();
		},

		_onWindowScroll: function()
		{
			this.windowScrollTop = BX.scrollTop(window);
			this.rowsRectList = null;
		},

		disable: function()
		{
			this.unregisterObjects();
		},

		enable: function()
		{
			this.reinit();
		},

		reinit: function()
		{
			this.unregisterObjects();
			this.setDefaultProps();
			this.init(this.parent);
		},

		getList: function()
		{
			return this.parent.getRows().getSourceBodyChild();
		},

		unregisterObjects: function()
		{
			this.list.forEach(this.unregister, this);
		},

		prepareListItems: function()
		{
			this.list.forEach(this.register, this);
		},

		register: function(row)
		{
			var Rows = this.parent.getRows();
			var rowInstance = Rows.get(row);
			if (rowInstance && rowInstance.isDraggable())
			{
				row.onbxdragstart = BX.delegate(this._onDragStart, this);
				row.onbxdrag = BX.delegate(this._onDrag, this);
				row.onbxdragstop = BX.delegate(this._onDragEnd, this);
				jsDD.registerObject(row);
			}
		},

		unregister: function(row)
		{
			jsDD.unregisterObject(row);
		},

		getIndex: function(item)
		{
			return BX.Grid.Utils.getIndex(this.list, item);
		},

		calcOffset: function()
		{
			var offset = this.dragRect.height;

			if (this.additionalDragItems.length)
			{
				this.additionalDragItems.forEach(function(row) {
					offset += row.clientHeight;
				});
			}

			return offset;
		},

		getTheadCells: function(sourceCells)
		{
			return [].map.call(sourceCells, function(cell, index) {
				return {
					block: '',
					tag: 'th',
					attrs: {
						style: 'width: '+BX.width(sourceCells[index])+'px;'
					}
				}
			});
		},

		createFake: function()
		{
			var content = [];
			this.cloneDragItem = BX.clone(this.dragItem);
			this.cloneDragAdditionalDragItems = [];
			this.cloneDragAdditionalDragItemRows = [];

			var theadCellsDecl = this.getTheadCells(this.dragItem.cells);
			content.push(this.cloneDragItem);

			this.additionalDragItems.forEach(function(row) {
				var cloneRow = BX.clone(row);
				content.push(cloneRow);
				this.cloneDragAdditionalDragItems.push(cloneRow);
				this.cloneDragAdditionalDragItemRows.push(new BX.Grid.Row(this.parent, cloneRow));
			}, this);

			var tableWidth = BX.width(this.parent.getTable());

			this.fake = BX.decl({
				block: 'main-grid-fake-container',
				attrs: {
					style: 'position: absolute; top: '+this.getDragStartRect().top+'px; width: ' + tableWidth + 'px'
				},
				content: {
					block: 'main-grid-table',
					mix: 'main-grid-table-fake',
					tag: 'table',
					attrs: {
						style: 'width: ' + tableWidth + 'px'
					},
					content: [
						{
							block: 'main-grid-header',
							tag: 'thead',
							content: {
								block: 'main-grid-row-head',
								tag: 'tr',
								content: theadCellsDecl
							}
						},
						{
							block: '',
							tag: 'tbody',
							content: content
						}
					]
				}
			});

			BX.insertAfter(this.fake, this.parent.getTable());

			this.cloneDragItem = new BX.Grid.Row(this.parent, this.cloneDragItem);
			return this.fake;
		},

		getDragStartRect: function()
		{
			return BX.pos(this.dragItem, this.parent.getTable());
		},

		_onDragStart: function()
		{
			this.moved = false;
			this.dragItem = jsDD.current_node;
			this.targetItem = this.dragItem;
			this.additionalDragItems = this.getAdditionalDragItems(this.dragItem);
			this.dragIndex = this.getIndex(this.dragItem);
			this.dragRect = this.getRowRect(this.dragItem, this.dragIndex);
			this.offset = this.calcOffset();
			this.dragStartOffset = (jsDD.start_y - this.dragRect.top);
			this.dragEvent = new BX.Grid.RowDragEvent();
			this.dragEvent.setEventName('BX.Main.grid:rowDragStart');
			this.dragEvent.setDragItem(this.dragItem);
			this.dragEvent.setTargetItem(this.targetItem);
			this.dragEvent.allowInsertBefore();

			var dragRow = this.parent.getRows().get(this.dragItem);
			this.startDragDepth = dragRow.getDepth();
			this.startDragParentId = dragRow.getParentId();

			this.createFake();

			BX.addClass(this.parent.getContainer(), this.parent.settings.get('classOnDrag'));
			BX.addClass(this.dragItem, this.parent.settings.get('classDragActive'));
			BX.onCustomEvent(window, 'BX.Main.grid:rowDragStart', [this.dragEvent, this.parent]);
		},

		getAdditionalDragItems: function(dragItem)
		{
			var Rows = this.parent.getRows();
			return Rows.getRowsByParentId(Rows.get(dragItem).getId(), true).map(function(row) {
				return row.getNode();
			});
		},


		/**
		 * @param {?HTMLElement} row
		 * @param {int} offset
		 * @param {?int} [transition] css transition-duration in ms
		 */
		moveRow: function(row, offset, transition)
		{
			if (!!row)
			{
				var transitionDuration = BX.type.isNumber(transition) ? transition : 300;
				row.style.transition = transitionDuration + 'ms';
				row.style.transform = 'translate3d(0px, '+offset+'px, 0px)';
			}
		},

		getDragOffset: function()
		{
			return jsDD.y - this.dragRect.top - this.dragStartOffset;
		},

		getWindowScrollTop: function()
		{
			if (this.windowScrollTop === null)
			{
				this.windowScrollTop = BX.scrollTop(window);
			}

			return this.windowScrollTop;
		},

		getSortOffset: function()
		{
			return jsDD.y;
		},

		getRowRect: function(row, index)
		{
			if (!this.rowsRectList)
			{
				this.rowsRectList = {};

				this.list.forEach(function(current, i) {
					this.rowsRectList[i] = current.getBoundingClientRect();
				}, this);
			}

			return this.rowsRectList[index];
		},

		getRowCenter: function(row, index)
		{
			var rect = this.getRowRect(row, index);
			return rect.top + this.getWindowScrollTop() + (rect.height / 2);
		},

		isDragToBottom: function(row, index)
		{
			var rowCenter = this.getRowCenter(row, index);
			var sortOffset = this.getSortOffset();
			return index > this.dragIndex && rowCenter < sortOffset;
		},

		isMovedToBottom: function(row)
		{
			return row.style.transform === 'translate3d(0px, '+(-this.offset)+'px, 0px)';
		},

		isDragToTop: function(row, index)
		{
			var rowCenter = this.getRowCenter(row, index);
			var sortOffset = this.getSortOffset();
			return index < this.dragIndex && rowCenter > sortOffset;
		},

		isMovedToTop: function(row)
		{
			return row.style.transform === 'translate3d(0px, '+this.offset+'px, 0px)';
		},

		isDragToBack: function(row, index)
		{
			var rowCenter = this.getRowCenter(row, index);
			var dragIndex = this.dragIndex;
			var y = jsDD.y;

			return (index > dragIndex && y < rowCenter) || (index < dragIndex && y > rowCenter);
		},

		isMoved: function(row)
		{
			return (row.style.transform !== 'translate3d(0px, 0px, 0px)' && row.style.transform !== '');
		},

		_onDrag: function()
		{
			var dragTransitionDuration = 0;
			var defaultOffset = 0;

			this.moveRow(this.dragItem, this.getDragOffset(), dragTransitionDuration);
			this.moveRow(this.fake, this.getDragOffset(), dragTransitionDuration);
			BX.Grid.Utils.styleForEach(this.additionalDragItems, {
				'transition': dragTransitionDuration + 'ms',
				'transform': 'translate3d(0px, '+(this.getDragOffset())+'px, 0px)'
			});

			this.list.forEach(function(current, index) {
				if (!!current && current !== this.dragItem && this.additionalDragItems.indexOf(current) === -1)
				{
					if (this.isDragToTop(current, index) && !this.isMovedToTop(current))
					{
						this.targetItem = current;
						this.moveRow(current, this.offset);
						this.dragEvent.setEventName('BX.Main.grid:rowDragMove');
						this.dragEvent.setTargetItem(this.targetItem);
						BX.onCustomEvent(window, 'BX.Main.grid:rowDragMove', [this.dragEvent, this.parent]);
						this.checkError(this.dragEvent);
						this.updateProperties(this.dragItem, this.targetItem);
						this.isDragetToTop = true;
						this.moved = true;
					}

					if (this.isDragToBottom(current, index) && !this.isMovedToBottom(current))
					{
						this.targetItem = this.findNextVisible(this.list, index);
						this.moveRow(current, -this.offset);
						this.dragEvent.setEventName('BX.Main.grid:rowDragMove');
						this.dragEvent.setTargetItem(this.targetItem);
						BX.onCustomEvent(window, 'BX.Main.grid:rowDragMove', [this.dragEvent, this.parent]);
						this.checkError(this.dragEvent);
						this.updateProperties(this.dragItem, this.targetItem);
						this.isDragetToTop = false;

						if (this.targetItem)
						{
							this.moved = true;
						}
					}

					if (this.isDragToBack(current, index) && this.isMoved(current))
					{
						this.moveRow(current, defaultOffset);
						this.targetItem = current;

						if (this.isDragetToTop)
						{
							this.targetItem = this.findNextVisible(this.list, index);
						}

						this.moved = true;

						this.dragEvent.setEventName('BX.Main.grid:rowDragMove');
						this.dragEvent.setTargetItem(this.targetItem);

						BX.onCustomEvent(window, 'BX.Main.grid:rowDragMove', [this.dragEvent, this.parent]);
						this.checkError(this.dragEvent);
						this.updateProperties(this.dragItem, this.targetItem);
					}
				}
			}, this);
		},

		createError: function(target, message)
		{
			var error = BX.decl({
				block: 'main-grid-error',
				content: !!message ? message : ''
			});

			!!target && target.appendChild(error);

			setTimeout(function() {
				BX.addClass(error, 'main-grid-error-show');
			}, 0);

			return error;
		},

		checkError: function(event)
		{
			if (!event.isAllowedMove() && !this.error)
			{
				this.error = this.createError(this.fake, event.getErrorMessage());
			}

			if (event.isAllowedMove() && this.error)
			{
				BX.remove(this.error);
				this.error = null;
			}
		},

		findNextVisible: function(list, index)
		{
			var result = null;
			var Rows = this.parent.getRows();

			list.forEach(function(item, currentIndex) {
				if (!result && currentIndex > index)
				{
					var row = Rows.get(item);
					if (row.isShown())
					{
						result = item;
					}
				}
			});

			return result;
		},


		/**
		 * Updates row properties
		 * @param {?HTMLTableRowElement} dragItem
		 * @param {?HTMLTableRowElement} targetItem
		 */
		updateProperties: function(dragItem, targetItem)
		{
			var Rows = this.parent.getRows();
			var dragRow = Rows.get(dragItem);
			var depth = 0;
			var parentId = 0;

			if (!!targetItem)
			{
				var targetRow = Rows.get(targetItem);
				depth = targetRow.getDepth();
				parentId = targetRow.getParentId();
			}

			dragRow.setDepth(depth);
			dragRow.setParentId(parentId);

			this.cloneDragItem.setDepth(depth);
			this.cloneDragAdditionalDragItemRows.forEach(function(row, index) {
				row.setDepth(BX.data(this.additionalDragItems[index], 'depth'));
			}, this);
		},


		resetDragProperties: function()
		{
			var dragRow = this.parent.getRows().get(this.dragItem);
			dragRow.setDepth(this.startDragDepth);
			dragRow.setParentId(this.startDragParentId);
		},

		_onDragOver: function() {},

		_onDragLeave: function() {},

		_onDragEnd: function()
		{
			BX.onCustomEvent(window, 'BX.Main.grid:rowDragEnd', [this.dragEvent, this.parent]);

			BX.removeClass(this.parent.getContainer(), this.parent.settings.get('classOnDrag'));
			BX.removeClass(this.dragItem, this.parent.settings.get('classDragActive'));

			BX.Grid.Utils.styleForEach(this.list, {'transition': '', 'transform': ''});

			if (this.dragEvent.isAllowedMove())
			{
				this.sortRows(this.dragItem, this.targetItem);
				this.sortAdditionalDragItems(this.dragItem, this.additionalDragItems);

				this.list = this.getList();
				this.parent.getRows().reset();

				var dragItem = this.parent.getRows().get(this.dragItem);
				var ids = this.parent.getRows().getBodyChild().map(function(row) {
					return row.getId();
				});

				this.saveRowsSort(ids);
				BX.onCustomEvent(window, 'Grid::rowMoved', [ids, dragItem, this.parent]);
			}
			else
			{
				this.resetDragProperties();
			}

			BX.remove(this.fake);

			this.setDefaultProps();
		},

		sortAdditionalDragItems: function(dragItem, additional)
		{
			additional.reduce(function(prev, current) {
				!!current && BX.insertAfter(current, prev);
				return current;
			}, dragItem);
		},

		sortRows: function(current, target)
		{
			if (!!target)
			{
				target.parentNode.insertBefore(current, target);
			}
			else if (this.moved)
			{
				current.parentNode.appendChild(current);
			}
		},

		saveRowsSort: function(rows)
		{
			var data = {
				ids: rows,
				action: this.parent.getUserOptions().getAction('GRID_SAVE_ROWS_SORT')
			};

			this.parent.getData().request(null, 'POST', data);
		},

		setDefaultProps: function()
		{
			this.moved = false;
			this.dragItem = null;
			this.targetItem = null;
			this.dragRect = null;
			this.dragIndex = null;
			this.offset = null;
			this.realX = null;
			this.realY = null;
			this.dragStartOffset = null;
			this.windowScrollTop = null;
			this.rowsRectList = null;
			this.error = false;
		}
	};
})();