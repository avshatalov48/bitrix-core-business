this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
this.BX.Calendar.Sync = this.BX.Calendar.Sync || {};
(function (exports,main_core_events,ui_dialogs_messagebox,calendar_util,calendar_sync_manager,ui_tilegrid,ui_forms,main_popup,main_core) {
	'use strict';

	var SyncPanelItem = /*#__PURE__*/function (_BX$TileGrid$Item) {
	  babelHelpers.inherits(SyncPanelItem, _BX$TileGrid$Item);

	  function SyncPanelItem(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, SyncPanelItem);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SyncPanelItem).call(this, options));
	    _this.options = options;
	    return _this;
	  }

	  babelHelpers.createClass(SyncPanelItem, [{
	    key: "getContent",
	    value: function getContent() {
	      if (this.options.className) {
	        var itemClass = main_core.Reflection.getClass(this.options.className);

	        if (main_core.Type.isFunction(itemClass)) {
	          var item = new itemClass(this.options);
	          return item.getInnerContent();
	        }

	        return '';
	      }
	    }
	  }]);
	  return SyncPanelItem;
	}(BX.TileGrid.Item);

	function _templateObject6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-slider-form-btn\"></div>\n\t\t"]);

	  _templateObject6 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button id=\"edit-connect-button\" class=\"calendar-sync-slider-btn ui-btn ui-btn-light-border\">", "</button>\n\t\t"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button id=\"disconnect-button\" class=\"calendar-sync-slider-btn ui-btn ui-btn-light-border\">", "</button>\n\t\t"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button id=\"connect-button\" class=\"ui-btn ui-btn-light-border\">", "</button>\n\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<form class=\"calendar-sync-slider-form\" action=\"\">\n\t\t\t\t<div class=\"calendar-sync-slider-field\">\n\t\t\t\t\t<div class=\"ui-ctl ui-ctl-w100 ui-ctl-textbox\">\n\t\t\t\t\t\t<input type=\"text\" class=\"ui-ctl-element\" placeholder=\"", "\" name=\"name\" value=\"", "\">\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"calendar-sync-slider-field\">\n\t\t\t\t\t<div class=\"ui-ctl ui-ctl-w100 ui-ctl-textbox\">\n\t\t\t\t\t\t<input type=\"text\" class=\"ui-ctl-element\" placeholder=\"", "\" name=\"server\" value=\"", "\">\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"calendar-sync-slider-field\">\n\t\t\t\t\t<div class=\"ui-ctl ui-ctl-w100 ui-ctl-textbox\">\n\t\t\t\t\t\t<input type=\"text\" class=\"ui-ctl-element\" placeholder=\"", "\" name=\"user_name\" value=\"", "\">\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"calendar-sync-slider-field\">\n\t\t\t\t\t<div class=\"ui-ctl ui-ctl-w100 ui-ctl-textbox\">\n\t\t\t\t\t\t<input type=\"password\" class=\"ui-ctl-element\" name=\"password\" placeholder=\"", "\">\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</form>\n\t\t"], ["\n\t\t\t<form class=\"calendar-sync-slider-form\" action=\"\">\n\t\t\t\t<div class=\"calendar-sync-slider-field\">\n\t\t\t\t\t<div class=\"ui-ctl ui-ctl-w100 ui-ctl-textbox\">\n\t\t\t\t\t\t<input type=\"text\" class=\"ui-ctl-element\" placeholder=\\\"", "\\\" name=\"name\" value=\"", "\">\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"calendar-sync-slider-field\">\n\t\t\t\t\t<div class=\"ui-ctl ui-ctl-w100 ui-ctl-textbox\">\n\t\t\t\t\t\t<input type=\"text\" class=\"ui-ctl-element\" placeholder=\\\"", "\\\" name=\"server\" value=\"", "\">\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"calendar-sync-slider-field\">\n\t\t\t\t\t<div class=\"ui-ctl ui-ctl-w100 ui-ctl-textbox\">\n\t\t\t\t\t\t<input type=\"text\" class=\"ui-ctl-element\" placeholder=\\\"", "\\\" name=\"user_name\" value=\"", "\">\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"calendar-sync-slider-field\">\n\t\t\t\t\t<div class=\"ui-ctl ui-ctl-w100 ui-ctl-textbox\">\n\t\t\t\t\t\t<input type=\"password\" class=\"ui-ctl-element\" name=\"password\" placeholder=\\\"", "\\\">\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</form>\n\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-slider-section calendar-sync-slider-section-form\"></div>\n\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var ConnectionControls = /*#__PURE__*/function () {
	  function ConnectionControls() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	    babelHelpers.classCallCheck(this, ConnectionControls);
	    babelHelpers.defineProperty(this, "userName", null);
	    babelHelpers.defineProperty(this, "server", null);
	    babelHelpers.defineProperty(this, "connectionName", null);
	    this.addButtonText = main_core.Loc.getMessage('CAL_UPPER_CONNECT');
	    this.removeButtonText = main_core.Loc.getMessage('CAL_UPPER_DISCONNECT');
	    this.saveButtonText = main_core.Loc.getMessage('CAL_UPPER_SAVE');

	    if (options !== null) {
	      this.userName = BX.util.htmlspecialchars(options.userName);
	      this.server = BX.util.htmlspecialchars(options.server);
	      this.connectionName = BX.util.htmlspecialchars(options.connectionName);
	    }
	  }

	  babelHelpers.createClass(ConnectionControls, [{
	    key: "getWrapper",
	    value: function getWrapper() {
	      return main_core.Tag.render(_templateObject());
	    }
	  }, {
	    key: "getForm",
	    value: function getForm() {
	      return main_core.Tag.render(_templateObject2(), main_core.Loc.getMessage('CAL_TEXT_NAME'), this.connectionName || '', main_core.Loc.getMessage('CAL_TEXT_SERVER_ADDRESS'), this.server || '', main_core.Loc.getMessage('CAL_TEXT_USER_NAME'), this.userName || '', main_core.Loc.getMessage('CAL_TEXT_PASSWORD'));
	    }
	  }, {
	    key: "getAddButton",
	    value: function getAddButton() {
	      return main_core.Tag.render(_templateObject3(), this.addButtonText);
	    }
	  }, {
	    key: "getDisconnectButton",
	    value: function getDisconnectButton() {
	      return main_core.Tag.render(_templateObject4(), this.removeButtonText);
	    }
	  }, {
	    key: "getSaveButton",
	    value: function getSaveButton() {
	      return main_core.Tag.render(_templateObject5(), this.saveButtonText);
	    }
	  }, {
	    key: "getButtonWrapper",
	    value: function getButtonWrapper() {
	      return main_core.Tag.render(_templateObject6());
	    }
	  }]);
	  return ConnectionControls;
	}();

	function _templateObject2$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"calendar-notice-mobile-banner\" data-hint=\"", "\" data-hint-no-icon=\"Y\"></span>"]);

	  _templateObject2$1 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t", "\n\t\t\t<div class=\"calendar-sync-qr-popup-content\">\n\t\t\t\t<div class=\"calendar-sync-qr-popup-title\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t<div class=\"calendar-sync-slider-content\">\n\t\t\t\t\t<img class=\"calendar-sync-slider-phone-img\" src=\"/bitrix/images/calendar/sync/qr-background.svg\" alt=\"\">\n\t\t\t\t\t<div class=\"calendar-sync-slider-qr\">\n\t\t\t\t\t\t<div class=\"", "\">", "</div>\n\t\t\t\t\t\t<span class=\"calendar-sync-slider-logo\"></span>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"calendar-sync-slider-instruction\">\n\t\t\t\t\t\t<!--<div class=\"calendar-sync-slider-instruction-subtitle\"></div>-->\n\t\t\t\t\t\t<div class=\"calendar-sync-slider-instruction-title\">", " ", "</div>\n\t\t\t\t\t\t<div class=\"calendar-sync-slider-instruction-notice\">", "</div>\n\t\t\t\t\t\t<a href=\"javascript:void(0);\" \n\t\t\t\t\t\t\t\tonclick=\"BX.Helper.show('redirect=detail&code=' + ", ",{zIndex:3100,}); event.preventDefault();\" \n\t\t\t\t\t\t\t\tclass=\"ui-btn ui-btn-success ui-btn-round\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</a>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var MobileSyncBanner = /*#__PURE__*/function () {
	  function MobileSyncBanner() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, MobileSyncBanner);
	    babelHelpers.defineProperty(this, "zIndex", 3100);
	    babelHelpers.defineProperty(this, "DOM", {});
	    babelHelpers.defineProperty(this, "QRCODE_SIZE", 186);
	    babelHelpers.defineProperty(this, "QRCODE_COLOR_LIGHT", '#ffffff');
	    babelHelpers.defineProperty(this, "QRCODE_COLOR_DARK", '#000000');
	    babelHelpers.defineProperty(this, "QRCODE_WRAP_CLASS", 'calendar-sync-slider-qr-container');
	    babelHelpers.defineProperty(this, "QRC", null);
	    this.type = options.type;
	    this.helpDeskCode = options.helpDeskCode || '11828176';
	  }

	  babelHelpers.createClass(MobileSyncBanner, [{
	    key: "show",
	    value: function show() {}
	  }, {
	    key: "showInPopup",
	    value: function showInPopup() {
	      this.popup = new main_popup.Popup({
	        className: 'calendar-sync-qr-popup',
	        draggable: true,
	        content: this.getContainer(),
	        width: 580,
	        zIndexAbsolute: this.zIndex,
	        cacheable: false,
	        closeByEsc: true,
	        closeIcon: true
	      });
	      this.popup.show();
	      this.initQrCode().then(this.drawQRCode.bind(this));
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      this.popup.close();
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      this.DOM.container = main_core.Tag.render(_templateObject$1(), this.getSliderContentInfoBlock, this.getTitle(), this.QRCODE_WRAP_CLASS, calendar_util.Util.getLoader(this.QRCODE_SIZE), main_core.Loc.getMessage('SYNC_MOBILE_NOTICE_HOW_TO'), this.type !== 'iphone' ? main_core.Tag.render(_templateObject2$1(), main_core.Loc.getMessage('CAL_ANDROID_QR_CODE_HINT')) : '', main_core.Loc.getMessage('SYNC_MOBILE_NOTICE'), this.getHelpdeskCode(), main_core.Loc.getMessage('SYNC_MOBILE_ABOUT_BTN'));
	      this.DOM.mobileHintIcon = this.DOM.container.querySelector('.calendar-notice-mobile-banner');

	      if (this.DOM.mobileHintIcon && BX.UI.Hint) {
	        BX.UI.Hint.initNode(this.DOM.mobileHintIcon);
	      }

	      return this.DOM.container;
	    }
	  }, {
	    key: "getInnerContainer",
	    value: function getInnerContainer() {
	      return this.DOM.container.querySelector('.' + this.QRCODE_WRAP_CLASS);
	    }
	  }, {
	    key: "initQrCode",
	    value: function initQrCode() {
	      return new Promise(function (resolve) {
	        main_core.Runtime.loadExtension(['main.qrcode']).then(function (exports) {
	          if (exports && exports.QRCode) {
	            resolve();
	          }
	        });
	      });
	    }
	  }, {
	    key: "drawQRCode",
	    value: function drawQRCode(wrap) {
	      var _this = this;

	      if (!main_core.Type.isDomNode(wrap)) {
	        wrap = this.getInnerContainer();
	      }

	      this.getMobileSyncUrl().then(function (link) {
	        main_core.Dom.clean(wrap);
	        _this.QRC = new QRCode(wrap, {
	          text: link,
	          width: _this.getSize(),
	          height: _this.getSize(),
	          colorDark: _this.QRCODE_COLOR_DARK,
	          colorLight: _this.QRCODE_COLOR_LIGHT,
	          correctLevel: QRCode.CorrectLevel.H
	        });
	      });
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      return main_core.Loc.getMessage('SYNC_BANNER_MOBILE_TITLE');
	    }
	  }, {
	    key: "getMobileSyncUrl",
	    value: function getMobileSyncUrl() {
	      var _this2 = this;

	      return new Promise(function (resolve, reject) {
	        BX.ajax.runAction('calendar.api.calendarajax.getAuthLink', {
	          data: {
	            type: _this2.type ? 'slider' : 'banner'
	          }
	        }).then(function (response) {
	          resolve(response.data.link);
	        }, reject);
	      });
	    }
	  }, {
	    key: "getSize",
	    value: function getSize() {
	      return this.QRCODE_SIZE;
	    }
	  }, {
	    key: "getDetailHelpUrl",
	    value: function getDetailHelpUrl() {
	      return 'https://helpdesk.bitrix24.ru/open/' + this.getHelpdeskCode();
	    }
	  }, {
	    key: "getHelpdeskCode",
	    value: function getHelpdeskCode() {
	      return this.helpDeskCode;
	    }
	  }]);
	  return MobileSyncBanner;
	}();

	function _templateObject5$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-sync-status-subtitle\">\n\t\t\t\t\t<span data-hint=\"\"></span>\n\t\t\t\t\t<span class=\"calendar-sync-status-text\">", ":</span>\n\t\t\t\t</div>"]);

	  _templateObject5$1 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-status-block\" id=\"calendar-sync-status-block\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject4$1 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div id=\"status-info-block\" class=\"ui-alert ui-alert-primary calendar-sync-status-info\">\n\t\t\t\t\t<span class=\"ui-alert-message\">", "</span>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject3$1 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div id=\"status-info-block\" class=\"ui-alert ui-alert-danger calendar-sync-status-info\">\n\t\t\t\t\t<span class=\"ui-alert-message\">", "</span>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject2$2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div id=\"status-info-block\" class=\"ui-alert ui-alert-success calendar-sync-status-info\">\n\t\t\t\t\t<span class=\"ui-alert-message\">", "</span>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject$2 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var StatusBlock = /*#__PURE__*/function () {
	  function StatusBlock(options) {
	    babelHelpers.classCallCheck(this, StatusBlock);
	    this.status = options.status;
	    this.connections = options.connections;
	    this.withStatusLabel = options.withStatusLabel;
	    this.popupWithUpdateButton = options.popupWithUpdateButton;
	    this.popupId = options.popupId;
	  }

	  babelHelpers.createClass(StatusBlock, [{
	    key: "setStatus",
	    value: function setStatus(status) {
	      this.status = status;
	      return this;
	    }
	  }, {
	    key: "setConnections",
	    value: function setConnections(connections) {
	      this.connections = connections;
	      return this;
	    }
	  }, {
	    key: "getContent",
	    value: function getContent() {
	      var _this = this;

	      var statusInfoBlock;

	      if (this.status === 'success') {
	        statusInfoBlock = main_core.Tag.render(_templateObject$2(), main_core.Loc.getMessage('SYNC_STATUS_SUCCESS'));
	      } else if (this.status === 'failed') {
	        statusInfoBlock = main_core.Tag.render(_templateObject2$2(), main_core.Loc.getMessage('SYNC_STATUS_ALERT'));
	      } else {
	        statusInfoBlock = main_core.Tag.render(_templateObject3$1(), main_core.Loc.getMessage('SYNC_STATUS_NOT_CONNECTED'));
	      }

	      statusInfoBlock.addEventListener('mouseenter', function () {
	        _this.handlerMouseEnter(statusInfoBlock);
	      });
	      statusInfoBlock.addEventListener('mouseleave', function () {
	        _this.handlerMouseLeave();
	      });
	      this.statusBlock = main_core.Tag.render(_templateObject4$1(), this.getStatusTextLabel(), statusInfoBlock);
	      return this.statusBlock;
	    }
	  }, {
	    key: "getStatusTextLabel",
	    value: function getStatusTextLabel() {
	      return this.withStatusLabel ? main_core.Tag.render(_templateObject5$1(), main_core.Loc.getMessage('LABEL_STATUS_INFO')) : '';
	    }
	  }, {
	    key: "handlerMouseEnter",
	    value: function handlerMouseEnter(statusBlock) {
	      var _this2 = this;

	      clearTimeout(this.statusBlockEnterTimeout);
	      this.buttonEnterTimeout = setTimeout(function () {
	        _this2.statusBlockEnterTimeout = null;

	        _this2.showPopup(statusBlock);
	      }, 500);
	    }
	  }, {
	    key: "handlerMouseLeave",
	    value: function handlerMouseLeave() {
	      var _this3 = this;

	      if (this.statusBlockEnterTimeout !== null) {
	        clearTimeout(this.statusBlockEnterTimeout);
	        this.statusBlockEnterTimeout = null;
	        return;
	      }

	      this.statusBlockLeaveTimeout = setTimeout(function () {
	        _this3.hidePopup();
	      }, 500);
	    }
	  }, {
	    key: "showPopup",
	    value: function showPopup(node) {
	      if (this.status !== 'not_connected') {
	        this.popup = this.getPopup(node);
	        this.popup.show();
	        this.addPopupHandlers();
	      }
	    }
	  }, {
	    key: "hidePopup",
	    value: function hidePopup() {
	      if (this.popup) {
	        this.popup.hide();
	      }
	    }
	  }, {
	    key: "addPopupHandlers",
	    value: function addPopupHandlers() {
	      var _this4 = this;

	      this.popup.getPopup().getPopupContainer().addEventListener('mouseenter', function () {
	        clearTimeout(_this4.statusBlockEnterTimeout);
	        clearTimeout(_this4.statusBlockLeaveTimeout);
	      });
	      this.popup.getPopup().getPopupContainer().addEventListener('mouseleave', function () {
	        _this4.hidePopup();
	      });
	    }
	  }, {
	    key: "getPopup",
	    value: function getPopup(node) {
	      return calendar_sync_manager.SyncStatusPopup.createInstance({
	        connections: this.connections,
	        withUpdateButton: this.popupWithUpdateButton,
	        node: node,
	        id: this.popupId
	      });
	    }
	  }, {
	    key: "refresh",
	    value: function refresh(status, connections) {
	      this.status = status;
	      this.connections = connections;
	      return this;
	    }
	  }], [{
	    key: "createInstance",
	    value: function createInstance(options) {
	      return new this(options);
	    }
	  }]);
	  return StatusBlock;
	}();

	function _templateObject8() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-slider-section\">\n\t\t\t\t<div class=\"calendar-sync-slider-header-icon ", "\"></div>\n\t\t\t\t<div class=\"calendar-sync-slider-header\">\n\t\t\t\t<div class=\"calendar-sync-slider-title\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t<div class=\"calendar-sync-slider-info\">\n\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</span>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"calendar-sync-slider-info\">\n\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">\n\t\t\t\t\t\t<a class=\"calendar-sync-slider-info-link\" href=\"javascript:void(0);\" onclick=\"", "\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</a>\n\t\t\t\t\t</span>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t</div>\n\t\t"]);

	  _templateObject8 = function _templateObject8() {
	    return data;
	  };

	  return data;
	}

	function _templateObject7() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-slider-section\">\n\t\t\t\t<div class=\"calendar-sync-slider-header-icon ", "\"></div>\n\t\t\t\t<div class=\"calendar-sync-slider-header\">\n\t\t\t\t<div class=\"calendar-sync-slider-title\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t<div class=\"calendar-sync-slider-info\">\n\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</span>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"calendar-sync-slider-info\">\n\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">\n\t\t\t\t\t\t<a class=\"calendar-sync-slider-info-link\" href=\"javascript:void(0);\" onclick=\"", "\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</a>\n\t\t\t\t\t</span>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t</div>\n\t\t"]);

	  _templateObject7 = function _templateObject7() {
	    return data;
	  };

	  return data;
	}

	function _templateObject6$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t", "\n\t\t"]);

	  _templateObject6$1 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-header\">\n\t\t\t\t<span class=\"calendar-sync-header-text\">", "</span>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject5$2 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t", "\n\t\t"]);

	  _templateObject4$2 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-header\">\n\t\t\t\t<span class=\"calendar-sync-header-text\">", "</span>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject3$2 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-wrap calendar-sync-wrap-detail\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject2$3 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-wrap calendar-sync-wrap-detail\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject$3 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var InterfaceTemplate = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(InterfaceTemplate, _EventEmitter);

	  function InterfaceTemplate(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, InterfaceTemplate);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(InterfaceTemplate).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "sliderWidth", 840);

	    _this.setEventNamespace('BX.Calendar.Sync.Interface.InterfaceTemplate');

	    _this.title = options.title;
	    _this.helpdeskCode = options.helpDeskCode;
	    _this.titleInfoHeader = options.titleInfoHeader;
	    _this.descriptionInfoHeader = options.descriptionInfoHeader;
	    _this.titleActiveHeader = options.titleActiveHeader;
	    _this.descriptionActiveHeader = options.descriptionActiveHeader;
	    _this.sliderIconClass = options.sliderIconClass;
	    _this.iconPath = options.iconPath;
	    _this.color = options.color;
	    _this.provider = options.provider;
	    _this.connection = options.connection;
	    _this.popupWithUpdateButton = options.popupWithUpdateButton;
	    return _this;
	  }

	  babelHelpers.createClass(InterfaceTemplate, [{
	    key: "getInfoConnectionContent",
	    value: function getInfoConnectionContent() {
	      return main_core.Tag.render(_templateObject$3(), this.getContentInfoHeader(), this.getContentInfoBody());
	    }
	  }, {
	    key: "getActiveConnectionContent",
	    value: function getActiveConnectionContent() {
	      return main_core.Tag.render(_templateObject2$3(), this.getContentActiveHeader(), this.getContentActiveBody());
	    }
	  }, {
	    key: "getContentInfoHeader",
	    value: function getContentInfoHeader() {
	      this.statusBlock = StatusBlock.createInstance({
	        status: "not_connected",
	        connections: [this.connection],
	        withStatusLabel: false,
	        popupWithUpdateButton: this.popupWithUpdateButton,
	        popupId: 'calendar-interfaceTemplate-status'
	      });
	      return main_core.Tag.render(_templateObject3$2(), this.getHeaderTitle(), this.statusBlock.getContent());
	    }
	  }, {
	    key: "getContentInfoBody",
	    value: function getContentInfoBody() {
	      return main_core.Tag.render(_templateObject4$2(), this.getContentInfoBodyHeader());
	    }
	  }, {
	    key: "getContentActiveHeader",
	    value: function getContentActiveHeader() {
	      this.statusBlock = StatusBlock.createInstance({
	        status: this.connection.getStatus(),
	        connections: [this.connection],
	        withStatusLabel: false,
	        popupWithUpdateButton: this.popupWithUpdateButton,
	        popupId: 'calendar-interfaceTemplate-status'
	      });
	      return main_core.Tag.render(_templateObject5$2(), this.getHeaderTitle(), this.statusBlock.getContent());
	    }
	  }, {
	    key: "getContentActiveBody",
	    value: function getContentActiveBody() {
	      return main_core.Tag.render(_templateObject6$1(), this.getContentActiveBodyHeader());
	    }
	  }, {
	    key: "showHelp",
	    value: function showHelp() {
	      if (BX.Helper) {
	        BX.Helper.show("redirect=detail&code=" + this.helpdeskCode);
	        event.preventDefault();
	      }
	    }
	  }, {
	    key: "getHelpdeskLink",
	    value: function getHelpdeskLink() {
	      return 'https://helpdesk.bitrix24.ru/open/' + this.helpdeskCode;
	    }
	  }, {
	    key: "getHeaderTitle",
	    value: function getHeaderTitle() {
	      return this.title;
	    }
	  }, {
	    key: "getContentInfoBodyHeader",
	    value: function getContentInfoBodyHeader() {
	      return main_core.Tag.render(_templateObject7(), this.sliderIconClass, this.titleInfoHeader, this.descriptionInfoHeader, this.showHelp.bind(this), main_core.Loc.getMessage('CAL_TEXT_ABOUT_WORK_SYNC'));
	    }
	  }, {
	    key: "getContentActiveBodyHeader",
	    value: function getContentActiveBodyHeader() {
	      return main_core.Tag.render(_templateObject8(), this.sliderIconClass, this.titleActiveHeader, this.descriptionActiveHeader, this.showHelp.bind(this), main_core.Loc.getMessage('CAL_TEXT_ABOUT_WORK_SYNC'));
	    }
	  }, {
	    key: "setProvider",
	    value: function setProvider(provider) {
	      this.provider = provider;
	    }
	  }, {
	    key: "sendRequestRemoveConnection",
	    value: function sendRequestRemoveConnection(id) {
	      BX.ajax.runAction('calendar.api.calendarajax.removeConnection', {
	        data: {
	          connectionId: id
	        }
	      }).then(function () {
	        BX.reload();
	      });
	    }
	  }, {
	    key: "runUpdateInfo",
	    value: function runUpdateInfo() {
	      var _this2 = this;

	      main_core.ajax.runAction('calendar.api.calendarajax.setSectionStatus', {
	        data: {
	          sectionStatus: this.sectionStatusObject
	        }
	      }).then(function (response) {
	        _this2.emit('reDrawCalendarGrid', {});
	      });
	    }
	  }, {
	    key: "refresh",
	    value: function refresh(connection) {
	      this.connection = connection;
	      this.statusBlock.setStatus(this.connection.getStatus()).setConnections([this.connection]);
	      main_core.Dom.replace(document.getElementById('status-info-block'), this.statusBlock.getContent());
	    }
	  }], [{
	    key: "createInstance",
	    value: function createInstance(provider) {
	      var connection = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      return new this(provider, connection);
	    }
	  }]);
	  return InterfaceTemplate;
	}(main_core_events.EventEmitter);
	babelHelpers.defineProperty(InterfaceTemplate, "SLIDER_WIDTH", 606);
	babelHelpers.defineProperty(InterfaceTemplate, "SLIDER_PREFIX", 'calendar:connection-sync-');

	function _templateObject2$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t", "\n\t\t\t", "\n\t\t"]);

	  _templateObject2$4 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t", "\n\t\t\t", "\n\t\t"]);

	  _templateObject$4 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var CaldavInterfaceTemplate = /*#__PURE__*/function (_InterfaceTemplate) {
	  babelHelpers.inherits(CaldavInterfaceTemplate, _InterfaceTemplate);

	  function CaldavInterfaceTemplate(options) {
	    babelHelpers.classCallCheck(this, CaldavInterfaceTemplate);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CaldavInterfaceTemplate).call(this, options));
	  }

	  babelHelpers.createClass(CaldavInterfaceTemplate, [{
	    key: "getContentInfoBody",
	    value: function getContentInfoBody() {
	      var _this = this;

	      var formObject = new ConnectionControls();
	      var formBlock = formObject.getWrapper();
	      var form = formObject.getForm();
	      var button = formObject.getAddButton();
	      var buttonWrapper = formObject.getButtonWrapper();
	      var bodyHeader = this.getContentInfoBodyHeader();
	      button.addEventListener('click', function (event) {
	        BX.ajax.runAction('calendar.api.calendarajax.analytical', {
	          analyticsLabel: {
	            click_to_connection_button: 'Y',
	            connection_type: _this.provider.getType()
	          }
	        });
	        main_core.Dom.addClass(button, ['ui-btn-clock', 'ui-btn-disabled']);
	        event.preventDefault();

	        _this.sendRequestAddConnection(form);
	      });
	      main_core.Dom.append(button, buttonWrapper);
	      main_core.Dom.append(buttonWrapper, form);
	      main_core.Dom.append(form, formBlock);
	      return main_core.Tag.render(_templateObject$4(), bodyHeader, formBlock);
	    }
	  }, {
	    key: "getContentActiveBody",
	    value: function getContentActiveBody() {
	      var _this2 = this;

	      var formObject = new ConnectionControls({
	        server: this.connection.addParams.server,
	        userName: this.connection.addParams.userName,
	        connectionName: this.connection.connectionName
	      });
	      var formBlock = formObject.getWrapper();
	      var form = formObject.getForm();
	      var button = formObject.getDisconnectButton();
	      var buttonWrapper = formObject.getButtonWrapper();
	      var bodyHeader = this.getContentActiveBodyHeader();
	      button.addEventListener('click', function (event) {
	        main_core.Dom.addClass(button, ['ui-btn-clock', 'ui-btn-disabled']);
	        event.preventDefault();

	        _this2.sendRequestRemoveConnection(_this2.connection.getId());
	      });
	      main_core.Dom.append(button, buttonWrapper);
	      main_core.Dom.append(buttonWrapper, form);
	      main_core.Dom.append(form, formBlock);
	      return main_core.Tag.render(_templateObject2$4(), bodyHeader, formBlock);
	    }
	  }, {
	    key: "sendRequestEditConnection",
	    value: function sendRequestEditConnection(form, options) {
	      BX.ajax.runAction('calendar.api.calendarajax.editConnection', {
	        data: {
	          form: new FormData(form),
	          connectionId: options.connectionId
	        }
	      }).then(function () {
	        BX.reload();
	      });
	    }
	  }, {
	    key: "sendRequestAddConnection",
	    value: function sendRequestAddConnection(form) {
	      var _this3 = this;

	      var fd = new FormData(form);
	      BX.ajax.runAction('calendar.api.calendarajax.addConnection', {
	        data: {
	          name: fd.get('name'),
	          server: fd.get('server'),
	          userName: fd.get('user_name'),
	          pass: fd.get('password')
	        }
	      }).then(function (response) {
	        BX.reload();
	      }, function (response) {
	        var button = form.querySelector('#connect-button');

	        _this3.showAlertPopup(response.errors[0], button);
	      });
	    }
	  }, {
	    key: "showAlertPopup",
	    value: function showAlertPopup(alert, button) {
	      var message = '';

	      if (alert.code === 'incorrect_parameters') {
	        message = main_core.Loc.getMessage('CAL_TEXT_ALERT_INCORRECT_PARAMETERS');
	      } else if (alert.code === 'tech_problem') {
	        message = main_core.Loc.getMessage('CAL_TEXT_ALERT_TECH_PROBLEM');
	      } else {
	        message = main_core.Loc.getMessage('CAL_TEXT_ALERT_DEFAULT');
	      }

	      var messageBox = new BX.UI.Dialogs.MessageBox({
	        message: message,
	        title: alert.message,
	        buttons: BX.UI.Dialogs.MessageBoxButtons.OK,
	        okCaption: main_core.Loc.getMessage('CAL_TEXT_BUTTON_RETURN_TO_SETTINGS'),
	        minWidth: 358,
	        mediumButtonSize: false,
	        popupOptions: {
	          zIndex: 3021,
	          height: 166,
	          width: 358,
	          className: 'calendar-alert-popup-connection'
	        },
	        onOk: function onOk() {
	          main_core.Dom.removeClass(button, ['ui-btn-clock', 'ui-btn-disabled']);
	          return true;
	        }
	      });
	      messageBox.show();
	    }
	  }]);
	  return CaldavInterfaceTemplate;
	}(InterfaceTemplate);

	var CaldavTemplate = /*#__PURE__*/function (_CaldavInterfaceTempl) {
	  babelHelpers.inherits(CaldavTemplate, _CaldavInterfaceTempl);

	  function CaldavTemplate(provider) {
	    var connection = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	    babelHelpers.classCallCheck(this, CaldavTemplate);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CaldavTemplate).call(this, {
	      title: main_core.Loc.getMessage("CALENDAR_TITLE_CALDAV"),
	      helpDeskCode: '5697365',
	      titleInfoHeader: main_core.Loc.getMessage('CAL_CONNECT_CALDAV_CALENDAR'),
	      descriptionInfoHeader: main_core.Loc.getMessage('CAL_CALDAV_CONNECT_DESCRIPTION'),
	      titleActiveHeader: main_core.Loc.getMessage('CAL_CALDAV_CALENDAR_IS_CONNECT'),
	      descriptionActiveHeader: main_core.Loc.getMessage('CAL_CALDAV_SELECTED_DESCRIPTION'),
	      sliderIconClass: 'calendar-sync-slider-header-icon-caldav',
	      iconPath: '/bitrix/images/calendar/sync/caldav.svg',
	      color: '#1eae43',
	      provider: provider,
	      connection: connection,
	      popupWithUpdateButton: true
	    }));
	  }

	  return CaldavTemplate;
	}(CaldavInterfaceTemplate);

	var ExchangeTemplate = /*#__PURE__*/function (_InterfaceTemplate) {
	  babelHelpers.inherits(ExchangeTemplate, _InterfaceTemplate);

	  function ExchangeTemplate(provider) {
	    var connection = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	    babelHelpers.classCallCheck(this, ExchangeTemplate);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ExchangeTemplate).call(this, {
	      title: main_core.Loc.getMessage("CALENDAR_TITLE_EXCHANGE"),
	      helpDeskCode: '11864622',
	      titleInfoHeader: main_core.Loc.getMessage('CAL_CONNECT_EXCHANGE_CALENDAR_TITLE'),
	      descriptionInfoHeader: main_core.Loc.getMessage('CAL_EXCHANGE_CONNECT_DESCRIPTION'),
	      titleActiveHeader: main_core.Loc.getMessage('CAL_SYNC_CONNECTED_EXCHANGE_TITLE'),
	      descriptionActiveHeader: main_core.Loc.getMessage('CAL_EXCHANGE_SELECTED_DESCRIPTION'),
	      sliderIconClass: 'calendar-sync-slider-header-icon-office',
	      iconPath: '/bitrix/images/calendar/sync/exchange.svg',
	      color: '#54d0df',
	      provider: provider,
	      connection: connection,
	      popupWithUpdateButton: true
	    }));
	  }

	  return ExchangeTemplate;
	}(InterfaceTemplate);

	function _templateObject5$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<li class=\"calendar-sync-slider-item\">\n\t\t\t\t\t<label class=\"ui-ctl ui-ctl-checkbox ui-ctl-xs\">\n\t\t\t\t\t\t<input type=\"checkbox\" class=\"ui-ctl-element\" value=\"", "\" onclick=\"", "\" ", ">\n\t\t\t\t\t\t<div class=\"ui-ctl-label-text\">", "</div>\n\t\t\t\t\t</label>\n\t\t\t\t</li>\n\t\t\t"]);

	  _templateObject5$3 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-slider-section calendar-sync-slider-section-col\">\n\t\t\t\t<div class=\"calendar-sync-slider-header\">\n\t\t\t\t\t<div class=\"calendar-sync-slider-subtitle\">", "</div>\n\t\t\t\t</div>\n\t\t\t\t<ul class=\"calendar-sync-slider-list\">\n\t\t\t\t\t", "\n\t\t\t\t</ul>\n\t\t\t</div>\n\t\t"]);

	  _templateObject4$3 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-slider-section\">\n\t\t\t\t<div class=\"calendar-sync-slider-header-icon calendar-sync-slider-header-icon-google\"></div>\n\t\t\t\t<div class=\"calendar-sync-slider-header\">\n\t\t\t\t\t<div class=\"calendar-sync-slider-title\">", "</div>\n\t\t\t\t\t<span class=\"calendar-sync-slider-account\">\n\t\t\t\t\t\t<span class=\"calendar-sync-slider-account-avatar\"></span>\n\t\t\t\t\t\t<span class=\"calendar-sync-slider-account-email\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</span>\n\t\t\t\t\t<div class=\"calendar-sync-slider-info\">\n\t\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">\n\t\t\t\t\t\t\t<a class=\"calendar-sync-slider-info-link\" href=\"javascript:void(0);\" onclick=\"", "\">\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</div>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t"]);

	  _templateObject3$3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t", "\n\t\t\t", "\n\t\t"]);

	  _templateObject2$5 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t", "\n\t\t"]);

	  _templateObject$5 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var GoogleTemplate = /*#__PURE__*/function (_InterfaceTemplate) {
	  babelHelpers.inherits(GoogleTemplate, _InterfaceTemplate);

	  function GoogleTemplate(provider) {
	    var _this;

	    var connection = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	    babelHelpers.classCallCheck(this, GoogleTemplate);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(GoogleTemplate).call(this, {
	      title: main_core.Loc.getMessage("CALENDAR_TITLE_GOOGLE"),
	      helpDeskCode: '6030429',
	      titleInfoHeader: main_core.Loc.getMessage('CAL_CONNECT_GOOGLE_CALENDAR'),
	      descriptionInfoHeader: main_core.Loc.getMessage('CAL_GOOGLE_CONNECT_DESCRIPTION'),
	      titleActiveHeader: main_core.Loc.getMessage('CAL_GOOGLE_CALENDAR_IS_CONNECT'),
	      descriptionActiveHeader: main_core.Loc.getMessage('CAL_GOOGLE_SELECTED_DESCRIPTION'),
	      sliderIconClass: 'calendar-sync-slider-header-icon-google',
	      iconPath: '/bitrix/images/calendar/sync/google.svg',
	      color: '#387ced',
	      provider: provider,
	      connection: connection,
	      popupWithUpdateButton: true
	    }));
	    _this.sectionStatusObject = {};
	    return _this;
	  }

	  babelHelpers.createClass(GoogleTemplate, [{
	    key: "createConnection",
	    value: function createConnection() {
	      BX.ajax.runAction('calendar.api.calendarajax.analytical', {
	        analyticsLabel: {
	          click_to_connection_button: 'Y',
	          connection_type: 'google'
	        }
	      });
	      BX.util.popup(this.provider.getSyncLink(), 500, 600);
	    }
	  }, {
	    key: "getContentInfoBody",
	    value: function getContentInfoBody() {
	      var _this2 = this;

	      var formObject = new ConnectionControls();
	      var button = formObject.getAddButton();
	      var buttonWrapper = formObject.getButtonWrapper();
	      var bodyHeader = this.getContentInfoBodyHeader();
	      var content = bodyHeader.querySelector('.calendar-sync-slider-header');

	      if (this.provider.hasSetSyncCaldavSettings()) {
	        button.addEventListener('click', function () {
	          _this2.createConnection();
	        });
	      } else {
	        button.addEventListener('click', function () {
	          _this2.showAlertPopup();
	        });
	      }

	      main_core.Dom.append(button, buttonWrapper);
	      main_core.Dom.append(buttonWrapper, content);
	      return main_core.Tag.render(_templateObject$5(), bodyHeader);
	    }
	  }, {
	    key: "getContentActiveBody",
	    value: function getContentActiveBody() {
	      return main_core.Tag.render(_templateObject2$5(), this.getContentActiveBodyHeader(), this.getContentActiveBodySectionsManager());
	    }
	  }, {
	    key: "getContentActiveBodyHeader",
	    value: function getContentActiveBodyHeader() {
	      var _this3 = this;

	      var formObject = new ConnectionControls();
	      var disconnectButton = formObject.getDisconnectButton();
	      disconnectButton.addEventListener('click', function (event) {
	        event.preventDefault();

	        _this3.sendRequestRemoveConnection(_this3.connection.getId());
	      });
	      return main_core.Tag.render(_templateObject3$3(), main_core.Loc.getMessage('CAL_GOOGLE_CALENDAR_IS_CONNECT'), BX.util.htmlspecialchars(this.connection.getConnectionName()), this.showHelp.bind(this), main_core.Loc.getMessage('CAL_TEXT_ABOUT_WORK_SYNC'), disconnectButton);
	    }
	  }, {
	    key: "getContentActiveBodySectionsManager",
	    value: function getContentActiveBodySectionsManager() {
	      return main_core.Tag.render(_templateObject4$3(), main_core.Loc.getMessage('CAL_AVAILABLE_CALENDAR'), this.getContentActiveBodySections(this.connection.getId()));
	    }
	  }, {
	    key: "getContentActiveBodySections",
	    value: function getContentActiveBodySections(connectionId) {
	      var _this4 = this;

	      var sectionList = [];
	      this.provider.getConnection().getSections().forEach(function (section) {
	        sectionList.push(main_core.Tag.render(_templateObject5$3(), BX.util.htmlspecialchars(section['ID']), _this4.onClickCheckSection.bind(_this4), section['ACTIVE'] === 'Y' ? 'checked' : '', BX.util.htmlspecialchars(section['NAME'])));
	      });
	      return sectionList;
	    }
	  }, {
	    key: "onClickCheckSection",
	    value: function onClickCheckSection(event) {
	      this.sectionStatusObject[event.target.value] = event.target.checked;
	      this.runUpdateInfo();
	    }
	  }, {
	    key: "showAlertPopup",
	    value: function showAlertPopup() {
	      var messageBox = new ui_dialogs_messagebox.MessageBox({
	        className: this.id,
	        message: main_core.Loc.getMessage('GOOGLE_IS_NOT_CALDAV_SETTINGS_WARNING_MESSAGE'),
	        width: 500,
	        offsetLeft: 60,
	        offsetTop: 5,
	        padding: 7,
	        onOk: function onOk() {
	          messageBox.close();
	        },
	        okCaption: 'OK',
	        buttons: BX.UI.Dialogs.MessageBoxButtons.OK,
	        popupOptions: {
	          zIndexAbsolute: 4020,
	          autoHide: true
	        }
	      });
	      messageBox.show();
	    }
	  }]);
	  return GoogleTemplate;
	}(InterfaceTemplate);

	function _templateObject3$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-slider-section calendar-sync-slider-section-col\">\n\t\t\t\t<div class=\"calendar-sync-slider-header calendar-sync-slider-header-divide\">\n\t\t\t\t\t<div class=\"calendar-sync-slider-subtitle\">", "</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"calendar-sync-slider-info\">\n\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">", ":</span>\n\t\t\t\t\t<ol class=\"calendar-sync-slider-info-list\">\n\t\t\t\t\t\t<li class=\"calendar-sync-slider-info-item\">\n\t\t\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">", "</span>\n\t\t\t\t\t\t</li>\n\t\t\t\t\t\t<li class=\"calendar-sync-slider-info-item\">\n\t\t\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">", "</span>\n\t\t\t\t\t\t</li>\n\t\t\t\t\t\t<li class=\"calendar-sync-slider-info-item\">\n\t\t\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">", "</span>\n\t\t\t\t\t\t</li>\n\t\t\t\t\t\t<li class=\"calendar-sync-slider-info-item\">\n\t\t\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">", "</span>\n\t\t\t\t\t\t</li>\n\t\t\t\t\t\t<li class=\"calendar-sync-slider-info-item\">\n\t\t\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">", "</span>\n\t\t\t\t\t\t</li>\n\t\t\t\t\t\t<li class=\"calendar-sync-slider-info-item\">\n\t\t\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">", "</span>\n\t\t\t\t\t\t</li>\n\t\t\t\t\t\t<li class=\"calendar-sync-slider-info-item\">\n\t\t\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">", "</span>\n\t\t\t\t\t\t</li>\n\t\t\t\t\t</ol>\n\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">", "</span>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"]);

	  _templateObject3$4 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t", "\n\t\t\t", "\n\t\t"]);

	  _templateObject2$6 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t", "\n\t\t\t", "\n\t\t"]);

	  _templateObject$6 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var MacTemplate = /*#__PURE__*/function (_InterfaceTemplate) {
	  babelHelpers.inherits(MacTemplate, _InterfaceTemplate);

	  function MacTemplate(provider) {
	    var connection = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	    babelHelpers.classCallCheck(this, MacTemplate);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(MacTemplate).call(this, {
	      title: main_core.Loc.getMessage("CALENDAR_TITLE_MAC"),
	      helpDeskCode: '5684075',
	      titleInfoHeader: main_core.Loc.getMessage('CAL_CONNECT_MAC_CALENDAR_TITLE'),
	      descriptionInfoHeader: main_core.Loc.getMessage('CAL_MAC_CONNECT_DESCRIPTION'),
	      titleActiveHeader: main_core.Loc.getMessage('CAL_MAC_CALENDAR_IS_CONNECT_TITLE'),
	      descriptionActiveHeader: main_core.Loc.getMessage('CAL_MAC_SELECTED_DESCRIPTION'),
	      sliderIconClass: 'calendar-sync-slider-header-icon-mac',
	      iconPath: '/bitrix/images/calendar/sync/mac.svg',
	      color: '#ff5752',
	      provider: provider,
	      connection: connection,
	      popupWithUpdateButton: false
	    }));
	  }

	  babelHelpers.createClass(MacTemplate, [{
	    key: "getPortalAddress",
	    value: function getPortalAddress() {
	      return this.portalAddress;
	    }
	  }, {
	    key: "getContentInfoBody",
	    value: function getContentInfoBody() {
	      return main_core.Tag.render(_templateObject$6(), this.getContentInfoBodyHeader(), this.getContentBodyConnect());
	    }
	  }, {
	    key: "getContentActiveBody",
	    value: function getContentActiveBody() {
	      return main_core.Tag.render(_templateObject2$6(), this.getContentActiveBodyHeader(), this.getContentBodyConnect());
	    }
	  }, {
	    key: "getContentBodyConnect",
	    value: function getContentBodyConnect() {
	      return main_core.Tag.render(_templateObject3$4(), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_HEADER'), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_DESCRIPTION'), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_FIRST'), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_SECOND'), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_THIRD'), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_FOURTH'), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_FIFTH').replace(/#PORTAL_ADDRESS#/gi, this.provider.getPortalAddress()), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_SIXTH'), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_SEVENTH'), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_CONCLUSION'));
	    }
	  }]);
	  return MacTemplate;
	}(InterfaceTemplate);

	var OutlookTemplate = /*#__PURE__*/function (_InterfaceTemplate) {
	  babelHelpers.inherits(OutlookTemplate, _InterfaceTemplate);

	  function OutlookTemplate(provider) {
	    var connection = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	    babelHelpers.classCallCheck(this, OutlookTemplate);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(OutlookTemplate).call(this, {
	      title: main_core.Loc.getMessage("CALENDAR_TITLE_MAC"),
	      helpDeskCode: '5684075',
	      titleInfoHeader: main_core.Loc.getMessage('CAL_CONNECT_MAC_CALENDAR_TITLE'),
	      descriptionInfoHeader: main_core.Loc.getMessage('CAL_MAC_CONNECT_DESCRIPTION'),
	      titleActiveHeader: main_core.Loc.getMessage('CAL_MAC_CALENDAR_IS_CONNECT_TITLE'),
	      descriptionActiveHeader: main_core.Loc.getMessage('CAL_MAC_SELECTED_DESCRIPTION'),
	      sliderIconClass: 'calendar-sync-slider-header-icon-mac',
	      iconPath: '/bitrix/images/calendar/sync/mac.svg',
	      color: '#ff5752',
	      provider: provider,
	      connection: connection,
	      popupWithUpdateButton: false
	    }));
	  }

	  return OutlookTemplate;
	}(InterfaceTemplate);

	var YandexTemplate = /*#__PURE__*/function (_CaldavInterfaceTempl) {
	  babelHelpers.inherits(YandexTemplate, _CaldavInterfaceTempl);

	  function YandexTemplate(provider) {
	    var connection = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	    babelHelpers.classCallCheck(this, YandexTemplate);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(YandexTemplate).call(this, {
	      title: main_core.Loc.getMessage("CALENDAR_TITLE_YANDEX"),
	      helpDeskCode: '10930170',
	      titleInfoHeader: main_core.Loc.getMessage('CAL_CONNECT_YANDEX_CALENDAR'),
	      descriptionInfoHeader: main_core.Loc.getMessage('CAL_YANDEX_CONNECT_DESCRIPTION'),
	      titleActiveHeader: main_core.Loc.getMessage('CAL_YANDEX_CALENDAR_IS_CONNECT'),
	      descriptionActiveHeader: main_core.Loc.getMessage('CAL_YANDEX_SELECTED_DESCRIPTION'),
	      sliderIconClass: 'calendar-sync-slider-header-icon-yandex',
	      iconPath: '/bitrix/images/calendar/sync/yandex.svg',
	      color: '#f9c500',
	      provider: provider,
	      connection: connection,
	      popupWithUpdateButton: true
	    }));
	  }

	  return YandexTemplate;
	}(CaldavInterfaceTemplate);

	function _templateObject3$5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-slider-section\">\n\t\t\t\t<div class=\"calendar-sync-slider-header-icon ", "\"></div>\n\t\t\t\t<div class=\"calendar-sync-slider-header\">\n\t\t\t\t<div class=\"calendar-sync-slider-title\">", "</div>\n\t\t\t\t<div class=\"calendar-sync-slider-info\">\n\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">", "</span>\n\t\t\t\t\t<span class=\"calendar-sync-slider-info-time\">", "</span>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"calendar-sync-slider-desc\">", "</div>\n\t\t\t\t\t<a class=\"calendar-sync-slider-link\" href=\"javascript:void(0);\" onclick=\"", "\">", "</a>\n\t\t\t\t</div>\n\t\t\t</div>"]);

	  _templateObject3$5 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$7() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t", "\n\t\t\t<div class=\"calendar-sync-slider-section calendar-sync-slider-section-banner\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject2$7 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$7() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t", "\n\t\t\t<div class=\"calendar-sync-slider-section calendar-sync-slider-section-banner\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject$7 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var MobileInterfaceTemplate = /*#__PURE__*/function (_InterfaceTemplate) {
	  babelHelpers.inherits(MobileInterfaceTemplate, _InterfaceTemplate);

	  function MobileInterfaceTemplate(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, MobileInterfaceTemplate);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(MobileInterfaceTemplate).call(this, options));
	    _this.banner = new MobileSyncBanner({
	      type: _this.provider.getType(),
	      helpDeskCode: options.helpDeskCode
	    });

	    if (_this.status) {
	      _this.syncDate = main_core.Type.isDate(_this.data.syncDate) ? _this.data.syncDate : calendar_util.Util.parseDate(_this.data.syncDate);
	    }

	    return _this;
	  }

	  babelHelpers.createClass(MobileInterfaceTemplate, [{
	    key: "getContentInfoBody",
	    value: function getContentInfoBody() {
	      return main_core.Tag.render(_templateObject$7(), this.getContentInfoBodyHeader(), this.getContentBodyConnect());
	    }
	  }, {
	    key: "getContentActiveBody",
	    value: function getContentActiveBody() {
	      return main_core.Tag.render(_templateObject2$7(), this.getContentActiveBodyHeader(), this.getContentBodyConnect());
	    }
	  }, {
	    key: "getContentActiveBodyHeader",
	    value: function getContentActiveBodyHeader() {
	      return main_core.Tag.render(_templateObject3$5(), this.sliderIconClass, this.titleActiveHeader, main_core.Loc.getMessage('CAL_SYNC_LAST_SYNC_DATE'), calendar_util.Util.formatDateUsable(this.connection.getSyncTimestamp()) + ' ' + BX.date.format(calendar_util.Util.getTimeFormatShort(), this.connection.getSyncTimestamp()), main_core.Loc.getMessage('CAL_SYNC_DISABLE'), this.showHelp.bind(this), main_core.Loc.getMessage('CAL_TEXT_ABOUT_WORK_SYNC'));
	    }
	  }, {
	    key: "getContentBodyConnect",
	    value: function getContentBodyConnect() {
	      this.banner.initQrCode().then(this.banner.drawQRCode.bind(this.banner));
	      return this.banner.getContainer();
	    }
	  }]);
	  return MobileInterfaceTemplate;
	}(InterfaceTemplate);

	var AndroidTemplate = /*#__PURE__*/function (_MobileInterfaceTempl) {
	  babelHelpers.inherits(AndroidTemplate, _MobileInterfaceTempl);

	  function AndroidTemplate(provider) {
	    var connection = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	    babelHelpers.classCallCheck(this, AndroidTemplate);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AndroidTemplate).call(this, {
	      title: main_core.Loc.getMessage("CALENDAR_TITLE_ANDROID"),
	      helpDeskCode: '5686179',
	      titleInfoHeader: main_core.Loc.getMessage('CAL_CONNECT_ANDROID_CALENDAR_TITLE'),
	      descriptionInfoHeader: main_core.Loc.getMessage('CAL_ANDROID_CONNECT_DESCRIPTION'),
	      titleActiveHeader: main_core.Loc.getMessage('CAL_SYNC_CONNECTED_ANDROID_TITLE'),
	      descriptionActiveHeader: main_core.Loc.getMessage('CAL_ANDROID_SELECTED_DESCRIPTION'),
	      sliderIconClass: 'calendar-sync-slider-header-icon-android',
	      iconPath: '/bitrix/images/calendar/sync/android.svg',
	      color: '#9ece03',
	      provider: provider,
	      connection: connection,
	      popupWithUpdateButton: false
	    }));
	  }

	  return AndroidTemplate;
	}(MobileInterfaceTemplate);

	var IphoneTemplate = /*#__PURE__*/function (_MobileInterfaceTempl) {
	  babelHelpers.inherits(IphoneTemplate, _MobileInterfaceTempl);

	  function IphoneTemplate(provider) {
	    var connection = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	    babelHelpers.classCallCheck(this, IphoneTemplate);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(IphoneTemplate).call(this, {
	      title: main_core.Loc.getMessage("CALENDAR_TITLE_IPHONE"),
	      helpDeskCode: '5686207',
	      titleInfoHeader: main_core.Loc.getMessage('CAL_CONNECT_IPHONE_CALENDAR_TITLE'),
	      descriptionInfoHeader: main_core.Loc.getMessage('CAL_IPHONE_CONNECT_DESCRIPTION'),
	      titleActiveHeader: main_core.Loc.getMessage('CAL_SYNC_CONNECTED_IPHONE_TITLE'),
	      descriptionActiveHeader: main_core.Loc.getMessage('CAL_IPHONE_SELECTED_DESCRIPTION'),
	      sliderIconClass: 'calendar-sync-slider-header-icon-iphone',
	      iconPath: '/bitrix/images/calendar/sync/iphone.svg',
	      color: '#2fc6f6',
	      provider: provider,
	      connection: connection,
	      popupWithUpdateButton: false
	    }));
	  }

	  return IphoneTemplate;
	}(MobileInterfaceTemplate);

	function _templateObject7$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-sync-title\">", "</div>\n\t\t"]);

	  _templateObject7$1 = function _templateObject7() {
	    return data;
	  };

	  return data;
	}

	function _templateObject6$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-sync-title\">", "</div>\n\t\t\t"]);

	  _templateObject6$2 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div id=\"calendar-sync-web\" class=\"calendar-sync-web\"></div>\n\t\t\t"]);

	  _templateObject5$4 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div id=\"calendar-sync-mobile\" class=\"calendar-sync-mobile\"></div>\n\t\t"]);

	  _templateObject4$4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span class=\"calendar-sync-header-text\">", "</span>\n\t\t\t"]);

	  _templateObject3$6 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$8() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-header\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject2$8 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$8() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-wrap\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject$8 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var SyncPanel = /*#__PURE__*/function () {
	  function SyncPanel(options) {
	    babelHelpers.classCallCheck(this, SyncPanel);
	    babelHelpers.defineProperty(this, "MAIN_SYNC_SLIDER_NAME", 'calendar:sync-slider');
	    babelHelpers.defineProperty(this, "SLIDER_WIDTH", 684);
	    babelHelpers.defineProperty(this, "LOADER_NAME", "calendar:loader");
	    babelHelpers.defineProperty(this, "cache", new main_core.Cache.MemoryCache());
	    this.status = options.status;
	    this.connectionsProviders = options.connectionsProviders;
	    this.userId = options.userId;
	    this.statusBlockEnterTimeout = null;
	    this.statusBlockLeaveTimeout = null;
	  }

	  babelHelpers.createClass(SyncPanel, [{
	    key: "openSlider",
	    value: function openSlider() {
	      var _this = this;

	      BX.SidePanel.Instance.open(this.MAIN_SYNC_SLIDER_NAME, {
	        contentCallback: function contentCallback(slider) {
	          return new Promise(function (resolve, reject) {
	            resolve(_this.getContent());
	          });
	        },
	        allowChangeHistory: false,
	        events: {
	          onLoad: function onLoad() {
	            _this.setGridContent();
	          } // onMessage: (event) => {
	          // 	if (event.getEventId() === 'refreshSliderGrid')
	          // 	{
	          // 		this.refreshData();
	          // 	}
	          // },
	          // onClose: (event) => {
	          // 	BX.SidePanel.Instance.postMessageTop(window.top.BX.SidePanel.Instance.getTopSlider(), "refreshCalendarGrid", {});
	          // },

	        },
	        cacheable: false,
	        width: this.SLIDER_WIDTH,
	        loader: this.LOADER_NAME
	      });
	    }
	  }, {
	    key: "getContent",
	    value: function getContent() {
	      return main_core.Tag.render(_templateObject$8(), this.getHeader(), this.getMobileHeader(), this.getMobileContentWrapper(), this.getWebHeader(), this.getWebContentWrapper());
	    }
	  }, {
	    key: "getHeader",
	    value: function getHeader() {
	      return main_core.Tag.render(_templateObject2$8(), this.getMainHeader(), this.getStatusBlockContent(this.getConnections()));
	    }
	  }, {
	    key: "getMainHeader",
	    value: function getMainHeader() {
	      return this.cache.remember('calendar-syncPanel-mainHeader', function () {
	        return main_core.Tag.render(_templateObject3$6(), main_core.Loc.getMessage('SYNC_CALENDAR_HEADER'));
	      });
	    }
	  }, {
	    key: "getMobileContentWrapper",
	    value: function getMobileContentWrapper() {
	      return this.cache.remember('calendar-syncPanel-mobileContentWrapper', function () {
	        return main_core.Tag.render(_templateObject4$4());
	      });
	    }
	  }, {
	    key: "getWebContentWrapper",
	    value: function getWebContentWrapper() {
	      return this.cache.remember('calendar-syncPanel-webContentWrapper', function () {
	        return main_core.Tag.render(_templateObject5$4());
	      });
	    }
	  }, {
	    key: "getMobileHeader",
	    value: function getMobileHeader() {
	      return this.cache.remember('calendar-syncPanel-mobileHeader', function () {
	        return main_core.Tag.render(_templateObject6$2(), main_core.Loc.getMessage('SYNC_MOBILE_HEADER'));
	      });
	    }
	  }, {
	    key: "getWebHeader",
	    value: function getWebHeader() {
	      return this.cache.remember('calendar-syncPanel-webHeader', function () {
	        return main_core.Tag.render(_templateObject7$1(), main_core.Loc.getMessage('SYNC_WEB_HEADER'));
	      });
	    }
	  }, {
	    key: "getStatusBlockContent",
	    value: function getStatusBlockContent(connections) {
	      this.statusBlock = StatusBlock.createInstance({
	        status: this.status,
	        connections: connections,
	        withStatusLabel: true,
	        popupWithUpdateButton: true,
	        popupId: 'calendar-syncPanel-status'
	      });
	      this.statusBlockContent = this.statusBlock.getContent();
	      return this.statusBlockContent;
	    }
	  }, {
	    key: "getConnections",
	    value: function getConnections() {
	      var connections = [];
	      var items = Object.values(this.connectionsProviders);
	      items.forEach(function (item) {
	        var itemConnections = item.getConnections();

	        if (itemConnections.length > 0) {
	          itemConnections.forEach(function (connection) {
	            if (calendar_sync_manager.ConnectionItem.isConnectionItem(connection) && connection.getConnectStatus() === true) {
	              connections.push(connection);
	            }
	          });
	        }
	      });
	      return connections;
	    }
	  }, {
	    key: "setGridContent",
	    value: function setGridContent() {
	      var items = Object.values(this.connectionsProviders);
	      var mobileItems = items.filter(function (item) {
	        return item.getViewClassification() === 'mobile';
	      });
	      var webItems = items.filter(function (item) {
	        return item.getViewClassification() === 'web';
	      });
	      this.showWebGridContent(webItems);
	      this.showMobileGridContent(mobileItems);
	    }
	  }, {
	    key: "showWebGridContent",
	    value: function showWebGridContent(items) {
	      var wrapper = this.getWebContentWrapper();
	      main_core.Dom.clean(wrapper);
	      var grid = new BX.TileGrid.Grid({
	        id: 'calendar_sync',
	        items: items,
	        container: wrapper,
	        sizeRatio: "55%",
	        itemMinWidth: 180,
	        tileMargin: 7,
	        itemType: 'BX.Calendar.Sync.Interface.GridUnit',
	        userId: this.userId
	      });
	      grid.draw();
	    }
	  }, {
	    key: "showMobileGridContent",
	    value: function showMobileGridContent(items) {
	      var wrapper = this.getMobileContentWrapper();
	      main_core.Dom.clean(wrapper);
	      var grid = new BX.TileGrid.Grid({
	        id: 'calendar_sync',
	        items: items,
	        container: wrapper,
	        sizeRatio: "55%",
	        itemMinWidth: 180,
	        tileMargin: 7,
	        itemType: 'BX.Calendar.Sync.Interface.GridUnit'
	      });
	      grid.draw();
	    }
	  }, {
	    key: "refresh",
	    value: function refresh(status, connectionsProviders) {
	      this.status = status;
	      this.connectionsProviders = connectionsProviders;
	      this.blockStatusContent = this.statusBlock.refresh(status, this.getConnections()).getContent();
	      main_core.Dom.replace(document.querySelector('#calendar-sync-status-block'), this.blockStatusContent);
	      this.setGridContent();
	    }
	  }]);
	  return SyncPanel;
	}();

	function _templateObject4$5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-sync-item-status\"></div>\n\t\t\t"]);

	  _templateObject4$5 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$7() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-item-image\">\n\t\t\t\t<div class=\"calendar-sync-item-image-item\" style=\"background-image: ", "\"></div>\n\t\t\t</div>"]);

	  _templateObject3$7 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$9() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-sync-item-title\">", "</div>"]);

	  _templateObject2$9 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$9() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-sync-item ", "\" style=\"", "\">\n\t\t\t<div class=\"calendar-item-content\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t</div>"]);

	  _templateObject$9 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var GridUnit = /*#__PURE__*/function (_BX$TileGrid$Item) {
	  babelHelpers.inherits(GridUnit, _BX$TileGrid$Item);

	  function GridUnit(item) {
	    var _this;

	    babelHelpers.classCallCheck(this, GridUnit);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(GridUnit).call(this, {
	      id: item.type
	    }));
	    _this.item = item;
	    return _this;
	  }

	  babelHelpers.createClass(GridUnit, [{
	    key: "getContent",
	    value: function getContent() {
	      this.gridUnit = main_core.Tag.render(_templateObject$9(), this.getAdditionalContentClass(), this.getContentStyles(), this.getImage(), this.getTitle(), this.isActive() ? this.getStatus() : '');
	      this.gridUnit.addEventListener('click', this.onClick.bind(this));
	      return this.gridUnit;
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      if (!this.layout.title) {
	        this.layout.title = main_core.Tag.render(_templateObject2$9(), BX.util.htmlspecialchars(this.item.getGridTitle()));
	      }

	      return this.layout.title;
	    }
	  }, {
	    key: "getImage",
	    value: function getImage() {
	      return main_core.Tag.render(_templateObject3$7(), 'url(' + this.item.getGridIcon() + ')');
	    }
	  }, {
	    key: "getStatus",
	    value: function getStatus() {
	      if (this.isActive()) {
	        return main_core.Tag.render(_templateObject4$5());
	      }

	      return '';
	    }
	  }, {
	    key: "isActive",
	    value: function isActive() {
	      return this.item.getConnectStatus();
	    }
	  }, {
	    key: "getAdditionalContentClass",
	    value: function getAdditionalContentClass() {
	      if (this.isActive()) {
	        if (this.item.getSyncStatus()) {
	          return 'calendar-sync-item-selected';
	        } else {
	          return 'calendar-sync-item-failed';
	        }
	      } else {
	        return '';
	      }
	    }
	  }, {
	    key: "getContentStyles",
	    value: function getContentStyles() {
	      if (this.isActive()) {
	        return 'background-color:' + this.item.getGridColor() + ';';
	      } else {
	        return '';
	      }
	    }
	  }, {
	    key: "onClick",
	    value: function onClick() {
	      BX.ajax.runAction('calendar.api.calendarajax.analytical', {
	        analyticsLabel: {
	          open_connection_slider: 'Y',
	          sync_connection_type: this.item.getType(),
	          sync_connection_status: this.item.getSyncStatus() ? 'Y' : 'N'
	        }
	      });

	      if (this.item.hasMenu()) {
	        this.item.showMenu(this.gridUnit);
	      } else if (this.item.getConnectStatus()) {
	        this.item.openActiveConnectionSlider(this.item.getConnection());
	      } else {
	        this.item.openInfoConnectionSlider();
	      }
	    }
	  }]);
	  return GridUnit;
	}(BX.TileGrid.Item);

	function _templateObject2$a() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-ical-popup-link-block\">\n\t\t\t\t\t<a class=\"ui-link ui-link-primary \" target=\"_blank\" href=\"", "\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</a>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject2$a = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$a() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-ical-popup-wrapper\">\n\t\t\t\t<h3>", "</h3>\n\t\t\t\t<div class=\"calendar-ical-popup-label-text\"><span>", "</span></div>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject$a = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var IcalSyncPopup = /*#__PURE__*/function () {
	  function IcalSyncPopup(options) {
	    babelHelpers.classCallCheck(this, IcalSyncPopup);
	    babelHelpers.defineProperty(this, "LINK_LENGTH", 112);
	    this.link = this.getIcalLink(options);
	  }

	  babelHelpers.createClass(IcalSyncPopup, [{
	    key: "show",
	    value: function show() {
	      this.createPopup().show();
	      this.startSync();
	    }
	  }, {
	    key: "startSync",
	    value: function startSync() {
	      var _this = this;

	      BX.ajax.get(this.link + '&check=Y', "", function (result) {
	        setTimeout(function () {
	          if (!result || result.length <= 0 || result.toUpperCase().indexOf('BEGIN:VCALENDAR') === -1) {
	            _this.showPopupWithSyncDataError();
	          }
	        }, 300);
	      });
	    }
	  }, {
	    key: "getContent",
	    value: function getContent() {
	      return main_core.Tag.render(_templateObject$a(), main_core.Loc.getMessage('EC_JS_EXPORT_TILE'), main_core.Loc.getMessage('EC_EXP_TEXT'), this.getLinkBlock());
	    }
	  }, {
	    key: "createPopup",
	    value: function createPopup() {
	      var _this2 = this;

	      return this.popup = new main_popup.Popup({
	        width: 400,
	        zIndexOptions: 4000,
	        autoHide: false,
	        closeByEsc: true,
	        draggable: true,
	        // titleBar: Loc.getMessage('EC_JS_EXPORT_TILE'),
	        closeIcon: {
	          right: "12px",
	          top: "10px"
	        },
	        className: "bxc-popup-window",
	        content: this.getContent(),
	        buttons: [new BX.UI.Button({
	          text: main_core.Loc.getMessage('EC_JS_ICAL_COPY_ICAL_SYNC_LINK'),
	          color: BX.UI.Button.Color.PRIMARY,
	          onclick: function onclick() {
	            _this2.copyLink(event);
	          }
	        }), new BX.UI.Button({
	          text: main_core.Loc.getMessage('EC_SEC_SLIDER_CLOSE'),
	          color: BX.UI.Button.Color.LINK,
	          onclick: function onclick() {
	            _this2.popup.close();
	          }
	        })]
	      });
	    }
	  }, {
	    key: "getIcalLink",
	    value: function getIcalLink(options) {
	      return options.calendarPath + (options.calendarPath.indexOf('?') >= 0 ? '&' : '?') + 'action=export' + options.sectionLink;
	    }
	  }, {
	    key: "getLinkBlock",
	    value: function getLinkBlock() {
	      return main_core.Tag.render(_templateObject2$a(), BX.util.htmlspecialchars(this.link), BX.util.htmlspecialchars(this.getShortenLink(this.link)));
	    }
	  }, {
	    key: "showPopupWithSyncDataError",
	    value: function showPopupWithSyncDataError() {
	      BX.UI.Dialogs.MessageBox.alert(main_core.Loc.getMessage('EC_EDEV_EXP_WARN'));
	    }
	  }, {
	    key: "copyLink",
	    value: function copyLink(event) {
	      window.BX.clipboard.copy(this.link);
	      event.preventDefault();
	      event.stopPropagation();
	    }
	  }, {
	    key: "getShortenLink",
	    value: function getShortenLink(link) {
	      return link.length < this.LINK_LENGTH ? link : link.substr(0, 105) + '...' + link.slice(-7);
	    }
	  }], [{
	    key: "createInstance",
	    value: function createInstance(options) {
	      return new this(options);
	    }
	  }, {
	    key: "checkPathes",
	    value: function checkPathes(options) {
	      return !!options.sectionLink || !!options.calendarPath;
	    }
	  }, {
	    key: "showPopupWithPathesError",
	    value: function showPopupWithPathesError() {
	      BX.UI.Dialogs.MessageBox.alert(main_core.Loc.getMessage('EC_JS_ICAL_ERROR_WITH_PATHES'));
	    }
	  }]);
	  return IcalSyncPopup;
	}();

	exports.SyncPanelItem = SyncPanelItem;
	exports.ConnectionControls = ConnectionControls;
	exports.MobileSyncBanner = MobileSyncBanner;
	exports.SyncPanel = SyncPanel;
	exports.GridUnit = GridUnit;
	exports.YandexTemplate = YandexTemplate;
	exports.CaldavTemplate = CaldavTemplate;
	exports.MacTemplate = MacTemplate;
	exports.ExchangeTemplate = ExchangeTemplate;
	exports.GoogleTemplate = GoogleTemplate;
	exports.OutlookTemplate = OutlookTemplate;
	exports.IphoneTemplate = IphoneTemplate;
	exports.AndroidTemplate = AndroidTemplate;
	exports.IcalSyncPopup = IcalSyncPopup;

}((this.BX.Calendar.Sync.Interface = this.BX.Calendar.Sync.Interface || {}),BX.Event,BX.UI.Dialogs,BX.Calendar,BX.Calendar.Sync.Manager,BX,BX,BX.Main,BX));
//# sourceMappingURL=syncinterface.bundle.js.map
