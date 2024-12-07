(function() {
	'use strict';

	BX.namespace('BX.Grid');

	BX.Grid.RowDragEvent = function(eventName)
	{
		this.allowMoveRow = true;
		this.allowInsertBeforeTarget = true;
		this.dragItem = null;
		this.targetItem = null;
		this.eventName = eventName || '';
		this.errorMessage = '';
	};

	BX.Grid.RowDragEvent.prototype = {
		allowMove() { this.allowMoveRow = true; this.errorMessage = ''; },
		allowInsertBefore() { this.allowInsertBeforeTarget = true; },
		disallowMove(errorMessage) { this.allowMoveRow = false; this.errorMessage = errorMessage || ''; },
		disallowInsertBefore() { this.allowInsertBeforeTarget = false; },
		getDragItem() { return this.dragItem; },
		getTargetItem() { return this.targetItem; },
		getEventName() { return this.eventName; },
		setDragItem(item) { return this.dragItem = item; },
		setTargetItem(item) { return this.targetItem = item; },
		setEventName(name) { return this.eventName = name; },
		isAllowedMove() { return this.allowMoveRow; },
		isAllowedInsertBefore() { return this.allowInsertBeforeTarget; },
		getErrorMessage() { return this.errorMessage; },
	};

	BX.Grid.RowsSortable = function(parent)
	{
		this.parent = null;
		this.list = null;
		this.setDefaultProps();
		this.init(parent);
	};

	BX.Grid.RowsSortable.prototype = {
		init(parent)
		{
			this.parent = parent;
			this.list = this.getList();
			this.prepareListItems();
			jsDD.Enable();

			if (!this.inited)
			{
				this.inited = true;
				this.onscrollDebounceHandler = BX.debounce(this._onWindowScroll, 300, this);

				if (!this.parent.getParam('ALLOW_ROWS_SORT_IN_EDIT_MODE', false))
				{
					BX.addCustomEvent('Grid::thereEditedRows', BX.proxy(this.disable, this));
					BX.addCustomEvent('Grid::noEditedRows', BX.proxy(this.enable, this));
				}

				document.addEventListener('scroll', this.onscrollDebounceHandler, BX.Grid.Utils.listenerParams({ passive: true }));
			}
		},

		destroy()
		{
			if (!this.parent.getParam('ALLOW_ROWS_SORT_IN_EDIT_MODE', false))
			{
				BX.removeCustomEvent('Grid::thereEditedRows', BX.proxy(this.disable, this));
				BX.removeCustomEvent('Grid::noEditedRows', BX.proxy(this.enable, this));
			}

			document.removeEventListener('scroll', this.onscrollDebounceHandler, BX.Grid.Utils.listenerParams({ passive: true }));
			this.unregisterObjects();
		},

		_onWindowScroll()
		{
			this.windowScrollTop = BX.scrollTop(window);
			this.rowsRectList = null;
		},

		disable()
		{
			this.unregisterObjects();
		},

		enable()
		{
			this.reinit();
		},

		reinit()
		{
			this.unregisterObjects();
			this.setDefaultProps();
			this.init(this.parent);
		},

		getList()
		{
			return this.parent.getRows().getSourceBodyChild();
		},

		unregisterObjects()
		{
			this.list.forEach(this.unregister, this);
		},

		prepareListItems()
		{
			this.list.forEach(this.register, this);
		},

		register(row)
		{
			const Rows = this.parent.getRows();
			const rowInstance = Rows.get(row);
			if (rowInstance && rowInstance.isDraggable())
			{
				row.onbxdragstart = BX.delegate(this._onDragStart, this);
				row.onbxdrag = BX.delegate(this._onDrag, this);
				row.onbxdragstop = BX.delegate(this._onDragEnd, this);
				jsDD.registerObject(row);
			}
		},

		unregister(row)
		{
			jsDD.unregisterObject(row);
		},

		getIndex(item)
		{
			return BX.Grid.Utils.getIndex(this.list, item);
		},

		calcOffset()
		{
			let offset = this.dragRect.height;

			if (this.additionalDragItems.length > 0)
			{
				this.additionalDragItems.forEach((row) => {
					offset += row.clientHeight;
				});
			}

			return offset;
		},

		getTheadCells(sourceCells)
		{
			return [].map.call(sourceCells, (cell, index) => {
				return {
					block: '',
					tag: 'th',
					attrs: {
						style: `width: ${BX.width(sourceCells[index])}px;`,
					},
				};
			});
		},

		createFake()
		{
			const content = [];
			this.cloneDragItem = BX.clone(this.dragItem);
			this.cloneDragAdditionalDragItems = [];
			this.cloneDragAdditionalDragItemRows = [];

			const theadCellsDecl = this.getTheadCells(this.dragItem.cells);
			content.push(this.cloneDragItem);

			this.additionalDragItems.forEach(function(row) {
				const cloneRow = BX.clone(row);
				content.push(cloneRow);
				this.cloneDragAdditionalDragItems.push(cloneRow);
				this.cloneDragAdditionalDragItemRows.push(new BX.Grid.Row(this.parent, cloneRow));
			}, this);

			const tableWidth = BX.width(this.parent.getTable());

			this.fake = BX.decl({
				block: 'main-grid-fake-container',
				attrs: {
					style: `position: absolute; top: ${this.getDragStartRect().top}px; width: ${tableWidth}px`,
				},
				content: {
					block: 'main-grid-table',
					mix: 'main-grid-table-fake',
					tag: 'table',
					attrs: {
						style: `width: ${tableWidth}px`,
					},
					content: [
						{
							block: 'main-grid-header',
							tag: 'thead',
							content: {
								block: 'main-grid-row-head',
								tag: 'tr',
								content: theadCellsDecl,
							},
						},
						{
							block: '',
							tag: 'tbody',
							content,
						},
					],
				},
			});

			BX.insertAfter(this.fake, this.parent.getTable());

			this.cloneDragItem = new BX.Grid.Row(this.parent, this.cloneDragItem);

			return this.fake;
		},

		getDragStartRect()
		{
			return BX.pos(this.dragItem, this.parent.getTable());
		},

		_onDragStart()
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

			const dragRow = this.parent.getRows().get(this.dragItem);
			this.startDragDepth = dragRow.getDepth();
			this.startDragParentId = dragRow.getParentId();

			this.createFake();

			BX.addClass(this.parent.getContainer(), this.parent.settings.get('classOnDrag'));
			BX.addClass(this.dragItem, this.parent.settings.get('classDragActive'));
			BX.onCustomEvent(window, 'BX.Main.grid:rowDragStart', [this.dragEvent, this.parent]);
		},

		getAdditionalDragItems(dragItem)
		{
			const Rows = this.parent.getRows();

			return Rows.getRowsByParentId(Rows.get(dragItem).getId(), true).map((row) => {
				return row.getNode();
			});
		},

		/**
		 * @param {?HTMLElement} row
		 * @param {int} offset
		 * @param {?int} [transition] css transition-duration in ms
		 */
		moveRow(row, offset, transition)
		{
			if (row)
			{
				const transitionDuration = BX.type.isNumber(transition) ? transition : 300;
				row.style.transition = `${transitionDuration}ms`;
				row.style.transform = `translate3d(0px, ${offset}px, 0px)`;
			}
		},

		getDragOffset()
		{
			return jsDD.y - this.dragRect.top - this.dragStartOffset;
		},

		getWindowScrollTop()
		{
			if (this.windowScrollTop === null)
			{
				this.windowScrollTop = BX.scrollTop(window);
			}

			return this.windowScrollTop;
		},

		getSortOffset()
		{
			return jsDD.y;
		},

		getRowRect(row, index)
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

		getRowCenter(row, index)
		{
			const rect = this.getRowRect(row, index);

			return rect.top + this.getWindowScrollTop() + (rect.height / 2);
		},

		isDragToBottom(row, index)
		{
			const rowCenter = this.getRowCenter(row, index);
			const sortOffset = this.getSortOffset();

			return index > this.dragIndex && rowCenter < sortOffset;
		},

		isMovedToBottom(row)
		{
			return row.style.transform === `translate3d(0px, ${-this.offset}px, 0px)`;
		},

		isDragToTop(row, index)
		{
			const rowCenter = this.getRowCenter(row, index);
			const sortOffset = this.getSortOffset();

			return index < this.dragIndex && rowCenter > sortOffset;
		},

		isMovedToTop(row)
		{
			return row.style.transform === `translate3d(0px, ${this.offset}px, 0px)`;
		},

		isDragToBack(row, index)
		{
			const rowCenter = this.getRowCenter(row, index);
			const dragIndex = this.dragIndex;
			const y = jsDD.y;

			return (index > dragIndex && y < rowCenter) || (index < dragIndex && y > rowCenter);
		},

		isMoved(row)
		{
			return (row.style.transform !== 'translate3d(0px, 0px, 0px)' && row.style.transform !== '');
		},

		_onDrag()
		{
			const dragTransitionDuration = 0;
			const defaultOffset = 0;

			this.moveRow(this.dragItem, this.getDragOffset(), dragTransitionDuration);
			this.moveRow(this.fake, this.getDragOffset(), dragTransitionDuration);
			BX.Grid.Utils.styleForEach(this.additionalDragItems, {
				transition: `${dragTransitionDuration}ms`,
				transform: `translate3d(0px, ${this.getDragOffset()}px, 0px)`,
			});

			this.list.forEach(function(current, index) {
				if (Boolean(current) && current !== this.dragItem && !this.additionalDragItems.includes(current))
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

		createError(target, message)
		{
			const error = BX.decl({
				block: 'main-grid-error',
				content: message || '',
			});

			Boolean(target) && target.appendChild(error);

			setTimeout(() => {
				BX.addClass(error, 'main-grid-error-show');
			}, 0);

			return error;
		},

		checkError(event)
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

		findNextVisible(list, index)
		{
			let result = null;
			const Rows = this.parent.getRows();

			list.forEach((item, currentIndex) => {
				if (!result && currentIndex > index)
				{
					const row = Rows.get(item);
					if (row && row.isShown())
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
		updateProperties(dragItem, targetItem)
		{
			const Rows = this.parent.getRows();
			const dragRow = Rows.get(dragItem);
			let depth = 0;
			let parentId = 0;

			if (targetItem)
			{
				const targetRow = Rows.get(targetItem);
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

		resetDragProperties()
		{
			const dragRow = this.parent.getRows().get(this.dragItem);
			dragRow.setDepth(this.startDragDepth);
			dragRow.setParentId(this.startDragParentId);
		},

		_onDragOver() {},

		_onDragLeave() {},

		_onDragEnd()
		{
			BX.onCustomEvent(window, 'BX.Main.grid:rowDragEnd', [this.dragEvent, this.parent]);

			BX.removeClass(this.parent.getContainer(), this.parent.settings.get('classOnDrag'));
			BX.removeClass(this.dragItem, this.parent.settings.get('classDragActive'));

			BX.Grid.Utils.styleForEach(this.list, { transition: '', transform: '' });

			if (this.dragEvent.isAllowedMove())
			{
				this.sortRows(this.dragItem, this.targetItem);
				this.sortAdditionalDragItems(this.dragItem, this.additionalDragItems);

				this.list = this.getList();
				this.parent.getRows().reset();

				const dragItem = this.parent.getRows().get(this.dragItem);
				const ids = this.parent.getRows().getBodyChild().map((row) => {
					return row.getId();
				});

				if (this.parent.getParam('ALLOW_ROWS_SORT_INSTANT_SAVE', true))
				{
					this.saveRowsSort(ids);
				}

				BX.onCustomEvent(window, 'Grid::rowMoved', [ids, dragItem, this.parent]);
			}
			else
			{
				this.resetDragProperties();
			}

			BX.remove(this.fake);

			this.setDefaultProps();
		},

		sortAdditionalDragItems(dragItem, additional)
		{
			additional.reduce((prev, current) => {
				Boolean(current) && BX.insertAfter(current, prev);

				return current;
			}, dragItem);
		},

		sortRows(current, target)
		{
			if (target)
			{
				target.parentNode.insertBefore(current, target);
			}
			else if (this.moved)
			{
				current.parentNode.appendChild(current);
			}
		},

		saveRowsSort(rows)
		{
			const data = {
				ids: rows,
				action: this.parent.getUserOptions().getAction('GRID_SAVE_ROWS_SORT'),
			};

			this.parent.getData().request(null, 'POST', data);
		},

		setDefaultProps()
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
		},
	};
})();
