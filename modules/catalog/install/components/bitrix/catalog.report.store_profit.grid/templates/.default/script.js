/* eslint-disable */
(function (exports,main_core) {
	'use strict';

	var _templateObject;
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _productListSliderUrl = /*#__PURE__*/new WeakMap();
	var _productListSliderFilter = /*#__PURE__*/new WeakMap();
	var StoreProfitGrid = /*#__PURE__*/function () {
	  function StoreProfitGrid(options) {
	    babelHelpers.classCallCheck(this, StoreProfitGrid);
	    _classPrivateFieldInitSpec(this, _productListSliderUrl, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _productListSliderFilter, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _productListSliderUrl, options.productListSliderUrl);
	    babelHelpers.classPrivateFieldSet(this, _productListSliderFilter, options.productListSliderFilter);
	    this.updateWidgetTitleInterval = setInterval(this.updateWidgetTitle.bind(this), 50);
	  }
	  babelHelpers.createClass(StoreProfitGrid, [{
	    key: "openStoreProductListGrid",
	    value: function openStoreProductListGrid(storeId) {
	      var url = main_core.Uri.addParam(babelHelpers.classPrivateFieldGet(this, _productListSliderUrl), {
	        storeId: storeId
	      });
	      BX.SidePanel.Instance.open(url, {
	        requestMethod: 'post',
	        requestParams: {
	          filter: babelHelpers.classPrivateFieldGet(this, _productListSliderFilter),
	          openedFromReport: true
	        },
	        cacheable: false
	      });
	    }
	  }, {
	    key: "updateWidgetTitle",
	    value: function updateWidgetTitle() {
	      var _document$querySelect, _document$querySelect2, _document$querySelect3, _document$querySelect4;
	      var dashboardElement = (_document$querySelect = document.querySelector('.amcharts-main-div')) === null || _document$querySelect === void 0 ? void 0 : (_document$querySelect2 = _document$querySelect.parentElement) === null || _document$querySelect2 === void 0 ? void 0 : (_document$querySelect3 = _document$querySelect2.parentElement) === null || _document$querySelect3 === void 0 ? void 0 : (_document$querySelect4 = _document$querySelect3.parentElement) === null || _document$querySelect4 === void 0 ? void 0 : _document$querySelect4.parentElement;
	      if (!dashboardElement) {
	        return;
	      }
	      var titleElement = dashboardElement.querySelector('.report-visualconstructor-dashboard-widget-head-wrapper > .report-visualconstructor-dashboard-widget-title-container');
	      document.querySelector('.report-visualconstructor-dashboard-widget-head-wrapper').appendChild(main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"chart-header\">\n\t\t\t\t", "\n\t\t\t\t<div onclick='top.BX.Helper.show(\"redirect=detail&code=18502626\")' class=\"how-it-works-guide-link\">", "</div>\n\t\t\t</div>\n\t\t"])), titleElement, main_core.Loc.getMessage('STORE_CHART_HINT_TITLE')));
	      clearInterval(this.updateWidgetTitleInterval);
	    }
	  }]);
	  return StoreProfitGrid;
	}();
	main_core.Reflection.namespace('BX.Catalog.Report.StoreProfit').StoreGrid = StoreProfitGrid;

}((this.window = this.window || {}),BX));
//# sourceMappingURL=script.js.map
