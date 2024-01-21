this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
this.BX.Calendar.Sync = this.BX.Calendar.Sync || {};
(function (exports,ui_designTokens,ui_fonts_opensans,calendar_sync_manager,calendar_entry,ui_tilegrid,ui_forms,main_core_events,ui_dialogs_messagebox,main_core,calendar_util,main_popup) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5;
	class StatusBlock {
	  constructor(options) {
	    this.status = options.status;
	    this.connections = options.connections;
	    this.withStatusLabel = options.withStatusLabel;
	    this.popupWithUpdateButton = options.popupWithUpdateButton;
	    this.popupId = options.popupId;
	  }
	  static createInstance(options) {
	    return new this(options);
	  }
	  setStatus(status) {
	    this.status = status;
	    return this;
	  }
	  setConnections(connections) {
	    this.connections = connections;
	    return this;
	  }
	  getContent() {
	    let statusInfoBlock;
	    if (this.status === 'success') {
	      statusInfoBlock = main_core.Tag.render(_t || (_t = _`
				<div id="status-info-block" class="ui-alert ui-alert-success calendar-sync-status-info">
					<span class="ui-alert-message">${0}</span>
				</div>
			`), main_core.Loc.getMessage('SYNC_STATUS_SUCCESS'));
	    } else if (this.status === 'failed') {
	      statusInfoBlock = main_core.Tag.render(_t2 || (_t2 = _`
				<div id="status-info-block" class="ui-alert ui-alert-danger calendar-sync-status-info">
					<span class="ui-alert-message">${0}</span>
				</div>
			`), main_core.Loc.getMessage('SYNC_STATUS_ALERT'));
	    } else {
	      statusInfoBlock = main_core.Tag.render(_t3 || (_t3 = _`
				<div id="status-info-block" class="ui-alert ui-alert-primary calendar-sync-status-info">
					<span class="ui-alert-message">${0}</span>
				</div>
			`), main_core.Loc.getMessage('SYNC_STATUS_NOT_CONNECTED'));
	    }
	    statusInfoBlock.addEventListener('mouseenter', () => {
	      this.handlerMouseEnter(statusInfoBlock);
	    });
	    statusInfoBlock.addEventListener('mouseleave', () => {
	      this.handlerMouseLeave();
	    });
	    this.statusBlock = main_core.Tag.render(_t4 || (_t4 = _`
			<div class="calendar-sync-status-block" id="calendar-sync-status-block">
				${0}
				${0}
			</div>
		`), this.getStatusTextLabel(), statusInfoBlock);
	    return this.statusBlock;
	  }
	  getStatusTextLabel() {
	    return this.withStatusLabel ? main_core.Tag.render(_t5 || (_t5 = _`
				<div class="calendar-sync-status-subtitle">
					<span data-hint=""></span>
					<span class="calendar-sync-status-text">${0}:</span>
				</div>`), main_core.Loc.getMessage('LABEL_STATUS_INFO')) : '';
	  }
	  handlerMouseEnter(statusBlock) {
	    clearTimeout(this.statusBlockEnterTimeout);
	    this.buttonEnterTimeout = setTimeout(() => {
	      this.statusBlockEnterTimeout = null;
	      this.showPopup(statusBlock);
	    }, 500);
	  }
	  handlerMouseLeave() {
	    if (this.statusBlockEnterTimeout !== null) {
	      clearTimeout(this.statusBlockEnterTimeout);
	      this.statusBlockEnterTimeout = null;
	      return;
	    }
	    this.statusBlockLeaveTimeout = setTimeout(() => {
	      this.hidePopup();
	    }, 500);
	  }
	  showPopup(node) {
	    if (this.status !== 'not_connected') {
	      this.popup = this.getPopup(node);
	      this.popup.show();
	      this.addPopupHandlers();
	    }
	  }
	  hidePopup() {
	    if (this.popup) {
	      this.popup.hide();
	    }
	  }
	  addPopupHandlers() {
	    this.popup.getPopup().getPopupContainer().addEventListener('mouseenter', () => {
	      clearTimeout(this.statusBlockEnterTimeout);
	      clearTimeout(this.statusBlockLeaveTimeout);
	    });
	    this.popup.getPopup().getPopupContainer().addEventListener('mouseleave', () => {
	      this.hidePopup();
	    });
	  }
	  getPopup(node) {
	    return calendar_sync_manager.SyncStatusPopup.createInstance({
	      connections: this.connections,
	      withUpdateButton: this.popupWithUpdateButton,
	      node: node,
	      id: this.popupId
	    });
	  }
	  refresh(status, connections) {
	    this.status = status;
	    this.connections = connections;
	    return this;
	  }
	}

	let _$1 = t => t,
	  _t$1,
	  _t2$1,
	  _t3$1,
	  _t4$1,
	  _t5$1,
	  _t6,
	  _t7;
	class AuxiliarySyncPanel {
	  constructor(options) {
	    this.MAIN_SYNC_SLIDER_NAME = 'calendar:auxiliary-sync-slider';
	    this.SLIDER_WIDTH = 684;
	    this.LOADER_NAME = "calendar:loader";
	    this.cache = new main_core.Cache.MemoryCache();
	    this.status = options.status;
	    this.connectionsProviders = options.connectionsProviders;
	    this.userId = options.userId;
	    this.statusBlockEnterTimeout = null;
	    this.statusBlockLeaveTimeout = null;
	  }
	  openSlider() {
	    BX.SidePanel.Instance.open(this.MAIN_SYNC_SLIDER_NAME, {
	      contentCallback: slider => {
	        return new Promise((resolve, reject) => {
	          resolve(this.getContent());
	        });
	      },
	      allowChangeHistory: false,
	      events: {
	        onLoad: () => {
	          this.setGridContent();
	        }
	        // onMessage: (event) => {
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
	  getContent() {
	    return main_core.Tag.render(_t$1 || (_t$1 = _$1`
			<div class="calendar-sync-wrap">
				${0}
				${0}
				${0}
				${0}
				${0}
			</div>
		`), this.getHeader(), this.getMobileHeader(), this.getMobileContentWrapper(), this.getWebHeader(), this.getWebContentWrapper());
	  }
	  getHeader() {
	    return main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
			<div class="calendar-sync-header">
				${0}
				${0}
			</div>
		`), this.getMainHeader(), this.getStatusBlockContent(this.getConnections()));
	  }
	  getMainHeader() {
	    return this.cache.remember('calendar-syncPanel-mainHeader', () => {
	      return main_core.Tag.render(_t3$1 || (_t3$1 = _$1`
				<span class="calendar-sync-header-text">${0}</span>
			`), main_core.Loc.getMessage('SYNC_CALENDAR_HEADER_NEW'));
	    });
	  }
	  getMobileContentWrapper() {
	    return this.cache.remember('calendar-syncPanel-mobileContentWrapper', () => {
	      return main_core.Tag.render(_t4$1 || (_t4$1 = _$1`
			<div id="calendar-sync-mobile" class="calendar-sync-mobile"></div>
		`));
	    });
	  }
	  getWebContentWrapper() {
	    return this.cache.remember('calendar-syncPanel-webContentWrapper', () => {
	      return main_core.Tag.render(_t5$1 || (_t5$1 = _$1`
				<div id="calendar-sync-web" class="calendar-sync-web"></div>
			`));
	    });
	  }
	  getMobileHeader() {
	    return this.cache.remember('calendar-syncPanel-mobileHeader', () => {
	      return main_core.Tag.render(_t6 || (_t6 = _$1`
				<div class="calendar-sync-title">${0}</div>
			`), main_core.Loc.getMessage('SYNC_MOBILE_HEADER'));
	    });
	  }
	  getWebHeader() {
	    return this.cache.remember('calendar-syncPanel-webHeader', () => {
	      return main_core.Tag.render(_t7 || (_t7 = _$1`
				<div class="calendar-sync-title">${0}</div>
		`), main_core.Loc.getMessage('SYNC_WEB_HEADER'));
	    });
	  }
	  getStatusBlockContent(connections) {
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
	  getConnections() {
	    const connections = [];
	    const items = Object.values(this.connectionsProviders);
	    items.forEach(item => {
	      const itemConnections = item.getConnections();
	      if (itemConnections.length > 0) {
	        itemConnections.forEach(connection => {
	          if (calendar_sync_manager.ConnectionItem.isConnectionItem(connection) && connection.getConnectStatus() === true) {
	            connections.push(connection);
	          }
	        });
	      }
	    });
	    return connections;
	  }
	  setGridContent() {
	    const items = Object.values(this.connectionsProviders);
	    this.showWebGridContent(items.filter(item => {
	      return item.mainPanel === false && item.getViewClassification() === 'web';
	    }));
	    this.showMobileGridContent(items.filter(item => {
	      return item.mainPanel === false && item.getViewClassification() === 'mobile';
	    }));
	  }
	  showWebGridContent(items) {
	    const wrapper = this.getWebContentWrapper();
	    main_core.Dom.clean(wrapper);
	    const grid = new BX.TileGrid.Grid({
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
	  showMobileGridContent(items) {
	    const wrapper = this.getMobileContentWrapper();
	    main_core.Dom.clean(wrapper);
	    const grid = new BX.TileGrid.Grid({
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
	  refresh(status, connectionsProviders) {
	    this.status = status;
	    this.connectionsProviders = connectionsProviders;
	    this.blockStatusContent = this.statusBlock.refresh(status, this.getConnections()).getContent();
	    main_core.Dom.replace(document.querySelector('#calendar-sync-status-block'), this.blockStatusContent);
	    this.setGridContent();
	  }
	}

	let _$2 = t => t,
	  _t$2,
	  _t2$2,
	  _t3$2,
	  _t4$2,
	  _t5$2,
	  _t6$1,
	  _t7$1,
	  _t8,
	  _t9,
	  _t10;
	class SyncPanelUnit {
	  constructor(options) {
	    this.logoClassName = '';
	    this.options = options;
	    this.connectionProvider = this.options.connectionProvider;
	  }
	  getConnectionTemplate() {
	    if (!this.connectionTemplate) {
	      this.connectionTemplate = this.connectionProvider.getClassTemplateItem().createInstance(this.connectionProvider);
	    }
	    return this.connectionTemplate;
	  }
	  renderTo(outerWrapper) {
	    if (main_core.Type.isElementNode(outerWrapper)) {
	      outerWrapper.appendChild(this.getContent());
	    }
	  }
	  getContent() {
	    this.unitNode = main_core.Tag.render(_t$2 || (_t$2 = _$2`
			<div class="calendar-sync__calendar-item">
				<div class="calendar-sync__calendar-item--logo">
					${0}
				</div>
				<div class="calendar-sync__calendar-item--container">
					<div class="calendar-sync__calendar-item--title">
						${0}
						${0}
					</div>
					${0}
				</div>
			</div>
		`), this.getLogoNode(), this.getTitle(), this.getSyncInfoWrap(), this.getButtonsWrap());
	    return this.unitNode;
	  }
	  getLogoNode() {
	    return main_core.Tag.render(_t2$2 || (_t2$2 = _$2`<div class="calendar-sync__calendar-item--logo-image ${0}"></div>`), this.connectionProvider.getSyncPanelLogo());
	  }
	  getTitle() {
	    return this.connectionProvider.getSyncPanelTitle();
	  }
	  getSyncInfoWrap() {
	    this.syncInfoWrap = main_core.Tag.render(_t3$2 || (_t3$2 = _$2`
			<div class="calendar-sync__account-info">
				<div class="calendar-sync__account-info--icon --animate"></div>
				<span data-role="sync_info_text" />
			</div>
		`));
	    return this.syncInfoWrap;
	  }
	  setSyncStatus(mode) {
	    this.unitNode.className = 'calendar-sync__calendar-item';
	    switch (mode) {
	      case this.connectionProvider.STATUS_REFUSED:
	        main_core.Dom.addClass(this.unitNode, '--refused');
	        this.setSyncInfoStatusText(main_core.Loc.getMessage('CAL_SYNC_INFO_STATUS_REFUSED'), false);
	        break;
	      case this.connectionProvider.STATUS_SUCCESS:
	        main_core.Dom.addClass(this.unitNode, '--complete');
	        this.setSyncInfoStatusText(this.formatSyncTime(this.connectionProvider.getSyncDate()));
	        break;
	      case this.connectionProvider.STATUS_FAILED:
	        main_core.Dom.addClass(this.unitNode, '--error');
	        this.setSyncInfoStatusText(main_core.Loc.getMessage('CAL_SYNC_INFO_STATUS_ERROR'));
	        break;
	      case this.connectionProvider.STATUS_PENDING:
	        main_core.Dom.addClass(this.unitNode, '--pending');
	        this.setSyncInfoStatusText('');
	        break;
	      case this.connectionProvider.STATUS_SYNCHRONIZING:
	        main_core.Dom.addClass(this.unitNode, '--active');
	        this.setSyncInfoStatusText(main_core.Loc.getMessage('CAL_SYNC_INFO_STATUS_SYNCHRONIZING'));
	        break;
	      case this.connectionProvider.STATUS_NOT_CONNECTED:
	        if (this.connectionProvider.isGoogleApplicationRefused) {
	          main_core.Dom.addClass(this.unitNode, '--off');
	          this.setSyncInfoStatusText(main_core.Loc.getMessage('CAL_SYNC_INFO_STATUS_REFUSED'), false);
	        } else {
	          this.setSyncInfoStatusText('');
	        }
	        break;
	    }
	  }
	  setSyncInfoStatusText(text, upperCase = true) {
	    const syncInfoStatusText = this.syncInfoWrap.querySelector('[data-role="sync_info_text"]');
	    if (main_core.Type.isElementNode(syncInfoStatusText)) {
	      syncInfoStatusText.innerHTML = upperCase ? main_core.Text.encode(text).toUpperCase() : main_core.Text.encode(text);
	    }
	  }
	  getButtonsWrap() {
	    if (!main_core.Type.isElementNode(this.buttonsWrap)) {
	      this.buttonsWrap = main_core.Tag.render(_t4$2 || (_t4$2 = _$2`<div class="calendar-sync__calendar-item--buttons">
				${0}
				${0}
			</div>`), this.getButton(), this.getMoreButton());
	    }
	    return this.buttonsWrap;
	  }
	  refreshButton() {
	    main_core.Dom.clean(this.buttonsWrap);
	    this.button = this.buttonsWrap.appendChild(this.getButton());
	    this.moreButton = this.buttonsWrap.appendChild(this.getMoreButton());
	  }
	  getButton() {
	    if (this.connectionProvider.isGoogleApplicationRefused) {
	      return null;
	    }
	    switch (this.connectionProvider.getStatus()) {
	      case this.connectionProvider.STATUS_SUCCESS:
	        this.button = main_core.Tag.render(_t5$2 || (_t5$2 = _$2`
					<a data-role="status-success" class="ui-btn ui-btn-icon-success ui-btn-link">
						${0}
					</a>`), main_core.Loc.getMessage('CAL_BUTTON_STATUS_SUCCESS'));
	        break;
	      case this.connectionProvider.STATUS_FAILED:
	        this.button = main_core.Tag.render(_t6$1 || (_t6$1 = _$2`
					<a data-role="status-failed" class="ui-btn ui-btn-icon-fail ui-btn-link">
						${0}
					</a>`), main_core.Loc.getMessage('CAL_BUTTON_STATUS_FAILED'));
	        break;
	      case this.connectionProvider.STATUS_PENDING:
	        this.button = main_core.Tag.render(_t7$1 || (_t7$1 = _$2`
					<a data-role="status-pending" class="ui-btn ui-btn-disabled ui-btn-link">
						${0}
					</a>`), main_core.Loc.getMessage('CAL_BUTTON_STATUS_PENDING'));
	        break;
	      case this.connectionProvider.STATUS_NOT_CONNECTED:
	        this.button = main_core.Tag.render(_t8 || (_t8 = _$2`
					<a data-role="status-not_connected" class="ui-btn ui-btn-success ui-btn-round">
						${0}
					</a>`), main_core.Loc.getMessage('CAL_BUTTON_STATUS_NOT_CONNECTED'));
	        main_core.Event.bind(this.button, 'click', this.handleItemClick.bind(this));
	        break;
	      case this.connectionProvider.STATUS_SYNCHRONIZING:
	        this.button = main_core.Tag.render(_t9 || (_t9 = _$2`
					<a data-role="status-not_connected" class="ui-btn ui-btn-success ui-btn-round ui-btn-clock ui-btn-disabled">
						${0}
					</a>`), main_core.Loc.getMessage('CAL_BUTTON_STATUS_SUCCESS'));
	        break;
	    }
	    return this.button;
	  }
	  getMoreButton() {
	    this.moreButton = main_core.Tag.render(_t10 || (_t10 = _$2`
			<div
				data-role="more-button" 
				class="ui-btn ui-btn-round ui-btn-light-border calendar-sync__calendar-item--more"
			></div>
		`));
	    main_core.Event.bind(this.moreButton, 'click', this.handleItemClick.bind(this));
	    return this.moreButton;
	  }
	  handleItemClick(e) {
	    const status = this.connectionProvider.getStatus();
	    if ([this.connectionProvider.STATUS_SUCCESS, this.connectionProvider.STATUS_FAILED, this.connectionProvider.STATUS_REFUSED].includes(status)) {
	      if (this.connectionProvider.hasMenu()) {
	        this.connectionProvider.showMenu(this.button);
	      } else if (this.connectionProvider.getConnectStatus()) {
	        this.connectionProvider.openActiveConnectionSlider(this.connectionProvider.getConnection());
	      } else {
	        this.connectionProvider.openInfoConnectionSlider();
	      }
	    } else if (status === this.connectionProvider.STATUS_NOT_CONNECTED) {
	      this.getConnectionTemplate().handleConnectButton();
	    }
	  }
	  formatSyncTime(date) {
	    const now = new Date();
	    let timestamp = date;
	    if (main_core.Type.isDate(date)) {
	      timestamp = Math.round(date.getTime() / 1000);
	      const secondsAgo = parseInt((now - date) / 1000);
	      if (secondsAgo < 60) {
	        return main_core.Loc.getMessage('CAL_JUST');
	      }
	    }
	    return BX.date.format([["tommorow", "tommorow, H:i:s"], ["i", "iago"], ["H", "Hago"], ["d", "dago"], ["m100", "mago"], ["m", "mago"], ["-", ""]], timestamp);
	  }
	}

	let _$3 = t => t,
	  _t$3,
	  _t2$3,
	  _t3$3,
	  _t4$3,
	  _t5$3,
	  _t6$2,
	  _t7$2;
	class SyncPanel {
	  constructor(options) {
	    this.MAIN_SYNC_SLIDER_NAME = 'calendar:sync-slider';
	    this.HELPDESK_CODE = 11828176;
	    this.SLIDER_WIDTH = 770;
	    this.LOADER_NAME = "calendar:loader";
	    this.cache = new main_core.Cache.MemoryCache();
	    this.status = options.status;
	    this.connectionsProviders = options.connectionsProviders;
	    this.userId = options.userId;
	    this.BX = window.top.BX || window.BX;
	  }
	  openSlider() {
	    BX.SidePanel.Instance.open(this.MAIN_SYNC_SLIDER_NAME, {
	      contentCallback: slider => {
	        return new Promise((resolve, reject) => {
	          resolve(this.getContent());
	        });
	      },
	      allowChangeHistory: false,
	      events: {
	        onLoad: () => {
	          this.displayConnectionUnits();
	        }
	      },
	      cacheable: false,
	      width: this.SLIDER_WIDTH,
	      loader: this.LOADER_NAME
	    });
	  }
	  getContent() {
	    return main_core.Tag.render(_t$3 || (_t$3 = _$3`
			<div class="calendar-sync__wrapper calendar-sync__scope">
				${0}
				<div class="calendar-sync__content">
				${0}
				${0}
				</div>
			</div>
		`), this.getHeaderWrapper(), this.getUnitsContentWrapper(), this.getFooterWrapper());
	  }
	  getHeaderWrapper() {
	    return main_core.Tag.render(_t2$3 || (_t2$3 = _$3`
			<div class="calendar-sync__header">
				<div class="calendar-sync__header-logo"></div>
				<div class="calendar-sync__header-container">
					<div class="calendar-sync__header-title">${0}</div>
					<div class="calendar-sync__header-sub-title">${0}</div>
				</div>
			</div>
		`), main_core.Loc.getMessage('CAL_SYNC_TITLE_NEW'), main_core.Loc.getMessage('CAL_SYNC_SUB_TITLE'));
	  }
	  getUnitsContentWrapper() {
	    this.unitsContentWrapper = main_core.Tag.render(_t3$3 || (_t3$3 = _$3`
			<div class="calendar-sync__calendar-list">
			</div>
		`));
	    return this.unitsContentWrapper;
	  }
	  getFooterWrapper() {
	    return main_core.Tag.render(_t4$3 || (_t4$3 = _$3`
			<div class="calendar-sync__content-block --space-bottom --space-left">
				${0}
			</div>
			<div class="calendar-sync__content-block --space-bottom --space-left--double">
				${0}
			</div>
			<div class="calendar-sync__content-block --space-left--double">
				${0}
			</div>
		`), this.getExtraInfoWithCheckIcon(), this.getOpenAuxiliaryPanelLink(), this.getOpenHelpLink());
	  }
	  getExtraInfoWithCheckIcon() {
	    const alreadyConnected = Object.values(this.connectionsProviders).filter(item => {
	      return item.mainPanel && item.status;
	    }).length > 0;
	    return main_core.Tag.render(_t5$3 || (_t5$3 = _$3`
			<div class="calendar-sync__content-text --icon-check${0}">
				${0}
			</div>
		`), alreadyConnected ? ' --disabled' : '', main_core.Loc.getMessage('CAL_SYNC_INFO_PROMO'));
	  }
	  getOpenAuxiliaryPanelLink() {
	    const link = main_core.Tag.render(_t6$2 || (_t6$2 = _$3`
			<div class="calendar-sync__content-link">
				${0}
			</div>
		`), main_core.Loc.getMessage('CAL_OPEN_AUXILIARY_PANEL'));
	    main_core.Event.bind(link, 'click', () => {
	      this.auxiliarySyncPanel = new AuxiliarySyncPanel({
	        connectionsProviders: this.connectionsProviders,
	        userId: this.userId,
	        status: this.status
	      });
	      this.auxiliarySyncPanel.openSlider();
	    });
	    return link;
	  }
	  getOpenHelpLink() {
	    const link = main_core.Tag.render(_t7$2 || (_t7$2 = _$3`
			<div class="calendar-sync__content-link">${0}</divclass>
		`), main_core.Loc.getMessage('CAL_SHOW_SYNC_HELP'));
	    main_core.Event.bind(link, 'click', () => {
	      if (this.BX.Helper) {
	        this.BX.Helper.show("redirect=detail&code=" + this.HELPDESK_CODE);
	      }
	    });
	    return link;
	  }
	  getConnections() {
	    const connections = [];
	    const items = Object.values(this.connectionsProviders);
	    items.forEach(item => {
	      const itemConnections = item.getConnections();
	      if (itemConnections.length > 0) {
	        itemConnections.forEach(connection => {
	          if (calendar_sync_manager.ConnectionItem.isConnectionItem(connection) && connection.getConnectStatus() === true) {
	            connections.push(connection);
	          }
	        });
	      }
	    });
	    return connections;
	  }
	  displayConnectionUnits() {
	    const items = Object.values(this.connectionsProviders).filter(item => {
	      return item.mainPanel || item.connected;
	    });
	    this.renderConnectionUnits(items);
	  }
	  renderConnectionUnits(providers) {
	    main_core.Dom.clean(this.unitsContentWrapper);
	    providers.forEach(provider => {
	      const interfaceUnit = new SyncPanelUnit({
	        connectionProvider: provider
	      });
	      provider.setInterfaceUnit(interfaceUnit);
	      interfaceUnit.renderTo(this.unitsContentWrapper);
	      interfaceUnit.setSyncStatus(provider.getStatus());
	    });
	  }
	  showWebGridContent(items) {
	    const wrapper = this.getWebContentWrapper();
	    main_core.Dom.clean(wrapper);
	    const grid = new BX.TileGrid.Grid({
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
	  refresh(status, connectionsProviders) {
	    this.status = status;
	    this.connectionsProviders = connectionsProviders;
	    main_core.Dom.replace(document.querySelector('#calendar-sync-status-block'), this.blockStatusContent);
	    this.displayConnectionUnits();
	    this.auxiliarySyncPanel.refresh(status, connectionsProviders);
	  }
	}

	let _$4 = t => t,
	  _t$4,
	  _t2$4,
	  _t3$4,
	  _t4$4;
	class GridUnit extends BX.TileGrid.Item {
	  constructor(item) {
	    super({
	      id: item.type
	    });
	    this.item = item;
	  }
	  getContent() {
	    this.gridUnit = main_core.Tag.render(_t$4 || (_t$4 = _$4`<div class="calendar-sync-item ${0}" style="${0}">
			<div class="calendar-item-content">
				${0}
				${0}
				${0}
			</div>
		</div>`), this.getAdditionalContentClass(), this.getContentStyles(), this.getImage(), this.getTitle(), this.isActive() ? this.getStatus() : '');
	    this.gridUnit.addEventListener('click', this.onClick.bind(this));
	    return this.gridUnit;
	  }
	  getTitle() {
	    if (!this.layout.title) {
	      this.layout.title = main_core.Tag.render(_t2$4 || (_t2$4 = _$4`
				<div class="calendar-sync-item-title">${0}</div>`), BX.util.htmlspecialchars(this.item.getGridTitle()));
	    }
	    return this.layout.title;
	  }
	  getImage() {
	    return main_core.Tag.render(_t3$4 || (_t3$4 = _$4`
			<div class="calendar-sync-item-image">
				<div class="calendar-sync-item-image-item" style="background-image: ${0}"></div>
			</div>`), 'url(' + this.item.getGridIcon() + ')');
	  }
	  getStatus() {
	    if (this.isActive()) {
	      return main_core.Tag.render(_t4$4 || (_t4$4 = _$4`
				<div class="calendar-sync-item-status"></div>
			`));
	    }
	    return '';
	  }
	  isActive() {
	    return this.item.getConnectStatus();
	  }
	  getAdditionalContentClass() {
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
	  getContentStyles() {
	    if (this.isActive()) {
	      return 'background-color:' + this.item.getGridColor() + ';';
	    } else {
	      return '';
	    }
	  }
	  onClick() {
	    if (this.item.hasMenu()) {
	      this.item.showMenu(this.gridUnit);
	    } else if (this.item.getConnectStatus()) {
	      this.item.openActiveConnectionSlider(this.item.getConnection());
	    } else {
	      this.item.openInfoConnectionSlider();
	    }
	  }
	}

	let _$5 = t => t,
	  _t$5,
	  _t2$5,
	  _t3$5,
	  _t4$5,
	  _t5$4,
	  _t6$3;
	class ConnectionControls {
	  constructor(options = null) {
	    this.userName = null;
	    this.server = null;
	    this.connectionName = null;
	    this.addButtonText = main_core.Loc.getMessage('CAL_UPPER_CONNECT');
	    this.removeButtonText = main_core.Loc.getMessage('CAL_UPPER_DISCONNECT');
	    this.saveButtonText = main_core.Loc.getMessage('CAL_UPPER_SAVE');
	    if (options !== null) {
	      this.userName = BX.util.htmlspecialchars(options.userName);
	      this.server = BX.util.htmlspecialchars(options.server);
	      this.connectionName = BX.util.htmlspecialchars(options.connectionName);
	    }
	  }
	  getWrapper() {
	    return main_core.Tag.render(_t$5 || (_t$5 = _$5`
			<div class="calendar-sync-slider-section calendar-sync-slider-section-form"></div>
		`));
	  }
	  getForm() {
	    return main_core.Tag.render(_t2$5 || (_t2$5 = _$5`
			<form class="calendar-sync-slider-form" action="">
				<div class="calendar-sync-slider-field">
					<div class="ui-ctl ui-ctl-w100 ui-ctl-textbox">
						<input type="text" class="ui-ctl-element" placeholder=\"${0}\" name="name" value="${0}">
					</div>
				</div>
				<div class="calendar-sync-slider-field">
					<div class="ui-ctl ui-ctl-w100 ui-ctl-textbox">
						<input type="text" class="ui-ctl-element" placeholder=\"${0}\" name="server" value="${0}">
					</div>
				</div>
				<div class="calendar-sync-slider-field">
					<div class="ui-ctl ui-ctl-w100 ui-ctl-textbox">
						<input type="text" class="ui-ctl-element" placeholder=\"${0}\" name="user_name" value="${0}">
					</div>
				</div>
				<div class="calendar-sync-slider-field">
					<div class="ui-ctl ui-ctl-w100 ui-ctl-textbox">
						<input type="password" class="ui-ctl-element" name="password" placeholder=\"${0}\">
					</div>
				</div>
			</form>
		`), main_core.Loc.getMessage('CAL_TEXT_NAME'), this.connectionName || '', main_core.Loc.getMessage('CAL_TEXT_SERVER_ADDRESS'), this.server || '', main_core.Loc.getMessage('CAL_TEXT_USER_NAME'), this.userName || '', main_core.Loc.getMessage('CAL_TEXT_PASSWORD'));
	  }
	  getAddButton() {
	    return main_core.Tag.render(_t3$5 || (_t3$5 = _$5`
			<button id="connect-button" class="ui-btn ui-btn-light-border">${0}</button>
		`), this.addButtonText);
	  }
	  getDisconnectButton() {
	    return main_core.Tag.render(_t4$5 || (_t4$5 = _$5`
			<button id="disconnect-button" class="calendar-sync-slider-btn ui-btn ui-btn-light-border">${0}</button>
		`), this.removeButtonText);
	  }
	  getSaveButton() {
	    return main_core.Tag.render(_t5$4 || (_t5$4 = _$5`
			<button id="edit-connect-button" class="calendar-sync-slider-btn ui-btn ui-btn-light-border">${0}</button>
		`), this.saveButtonText);
	  }
	  getButtonWrapper() {
	    return main_core.Tag.render(_t6$3 || (_t6$3 = _$5`
			<div class="calendar-sync-slider-form-btn"></div>
		`));
	  }
	}

	let _$6 = t => t,
	  _t$6,
	  _t2$6,
	  _t3$6,
	  _t4$6,
	  _t5$5;
	class MobileSyncBanner {
	  constructor(options = {}) {
	    this.zIndex = 3100;
	    this.DOM = {};
	    this.QRCODE_SIZE = 186;
	    this.QRCODE_COLOR_LIGHT = '#ffffff';
	    this.QRCODE_COLOR_DARK = '#000000';
	    this.QRCODE_WRAP_CLASS = 'calendar-sync-slider-qr-container';
	    this.QRC = null;
	    this.type = options.type;
	    this.helpDeskCode = options.helpDeskCode || '11828176';
	    this.alreadyConnectedToNew = this.type === 'android' ? calendar_util.Util.isGoogleConnected() : calendar_util.Util.isIcloudConnected();
	  }
	  show() {}
	  showInPopup() {
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
	  close() {
	    this.popup.close();
	  }
	  getContainer() {
	    this.DOM.container = main_core.Tag.render(_t$6 || (_t$6 = _$6`
			<div class="calendar-sync-qr-popup-content">
				<div class="calendar-sync-qr-popup-title">
					${0}
				</div>
				<div class="calendar-sync-slider-content">
					<img class="calendar-sync-slider-phone-img" src="/bitrix/images/calendar/sync/qr-background.svg" alt="">
					${0}
					${0}
				</div>
			</div>
		`), this.getTitle(), this.getQrContainer(), this.getInstructionContainer());
	    calendar_util.Util.initHintNode(this.DOM.container.querySelector('.calendar-notice-mobile-banner'));
	    return this.DOM.container;
	  }
	  getQrContainer() {
	    if (!this.DOM.qrContainer) {
	      this.DOM.qrContainer = main_core.Tag.render(_t2$6 || (_t2$6 = _$6`
				<div class="calendar-sync-slider-qr">
					<div class="${0}">${0}</div>
					<span class="calendar-sync-slider-logo"></span>
				</div>
			`), this.QRCODE_WRAP_CLASS, calendar_util.Util.getLoader(this.QRCODE_SIZE));
	    }
	    return this.DOM.qrContainer;
	  }
	  getInstructionContainer() {
	    if (!this.DOM.instructionContainer) {
	      this.DOM.instructionContainer = main_core.Tag.render(_t3$6 || (_t3$6 = _$6`
				<div class="calendar-sync-slider-instruction">
					<!--<div class="calendar-sync-slider-instruction-subtitle"></div>-->
					${0}
					<div class="calendar-sync-slider-instruction-notice">${0}</div>
					<a href="javascript:void(0);" 
							onclick="BX.Helper.show('redirect=detail&code=' + ${0},{zIndex:3100,}); event.preventDefault();" 
							class="ui-btn ui-btn-success ui-btn-round">
						${0}
					</a>
				</div>
			`), this.getInstructionTextContainer(), main_core.Loc.getMessage('SYNC_MOBILE_NOTICE'), this.getHelpdeskCode(), main_core.Loc.getMessage('SYNC_MOBILE_ABOUT_BTN'));
	    }
	    return this.DOM.instructionContainer;
	  }
	  getInstructionTextContainer() {
	    if (!this.DOM.instructionTextContainer) {
	      this.DOM.instructionTextContainer = main_core.Tag.render(_t4$6 || (_t4$6 = _$6`
				<div class="calendar-sync-slider-instruction-title">
					${0} 
					${0}
				</div>
			`), main_core.Loc.getMessage('SYNC_MOBILE_NOTICE_HOW_TO') + ' ', this.type !== 'iphone' ? this.getAndroidHintIcon() : '');
	    }
	    return this.DOM.instructionTextContainer;
	  }
	  getAndroidHintIcon() {
	    if (!this.DOM.androidHintIcon) {
	      this.DOM.androidHintIcon = main_core.Tag.render(_t5$5 || (_t5$5 = _$6`
			<span 
				class="calendar-notice-mobile-banner" 
				data-hint="${0}" 
				data-hint-no-icon="Y">
			</span>`), main_core.Loc.getMessage('CAL_ANDROID_QR_CODE_HINT'));
	    }
	    return this.DOM.androidHintIcon;
	  }
	  getInnerContainer() {
	    return this.DOM.container.querySelector('.' + this.QRCODE_WRAP_CLASS);
	  }
	  initQrCode() {
	    return new Promise(resolve => {
	      main_core.Runtime.loadExtension(['main.qrcode']).then(() => {
	        if (typeof QRCode !== 'undefined') {
	          resolve();
	        }
	      });
	    });
	  }
	  drawQRCode(wrap) {
	    if (!main_core.Type.isDomNode(wrap)) {
	      wrap = this.getInnerContainer();
	    }
	    this.getMobileSyncUrl().then(link => {
	      main_core.Dom.clean(wrap);
	      this.QRC = new QRCode(wrap, {
	        text: link,
	        width: this.getSize(),
	        height: this.getSize(),
	        colorDark: this.QRCODE_COLOR_DARK,
	        colorLight: this.QRCODE_COLOR_LIGHT,
	        correctLevel: QRCode.CorrectLevel.H
	      });
	    });
	  }
	  getTitle() {
	    return main_core.Loc.getMessage('SYNC_BANNER_MOBILE_TITLE');
	  }
	  getMobileSyncUrl() {
	    return new Promise((resolve, reject) => {
	      BX.ajax.runAction('calendar.api.syncajax.getAuthLink', {
	        data: {
	          type: this.type ? 'slider' : 'banner'
	        }
	      }).then(response => {
	        resolve(response.data.link);
	      }, reject);
	    });
	  }
	  getSize() {
	    return this.QRCODE_SIZE;
	  }
	  getDetailHelpUrl() {
	    return 'https://helpdesk.bitrix24.ru/open/' + this.getHelpdeskCode();
	  }
	  getHelpdeskCode() {
	    return this.helpDeskCode;
	  }
	}

	let _$7 = t => t,
	  _t$7,
	  _t2$7,
	  _t3$7,
	  _t4$7,
	  _t5$6,
	  _t6$4,
	  _t7$3,
	  _t8$1,
	  _t9$1,
	  _t10$1,
	  _t11,
	  _t12,
	  _t13;
	class InterfaceTemplate extends main_core_events.EventEmitter {
	  constructor(options) {
	    super();
	    this.sliderWidth = 840;
	    this.setEventNamespace('BX.Calendar.Sync.Interface.InterfaceTemplate');
	    this.title = options.title;
	    this.helpdeskCode = options.helpDeskCode;
	    this.titleInfoHeader = options.titleInfoHeader;
	    this.descriptionInfoHeader = options.descriptionInfoHeader;
	    this.titleActiveHeader = options.titleActiveHeader;
	    this.descriptionActiveHeader = options.descriptionActiveHeader;
	    this.sliderIconClass = options.sliderIconClass;
	    this.iconPath = options.iconPath;
	    this.iconLogoClass = options.iconLogoClass || '';
	    this.color = options.color;
	    this.provider = options.provider;
	    this.connection = options.connection;
	    this.popupWithUpdateButton = options.popupWithUpdateButton;
	  }
	  static createInstance(provider, connection = null) {
	    return new this(provider, connection);
	  }
	  getInfoConnectionContent() {
	    return main_core.Tag.render(_t$7 || (_t$7 = _$7`
			<div class="calendar-sync-wrap calendar-sync-wrap-detail">
				<div class="calendar-sync-header">
					<span class="calendar-sync-header-text">${0}</span>
				</div>
				${0}
			</div>
		`), this.getHeaderTitle(), this.getContentInfoBody());
	  }
	  getContentActiveBodyHeader() {
	    this.disconnectButton = this.getDisconnectButton();
	    main_core.Event.bind(this.disconnectButton, 'click', this.handleDisconnectButton.bind(this));
	    const timestamp = this.connection.getSyncDate().getTime() / 1000;
	    const syncTime = timestamp ? calendar_util.Util.formatDateUsable(timestamp) + ' ' + BX.date.format(calendar_util.Util.getTimeFormatShort(), timestamp) : '';
	    return main_core.Tag.render(_t2$7 || (_t2$7 = _$7`
			<div class="calendar-sync__account ${0}">
				<div class="calendar-sync__account-logo">
					<div class="calendar-sync__account-logo--image ${0}"></div>
				</div>
				<div class="calendar-sync__account-content">
					${0}
					<div class="calendar-sync__account-info">
						<div class="calendar-sync__account-info--icon --animate"></div>
						${0}
					</div>
				</div>
				${0}
			</div>
			`), this.getSyncStatusClassName(), this.getLogoIconClass(), BX.util.htmlspecialchars(this.connection.getConnectionName()), syncTime, this.disconnectButton);
	  }
	  getActiveConnectionContent() {
	    return main_core.Tag.render(_t3$7 || (_t3$7 = _$7`
			<div class="calendar-sync-wrap calendar-sync-wrap-detail">
				<div class="calendar-sync-header">
					<span class="calendar-sync-header-text">${0}</span>
				</div>
				<div class="calendar-sync__scope">
					<div class="calendar-sync__content --border-radius">
						<div class="calendar-sync__content-block --space-bottom">
							${0}
						</div>
					</div>
				</div>
			</div>
		`), this.getHeaderTitle(), this.getContentActiveBody());
	  }
	  getContentInfoBody() {
	    return main_core.Tag.render(_t4$7 || (_t4$7 = _$7`
			${0}
		`), this.getContentInfoBodyHeader());
	  }
	  getContentActiveBody() {
	    return main_core.Tag.render(_t5$6 || (_t5$6 = _$7`
			${0}
			${0}
			${0}
		`), this.getContentActiveBodyHeader(), this.getContentActiveBodySectionsHeader(), this.getContentActiveBodySectionsManager());
	  }
	  showHelp(event) {
	    if (top.BX.Helper) {
	      top.BX.Helper.show("redirect=detail&code=" + this.helpdeskCode);
	      event.preventDefault();
	    }
	  }
	  getHelpdeskLink() {
	    return 'https://helpdesk.bitrix24.ru/open/' + this.helpdeskCode;
	  }
	  getHeaderTitle() {
	    return this.title;
	  }
	  getLogoIconClass() {
	    return this.iconLogoClass;
	  }
	  getContentInfoBodyHeader() {
	    if (!this.infoBodyHeader) {
	      this.infoBodyHeader = main_core.Tag.render(_t6$4 || (_t6$4 = _$7`
				<div class="calendar-sync-slider-section calendar-sync-slider-section-flex-wrap">
					<div class="calendar-sync-slider-header-icon ${0}"></div>
					<div class="calendar-sync-slider-header">
						<div class="calendar-sync-slider-title">
							${0}
						</div>
						<div class="calendar-sync-slider-info">
							<span class="calendar-sync-slider-info-text">
								${0}
							</span>
						</div>
						${0}
					</div>
				</div>
			`), this.sliderIconClass, this.titleInfoHeader, this.descriptionInfoHeader, this.getContentInfoBodyHeaderHelper());
	    }
	    return this.infoBodyHeader;
	  }
	  getContentInfoBodyHeaderHelper() {
	    return main_core.Tag.render(_t7$3 || (_t7$3 = _$7`
			<div class="calendar-sync-slider-info">
				<span class="calendar-sync-slider-info-text">
					<a class="calendar-sync-slider-info-link" href="javascript:void(0);" onclick="${0}">
						${0}
					</a>
				</span>
			</div>
		`), this.showHelp.bind(this), main_core.Loc.getMessage('CAL_TEXT_ABOUT_WORK_SYNC'));
	  }
	  getContentInfoWarning() {
	    const mobileSyncButton = this.getMobileSyncControlButton();
	    if (this.alreadyConnectedToNew) {
	      main_core.Event.bind(mobileSyncButton, 'click', this.handleMobileButtonOtherSyncInfo.bind(this));
	    } else {
	      main_core.Event.bind(mobileSyncButton, 'click', this.handleMobileButtonConnectClick.bind(this));
	    }
	    return main_core.Tag.render(_t8$1 || (_t8$1 = _$7`
				<div class="calendar-sync-slider-section-warning calendar-sync-slider-section-col">
					<div class="ui-alert ui-alert-warning ui-alert-icon-info">
						<span class="ui-alert-message">${0}
						</span>
					</div>
					<div class="calendar-sync-button-warning">${0}</div>
				</div>
			`), this.warningText, mobileSyncButton);
	  }
	  getMobileSyncControlButton() {
	    return main_core.Tag.render(_t9$1 || (_t9$1 = _$7`
			<button class="ui-btn ui-btn-success ui-btn-sm ui-btn-round">
				${0}
			</button>
		`), this.mobileSyncButtonText);
	  }
	  setProvider(provider) {
	    this.provider = provider;
	  }

	  // TODO: move logic to provider
	  sendRequestRemoveConnection(id) {
	    BX.ajax.runAction('calendar.api.syncajax.removeConnection', {
	      data: {
	        connectionId: id,
	        removeCalendars: 'Y' //by default
	      }
	    }).then(() => {
	      BX.reload();
	    });
	  }
	  runUpdateInfo() {
	    BX.ajax.runAction('calendar.api.calendarajax.setSectionStatus', {
	      data: {
	        sectionStatus: this.sectionStatusObject
	      }
	    }).then(response => {
	      this.emit('reDrawCalendarGrid', {});
	    });
	  }
	  refresh(connection) {
	    this.connection = connection;
	    if (this.connection) {
	      this.statusBlock.setStatus(this.connection.getStatus()).setConnections([this.connection]);
	    }
	    main_core.Dom.replace(document.getElementById('status-info-block'), this.statusBlock.getContent());
	  }
	  handleConnectButton() {}
	  getDisconnectButton() {
	    return main_core.Tag.render(_t10$1 || (_t10$1 = _$7`
			<button class="ui-btn ui-btn-light-border calendar-sync__account-btn">${0}</button>
		`), main_core.Loc.getMessage('CAL_SYNC_DISCONNECT_BUTTON'));
	  }
	  getSyncStatusClassName() {
	    return this.provider.getStatus() === "success" ? '--complete' : '--error';
	  }
	  getContentActiveBodySectionsHeader() {
	    return main_core.Tag.render(_t11 || (_t11 = _$7`
			<div class="calendar-sync__account-desc">${0}</div>
		`), main_core.Loc.getMessage('CAL_SYNC_SELECTED_LIST_TITLE'));
	  }
	  getContentActiveBodySectionsManager() {
	    return main_core.Tag.render(_t12 || (_t12 = _$7`
			<div class="calendar-sync__account-check-list">
				${0}
			</div>
		`), this.getContentActiveBodySections());
	  }
	  getContentActiveBodySections() {
	    const sectionList = [];
	    this.sectionList.forEach(section => {
	      sectionList.push(main_core.Tag.render(_t13 || (_t13 = _$7`
				<label class="calendar-sync__account-check-list-label">
					<input type="checkbox" class="calendar-sync__account-check-list-input"
						value="${0}" 
						onclick="${0}" ${0}/>
					<span class="calendar-sync__account-check-list-text">${0}</span>
				</label>
			`), BX.util.htmlspecialchars(section['ID']), this.onClickCheckSection.bind(this), section['ACTIVE'] === 'Y' ? 'checked' : '', BX.util.htmlspecialchars(section['NAME'])));
	    });
	    return sectionList;
	  }
	  showUpdateSectionListNotification() {
	    calendar_util.Util.showNotification(main_core.Loc.getMessage('CAL_SYNC_CALENDAR_LIST_UPDATED'));
	  }
	  handleDisconnectButton(event) {
	    if (main_core.Type.isElementNode(this.disconnectButton)) {
	      main_core.Dom.addClass(this.disconnectButton, ['ui-btn-clock', 'ui-btn-disabled']);
	    }
	    event.preventDefault();
	    // this.provider.removeConnection();
	    this.sendRequestRemoveConnection(this.connection.getId());
	  }
	  deactivateConnection(id) {
	    BX.ajax.runAction('calendar.api.syncajax.deactivateConnection', {
	      data: {
	        connectionId: id,
	        removeCalendars: 'N' //by default
	      }
	    }).then(() => {
	      this.provider.closeSlider();
	      this.provider.setStatus(this.provider.STATUS_NOT_CONNECTED);
	      this.provider.getInterfaceUnit().refreshButton();
	      this.provider.getInterfaceUnit().setSyncStatus(this.provider.STATUS_NOT_CONNECTED);
	      this.emit('reDrawCalendarGrid', {});
	    });
	  }
	}
	InterfaceTemplate.SLIDER_WIDTH = 606;
	InterfaceTemplate.SLIDER_PREFIX = 'calendar:connection-sync-';

	let _$8 = t => t,
	  _t$8,
	  _t2$8;
	class CaldavInterfaceTemplate extends InterfaceTemplate {
	  constructor(options) {
	    super(options);
	  }
	  getContentInfoBody() {
	    const formObject = new ConnectionControls();
	    const formBlock = formObject.getWrapper();
	    const form = formObject.getForm();
	    const button = formObject.getAddButton();
	    const buttonWrapper = formObject.getButtonWrapper();
	    const bodyHeader = this.getContentInfoBodyHeader();
	    button.addEventListener('click', event => {
	      main_core.Dom.addClass(button, ['ui-btn-clock', 'ui-btn-disabled']);
	      event.preventDefault();
	      this.sendRequestAddConnection(form);
	    });
	    main_core.Dom.append(button, buttonWrapper);
	    main_core.Dom.append(buttonWrapper, form);
	    main_core.Dom.append(form, formBlock);
	    return main_core.Tag.render(_t$8 || (_t$8 = _$8`
			${0}
			${0}
		`), bodyHeader, formBlock);
	  }
	  getContentActiveBody() {
	    const formObject = new ConnectionControls({
	      server: this.connection.addParams.server,
	      userName: this.connection.addParams.userName,
	      connectionName: this.connection.connectionName
	    });
	    const formBlock = formObject.getWrapper();
	    const form = formObject.getForm();
	    const bodyHeader = this.getContentActiveBodyHeader();
	    main_core.Dom.append(form, formBlock);
	    return main_core.Tag.render(_t2$8 || (_t2$8 = _$8`
			${0}
			${0}
		`), bodyHeader, formBlock);
	  }
	  sendRequestAddConnection(form) {
	    const fd = new FormData(form);
	    BX.ajax.runAction('calendar.api.syncajax.addConnection', {
	      data: {
	        name: fd.get('name'),
	        server: fd.get('server'),
	        userName: fd.get('user_name'),
	        pass: fd.get('password')
	      }
	    }).then(response => {
	      BX.reload();
	    }, response => {
	      const button = form.querySelector('#connect-button');
	      this.showAlertPopup(response.errors[0], button);
	    });
	  }
	  showAlertPopup(alert, button) {
	    let message = '';
	    if (alert.code === 'incorrect_parameters') {
	      message = main_core.Loc.getMessage('CAL_TEXT_ALERT_INCORRECT_PARAMETERS');
	    } else if (alert.code === 'tech_problem') {
	      message = main_core.Loc.getMessage('CAL_TEXT_ALERT_TECH_PROBLEM');
	    } else {
	      message = main_core.Loc.getMessage('CAL_TEXT_ALERT_DEFAULT');
	    }
	    const messageBox = new BX.UI.Dialogs.MessageBox({
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
	      onOk: () => {
	        main_core.Dom.removeClass(button, ['ui-btn-clock', 'ui-btn-disabled']);
	        return true;
	      }
	    });
	    messageBox.show();
	  }
	}

	class CaldavTemplate extends CaldavInterfaceTemplate {
	  constructor(provider, connection = null) {
	    super({
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
	    });
	  }
	}

	let _$9 = t => t,
	  _t$9,
	  _t2$9,
	  _t3$8,
	  _t4$8;
	class ExchangeTemplate extends InterfaceTemplate {
	  constructor(provider, connection = null) {
	    super({
	      title: main_core.Loc.getMessage("CALENDAR_TITLE_EXCHANGE"),
	      helpDeskCode: '9860971',
	      titleInfoHeader: main_core.Loc.getMessage('CAL_CONNECT_EXCHANGE_CALENDAR'),
	      descriptionInfoHeader: main_core.Loc.getMessage('CAL_EXCHANGE_CONNECT_DESCRIPTION'),
	      titleActiveHeader: main_core.Loc.getMessage('CAL_EXCHANGE_CALENDAR_IS_CONNECT'),
	      descriptionActiveHeader: main_core.Loc.getMessage('CAL_EXCHANGE_SELECTED_DESCRIPTION'),
	      sliderIconClass: 'calendar-sync-slider-header-icon-office',
	      iconLogoClass: '--exchange',
	      iconPath: '/bitrix/images/calendar/sync/exchange.svg',
	      color: '#54d0df',
	      provider: provider,
	      connection: connection,
	      popupWithUpdateButton: true
	    });
	  }
	  getContentActiveBody() {
	    return main_core.Tag.render(_t$9 || (_t$9 = _$9`
			${0}
			${0}
			${0}
		`), this.getContentActiveBodyHeader(), this.getContentBody(), this.getHelpdeskBlock());
	  }
	  getContentActiveBodyHeader() {
	    const timestamp = this.connection.getSyncDate().getTime() / 1000;
	    const syncTime = timestamp ? calendar_util.Util.formatDateUsable(timestamp) + ' ' + BX.date.format(calendar_util.Util.getTimeFormatShort(), timestamp) : '';
	    return main_core.Tag.render(_t2$9 || (_t2$9 = _$9`
			<div class="calendar-sync__account ${0}">
				<div class="calendar-sync__account-logo">
					<div class="calendar-sync__account-logo--image ${0}"></div>
				</div>
				<div class="calendar-sync__account-content">
					${0}
					<div class="calendar-sync__account-info">
						<div class="calendar-sync__account-info--icon --animate"></div>
						${0}
					</div>
				</div>
			</div>
			`), this.getSyncStatusClassName(), this.getLogoIconClass(), BX.util.htmlspecialchars(this.connection.getConnectionName()), syncTime);
	  }
	  getContentBody() {
	    return main_core.Tag.render(_t3$8 || (_t3$8 = _$9`
			<div class="calendar-sync__account-desc">
				${0}
			</div>
		`), main_core.Loc.getMessage('CAL_EXCHANGE_SELECTED_DESCRIPTION'));
	  }
	  getHelpdeskBlock() {
	    return main_core.Tag.render(_t4$8 || (_t4$8 = _$9`
			<div>
				<a class="calendar-sync-slider-info-link" href="javascript:void(0);" onclick="${0}">
					${0}
				</a>
			</div>
		`), this.showHelp.bind(this), main_core.Loc.getMessage('CAL_TEXT_ABOUT_WORK_SYNC'));
	  }
	}

	let _$a = t => t,
	  _t$a,
	  _t2$a,
	  _t3$9,
	  _t4$9,
	  _t5$7,
	  _t6$5,
	  _t7$4,
	  _t8$2,
	  _t9$2,
	  _t10$2,
	  _t11$1,
	  _t12$1,
	  _t13$1,
	  _t14,
	  _t15;
	class SyncWizard extends main_core_events.EventEmitter {
	  // in ms

	  constructor() {
	    super();
	    this.TYPE = 'undefined';
	    this.SLIDER_NAME = 'calendar:sync-wizard-slider';
	    this.SLIDER_WIDTH = 450;
	    this.LOADER_NAME = "calendar:loader";
	    this.cache = new main_core.Cache.MemoryCache();
	    this.syncStagesList = [];
	    this.accountName = '';
	    this.HELPDESK_CODE = 11828176;
	    this.MIN_UPDATE_STATE_DELAY = 1500;
	    this.CONFETTI_DELAY = 1000;
	    this.setEventNamespace('BX.Calendar.Sync.Interface.SyncWizard');
	    this.BX = window.top.BX || window.BX;
	    this.pullWizardEventHandler = this.handlePullNewEvent.bind(this);
	    this.lastUpdateStateTimestamp = Date.now();
	    this.logoIconClass = '';
	  }
	  openSlider() {
	    BX.SidePanel.Instance.open(this.SLIDER_NAME, {
	      contentCallback: slider => {
	        return new Promise((resolve, reject) => {
	          resolve(this.getContent());
	        });
	      },
	      allowChangeHistory: false,
	      events: {
	        onLoad: () => {
	          this.displaySyncStages();
	          this.bindButtonsHandlers();
	        },
	        onDestroy: this.handleCloseWizard.bind(this)
	      },
	      cacheable: false,
	      width: this.SLIDER_WIDTH,
	      loader: this.LOADER_NAME
	    });
	    this.slider = BX.SidePanel.Instance.getTopSlider();
	    this.syncIsFinished = false;
	    this.errorStatus = false;
	  }
	  getContent() {
	    return main_core.Tag.render(_t$a || (_t$a = _$a`
			<div class="calendar-sync__wrapper calendar-sync__scope">
				<div class="calendar-sync__content --border-radius">
					<div class="calendar-sync__content-block --space-bottom">
						${0}
						${0}
						${0}
						${0}
						${0}
						${0}
						${0}
					</div>
				</div>
			</div>
		`), this.getTitleWrapper(), this.getSyncStagesWrapper(), this.getInfoStatusWrapper(), this.getErrorWrapper(), this.getFinalCheckWrapper(), this.getHelpLinkWrapper(), this.getButtonWrapper());
	  }
	  getTitleWrapper() {
	    this.syncTitleWrapper = main_core.Tag.render(_t2$a || (_t2$a = _$a`
			<div class="calendar-sync__account">
				<div class="calendar-sync__account-logo">
					<div class="calendar-sync__account-logo--image ${0}"></div>
				</div>
				<div class="calendar-sync__account-content">
					${0}
					<div class="calendar-sync__account-info">
						<div class="calendar-sync__account-info--icon --animate"></div>
						${0}
					</div>
				</div>
			</div>
		`), this.getLogoIconClass(), this.getAccountNameNode(), this.getActiveStatusNode());
	    return this.syncTitleWrapper;
	  }
	  getSyncStagesWrapper() {
	    this.syncStagesWrapper = main_core.Tag.render(_t3$9 || (_t3$9 = _$a`<div class="calendar-sync-stages-wrap"></div>`));
	    return this.syncStagesWrapper;
	  }
	  getInfoStatusWrapper() {
	    this.infoStatusWrapper = main_core.Tag.render(_t4$9 || (_t4$9 = _$a`
			<div class="calendar-sync__content-block --space-bottom-xl" style="display: none;">
				<div class="calendar-sync__notification">
					<div class="calendar-sync__notification-title">${0}</div>
					<div class="calendar-sync__notification-message">${0}</div>
				</div>
			</div>
		`), main_core.Loc.getMessage('CAL_INFO_STATUS_CONG_1'), main_core.Loc.getMessage('CAL_INFO_STATUS_CONG_2'));
	    return this.infoStatusWrapper;
	  }
	  getErrorWrapper() {
	    this.errorWrapper = main_core.Tag.render(_t5$7 || (_t5$7 = _$a`
			<div class="calendar-sync__content-block --space-bottom-xl" style="display: none;">
				<div class="calendar-sync__error">
					<div class="calendar-sync__notification-message">
						<div class="calendar-sync__notification-message-inner">
							${0}
						</div>
						${0}</div>
				</div>
			</div>
		`), main_core.Loc.getMessage('CAL_ERROR_WARN_1'), main_core.Loc.getMessage('CAL_ERROR_WARN_2'));
	    return this.errorWrapper;
	  }
	  getHelpLinkWrapper() {
	    this.helpLinkWrapper = main_core.Tag.render(_t6$5 || (_t6$5 = _$a`
			<div class="calendar-sync__content-block" style="display: none;"></div>
		`));
	    return this.helpLinkWrapper;
	  }
	  getFinalCheckWrapper() {
	    this.finalCheckWrapper = main_core.Tag.render(_t7$4 || (_t7$4 = _$a`
			<div class="calendar-sync__content-block" style="display: none;"></div>
		`));
	    return this.finalCheckWrapper;
	  }
	  getButtonWrapper() {
	    this.buttonWrapper = main_core.Tag.render(_t8$2 || (_t8$2 = _$a`
			<div style="display: none" class="calendar-sync__content-block --align-center">
				<a class="ui-btn ui-btn-lg ui-btn-primary ui-btn-round" data-role="continue_btn">
					${0}
				</a>
				<a style="display: none" class="ui-btn ui-btn-lg ui-btn-light-border ui-btn-round" data-role="everything_is_fine_btn">
					${0}
				</a>
				<a style="display: none" class="ui-btn ui-btn-lg ui-btn-light-border ui-btn-round" data-role="close_button">
					${0}
				</a>
			</div>
		`), main_core.Loc.getMessage('CAL_BUTTON_CONTINUE'), main_core.Loc.getMessage('CAL_BUTTON_EVERYTHING_IS_FINE'), main_core.Loc.getMessage('CAL_ERROR_CLOSE'));
	    return this.buttonWrapper;
	  }
	  getNewEventCardWrapper() {
	    this.newEventCardWrapper = main_core.Tag.render(_t9$2 || (_t9$2 = _$a`
			<div class="calendar-sync__content-block --space-bottom" style="display: none;"></div>
		`));
	    return this.newEventCardWrapper;
	  }
	  getSkeletonWrapper() {
	    this.skeletonWrapper = main_core.Tag.render(_t10$2 || (_t10$2 = _$a`
			<div class="calendar-sync__content-block --space-bottom">
					<div class="calendar-sync__balloon --skeleton">
						<div class="calendar-sync__balloon__skeleton-box">
							<div class="calendar-sync__balloon__skeleton-inline-box">
								<div class="calendar-sync__balloon__skeleton-circle"></div>
								<div class="calendar-sync__balloon__skeleton-line"></div>
							</div>
							<div class="calendar-sync__balloon__skeleton-line"></div>
						</div>
						<div class="calendar-sync__content-text">${0}</div>
					</div>
				</div>
		`), this.getSkeletonTitle());
	    return this.skeletonWrapper;
	  }
	  getSkeletonTitle() {
	    return '';
	  }
	  getExtraInfoWithCheckIcon() {
	    const alreadyConnected = Object.values(this.connectionsProviders).filter(item => {
	      return item.mainPanel && item.status;
	    }).length > 0;
	    return main_core.Tag.render(_t11$1 || (_t11$1 = _$a`
			<div class="calendar-sync__content-text --icon-check${0}">
				${0}
			</div>
		`), alreadyConnected ? ' --disabled' : '', main_core.Loc.getMessage('CAL_SYNC_INFO_PROMO'));
	  }
	  getAccountNameNode() {
	    if (!main_core.Type.isElementNode(this.accountNameNode)) {
	      this.accountNameNode = main_core.Tag.render(_t12$1 || (_t12$1 = _$a`
			<div class="calendar-sync__account-title">${0}</div>
		`), this.getAccountName());
	    }
	    return this.accountNameNode;
	  }
	  setAccountName(value) {
	    this.accountName = value;
	  }
	  getAccountName() {
	    return this.accountName;
	  }
	  getActiveStatusNode() {
	    if (!main_core.Type.isElementNode(this.activeStatusNode)) {
	      this.activeStatusNode = main_core.Tag.render(_t13$1 || (_t13$1 = _$a`
				<span class="calendar-active-status-node-carousel">
					<span class="calendar-active-status-node-phrase">
						${0}
					</span>
				</span>
			`), main_core.Loc.getMessage('CAL_STATUS_SYNC_IN_PROGRESS'));
	      this.startStatusCarousel(this.activeStatusNode);
	    }
	    return this.activeStatusNode;
	  }
	  startStatusCarousel(statusNode) {
	    const progressStatuses = [main_core.Loc.getMessage('CAL_STATUS_SYNC_IN_PROGRESS_STATUSES_FIRST'), main_core.Loc.getMessage('CAL_STATUS_SYNC_IN_PROGRESS_STATUSES_SECOND')];
	    let dotCycle = 1;
	    this.statusCarouselInterval = setInterval(() => {
	      const currentPhraseNode = statusNode.firstElementChild;
	      if (this.countDots(currentPhraseNode.innerText) < 3) {
	        currentPhraseNode.innerText += '.';
	        statusNode.style.width = currentPhraseNode.offsetWidth + 1 + 'px';
	        return;
	      }
	      if (dotCycle < 2) {
	        dotCycle++;
	        currentPhraseNode.innerText = currentPhraseNode.innerText.slice(0, -3);
	        return;
	      }
	      dotCycle = 1;
	      if (progressStatuses.length > 0) {
	        const status = progressStatuses.shift();
	        this.animateNextStatus(statusNode, status);
	      } else {
	        const almostDoneStatus = main_core.Loc.getMessage('CAL_STATUS_SYNC_IN_PROGRESS_ALMOST_DONE');
	        this.animateNextStatus(statusNode, almostDoneStatus);
	        statusNode.style.width = '';
	        clearInterval(this.statusCarouselInterval);
	      }
	    }, 900);
	  }
	  animateNextStatus(carousel, phraseText) {
	    const currentPhraseNode = carousel.firstElementChild;
	    const nextPhraseNode = main_core.Tag.render(_t14 || (_t14 = _$a`
			<span class="calendar-active-status-node-phrase">${0}</span>
		`), phraseText);
	    carousel.append(nextPhraseNode);
	    const maxWidth = Math.max(nextPhraseNode.offsetWidth, currentPhraseNode.offsetWidth);
	    carousel.style.width = maxWidth + 1 + 'px';
	    currentPhraseNode.style.transition = ''; // turn on animation
	    currentPhraseNode.style.transform = `translateX(-${currentPhraseNode.offsetWidth}px)`;
	    nextPhraseNode.style.transform = `translateX(-${currentPhraseNode.offsetWidth}px)`;
	    setTimeout(() => {
	      currentPhraseNode.remove();
	      nextPhraseNode.style.transition = 'none'; // turn off animation
	      nextPhraseNode.style.transform = '';
	    }, 300);
	  }
	  countDots(string) {
	    return (string.match(/\./g) || []).length;
	  }
	  setSyncStages() {
	    this.syncStagesList = [];
	  }
	  getSyncStages() {
	    return this.syncStagesList;
	  }
	  getHelpDeskCode() {
	    return this.HELPDESK_CODE;
	  }
	  displaySyncStages() {
	    main_core.Dom.clean(this.syncStagesWrapper);
	    this.getSyncStages().forEach(stage => {
	      stage.renderTo(this.syncStagesWrapper);
	    });
	  }
	  bindButtonsHandlers() {
	    const continueButton = this.buttonWrapper.querySelector('.ui-btn[data-role="continue_btn"]');
	    if (main_core.Type.isElementNode(continueButton)) {
	      main_core.Event.bind(continueButton, 'click', this.handleContinueButtonClick.bind(this));
	    }
	    const eifButton = this.buttonWrapper.querySelector('.ui-btn[data-role="everything_is_fine_btn"]');
	    if (main_core.Type.isElementNode(eifButton)) {
	      main_core.Event.bind(eifButton, 'click', this.handleFinalCloseButtonClick.bind(this));
	    }
	  }
	  handleContinueButtonClick() {
	    this.showFinalStage();
	  }
	  showFinalStage() {
	    this.syncIsFinished = true;
	    const eifButton = this.buttonWrapper.querySelector('.ui-btn[data-role="everything_is_fine_btn"]');
	    if (main_core.Type.isElementNode(eifButton)) {
	      eifButton.style.display = '';
	    }
	    const continueButton = this.buttonWrapper.querySelector('.ui-btn[data-role="continue_btn"]');
	    if (main_core.Type.isElementNode(continueButton)) {
	      continueButton.style.display = 'none';
	    }
	    this.showFinalCheckWrapper();
	    this.showHelpLinkWrapper();
	    this.hideSyncStagesWrapper();
	    this.hideInfoStatusWrapper();
	    calendar_util.Util.getBX().Event.EventEmitter.subscribe('onPullEvent-calendar', this.pullWizardEventHandler);
	    this.emit('startWizardWaitingMode');
	  }
	  isSyncFinished() {
	    return this.syncIsFinished;
	  }
	  handleFinalCloseButtonClick() {
	    BX.SidePanel.Instance.getOpenSliders().forEach(slider => {
	      if (['calendar:sync-slider', 'calendar:section-slider', this.SLIDER_NAME].includes(slider.getUrl())) {
	        slider.close();
	      }
	    });
	  }
	  handleUpdateState(stateData) {
	    const currentTimestamp = Date.now();
	    if (currentTimestamp - this.lastUpdateStateTimestamp > this.MIN_UPDATE_STATE_DELAY) {
	      this.updateState(stateData);
	    } else {
	      setTimeout(() => {
	        this.handleUpdateState(stateData);
	      }, this.MIN_UPDATE_STATE_DELAY);
	    }
	  }
	  updateState(stateData) {
	    if (this.errorStatus) {
	      return;
	    }
	    if (stateData.stage === 'connection_created' && stateData.accountName && main_core.Type.isElementNode(this.accountNameNode)) {
	      this.setAccountName(stateData.accountName);
	      this.accountNameNode.innerHTML = main_core.Text.encode(stateData.accountName);
	    }
	    this.lastUpdateStateTimestamp = Date.now();
	  }
	  setActiveStatusFinished() {
	    this.activeStatusNode.style.width = '';
	    clearInterval(this.statusCarouselInterval);
	    this.syncIsFinished = true;
	    if (main_core.Type.isElementNode(this.activeStatusNode)) {
	      this.activeStatusNode.innerHTML = main_core.Text.encode(main_core.Loc.getMessage('CAL_STATUS_SYNC_SUCCESS').toUpperCase());
	      main_core.Dom.remove(this.syncTitleWrapper.querySelector('.calendar-sync__account-info--icon'));
	    }
	  }
	  showButtonWrapper() {
	    if (main_core.Type.isElementNode(this.buttonWrapper)) {
	      this.buttonWrapper.style.display = '';
	    }
	  }
	  hideButtonWrapper() {
	    if (main_core.Type.isElementNode(this.buttonWrapper)) {
	      this.buttonWrapper.style.display = 'none';
	    }
	  }
	  showInfoStatusWrapper() {
	    if (main_core.Type.isElementNode(this.infoStatusWrapper)) {
	      this.infoStatusWrapper.style.display = '';
	    }
	  }
	  hideInfoStatusWrapper() {
	    if (main_core.Type.isElementNode(this.infoStatusWrapper)) {
	      this.infoStatusWrapper.style.display = 'none';
	    }
	  }
	  showErrorWrapper() {
	    if (main_core.Type.isElementNode(this.errorWrapper)) {
	      this.errorWrapper.style.display = '';
	    }
	  }
	  hideErrorWrapper() {
	    if (main_core.Type.isElementNode(this.errorWrapper)) {
	      this.errorWrapper.style.display = 'none';
	    }
	  }
	  showFinalCheckWrapper() {
	    if (main_core.Type.isElementNode(this.finalCheckWrapper)) {
	      this.finalCheckWrapper.style.display = '';
	    }
	  }
	  hideFinalCheckWrapper() {
	    if (main_core.Type.isElementNode(this.finalCheckWrapper)) {
	      this.finalCheckWrapper.style.display = 'none';
	    }
	  }
	  showSyncStagesWrapper() {
	    if (main_core.Type.isElementNode(this.syncStagesWrapper)) {
	      this.syncStagesWrapper.style.display = '';
	    }
	  }
	  hideSyncStagesWrapper() {
	    if (main_core.Type.isElementNode(this.syncStagesWrapper)) {
	      this.syncStagesWrapper.style.display = 'none';
	    }
	  }
	  showHelpLinkWrapper() {
	    if (main_core.Type.isElementNode(this.helpLinkWrapper)) {
	      this.helpLinkWrapper.style.display = '';
	    }
	  }
	  hideHelpLinkWrapper() {
	    if (main_core.Type.isElementNode(this.helpLinkWrapper)) {
	      this.helpLinkWrapper.style.display = 'none';
	    }
	  }
	  handlePullNewEvent(event) {
	    if (event && main_core.Type.isFunction(event.getData)) {
	      const data = {
	        command: event.getData()[0],
	        ...event.getData()[1]
	      };
	      if (data.command === 'edit_event' && data.newEvent) {
	        if (main_core.Type.isElementNode(this.finalCheckWrapper)) {
	          const syncBalloon = this.finalCheckWrapper.querySelector('.calendar-sync__balloon');
	          if (main_core.Type.isElementNode(syncBalloon) && main_core.Dom.hasClass(syncBalloon, '--progress')) {
	            main_core.Dom.removeClass(syncBalloon, '--progress');
	            main_core.Dom.addClass(syncBalloon, '--done');
	          }
	        }
	        const entry = new calendar_entry.Entry({
	          data: data.fields
	        });
	        this.displayNewEvent(entry);
	        calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('onPullEvent-calendar', this.pullWizardEventHandler);
	        const eifButton = this.buttonWrapper.querySelector('.ui-btn[data-role="everything_is_fine_btn"]');
	        if (main_core.Type.isElementNode(eifButton)) {
	          eifButton.innerHTML = main_core.Text.encode(main_core.Loc.getMessage('CAL_BUTTON_KEEP_GOING'));
	          main_core.Dom.addClass(eifButton, 'ui-btn-primary');
	          main_core.Dom.removeClass(eifButton, 'ui-btn-light-border');
	        }
	        this.emit('endWizardWaitingMode');
	      }
	    }
	  }
	  displayNewEvent(entry) {
	    // Hide skeleton
	    if (main_core.Type.isElementNode(this.skeletonWrapper)) {
	      main_core.Dom.remove(this.skeletonWrapper);
	    }
	    if (main_core.Type.isElementNode(this.newEventCardWrapper)) {
	      this.newEventCardWrapper.style.display = '';
	      main_core.Dom.clean(this.newEventCardWrapper);
	      this.newEventCardWrapper.appendChild(this.getNewEventCard(entry));
	    }
	  }
	  getNewEventCard(entry) {
	    const from = new Date(entry.from.getTime() - (parseInt(entry.data['~USER_OFFSET_FROM']) || 0) * 1000);
	    const to = new Date(entry.to.getTime() - (parseInt(entry.data['~USER_OFFSET_TO']) || 0) * 1000);
	    const fromTimestamp = from.getTime();
	    const dateFrom = BX.date.format(calendar_util.Util.getDayMonthFormat(), fromTimestamp / 1000);
	    const timeFrom = calendar_util.Util.formatTime(from.getHours(), from.getMinutes());
	    const timeTo = calendar_util.Util.formatTime(to.getHours(), to.getMinutes());
	    const timeField = entry.isFullDay() ? main_core.Loc.getMessage('CAL_WIZARD_FULL_DAY') : timeFrom + ' - ' + timeTo;
	    this.newEventCard = main_core.Tag.render(_t15 || (_t15 = _$a`
			<div class="calendar-sync__balloon --calendar ${0}">
				<div class="calendar-sync__content-text">
					${0}
					<span class="calendar-date-year">
						${0}
					</span>
				</div>
				<div class="calendar-sync__content-text">${0}</div>
				<div class="calendar-sync__time-box">
					<div class="calendar-sync__time">
						<div class="calendar-sync__time-date">${0}</div>
						<div class="calendar-sync__time-line"></div>
					</div>
					<div class="calendar-sync__time-notification-box">
						<div class="calendar-sync__content-text">${0}</div>
						<div class="calendar-sync__content-text">${0}</div>
					</div>
					<div class="calendar-sync__time">
						<div class="calendar-sync__time-date">${0}</div>
						<div class="calendar-sync__time-line"></div>
					</div>
				</div>
			</div>
		`), entry.isFullDay() ? '--fullday-event' : '', dateFrom, BX.date.format('Y', fromTimestamp / 1000), BX.date.format('l', fromTimestamp / 1000), timeFrom, main_core.Text.encode(entry.getName()), timeField, timeTo);
	    return this.newEventCard;
	  }
	  handleCloseWizard() {
	    this.slider = null;
	    clearInterval(this.statusCarouselInterval);
	    calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('onPullEvent-calendar', this.pullWizardEventHandler);
	    this.emit('onClose');
	  }
	  showConfetti() {
	    setTimeout(() => {
	      const bx = calendar_util.Util.getBX();
	      bx.UI.Confetti.fire({
	        particleCount: 240,
	        spread: 170,
	        origin: {
	          y: 0.3,
	          x: 0.9
	        },
	        zIndex: bx.SidePanel.Instance.getTopSlider().getZindex() + 1
	      });
	    }, this.CONFETTI_DELAY);
	  }
	  getLogoIconClass() {
	    return this.logoIconClass;
	  }
	  getSlider() {
	    return this.slider;
	  }
	  setErrorState() {
	    this.errorStatus = true;
	    this.showErrorWrapper();
	    this.hideInfoStatusWrapper();
	    this.hideSyncStagesWrapper();
	    this.showButtonWrapper();
	    main_core.Dom.addClass(this.syncTitleWrapper, '--error');
	    if (main_core.Type.isElementNode(this.activeStatusNode)) {
	      this.activeStatusNode.innerHTML = main_core.Text.encode(main_core.Loc.getMessage('CAL_STATUS_SYNC_ERROR').toUpperCase());
	    }
	    const closeButton = this.buttonWrapper.querySelector('.ui-btn[data-role="close_button"]');
	    if (main_core.Type.isElementNode(closeButton)) {
	      closeButton.style.display = '';
	      main_core.Event.bind(closeButton, 'click', () => {
	        BX.SidePanel.Instance.getOpenSliders().forEach(slider => {
	          if (['calendar:sync-slider', 'calendar:section-slider', this.SLIDER_NAME].includes(slider.getUrl())) {
	            slider.close();
	          }
	        });
	        BX.reload();
	      });
	    }
	    const continueButton = this.buttonWrapper.querySelector('.ui-btn[data-role="continue_btn"]');
	    if (main_core.Type.isElementNode(continueButton)) {
	      continueButton.style.display = 'none';
	    }
	  }
	}

	let _$b = t => t,
	  _t$b;
	class SyncStageUnit {
	  constructor(options) {
	    this.name = options.name || '';
	    this.title = options.title || '';
	    this.doneStatus = false;
	  }
	  renderTo(outerWrapper) {
	    if (main_core.Type.isElementNode(outerWrapper)) {
	      outerWrapper.appendChild(this.getContent());
	    }
	    main_core_events.EventEmitter.emit('BX.Calendar.Sync.Interface.SyncStageUnit:onRenderDone');
	  }
	  getContent() {
	    this.contentNode = main_core.Tag.render(_t$b || (_t$b = _$b`
			<div class="calendar-sync__content-block --space-bottom-xl">
				<div class="calendar-sync__content-text --icon-check --disabled">${0}</div>
			</div>
		`), this.title);
	    return this.contentNode;
	  }
	  setDone() {
	    this.doneStatus = true;
	    main_core.Dom.removeClass(this.contentNode.querySelector('.--icon-check'), '--disabled');
	  }
	  setUndone() {
	    this.doneStatus = false;
	    main_core.Dom.addClass(this.contentNode.querySelector('.--icon-check'), '--disabled');
	  }
	}

	let _$c = t => t,
	  _t$c,
	  _t2$b,
	  _t3$a;
	class GoogleSyncWizard extends SyncWizard {
	  constructor() {
	    super();
	    this.TYPE = 'google';
	    this.SLIDER_NAME = 'calendar:sync-wizard-google';
	    this.STAGE_1_CODE = 'google-to-b24';
	    this.STAGE_2_CODE = 'b24-to-google';
	    this.STAGE_3_CODE = 'b24-events-to-google';
	    this.GOOGLE_ON_MOBILE_HELPDESK = 15456338;
	    this.setEventNamespace('BX.Calendar.Sync.Interface.GoogleSyncWizard');
	    this.setAccountName(main_core.Loc.getMessage('CALENDAR_TITLE_GOOGLE'));
	    this.setSyncStages();
	    this.logoIconClass = '--google';
	  }
	  getHelpLinkWrapper() {
	    let link;
	    this.helpLinkWrapper = main_core.Tag.render(_t$c || (_t$c = _$c`
			<div class="calendar-sync__content-block --align-center --space-bottom" style="display: none;">
				${0}
			</div>
		`), link = main_core.Tag.render(_t2$b || (_t2$b = _$c`<a href="#" class="calendar-sync__content-link">
					${0}
				</a>`), main_core.Loc.getMessage('CAL_SYNC_NO_GOOGLE_ON_PHONE')));
	    main_core.Event.bind(link, 'click', () => {
	      const helper = calendar_util.Util.getBX().Helper;
	      if (helper) {
	        helper.show("redirect=detail&code=" + this.GOOGLE_ON_MOBILE_HELPDESK);
	      }
	    });
	    return this.helpLinkWrapper;
	  }
	  getFinalCheckWrapper() {
	    this.finalCheckWrapper = main_core.Tag.render(_t3$a || (_t3$a = _$c`
			<div style="display: none;">
				<div class="calendar-sync__content-block --space-bottom">
					<div class="calendar-sync__balloon --progress">
						<div class="calendar-sync__content-text calendar-sync__content-title --show-for-progress">${0}</div>
						<div class="calendar-sync__content-text calendar-sync__content-subtitle --show-for-progress">${0}</div>
						<div class="calendar-sync__content-text calendar-sync__content-title --show-for-done">${0}</div>
						<div class="calendar-sync__content-text calendar-sync__content-subtitle --show-for-done">${0}</div>
						<div class="calendar-sync__balloon--icon"></div>
					</div>
				</div>
				${0}
				${0}
			</div>
		`), main_core.Loc.getMessage('CAL_SYNC_LETS_CHECK'), main_core.Loc.getMessage('CAL_SYNC_CREATE_EVENT_GOOGLE'), main_core.Loc.getMessage('CAL_SYNC_NEW_EVENT_ADDED_GOOGLE'), main_core.Loc.getMessage('CAL_SYNC_NEW_EVENT_YOULL_SEE'), this.getSkeletonWrapper(), this.getNewEventCardWrapper());
	    return this.finalCheckWrapper;
	  }
	  setSyncStages() {
	    this.syncStagesList = [new SyncStageUnit({
	      name: this.STAGE_1_CODE,
	      title: main_core.Loc.getMessage('CAL_SYNC_STAGE_GOOGLE_1')
	    }), new SyncStageUnit({
	      name: this.STAGE_2_CODE,
	      title: main_core.Loc.getMessage('CAL_SYNC_STAGE_GOOGLE_2')
	    }), new SyncStageUnit({
	      name: this.STAGE_3_CODE,
	      title: main_core.Loc.getMessage('CAL_SYNC_STAGE_GOOGLE_3')
	    })];
	  }
	  updateState(stateData) {
	    super.updateState(stateData);
	    this.getSyncStages().forEach(stage => {
	      if (stateData.stage === 'connection_created' && stage.name === this.STAGE_1_CODE) {
	        stage.setDone();
	      } else if (stateData.stage === 'import_finished' && (stage.name === this.STAGE_1_CODE || stage.name === this.STAGE_2_CODE)) {
	        stage.setDone();
	      } else if (stateData.stage === 'export_finished') {
	        stage.setDone();
	        if (stage.name === this.STAGE_3_CODE) {
	          this.setActiveStatusFinished();
	          this.showButtonWrapper();
	          this.showInfoStatusWrapper();
	          this.showConfetti();
	          this.emit('onConnectionCreated');
	        }
	      }
	    });
	  }
	  getSkeletonTitle() {
	    return main_core.Loc.getMessage('CAL_SYNC_NEW_EVENT_GOOGLE_TITLE');
	  }
	}

	class GoogleTemplate extends InterfaceTemplate {
	  constructor(provider, connection = null) {
	    super({
	      title: main_core.Loc.getMessage("CALENDAR_TITLE_GOOGLE"),
	      helpDeskCode: '6030429',
	      titleInfoHeader: main_core.Loc.getMessage('CAL_CONNECT_GOOGLE_CALENDAR'),
	      descriptionInfoHeader: main_core.Loc.getMessage('CAL_GOOGLE_CONNECT_DESCRIPTION'),
	      titleActiveHeader: main_core.Loc.getMessage('CAL_GOOGLE_CALENDAR_IS_CONNECT'),
	      descriptionActiveHeader: main_core.Loc.getMessage('CAL_GOOGLE_SELECTED_DESCRIPTION'),
	      sliderIconClass: 'calendar-sync-slider-header-icon-google',
	      iconPath: '/bitrix/images/calendar/sync/google.svg',
	      iconLogoClass: '--google',
	      color: '#387ced',
	      provider: provider,
	      connection: connection,
	      popupWithUpdateButton: true
	    });
	    this.HANDLE_CONNECTION_DELAY = 500;
	    this.sectionStatusObject = {};
	    this.sectionList = [];
	    this.handleSuccessConnectionDebounce = main_core.Runtime.debounce(this.handleSuccessConnection, this.HANDLE_CONNECTION_DELAY, this);
	  }
	  createConnection() {
	    const syncLink = this.provider.getSyncLink();
	    BX.util.popup(syncLink, 500, 600);
	    main_core.Event.bind(window, 'hashchange', this.handleSuccessConnectionDebounce);
	    main_core.Event.bind(window, 'message', this.handleSuccessConnectionDebounce);
	  }
	  handleSuccessConnection(event) {
	    if (window.location.hash === '#googleAuthSuccess' || event.data.title === 'googleAuthSuccess') {
	      calendar_util.Util.removeHash();
	      this.provider.setWizardSyncMode(true);
	      this.provider.saveConnection();
	      this.openSyncWizard();
	      this.provider.setStatus(this.provider.STATUS_SYNCHRONIZING);
	      this.provider.getInterfaceUnit().refreshButton();
	      main_core.Event.unbind(window, 'hashchange', this.handleSuccessConnectionDebounce);
	      main_core.Event.unbind(window, 'message', this.handleSuccessConnectionDebounce);
	    }
	  }
	  getSectionsForGoogle() {
	    return new Promise(resolve => {
	      BX.ajax.runAction('calendar.api.syncajax.getAllSectionsForGoogle', {
	        data: {
	          connectionId: this.connection.addParams.id
	        }
	      }).then(response => {
	        this.sectionList = response.data;
	        resolve(response.data);
	      }, response => {
	        resolve(response.errors);
	      });
	    });
	  }
	  onClickCheckSection(event) {
	    this.sectionStatusObject[event.target.value] = event.target.checked;
	    this.runUpdateInfo();
	    this.showUpdateSectionListNotification();
	  }
	  showAlertPopup() {
	    const messageBox = new ui_dialogs_messagebox.MessageBox({
	      className: this.id,
	      message: main_core.Loc.getMessage('GOOGLE_IS_NOT_CALDAV_SETTINGS_WARNING_MESSAGE'),
	      width: 500,
	      offsetLeft: 60,
	      offsetTop: 5,
	      padding: 7,
	      onOk: () => {
	        messageBox.close();
	      },
	      okCaption: 'OK',
	      buttons: BX.UI.Dialogs.MessageBoxButtons.OK,
	      popupOptions: {
	        zIndexAbsolute: 4020,
	        autoHide: true,
	        animation: 'fading-slide'
	      }
	    });
	    messageBox.show();
	  }
	  handleConnectButton() {
	    if (this.provider.hasSetSyncGoogleSettings()) {
	      this.createConnection();
	    } else {
	      this.showAlertPopup();
	    }
	  }
	  openSyncWizard() {
	    if (!this.wizard) {
	      this.wizard = new GoogleSyncWizard();
	      this.wizard.openSlider();
	      this.provider.setActiveWizard(this.wizard);
	    }
	  }
	  sendRequestRemoveConnection(id) {
	    this.deactivateConnection(id);
	  }
	}

	let _$d = t => t,
	  _t$d,
	  _t2$c,
	  _t3$b,
	  _t4$a,
	  _t5$8,
	  _t6$6,
	  _t7$5,
	  _t8$3,
	  _t9$3,
	  _t10$3,
	  _t11$2;
	class IcloudAuthDialog extends main_core_events.EventEmitter {
	  constructor(options = {}) {
	    super();
	    this.zIndex = 3100;
	    this.DOM = {};
	    this.appPasswordTemplate = 'xxxx-xxxx-xxxx-xxxx';
	    this.type = options.type;
	    this.setEventNamespace('BX.Calendar.Sync.Icloud');
	    this.keyHandler = this.handleKeyPress.bind(this);
	    this.checkOutsideClickClose = this.checkOutsideClickClose.bind(this);
	    this.outsideMouseDownClose = this.outsideMouseDownClose.bind(this);
	    this.initAlertBlock();
	  }
	  show() {
	    this.popup = new main_popup.Popup({
	      className: 'calendar-sync__auth-popup calendar-sync__scope',
	      titleBar: main_core.Loc.getMessage('CAL_ICLOUD_AUTH_TITLE'),
	      draggable: true,
	      content: this.getContainer(),
	      width: 475,
	      animation: 'fading-slide',
	      zIndexAbsolute: this.zIndex,
	      cacheable: false,
	      closeByEsc: true,
	      closeIcon: true,
	      contentBackground: "#fff",
	      overlay: {
	        opacity: 15
	      },
	      lightShadow: true,
	      buttons: [new BX.UI.Button({
	        text: main_core.Loc.getMessage('CAL_ICLOUD_CONNECT_BUTTON'),
	        className: `ui-btn ui-btn-md ui-btn-success ui-btn-round`,
	        events: {
	          click: this.authorize.bind(this)
	        }
	      }), new BX.UI.Button({
	        text: main_core.Loc.getMessage('EC_SEC_SLIDER_CANCEL'),
	        className: 'ui-btn ui-btn-md ui-btn-light-border ui-btn-round',
	        events: {
	          click: this.close.bind(this)
	        }
	      })],
	      events: {
	        onPopupClose: this.close.bind(this)
	      }
	    });
	    this.popup.show();
	    main_core.Event.bind(document, 'keydown', this.keyHandler);
	    main_core.Event.bind(document, 'mouseup', this.checkOutsideClickClose);
	    main_core.Event.bind(document, 'mousedown', this.outsideMouseDownClose);
	  }
	  authorize() {
	    if (this.isFormDataValid()) {
	      const saveBtn = this.popup.getButtons()[0];
	      saveBtn.setClocking(true);
	      saveBtn.setDisabled(true);
	      const cancelButton = this.popup.getButtons()[1];
	      cancelButton.setDisabled(true);
	      if (this.DOM.container.contains(this.DOM.alertBlock)) {
	        main_core.Dom.remove(this.DOM.alertBlock);
	      }
	      this.emit('onSubmit', new main_core_events.BaseEvent({
	        data: {
	          appleId: this.DOM.appleIdInput.value.toString().trim(),
	          appPassword: this.DOM.appPasswordInput.value.toString().trim()
	        }
	      }));
	    } else {
	      this.highlightInvalidFormData();
	    }
	  }
	  isFormDataValid() {
	    return this.DOM.appleIdInput.value.toString().trim() !== '' && this.DOM.appPasswordInput.value.toString().trim() !== '';
	  }
	  highlightInvalidFormData() {
	    const saveBtn = this.popup.getButtons()[0];
	    saveBtn.setClocking(false);
	    saveBtn.setDisabled(false);
	    const cancelButton = this.popup.getButtons()[1];
	    cancelButton.setDisabled(false);
	    if (this.DOM.appleIdInput.value.toString().trim() === '') {
	      this.highlightInvalidAppleIdInput();
	    }
	    if (this.DOM.appPasswordInput.value.toString().trim() === '') {
	      this.highlightInvalidPasswordInput();
	    }
	  }
	  highlightInvalidAppleIdInput() {
	    main_core.Dom.addClass(this.DOM.appleIdInput, 'calendar-field-string-error');
	    const clearInvalidation = () => {
	      main_core.Dom.removeClass(this.DOM.appleIdInput, 'calendar-field-string-error');
	      main_core.Event.unbind(this.DOM.appleIdInput, 'change', clearInvalidation);
	      main_core.Event.unbind(this.DOM.appleIdInput, 'keyup', clearInvalidation);
	    };
	    main_core.Event.bind(this.DOM.appleIdInput, 'change', clearInvalidation);
	    main_core.Event.bind(this.DOM.appleIdInput, 'keyup', clearInvalidation);
	  }
	  highlightInvalidPasswordInput() {
	    main_core.Dom.addClass(this.DOM.appPasswordInput, 'calendar-field-string-error');
	    const clearInvalidation = () => {
	      main_core.Dom.removeClass(this.DOM.appPasswordInput, 'calendar-field-string-error');
	      main_core.Event.unbind(this.DOM.appPasswordInput, 'change', clearInvalidation);
	      main_core.Event.unbind(this.DOM.appPasswordInput, 'keyup', clearInvalidation);
	    };
	    main_core.Event.bind(this.DOM.appPasswordInput, 'change', clearInvalidation);
	    main_core.Event.bind(this.DOM.appPasswordInput, 'keyup', clearInvalidation);
	    this.DOM.appPasswordInput.focus();
	  }
	  enableSaveButton() {
	    const saveBtn = this.popup.getButtons()[0];
	    saveBtn.setDisabled(false);
	    const cancelButton = this.popup.getButtons()[1];
	    cancelButton.setDisabled(false);
	  }
	  getContainer() {
	    this.DOM.container = main_core.Tag.render(_t$d || (_t$d = _$d`
			<div>
				${0}
				<div class="calendar-sync__auth-popup--row" id="calendar-apple-id-block">
					${0}
					${0}
					${0}
				</div>
				<div class="calendar-sync__auth-popup--row" id="calendar-apple-pass-block">
					<div class="calendar-sync__auth-popup--label-block">
						${0}
						${0}
					</div>
					<div class="ui-ctl ui-ctl-w100 ui-ctl-after-icon">
						${0}
						${0}
					</div>
					${0}
				</div>
			</div>
		`), this.getAppleInfoBlock(), this.getAppleIdTitle(), this.getAppleIdInput(), this.getAppleIdError(), this.getAppPasswordTitle(), this.getLearnMoreButton(), this.getAppPasswordInput(), this.getShowHidePasswordIcon(), this.getAppPasswordError());
	    return this.DOM.container;
	  }
	  getAppleInfoBlock() {
	    if (!this.DOM.appleInfo) {
	      this.DOM.appleInfo = main_core.Tag.render(_t2$c || (_t2$c = _$d`
				<div class="calendar-sync__auth-popup--info">
					<div class="calendar-sync__auth-popup--logo-image --icloud"></div>
					<div class="calendar-sync__auth-popup--logo-text">${0}</div>
				</div>
			`), main_core.Loc.getMessage('CAL_ICLOUD_INFO_BLOCK'));
	    }
	    return this.DOM.appleInfo;
	  }
	  getAppleIdTitle() {
	    if (!this.DOM.appleIdTitle) {
	      this.DOM.appleIdTitle = main_core.Tag.render(_t3$b || (_t3$b = _$d`
			<p class="calendar-sync__auth-popup--label-text">
				${0}
			</p>
			`), main_core.Loc.getMessage('CAL_ICLOUD_APPLE_ID_PLACEHOLDER'));
	    }
	    return this.DOM.appleIdTitle;
	  }
	  getAppPasswordTitle() {
	    if (!this.DOM.appPasswordTitle) {
	      this.DOM.appPasswordTitle = main_core.Tag.render(_t4$a || (_t4$a = _$d`
				<p class="calendar-sync__auth-popup--label-text">
					${0}
				</p>
			`), main_core.Loc.getMessage('CAL_ICLOUD_PASS_PLACEHOLDER'));
	    }
	    return this.DOM.appPasswordTitle;
	  }
	  getAppleIdError() {
	    if (!this.DOM.appleIdError) {
	      this.DOM.appleIdError = main_core.Tag.render(_t5$8 || (_t5$8 = _$d`
				<div class="calendar-sync__auth-popup--label-text --error">
					${0}
				</div>
			`), main_core.Loc.getMessage('CAL_ICLOUD_APPLE_ID_ERROR'));
	    }
	    return this.DOM.appleIdError;
	  }
	  getAppPasswordError() {
	    if (!this.DOM.appPasswordError) {
	      this.DOM.appPasswordError = main_core.Tag.render(_t6$6 || (_t6$6 = _$d`
				<div class="calendar-sync__auth-popup--label-text --error">
					${0}
				</div>
			`), main_core.Loc.getMessage('CAL_ICLOUD_APP_PASSWORD_ERROR', {
	        '#LINK_START#': '<a href="#" data-role="open-helpdesk-password">',
	        '#LINK_END#': '</a>'
	      }));
	      const link = this.DOM.appPasswordError.querySelector('a[data-role="open-helpdesk-password"]');
	      if (link) {
	        main_core.Event.bind(link, 'click', this.openHelpDesk.bind(this));
	      }
	    }
	    return this.DOM.appPasswordError;
	  }
	  getAppleIdInput() {
	    if (!this.DOM.appleIdInput) {
	      this.DOM.appleIdInput = main_core.Tag.render(_t7$5 || (_t7$5 = _$d`
				<input
					type="text"
					placeholder="${0}"
					class="calendar-field-string ui-ctl-element"
				/>
			`), main_core.Loc.getMessage('CAL_ICLOUD_AUTH_EMAIL_PLACEHOLDER'));
	      this.DOM.appleIdInput.onfocus = () => {
	        if (main_core.Dom.hasClass(this.DOM.appleIdInput, 'calendar-field-string-error')) {
	          main_core.Dom.removeClass(this.DOM.appleIdInput, 'calendar-field-string-error');
	          main_core.Dom.removeClass(this.DOM.appleIdError, 'show');
	        }
	      };
	      this.DOM.appleIdInput.onblur = () => {
	        if (!this.validateAppleIdInput() && !main_core.Dom.hasClass(this.DOM.appleIdInput, 'calendar-field-string-error')) {
	          main_core.Dom.addClass(this.DOM.appleIdInput, 'calendar-field-string-error');
	          main_core.Dom.addClass(this.DOM.appleIdError, 'show');
	        }
	      };
	    }
	    return this.DOM.appleIdInput;
	  }
	  getAppPasswordInput() {
	    if (!this.DOM.appPasswordInput) {
	      this.DOM.appPasswordInput = main_core.Tag.render(_t8$3 || (_t8$3 = _$d`
				<input
					type="password"
					placeholder="${0}"
					class="calendar-field-string ui-ctl-element"
					required maxlength="19"
				/>
			`), main_core.Loc.getMessage('CAL_ICLOUD_AUTH_APPPASS_PLACEHOLDER'));
	      main_core.Event.bind(this.DOM.appPasswordInput, 'input', this.validateAppPasswordInput.bind(this));
	    }
	    return this.DOM.appPasswordInput;
	  }
	  getShowHidePasswordIcon() {
	    if (!this.DOM.showHidePasswordIcon) {
	      this.DOM.showHidePasswordIcon = main_core.Tag.render(_t9$3 || (_t9$3 = _$d`
				<div class="ui-ctl-after calendar-sync__auth-popup--icon-adjust-password"></div>
			`));
	      main_core.Event.bind(this.DOM.showHidePasswordIcon, 'click', this.switchPasswordVisibility.bind(this));
	    }
	    return this.DOM.showHidePasswordIcon;
	  }
	  getLearnMoreButton() {
	    if (!this.DOM.learnMoreButton) {
	      this.DOM.learnMoreButton = main_core.Tag.render(_t10$3 || (_t10$3 = _$d`
				<span class="calendar-sync__auth-popup--learn-more">${0}</span>
			`), main_core.Loc.getMessage('CAL_ICLOUD_AUTH_APPPASS_ABOUT'));
	      main_core.Event.bind(this.DOM.learnMoreButton, 'click', this.openHelpDesk.bind(this));
	    }
	    return this.DOM.learnMoreButton;
	  }
	  initAlertBlock() {
	    if (!this.DOM.alertBlock) {
	      this.DOM.alertBlock = main_core.Tag.render(_t11$2 || (_t11$2 = _$d`
				<div class="ui-alert ui-alert-danger calendar-sync__auth-error">
	                <span class="ui-alert-message">${0}</span>
				</div>
			`), main_core.Loc.getMessage('CAL_ICLOUD_AUTH_ERROR'));
	    }
	  }
	  showErrorAuthorizationAlert() {
	    this.highlightInvalidAppleIdInput();
	    this.highlightInvalidPasswordInput();
	    this.enableSaveButton();
	    if (!this.DOM.container.contains(this.DOM.alertBlock)) {
	      main_core.Dom.append(this.DOM.alertBlock, this.DOM.container);
	    }
	  }
	  validateAppleIdInput() {
	    const emailRegExp = /^[a-zA-Z\d.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z\d-]+(?:\.[a-zA-Z\d-]+)*$/;
	    const input = this.DOM.appleIdInput.value.toString().trim();
	    if (input === '') {
	      return true;
	    }
	    return emailRegExp.test(input);
	  }
	  validateAppPasswordInput() {
	    const appPasswordRegExp = /^[a-z]{4}-[a-z]{4}-[a-z]{4}-[a-z]{4}$/;
	    const input = this.completeWithTemplate(this.DOM.appPasswordInput.value.toString().trim());
	    if (appPasswordRegExp.test(input)) {
	      main_core.Dom.removeClass(this.DOM.appPasswordInput, 'calendar-field-string-error');
	      main_core.Dom.removeClass(this.DOM.appPasswordError, 'show');
	    } else {
	      main_core.Dom.addClass(this.DOM.appPasswordInput, 'calendar-field-string-error');
	      main_core.Dom.addClass(this.DOM.appPasswordError, 'show');
	    }
	  }
	  switchPasswordVisibility() {
	    if (main_core.Dom.hasClass(this.DOM.showHidePasswordIcon, '--hide')) {
	      this.DOM.appPasswordInput.type = 'password';
	      main_core.Dom.removeClass(this.DOM.showHidePasswordIcon, '--hide');
	    } else {
	      this.DOM.appPasswordInput.type = 'text';
	      main_core.Dom.addClass(this.DOM.showHidePasswordIcon, '--hide');
	    }
	  }
	  clearForm() {
	    this.DOM.appPasswordInput.value = '';
	    this.DOM.appleIdInput.value = '';
	    if (main_core.Dom.hasClass(this.DOM.appleIdInput, 'calendar-field-string-error')) {
	      main_core.Dom.removeClass(this.DOM.appleIdInput, 'calendar-field-string-error');
	    }
	    if (main_core.Dom.hasClass(this.DOM.appPasswordInput, 'calendar-field-string-error')) {
	      main_core.Dom.removeClass(this.DOM.appPasswordInput, 'calendar-field-string-error');
	    }
	    if (main_core.Dom.hasClass(this.DOM.appleIdError, 'show')) {
	      main_core.Dom.removeClass(this.DOM.appleIdError, 'show');
	    }
	    if (main_core.Dom.hasClass(this.DOM.appPasswordError, 'show')) {
	      main_core.Dom.removeClass(this.DOM.appPasswordError, 'show');
	    }
	  }
	  completeWithTemplate(password) {
	    const addition = this.appPasswordTemplate.slice(password.length, this.appPasswordTemplate.length);
	    password += addition;
	    return password;
	  }
	  openHelpDesk() {
	    const helpDeskCode = '15426356';
	    top.BX.Helper.show('redirect=detail&code=' + helpDeskCode);
	  }
	  handleKeyPress(e) {
	    if (e.keyCode === calendar_util.Util.getKeyCode('enter')) {
	      this.authorize();
	    } else if (e.keyCode === calendar_util.Util.getKeyCode('escape')) {
	      this.close();
	    }
	  }
	  checkOutsideClickClose(e) {
	    let target = e.target || e.srcElement;
	    this.outsideMouseUp = !target.closest('div.popup-window');
	    if (this.outsideMouseUp && this.outsideMouseDown && this.checkTopSlider()) {
	      this.close();
	    }
	  }
	  outsideMouseDownClose(e) {
	    let target = e.target || e.srcElement;
	    this.outsideMouseDown = !target.closest('div.popup-window');
	  }
	  close() {
	    if (this.popup) {
	      this.popup.destroy();
	    }
	    main_core.Event.unbind(document, 'keydown', this.keyHandler);
	    main_core.Event.unbind(document, 'mouseup', this.checkOutsideClickClose);
	    main_core.Event.unbind(document, 'mousedown', this.outsideMouseDownClose);
	    this.clearForm();
	  }
	  checkTopSlider() {
	    return !calendar_util.Util.getBX().SidePanel.Instance.getTopSlider();
	  }
	}

	let _$e = t => t,
	  _t$e;
	class IcloudSyncWizard extends SyncWizard {
	  constructor() {
	    super();
	    this.TYPE = 'icloud';
	    this.SLIDER_NAME = 'calendar:sync-wizard-icloud';
	    this.STAGE_1_CODE = 'icloud-to-b24';
	    this.STAGE_2_CODE = 'b24-events-to-icloud';
	    this.STAGE_3_CODE = 'b24-to-icloud';
	    this.setEventNamespace('BX.Calendar.Sync.Interface.IcloudSyncWizard');
	    this.setAccountName(main_core.Loc.getMessage('CALENDAR_TITLE_ICLOUD'));
	    this.setSyncStages();
	    this.logoIconClass = '--icloud';
	  }
	  getHelpLinkWrapper() {
	    return '';
	  }
	  getFinalCheckWrapper() {
	    this.finalCheckWrapper = main_core.Tag.render(_t$e || (_t$e = _$e`
			<div style="display: none;">
				<div class="calendar-sync__content-block --space-bottom">
					<div class="calendar-sync__balloon --progress">
						<div class="calendar-sync__content-text calendar-sync__content-title --show-for-progress">${0}</div>
						<div class="calendar-sync__content-text calendar-sync__content-subtitle --show-for-progress">${0}</div>
						<div class="calendar-sync__content-text calendar-sync__content-title --show-for-done">${0}</div>
						<div class="calendar-sync__content-text calendar-sync__content-subtitle --show-for-done">${0}</div>
						<div class="calendar-sync__balloon--icon"></div>
					</div>
				</div>
				${0}
				${0}
			</div>
		`), main_core.Loc.getMessage('CAL_SYNC_LETS_CHECK'), main_core.Loc.getMessage('CAL_SYNC_CREATE_EVENT_ICLOUD'), main_core.Loc.getMessage('CAL_SYNC_NEW_EVENT_ADDED_FROM_ICLOUD'), main_core.Loc.getMessage('CAL_SYNC_NEW_EVENT_YOULL_SEE'), this.getSkeletonWrapper(), this.getNewEventCardWrapper());
	    return this.finalCheckWrapper;
	  }
	  setSyncStages() {
	    this.syncStagesList = [new SyncStageUnit({
	      name: this.STAGE_1_CODE,
	      title: main_core.Loc.getMessage('CAL_SYNC_STAGE_ICLOUD_1')
	    }), new SyncStageUnit({
	      name: this.STAGE_2_CODE,
	      title: main_core.Loc.getMessage('CAL_SYNC_STAGE_ICLOUD_2')
	    }), new SyncStageUnit({
	      name: this.STAGE_3_CODE,
	      title: main_core.Loc.getMessage('CAL_SYNC_STAGE_ICLOUD_3')
	    })];
	  }
	  updateState(stateData) {
	    super.updateState(stateData);
	    this.getSyncStages().forEach(stage => {
	      if (stateData.stage === 'connection_created' && stage.name === this.STAGE_1_CODE) {
	        stage.setDone();
	      } else if (stateData.stage === 'import_finished' && (stage.name === this.STAGE_1_CODE || stage.name === this.STAGE_2_CODE)) {
	        stage.setDone();
	      } else if (stateData.stage === 'export_finished') {
	        stage.setDone();
	        if (stage.name === this.STAGE_3_CODE) {
	          this.setActiveStatusFinished();
	          this.showButtonWrapper();
	          this.showInfoStatusWrapper();
	          this.showConfetti();
	          this.emit('onConnectionCreated');
	        }
	      }
	    });
	  }
	  getSkeletonTitle() {
	    return main_core.Loc.getMessage('CAL_SYNC_NEW_EVENT_ICLOUD_TITLE');
	  }
	}

	let _$f = t => t,
	  _t$f;
	class WarnSyncIcloudDialog {
	  constructor(options = {}) {
	    this.zIndex = 3100;
	    this.DOM = {};
	    this.authDialog = options.authDialog;
	  }
	  show() {
	    this.popup = new main_popup.Popup({
	      className: 'calendar-sync__auth-popup calendar-sync__scope',
	      titleBar: main_core.Loc.getMessage('CAL_ICLOUD_ALERT_OTHER_APPLE_SYNC_TITLE'),
	      width: 500,
	      draggable: true,
	      content: this.getContainer(),
	      cacheable: false,
	      closeByEsc: true,
	      closeIcon: true,
	      contentBackground: "#fff",
	      overlay: {
	        opacity: 15
	      },
	      buttons: [new BX.UI.Button({
	        text: main_core.Loc.getMessage('CAL_ICLOUD_ALERT_OTHER_APPLE_SYNC_LEARN_MORE'),
	        className: 'ui-btn ui-btn-md ui-btn-primary',
	        events: {
	          click: this.openHelpDesk.bind(this)
	        }
	      }), new BX.UI.Button({
	        text: main_core.Loc.getMessage('CAL_BUTTON_CONTINUE'),
	        className: 'ui-btn ui-btn-md ui-btn-light',
	        events: {
	          click: this.openAuthDialog.bind(this)
	        }
	      })],
	      events: {
	        onPopupClose: this.close.bind(this)
	      }
	    });
	    this.popup.show();
	  }
	  getContainer() {
	    this.DOM.container = main_core.Tag.render(_t$f || (_t$f = _$f`
			<div>
				${0}
			</div>
		`), this.getAlertInformation());
	    return this.DOM.container;
	  }
	  getAlertInformation() {
	    this.DOM.alertBlock = new BX.UI.Alert({
	      text: main_core.Loc.getMessage('CAL_ICLOUD_ALERT_OTHER_APPLE_SYNC_INFO'),
	      color: BX.UI.Alert.Color.WARNING,
	      icon: BX.UI.Alert.Icon.INFO
	    });
	    const container = this.DOM.alertBlock.getContainer();
	    const text = container.querySelector('.ui-alert-message');
	    main_core.Dom.addClass(text, 'calendar-sync__alert-popup--text');
	    return container;
	  }
	  openHelpDesk() {
	    const helpDeskCode = '16020988';
	    top.BX.Helper.show('redirect=detail&code=' + helpDeskCode);
	  }
	  disableConnection() {
	    BX.ajax.runAction('calendar.api.syncajax.disableIphoneOrMacConnection').then(() => {
	      this.authDialog.show();
	      this.close();
	      calendar_util.Util.setIphoneConnectionStatus(false);
	      calendar_util.Util.setMacConnectionStatus(false);
	    });
	  }
	  openAuthDialog() {
	    this.authDialog.show();
	    this.close();
	  }
	  close() {
	    if (this.popup) {
	      this.popup.destroy();
	    }
	  }
	}

	class IcloudTemplate extends InterfaceTemplate {
	  constructor(provider, connection = null) {
	    super({
	      title: main_core.Loc.getMessage("CALENDAR_TITLE_ICLOUD"),
	      helpDeskCode: '6030429',
	      titleInfoHeader: main_core.Loc.getMessage('CAL_CONNECT_ICLOUD_CALENDAR'),
	      descriptionInfoHeader: main_core.Loc.getMessage('CAL_ICLOUD_CONNECT_DESCRIPTION'),
	      titleActiveHeader: main_core.Loc.getMessage('CAL_CALENDAR_IS_CONNECT'),
	      descriptionActiveHeader: main_core.Loc.getMessage('CAL_ICLOUD_SELECTED_DESCRIPTION'),
	      sliderIconClass: 'calendar-sync-slider-header-icon-icloud',
	      iconPath: '/bitrix/images/calendar/sync/icloud.svg',
	      iconLogoClass: '--icloud',
	      color: '#95a0af',
	      provider: provider,
	      connection: connection,
	      popupWithUpdateButton: true
	    });
	    this.sectionStatusObject = {};
	    this.sectionList = [];
	  }
	  createConnection(data) {
	    BX.ajax.runAction('calendar.api.syncajax.createIcloudConnection', {
	      data: {
	        appleId: data.appleId,
	        appPassword: data.appPassword
	      }
	    }).then(response => {
	      const result = response.data;
	      if (result.status === 'success' && result.connectionId) {
	        this.openSyncWizard(data.appleId);
	        this.syncCalendarsWithIcloud(result.connectionId);
	      }
	    }, () => {
	      this.authDialog.showErrorAuthorizationAlert();
	    });
	  }
	  syncCalendarsWithIcloud(connectionId) {
	    this.authDialog.close();
	    return new Promise(resolve => {
	      BX.ajax.runAction('calendar.api.syncajax.syncIcloudConnection', {
	        data: {
	          connectionId: connectionId
	        }
	      }).then(response => {
	        this.provider.setStatus(this.provider.STATUS_SUCCESS);
	        if (connectionId) {
	          this.provider.getConnection().setId(connectionId);
	          this.provider.getConnection().setStatus(true);
	          this.provider.getConnection().setConnected(true);
	          this.provider.getConnection().setSyncDate(new Date());
	        }
	        resolve(response.data);
	      }, response => {
	        this.provider.setStatus(this.provider.STATUS_FAILED);
	        this.provider.setWizardState({
	          status: this.provider.ERROR_CODE,
	          vendorName: this.provider.type
	        });
	        resolve(response.errors);
	      });
	    });
	  }
	  getSectionsForIcloud() {
	    return new Promise(resolve => {
	      BX.ajax.runAction('calendar.api.syncajax.getAllSectionsForIcloud', {
	        data: {
	          connectionId: this.connection.addParams.id
	        }
	      }).then(response => {
	        this.sectionList = response.data;
	        resolve(response.data);
	      }, response => {
	        resolve(response.errors);
	      });
	    });
	  }
	  onClickCheckSection(event) {
	    this.sectionStatusObject[event.target.value] = event.target.checked;
	    this.runUpdateInfo();
	    this.showUpdateSectionListNotification();
	  }
	  handleConnectButton() {
	    this.initPopup();
	    if (calendar_util.Util.isIphoneConnected() || calendar_util.Util.isMacConnected()) {
	      this.alertSyncPopup.show();
	    } else {
	      this.authDialog.show();
	    }
	  }
	  initPopup() {
	    if (!this.authDialog) {
	      this.authDialog = new IcloudAuthDialog();
	      main_core_events.EventEmitter.unsubscribeAll('BX.Calendar.Sync.Icloud:onSubmit');
	      main_core_events.EventEmitter.subscribe('BX.Calendar.Sync.Icloud:onSubmit', e => {
	        this.createConnection(e.data);
	      });
	    }
	    if (!this.alertSyncPopup) {
	      this.alertSyncPopup = new WarnSyncIcloudDialog({
	        authDialog: this.authDialog
	      });
	    }
	  }
	  openSyncWizard(appleId) {
	    this.provider.setWizardSyncMode(true);
	    this.wizard = new IcloudSyncWizard();
	    this.wizard.openSlider();
	    this.provider.setActiveWizard(this.wizard);
	    main_core_events.EventEmitter.subscribeOnce('BX.Calendar.Sync.Interface.SyncStageUnit:onRenderDone', () => {
	      this.wizard.updateState({
	        stage: 'connection_created',
	        vendorName: 'icloud',
	        accountName: appleId
	      });
	    });
	  }
	  sendRequestRemoveConnection(id) {
	    this.deactivateConnection(id);
	  }
	}

	let _$g = t => t,
	  _t$g;
	class Office365SyncWizard extends SyncWizard {
	  constructor() {
	    super();
	    this.TYPE = 'office365';
	    this.SLIDER_NAME = 'calendar:sync-wizard-office365';
	    this.STAGE_1_CODE = 'office365-to-b24';
	    this.STAGE_2_CODE = 'sections_sync_finished';
	    this.STAGE_3_CODE = 'events_sync_finished';
	    this.setEventNamespace('BX.Calendar.Sync.Interface.Office365SyncWizard');
	    this.setAccountName(main_core.Loc.getMessage('CALENDAR_TITLE_OFFICE365'));
	    this.setSyncStages();
	    this.logoIconClass = '--office365';
	  }
	  getHelpLinkWrapper() {
	    return '';
	  }
	  getFinalCheckWrapper() {
	    this.finalCheckWrapper = main_core.Tag.render(_t$g || (_t$g = _$g`
			<div style="display: none;">
				<div class="calendar-sync__content-block --space-bottom">
					<div class="calendar-sync__balloon --progress">
						<div class="calendar-sync__content-text calendar-sync__content-title --show-for-progress">${0}</div>
						<div class="calendar-sync__content-text calendar-sync__content-subtitle --show-for-progress">${0}</div>
						<div class="calendar-sync__content-text calendar-sync__content-title --show-for-done">${0}</div>
						<div class="calendar-sync__content-text calendar-sync__content-subtitle --show-for-done">${0}</div>
						<div class="calendar-sync__balloon--icon"></div>
					</div>
				</div>
				${0}
				${0}
			</div>
		`), main_core.Loc.getMessage('CAL_SYNC_LETS_CHECK'), main_core.Loc.getMessage('CAL_SYNC_CREATE_EVENT_OFFICE365'), main_core.Loc.getMessage('CAL_SYNC_NEW_EVENT_ADDED_FROM_OFFICE365'), main_core.Loc.getMessage('CAL_SYNC_NEW_EVENT_YOULL_SEE'), this.getSkeletonWrapper(), this.getNewEventCardWrapper());
	    return this.finalCheckWrapper;
	  }
	  setSyncStages() {
	    this.syncStagesList = [new SyncStageUnit({
	      name: this.STAGE_1_CODE,
	      title: main_core.Loc.getMessage('CAL_SYNC_STAGE_OFFICE365_1')
	    }), new SyncStageUnit({
	      name: this.STAGE_2_CODE,
	      title: main_core.Loc.getMessage('CAL_SYNC_STAGE_OFFICE365_2')
	    }), new SyncStageUnit({
	      name: this.STAGE_3_CODE,
	      title: main_core.Loc.getMessage('CAL_SYNC_STAGE_OFFICE365_3')
	    })];
	  }
	  updateState(stateData) {
	    super.updateState(stateData);
	    this.getSyncStages().forEach(stage => {
	      if (stateData.stage === 'connection_created' && stage.name === this.STAGE_1_CODE) {
	        stage.setDone();
	      } else if (stateData.stage === 'import_finished' && (stage.name === this.STAGE_1_CODE || stage.name === this.STAGE_2_CODE)) {
	        stage.setDone();
	      } else if (stateData.stage === 'export_finished') {
	        stage.setDone();
	        this.setActiveStatusFinished();
	        this.showButtonWrapper();
	        this.showInfoStatusWrapper();
	        this.showConfetti();
	        this.emit('onConnectionCreated');
	      }
	    });
	  }
	  getSkeletonTitle() {
	    return main_core.Loc.getMessage('CAL_SYNC_NEW_EVENT_OFFICE365_TITLE');
	  }
	}

	class Office365template extends InterfaceTemplate {
	  constructor(provider, connection = null) {
	    super({
	      title: main_core.Loc.getMessage("CALENDAR_TITLE_OFFICE365"),
	      helpDeskCode: '6030429',
	      titleInfoHeader: main_core.Loc.getMessage('CAL_CONNECT_OFFICE365_CALENDAR'),
	      descriptionInfoHeader: main_core.Loc.getMessage('CAL_OFFICE365_CONNECT_DESCRIPTION'),
	      titleActiveHeader: main_core.Loc.getMessage('CAL_OFFICE365_CALENDAR_IS_CONNECT'),
	      descriptionActiveHeader: main_core.Loc.getMessage('CAL_OFFICE365_SELECTED_DESCRIPTION'),
	      sliderIconClass: 'calendar-sync-slider-header-icon-office365',
	      iconPath: '/bitrix/images/calendar/sync/office365.svg',
	      iconLogoClass: '--office365',
	      color: '#fc1d1d',
	      provider: provider,
	      connection: connection,
	      popupWithUpdateButton: true
	    });
	    this.HANDLE_CONNECTION_DELAY = 500;
	    this.sectionStatusObject = {};
	    this.sectionList = [];
	    this.handleSuccessConnectionDebounce = main_core.Runtime.debounce(this.handleSuccessConnection, this.HANDLE_CONNECTION_DELAY, this);
	  }
	  createConnection() {
	    const syncLink = this.provider.getSyncLink();
	    BX.util.popup(syncLink, 500, 600);
	    main_core.Event.bind(window, 'hashchange', this.handleSuccessConnectionDebounce);
	  }
	  handleSuccessConnection(event) {
	    if (window.location.hash === '#office365AuthSuccess') {
	      calendar_util.Util.removeHash();
	      this.provider.setWizardSyncMode(true);
	      this.provider.saveConnection();
	      this.openSyncWizard();
	      this.provider.setStatus(this.provider.STATUS_SYNCHRONIZING);
	      this.provider.getInterfaceUnit().refreshButton();
	      main_core.Event.unbind(window, 'hashchange', this.handleSuccessConnectionDebounce);
	    }
	  }
	  onClickCheckSection(event) {
	    this.sectionStatusObject[event.target.value] = event.target.checked;
	    this.runUpdateInfo();
	    this.showUpdateSectionListNotification();
	  }
	  handleConnectButton() {
	    if (this.provider.hasSetSyncOffice365Settings()) {
	      this.createConnection();
	    } else {
	      this.showAlertPopup();
	    }
	  }
	  openSyncWizard() {
	    this.wizard = new Office365SyncWizard();
	    this.wizard.openSlider();
	    this.provider.setActiveWizard(this.wizard);
	  }
	  getSectionsForOffice365() {
	    return new Promise(resolve => {
	      BX.ajax.runAction('calendar.api.syncajax.getAllSectionsForOffice365', {
	        data: {
	          connectionId: this.connection.addParams.id
	        }
	      }).then(response => {
	        this.sectionList = response.data;
	        resolve(response.data);
	      }, response => {
	        resolve(response.errors);
	      });
	    });
	  }
	  sendRequestRemoveConnection(id) {
	    this.deactivateConnection(id);
	  }
	  showAlertPopup() {
	    const messageBox = new ui_dialogs_messagebox.MessageBox({
	      className: this.id,
	      message: main_core.Loc.getMessage('OFFICE365_IS_NOT_CALDAV_SETTINGS_WARNING_MESSAGE'),
	      width: 500,
	      offsetLeft: 60,
	      offsetTop: 5,
	      padding: 7,
	      onOk: () => {
	        messageBox.close();
	      },
	      okCaption: 'OK',
	      buttons: BX.UI.Dialogs.MessageBoxButtons.OK,
	      popupOptions: {
	        zIndexAbsolute: 4020,
	        autoHide: true,
	        animation: 'fading-slide'
	      }
	    });
	    messageBox.show();
	  }
	}

	let _$h = t => t,
	  _t$h,
	  _t2$d,
	  _t3$c,
	  _t4$b,
	  _t5$9,
	  _t6$7;
	class MacTemplate extends InterfaceTemplate {
	  constructor(provider, connection = null) {
	    super({
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
	    });
	    this.alreadyConnectedToNew = calendar_util.Util.isIcloudConnected();
	    if (this.alreadyConnectedToNew) {
	      this.warningText = main_core.Loc.getMessage('CAL_SYNC_WARNING_IPHONE_AND_MAC_CONNECTED');
	      this.mobileSyncButtonText = main_core.Loc.getMessage('CALENDAR_CHECK_ICLOUD_SETTINGS');
	    } else {
	      this.warningText = main_core.Loc.getMessage('CAL_SYNC_WARNING_IPHONE_AND_MAC');
	      this.mobileSyncButtonText = main_core.Loc.getMessage('CALENDAR_CONNECT_ICLOUD');
	    }
	  }
	  getPortalAddress() {
	    return this.portalAddress;
	  }
	  getContentInfoBody() {
	    return main_core.Tag.render(_t$h || (_t$h = _$h`
			${0}
			${0}
		`), this.getContentInfoBodyHeader(), this.getContentInfoWarning());
	  }
	  getActiveConnectionContent() {
	    return main_core.Tag.render(_t2$d || (_t2$d = _$h`
			<div class="calendar-sync-wrap calendar-sync-wrap-detail">
				<div class="calendar-sync-header">
					<span class="calendar-sync-header-text">${0}</span>
				</div>
				${0}
			</div>
		`), this.getHeaderTitle(), this.getContentActiveBody());
	  }
	  getContentActiveBody() {
	    return main_core.Tag.render(_t3$c || (_t3$c = _$h`
			${0}
			<div class="calendar-sync-slider-section calendar-sync-slider-section-banner">
				${0}
			</div>
		`), this.getContentActiveBodyHeader(), this.getContentBodyConnect());
	  }
	  getContentActiveBodyHeader() {
	    const timestamp = this.connection.getSyncDate().getTime() / 1000;
	    const syncTime = timestamp ? calendar_util.Util.formatDateUsable(timestamp) + ' ' + BX.date.format(calendar_util.Util.getTimeFormatShort(), timestamp) : '';
	    return main_core.Tag.render(_t4$b || (_t4$b = _$h`
			<div class="calendar-sync-slider-section">
				<div class="calendar-sync-slider-header-icon ${0}"></div>
				<div class="calendar-sync-slider-header">
				<div class="calendar-sync-slider-title">${0}</div>
				<div class="calendar-sync-slider-info">
					<span class="calendar-sync-slider-info-text">${0}</span>
					<span class="calendar-sync-slider-info-time">${0}</span>
				</div>
					<a class="calendar-sync-slider-link" href="javascript:void(0);" onclick="${0}">${0}</a>
				</div>
			</div>`), this.sliderIconClass, this.titleActiveHeader, main_core.Loc.getMessage('CAL_SYNC_LAST_SYNC_DATE'), syncTime, this.showHelp.bind(this), main_core.Loc.getMessage('CAL_TEXT_ABOUT_WORK_SYNC'));
	  }
	  getContentInfoBodyHeaderHelper() {
	    if (!this.headerHelper) {
	      this.headerHelper = main_core.Tag.render(_t5$9 || (_t5$9 = _$h`
				<div class="calendar-sync-slider-info">
					<span class="calendar-sync-slider-info-text">
						<a class="calendar-sync-slider-info-link">
							${0}
						</a>
					</span>
				</div>
			`), main_core.Loc.getMessage('CAL_CONNECT_PC'));
	      main_core.Event.bind(this.headerHelper, 'click', this.showExtendedInfoMacOs.bind(this));
	    }
	    return this.headerHelper;
	  }
	  showExtendedInfoMacOs() {
	    this.headerHelper.style.display = 'none';
	    main_core.Dom.append(this.getContentBodyConnect(), this.infoBodyHeader);
	  }
	  getContentBodyConnect() {
	    return main_core.Tag.render(_t6$7 || (_t6$7 = _$h`
			<div class="calendar-sync-slider-section calendar-sync-slider-section-col">
				<div class="calendar-sync-slider-header calendar-sync-slider-header-divide">
					<div class="calendar-sync-slider-subtitle">${0}</div>
				</div>
				<div class="calendar-sync-slider-info">
					<span class="calendar-sync-slider-info-text">${0}:</span>
					<ol class="calendar-sync-slider-info-list">
						<li class="calendar-sync-slider-info-item">
							<span class="calendar-sync-slider-info-text">${0}</span>
						</li>
						<li class="calendar-sync-slider-info-item">
							<span class="calendar-sync-slider-info-text">${0}</span>
						</li>
						<li class="calendar-sync-slider-info-item">
							<span class="calendar-sync-slider-info-text">${0}</span>
						</li>
						<li class="calendar-sync-slider-info-item">
							<span class="calendar-sync-slider-info-text">${0}</span>
						</li>
						<li class="calendar-sync-slider-info-item">
							<span class="calendar-sync-slider-info-text">${0}</span>
						</li>
						<li class="calendar-sync-slider-info-item">
							<span class="calendar-sync-slider-info-text">${0}</span>
						</li>
						<li class="calendar-sync-slider-info-item">
							<span class="calendar-sync-slider-info-text">${0}</span>
						</li>
					</ol>
					<span class="calendar-sync-slider-info-text">${0}</span>
					<div class="calendar-sync-slider-info" style="margin-top: 20px">
						<span class="calendar-sync-slider-info-text">
							<a class="calendar-sync-slider-info-link" href="javascript:void(0);" onclick="${0}">
								${0}
							</a>
						</span>
					</div>
				</div>
			</div>
		`), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_HEADER'), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_DESCRIPTION'), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_FIRST'), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_SECOND'), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_THIRD'), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_FOURTH'), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_FIFTH', {
	      '#PORTAL_ADDRESS#': this.provider.getPortalAddress()
	    }), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_SIXTH'), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_SEVENTH'), main_core.Loc.getMessage('CAL_MAC_INSTRUCTION_CONCLUSION'), this.showHelp.bind(this), main_core.Loc.getMessage('CAL_TEXT_ABOUT_WORK_SYNC'));
	  }
	  handleMobileButtonConnectClick() {
	    BX.SidePanel.Instance.getOpenSliders().forEach(slider => {
	      if (['calendar:auxiliary-sync-slider', 'calendar:item-sync-connect-mac'].includes(slider.getUrl())) {
	        slider.close();
	      }
	    });
	    const calendarContext = calendar_util.Util.getCalendarContext();
	    if (calendarContext) {
	      calendarContext.syncInterface.getIcloudProvider().getInterfaceUnit().getConnectionTemplate().handleConnectButton();
	    }
	  }
	  handleMobileButtonOtherSyncInfo() {
	    BX.SidePanel.Instance.getOpenSliders().forEach(slider => {
	      if (['calendar:auxiliary-sync-slider', 'calendar:item-sync-connect-mac'].includes(slider.getUrl())) {
	        slider.close();
	      }
	    });
	    const calendarContext = calendar_util.Util.getCalendarContext();
	    if (calendarContext) {
	      const connectionProvider = calendarContext.syncInterface.getIcloudProvider().getInterfaceUnit().connectionProvider;
	      connectionProvider.openActiveConnectionSlider(connectionProvider.getConnection());
	    }
	  }
	}

	class OutlookTemplate extends InterfaceTemplate {
	  constructor(provider, connection = null) {
	    super({
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
	    });
	  }
	}

	class YandexTemplate extends CaldavInterfaceTemplate {
	  constructor(provider, connection = null) {
	    super({
	      title: main_core.Loc.getMessage("CALENDAR_TITLE_YANDEX"),
	      helpDeskCode: '12925048',
	      titleInfoHeader: main_core.Loc.getMessage('CAL_CONNECT_YANDEX_CALENDAR'),
	      descriptionInfoHeader: main_core.Loc.getMessage('CAL_YANDEX_CONNECT_DESCRIPTION'),
	      titleActiveHeader: main_core.Loc.getMessage('CAL_YANDEX_CALENDAR_IS_CONNECT'),
	      descriptionActiveHeader: main_core.Loc.getMessage('CAL_YANDEX_SELECTED_DESCRIPTION'),
	      sliderIconClass: 'calendar-sync-slider-header-icon-yandex',
	      iconPath: '/bitrix/images/calendar/sync/yandex.svg',
	      iconLogoClass: '--yandex',
	      color: '#f9c500',
	      provider: provider,
	      connection: connection,
	      popupWithUpdateButton: true
	    });
	  }
	}

	let _$i = t => t,
	  _t$i,
	  _t2$e,
	  _t3$d,
	  _t4$c,
	  _t5$a;
	class MobileInterfaceTemplate extends InterfaceTemplate {
	  constructor(options) {
	    super(options);
	    this.banner = new MobileSyncBanner({
	      type: this.provider.getType(),
	      helpDeskCode: options.helpDeskCode
	    });
	    if (this.status) {
	      this.syncDate = main_core.Type.isDate(this.data.syncDate) ? this.data.syncDate : calendar_util.Util.parseDate(this.data.syncDate);
	    }
	  }
	  getContentInfoBody() {
	    return main_core.Tag.render(_t$i || (_t$i = _$i`
			${0}
			${0}
		`), this.getContentInfoBodyHeader(), this.getContentInfoWarning());
	  }
	  getContentInfoBodyHeaderHelper() {
	    if (!this.headerHelper) {
	      this.headerHelper = main_core.Tag.render(_t2$e || (_t2$e = _$i`
				<div class="calendar-sync-slider-info">
					<span class="calendar-sync-slider-info-text">
						<a class="calendar-sync-slider-info-link">
							${0}
						</a>
					</span>
				</div>
			`), main_core.Loc.getMessage('CAL_CONNECT_PHONE'));
	      main_core.Event.bind(this.headerHelper, 'click', this.showMobileSyncBanner.bind(this));
	    }
	    return this.headerHelper;
	  }
	  showMobileSyncBanner() {
	    this.headerHelper.style.display = 'none';
	    main_core.Dom.append(this.getContentBodyConnect(), this.infoBodyHeader);
	  }
	  getContentActiveBody() {
	    return main_core.Tag.render(_t3$d || (_t3$d = _$i`
			${0}
			<div class="calendar-sync-slider-section calendar-sync-slider-section-banner">
				${0}
			</div>
		`), this.getContentActiveBodyHeader(), this.getContentBodyConnect());
	  }
	  getContentActiveBodyHeader() {
	    const timestamp = this.connection.getSyncDate().getTime() / 1000;
	    const syncTime = timestamp ? calendar_util.Util.formatDateUsable(timestamp) + ' ' + BX.date.format(calendar_util.Util.getTimeFormatShort(), timestamp) : '';
	    return main_core.Tag.render(_t4$c || (_t4$c = _$i`
			<div class="calendar-sync-slider-section">
				<div class="calendar-sync-slider-header-icon ${0}"></div>
				<div class="calendar-sync-slider-header">
				<div class="calendar-sync-slider-title">${0}</div>
				<div class="calendar-sync-slider-info">
					<span class="calendar-sync-slider-info-text">${0}</span>
					<span class="calendar-sync-slider-info-time">${0}</span>
				</div>
				<div class="calendar-sync-slider-desc">${0}</div>
					<a class="calendar-sync-slider-link" href="javascript:void(0);" onclick="${0}">${0}</a>
				</div>
			</div>`), this.sliderIconClass, this.titleActiveHeader, main_core.Loc.getMessage('CAL_SYNC_LAST_SYNC_DATE'), syncTime, main_core.Loc.getMessage('CAL_SYNC_DISABLE'), this.showHelp.bind(this), main_core.Loc.getMessage('CAL_TEXT_ABOUT_WORK_SYNC'));
	  }
	  getContentBodyConnect() {
	    this.banner.initQrCode().then(this.banner.drawQRCode.bind(this.banner));
	    return this.banner.getContainer();
	  }
	  getActiveConnectionContent() {
	    return main_core.Tag.render(_t5$a || (_t5$a = _$i`
			<div class="calendar-sync-wrap calendar-sync-wrap-detail">
				<div class="calendar-sync-header">
					<span class="calendar-sync-header-text">${0}</span>
				</div>
				${0}
			</div>
		`), this.getHeaderTitle(), this.getContentActiveBody());
	  }
	}

	class AndroidTemplate extends MobileInterfaceTemplate {
	  constructor(provider, connection = null) {
	    super({
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
	    });
	    this.alreadyConnectedToNew = calendar_util.Util.isGoogleConnected();
	    if (this.alreadyConnectedToNew) {
	      this.warningText = main_core.Loc.getMessage('CAL_SYNC_WARNING_ANDROID_CONNECTED');
	      this.mobileSyncButtonText = main_core.Loc.getMessage('CALENDAR_CHECK_GOOGLE_SETTINGS');
	    } else {
	      this.warningText = main_core.Loc.getMessage('CAL_SYNC_WARNING_ANDROID');
	      this.mobileSyncButtonText = main_core.Loc.getMessage('CALENDAR_CONNECT_GOOGLE');
	    }
	  }
	  handleMobileButtonConnectClick() {
	    BX.SidePanel.Instance.getOpenSliders().forEach(slider => {
	      if (['calendar:auxiliary-sync-slider', 'calendar:item-sync-connect-android'].includes(slider.getUrl())) {
	        slider.close();
	      }
	    });
	    const calendarContext = calendar_util.Util.getCalendarContext();
	    if (calendarContext) {
	      calendarContext.syncInterface.getGoogleProvider().getInterfaceUnit().getConnectionTemplate().handleConnectButton();
	    }
	  }
	  handleMobileButtonOtherSyncInfo() {
	    BX.SidePanel.Instance.getOpenSliders().forEach(slider => {
	      if (['calendar:auxiliary-sync-slider', 'calendar:item-sync-connect-android'].includes(slider.getUrl())) {
	        slider.close();
	      }
	    });
	    const calendarContext = calendar_util.Util.getCalendarContext();
	    if (calendarContext) {
	      const connectionProvider = calendarContext.syncInterface.getGoogleProvider().getInterfaceUnit().connectionProvider;
	      connectionProvider.openActiveConnectionSlider(connectionProvider.getConnection());
	    }
	  }
	}

	class IphoneTemplate extends MobileInterfaceTemplate {
	  constructor(provider, connection = null) {
	    super({
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
	    });
	    this.alreadyConnectedToNew = calendar_util.Util.isIcloudConnected();
	    if (this.alreadyConnectedToNew) {
	      this.warningText = main_core.Loc.getMessage('CAL_SYNC_WARNING_IPHONE_AND_MAC_CONNECTED');
	      this.mobileSyncButtonText = main_core.Loc.getMessage('CALENDAR_CHECK_ICLOUD_SETTINGS');
	    } else {
	      this.warningText = main_core.Loc.getMessage('CAL_SYNC_WARNING_IPHONE_AND_MAC');
	      this.mobileSyncButtonText = main_core.Loc.getMessage('CALENDAR_CONNECT_ICLOUD');
	    }
	    // this.warningText = this.alreadyConnectedToNew
	    // 	? Loc.getMessage('CAL_SYNC_WARNING_IPHONE_AND_MAC_CONNECTED')
	    // 	: Loc.getMessage('CAL_SYNC_WARNING_IPHONE_AND_MAC');
	  }

	  handleMobileButtonConnectClick() {
	    BX.SidePanel.Instance.getOpenSliders().forEach(slider => {
	      if (['calendar:auxiliary-sync-slider', 'calendar:item-sync-connect-iphone'].includes(slider.getUrl())) {
	        slider.close();
	      }
	    });
	    const calendarContext = calendar_util.Util.getCalendarContext();
	    if (calendarContext) {
	      calendarContext.syncInterface.getIcloudProvider().getInterfaceUnit().getConnectionTemplate().handleConnectButton();
	    }
	  }
	  handleMobileButtonOtherSyncInfo() {
	    BX.SidePanel.Instance.getOpenSliders().forEach(slider => {
	      if (['calendar:auxiliary-sync-slider', 'calendar:item-sync-connect-iphone'].includes(slider.getUrl())) {
	        slider.close();
	      }
	    });
	    const calendarContext = calendar_util.Util.getCalendarContext();
	    if (calendarContext) {
	      const connectionProvider = calendarContext.syncInterface.getIcloudProvider().getInterfaceUnit().connectionProvider;
	      connectionProvider.openActiveConnectionSlider(connectionProvider.getConnection());
	    }
	  }
	}

	let _$j = t => t,
	  _t$j,
	  _t2$f;
	var _showSuccessCopyNotification = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showSuccessCopyNotification");
	var _showFailedCopyNotification = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showFailedCopyNotification");
	var _showResultNotification = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showResultNotification");
	class IcalSyncPopup {
	  constructor(options) {
	    Object.defineProperty(this, _showResultNotification, {
	      value: _showResultNotification2
	    });
	    Object.defineProperty(this, _showFailedCopyNotification, {
	      value: _showFailedCopyNotification2
	    });
	    Object.defineProperty(this, _showSuccessCopyNotification, {
	      value: _showSuccessCopyNotification2
	    });
	    this.LINK_LENGTH = 112;
	    this.link = this.getIcalLink(options);
	  }
	  static createInstance(options) {
	    return new this(options);
	  }
	  show() {
	    this.createPopup().show();
	    this.startSync();
	  }
	  startSync() {
	    BX.ajax.get(this.link + '&check=Y', "", result => {
	      setTimeout(() => {
	        if (!result || result.length <= 0 || result.toUpperCase().indexOf('BEGIN:VCALENDAR') === -1) {
	          this.showPopupWithSyncDataError();
	        }
	      }, 300);
	    });
	  }
	  getContent() {
	    return main_core.Tag.render(_t$j || (_t$j = _$j`
			<div class="calendar-ical-popup-wrapper">
				<h3>${0}</h3>
				<div class="calendar-ical-popup-label-text"><span>${0}</span></div>
				${0}
			</div>
		`), main_core.Loc.getMessage('EC_JS_EXPORT_TILE'), main_core.Loc.getMessage('EC_EXP_TEXT'), this.getLinkBlock());
	  }
	  createPopup() {
	    return this.popup = new main_popup.Popup({
	      width: 400,
	      zIndexOptions: 4000,
	      autoHide: false,
	      closeByEsc: true,
	      draggable: true,
	      closeIcon: {
	        right: "12px",
	        top: "10px"
	      },
	      className: "bxc-popup-window",
	      content: this.getContent(),
	      buttons: [new BX.UI.Button({
	        text: main_core.Loc.getMessage('EC_JS_ICAL_COPY_ICAL_SYNC_LINK'),
	        color: BX.UI.Button.Color.PRIMARY,
	        onclick: () => {
	          this.copyLink(event);
	        }
	      }), new BX.UI.Button({
	        text: main_core.Loc.getMessage('EC_SEC_SLIDER_CLOSE'),
	        color: BX.UI.Button.Color.LINK,
	        onclick: () => {
	          this.popup.close();
	        }
	      })]
	    });
	  }
	  getIcalLink(options) {
	    return options.calendarPath + (options.calendarPath.indexOf('?') >= 0 ? '&' : '?') + 'action=export' + options.sectionLink;
	  }
	  getLinkBlock() {
	    return main_core.Tag.render(_t2$f || (_t2$f = _$j`
				<div class="calendar-ical-popup-link-block">
					<a class="ui-link ui-link-primary " target="_blank" href="${0}">
						${0}
					</a>
				</div>
			`), BX.util.htmlspecialchars(this.link), BX.util.htmlspecialchars(this.getShortenLink(this.link)));
	  }
	  static checkPathes(options) {
	    return !!options.sectionLink || !!options.calendarPath;
	  }
	  static showPopupWithPathesError() {
	    BX.UI.Dialogs.MessageBox.alert(main_core.Loc.getMessage('EC_JS_ICAL_ERROR_WITH_PATHES'));
	  }
	  showPopupWithSyncDataError() {
	    BX.UI.Dialogs.MessageBox.alert(main_core.Loc.getMessage('EC_EDEV_EXP_WARN'));
	  }
	  copyLink(event) {
	    window.BX.clipboard.copy(this.link) ? babelHelpers.classPrivateFieldLooseBase(this, _showSuccessCopyNotification)[_showSuccessCopyNotification]() : babelHelpers.classPrivateFieldLooseBase(this, _showFailedCopyNotification)[_showFailedCopyNotification]();
	    event.preventDefault();
	    event.stopPropagation();
	  }
	  getShortenLink(link) {
	    return link.length < this.LINK_LENGTH ? link : link.substr(0, 105) + '...' + link.slice(-7);
	  }
	}
	function _showSuccessCopyNotification2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _showResultNotification)[_showResultNotification](main_core.Loc.getMessage('EC_JS_ICAL_COPY_ICAL_SYNC_LINK_SUCCESS'));
	}
	function _showFailedCopyNotification2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _showResultNotification)[_showResultNotification](main_core.Loc.getMessage('EC_JS_ICAL_COPY_ICAL_SYNC_LINK_FAILED'));
	}
	function _showResultNotification2(message) {
	  calendar_util.Util.showNotification(message);
	}

	class AfterSyncTour {
	  constructor(options = {}) {
	    this.options = options;
	  }
	  static createInstance(options) {
	    return new this(options);
	  }
	  loadExtension() {
	    return new Promise(resolve => {
	      main_core.Runtime.loadExtension('ui.tour').then(exports => {
	        if (exports && exports['Guide'] && exports['Manager']) {
	          resolve();
	        } else {
	          console.error(`Extension "ui.tour" not found`);
	        }
	      });
	    });
	  }
	  show() {
	    this.loadExtension().then(() => {
	      this.guide = new BX.UI.Tour.Guide({
	        steps: [{
	          target: this.getTarget(),
	          title: main_core.Loc.getMessage('CAL_AFTER_SYNC_AHA_TITLE'),
	          text: main_core.Loc.getMessage('CAL_AFTER_SYNC_AHA_TEXT')
	        }],
	        onEvents: true
	      });
	      this.guide.start();
	    });
	  }
	  getTarget() {
	    let target;
	    const view = this.options.view;
	    const viewWrap = view.getContainer();
	    if (view.getName() === 'month') {
	      target = viewWrap.querySelectorAll(".calendar-grid-today")[0];
	    } else if (view.getName() === 'day' || view.getName() === 'week') {
	      const dayCode = calendar_util.Util.getDayCode(new Date());
	      target = viewWrap.querySelector('div[data-bx-calendar-timeline-day="' + dayCode + '"] .calendar-grid-cell-inner');
	    } else {
	      target = document.querySelector('span[data-role="addButton"]');
	    }
	    return target;
	  }
	}

	exports.SyncPanel = SyncPanel;
	exports.SyncPanelUnit = SyncPanelUnit;
	exports.AuxiliarySyncPanel = AuxiliarySyncPanel;
	exports.GridUnit = GridUnit;
	exports.ConnectionControls = ConnectionControls;
	exports.MobileSyncBanner = MobileSyncBanner;
	exports.YandexTemplate = YandexTemplate;
	exports.CaldavTemplate = CaldavTemplate;
	exports.MacTemplate = MacTemplate;
	exports.ExchangeTemplate = ExchangeTemplate;
	exports.GoogleTemplate = GoogleTemplate;
	exports.IcloudTemplate = IcloudTemplate;
	exports.OutlookTemplate = OutlookTemplate;
	exports.IphoneTemplate = IphoneTemplate;
	exports.AndroidTemplate = AndroidTemplate;
	exports.IcalSyncPopup = IcalSyncPopup;
	exports.AfterSyncTour = AfterSyncTour;
	exports.GoogleSyncWizard = GoogleSyncWizard;
	exports.Office365template = Office365template;
	exports.IcloudAuthDialog = IcloudAuthDialog;

}((this.BX.Calendar.Sync.Interface = this.BX.Calendar.Sync.Interface || {}),BX,BX,BX.Calendar.Sync.Manager,BX.Calendar,BX,BX,BX.Event,BX.UI.Dialogs,BX,BX.Calendar,BX.Main));
//# sourceMappingURL=syncinterface.bundle.js.map
