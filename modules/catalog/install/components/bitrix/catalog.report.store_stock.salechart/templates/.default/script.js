(function (exports,currency,main_core,catalog_storeChart) {
	'use strict';

	var _templateObject;

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _storeStockChart = /*#__PURE__*/new WeakMap();

	var _boardId = /*#__PURE__*/new WeakMap();

	var _board = /*#__PURE__*/new WeakMap();

	var _widgetId = /*#__PURE__*/new WeakMap();

	var _widget = /*#__PURE__*/new WeakMap();

	var StoreStockChartManager = /*#__PURE__*/function () {
	  function StoreStockChartManager(props) {
	    babelHelpers.classCallCheck(this, StoreStockChartManager);

	    _classPrivateFieldInitSpec(this, _storeStockChart, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _boardId, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _board, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _widgetId, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _widget, {
	      writable: true,
	      value: void 0
	    });

	    if (props.chartProps.detailSliderUrl) {
	      this.detailSliderUrl = props.chartProps.detailSliderUrl;
	      props.chartProps.onChartClick = this.openDetailSlider.bind(this);
	    }

	    babelHelpers.classPrivateFieldSet(this, _widgetId, props.widgetId);
	    babelHelpers.classPrivateFieldSet(this, _boardId, props.boardId);
	    babelHelpers.classPrivateFieldSet(this, _board, BX.VisualConstructor.BoardRepository.getBoard(babelHelpers.classPrivateFieldGet(this, _boardId)));
	    babelHelpers.classPrivateFieldSet(this, _widget, babelHelpers.classPrivateFieldGet(this, _board).dashboard.getWidget(babelHelpers.classPrivateFieldGet(this, _widgetId)));
	    babelHelpers.classPrivateFieldSet(this, _storeStockChart, new BX.Catalog.StoreStockChart(props.chartProps));
	    this.updateWidgetTitle();
	  }

	  babelHelpers.createClass(StoreStockChartManager, [{
	    key: "openDetailSlider",
	    value: function openDetailSlider() {
	      BX.SidePanel.Instance.open(this.detailSliderUrl, {
	        cacheable: false,
	        allowChangeTitle: false,
	        allowChangeHistory: false
	      });
	    }
	  }, {
	    key: "updateWidgetTitle",
	    value: function updateWidgetTitle() {
	      BX.clean(babelHelpers.classPrivateFieldGet(this, _widget).layout.titleContainer);
	      babelHelpers.classPrivateFieldGet(this, _widget).layout.titleContainer.appendChild(main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t<div class=\"chart-header\">\n\t\t\t<div>", "</div>\n\t\t\t", "\n\t\t</div>\n\t\t"])), babelHelpers.classPrivateFieldGet(this, _widget).config.title, babelHelpers.classPrivateFieldGet(this, _storeStockChart).getHelpdeskButton('15503856')));
	      babelHelpers.classPrivateFieldGet(this, _widget).layout.titleContainer.style.width = '100%';
	    }
	  }]);
	  return StoreStockChartManager;
	}();

	main_core.Reflection.namespace('BX.Catalog.Report').StoreStockChartManager = StoreStockChartManager;

}((this.window = this.window || {}),BX,BX,BX.Catalog));
//# sourceMappingURL=script.js.map
