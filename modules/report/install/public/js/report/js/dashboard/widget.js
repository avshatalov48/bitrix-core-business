;(function ()
{
	BX.namespace("BX.Report.Dashboard");
	/**
	 * @param {object[]} [options.actionItems]
	 * @param {string} [options.id]
	 * @param {string} [options.rowId]
	 * @param {string} [options.title]
	 * @param {object} [options.config]
	 * @param {number} [options.weight]
	 * @param {object} [options.data]
	 * @param {object} [options.events]
	 * @param {number} [options.width]
	 * @param {number} [options.height]
	 * @param {bool} [options.draggable]
	 * @param {bool} [options.droppable]
	 * @param {bool} [options.resizable]
	 * @param {bool} [options.loaded]
	 * @param {bool} [options.cell]
	 * @param {BX.Report.Dashboard.Content|null} [options.content]
	 * @constructor
	 */
	BX.Report.Dashboard.Widget = function (options)
	{
		this.id = options.id;
		this.rowId = options.rowId;
		this.title = options.title || 'No title';
		this.weight = options.weight || 0;
		this.data = options.data || {};

		this.setContent(options.content || null);
		this.width = options.width || '100%';
		this.height = options.height || '100%';
		this.draggable = options.draggable || false;
		this.droppable = options.droppable || false;
		this.loaded = options.loaded || false;
		this.cell = options.cell || null;
		this.config = options.config || null;
		this.resizable = options.resizable;
		this.dropped = false;
		this.layout = {
			lazyLoadPresetContainer: null,
			widgetContainer: null,
			widgetWrapper: null,
			headContainer: null,
			headWrapper: null,
			titleContainer: null,
			controlsContainer: null,
			contentContainer: null,
			contentWrapper: null,
			menuOpenButton: null
		};

		this.actionItems = options.actionItems || [];
		this.events = options.events || {};
		this.actionItems.push(BX.create('div', {
			text: BX.message('DASHBOARD_WIDGET_REMOVE_TITLE'),
			events: {
				click: this.openDeleteConfirmPopup.bind(this)
			}
		}));
		this.board = null;

	};

	BX.Report.Dashboard.Widget.prototype =
		{
			isResizeable: function ()
			{
				return this.resizable;
			},
			isDarkMode: function ()
			{
				var color = this.getColor().substring(1, 7);
				return BX.Report.Dashboard.Utils.isDarkColor(color);
			},
			getColor: function ()
			{
				return this.config.color ? this.config.color : '#ffffff';
			},
			setColor: function (color)
			{
				this.config.color = color;
			},
			applyColor: function ()
			{
				if (this.loaded)
				{
					this.getWidgetWrapper().style.backgroundColor = this.getColor();
					if (this.isDarkMode())
					{
						this.getWidgetWrapper().classList.add('report-visualconstructor-dashboard-widget-dark');
						this.getWidgetWrapper().classList.remove('report-visualconstructor-dashboard-widget-light');
					}
					else
					{
						this.getWidgetWrapper().classList.add('report-visualconstructor-dashboard-widget-light');
						this.getWidgetWrapper().classList.remove('report-visualconstructor-dashboard-widget-dark');
					}
				}
			},
			getLazyLoadPresetContainer: function ()
			{
				if (this.layout.lazyLoadPresetContainer)
				{
					return this.layout.lazyLoadPresetContainer;
				}
				var preview = this.runtimeContent.renderPreview();
				if (preview)
				{
					this.layout.lazyLoadPresetContainer = preview;
				}
				else
				{
					var loader = new BX.Loader({size: 60});
					this.layout.lazyLoadPresetContainer = BX.create('div', {
						attrs: {
							className: 'report-visualconstructor-dashboard-widget-lazy-load-preset'
						}
					});
					loader.show(this.layout.lazyLoadPresetContainer);
				}


				return this.layout.lazyLoadPresetContainer;
			},
			getWidgetContainer: function ()
			{
				if (this.layout.widgetContainer)
				{
					return this.layout.widgetContainer;
				}
				this.layout.widgetContainer = BX.create('div', {
					attrs: {
						className: 'report-visualconstructor-dashboard-widget-container',
						"data-id": this.getId(),
						"data-weight": this.getWeight(),
						"data-type": "widget"
					}
				});

				this.makeDraggable(this.layout.widgetContainer);
				return this.layout.widgetContainer;
			},
			getWidgetWrapper: function ()
			{
				var result = null;
				if (this.layout.widgetWrapper)
				{
					return this.layout.widgetWrapper;
				}
				else
				{
					result = BX.create('div', {
						attrs: {
							className: 'report-visualconstructor-dashboard-widget-wrapper'
						}
					});
				}
				this.layout.widgetWrapper = result;
				return this.layout.widgetWrapper;
			},
			makeDraggable: function (draggableContainer)
			{
				if (!this.isDraggable())
				{
					return;
				}

				this.getWidgetContainer().classList.add('report-visualconstructor-dashboard-draggable-widget-container');
				// var draggableContainer = this.getWidgetContainer();

				//main events
				draggableContainer.onbxdragstart = BX.delegate(this.onDragStart, this);
				draggableContainer.onbxdrag = BX.delegate(this.onDrag, this);
				draggableContainer.onbxdragstop = BX.delegate(this.onDragStop, this);
				draggableContainer.onbxdragfinish = BX.delegate(this.onDragFinish, this);
				draggableContainer.onbxdragrelease = BX.delegate(this.onDragEnd, this);

				jsDD.registerObject(draggableContainer);
			},
			isDraggable: function ()
			{
				return this.draggable;
			},
			onDragStart: function ()
			{
				if (!this.dragElement)
				{
					this.dropped = false;
					var widgetContainer = this.getWidgetContainer();

					this.dragElement = widgetContainer.cloneNode(true);
					this.dragElement.rotated = false;
					this.dragWidgetOffsetX = jsDD.start_x - this.getRectArea().left;
					this.dragWidgetOffsetY = jsDD.start_y - this.getRectArea().top;
					this.dragElement.style.position = "absolute";
					this.dragElement.style.height = this.getRectArea().height + 'px';
					this.dragElement.style.width = this.getRectArea().width + 'px';
					this.dragElement.style.flex = 'none';
					this.dragElement.classList.add("report-visualconstructor-dashboard-widget-drag");
					document.body.appendChild(this.dragElement);
					this.getCell().clear();
					this.getCell().getContainer().style.height = this.getRectArea().height + 'px';

				}
			},
			onDrag: function (x, y)
			{
				if (this.dragElement)
				{
					this.autoResize();
					this.rotateDragWidget(x, y);
					this.autoScroll(x, y);
					this.moveDragWidget(x, y);

					this.getRow().getBoard().showPriorityPseudoRowsByYPos(this.buildPriorityRows(), y);
				}
			},
			buildPriorityRows: function()
			{
				var priorityRows = [];
				priorityRows[0] = this.getSingleCellRow();
				if (this.isResizeable())
				{
					priorityRows[1] = this.getDoubledPriorityRow();
				}
				return priorityRows;
			},
			getSingleCellRow: function()
			{
				return new BX.Report.Dashboard.Row({
					id: "js_" + BX.util.getRandomString(),
					pseudo: true,
					layoutMap: {
						type: 'cell-container',
						orientation: 'horizontal',
						elements: [
							{
								type: 'cell',
								flexValue: 1,
								id: "js_" + BX.util.getRandomString()
							}
						]
					}
				});
			},
			getDoubledPriorityRow: function()
			{
				return new BX.Report.Dashboard.Row({
					id: "js_" + BX.util.getRandomString(),
					pseudo: true,
					layoutMap: {
						type: 'cell-container',
						orientation: 'horizontal',
						elements: [
							{
								type: 'cell',
								flexValue: 1,
								id: "js_" + BX.util.getRandomString()
							},
							{
								type: 'cell',
								flexValue: 1,
								id: "js_" + BX.util.getRandomString()
							}
						]
					}
				});

			},
			autoResize: function ()
			{
				if (!this.dragElement.scaled)
				{
					this.dragElement.style.transform = 'scale(0.5)';
					this.dragElement.style.transition = '100ms';
					this.dragElement.style.zIndex = '9999';
					this.dragElement.scaled = true;

					this.dragElement.style.transformOrigin = this.dragWidgetOffsetX + "px " + this.dragWidgetOffsetY + "px";
					this.dragElement.style.mstransformOrigin = this.dragWidgetOffsetX + "px " + this.dragWidgetOffsetY + "px";
					this.dragElement.style.webkittransformOrigin = this.dragWidgetOffsetX + "px " + this.dragWidgetOffsetY + "px";
				}
			},
			rotateDragWidget: function (mouseCurrentXPosition)
			{

				if (!this.dragElement.rotated)
				{
					if (jsDD.start_x > mouseCurrentXPosition)
					{
						this.dragElement.classList.add('report-visualconstructor-dashboard-widget-rotated-right');
					}
					else
					{
						this.dragElement.classList.add('report-visualconstructor-dashboard-widget-rotated-left');
					}
					this.dragElement.rotated = true;
				}
			},
			moveDragWidget: function (x, y)
			{
				if (x < 0 || y < 0)
				{
					return;
				}

				if (this.dragElement)
				{
					this.dragElement.style.left = x - this.dragWidgetOffsetX + "px";
					this.dragElement.style.top = y - this.dragWidgetOffsetY + "px";
				}
			},
			onDragStop: function ()
			{
				this.stopScroll();
				BX.remove(this.dragElement);
				this.dragElement = null;
				this.getCell().getContainer().style.height = '';
				this.resetRectArea();
			},
			onDragFinish: function (destination)
			{
				if (destination.dataset.type !== 'cell')
				{
					this.getCell().setWidget(this);
				}
			},
			onDragEnd: function ()
			{
				if (!this.dropped)
				{
					this.getCell().setWidget(this);
					this.getCell().getRow().getBoard().removePseudoRows();
				}
			},
			getHeadContainer: function ()
			{
				if (this.layout.headContainer)
				{
					this.layout.headContainer.style.backgroundColor = '';
					return this.layout.headContainer;
				}
				this.layout.headContainer = BX.create('div', {
					attrs: {
						className: 'report-visualconstructor-dashboard-widget-head-container',
						"data-id": this.getId(),
						"data-weight": this.getWeight(),
						"data-type": "widget"
					}
				});
				return this.layout.headContainer;
			},
			getHeadWrapper: function ()
			{
				if (this.layout.headWrapper)
				{
					return this.layout.headWrapper;
				}
				this.layout.headWrapper = BX.create('div', {
					attrs: {
						className: 'report-visualconstructor-dashboard-widget-head-wrapper'
					}
				});
				return this.layout.headWrapper;
			},
			getTitleContainer: function ()
			{
				if (this.layout.titleContainer)
				{
					return this.layout.titleContainer;
				}
				this.layout.titleContainer = BX.create('div', {
					props: {
						className: 'report-visualconstructor-dashboard-widget-title-container'
					}
				});
				return this.layout.titleContainer;
			},
			getControlsContainer: function ()
			{
				if (this.layout.controlsContainer)
				{
					return this.layout.controlsContainer;
				}
				this.layout.controlsContainer = BX.create('div', {
					props: {
						className: 'report-visualconstructor-dashboard-widget-controls-container'
					}
				});
				return this.layout.controlsContainer;
			},
			getContentContainer: function ()
			{
				if (this.layout.contentContainer)
				{
					return this.layout.contentContainer;
				}
				this.layout.contentContainer = BX.create('div', {
					props: {
						className: 'report-visualconstructor-dashboard-widget-content-container'
					}
				});
				return this.layout.contentContainer;
			},
			getContentWrapper: function ()
			{
				if (this.layout.contentWrapper)
				{
					return this.layout.contentWrapper;
				}
				this.layout.contentWrapper = BX.create('div', {
					props: {
						className: 'report-visualconstructor-dashboard-widget-content-wrapper'
					}
				});
				return this.layout.contentWrapper;
			},
			setRowId: function (rowId)
			{
				this.rowId = rowId;
			},
			setRow: function (row)
			{
				this.row = row;
				this.rowId = this.row.getId();
			},
			getRow: function ()
			{
				return this.row;
			},
			getCell: function ()
			{
				return this.cell;
			},
			setCell: function (cell)
			{
				this.cell = cell;
				this.weight = this.cell.getId();
			},
			getId: function ()
			{
				return this.id;
			},
			/**
			 * @returns {number}
			 */
			getWeight: function ()
			{
				return this.weight;
			},
			/**
			 * @returns {BX.Report.Dashboard.Content}
			 */
			getContent: function ()
			{
				return this.runtimeContent;
			},
			setContent: function (content)
			{
				if (BX.type.isPlainObject(content) && !(content instanceof BX.Report.Dashboard.Content))
				{
					var contentClass = this.getContentClass(content.className);
					content.params.widget = this;
					this.runtimeContent = new contentClass(content.params);
				}
				else if (content instanceof BX.Report.Dashboard.Content)
				{
					this.runtimeContent.setWidget(this);
				}

				if (this.runtimeContent.errors.length !== 0)
				{
					var options = {
						errors: this.runtimeContent.errors
					};
					this.runtimeContent = new BX.Report.Dashboard.Content.Error(options);
				}
			},
			getHeight: function ()
			{
				if (this.getContent().getHeight() !== 'auto')
				{
					return this.getContent().getHeight() + 55;
				}
				else
				{
					return 323;
				}

			},
			getContentClass: function (className)
			{
				var classFn = BX.Report.Dashboard.Utils.getClass(className);
				if (BX.type.isFunction(classFn))
				{
					return classFn;
				}

				return BX.Report.Dashboard.Content.Empty;
			},
			getBoard: function ()
			{
				return this.board;
			},
			/**
			 * @param {BX.Report.Dashboard.Board} board
			 */
			setBoard: function (board)
			{
				this.board = board;
			},
			lazyLoad: function ()
			{
				this.loaded = true;
				this.render();
				BX.onCustomEvent(this, 'Dashboard.Board.Widget:onAfterRender');
			},
			render: function ()
			{
				var widgetContainer = this.getWidgetContainer();
				var content = this.getContent();

				if (this.loaded)
				{
					var headContainer = this.getHeadContainer();
					var headWrapper = this.getHeadWrapper();
					var controlsContainer = this.getControlsContainer();
					var contentContainer = this.getContentContainer();
					var contentWrapper = this.getContentWrapper();
					if (this.checkIsRendered())
					{
						BX.cleanNode(widgetContainer);
						BX.cleanNode(contentContainer);
						BX.cleanNode(contentWrapper);
					}
					contentWrapper = this.getContentWrapper();
					contentContainer.appendChild(contentWrapper);

					var titleContainer = this.getTitleContainer();
					titleContainer.innerHTML = this.config.title;
					headWrapper.appendChild(titleContainer);
					var widgetWrapper = this.getWidgetWrapper();
					this.applyColor();

					contentWrapper.appendChild(content.render());
					controlsContainer.appendChild(this.getPropertiesOpenButton());
					headWrapper.appendChild(controlsContainer);
					headContainer.appendChild(headWrapper);

					widgetWrapper.appendChild(headContainer);
					widgetWrapper.appendChild(contentContainer);
					this.getLazyLoadPresetContainer().classList.add('report-visualconstructor-dashboard-widget-lazy-load-preset-disable');
					widgetContainer.appendChild(widgetWrapper);
				}
				else
				{
					var lazyLoadContainer = this.getLazyLoadPresetContainer();
					widgetContainer.appendChild(lazyLoadContainer);
				}


				this.setRenderStatus(true);


				return widgetContainer;
			},
			remove: function ()
			{
				this.getCell().clear();
				this.getDeleteConfirmPopup().close();
				this.getRow().removeWidget(this);
			},
			destroy: function ()
			{
				this.setRenderStatus(false);
				jsDD.unregisterObject(this.getWidgetContainer());
				BX.remove(this.getWidgetContainer());
				delete this.propertiesPopup;
			},
			getPropertiesOpenButton: function ()
			{
				if (this.layout.menuOpenButton)
				{
					return this.layout.menuOpenButton;
				}
				this.layout.menuOpenButton = BX.create('div', {
					attrs: {
						className: 'report-visualconstructor-dashboard-widget-properties-button'
					},
					events: {
						click: this.propertiesOpenButtonClickHandler.bind(this)
					}
				});
				return this.layout.menuOpenButton;
			},
			propertiesOpenButtonClickHandler: function (event)
			{
				var actionsButton = event.target || event.srcElement;
				this.openPopupMenu(actionsButton);
			},
			getPropertiesPopup: function (actionsButton)
			{
				if (!this.propertiesPopup)
				{
					var popupMenuId = 'report-visualconstructor-drashboard-widget-popup' + this.getId();
					this.propertiesPopup = new BX.PopupWindow(popupMenuId, actionsButton, {
						noAllPaddings: true,
						closeByEsc: true,
						autoHide: true,
						content: this.getActionsMenuItemsLayout()
					});
				}
				return this.propertiesPopup;
			},
			openPopupMenu: function (actionsButton)
			{
				this.getPropertiesPopup(actionsButton).show();
			},
			getActionsMenuItemsLayout: function ()
			{
				for (var i in this.actionItems)
				{
					this.actionItems[i].classList.add('report-visualconstructor-dashboard-widget-properties-popup-item')
				}
				return BX.create('div', {
					attrs: {
						className: 'report-visualconstructor-dashboard-widget-properties-popup-wrapper'
					},
					children: this.actionItems
				});
			},
			openDeleteConfirmPopup: function ()
			{
				this.getPropertiesPopup().close();
				this.removePopup = new BX.PopupWindow('report-visualconstructor-dashboard-widget-remove-popup-' + this.getId(), null, {
					closeIcon: {right: "20px", top: "10px"},
					titleBar: this.config.title,
					zIndex: 0,
					offsetLeft: 0,
					offsetTop: 0,
					draggable: {restrict: false},
					overlay: {backgroundColor: 'black', opacity: '80'},
					buttons: [
						new BX.PopupWindowButton({
							text: BX.message('DASHBOARD_WIDGET_REMOVE_ACCEPT_TITLE'),
							className: "popup-window-button-accept",
							events: {
								click: this.remove.bind(this)
							}
						}),
						new BX.PopupWindowButton({
							text: BX.message('DASHBOARD_WIDGET_REMOVE_CANCEL_TITLE'),
							className: "webform-button-link-cancel",
							events: {
								click: function ()
								{
									this.popupWindow.close();
								}
							}
						})
					],
					content: BX.message('DASHBOARD_WIDGET_REMOVE_CONTENT')
				});
				this.removePopup.show();
			},
			getDeleteConfirmPopup: function ()
			{
				return this.removePopup;
			},
			checkIsRendered: function ()
			{
				return this.isRendered;
			},
			setRenderStatus: function (status)
			{
				this.isRendered = status
			},
			/**
			 *
			 * @returns {ClientRect}
			 */
			getRectArea: function ()
			{
				if (!this.rectArea)
				{
					this.rectArea = BX.pos(this.getWidgetContainer());
					this.rectArea.middle = this.rectArea.left + this.rectArea.width / 2;
				}

				return this.rectArea;
			},
			resetRectArea: function ()
			{
				this.rectArea = null;
			},
			autoScroll: function (mouseCurrentXPosition, mouseCurrentYPosition)
			{
				var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
				var clientHeight = document.documentElement.clientHeight;
				var pointerPosition = mouseCurrentYPosition - scrollTop;

				if (pointerPosition >= (clientHeight - 50))
				{
					//auto scroll down
					this.scrollDown(mouseCurrentXPosition, mouseCurrentYPosition);
				}
				else if (pointerPosition <= 50)
				{
					//auto scroll up
					this.scrollUp(mouseCurrentXPosition, mouseCurrentYPosition);
				}
				else
				{
					this.stopScroll();
				}
			},
			scrollUp: function (mouseCurrentXPosition, mouseCurrentYPosition)
			{
				if (this.isScrollingUp)
				{
					return;
				}
				this.isScrollingUp = true;
				this.timer = setInterval(BX.delegate(function ()
				{
					var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
					window.scrollTo(0, scrollTop - 10);
					mouseCurrentYPosition -= 10;
					this.moveDragWidget(mouseCurrentXPosition, mouseCurrentYPosition);
				}, this), 20);
			},
			scrollDown: function (mouseCurrentXPosition, mouseCurrentYPosition)
			{
				if (this.isScrollingDown)
				{
					return;
				}
				this.isScrollingDown = true;
				this.timer = setInterval(BX.delegate(function ()
				{
					var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
					window.scrollTo(0, scrollTop + 10);
					mouseCurrentYPosition += 10;
					this.moveDragWidget(mouseCurrentXPosition, mouseCurrentYPosition);
				}, this), 20);
			},

			stopScroll: function ()
			{
				this.isScrollingUp = false;
				this.isScrollingDown = false;
				clearInterval(this.timer);
			}

		}
})();