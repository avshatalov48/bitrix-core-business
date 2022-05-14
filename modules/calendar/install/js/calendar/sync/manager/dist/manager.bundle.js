this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
this.BX.Calendar.Sync = this.BX.Calendar.Sync || {};
(function (exports,main_popup,main_core_events,main_core,calendar_util) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5;

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

	      this.container = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-popup-list\"></div>\n\t\t"])));
	      this.connections.forEach(function (connection) {
	        if (connection.getConnectStatus() !== true) {
	          return;
	        }

	        var options = {};
	        options.syncTime = _this2.getTime(connection.getSyncTimestamp());
	        options.classStatus = connection.getSyncStatus() ? 'calendar-sync-popup-item-status-success' : 'calendar-sync-popup-item-status-fail';
	        options.classLable = 'calendar-sync-popup-item-text-' + connection.getClassLabel();
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
	    value: function getTime(timestamp) {
	      var format = [["tommorow", "tommorow, H:i:s"], ["s", main_core.Loc.getMessage('CAL_JUST')], ["i", "iago"], ["H", "Hago"], ["d", "dago"], ["m100", "mago"], ["m", "mago"], // ["m5", Loc.getMessage('CAL_JUST')],
	      ["-", ""]];
	      return BX.date.format(format, timestamp);
	    }
	  }, {
	    key: "getSyncElement",
	    value: function getSyncElement(options) {
	      return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-sync-popup-item\">\n\t\t\t\t\t<span class=\"calendar-sync-popup-item-text ", "\">", "</span>\n\t\t\t\t\t<div class=\"calendar-sync-popup-item-detail\">\n\t\t\t\t\t\t<span class=\"calendar-sync-popup-item-time\">", "</span>\n\t\t\t\t\t\t<span class=\"calendar-sync-popup-item-status ", "\"></span>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), options.classLable, BX.util.htmlspecialchars(options.title), options.syncTime, options.classStatus);
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
	      if (main_core.Type.isElementNode(this.refreshStatusBlock)) {
	        this.refreshStatusBlock.remove();
	      }
	    }
	  }, {
	    key: "enableRefreshButton",
	    value: function enableRefreshButton() {
	      if (main_core.Type.isElementNode(this.refreshButton)) {
	        this.refreshButton.className = 'calendar-sync-popup-footer-btn';
	      }
	    }
	  }, {
	    key: "disableRefreshButton",
	    value: function disableRefreshButton() {
	      if (main_core.Type.isElementNode(this.refreshButton)) {
	        this.refreshButton.className = 'calendar-sync-popup-footer-btn calendar-sync-popup-footer-btn-disabled';
	      }
	    }
	  }, {
	    key: "getContentRefreshBlock",
	    value: function getContentRefreshBlock() {
	      this.footerWrapper = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-sync-popup-footer-wrap\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), this.getContentRefreshButton());
	      return this.footerWrapper;
	    }
	  }, {
	    key: "getContentRefreshButton",
	    value: function getContentRefreshButton() {
	      var _this4 = this;

	      this.refreshButton = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button class=\"calendar-sync-popup-footer-btn\">", "</button>\n\t\t"])), main_core.Loc.getMessage('CAL_REFRESH'));
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
	      this.refreshStatusBlock = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span class=\"calendar-sync-popup-footer-status\">", "</span>\n\t\t"])), main_core.Loc.getMessage('CAL_REFRESH_JUST'));
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

	var SyncButton = /*#__PURE__*/function () {
	  function SyncButton(options) {
	    babelHelpers.classCallCheck(this, SyncButton);
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
	          id: 'calendar-syncButton-status'
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
	      (window.top.BX || window.BX).Runtime.loadExtension('calendar.sync.interface').then(function (exports) {
	        BX.ajax.runAction('calendar.api.calendarajax.analytical', {
	          analyticsLabel: {
	            sync_button_click: 'Y',
	            has_active_connection: _this3.status === 'not_connected' ? 'N' : 'Y'
	          }
	        });
	        _this3.syncPanel = new exports.SyncPanel({
	          connectionsProviders: _this3.connectionsProviders,
	          userId: _this3.userId,
	          status: _this3.status
	        });

	        _this3.syncPanel.openSlider();
	      });
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

	var isConnectionItemProperty = Symbol["for"]('BX.Calendar.Sync.Manager.ConnectionItem.isConnectionItem');

	var ConnectionItem = /*#__PURE__*/function () {
	  function ConnectionItem(options) {
	    babelHelpers.classCallCheck(this, ConnectionItem);
	    this[isConnectionItemProperty] = true;
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
	    key: "getClassLabel",
	    value: function getClassLabel() {
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
	  }, {
	    key: "isConnectionItem",
	    value: function isConnectionItem(target) {
	      return main_core.Type.isObject(target) && target[isConnectionItemProperty] === true;
	    }
	  }]);
	  return ConnectionItem;
	}();

	var ConnectionProvider = /*#__PURE__*/function () {
	  function ConnectionProvider(options) {
	    babelHelpers.classCallCheck(this, ConnectionProvider);
	    babelHelpers.defineProperty(this, "MENU_WIDTH", 200);
	    babelHelpers.defineProperty(this, "MENU_PADDING", 7);
	    babelHelpers.defineProperty(this, "MENU_INDEX", 3020);
	    babelHelpers.defineProperty(this, "SLIDER_WIDTH", 606);
	    this.status = options.status;
	    this.connected = options.connected;
	    this.mainPanel = options.mainPanel === true;
	    this.pendingStatus = options.pendingStatus === true;
	    this.gridTitle = options.gridTitle;
	    this.gridColor = options.gridColor;
	    this.gridIcon = options.gridIcon;
	    this.type = options.type;
	    this.viewClassification = options.viewClassification;
	    this.templateClass = options.templateClass;
	    this.connections = [];
	  }

	  babelHelpers.createClass(ConnectionProvider, [{
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
	    key: "setSyncTimestamp",
	    value: function setSyncTimestamp(timestamp) {
	      this.syncTimestamp = timestamp;
	      return this;
	    }
	  }, {
	    key: "setStatus",
	    value: function setStatus(status) {
	      this.status = status;
	      return this;
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
	      } else if (this.pendingStatus) {
	        return 'pending';
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
	      var _this2 = this;

	      var itemInterface = this.getClassTemplateItem().createInstance(this, connection);

	      if (this.type === 'google') {
	        itemInterface.getSectionsForGoogle().then(function () {
	          var content = itemInterface.getActiveConnectionContent();

	          _this2.openSlider({
	            sliderId: 'calendar:item-sync-' + connection.id,
	            content: content,
	            cacheable: false,
	            data: {
	              provider: _this2,
	              connection: connection,
	              itemInterface: itemInterface
	            }
	          });
	        });
	      } else {
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
	        var result = connections.filter(function (connection) {
	          return connection.getId() == id;
	        });

	        if (result) {
	          return result[0];
	        }
	      }

	      return null;
	    }
	  }, {
	    key: "getSyncPanelTitle",
	    value: function getSyncPanelTitle() {
	      return this.gridTitle;
	    }
	  }, {
	    key: "getSyncPanelLogo",
	    value: function getSyncPanelLogo() {
	      return '--' + this.type;
	    }
	  }], [{
	    key: "createInstance",
	    value: function createInstance(options) {
	      return new this(options);
	    }
	  }]);
	  return ConnectionProvider;
	}();

	var GoogleProvider = /*#__PURE__*/function (_ConnectionProvider) {
	  babelHelpers.inherits(GoogleProvider, _ConnectionProvider);

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
	      templateClass: 'BX.Calendar.Sync.Interface.GoogleTemplate',
	      mainPanel: options.mainPanel
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
	}(ConnectionProvider);

	var Office365Provider = /*#__PURE__*/function (_ConnectionProvider) {
	  babelHelpers.inherits(Office365Provider, _ConnectionProvider);

	  function Office365Provider(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Office365Provider);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Office365Provider).call(this, {
	      status: options.syncInfo.status || false,
	      connected: options.syncInfo.connected || false,
	      gridTitle: main_core.Loc.getMessage('CALENDAR_TITLE_OFFICE365'),
	      gridColor: '#000',
	      gridIcon: '',
	      type: 'office365',
	      interfaceClassName: '',
	      viewClassification: 'web',
	      templateClass: 'BX.Calendar.Sync.Interface.Office365template',
	      mainPanel: true,
	      pendingStatus: true
	    }));
	    _this.connectionName = 'Office365';
	    _this.syncLink = options.syncLink || '';
	    _this.id = options.syncInfo.id;

	    _this.setConnections();

	    return _this;
	  }

	  babelHelpers.createClass(Office365Provider, [{
	    key: "getSyncLink",
	    value: function getSyncLink() {
	      return this.syncLink;
	    }
	  }]);
	  return Office365Provider;
	}(ConnectionProvider);

	var ICloudProvider = /*#__PURE__*/function (_ConnectionProvider) {
	  babelHelpers.inherits(ICloudProvider, _ConnectionProvider);

	  function ICloudProvider(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, ICloudProvider);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ICloudProvider).call(this, {
	      status: options.syncInfo.status || false,
	      connected: options.syncInfo.connected || false,
	      gridTitle: main_core.Loc.getMessage('CALENDAR_TITLE_ICLOUD'),
	      gridColor: '#000',
	      gridIcon: '',
	      type: 'icloud',
	      interfaceClassName: '',
	      viewClassification: 'web',
	      templateClass: 'BX.Calendar.Sync.Interface.IcloudTemplate',
	      mainPanel: true,
	      pendingStatus: true
	    }));
	    _this.connectionName = 'icloud';
	    _this.id = options.syncInfo.id;

	    _this.setConnections();

	    return _this;
	  }

	  return ICloudProvider;
	}(ConnectionProvider);

	var AndroidProvider = /*#__PURE__*/function (_ConnectionProvider) {
	  babelHelpers.inherits(AndroidProvider, _ConnectionProvider);

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
	}(ConnectionProvider);

	var CaldavConnection = /*#__PURE__*/function (_ConnectionProvider) {
	  babelHelpers.inherits(CaldavConnection, _ConnectionProvider);

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
	      if (this.menu) {
	        this.menu.getPopupWindow().setBindElement(bindElement);
	        this.menu.show();
	        return;
	      }

	      var menuItems = this.getMenuItems();
	      menuItems.push.apply(menuItems, babelHelpers.toConsumableArray(this.getMenuItemConnect()));
	      this.menu = this.getMenu(bindElement, menuItems);
	      this.addMenuHandler();
	      this.menu.show();
	    }
	  }, {
	    key: "addMenuHandler",
	    value: function addMenuHandler() {
	      var _this = this;

	      if (this.menu) {
	        this.menu.getMenuContainer().addEventListener('click', function () {
	          _this.menu.close();
	        });
	      }
	    }
	  }, {
	    key: "getMenuItems",
	    value: function getMenuItems() {
	      var _this2 = this;

	      var menuItems = this.connections;
	      menuItems.forEach(function (item) {
	        item.type = _this2.type;
	        item.id = item.addParams.id;
	        item.text = item.connectionName;

	        item.onclick = function () {
	          _this2.openActiveConnectionSlider(item);
	        };
	      });
	      return menuItems;
	    }
	  }, {
	    key: "getMenuItemConnect",
	    value: function getMenuItemConnect() {
	      var _this3 = this;

	      return [{
	        delimiter: true
	      }, {
	        id: 'connect',
	        text: main_core.Loc.getMessage('ADD_MENU_CONNECTION'),
	        onclick: function onclick() {
	          _this3.openInfoConnectionSlider();
	        }
	      }];
	    }
	  }, {
	    key: "getMenu",
	    value: function getMenu(bindElement, menuItems) {
	      return new (window.top.BX || window.BX).Main.Menu({
	        className: 'calendar-sync-popup-status',
	        bindElement: bindElement,
	        items: menuItems,
	        width: this.MENU_WIDTH,
	        padding: this.MENU_PADDING,
	        zIndexAbsolute: this.MENU_INDEX,
	        autoHide: true,
	        closeByEsc: true,
	        id: this.getType() + '-menu'
	      });
	    }
	  }, {
	    key: "setConnections",
	    value: function setConnections() {
	      var _this4 = this;

	      if (this.connectionsSyncInfo.length > 0) {
	        this.connectionsSyncInfo.forEach(function (connection) {
	          _this4.connections.push(ConnectionItem.createInstance({
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
	            type: _this4.type
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
	        if (this.isFailedConnections(connections[key])) {
	          return false;
	        }
	      }

	      return true;
	    }
	  }, {
	    key: "isFailedConnections",
	    value: function isFailedConnections(connection) {
	      if (connection.syncInfo.connected === true && connection.syncInfo.status === false) {
	        return true;
	      }

	      return false;
	    }
	  }]);
	  return CaldavConnection;
	}(ConnectionProvider);

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

	var ExchangeProvider = /*#__PURE__*/function (_ConnectionProvider) {
	  babelHelpers.inherits(ExchangeProvider, _ConnectionProvider);

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
	}(ConnectionProvider);

	var IphoneProvider = /*#__PURE__*/function (_ConnectionProvider) {
	  babelHelpers.inherits(IphoneProvider, _ConnectionProvider);

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
	}(ConnectionProvider);

	var MacProvider = /*#__PURE__*/function (_ConnectionProvider) {
	  babelHelpers.inherits(MacProvider, _ConnectionProvider);

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
	}(ConnectionProvider);

	var OutlookProvider = /*#__PURE__*/function (_ConnectionProvider) {
	  babelHelpers.inherits(OutlookProvider, _ConnectionProvider);

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
	          this.menu = new (window.top.BX || window.BX).Main.Menu({
	            className: 'calendar-sync-popup-status',
	            bindElement: bindElement,
	            items: menuItems,
	            // width: this.MENU_WIDTH,
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
	}(ConnectionProvider);

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

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	var Manager = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Manager, _EventEmitter);

	  function Manager(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Manager);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Manager).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "status", 'not_connected');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "STATUS_SUCCESS", 'success');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "STATUS_FAILED", 'failed');

	    _this.setEventNamespace('BX.Calendar.Sync.Manager.Manager');

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

	    _this.subscribeOnEvent();

	    return _this;
	  }

	  babelHelpers.createClass(Manager, [{
	    key: "subscribeOnEvent",
	    value: function subscribeOnEvent() {
	      var _this2 = this;

	      main_core_events.EventEmitter.subscribe('BX.Calendar.Sync.Interface.SyncStatusPopup:onRefresh', function (event) {
	        _this2.refresh(event);
	      });
	      main_core_events.EventEmitter.subscribe('BX.Calendar.Sync.Interface.InterfaceTemplate:reDrawCalendarGrid', function (event) {
	        _this2.reDrawCalendarGrid();
	      });
	      window.addEventListener('message', function (event) {
	        if (event.data.title === 'googleOAuthSuccess') {
	          window.location.reload();
	        }
	      });
	    }
	  }, {
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

	        this.calculateStatus(syncInfo[key]);
	      }

	      this.connectionsProviders = {
	        google: GoogleProvider.createInstance({
	          syncInfo: syncInfo.google || {},
	          sections: sectionsByType.google || {},
	          syncLink: this.syncLinks.google || null,
	          isSetSyncCaldavSettings: this.isSetSyncCaldavSettings,
	          mainPanel: true
	        }),
	        office365: Office365Provider.createInstance({
	          syncInfo: syncInfo.office365 || {},
	          syncLink: this.syncLinks.office365 || null,
	          mainPanel: true
	        }),
	        icloud: ICloudProvider.createInstance({
	          syncInfo: syncInfo.icloud || {},
	          mainPanel: true
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
	    key: "calculateStatus",
	    value: function calculateStatus(provider) {
	      if (provider.connected === true) {
	        if (provider.status === true && this.status !== this.STATUS_FAILED) {
	          this.status = this.STATUS_SUCCESS;
	        } else if (provider.status === false) {
	          this.status = this.STATUS_FAILED;
	        }
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
	        } else if (section.data['GAPI_CALENDAR_ID'] && section.data['CAL_DAV_CON'] && section.data['EXTERNAL_TYPE'] !== 'local') {
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
	      var _this3 = this;

	      var activePopup = event.getTarget();
	      BX.ajax.runAction('calendar.api.calendarajax.updateConnection', {
	        data: {
	          type: 'user',
	          requestUid: calendar_util.Util.registerRequestId()
	        }
	      }).then(function (response) {
	        _this3.syncInfo = response.data;
	        _this3.status = _this3.STATUS_SUCCESS;

	        _this3.refreshContent(activePopup);
	      });
	    }
	  }, {
	    key: "refreshContent",
	    value: function refreshContent() {
	      var activePopup = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      this.init();
	      this.refreshCalendarGrid();
	      this.refreshSyncButton();
	      this.refreshActivePopup(activePopup);
	      this.refreshOpenSliders(activePopup);
	    }
	  }, {
	    key: "refreshCalendarGrid",
	    value: function refreshCalendarGrid() {
	      this.calendarInstance.reload();
	    }
	  }, {
	    key: "refreshSyncButton",
	    value: function refreshSyncButton() {
	      this.syncButton.refresh(this.status, this.connectionsProviders);
	    }
	  }, {
	    key: "refreshActivePopup",
	    value: function refreshActivePopup(activePopup) {
	      if (activePopup instanceof SyncStatusPopup && activePopup.getId() === 'calendar-syncPanel-status') {
	        activePopup.refresh(this.getConnections());
	      } else if (this.syncButton.popup instanceof SyncStatusPopup && this.syncButton.popup.getId() === 'calendar-syncButton-status') {
	        this.syncButton.popup.refresh(this.getConnections());
	      }
	    }
	  }, {
	    key: "refreshOpenSliders",
	    value: function refreshOpenSliders() {
	      var _this4 = this;

	      var activePopup = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var openSliders = BX.SidePanel.Instance.getOpenSliders();

	      if (openSliders.length > 0) {
	        openSliders.forEach(function (slider) {
	          if (slider.getUrl() === 'calendar:auxiliary-sync-slider') {
	            _this4.refreshMainSlider(_this4.syncButton.getSyncPanel());
	          } else if (slider.getUrl().indexOf('calendar:item-sync-') !== -1) {
	            _this4.refreshConnectionSlider(slider, activePopup);
	          }
	        });
	      }
	    }
	  }, {
	    key: "refreshConnectionSlider",
	    value: function refreshConnectionSlider(slider, activePopup) {
	      var updatedConnection = undefined;
	      var itemInterface = slider.getData().get('itemInterface');
	      var connection = slider.getData().get('connection');

	      if (connection) {
	        updatedConnection = this.connectionsProviders[connection.getType()].getConnectionById(connection.getId());
	      }

	      if (activePopup instanceof SyncStatusPopup && updatedConnection) {
	        activePopup.refresh([updatedConnection]);
	      }

	      if (itemInterface && updatedConnection) {
	        itemInterface.refresh(updatedConnection);
	      }

	      slider.reload();
	    }
	  }, {
	    key: "refreshMainSlider",
	    value: function refreshMainSlider(syncPanel) {
	      syncPanel.refresh(this.status, this.connectionsProviders);
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
	  }, {
	    key: "updateSyncStatus",
	    value: function updateSyncStatus(params) {
	      if (!BX.Calendar.Util.checkRequestId(params.requestUid)) {
	        return;
	      }

	      for (var connectionName in params.syncInfo) {
	        if (this.syncInfo[connectionName]) {
	          this.syncInfo[connectionName] = _objectSpread(_objectSpread({}, this.syncInfo[connectionName]), params.syncInfo[connectionName]);
	        }
	      }

	      this.status = this.STATUS_SUCCESS;
	      this.refreshContent();
	    }
	  }, {
	    key: "addSyncConnection",
	    value: function addSyncConnection(params) {
	      for (var connectionName in params.syncInfo) {
	        if (['yandex', 'caldav', 'google'].includes(params.syncInfo[connectionName].type)) {
	          BX.reload();
	        }

	        if (BX.Calendar.Util.checkRequestId(params.requestUid)) {
	          if (this.syncInfo[connectionName]) {
	            this.syncInfo[connectionName] = _objectSpread(_objectSpread({}, this.syncInfo[connectionName]), params.syncInfo[connectionName]);
	          }
	        }
	      }

	      this.status = this.STATUS_SUCCESS;
	      this.refreshContent();
	    }
	  }, {
	    key: "deleteSyncConnection",
	    value: function deleteSyncConnection(params) {
	      if (!BX.Calendar.Util.checkRequestId(params.requestUid)) {
	        return;
	      }

	      for (var connectionName in params.syncInfo) {
	        if (this.syncInfo[connectionName]) {
	          delete this.syncInfo[connectionName];
	        }
	      }

	      if (this.status !== 'not_connected') {
	        this.status = this.STATUS_SUCCESS;
	      }

	      this.refreshContent();
	    }
	  }, {
	    key: "getProviderById",
	    value: function getProviderById(id) {
	      var connection = undefined;

	      for (var providerName in this.connectionsProviders) {
	        if (!this.connectionsProviders[providerName].connected || !['google', 'caldav', 'yandex'].includes(providerName)) {
	          continue;
	        }

	        connection = this.connectionsProviders[providerName].getConnectionById(id);

	        if (connection) {
	          return [this.connectionsProviders[providerName], connection];
	        }
	      }

	      return null;
	    }
	  }]);
	  return Manager;
	}(main_core_events.EventEmitter);

	exports.Manager = Manager;
	exports.SyncButton = SyncButton;
	exports.SyncStatusPopup = SyncStatusPopup;
	exports.ConnectionItem = ConnectionItem;

}((this.BX.Calendar.Sync.Manager = this.BX.Calendar.Sync.Manager || {}),BX.Main,BX.Event,BX,BX.Calendar));
//# sourceMappingURL=manager.bundle.js.map
