this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
this.BX.Calendar.Sync = this.BX.Calendar.Sync || {};
(function (exports,ui_dialogs_messagebox,calendar_util,main_core_events,ui_tilegrid,ui_forms,main_popup,main_core) {
	'use strict';

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span class=\"calendar-sync-popup-footer-status\">", "</span>\n\t\t"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button class=\"calendar-sync-popup-footer-btn\">", "</button>\n\t\t"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-popup-footer-wrap\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-sync-popup-item\">\n\t\t\t\t\t<span class=\"calendar-sync-popup-item-text ", "\">", "</span>\n\t\t\t\t\t<div class=\"calendar-sync-popup-item-detail\">\n\t\t\t\t\t\t<span class=\"calendar-sync-popup-item-time\">", "</span>\n\t\t\t\t\t\t<span class=\"calendar-sync-popup-item-status ", "\"></span>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-popup-list\"></div>\n\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var SyncStatusPopup = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(SyncStatusPopup, _EventEmitter);

	  function SyncStatusPopup(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, SyncStatusPopup);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SyncStatusPopup).call(this));

	    _this.setEventNamespace('BX.Calendar.Sync.Interface.SyncStatusPopup');

	    _this.connections = options.connections;
	    _this.withUpdateButton = options.withUpdateButton;
	    _this.node = options.node;
	    _this.id = options.id;

	    _this.init();

	    return _this;
	  }

	  babelHelpers.createClass(SyncStatusPopup, [{
	    key: "init",
	    value: function init() {
	      this.setPopupContent();
	    }
	  }, {
	    key: "createPopup",
	    value: function createPopup() {
	      this.popup = new main_popup.Popup({
	        className: this.id,
	        bindElement: this.node,
	        content: this.container,
	        angle: true,
	        width: 360,
	        offsetLeft: 60,
	        offsetTop: 5,
	        padding: 7,
	        darkMode: true,
	        autoHide: true,
	        zIndexAbsolute: 3010
	      });
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      this.createPopup();
	      this.popup.show();
	    }
	  }, {
	    key: "setPopupContent",
	    value: function setPopupContent() {
	      var _this2 = this;

	      this.container = main_core.Tag.render(_templateObject());
	      this.connections.forEach(function (connection) {
	        if (connection.getConnectStatus() !== true) {
	          return;
	        }

	        var options = {};
	        options.syncTime = _this2.getTime(connection.getSyncTimestamp());
	        options.classStatus = connection.getSyncStatus() ? 'calendar-sync-popup-item-status-success' : 'calendar-sync-popup-item-status-fail';
	        options.classLable = 'calendar-sync-popup-item-text-' + connection.getClassLable();
	        options.title = connection.getConnectionName();

	        var block = _this2.getSyncElement(options);

	        _this2.container.append(block);
	      });

	      if (this.withUpdateButton) {
	        this.container.append(this.getContentRefreshBlock());

	        if (SyncStatusPopup.IS_RUN_REFRESH) {
	          this.showRefreshStatus();
	        }
	      }

	      return this.container;
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      this.popup.destroy();
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      return this.container;
	    }
	  }, {
	    key: "getPopup",
	    value: function getPopup() {
	      return this.popup;
	    }
	  }, {
	    key: "getTime",
	    value: function getTime(time) {
	      var format = [["tommorow", "tommorow, H:i:s"], ["s", main_core.Loc.getMessage('CAL_JUST')], ["i", "iago"], ["H", "Hago"], ["d", "dago"], ["m100", "mago"], ["m", "mago"], // ["m5", Loc.getMessage('CAL_JUST')],
	      ["-", ""]];
	      return BX.date.format(format, time);
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle(key) {
	      var title;

	      switch (key) {
	        case 'google':
	          title = main_core.Loc.getMessage('CALENDAR_TITLE_GOOGLE');
	          break;

	        case 'mac':
	          title = main_core.Loc.getMessage('CALENDAR_TITLE_MAC');
	          break;

	        case 'iphone':
	          title = main_core.Loc.getMessage('CALENDAR_TITLE_IPHONE');
	          break;

	        case 'android':
	          title = main_core.Loc.getMessage('CALENDAR_TITLE_ANDROID');
	          break;

	        case 'outlook':
	          title = main_core.Loc.getMessage('CALENDAR_TITLE_OUTLOOK');
	          break;

	        case 'office365':
	          title = main_core.Loc.getMessage('CALENDAR_TITLE_OFFICE365');
	          break;

	        case 'exchange':
	          title = main_core.Loc.getMessage('CALENDAR_TITLE_EXCHANGE');
	          break;
	      }

	      return title;
	    }
	  }, {
	    key: "getSyncElement",
	    value: function getSyncElement(options) {
	      return main_core.Tag.render(_templateObject2(), options.classLable, BX.util.htmlspecialchars(options.title), options.syncTime, options.classStatus);
	    }
	  }, {
	    key: "refresh",
	    value: function refresh(connections) {
	      this.connections = connections;
	      this.popup.setContent(this.setPopupContent());
	      this.setRefreshStatusBlock();
	    }
	  }, {
	    key: "setRefreshStatusBlock",
	    value: function setRefreshStatusBlock() {
	      var _this3 = this;

	      setTimeout(function () {
	        _this3.removeRefreshStatusBlock();

	        _this3.enableRefreshButton();

	        SyncStatusPopup.IS_RUN_REFRESH = false;
	      }, 300000);
	    }
	  }, {
	    key: "removeRefreshStatusBlock",
	    value: function removeRefreshStatusBlock() {
	      this.refreshStatusBlock.remove();
	    }
	  }, {
	    key: "enableRefreshButton",
	    value: function enableRefreshButton() {
	      this.refreshButton.className = 'calendar-sync-popup-footer-btn';
	    }
	  }, {
	    key: "disableRefreshButton",
	    value: function disableRefreshButton() {
	      this.refreshButton.className = 'calendar-sync-popup-footer-btn calendar-sync-popup-footer-btn-disabled';
	    }
	  }, {
	    key: "getContentRefreshBlock",
	    value: function getContentRefreshBlock() {
	      this.footerWrapper = main_core.Tag.render(_templateObject3(), this.getContentRefreshButton());
	      return this.footerWrapper;
	    }
	  }, {
	    key: "getContentRefreshButton",
	    value: function getContentRefreshButton() {
	      var _this4 = this;

	      this.refreshButton = main_core.Tag.render(_templateObject4(), main_core.Loc.getMessage('CAL_REFRESH'));
	      this.refreshButton.addEventListener('click', function () {
	        main_core.Dom.addClass(_this4.refreshButton, 'calendar-sync-popup-footer-btn-load');
	        SyncStatusPopup.IS_RUN_REFRESH = true;
	        _this4.refreshButton.innerText = main_core.Loc.getMessage('CAL_REFRESHING');

	        _this4.runRefresh();
	      });
	      return this.refreshButton;
	    }
	  }, {
	    key: "showRefreshStatus",
	    value: function showRefreshStatus() {
	      this.disableRefreshButton();
	      this.footerWrapper.prepend(this.getRefreshStatus());
	    }
	  }, {
	    key: "getRefreshStatus",
	    value: function getRefreshStatus() {
	      this.refreshStatusBlock = main_core.Tag.render(_templateObject5(), main_core.Loc.getMessage('CAL_REFRESH_JUST'));
	      return this.refreshStatusBlock;
	    }
	  }, {
	    key: "runRefresh",
	    value: function runRefresh() {
	      this.emit('onRefresh', {});
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }], [{
	    key: "createInstance",
	    value: function createInstance(options) {
	      return new this(options);
	    }
	  }]);
	  return SyncStatusPopup;
	}(main_core_events.EventEmitter);

	babelHelpers.defineProperty(SyncStatusPopup, "IS_RUN_REFRESH", false);

	function _templateObject5$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-status-block\" id=\"calendar-sync-status-block\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject5$1 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-status-subtitle\">\n\t\t\t\t<span data-hint=\"\"></span>\n\t\t\t\t<span class=\"calendar-sync-status-text\">", ":</span>\n\t\t\t</div>\n\t\t"]);

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

	function _templateObject2$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div id=\"status-info-block\" class=\"ui-alert ui-alert-danger calendar-sync-status-info\">\n\t\t\t\t\t<span class=\"ui-alert-message\">", "</span>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject2$1 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div id=\"status-info-block\" class=\"ui-alert ui-alert-success calendar-sync-status-info\">\n\t\t\t\t\t<span class=\"ui-alert-message\">", "</span>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var StatusBlock = /*#__PURE__*/function () {
	  function StatusBlock(options) {
	    babelHelpers.classCallCheck(this, StatusBlock);
	    this.status = options.status;
	    this.connections = options.connections;
	    this.withStatus = options.withStatus;
	    this.popupWithUpdateButton = options.popupWithUpdateButton;
	    this.popupId = options.id;
	  }

	  babelHelpers.createClass(StatusBlock, [{
	    key: "getContentStatusBlock",
	    value: function getContentStatusBlock() {
	      var _this = this;

	      var statusInfoBlock;

	      if (this.status === 'success') {
	        statusInfoBlock = main_core.Tag.render(_templateObject$1(), main_core.Loc.getMessage('SYNC_STATUS_SUCCESS'));
	      } else if (this.status === 'failed') {
	        statusInfoBlock = main_core.Tag.render(_templateObject2$1(), main_core.Loc.getMessage('SYNC_STATUS_ALERT'));
	      } else {
	        statusInfoBlock = main_core.Tag.render(_templateObject3$1(), main_core.Loc.getMessage('SYNC_STATUS_NOT_CONNECTED'));
	      }

	      statusInfoBlock.addEventListener('mouseenter', function (event) {
	        _this.handlerMouseEnter(statusInfoBlock);
	      });
	      statusInfoBlock.addEventListener('mouseleave', function (event) {
	        _this.handlerMouseLeave();
	      });
	      var statusTextLabel = main_core.Tag.render(_templateObject4$1(), main_core.Loc.getMessage('LABEL_STATUS_INFO'));
	      return main_core.Tag.render(_templateObject5$1(), this.withStatus ? statusTextLabel : '', statusInfoBlock);
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
	      var _this4 = this;

	      if (this.status !== 'not_connected') {
	        this.popup = SyncStatusPopup.createInstance({
	          connections: this.connections,
	          withUpdateButton: this.popupWithUpdateButton,
	          node: node,
	          id: this.popupId
	        });
	        this.popup.show();
	        this.popup.getPopup().getPopupContainer().addEventListener('mouseenter', function (e) {
	          clearTimeout(_this4.statusBlockEnterTimeout);
	          clearTimeout(_this4.statusBlockLeaveTimeout);
	        });
	        this.popup.getPopup().getPopupContainer().addEventListener('mouseleave', function () {
	          _this4.hidePopup();
	        });
	      }
	    }
	  }, {
	    key: "hidePopup",
	    value: function hidePopup() {
	      if (this.popup) {
	        this.popup.hide();
	      }
	    }
	  }], [{
	    key: "createInstance",
	    value: function createInstance(options) {
	      return new this(options);
	    }
	  }]);
	  return StatusBlock;
	}();

	var ConnectionItem = /*#__PURE__*/function () {
	  function ConnectionItem(options) {
	    babelHelpers.classCallCheck(this, ConnectionItem);
	    this.syncTimestamp = options.syncTimestamp;
	    this.connectionName = options.connectionName;
	    this.status = options.status;
	    this.connected = options.connected;
	    this.addParams = options.addParams;
	    this.type = options.type;
	    this.id = options.type;
	  }

	  babelHelpers.createClass(ConnectionItem, [{
	    key: "getSyncTimestamp",
	    value: function getSyncTimestamp() {
	      return this.syncTimestamp;
	    }
	  }, {
	    key: "getConnectionName",
	    value: function getConnectionName() {
	      return this.connectionName;
	    }
	  }, {
	    key: "getSyncStatus",
	    value: function getSyncStatus() {
	      return this.status;
	    }
	  }, {
	    key: "getConnectStatus",
	    value: function getConnectStatus() {
	      return this.connected;
	    }
	  }, {
	    key: "getStatus",
	    value: function getStatus() {
	      if (this.connected) {
	        return this.status ? "success" : "failed";
	      } else {
	        return 'not_connected';
	      }
	    }
	  }, {
	    key: "getClassLable",
	    value: function getClassLable() {
	      return this.type;
	    }
	  }, {
	    key: "getSections",
	    value: function getSections() {
	      return this.addParams.sections;
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.addParams.id;
	    }
	  }, {
	    key: "getType",
	    value: function getType() {
	      return this.type;
	    }
	  }], [{
	    key: "createInstance",
	    value: function createInstance(options) {
	      return new this(options);
	    }
	  }]);
	  return ConnectionItem;
	}();

	function _templateObject6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-wrap\">\n\t\t\t\t<div class=\"calendar-sync-header\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject6 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div id=\"calendar-sync-mobile\" class=\"calendar-sync-mobile\"></div>\n\t\t"]);

	  _templateObject5$2 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div id=\"calendar-sync-web\" class=\"calendar-sync-web\"></div>\n\t\t"]);

	  _templateObject4$2 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-title\">", "</div>\n\t\t"]);

	  _templateObject3$2 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-title\">", "</div>\n\t\t"]);

	  _templateObject2$2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span class=\"calendar-sync-header-text\">", "</span>\n\t\t"]);

	  _templateObject$2 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var SyncPanel = /*#__PURE__*/function () {
	  function SyncPanel(options) {
	    babelHelpers.classCallCheck(this, SyncPanel);
	    this.status = options.status;
	    this.connectionsProviders = options.connectionsProviders;
	    this.userId = options.userId;
	    this.statusBlockEnterTimeout = null;
	    this.statusBlockLeaveTimeout = null;
	  }

	  babelHelpers.createClass(SyncPanel, [{
	    key: "showContent",
	    value: function showContent() {
	      var mainHeader = main_core.Tag.render(_templateObject$2(), main_core.Loc.getMessage('SYNC_CALENDAR_HEADER'));
	      var connections = this.getConnections();
	      this.blockStatus = StatusBlock.createInstance({
	        status: this.status,
	        connections: connections,
	        withStatus: true,
	        popupWithUpdateButton: true,
	        popupId: 'calendar-syncPanel-status'
	      }).getContentStatusBlock();
	      var webHeader = main_core.Tag.render(_templateObject2$2(), main_core.Loc.getMessage('SYNC_WEB_HEADER'));
	      var mobileHeader = main_core.Tag.render(_templateObject3$2(), main_core.Loc.getMessage('SYNC_MOBILE_HEADER'));
	      var webContentBlock = main_core.Tag.render(_templateObject4$2());
	      var mobileContentBlock = main_core.Tag.render(_templateObject5$2());
	      return main_core.Tag.render(_templateObject6(), mainHeader, this.blockStatus, mobileHeader, mobileContentBlock, webHeader, webContentBlock);
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
	            if (connection instanceof ConnectionItem) {
	              if (connection.getConnectStatus() === true) {
	                connections.push(connection);
	              }
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
	      var grid = new BX.TileGrid.Grid({
	        id: 'calendar_sync',
	        items: items,
	        container: document.getElementById('calendar-sync-web'),
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
	      var grid = new BX.TileGrid.Grid({
	        id: 'calendar_sync',
	        items: items,
	        container: document.getElementById('calendar-sync-mobile'),
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
	    }
	  }]);
	  return SyncPanel;
	}();

	var SyncButton = /*#__PURE__*/function () {
	  function SyncButton(options) {
	    babelHelpers.classCallCheck(this, SyncButton);
	    babelHelpers.defineProperty(this, "SLIDER_WIDTH", 684);
	    babelHelpers.defineProperty(this, "LOADER_NAME", "calendar:loader");
	    babelHelpers.defineProperty(this, "BUTTON_SIZE", BX.UI.Button.Size.EXTRA_SMALL);
	    babelHelpers.defineProperty(this, "BUTTON_ROUND", true);
	    this.connectionsProviders = options.connectionsProviders;
	    this.wrapper = options.wrapper;
	    this.userId = options.userId;
	    this.status = options.status;
	    this.buttonEnterTimeout = null;
	    this.buttonLeaveTimeout = null;
	  }

	  babelHelpers.createClass(SyncButton, [{
	    key: "show",
	    value: function show() {
	      var _this = this;

	      var buttonData = this.getButtonData();
	      this.button = new BX.UI.Button({
	        text: buttonData.text,
	        round: this.BUTTON_ROUND,
	        size: this.BUTTON_SIZE,
	        color: buttonData.color,
	        className: 'ui-btn-themes ' + (buttonData.iconClass || ''),
	        onclick: function onclick() {
	          _this.handleClick();
	        },
	        events: {
	          mouseenter: this.handlerMouseEnter.bind(this),
	          mouseleave: this.handlerMouseLeave.bind(this)
	        }
	      });
	      this.button.renderTo(this.wrapper);
	    }
	  }, {
	    key: "showPopup",
	    value: function showPopup(button) {
	      var _this2 = this;

	      if (this.status !== 'not_connected') {
	        var connections = [];
	        var providersCollection = Object.values(this.connectionsProviders);
	        providersCollection.forEach(function (provider) {
	          var providerConnections = provider.getConnections();

	          if (providerConnections.length > 0) {
	            providerConnections.forEach(function (connection) {
	              if (connection.getConnectStatus() === true) {
	                connections.push(connection);
	              }
	            });
	          }
	        });
	        this.popup = SyncStatusPopup.createInstance({
	          connections: connections,
	          withUpdateButton: true,
	          node: button.getContainer(),
	          id: 'calendar-syncPanel-status'
	        });
	        this.popup.show();
	        this.popup.getPopup().getPopupContainer().addEventListener('mouseenter', function (e) {
	          clearTimeout(_this2.buttonEnterTimeout);
	          clearTimeout(_this2.buttonLeaveTimeout);
	        });
	        this.popup.getPopup().getPopupContainer().addEventListener('mouseleave', function () {
	          _this2.hidePopup();
	        });
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
	    key: "refresh",
	    value: function refresh(status, connectionProviders) {
	      this.status = status;
	      this.connectionsProviders = connectionProviders;
	      var buttonData = this.getButtonData();
	      this.button.setColor(buttonData.color);
	      this.button.setText(buttonData.text);
	      this.button.removeClass('ui-btn-icon-fail ui-btn-icon-success');
	      this.button.addClass(buttonData.iconClass);
	    }
	  }, {
	    key: "handleClick",
	    value: function handleClick() {
	      var _this3 = this;

	      clearTimeout(this.buttonEnterTimeout);
	      this.syncPanel = new SyncPanel({
	        connectionsProviders: this.connectionsProviders,
	        userId: this.userId,
	        status: this.status
	      });
	      var syncPanel = this.syncPanel;
	      BX.SidePanel.Instance.open("calendar:sync-slider", {
	        contentCallback: function contentCallback(slider) {
	          return new Promise(function (resolve, reject) {
	            resolve(syncPanel.showContent());
	          });
	        },
	        allowChangeHistory: false,
	        events: {
	          onLoad: function onLoad(slider) {
	            _this3.syncPanel.setGridContent();
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
	      }); // this.refreshData();
	    }
	  }, {
	    key: "handlerMouseEnter",
	    value: function handlerMouseEnter(button) {
	      var _this4 = this;

	      clearTimeout(this.buttonEnterTimeout);
	      this.buttonEnterTimeout = setTimeout(function () {
	        _this4.buttonEnterTimeout = null;

	        _this4.showPopup(button);
	      }, 500);
	    }
	  }, {
	    key: "handlerMouseLeave",
	    value: function handlerMouseLeave() {
	      var _this5 = this;

	      if (this.buttonEnterTimeout !== null) {
	        clearTimeout(this.buttonEnterTimeout);
	        this.buttonEnterTimeout = null;
	        return;
	      }

	      this.buttonLeaveTimeout = setTimeout(function () {
	        _this5.hidePopup();
	      }, 500);
	    }
	  }, {
	    key: "getButtonData",
	    value: function getButtonData() {
	      if (this.status === 'success') {
	        return {
	          text: main_core.Loc.getMessage('STATUS_BUTTON_SYNCHRONIZATION'),
	          color: BX.UI.Button.Color.LIGHT_BORDER,
	          iconClass: 'ui-btn-icon-success'
	        };
	      } else if (this.status === 'failed') {
	        return {
	          text: main_core.Loc.getMessage('STATUS_BUTTON_FAILED'),
	          color: BX.UI.Button.Color.LIGHT_BORDER,
	          iconClass: 'ui-btn-icon-fail'
	        };
	      }

	      return {
	        text: main_core.Loc.getMessage('STATUS_BUTTON_SYNC_CALENDAR'),
	        color: BX.UI.Button.Color.PRIMARY
	      };
	    }
	  }, {
	    key: "getSyncPanel",
	    value: function getSyncPanel() {
	      return this.syncPanel;
	    }
	  }], [{
	    key: "createInstance",
	    value: function createInstance(options) {
	      return new this(options);
	    }
	  }]);
	  return SyncButton;
	}();

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

	function _templateObject6$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-slider-form-btn\"></div>\n\t\t"]);

	  _templateObject6$1 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button id=\"edit-connect-button\" class=\"calendar-sync-slider-btn ui-btn ui-btn-light-border\">", "</button>\n\t\t"]);

	  _templateObject5$3 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button id=\"disconnect-button\" class=\"calendar-sync-slider-btn ui-btn ui-btn-light-border\">", "</button>\n\t\t"]);

	  _templateObject4$3 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button id=\"connect-button\" class=\"ui-btn ui-btn-light-border\">", "</button>\n\t\t"]);

	  _templateObject3$3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<form class=\"calendar-sync-slider-form\" action=\"\">\n\t\t\t\t<div class=\"calendar-sync-slider-field\">\n\t\t\t\t\t<div class=\"ui-ctl ui-ctl-w100 ui-ctl-textbox\">\n\t\t\t\t\t\t<input type=\"text\" class=\"ui-ctl-element\" placeholder=\"", "\" name=\"name\" value=\"", "\">\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"calendar-sync-slider-field\">\n\t\t\t\t\t<div class=\"ui-ctl ui-ctl-w100 ui-ctl-textbox\">\n\t\t\t\t\t\t<input type=\"text\" class=\"ui-ctl-element\" placeholder=\"", "\" name=\"server\" value=\"", "\">\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"calendar-sync-slider-field\">\n\t\t\t\t\t<div class=\"ui-ctl ui-ctl-w100 ui-ctl-textbox\">\n\t\t\t\t\t\t<input type=\"text\" class=\"ui-ctl-element\" placeholder=\"", "\" name=\"user_name\" value=\"", "\">\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"calendar-sync-slider-field\">\n\t\t\t\t\t<div class=\"ui-ctl ui-ctl-w100 ui-ctl-textbox\">\n\t\t\t\t\t\t<input type=\"password\" class=\"ui-ctl-element\" name=\"password\" placeholder=\"", "\">\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</form>\n\t\t"], ["\n\t\t\t<form class=\"calendar-sync-slider-form\" action=\"\">\n\t\t\t\t<div class=\"calendar-sync-slider-field\">\n\t\t\t\t\t<div class=\"ui-ctl ui-ctl-w100 ui-ctl-textbox\">\n\t\t\t\t\t\t<input type=\"text\" class=\"ui-ctl-element\" placeholder=\\\"", "\\\" name=\"name\" value=\"", "\">\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"calendar-sync-slider-field\">\n\t\t\t\t\t<div class=\"ui-ctl ui-ctl-w100 ui-ctl-textbox\">\n\t\t\t\t\t\t<input type=\"text\" class=\"ui-ctl-element\" placeholder=\\\"", "\\\" name=\"server\" value=\"", "\">\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"calendar-sync-slider-field\">\n\t\t\t\t\t<div class=\"ui-ctl ui-ctl-w100 ui-ctl-textbox\">\n\t\t\t\t\t\t<input type=\"text\" class=\"ui-ctl-element\" placeholder=\\\"", "\\\" name=\"user_name\" value=\"", "\">\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"calendar-sync-slider-field\">\n\t\t\t\t\t<div class=\"ui-ctl ui-ctl-w100 ui-ctl-textbox\">\n\t\t\t\t\t\t<input type=\"password\" class=\"ui-ctl-element\" name=\"password\" placeholder=\\\"", "\\\">\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</form>\n\t\t"]);

	  _templateObject2$3 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-slider-section calendar-sync-slider-section-form\"></div>\n\t\t"]);

	  _templateObject$3 = function _templateObject() {
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
	      return main_core.Tag.render(_templateObject$3());
	    }
	  }, {
	    key: "getForm",
	    value: function getForm() {
	      return main_core.Tag.render(_templateObject2$3(), main_core.Loc.getMessage('CAL_TEXT_NAME'), this.connectionName || '', main_core.Loc.getMessage('CAL_TEXT_SERVER_ADDRESS'), this.server || '', main_core.Loc.getMessage('CAL_TEXT_USER_NAME'), this.userName || '', main_core.Loc.getMessage('CAL_TEXT_PASSWORD'));
	    }
	  }, {
	    key: "getAddButton",
	    value: function getAddButton() {
	      return main_core.Tag.render(_templateObject3$3(), this.addButtonText);
	    }
	  }, {
	    key: "getDisconnectButton",
	    value: function getDisconnectButton() {
	      return main_core.Tag.render(_templateObject4$3(), this.removeButtonText);
	    }
	  }, {
	    key: "getSaveButton",
	    value: function getSaveButton() {
	      return main_core.Tag.render(_templateObject5$3(), this.saveButtonText);
	    }
	  }, {
	    key: "getButtonWrapper",
	    value: function getButtonWrapper() {
	      return main_core.Tag.render(_templateObject6$1());
	    }
	  }]);
	  return ConnectionControls;
	}();

	function _templateObject2$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"calendar-notice-mobile-banner\" data-hint=\"", "\" data-hint-no-icon=\"Y\"></span>"]);

	  _templateObject2$4 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t", "\n\t\t\t<div class=\"calendar-sync-qr-popup-content\">\n\t\t\t\t<div class=\"calendar-sync-qr-popup-title\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t<div class=\"calendar-sync-slider-content\">\n\t\t\t\t\t<img class=\"calendar-sync-slider-phone-img\" src=\"/bitrix/images/calendar/sync/qr-background.svg\" alt=\"\">\n\t\t\t\t\t<div class=\"calendar-sync-slider-qr\">\n\t\t\t\t\t\t<div class=\"", "\">", "</div>\n\t\t\t\t\t\t<span class=\"calendar-sync-slider-logo\"></span>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"calendar-sync-slider-instruction\">\n\t\t\t\t\t\t<!--<div class=\"calendar-sync-slider-instruction-subtitle\"></div>-->\n\t\t\t\t\t\t<div class=\"calendar-sync-slider-instruction-title\">", " ", "</div>\n\t\t\t\t\t\t<div class=\"calendar-sync-slider-instruction-notice\">", "</div>\n\t\t\t\t\t\t<a href=\"javascript:void(0);\" \n\t\t\t\t\t\t\t\tonclick=\"BX.Helper.show('redirect=detail&code=' + ", ",{zIndex:3100,}); event.preventDefault();\" \n\t\t\t\t\t\t\t\tclass=\"ui-btn ui-btn-success ui-btn-round\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</a>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"]);

	  _templateObject$4 = function _templateObject() {
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
	    babelHelpers.defineProperty(this, "QRCODE_SIZE", 128);
	    babelHelpers.defineProperty(this, "QRCODE_COLOR_LIGHT", '#ffffff');
	    babelHelpers.defineProperty(this, "QRCODE_COLOR_DARK", '#000000');
	    babelHelpers.defineProperty(this, "QRCODE_WRAP_CLASS", 'calendar-sync-slider-qr-container');
	    babelHelpers.defineProperty(this, "QRC", null);
	    this.type = options.type;
	    this.helpDeskCode = options.helpDeskCode || '11828176';
	    this.fixHintPopupZIndexBinded = this.fixHintPopupZIndex.bind(this);
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
	      this.DOM.container = main_core.Tag.render(_templateObject$4(), this.getSliderContentInfoBlock, this.getTitle(), this.QRCODE_WRAP_CLASS, calendar_util.Util.getLoader(this.QRCODE_SIZE), main_core.Loc.getMessage('SYNC_MOBILE_NOTICE_HOW_TO'), this.type !== 'iphone' ? main_core.Tag.render(_templateObject2$4(), main_core.Loc.getMessage('CAL_ANDROID_QR_CODE_HINT')) : '', main_core.Loc.getMessage('SYNC_MOBILE_NOTICE'), this.getHelpdeskCode(), main_core.Loc.getMessage('SYNC_MOBILE_ABOUT_BTN'));
	      this.DOM.mobileHintIcon = this.DOM.container.querySelector('.calendar-notice-mobile-banner');

	      if (this.DOM.mobileHintIcon && BX.UI.Hint) {
	        BX.UI.Hint.initNode(this.DOM.mobileHintIcon);
	        BX.addCustomEvent('onPopupShow', this.fixHintPopupZIndexBinded);
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
	      return new Promise(function (resolve, reject) {
	        BX.ajax.runAction('calendar.api.calendarajax.getAuthLink').then(function (response) {
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
	  }, {
	    key: "fixHintPopupZIndex",
	    value: function fixHintPopupZIndex(popupWindow) {
	      if (popupWindow.uniquePopupId === 'ui-hint-popup' && popupWindow.bindElement === this.DOM.mobileHintIcon) {
	        var Z_INDEX = 3200;

	        if (popupWindow.params.zIndex && popupWindow.params.zIndex < Z_INDEX || popupWindow.popupContainer.style.zIndex && popupWindow.popupContainer.style.zIndex < Z_INDEX) {
	          popupWindow.params.zIndex = Z_INDEX;
	          popupWindow.popupContainer.style.zIndex = Z_INDEX;
	        }
	      }
	    }
	  }]);
	  return MobileSyncBanner;
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

	function _templateObject6$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t", "\n\t\t"]);

	  _templateObject6$2 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-header\">\n\t\t\t\t<span class=\"calendar-sync-header-text\">", "</span>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject5$4 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t", "\n\t\t"]);

	  _templateObject4$4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-header\">\n\t\t\t\t<span class=\"calendar-sync-header-text\">", "</span>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject3$4 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-wrap calendar-sync-wrap-detail\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject2$5 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-wrap calendar-sync-wrap-detail\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject$5 = function _templateObject() {
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
	      return main_core.Tag.render(_templateObject$5(), this.getContentInfoHeader(), this.getContentInfoBody());
	    }
	  }, {
	    key: "getActiveConnectionContent",
	    value: function getActiveConnectionContent() {
	      return main_core.Tag.render(_templateObject2$5(), this.getContentActiveHeader(), this.getContentActiveBody());
	    }
	  }, {
	    key: "getContentInfoHeader",
	    value: function getContentInfoHeader() {
	      var statusBlock = StatusBlock.createInstance({
	        status: "not_connected",
	        connections: [this.connection],
	        withStatus: false,
	        popupWithUpdateButton: this.popupWithUpdateButton,
	        popupId: 'calendar-interfaceTemplate-status'
	      });
	      return main_core.Tag.render(_templateObject3$4(), this.getHeaderTitle(), statusBlock.getContentStatusBlock());
	    }
	  }, {
	    key: "getContentInfoBody",
	    value: function getContentInfoBody() {
	      return main_core.Tag.render(_templateObject4$4(), this.getContentInfoBodyHeader());
	    }
	  }, {
	    key: "getContentActiveHeader",
	    value: function getContentActiveHeader() {
	      var statusBlock = StatusBlock.createInstance({
	        status: this.connection.getStatus(),
	        connections: [this.connection],
	        withStatus: false,
	        popupWithUpdateButton: this.popupWithUpdateButton,
	        popupId: 'calendar-interfaceTemplate-status'
	      });
	      return main_core.Tag.render(_templateObject5$4(), this.getHeaderTitle(), statusBlock.getContentStatusBlock());
	    }
	  }, {
	    key: "getContentActiveBody",
	    value: function getContentActiveBody() {
	      return main_core.Tag.render(_templateObject6$2(), this.getContentActiveBodyHeader());
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
	    key: "setConnection",
	    value: function setConnection(connection) {
	      this.connection = connection;
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
	        main_core.Dom.addClass(button, ['ui-btn-clock', 'ui-btn-disabled']);
	        event.preventDefault();

	        _this.sendRequestAddConnection(form);
	      });
	      main_core.Dom.append(button, buttonWrapper);
	      main_core.Dom.append(buttonWrapper, form);
	      main_core.Dom.append(form, formBlock);
	      return main_core.Tag.render(_templateObject$6(), bodyHeader, formBlock);
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
	      return main_core.Tag.render(_templateObject2$6(), bodyHeader, formBlock);
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

	function _templateObject5$5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<li class=\"calendar-sync-slider-item\">\n\t\t\t\t\t<label class=\"ui-ctl ui-ctl-checkbox ui-ctl-xs\">\n\t\t\t\t\t\t<input type=\"checkbox\" class=\"ui-ctl-element\" value=\"", "\" onclick=\"", "\" ", ">\n\t\t\t\t\t\t<div class=\"ui-ctl-label-text\">", "</div>\n\t\t\t\t\t</label>\n\t\t\t\t</li>\n\t\t\t"]);

	  _templateObject5$5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-slider-section calendar-sync-slider-section-col\">\n\t\t\t\t<div class=\"calendar-sync-slider-header\">\n\t\t\t\t\t<div class=\"calendar-sync-slider-subtitle\">", "</div>\n\t\t\t\t</div>\n\t\t\t\t<ul class=\"calendar-sync-slider-list\">\n\t\t\t\t\t", "\n\t\t\t\t</ul>\n\t\t\t</div>\n\t\t"]);

	  _templateObject4$5 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-slider-section\">\n\t\t\t\t<div class=\"calendar-sync-slider-header-icon calendar-sync-slider-header-icon-google\"></div>\n\t\t\t\t<div class=\"calendar-sync-slider-header\">\n\t\t\t\t\t<div class=\"calendar-sync-slider-title\">", "</div>\n\t\t\t\t\t<span class=\"calendar-sync-slider-account\">\n\t\t\t\t\t\t<span class=\"calendar-sync-slider-account-avatar\"></span>\n\t\t\t\t\t\t<span class=\"calendar-sync-slider-account-email\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</span>\n\t\t\t\t\t<div class=\"calendar-sync-slider-info\">\n\t\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">\n\t\t\t\t\t\t\t<a class=\"calendar-sync-slider-info-link\" href=\"javascript:void(0);\" onclick=\"", "\">\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</div>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t"]);

	  _templateObject3$5 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$7() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t", "\n\t\t\t", "\n\t\t"]);

	  _templateObject2$7 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$7() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t", "\n\t\t"]);

	  _templateObject$7 = function _templateObject() {
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
	      return main_core.Tag.render(_templateObject$7(), bodyHeader);
	    }
	  }, {
	    key: "getContentActiveBody",
	    value: function getContentActiveBody() {
	      return main_core.Tag.render(_templateObject2$7(), this.getContentActiveBodyHeader(), this.getContentActiveBodySectionsManager());
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
	      return main_core.Tag.render(_templateObject3$5(), main_core.Loc.getMessage('CAL_GOOGLE_CALENDAR_IS_CONNECT'), BX.util.htmlspecialchars(this.connection.getConnectionName()), this.showHelp.bind(this), main_core.Loc.getMessage('CAL_TEXT_ABOUT_WORK_SYNC'), disconnectButton);
	    }
	  }, {
	    key: "getContentActiveBodySectionsManager",
	    value: function getContentActiveBodySectionsManager() {
	      return main_core.Tag.render(_templateObject4$5(), main_core.Loc.getMessage('CAL_AVAILABLE_CALENDAR'), this.getContentActiveBodySections(this.connection.getId()));
	    }
	  }, {
	    key: "getContentActiveBodySections",
	    value: function getContentActiveBodySections(connectionId) {
	      var _this4 = this;

	      var sectionList = [];
	      this.provider.getConnection().getSections().forEach(function (section) {
	        sectionList.push(main_core.Tag.render(_templateObject5$5(), BX.util.htmlspecialchars(section['ID']), _this4.onClickCheckSection.bind(_this4), section['ACTIVE'] === 'Y' ? 'checked' : '', BX.util.htmlspecialchars(section['NAME'])));
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

	function _templateObject3$6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-slider-section calendar-sync-slider-section-col\">\n\t\t\t\t<div class=\"calendar-sync-slider-header calendar-sync-slider-header-divide\">\n\t\t\t\t\t<div class=\"calendar-sync-slider-subtitle\">", "</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"calendar-sync-slider-info\">\n\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">", ":</span>\n\t\t\t\t\t<ol class=\"calendar-sync-slider-info-list\">\n\t\t\t\t\t\t<li class=\"calendar-sync-slider-info-item\">\n\t\t\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">", "</span>\n\t\t\t\t\t\t</li>\n\t\t\t\t\t\t<li class=\"calendar-sync-slider-info-item\">\n\t\t\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">", "</span>\n\t\t\t\t\t\t</li>\n\t\t\t\t\t\t<li class=\"calendar-sync-slider-info-item\">\n\t\t\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">", "</span>\n\t\t\t\t\t\t</li>\n\t\t\t\t\t\t<li class=\"calendar-sync-slider-info-item\">\n\t\t\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">", "</span>\n\t\t\t\t\t\t</li>\n\t\t\t\t\t\t<li class=\"calendar-sync-slider-info-item\">\n\t\t\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">", "</span>\n\t\t\t\t\t\t</li>\n\t\t\t\t\t\t<li class=\"calendar-sync-slider-info-item\">\n\t\t\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">", "</span>\n\t\t\t\t\t\t</li>\n\t\t\t\t\t\t<li class=\"calendar-sync-slider-info-item\">\n\t\t\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">", "</span>\n\t\t\t\t\t\t</li>\n\t\t\t\t\t</ol>\n\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">", "</span>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"]);

	  _templateObject3$6 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$8() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t", "\n\t\t\t", "\n\t\t"]);

	  _templateObject2$8 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$8() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t", "\n\t\t\t", "\n\t\t"]);

	  _templateObject$8 = function _templateObject() {
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
	      return main_core.Tag.render(_templateObject$8(), this.getContentInfoBodyHeader(), this.getContentBodyConnect());
	    }
	  }, {
	    key: "getContentActiveBody",
	    value: function getContentActiveBody() {
	      return main_core.Tag.render(_templateObject2$8(), this.getContentActiveBodyHeader(), this.getContentBodyConnect());
	    }
	  }, {
	    key: "getContentBodyConnect",
	    value: function getContentBodyConnect() {
	      return main_core.Tag.render(_templateObject3$6(), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_HEADER'), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_DESCRIPTION'), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_FIRST'), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_SECOND'), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_THIRD'), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_FOURTH'), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_FIFTH').replace(/#PORTAL_ADDRESS#/gi, this.provider.getPortalAddress()), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_SIXTH'), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_SEVENTH'), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_CONCLUSION'));
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

	function _templateObject3$7() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-slider-section\">\n\t\t\t\t<div class=\"calendar-sync-slider-header-icon ", "\"></div>\n\t\t\t\t<div class=\"calendar-sync-slider-header\">\n\t\t\t\t<div class=\"calendar-sync-slider-title\">", "</div>\n\t\t\t\t<div class=\"calendar-sync-slider-info\">\n\t\t\t\t\t<span class=\"calendar-sync-slider-info-text\">", "</span>\n\t\t\t\t\t<span class=\"calendar-sync-slider-info-time\">", "</span>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"calendar-sync-slider-desc\">", "</div>\n\t\t\t\t\t<a class=\"calendar-sync-slider-link\" href=\"javascript:void(0);\" onclick=\"", "\">", "</a>\n\t\t\t\t</div>\n\t\t\t</div>"]);

	  _templateObject3$7 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$9() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t", "\n\t\t\t<div class=\"calendar-sync-slider-section calendar-sync-slider-section-banner\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject2$9 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$9() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t", "\n\t\t\t<div class=\"calendar-sync-slider-section calendar-sync-slider-section-banner\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject$9 = function _templateObject() {
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
	      return main_core.Tag.render(_templateObject$9(), this.getContentInfoBodyHeader(), this.getContentBodyConnect());
	    }
	  }, {
	    key: "getContentActiveBody",
	    value: function getContentActiveBody() {
	      return main_core.Tag.render(_templateObject2$9(), this.getContentActiveBodyHeader(), this.getContentBodyConnect());
	    }
	  }, {
	    key: "getContentActiveBodyHeader",
	    value: function getContentActiveBodyHeader() {
	      return main_core.Tag.render(_templateObject3$7(), this.sliderIconClass, this.titleActiveHeader, main_core.Loc.getMessage('CAL_SYNC_LAST_SYNC_DATE'), calendar_util.Util.formatDateUsable(this.connection.getSyncTimestamp()) + ' ' + calendar_util.Util.formatTime(this.connection.getSyncTimestamp()), main_core.Loc.getMessage('CAL_SYNC_DISABLE'), this.showHelp.bind(this), main_core.Loc.getMessage('CAL_TEXT_ABOUT_WORK_SYNC'));
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

	var Connection = /*#__PURE__*/function () {
	  function Connection(options) {
	    babelHelpers.classCallCheck(this, Connection);
	    babelHelpers.defineProperty(this, "SLIDER_WIDTH", 606);
	    this.status = options.status;
	    this.connected = options.connected;
	    this.connections = options.connections;
	    this.gridTitle = options.gridTitle;
	    this.gridColor = options.gridColor;
	    this.gridIcon = options.gridIcon;
	    this.type = options.type;
	    this.viewClassification = options.viewClassification;
	    this.templateClass = options.templateClass;
	    this.connections = [];
	  }

	  babelHelpers.createClass(Connection, [{
	    key: "isActive",
	    value: function isActive() {
	      return this.connected;
	    }
	  }, {
	    key: "hasMenu",
	    value: function hasMenu() {
	      return false;
	    }
	  }, {
	    key: "setAdditionalParams",
	    value: function setAdditionalParams(options) {
	      this.additionalParams = options;
	    }
	  }, {
	    key: "getGridTitle",
	    value: function getGridTitle() {
	      return this.gridTitle;
	    }
	  }, {
	    key: "getGridColor",
	    value: function getGridColor() {
	      return this.gridColor;
	    }
	  }, {
	    key: "getGridIcon",
	    value: function getGridIcon() {
	      return this.gridIcon;
	    }
	  }, {
	    key: "setConnections",
	    value: function setConnections() {
	      this.connections.push(ConnectionItem.createInstance({
	        syncTimestamp: this.syncTimestamp,
	        connectionName: this.connectionName,
	        status: this.status,
	        connected: this.connected,
	        addParams: {
	          sections: this.sections,
	          id: this.id || this.type
	        },
	        type: this.type
	      }));
	    }
	  }, {
	    key: "getConnections",
	    value: function getConnections() {
	      return this.connections;
	    }
	  }, {
	    key: "getConnection",
	    value: function getConnection() {
	      return this.connections[0];
	    }
	  }, {
	    key: "getType",
	    value: function getType() {
	      return this.type;
	    }
	  }, {
	    key: "getViewClassification",
	    value: function getViewClassification() {
	      return this.viewClassification;
	    }
	  }, {
	    key: "getConnectStatus",
	    value: function getConnectStatus() {
	      return this.connected;
	    }
	  }, {
	    key: "getSyncStatus",
	    value: function getSyncStatus() {
	      return this.status;
	    }
	  }, {
	    key: "getStatus",
	    value: function getStatus() {
	      if (this.connected) {
	        return this.status ? "success" : "failed";
	      } else {
	        return 'not_connected';
	      }
	    }
	  }, {
	    key: "getTemplateClass",
	    value: function getTemplateClass() {
	      return this.templateClass;
	    }
	  }, {
	    key: "openSlider",
	    value: function openSlider(options) {
	      var _this = this;

	      BX.SidePanel.Instance.open(options.sliderId, {
	        contentCallback: function contentCallback(slider) {
	          return new Promise(function (resolve, reject) {
	            resolve(options.content);
	          });
	        },
	        data: options.data || {},
	        cacheable: options.cacheable,
	        width: this.SLIDER_WIDTH,
	        allowChangeHistory: false,
	        events: {
	          onLoad: function onLoad(event) {
	            _this.itemSlider = event.getSlider();
	          }
	        }
	      });
	    }
	  }, {
	    key: "openInfoConnectionSlider",
	    value: function openInfoConnectionSlider() {
	      var content = this.getClassTemplateItem().createInstance(this).getInfoConnectionContent();
	      this.openSlider({
	        sliderId: 'calendar:item-sync-connect-' + this.type,
	        content: content,
	        cacheable: false,
	        data: {
	          provider: this
	        }
	      });
	    }
	  }, {
	    key: "openActiveConnectionSlider",
	    value: function openActiveConnectionSlider(connection) {
	      var itemInterface = this.getClassTemplateItem().createInstance(this, connection);
	      var content = itemInterface.getActiveConnectionContent();
	      this.openSlider({
	        sliderId: 'calendar:item-sync-' + connection.id,
	        content: content,
	        cacheable: false,
	        data: {
	          provider: this,
	          connection: connection,
	          itemInterface: itemInterface
	        }
	      });
	    }
	  }, {
	    key: "getClassTemplateItem",
	    value: function getClassTemplateItem() {
	      var itemClass = main_core.Reflection.getClass(this.getTemplateClass());

	      if (main_core.Type.isFunction(itemClass)) {
	        return itemClass;
	      }

	      return null;
	    }
	  }, {
	    key: "getConnectionById",
	    value: function getConnectionById(id) {
	      var connections = this.getConnections();

	      if (connections.length > 0) {
	        connections.forEach(function (connection) {
	          if (connection.getId() === id) {
	            return connection;
	          }
	        });
	      }

	      return this.getConnection();
	    }
	  }], [{
	    key: "createInstance",
	    value: function createInstance(options) {
	      return new this(options);
	    }
	  }]);
	  return Connection;
	}();

	var MacProvider = /*#__PURE__*/function (_Connection) {
	  babelHelpers.inherits(MacProvider, _Connection);

	  function MacProvider(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, MacProvider);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(MacProvider).call(this, {
	      status: options.syncInfo.status,
	      connected: options.syncInfo.connected,
	      gridTitle: main_core.Loc.getMessage('CALENDAR_TITLE_MAC'),
	      gridColor: '#ff5752',
	      gridIcon: '/bitrix/images/calendar/sync/mac.svg',
	      type: 'mac',
	      viewClassification: 'web',
	      templateClass: 'BX.Calendar.Sync.Interface.MacTemplate'
	    }));
	    _this.syncTimestamp = options.syncInfo.syncTimestamp;
	    _this.portalAddress = options.portalAddress;
	    _this.connectionName = main_core.Loc.getMessage('CALENDAR_TITLE_MAC');

	    _this.setConnections();

	    return _this;
	  }

	  babelHelpers.createClass(MacProvider, [{
	    key: "getPortalAddress",
	    value: function getPortalAddress() {
	      return this.portalAddress;
	    }
	  }]);
	  return MacProvider;
	}(Connection);

	var OutlookProvider = /*#__PURE__*/function (_Connection) {
	  babelHelpers.inherits(OutlookProvider, _Connection);

	  function OutlookProvider(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, OutlookProvider);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(OutlookProvider).call(this, {
	      status: options.syncInfo.status,
	      connected: options.syncInfo.connected,
	      gridTitle: main_core.Loc.getMessage('CALENDAR_TITLE_OUTLOOK'),
	      gridColor: '#ffa900',
	      gridIcon: '/bitrix/images/calendar/sync/outlook.svg',
	      type: 'outlook',
	      viewClassification: 'web',
	      templateClass: 'BX.Calendar.Sync.Interface.OutlookTemplate'
	    }));
	    _this.syncTimestamp = options.syncInfo.syncTimestamp;
	    _this.connectionName = main_core.Loc.getMessage('CALENDAR_TITLE_OUTLOOK');
	    _this.sections = options.sections;
	    _this.infoBySections = options.infoBySections; // this.setConnectStatus();

	    _this.setConnections();

	    return _this;
	  }

	  babelHelpers.createClass(OutlookProvider, [{
	    key: "hasMenu",
	    value: function hasMenu() {
	      return this.sections.length > 0;
	    }
	  }, {
	    key: "showMenu",
	    value: function showMenu(bindElement) {
	      var _this2 = this;

	      if (this.hasMenu()) {
	        if (this.menu) {
	          this.menu.getPopupWindow().setBindElement(bindElement);
	          this.menu.show();
	        } else {
	          var menuItems = this.getConnection().getSections();
	          menuItems.forEach(function (item) {
	            if (_this2.infoBySections[item.id]) {
	              item.className = 'calendar-sync-outlook-popup-item';
	            }

	            item.onclick = function () {
	              if (item && item.connectURL) {
	                try {
	                  eval(item.connectURL);
	                } catch (e) {}
	              }
	            };
	          });
	          this.menu = new main_popup.Menu({
	            className: 'calendar-sync-popup-status',
	            bindElement: bindElement,
	            items: menuItems,
	            width: this.MENU_WIDTH,
	            padding: 7,
	            autoHide: true,
	            closeByEsc: true,
	            zIndexAbsolute: 3020,
	            id: this.getType() + '-menu'
	          });
	          this.menu.getMenuContainer().addEventListener('click', function () {
	            _this2.menu.close();
	          });
	          this.menu.show();
	        }
	      }
	    }
	  }]);
	  return OutlookProvider;
	}(Connection);

	var AndroidProvider = /*#__PURE__*/function (_Connection) {
	  babelHelpers.inherits(AndroidProvider, _Connection);

	  function AndroidProvider(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, AndroidProvider);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AndroidProvider).call(this, {
	      status: options.syncInfo.status,
	      connected: options.syncInfo.connected,
	      gridTitle: main_core.Loc.getMessage('CALENDAR_TITLE_ANDROID'),
	      gridColor: '#9ece03',
	      gridIcon: '/bitrix/images/calendar/sync/android.svg',
	      type: 'android',
	      viewClassification: 'mobile',
	      templateClass: 'BX.Calendar.Sync.Interface.AndroidTemplate'
	    }));
	    _this.syncTimestamp = options.syncInfo.syncTimestamp;
	    _this.connectionName = main_core.Loc.getMessage('CALENDAR_TITLE_ANDROID');

	    _this.setConnections();

	    return _this;
	  }

	  return AndroidProvider;
	}(Connection);

	function _templateObject4$6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-sync-item-status\"></div>\n\t\t\t"]);

	  _templateObject4$6 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$8() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-item-image\">\n\t\t\t\t<div class=\"calendar-sync-item-image-item\" style=\"background-image: ", "\"></div>\n\t\t\t</div>"]);

	  _templateObject3$8 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$a() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-sync-item-title\">", "</div>"]);

	  _templateObject2$a = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$a() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-sync-item ", "\" style=\"", "\">\n\t\t\t<div class=\"calendar-item-content\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t</div>"]);

	  _templateObject$a = function _templateObject() {
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
	      this.gridUnit = main_core.Tag.render(_templateObject$a(), this.getAdditionalContentClass(), this.getContentStyles(), this.getImage(), this.getTitle(), this.isActive() ? this.getStatus() : '');
	      this.gridUnit.addEventListener('click', this.onClick.bind(this));
	      return this.gridUnit;
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      if (!this.layout.title) {
	        this.layout.title = main_core.Tag.render(_templateObject2$a(), BX.util.htmlspecialchars(this.item.getGridTitle()));
	      }

	      return this.layout.title;
	    }
	  }, {
	    key: "getImage",
	    value: function getImage() {
	      return main_core.Tag.render(_templateObject3$8(), 'url(' + this.item.getGridIcon() + ')');
	    }
	  }, {
	    key: "getStatus",
	    value: function getStatus() {
	      if (this.isActive()) {
	        return main_core.Tag.render(_templateObject4$6());
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

	babelHelpers.defineProperty(GridUnit, "MENU_WIDTH", 200);
	babelHelpers.defineProperty(GridUnit, "MENU_PADDING", 7);
	babelHelpers.defineProperty(GridUnit, "MENU_INDEX", 3020);

	var CaldavConnection = /*#__PURE__*/function (_Connection) {
	  babelHelpers.inherits(CaldavConnection, _Connection);

	  function CaldavConnection(options) {
	    babelHelpers.classCallCheck(this, CaldavConnection);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CaldavConnection).call(this, options));
	  }

	  babelHelpers.createClass(CaldavConnection, [{
	    key: "hasMenu",
	    value: function hasMenu() {
	      return this.connected;
	    }
	  }, {
	    key: "showMenu",
	    value: function showMenu(bindElement) {
	      var _this = this;

	      if (this.menu) {
	        this.menu.getPopupWindow().setBindElement(bindElement);
	        this.menu.show();
	        return;
	      }

	      var menuItems = this.connections;
	      menuItems.forEach(function (item) {
	        item.type = _this.type;
	        item.id = item.addParams.id;
	        item.text = item.connectionName;

	        item.onclick = function () {
	          _this.openActiveConnectionSlider(item);
	        };
	      });
	      menuItems.push({
	        delimiter: true
	      }, {
	        id: 'connect',
	        text: main_core.Loc.getMessage('ADD_MENU_CONNECTION'),
	        onclick: function onclick() {
	          _this.openInfoConnectionSlider();
	        }
	      });
	      this.menu = new main_popup.Menu({
	        className: 'calendar-sync-popup-status',
	        bindElement: bindElement,
	        items: menuItems,
	        width: GridUnit.MENU_WIDTH,
	        padding: GridUnit.MENU_PADDING,
	        autoHide: true,
	        closeByEsc: true,
	        zIndexAbsolute: GridUnit.MENU_INDEX,
	        id: this.getType() + '-menu'
	      });
	      this.menu.getMenuContainer().addEventListener('click', function () {
	        _this.menu.close();
	      });
	      this.menu.show();
	    }
	  }, {
	    key: "setConnections",
	    value: function setConnections() {
	      var _this2 = this;

	      if (this.connectionsSyncInfo.length > 0) {
	        this.connectionsSyncInfo.forEach(function (connection) {
	          _this2.connections.push(ConnectionItem.createInstance({
	            syncTimestamp: connection.syncInfo.syncTimestamp,
	            connectionName: connection.syncInfo.connectionName,
	            status: connection.syncInfo.status,
	            connected: connection.syncInfo.connected,
	            addParams: {
	              sections: connection.sections,
	              id: connection.syncInfo.id,
	              userName: connection.syncInfo.userName,
	              server: connection.syncInfo.server
	            },
	            type: _this2.type
	          }));
	        });
	      }
	    }
	  }], [{
	    key: "calculateStatus",
	    value: function calculateStatus(connections) {
	      if (connections.length === 0) {
	        return false;
	      }

	      for (var key in connections) {
	        if (connections[key].connected === true && connections[key].status === false) {
	          return false;
	        }
	      }

	      return true;
	    }
	  }]);
	  return CaldavConnection;
	}(Connection);

	var CaldavProvider = /*#__PURE__*/function (_CaldavConnection) {
	  babelHelpers.inherits(CaldavProvider, _CaldavConnection);

	  function CaldavProvider(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, CaldavProvider);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CaldavProvider).call(this, {
	      status: options.status,
	      connected: options.connected,
	      gridTitle: main_core.Loc.getMessage('CALENDAR_TITLE_CALDAV'),
	      gridColor: '#1eae43',
	      gridIcon: '/bitrix/images/calendar/sync/caldav.svg',
	      type: 'caldav',
	      viewClassification: 'web',
	      templateClass: 'BX.Calendar.Sync.Interface.CaldavTemplate'
	    }));
	    _this.connectionsSyncInfo = options.connections;

	    _this.setConnections(options);

	    return _this;
	  }

	  return CaldavProvider;
	}(CaldavConnection);

	var ExchangeProvider = /*#__PURE__*/function (_Connection) {
	  babelHelpers.inherits(ExchangeProvider, _Connection);

	  function ExchangeProvider(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, ExchangeProvider);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ExchangeProvider).call(this, {
	      status: options.syncInfo.status || false,
	      connected: options.syncInfo.connected || false,
	      gridTitle: main_core.Loc.getMessage('CALENDAR_TITLE_EXCHANGE'),
	      gridColor: '#54d0df',
	      gridIcon: '/bitrix/images/calendar/sync/exchange.svg',
	      type: 'exchange',
	      viewClassification: 'web',
	      templateClass: 'BX.Calendar.Sync.Interface.ExchangeTemplate'
	    }));
	    _this.syncTimestamp = options.syncInfo.syncTimestamp;
	    _this.connectionName = main_core.Loc.getMessage('CALENDAR_TITLE_EXCHANGE');
	    _this.sections = options.sections;

	    _this.setConnections();

	    return _this;
	  }

	  return ExchangeProvider;
	}(Connection);

	var GoogleProvider = /*#__PURE__*/function (_Connection) {
	  babelHelpers.inherits(GoogleProvider, _Connection);

	  function GoogleProvider(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, GoogleProvider);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(GoogleProvider).call(this, {
	      status: options.syncInfo.status || false,
	      connected: options.syncInfo.connected || false,
	      gridTitle: main_core.Loc.getMessage('CALENDAR_TITLE_GOOGLE'),
	      gridColor: '#387ced',
	      gridIcon: '/bitrix/images/calendar/sync/google.svg',
	      type: 'google',
	      interfaceClassName: '',
	      viewClassification: 'web',
	      templateClass: 'BX.Calendar.Sync.Interface.GoogleTemplate'
	    }));
	    _this.syncTimestamp = options.syncInfo.syncTimestamp;
	    _this.connectionName = options.syncInfo.userName ? options.syncInfo.userName : main_core.Loc.getMessage('CALENDAR_TITLE_GOOGLE');
	    _this.id = options.syncInfo.id;
	    _this.isSetSyncCaldavSettings = options.isSetSyncCaldavSettings;
	    _this.syncLink = options.syncLink;
	    _this.sections = options.sections;

	    _this.setConnections();

	    return _this;
	  }

	  babelHelpers.createClass(GoogleProvider, [{
	    key: "getSyncLink",
	    value: function getSyncLink() {
	      return this.syncLink;
	    }
	  }, {
	    key: "hasSetSyncCaldavSettings",
	    value: function hasSetSyncCaldavSettings() {
	      return this.isSetSyncCaldavSettings;
	    }
	  }]);
	  return GoogleProvider;
	}(Connection);

	var IphoneProvider = /*#__PURE__*/function (_Connection) {
	  babelHelpers.inherits(IphoneProvider, _Connection);

	  function IphoneProvider(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, IphoneProvider);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(IphoneProvider).call(this, {
	      status: options.syncInfo.status,
	      connected: options.syncInfo.connected,
	      gridTitle: main_core.Loc.getMessage('CALENDAR_TITLE_IPHONE'),
	      gridColor: '#2fc6f6',
	      gridIcon: '/bitrix/images/calendar/sync/iphone.svg',
	      type: 'iphone',
	      viewClassification: 'mobile',
	      templateClass: 'BX.Calendar.Sync.Interface.IphoneTemplate'
	    }));
	    _this.syncTimestamp = options.syncInfo.syncTimestamp;
	    _this.connectionName = main_core.Loc.getMessage('CALENDAR_TITLE_IPHONE');

	    _this.setConnections();

	    return _this;
	  }

	  return IphoneProvider;
	}(Connection);

	var YandexProvider = /*#__PURE__*/function (_CaldavConnection) {
	  babelHelpers.inherits(YandexProvider, _CaldavConnection);

	  function YandexProvider(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, YandexProvider);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(YandexProvider).call(this, {
	      status: options.status,
	      connected: options.connected,
	      gridTitle: main_core.Loc.getMessage('CALENDAR_TITLE_YANDEX'),
	      gridColor: '#f9c500',
	      gridIcon: '/bitrix/images/calendar/sync/yandex.svg',
	      type: 'yandex',
	      viewClassification: 'web',
	      templateClass: 'BX.Calendar.Sync.Interface.YandexTemplate'
	    }));
	    _this.connectionsSyncInfo = options.connections;

	    _this.setConnections(options);

	    return _this;
	  }

	  return YandexProvider;
	}(CaldavConnection);

	var SyncInterfaceManager = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(SyncInterfaceManager, _EventEmitter);

	  function SyncInterfaceManager(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, SyncInterfaceManager);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SyncInterfaceManager).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "status", 'not_connected');

	    _this.setEventNamespace('BX.Calendar.Sync.Interface.SyncInterfaceManager');

	    _this.wrapper = options.wrapper;
	    _this.syncInfo = options.syncInfo;
	    _this.userId = options.userId;
	    _this.syncLinks = options.syncLinks;
	    _this.sections = options.sections;
	    _this.portalAddress = options.portalAddress;
	    _this.isRuZone = options.isRuZone;
	    _this.calendarInstance = options.calendar;
	    _this.isSetSyncCaldavSettings = options.isSetSyncCaldavSettings;

	    _this.init();

	    main_core_events.EventEmitter.subscribe('BX.Calendar.Sync.Interface.SyncStatusPopup:onRefresh', function (event) {
	      _this.refresh(event);
	    });
	    main_core_events.EventEmitter.subscribe('BX.Calendar.Sync.Interface.InterfaceTemplate:reDrawCalendarGrid', function (event) {
	      _this.reDrawCalendarGrid();
	    });
	    return _this;
	  }

	  babelHelpers.createClass(SyncInterfaceManager, [{
	    key: "showSyncButton",
	    value: function showSyncButton() {
	      this.syncButton = SyncButton.createInstance({
	        status: this.status,
	        wrapper: this.wrapper,
	        connectionsProviders: this.connectionsProviders,
	        userId: this.userId
	      });
	      this.syncButton.show();
	    }
	  }, {
	    key: "init",
	    value: function init() {
	      this.connectionsProviders = {};
	      this.webItems = [];
	      this.mobileItems = [];
	      var yandexConnections = [];
	      var caldavConnections = [];
	      var syncInfo = this.syncInfo;
	      var sectionsByType = this.sortSections();

	      for (var key in syncInfo) {
	        switch (syncInfo[key].type) {
	          case 'yandex':
	            yandexConnections.push({
	              syncInfo: syncInfo[key],
	              sections: sectionsByType.caldav['caldav' + syncInfo[key].id],
	              isRuZone: this.isRuZone
	            });
	            break;

	          case 'caldav':
	            caldavConnections.push({
	              syncInfo: syncInfo[key],
	              sections: sectionsByType.caldav['caldav' + syncInfo[key].id]
	            });
	            break;
	        }

	        if (syncInfo[key].connected === true) {
	          if (syncInfo[key].status === true && this.status !== 'failed') {
	            this.status = 'success';
	          } else if (syncInfo[key].status === false) {
	            this.status = 'failed';
	          }
	        }
	      }

	      this.connectionsProviders = {
	        google: GoogleProvider.createInstance({
	          syncInfo: syncInfo.google || {},
	          sections: sectionsByType.google || {},
	          syncLink: this.syncLinks.google || null,
	          isSetSyncCaldavSettings: this.isSetSyncCaldavSettings
	        }),
	        caldav: CaldavProvider.createInstance({
	          status: CaldavConnection.calculateStatus(caldavConnections),
	          connected: caldavConnections.length > 0,
	          connections: caldavConnections
	        }),
	        iphone: IphoneProvider.createInstance({
	          syncInfo: syncInfo.iphone
	        }),
	        android: AndroidProvider.createInstance({
	          syncInfo: syncInfo.android
	        }),
	        mac: MacProvider.createInstance({
	          syncInfo: syncInfo.mac,
	          portalAddress: this.portalAddress
	        })
	      };

	      if (this.isRuZone) {
	        this.connectionsProviders.yandex = YandexProvider.createInstance({
	          status: CaldavConnection.calculateStatus(yandexConnections),
	          connected: yandexConnections.length > 0,
	          connections: yandexConnections
	        });
	      }

	      if (!BX.browser.IsMac()) {
	        this.connectionsProviders.outlook = OutlookProvider.createInstance({
	          syncInfo: syncInfo.outlook,
	          sections: sectionsByType.outlook,
	          infoBySections: syncInfo.outlook.infoBySections || {}
	        });
	      }

	      var has = Object.prototype.hasOwnProperty;

	      if (has.call(syncInfo, "exchange")) {
	        this.connectionsProviders.exchange = ExchangeProvider.createInstance({
	          syncInfo: syncInfo.exchange
	        });
	      }
	    }
	  }, {
	    key: "sortSections",
	    value: function sortSections() {
	      var sections = this.sections;
	      var exchangeSections = [];
	      var googleSections = [];
	      var sectionsByType = {};
	      var outlookSections = [];
	      sectionsByType.caldav = {};
	      sections.forEach(function (section) {
	        if (section.belongsToView() && section.data.OUTLOOK_JS) {
	          outlookSections.push({
	            id: section.id,
	            connectURL: section.data.OUTLOOK_JS,
	            text: section.name
	          });
	        }

	        if (section.data['IS_EXCHANGE'] === true) {
	          exchangeSections.push(section.data);
	        } else if (section.data['GAPI_CALENDAR_ID'] && section.data['CAL_DAV_CON']) {
	          googleSections.push(section.data);
	        } else if (section.data['CAL_DAV_CON'] && section.data['CAL_DAV_CAL']) {
	          sectionsByType.caldav['caldav' + section.data['CAL_DAV_CON']] = section.data;
	        }
	      });
	      sectionsByType.google = googleSections;
	      sectionsByType.exchange = exchangeSections;
	      sectionsByType.outlook = outlookSections;
	      return sectionsByType;
	    }
	  }, {
	    key: "refresh",
	    value: function refresh(event) {
	      var _this2 = this;

	      BX.ajax.runAction('calendar.api.calendarajax.updateConnection', {
	        data: {
	          type: 'user'
	        }
	      }).then(function (response) {
	        _this2.syncInfo = response.data;

	        _this2.init();

	        _this2.calendarInstance.reload();

	        _this2.syncButton.refresh(_this2.status, _this2.connectionsProviders); //popup refresh


	        if (event.getTarget().getId() === 'calendar-syncPanel-status') {
	          event.getTarget().refresh(_this2.getConnections());
	        }

	        var openSliders = BX.SidePanel.Instance.getOpenSliders();

	        if (openSliders.length > 0) {
	          var syncPanel = _this2.syncButton.getSyncPanel();

	          openSliders.forEach(function (slider) {
	            if (slider.getUrl() === 'calendar:sync-slider') {
	              syncPanel.refresh(_this2.status, _this2.connectionsProviders);
	              slider.reload();
	            } else {
	              var itemInterface = slider.getData().get('itemInterface');
	              var connection = slider.getData().get('connection');

	              var updatedConnection = _this2.connectionsProviders[connection.getType()].getConnectionById(connection.getId());

	              event.getTarget().refresh([updatedConnection]);
	              itemInterface.setConnection(updatedConnection);
	              slider.reload();
	            }
	          });
	        }
	      });
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
	            if (connection.getConnectStatus() === true) {
	              connections.push(connection);
	            }
	          });
	        }
	      });
	      return connections;
	    }
	  }, {
	    key: "reDrawCalendarGrid",
	    value: function reDrawCalendarGrid() {
	      this.calendarInstance.reload();
	    }
	  }]);
	  return SyncInterfaceManager;
	}(main_core_events.EventEmitter);

	exports.SyncInterfaceManager = SyncInterfaceManager;
	exports.SyncButton = SyncButton;
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

}((this.BX.Calendar.Sync.Interface = this.BX.Calendar.Sync.Interface || {}),BX.UI.Dialogs,BX.Calendar,BX.Event,BX,BX,BX.Main,BX));
//# sourceMappingURL=syncinterface.bundle.js.map
