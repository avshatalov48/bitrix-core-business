this.BX = this.BX || {};
this.BX.Catalog = this.BX.Catalog || {};
(function (exports,ui_designTokens,catalog_storeUse,main_core_events,ui_buttons,main_core,main_popup) {
	'use strict';

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _makeAnalyticsData = /*#__PURE__*/new WeakSet();

	var Controller = /*#__PURE__*/function () {
	  function Controller() {
	    babelHelpers.classCallCheck(this, Controller);

	    _classPrivateMethodInitSpec(this, _makeAnalyticsData);
	  }

	  babelHelpers.createClass(Controller, [{
	    key: "inventoryManagementAnalyticsFromLanding",
	    value: function inventoryManagementAnalyticsFromLanding() {
	      var _this = this;

	      var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      this.sendAnalyticsLabel(data).then(function () {
	        _this.unRegisterOnProlog();
	      });
	    }
	  }, {
	    key: "sendAnalyticsLabel",
	    value: function sendAnalyticsLabel() {
	      var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

	      var analytics = _classPrivateMethodGet(this, _makeAnalyticsData, _makeAnalyticsData2).call(this, data);

	      return main_core.ajax.runAction('catalog.analytics.sendAnalyticsLabel', {
	        analyticsLabel: analytics,
	        data: {}
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

	      var analytics = _classPrivateMethodGet(this, _makeAnalyticsData, _makeAnalyticsData2).call(this, data);

	      return main_core.ajax.runAction('catalog.config.inventoryManagementYAndResetQuantity', {
	        analyticsLabel: analytics,
	        data: {
	          preset: data.preset
	        }
	      });
	    }
	  }, {
	    key: "inventoryManagementEnableWithResetDocuments",
	    value: function inventoryManagementEnableWithResetDocuments() {
	      var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      return main_core.ajax.runAction('catalog.config.inventoryManagementYAndResetQuantityWithDocuments', {
	        analyticsLabel: _classPrivateMethodGet(this, _makeAnalyticsData, _makeAnalyticsData2).call(this, data),
	        data: {
	          preset: data.preset
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
	        analyticsLabel: _classPrivateMethodGet(this, _makeAnalyticsData, _makeAnalyticsData2).call(this, data),
	        data: {
	          preset: data.preset
	        }
	      });
	    }
	  }, {
	    key: "inventoryManagementDisabled",
	    value: function inventoryManagementDisabled() {
	      return main_core.ajax.runAction('catalog.config.inventoryManagementN', {});
	    }
	  }, {
	    key: "inventoryManagementInstallPreset",
	    value: function inventoryManagementInstallPreset() {
	      var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      return main_core.ajax.runAction('catalog.config.inventoryManagementInstallPreset', {
	        data: {
	          preset: data.preset
	        }
	      });
	    }
	  }]);
	  return Controller;
	}();

	function _makeAnalyticsData2() {
	  var _data$preset;

	  var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	  var analyticsData = {
	    iME: 'inventoryManagementEnabled' + '_' + ((_data$preset = data.preset) === null || _data$preset === void 0 ? void 0 : _data$preset.sort().join('_'))
	  };

	  if (main_core.Type.isStringFilled(data.inventoryManagementSource)) {
	    analyticsData.inventoryManagementSource = data.inventoryManagementSource;
	  }

	  return analyticsData;
	}

	var EventType = Object.freeze({
	  popup: {
	    enable: 'BX:Sale:StoreMaster:EventType:popup:enable',
	    enableWithoutReset: 'BX:Sale:StoreMaster:EventType:popup:enableWithoutReset',
	    enableWithResetDocuments: 'BX:Sale:StoreMaster:EventType:popup:enableWithResetDocuments',
	    disable: 'BX:Sale:StoreMaster:EventType:popup:disable',
	    disableCancel: 'BX:Sale:StoreMaster:EventType:popup:disable:cancel',
	    confirm: 'BX:Sale:StoreMaster:EventType:popup:confirm',
	    confirmCancel: 'BX:Sale:StoreMaster:EventType:popup:confirm:cancel'
	  }
	});

	var _templateObject;
	var DialogOneC = /*#__PURE__*/function () {
	  function DialogOneC() {
	    babelHelpers.classCallCheck(this, DialogOneC);
	  }

	  babelHelpers.createClass(DialogOneC, [{
	    key: "popup",
	    value: function popup() {
	      var popup = new main_popup.Popup(null, null, {
	        events: {
	          onPopupClose: function onPopupClose() {
	            popup.destroy();
	          }
	        },
	        content: this.getContent(),
	        maxWidth: 500,
	        overlay: true,
	        buttons: [new ui_buttons.Button({
	          text: main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_CLOSE'),
	          color: ui_buttons.Button.Color.PRIMARY,
	          onclick: function onclick() {
	            popup.close();
	          }
	        })]
	      });
	      popup.show();
	    }
	  }, {
	    key: "getContent",
	    value: function getContent() {
	      return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class='catalog-warehouse-master-clear-popup-content'>\n\t\t\t\t\t\t<h3>", "</h3>\n\t\t\t\t\t\t<div class=\"catalog-warehouse-master-clear-popup-text\">", "\n\t\t\t\t\t</div>\n\t\t\t\t"])), main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_10'), main_core.Text.encode(main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_9')));
	    }
	  }]);
	  return DialogOneC;
	}();

	var _templateObject$1;
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
	      var popup = new main_popup.Popup(null, null, {
	        events: {
	          onPopupClose: function onPopupClose() {
	            popup.destroy();
	          }
	        },
	        content: this.getDisablePopupContent(),
	        maxWidth: 500,
	        overlay: true,
	        buttons: [new ui_buttons.Button({
	          text: main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_6'),
	          color: ui_buttons.Button.Color.PRIMARY,
	          onclick: function onclick() {
	            popup.close();
	            main_core_events.EventEmitter.emit(EventType.popup.disable, {});
	          }
	        }), new BX.UI.Button({
	          text: main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_1'),
	          color: BX.UI.Button.Color.LINK,
	          onclick: function onclick() {
	            popup.close();
	            main_core_events.EventEmitter.emit(EventType.popup.disableCancel, {});
	          }
	        })]
	      });
	      popup.show();
	    }
	  }, {
	    key: "getDisablePopupContent",
	    value: function getDisablePopupContent() {
	      return main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class='catalog-warehouse-master-clear-popup-content'>\n\t\t\t\t\t\t<h3>", "</h3>\n\t\t\t\t\t\t<div class=\"catalog-warehouse-master-clear-popup-text\">", "\n\t\t\t\t\t\t<br>", "<div>\n\t\t\t\t\t</div>\n\t\t\t\t"])), main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_DISABLE_POPUP_TITLE'), main_core.Text.encode(main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_7')), main_core.Text.encode(main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_8')));
	    }
	  }]);
	  return DialogDisable;
	}();

	var _templateObject$2;

	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }

	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _getArticleCode = /*#__PURE__*/new WeakSet();

	var _getPopupContent = /*#__PURE__*/new WeakSet();

	var DialogEnable = /*#__PURE__*/function () {
	  function DialogEnable() {
	    babelHelpers.classCallCheck(this, DialogEnable);

	    _classPrivateMethodInitSpec$1(this, _getPopupContent);

	    _classPrivateMethodInitSpec$1(this, _getArticleCode);
	  }

	  babelHelpers.createClass(DialogEnable, [{
	    key: "popup",
	    value: function popup() {
	      var _this = this;

	      main_core.ajax.runAction('catalog.config.checkEnablingConditions', {}).then(function (response) {
	        var result = response.data;

	        if (result.includes(DialogEnable.QUANTITY_INCONSISTENCY_EXISTS) && result.includes(DialogEnable.CONDUCTED_DOCUMENTS_EXIST)) {
	          _this.quantityInconsistencyPopup();
	        } else if (result.includes(DialogEnable.QUANTITY_INCONSISTENCY_EXISTS)) {
	          new catalog_storeUse.DialogClearing().popup();
	        } else if (result.includes(DialogEnable.CONDUCTED_DOCUMENTS_EXIST)) {
	          _this.conductedDocumentsPopup();
	        } else {
	          main_core_events.EventEmitter.emit(EventType.popup.enable, {});
	        }
	      });
	    }
	  }, {
	    key: "quantityInconsistencyPopup",
	    value: function quantityInconsistencyPopup() {
	      var popup = new main_popup.Popup({
	        events: {
	          onPopupClose: function onPopupClose() {
	            popup.destroy();
	          }
	        },
	        content: _classPrivateMethodGet$1(this, _getPopupContent, _getPopupContent2).call(this, main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_ENABLE_CLEAR_CONFIRM')),
	        maxWidth: 500,
	        overlay: true,
	        closeIcon: true,
	        closeByEsc: true,
	        buttons: [new ui_buttons.Button({
	          text: main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_ENABLE_WITH_RESET'),
	          color: ui_buttons.Button.Color.PRIMARY,
	          onclick: function onclick() {
	            popup.close();
	            main_core_events.EventEmitter.emit(EventType.popup.enableWithResetDocuments, {});
	          }
	        }), new BX.UI.Button({
	          text: main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_ENABLE_EASY'),
	          color: BX.UI.Button.Color.LINK,
	          onclick: function onclick() {
	            popup.close();
	            main_core_events.EventEmitter.emit(EventType.popup.enableWithoutReset, {});
	          }
	        })]
	      });
	      popup.show();
	    }
	  }, {
	    key: "conductedDocumentsPopup",
	    value: function conductedDocumentsPopup() {
	      var popup = new main_popup.Popup({
	        events: {
	          onPopupClose: function onPopupClose() {
	            popup.destroy();
	          }
	        },
	        content: _classPrivateMethodGet$1(this, _getPopupContent, _getPopupContent2).call(this, main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_ENABLE_CONFIRM')),
	        maxWidth: 500,
	        overlay: true,
	        closeIcon: true,
	        closeByEsc: true,
	        buttons: [new ui_buttons.Button({
	          text: main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_ENABLE_EASY'),
	          color: ui_buttons.Button.Color.PRIMARY,
	          onclick: function onclick() {
	            popup.close();
	            main_core_events.EventEmitter.emit(EventType.popup.enableWithoutReset, {});
	          }
	        }), new BX.UI.Button({
	          text: main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_ENABLE_WITH_RESET'),
	          color: BX.UI.Button.Color.LINK,
	          onclick: function onclick() {
	            popup.close();
	            main_core_events.EventEmitter.emit(EventType.popup.enableWithResetDocuments, {});
	          }
	        })]
	      });
	      popup.show();
	    }
	  }]);
	  return DialogEnable;
	}();

	function _getArticleCode2() {
	  return 15992592;
	}

	function _getPopupContent2(text) {
	  var _this2 = this;

	  var content = main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class='catalog-warehouse-master-clear-popup-content'>\n\t\t\t\t<h3>", "</h3>\n\t\t\t\t<div class=\"catalog-warehouse-master-clear-popup-text\">\n\t\t\t\t\t<span>", "</span> <a href='#' class=\"catalog-warehouse-master-clear-popup-hint\">", "</a>\n\t\t\t\t<div>\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_ENABLE_TITLE'), text, main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_ENABLE_LINK_TITLE'));
	  content.querySelector('.catalog-warehouse-master-clear-popup-hint').addEventListener('click', function (e) {
	    e.preventDefault();

	    if (top.BX.Helper) {
	      top.BX.Helper.show("redirect=detail&code=".concat(_classPrivateMethodGet$1(_this2, _getArticleCode, _getArticleCode2).call(_this2)));
	    }
	  });
	  return content;
	}

	babelHelpers.defineProperty(DialogEnable, "QUANTITY_INCONSISTENCY_EXISTS", 'QUANTITY_INCONSISTENCY_EXISTS');
	babelHelpers.defineProperty(DialogEnable, "CONDUCTED_DOCUMENTS_EXIST", 'CONDUCTED_DOCUMENTS_EXIST');

	var _templateObject$3;
	var DialogClearing = /*#__PURE__*/function () {
	  function DialogClearing() {
	    babelHelpers.classCallCheck(this, DialogClearing);
	  }

	  babelHelpers.createClass(DialogClearing, [{
	    key: "popup",
	    value: function popup() {
	      var popup = new main_popup.Popup(null, null, {
	        events: {
	          onPopupClose: function onPopupClose() {
	            popup.destroy();
	          }
	        },
	        content: this.getContent(),
	        maxWidth: 500,
	        overlay: true,
	        buttons: [new ui_buttons.Button({
	          text: main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_2'),
	          color: ui_buttons.Button.Color.PRIMARY,
	          onclick: function onclick() {
	            popup.close();
	            main_core_events.EventEmitter.emit(EventType.popup.enable, {});
	          }
	        }), new BX.UI.Button({
	          text: main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_1'),
	          color: BX.UI.Button.Color.LINK,
	          onclick: function onclick() {
	            popup.close();
	            main_core_events.EventEmitter.emit(EventType.popup.confirmCancel, {});
	          }
	        })]
	      });
	      popup.show();
	    }
	  }, {
	    key: "getContent",
	    value: function getContent() {
	      return main_core.Tag.render(_templateObject$3 || (_templateObject$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class='catalog-warehouse-master-clear-popup-content'>\n\t\t\t\t\t\t<h3>", "</h3>\n\t\t\t\t\t\t<div class=\"catalog-warehouse-master-clear-popup-text\">", "\n\t\t\t\t\t\t<br>", "<div>\n\t\t\t\t\t</div>\n\t\t\t\t"])), main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_3'), main_core.Text.encode(main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_4')), main_core.Text.encode(main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_5')));
	    }
	  }]);
	  return DialogClearing;
	}();

	var _templateObject$4, _templateObject2;
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
	      var popup = new main_popup.Popup(null, null, {
	        events: {
	          onPopupClose: function onPopupClose() {
	            return popup.destroy();
	          }
	        },
	        content: this.getContent(),
	        maxWidth: 500,
	        overlay: true,
	        buttons: [new ui_buttons.Button({
	          text: main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_CLOSE'),
	          color: ui_buttons.Button.Color.PRIMARY,
	          onclick: function onclick() {
	            return popup.close();
	          }
	        })]
	      });
	      popup.show();
	    }
	  }, {
	    key: "showHelp",
	    value: function showHelp(event) {
	      if (top.BX.Helper) {
	        top.BX.Helper.show('redirect=detail&code=' + this.helpArticleId);
	        event.preventDefault();
	      }
	    }
	  }, {
	    key: "getHelpLink",
	    value: function getHelpLink() {
	      var result = main_core.Tag.render(_templateObject$4 || (_templateObject$4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<a href=\"#\" class=\"ui-link ui-link-dashed documents-grid-link\">\n\t\t\t\t", "\n\t\t\t</a>\n\t\t"])), main_core.Text.encode(main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_DETAILS')));
	      result.addEventListener('click', this.showHelp.bind(this));
	      return result;
	    }
	  }, {
	    key: "getContent",
	    value: function getContent() {
	      return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"catalog-warehouse-master-clear-popup-content\">\n\t\t\t\t<h3>\n\t\t\t\t\t", "\n\t\t\t\t</h3>\t\n\t\t\t\t<div class=\"catalog-warehouse-master-clear-popup-text\">\n\t\t\t\t\t", " \n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_10'), main_core.Text.encode(this.text), this.helpArticleId ? this.getHelpLink() : '');
	    }
	  }]);
	  return DialogError;
	}();

	var Slider = /*#__PURE__*/function () {
	  function Slider() {
	    babelHelpers.classCallCheck(this, Slider);
	  }

	  babelHelpers.createClass(Slider, [{
	    key: "open",
	    value: function open(url) {
	      var params = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      params = main_core.Type.isPlainObject(params) ? params : {};
	      return new Promise(function (resolve) {
	        var data = params.hasOwnProperty("data") ? params.data : {};
	        var events = params.hasOwnProperty("events") ? params.events : {};
	        events.onClose = events.hasOwnProperty("onClose") ? events.onClose : function (event) {
	          return resolve(event.getSlider());
	        };
	        url = BX.util.add_url_param(url, {
	          "analyticsLabel": "inventoryManagementEnabled_openSlider"
	        });

	        if (main_core.Type.isString(url) && url.length > 1) {
	          BX.SidePanel.Instance.open(url, {
	            cacheable: false,
	            allowChangeHistory: false,
	            events: events,
	            data: data,
	            width: 1130
	          });
	        } else {
	          resolve();
	        }
	      });
	    }
	  }]);
	  return Slider;
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
	      new Slider().open(url, {
	        data: {
	          closeSliderOnDone: false
	        }
	      }).then(function () {
	        ProductGridMenu.reloadGridAction();
	      });
	    }
	  }]);
	  return ProductGridMenu;
	}();

	exports.Controller = Controller;
	exports.EventType = EventType;
	exports.DialogOneC = DialogOneC;
	exports.DialogEnable = DialogEnable;
	exports.DialogDisable = DialogDisable;
	exports.DialogClearing = DialogClearing;
	exports.DialogError = DialogError;
	exports.Slider = Slider;
	exports.Popup = Popup;
	exports.ProductGridMenu = ProductGridMenu;

}((this.BX.Catalog.StoreUse = this.BX.Catalog.StoreUse || {}),BX,BX.Catalog.StoreUse,BX.Event,BX.UI,BX,BX.Main));
//# sourceMappingURL=store-use.bundle.js.map
