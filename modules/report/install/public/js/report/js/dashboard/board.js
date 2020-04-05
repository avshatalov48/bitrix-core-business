;(function ()
{
	"use strict";


	BX.namespace("BX.Report.Dashboard");
	/**
	 *
	 * @param options
	 * @constructor
	 */
	BX.Report.Dashboard.Board = function (options)
	{
		if (!BX.type.isPlainObject(options))
		{
			throw new Error("BX.Report.Dashboard.Board: 'options' is not an object.");
		}

		if (!BX.type.isDomNode(options.renderTo))
		{
			throw new Error("BX.Report.Dashboard.Board: 'renderTo' is not a DOMNode.");
		}

		this.renderTo = options.renderTo;
		this.id = options.id || null;
		this.rendered = false;
		this.designerMode = options.designerMode;
		this.isDefault = options.isDefault || false;

		this.defaultWidgetClass = options.defaultWidgetClass || 'BX.Report.Dashboard.Widget';
		this.rowsOrder = [];



		this.layout = {
			outerContainer: null,
			innerContainer: null,
			boardContainer: null,
			emptyBoardContainer: null
		};
		this.rowWithPseudoRows = null;

		this.addRows(options.rows);
		this.init();
	};

	BX.Report.Dashboard.Board.prototype = {
		isEmpty: function()
		{
			return this.empty;
		},
		setEmpty: function(value)
		{
			this.empty = value;
			if (this.empty)
			{
				BX.cleanNode(this.getBoardContainer());
				this.getBoardContainer().appendChild(this.getEmptyBoardContent());
			}
			else
			{
				BX.cleanNode(this.getBoardContainer());
			}
		},
		init: function ()
		{
			BX.Report.Dashboard.BoardRepository.addBoard(this);
		},
		getId: function ()
		{
			return this.id;
		},
		toggleDesignerMode: function ()
		{
			if (this.isDesignerMode())
			{
				this.setDesignerMode(false);
			}
			else
			{
				this.setDesignerMode(true);
			}
		},
		setDesignerMode: function (mode)
		{
			this.designerMode = mode;
			if (this.designerMode)
			{
				this.getInnerContainer().classList.add('report-visualconstructor-dashboard-designer-mode')
			}
			else
			{
				this.getInnerContainer().classList.remove('report-visualconstructor-dashboard-designer-mode')
			}
		},
		isDesignerMode: function ()
		{
			return this.designerMode;
		},
		getRenderToContainer: function ()
		{
			return this.renderTo;
		},
		/**
		 *
		 * @returns {Element}
		 */
		getOuterContainer: function ()
		{
			if (this.layout.outerContainer)
			{
				return this.layout.outerContainer;
			}

			this.layout.outerContainer = BX.create("div", {
				props: {
					className: "report-visualconstructor"
				}
			});

			return this.layout.outerContainer;
		},
		/**
		 *
		 * @returns {Element}
		 */
		getInnerContainer: function ()
		{
			if (this.layout.innerContainer)
			{
				return this.layout.innerContainer;
			}

			this.layout.innerContainer = BX.create("div", {
				props: {
					className: "report-visualconstructor-inner"
				}
			});

			return this.layout.innerContainer;
		},
		getBoardContainer: function ()
		{
			if (this.layout.boardContainer)
			{
				return this.layout.boardContainer;
			}

			this.layout.boardContainer = BX.create('div', {
				props: {
					className: 'report-visualconstructor-dashboard-container'
				}
			});
			return this.layout.boardContainer;
		},
		scrollEventListener: function()
		{
			if (this.scrollEventListenerFunc !== undefined)
			{
				return this.scrollEventListenerFunc;
			}

			this.scrollEventListenerFunc = BX.throttle(this.lazyLoad, 100, this);
			return this.scrollEventListenerFunc;
		},
		lazyLoad: function ()
		{
			var rows = this.getRows();
			var clientHeight = document.documentElement.clientHeight;
			rows.forEach(function (row)
			{
				var rowPos = row.getRowWrapper().getBoundingClientRect();
				if (
					rowPos.top >= 0 && rowPos.top <= clientHeight ||
					rowPos.bottom >= 0 && rowPos.bottom <= clientHeight
				)
				{
					row.lazyLoadWidgets();
				}
			});

		},
		addRow: function (options, targetRow, isBefore)
		{
			if (this.isEmpty())
			{
				this.setEmpty(false);
			}

			var row = this.getRowObject(options);
			row.setBoard(this);

			if (isBefore === true)
			{
				this.addRowBefore(row, targetRow);
			}
			else
			{
				this.addRowAfter(row, targetRow);
			}


			jsDD.refreshDestArea();
			return row;
		},
		getRowObject: function (options)
		{
			var row;
			if (BX.type.isPlainObject(options) && !(options instanceof BX.Report.Dashboard.Row))
			{
				row = new BX.Report.Dashboard.Row(options);
			}
			else if (options instanceof BX.Report.Dashboard.Row)
			{
				row = options;
			}
			else
			{
				throw new Error("Unable to create or get row object");
			}
			return row;
		},
		addRowToStart: function (row)
		{
			row.setBoard(this);
			this.rowsOrder.splice(0, 0, row);
			if (this.isRendered())
			{
				BX.prepend(row.render(), this.getBoardContainer());
			}
			return row;
		},
		addRowBefore: function (row, targetRow)
		{

			targetRow = this.getRow(targetRow);
			var targetRowIndex = BX.util.array_search(targetRow, this.rowsOrder);
			if (targetRowIndex >= 0)
			{
				this.rowsOrder.splice(targetRowIndex, 0, row);
				if (this.isRendered())
				{
					this.getBoardContainer().insertBefore(row.render(), targetRow.getRowContainer());
				}
			}
		},
		addRowAfter: function (row, targetRow)
		{
			targetRow = this.getRow(targetRow);
			var targetRowIndex = BX.util.array_search(targetRow, this.rowsOrder);
			var targetNext = this.getNextRowSibling(targetRow);
			if (targetRowIndex >= 0 && targetNext)
			{
				this.rowsOrder.splice(targetRowIndex + 1, 0, row);
				if (this.isRendered())
				{
					this.getBoardContainer().insertBefore(row.render(), targetNext.getRowContainer());
				}
			}
			else
			{
				this.rowsOrder.push(row);
				if (this.isRendered())
				{
					this.getBoardContainer().appendChild(row.render());
				}
			}
		},
		addRows: function (options)
		{
			options.forEach(function (option)
			{
				this.addRow(option);
			}.bind(this));
		},
		adjustRowsWeight: function ()
		{
			var rows = this.getRows();
			for (var i = 0; i < rows.length; i++)
			{
				rows[i].setWeight(i + 1);
			}
			BX.onCustomEvent(this, 'BX.Report.Dashboard.Board:afterRowsAdjust', [rows]);
		},
		getRows: function ()
		{
			return this.rowsOrder;
		},
		/**
		 *
		 * @param {BX.Report.Dashboard.Row|string|number} row
		 * @returns {BX.Report.Dashboard.Row}
		 */
		getRow: function (row)
		{
			var rowId = row instanceof BX.Report.Dashboard.Row ? row.getId() : row;

			for (var i = 0; i < this.rowsOrder.length; i++)
			{
				if (this.rowsOrder[i] instanceof BX.Report.Dashboard.Row && this.rowsOrder[i].getId() === rowId)
				{
					return this.rowsOrder[i];
				}
			}
			return null;
		},
		removeRow: function (row)
		{
			var rowIndex = BX.util.array_search(row, this.rowsOrder);
			row.remove();
			this.rowsOrder.splice(rowIndex, 1);
			BX.onCustomEvent(this, 'BX.Report.Dashboard.Board:afterRowRemove', [{row: row}]);
		},
		moveRow: function (row, targetRow)
		{
			row = this.getRow(row);
			targetRow = this.getRow(targetRow);
			if (!row || row === targetRow)
			{
				return false;
			}
			var rowIndex = BX.util.array_search(row, this.rowsOrder);
			this.rowsOrder.splice(rowIndex, 1);

			var targetRowIndex = BX.util.array_search(targetRow, this.rowsOrder);
			if (targetRowIndex >= 0)
			{
				this.rowsOrder.splice(targetRowIndex, 0, row);
				if (this.isRendered())
				{
					this.getBoardContainer().insertBefore(row.getRowContainer(), targetRow.getRowContainer());
				}
			}
			else
			{
				this.rowsOrder.push(row);
				if (this.isRendered())
				{
					this.getBoardContainer().appendChild(row.getRowContainer());
				}
			}
			this.adjustRowsWeight();
			return true;
		},
		/**
		 *
		 * @param {BX.Report.Dashboard.Row} currentRow
		 * @returns {BX.Report.Dashboard.Row}
		 */
		getNextRowSibling: function (currentRow)
		{
			var rowIndex = this.getRowIndex(currentRow);
			var rows = this.getRows();

			return rowIndex !== -1 && rows[rowIndex + 1] ? rows[rowIndex + 1] : null;
		},

		/**
		 *
		 * @param {BX.Report.Dashboard.Row} currentRow
		 * @returns {BX.Report.Dashboard.Row}
		 */
		getPreviousRowSibling: function (currentRow)
		{
			var rowIndex = this.getRowIndex(currentRow);
			var rows = this.getRows();

			return rowIndex > 0 && rows[rowIndex - 1] ? rows[rowIndex - 1] : null;
		},
		/**
		 *
		 * @param row
		 * @returns {number}
		 */
		getRowIndex: function (row)
		{
			row = this.getRow(row);
			return BX.util.array_search(row, this.getRows());
		},
		render: function ()
		{
			this.setDesignerMode(this.isDesignerMode());
			var rowFragment = document.createDocumentFragment();



			var rows = this.getRows();
			BX.Report.Dashboard.Utils.forEach(rows, function (key)
			{
				rowFragment.appendChild(rows[key].render());
			}.bind(this));
			BX.cleanNode(this.getBoardContainer());
			this.getBoardContainer().appendChild(rowFragment);
			if (!this.isRendered())
			{
				this.renderLayout();
				this.setRenderStatus(true);
				BX.onCustomEvent(this, "Dashboard.Board:onFirstRender", [this]);
			}

			BX.onCustomEvent(this, "Dashboard.Board:onRender", [this]);


			window.addEventListener('scroll', this.scrollEventListener());
		},
		renderLayout: function ()
		{
			if (this.getOuterContainer().parentNode)
			{
				return;
			}

			var innerContainer = this.getInnerContainer();
			innerContainer.appendChild(this.getBoardContainer());



			this.getRenderToContainer().appendChild(innerContainer);
			this.lazyLoad();
		},
		getEmptyBoardContent: function()
		{
			if (this.layout.emptyBoardContainer)
			{
				return this.layout.emptyBoardContainer;
			}

			this.layout.emptyBoardContainer = BX.create('div', {
				attrs: {
					className: 'report-visualconstructor-empty-board'
				},
				text: BX.message('DASHBOARD_EMPTY_BOARD_CONTENT')
			});
			return this.layout.emptyBoardContainer;
		},
		setRenderStatus: function (status)
		{
			if (BX.type.isBoolean(status))
			{
				this.rendered = status;
			}
			else
			{
				throw Error('Render status might be boolean');
			}
		},
		isRendered: function ()
		{
			return this.rendered;
		},
		clearRows: function ()
		{
			this.rowsOrder = [];
		},
		destroy: function ()
		{
			this.rendered = false;
			BX.remove(this.getBoardContainer());
			window.removeEventListener('scroll', this.scrollEventListener());
			var rows = this.getRows();
			for (var i in rows)
			{
				if (rows.hasOwnProperty(i))
				{
					rows[i].destroy();
				}
			}
		},
		/**
		 *
		 * @param {string|number} param
		 * @returns {BX.Report.Dashboard.Widget}
		 */
		getWidget: function (param)
		{
			var widgetId = param instanceof BX.Report.Dashboard.Widget ? param.getId() : param;
			var rows = this.getRows();
			for (var rowNum = 0; rowNum < rows.length; rowNum++)
			{
				if (rows[rowNum].getWidget(widgetId))
				{
					return rows[rowNum].getWidget(widgetId);
				}
			}
			return null;
		},

		showPriorityPseudoRowsByYPos: function (priorityRows, mouseCurrentYPosition)
		{
			var rows = this.getRows();
			for (var i in rows)
			{
				if (!rows.hasOwnProperty(i))
				{
					continue;
				}

				var row = rows[i];
				if (row.isPseudo())
				{
					continue;
				}


				var rowRectArea = row.getRectArea();
				var rowBottom = rowRectArea.bottom;
				var rowTop = rowRectArea.top;

				if (rowTop <= mouseCurrentYPosition && rowBottom >= mouseCurrentYPosition)
				{
					if (!row.hasShownPriorityPseudoRows)
					{
						for (var j = 0; j < priorityRows.length; j++)
						{
							row.pseudoRowsList.push(priorityRows[j]);
							this.addRow(priorityRows[j], row, true);
						}

						row.hasShownPriorityPseudoRows = true;
					}
				}
				else
				{
					var minPseudoRowTop = 0;

					//TODO refactor this
					if (row.hasShownPriorityPseudoRows)
					{
						for (var n = 0; n < row.pseudoRowsList.length; n++)
						{
							var pseudoRow = row.pseudoRowsList[n];
							if (pseudoRow.isRendered())
							{
								var pseudoRowRect = pseudoRow.getRectArea();
								if (!minPseudoRowTop)
								{
									minPseudoRowTop = pseudoRowRect.top;
								}
								else if (minPseudoRowTop > pseudoRowRect.top)
								{
									minPseudoRowTop = pseudoRowRect.top;
								}
							}

						}
						if (mouseCurrentYPosition > rowBottom || mouseCurrentYPosition < minPseudoRowTop)
						{
							this.removePseudoRows();
						}

					}
				}
			}
		},
		removePseudoRows: function ()
		{
			var rows = this.getRows();
			var removedRowsList = [];
			for (var i = 0; i < rows.length; i++)
			{
				if (rows[i].isPseudo())
				{
					removedRowsList.push(rows[i]);
				}
				else
				{
					rows[i].hasShownPriorityPseudoRows = false;
				}
			}

			for (var j = 0; j < removedRowsList.length; j++)
			{
				this.removeRow(removedRowsList[j]);
			}
			jsDD.refreshDestArea();
		}
	};


	BX.Report.Dashboard.BoardRepository = {
		dashboards: [],
		addBoard: function (board)
		{
			this.dashboards.push(board);
		},
		getBoard: function (id)
		{
			var boards = this.getBoards();
			for (var i = 0; i < boards.length; i++)
			{
				if (boards[i].getId() === id)
				{
					return boards[i];
				}
			}
			return false;
		},
		getBoards: function ()
		{
			return this.dashboards;
		},
		destroyBoards: function()
		{
			var boards = this.getBoards();

			for (var i = 0; i < boards.length; i++)
			{
				boards[i].destroy();
				boards.splice(i, 1);
			}
		}

	}
})();


