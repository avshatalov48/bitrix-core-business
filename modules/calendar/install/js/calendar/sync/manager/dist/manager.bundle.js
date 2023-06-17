this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
this.BX.Calendar.Sync = this.BX.Calendar.Sync || {};
(function (exports,main_popup,main_core_events,calendar_util,main_core) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5;
	class SyncStatusPopup extends main_core_events.EventEmitter {
	  constructor(options) {
	    super();
	    this.setEventNamespace('BX.Calendar.Sync.Interface.SyncStatusPopup');
	    this.connections = options.connections;
	    this.withUpdateButton = options.withUpdateButton;
	    this.node = options.node;
	    this.id = options.id;
	    this.init();
	  }
	  static createInstance(options) {
	    return new this(options);
	  }
	  init() {
	    this.setPopupContent();
	  }
	  createPopup() {
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
	  show() {
	    this.createPopup();
	    this.popup.show();
	  }
	  setPopupContent() {
	    this.container = main_core.Tag.render(_t || (_t = _`
			<div class="calendar-sync-popup-list"></div>
		`));
	    this.connections.forEach(connection => {
	      if (connection.getConnectStatus() !== true) {
	        return;
	      }
	      const options = {};
	      options.syncTime = this.getFormattedTime(connection.getSyncDate());
	      options.classStatus = connection.getSyncStatus() ? 'calendar-sync-popup-item-status-success' : 'calendar-sync-popup-item-status-fail';
	      options.classLable = 'calendar-sync-popup-item-text-' + connection.getClassLabel();
	      options.title = connection.getConnectionName();
	      const block = this.getSyncElement(options);
	      this.container.append(block);
	    });
	    if (this.withUpdateButton) {
	      this.container.append(this.getContentRefreshBlock());
	      if (SyncStatusPopup.IS_RUN_REFRESH) {
	        this.showRefreshStatus();
	      }
	    }
	    return this.container;
	  }
	  hide() {
	    this.popup.destroy();
	  }
	  getContainer() {
	    return this.container;
	  }
	  getPopup() {
	    return this.popup;
	  }
	  getFormattedTime(date) {
	    const now = new Date();
	    let timestamp = date;
	    if (main_core.Type.isDate(date)) {
	      timestamp = Math.round(date.getTime() / 1000);
	      let secondsAgo = parseInt((now - date) / 1000);
	      if (secondsAgo < 60) {
	        return main_core.Loc.getMessage('CAL_JUST');
	      }
	    }
	    return BX.date.format([["tommorow", "tommorow, H:i:s"], ["i", "iago"], ["H", "Hago"], ["d", "dago"], ["m100", "mago"], ["m", "mago"], ["-", ""]], timestamp);
	  }
	  getSyncElement(options) {
	    return main_core.Tag.render(_t2 || (_t2 = _`
				<div class="calendar-sync-popup-item">
					<span class="calendar-sync-popup-item-text ${0}">${0}</span>
					<div class="calendar-sync-popup-item-detail">
						<span class="calendar-sync-popup-item-time">${0}</span>
						<span class="calendar-sync-popup-item-status ${0}"></span>
					</div>
				</div>
			`), options.classLable, BX.util.htmlspecialchars(options.title), options.syncTime, options.classStatus);
	  }
	  refresh(connections) {
	    this.connections = connections;
	    this.popup.setContent(this.setPopupContent());
	    this.setRefreshStatusBlock();
	  }
	  setRefreshStatusBlock() {
	    setTimeout(() => {
	      this.removeRefreshStatusBlock();
	      this.enableRefreshButton();
	      SyncStatusPopup.IS_RUN_REFRESH = false;
	    }, 120000);
	  }
	  removeRefreshStatusBlock() {
	    if (main_core.Type.isElementNode(this.refreshStatusBlock)) {
	      this.refreshStatusBlock.remove();
	    }
	  }
	  enableRefreshButton() {
	    if (main_core.Type.isElementNode(this.refreshButton)) {
	      this.refreshButton.className = 'calendar-sync-popup-footer-btn';
	    }
	  }
	  disableRefreshButton() {
	    if (main_core.Type.isElementNode(this.refreshButton)) {
	      this.refreshButton.className = 'calendar-sync-popup-footer-btn calendar-sync-popup-footer-btn-disabled';
	    }
	  }
	  getContentRefreshBlock() {
	    this.footerWrapper = main_core.Tag.render(_t3 || (_t3 = _`
			<div class="calendar-sync-popup-footer-wrap">
				${0}
			</div>
		`), this.getContentRefreshButton());
	    return this.footerWrapper;
	  }
	  getContentRefreshButton() {
	    this.refreshButton = main_core.Tag.render(_t4 || (_t4 = _`
			<button class="calendar-sync-popup-footer-btn">${0}</button>
		`), main_core.Loc.getMessage('CAL_REFRESH'));
	    this.refreshButton.addEventListener('click', () => {
	      main_core.Dom.addClass(this.refreshButton, 'calendar-sync-popup-footer-btn-load');
	      SyncStatusPopup.IS_RUN_REFRESH = true;
	      this.refreshButton.innerText = main_core.Loc.getMessage('CAL_REFRESHING');
	      this.runRefresh();
	    });
	    return this.refreshButton;
	  }
	  showRefreshStatus() {
	    this.disableRefreshButton();
	    this.footerWrapper.prepend(this.getRefreshStatus());
	  }
	  getRefreshStatus() {
	    this.refreshStatusBlock = main_core.Tag.render(_t5 || (_t5 = _`
			<span class="calendar-sync-popup-footer-status">${0}</span>
		`), main_core.Loc.getMessage('CAL_REFRESH_JUST'));
	    return this.refreshStatusBlock;
	  }
	  runRefresh() {
	    this.emit('onRefresh', {});
	  }
	  getId() {
	    return this.id;
	  }
	}
	SyncStatusPopup.IS_RUN_REFRESH = false;

	class SyncButton {
	  constructor(options) {
	    this.BUTTON_SIZE = BX.UI.Button.Size.EXTRA_SMALL;
	    this.BUTTON_ROUND = true;
	    this.connectionsProviders = options.connectionsProviders;
	    this.wrapper = options.wrapper;
	    this.userId = options.userId;
	    this.status = options.status;
	    this.buttonEnterTimeout = null;
	    this.buttonLeaveTimeout = null;
	  }
	  static createInstance(options) {
	    return new this(options);
	  }
	  show() {
	    const buttonData = this.getButtonData();
	    this.button = new BX.UI.Button({
	      text: buttonData.text,
	      round: this.BUTTON_ROUND,
	      size: this.BUTTON_SIZE,
	      color: buttonData.color,
	      className: 'ui-btn-themes ' + (buttonData.iconClass || ''),
	      onclick: () => {
	        this.handleClick();
	      },
	      events: {
	        mouseenter: this.handlerMouseEnter.bind(this),
	        mouseleave: this.handlerMouseLeave.bind(this)
	      }
	    });
	    this.button.renderTo(this.wrapper);
	  }
	  showPopup(button) {
	    if (this.status !== 'not_connected') {
	      const connections = [];
	      const providersCollection = Object.values(this.connectionsProviders);
	      providersCollection.forEach(provider => {
	        const providerConnections = provider.getConnections();
	        if (providerConnections.length > 0) {
	          providerConnections.forEach(connection => {
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
	        id: 'calendar-sync-button-status'
	      });
	      this.popup.show();
	      this.popup.getPopup().getPopupContainer().addEventListener('mouseenter', e => {
	        clearTimeout(this.buttonEnterTimeout);
	        clearTimeout(this.buttonLeaveTimeout);
	      });
	      this.popup.getPopup().getPopupContainer().addEventListener('mouseleave', () => {
	        this.hidePopup();
	      });
	    }
	  }
	  hidePopup() {
	    if (this.popup) {
	      this.popup.hide();
	    }
	  }
	  refresh(status) {
	    this.status = status;
	    const buttonData = this.getButtonData();
	    this.button.setColor(buttonData.color);
	    this.button.setText(buttonData.text);
	    this.button.removeClass('ui-btn-icon-fail ui-btn-icon-success ui-btn-clock');
	    this.button.addClass(buttonData.iconClass);
	  }
	  handleClick() {
	    clearTimeout(this.buttonEnterTimeout);
	    (window.top.BX || window.BX).Runtime.loadExtension('calendar.sync.interface').then(exports => {
	      if (!main_core.Dom.hasClass(this.button.button, 'ui-btn-clock')) {
	        BX.ajax.runAction('calendar.api.calendarajax.analytical', {
	          analyticsLabel: {
	            sync_button_click: 'Y',
	            has_active_connection: this.status === 'not_connected' ? 'N' : 'Y'
	          }
	        });
	        this.syncPanel = new exports.SyncPanel({
	          connectionsProviders: this.connectionsProviders,
	          userId: this.userId,
	          status: this.status
	        });
	        this.syncPanel.openSlider();
	      }
	    });
	  }
	  handlerMouseEnter(button) {
	    clearTimeout(this.buttonEnterTimeout);
	    this.buttonEnterTimeout = setTimeout(() => {
	      this.buttonEnterTimeout = null;
	      if (!main_core.Dom.hasClass(button.button, 'ui-btn-clock')) {
	        this.showPopup(button);
	      }
	    }, 500);
	  }
	  handlerMouseLeave() {
	    if (this.buttonEnterTimeout !== null) {
	      clearTimeout(this.buttonEnterTimeout);
	      this.buttonEnterTimeout = null;
	      return;
	    }
	    this.buttonLeaveTimeout = setTimeout(() => {
	      this.hidePopup();
	    }, 500);
	  }
	  getButtonData() {
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
	    } else if (this.status === 'synchronizing') {
	      return {
	        text: main_core.Loc.getMessage('STATUS_BUTTON_SYNCHRONIZATION'),
	        color: BX.UI.Button.Color.LIGHT_BORDER,
	        iconClass: 'ui-btn-clock'
	      };
	    }
	    return {
	      text: main_core.Loc.getMessage('STATUS_BUTTON_SYNC_CALENDAR_NEW'),
	      color: BX.UI.Button.Color.PRIMARY
	    };
	  }
	  getSyncPanel() {
	    return this.syncPanel;
	  }
	  setConnectionProviders(connectionsProviders) {
	    this.connectionsProviders = connectionsProviders;
	  }
	}

	const isConnectionItemProperty = Symbol.for('BX.Calendar.Sync.Manager.ConnectionItem.isConnectionItem');
	class ConnectionItem {
	  constructor(options) {
	    this[isConnectionItemProperty] = true;
	    this.syncDate = main_core.Type.isDate(options.syncDate) ? options.syncDate : new Date();
	    this.connectionName = options.connectionName;
	    this.status = options.status;
	    this.connected = options.connected;
	    this.addParams = options.addParams;
	    this.type = options.type;
	    this.id = options.type;
	    this.userName = options.userName;
	  }
	  static createInstance(options) {
	    return new this(options);
	  }
	  static isConnectionItem(target) {
	    return main_core.Type.isObject(target) && target[isConnectionItemProperty] === true;
	  }
	  getSyncDate() {
	    return this.syncDate;
	  }
	  getConnectionName() {
	    return this.connectionName;
	  }
	  getSyncStatus() {
	    return this.status;
	  }
	  getConnectStatus() {
	    return this.connected;
	  }
	  getStatus() {
	    if (this.connected) {
	      return this.status ? "success" : "failed";
	    } else {
	      return 'not_connected';
	    }
	  }
	  getClassLabel() {
	    return this.type;
	  }
	  getSections() {
	    return this.addParams.sections;
	  }
	  getId() {
	    return this.addParams.id;
	  }
	  getConnectionAccountName() {
	    return this.userName;
	  }
	  getType() {
	    return this.type;
	  }
	  setId(id) {
	    this.addParams.id = id;
	  }
	  setStatus(status) {
	    this.status = status;
	  }
	  setUserName(userName) {
	    this.userName = userName;
	  }
	  setConnected(connected) {
	    this.connected = connected;
	  }
	  setSyncDate(syncDate) {
	    this.syncDate = syncDate;
	  }
	}

	class ConnectionProvider extends main_core_events.EventEmitter {
	  // 6 min

	  constructor(options) {
	    super();
	    this.MENU_WIDTH = 200;
	    this.MENU_PADDING = 7;
	    this.MENU_INDEX = 3020;
	    this.SLIDER_WIDTH = 606;
	    this.STATUS_SYNCHRONIZING = 'synchronizing';
	    this.STATUS_SUCCESS = 'success';
	    this.STATUS_FAILED = 'failed';
	    this.STATUS_PENDING = 'pending';
	    this.STATUS_NOT_CONNECTED = 'not_connected';
	    this.ERROR_CODE = 'error';
	    this.STATUS_LIST = [this.STATUS_SYNCHRONIZING, this.STATUS_SUCCESS, this.STATUS_FAILED, this.STATUS_PENDING, this.STATUS_NOT_CONNECTED];
	    this.WAITING_MODE_MAX_TIME = 360000;
	    this.setEventNamespace('BX.Calendar.Sync.Manager.ConnectionProvider');
	    this.status = options.status;
	    this.connected = options.connected;
	    this.userName = options.userName || '';
	    this.mainPanel = options.mainPanel === true;
	    this.pendingStatus = options.pendingStatus === true;
	    this.gridTitle = options.gridTitle;
	    this.gridColor = options.gridColor;
	    this.gridIcon = options.gridIcon;
	    this.type = options.type;
	    this.viewClassification = options.viewClassification;
	    this.templateClass = options.templateClass;
	    // this.wizardClassName = options.wizardClass || null;
	    this.connections = [];
	    this.id = options.id || '';
	  }
	  static createInstance(options) {
	    return new this(options);
	  }
	  isActive() {
	    return this.connected;
	  }
	  hasMenu() {
	    return false;
	  }
	  setAdditionalParams(options) {
	    this.additionalParams = options;
	  }
	  setSyncDate(offset) {
	    offset = parseInt(offset);
	    if (offset > 60) {
	      this.syncDate = new Date(new Date().getTime() - offset * 1000);
	    } else if (!isNaN(offset)) {
	      this.syncDate = new Date();
	    } else {
	      this.syncDate = null;
	    }
	    if (this.getConnection()) {
	      this.getConnection().syncDate = this.syncDate;
	    }
	  }
	  getSyncDate() {
	    return this.syncDate;
	  }
	  setSections(sections) {
	    this.sections = sections;
	  }
	  setStatus(status) {
	    if (this.STATUS_LIST.includes(status)) {
	      this.status = status;
	      if (!this.connected && (status === this.STATUS_SUCCESS || status === this.STATUS_FAILED)) {
	        this.connected = true;
	      } else if (this.connected && status === this.STATUS_NOT_CONNECTED) {
	        this.connected = false;
	      }
	    }
	    return this;
	  }
	  getGridTitle() {
	    return this.gridTitle;
	  }
	  getGridColor() {
	    return this.gridColor;
	  }
	  getGridIcon() {
	    return this.gridIcon;
	  }
	  clearConnections() {
	    this.connections = [];
	  }
	  setConnections() {
	    this.connections.push(ConnectionItem.createInstance({
	      syncDate: this.getSyncDate(),
	      connectionName: this.connectionName,
	      status: this.status,
	      connected: this.connected,
	      userName: this.userName,
	      addParams: {
	        sections: this.sections,
	        id: this.id || this.type
	      },
	      type: this.type
	    }));
	  }
	  setInterfaceUnit(interfaceUnit) {
	    this.interfaceUnit = interfaceUnit;
	  }
	  getInterfaceUnit() {
	    return this.interfaceUnit;
	  }
	  getConnections() {
	    return this.connections;
	  }
	  getConnection() {
	    return this.connections[0];
	  }
	  getType() {
	    return this.type;
	  }
	  getViewClassification() {
	    return this.viewClassification;
	  }
	  getConnectStatus() {
	    return this.connected;
	  }
	  getSyncStatus() {
	    return this.status;
	  }
	  getStatus() {
	    if (this.getWizardSyncMode()) {
	      return 'synchronizing';
	    }
	    if (this.connected) {
	      return this.status ? "success" : "failed";
	    } else if (this.pendingStatus) {
	      return 'pending';
	    } else {
	      return 'not_connected';
	    }
	  }
	  getTemplateClass() {
	    return this.templateClass;
	  }
	  openSlider(options) {
	    BX.SidePanel.Instance.open(options.sliderId, {
	      contentCallback(slider) {
	        return new Promise((resolve, reject) => {
	          resolve(options.content);
	        });
	      },
	      data: options.data || {},
	      cacheable: options.cacheable,
	      width: this.SLIDER_WIDTH,
	      allowChangeHistory: false,
	      events: {
	        onLoad: event => {
	          this.itemSlider = event.getSlider();
	        }
	      }
	    });
	  }
	  closeSlider() {
	    if (this.itemSlider) {
	      this.itemSlider.close();
	    }
	  }
	  openInfoConnectionSlider() {
	    const content = this.getClassTemplateItem().createInstance(this).getInfoConnectionContent();
	    this.openSlider({
	      sliderId: 'calendar:item-sync-connect-' + this.type,
	      content: content,
	      cacheable: false,
	      data: {
	        provider: this
	      }
	    });
	  }
	  openActiveConnectionSlider(connection) {
	    const itemInterface = this.getClassTemplateItem().createInstance(this, connection);
	    if (this.type === 'google') {
	      itemInterface.getSectionsForGoogle().then(() => {
	        this.openActiveConnectionSliderVendor(itemInterface, connection);
	      });
	    } else if (this.type === 'icloud') {
	      itemInterface.getSectionsForIcloud().then(() => {
	        this.openActiveConnectionSliderVendor(itemInterface, connection);
	      });
	    } else if (this.type === 'office365') {
	      itemInterface.getSectionsForOffice365().then(() => {
	        this.openActiveConnectionSliderVendor(itemInterface, connection);
	      });
	    } else {
	      this.openActiveConnectionSliderVendor(itemInterface, connection);
	    }
	  }
	  openActiveConnectionSliderVendor(itemInterface, connection) {
	    const content = itemInterface.getActiveConnectionContent();
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
	  getClassTemplateItem() {
	    const itemClass = main_core.Reflection.getClass(this.getTemplateClass());
	    if (main_core.Type.isFunction(itemClass)) {
	      return itemClass;
	    }
	    return null;
	  }
	  getConnectionById(id) {
	    const connections = this.getConnections();
	    if (connections.length > 0) {
	      const result = connections.filter(connection => {
	        return connection.getId() == id;
	      });
	      if (result) {
	        return result[0];
	      }
	    }
	    return null;
	  }
	  getSyncPanelTitle() {
	    return this.gridTitle;
	  }
	  getSyncPanelLogo() {
	    return '--' + this.type;
	  }
	  setWizardSyncMode(value) {
	    this.wizardSyncMode = value;
	  }
	  getWizardSyncMode() {
	    return this.wizardSyncMode;
	  }
	  setWizardState(stateData) {
	    const wizard = this.getActiveWizard();
	    if (wizard) {
	      if (stateData.status === this.ERROR_CODE) {
	        wizard.setErrorState(stateData);
	      } else {
	        wizard.handleUpdateState(stateData);
	      }
	    }
	  }
	  setUserName(userName = '') {
	    this.userName = userName;
	    if (this.getConnection()) {
	      this.getConnection().setUserName(userName);
	    }
	  }
	  setActiveWizard(wizard) {
	    this.activeWizard = wizard;
	    wizard.subscribe('onConnectionCreated', this.handleCreatedConnection.bind(this));
	    wizard.subscribe('onClose', this.handleCloseWizard.bind(this));
	    wizard.subscribe('startWizardWaitingMode', this.startWaitingMode.bind(this));
	    wizard.subscribe('endWizardWaitingMode', this.endWaitingMode.bind(this));
	  }
	  getActiveWizard() {
	    return this.activeWizard || null;
	  }
	  startWaitingMode() {
	    this.emit('onStartWaitingMode');
	    this.waitingModeReserveTimeout = setTimeout(() => {
	      if (this.getActiveWizard() && this.getActiveWizard().getSlider()) {
	        BX.reload();
	      }
	    }, this.WAITING_MODE_MAX_TIME);
	  }
	  endWaitingMode() {
	    this.emit('onEndWaitingMode');
	    if (this.waitingModeReserveTimeout) {
	      clearTimeout(this.waitingModeReserveTimeout);
	      this.waitingModeReserveTimeout = null;
	    }
	  }
	  handleCreatedConnection() {
	    this.setStatus(this.STATUS_SUCCESS);
	    this.getInterfaceUnit().setSyncStatus(this.STATUS_SUCCESS);
	    BX.ajax.runAction('calendar.api.syncajax.clearSuccessfulConnectionNotifier', {
	      data: {
	        accountType: this.getType()
	      }
	    });

	    // TODO: It's better to avoid using of calendarContext.
	    //  Replace it with eventEmitter events and check for unnecessary requests
	    const calendarContext = calendar_util.Util.getCalendarContext();
	    if (calendarContext) {
	      calendarContext.syncInterface.refreshDebounce();
	    }
	  }
	  handleCloseWizard() {
	    const wizard = this.getActiveWizard();
	    this.setWizardSyncMode(false);
	    if (wizard && wizard.isSyncFinished()) {
	      this.setStatus(this.STATUS_SUCCESS);
	      this.getInterfaceUnit().setSyncStatus(this.STATUS_SUCCESS);
	    } else {
	      this.setStatus(this.STATUS_SYNCHRONIZING);
	      this.getInterfaceUnit().setSyncStatus(this.STATUS_SYNCHRONIZING);
	      BX.SidePanel.Instance.getOpenSliders().forEach(slider => {
	        if (['calendar:sync-slider', 'calendar:section-slider'].includes(slider.getUrl())) {
	          slider.close();
	        }
	      });
	    }
	    this.getInterfaceUnit().refreshButton();
	    this.emit('onEndWaitingMode');
	    this.emit('onCloseSyncWizard');
	    if (wizard) {
	      wizard.unsubscribeAll();
	    }
	  }
	  refresh(options) {
	    this.status = options.syncInfo.status || false;
	    this.connected = options.syncInfo.connected || false;
	    this.id = options.syncInfo.id || null;
	    if (options.syncLink) {
	      this.syncLink = options.syncLink;
	    }
	    this.setSyncDate(options.syncInfo.syncOffset);
	    this.setSections(options.sections);
	    this.clearConnections();
	    this.setConnections();
	  }
	}

	class GoogleProvider extends ConnectionProvider {
	  constructor(options) {
	    super({
	      id: options.syncInfo.id || null,
	      status: options.syncInfo.status || false,
	      connected: options.syncInfo.connected || false,
	      userName: options.syncInfo.userName || '',
	      gridTitle: main_core.Loc.getMessage('CALENDAR_TITLE_GOOGLE'),
	      gridColor: '#387ced',
	      gridIcon: '/bitrix/images/calendar/sync/google.svg',
	      type: 'google',
	      interfaceClassName: '',
	      viewClassification: 'web',
	      templateClass: 'BX.Calendar.Sync.Interface.GoogleTemplate',
	      mainPanel: options.mainPanel
	    });
	    this.connectionName = main_core.Loc.getMessage('CALENDAR_TITLE_GOOGLE');
	    this.isSetSyncGoogleSettings = options.isSetSyncGoogleSettings;
	    this.syncLink = options.syncLink;
	    this.setSyncDate(options.syncInfo.syncOffset);
	    this.setSections(options.sections);
	    this.setConnections();
	  }
	  getSyncLink() {
	    return this.syncLink;
	  }
	  hasSetSyncGoogleSettings() {
	    return this.isSetSyncGoogleSettings;
	  }
	  saveConnection() {
	    BX.ajax.runAction('calendar.api.syncajax.createGoogleConnection', {
	      data: {}
	    }).then(response => {
	      var _response$data;
	      if ((response == null ? void 0 : (_response$data = response.data) == null ? void 0 : _response$data.status) === this.ERROR_CODE) {
	        var _response$data2, _response$data2$googl;
	        this.setStatus(this.STATUS_FAILED);
	        this.setWizardState({
	          status: this.ERROR_CODE,
	          vendorName: this.type,
	          accountName: response == null ? void 0 : (_response$data2 = response.data) == null ? void 0 : (_response$data2$googl = _response$data2.googleApiStatus) == null ? void 0 : _response$data2$googl.googleCalendarPrimaryId
	        });
	      } else {
	        var _response$data3, _response$data3$googl;
	        this.setWizardState({
	          stage: 'connection_created',
	          vendorName: this.type,
	          accountName: response == null ? void 0 : (_response$data3 = response.data) == null ? void 0 : (_response$data3$googl = _response$data3.googleApiStatus) == null ? void 0 : _response$data3$googl.googleCalendarPrimaryId
	        });
	      }
	      this.emit('onSyncInfoUpdated', new main_core.Event.BaseEvent({
	        data: {
	          syncInfo: response.data.syncInfo
	        }
	      }));
	    }, response => {
	      this.setStatus(this.STATUS_FAILED);
	      this.setWizardState({
	        status: this.ERROR_CODE,
	        vendorName: this.type
	      });
	    });
	  }
	}

	class Office365Provider extends ConnectionProvider {
	  constructor(options) {
	    super({
	      id: options.syncInfo.id || null,
	      status: options.syncInfo.status || false,
	      connected: options.syncInfo.connected || false,
	      userName: options.syncInfo.userName || options.syncInfo.connectionName || '',
	      gridTitle: main_core.Loc.getMessage('CALENDAR_TITLE_OFFICE365'),
	      gridColor: '#fc1d1d',
	      gridIcon: '/bitrix/images/calendar/sync/office365.svg',
	      type: 'office365',
	      interfaceClassName: '',
	      viewClassification: 'web',
	      templateClass: 'BX.Calendar.Sync.Interface.Office365template',
	      mainPanel: true
	    });
	    this.connectionName = main_core.Loc.getMessage('CALENDAR_TITLE_OFFICE365');
	    this.syncLink = options.syncLink || '';
	    this.isSetSyncOffice365Settings = options.isSetSyncOffice365Settings;
	    this.setSyncDate(options.syncInfo.syncOffset);
	    this.setSections(options.sections);
	    this.setConnections();
	  }
	  getSyncLink() {
	    return this.syncLink;
	  }
	  hasSetSyncOffice365Settings() {
	    return this.isSetSyncOffice365Settings;
	  }
	  removeConnection(id) {
	    BX.ajax.runAction('calendar.api.syncajax.deactivateConnection', {
	      data: {
	        connectionId: id
	      }
	    }).then(() => {
	      BX.reload();
	    });
	  }
	}

	class ICloudProvider extends ConnectionProvider {
	  constructor(options) {
	    super({
	      id: options.syncInfo.id || null,
	      status: options.syncInfo.status || false,
	      connected: options.syncInfo.connected || false,
	      userName: options.syncInfo.userName || '',
	      gridTitle: main_core.Loc.getMessage('CALENDAR_TITLE_ICLOUD'),
	      gridColor: '#948f8f',
	      gridIcon: '/bitrix/images/calendar/sync/icloud.svg',
	      type: 'icloud',
	      interfaceClassName: '',
	      viewClassification: 'web',
	      templateClass: 'BX.Calendar.Sync.Interface.IcloudTemplate',
	      mainPanel: true
	    });
	    this.connectionName = main_core.Loc.getMessage('CALENDAR_TITLE_ICLOUD');
	    this.setSyncDate(options.syncInfo.syncOffset);
	    this.setSections(options.sections);
	    this.setConnections();
	  }
	}

	class AndroidProvider extends ConnectionProvider {
	  constructor(options) {
	    super({
	      status: options.syncInfo.status,
	      connected: options.syncInfo.connected,
	      gridTitle: main_core.Loc.getMessage('CALENDAR_TITLE_ANDROID'),
	      gridColor: '#9ece03',
	      gridIcon: '/bitrix/images/calendar/sync/android.svg',
	      type: 'android',
	      viewClassification: 'mobile',
	      templateClass: 'BX.Calendar.Sync.Interface.AndroidTemplate'
	    });
	    this.connectionName = main_core.Loc.getMessage('CALENDAR_TITLE_ANDROID');
	    this.setSyncDate(options.syncInfo.syncOffset);
	    this.setConnections();
	  }
	}

	class CaldavConnection extends ConnectionProvider {
	  constructor(options) {
	    super(options);
	  }
	  static calculateStatus(connections) {
	    if (connections.length === 0) {
	      return false;
	    }
	    for (let key in connections) {
	      if (this.isFailedConnections(connections[key])) {
	        return false;
	      }
	    }
	    return true;
	  }
	  static isFailedConnections(connection) {
	    if (connection.syncInfo.connected === true && connection.syncInfo.status === false) {
	      return true;
	    }
	    return false;
	  }
	  hasMenu() {
	    return this.connected;
	  }
	  showMenu(bindElement) {
	    if (this.menu) {
	      this.menu.destroy();
	    }
	    const menuItems = this.getMenuItems();
	    menuItems.push(...this.getMenuItemConnect());
	    this.menu = this.getMenu(bindElement, menuItems);
	    this.addMenuHandler();
	    this.menu.show();
	  }
	  addMenuHandler() {
	    if (this.menu) {
	      this.menu.getMenuContainer().addEventListener('click', () => {
	        this.menu.close();
	      });
	    }
	  }
	  getMenuItems() {
	    const menuItems = [];
	    this.connections.forEach(item => {
	      item.type = this.type;
	      item.id = item.addParams.id;
	      item.text = item.connectionName;
	      item.onclick = () => {
	        this.openActiveConnectionSlider(item);
	      };
	      menuItems.push(item);
	    });
	    return menuItems;
	  }
	  getMenuItemConnect() {
	    return [{
	      delimiter: true
	    }, {
	      id: 'connect',
	      text: main_core.Loc.getMessage('ADD_MENU_CONNECTION'),
	      onclick: () => {
	        this.openInfoConnectionSlider();
	      }
	    }];
	  }
	  getMenu(bindElement, menuItems) {
	    return new (window.top.BX || window.BX).Main.Menu({
	      className: 'calendar-sync-popup-status',
	      bindElement: bindElement,
	      items: menuItems,
	      width: this.MENU_WIDTH,
	      padding: this.MENU_PADDING,
	      zIndexAbsolute: this.MENU_INDEX,
	      autoHide: true,
	      closeByEsc: true,
	      id: this.getType() + '-menu',
	      offsetLeft: -40
	    });
	  }
	  setConnections() {
	    if (this.connectionsSyncInfo.length > 0) {
	      this.connectionsSyncInfo.forEach(connection => {
	        this.connections.push(ConnectionItem.createInstance({
	          connectionName: connection.syncInfo.connectionName,
	          status: connection.syncInfo.status,
	          connected: connection.syncInfo.connected,
	          addParams: {
	            sections: connection.sections,
	            id: connection.syncInfo.id,
	            userName: connection.syncInfo.userName,
	            server: connection.syncInfo.server
	          },
	          type: this.type
	        }));
	      });
	    }
	  }
	}

	class CaldavProvider extends CaldavConnection {
	  constructor(options) {
	    super({
	      status: options.status,
	      connected: options.connected,
	      gridTitle: main_core.Loc.getMessage('CALENDAR_TITLE_CALDAV'),
	      gridColor: '#1eae43',
	      gridIcon: '/bitrix/images/calendar/sync/caldav.svg',
	      type: 'caldav',
	      viewClassification: 'web',
	      templateClass: 'BX.Calendar.Sync.Interface.CaldavTemplate'
	    });
	    this.connectionsSyncInfo = options.connections;
	    if (options.connections && options.connections[0] && options.connections[0].syncInfo) {
	      this.setSyncDate(options.connections[0].syncInfo.syncOffset);
	    }
	    this.setConnections();
	  }
	}

	class ExchangeProvider extends ConnectionProvider {
	  constructor(options) {
	    super({
	      status: options.syncInfo.status || false,
	      connected: options.syncInfo.connected || false,
	      gridTitle: main_core.Loc.getMessage('CALENDAR_TITLE_EXCHANGE'),
	      gridColor: '#54d0df',
	      gridIcon: '/bitrix/images/calendar/sync/exchange.svg',
	      type: 'exchange',
	      viewClassification: 'web',
	      templateClass: 'BX.Calendar.Sync.Interface.ExchangeTemplate'
	    });
	    this.connectionName = main_core.Loc.getMessage('CALENDAR_TITLE_EXCHANGE');
	    this.setSyncDate(options.syncInfo.syncOffset);
	    this.setSections(options.sections);
	    this.setConnections();
	  }
	}

	class IphoneProvider extends ConnectionProvider {
	  constructor(options) {
	    super({
	      status: options.syncInfo.status,
	      connected: options.syncInfo.connected,
	      gridTitle: main_core.Loc.getMessage('CALENDAR_TITLE_IPHONE'),
	      gridColor: '#2fc6f6',
	      gridIcon: '/bitrix/images/calendar/sync/iphone.svg',
	      type: 'iphone',
	      viewClassification: 'mobile',
	      templateClass: 'BX.Calendar.Sync.Interface.IphoneTemplate'
	    });
	    this.connectionName = main_core.Loc.getMessage('CALENDAR_TITLE_IPHONE');
	    this.setSyncDate(options.syncInfo.syncOffset);
	    this.setConnections();
	  }
	}

	class MacProvider extends ConnectionProvider {
	  constructor(options) {
	    super({
	      status: options.syncInfo.status,
	      connected: options.syncInfo.connected,
	      gridTitle: main_core.Loc.getMessage('CALENDAR_TITLE_MAC'),
	      gridColor: '#ff5752',
	      gridIcon: '/bitrix/images/calendar/sync/mac.svg',
	      type: 'mac',
	      viewClassification: 'web',
	      templateClass: 'BX.Calendar.Sync.Interface.MacTemplate'
	    });
	    this.portalAddress = options.portalAddress;
	    this.connectionName = main_core.Loc.getMessage('CALENDAR_TITLE_MAC');
	    this.setSyncDate(options.syncInfo.syncOffset);
	    this.setConnections();
	  }
	  getPortalAddress() {
	    return this.portalAddress;
	  }
	}

	class OutlookProvider extends ConnectionProvider {
	  constructor(options) {
	    super({
	      status: options.syncInfo.status,
	      connected: options.syncInfo.connected,
	      gridTitle: main_core.Loc.getMessage('CALENDAR_TITLE_OUTLOOK'),
	      gridColor: '#ffa900',
	      gridIcon: '/bitrix/images/calendar/sync/outlook.svg',
	      type: 'outlook',
	      viewClassification: 'web',
	      templateClass: 'BX.Calendar.Sync.Interface.OutlookTemplate'
	    });
	    this.setSyncDate(options.syncInfo.syncOffset);
	    this.connectionName = main_core.Loc.getMessage('CALENDAR_TITLE_OUTLOOK');
	    this.sections = options.sections;
	    this.infoBySections = options.infoBySections;
	    this.setConnections();
	  }
	  hasMenu() {
	    return this.sections.length > 0;
	  }
	  showMenu(bindElement) {
	    if (this.hasMenu()) {
	      if (this.menu) {
	        this.menu.destroy();
	      }
	      const menuItems = this.getConnection().getSections();
	      menuItems.forEach(item => {
	        if (this.infoBySections[item.id]) {
	          item.className = 'calendar-sync-outlook-popup-item';
	        }
	        item.onclick = () => {
	          this.connectToOutlook(item);
	        };
	      });
	      this.menu = new (window.top.BX || window.BX).Main.Menu({
	        className: 'calendar-sync-popup-status',
	        bindElement: bindElement,
	        items: menuItems,
	        padding: 7,
	        autoHide: true,
	        closeByEsc: true,
	        zIndexAbsolute: 3020,
	        id: this.getType() + '-menu',
	        offsetLeft: -40
	      });
	      this.menu.getMenuContainer().addEventListener('click', () => {
	        this.menu.close();
	      });
	      this.menu.show();
	    }
	  }
	  connectToOutlook(section) {
	    if (section.id) {
	      BX.ajax.runAction('calendar.api.syncajax.getOutlookLink', {
	        data: {
	          id: section.id
	        }
	      }).then(response => {
	        const url = response.data.result;
	        eval(url);
	      });
	    }
	  }
	}

	class YandexProvider extends CaldavConnection {
	  constructor(options) {
	    super({
	      status: options.status,
	      connected: options.connected,
	      gridTitle: main_core.Loc.getMessage('CALENDAR_TITLE_YANDEX'),
	      gridColor: '#f9c500',
	      gridIcon: '/bitrix/images/calendar/sync/yandex.svg',
	      type: 'yandex',
	      viewClassification: 'web',
	      templateClass: 'BX.Calendar.Sync.Interface.YandexTemplate'
	    });
	    this.connectionName = main_core.Loc.getMessage('CALENDAR_TITLE_YANDEX');
	    this.connectionsSyncInfo = options.connections;
	    if (options.connections && options.connections[0] && options.connections[0].syncInfo) {
	      this.setSyncDate(options.connections[0].syncInfo.syncOffset);
	    }
	    this.setConnections();
	  }
	}

	class Manager extends main_core_events.EventEmitter {
	  constructor(options) {
	    super();
	    this.status = 'not_connected';
	    this.STATUS_SUCCESS = 'success';
	    this.STATUS_FAILED = 'failed';
	    this.STATUS_NOT_CONNECTED = 'not_connected';
	    this.WIZARD_SYNC_MODE = 'wizard_sync_mode';
	    this.STATUS_SYNCHRONIZING = 'synchronizing';
	    this.WAITING_MODE_PERIODIC_TIMEOUT = 5000;
	    this.REFRESH_DELAY = 300;
	    this.REFRESH_CONTENT_DELAY = 300;
	    this.WIZARD_SLIDER_PREFIX = 'calendar:sync-wizard';
	    this.setEventNamespace('BX.Calendar.Sync.Manager.Manager');
	    this.wrapper = options.wrapper;
	    this.setSyncInfo(options.syncInfo);
	    this.userId = options.userId;
	    this.syncLinks = options.syncLinks;
	    this.sections = options.sections;
	    this.portalAddress = options.portalAddress;
	    this.isRuZone = options.isRuZone;
	    this.calendarInstance = options.calendar;
	    this.isSetSyncGoogleSettings = options.isSetSyncGoogleSettings;
	    this.isSetSyncOffice365Settings = options.isSetSyncOffice365Settings;
	    this.refreshDebounce = main_core.Runtime.debounce(this.refresh, this.REFRESH_DELAY, this);
	    this.refreshContentDebounce = main_core.Runtime.debounce(this.refreshContent, this.REFRESH_CONTENT_DELAY, this);
	    this.init();
	    this.subscribeOnEvent();
	  }
	  subscribeOnEvent() {
	    main_core_events.EventEmitter.subscribe('BX.Calendar.Sync.Interface.SyncStatusPopup:onRefresh', event => {
	      this.refreshDebounce(event);
	    });
	    main_core_events.EventEmitter.subscribe('BX.Calendar.Sync.Interface.InterfaceTemplate:reDrawCalendarGrid', event => {
	      this.reDrawCalendarGrid();
	    });
	    window.addEventListener('message', event => {
	      if (event.data.title === 'googleOAuthSuccess') {
	        window.location.reload();
	      }
	    });
	  }
	  showSyncButton() {
	    this.syncButton = new SyncButton({
	      status: this.status,
	      wrapper: this.wrapper,
	      connectionsProviders: this.connectionsProviders,
	      userId: this.userId
	    });
	    this.syncButton.show();
	  }
	  init() {
	    this.connectionsProviders = {};
	    const yandexConnections = [];
	    const caldavConnections = [];
	    const syncInfo = this.syncInfo;
	    this.sectionsByType = this.sortSections();
	    for (let key in syncInfo) {
	      if (syncInfo.hasOwnProperty(key)) {
	        switch (syncInfo[key].type) {
	          case 'yandex':
	            yandexConnections.push({
	              syncInfo: syncInfo[key],
	              sections: this.sectionsByType.caldav['caldav' + syncInfo[key].id],
	              isRuZone: this.isRuZone
	            });
	            break;
	          case 'caldav':
	            caldavConnections.push({
	              syncInfo: syncInfo[key],
	              sections: this.sectionsByType.caldav['caldav' + syncInfo[key].id]
	            });
	            break;
	        }
	      }
	    }
	    this.connectionsProviders = {
	      google: this.getGoogleProvider(),
	      office365: this.getOffice365Provider(),
	      icloud: this.getIcloudProvider(),
	      caldav: this.getCaldavProvider(caldavConnections),
	      iphone: this.getIphoneProvider(),
	      android: this.getAndroidProvider(),
	      mac: this.getMacProvider()
	    };
	    if (this.isRuZone) {
	      this.connectionsProviders.yandex = this.getYandexProvider(yandexConnections);
	    }
	    if (!BX.browser.IsMac()) {
	      this.connectionsProviders.outlook = this.getOutlookProvider();
	    }
	    if (syncInfo.hasOwnProperty('exchange')) {
	      this.connectionsProviders.exchange = this.getExchangeProvider();
	    }
	    this.status = this.getSummarySyncStatus();
	    this.subscribeEventHandlers();
	  }
	  setSyncMode(value) {
	    this.syncMode = value;
	  }
	  getSyncMode() {
	    return this.syncMode;
	  }
	  isWizardSyncMode() {
	    for (let providerName in this.connectionsProviders) {
	      if (this.connectionsProviders.hasOwnProperty(providerName) && this.connectionsProviders[providerName].getWizardSyncMode()) {
	        return true;
	      }
	    }
	    return false;
	  }
	  isSyncInProcess() {
	    for (let providerName in this.connectionsProviders) {
	      if (this.connectionsProviders.hasOwnProperty(providerName) && this.connectionsProviders[providerName].getSyncStatus() === this.STATUS_SYNCHRONIZING) {
	        return true;
	      }
	    }
	    return false;
	  }
	  sortSections() {
	    const sections = this.sections;
	    const exchangeSections = [];
	    const googleSections = [];
	    const icloudSections = [];
	    const sectionsByType = {};
	    const outlookSections = [];
	    const office365Sections = [];
	    sectionsByType.caldav = {};
	    sections.forEach(section => {
	      if (section.belongsToView() && section.data.OUTLOOK_JS && section.data['EXTERNAL_TYPE'] === 'local') {
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
	      } else if (section.data['EXTERNAL_TYPE'] === 'icloud') {
	        icloudSections.push(section.data);
	      } else if (section.data['EXTERNAL_TYPE'] === 'office365') {
	        office365Sections.push(section.data);
	      } else if (section.data['CAL_DAV_CON'] && section.data['CAL_DAV_CAL']) {
	        sectionsByType.caldav['caldav' + section.data['CAL_DAV_CON']] = section.data;
	      }
	    });
	    sectionsByType.google = googleSections;
	    sectionsByType.icloud = icloudSections;
	    sectionsByType.office365 = office365Sections;
	    sectionsByType.exchange = exchangeSections;
	    sectionsByType.outlook = outlookSections;
	    return sectionsByType;
	  }
	  refresh(event) {
	    return new Promise(resolve => {
	      BX.ajax.runAction('calendar.api.syncajax.updateConnection', {
	        data: {
	          type: 'user',
	          requestUid: calendar_util.Util.registerRequestId()
	        }
	      }).then(response => {
	        this.setSyncInfo(response.data);
	        this.status = this.getSummarySyncStatus();
	        const activePopup = event && event.getTarget ? event.getTarget() : null;
	        this.refreshContent(activePopup);
	        resolve();
	      });
	    });
	  }
	  refreshContent(activePopup = {}) {
	    this.init();
	    this.refreshCalendarGrid();
	    if (this.syncButton) {
	      this.syncButton.refresh(this.status);
	      this.syncButton.setConnectionProviders(this.connectionsProviders);
	    }
	    if (activePopup) {
	      this.refreshActivePopup(activePopup);
	      this.refreshOpenSliders(activePopup);
	    }
	  }
	  refreshCalendarGrid() {
	    this.calendarInstance.reload();
	  }
	  refreshActivePopup(activePopup) {
	    if (activePopup instanceof SyncStatusPopup && activePopup.getId() === 'calendar-syncPanel-status') {
	      activePopup.refresh(this.getConnections());
	    } else if (this.syncButton.popup instanceof SyncStatusPopup && this.syncButton.popup.getId() === 'calendar-sync-button-status') {
	      this.syncButton.popup.refresh(this.getConnections());
	    }
	  }
	  refreshOpenSliders(activePopup = {}) {
	    const openSliders = BX.SidePanel.Instance.getOpenSliders();
	    if (openSliders.length > 0) {
	      openSliders.forEach(slider => {
	        if (slider.getUrl() === 'calendar:auxiliary-sync-slider') {
	          this.refreshMainSlider(this.syncButton.getSyncPanel());
	        } else if (slider.getUrl().indexOf('calendar:item-sync-') !== -1) {
	          this.refreshConnectionSlider(slider, activePopup);
	        }
	      });
	    }
	  }
	  refreshConnectionSlider(slider, activePopup) {
	    let updatedConnection = undefined;
	    const itemInterface = slider.getData().get('itemInterface');
	    const connection = slider.getData().get('connection');
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
	  refreshMainSlider(syncPanel) {
	    syncPanel.refresh(this.status, this.connectionsProviders);
	  }
	  getConnections() {
	    const connections = [];
	    const items = Object.values(this.connectionsProviders);
	    items.forEach(item => {
	      const itemConnections = item.getConnections();
	      if (itemConnections.length > 0) {
	        itemConnections.forEach(connection => {
	          if (connection.getConnectStatus() === true) {
	            connections.push(connection);
	          }
	        });
	      }
	    });
	    return connections;
	  }
	  reDrawCalendarGrid() {
	    this.calendarInstance.reloadDebounce();
	  }
	  updateSyncStatus(params) {
	    for (let connectionName in params.syncInfo) {
	      if (params.syncInfo.hasOwnProperty(connectionName) && this.syncInfo[connectionName]) {
	        this.syncInfo[connectionName] = {
	          ...this.syncInfo[connectionName],
	          ...params.syncInfo[connectionName]
	        };
	      }
	    }
	    this.status = this.STATUS_SUCCESS;
	    this.refreshContentDebounce();
	  }
	  addSyncConnection(params) {
	    for (const connectionName in params.syncInfo) {
	      if (['yandex', 'caldav'].includes(params.syncInfo[connectionName].type)) {
	        BX.reload();
	      }
	      if (BX.Calendar.Util.checkRequestId(params.requestUid)) {
	        if (this.syncInfo[connectionName]) {
	          this.syncInfo[connectionName] = {
	            ...this.syncInfo[connectionName],
	            ...params.syncInfo[connectionName]
	          };
	        }
	      }
	    }
	    this.status = this.STATUS_SUCCESS;
	    this.refreshContentDebounce();
	  }
	  deleteSyncConnection(params) {
	    if (!BX.Calendar.Util.checkRequestId(params.requestUid)) {
	      return;
	    }
	    if (params.connectionId) {
	      for (const connectionName in this.syncInfo) {
	        if (this.syncInfo.hasOwnProperty(connectionName) && this.syncInfo[connectionName] && parseInt(this.syncInfo[connectionName].id) === parseInt(params.connectionId)) {
	          delete this.syncInfo[connectionName];
	        }
	      }
	    }
	    if (params.syncInfo) {
	      for (const connectionName in params.syncInfo) {
	        if (this.syncInfo[connectionName]) {
	          delete this.syncInfo[connectionName];
	        }
	      }
	    }
	    if (this.status !== this.STATUS_NOT_CONNECTED) {
	      this.status = this.STATUS_SUCCESS;
	    }
	    this.refreshDebounce();
	  }
	  getProviderById(id) {
	    let connection;
	    for (let providerName in this.connectionsProviders) {
	      if (this.connectionsProviders.hasOwnProperty(providerName) && this.connectionsProviders[providerName].connected && ['google', 'caldav', 'yandex', 'icloud', 'office365'].includes(providerName)) {
	        connection = this.connectionsProviders[providerName].getConnectionById(id);
	        if (connection) {
	          return [this.connectionsProviders[providerName], connection];
	        }
	      }
	    }
	    return [undefined, undefined];
	  }
	  processSyncConnection(params) {
	    for (let providerName in this.connectionsProviders) {
	      if (this.connectionsProviders.hasOwnProperty(providerName) && this.connectionsProviders[providerName].getWizardSyncMode() && providerName === (params == null ? void 0 : params.vendorName)) {
	        if (params.accountName) {
	          this.connectionsProviders[providerName].setUserName(params.accountName);
	        }
	        this.connectionsProviders[providerName].setWizardState(params);
	        break;
	      }
	    }
	  }
	  handlePullEvent(params) {
	    let wizardSyncMode = this.isWizardSyncMode();
	    switch (params.command) {
	      case 'refresh_sync_status':
	        if (!wizardSyncMode) {
	          this.updateSyncStatus(params);
	        }
	        break;
	      case 'add_sync_connection':
	        if (!wizardSyncMode) {
	          this.addSyncConnection(params);
	        }
	        break;
	      case 'delete_sync_connection':
	        if (!wizardSyncMode) {
	          this.deleteSyncConnection(params);
	        }
	        break;
	      case 'process_sync_connection':
	        if (wizardSyncMode) {
	          this.processSyncConnection(params);
	        }
	        break;
	    }
	  }
	  setSyncInfo(syncInfo) {
	    this.syncInfo = syncInfo;
	  }
	  subscribeEventHandlers() {
	    for (let providerName in this.connectionsProviders) {
	      if (this.connectionsProviders.hasOwnProperty(providerName)) {
	        this.connectionsProviders[providerName].unsubscribeAll('onStartWaitingMode');
	        this.connectionsProviders[providerName].unsubscribeAll('onEndWaitingMode');
	        this.connectionsProviders[providerName].unsubscribeAll('onCloseSyncWizard');
	        this.connectionsProviders[providerName].subscribe('onStartWaitingMode', this.handleStartWaitingMode.bind(this));
	        this.connectionsProviders[providerName].subscribe('onEndWaitingMode', this.handleEndWaitingMode.bind(this));
	        this.connectionsProviders[providerName].subscribe('onCloseSyncWizard', this.handleCloseSyncWizard.bind(this));
	      }
	    }
	  }
	  handleCloseSyncWizard() {
	    if (this.isSyncInProcess()) {
	      if (this.syncButton) {
	        this.syncButton.refresh(this.STATUS_SYNCHRONIZING);
	      }
	    } else {
	      this.refreshContentDebounce();
	    }
	  }
	  handleStartWaitingMode() {
	    this.doPeriodicRefresh();
	  }
	  handleEndWaitingMode() {
	    this.stopPeriodicRefresh();
	  }
	  doPeriodicRefresh() {
	    if (!this.hasOpenedWizard()) {
	      return;
	    }
	    if (calendar_util.Util.documentIsDisplayingNow()) {
	      this.refresh().then(() => {
	        this.refreshTimeout = setTimeout(this.doPeriodicRefresh.bind(this), this.WAITING_MODE_PERIODIC_TIMEOUT);
	      });
	    } else {
	      this.refreshTimeout = setTimeout(this.doPeriodicRefresh.bind(this), this.WAITING_MODE_PERIODIC_TIMEOUT);
	    }
	  }
	  stopPeriodicRefresh() {
	    if (this.refreshTimeout) {
	      clearInterval(this.refreshTimeout);
	      this.refreshTimeout = null;
	    }
	  }
	  openSyncPanel() {
	    this.syncButton.handleClick();
	  }
	  getSummarySyncStatus() {
	    let status = this.STATUS_NOT_CONNECTED;
	    for (let providerName in this.connectionsProviders) {
	      if (this.connectionsProviders.hasOwnProperty(providerName)) {
	        if ([this.STATUS_SUCCESS, this.STATUS_FAILED].includes(this.connectionsProviders[providerName].getStatus())) {
	          status = this.connectionsProviders[providerName].getStatus();
	          break;
	        }
	      }
	    }
	    return status;
	  }
	  getGoogleProvider() {
	    if (!this.googleProvider) {
	      this.googleProvider = GoogleProvider.createInstance({
	        syncInfo: this.syncInfo.google || {},
	        sections: this.sectionsByType.google || {},
	        syncLink: this.syncLinks.google || null,
	        isSetSyncGoogleSettings: this.isSetSyncGoogleSettings,
	        mainPanel: true
	      });
	    } else {
	      this.googleProvider.refresh({
	        syncInfo: this.syncInfo.google || {},
	        sections: this.sectionsByType.google || {},
	        syncLink: this.syncLinks.google || null
	      });
	    }
	    return this.googleProvider;
	  }
	  getOffice365Provider() {
	    if (!this.office365Provider) {
	      this.office365Provider = Office365Provider.createInstance({
	        syncInfo: this.syncInfo.office365 || {},
	        sections: this.sectionsByType.office365 || {},
	        syncLink: this.syncLinks.office365 || null,
	        isSetSyncOffice365Settings: this.isSetSyncOffice365Settings,
	        mainPanel: true
	      });
	    } else {
	      this.office365Provider.refresh({
	        syncInfo: this.syncInfo.office365 || {},
	        sections: this.sectionsByType.office365 || {},
	        syncLink: this.syncLinks.office365 || null
	      });
	    }
	    return this.office365Provider;
	  }
	  getIcloudProvider() {
	    if (!this.icloudProvider) {
	      this.icloudProvider = ICloudProvider.createInstance({
	        syncInfo: this.syncInfo.icloud || {},
	        sections: this.sectionsByType.icloud || {},
	        mainPanel: true
	      });
	    } else {
	      this.icloudProvider.refresh({
	        syncInfo: this.syncInfo.icloud || {},
	        sections: this.sectionsByType.icloud || {}
	      });
	    }
	    return this.icloudProvider;
	  }
	  getCaldavProvider(caldavConnections) {
	    return CaldavProvider.createInstance({
	      status: CaldavConnection.calculateStatus(caldavConnections),
	      connected: caldavConnections.length > 0,
	      connections: caldavConnections
	    });
	  }
	  getIphoneProvider() {
	    return IphoneProvider.createInstance({
	      syncInfo: this.syncInfo.iphone
	    });
	  }
	  getAndroidProvider() {
	    return AndroidProvider.createInstance({
	      syncInfo: this.syncInfo.android
	    });
	  }
	  getMacProvider() {
	    return MacProvider.createInstance({
	      syncInfo: this.syncInfo.mac,
	      portalAddress: this.portalAddress
	    });
	  }
	  getYandexProvider(yandexConnections) {
	    return YandexProvider.createInstance({
	      status: CaldavConnection.calculateStatus(yandexConnections),
	      connected: yandexConnections.length > 0,
	      connections: yandexConnections
	    });
	  }
	  getOutlookProvider() {
	    return OutlookProvider.createInstance({
	      syncInfo: this.syncInfo.outlook,
	      sections: this.sectionsByType.outlook,
	      infoBySections: this.syncInfo.outlook.infoBySections || {}
	    });
	  }
	  getExchangeProvider() {
	    return ExchangeProvider.createInstance({
	      syncInfo: this.syncInfo.exchange,
	      sections: this.sectionsByType.exchange
	    });
	  }
	  hasOpenedWizard() {
	    const sliderList = BX.SidePanel.Instance.getOpenSliders();
	    for (let i in sliderList) {
	      if (sliderList.hasOwnProperty(i) && sliderList[i].getUrl().indexOf(this.WIZARD_SLIDER_PREFIX) !== -1) {
	        return true;
	      }
	    }
	    return false;
	  }
	}

	exports.Manager = Manager;
	exports.SyncButton = SyncButton;
	exports.SyncStatusPopup = SyncStatusPopup;
	exports.ConnectionItem = ConnectionItem;

}((this.BX.Calendar.Sync.Manager = this.BX.Calendar.Sync.Manager || {}),BX.Main,BX.Event,BX.Calendar,BX));
//# sourceMappingURL=manager.bundle.js.map
