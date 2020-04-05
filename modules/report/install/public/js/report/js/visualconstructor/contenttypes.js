;(function ()
{
	"use strict";
	BX.namespace("BX.Report.VisualConstructor.Widget.Content");


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

		BX.addCustomEvent(this.widget, 'Dashboard.Board.Widget:onAfterRender', BX.delegate(function ()
		{
			if (this.data.isFilled)
			{
				if (!AmCharts.isReady)
				{
					AmCharts.ready(BX.delegate(this.makeChart, this));
				}
				else
				{
					this.makeChart();
				}
			}
		}, this));

	};


	BX.Report.VisualConstructor.Widget.Content.AmChart.prototype = {
		__proto__: BX.Report.Dashboard.Content.prototype,
		constructor: BX.Report.VisualConstructor.Widget.Content.AmChart,
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
				}
			}

			if (this.chart)
			{
				this.chart.invalidateSize();
			}
		},
		prepareDataForAmChart: function()
		{

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

	/**
	 * @param options
	 * @extends {BX.Report.Dashboard.Content}
	 * @constructor
	 */
	BX.Report.VisualConstructor.Widget.Content.Activity = function (options)
	{
		BX.Report.Dashboard.Content.apply(this, arguments);
		BX.addCustomEvent(this.getWidget().getCell(), 'BX.Report.Dashboard.Cell:clean', this.handlerClearCell.bind(this));
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