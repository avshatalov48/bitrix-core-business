;(function ()
{
	"use strict";
	BX.namespace("BX.Report.VisualConstructor.Widget.Content");

	function decodeHtmlEntities(str)
	{
		var p = document.createElement("p");
		p.innerHTML = str;
		return p.innerText;
	}

	/**
	 * @param options
	 * @extends {BX.Report.Dashboard.Content}
	 * @constructor
	 */
	BX.Report.VisualConstructor.Widget.Content.AmChart = function (options)
	{
		BX.Report.Dashboard.Content.apply(this, arguments);
		this.chartWrapper = BX.create('div', {
			style: {
				height: this.getHeight() - 8 + 'px',
				paddingTop: '8px'
			}
		});

		BX.addCustomEvent(this.widget, 'Dashboard.Board.Widget:onAfterRender', function ()
		{
			if (this.data.isFilled)
			{
				if (!AmCharts.isReady)
				{
					AmCharts.ready(this.makeChart.bind(this));
				}
				else
				{
					this.makeChart();
				}
			}
		}.bind(this));
	};


	BX.Report.VisualConstructor.Widget.Content.AmChart.prototype = {
		__proto__: BX.Report.Dashboard.Content.prototype,
		constructor: BX.Report.VisualConstructor.Widget.Content.AmChart,

		clickHandler: null,
		render: function ()
		{
			jsDD.unregisterObject(this.getWidget().getWidgetContainer());
			this.getWidget().makeDraggable(this.getWidget().getHeadContainer());
			return this.chartWrapper;
		},
		makeChart: function ()
		{
			var monthNames = [];
			var shortMonthNames = [];
			for(var m = 1; m <= 12; m++)
			{
				monthNames.push(BX.message["MONTH_" + m.toString()]);
				shortMonthNames.push(BX.message["MON_" + m.toString()]);
			}

			AmCharts.monthNames = monthNames;
			AmCharts.shortMonthNames = shortMonthNames;

			if (!this.chart)
			{
				this.prepareDataForAmChart();
				if (this.data.dataProvider.length)
				{
					this.chart = AmCharts.makeChart(this.chartWrapper, this.data);
					if(this.clickHandler)
					{
						this.chart.addListener("clickGraphItem", this.clickHandler);
					}
				}
			}

			if (this.chart)
			{
				this.chart.invalidateSize();
			}
		},
		prepareDataForAmChart: function()
		{
			var func;

			if (this.data["graphs"] && BX.type.isArray(this.data["graphs"]))
			{
				this.data["graphs"].forEach(function(graph)
				{
					if(graph["balloonFunction"])
					{
						func = BX.Report.VC.Core.getFunction(graph["balloonFunction"]);
						if(BX.Type.isFunction(func))
						{
							graph["balloonFunction"] = func;
						}
						else
						{
							throw new Error("balloonFunction " + graph["balloonFunction"] + " is not a function");
						}
					}
					if(graph["title"])
					{
						graph["title"] = decodeHtmlEntities(graph["title"]);
					}
				});
			}
			if(this.data["clickGraphItem"])
			{
				func = BX.Report.VC.Core.getFunction(this.data["clickGraphItem"]);
				if(BX.Type.isFunction(func))
				{
					this.clickHandler = func;
					delete this.data["clickGraphItem"];
				}
				else
				{
					throw new Error("clickGraphItem event handler " + this.data["clickGraphItem"] + " is not a function");
				}
			}
			else
			{
				this.clickHandler = this.handleItemClick.bind(this)
			}
		},
		handleItemClick: function(event)
		{
			var valueField = event.item.graph.valueField.toString();
			var urlField = 'targetUrl';
			var dashPosition = valueField.search('_');

			if (dashPosition != -1)
			{
				var graphNum = valueField.substr(dashPosition + 1);
				urlField = urlField + "_" + graphNum;
			}

			if (!event.item.dataContext.hasOwnProperty(urlField))
			{
				return;
			}
			var url = event.item.dataContext[urlField];
			if(BX.type.isNotEmptyString(url))
			{
				if(BX.SidePanel)
				{
					BX.SidePanel.Instance.open(url, {
						cacheable: false
					});
				}
				else
				{
					window.open(url);
				}
			}
		}
	};


	BX.Report.VisualConstructor.Widget.Content.AmChart.PieDiagram = function(options)
	{
		BX.Report.VisualConstructor.Widget.Content.AmChart.apply(this, arguments);
	};

	BX.Report.VisualConstructor.Widget.Content.AmChart.PieDiagram.prototype = {
		__proto__: BX.Report.VisualConstructor.Widget.Content.AmChart.prototype,
		constructor: BX.Report.VisualConstructor.Widget.Content.AmChart.PieDiagram,
		render: function ()
		{
			var chartDiv = BX.Report.VisualConstructor.Widget.Content.AmChart.prototype.render.call(this);
            chartDiv.style.opacity = 0;
            setTimeout(function() {
                chartDiv.style.opacity = 1;
                chartDiv.style.transition = '200ms';
			}.bind(chartDiv), 100);
			jsDD.unregisterObject(this.getWidget().getHeadContainer());
			this.getWidget().makeDraggable(this.getWidget().getWidgetContainer());
			return chartDiv;
		}
	};



	BX.Report.VisualConstructor.Widget.Content.AmChart.Funnel = function(options)
	{
		BX.Report.VisualConstructor.Widget.Content.AmChart.apply(this, arguments);
		this.chartWrapper.style.paddingLeft = '25px';
	};

	BX.Report.VisualConstructor.Widget.Content.AmChart.Funnel.prototype = {
		__proto__: BX.Report.VisualConstructor.Widget.Content.AmChart.prototype,
		constructor: BX.Report.VisualConstructor.Widget.Content.AmChart.Funnel
	};

	BX.Report.VisualConstructor.Widget.Content.AmCharts4 = function (options)
	{
		BX.Report.Dashboard.Content.apply(this, arguments);
		this.currentColumnWidth = 0;

		this.chartWrapper = BX.create('div', {
			style: {
				height: this.getHeight() - 8 + 'px',
				paddingTop: '8px'
			}
		});

		BX.addCustomEvent(this.widget, 'Dashboard.Board.Widget:onAfterRender', function ()
		{
			if (this.data.isFilled)
			{
				this.makeChart();
			}
		}.bind(this));

		am4core.useTheme(am4themes_animated);
	};

	BX.Report.VisualConstructor.Widget.Content.AmCharts4.prototype = {
		__proto__: BX.Report.Dashboard.Content.prototype,
		constructor: BX.Report.VisualConstructor.Widget.Content.AmCharts4,

		render: function ()
		{
			jsDD.unregisterObject(this.getWidget().getWidgetContainer());
			this.getWidget().makeDraggable(this.getWidget().getHeadContainer());
			return this.chartWrapper;
		},
		makeChart: function ()
		{
			if (!this.chart)
			{
				if (this.data.data.length)
				{
					this.prepareDataForAmChart();
					this.chart = am4core.createFromConfig(this.data, this.chartWrapper, this.data.type);
					this.onAfterChartCreate();
				}
			}

			if (this.chart)
			{
				this.chart.invalidateLayout();
			}
		},
		prepareDataForAmChart: function()
		{
			this.data.xAxes.forEach(function(axis)
			{
				if(axis.renderer && axis.renderer.labels && axis.renderer.labels.template && axis.renderer.labels.template.ellipsis)
				{
					axis.renderer.labels.template.ellipsis = decodeHtmlEntities(axis.renderer.labels.template.ellipsis);
				}
			}, this);

			this.data.series.forEach(function(series)
			{
				if(series.columns && series.columns.adapter && series.columns.adapter.tooltipHTML)
				{
					var func = BX.Report.VC.Core.getFunction(series.columns.adapter.tooltipHTML);
					if(BX.Type.isFunction(func))
					{
						series.columns.adapter.tooltipHTML = func;
					}
					else
					{
						throw new Error("tooltipHTML adapter " + series.columns.adapter.tooltipHTML + " is not a function");
					}
				}
				if(!series.columns.events)
				{
					series.columns.events = {};
				}
				series.columns.events.hit = this.handleItemClick.bind(this);

			}, this);
		},
		onAfterChartCreate: function()
		{

		},
		handleItemClick: function(event)
		{
			if(!event.target.hasOwnProperty('valueUrl') || !BX.type.isNotEmptyString(event.target.valueUrl))
			{
				return;
			}

			if(BX.SidePanel)
			{
				BX.SidePanel.Instance.open(event.target.valueUrl, {
					cacheable: false
				});
			}
			else
			{
				window.open(event.target.valueUrl);
			}
		}
	};


	/**
	 * @param options
	 * @extends {BX.Report.Dashboard.Content}
	 * @constructor
	 */
	BX.Report.VisualConstructor.Widget.Content.Activity = function (options)
	{
		BX.Report.Dashboard.Content.apply(this, arguments);
		var cell = this.getWidget().getCell();
		if (cell)
		{
			BX.addCustomEvent(cell, 'BX.Report.Dashboard.Cell:clean', this.handlerClearCell.bind(this));
		}
		this.graph = null;
	};

	BX.Report.VisualConstructor.Widget.Content.Activity.prototype = {
		__proto__: BX.Report.Dashboard.Content.prototype,
		constructor: BX.Report.VisualConstructor.Widget.Activity,
		handlerClearCell: function()
		{
			BX.Report.VC.PopupWindowManager.closeAllPopups();
		},
		render: function()
		{
			jsDD.unregisterObject(this.getWidget().getWidgetContainer());
			this.getWidget().makeDraggable(this.getWidget().getHeadContainer());

			if (!this.graphContainer)
			{
				this.graphContainer = BX.create('div', {
					style: {
						height: this.getHeight() + 'px'
					}
				});
				var graph = new BX.ActivityTileWidget({
					renderTo: this.graphContainer,
					labelY: this.data.config.labelY,
					labelX: this.data.config.labelX,
					items: this.data.items
				});
				graph.render();
			}


			return this.graphContainer;
		}
	};


	/**
	 * @param options
	 * @extends {BX.Report.Dashboard.Content}
	 * @constructor
	 */
	BX.Report.VisualConstructor.Widget.Content.NumberBlock = function (options)
	{
		BX.Report.Dashboard.Content.Html.apply(this, arguments);
	};

	BX.Report.VisualConstructor.Widget.Content.NumberBlock.prototype = {
		__proto__: BX.Report.Dashboard.Content.Html.prototype,
		constructor: BX.Report.VisualConstructor.Widget.NumberBlock,
		render: function()
		{
			this.getWidget().setColor('inherit');
			this.getWidget().applyColor();
			this.getWidget().getHeadContainer().style.backgroundColor = this.getColor();
			this.getWidget().getWidgetWrapper().style.backgroundColor = 'inherit';

			var color = this.getColor().substring(1, 7);

			var isWidgetDarkMode = BX.Report.Dashboard.Utils.isDarkColor(color);
			if (isWidgetDarkMode)
			{
				this.getWidget().getWidgetWrapper().classList.remove('report-visualconstructor-dashboard-widget-light');
				this.getWidget().getWidgetWrapper().classList.add('report-visualconstructor-dashboard-widget-dark');
			}
			else
			{
				this.getWidget().getWidgetWrapper().classList.remove('report-visualconstructor-dashboard-widget-dark');
				this.getWidget().getWidgetWrapper().classList.add('report-visualconstructor-dashboard-widget-light');
			}

			return BX.Report.Dashboard.Content.Html.prototype.render.call(this);
		}
	};


	/**
	 * @param options
	 * @extends {BX.Report.Dashboard.Content}
	 * @constructor
	 */
	BX.Report.VisualConstructor.Widget.Content.GroupedDataGrid = function (options)
	{
		BX.Report.Dashboard.Content.Html.apply(this, arguments);
	};

	BX.Report.VisualConstructor.Widget.Content.GroupedDataGrid.prototype = {
		__proto__: BX.Report.Dashboard.Content.Html.prototype,
		constructor: BX.Report.VisualConstructor.Widget.GroupedDataGrid,
		getHeight: function ()
		{

			if (this.htmlContentWrapper.parentNode)
			{
				var content = BX.Report.Dashboard.Content.Html.prototype.render.call(this);
				return content.clientHeight;
			}
			else
			{
				return 325;
			}
		},
		render: function()
		{
			return BX.Report.Dashboard.Content.Html.prototype.render.call(this);
		}
	}
})();