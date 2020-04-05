;(function() {
	"use strict";
	BX.namespace("BX.Report.Dashboard");

	/**
	 * @param options.widgets
	 * @param options.id
	 * @param options.weight
	 * @param options.layoutMap
	 * @param options.pseudo
	 * @param options.draggable
	 * @param options.loaded
	 * @constructor
	 */
	BX.Report.Dashboard.Row = function(options)
	{
		this.id = options.id;
		this.weight = options.weight || 0;
		this.layoutMap = options.layoutMap || '';

		this.widgetsOrder = [];
		this.pseudo = options.pseudo || false;
		if (BX.type.isArray(options.widgets))
		{
			this.addWidgets(options.widgets);
		}
		this.draggable = options.draggable || true;
		this.loaded = options.loaded || false;
		this.timer = null;
		this.isScrollingUp = false;
		this.isScrollingDown = false;
		this.timer = null;
		this.rectArea = null;
		this.board = {};

		this.rowLayout = new BX.Report.Dashboard.Row.LayoutFabric({
			row: this,
			map: this.layoutMap
		});

		this.layout = {
			rowContainer: null,
			rowMoveControlButton: null,
			rowWrapper: null,
			rowAddButton: null,
			rowRemoveButton: null,
			controlsContainer: null,
			contentContainer: null,
			actionMenuOpenButton: null
		};

		this.pseudoRowsList = [];
	};

	BX.Report.Dashboard.Row.prototype =
	{
		isPseudo: function ()
		{
			return this.pseudo;
		},
		removePseudo: function()
		{
			this.pseudo = false;
			if (this.isRendered())
			{
				this.getRowContainer().classList.remove('report-visualconstructor-dashboard-pseudo-row');
			}
		},
		getWidgetClass: function(className)
		{
			var classFn = BX.Report.Dashboard.Utils.getClass(className);
			if (BX.type.isFunction(classFn))
			{
				return classFn;
			}

			return BX.Report.Dashboard.Widget;
		},
		getRowContainer: function()
		{
			if (this.layout.rowContainer)
			{
				return this.layout.rowContainer;
			}
			this.layout.rowContainer = BX.create('div', {
				attrs: {
					className: 'report-visualconstructor-dashboard-row-container',
					"data-id": this.getId(),
					"data-weight": this.getWeight(),
					"data-type": "row-container"
				}
			});


			if (this.isPseudo())
			{
				this.layout.rowContainer.classList.add('report-visualconstructor-dashboard-pseudo-row');
			}
			else
			{
				jsDD.registerDest(this.layout.rowContainer, 31);
			}

			this.makeDraggable();
			return this.layout.rowContainer;
		},
		getRowWrapper: function ()
		{
			if (this.layout.rowWrapper)
			{
				return this.layout.rowWrapper;
			}
			this.layout.rowWrapper = BX.create('div', {
				attrs: {
					className: 'report-visualconstructor-dashboard-row-wrapper',
					"data-id": this.getId(),
					"data-type": "row-wrapper"
				}
			});
			return this.layout.rowWrapper;
		},
		getRowMoveControlButton: function()
		{
			if (this.layout.rowMoveControlButton)
			{
				return this.layout.rowMoveControlButton;
			}
			this.layout.rowMoveControlButton = BX.create('div', {
				text: '^',
				props: {
					className: 'report-visualconstructor-dashboard-row-move-control'
				}
			});
			return this.layout.rowMoveControlButton;
		},
		getRowAddButton: function()
		{
			if (this.layout.rowAddButton)
			{
				return this.layout.rowAddButton;
			}
			this.layout.rowAddButton = BX.create('div', {
				text: '+',
				props: {
					className: 'report-visualconstructor-dashboard-row-add-control'
				},
				events: {
					click: this.rowAddButtonClickHandler.bind(this)
				}
			});
			return this.layout.rowAddButton;
		},
		// getRowRemoveButton: function()
		// {
		// 	if (this.layout.rowRemoveButton)
		// 	{
		// 		return this.layout.rowRemoveButton;
		// 	}
		// 	this.layout.rowRemoveButton = BX.create('div', {
		// 		text: 'X',
		// 		props: {
		// 			className: 'report-visualconstructor-dashboard-row-remove-control'
		// 		},
		// 		events: {
		// 			click: this.remove.bind(this)
		// 		}
		// 	});
		// 	return this.layout.rowRemoveButton;
		// },
		getRowLayoutChooseContent: function()
		{
			var linearOneCell = new BX.Report.Dashboard.Row.HorizontalLinear({
				row: this,
				cellCount: 1
			});
			var linearTwoCell = new BX.Report.Dashboard.Row.HorizontalLinear({
				row: this,
				cellCount: 2
			});
			var linearThreeCell = new BX.Report.Dashboard.Row.HorizontalLinear({
				row: this,
				cellCount: 3
			});
			var linearFourCell = new BX.Report.Dashboard.Row.HorizontalLinear({
				row: this,
				cellCount: 4
			});
			return BX.create('div', {
				attrs: {
					className: 'report-visualconstructor-dashboard-row-miniature-popup-content'
				},
				children: [
					linearOneCell.getMiniatureContainer(),
					linearTwoCell.getMiniatureContainer(),
					linearThreeCell.getMiniatureContainer(),
					linearFourCell.getMiniatureContainer()
				]
			})
		},
		rowAddButtonClickHandler: function()
		{
			this.openRowLayoutChoosePopup();
		},
		openRowLayoutChoosePopup: function()
		{
			this.addRowPopup = new BX.PopupWindow('report-visualconstructor-dashboard-add-row-popup-' + this.getId(), this.getRowAddButton(), {
				title: 'Select Row Layout',
				closeIcon: {right: "20px", top: "10px"},
				angle: true,
				autoHide: true,
				content: this.getRowLayoutChooseContent(),
				events: {
					onPopupClose: this.unpinRowAddButton.bind(this)
				}
			});
			this.addRowPopup.show();
			this.pinRowAddButton();
		},
		pinRowAddButton: function()
		{
			this.getRowAddButton().classList.add('report-visualconstructor-dashboard-add-row-popup-opened');
		},
		unpinRowAddButton: function()
		{
			this.getRowAddButton().classList.remove('report-visualconstructor-dashboard-add-row-popup-opened');
		},
		render: function ()
		{
			var rowContainer = this.getRowContainer();
			var rowWrapper = this.getRowWrapper();


			rowWrapper.appendChild(this.getRowLayout().render());
			rowContainer.appendChild(rowWrapper);


			rowContainer.appendChild(this.getRowMoveControlButton());
			rowContainer.appendChild(this.getRowAddButton());

			//rowContainer.appendChild(this.getRowRemoveButton());
			this.setRenderStatus(true);
			return rowContainer;
		},
		getId: function ()
		{
			return this.id;
		},
		setId: function (id)
		{
			if (this.isRendered())
			{
				this.getRowContainer().setAttribute('data-id', id);
				this.getRowWrapper().setAttribute('data-id', id);

			}
			this.id = id;
		},
		getWeight: function ()
		{
			return this.weight;
		},
		setWeight: function (weight)
		{
			this.weight = weight;
			if (this.isRendered())
			{
				this.getRowContainer().setAttribute("data-weight", weight);
			}
		},
		getBoard: function ()
		{
			return this.board;
		},
		setBoard: function(board)
		{
			this.board = board;
		},
		getWidget: function(param)
		{
			var widgetId = param instanceof BX.Report.Dashboard.Widget ? param.getId() : param;

			for (var i = 0; i < this.widgetsOrder.length; i++)
			{
				if (this.widgetsOrder[i] instanceof BX.Report.Dashboard.Widget && this.widgetsOrder[i].getId() === widgetId)
				{
					return this.widgetsOrder[i];
				}
			}
			return null;
		},
		getWidgets: function ()
		{
			return this.widgetsOrder;
		},
		/**
		 * @param {object} options
		 */
		addWidget: function (options)
		{
			var widget;
			if (BX.type.isPlainObject(options) && !(options instanceof BX.Report.Dashboard.Widget))
			{
				var widgetClass = this.getWidgetClass(options.className);
				widget = new widgetClass(options);
			}
			else if (options instanceof BX.Report.Dashboard.Widget)
			{
				widget = options
			}
			else
			{
				throw new Error("Unable to create or get widget object");
			}

			widget.setRowId(this.getId());
			widget.setRow(this);
			this.widgetsOrder.push(widget);
			if (this.isRendered())
			{
				if (!widget.getCell())
				{
					var emptyCell = this.getRowLayout().getFirstEmptyCell();
					widget.setCell(emptyCell);
				}

				widget.getCell().setWidget(widget);
			}
			return widget;
		},
		/**
		 * @param {object[]} options
		 */
		addWidgets: function(options)
		{
			options.forEach(function(option){
				this.addWidget(option);
			}.bind(this))
		},
		moveWidget: function(widget, targetWidget)
		{
			if (targetWidget.getHeight() > widget.getCell().getHeight() || widget.getHeight() > targetWidget.getCell().getHeight())
			{
				return;
			}

			if (!targetWidget.isDraggable())
			{
				return;
			}

			var widgetCell = widget.getCell();
			var targetCell = targetWidget.getCell();


			var targetCellRowLayout = this.getRowLayout();
			var cellsInRow = targetCellRowLayout.getCells();
			var startCell = null;
			if (widget.getRow() === this)
			{
				startCell = widgetCell;
			}
			else
			{
				startCell = this.getRowLayout().getFirstEmptyCell();
			}
			if (startCell)
			{
				var startCellIndex = BX.util.array_search(startCell, cellsInRow);
				var targetCellIndex = BX.util.array_search(targetCell, cellsInRow);
				targetCell.getContainer().classList.add('report-visualconstructor-dashboard-empty-cell-droppable');
				targetCell.getContainer().classList.add('report-visualconstructor-dashboard-empty-cell-droppable-active');
				var isMoved = true;
				//targetCell.getContainer().style.minHeight = widget.getHeight() + 'px';
				if (startCellIndex > targetCellIndex)
				{
					//move right
					for (var i = startCellIndex; i > targetCellIndex; i--)
					{
						if (cellsInRow[i - 1].getWidget() && cellsInRow[i - 1].getWidget().getHeight() <= cellsInRow[i].getHeight())
						{
							cellsInRow[i].setWidget(cellsInRow[i - 1].getWidget());
						}
						else
						{
							isMoved = false;
							break;
						}
					}
					if (isMoved)
					{
						targetCell.clear();
						widget.setCell(targetCell);
					}
				}
				else
				{
					//move left
					for (var p = startCellIndex; p < targetCellIndex; p++)
					{
						if (cellsInRow[p+1].getWidget() && cellsInRow[p+1].getWidget().getHeight() <= cellsInRow[p].getHeight())
						{
							cellsInRow[p].setWidget(cellsInRow[p+1].getWidget());
						}
						else
						{
							isMoved = false;
							break;
						}
					}

					if (isMoved)
					{
						targetCell.clear();
						widget.setCell(targetCell);
					}

				}
				if (isMoved)
				{
					targetCell.setHeight(widget.getHeight());
					// targetCell.getContainer().style.minHeight = widget.getHeight() + 'px';
					var widgetIndex = BX.util.array_search(widget, this.widgetsOrder);
					this.widgetsOrder.splice(widgetIndex, 1);
					var targetWidgetIndex = BX.util.array_search(targetWidget, this.widgetsOrder);
					if (targetWidgetIndex >= 0)
					{
						this.widgetsOrder.splice(targetWidgetIndex, 0, widget);
					}
					else
					{
						this.widgetsOrder.push(widget);
					}
				}

			}

		},
		removeWidget: function (widget)
		{
			var widgetIndex = BX.util.array_search(widget, this.widgetsOrder);
			this.widgetsOrder.splice(widgetIndex, 1);
			if (this.getWidgets().length === 0)
			{
				this.getBoard().removeRow(this);
			}
		},
		remove: function ()
		{
			this.destroy();
		},
		destroy: function()
		{
			jsDD.unregisterDest(this.layout.rowContainer);
			this.setRenderStatus(false);
			BX.remove(this.getRowContainer());
			this.getRowLayout().destroy();
		},
		getRowLayout: function ()
		{
			return this.rowLayout;
		},
		setRenderStatus: function(status)
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
		isDraggable: function ()
		{
			return this.draggable;
		},
		makeDraggable: function()
		{
			if (!this.isDraggable())
			{
				return;
			}

			//main events
			this.getRowMoveControlButton().onbxdragstart = this.onDragStart.bind(this);
			this.getRowMoveControlButton().onbxdrag = this.onDrag.bind(this);
			this.getRowMoveControlButton().onbxdragstop = this.onDragStop.bind(this);

			jsDD.registerObject(this.getRowMoveControlButton());
		},
		onDragStart: function()
		{
			this.getRowContainer().classList.add('report-visualconstructor-dashboard-row-drag');
			this.dragRowOffset = jsDD.start_y;
			this.dragRowId = BX.util.array_search(this, this.getBoard().getRows());
			this.dragTargetRow = this.dragTargetRow || this;

		},
		onDrag: function(x, y)
		{
			this.verticalAutoScroll(y);
			this.moveVisuallyRows(y);
		},
		onDragStop: function()
		{

			var success = this.getBoard().moveRow(this, this.dragTargetRow);
			if (success)
			{
				BX.onCustomEvent(this.getBoard(), "BX.Report.Dashboard.Board:afterRowMoved", [this.getBoard().getRows()]);
			}

			this.getRowContainer().classList.remove('report-visualconstructor-dashboard-row-drag');

			var rows = this.getBoard().getRows();
			for (var rowId in rows)
			{
				if (!rows.hasOwnProperty(rowId))
				{
					continue;
				}

				var row = rows[rowId];
				var rowContainer = row.getRowContainer();

				row.resetRectArea();
				rowContainer.style.removeProperty("transition");
				rowContainer.style.removeProperty("transform");

			}
			this.stopScroll();

			//this.stopScroll();
			//todo fire custom event that row set changed, and send new row list
		},
		/**
		 * @returns {ClientRect}
		 */
		getRectArea: function()
		{

			this.rectArea = BX.pos(this.getRowContainer());
			this.rectArea.middle = this.rectArea.top + this.rectArea.height / 2;


			return this.rectArea;
		},
		moveVisuallyRows: function(mouseCurrentYPosition)
		{
			this.getRowContainer().style.transform = "translateY(" + (mouseCurrentYPosition - this.dragRowOffset) + "px)";
			var rows = this.getBoard().getRows();
			var rowHeight = this.getRectArea().height;
			var currentPastWeight;
			for (var rowId in rows)
			{
				if (!rows.hasOwnProperty(rowId))
				{
					continue;
				}

				var row = rows[rowId];
				if (row === this)
				{
					continue;
				}
				var rowContainer = row.getRowContainer();
				var rowRectArea = row.getRectArea();
				var rowMiddle = rowRectArea.middle;


				if (
					mouseCurrentYPosition > rowMiddle &&
					rowId > this.dragRowId &&
					rowContainer.style.transform !== "translateY(" + (-rowHeight) + "px)"
				)
				{
					currentPastWeight = this.getWeight();
					this.setWeight(row.getWeight());
					row.setWeight(currentPastWeight);
					//move down
					rowContainer.style.transition = "300ms";
					rowContainer.style.transform = "translateY(" + (-rowHeight) + "px)";
					this.dragTargetRow = this.getBoard().getNextRowSibling(row);

					row.resetRectArea();
				}

				if (
					mouseCurrentYPosition < rowMiddle &&
					rowId < this.dragRowId &&
					rowContainer.style.transform !== "translateY("+(rowHeight)+"px)"
				)
				{
					currentPastWeight = this.getWeight();
					this.setWeight(row.getWeight());
					row.setWeight(currentPastWeight);
					//move up
					rowContainer.style.transition = "300ms";
					rowContainer.style.transform = "translateY(" + rowHeight + "px)";
					this.dragTargetRow = row;
					row.resetRectArea();
				}


				var moveBackDown =
					mouseCurrentYPosition < rowMiddle &&
					rowId > this.dragRowId &&
					rowContainer.style.transform !== "" &&
					rowContainer.style.transform !== "translateY(0px)";

				var moveBackUp =
					mouseCurrentYPosition > rowMiddle &&
					rowId < this.dragRowId &&
					rowContainer.style.transform !== "" &&
					rowContainer.style.transform !== "translateY(0px)";

				if (moveBackDown || moveBackUp)
				{
					//move to the start position
					rowContainer.style.transition = "300ms";
					rowContainer.style.transform = "translateY(0px)";
					this.dragTargetRow = moveBackDown ? row : this.getBoard().getNextRowSibling(row);
					row.resetRectArea();
				}
			}
		},
		resetRectArea: function()
		{
			this.rectArea = null;
		},
		lazyLoadWidgets: function()
		{
			if (!this.loaded)
			{
				var widgets = this.getWidgets();
				widgets.forEach(function(widget) {
					widget.lazyLoad()
				});
				this.loaded = true;
			}
		},
		verticalAutoScroll: function (mouseCurrentYPosition)
		{
			var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
			var clientHeight = document.documentElement.clientHeight;
			var pointerPosition = mouseCurrentYPosition - scrollTop;

			if (pointerPosition >= (clientHeight - 50))
			{
				this.scrollDown(mouseCurrentYPosition);
			}
			else if (pointerPosition <= 50)
			{
				//auto scroll up
				this.scrollUp(mouseCurrentYPosition);
			}
			else
			{
				this.stopScroll();
			}
		},
		scrollUp: function(mouseCurrentYPosition)
		{
			if (this.isScrollingUp)
			{
				return;
			}
			this.isScrollingUp = true;
			this.timer = setInterval(function()
			{
				var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
				window.scrollTo(0, scrollTop - 10);
				mouseCurrentYPosition -= 10;
				this.moveVisuallyRows(mouseCurrentYPosition)
			}.bind(this), 20);
		},
		scrollDown: function(mouseCurrentYPosition)
		{
			if (this.isScrollingDown)
			{
				return;
			}
			this.isScrollingDown = true;
			this.timer = setInterval(function()
			{
				var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
				window.scrollTo(0, scrollTop + 10);
				mouseCurrentYPosition += 10;
				this.moveVisuallyRows(mouseCurrentYPosition)
			}.bind(this), 20);
		},
		stopScroll: function()
		{
			this.isScrollingUp = false;
			this.isScrollingDown = false;
			clearInterval(this.timer);
		},
		removeTopPseudoRows: function()
		{
			for (var i = 0; i < this.pseudoRowsList.top.length; i++)
			{
				if (this.pseudoRowsList.top[i] instanceof BX.Report.Dashboard.Row && this.pseudoRowsList.top[i].isPseudo())
				{
					this.getBoard().removeRow(this.pseudoRowsList.top[i]);
				}
			}
			this.pseudoRowsList.top = [];
		},
		removeBottomPseudoRows: function()
		{
			for (var i = 0; i < this.pseudoRowsList.bottom.length; i++)
			{
				if (this.pseudoRowsList.bottom[i] instanceof BX.Report.Dashboard.Row && this.pseudoRowsList.bottom[i].isPseudo())
				{
					this.getBoard().removeRow(this.pseudoRowsList.bottom[i]);
				}
			}
			this.pseudoRowsList.bottom = [];
		}
	}
})();