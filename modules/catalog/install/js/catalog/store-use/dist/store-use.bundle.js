/* eslint-disable */
this.BX = this.BX || {};
this.BX.Catalog = this.BX.Catalog || {};
(function (exports,ui_designTokens,ui_layoutForm,main_core_events,ui_buttons,ui_dialogs_messagebox,main_core,main_popup) {
	'use strict';

	var Controller = /*#__PURE__*/function () {
	  function Controller() {
	    babelHelpers.classCallCheck(this, Controller);
	  }
	  babelHelpers.createClass(Controller, [{
	    key: "inventoryManagementAnalyticsFromLanding",
	    value: function inventoryManagementAnalyticsFromLanding() {
	      var _this = this;
	      var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      this.sendAnalyticsLabel(data).then(function () {
	        _this.unRegisterOnProlog();
	      })["catch"](function () {});
	    }
	  }, {
	    key: "sendAnalyticsLabel",
	    value: function sendAnalyticsLabel() {
	      var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var analytics = this.makeAnalyticsData(data);
	      return main_core.ajax.runAction('catalog.analytics.sendAnalyticsLabel', {
	        analyticsLabel: analytics
	      });
	    }
	  }, {
	    key: "unRegisterOnProlog",
	    value: function unRegisterOnProlog() {
	      return main_core.ajax.runAction('catalog.config.unRegisterOnProlog');
	    }
	  }, {
	    key: "inventoryManagementEnabled",
	    value: function inventoryManagementEnabled() {
	      var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var analytics = this.makeAnalyticsData(data);
	      return main_core.ajax.runAction('catalog.config.inventoryManagementYAndResetQuantity', {
	        analyticsLabel: analytics
	      });
	    }
	  }, {
	    key: "inventoryManagementEnableWithResetDocuments",
	    value: function inventoryManagementEnableWithResetDocuments() {
	      var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      return main_core.ajax.runAction('catalog.config.inventoryManagementYAndResetQuantityWithDocuments', {
	        analyticsLabel: this.makeAnalyticsData(data),
	        data: {
	          costPriceCalculationMethod: data.costPriceAccountingMethod
	        }
	      }).then(function (response) {
	        top.BX.onCustomEvent('CatalogWarehouseMasterClear:resetDocuments');
	        return response;
	      });
	    }
	  }, {
	    key: "inventoryManagementEnableWithoutReset",
	    value: function inventoryManagementEnableWithoutReset() {
	      var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      return main_core.ajax.runAction('catalog.config.inventoryManagementY', {
	        analyticsLabel: this.makeAnalyticsData(data),
	        data: {
	          costPriceCalculationMethod: data.costPriceAccountingMethod
	        }
	      });
	    }
	  }, {
	    key: "makeAnalyticsData",
	    value: function makeAnalyticsData() {
	      var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var analyticsData = {
	        iME: 'inventoryManagementEnabled'
	      };
	      if (main_core.Type.isStringFilled(data.inventoryManagementSource)) {
	        analyticsData.inventoryManagementSource = data.inventoryManagementSource;
	      }
	      return analyticsData;
	    }
	  }, {
	    key: "inventoryManagementDisabled",
	    value: function inventoryManagementDisabled() {
	      return main_core.ajax.runAction('catalog.config.inventoryManagementN', {});
	    }
	  }]);
	  return Controller;
	}();

	var EventType = Object.freeze({
	  popup: {
	    enable: 'BX:Sale:StoreMaster:EventType:popup:enable',
	    enableWithoutReset: 'BX:Sale:StoreMaster:EventType:popup:enableWithoutReset',
	    enableWithResetDocuments: 'BX:Sale:StoreMaster:EventType:popup:enableWithResetDocuments',
	    disable: 'BX:Sale:StoreMaster:EventType:popup:disable',
	    disableCancel: 'BX:Sale:StoreMaster:EventType:popup:disable:cancel',
	    confirm: 'BX:Sale:StoreMaster:EventType:popup:confirm',
	    confirmCancel: 'BX:Sale:StoreMaster:EventType:popup:confirm:cancel',
	    selectCostPriceAccountingMethod: 'BX:Sale:StoreMaster:EventType:popup:costPriceAccountingMethodSelect'
	  }
	});

	var DialogDisable = /*#__PURE__*/function () {
	  function DialogDisable() {
	    babelHelpers.classCallCheck(this, DialogDisable);
	  }
	  babelHelpers.createClass(DialogDisable, [{
	    key: "popup",
	    value: function popup() {
	      this.disablePopup();
	    }
	  }, {
	    key: "disablePopup",
	    value: function disablePopup() {
	      ui_dialogs_messagebox.MessageBox.confirm(main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_DISABLE_POPUP_CONTENT'), main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_DISABLE_POPUP_TITLE_MSGVER_1'), function (messageBox) {
	        messageBox.close();
	        main_core_events.EventEmitter.emit(EventType.popup.disable, {});
	      }, main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_DISABLE_POPUP_CONFIRM_BUTTON'), function (messageBox) {
	        return messageBox.close();
	      }, main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_POPUP_CANCEL_BUTTON'));
	    }
	  }]);
	  return DialogDisable;
	}();

	var _templateObject, _templateObject2, _templateObject3;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _getArticleCode = /*#__PURE__*/new WeakSet();
	var DialogCostPriceAccountingMethodSelection = /*#__PURE__*/function () {
	  function DialogCostPriceAccountingMethodSelection() {
	    babelHelpers.classCallCheck(this, DialogCostPriceAccountingMethodSelection);
	    _classPrivateMethodInitSpec(this, _getArticleCode);
	    babelHelpers.defineProperty(this, "selectedMethod", DialogCostPriceAccountingMethodSelection.METHOD_AVERAGE);
	  }
	  babelHelpers.createClass(DialogCostPriceAccountingMethodSelection, [{
	    key: "popup",
	    value: function popup() {
	      var _this = this;
	      return new Promise(function (resolve) {
	        var messageBox = ui_dialogs_messagebox.MessageBox.create({
	          title: main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_COST_PRICE_ACCOUNTING_METHOD_TITLE'),
	          message: _this.getContent(),
	          buttons: [new ui_buttons.Button({
	            text: main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_COST_PRICE_ACCOUNTING_METHOD_SELECT'),
	            color: ui_buttons.Button.Color.PRIMARY,
	            onclick: function onclick() {
	              main_core_events.EventEmitter.emit(EventType.popup.selectCostPriceAccountingMethod, {
	                method: _this.selectedMethod
	              });
	              messageBox.close();
	              resolve();
	            }
	          })],
	          maxWidth: 500
	        });
	        messageBox.show();
	      });
	    }
	  }, {
	    key: "getContent",
	    value: function getContent() {
	      var _this2 = this;
	      var selector = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<select class=\"ui-ctl-element\">\n\t\t\t\t<option value=\"", "\" selected>\n\t\t\t\t\t", "\n\t\t\t\t</option>\n\t\t\t\t<option value=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t</option>\n\t\t\t</select>\n\t\t"])), DialogCostPriceAccountingMethodSelection.METHOD_AVERAGE, main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_COST_PRICE_ACCOUNTING_METHOD_AVERAGE'), DialogCostPriceAccountingMethodSelection.METHOD_FIFO, main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_COST_PRICE_ACCOUNTING_METHOD_FIFO'));
	      main_core.Event.bind(selector, 'change', function () {
	        _this2.selectedMethod = selector.value;
	      });
	      var link = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<a href='#' class=\"catalog-warehouse-master-clear-popup-hint\">\n\t\t\t\t", "\n\t\t\t</a>\n\t\t"])), main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_DETAILS'));
	      main_core.Event.bind(link, 'click', function (e) {
	        e.preventDefault();
	        if (top.BX.Helper) {
	          top.BX.Helper.show("redirect=detail&code=".concat(_classPrivateMethodGet(_this2, _getArticleCode, _getArticleCode2).call(_this2)));
	        }
	      });
	      return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class='catalog-warehouse-master-clear-popup-content'>\n\t\t\t\t<div class=\"catalog-warehouse-master-clear-popup-text\">\n\t\t\t\t\t<p>", " ", "</p>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"catalog-warehouse-master-clear-popup-text ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100\">\n\t\t\t\t\t<div class=\"ui-ctl-after ui-ctl-icon-angle\"></div>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_COST_PRICE_ACCOUNTING_METHOD_TEXT'), link, selector);
	    }
	  }]);
	  return DialogCostPriceAccountingMethodSelection;
	}();
	function _getArticleCode2() {
	  return 17858278;
	}
	babelHelpers.defineProperty(DialogCostPriceAccountingMethodSelection, "METHOD_AVERAGE", 'average');
	babelHelpers.defineProperty(DialogCostPriceAccountingMethodSelection, "METHOD_FIFO", 'fifo');

	var DialogClearing = /*#__PURE__*/function () {
	  function DialogClearing() {
	    babelHelpers.classCallCheck(this, DialogClearing);
	  }
	  babelHelpers.createClass(DialogClearing, [{
	    key: "popup",
	    value: function popup() {
	      var messageBox = ui_dialogs_messagebox.MessageBox.create({
	        message: main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_ENABLE_POPUP_CONTENT_MSGVER_1'),
	        buttons: [new ui_buttons.Button({
	          text: main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_ENABLE_POPUP_CONFIRM_BUTTON_MSGVER_1'),
	          color: ui_buttons.Button.Color.PRIMARY,
	          onclick: function onclick() {
	            main_core_events.EventEmitter.emit(EventType.popup.enableWithResetDocuments, {});
	            messageBox.close();
	          }
	        }), new ui_buttons.Button({
	          text: main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_POPUP_CANCEL_BUTTON_MSGVER_1'),
	          color: ui_buttons.Button.Color.LINK,
	          onclick: function onclick() {
	            messageBox.close();
	          }
	        })],
	        maxWidth: 400
	      });
	      messageBox.show();
	    }
	  }]);
	  return DialogClearing;
	}();

	var DialogEnable = /*#__PURE__*/function () {
	  function DialogEnable() {
	    babelHelpers.classCallCheck(this, DialogEnable);
	  }
	  babelHelpers.createClass(DialogEnable, [{
	    key: "popup",
	    value: function popup() {
	      var _this = this;
	      main_core.ajax.runAction('catalog.config.checkEnablingConditions', {}).then(function (response) {
	        var result = response.data;

	        /**
	         * if there are some existing documents or some quantities exist, we warn the user in the batch method popup
	         *
	         * if no documents and no unaccounted quantities exist, we show the batch method popup without any warnings
	         */
	        var batchMethodPopupParams = {
	          clearDocuments: false
	        };
	        if (result.includes(DialogEnable.CONDUCTED_DOCUMENTS_EXIST) || result.includes(DialogEnable.QUANTITY_INCONSISTENCY_EXISTS)) {
	          batchMethodPopupParams.clearDocuments = true;
	        }
	        _this.selectBatchMethodPopup(batchMethodPopupParams);
	      })["catch"](function () {});
	    }
	  }, {
	    key: "selectBatchMethodPopup",
	    value: function selectBatchMethodPopup(params) {
	      new DialogCostPriceAccountingMethodSelection().popup().then(function () {
	        if (params.clearDocuments) {
	          new DialogClearing().popup();
	        } else {
	          main_core_events.EventEmitter.emit(EventType.popup.enableWithoutReset);
	        }
	      })["catch"](function () {});
	    }
	  }]);
	  return DialogEnable;
	}();
	babelHelpers.defineProperty(DialogEnable, "QUANTITY_INCONSISTENCY_EXISTS", 'QUANTITY_INCONSISTENCY_EXISTS');
	babelHelpers.defineProperty(DialogEnable, "CONDUCTED_DOCUMENTS_EXIST", 'CONDUCTED_DOCUMENTS_EXIST');

	var _templateObject$1;
	var DialogError = /*#__PURE__*/function () {
	  function DialogError() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, DialogError);
	    this.text = options.text || '';
	    this.helpArticleId = options.helpArticleId || '';
	  }
	  babelHelpers.createClass(DialogError, [{
	    key: "popup",
	    value: function popup() {
	      ui_dialogs_messagebox.MessageBox.alert(this.getContent(), function (messageBox) {
	        return messageBox.close();
	      }, main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_CLOSE'));
	    }
	  }, {
	    key: "getContent",
	    value: function getContent() {
	      var _this = this;
	      var result = main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"catalog-warehouse-master-clear-popup-text\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), this.text);
	      var helpLinkContainer = result.querySelector('a');
	      if (helpLinkContainer) {
	        main_core.Event.bind(helpLinkContainer, 'click', function (event) {
	          event.preventDefault();
	          if (top.BX.Helper) {
	            top.BX.Helper.show("redirect=detail&code=".concat(_this.helpArticleId));
	          }
	        });
	      }
	      return result;
	    }
	  }]);
	  return DialogError;
	}();

	var StoreSlider = /*#__PURE__*/function () {
	  function StoreSlider() {
	    babelHelpers.classCallCheck(this, StoreSlider);
	  }
	  babelHelpers.createClass(StoreSlider, [{
	    key: "open",
	    value: function open(url) {
	      var params = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var sliderParams = main_core.Type.isPlainObject(params) ? params : {};
	      return new Promise(function (resolve) {
	        var _sliderParams$data, _sliderParams$events, _events$onClose;
	        var data = (_sliderParams$data = sliderParams.data) !== null && _sliderParams$data !== void 0 ? _sliderParams$data : {};
	        var events = (_sliderParams$events = sliderParams.events) !== null && _sliderParams$events !== void 0 ? _sliderParams$events : {};
	        events.onClose = (_events$onClose = events.onClose) !== null && _events$onClose !== void 0 ? _events$onClose : function (event) {
	          return resolve(event.getSlider());
	        };
	        var sliderUrl = BX.util.add_url_param(url, {
	          analyticsLabel: 'inventoryManagementEnabled_openSlider'
	        });
	        if (main_core.Type.isString(sliderUrl) && sliderUrl.length > 1) {
	          BX.SidePanel.Instance.open(sliderUrl, {
	            cacheable: false,
	            allowChangeHistory: false,
	            events: events,
	            data: data,
	            width: 1170
	          });
	        } else {
	          resolve();
	        }
	      });
	    }
	  }]);
	  return StoreSlider;
	}();

	var Popup = /*#__PURE__*/function () {
	  function Popup() {
	    babelHelpers.classCallCheck(this, Popup);
	  }
	  babelHelpers.createClass(Popup, [{
	    key: "show",
	    value: function show(target, message, timer) {
	      var _this = this;
	      if (this.popup) {
	        this.popup.destroy();
	        this.popup = null;
	      }
	      if (!target && !message) {
	        return;
	      }
	      this.popup = new main_popup.Popup(null, target, {
	        events: {
	          onPopupClose: function onPopupClose() {
	            _this.popup.destroy();
	            _this.popup = null;
	          }
	        },
	        darkMode: true,
	        content: message,
	        offsetLeft: target.offsetWidth
	      });
	      if (timer) {
	        setTimeout(function () {
	          _this.popup.destroy();
	          _this.popup = null;
	        }, timer);
	      }
	      this.popup.show();
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      if (this.popup) {
	        this.popup.destroy();
	      }
	    }
	  }]);
	  return Popup;
	}();

	var ProductGridMenu = /*#__PURE__*/function () {
	  function ProductGridMenu() {
	    babelHelpers.classCallCheck(this, ProductGridMenu);
	  }
	  babelHelpers.createClass(ProductGridMenu, null, [{
	    key: "reloadGridAction",
	    value: function reloadGridAction() {
	      document.location.reload();
	    }
	  }, {
	    key: "openWarehousePanel",
	    value: function openWarehousePanel(url) {
	      new StoreSlider().open(url, {
	        data: {
	          closeSliderOnDone: false
	        }
	      }).then(function () {
	        ProductGridMenu.reloadGridAction();
	      })["catch"](function () {});
	    }
	  }]);
	  return ProductGridMenu;
	}();

	exports.Controller = Controller;
	exports.EventType = EventType;
	exports.DialogEnable = DialogEnable;
	exports.DialogDisable = DialogDisable;
	exports.DialogClearing = DialogClearing;
	exports.DialogError = DialogError;
	exports.StoreSlider = StoreSlider;
	exports.DialogCostPriceAccountingMethodSelection = DialogCostPriceAccountingMethodSelection;
	exports.Popup = Popup;
	exports.ProductGridMenu = ProductGridMenu;

}((this.BX.Catalog.StoreUse = this.BX.Catalog.StoreUse || {}),BX,BX.UI,BX.Event,BX.UI,BX.UI.Dialogs,BX,BX.Main));
//# sourceMappingURL=store-use.bundle.js.map
