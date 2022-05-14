(function (exports,currency,main_core) {
	'use strict';

	var StoreStockSaleChart = /*#__PURE__*/function () {
	  function StoreStockSaleChart(params) {
	    babelHelpers.classCallCheck(this, StoreStockSaleChart);
	    babelHelpers.defineProperty(this, "_legendIsPrepared", false);
	    babelHelpers.defineProperty(this, "_series", []);
	    babelHelpers.defineProperty(this, "_sumStoredColor", '#42659B');
	    babelHelpers.defineProperty(this, "_sumSoldColor", '#6DA3E6');
	    this._chartId = params.chartId;
	    this._boardId = params.boardId;
	    this._widgetId = params.widgetId;
	    this._board = BX.VisualConstructor.BoardRepository.getBoard(this._boardId);
	    this._widget = this._board.dashboard.getWidget(this._widgetId);
	    this._storeInfoPopupTemplate = params.storeInfoPopupTemplate;
	    this._storeInfoPopup = null;
	    this._sliderUrl = params.chartData.sliderUrl;
	    this._enablePopup = Boolean(params.chartData.enablePopup);
	    this._currencySymbol = params.chartData.currencySymbol;
	    this._chartCurrency = params.chartData.currency;
	    this._isOneColumn = Boolean(params.chartData.isOneColumn);
	    this.createChart(params.chartData);
	    this.updateWidgetTitle();
	    BX.addCustomEvent(this._widget, "Dashboard.Board.Widget:onAfterRender", this.updateWidgetTitle.bind(this));
	  }

	  babelHelpers.createClass(StoreStockSaleChart, [{
	    key: "createChart",
	    value: function createChart(chartData) {
	      var _this = this;

	      window.am4core.useTheme(am4themes_animated);
	      this._chart = am4core.create(this._chartId, am4charts.XYChart);
	      this._chart.zoomOutButton.readerTitle = main_core.Loc.getMessage('STORE_STOCK_CHART_ZOOMOUT_TITLE');
	      this._chart.data = [];
	      this._chart.legend = new am4charts.Legend();
	      this._chart.legend.position = 'bottom';
	      this._chartLabel = chartData._chartLabel;
	      this.createAxes();
	      this.fillChartData(chartData.data);
	      this.fillSeries();

	      if (chartData.isOneColumn) {
	        this._categoryAxis.renderer.labels.template.html = chartData.chartLabel;
	      }

	      this._chart.events.on("inited", function () {
	        _this.onChartLoaded();
	      }, this);
	    }
	  }, {
	    key: "createAxes",
	    value: function createAxes() {
	      this._categoryAxis = this._chart.xAxes.push(new am4charts.CategoryAxis());
	      this._categoryAxis.dataFields.category = 'ID';
	      this._categoryAxis.renderer.grid.template.opacity = 0;
	      this._valueAxis = this._chart.yAxes.push(new am4charts.ValueAxis());
	      this._valueAxis.min = 0;
	      this._valueAxis.renderer.grid.template.opacity = 0;
	      this._valueAxis.renderer.ticks.template.strokeOpacity = 0.5;
	      this._valueAxis.renderer.ticks.template.length = 10;
	      this._valueAxis.renderer.line.strokeOpacity = 0.5;
	      this._valueAxis.renderer.baseGrid.disabled = true;
	      this._valueAxis.renderer.minGridDistance = 40;
	      this._valueAxis.calculateTotals = true; // some space needed for the total label

	      this._categoryAxis.renderer.labels.template.marginRight = 40;
	    }
	  }, {
	    key: "fillChartData",
	    value: function fillChartData(chartData) {
	      this._chart.data = chartData.map(function (columnData, index) {
	        columnData['ID'] = index;
	        return columnData;
	      });
	    }
	  }, {
	    key: "fillSeries",
	    value: function fillSeries() {
	      var _this2 = this;

	      var emptySeries = this.createSeries("empty", "empty");
	      emptySeries.hiddenInLegend = true;
	      emptySeries.maskBullets = false;
	      var sumStoredTitle = main_core.Loc.getMessage('STORE_STOCK_CHART_SUM_STORED_TITLE');
	      var sumStoredSeries = this.createSeries("SUM_STORED", sumStoredTitle);
	      sumStoredSeries.fill = am4core.color(this._sumStoredColor);
	      sumStoredSeries.stroke = am4core.color("#FFFFFF");

	      this._series.push(sumStoredSeries);

	      var sumSoldTitle = main_core.Loc.getMessage('STORE_STOCK_CHART_SUM_SOLD_TITLE');
	      var sumSoldSeries = this.createSeries("SUM_SOLD", sumSoldTitle);
	      sumSoldSeries.fill = am4core.color(this._sumSoldColor);
	      sumSoldSeries.stroke = am4core.color("#FFFFFF");

	      this._series.push(sumSoldSeries);

	      sumStoredSeries.events.on("inited", function (eventObject) {
	        _this2.onSeriesLoad(eventObject.target, 'SUM_STORED');
	      }, this);
	      sumSoldSeries.events.on("inited", function (eventObject) {
	        _this2.onSeriesLoad(eventObject.target, 'SUM_SOLD');
	      }, this);
	    }
	  }, {
	    key: "onSeriesLoad",
	    value: function onSeriesLoad(seriesObject, seriesTypeId) {
	      if (this._chart.legend.labels.values.length === this._series.length) {
	        this.prepareChartLegend();
	      }

	      if (this._enablePopup) {
	        this.bindPopupEvents(seriesObject, seriesTypeId);
	      }
	    }
	  }, {
	    key: "bindPopupEvents",
	    value: function bindPopupEvents(seriesObject, seriesTypeId) {
	      for (var i = 0; i < seriesObject.columns.length; i++) {
	        var column = {
	          columnObject: seriesObject.columns.getIndex(i),
	          columnTypeId: seriesTypeId,
	          storeData: this._chart.data[i]
	        };
	        BX.bind(column.columnObject.group.node, 'mouseover', this.handleColumnMouseOver.bind(this, column));
	        BX.bind(column.columnObject.group.node, 'mouseout', this.handleColumnMouseOut.bind(this));

	        if (this._sliderUrl) {
	          column.columnObject.group.node.style.cursor = 'pointer';
	          BX.bind(column.columnObject.group.node, 'click', this.openStoreStockChartGridSlider.bind(this));
	        }
	      }
	    }
	  }, {
	    key: "prepareChartLegend",
	    value: function prepareChartLegend() {
	      if (this._legendIsPrepared) {
	        return;
	      } else {
	        this._legendIsPrepared = true;
	      }

	      var chartLegends = this._chart.legend.labels.values;
	      var currencyPostfix = ', ' + this._currencySymbol;

	      for (var i = 0; i < chartLegends.length; i++) {
	        chartLegends[i].fill = am4core.color("#000000");
	        chartLegends[i].html = chartLegends[i].currentText + currencyPostfix;
	      }
	    }
	  }, {
	    key: "handleColumnMouseOver",
	    value: function handleColumnMouseOver(column) {
	      this.openStoreInfoPopup(column);
	    }
	  }, {
	    key: "handleColumnMouseOut",
	    value: function handleColumnMouseOut() {
	      if (this._storeInfoPopup !== null) {
	        this._storeInfoPopup.close();
	      }
	    }
	  }, {
	    key: "openStoreStockChartGridSlider",
	    value: function openStoreStockChartGridSlider() {
	      BX.SidePanel.Instance.open(this._sliderUrl, {
	        cacheable: false,
	        allowChangeTitle: false,
	        allowChangeHistory: false
	      });
	    }
	  }, {
	    key: "openStoreInfoPopup",
	    value: function openStoreInfoPopup(column) {
	      if (this._storeInfoPopup !== null) {
	        this._storeInfoPopup.destroy();
	      }

	      var popupData = {
	        typeId: column.columnTypeId,
	        storeData: column.storeData
	      };
	      this._storeInfoPopup = new BX.PopupWindow('widget-store-stock-info-popup', column.columnObject.group.node, {
	        bindOptions: {
	          position: "top"
	        },
	        offsetLeft: 30,
	        offsetTop: -1,
	        noAllPaddings: true,
	        autoHide: false,
	        draggable: {
	          restrict: false
	        },
	        cacheable: false,
	        content: this.getFormedStoreInfoPopupContent(popupData)
	      });

	      this._storeInfoPopup.show();
	    }
	  }, {
	    key: "getFormedStoreInfoPopupContent",
	    value: function getFormedStoreInfoPopupContent(popupData) {
	      var popup = {
	        title: this._storeInfoPopupTemplate.querySelector('#chart-popup-template-title'),
	        sum: this._storeInfoPopupTemplate.querySelector('#chart-popup-template-sum'),
	        sumProc: this._storeInfoPopupTemplate.querySelector('#chart-popup-template-sum-proc')
	      };

	      switch (popupData.typeId) {
	        case 'SUM_SOLD':
	          popup.title.innerText = main_core.Loc.getMessage('STORE_STOCK_CHART_POPUP_SOLD_TITLE');
	          popup.sum.innerHTML = this.sumCurrencyFormat(popupData.storeData['SUM_SOLD']);
	          popup.sumProc.innerText = this.formatPercentValue(popupData.storeData['SUM_SOLD_PERCENT']);
	          this._storeInfoPopupTemplate.style.borderColor = this._sumSoldColor;
	          break;

	        case 'SUM_STORED':
	          popup.title.innerText = main_core.Loc.getMessage('STORE_STOCK_CHART_POPUP_STORED_TITLE');
	          popup.sum.innerHTML = this.sumCurrencyFormat(popupData.storeData['SUM_STORED']);
	          popup.sumProc.innerText = this.formatPercentValue(popupData.storeData['SUM_STORED_PERCENT']);
	          this._storeInfoPopupTemplate.style.borderColor = this._sumStoredColor;
	          break;
	      }

	      return this._storeInfoPopupTemplate;
	    }
	  }, {
	    key: "sumCurrencyFormat",
	    value: function sumCurrencyFormat(sum) {
	      sum = parseFloat(sum);
	      return BX.Currency.currencyFormat(sum, this._chartCurrency, true);
	    }
	  }, {
	    key: "formatPercentValue",
	    value: function formatPercentValue(percentValue) {
	      return parseFloat(percentValue).toFixed(2) + '%';
	    }
	  }, {
	    key: "createSeries",
	    value: function createSeries(field, name) {
	      var series = this._chart.series.push(new am4charts.ColumnSeries());

	      series.dataFields.valueY = field;
	      series.dataFields.categoryX = 'ID';
	      series.stacked = true;
	      series.name = name;
	      return series;
	    }
	  }, {
	    key: "updateWidgetTitle",
	    value: function updateWidgetTitle() {
	      BX.clean(this._widget.layout.titleContainer);
	      BX.adjust(this._widget.layout.titleContainer, {
	        children: [BX.create("span", {
	          text: this._widget.config.title
	        }), this._hint = BX.UI.Hint.createNode(main_core.Loc.getMessage('STORE_STOCK_CHART_HINT_TITLE'))]
	      });
	      BX.bind(this._hint, "click", function () {
	        if (top.BX.Helper) {
	          top.BX.Helper.show("redirect=detail&code=15503856");
	        }
	      });
	    }
	  }, {
	    key: "onChartLoaded",
	    value: function onChartLoaded() {
	      if (!this._isOneColumn) {
	        this.fillColumnsTitle();
	      } else {
	        this._categoryAxis.dataItemsByCategory.getKey('0').text = '';
	      }
	    }
	  }, {
	    key: "fillColumnsTitle",
	    value: function fillColumnsTitle() {
	      var _this3 = this;

	      this._chart.data.forEach(function (columnData) {
	        _this3._categoryAxis.dataItemsByCategory.getKey(columnData['ID']).text = columnData['STORE_NAME'];
	      });
	    }
	  }]);
	  return StoreStockSaleChart;
	}();

	main_core.Reflection.namespace('BX.Catalog.Report.StoreStock').StoreStockSaleChart = StoreStockSaleChart;

}((this.window = this.window || {}),BX,BX));
//# sourceMappingURL=script.js.map
