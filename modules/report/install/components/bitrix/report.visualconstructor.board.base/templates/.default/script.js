;(function ()
{
	BX.namespace("BX.VisualConstructor");
	"use strict";


	BX.VisualConstructor.BoardBase = function (options)
	{
		this.renderTo = options.renderTo;
		this.boardId = options.boardId;
		this.filterId = options.filterId;
		this.isNowFiltering = false;
		this.rows = options.rows;
		this.dashboard = null;
		this.demoMode = options.demoMode || false;
		this.defaultBoard = options.defaultBoard || false;
		this.layout = {
			demoModeFlagContainer: null
		};
		this.init();
	};


	BX.VisualConstructor.BoardBase.prototype = {
		init: function ()
		{
			this.buildDashboard();
			this.getDashboard().render();
			this.renderTo.style.position = 'relative';
			this.renderTo.appendChild(this.getDemoModeFlagContainer());

			if (this.isDemoMode())
			{
				this.showDemoFlag();
			}
			BX.addCustomEvent("BX.Report.VisualConstructor.afterWidgetAdd", this.loadWidget.bind(this));
			BX.addCustomEvent(this.dashboard, "BX.Report.Dashboard.Widget:afterMove", this.saveWidgetPositions.bind(this));
			BX.addCustomEvent("BX.Report.VisualConstructor.Widget.Form:afterSave", this.handleFormSave.bind(this));
			BX.addCustomEvent("BX.Report.VisualConstructor.Widget.Form:cancel", this.closeSlidePanel.bind(this));
			BX.addCustomEvent(this.dashboard, "BX.Report.Dashboard.Board:afterRowRemove", this.removeRow.bind(this));
			BX.addCustomEvent(this.dashboard, "BX.Report.Dashboard.Board:afterRowsAdjust", this.adjustRows.bind(this));
			BX.addCustomEvent('BX.Main.Filter:beforeApply', this.onBeforeApplyFilter.bind(this));
			BX.addCustomEvent('BX.Main.Filter:apply', this.onApplyFilter.bind(this));
			BX.VisualConstructor.BoardRepository.addBoard(this);
		},
		showDemoFlag: function()
		{
			this.getDemoModeFlagContainer().classList.add('report-visualconstructor-demo-flag-visible');
		},
		hideDemoFlag: function()
		{
			this.getDemoModeFlagContainer().classList.remove('report-visualconstructor-demo-flag-visible');
		},
		getDemoModeFlagContainer: function()
		{
			if (this.layout.demoModeFlagContainer)
			{
				return this.layout.demoModeFlagContainer;
			}

			this.layout.demoModeFlagContainer = BX.create('div', {
				attrs: {
					className: 'report-visualconstructor-demo-flag'
				},
				children: [
					BX.create('div', {
						attrs: {
							className: 'report-visualconstructor-demo-flag-text'
						},
						text: BX.message('DASHBOARD_DEMO_FLAG_TEXT')
					}),
					BX.create('div', {
						attrs: {
							className: 'report-visualconstructor-demo-flag-close-button'
						},
						events: {
							click: this.hideDemoFlag.bind(this)
						}
					}),
					BX.create('div', {
						attrs: {
							className: 'report-visualconstructor-demo-flag-hide-link'
						},
						text: BX.message('DASHBOARD_DEMO_FLAG_HIDE_LINK'),
						events: {
							click: this.toggleDemoMode.bind(this)
						}
					})
				]
			});
			return this.layout.demoModeFlagContainer;
		},
		toggleDemoMode: function()
		{
			BX.Report.VC.Core.ajaxPost('board.toggleMode', {
				data: {
					boardKey: this.boardId
				},
				onFullSuccess: BX.delegate(function (response) {
					if (!response.errors.length)
					{
						this.getDashboard().clearRows();
						this.getDashboard().destroy();
						this.setDemoMode(response.data.demoMode);

						BX.Report.VC.Core.ajaxGet('widget.loadByBoardId', {
							urlParams: {
								'boardId': this.boardId
							},
							onFullSuccess: BX.delegate(function (result) {
								if (result.data.rows)
								{
									this.getDashboard().addRows(result.data.rows);
									this.getDashboard().render();
								}
							}, this)
						});
					}
				}, this)
			});
		},
		onBeforeApplyFilter: function(filterId)
		{
			if (this.filterId !== filterId)
			{
				return;
			}

			if (this.isNowFiltering)
			{
				return;
			}

			this.getDashboard().clearRows();
			this.getDashboard().destroy();

		},
		onApplyFilter: function(filterId, data, ctx, promise, params)
		{




			if (this.filterId !== filterId)
			{
				return;
			}


			if (this.isNowFiltering)
			{

				return;
			}

			this.isNowFiltering = true;

			BX.Report.VC.Core.ajaxGet('widget.loadByBoardId', {
				urlParams: {
					'boardId': this.getBoardId()
				},
				onFullSuccess: BX.defer(function (result)
				{
					if (result.data.rows)
					{
						this.isNowFiltering = false;
						this.getDashboard().addRows(result.data.rows);
						this.getDashboard().render();
					}
				}, this)
			});

		},
		isDemoMode: function()
		{
			return this.demoMode;
		},
		setDemoMode: function(mode)
		{
			this.demoMode = mode;
			if (!this.demoMode)
			{
				this.hideDemoFlag();
			}
			else
			{
				this.showDemoFlag();
			}
		},
		getBoardId: function()
		{
			return this.boardId;
		},
		saveWidgetPositions: function(widgets, row)
		{
			if (row.isPseudo())
			{
				BX.Report.VC.Core.ajaxPost('row.add', {
					data: {
						params: {
							boardKey: this.getBoardId(),
							layoutMap: row.getRowLayout().getMapWithoutDomElements()
						}
					},
					onFullSuccess: BX.delegate(function(response) {
						row.setId(response.data.id);
						this.getDashboard().adjustRowsWeight();
						this.sendWidgetUpdateRequests(widgets);
					}, this)
				});
			}
			else
			{
				this.sendWidgetUpdateRequests(widgets);
			}
		},
		adjustRows: function(rows)
		{
			var rowsIdsWithWeight = [];
			for (var i = 0; i < rows.length; i++)
			{
				rowsIdsWithWeight[rows[i].getId()] = {
					weight: rows[i].getWeight()
				}
			}
			BX.Report.VC.Core.ajaxPost('row.adjustWeights', {
				data: {
					boardKey: this.getBoardId(),
					rows: rowsIdsWithWeight
				},
				onFullSuccess: function(response)
				{

				}
			});
		},
		sendWidgetUpdateRequests: function(widgets)
		{
			for (var i = 0; i < widgets.length; i++)
			{
				BX.Report.VC.Core.ajaxPost('widget.update', {
					data: {
						boardKey: this.getBoardId(),
						widgetId: widgets[i].getId(),
						params: {
							rowId: widgets[i].getCell().getRow().getId(),
							rowLayoutMap:  widgets[i].getCell().getRow().getRowLayout().getMapWithoutDomElements(),
							cellId: widgets[i].getCell().getId()
						}
					},
					onFullSuccess: function(response)
					{

					}
				});
			}
		},
		loadWidget: function (params)
		{
			BX.Report.VC.Core.ajaxGet('widget.load', {
				urlParams: {
					'widgetId': params.widgetId
				},
				onFullSuccess: BX.defer(function (result)
				{
					var row = this.getDashboard().getRow(result.data.row.id);
					if (!row)
					{
						row = new BX.Report.Dashboard.Row({
							id: result.data.row.id,
							layoutMap: result.data.row.layoutMap
						});
						this.getDashboard().addRowToStart(row);
						this.getDashboard().adjustRowsWeight();

					}
					row.addWidget(result.data)
				}, this)
			});
		},
		handleFormSave: function (params)
		{
			switch (params.mode) {
				case 'create':
					this.handleWidgetCreate(params);
					break;
				case 'update':
					this.handleWidgetUpdate(params);
					break;
			}

		},
		handleWidgetCreate: function(params)
		{
			this.loadWidget(params);
		},
		handleWidgetUpdate: function(params)
		{
			var widget = this.getDashboard().getWidget(params.widgetId);
			widget.reload(function() {widget.sidePanel.close()});
		},
		closeSlidePanel: function()
		{
			var sidePanel = BX.SidePanel.Instance;
			sidePanel.close();
		},
		removeRow: function(params)
		{


			//HACK:
			setTimeout(function() {
				if (!params.row.isPseudo())
				{
					BX.Report.VC.Core.ajaxPost('row.delete', {
						data: {
							params: {
								boardId: params.row.getBoard().getId(),
								rowId: params.row.getId()
							}
						},
						onFullSuccess: function(result)
						{
						}
					});
				}
			}, 3000);

		},
		getDashboard: function()
		{
			return this.dashboard;
		},
		buildDashboard: function()
		{
			this.dashboard  = new BX.Report.Dashboard.Board({
				id: this.getBoardId(),
				renderTo: this.renderTo,
				rows: this.rows,
				designerMode: false,
				defaultWidgetClass: 'BX.VisualConstructor.Widget',
				isDefault: this.defaultBoard
			});
		},
		reBuildDashboard: function(rows)
		{
			this.dashboard = new BX.Report.Dashboard.Board({
				id: this.getBoardId(),
				renderTo: this.renderTo,
				rows: rows,
				designerMode: false,
				defaultWidgetClass: 'BX.VisualConstructor.Widget',
				isDefault: this.defaultBoard
			});
		}
	};

	BX.VisualConstructor.BoardRepository = {
		dashboards: [],
		addBoard: function(board)
		{
			this.dashboards.push(board);
		},
		getBoard: function(id)
		{
			var boards = this.getBoards();
			for (var i = 0; i < boards.length; i++)
			{
				if (boards[i].getBoardId() === id)
				{
					return boards[i];
				}
			}
			return false;
		},
		getBoards: function()
		{
			return this.dashboards;
		}
	};

	/**
	 * @param options
	 * @extends {BX.Report.Dashboard.Widget}
	 * @constructor
	 */
	BX.VisualConstructor.Widget = function (options)
	{
		this.sidePanel = null;
		options.actionItems = [
			BX.create('div', {
				text: BX.message('DASHBOARD_WIDGET_PROPERTIES_TITLE'),
				events: {
					click: this.onPropertiesClickHandler.bind(this)
				}
			})
		];
		options.events = options.events || {};
		BX.Report.Dashboard.Widget.apply(this, arguments);
		this.layout.timePeriodMark = null;
		this.timePeriodMark = options.config.timePeriod || '';
	};

	BX.VisualConstructor.Widget.prototype = {
		__proto__: BX.Report.Dashboard.Widget.prototype,
		constructor: BX.VisualConstructor.Widget,
		setTimePeriodMark: function(mark)
		{
			this.timePeriodMark = mark;
		},
		onPropertiesClickHandler: function ()
		{
			if (this.propertiesPopup)
			{
				this.getPropertiesPopup().close();
			}


			this.sidePanel = BX.SidePanel.Instance;
			this.sidePanel.open("widget:properties-edit-" + this.id, {
				cacheable: false,
				contentCallback: BX.delegate(function getSliderContent(slider) {
					var promise = new BX.Promise();
					BX.Report.VC.Core.ajaxGet('widget.showConfigurationForm', {
						urlParams: {
							boardId: this.getRow().getBoard().getId(),
							widgetId: this.id
						},
						onFullSuccess: BX.delegate(function(result) {
							slider.getData().set("configurationFormContent", result.data);
							promise.fulfill(result.data);
						}, this)
					});
					return promise;
				}, this),
				animationDuration: 100,
				width: 950,
				events: {
					onLoad: function(event) {
						var slider = event.getSlider();
						BX.html(slider.layout.content, slider.getData().get("configurationFormContent"));
					},
					onClose: function()
					{
						BX.Report.VC.PopupWindowManager.closeAllPopups()
					}
				}
			});
		},
		reload: function (callback)
		{
			BX.Report.VC.Core.ajaxGet('widget.load', {
				urlParams: {
					'widgetId': this.id
				},
				onFullSuccess: BX.delegate(function (result)
				{
					this.config.title = result.data.config.title;
					this.setTimePeriodMark(result.data.config.timePeriod);
					this.setColor(result.data.config.color);
					this.setContent(result.data.content);
					BX.Report.Dashboard.Widget.prototype.lazyLoad.call(this);
					this.getCell().setHeight(this.getHeight());
					callback.call(this);
				}, this)
			});

		},
		lazyLoad: function()
		{
			if (!this.loaded)
			{
				BX.Report.VC.Core.ajaxGet('widget.load', {
					urlParams: {
						'widgetId': this.id
					},
					onFullSuccess: BX.delegate(function (result)
					{
						this.config.title = result.data.config.title;
						this.setColor(result.data.config.color);
						this.setTimePeriodMark(result.data.config.timePeriod);
						this.setContent(result.data.content);
						BX.Report.Dashboard.Widget.prototype.lazyLoad.call(this);
					}, this)
				});
			}
		},
		remove: function ()
		{
			BX.Report.VC.Core.ajaxPost('widget.remove', {
				data: {
					params: {
						boardId: this.getRow().getBoard().getId(),
						widgetId: this.id
					}
				},
				onFullSuccess: BX.defer(function ()
				{
					BX.Report.Dashboard.Widget.prototype.remove.call(this);
				}, this)
			});
		},
		getContentWrapper: function()
		{
			var contentWrapper = BX.Report.Dashboard.Widget.prototype.getContentWrapper.call(this);
			if (this.checkIsRendered())
			{
				BX.cleanNode(contentWrapper);
			}

			if (this.getCell() !== null && !this.getCell().getRow().getBoard().isDefault)
			{
				contentWrapper.appendChild(this.getWidgetTimePeriodMark());
			}
			else if(this.getCell() === null)
			{
				contentWrapper.appendChild(this.getWidgetTimePeriodMark());
			}

			return contentWrapper;
		},
		getWidgetTimePeriodMark: function()
		{
			var result = null;
			if (this.layout.timePeriodMark)
			{
				result = this.layout.timePeriodMark;
			}
			else
			{
				result = BX.create('div', {
					attrs: {
						className: 'report-visualconstructor-widget-timer-period-mark-container'
					}
				});
			}
			result.innerHTML = this.timePeriodMark;

			this.layout.timePeriodMark = result;
			return this.layout.timePeriodMark;
		},
		getControlsContainer: function()
		{
			var controlsContainer = BX.Report.Dashboard.Widget.prototype.getControlsContainer.call(this);
			if (!this.getCell().getRow().getBoard().isDefault)
			{
				controlsContainer.appendChild(this.settingsButtonInHeader());
			}
			else
			{
				controlsContainer.classList.add('report-visualconstuctor-widget-property-invisible');
			}



			return controlsContainer;
		},
		settingsButtonInHeader: function()
		{
			if (this.layout.settingsButtonContainer)
			{
				return this.layout.settingsButtonContainer;
			}

			this.layout.settingsButtonContainer = BX.create('div', {
				attrs: {
					className: 'report-visualconstructor-properties-in-heed-button'
				},
				text: BX.message('DASHBOARD_WIDGET_PROPERTIES_BUTTON_HEAD_TITLE'),
				events: {
					click: this.onPropertiesClickHandler.bind(this)
				}
			});
			return this.layout.settingsButtonContainer;
		}


	}
})();