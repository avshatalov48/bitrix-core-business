this.BX = this.BX || {};
this.BX.Catalog = this.BX.Catalog || {};
(function (exports,main_popup,ui_buttons,main_core_events,main_core) {
	'use strict';

	var EventType = Object.freeze({
	  popup: {
	    disable: 'BX:Sale:StoreMaster:EventType:popup:disable',
	    disableCancel: 'BX:Sale:StoreMaster:EventType:popup:disable:cancel',
	    confirm: 'BX:Sale:StoreMaster:EventType:popup:confirm',
	    confirmCancel: 'BX:Sale:StoreMaster:EventType:popup:confirm:cancel'
	  }
	});

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class='catalog-warehouse-master-clear-popup-content'>\n\t\t\t\t\t\t<h3>", "</h3>\n\t\t\t\t\t\t<div class=\"catalog-warehouse-master-clear-popup-text\">", "\n\t\t\t\t\t</div>\n\t\t\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
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
	      return main_core.Tag.render(_templateObject(), main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_10'), main_core.Text.encode(main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_9')));
	    }
	  }]);
	  return DialogOneC;
	}();

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class='catalog-warehouse-master-clear-popup-content'>\n\t\t\t\t\t\t<h3>", "</h3>\n\t\t\t\t\t\t<div class=\"catalog-warehouse-master-clear-popup-text\">", "<div>\n\t\t\t\t\t</div>\n\t\t\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class='catalog-warehouse-master-clear-popup-content'>\n\t\t\t\t\t\t<h3>", "</h3>\n\t\t\t\t\t\t<div class=\"catalog-warehouse-master-clear-popup-text\">", "\n\t\t\t\t\t\t<br>", "<div>\n\t\t\t\t\t</div>\n\t\t\t\t"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var DialogDisable = /*#__PURE__*/function () {
	  function DialogDisable() {
	    babelHelpers.classCallCheck(this, DialogDisable);
	  }

	  babelHelpers.createClass(DialogDisable, [{
	    key: "popup",
	    value: function popup() {
	      var _this = this;

	      main_core.ajax.runAction('catalog.config.conductedDocumentsExist', {}).then(function (response) {
	        var documentsExist = response.data;

	        if (documentsExist) {
	          _this.conductedDocumentsPopup();
	        } else {
	          _this.disablePopup();
	        }
	      });
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
	      return main_core.Tag.render(_templateObject$1(), main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_DISABLE_POPUP_TITLE'), main_core.Text.encode(main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_7')), main_core.Text.encode(main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_8')));
	    }
	  }, {
	    key: "conductedDocumentsPopup",
	    value: function conductedDocumentsPopup() {
	      var popup = new BX.Main.Popup(null, null, {
	        events: {
	          onPopupClose: function onPopupClose() {
	            popup.destroy();
	          }
	        },
	        content: this.getConductedDocumentsPopupContent(),
	        maxWidth: 500,
	        overlay: true,
	        buttons: [new BX.UI.Button({
	          text: main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_CLOSE'),
	          color: BX.UI.Button.Color.PRIMARY,
	          onclick: function onclick() {
	            popup.close();
	            main_core_events.EventEmitter.emit(EventType.popup.disableCancel, {});
	          }
	        })]
	      });
	      popup.show();
	    }
	  }, {
	    key: "getConductedDocumentsPopupContent",
	    value: function getConductedDocumentsPopupContent() {
	      return main_core.Tag.render(_templateObject2(), main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_DISABLE_POPUP_TITLE'), main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_CONDUCTED_DOCUMENTS_EXIST'));
	    }
	  }]);
	  return DialogDisable;
	}();

	function _templateObject$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class='catalog-warehouse-master-clear-popup-content'>\n\t\t\t\t\t\t<h3>", "</h3>\n\t\t\t\t\t\t<div class=\"catalog-warehouse-master-clear-popup-text\">", "\n\t\t\t\t\t\t<br>", "<div>\n\t\t\t\t\t</div>\n\t\t\t\t"]);

	  _templateObject$2 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
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
	            main_core_events.EventEmitter.emit(EventType.popup.confirm, {});
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
	      return main_core.Tag.render(_templateObject$2(), main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_3'), main_core.Text.encode(main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_4')), main_core.Text.encode(main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_5')));
	    }
	  }]);
	  return DialogClearing;
	}();

	var Slider = /*#__PURE__*/function () {
	  function Slider() {
	    babelHelpers.classCallCheck(this, Slider);
	  }

	  babelHelpers.createClass(Slider, [{
	    key: "open",
	    value: function open(url, options) {
	      if (!main_core.Type.isPlainObject(options)) {
	        options = {};
	      }

	      options = babelHelpers.objectSpread({}, {
	        cacheable: false,
	        allowChangeHistory: false,
	        events: {}
	      }, options);
	      return new Promise(function (resolve) {
	        if (main_core.Type.isString(url) && url.length > 1) {
	          options.events.onClose = function (event) {
	            resolve(event.getSlider());
	          };

	          BX.SidePanel.Instance.open(url, options);
	        } else {
	          resolve();
	        }
	      });
	    }
	  }]);
	  return Slider;
	}();

	exports.EventType = EventType;
	exports.DialogOneC = DialogOneC;
	exports.DialogDisable = DialogDisable;
	exports.DialogClearing = DialogClearing;
	exports.Slider = Slider;

}((this.BX.Catalog.StoreUse = this.BX.Catalog.StoreUse || {}),BX.Main,BX.UI,BX.Event,BX));
//# sourceMappingURL=store-use.bundle.js.map
