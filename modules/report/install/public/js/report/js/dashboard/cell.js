;(function ()
{
	BX.namespace("BX.Report.Dashboard");


	BX.Report.Dashboard.Cell = function(options)
	{
		this.height = options.height || 400;
		this.widget = options.widget;
		this.flexValue = options.flexValue || 1;
		this.row = options.row;
		this.empty = true;
		this.rendered = false;
		this.id = options.id;

		this.layout = {
			cellContainer: null,
			tempContainer: null
		}
	};

	BX.Report.Dashboard.Cell.prototype =
	{
		getId: function()
		{
			return this.id;
		},
		getWidget: function()
		{
			return this.widget;
		},
		getRow: function()
		{
			return this.row;
		},
		getHeight: function ()
		{
			return this.height;
		},
		setHeight: function (height)
		{
			this.height = height;
			if (this.isRendered())
			{
				this.getContainer().style.minHeight = height + 'px';
			}
		},
		getContainer: function()
		{
			if (this.layout.cellContainer)
			{
				return this.layout.cellContainer;
			}
			this.layout.cellContainer = BX.create('div', {
				attrs: {
					className: 'report-visualconstructor-dashboard-cell report-visualconstructor-dashboard-empty-cell',
					"data-id": this.id,
					"data-type": 'cell'
				},
				style: {
					flex: this.flexValue || 1,
					minHeight: '0'
				}
			});



			this.layout.cellContainer.onbxdestdragstart = BX.delegate(this.onWidgetDragStart, this);
			this.layout.cellContainer.onbxdestdraghover = BX.delegate(this.onWidgetDragEnter, this);
			this.layout.cellContainer.onbxdestdraghout = BX.delegate(this.onWidgetDragLeave, this);
			this.layout.cellContainer.onbxdestdragfinish = BX.delegate(this.onWidgetDragDrop, this);
			this.layout.cellContainer.onbxdestdragstop = BX.delegate(this.onWidgetDragEnd, this);

			jsDD.registerDest(this.layout.cellContainer, 30);

			return this.layout.cellContainer;
		},
		getCellTempContainer: function()
		{
			if (this.layout.tempContainer)
			{
				return this.layout.tempContainer;
			}
			this.layout.tempContainer = BX.create('div', {
				text: 'Drop here widget or ',
				attrs: {
					className: 'report-visualconstructor-dashboard-cell-temp-content'
				}
			});

			var createButton = BX.create('ins', {
				html: 'create',
				events: {
					//click: this.createWidgetInThisCell.bind(this)
				}
			});

			this.layout.tempContainer.appendChild(createButton);
			return this.layout.tempContainer;
		},

		onWidgetDragStart: function(itemNode)
		{

			if (itemNode.dataset.type !== 'widget')
			{
				return;
			}
			var widget = this.getRow().getBoard().getWidget(itemNode.dataset.id);
			if (widget.getCell().getRow().getBoard() !== this.getRow().getBoard())
			{
				return;
			}
			if (widget.getHeight() > this.getHeight())
			{
				return;
			}


			if (!widget.isResizeable() && widget.getCell().getContainer().clientWidth > (this.getContainer().clientWidth + 4))
			{
				return;
			}


			if (this.isEmpty())
			{
				this.getContainer().classList.add('report-visualconstructor-dashboard-empty-cell-droppable');
				//this.getContainer().style.minHeight = widget.getHeight() + 'px';
			}
			jsDD.refreshDestArea();
		},
		onWidgetDragEnter: function(itemNode)
		{

			if (itemNode.dataset.type !== 'widget')
			{
				return;
			}

			var widget = this.getRow().getBoard().getWidget(itemNode.dataset.id);
			if (widget.getCell().getRow().getBoard() !== this.getRow().getBoard())
			{
				return;
			}

			if (widget.getHeight() > this.getHeight())
			{
				return;
			}

			if (!widget.isResizeable() && widget.getCell().getContainer().clientWidth > (this.getContainer().clientWidth + 4))
			{
				return;
			}

			if (this.isEmpty())
			{
				this.getContainer().classList.add('report-visualconstructor-dashboard-empty-cell-droppable-active');

				this.expendTimer = setTimeout(function() {
					this.getContainer().style.transition = 'min-height 700ms';
					this.getContainer().style.minHeight = widget.getHeight() + 'px';
					this.getContainer().addEventListener("transitionend", function() {jsDD.refreshDestArea()}, false);

				}.bind(this), 300);

			}
			else
			{
				this.moveTimer = setTimeout(BX.delegate(function() {
					var draggedWidget = this.getRow().getBoard().getWidget(itemNode.dataset.id);
					if (draggedWidget !== this.getWidget())
					{
						this.getRow().moveWidget(draggedWidget, this.getWidget());
					}
				}, this), 500);

			}
		},
		onWidgetDragLeave: function(itemNode)
		{
			if (itemNode.dataset.type !== 'widget')
			{
				return;
			}
			var widget = this.getRow().getBoard().getWidget(itemNode.dataset.id);
			if (widget.getCell().getRow().getBoard() !== this.getRow().getBoard())
			{
				return;
			}

			if (!widget.isResizeable() && widget.getCell().getContainer().clientWidth > (this.getContainer().clientWidth + 4))
			{
				return;
			}

			this.getContainer().classList.remove('report-visualconstructor-dashboard-empty-cell-droppable-active');
			clearTimeout(this.moveTimer);
			clearTimeout(this.expendTimer);

			setTimeout(function() {
				this.getContainer().style.minHeight = '50px';

			}.bind(this), 300);



		},
		onWidgetDragDrop: function(itemNode)
		{
			clearTimeout(this.moveTimer);
			if (itemNode.dataset.type !== 'widget')
			{
				return;
			}
			var widget = this.getRow().getBoard().getWidget(itemNode.dataset.id);
			if (widget.getCell().getRow().getBoard() !== this.getRow().getBoard())
			{
				return;
			}

			if (!widget.isResizeable() && widget.getCell().getContainer().clientWidth > (this.getContainer().clientWidth + 4))
			{
				return;
			}

			if (widget.getHeight() > this.getHeight())
			{
				return
			}

			if (this.isEmpty())
			{
				widget.dropped = true;

				var originalWidgetCell = widget.getCell();
				var originalWidgetRow = widget.getRow();


				if (originalWidgetRow !== this.getRow())
				{
					originalWidgetRow.removeWidget(widget);
					this.getRow().addWidget(widget);
				}

				this.setWidget(widget);
				widget.setCell(this);

				var cellClientHeight = this.getContainer().clientHeight;
				var widgetHeight = widget.getHeight();

				var maxWidgetsByHeightCanBeHere = Math.round(cellClientHeight / widgetHeight);
				if (maxWidgetsByHeightCanBeHere > 1)
				{
					this.getRow().getRowLayout().transformCellToContainer(this, {
						orientation: 'vertical',
						cellCount: maxWidgetsByHeightCanBeHere
					});
				}

				this.getRow().getRowLayout().adjustNoEmptyCellsInCellLevel(this);

				if (originalWidgetRow.getWidgets().length !== 0 && originalWidgetCell.getId() !== this.getId())
				{
					originalWidgetCell.clear();
				}
				this.getRow().getBoard().lazyLoad();
				BX.onCustomEvent(this.getRow().getBoard(), "BX.Report.Dashboard.Widget:afterMove", [this.getRow().getWidgets(), this.getRow()]);



				if (originalWidgetRow.getWidgets().length !== 0)
				{
					originalWidgetCell.adjustToMaxSize();
					BX.onCustomEvent(this.getRow().getBoard(), "BX.Report.Dashboard.Widget:afterMove", [originalWidgetCell.getRow().getWidgets(), originalWidgetCell.getRow()]);
				}



				this.getRow().removePseudo();
			}

			this.getRow().getBoard().removePseudoRows();
		},
		onWidgetDragEnd: function(itemNode)
		{
			if (itemNode.dataset.type === 'widget')
			{
				//HACK because our dd is singleton on all over page
				var widget = this.getRow().getBoard().getWidget(itemNode.dataset.id);
				if (widget && !widget.dropped)
				{
					widget.getCell().setWidget(widget);
				}
				this.getContainer().classList.remove('report-visualconstructor-dashboard-empty-cell-droppable');
				this.getContainer().classList.remove('report-visualconstructor-dashboard-empty-cell-droppable-active');

				if (!this.isEmpty())
				{
					this.getContainer().style.transition = 'min-height 300ms';
					this.getContainer().style.minHeight = this.getWidget().getHeight() + 'px';
				}
			}

		},
		clear: function()
		{
			BX.cleanNode(this.getContainer());

			this.getCellTempContainer().classList.remove('report-visualconstructor-dashboard-hidden-temp-content');
			this.getContainer().appendChild(this.getCellTempContainer());
			this.getContainer().classList.add('report-visualconstructor-dashboard-empty-cell');
			this.getContainer().style.minHeight = '';
			this.empty = true;
			this.widget = null;
			this.getRow().getRowLayout().getCellById(this.getId()).empty = true;
			this.getRow().getRowLayout().getCellById(this.getId()).widget = null;
			BX.onCustomEvent(this, "BX.Report.Dashboard.Cell:clean", [this]);
		},
		isEmpty: function()
		{
			return this.empty;
		},
		render: function()
		{
			var container = this.getContainer();

			container.appendChild(this.getCellTempContainer());
			if (this.getWidget() instanceof BX.Report.Dashboard.Widget)
			{
				container.classList.remove('report-visualconstructor-dashboard-empty-cell');
				container.classList.remove('report-visualconstructor-dashboard-empty-cell-droppable');


				container.appendChild(this.getWidget().render());

				container.style.minHeight = this.getWidget().getHeight() + 'px';

				this.getWidget().setCell(this);

				this.getCellTempContainer().classList.add('report-visualconstructor-dashboard-hidden-temp-content');
				this.empty = false;
			}
			else
			{

				this.clear();
			}
			this.rendered = true;
			return container;
		},
		isRendered: function()
		{
			return this.rendered;
		},
		setWidget: function(widget)
		{
			this.widget = widget;
			this.setHeight(widget.getHeight());
			if (this.isRendered())
			{
				this.render();
				if (this.widget.loaded)
				{
					BX.onCustomEvent(this.getWidget(), 'Dashboard.Board.Widget:onAfterRender');
				}
			}
		},
		adjustToMaxSize: function()
		{
			this.getRow().getRowLayout().adjustCellToMaxSize(this);
		},
		destroy: function()
		{
			jsDD.unregisterDest(this.getContainer());

			/**
			 * HACK for clear current destination area.
			 * Must fix in jsDD
			 *
			 * @type {boolean}
			 */
			jsDD.current_dest_index = false;
			BX.remove(this.getContainer());
		}
	};



})();