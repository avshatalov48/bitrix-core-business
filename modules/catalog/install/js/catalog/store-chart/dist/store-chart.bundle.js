this.BX = this.BX || {};
(function (exports,main_popup,currency_currencyCore,main_core) {
	'use strict';

	var _templateObject;

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _chartId = /*#__PURE__*/new WeakMap();

	var _chart = /*#__PURE__*/new WeakMap();

	var _chartPopup = /*#__PURE__*/new WeakMap();

	var _isChartCommon = /*#__PURE__*/new WeakMap();

	var _onChartClick = /*#__PURE__*/new WeakMap();

	var _onSeriesLabelLoadHandler = /*#__PURE__*/new WeakMap();

	var _legendIsPrepared = /*#__PURE__*/new WeakMap();

	var _axes = /*#__PURE__*/new WeakMap();

	var _seriesList = /*#__PURE__*/new WeakMap();

	var _seriesLoadedLen = /*#__PURE__*/new WeakMap();

	var _isPopupEnabled = /*#__PURE__*/new WeakMap();

	var _initializeChart = /*#__PURE__*/new WeakSet();

	var _initColumnsTitle = /*#__PURE__*/new WeakSet();

	var _initAxes = /*#__PURE__*/new WeakSet();

	var _initColumnsData = /*#__PURE__*/new WeakSet();

	var _initSeries = /*#__PURE__*/new WeakSet();

	var _onSeriesLoaded = /*#__PURE__*/new WeakSet();

	var _prepareChartLegend = /*#__PURE__*/new WeakSet();

	var _onChartLoaded = /*#__PURE__*/new WeakSet();

	var _bindPopupEvents = /*#__PURE__*/new WeakSet();

	var _onStuckMouseOver = /*#__PURE__*/new WeakSet();

	var _onStuckMouseOut = /*#__PURE__*/new WeakSet();

	var StackedBarChart = /*#__PURE__*/function () {
	  function StackedBarChart(props) {
	    babelHelpers.classCallCheck(this, StackedBarChart);

	    _classPrivateMethodInitSpec(this, _onStuckMouseOut);

	    _classPrivateMethodInitSpec(this, _onStuckMouseOver);

	    _classPrivateMethodInitSpec(this, _bindPopupEvents);

	    _classPrivateMethodInitSpec(this, _onChartLoaded);

	    _classPrivateMethodInitSpec(this, _prepareChartLegend);

	    _classPrivateMethodInitSpec(this, _onSeriesLoaded);

	    _classPrivateMethodInitSpec(this, _initSeries);

	    _classPrivateMethodInitSpec(this, _initColumnsData);

	    _classPrivateMethodInitSpec(this, _initAxes);

	    _classPrivateMethodInitSpec(this, _initColumnsTitle);

	    _classPrivateMethodInitSpec(this, _initializeChart);

	    _classPrivateFieldInitSpec(this, _chartId, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _chart, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _chartPopup, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _isChartCommon, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _onChartClick, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _onSeriesLabelLoadHandler, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _legendIsPrepared, {
	      writable: true,
	      value: false
	    });

	    _classPrivateFieldInitSpec(this, _axes, {
	      writable: true,
	      value: {
	        categoryAxis: am4charts.CategoryAxis,
	        valueAxis: am4charts.ValueAxis
	      }
	    });

	    _classPrivateFieldInitSpec(this, _seriesList, {
	      writable: true,
	      value: []
	    });

	    _classPrivateFieldInitSpec(this, _seriesLoadedLen, {
	      writable: true,
	      value: 0
	    });

	    _classPrivateFieldInitSpec(this, _isPopupEnabled, {
	      writable: true,
	      value: void 0
	    });

	    babelHelpers.classPrivateFieldSet(this, _isPopupEnabled, props === null || props === void 0 ? void 0 : props.isPopupEnabled);
	    babelHelpers.classPrivateFieldSet(this, _chartPopup, null);
	    babelHelpers.classPrivateFieldSet(this, _onChartClick, props.onChartClick);
	    babelHelpers.classPrivateFieldSet(this, _onSeriesLabelLoadHandler, props === null || props === void 0 ? void 0 : props.onSeriesLabelLoad);

	    _classPrivateMethodGet(this, _initializeChart, _initializeChart2).call(this, props.chartProps);
	  }

	  babelHelpers.createClass(StackedBarChart, null, [{
	    key: "formPopupContent",
	    value: function formPopupContent(color, title, innerContent) {
	      return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div id=\"chart-popup-template\" class=\"stacked-bar-chart-popup\" style=\"border-color: ", ";\">\n\t\t\t\t<div class=\"stacked-bar-chart-popup-head\">\n\t\t\t\t\t<div id=\"chart-popup-template-title\" class=\"stacked-bar-chart-popup-title\">", "</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"stacked-bar-chart-popup-main\">\n\t\t\t\t\t<div class=\"stacked-bar-chart-popup-info\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), color, title, innerContent);
	    }
	  }, {
	    key: "createSeries",
	    value: function createSeries(seriesData) {
	      var series = new am4charts.ColumnSeries();
	      series.dataFields.valueY = seriesData.id;
	      series.dataFields.categoryX = 'id';
	      series.stacked = true;
	      series.name = seriesData.title;
	      series.fill = am4core.color(seriesData.color);
	      series.stroke = am4core.color('#ffffff');
	      return series;
	    }
	  }]);
	  return StackedBarChart;
	}();

	function _initializeChart2(chartProps) {
	  var _this = this;

	  window.am4core.useTheme(am4themes_animated);
	  babelHelpers.classPrivateFieldSet(this, _chartId, chartProps.id);
	  babelHelpers.classPrivateFieldSet(this, _chart, am4core.create(babelHelpers.classPrivateFieldGet(this, _chartId), am4charts.XYChart));
	  babelHelpers.classPrivateFieldGet(this, _chart).data = [];
	  babelHelpers.classPrivateFieldGet(this, _chart).zoomOutButton.readerTitle = main_core.Loc.getMessage('STORE_CHART_ZOOMOUT_TITLE');
	  babelHelpers.classPrivateFieldGet(this, _chart).legend = new am4charts.Legend();
	  babelHelpers.classPrivateFieldGet(this, _chart).legend.position = 'bottom';

	  _classPrivateMethodGet(this, _initAxes, _initAxes2).call(this);

	  _classPrivateMethodGet(this, _initColumnsData, _initColumnsData2).call(this, chartProps.columns);

	  _classPrivateMethodGet(this, _initSeries, _initSeries2).call(this, chartProps.seriesList);

	  if (chartProps.isCommonChart) {
	    babelHelpers.classPrivateFieldGet(this, _axes).categoryAxis.renderer.labels.template.html = chartProps.label;
	  }

	  babelHelpers.classPrivateFieldGet(this, _chart).events.on("inited", function () {
	    _classPrivateMethodGet(_this, _onChartLoaded, _onChartLoaded2).call(_this);
	  });
	}

	function _initColumnsTitle2() {
	  var _this2 = this;

	  babelHelpers.classPrivateFieldGet(this, _chart).data.forEach(function (columnData) {
	    babelHelpers.classPrivateFieldGet(_this2, _axes).categoryAxis.dataItemsByCategory.getKey(columnData['id']).text = columnData['name'];
	  });
	}

	function _initAxes2() {
	  babelHelpers.classPrivateFieldGet(this, _axes).categoryAxis = babelHelpers.classPrivateFieldGet(this, _chart).xAxes.push(new am4charts.CategoryAxis());
	  babelHelpers.classPrivateFieldGet(this, _axes).categoryAxis.dataFields.category = 'id';
	  babelHelpers.classPrivateFieldGet(this, _axes).categoryAxis.renderer.grid.template.opacity = 0;
	  babelHelpers.classPrivateFieldGet(this, _axes).valueAxis = babelHelpers.classPrivateFieldGet(this, _chart).yAxes.push(new am4charts.ValueAxis());
	  babelHelpers.classPrivateFieldGet(this, _axes).valueAxis.min = 0;
	  babelHelpers.classPrivateFieldGet(this, _axes).valueAxis.renderer.grid.template.opacity = 0;
	  babelHelpers.classPrivateFieldGet(this, _axes).valueAxis.renderer.ticks.template.strokeOpacity = 0.5;
	  babelHelpers.classPrivateFieldGet(this, _axes).valueAxis.renderer.ticks.template.length = 10;
	  babelHelpers.classPrivateFieldGet(this, _axes).valueAxis.renderer.line.strokeOpacity = 0.5;
	  babelHelpers.classPrivateFieldGet(this, _axes).valueAxis.renderer.baseGrid.disabled = true;
	  babelHelpers.classPrivateFieldGet(this, _axes).valueAxis.renderer.minGridDistance = 40;
	  babelHelpers.classPrivateFieldGet(this, _axes).valueAxis.calculateTotals = true; // some space needed for the total label

	  babelHelpers.classPrivateFieldGet(this, _axes).categoryAxis.renderer.labels.template.marginRight = 40;
	}

	function _initColumnsData2(columnsData) {
	  babelHelpers.classPrivateFieldGet(this, _chart).data = columnsData.map(function (columnData) {
	    columnData.id = columnData.id ? columnData.id : BX.util.getRandomString(4);
	    return columnData;
	  });
	}

	function _initSeries2(seriesList) {
	  var _this3 = this;

	  var emptySeries = StackedBarChart.createSeries({
	    id: 5,
	    title: 'empty',
	    color: '#ffffff'
	  });
	  emptySeries.hiddenInLegend = true;
	  emptySeries.maskBullets = false;
	  babelHelpers.classPrivateFieldGet(this, _chart).series.push(emptySeries);
	  seriesList.sort(function (firstSeries, secondSeries) {
	    if (!firstSeries.weight) {
	      return -1;
	    }

	    if (firstSeries.weight < secondSeries.weight) {
	      return 1;
	    } else if (firstSeries.weight > secondSeries.weight) {
	      return -1;
	    }

	    return 0;
	  });
	  seriesList.forEach(function (seriesData) {
	    var _this$seriesLoadedLen, _this$seriesLoadedLen2;

	    var seriesObject = babelHelpers.classPrivateFieldGet(_this3, _chart).series.push(StackedBarChart.createSeries(seriesData));
	    babelHelpers.classPrivateFieldGet(_this3, _seriesList)[seriesData.id] = [seriesData, seriesObject];
	    babelHelpers.classPrivateFieldSet(_this3, _seriesLoadedLen, (_this$seriesLoadedLen = babelHelpers.classPrivateFieldGet(_this3, _seriesLoadedLen), _this$seriesLoadedLen2 = _this$seriesLoadedLen++, _this$seriesLoadedLen)), _this$seriesLoadedLen2;
	    seriesObject.events.on("inited", function (eventObject) {
	      _classPrivateMethodGet(_this3, _onSeriesLoaded, _onSeriesLoaded2).call(_this3, eventObject.target, seriesData.id);
	    }, _this3);
	  });
	}

	function _onSeriesLoaded2(event, seriesId) {
	  if (babelHelpers.classPrivateFieldGet(this, _chart).legend.labels.values.length === babelHelpers.classPrivateFieldGet(this, _seriesLoadedLen)) {
	    _classPrivateMethodGet(this, _prepareChartLegend, _prepareChartLegend2).call(this);
	  }

	  if (babelHelpers.classPrivateFieldGet(this, _isPopupEnabled)) {
	    _classPrivateMethodGet(this, _bindPopupEvents, _bindPopupEvents2).call(this, event, seriesId);
	  }
	}

	function _prepareChartLegend2() {
	  if (babelHelpers.classPrivateFieldGet(this, _legendIsPrepared)) {
	    return;
	  } else {
	    babelHelpers.classPrivateFieldSet(this, _legendIsPrepared, true);
	  }

	  if (babelHelpers.classPrivateFieldGet(this, _onSeriesLabelLoadHandler) instanceof Function) {
	    babelHelpers.classPrivateFieldGet(this, _onSeriesLabelLoadHandler).call(this, babelHelpers.classPrivateFieldGet(this, _chart).legend.labels.values);
	  }
	}

	function _onChartLoaded2() {
	  if (!babelHelpers.classPrivateFieldGet(this, _isChartCommon)) {
	    _classPrivateMethodGet(this, _initColumnsTitle, _initColumnsTitle2).call(this);
	  } else {
	    babelHelpers.classPrivateFieldGet(this, _axes).categoryAxis.dataItemsByCategory.getKey('0').text = '';
	  }
	}

	function _bindPopupEvents2(seriesObject, seriesId) {
	  var _this4 = this;

	  var _loop = function _loop(i) {
	    var _babelHelpers$classPr = babelHelpers.slicedToArray(babelHelpers.classPrivateFieldGet(_this4, _seriesList)[seriesId], 1),
	        series = _babelHelpers$classPr[0];

	    var column = {
	      columnObject: seriesObject.columns.getIndex(i),
	      columnSeries: series,
	      storedData: babelHelpers.classPrivateFieldGet(_this4, _chart).data[i]
	    };
	    main_core.Event.bind(column.columnObject.group.node, 'mouseover', _classPrivateMethodGet(_this4, _onStuckMouseOver, _onStuckMouseOver2).bind(_this4, column));
	    main_core.Event.bind(column.columnObject.group.node, 'mouseout', _classPrivateMethodGet(_this4, _onStuckMouseOut, _onStuckMouseOut2).bind(_this4, column));

	    if (babelHelpers.classPrivateFieldGet(_this4, _onChartClick)) {
	      column.columnObject.group.node.style.cursor = 'pointer';
	      main_core.Event.bind(column.columnObject.group.node, 'click', function () {
	        return babelHelpers.classPrivateFieldGet(_this4, _onChartClick).call(_this4, column.storedData, series);
	      });
	    }
	  };

	  for (var i = 0; i < seriesObject.columns.length; i++) {
	    _loop(i);
	  }
	}

	function _onStuckMouseOver2(column) {
	  var _column$columnSeries;

	  var popupContent = (_column$columnSeries = column.columnSeries) === null || _column$columnSeries === void 0 ? void 0 : _column$columnSeries.getPopupContent(column.storedData);

	  if (popupContent && !babelHelpers.classPrivateFieldGet(this, _chartPopup)) {
	    var popupTitle = column.columnSeries.title;

	    if (babelHelpers["typeof"](popupContent) === 'object') {
	      popupTitle = popupContent.title ? popupContent.title : popupTitle;
	      popupContent = popupContent.content;
	    }

	    babelHelpers.classPrivateFieldSet(this, _chartPopup, new main_popup.Popup("stacked-bar-chart-popup-".concat(BX.util.getRandomString(4)), column.columnObject.group.node, {
	      content: StackedBarChart.formPopupContent(column.columnSeries.color, popupTitle, popupContent),
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
	      cacheable: false
	    }));
	    babelHelpers.classPrivateFieldGet(this, _chartPopup).show();
	  }
	}

	function _onStuckMouseOut2(column) {
	  if (babelHelpers.classPrivateFieldGet(this, _chartPopup)) {
	    babelHelpers.classPrivateFieldGet(this, _chartPopup).close();
	    babelHelpers.classPrivateFieldGet(this, _chartPopup).destroy();
	    babelHelpers.classPrivateFieldSet(this, _chartPopup, null);
	  }
	}

	var _templateObject$1;

	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _chart$1 = /*#__PURE__*/new WeakMap();

	var _currency = /*#__PURE__*/new WeakMap();

	var StoreStackedChart = /*#__PURE__*/function () {
	  function StoreStackedChart(props) {
	    babelHelpers.classCallCheck(this, StoreStackedChart);

	    _classPrivateFieldInitSpec$1(this, _chart$1, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$1(this, _currency, {
	      writable: true,
	      value: void 0
	    });

	    if ((this instanceof StoreStackedChart ? this.constructor : void 0) === StoreStackedChart) {
	      throw new Error('StoreStackedChart cannot be directly instantiated');
	    }

	    this.properties = props;
	    this.loadCurrency();
	    babelHelpers.classPrivateFieldSet(this, _chart$1, new StackedBarChart({
	      chartProps: this.getFormedChartProps(),
	      isPopupEnabled: this.properties.isPopupEnabled,
	      onChartClick: this.properties.onChartClick,
	      onSeriesLabelLoad: this.onChartLabelLoadHandler.bind(this)
	    }));
	  }

	  babelHelpers.createClass(StoreStackedChart, [{
	    key: "loadCurrency",
	    value: function loadCurrency() {
	      if (this.properties.currency) {
	        babelHelpers.classPrivateFieldSet(this, _currency, this.properties.currency);
	      } else {
	        var extensionSettingsCollection = main_core.Extension.getSettings('catalog.store-chart');
	        babelHelpers.classPrivateFieldSet(this, _currency, {
	          id: extensionSettingsCollection.get('currency'),
	          symbol: extensionSettingsCollection.get('currencySymbol'),
	          format: extensionSettingsCollection.get('currencyFormat')
	        });
	      }

	      currency_currencyCore.CurrencyCore.setCurrencyFormat(babelHelpers.classPrivateFieldGet(this, _currency).id, babelHelpers.classPrivateFieldGet(this, _currency).format);
	    }
	  }, {
	    key: "getCurrency",
	    value: function getCurrency() {
	      return babelHelpers.classPrivateFieldGet(this, _currency);
	    }
	  }, {
	    key: "formatByCurrency",
	    value: function formatByCurrency(value) {
	      return currency_currencyCore.CurrencyCore.currencyFormat(value, this.getCurrency().id, true);
	    }
	  }, {
	    key: "getFormedChartProps",
	    value: function getFormedChartProps() {
	      return {
	        id: this.properties.id,
	        label: this.getChartLabel(),
	        isCommonChart: this.isCommonChart(),
	        seriesList: this.getChartSeries(),
	        columns: this.getChartColumns(this.properties.stores)
	      };
	    }
	  }, {
	    key: "getHelpdeskButton",
	    value: function getHelpdeskButton(code) {
	      return main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div onclick='top.BX.Helper.show(\"redirect=detail&code=", "\")' class=\"how-it-works-guide-link\">", "</div>\n\t\t"])), code, main_core.Loc.getMessage('STORE_CHART_HINT_TITLE'));
	    }
	  }, {
	    key: "getChartLabel",
	    value: function getChartLabel() {
	      return this.properties.label;
	    }
	  }, {
	    key: "isCommonChart",
	    value: function isCommonChart() {
	      return this.properties.isCommonChart;
	    }
	  }, {
	    key: "getChartSeries",
	    value: function getChartSeries() {
	      return [];
	    }
	  }, {
	    key: "getChartColumns",
	    value: function getChartColumns(columns) {
	      return [];
	    }
	  }, {
	    key: "onChartLabelLoadHandler",
	    value: function onChartLabelLoadHandler(legendValues) {
	      var currencyPostfix = ', ' + babelHelpers.classPrivateFieldGet(this, _currency).symbol;

	      for (var i = 0; i < legendValues.length; i++) {
	        legendValues[i].fill = am4core.color("#000000");
	        legendValues[i].html = legendValues[i].currentText + currencyPostfix;
	      }
	    }
	  }, {
	    key: "getChart",
	    value: function getChart() {
	      return babelHelpers.classPrivateFieldGet(this, _chart$1);
	    }
	  }]);
	  return StoreStackedChart;
	}();

	var StoreStockChart = /*#__PURE__*/function (_StoreStackedChart) {
	  babelHelpers.inherits(StoreStockChart, _StoreStackedChart);

	  function StoreStockChart() {
	    babelHelpers.classCallCheck(this, StoreStockChart);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(StoreStockChart).apply(this, arguments));
	  }

	  babelHelpers.createClass(StoreStockChart, [{
	    key: "getChartLabel",
	    value: function getChartLabel() {
	      return babelHelpers.get(babelHelpers.getPrototypeOf(StoreStockChart.prototype), "getChartLabel", this).call(this);
	    }
	  }, {
	    key: "isCommonChart",
	    value: function isCommonChart() {
	      return babelHelpers.get(babelHelpers.getPrototypeOf(StoreStockChart.prototype), "isCommonChart", this).call(this);
	    }
	  }, {
	    key: "getChartSeries",
	    value: function getChartSeries() {
	      var _this = this;

	      return [{
	        id: 'sum_stored',
	        color: '#42659B',
	        title: main_core.Loc.getMessage('STORE_STOCK_CHART_SUM_STORED_SERIES_TITLE'),
	        getPopupContent: function getPopupContent(storeData) {
	          return {
	            title: main_core.Loc.getMessage('STORE_STOCK_CHART_SUM_STORED_SERIES_POPUP_TITLE'),
	            content: "\n\t\t\t\t\t\t\t<div class=\"stacked-bar-chart-popup-info-item\" style=\"display: block\">\n\t\t\t\t\t\t\t\t<div class=\"stacked-bar-chart-popup-info-subtitle\">".concat(main_core.Loc.getMessage('STORE_STOCK_CHART_SUM_STORED_SERIES_POPUP_SUM'), "</div>\n\t\t\t\t\t\t\t\t<div class=\"stacked-bar-chart-popup-info-value-box\">\n\t\t\t\t\t\t\t\t\t<div id=\"chart-popup-template-sum\" class=\"stacked-bar-chart-popup-info-value\">").concat(_this.formatByCurrency(storeData.sum_stored), "</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t")
	          };
	        }
	      }];
	    }
	  }, {
	    key: "getChartColumns",
	    value: function getChartColumns(columns) {
	      var stores = [];

	      for (var storeId in columns) {
	        stores.push(columns[storeId]);
	      }

	      return stores;
	    }
	  }]);
	  return StoreStockChart;
	}(StoreStackedChart);

	var StoreSaleChart = /*#__PURE__*/function (_StoreStackedChart) {
	  babelHelpers.inherits(StoreSaleChart, _StoreStackedChart);

	  function StoreSaleChart() {
	    babelHelpers.classCallCheck(this, StoreSaleChart);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(StoreSaleChart).apply(this, arguments));
	  }

	  babelHelpers.createClass(StoreSaleChart, [{
	    key: "getChartLabel",
	    value: function getChartLabel() {
	      return babelHelpers.get(babelHelpers.getPrototypeOf(StoreSaleChart.prototype), "getChartLabel", this).call(this);
	    }
	  }, {
	    key: "isCommonChart",
	    value: function isCommonChart() {
	      return babelHelpers.get(babelHelpers.getPrototypeOf(StoreSaleChart.prototype), "isCommonChart", this).call(this);
	    }
	  }, {
	    key: "getChartSeries",
	    value: function getChartSeries() {
	      var _this = this;

	      return [{
	        id: 'sum_shipped',
	        color: '#6DA3E6',
	        title: main_core.Loc.getMessage('STORE_SALE_CHART_SUM_SHIPPED_SERIES_TITLE'),
	        getPopupContent: function getPopupContent(storeData) {
	          return {
	            title: main_core.Loc.getMessage('STORE_SALE_CHART_SUM_SHIPPED_SERIES_POPUP_TITLE'),
	            content: "\n\t\t\t\t\t\t<div class=\"stacked-bar-chart-popup-info-multiple\">\n\t\t\t\t\t\t\t<div class=\"stacked-bar-chart-popup-info-item\" style=\"display: block\">\n\t\t\t\t\t\t\t\t<div class=\"stacked-bar-chart-popup-info-subtitle\">".concat(main_core.Loc.getMessage('STORE_SALE_CHART_SUM_SHIPPED_SERIES_POPUP_SUM'), "</div>\n\t\t\t\t\t\t\t\t<div class=\"stacked-bar-chart-popup-info-value-box\">\n\t\t\t\t\t\t\t\t\t<div id=\"chart-popup-template-sum\" class=\"stacked-bar-chart-popup-info-value\">").concat(_this.formatByCurrency(storeData.sum_shipped), "</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"stacked-bar-chart-popup-info-item\">\n\t\t\t\t\t\t\t\t<div class=\"stacked-bar-chart-popup-info-subtitle\">").concat(main_core.Loc.getMessage('STORE_SALE_CHART_SUM_SHIPPED_SERIES_POPUP_SOLD_PERCENT'), "</div>\n\t\t\t\t\t\t\t\t<div class=\"stacked-bar-chart-popup-info-value-box\">\n\t\t\t\t\t\t\t\t<div class=\"stacked-bar-chart-popup-info-value\">").concat(storeData.sold_percent, "%</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t")
	          };
	        }
	      }, {
	        id: 'sum_arrived',
	        color: '#42659B',
	        title: main_core.Loc.getMessage('STORE_SALE_CHART_SUM_ARRIVED_SERIES_TITLE'),
	        getPopupContent: function getPopupContent(storeData) {
	          return {
	            title: main_core.Loc.getMessage('STORE_SALE_CHART_SUM_ARRIVED_SERIES_POPUP_TITLE'),
	            content: "\n\t\t\t\t\t\t\t<div class=\"stacked-bar-chart-popup-info-item\" style=\"display: block\">\n\t\t\t\t\t\t\t\t<div class=\"stacked-bar-chart-popup-info-subtitle\">".concat(main_core.Loc.getMessage('STORE_SALE_CHART_SUM_ARRIVED_SERIES_POPUP_SUM'), "</div>\n\t\t\t\t\t\t\t\t<div class=\"stacked-bar-chart-popup-info-value-box\">\n\t\t\t\t\t\t\t\t\t<div id=\"chart-popup-template-sum\" class=\"stacked-bar-chart-popup-info-value\">").concat(_this.formatByCurrency(storeData.sum_arrived), "</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t")
	          };
	        }
	      }];
	    }
	  }, {
	    key: "getChartColumns",
	    value: function getChartColumns(columns) {
	      var stores = [];

	      for (var storeId in columns) {
	        stores.push(columns[storeId]);
	      }

	      return stores;
	    }
	  }]);
	  return StoreSaleChart;
	}(StoreStackedChart);

	exports.StoreStockChart = StoreStockChart;
	exports.StoreSaleChart = StoreSaleChart;
	exports.StackedBarChart = StackedBarChart;

}((this.BX.Catalog = this.BX.Catalog || {}),BX.Main,BX.Currency,BX));
//# sourceMappingURL=store-chart.bundle.js.map
