this.BX = this.BX || {};
this.BX.Catalog = this.BX.Catalog || {};
(function (exports,main_core_events,ui_buttons,main_core,main_popup) {
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

	function _templateObject2$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"catalog-warehouse-master-clear-popup-content\">\n\t\t\t\t<h3>\n\t\t\t\t\t", "\n\t\t\t\t</h3>\t\n\t\t\t\t<div class=\"catalog-warehouse-master-clear-popup-text\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"]);

	  _templateObject2$1 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<a href=\"#\" class=\"ui-link ui-link-dashed documents-grid-link\">\n\t\t\t\t", "\n\t\t\t</a>\n\t\t"]);

	  _templateObject$3 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
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
	      var result = main_core.Tag.render(_templateObject$3(), main_core.Text.encode(main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_DETAILS')));
	      result.addEventListener('click', this.showHelp.bind(this));
	      return result;
	    }
	  }, {
	    key: "getContent",
	    value: function getContent() {
	      return main_core.Tag.render(_templateObject2$1(), main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_STORE_USE_10'), main_core.Text.encode(this.text), this.helpArticleId ? this.getHelpLink() : '');
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

	exports.EventType = EventType;
	exports.DialogOneC = DialogOneC;
	exports.DialogDisable = DialogDisable;
	exports.DialogClearing = DialogClearing;
	exports.DialogError = DialogError;
	exports.Slider = Slider;
	exports.Popup = Popup;

}((this.BX.Catalog.StoreUse = this.BX.Catalog.StoreUse || {}),BX.Event,BX.UI,BX,BX.Main));
//# sourceMappingURL=store-use.bundle.js.map
