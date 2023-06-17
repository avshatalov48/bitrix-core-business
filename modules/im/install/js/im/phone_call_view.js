/**
 * @bxjs_lang_path js_phone_call_view.php
 */
(function()
{
	/* Phone Call UI */
	var nop = function(){};
	var layouts = {
		simple: 'simple',
		crm: 'crm'
	};

	var initialSize = {
		simple: {
			width: 550,
			height: 492
		},
		crm: {
			width: 550,
			height: 650
		}
	};

	var lsKeys = {
		height: 'im-phone-call-view-height',
		width: 'im-phone-call-view-width'
	};

	var baseZIndex = 15000;

	var desktopEvents = {
		setTitle: 'phoneCallViewSetTitle',
		setStatus: 'phoneCallViewSetStatus',
		setUiState: 'phoneCallViewSetUiState',
		setDeviceCall: 'phoneCallViewSetDeviceCall',
		setCrmEntity: 'phoneCallViewSetCrmEntity',
		setPortalCall: 'phoneCallViewSetPortalCall',
		setPortalCallUserId: 'phoneCallViewSetPortalCallUserId',
		setPortalCallQueueName: 'phoneCallViewSetPortalCallQueueName',
		setPortalCallData: 'phoneCallViewSetPortalCallData',
		setConfig: 'phoneCallViewSetConfig',
		setCallState: 'phoneCallViewSetCallState',
		reloadCrmCard: 'phoneCallViewReloadCrmCard',
		setCallId: 'phoneCallViewSetCallId',
		setLineNumber: 'phoneCallViewSetLineNumber',
		setPhoneNumber: 'phoneCallViewSetPhoneNumber',
		setCompanyPhoneNumber: 'phoneCallViewSetCompanyPhoneNumber',
		setTransfer: 'phoneCallViewSetTransfer',
		closeWindow: 'phoneCallViewCloseWindow',

		onHold: 'phoneCallViewOnHold',
		onUnHold: 'phoneCallViewOnUnHold',
		onMute: 'phoneCallViewOnMute',
		onUnMute: 'phoneCallViewOnUnMute',
		onMakeCall: 'phoneCallViewOnMakeCall',
		onCallListMakeCall: 'phoneCallViewOnCallListMakeCall',
		onAnswer: 'phoneCallViewOnAnswer',
		onSkip: 'phoneCallViewOnSkip',
		onHangup: 'phoneCallViewOnHangup',
		onClose: 'phoneCallViewOnClose',
		onStartTransfer: 'phoneCallViewOnStartTransfer',
		onCompleteTransfer: 'phoneCallViewOnCompleteTransfer',
		onCancelTransfer: 'phoneCallViewOnCancelTransfer',
		onBeforeUnload: 'phoneCallViewOnBeforeUnload',
		onSwitchDevice: 'phoneCallViewOnSwitchDevice',
		onQualityGraded: 'phoneCallViewOnQualityGraded',
		onDialpadButtonClicked: 'phoneCallViewOnDialpadButtonClicked',
		onCommentShown: 'phoneCallViewOnCommentShown',
		onSaveComment: 'phoneCallViewOnSaveComment',
		onSetAutoClose: 'phoneCallViewOnSetAutoClose',
	};

	var defaults = {
		restApps: [],		// {id: int, name: string}
		callInterceptAllowed: false
	};

	var blankAvatar = '/bitrix/js/im/images/blank.gif';

	BX.PhoneCallView = function(params)
	{
		this.id = 'im-phone-call-view';
		this.BXIM = params.BXIM || window.BXIM;

		if(!BX.type.isPlainObject(params))
			params = {};

		this.keypad = null;

		//params
		this.phoneNumber = params.phoneNumber || 'hidden';
		this.lineNumber = params.lineNumber || '';
		this.companyPhoneNumber = params.companyPhoneNumber || '';
		this.direction = params.direction || BX.PhoneCallView.Direction.incoming;
		this.fromUserId = params.fromUserId;
		this.toUserId = params.toUserId;
		this.config = params.config || {};
		this.callId = params.callId || '';
		this.callState = BX.PhoneCallView.CallState.idle;

		//associated crm entities
		this.crmEntityType = BX.prop.getString(params, 'crmEntityType', '');
		this.crmEntityId = BX.prop.getInteger(params, 'crmEntityId', 0);
		this.crmActivityId = BX.prop.getInteger(params, 'crmActivityId', 0);
		this.crmActivityEditUrl = BX.prop.getString(params, 'crmActivityEditUrl', '');
		this.crmData = BX.prop.getObject(params, 'crmData', {});
		this.crmBindings = BX.prop.getArray(params, 'crmBindings', []);
		this.externalRequests = {};

		//portal call
		this.portalCallData = params.portalCallData;
		this.portalCallUserId = params.portalCallUserId;
		this.portalCallQueueName = params.portalCallQueueName;

		//flags
		this.hasSipPhone = (params.hasSipPhone === true);
		this.deviceCall = (params.deviceCall === true);
		this.portalCall = (params.portalCall === true);
		this.crm = (params.crm === true);
		this.held = false;
		this.muted = false;
		this.recording = (params.recording === true);
		this.makeCall = (params.makeCall === true); // emulate pressing on "dial" button right after showing call view
		this.closable = false;
		this.allowAutoClose = true;
		this.folded = (params.folded === true);
		this.autoFold = (params.autoFold === true);
		this.transfer = (params.transfer === true);

		this.title = '';
		this._uiState = params.uiState || BX.PhoneCallView.UiState.idle;
		this.statusText = params.statusText || '';
		this.progress = '';
		this.quality = 0;
		this.qualityPopup = null;
		this.qualityGrade = 0;
		this.comment = '';
		this.commentShown = false;

		//timer
		this.initialTimestamp = params.initialTimestamp || 0;
		this.timerInterval = null;

		this.elements = this.getInitialElements();
		this.sections = this.getInitialSections();

		var uiStateButtons = this.getUiStateButtons(this._uiState);
		this.buttonLayout = uiStateButtons.layout;
		this.buttons = uiStateButtons.buttons;

		if(!BX.type.isPlainObject(params.events))
			params.events = {};

		this.callbacks = {
			hold: BX.type.isFunction(params.events.hold) ? params.events.hold : nop,
			unhold: BX.type.isFunction(params.events.unhold) ? params.events.unhold : nop,
			mute: BX.type.isFunction(params.events.mute) ? params.events.mute : nop,
			unmute: BX.type.isFunction(params.events.unmute) ? params.events.unmute : nop,
			makeCall: BX.type.isFunction(params.events.makeCall) ? params.events.makeCall : nop,
			callListMakeCall: BX.type.isFunction(params.events.callListMakeCall) ? params.events.callListMakeCall : nop,
			answer: BX.type.isFunction(params.events.answer) ? params.events.answer : nop,
			skip: BX.type.isFunction(params.events.skip) ? params.events.skip : nop,
			hangup: BX.type.isFunction(params.events.hangup) ? params.events.hangup : nop,
			close: BX.type.isFunction(params.events.close) ? params.events.close : nop,
			transfer: BX.type.isFunction(params.events.transfer) ? params.events.transfer : nop,
			completeTransfer: BX.type.isFunction(params.events.completeTransfer) ? params.events.completeTransfer : nop,
			cancelTransfer: BX.type.isFunction(params.events.cancelTransfer) ? params.events.cancelTransfer : nop,
			switchDevice: BX.type.isFunction(params.events.switchDevice) ? params.events.switchDevice : nop,
			qualityGraded: BX.type.isFunction(params.events.qualityGraded) ? params.events.qualityGraded : nop,
			dialpadButtonClicked: BX.type.isFunction(params.events.dialpadButtonClicked) ? params.events.dialpadButtonClicked : nop
		};

		this.popup = null;

		// event handlers
		this._onBeforeUnloadHandler = this._onBeforeUnload.bind(this);
		this._onDblClickHandler = this._onDblClick.bind(this);
		this._onHoldButtonClickHandler = this._onHoldButtonClick.bind(this);
		this._onMuteButtonClickHandler = this._onMuteButtonClick.bind(this);
		this._onTransferButtonClickHandler = this._onTransferButtonClick.bind(this);
		this._onTransferCompleteButtonClickHandler = this._onTransferCompleteButtonClick.bind(this);
		this._onTransferCancelButtonClickHandler = this._onTransferCancelButtonClick.bind(this);
		this._onDialpadButtonClickHandler = this._onDialpadButtonClick.bind(this);
		this._onHangupButtonClickHandler = this._onHangupButtonClick.bind(this);
		this._onCloseButtonClickHandler = this._onCloseButtonClick.bind(this);
		this._onMakeCallButtonClickHandler = this._onMakeCallButtonClick.bind(this);
		this._onNextButtonClickHandler = this._onNextButtonClick.bind(this);
		this._onRedialButtonClickHandler = this._onRedialButtonClick.bind(this);
		this._onFoldButtonClickHandler = this._onFoldButtonClick.bind(this);
		this._onAnswerButtonClickHandler = this._onAnswerButtonClick.bind(this);
		this._onSkipButtonClickHandler = this._onSkipButtonClick.bind(this);
		this._onSwitchDeviceButtonClickHandler = this._onSwitchDeviceButtonClick.bind(this);
		this._onQualityMeterClickHandler = this._onQualityMeterClick.bind(this);
		this._onPullEventCrmHandler = this._onPullEventCrm.bind(this);

		this._externalEventHandler = this._onExternalEvent.bind(this);
		this._unloadHandler = this._onWindowUnload.bind(this);

		// tabs
		this.hiddenTabs = [];
		this.currentTabName = '';
		this.moreTabsMenu = null;

		// callList
		this.callListId = params.callListId || 0;
		this.callListStatusId = params.callListStatusId || null;
		this.callListItemIndex = params.callListItemIndex || null;
		this.callListView = null;
		this.currentEntity = null;
		this.callingEntity = null;
		this.numberSelectMenu = null;

		// webform
		this.webformId = params.webformId || 0;
		this.webformSecCode = params.webformSecCode || '';
		this.webformLoaded = false;
		this.formManager = null;

		// partner data
		this.restAppLayoutLoaded = false;
		this.restAppLayoutLoading = false;
		this.restAppInterface = null;

		// desktop integration
		this.callWindow = null;
		this.slave = params.slave === true;
		this.skipOnResize = params.skipOnResize === true;
		this.desktop = new Desktop({
			BXIM: this.BXIM,
			parentPhoneCallView: this,
			closable: (this.callListId > 0 ? true : this.closable)
		});

		this.currentLayout = (this.callListId > 0 ? layouts.crm : layouts.simple);
		BackgroundWorker.isExternalCall = !!params.isExternalCall;
		BackgroundWorker.desktop.isCurrentPage = true;
		this.init();
		this.createTitle().then(this.setTitle.bind(this));
		if(params.hasOwnProperty('uiState'))
		{
			this.setUiState(params['uiState']);
		}

		BackgroundWorker.CallCard = this;
		if (BackgroundWorker.isDesktop())
		{
			BackgroundWorker.removeDesktopEventHandlers();
		}

		BackgroundWorker.onInitialize(this.getPlacementOptions());
		window.test = this;
	};


	BX.PhoneCallView.ButtonLayouts = {
		centered: 'centered',
		spaced: 'spaced'
	};

	BX.PhoneCallView.create = function(params)
	{
		return new BX.PhoneCallView(params);
	};

	BX.PhoneCallView.prototype.getInitialElements = function()
	{
		return {
			main: null,
			title: null,
			sections: {
				status: null,
				timer: null,
				crmButtons: null,
			},
			avatar: null,
			progress: null,
			timer: null,
			status: null,
			commentEditorContainer: null,
			commentEditor: null,
			qualityMeter: null,
			crmCard: null,
			crmButtonsContainer: null,
			crmButtons: {},
			buttonsContainer: null,
			topLevelButtonsContainer: null,
			topButtonsContainer: null, //well..
			buttons: {},
			sidebarContainer: null,
			tabsContainer: null,
			tabsBodyContainer: null,
			tabs: {
				callList: null,
				webform: null,
				app: null
			},
			tabsBody: {
				callList: null,
				webform: null,
				app: null
			},
			moreTabs: null
		};
	};

	BX.PhoneCallView.prototype.getInitialSections = function()
	{
		return {
			status: {visible: false},
			timer: {visible: false},
			crmButtons: {visible: false},
			commentEditor: {visible: false}
		}
	};

	BX.PhoneCallView.prototype.init = function()
	{
		var self = this;

		if(BX.MessengerCommon.isDesktop() && !this.slave)
		{
			this.desktop.openCallWindow('', null, {
				width: this.getInitialWidth(),
				height: this.getInitialHeight(),
				resizable: (this.currentLayout == layouts.crm),
				minWidth: this.elements.sidebarContainer ? 950 : 550,
				minHeight: 650
			});
			this.bindMasterDesktopEvents();

			window.addEventListener('beforeunload', this._unloadHandler); //master window unload
			return;
		}

		this.elements.main = this.createLayout();
		this.updateView();

		if(this.isDesktop())
		{
			document.body.appendChild(this.elements.main);
			this.bindSlaveDesktopEvents();
		}
		else if(this.isFolded())
		{
			document.body.appendChild(this.elements.main);
		}
		else
		{
			this.popup = this.createPopup();
			BX.addCustomEvent(window, "onLocalStorageSet", this._externalEventHandler);
		}

		if(this.callListId > 0)
		{
			if(this.callListView)
			{
				this.callListView.reinit({
					node: this.elements.tabsBody.callList
				});
			}
			else
			{
				this.callListView = new CallList({
					node: this.elements.tabsBody.callList,
					id: this.callListId,
					statusId: this.callListStatusId,
					itemIndex: this.callListItemIndex,
					makeCall: this.makeCall,
					BXIM: this.BXIM,
					onSelectedItem: this.onCallListSelectedItem.bind(this)
				});

				this.callListView.init(function()
				{
					if(self.makeCall)
					{
						self._onMakeCallButtonClick();
					}
				});
				this.setUiState(BX.PhoneCallView.UiState.outgoing);
			}
		}
		else if(this.crm && !this.isFolded())
		{
			this.loadCrmCard(this.crmEntityType, this.crmEntityId);
		}

		BX.addCustomEvent("onPullEvent-crm", this._onPullEventCrmHandler);
		if(!this.isDesktop())
		{
			window.addEventListener('beforeunload', this._onBeforeUnloadHandler);
		}
	};

	BX.PhoneCallView.prototype.reinit = function()
	{
		this.elements = this.getInitialElements();

		window.removeEventListener('beforeunload', this._unloadHandler);
		BX.removeCustomEvent(window, "onLocalStorageSet", this._externalEventHandler);
		BX.removeCustomEvent("onPullEvent-crm", this._onPullEventCrmHandler);

		this.init();
	};

	BX.PhoneCallView.setDefaults = function(params)
	{
		for(paramName in params)
		{
			if(params.hasOwnProperty(paramName) && defaults.hasOwnProperty(paramName))
			{
				defaults[paramName] = params[paramName];
			}
		}
	};

	BX.PhoneCallView.prototype.show = function()
	{
		if(!this.popup && this.isDesktop())
		{
			return;
		}
		if(!this.popup)
		{
			this.reinit();
		}

		if(!this.isDesktop() && !this.isFolded())
			this.disableDocumentScroll();

		this.popup.show();
		return this;
	};

	BX.PhoneCallView.prototype.createPopup = function()
	{
		var self = this;

		return new BX.PopupWindow(self.getId(), null, {
			targetContainer: document.body,
			content: this.elements.main,
			closeIcon: false,
			noAllPaddings: true,
			zIndex: baseZIndex,
			offsetLeft: 0,
			offsetTop: 0,
			closeByEsc: false,
			draggable: {restrict: false},
			overlay: {backgroundColor: 'black', opacity: 30},
			events: {
				onPopupClose: function()
				{
					if(self.isFolded())
					{
						// this.destroy();
					}
					else
					{
						self.callbacks.close();
					}
				},
				onPopupDestroy: function()
				{
					self.popup = null;
				}
			}
		});
	};

	BX.PhoneCallView.prototype.createLayout = function()
	{
		if(this.isFolded())
			return this.createLayoutFolded();
		else if(this.currentLayout == layouts.crm)
			return this.createLayoutCrm();
		else
			return this.createLayoutSimple();
	};

	BX.PhoneCallView.prototype.createLayoutCrm = function()
	{
		var result = BX.create("div", {
			props: {className: 'im-phone-call-top-level'},
			events: {
				dblclick: this._onDblClickHandler
			},
			children: [
				this.elements.topLevelButtonsContainer = BX.create("div"),
				BX.create("div", {
					props: {className: 'im-phone-call-wrapper' + (this.hasSideBar() ? '' : ' im-phone-call-wrapper-without-sidebar')},
					children: [
						BX.create("div", {
							props: {className: 'im-phone-call-container' + (this.hasSideBar() ? '' : ' im-phone-call-container-without-sidebar') },
							children: [
								BX.create("div", {props: {className: 'im-phone-call-header-container'}, children: [
									BX.create("div", {props: {className: 'im-phone-call-header'}, children: [
										this.elements.title = BX.create('div', {props: {className: 'im-phone-call-title-text'}, html: this.renderTitle()})
									]})
								]}),
								this.elements.crmCard = BX.create("div", {props: {className: 'im-phone-call-crm-card'}}),
								this.elements.sections.status = BX.create("div", {props: {className: 'im-phone-call-section'}, style: this.sections.status.visible ? {} : {display: 'none'}, children: [
									BX.create("div", {props: {className: 'im-phone-call-status-description'}, children: [
										this.elements.status = BX.create("div", {props: {className: 'im-phone-call-status-description-item'}, text: this.statusText})
									]})
								]}),
								this.elements.sections.timer = BX.create("div", {props: {className: 'im-phone-call-section'}, style: this.sections.timer.visible ? {} : {display: 'none'}, children: [
									BX.create("div", {props: {className: 'im-phone-call-status-timer'}, children: [
										BX.create("div", {props: {className: 'im-phone-call-status-timer-item'}, children: [
											this.elements.timer = BX.create("span")
										]})
									]})
								]}),
								this.elements.commentEditorContainer = BX.create("div", {props: {className: 'im-phone-call-section'}, style: this.commentShown ? {} : {display: 'none'}, children: [
									BX.create("div", {props: {className: 'im-phone-call-comments'}, children: [
										this.elements.commentEditor = BX.create("textarea", {
											props: {
												className: 'im-phone-call-comments-textarea',
												value: this.comment,
												placeholder: BX.message('IM_PHONE_CALL_COMMENT_PLACEHOLDER')
											},
											events: {
												bxchange: this._onCommentChanged.bind(this)
											}
										})
									]})
								]}),
								this.elements.sections.crmButtons = BX.create("div", {props: {className: 'im-phone-call-section'}, style: this.sections.crmButtons.visible ? {} : {display: 'none'}, children: [
									this.elements.crmButtonsContainer = BX.create("div", {props: {className: 'im-phone-call-crm-buttons'}})
								]}),
								this.elements.buttonsContainer = BX.create("div", {props: {className: 'im-phone-call-buttons-container'}}),
								this.elements.topButtonsContainer = BX.create("div", {props: {className: 'im-phone-call-buttons-container-top'}})
							]
						})
					]
				})
			]
		});

		if(this.hasSideBar())
		{
			this.createSidebarLayout();
			if(this.elements.sidebarContainer)
			{
				result.appendChild(this.elements.sidebarContainer);
			}

			setTimeout(function(){
				this.checkMoreButton();
			}.bind(this), 0);
		}

		if(this.isDesktop())
		{
			result.style.position = 'fixed';
			result.style.top = 0;
			result.style.bottom = 0;
			result.style.left = 0;
			result.style.right = 0;
		}
		else
		{
			result.style.width = this.getInitialWidth() + 'px';
			result.style.height = this.getInitialHeight() + 'px';
		}

		return result;
	};

	/**
	 * @return boolean
	 */
	BX.PhoneCallView.prototype.hasSideBar = function()
	{
		if(this.isDesktop() && !this.desktop.isFeatureSupported('iframe'))
			return this.callListId > 0;
		else
			return (this.callListId > 0 || this.webformId > 0 || defaults.restApps.length > 0);
	};

	BX.PhoneCallView.prototype.getInitialWidth = function()
	{
		var storedWidth = (window.localStorage) ? parseInt(window.localStorage.getItem(lsKeys.width)) : 0;

		if(this.currentLayout == layouts.simple)
		{
			return initialSize.simple.width;
		}
		else if(this.hasSideBar())
		{
			if(storedWidth > 0)
			{
				return storedWidth;
			}
			else
			{
				return Math.min(Math.floor(screen.width * 0.8), 1200);
			}
		}
		else
		{
			return initialSize.crm.width;
		}
	};

	BX.PhoneCallView.prototype.getInitialHeight = function()
	{
		var storedHeight = (window.localStorage) ? parseInt(window.localStorage.getItem(lsKeys.height)) : 0;

		if(this.currentLayout == layouts.simple)
		{
			return initialSize.simple.height
		}
		else if(storedHeight > 0)
		{
			return storedHeight;
		}
		else
		{
			return initialSize.crm.height;
		}
	};

	BX.PhoneCallView.prototype.saveInitialSize = function(width, height)
	{
		if(!window.localStorage)
			return false;

		if(this.currentLayout == layouts.crm)
		{
			window.localStorage.setItem(lsKeys.height, height.toString());
			if(this.hasSideBar())
			{
				window.localStorage.setItem(lsKeys.width, width);
			}
		}
	};

	BX.PhoneCallView.prototype.showSections = function(sections)
	{
		var self = this;
		if(!BX.type.isArray(sections))
			return;

		sections.forEach(function(sectionName)
		{
			if(self.elements.sections[sectionName])
				self.elements.sections[sectionName].style.removeProperty('display');

			if(self.sections[sectionName])
				self.sections[sectionName].visible = true;
		});
	};

	BX.PhoneCallView.prototype.hideSections = function(sections)
	{
		var self = this;
		if(!BX.type.isArray(sections))
			return;

		sections.forEach(function(sectionName)
		{
			if(self.elements.sections[sectionName])
				self.elements.sections[sectionName].style.display = 'none';

			if(self.sections[sectionName])
				self.sections[sectionName].visible = false;
		});
	};

	BX.PhoneCallView.prototype.showOnlySections = function(sections)
	{
		var self = this;
		if(!BX.type.isArray(sections))
			return;

		var sectionsIndex = {};
		sections.forEach(function(sectionName)
		{
			sectionsIndex[sectionName] = true;
		});

		for(var sectionName in this.elements.sections)
		{
			if (!this.elements.sections.hasOwnProperty(sectionName) || !BX.type.isDomNode(this.elements.sections[sectionName]))
				continue;

			if (sectionsIndex[sectionName])
			{
				this.elements.sections[sectionName].style.removeProperty('display');
				if(this.sections.hasOwnProperty(sectionName))
					this.sections[sectionName].visible = true;
			}
			else
			{
				this.elements.sections[sectionName].style.display = 'none';
				if(this.sections.hasOwnProperty(sectionName))
					this.sections[sectionName].visible = false;
			}
		}
	};

	BX.PhoneCallView.prototype.createSidebarLayout = function()
	{
		var self = this;
		var tabs = [];
		var tabsBody = [];

		if (this.callListId > 0)
		{
			this.elements.tabs.callList = BX.create("span", {
				props: {className: 'im-phone-sidebar-tab'},
				dataset: {tabId : 'callList', tabBodyId: 'callList'},
				text: BX.message('IM_PHONE_CALL_VIEW_CALL_LIST_TITLE'),
				events: {click: this._onTabHeaderClick.bind(this)}
			});
			tabs.push(this.elements.tabs.callList);
			this.elements.tabsBody.callList = BX.create('div');
			tabsBody.push(this.elements.tabsBody.callList);
		}

		if (this.webformId > 0 && this.isWebformSupported())
		{
			this.elements.tabs.webform = BX.create("span", {
				props: {className: 'im-phone-sidebar-tab'},
				dataset: {tabId : 'webform',  tabBodyId: 'webform'},
				text: BX.message('IM_PHONE_CALL_VIEW_WEBFORM_TITLE'),
				events: {click: this._onTabHeaderClick.bind(this)}
			});
			tabs.push(this.elements.tabs.webform);
			this.elements.tabsBody.webform = BX.create('div', {props: {className: 'im-phone-call-form-container'}});
			tabsBody.push(this.elements.tabsBody.webform);

			this.formManager = new FormManager({
				node: this.elements.tabsBody.webform,
				onFormSend: this._onFormSend.bind(this)
			})
		}

		if (defaults.restApps.length > 0 && this.isRestAppsSupported())
		{
			defaults.restApps.forEach(function(restApp)
			{
				var restAppId = restApp.id;
				var tabId = 'restApp' + restAppId;
				self.elements.tabs[tabId] = BX.create("span", {
					props: {className: 'im-phone-sidebar-tab'},
					dataset: {tabId : tabId, tabBodyId: 'app', restAppId: restAppId},
					text: BX.util.htmlspecialchars(restApp.name),
					events: {click: self._onTabHeaderClick.bind(self)}
				});
				tabs.push(self.elements.tabs[tabId]);

			});
			self.elements.tabsBody.app = BX.create('div', {props: {className: 'im-phone-call-app-container'}});
			tabsBody.push(self.elements.tabsBody.app);
		}

		this.elements.sidebarContainer = BX.create("div", {props: {className: 'im-phone-sidebar-wrap'}, children: [
			BX.create("div", {props: {className: 'im-phone-sidebar-tabs-container'}, children: [
				this.elements.tabsContainer = BX.create("div", {props: {className: 'im-phone-sidebar-tabs-left'}, children: tabs}),
				BX.create("div", {props: {className: 'im-phone-sidebar-tabs-right'}, children: [
					this.elements.moreTabs = BX.create("span", {
						props: {className: 'im-phone-sidebar-tab im-phone-sidebar-tab-more'},
						style: {display: 'none'},
						dataset: {},
						text: BX.message('IM_PHONE_CALL_VIEW_MORE'),
						events: {click: this._onTabMoreClick.bind(this)}
					})
				]})
			]}),
			this.elements.tabsBodyContainer = BX.create("div", {props: {className: 'im-phone-sidebar-tabs-body-container'}, children: tabsBody})
		]});

		if(this.callListId > 0)
			this.setActiveTab({tabId: 'callList', tabBodyId: 'callList'});
		else if (this.webformId > 0 && this.isWebformSupported())
			this.setActiveTab({tabId: 'webform', tabBodyId: 'webform'});
		else if (defaults.restApps.length > 0 && this.isRestAppsSupported())
			this.setActiveTab({tabId: 'restApp' + defaults.restApps[0].id, tabBodyId: 'app', restAppId: defaults.restApps[0].id});
	};

	BX.PhoneCallView.prototype.createLayoutSimple = function()
	{
		var portalCallUserImage = '';
		if(this.isPortalCall()
			&& this.portalCallData.hrphoto
			&& this.portalCallData.hrphoto[this.portalCallUserId]
			&& this.portalCallData.hrphoto[this.portalCallUserId] != blankAvatar
		)
		{
			portalCallUserImage = this.portalCallData.hrphoto[this.portalCallUserId];
		}
		var result = BX.create("div", {props: {className: 'im-phone-call-wrapper'}, children: [
			BX.create("div", {props: {className: 'im-phone-call-container'}, children: [
				BX.create("div", {props: {className: 'im-phone-calling-section'}, children: [
					this.elements.title = BX.create("div", {props: {className: 'im-phone-calling-text'}})
				]}),
				BX.create("div", {props: {className: 'im-phone-call-section im-phone-calling-progress-section'}, children: [
					BX.create("div", {props: {className: 'im-phone-calling-progress-container'}, children: [
						BX.create("div", {props: {className: 'im-phone-calling-progress-container-block-l'}, children: [
							BX.create("div", {props: {className: 'im-phone-calling-progress-phone'}})
						]}),
						this.elements.progress = BX.create("div", {props: {className: 'im-phone-calling-progress-container-block-c'}}),
						BX.create("div", {props: {className: 'im-phone-calling-progress-container-block-r'}, children: [
							this.elements.avatar = BX.create("div", {
								props: {className: 'im-phone-calling-progress-customer'},
								style: portalCallUserImage == '' ?  {} : {'background-image': 'url(\'' + portalCallUserImage +'\')'}
							})
						]})
					]})
				]}),
				BX.create("div", {props: {className: 'im-phone-call-section'}, children: [
					this.elements.status = BX.create("div", {props: {className: 'im-phone-calling-process-status'}})
				]}),
				this.elements.buttonsContainer = BX.create("div", {props: {className: 'im-phone-call-buttons-container'}}),
				this.elements.topButtonsContainer = BX.create("div", {props: {className: 'im-phone-call-buttons-container-top'}})
			]})
		]});

		result.style.width = this.getInitialWidth() + 'px';
		result.style.height = this.getInitialHeight() + 'px';

		return result;
	};

	BX.PhoneCallView.prototype.createLayoutFolded = function()
	{
		var self = this;

		return BX.create("div", {
			props: {className: "im-phone-call-panel-mini"},
			style: {zIndex: baseZIndex},
			children: [
				this.elements.sections.timer = this.elements.timer = BX.create("div", {
					props: {className: "im-phone-call-panel-mini-time"},
					style: this.sections.timer.visible ? {} : {display: 'none'}
				}),
				this.elements.buttonsContainer = BX.create("div", {props: {className: 'im-phone-call-panel-mini-buttons'}}),
				BX.create("div", {
					props: {className: "im-phone-call-panel-mini-expand"},
					events: {click: function(){self.unfold();}}
				})
			]
		});
	};

	BX.PhoneCallView.prototype.setActiveTab = function(params)
	{
		var tabId = params.tabId;
		var tabBodyId = params.tabBodyId;
		var restAppId = params.restAppId || '';
		params.hidden = params.hidden === true;
		for(tab in this.elements.tabs)
		{
			if(this.elements.tabs.hasOwnProperty(tab) && BX.type.isDomNode(this.elements.tabs[tab]))
			{
				if(tab == tabId)
					BX.addClass(this.elements.tabs[tab], 'im-phone-sidebar-tab-active');
				else
					BX.removeClass(this.elements.tabs[tab], 'im-phone-sidebar-tab-active');
			}
		}

		if(params.hidden)
			BX.addClass(this.elements.moreTabs, 'im-phone-sidebar-tab-active');
		else
			BX.removeClass(this.elements.moreTabs, 'im-phone-sidebar-tab-active');

		for(tab in this.elements.tabsBody)
		{
			if(this.elements.tabsBody.hasOwnProperty(tab) && BX.type.isDomNode(this.elements.tabsBody[tab]))
			{
				if(tab == tabBodyId)
					this.elements.tabsBody[tab].style.removeProperty('display');
				else
					this.elements.tabsBody[tab].style.display = 'none';
			}
		}

		this.currentTabName = tabId;

		if(tabId === 'webform' && !this.webformLoaded)
		{
			this.loadForm({
				id: this.webformId,
				secCode: this.webformSecCode
			})
		}

		if(restAppId !== '')
		{
			this.loadRestApp({
				id: restAppId,
				callId: this.BXIM.webrtc.phoneCallId,
				node: this.elements.tabsBody.app
			});
		}
	};

	BX.PhoneCallView.prototype.isCurrentTabHidden = function()
	{
		var result = false;
		for(var i = 0; i < this.hiddenTabs.length; i++)
		{
			if(this.hiddenTabs[i].dataset.tabId == this.currentTabName)
			{
				result = true;
				break;
			}
		}
		return result;
	};

	BX.PhoneCallView.prototype.checkMoreButton = function()
	{
		if(!this.elements.tabsContainer)
			return;

		var tabs = this.elements.tabsContainer.children;
		var currentTab;
		this.hiddenTabs = [];

		for(var i = 0; i < tabs.length; i++)
		{
			currentTab = tabs.item(i);
			if(currentTab.offsetTop > 7)
			{
				this.hiddenTabs.push(currentTab);
			}
		}
		if(this.hiddenTabs.length > 0)
			this.elements.moreTabs.style.removeProperty('display');
		else
			this.elements.moreTabs.style.display = 'none';

		if(this.isCurrentTabHidden())
			BX.addClass(this.elements.moreTabs, 'im-phone-sidebar-tab-active');
		else
			BX.removeClass(this.elements.moreTabs, 'im-phone-sidebar-tab-active');
	};

	BX.PhoneCallView.prototype._onTabHeaderClick = function(e)
	{
		if(this.moreTabsMenu)
			this.moreTabsMenu.close();

		this.setActiveTab({
			tabId: e.target.dataset.tabId,
			tabBodyId: e.target.dataset.tabBodyId,
			restAppId: e.target.dataset.restAppId || '',
			hidden: false
		});
	};

	BX.PhoneCallView.prototype._onTabMoreClick = function(e)
	{
		var self = this;
		if(this.hiddenTabs.length === 0)
			return;

		if(this.moreTabsMenu)
		{
			this.moreTabsMenu.close();
			return;
		}

		var menuItems = [];
		this.hiddenTabs.forEach(function(tabElement)
		{
			menuItems.push({
				id: "selectTab_" + tabElement.dataset.tabId,
				text: tabElement.innerText,
				onclick: function()
				{
					self.moreTabsMenu.close();
					self.setActiveTab({
						tabId: tabElement.dataset.tabId,
						tabBodyId: tabElement.dataset.tabBodyId,
						restAppId: tabElement.dataset.restAppId || '',
						hidden: true
					});
				}
			})
		});

		this.moreTabsMenu = BX.PopupMenu.create(
			'phoneCallViewMoreTabs',
			this.elements.moreTabs,
			menuItems,
			{
				autoHide: true,
				offsetTop: 0,
				offsetLeft: 0,
				angle: {position: "top"},
				zIndex: baseZIndex + 100,
				events: {
					onPopupClose : function()
					{
						self.moreTabsMenu.popupWindow.destroy();
						BX.PopupMenu.destroy('phoneCallViewMoreTabs');
					},
					onPopupDestroy: function ()
					{
						self.moreTabsMenu = null;
					}
				}
			}
		);
		this.moreTabsMenu.popupWindow.show();
	};


	BX.PhoneCallView.prototype.getId = function()
	{
		return this.id;
	};

	BX.PhoneCallView.prototype.createTitle = function()
	{
		var result = new BX.Promise();
		var callTitle = '';

		BX.PhoneNumberParser.getInstance().parse(this.phoneNumber).then(function(parsedNumber)
		{
			if(this.phoneNumber == 'unknown')
			{
				result.resolve(BX.message('IM_PHONE_CALL_VIEW_NUMBER_UNKNOWN'));
				return;
			}
			if (this.phoneNumber == 'hidden')
			{
				callTitle = BX.message('IM_PHONE_HIDDEN_NUMBER');
			}
			else
			{
				callTitle = this.phoneNumber.toString();

				if(parsedNumber.isValid())
				{
					callTitle = parsedNumber.format();

					if(parsedNumber.isInternational() && callTitle.charAt(0) != '+')
					{
						callTitle = '+' + callTitle;
					}
				}
				else
				{
					callTitle = this.phoneNumber.toString();
				}
			}

			if(this.isCallback())
			{
				callTitle = BX.message('IM_PHONE_CALLBACK_TO').replace('#PHONE#', callTitle);
			}
			else if(this.isPortalCall())
			{
				switch (this.direction)
				{
					case BX.PhoneCallView.Direction.incoming:
						if (this.portalCallUserId)
						{
							callTitle = BX.message("IM_M_CALL_VOICE_FROM").replace('#USER#', this.portalCallData.users[this.portalCallUserId].name);
						}
						break;
					case BX.PhoneCallView.Direction.outgoing:
						if (this.portalCallUserId)
						{
							callTitle = BX.message("IM_M_CALL_VOICE_TO").replace('#USER#', this.portalCallData.users[this.portalCallUserId].name);
						}
						else
						{
							callTitle = BX.message("IM_M_CALL_VOICE_TO").replace('#USER#', this.portalCallQueueName) + ' (' + this.phoneNumber + ')';
						}
						break;
				}
			}
			else
			{
				callTitle = BX.message(this.direction === BX.PhoneCallView.Direction.incoming ? 'IM_PHONE_CALL_VOICE_FROM': 'IM_PHONE_CALL_VOICE_TO').replace('#PHONE#', callTitle);

				if (this.direction === BX.PhoneCallView.Direction.incoming && this.companyPhoneNumber)
				{
					callTitle = callTitle + ', ' + BX.message('IM_PHONE_CALL_TO_PHONE').replace('#PHONE#', this.companyPhoneNumber);
				}

				if (this.isTransfer())
				{
					callTitle = callTitle + ' ' + BX.message('IM_PHONE_CALL_TRANSFERED');
				}
			}

			result.resolve(callTitle);
		}.bind(this));
		return result;
	};

	BX.PhoneCallView.prototype.renderTitle = function()
	{
		return BX.util.htmlspecialchars(this.title);
	};

	BX.PhoneCallView.prototype.renderAvatar = function()
	{
		var portalCallUserImage = '';
		if(this.isPortalCall()
			&& this.elements.avatar
			&& this.portalCallData.hrphoto
			&& this.portalCallData.hrphoto[this.portalCallUserId]
			&& this.portalCallData.hrphoto[this.portalCallUserId] != blankAvatar
		)
		{
			portalCallUserImage = this.portalCallData.hrphoto[this.portalCallUserId];

			BX.adjust(this.elements.avatar, {
				style: portalCallUserImage == '' ? {} :  {'background-image': 'url(\'' + portalCallUserImage +'\')'}
			});
		}
	};

	BX.PhoneCallView.prototype._getCrmEditUrl = function(entityTypeName, entityId)
	{
		if(!BX.type.isNotEmptyString(entityTypeName))
			return '';

		entityId = parseInt(entityId) || 0;

		return '/crm/' + entityTypeName.toLowerCase() + '/edit/' + entityId.toString() + '/';
	};

	BX.PhoneCallView.prototype._generateExternalContext = function()
	{
		return this._getRandomString(16);
	};

	BX.PhoneCallView.prototype._getRandomString = function (len)
	{
		charSet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		var randomString = '';
		for (var i = 0; i < len; i++) {
			var randomPoz = Math.floor(Math.random() * charSet.length);
			randomString += charSet.substring(randomPoz,randomPoz+1);
		}
		return randomString;
	};

	BX.PhoneCallView.prototype.setPhoneNumber = function(phoneNumber)
	{
		this.phoneNumber = phoneNumber;
		this.setOnSlave(desktopEvents.setPhoneNumber, [phoneNumber]);
	};

	BX.PhoneCallView.prototype.setTitle = function(title)
	{
		this.title = title;
		if(this.isDesktop())
		{
			if(this.slave)
			{
				BXDesktopWindow.SetProperty('title', title);
			}
			else
			{
				BX.desktop.onCustomEvent(desktopEvents.setTitle, [title]);
			}
		}

		if(this.elements.title)
		{
			this.elements.title.innerHTML = this.renderTitle();
		}
	};

	BX.PhoneCallView.prototype.getTitle = function()
	{
		return this.title;
	};

	BX.PhoneCallView.prototype.setQuality = function(quality)
	{
		this.quality = quality;

		if(this.elements.qualityMeter)
			this.elements.qualityMeter.style.width = this.getQualityMeterWidth();
	};

	BX.PhoneCallView.prototype.getQualityMeterWidth = function()
	{
		if(this.quality > 0 && this.quality <= 5)
			return this.quality * 20 + '%';
		else
			return '0';
	};

	BX.PhoneCallView.prototype.setProgress = function(progress)
	{
		this.progress = progress;

		if(!this.elements.progress)
			return;

		BX.cleanNode(this.elements.progress);
		this.elements.progress.appendChild(this.renderProgress());
	};

	BX.PhoneCallView.prototype.setStatusText = function(statusText)
	{
		if(this.isDesktop() && !this.slave)
		{
			BX.desktop.onCustomEvent(desktopEvents.setStatus, [statusText]);
			return;
		}

		this.statusText = statusText;
		if(this.elements.status)
			this.elements.status.innerText = this.statusText;
	};

	BX.PhoneCallView.prototype.setConfig = function(config)
	{
		if(!BX.type.isPlainObject(config))
			return;

		this.config = config;
		if(!this.isDesktop() || this.slave)
		{
			this.renderCrmButtons();
		}
		this.setOnSlave(desktopEvents.setConfig, [config]);
	};

	BX.PhoneCallView.prototype.setCallId = function(callId)
	{
		this.callId = callId;
		this.setOnSlave(desktopEvents.setCallId, [callId]);
	};

	BX.PhoneCallView.prototype.setLineNumber = function(lineNumber)
	{
		this.lineNumber = lineNumber;
		this.setOnSlave(desktopEvents.setLineNumber, [lineNumber]);
	};

	BX.PhoneCallView.prototype.setCompanyPhoneNumber = function(companyPhoneNumber)
	{
		this.companyPhoneNumber = companyPhoneNumber;
		this.setOnSlave(desktopEvents.setCompanyPhoneNumber, [companyPhoneNumber]);
	};

	BX.PhoneCallView.prototype.setButtons = function(buttons, layout)
	{
		if(!BX.PhoneCallView.ButtonLayouts[layout])
			layout = BX.PhoneCallView.ButtonLayouts.centered;

		this.buttonLayout = layout;
		this.buttons = buttons;
		this.renderButtons();
	};

	BX.PhoneCallView.prototype.setUiState = function(uiState)
	{
		this._uiState = uiState;

		var stateButtons = this.getUiStateButtons(uiState);
		this.buttons = stateButtons.buttons;
		this.buttonLayout = stateButtons.layout;

		switch (uiState)
		{
			case BX.PhoneCallView.UiState.incoming:
				this.setClosable(false);
				this.showOnlySections(['status']);
				this.renderCrmButtons();
				this.stopTimer();
				break;
			case BX.PhoneCallView.UiState.transferIncoming:
				this.setClosable(false);
				this.showOnlySections(['status']);
				this.renderCrmButtons();
				this.stopTimer();
				break;
			case BX.PhoneCallView.UiState.outgoing:
				this.setClosable(true);
				this.showOnlySections(['status']);
				this.renderCrmButtons();
				this.stopTimer();
				this.hideCallIcon();
				break;
			case BX.PhoneCallView.UiState.connectingIncoming:
				this.setClosable(false);
				this.showOnlySections(['status']);
				this.renderCrmButtons();
				this.stopTimer();
				break;
			case BX.PhoneCallView.UiState.connectingOutgoing:
				this.setClosable(false);
				this.showOnlySections(['status']);
				this.renderCrmButtons();
				this.showCallIcon();
				this.stopTimer();
				break;
			case BX.PhoneCallView.UiState.connected:
				if(this.deviceCall)
					this.setClosable(true);
				else
					this.setClosable(false);

				this.showSections(['status', 'timer']);
				this.renderCrmButtons();
				this.showCallIcon();
				this.startTimer();
				break;
			case BX.PhoneCallView.UiState.transferring:
				this.setClosable(false);
				this.showSections(['status', 'timer']);
				this.renderCrmButtons();
				break;
			case BX.PhoneCallView.UiState.idle:
				this.setClosable(true);
				this.stopTimer();
				this.hideCallIcon();
				this.showOnlySections(['status']);
				this.renderCrmButtons();
				break;
			case BX.PhoneCallView.UiState.error:
				this.setClosable(true);
				this.stopTimer();
				this.hideCallIcon();
				break;
			case BX.PhoneCallView.UiState.moneyError:
				this.setClosable(true);
				this.stopTimer();
				this.hideCallIcon();
				break;
			case BX.PhoneCallView.UiState.sipPhoneError:
				this.setClosable(true);
				this.stopTimer();
				this.hideCallIcon();
				break;
			case BX.PhoneCallView.UiState.redial:
				this.setClosable(true);
				this.stopTimer();
				this.hideCallIcon();
				break;
		}

		if(this.isDesktop() && !this.slave)
		{
			BX.desktop.onCustomEvent(desktopEvents.setUiState, [uiState]);
			return;
		}
		this.renderButtons();
	};

	BX.PhoneCallView.prototype.setCallState = function(callState, additionalParams)
	{
		if(this.callState === callState)
			return;

		this.callState = callState;

		if(!BX.type.isPlainObject(additionalParams))
			additionalParams = {};

		this.renderButtons();
		if(callState === BX.PhoneCallView.CallState.connected && this.isAutoFoldAllowed())
		{
			this.fold();
		}

		BX.onCustomEvent(window, "CallCard::CallStateChanged", [callState, additionalParams]);
		this.setOnSlave(desktopEvents.setCallState, [callState, additionalParams]);
	};

	BX.PhoneCallView.prototype.isAutoFoldAllowed = function()
	{
		return (this.autoFold === true && !this.isDesktop() && !this.isFolded() && defaults.restApps.length == 0);
	};

	BX.PhoneCallView.prototype.isHeld = function()
	{
		return this.held;
	};

	BX.PhoneCallView.prototype.setHeld = function(held)
	{
		this.held = held;
	};

	BX.PhoneCallView.prototype.setRecording = function(recording)
	{
		this.recording = recording;
	};

	BX.PhoneCallView.prototype.isRecording = function()
	{
		return this.recording;
	};

	BX.PhoneCallView.prototype.isMuted = function()
	{
		return this.muted;
	};

	BX.PhoneCallView.prototype.setMuted = function(muted)
	{
		this.muted = muted;
	};

	BX.PhoneCallView.prototype.isTransfer = function()
	{
		return this.transfer;
	};

	BX.PhoneCallView.prototype.setTransfer = function(transfer)
	{
		transfer = (transfer == true);
		if(this.transfer == transfer)
		{
			return;
		}

		this.transfer = transfer;
		this.setOnSlave(desktopEvents.setTransfer, [transfer]);
		this.setUiState(this._uiState);
	};

	BX.PhoneCallView.prototype.isCallback = function()
	{
		return (this.direction === BX.PhoneCallView.Direction.callback);
	};

	BX.PhoneCallView.prototype.isPortalCall = function()
	{
		return this.portalCall;
	};

	BX.PhoneCallView.prototype.setCallback = function(eventName, callback)
	{
		if(!this.callbacks.hasOwnProperty(eventName))
			return false;

		this.callbacks[eventName] = BX.type.isFunction(callback) ? callback : nop;
	};

	BX.PhoneCallView.prototype.setDeviceCall = function(deviceCall)
	{
		this.deviceCall = deviceCall;

		if(this.elements.buttons.sipPhone)
		{
			if(deviceCall)
				BX.addClass(this.elements.buttons.sipPhone, 'active');
			else
				BX.removeClass(this.elements.buttons.sipPhone, 'active');
		}

		if(this.isDesktop() && !this.slave)
		{
			BX.desktop.onCustomEvent(desktopEvents.setDeviceCall, [deviceCall]);
		}
	};

	BX.PhoneCallView.prototype.setCrmEntity = function (params)
	{
		this.crmEntityType = params.type;
		this.crmEntityId = params.id;
		this.crmActivityId = params.activityId || '';
		this.crmActivityEditUrl = params.activityEditUrl || '';
		this.crmBindings = BX.type.isArray(params.bindings) ? params.bindings : [];

		if(this.isDesktop() && !this.slave)
		{
			BX.desktop.onCustomEvent(desktopEvents.setCrmEntity, [params]);
		}
	};

	BX.PhoneCallView.prototype.setCrmData = function (crmData)
	{
		if(!BX.type.isPlainObject(crmData))
			return;

		this.crm = true;
		this.crmData = crmData;
	};

	BX.PhoneCallView.prototype.loadCrmCard = function(entityType, entityId)
	{
		BX.onCustomEvent(window, 'CallCard::EntityChanged', [{
			'CRM_ENTITY_TYPE': entityType,
			'CRM_ENTITY_ID': entityId,
			'PHONE_NUMBER': this.phoneNumber
		}]);
		BackgroundWorker.onEntityChanged({
			'CRM_ENTITY_TYPE': entityType,
			'CRM_ENTITY_ID': entityId,
			'PHONE_NUMBER': this.phoneNumber
		});

		BX.ajax.runAction("voximplant.callview.getCrmCard", {
			data: {
				entityType: entityType,
				entityId: entityId
			}
		}).then(function(response)
		{
			if(this.currentLayout == layouts.simple)
			{
				this.currentLayout = layouts.crm;
				this.crm = true;
				var newMainElement = this.createLayoutCrm();

				this.elements.main.parentNode.replaceChild(newMainElement, this.elements.main);
				this.elements.main = newMainElement;
				this.setUiState(this._uiState);
				this.setStatusText(this.statusText);
			}

			if(this.elements.crmCard)
			{
				BX.html(this.elements.crmCard, response.data.html);
				setTimeout(function(){
					if(this.isDesktop())
					{
						this.resizeWindow(this.getInitialWidth(), this.getInitialHeight());
					}
					this.adjust();
					this.bindCrmCardEvents();
				}.bind(this), 100);
			}

			this.renderCrmButtons();
		}.bind(this)).catch(function(response)
		{
			console.error("Could not load crm card: ", response.errors[0])
		});
	};

	BX.PhoneCallView.prototype.reloadCrmCard = function()
	{
		if(this.isDesktop() && !this.slave)
		{
			BX.desktop.onCustomEvent(desktopEvents.reloadCrmCard, []);
		}
		else
		{
			this.loadCrmCard(this.crmEntityType, this.crmEntityId);
		}
	};

	BX.PhoneCallView.prototype.bindCrmCardEvents = function()
	{
		var self = this;
		if(!this.elements.crmCard)
			return;

		if(!BX.Crm || !BX.Crm.Page)
			return;

		var anchors = this.elements.crmCard.querySelectorAll('a[data-use-slider=Y]');
		for(var i = 0; i < anchors.length; i++)
		{
			BX.bind(anchors[i], 'click', function(e)
			{
				if(BX.Crm.Page.isSliderEnabled(e.currentTarget.href))
				{
					if(!self.isFolded())
					{
						self.fold();
					}
				}
			});
		}
	};

	BX.PhoneCallView.prototype.setPortalCallUserId = function(userId)
	{
		this.portalCallUserId = userId;
		this.setOnSlave(desktopEvents.setPortalCallUserId, [userId]);

		if(this.portalCallData && this.portalCallData.users[this.portalCallUserId])
		{
			this.renderAvatar();
			this.createTitle().then(function(title)
			{
				this.setTitle(title)
			}.bind(this));
		}
	};

	BX.PhoneCallView.prototype.setPortalCallQueueName = function(queueName)
	{
		this.portalCallQueueName = queueName;
		this.setOnSlave(desktopEvents.setPortalCallQueueName, [queueName]);

		this.createTitle().then(function(title)
		{
			this.setTitle(title)
		}.bind(this));
	};

	BX.PhoneCallView.prototype.setPortalCall = function(portalCall)
	{
		this.portalCall = (portalCall == true);
		this.setOnSlave(desktopEvents.setPortalCall, [portalCall]);
	};

	BX.PhoneCallView.prototype.setPortalCallData = function(data)
	{
		this.portalCallData = data;
		this.setOnSlave(desktopEvents.setPortalCallData, [data]);
	};

	BX.PhoneCallView.prototype.setOnSlave = function(message, parameters)
	{
		if(this.isDesktop() && !this.slave)
		{
			BX.desktop.onCustomEvent(message, parameters);
		}
	};

	BX.PhoneCallView.prototype.updateView = function()
	{
		if(this.elements.title)
			this.elements.title.innerHTML = this.renderTitle();

		if(this.elements.progress)
		{
			BX.cleanNode(this.elements.progress);
			this.elements.progress.appendChild(this.renderProgress());
		}

		if(this.elements.status)
			this.elements.status.innerText = this.statusText;

		this.renderButtons();
		this.renderTimer();
	};

	BX.PhoneCallView.prototype.renderProgress = function()
	{
		var result;
		var progress = this.progress;
		if (progress == 'connect')
		{
			result = BX.create("div", { props : { className : 'bx-messenger-call-overlay-progress'}, children: [
				BX.create("img", { props : { className : 'bx-messenger-call-overlay-progress-status bx-messenger-call-overlay-progress-status-anim-1'}}),
				BX.create("img", { props : { className : 'bx-messenger-call-overlay-progress-status bx-messenger-call-overlay-progress-status-anim-2'}})
			]});
		}
		else if (progress == 'online')
		{
			result =  BX.create("div", { props : { className : 'bx-messenger-call-overlay-progress bx-messenger-call-overlay-progress-online'}, children: [
				BX.create("img", { props : { className : 'bx-messenger-call-overlay-progress-status bx-messenger-call-overlay-progress-status-anim-3'}})
			]});
		}
		else if (progress == 'wait' || progress == 'offline' || progress == 'error')
		{
			if (progress == 'offline')
			{
				this.BXIM.playSound('error');
			}
			else if (progress == 'error')
			{
				progress = 'offline';
			}
			result =  BX.create("div", { props : { className : 'bx-messenger-call-overlay-progress bx-messenger-call-overlay-progress-'+progress}});
		}
		else
		{
			result =  BX.create("div", { props : { className : 'bx-messenger-call-overlay-progress bx-messenger-call-overlay-progress-'+progress}});
		}
		return result;
	};

	/**
	 * @param uiState BX.PhoneCallView.UiState
	 * @returns object {buttons: string[], layout: string}
	 */
	BX.PhoneCallView.prototype.getUiStateButtons = function(uiState)
	{
		var result = {
			buttons: [],
			layout: BX.PhoneCallView.ButtonLayouts.centered
		};
		switch (uiState)
		{
			case BX.PhoneCallView.UiState.incoming:
				result.buttons = ['answer', 'skip'];

				break;
			case BX.PhoneCallView.UiState.transferIncoming:
				result.buttons = ['answer', 'skip'];
				break;
			case BX.PhoneCallView.UiState.outgoing:
				result.buttons = ['call'];

				if(this.callListId > 0)
				{
					result.buttons.push('next');
					result.buttons.push('fold');

					if(!this.isDesktop())
						result.buttons.push('topClose');
				}
				break;
			case BX.PhoneCallView.UiState.connectingIncoming:
				result.buttons = ['hangup'];

				break;
			case BX.PhoneCallView.UiState.connectingOutgoing:
				if(this.hasSipPhone)
				{
					result.buttons.push('sipPhone');
				}
				result.buttons.push('hangup');
				break;
			case BX.PhoneCallView.UiState.error:
				if (this.hasSipPhone)
				{
					result.buttons.push('sipPhone');
				}
				if(this.callListId > 0)
				{
					result.buttons.push('redial', 'next', 'topClose');
				}
				else
				{
					result.buttons.push('close');
				}
				break;
			case BX.PhoneCallView.UiState.moneyError:
				result.buttons = ['notifyAdmin', 'close'];
				break;
			case BX.PhoneCallView.UiState.sipPhoneError:
				result.buttons = ['sipPhone', 'close'];
				break;
			case BX.PhoneCallView.UiState.connected:
				result.buttons = this.isTransfer() ? [] : ['hold'];
				if(!this.deviceCall)
				{
					result.buttons.push('mute', 'qualityMeter');
				}
				result.buttons.push('fold');


				if(!this.callListId && !this.isTransfer())
				{
					result.buttons.push('transfer');
				}

				if(this.deviceCall)
				{
					result.buttons.push('close');
				}
				else
				{
					result.buttons.push('dialpad', 'hangup');
				}

				result.layout = BX.PhoneCallView.ButtonLayouts.spaced;
				break;
			case BX.PhoneCallView.UiState.transferring:
				result.buttons = ['transferComplete', 'transferCancel'];
				break;
			case BX.PhoneCallView.UiState.transferFailed:
				result.buttons = ['transferCancel'];
				break;
			case BX.PhoneCallView.UiState.transferConnected:
				result.buttons = ['hangup'];
				break;
			case BX.PhoneCallView.UiState.idle:
				if (this.hasSipPhone)
					result.buttons = ['close'];
				else if (this.direction == BX.PhoneCallView.Direction.incoming)
					result.buttons = ['close'];
				else if (this.direction == BX.PhoneCallView.Direction.outgoing)
				{
					result.buttons = ['redial'];
					if(this.callListId > 0)
					{
						result.buttons.push('next');
						result.buttons.push('fold');
					}
					else
					{
						result.buttons.push('close');
					}
				}
				if(this.callListId > 0 && !this.isDesktop())
				{
					result.buttons.push('topClose');
				}
				break;
			case BX.PhoneCallView.UiState.redial:
				result.buttons = ['redial'];
				break;
			case BX.PhoneCallView.UiState.externalCard:
				result.buttons = ['close'];
				result.buttons.push('fold');
				break;
		}

		return result;
	};

	BX.PhoneCallView.prototype.renderButtons = function()
	{
		if(this.isFolded())
		{
			this.renderButtonsFolded();
		}
		else
		{
			this.renderButtonsDefault();
		}
	};

	BX.PhoneCallView.prototype.renderButtonsDefault = function()
	{
		var self = this;
		var buttonsFragment = document.createDocumentFragment();
		var topButtonsFragment = document.createDocumentFragment();
		var topLevelButtonsFragment = document.createDocumentFragment();
		var subContainers = {
			left: null,
			right: null
		};
		this.elements.buttons = {};
		if(this.buttonLayout == BX.PhoneCallView.ButtonLayouts.spaced)
		{
			subContainers.left = BX.create('div', {props: {className: 'im-phone-call-buttons-container-left'}});
			subContainers.right = BX.create('div', {props: {className: 'im-phone-call-buttons-container-right'}});
			buttonsFragment.appendChild(subContainers.left);
			buttonsFragment.appendChild(subContainers.right);
		}

		this.buttons.forEach(function(buttonName)
		{
			var buttonNode;

			switch (buttonName)
			{
				case 'hold':
					buttonNode = self._renderSimpleButton('', 'im-phone-call-btn-hold', self._onHoldButtonClickHandler);
					if(self.isHeld())
						BX.addClass(buttonNode, 'active');

					if(self.buttonLayout == BX.PhoneCallView.ButtonLayouts.spaced)
						subContainers.left.appendChild(buttonNode);
					else
						buttonsFragment.appendChild(buttonNode);

					break;
				case 'mute':
					buttonNode = self._renderSimpleButton('', 'im-phone-call-btn-mute', self._onMuteButtonClickHandler);
					if(self.isMuted())
						BX.addClass(buttonNode, 'active');

					if(self.buttonLayout == BX.PhoneCallView.ButtonLayouts.spaced)
						subContainers.left.appendChild(buttonNode);
					else
						buttonsFragment.appendChild(buttonNode);

					break;
				case 'transfer':
					buttonNode = self._renderSimpleButton('', 'im-phone-call-btn-transfer', self._onTransferButtonClickHandler);
					if(self.buttonLayout == BX.PhoneCallView.ButtonLayouts.spaced)
						subContainers.left.appendChild(buttonNode);
					else
						buttonsFragment.appendChild(buttonNode);

					break;
				case 'transferComplete':
					buttonNode = self._renderSimpleButton(
						BX.message('IM_M_CALL_BTN_TRANSFER'),
						'im-phone-call-btn im-phone-call-btn-blue im-phone-call-btn-arrow',
						self._onTransferCompleteButtonClickHandler
					);
					buttonsFragment.appendChild(buttonNode);
					break;
				case 'transferCancel':
					buttonNode = self._renderSimpleButton(
						BX.message('IM_M_CALL_BTN_RETURN'),
						'im-phone-call-btn im-phone-call-btn-red',
						self._onTransferCancelButtonClickHandler
					);
					buttonsFragment.appendChild(buttonNode);
					break;
				case 'dialpad':
					buttonNode = self._renderSimpleButton('', 'im-phone-call-btn-dialpad', self._onDialpadButtonClickHandler);
					if(self.buttonLayout == BX.PhoneCallView.ButtonLayouts.spaced)
						subContainers.left.appendChild(buttonNode);
					else
						buttonsFragment.appendChild(buttonNode);

					break;
				case 'call':
					buttonNode = self._renderSimpleButton(
						BX.message('IM_PHONE_CALL'),
						'im-phone-call-btn im-phone-call-btn-green',
						self._onMakeCallButtonClickHandler
					);
					buttonsFragment.appendChild(buttonNode);
					break;
				case 'answer':
					buttonNode = self._renderSimpleButton(
						BX.message('IM_PHONE_BTN_ANSWER'),
						'im-phone-call-btn im-phone-call-btn-green',
						self._onAnswerButtonClickHandler
					);
					buttonsFragment.appendChild(buttonNode);
					break;
				case 'skip':
					buttonNode = self._renderSimpleButton(
						BX.message('IM_PHONE_BTN_BUSY'),
						'im-phone-call-btn im-phone-call-btn-red',
						self._onSkipButtonClickHandler
					);
					buttonsFragment.appendChild(buttonNode);
					break;
				case 'hangup':
					buttonNode = self._renderSimpleButton(
						BX.message('IM_M_CALL_BTN_HANGUP'),
						'im-phone-call-btn im-phone-call-btn-red  im-phone-call-btn-tube',
						self._onHangupButtonClickHandler
					);
					if(self.buttonLayout == BX.PhoneCallView.ButtonLayouts.spaced)
						subContainers.right.appendChild(buttonNode);
					else
						buttonsFragment.appendChild(buttonNode);

					break;
				case 'close':
					buttonNode =  self._renderSimpleButton(
						BX.message('IM_M_CALL_BTN_CLOSE'),
						'im-phone-call-btn im-phone-call-btn-red',
						self._onCloseButtonClickHandler
					);
					if(self.buttonLayout == BX.PhoneCallView.ButtonLayouts.spaced)
						subContainers.right.appendChild(buttonNode);
					else
						buttonsFragment.appendChild(buttonNode);

					break;
				case 'topClose':
					if(!self.isDesktop())
					{
						buttonNode = BX.create("div", {
							props: {className: 'im-phone-call-top-close-btn'},
							events: {
								click: self._onCloseButtonClickHandler
							}
						});
						topLevelButtonsFragment.appendChild(buttonNode);
					}
					break;
				case 'notifyAdmin':
					buttonNode = self._renderSimpleButton(
						BX.message('IM_M_CALL_BTN_NOTIFY_ADMIN'),
						'im-phone-call-btn im-phone-call-btn-blue im-phone-call-btn-arrow',
						function()
						{
							BackgroundWorker.isUsed
								? BackgroundWorker.onNotifyAdminButtonClick()
								: self.callbacks.notifyAdmin()
							;
						}
					);
					buttonsFragment.appendChild(buttonNode);
					break;
				case 'sipPhone':
					buttonNode = self._renderSimpleButton('', (self.deviceCall ? 'im-phone-call-btn-phone active' : 'im-phone-call-btn-phone'), self._onSwitchDeviceButtonClickHandler);
					if(self.buttonLayout == BX.PhoneCallView.ButtonLayouts.spaced)
						subContainers.left.appendChild(buttonNode);
					else
						buttonsFragment.appendChild(buttonNode);
					break;
				case 'qualityMeter':
					buttonNode = BX.create("span", {
						props: {className: 'im-phone-call-btn-signal'},
						events: {click: self._onQualityMeterClickHandler},
						children: [
							BX.create("span", {props: {className: 'im-phone-call-btn-signal-icon-container'}, children: [
								BX.create("span", {props: {className: 'im-phone-call-btn-signal-background'}}),
								self.elements.qualityMeter = BX.create("span", {
									props: {className: 'im-phone-call-btn-signal-active'},
									style: {width: self.getQualityMeterWidth()}
								})
							]})
						]
					});
					buttonsFragment.appendChild(buttonNode);

					break;
				case 'settings':
					// todo
					break;
				case 'next':
					buttonNode = self._renderSimpleButton(
						BX.message('IM_M_CALL_BTN_NEXT'),
						'im-phone-call-btn im-phone-call-btn-gray im-phone-call-btn-arrow',
						self._onNextButtonClickHandler
					);
					buttonsFragment.appendChild(buttonNode);
					break;
				case 'redial':
					buttonNode = self._renderSimpleButton(
						BX.message('IM_M_CALL_BTN_RECALL'),
						'im-phone-call-btn im-phone-call-btn-green',
						self._onMakeCallButtonClickHandler
					);
					buttonsFragment.appendChild(buttonNode);
					break;
				case 'fold':
					if(!self.isDesktop() && self.canBeFolded())
					{
						buttonNode = BX.create("div", {props: {className: 'im-phone-btn-arrow'},text: BX.message('IM_PHONE_CALL_VIEW_FOLD'), events: {
							click: self._onFoldButtonClickHandler
						}});
						topButtonsFragment.appendChild(buttonNode);
					}
					break;
				default:
					throw "Unknown button " + buttonName;
			}

			if(buttonNode)
			{
				self.elements.buttons[buttonName] = buttonNode;
			}
		});
		if(this.elements.buttonsContainer)
		{
			BX.cleanNode(this.elements.buttonsContainer);
			this.elements.buttonsContainer.appendChild(buttonsFragment);
		}
		if(this.elements.topButtonsContainer)
		{
			BX.cleanNode(this.elements.topButtonsContainer);
			this.elements.topButtonsContainer.appendChild(topButtonsFragment);
		}
		if(this.elements.topLevelButtonsContainer)
		{
			BX.cleanNode(this.elements.topLevelButtonsContainer);
			this.elements.topLevelButtonsContainer.appendChild(topLevelButtonsFragment);
		}
	};

	BX.PhoneCallView.prototype.renderButtonsFolded = function()
	{
		var self = this;
		var buttonsFragment = document.createDocumentFragment();
		this.elements.buttons = {};

		this.buttons.forEach(function(buttonName)
		{
			switch (buttonName)
			{
				case 'hangup':
					buttonNode = self._renderSimpleButton(
						BX.message('IM_M_CALL_BTN_HANGUP'),
						'im-phone-call-panel-mini-cancel',
						self._onHangupButtonClickHandler
					);
					buttonsFragment.appendChild(buttonNode);

					break;
				case 'close':
					buttonNode =  self._renderSimpleButton(
						BX.message('IM_M_CALL_BTN_CLOSE'),
						'im-phone-call-panel-mini-cancel',
						self._onCloseButtonClickHandler
					);
					buttonsFragment.appendChild(buttonNode);
					break;
			}
		});

		if(this.elements.buttonsContainer)
		{
			BX.cleanNode(this.elements.buttonsContainer);
			this.elements.buttonsContainer.appendChild(buttonsFragment);
		}
	};

	BX.PhoneCallView.prototype.renderCrmButtons = function()
	{
		var self = this;
		var buttons;
		var buttonsFragment = document.createDocumentFragment();
		this.elements.crmButtons = {};

		if(!this.elements.crmButtonsContainer)
			return;

		buttons = ['addComment'];

		if(this.crmEntityType == 'CONTACT')
		{
			buttons.push('addDeal');
			buttons.push('addInvoice');
		}
		else if (this.crmEntityType == 'COMPANY')
		{
			buttons.push('addDeal');
			buttons.push('addInvoice');
		}
		else if (!this.crmEntityType && this.config.CRM_CREATE == 'none')
		{
			buttons.push('addLead');
			buttons.push('addContact');
		}

		if(buttons.length  > 0)
		{
			buttons.forEach(function(buttonName)
			{
				var buttonNode;
				switch (buttonName)
				{
					case 'addComment':
						buttonNode = BX.create("div", {
							props: {className: 'im-phone-call-crm-button im-phone-call-crm-button-comment' + (self.commentShown ? ' im-phone-call-crm-button-active' : '')},
							children: [
								self.elements.crmButtons.addCommentLabel = BX.create("div", {props: {className: 'im-phone-call-crm-button-item'},
									text: self.commentShown ? BX.message('IM_PHONE_CALL_VIEW_SAVE') : BX.message('IM_PHONE_ACTION_CRM_COMMENT')
								})],
							events: {click: self._onAddCommentButtonClick.bind(self)}
						});
						break;
					case 'addDeal':
						buttonNode = BX.create("div", {
							props: {className: 'im-phone-call-crm-button'}, children: [
								BX.create("div", {props: {className: 'im-phone-call-crm-button-item'},
									text: BX.message('IM_PHONE_ACTION_CRM_DEAL')
								})],
							events: {click: self._onAddDealButtonClick.bind(self)}
						});
						break;
					case 'addInvoice':
						buttonNode = BX.create("div", {
							props: {className: 'im-phone-call-crm-button'}, children: [
								BX.create("div", {props: {className: 'im-phone-call-crm-button-item'},
									text: BX.message('IM_PHONE_ACTION_CRM_INVOICE')
								})],
							events: {click: self._onAddInvoiceButtonClick.bind(self)}
						});
						break;
					case 'addLead':
						buttonNode = BX.create("div", {
							props: {className: 'im-phone-call-crm-button'}, children: [
								BX.create("div", {props: {className: 'im-phone-call-crm-button-item'},
									text: BX.message('IM_CRM_BTN_NEW_LEAD')
								})],
							events: {click: self._onAddLeadButtonClick.bind(self)}
						});
						break;
					case 'addContact':
						buttonNode = BX.create("div", {
							props: {className: 'im-phone-call-crm-button'}, children: [
								BX.create("div", {props: {className: 'im-phone-call-crm-button-item'},
									text: BX.message('IM_CRM_BTN_NEW_CONTACT')
								})],
							events: {click: self._onAddContactButtonClick.bind(self)}
						});
						break;
				}
				if(buttonNode)
				{
					buttonsFragment.appendChild(buttonNode);
					self.elements.crmButtons[buttonName] = buttonNode;
				}
			});

			BX.cleanNode(this.elements.crmButtonsContainer);
			this.elements.crmButtonsContainer.appendChild(buttonsFragment);
			this.showSections(['crmButtons']);
		}
		else
		{
			BX.cleanNode(this.elements.crmButtonsContainer);
			this.hideSections(['crmButtons']);
		}
	};

	BX.PhoneCallView.prototype._renderSimpleButton = function(text, className, clickCallback)
	{
		var params = { };
		if (text != '')
			params.text = text;

		if (className != '')
			params.props = {className: className};

		if (BX.type.isFunction(clickCallback))
			params.events = {click: clickCallback};

		return BX.create('span', params);
	};

	BX.PhoneCallView.prototype.loadForm = function(params)
	{
		if(!this.formManager)
			return;

		this.formManager.load({
			id: params.id,
			secCode: params.secCode
		})
	};

	BX.PhoneCallView.prototype.unloadForm = function()
	{
		if(!this.formManager)
			return;

		this.formManager.unload();
		BX.cleanNode(this.elements.tabsBody.webform);
	};

	BX.PhoneCallView.prototype._onFormSend = function(e)
	{
		if(!this.callListView)
			return;

		var currentElement = this.callListView.getCurrentElement();
		this.callListView.setWebformResult(currentElement.ELEMENT_ID, e.resultId);
	};

	BX.PhoneCallView.prototype.loadRestApp = function(params)
	{
		var restAppId = params.id;
		var node = params.node;

		if(this.restAppLayoutLoaded)
		{
			BX.rest.AppLayout.getPlacement('CALL_CARD').load(restAppId, this.getPlacementOptions());
			return;
		}

		if(this.restAppLayoutLoading)
		{
			return;
		}
		this.restAppLayoutLoading = true;

		BX.ajax.runAction("voximplant.callView.loadRestApp", {
			data: {
				'appId': restAppId,
				'placementOptions': this.getPlacementOptions()
			}
		}).then(function(response)
		{
			if(!this.popup && !this.isDesktop())
			{
				return;
			}
			BX.html(node, response.data.html);
			this.restAppLayoutLoaded = true;
			this.restAppLayoutLoading = false;
			this.restAppInterface = BX.rest.AppLayout.initializePlacement('CALL_CARD');
			this.initializeAppInterface(this.restAppInterface);
		}.bind(this));
	};

	BX.PhoneCallView.prototype.unloadRestApps = function()
	{
		if(!BX.rest || !BX.rest.AppLayout)
		{
			return false;
		}

		var placement = BX.rest.AppLayout.getPlacement('CALL_CARD');
		if(this.restAppLayoutLoaded && placement)
		{
			placement.destroy();
			this.restAppLayoutLoaded = false;
		}
	};

	BX.PhoneCallView.prototype.initializeAppInterface = function(appInterface)
	{
		appInterface.prototype.events.push('CallCard::EntityChanged');
		appInterface.prototype.events.push('CallCard::BeforeClose');
		appInterface.prototype.events.push('CallCard::CallStateChanged');
		appInterface.prototype.getStatus = function(params, cb)
		{
			cb(this.getPlacementOptions());
		}.bind(this);

		appInterface.prototype.disableAutoClose = function(params, cb)
		{
			this.disableAutoClose();
			cb([]);
		}.bind(this);

		appInterface.prototype.enableAutoClose = function(params, cb)
		{
			this.enableAutoClose();
			cb([]);
		}.bind(this);
	};

	BX.PhoneCallView.prototype.getPlacementOptions = function()
	{
		return {
			'CALL_ID': this.callId,
			'PHONE_NUMBER': this.phoneNumber === "unknown" ? undefined : this.phoneNumber,
			'LINE_NUMBER': this.lineNumber,
			'LINE_NAME': this.companyPhoneNumber,
			'CRM_ENTITY_TYPE': this.crmEntityType,
			'CRM_ENTITY_ID': this.crmEntityId,
			'CRM_ACTIVITY_ID': this.crmActivityId === 0 ? undefined : this.crmActivityId,
			'CRM_BINDINGS': this.crmBindings,
			'CALL_DIRECTION': this.direction,
			'CALL_STATE': this.callState,
			'CALL_LIST_MODE': this.callListId > 0
		}
	};

	BX.PhoneCallView.prototype.isUnloadAllowed = function()
	{
		if (BackgroundWorker.isActiveIntoCurrentCall())
		{
			return false;
		}
		return this.folded
			&& (
				this.deviceCall ||
				this._uiState === BX.PhoneCallView.UiState.idle ||
				this._uiState === BX.PhoneCallView.UiState.error ||
				this._uiState === BX.PhoneCallView.UiState.externalCard
			);
	};

	BX.PhoneCallView.prototype._onBeforeUnload = function(e)
	{
		if(!this.isUnloadAllowed())
		{
			e.returnValue = BX.message('IM_PHONE_CALL_VIEW_DONT_LEAVE');
			return BX.message('IM_PHONE_CALL_VIEW_DONT_LEAVE');
		}
	};

	BX.PhoneCallView.prototype._onDblClick = function(e)
	{
		BX.PreventDefault(e);
		if(!this.isFolded() && this.canBeFolded())
			this.fold();
	};

	BX.PhoneCallView.prototype._onHoldButtonClick = function(e)
	{
		if (this.isHeld())
		{
			this.held = false;
			BX.removeClass(this.elements.buttons.hold, 'active');
			if(this.isDesktop() && this.slave)
				BX.desktop.onCustomEvent(desktopEvents.onUnHold, []);
			else
				this.callbacks.unhold();
		}
		else
		{
			this.held = true;
			BX.addClass(this.elements.buttons.hold, 'active');
			if(this.isDesktop() && this.slave)
				BX.desktop.onCustomEvent(desktopEvents.onHold, []);
			else
				this.callbacks.hold();
		}
		BackgroundWorker.onHoldButtonClick(this.isHeld())
	};

	BX.PhoneCallView.prototype._onMuteButtonClick = function(e)
	{
		if (this.isMuted())
		{
			this.muted = false;
			BX.removeClass(this.elements.buttons.mute, 'active');
			if(this.isDesktop() && this.slave)
				BX.desktop.onCustomEvent(desktopEvents.onUnMute, []);
			else
				this.callbacks.unmute();
		}
		else
		{
			this.muted = true;
			BX.addClass(this.elements.buttons.mute, 'active');
			if(this.isDesktop() && this.slave)
				BX.desktop.onCustomEvent(desktopEvents.onMute, []);
			else
				this.callbacks.mute();
		}
		BackgroundWorker.onMuteButtonClick(this.isMuted());
	};
	BX.PhoneCallView.prototype._onTransferButtonClick = function(e)
	{
		this.selectTransferTarget(function(result)
		{
			BackgroundWorker.onTransferButtonClick(result)
			if(this.isDesktop() && this.slave)
			{
				BX.desktop.onCustomEvent(desktopEvents.onStartTransfer, [result]);
			}
			else
			{
				this.callbacks.transfer(result);
			}
		}.bind(this));
	};

	BX.PhoneCallView.prototype._onTransferCompleteButtonClick = function(e)
	{
		BackgroundWorker.onCompleteTransferButtonClick();
		if(this.isDesktop() && this.slave)
			BX.desktop.onCustomEvent(desktopEvents.onCompleteTransfer, []);
		else
			this.callbacks.completeTransfer();
	};

	BX.PhoneCallView.prototype._onTransferCancelButtonClick = function(e)
	{
		BackgroundWorker.onCancelTransferButtonClick();
		if(this.isDesktop() && this.slave)
			BX.desktop.onCustomEvent(desktopEvents.onCancelTransfer, []);
		else
			this.callbacks.cancelTransfer();
	};

	BX.PhoneCallView.prototype._onDialpadButtonClick = function(e)
	{
		var self = this;
		this.keypad = new Keypad({
			bindElement: this.elements.buttons.dialpad,
			hideDial: true,
			onButtonClick: function(e)
			{
				var key = e.key;
				if(self.isDesktop() && self.slave)
					BX.desktop.onCustomEvent(desktopEvents.onDialpadButtonClicked, [key]);
				else
					self.callbacks.dialpadButtonClicked(key);
				BackgroundWorker.onDialpadButtonClick(key);
			},
			onClose: function(e)
			{
				self.keypad.destroy();
				self.keypad = null;
			}
		});
		self.keypad.show();
	};
	BX.PhoneCallView.prototype._onHangupButtonClick = function(e)
	{
		BackgroundWorker.onHangupButtonClick();
		if(this.isDesktop() && this.slave)
			BX.desktop.onCustomEvent(desktopEvents.onHangup, []);
		else
			this.callbacks.hangup();
	};
	BX.PhoneCallView.prototype._onCloseButtonClick = function(e)
	{
		BackgroundWorker.onCloseButtonClick();
		if(this.isDesktop() && this.slave)
			BX.desktop.onCustomEvent(desktopEvents.onClose, []);
		else
			this.close();
	};
	BX.PhoneCallView.prototype._onMakeCallButtonClick = function(e)
	{
		BackgroundWorker.onMakeCallButtonClick();
		var event = {};
		if(this.callListId > 0)
		{
			this.callingEntity = this.currentEntity;

			if(this.currentEntity.phones.length === 0)
			{
				// show keypad and dial entered number
				this.keypad = new Keypad({
					bindElement: this.elements.buttons.call ? this.elements.buttons.call : null,
					onClose: function()
					{
						this.keypad.destroy();
						this.keypad = null;
					}.bind(this),
					onDial: function(e)
					{
						this.keypad.close();
						this.phoneNumber = e.phoneNumber;
						this.createTitle().then(function(title)
						{
							this.setTitle(title)
						}.bind(this));

						event = {
							phoneNumber: e.phoneNumber,
							crmEntityType: this.crmEntityType,
							crmEntityId: this.crmEntityId,
							callListId: this.callListId
						};

						if(this.isDesktop() && this.slave)
						{
							BX.desktop.onCustomEvent(desktopEvents.onCallListMakeCall, [event]);
						}
						else
						{
							this.callbacks.callListMakeCall(event);
						}
					}.bind(this)
				});
				this.keypad.show();
			}
			else if(this.currentEntity.phones.length == 1)
			{
				// just dial the number
				event.phoneNumber = this.currentEntity.phones[0].VALUE;
				event.crmEntityType = this.crmEntityType;
				event.crmEntityId = this.crmEntityId;
				event.callListId = this.callListId;
				if(this.isDesktop() && this.slave)
				{
					BX.desktop.onCustomEvent(desktopEvents.onCallListMakeCall, [event]);
				}
				else
				{
					this.callbacks.callListMakeCall(event);
				}
			}
			else
			{
				// allow user to select the number
				this.showNumberSelectMenu({
					bindElement: this.elements.buttons.call ? this.elements.buttons.call : null,
					phoneNumbers: this.currentEntity.phones,
					onSelect: function(e)
					{
						this.closeNumberSelectMenu();
						this.phoneNumber = e.phoneNumber;
						this.createTitle().then(function(title){
							this.setTitle(title)
						}.bind(this));

						event = {
							phoneNumber: e.phoneNumber,
							crmEntityType: this.crmEntityType,
							crmEntityId: this.crmEntityId,
							callListId: this.callListId
						};

						if(this.isDesktop() && this.slave)
							BX.desktop.onCustomEvent(desktopEvents.onCallListMakeCall, [event]);
						else
							this.callbacks.callListMakeCall(event);
					}.bind(this)
				});
			}
		}
		else
		{
			if(this.isDesktop() && this.slave)
				BX.desktop.onCustomEvent(desktopEvents.onMakeCall, [this.phoneNumber]);
			else
				this.callbacks.makeCall(this.phoneNumber);
		}
	};

	BX.PhoneCallView.prototype._onNextButtonClick = function(e)
	{
		if(!this.callListView)
			return;

		BackgroundWorker.onNextButtonClick();
		this.setUiState(BX.PhoneCallView.UiState.outgoing);
		this.callListView.moveToNextItem();
		this.setStatusText('');
	};

	BX.PhoneCallView.prototype._onRedialButtonClick = function(e)
	{

	};

	BX.PhoneCallView.prototype._onCommentChanged = function(e)
	{
		this.comment = this.elements.commentEditor.value;
	};

	BX.PhoneCallView.prototype._onAddCommentButtonClick = function(e)
	{
		this.commentShown = !this.commentShown;
		if(this.isDesktop() && this.slave)
		{
			BX.desktop.onCustomEvent(desktopEvents.onCommentShown, [this.commentShown]);
		}

		if(this.commentShown)
		{
			if(this.elements.crmButtons.addComment)
			{
				BX.addClass(this.elements.crmButtons.addComment, 'im-phone-call-crm-button-active');
				this.elements.crmButtons.addCommentLabel.innerText = BX.message('IM_PHONE_CALL_VIEW_SAVE');
			}
			if(this.elements.commentEditor)
			{
				this.elements.commentEditor.value = this.comment;
				this.elements.commentEditor.focus();
			}

			if(this.elements.commentEditorContainer)
				this.elements.commentEditorContainer.style.removeProperty('display');
		}
		else
		{

			if(this.elements.crmButtons.addComment)
			{
				BX.removeClass(this.elements.crmButtons.addComment, 'im-phone-call-crm-button-active');
				this.elements.crmButtons.addCommentLabel.innerText = BX.message('IM_PHONE_ACTION_CRM_COMMENT');
			}

			if(this.elements.commentEditorContainer)
				this.elements.commentEditorContainer.style.display = 'none';

			if(this.isDesktop() && this.slave)
				BX.desktop.onCustomEvent(desktopEvents.onSaveComment, [this.comment]);
			else
				this.saveComment();

			BackgroundWorker.onAddCommentButtonClick(this.comment);
		}
	};

	BX.PhoneCallView.prototype._onAddDealButtonClick = function(e)
	{
		var url = this._getCrmEditUrl('DEAL', 0);
		var externalContext = this._generateExternalContext();
		if(this.crmEntityType === 'CONTACT')
			url = BX.util.add_url_param(url, { contact_id: this.crmEntityId });
		else if(this.crmEntityType === 'COMPANY')
			url = BX.util.add_url_param(url, { company_id: this.crmEntityId });

		url = BX.util.add_url_param(url, { external_context: externalContext });
		if(this.callListId > 0)
		{
			url = BX.util.add_url_param(url, { call_list_id: this.callListId });
			url = BX.util.add_url_param(url, { call_list_element: this.currentEntity.id });
		}

		this.externalRequests[externalContext] = {
			type: 'add',
			context: externalContext,
			window: window.open(url)
		};
	};

	BX.PhoneCallView.prototype._onAddInvoiceButtonClick = function(e)
	{
		var url = this._getCrmEditUrl('INVOICE', 0);
		url = BX.util.add_url_param(url, { redirect: "y" });
		var externalContext = this._generateExternalContext();
		if(this.crmEntityType === 'CONTACT')
			url = BX.util.add_url_param(url, { contact: this.crmEntityId });
		else if(this.crmEntityType === 'COMPANY')
			url = BX.util.add_url_param(url, { company: this.crmEntityId });

		url = BX.util.add_url_param(url, { external_context: externalContext });
		if(this.callListId > 0)
		{
			url = BX.util.add_url_param(url, { call_list_id: this.callListId });
			url = BX.util.add_url_param(url, { call_list_element: this.currentEntity.id });
		}

		this.externalRequests[externalContext] = {
			type: 'add',
			context: externalContext,
			window: window.open(url)
		};
	};

	BX.PhoneCallView.prototype._onAddLeadButtonClick = function(e)
	{
		var url = this._getCrmEditUrl('LEAD', 0);
		url = BX.util.add_url_param(url, {
				phone: this.phoneNumber,
				origin_id: 'VI_' + this.callId
		});
		window.open(url);
	};

	BX.PhoneCallView.prototype._onAddContactButtonClick = function(e)
	{
		var url = this._getCrmEditUrl('CONTACT', 0);
		url = BX.util.add_url_param(url, {
			phone: this.phoneNumber,
			origin_id: 'VI_' + this.callId
		});
		window.open(url);
	};

	BX.PhoneCallView.prototype._onFoldButtonClick = function(e)
	{
		this.fold();
	};

	BX.PhoneCallView.prototype._onAnswerButtonClick = function(e)
	{
		BackgroundWorker.onAnswerButtonClick();
		if(this.isDesktop() && this.slave)
			BX.desktop.onCustomEvent(desktopEvents.onAnswer, []);
		else
			this.callbacks.answer();
	};

	BX.PhoneCallView.prototype._onSkipButtonClick = function(e)
	{
		BackgroundWorker.onSkipButtonClick();
		if(this.isDesktop() && this.slave)
			BX.desktop.onCustomEvent(desktopEvents.onSkip, []);
		else
			this.callbacks.skip();
	};

	BX.PhoneCallView.prototype._onSwitchDeviceButtonClick = function(e)
	{
		if(this.isDesktop() && this.slave)
			BX.desktop.onCustomEvent(desktopEvents.onSwitchDevice, [{
				phoneNumber: this.phoneNumber
			}]);
		else
			this.callbacks.switchDevice({
				phoneNumber: this.phoneNumber
			});
	};

	BX.PhoneCallView.prototype._onQualityMeterClick = function(e)
	{
		var self = this;
		this.showQualityPopup({
			onSelect: function(qualityGrade)
			{
				BackgroundWorker.onQualityMeterClick(qualityGrade);
				self.qualityGrade = qualityGrade;
				self.closeQualityPopup();
				if(self.isDesktop() && self.slave)
					BX.desktop.onCustomEvent(desktopEvents.onQualityGraded, [qualityGrade]);
				else
					self.callbacks.qualityGraded(qualityGrade);
			}
		});
	};

	BX.PhoneCallView.prototype._onExternalEvent = function(params)
	{
		params = BX.type.isPlainObject(params) ? params : {};
		params.key = params.key || '';

		var value = params.value || {};
		value.entityTypeName = value.entityTypeName || '';
		value.context = value.context || '';
		value.isCanceled = BX.type.isBoolean(value.isCanceled) ? value.isCanceled : false;

		if(value.isCanceled)
			return;

		if(params.key === "onCrmEntityCreate" && this.externalRequests[value.context])
		{
			if(this.externalRequests[value.context])
			{
				if (this.externalRequests[value.context]['type'] == 'create')
				{
					this.crmEntityType = value.entityTypeName;
					this.crmEntityId = value.entityInfo.id;
					this.loadCrmCard(this.crmEntityType, this.crmEntityId);
				}
				else if (this.externalRequests[value.context]['type'] == 'add')
				{
					// reload crm card
					this.loadCrmCard(this.crmEntityType, this.crmEntityId);
				}

				if(this.externalRequests[value.context]['window'])
					this.externalRequests[value.context]['window'].close();

				delete this.externalRequests[value.context];
			}
		}
	};

	BX.PhoneCallView.prototype._onPullEventCrm = function(command, params)
	{

		if(command === 'external_event')
		{
			if(params.NAME === 'onCrmEntityCreate' && params.IS_CANCELED == false)
			{
				var eventParams = params.PARAMS;
				if(this.externalRequests[eventParams.context])
				{
					var crmEntityType = eventParams.entityTypeName;
					var crmEntityId = eventParams.entityInfo.id;

					if(this.callListView)
					{
						var currentElement = this.callListView.getCurrentElement();
					}
				}
			}
		}
	};

	BX.PhoneCallView.prototype.onCallListSelectedItem = function(entity)
	{
		this.currentEntity = entity;
		this.crmEntityType = entity.type;
		this.crmEntityId = entity.id;
		this.comment = "";

		if(BX.type.isArray(entity.bindings))
		{
			this.crmBindings = entity.bindings.map(function(value)
			{
				return {
					'ENTITY_TYPE': value.type,
					'ENTITY_ID': value.id
				};
			});
		}
		else
		{
			this.crmBindings = [];
		}

		if(entity.phones.length > 0)
			this.phoneNumber = entity.phones[0].VALUE;
		else
			this.phoneNumber = 'unknown';

		this.createTitle().then(this.setTitle.bind(this));
		this.loadCrmCard(entity.type, entity.id);
		if(this.currentTabName === 'webform')
		{
			this.formManager.unload();
			this.formManager.load({
				id: this.webformId,
				secCode: this.webformSecCode,
				lang: BX.message("LANGUAGE_ID"),
			})
		}
		if(this._uiState === BX.PhoneCallView.UiState.redial)
			this.setUiState(BX.PhoneCallView.UiState.outgoing);

		this.updateView();
	};

	BX.PhoneCallView.prototype._onWindowUnload = function()
	{
		this.close();
	};

	BX.PhoneCallView.prototype.showCallIcon = function()
	{
		if(!this.callListView)
			return;

		if(!this.callingEntity)
			return;

		this.callListView.setCallingElement(this.callingEntity.statusId, this.callingEntity.index);
	};

	BX.PhoneCallView.prototype.hideCallIcon = function()
	{
		if(!this.callListView)
			return;

		this.callListView.resetCallingElement();
	};

	BX.PhoneCallView.prototype.isTimerStarted = function()
	{
		return !!this.timerInterval;
	}

	BX.PhoneCallView.prototype.startTimer = function()
	{
		if(this.isTimerStarted())
		{
			return;
		}

		if(this.initialTimestamp === 0)
		{
			this.initialTimestamp = (new Date()).getTime();
		}
		this.timerInterval = setInterval(this.renderTimer.bind(this), 1000);
		this.renderTimer();
	};

	BX.PhoneCallView.prototype.renderTimer = function()
	{
		if(!this.elements.timer)
			return;

		var currentTimestamp = (new Date()).getTime();
		var elapsedMilliSeconds = (currentTimestamp - this.initialTimestamp);

		var elapsedSeconds = Math.floor(elapsedMilliSeconds / 1000);
		var minutes = Math.floor(elapsedSeconds / 60).toString();
		if(minutes.length < 2)
			minutes = '0' + minutes;
		var seconds = (elapsedSeconds % 60).toString();
		if(seconds.length < 2)
			seconds = '0' + seconds;
		var template = (this.isRecording() ? BX.message('IM_PHONE_TIMER_WITH_RECORD') : BX.message('IM_PHONE_TIMER_WITHOUT_RECORD'));

		if(this.isFolded())
		{
			this.elements.timer.innerText = minutes + ':' + seconds;
		}
		else
		{
			this.elements.timer.innerText = template.replace('#MIN#', minutes).replace('#SEC#', seconds);
		}
	};

	BX.PhoneCallView.prototype.stopTimer = function()
	{
		if(!this.isTimerStarted())
		{
			return;
		}

		clearInterval(this.timerInterval);
		this.timerInterval = null;
	};

	BX.PhoneCallView.prototype.showQualityPopup = function(params)
	{
		if(!BX.type.isPlainObject(params))
			params = {};

		if(!BX.type.isFunction(params.onSelect))
			params.onSelect = BX.DoNothing;

		var self = this;
		var elements = {
			'1': null,
			'2': null,
			'3': null,
			'4': null,
			'5': null
		};
		var createStar = function(grade)
		{
			return BX.create("div", {
				props: {className: 'im-phone-popup-rating-stars-item ' + (self.qualityGrade == grade ? 'im-phone-popup-rating-stars-item-active' : '')},
				dataset: {grade: grade},
				events: {
					click: function(e)
					{
						BX.PreventDefault(e);
						var grade = e.currentTarget.dataset.grade;
						params.onSelect(grade);
					}
				}
			});
		};

		this.qualityPopup = new BX.PopupWindow('PhoneCallViewQualityGrade', this.elements.qualityMeter, {
			targetContainer: document.body,
			darkMode: true,
			closeByEsc: true,
			autoHide: true,
			zIndex: baseZIndex + 200,
			noAllPaddings: true,
			overlay: {
				backgroundColor: 'white',
				opacity: 0
			},
			bindOptions: {
				position: 'top'
			},
			angle: {
				position: 'bottom',
				offset: 30
			},
			content: BX.create("div", {props: {className: 'im-phone-popup-rating'}, children: [
				BX.create("div", {props: {className: 'im-phone-popup-rating-title'}, text: BX.message('IM_PHONE_CALL_VIEW_RATE_QUALITY')}),
				BX.create("div", {props: {className: 'im-phone-popup-rating-stars'}, children: [
					elements['1'] = createStar(1),
					elements['2'] = createStar(2),
					elements['3'] = createStar(3),
					elements['4'] = createStar(4),
					elements['5'] = createStar(5)
				], events: {
					mouseover: function(){
						if(elements[self.qualityGrade])
							BX.removeClass(elements[self.qualityGrade], 'im-phone-popup-rating-stars-item-active');
					},
					mouseout: function(){
						if(elements[self.qualityGrade])
							BX.addClass(elements[self.qualityGrade], 'im-phone-popup-rating-stars-item-active');
					}
				}})
			]}),
			events: {
				onPopupClose: function()
				{
					this.destroy();
				},
				onPopupDestroy: function()
				{
					self.qualityPopup = null;
				}
			}
		});

		this.qualityPopup.show();
	};

	BX.PhoneCallView.prototype.closeQualityPopup = function()
	{
		if(this.qualityPopup)
			this.qualityPopup.close();
	};

	BX.PhoneCallView.prototype.saveComment = function()
	{
		BX.MessengerCommon.phoneCommand("saveComment", {
			'CALL_ID': this.callId,
			'COMMENT': this.comment
		})
	};

	BX.PhoneCallView.prototype.showNumberSelectMenu = function(params)
	{
		var self = this;
		var menuItems = [];
		if(!BX.type.isPlainObject(params))
			params = {};

		if(!BX.type.isArray(params.phoneNumbers))
			return;

		params.onSelect = BX.type.isFunction(params.onSelect) ? params.onSelect : BX.DoNothing;
		params.phoneNumbers.forEach(function(phoneNumber)
		{
			menuItems.push({
				id: 'number-select-' + BX.util.getRandomString(10),
				text: phoneNumber.VALUE,
				onclick: function()
				{
					params.onSelect({
						phoneNumber: phoneNumber.VALUE
					})
				}
			})
		});

		this.numberSelectMenu = BX.PopupMenu.create(
			'im-phone-call-view-number-select',
			params.bindElement,
			menuItems,
			{
				autoHide: true,
				offsetTop: 0,
				offsetLeft: 40,
				angle: {position: "top"},
				zIndex: baseZIndex + 200,
				closeByEsc: true,
				overlay: {
					backgroundColor: 'white',
					opacity: 0
				},
				events: {
					onPopupClose : function()
					{
						self.numberSelectMenu.popupWindow.destroy();
						BX.PopupMenu.destroy('im-phone-call-view-number-select');
					},
					onPopupDestroy: function ()
					{
						self.numberSelectMenu = null;
					}
				}
			}
		);
		this.numberSelectMenu.popupWindow.show();
	};

	BX.PhoneCallView.prototype.closeNumberSelectMenu = function()
	{
		if(this.numberSelectMenu)
			this.numberSelectMenu.popupWindow.close();
	};

	BX.PhoneCallView.prototype.fold = function()
	{
		if(!this.canBeFolded())
			return false;

		if(this.callListId > 0 && this.callState === BX.PhoneCallView.CallState.idle)
		{
			this.foldCallView();
		}
		else
		{
			this.foldCall();
		}
	};

	BX.PhoneCallView.prototype.unfold = function()
	{
		if(!this.isDesktop() && this.isFolded())
		{
			BX.cleanNode(this.elements.main, true);
			this.folded = false;
			this.elements = this.unfoldedElements;
			this.show();
		}
	};

	BX.PhoneCallView.prototype.foldCall = function()
	{
		if(this.isDesktop() || !this.popup)
			return;

		var self = this;
		var popupNode = this.popup.popupContainer;
		var overlayNode = this.popup.overlay.element;

		BX.addClass(popupNode, 'im-phone-call-view-folding');
		BX.addClass(overlayNode, 'popup-window-overlay-im-phone-call-view-folding');
		setTimeout(function()
		{
			self.folded = true;
			self.popup.close();
			self.unfoldedElements = self.elements;
			BX.removeClass(popupNode, 'im-phone-call-view-folding');
			BX.removeClass(overlayNode, 'popup-window-overlay-im-phone-call-view-folding');
			self.reinit();
			self.enableDocumentScroll();
		}, 300);
	};

	BX.PhoneCallView.prototype.foldCallView = function()
	{
		var self = this;
		var foldedCallView = BX.FoldedCallView.getInstance();
		var popupNode = this.popup.popupContainer;
		var overlayNode = this.popup.overlay.element;

		BX.addClass(popupNode, 'im-phone-call-view-folding');
		BX.addClass(overlayNode, 'popup-window-overlay-im-phone-call-view-folding');
		setTimeout(
			function()
			{
				self.close();
				foldedCallView.fold({
					callListId: self.callListId,
					webformId: self.webformId,
					webformSecCode: self.webformSecCode,
					currentItemIndex: self.callListView.currentItemIndex,
					currentItemStatusId: self.callListView.currentStatusId,
					statusList: self.callListView.statuses,
					entityType: self.callListView.entityType
				}, true);
			},
			300
		);
	};

	BX.PhoneCallView.prototype.bindSlaveDesktopEvents = function()
	{
		var self = this;
		BX.desktop.addCustomEvent(desktopEvents.setTitle, this.setTitle.bind(this));
		BX.desktop.addCustomEvent(desktopEvents.setStatus, this.setStatusText.bind(this));
		BX.desktop.addCustomEvent(desktopEvents.setUiState, this.setUiState.bind(this));
		BX.desktop.addCustomEvent(desktopEvents.setDeviceCall, this.setDeviceCall.bind(this));
		BX.desktop.addCustomEvent(desktopEvents.setCrmEntity, this.setCrmEntity.bind(this));
		BX.desktop.addCustomEvent(desktopEvents.reloadCrmCard, this.reloadCrmCard.bind(this));
		BX.desktop.addCustomEvent(desktopEvents.setPortalCall, this.setPortalCall.bind(this));
		BX.desktop.addCustomEvent(desktopEvents.setPortalCallUserId, this.setPortalCallUserId.bind(this));
		BX.desktop.addCustomEvent(desktopEvents.setPortalCallQueueName, this.setPortalCallQueueName.bind(this));
		BX.desktop.addCustomEvent(desktopEvents.setPortalCallData, this.setPortalCallData.bind(this));
		BX.desktop.addCustomEvent(desktopEvents.setConfig, this.setConfig.bind(this));
		BX.desktop.addCustomEvent(desktopEvents.setCallId, this.setCallId.bind(this));
		BX.desktop.addCustomEvent(desktopEvents.setLineNumber, this.setLineNumber.bind(this));
		BX.desktop.addCustomEvent(desktopEvents.setCompanyPhoneNumber, this.setCompanyPhoneNumber.bind(this));
		BX.desktop.addCustomEvent(desktopEvents.setPhoneNumber, this.setPhoneNumber.bind(this));
		BX.desktop.addCustomEvent(desktopEvents.setTransfer, this.setTransfer.bind(this));
		BX.desktop.addCustomEvent(desktopEvents.setCallState, this.setCallState.bind(this));
		BX.desktop.addCustomEvent(desktopEvents.closeWindow, function(){window.close()});

		BX.bind(window, "beforeunload", function ()
		{
			BX.unbindAll(window, "beforeunload");
			BX.desktop.onCustomEvent(desktopEvents.onBeforeUnload, []);
		});

		BX.bind(window, "resize", BX.debounce(function(e)
		{
			if(self.skipOnResize)
			{
				self.skipOnResize = false;
				return;
			}

			self.saveInitialSize(window.innerWidth, window.innerHeight)
		}, 100, this));

		BX.addCustomEvent("SidePanel.Slider:onOpen", function(event)
		{
			if (!event.getSlider().isSelfContained())
			{
				event.denyAction();
				window.open(event.slider.url);
			}
		});

		/*BX.bind(window, "keydown", function(e)
		{
			if(e.keyCode === 27)
			{
				BX.desktop.onCustomEvent(desktopEvents.onBeforeUnload, []);
			}
		}.bind(this));*/
	};

	BX.PhoneCallView.prototype.bindMasterDesktopEvents = function()
	{
		var self = this;
		BX.desktop.addCustomEvent(desktopEvents.onHold, function(){self.callbacks.hold()});
		BX.desktop.addCustomEvent(desktopEvents.onUnHold, function(){self.callbacks.unhold()});
		BX.desktop.addCustomEvent(desktopEvents.onMute, function(){self.callbacks.mute()});
		BX.desktop.addCustomEvent(desktopEvents.onUnMute, function(){self.callbacks.unmute()});
		BX.desktop.addCustomEvent(desktopEvents.onMakeCall, function(phoneNumber){self.callbacks.makeCall(phoneNumber)});
		BX.desktop.addCustomEvent(desktopEvents.onCallListMakeCall, function(e){self.callbacks.callListMakeCall(e)});
		BX.desktop.addCustomEvent(desktopEvents.onAnswer, function(){self.callbacks.answer()});
		BX.desktop.addCustomEvent(desktopEvents.onSkip, function(){self.callbacks.skip()});
		BX.desktop.addCustomEvent(desktopEvents.onHangup, function(){self.callbacks.hangup()});
		BX.desktop.addCustomEvent(desktopEvents.onClose, function(){self.close()});
		BX.desktop.addCustomEvent(desktopEvents.onStartTransfer, function(e){self.callbacks.transfer(e)});
		BX.desktop.addCustomEvent(desktopEvents.onCompleteTransfer, function(){self.callbacks.completeTransfer()});
		BX.desktop.addCustomEvent(desktopEvents.onCancelTransfer, function(){self.callbacks.cancelTransfer()});
		BX.desktop.addCustomEvent(desktopEvents.onSwitchDevice, function(e){self.callbacks.switchDevice(e)});
		BX.desktop.addCustomEvent(desktopEvents.onBeforeUnload, function(){
			self.desktop.window = null;
			self.callbacks.hangup();
			self.callbacks.close();
		}); //slave window unload
		BX.desktop.addCustomEvent(desktopEvents.onQualityGraded, function(grade){self.callbacks.qualityGraded(grade)});
		BX.desktop.addCustomEvent(desktopEvents.onDialpadButtonClicked, function(grade){self.callbacks.dialpadButtonClicked(grade)});
		BX.desktop.addCustomEvent(desktopEvents.onCommentShown, function(commentShown){self.commentShown = commentShown});
		BX.desktop.addCustomEvent(desktopEvents.onSaveComment, function(comment){self.comment = comment; self.saveComment();});
		BX.desktop.addCustomEvent(desktopEvents.onSetAutoClose, function(autoClose){self.autoClose = autoClose;});

	};

	BX.PhoneCallView.prototype.unbindDesktopEvents = function()
	{
		for(eventId in desktopEvents)
		{
			if(desktopEvents.hasOwnProperty(eventId))
			{
				BX.desktop.removeCustomEvents(desktopEvents[eventId]);
			}
		}
	};

	BX.PhoneCallView.prototype.isDesktop = function()
	{
		return BX.MessengerCommon.isDesktop();
	};

	BX.PhoneCallView.prototype.isFolded = function()
	{
		return this.folded;
	};

	BX.PhoneCallView.prototype.canBeFolded = function()
	{
		return this.allowAutoClose && (this.callState === BX.PhoneCallView.CallState.connected || (this.callState === BX.PhoneCallView.CallState.idle && this.callListId > 0));
	};

	BX.PhoneCallView.prototype.getFoldedHeight = function()
	{
		if(!this.folded)
			return 0;

		if(!this.elements.main)
			return 0;

		return this.elements.main.clientHeight + (this.elements.sections.status ? this.elements.sections.status.clientHeight : 0);
	};

	BX.PhoneCallView.prototype.isWebformSupported = function()
	{
		return (!this.isDesktop() || this.desktop.isFeatureSupported('iframe'));
	};

	BX.PhoneCallView.prototype.isRestAppsSupported = function()
	{
		return (!this.isDesktop() || this.desktop.isFeatureSupported('iframe'));
	};

	BX.PhoneCallView.prototype.setClosable = function(closable)
	{
		closable = (closable == true);
		this.closable = closable;
		if(this.isDesktop())
		{
			//this.desktop.setClosable(closable);
		}
		else if(this.popup)
		{
			this.popup.setClosingByEsc(closable);
			//this.popup.setAutoHide(closable);
		}
	};

	BX.PhoneCallView.prototype.isClosable = function()
	{
		return this.closable;
	};

	BX.PhoneCallView.prototype.adjust = function()
	{
		if(this.popup)
		{
			this.popup.adjustPosition();
		}

		if(this.isDesktop() && this.slave)
		{
			if(this.currentLayout == layouts.simple)
			{
				this.desktop.setResizable(false);
			}
			else
			{
				this.desktop.setResizable(true);
				this.desktop.setMinSize((this.elements.sidebarContainer ? 900 : 550), 650);
			}
			this.desktop.center();
		}
	};

	BX.PhoneCallView.prototype.resizeWindow = function(width, height)
	{
		if(!this.isDesktop() || !this.slave)
			return false;

		this.skipOnResize = true;
		this.desktop.resize(width, height);
	};

	BX.PhoneCallView.prototype.close = function()
	{
		BX.onCustomEvent(window, 'CallCard::BeforeClose', []);

		if(this.isFolded() && this.elements.main)
		{
			BX.addClass(this.elements.main, 'im-phone-call-panel-mini-closing');
			setTimeout(function()
				{
					BX.cleanNode(this.elements.main, true);
					this.elements = this.getInitialElements();
				}.bind(this),
				300
			);
		}

		if (this.popup)
			this.popup.close();

		if(this.desktop.window)
		{
			BX.desktop.onCustomEvent(desktopEvents.closeWindow, []);
			//this.desktop.window.ExecuteCommand('close');
			//this.desktop.window = null;
		}
		BackgroundWorker.CallCard = null;

		this.enableDocumentScroll();

		this.callbacks.close();
		BX.onCustomEvent(window, 'CallCard::AfterClose', []);
	};

	BX.PhoneCallView.prototype.disableAutoClose = function()
	{
		this.allowAutoClose = false;
		if(this.isDesktop() && this.slave)
		{
			BX.desktop.onCustomEvent(desktopEvents.onSetAutoClose, [this.allowAutoClose]);
		}
		this.renderButtons();
	};

	BX.PhoneCallView.prototype.enableAutoClose = function()
	{
		this.allowAutoClose = true;
		if(this.isDesktop() && this.slave)
		{
			BX.desktop.onCustomEvent(desktopEvents.onSetAutoClose, [this.allowAutoClose]);
		}
		this.renderButtons();
	};

	BX.PhoneCallView.prototype.autoClose = function()
	{
		if(this.allowAutoClose && !this.commentShown)
		{
			this.close();
		}
		else
		{
			BX.onCustomEvent(window, 'CallCard::BeforeClose', []);
		}
	};

	BX.PhoneCallView.prototype.disableDocumentScroll = function()
	{
		var scrollWidth = window.innerWidth - document.documentElement.clientWidth;
		document.body.style.setProperty('padding-right', scrollWidth + "px");
		document.body.classList.add('im-phone-call-disable-scroll');
		var imBar = BX('bx-im-bar');
		if(imBar)
		{
			imBar.style.setProperty('right', scrollWidth + "px");
		}
	};

	BX.PhoneCallView.prototype.enableDocumentScroll = function()
	{
		document.body.classList.remove('im-phone-call-disable-scroll');
		document.body.style.removeProperty('padding-right');
		var imBar = BX('bx-im-bar');
		if(imBar)
		{
			imBar.style.removeProperty('right');
		}
	};

	BX.PhoneCallView.prototype.dispose = function()
	{
		window.removeEventListener('beforeunload', this._onBeforeUnloadHandler);
		BX.removeCustomEvent("onPullEvent-crm", this._onPullEventCrmHandler);
		this.unloadRestApps();
		this.unloadForm();

		if(this.isFolded() && this.elements.main)
		{
			BX.addClass(this.elements.main, 'im-phone-call-panel-mini-closing');
			setTimeout(function()
				{
					BX.cleanNode(this.elements.main, true);
				}.bind(this),
				300
			);
		}

		if(this.popup)
		{
			this.popup.destroy();
			this.popup = null;
		}

		if(this.qualityPopup)
			this.qualityPopup.close();

		if(this.transferPopup)
			this.transferPopup.close();

		if(this.keypad)
			this.keypad.close();

		if(this.numberSelectMenu)
			this.closeNumberSelectMenu();

		if(this.isDesktop())
		{
			this.unbindDesktopEvents();
			if(this.desktop.window)
			{
				BX.desktop.onCustomEvent(desktopEvents.closeWindow, []);
				//this.desktop.window.ExecuteCommand('close');
				this.desktop.window = null;
			}
			if(!this.slave)
			{
				window.removeEventListener('beforeunload', this._unloadHandler); //master window unload
			}
		}
		else
		{
			window.removeEventListener('beforeunload', this._onBeforeUnloadHandler);
		}
	};

	BX.PhoneCallView.prototype.canBeUnloaded = function()
	{
		if (BackgroundWorker.isUsed)
		{
			return false;
		}
		return this.allowAutoClose && this.isFolded();
	};

	BX.PhoneCallView.prototype.isCallListMode = function()
	{
		return (this.callListId > 0);
	};

	BX.PhoneCallView.prototype.getState = function()
	{
		return {
			callId:  this.callId,
			folded: this.folded,
			uiState: this._uiState,
			phoneNumber:  this.phoneNumber,
			companyPhoneNumber:  this.companyPhoneNumber,
			direction:  this.direction,
			fromUserId:  this.fromUserId,
			toUserId:  this.toUserId,
			statusText: this.statusText,
			crm: this.crm,
			hasSipPhone: this.hasSipPhone,
			deviceCall: this.deviceCall,
			transfer: this.transfer,
			callback: this.callback,
			crmEntityType:  this.crmEntityType,
			crmEntityId:  this.crmEntityId,
			crmActivityId:  this.crmActivityId,
			crmActivityEditUrl:  this.crmActivityEditUrl,
			callListId: this.callListId,
			callListStatusId:  this.callListStatusId,
			callListItemIndex: this.callListItemIndex,
			config: (this.config ? this.config : '{}'),
			portalCall: (this.portalCall ? 'true' : 'false'),
			portalCallData: (this.portalCallData ? this.portalCallData : '{}'),
			portalCallUserId: this.portalCallUserId,
			webformId: this.webformId,
			webformSecCode:  this.webformSecCode,
			initialTimestamp: this.initialTimestamp,
			crmData: this.crmData
		};
	};

	BX.PhoneCallView.prototype.selectTransferTarget = function(resultCallback)
	{
		resultCallback = BX.type.isFunction(resultCallback) ? resultCallback : BX.DoNothing;

		BX.loadExt('ui.entity-selector').then(function()
		{
			var config =
				BackgroundWorker.isUsed
					? this.getDialogConfigForBackgroundApp(resultCallback)
					: this.getDefaultDialogConfig(resultCallback)
			;
			var transferDialog = new BX.UI.EntitySelector.Dialog(config);

			transferDialog.show();
		}.bind(this));
	};

	BX.PhoneCallView.prototype.getDialogConfigForBackgroundApp = function(resultCallback)
	{
		return {
			targetNode: this.elements.buttons.transfer,
			multiple: false,
			cacheable: false,
			hideOnSelect: false,
			enableSearch: true,
			entities: [
				{
					id: 'user',
					options: {
						inviteEmployeeLink: false,
						selectFields: ['personalPhone', 'personalMobile', 'workPhone']
					}
				},
				{
					id: 'department'
				},
			],
			events: {
				'Item:onSelect': function(event)
				{
					event.target.deselectAll();

					var item = event.data.item;

					if (item.getEntityId() === 'user')
					{
						var customData = item.getCustomData();
						if (customData.get('personalPhone') || customData.get('personalMobile') || customData.get('workPhone'))
						{
							this.showTransferToUserMenu({
								userId: item.getId(),
								customData: Object.fromEntries(customData),
								onSelect: function(result)
								{
									event.target.hide();
									resultCallback({
										phoneNumber: this.phoneNumber,
										target: result.target
									})
								}
							})
						}
						else
						{
							event.target.hide();
							resultCallback({
								phoneNumber: this.phoneNumber,
								target: item.getId()
							})
						}
					}
				}.bind(this)
			}
		}
	}

	BX.PhoneCallView.prototype.getDefaultDialogConfig = function(resultCallback)
	{
		return {
			targetNode: this.elements.buttons.transfer,
			multiple: false,
			cacheable: false,
			hideOnSelect: false,
			enableSearch: true,
			entities: [
				{
					id: 'user',
					options: {
						inviteEmployeeLink: false,
						selectFields: ['personalPhone', 'personalMobile', 'workPhone']
					}
				},
				{
					id: 'department'
				},
				{
					id: 'voximplant_group'
				},
			],
			events: {
				'Item:onSelect': function(event)
				{
					event.target.deselectAll();

					var item = event.data.item;

					if (item.getEntityId() === 'user')
					{
						var customData = item.getCustomData();
						if (customData.get('personalPhone') || customData.get('personalMobile') || customData.get('workPhone'))
						{
							this.showTransferToUserMenu({
								userId: item.getId(),
								customData: Object.fromEntries(customData),
								onSelect: function(result)
								{
									event.target.hide();
									resultCallback({
										type: result.type,
										target: result.target
									})
								}
							})
						}
						else
						{
							event.target.hide();
							resultCallback({
								type: 'user',
								target: item.getId()
							})
						}
					}
					else if (item.getEntityId() === 'voximplant_group')
					{
						event.target.hide();
						resultCallback({
							type: 'queue',
							target: item.getId()
						})
					}
				}.bind(this)
			}
		};
	}

	BX.PhoneCallView.prototype.showTransferToUserMenu = function(options)
	{
		var userId = BX.prop.getInteger(options, "userId", 0);
		var userCustomData = BX.prop.getObject(options, "customData", {});
		var onSelect = BX.prop.getFunction(options, "onSelect", BX.DoNothing);
		var popup;

		var onMenuItemClick = function (e)
		{
			var type = e.currentTarget.dataset["type"];
			var target = e.currentTarget.dataset["target"];
			onSelect({
				type: type,
				target: target,
			});
			popup.close();
		};

		var menuItems = [
			{
				icon: 'bx-messenger-menu-call-voice',
				text: BX.message('IM_PHONE_INNER_CALL'),
				dataset: {
					type: 'user',
					target: userId
				},
				onclick: onMenuItemClick
			},
			{
				separator: true
			},
		];

		if (userCustomData["personalMobile"])
		{
			menuItems.push({
				type: "call",
				text: BX.message("IM_PHONE_PERSONAL_MOBILE"),
				phone: BX.util.htmlspecialchars(userCustomData["personalMobile"]),
				dataset: {
					type: 'pstn',
					target: userCustomData["personalMobile"]
				},
				onclick: onMenuItemClick,
			});
		}
		if (userCustomData["personalPhone"])
		{
			menuItems.push({
				type: "call",
				text: BX.message("IM_PHONE_PERSONAL_PHONE"),
				phone: BX.util.htmlspecialchars(userCustomData["personalPhone"]),
				dataset: {
					type: 'pstn',
					target: userCustomData["personalPhone"]
				},
				onclick: onMenuItemClick,
			});
		}
		if (userCustomData["workPhone"])
		{
			menuItems.push({
				type: "call",
				text: BX.message("IM_PHONE_WORK_PHONE"),
				phone: BX.util.htmlspecialchars(userCustomData["workPhone"]),
				dataset: {
					type: 'pstn',
					target: userCustomData["workPhone"]
				},
				onclick: onMenuItemClick,
			});
		}
		var popupContent = BX.create("div", {
			props: {className: "bx-messenger-popup-menu"},
			children: [
				BX.create("div", {
					props: {className: "bx-messenger-popup-menu-items"},
					children: BX.MessengerChat.MenuPrepareList(menuItems),
				}),
			],
		});

		popup = new BX.PopupWindow("bx-messenger-phone-transfer-menu", null, {
			targetContainer: document.body,
			darkMode: this.BXIM.settings.enableDarkTheme,
			lightShadow: true,
			autoHide: true,
			closeByEsc: true,
			cacheable: false,
			overlay: {
				backgroundColor: '#FFFFFF',
				opacity: 0
			},
			content: popupContent,
		});
		popup.show();

	};

	BX.PhoneCallView.initializeBackgroundAppPlacement = function(params)
	{
		BackgroundWorker.initializePlacement(params);
	}


	BX.PhoneCallView.Direction = {
		incoming: 'incoming',
		outgoing: 'outgoing',
		callback: 'callback'
	};

	BX.PhoneCallView.UiState = {
		incoming: 1,
		transferIncoming: 2,
		outgoing: 3,
		connectingIncoming: 4,
		connectingOutgoing: 5,
		connected: 6,
		transferring: 7,
		transferFailed: 8,
		transferConnected: 9,
		idle: 10,
		error: 11,
		moneyError: 12,
		sipPhoneError: 13,
		redial: 14,
		externalCard: 15
	};

	BX.PhoneCallView.CallState = {
		idle: 'idle',
		connecting: 'connecting',
		connected: 'connected'
	};

	var desktopFeatureMap = {
		'iframe': 39
	};

	var Desktop = function(params)
	{
		this.BXIM = params.BXIM;
		this.parentPhoneCallView = params.parentPhoneCallView;
		this.closable = params.closable;
		this.title = params.title || '';
		this.window = null;
	};

	Desktop.prototype.openCallWindow = function(content, js, params)
	{
		if (!BX.MessengerCommon.isDesktop())
			return false;
		params = params || {};

		if(params.minSettingsWidth)
			this.minSettingsWidth = params.minSettingsWidth;

		if(params.minSettingsHeight)
			this.minSettingsHeight = params.minSettingsHeight;

		params.resizable = (params.resizable == true);

		BX.desktop.createWindow("callWindow", BX.delegate(function(callWindow) {
			callWindow.SetProperty("clientSize", {Width: params.width, Height: params.height});
			callWindow.SetProperty("resizable", params.resizable);
			if(params.resizable && params.hasOwnProperty('minWidth') && params.hasOwnProperty('minHeight'))
			{
				callWindow.SetProperty("minClientSize", {Width: params.minWidth, Height: params.minHeight});
			}
			callWindow.SetProperty("title", this.title);
			callWindow.SetProperty("closable", true);
			//callWindow.OpenDeveloperTools();
			callWindow.ExecuteCommand("html.load", this.getHtmlPage(content, js, {}));
			this.window = callWindow;
		}, this));
	};

	Desktop.prototype.setClosable = function(closable)
	{
		this.closable = (closable == true);
		if(this.window)
		{
			this.window.SetProperty("closable", this.closable);
		}
	};

	Desktop.prototype.setTitle = function(title)
	{
		this.title = title;
		if(this.window)
			this.window.SetProperty("title", title)
	};

	Desktop.prototype.getHtmlPage = function(content, jsContent, initImJs, bodyClass)
	{
		if (!BX.MessengerCommon.isDesktop()) return;

		content = content || '';
		jsContent = jsContent || '';
		bodyClass = bodyClass || '';

		var initImConfig = typeof(initImJs) == "undefined" || typeof(initImJs) != "object"? {}: initImJs;
		initImJs = typeof(initImJs) != "undefined";
		if (this.htmlWrapperHead == null)
			this.htmlWrapperHead = document.head.outerHTML.replace(/BX\.PULL\.start\([^)]*\);/g, '');

		if (content != '' && BX.type.isDomNode(content))
			content = content.outerHTML;

		if (jsContent != '' && BX.type.isDomNode(jsContent))
			jsContent = jsContent.outerHTML;

		if (jsContent != '')
			jsContent = '<script type="text/javascript">BX.ready(function(){'+jsContent+'});</script>';

		var initJs = '';
		if (initImJs == true)
		{
			initJs = "<script type=\"text/javascript\">"+
				"BX.ready(function() {"+
					"BXIM = new BX.IM(null, {"+
						"'init': false,"+
						"'colors' : "+(this.BXIM.colors? JSON.stringify(this.BXIM.colors): "false")+","+
						"'settings' : "+JSON.stringify(this.BXIM.settings)+","+
						"'settingsView' : "+JSON.stringify(this.BXIM.settingsView)+","+
						"'updateStateInterval': '"+this.BXIM.updateStateInterval+"',"+
						"'desktop': "+BX.MessengerCommon.isPage()+","+
						"'desktopVersion': "+this.BXIM.desktopVersion+","+
						"'ppStatus': false,"+
						"'ppServerStatus': false,"+
						"'xmppStatus': "+this.BXIM.xmppStatus+","+
						"'bitrixNetwork': "+this.BXIM.bitrixNetwork+","+
						"'bitrixOpenLines': "+this.BXIM.bitrixOpenLines+","+
						"'bitrix24': "+this.BXIM.bitrix24+","+
						"'bitrixIntranet': "+this.BXIM.bitrixIntranet+","+
						"'bitrixXmpp': "+this.BXIM.bitrixXmpp+","+
						"'bitrixMobile': "+this.BXIM.bitrixMobile+","+
						"'files' : "+(initImConfig.files? JSON.stringify(initImConfig.files): '{}')+","+
						"'notify' : "+(initImConfig.notify? JSON.stringify(initImConfig.notify): '{}')+","+
						"'users' : "+(initImConfig.users? JSON.stringify(initImConfig.users): '{}')+","+
						"'chat' : "+(initImConfig.chat? JSON.stringify(initImConfig.chat): '{}')+","+
						"'userChat' : "+(initImConfig.userChat? JSON.stringify(initImConfig.userChat): '{}')+","+
						"'userInChat' : "+(initImConfig.userInChat? JSON.stringify(initImConfig.userInChat): '{}')+","+
						"'hrphoto' : "+(initImConfig.hrphoto? JSON.stringify(initImConfig.hrphoto): '{}')+","+
						"'phoneCrm' : "+(initImConfig.phoneCrm? JSON.stringify(initImConfig.phoneCrm): '{}')+","+
						"'generalChatId': "+this.BXIM.messenger.generalChatId+","+
						"'canSendMessageGeneralChat': "+this.BXIM.messenger.canSendMessageGeneralChat+","+
						"'userId': "+this.BXIM.userId+","+
						"'userEmail': '"+this.BXIM.userEmail+"',"+
						"'userColor': '"+this.BXIM.userColor+"',"+
						"'userGender': '"+this.BXIM.userGender+"',"+
						"'userExtranet': "+this.BXIM.userExtranet+","+
						"'disk': {'enable': "+(this.disk? this.disk.enable: false)+"},"+
						"'path' : "+JSON.stringify(this.BXIM.path)+
					"});"+
					"BXIM.messenger.contactListLoad = false;" +
					"BX.PhoneCallView.setDefaults(" + JSON.stringify(defaults) + ");" +
					"PCW = new BX.PhoneCallView({" +
						"'slave': true, "+
						"'skipOnResize': true, "+
						"'callId': '" + this.parentPhoneCallView.callId + "'," +
						"'uiState': " + this.parentPhoneCallView._uiState + "," +
						"'phoneNumber': '" + this.parentPhoneCallView.phoneNumber + "'," +
						"'companyPhoneNumber': '" + this.parentPhoneCallView.companyPhoneNumber + "'," +
						"'direction': '" + this.parentPhoneCallView.direction + "'," +
						"'fromUserId': '" + this.parentPhoneCallView.fromUserId + "'," +
						"'toUserId': '" + this.parentPhoneCallView.toUserId + "'," +
						"'crm': " + this.parentPhoneCallView.crm + "," +
						"'hasSipPhone': " + this.parentPhoneCallView.hasSipPhone + "," +
						"'deviceCall': " + this.parentPhoneCallView.deviceCall + "," +
						"'transfer': " + this.parentPhoneCallView.transfer + "," +
						"'callback': " + this.parentPhoneCallView.callback + "," +
						"'crmEntityType': '" + this.parentPhoneCallView.crmEntityType + "'," +
						"'crmEntityId': '" + this.parentPhoneCallView.crmEntityId + "'," +
						"'crmActivityId': '" + this.parentPhoneCallView.crmActivityId + "'," +
						"'crmActivityEditUrl': '" + this.parentPhoneCallView.crmActivityEditUrl + "'," +
						"'callListId': " + this.parentPhoneCallView.callListId + "," +
						"'callListStatusId': '" + this.parentPhoneCallView.callListStatusId + "'," +
						"'callListItemIndex': " + this.parentPhoneCallView.callListItemIndex + "," +
						"'config': " + (this.parentPhoneCallView.config ? JSON.stringify(this.parentPhoneCallView.config): '{}') + "," +
						"'portalCall': " + (this.parentPhoneCallView.portalCall ? 'true' : 'false') + "," +
						"'portalCallData': " + (this.parentPhoneCallView.portalCallData ? JSON.stringify(this.parentPhoneCallView.portalCallData) : '{}') + "," +
						"'portalCallUserId': " + this.parentPhoneCallView.portalCallUserId + "," +
						"'webformId': " + this.parentPhoneCallView.webformId + "," +
						"'webformSecCode': '" + this.parentPhoneCallView.webformSecCode + "'" +
					"});"+
				"});"+
				"</script>";
		}
		return '<!DOCTYPE html><html>'+this.htmlWrapperHead+'<body class="im-desktop im-desktop-popup '+bodyClass+'"><div id="placeholder-messanger">'+content+'</div>'+initJs+jsContent+'</body></html>';
	};

	Desktop.prototype.addCustomEvent = function(eventName, eventHandler)
	{
		if (!BX.MessengerCommon.isDesktop())
			return false;

		BX.desktop.addCustomEvent(eventName, eventHandler);
	};

	Desktop.prototype.onCustomEvent = function(windowTarget, eventName, arEventParams)
	{
		if (!BX.MessengerCommon.isDesktop())
			return false;

		BX.desktop.onCustomEvent(windowTarget, eventName, arEventParams);
	};

	Desktop.prototype.resize = function(width, height)
	{
		BXDesktopWindow.SetProperty("clientSize", {Width: width, Height: height});
	};

	Desktop.prototype.setResizable = function(resizable)
	{
		resizable = (resizable == true);
		BXDesktopWindow.SetProperty("resizable", resizable);
	};

	Desktop.prototype.setMinSize = function(width, height)
	{
		BXDesktopWindow.SetProperty("minClientSize", {Width: width, Height: height});
	};

	Desktop.prototype.setWindowPosition = function (params)
	{
		BXDesktopWindow.SetProperty("position", params);
	};

	Desktop.prototype.center = function ()
	{
		BXDesktopWindow.ExecuteCommand("center");
	};

	Desktop.prototype.getVersion = function(full)
	{
		if (typeof(BXDesktopSystem) == 'undefined')
			return 0;

		if (!this.clientVersion)
			this.clientVersion = BXDesktopSystem.GetProperty('versionParts');

		return full? this.clientVersion.join('.'): this.clientVersion[3];
	};

	Desktop.prototype.isFeatureSupported = function(featureName)
	{
		if(!desktopFeatureMap.hasOwnProperty(featureName))
			return false;

		return this.getVersion() >= desktopFeatureMap[featureName];
	};

	var CallList = function(params)
	{
		this.node = params.node;
		this.id = params.id;

		this.entityType = '';
		this.statuses = new Map(); // {STATUS_ID (string): { STATUS_NAME; string, CLASS: string, ITEMS: []}
		this.elements = {};
		this.currentStatusId = params.callListStatusId || 'IN_WORK';
		this.currentItemIndex = params.itemIndex || 0;
		this.callingStatusId = null;
		this.callingItemIndex = null;
		this.selectionLocked = false;

		this.itemActionMenu = null;
		this.callbacks = {
			onError: BX.type.isFunction(params.onError) ? params.onError : nop,
			onSelectedItem: BX.type.isFunction(params.onSelectedItem) ? params.onSelectedItem : nop
		};

		this.showLimit = 10;
		this.showDelta = 10;
	};

	CallList.getAjaxUrl = function()
	{
		return (BX.MessengerCommon.isDesktop() ? '/desktop_app/call_list.ajax.php' : '/bitrix/components/bitrix/crm.activity.call_list/ajax.php')
	};


	CallList.prototype.init = function(next)
	{
		if(!BX.type.isFunction(next))
			next = BX.DoNothing;

		var self = this;
		this.load(function()
		{
			const currentStatus = self.statuses.get(self.currentStatusId)
			if(currentStatus && currentStatus.ITEMS.length  > 0)
			{
				self.render();
				self.selectItem(self.currentStatusId, self.currentItemIndex);
				next();
			}
			else
			{
				BX.debug('empty call list. don\'t know what to do');
			}
		})
	};

	/**
	 * @param {object} params
	 * @param {Node} params.node DOM node to render call list.
	 */
	CallList.prototype.reinit = function(params)
	{
		if(BX.type.isDomNode(params.node))
			this.node = params.node;

		this.render();
		this.selectItem(this.currentStatusId, this.currentItemIndex);
		if(this.callingStatusId !== null && this.callingItemIndex !== null)
			this.setCallingElement(this.callingStatusId, this.callingItemIndex);

	};

	CallList.prototype.load = function(next)
	{
		var self = this;
		var params = {
			'sessid': BX.bitrix_sessid(),
			'ajax_action': 'GET_CALL_LIST',
			'callListId': this.id
		};

		BX.ajax({
			url: CallList.getAjaxUrl(),
			method: 'POST',
			dataType: 'json',
			data: params,
			onsuccess: function(data)
			{
				if(!data.ERROR)
				{
					if(BX.type.isArray(data.STATUSES))
					{
						//self.statuses = data.STATUSES;
						data.STATUSES.forEach(function(statusRecord)
						{
							statusRecord.ITEMS = [];
							self.statuses.set(statusRecord.STATUS_ID, statusRecord);
						});

						data.ITEMS.forEach(function(item)
						{
							let itemStatus = self.statuses.get(item.STATUS_ID);
							if (itemStatus)
							{
								itemStatus.ITEMS.push(item);
							}
						});
					}
					self.entityType = data.ENTITY_TYPE;
					let currentStatus = self.statuses.get(self.currentStatusId);
					if(currentStatus && currentStatus.ITEMS.length == 0)
					{
						self.currentStatusId = self.getNonEmptyStatusId();
						self.currentItemIndex = 0;
					}
					next();
				}
				else
				{
					console.log(data);
				}
			}
		});
	};

	CallList.prototype.selectItem = function(statusId, newIndex)
	{
		var currentNode = this.statuses.get(this.currentStatusId).ITEMS[this.currentItemIndex]._node;
		BX.removeClass(currentNode, 'im-phone-call-list-customer-block-active');

		if(this.itemActionMenu)
			this.itemActionMenu.close();

		this.currentStatusId = statusId;
		this.currentItemIndex = newIndex;

		currentNode = this.statuses.get(this.currentStatusId).ITEMS[this.currentItemIndex]._node;
		BX.addClass(currentNode, 'im-phone-call-list-customer-block-active');

		var newEntity = this.statuses.get(statusId).ITEMS[newIndex];

		if((this.entityType == 'DEAL' || this.entityType == 'QUOTE' || this.entityType == 'INVOICE') && newEntity.ASSOCIATED_ENTITY)
		{
			this.callbacks.onSelectedItem({
				type: newEntity.ASSOCIATED_ENTITY.TYPE,
				id: newEntity.ASSOCIATED_ENTITY.ID,
				bindings: [
					{
						type: this.entityType,
						id: newEntity.ELEMENT_ID
					}
				],
				phones: newEntity.ASSOCIATED_ENTITY.PHONES,
				statusId: statusId,
				index: newIndex
			});
		}
		else
		{
			this.callbacks.onSelectedItem({
				type: this.entityType,
				id: newEntity.ELEMENT_ID,
				phones: newEntity.PHONES,
				statusId: statusId,
				index: newIndex
			});
		}
	};

	CallList.prototype.moveToNextItem = function()
	{
		var newIndex = this.currentItemIndex+1;
		if(newIndex>= this.statuses.get(this.currentStatusId).ITEMS.length)
			newIndex = 0;

		this.selectItem(this.currentStatusId, newIndex);
	};

	CallList.prototype.setCallingElement = function(statusId, index)
	{
		this.callingStatusId = statusId;
		this.callingItemIndex = index;
		currentNode = this.statuses.get(this.callingStatusId).ITEMS[this.callingItemIndex]._node;
		BX.addClass(currentNode, 'im-phone-call-list-customer-block-calling');
		this.selectionLocked = true;
	};

	CallList.prototype.resetCallingElement = function()
	{
		if(this.callingStatusId === null || this.callingItemIndex === null)
			return;

		currentNode = this.statuses.get(this.callingStatusId).ITEMS[this.callingItemIndex]._node;
		BX.removeClass(currentNode, 'im-phone-call-list-customer-block-calling');
		this.callingStatusId = null;
		this.callingItemIndex = null;
		this.selectionLocked = false;
	};

	CallList.prototype.render = function()
	{
		BX.cleanNode(this.node); // BX.create("div", {props: {className: ''}})
		var layout = BX.create("div", {props: {className: 'im-phone-call-list-container'}, children: this.renderStatusBlocks()});
		this.node.appendChild(layout);
	};

	CallList.prototype.renderStatusBlocks = function()
	{
		var result = [];

		for(let [statusId, status] of this.statuses)
		{
			if (!status || status.ITEMS.length === 0)
				continue;

			status._node = this.renderStatusBlock(status);
			result.push(status._node);
		}
		return result;
	};

	CallList.prototype.renderStatusBlock = function(status)
	{
		var animationTimeout;
		var itemsNode;
		var measuringNode;
		var statusId = status.STATUS_ID;

		if(!status.hasOwnProperty('_folded'))
			status._folded = false;

		className = 'im-phone-call-list-block';

		if(status.CLASS != '')
			className = className + ' ' + status.CLASS;

		return BX.create("div", {props: {className: className}, children: [
			BX.create("div", {
				props: {className: 'im-phone-call-list-block-title' + (status._folded ? '' : ' active')},
				children: [
					BX.create("span", {text: this.getStatusTitle(statusId)}),
					BX.create("div", {props: {className: 'im-phone-call-list-block-title-arrow'}})
				],
				events: {
					click: function(e)
					{
						clearTimeout(animationTimeout);
						status._folded = !status._folded;
						if (status._folded)
						{
							BX.removeClass(e.target, 'active');
							itemsNode.style.height = measuringNode.clientHeight.toString() + 'px';
							animationTimeout = setTimeout(function ()
							{
								itemsNode.style.height = 0;
							}, 50);
						}
						else
						{
							BX.addClass(e.target, 'active');
							itemsNode.style.height = 0;
							animationTimeout = setTimeout(function ()
							{
								itemsNode.style.height = measuringNode.clientHeight + 'px';
							}, 50);
						}
						BX.PreventDefault(e);
					}
				}
			}),
			itemsNode = BX.create("div", {
				props: {className: 'im-phone-call-list-items-block'},
				children:[
					measuringNode = BX.create("div", {props: {className: 'im-phone-call-list-items-measuring'}, children: this.renderCallListItems(statusId)})
				],
				events: {
					'transitionend': function()
					{
						if(!status._folded)
						{
							itemsNode.style.removeProperty('height');
						}
					}
				}
			})
		]});
	};

	CallList.prototype.renderCallListItems = function(statusId)
	{
		var result = [];
		var status = this.statuses.get(statusId);

		if(status._shownCount > 0)
		{
			if (status._shownCount > status.ITEMS.length)
				status._shownCount = status.ITEMS.length;
		}
		else
		{
			status._shownCount = Math.min(this.showLimit, status.ITEMS.length);
		}

		for(var i = 0; i < status._shownCount; i++)
		{
			result.push(this.renderCallListItem(status.ITEMS[i], statusId, i));
		}

		if(status.ITEMS.length > status._shownCount)
		{
			status._showMoreNode = BX.create("div", {props: {className: 'im-phone-call-list-show-more-wrap'}, children: [
				BX.create("span", {
					props: {className: 'im-phone-call-list-show-more-button'},
					dataset: {statusId: statusId},
					text: BX.message('IM_PHONE_CALL_LIST_MORE').replace('#COUNT#', (status.ITEMS.length - status._shownCount)),
					events: {click: this.onShowMoreClick.bind(this)}
				})
			]});
			result.push(status._showMoreNode);
		}
		else
		{
			status._showMoreNode = null;
		}

		return result;
	};

	CallList.prototype.renderCallListItem = function(itemDescriptor, statusId, itemIndex)
	{
		var statusName = this.statuses.get(statusId).NAME;
		var self = this;

		var phonesText = '';
		if(BX.type.isArray(itemDescriptor.PHONES))
		{
			itemDescriptor.PHONES.forEach(function(phone, index)
			{
				if(index != 0)
				{
					phonesText += '; ';
				}

				phonesText += BX.util.htmlspecialchars(phone.VALUE);
			})
		}

		itemDescriptor._node = BX.create("div", {
			props: {className: (this.currentStatusId == statusId && this.currentItemIndex == itemIndex ? 'im-phone-call-list-customer-block im-phone-call-list-customer-block-active' : 'im-phone-call-list-customer-block')},
			children: [
				BX.create("div", {props: {className: 'im-phone-call-list-customer-block-action'}, children: [BX.create("span", {text: statusName})], events: {
					click: function(e)
					{
						if(self.itemActionMenu)
							self.itemActionMenu.popupWindow.close();
						else
							self.showItemMenu(itemDescriptor, e.target);

						BX.PreventDefault(e);
					}
				}}),
				BX.create("div", {props: {className: 'im-phone-call-list-item-customer-name' + (itemDescriptor.ASSOCIATED_ENTITY ? ' im-phone-call-list-connection-line' : '')}, children: [
					BX.create("a", {attrs: {href: itemDescriptor.EDIT_URL, target: '_blank'}, props: {className: 'im-phone-call-list-item-customer-link'}, text: itemDescriptor.NAME, events: {
						click: function(e)
						{
							window.open(itemDescriptor.EDIT_URL);
							BX.PreventDefault(e);
						}
					}})
				]}),

				(itemDescriptor.POST ? BX.create("div", {props: {className: 'im-phone-call-list-item-customer-info'}, text: itemDescriptor.POST}) : null),
				(itemDescriptor.COMPANY_TITLE ? BX.create("div", {props: {className: 'im-phone-call-list-item-customer-info'}, text: itemDescriptor.COMPANY_TITLE}) : null),
				(phonesText ? BX.create("div", {props: {className: 'im-phone-call-list-item-customer-info'}, text: phonesText}) : null),
				(itemDescriptor.ASSOCIATED_ENTITY ? this.renderAssociatedEntity(itemDescriptor.ASSOCIATED_ENTITY) : null)
			],
			events: {
				click: function()
				{
					if(!self.selectionLocked && (self.currentStatusId != itemDescriptor.STATUS_ID || self.currentItemIndex != itemIndex))
					{
						self.selectItem(itemDescriptor.STATUS_ID, itemIndex);
					}
				}
			}
		});

	 	return itemDescriptor._node;
	};

	CallList.prototype.renderAssociatedEntity = function(associatedEntity)
	{
		var phonesText = '';
		if(BX.type.isArray(associatedEntity.PHONES))
		{
			associatedEntity.PHONES.forEach(function(phone, index)
			{
				if(index != 0)
				{
					phonesText += '; ';
				}

				phonesText += BX.util.htmlspecialchars(phone.VALUE);
			})
		}

		return BX.create("div", {props: {className: 'im-phone-call-list-item-customer-entity im-phone-call-list-connection-line-item'}, children: [
			BX.create("a", {attrs: {href: associatedEntity.EDIT_URL, target: '_blank'}, props: {className: 'im-phone-call-list-item-customer-link'}, text: associatedEntity.NAME, events: {
				click: function(e)
				{
					window.open(associatedEntity.EDIT_URL);
					BX.PreventDefault(e);
				}
			}}),
			BX.create("div", {props: {className: 'im-phone-call-list-item-customer-info'}, text: associatedEntity.POST}),
			BX.create("div", {props: {className: 'im-phone-call-list-item-customer-info'}, text: associatedEntity.COMPANY_TITLE}),
			(phonesText ? BX.create("div", {props: {className: 'im-phone-call-list-item-customer-info'}, text: phonesText}) : null)
		]});
	};

	CallList.prototype.onShowMoreClick = function(e)
	{
		var statusId = e.target.dataset.statusId;
		var status = this.statuses.get(statusId);

		status._shownCount += this.showDelta;
		if(status._shownCount > status.ITEMS.length)
			status._shownCount = status.ITEMS.length;

		var newStatusNode = this.renderStatusBlock(status);
		status._node.parentNode.replaceChild(newStatusNode, status._node);
		status._node = newStatusNode;
	};

	CallList.prototype.showItemMenu = function(callListItem, node)
	{
		var self = this;
		var menuItems = [];
		var menuItem;
		for(let [statusId, status] of this.statuses)
		{
			menuItem = {
				id: "setStatus_" + statusId,
				text: status.NAME,
				onclick: this.actionMenuItemClickHandler(callListItem.ELEMENT_ID, statusId).bind(this)
			};
			menuItems.push(menuItem);
		}
		menuItems.push({
			id: 'callListItemActionMenu_delimiter',
			delimiter: true

		});
		menuItems.push({
			id: "defer15min",
			text: BX.message('IM_PHONE_CALL_VIEW_CALL_LIST_DEFER_15_MIN'),
			onclick: function()
			{
				self.itemActionMenu.popupWindow.close();
				self.setElementRank(callListItem.ELEMENT_ID, callListItem.RANK + 35);
			}
		});
		menuItems.push({
			id: "defer1hour",
			text: BX.message('IM_PHONE_CALL_VIEW_CALL_LIST_DEFER_HOUR'),
			onclick: function()
			{
				self.itemActionMenu.popupWindow.close();
				self.setElementRank(callListItem.ELEMENT_ID, callListItem.RANK + 185);
			}
		});
		menuItems.push({
			id: "moveToEnd",
			text: BX.message('IM_PHONE_CALL_VIEW_CALL_LIST_TO_END'),
			onclick: function()
			{
				self.itemActionMenu.popupWindow.close();
				self.setElementRank(callListItem.ELEMENT_ID, callListItem.RANK + 5100);
			}
		});

		this.itemActionMenu = BX.PopupMenu.create(
			'callListItemActionMenu',
			node,
			menuItems,
			{
				autoHide: true,
				offsetTop: 0,
				offsetLeft: 0,
				angle: {position: "top"},
				zIndex: baseZIndex + 200,
				events: {
					onPopupClose : function()
					{
						self.itemActionMenu.popupWindow.destroy();
						BX.PopupMenu.destroy('callListItemActionMenu');
					},
					onPopupDestroy: function ()
					{
						self.itemActionMenu = null;
					}
				}
			}
		);
		this.itemActionMenu.popupWindow.show();
	};

	CallList.prototype.actionMenuItemClickHandler = function(elementId, statusId)
	{
		var self = this;
		return function()
		{
			self.itemActionMenu.popupWindow.close();
			self.setElementStatus(elementId, statusId);
		}
	};

	CallList.prototype.setElementRank = function(elementId, rank)
	{
		var self = this;
		this.executeItemAction({
			action: 'SET_ELEMENT_RANK',
			parameters: {
				callListId: this.id,
				elementId: elementId,
				rank: rank
			},
			successCallback: function(data)
			{
				if(data.ITEMS)
				{
					self.repopulateItems(data.ITEMS);
					self.render();
				}
			}
		});
	};

	CallList.prototype.setElementStatus = function(elementId, statusId)
	{
		var self = this;
		this.executeItemAction({
			action: 'SET_ELEMENT_STATUS',
			parameters: {
				callListId: this.id,
				elementId: elementId,
				statusId: statusId
			},
			successCallback: function(data)
			{
				self.repopulateItems(data.ITEMS);
				self.render();
			}
		})
	};

	/**
	 * @param {int} elementId
	 * @param {int} webformResultId
	 */
	CallList.prototype.setWebformResult = function(elementId, webformResultId)
	{
		this.executeItemAction({
			action: 'SET_WEBFORM_RESULT',
			parameters: {
				callListId: this.id,
				elementId: elementId,
				webformResultId: webformResultId
			}
		})
	};

	CallList.prototype.executeItemAction = function (params)
	{
		var self = this;

		if(!BX.type.isPlainObject(params))
			params = {};

		if(!BX.type.isFunction(params.successCallback))
			params.successCallback = BX.DoNothing;

		var requestParams = {
			'sessid': BX.bitrix_sessid(),
			'ajax_action': params.action,
			'parameters': params.parameters
		};

		BX.ajax({
			url: CallList.getAjaxUrl(),
			method: 'POST',
			dataType: 'json',
			data: requestParams,
			onsuccess: function(data)
			{
				params.successCallback(data);
			}
		});
	};

	CallList.prototype.repopulateItems = function(items)
	{
		var self = this;
		for (let [statusId, status] of this.statuses)
		{
			status.ITEMS = [];
		}

		items.forEach(function(item)
		{
			self.statuses.get(item.STATUS_ID).ITEMS.push(item);
		});

		if(this.statuses.get(this.currentStatusId).ITEMS.length === 0)
		{
			this.currentStatusId = this.getNonEmptyStatusId();
			this.currentItemIndex = 0;
		}
		else
		{
			if(this.currentItemIndex >= this.statuses.get(this.currentStatusId).ITEMS.length)
				this.currentItemIndex = 0;
		}

		this.selectItem(this.currentStatusId, this.currentItemIndex);
	};

	CallList.prototype.getNonEmptyStatusId = function()
	{
		var foundStatusId = false;

		for(let [statusId, status] of this.statuses)
		{
			if(status.ITEMS.length > 0)
			{
				foundStatusId = statusId;
				break;
			}
		}
		return foundStatusId;
	};

	CallList.prototype.getCurrentElement = function()
	{
		return this.statuses.get(this.currentStatusId).ITEMS[this.currentItemIndex];
	};

	CallList.prototype.getStatusTitle = function(statusId)
	{
		var count = this.statuses.get(statusId).ITEMS.length;

		return BX.util.htmlspecialchars(this.statuses.get(statusId).NAME) + ' (' +  count.toString() + ')';
	};

	var foldedCallListInstance = null;
	var avatars = {};

	BX.FoldedCallView = function(params)
	{
		this.currentItem = {};
		this.callListParams = {
			id: 0,
			webformId: 0,
			webformSecCode: '',
			itemIndex: 0,
			itemStatusId: '',
			statusList: {},
			entityType: ''
		};
		this.node = null;
		this.elements = {
			avatar: null,
			callButton: null,
			nextButton: null,
			unfoldButton: null
		};
		this._lsKey = 'bx-im-folded-call-view-data';
		this._lsTtl = 86400;
		this.init();
	};

	BX.FoldedCallView.getInstance = function()
	{
		if(foldedCallListInstance == null)
			foldedCallListInstance = new BX.FoldedCallView();

		return foldedCallListInstance;
	};

	BX.FoldedCallView.prototype.init = function()
	{
		this.load();
		if(this.callListParams.id > 0)
		{
			this.currentItem = this.callListParams.statusList[this.callListParams.itemStatusId].ITEMS[this.callListParams.itemIndex];
			this.render();
		}
	};

	BX.FoldedCallView.prototype.load = function()
	{
		var savedData = BX.localStorage.get(this._lsKey);
		if(BX.type.isPlainObject(savedData))
		{
			this.callListParams = savedData;
		}
	};

	BX.FoldedCallView.prototype.destroy = function()
	{
		if(this.node)
		{
			BX.cleanNode(this.node, true);
			this.node = null;
		}

		BX.localStorage.remove(this._lsKey);
	};

	BX.FoldedCallView.prototype.store = function()
	{
		BX.localStorage.set(this._lsKey, this.callListParams, this._lsTtl);
	};

	BX.FoldedCallView.prototype.fold = function(params, animation)
	{
		animation = (animation == true);
		this.callListParams.id = params.callListId;
		this.callListParams.webformId = params.webformId;
		this.callListParams.webformSecCode = params.webformSecCode;
		this.callListParams.itemIndex = params.currentItemIndex;
		this.callListParams.itemStatusId = params.currentItemStatusId;
		this.callListParams.statusList = params.statusList;
		this.callListParams.entityType = params.entityType;
		this.currentItem = this.callListParams.statusList[this.callListParams.itemStatusId].ITEMS[this.callListParams.itemIndex];
		this.store();
		this.render(animation);
	};

	BX.FoldedCallView.prototype.unfold = function(makeCall)
	{
		var self = this;
		BX.addClass(this.node, "im-phone-folded-call-view-unfold");
		this.node.addEventListener('animationend', function() {
			if(self.node)
			{
				BX.cleanNode(self.node, true);
				self.node = null;
			}

			BX.localStorage.remove(self._lsKey);
			if(!window.BXIM || self.callListParams.id == 0)
				return false;

			var restoredParams = {};
			if(self.callListParams.webformId > 0 && self.callListParams.webformSecCode != '')
			{
				restoredParams.webformId = self.callListParams.webformId;
				restoredParams.webformSecCode = self.callListParams.webformSecCode;
			}
			restoredParams.callListStatusId =self.callListParams.itemStatusId;
			restoredParams.callListItemIndex = self.callListParams.itemIndex;
			restoredParams.makeCall = makeCall;

			window.BXIM.startCallList(self.callListParams.id, restoredParams);
		});
	};

	BX.FoldedCallView.prototype.moveToNext = function()
	{
		this.callListParams.itemIndex++;
		if(this.callListParams.itemIndex >= this.callListParams.statusList[this.callListParams.itemStatusId].ITEMS.length)
			this.callListParams.itemIndex = 0;

		this.currentItem = this.callListParams.statusList[this.callListParams.itemStatusId].ITEMS[this.callListParams.itemIndex];
		this.store();
		this.render();
	};

	BX.FoldedCallView.prototype.render = function(animation)
	{
		var self = this;
		animation = (animation == true);
		if(this.node == null)
		{
			this.node = BX.create("div", {
				props: {id: 'im-phone-folded-call-view', className: 'im-phone-call-wrapper im-phone-call-wrapper-fixed im-phone-call-panel'},
				events: {
					dblclick: this._onViewDblClick.bind(this)
				}
			});
			document.body.appendChild(this.node);
		}
		else
		{
			BX.cleanNode(this.node);
		}

		this.node.appendChild(BX.create("div", {props: {className: 'im-phone-call-wrapper-fixed-left'}, style: (animation? {bottom: '-90px'} : {}), children: [
			BX.create("div", {props: {className: 'im-phone-call-wrapper-fixed-user'}, children: [
				BX.create("div", {props: {className: 'im-phone-call-wrapper-fixed-user-image'}, children: [
					this.elements.avatar = BX.create("div", {props: {className: 'im-phone-call-wrapper-fixed-user-image-item'}})
				]}),
				BX.create("div", {props: {className: 'im-phone-call-wrapper-fixed-user-info'}, children: this.renderUserInfo()})
			]})
		]}));

		this.node.appendChild(BX.create("div", {props: {className: 'im-phone-call-wrapper-fixed-right'}, children: [
			BX.create("div", {props: {className: 'im-phone-call-wrapper-fixed-btn-container'}, children: [
				this.elements.callButton = BX.create("span", {
					props: {className: 'im-phone-call-btn im-phone-call-btn-green'},
					text: BX.message('IM_PHONE_CALL_VIEW_FOLDED_BUTTON_CALL'),
					events: {
						click: this._onDialButtonClick.bind(this)
					}
				}),
				this.elements.nextButton = BX.create("span", {
					props: {className: 'im-phone-call-btn im-phone-call-btn-gray im-phone-call-btn-arrow'},
					text: BX.message('IM_PHONE_CALL_VIEW_FOLDED_BUTTON_NEXT'),
					events: {
						click: this._onNextButtonClick.bind(this)
					}
				})
			]})
		]}));

		this.node.appendChild(BX.create("div", {props: {className: 'im-phone-btn-block'}, children: [
				this.elements.unfoldButton = BX.create("div", {
					props: {className: 'im-phone-btn-arrow'},
					children: [
						BX.create("div", {props: {className: 'im-phone-btn-arrow-inner'}, text: BX.message("IM_PHONE_CALL_VIEW_UNFOLD")})
					],
					events: {
						click: this._onUnfoldButtonClick.bind(this)
					}
				})
			]})
		);

		if(avatars[this.currentItem.ELEMENT_ID])
		{
			this.elements.avatar.style.backgroundImage = 'url(\'' + BX.util.htmlspecialchars(avatars[this.currentItem.ELEMENT_ID]) + '\')';
		}
		else
		{
			this.loadAvatar(this.callListParams.entityType, this.currentItem.ELEMENT_ID);
		}

		if(animation)
		{
			BX.addClass(this.node, 'im-phone-folded-call-view-fold');
			this.node.addEventListener('animationend', function()
			{
				BX.removeClass(self.node, 'im-phone-folded-call-view-fold');
			})
		}
	};

	BX.FoldedCallView.prototype.renderUserInfo = function()
	{
		var result = [];

		result.push(BX.create("div", {props: {className: 'im-phone-call-wrapper-fixed-user-name'}, text: this.currentItem.NAME}));
		if(this.currentItem.POST)
			result.push(BX.create("div", {props: {className: 'im-phone-call-wrapper-fixed-user-item'}, text: this.currentItem.POST}));
		if(this.currentItem.COMPANY_TITLE)
			result.push(BX.create("div", {props: {className: 'im-phone-call-wrapper-fixed-user-item'}, text: this.currentItem.COMPANY_TITLE}));

		return result;
	};

	BX.FoldedCallView.prototype.loadAvatar = function(entityType, entityId)
	{
		var self = this;
		BX.ajax({
			url: CallList.getAjaxUrl(),
			method: 'POST',
			dataType: 'json',
			data: {
				'sessid': BX.bitrix_sessid(),
				'ajax_action': 'GET_AVATAR',
				'entityType': entityType,
				'entityId': entityId
			},
			onsuccess: function(data)
			{
				if(!data.avatar)
					return;

				avatars[entityId] = data.avatar;
				if(self.currentItem.ELEMENT_ID == entityId && self.elements.avatar)
				{
					self.elements.avatar.style.backgroundImage = 'url(\'' + BX.util.htmlspecialchars(data.avatar) + '\')';
				}
			}
		});
	};

	BX.FoldedCallView.prototype._onViewDblClick = function(e)
	{
		BX.PreventDefault(e);
		this.unfold(false);
	};

	BX.FoldedCallView.prototype._onDialButtonClick = function(e)
	{
		BX.PreventDefault(e);
		this.unfold(true);
	};

	BX.FoldedCallView.prototype._onNextButtonClick = function(e)
	{
		BX.PreventDefault(e);
		this.moveToNext();
	};

	BX.FoldedCallView.prototype._onUnfoldButtonClick = function(e)
	{
		BX.PreventDefault(e);
		this.unfold(false);
	};

	var Keypad = function(params)
	{
		if(!BX.type.isPlainObject(params))
			params = {};

		this.bindElement = params.bindElement || null;
		this.offsetTop = params.offsetTop || 0;
		this.offsetLeft = params.offsetLeft || 0;
		this.anglePosition = params.anglePosition || '';
		this.angleOffset = params.angleOffset || 0;
		this.history = params.history || [];

		this.selectedLineId = params.defaultLineId;
		this.lines = params.lines || {};
		this.availableLines = params.availableLines || [];

		this.zIndex = baseZIndex + 200;

		//flags
		this.hideDial = (params.hideDial === true);
		this.plusEntered = false;

		this.callbacks = {
			onButtonClick: BX.type.isFunction(params.onButtonClick) ? params.onButtonClick : BX.DoNothing,
			onDial: BX.type.isFunction(params.onDial) ? params.onDial : BX.DoNothing,
			onClose: BX.type.isFunction(params.onClose) ? params.onClose : BX.DoNothing
		};

		this.elements = {
			inputContainer: null,
			input: null,
			lineSelector: null,
			lineName: null,
			interceptButton: null,
			historyButton: null
		};
		this.plusKeyTimeout = null;

		this.lineSelectMenu = null;
		this.historySelectMenu = null;
		this.interceptErrorPopup = null;
		this.popup = this.createPopup();
	};

	Keypad.prototype.createPopup = function()
	{
		var self = this;
		var popupOptions = {
			targetContainer: document.body,
			darkMode: true,
			closeByEsc: true,
			autoHide: true,
			zIndex: this.zIndex,
			content: this.render(),
			noAllPaddings: true,

			offsetTop: this.offsetTop,
			offsetLeft: this.offsetLeft,

			overlay: {
				backgroundColor: 'white',
				opacity: 0
			},
			events: {
				onPopupClose: function()
				{
					self.callbacks.onClose();
					if (self.popup)
					{
						self.popup.destroy();
					}
				}
			}
		};

		if(this.anglePosition !== '')
		{
			popupOptions.angle = {
				position : this.anglePosition,
				offset: this.angleOffset
			};
		}

		return new BX.PopupWindow('phone-call-view-popup-keypad', this.bindElement, popupOptions);
	};

	Keypad.prototype.canSelectLine = function()
	{
		return this.availableLines.length > 1;
	};

	Keypad.prototype.setSelectedLineId = function(lineId)
	{
		this.selectedLineId = lineId;

		if(this.elements.lineName)
		{
			this.elements.lineName.innerText = this.getLineName(lineId)
		}
	};

	Keypad.prototype.getLineName = function(lineId)
	{
		return this.lines.hasOwnProperty(lineId) ? this.lines[lineId].SHORT_NAME : '';
	};

	Keypad.prototype.render = function()
	{
		var self = this;
		var createNumber = function(number)
		{
			var classSuffix;
			if(number == '*')
				classSuffix = '10';
			else if (number == '#')
				classSuffix = '11';
			else
				classSuffix = number;
			return BX.create("span", {
				dataset: {'digit': number},
				props: {className : "bx-messenger-calc-btn bx-messenger-calc-btn-" + classSuffix},
				children:[
					BX.create("span", {props: {className: 'bx-messenger-calc-btn-num'}})
				],
				events: {
					mousedown: self._onKeyButtonMouseDown.bind(self),
					mouseup: self._onKeyButtonMouseUp.bind(self)
				}
			});
		};

		return BX.create("div", {
			props: {className : "bx-messenger-calc-wrap" + (BX.MessengerCommon.isPage() ? ' bx-messenger-calc-wrap-desktop': '') },
			events: {click: this._onBodyClick.bind(this)},
			children: [
				BX.create("div", { props : { className : "bx-messenger-calc-body" }, children: [
					this.elements.inputContainer = BX.create("div", { props: {className: 'bx-messenger-calc-panel'}, children: [
						BX.create("span", {props: {className: "bx-messenger-calc-panel-delete"}, events: {
							click: this._onDeleteButtonClick.bind(this)
						}}),
						this.elements.input = BX.create("input", {
							attrs: {'readonly': this.hideDial, type: "text", value: '', placeholder: BX.message(this.hideDial ? 'IM_PHONE_PUT_DIGIT' : 'IM_PHONE_PUT_NUMBER')},
							props: { className : "bx-messenger-calc-panel-input" },
							events: {
								keydown: this._onInputKeydown.bind(this),
								keyup: function()
								{
									self._onAfterNumberChanged();
								}
							}
						})
					]}),
					BX.create("div", {
						props: {className : "bx-messenger-calc-btns-block"},
						children: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '*', '0', '#'].map(createNumber)
					})
				]}),
				this.hideDial ? null :  BX.create("div", {props : {className: "bx-messenger-call-btn-wrap" }, children: [
					this.elements.lineSelector = !this.canSelectLine() ? null : BX.create("div", {
						props: {className: "im-phone-select-line"},
						children: [
							this.elements.lineName = BX.create("span", {props: {className: "im-phone-select-line-name"}, text: this.getLineName(this.selectedLineId)}),
							BX.create("span", {props: {className: "im-phone-select-line-select"}})
						],
						events: {
							click: this._onLineSelectClick.bind(this)
						}
					}),
					BX.create("span", {
						props: {className: "bx-messenger-call-btn-separate"},
						children: [
							BX.create("span", {
								props: {className: "bx-messenger-call-btn"},
								children: [
									BX.create("span", {props: {className: "bx-messenger-call-btn-text"}, html: BX.message('IM_PHONE_CALL')})
								],
								events: {
									click: this._onDialButtonClick.bind(this)
								}
							}),
							this.elements.historyButton = BX.create("span", {props: {className: "bx-messenger-call-btn-arrow"}, events: {click: this._onShowHistoryButtonClick.bind(this)}})
						]
					}),
					this.elements.interceptButton = BX.create("span", {
						props: {className: "im-phone-intercept-button" + (defaults.callInterceptAllowed ? "" : " im-phone-intercept-button-locked")},
						text: BX.message("IM_PHONE_CALL_VIEW_INTERCEPT"),
						events: {click: this._onInterceptButtonClick.bind(this)}
					})
				]})
			]
		});
	};

	Keypad.prototype._onBodyClick = function(e)
	{
		if(this.interceptErrorPopup)
			this.interceptErrorPopup.close();
	};

	Keypad.prototype._onInputKeydown = function(e)
	{
		if (e.keyCode == 13)
		{
			this.callbacks.onDial({
				phoneNumber: this.elements.input.value,
				lineId: this.selectedLineId
			});
		}
		else if (e.keyCode == 37 || e.keyCode == 39 || e.keyCode == 8 || e.keyCode == 107 || e.keyCode == 46 || e.keyCode == 35 || e.keyCode == 36) // left, right, backspace, num plus, home, end
		{}
		else if (e.key === '+' || e.key === '#' || e.key === '*') // +
		{}
		else if ((e.keyCode == 67 || e.keyCode == 86 || e.keyCode == 65 || e.keyCode == 88) && (e.metaKey || e.ctrlKey)) // ctrl+v/c/a/x
		{}
		else if (e.keyCode >= 48 && e.keyCode <= 57 && !e.shiftKey) // 0-9
		{
			insertAtCursor(this.elements.input, e.key);

			e.preventDefault();
			this.callbacks.onButtonClick({
				key: e.key
			});
		}
		else if (e.keyCode >= 96 && e.keyCode <= 105 && !e.shiftKey) // extra 0-9
		{
			insertAtCursor(this.elements.input, e.key);

			e.preventDefault();
			this.callbacks.onButtonClick({
				key: e.key
			});
		}
		else
		{
			return BX.PreventDefault(e);
		}
	};

	Keypad.prototype._onAfterNumberChanged = function()
	{
		if (this.elements.input.value.length > 0)
			BX.addClass(this.elements.inputContainer, 'bx-messenger-calc-panel-active');
		else
			BX.removeClass(this.elements.inputContainer, 'bx-messenger-calc-panel-active');

		this.elements.input.focus();
	};

	Keypad.prototype._onDeleteButtonClick = function()
	{
		this.elements.input.value = this.elements.input.value.substr(0, this.elements.input.value.length-1);
		this._onAfterNumberChanged();
	};

	Keypad.prototype._onDialButtonClick = function()
	{
		this.callbacks.onDial({
			phoneNumber: this.elements.input.value,
			lineId: this.selectedLineId
		});
	};

	Keypad.prototype._onInterceptButtonClick = function()
	{
		if(!defaults.callInterceptAllowed)
		{
			this.close();
			if ('UI' in BX && 'InfoHelper' in BX.UI)
			{
				BX.UI.InfoHelper.show('limit_contact_center_telephony_intercept');
			}
			return;
		}

		BX.MessengerCommon.phoneCommand('interceptCall', {}, true, function(response)
		{
			if(!response.FOUND || response.FOUND == 'Y')
			{
				this.close();
			}
			else
			{
				if(response.ERROR)
				{
					this.interceptErrorPopup = new BX.PopupWindow('intercept-call-error', this.elements.interceptButton, {
						targetContainer: document.body,
						content: BX.util.htmlspecialchars(response.ERROR),
						autoHide: true,
						closeByEsc: true,
						bindOptions: {
							position: 'bottom'
						},
						angle: {
							offset: 40
						},
						zIndex: this.zIndex + 100,
						events: {
							onPopupClose: function(e)
							{
								this.interceptErrorPopup.destroy();
							}.bind(this),
							onPopupDestroy: function(e)
							{
								this.interceptErrorPopup = null;
							}.bind(this)
						}
					});
					this.interceptErrorPopup.show();
				}
			}
		}.bind(this));
	};

	Keypad.prototype._onKeyButtonMouseDown = function(e)
	{
		BX.PreventDefault(e);
		var key = e.currentTarget.dataset.digit.toString();
		var self = this;
		if (key == 0)
		{
			self.plusEntered = false;
			this.plusKeyTimeout = setTimeout(function()
			{
				if (!self.elements.input.value.startsWith('+'))
				{
					self.plusEntered = true;
					self.elements.input.value = '+' + self.elements.input.value;
				}
			}, 500);
		}
	};

	Keypad.prototype._onKeyButtonMouseUp = function(e)
	{
		BX.PreventDefault(e);
		var key = e.currentTarget.dataset.digit.toString();
		if (key == 0)
		{
			clearTimeout(this.plusKeyTimeout);
			if (!this.plusEntered)
			{
				insertAtCursor(this.elements.input, '0');
			}

			this.plusEntered = false;
		}
		else
		{
			insertAtCursor(this.elements.input, key);
		}
		this._onAfterNumberChanged();
		this.callbacks.onButtonClick({
			key: key
		});
	};

	Keypad.prototype._onShowHistoryButtonClick = function()
	{
		var self = this;
		var menuItems = [];

		if(!BX.type.isArray(this.history) || this.history.length === 0)
			return;

		this.history.forEach(function(phoneNumber, index)
		{
			menuItems.push({
				id: "history_" + index,
				text: BX.util.htmlspecialchars(phoneNumber),
				onclick: function()
				{
					self.historySelectMenu.close();
					self.callbacks.onDial({
						phoneNumber: phoneNumber,
						lineId: self.selectedLineId
					});
				}
			})
		});

		this.historySelectMenu = BX.PopupMenu.create(
			'phoneCallViewDialHistory',
			this.elements.historyButton,
			menuItems,
			{
				autoHide: true,
				offsetTop: 0,
				offsetLeft: 0,
				zIndex: baseZIndex + 300,
				bindOptions: {
					position: 'top'
				},
				angle: {
					offset: 33
				},
				closeByEsc: true,
				overlay: {
					backgroundColor: 'white',
					opacity: 0
				},
				events: {
					onPopupClose : function()
					{
						self.historySelectMenu.popupWindow.destroy();
						BX.PopupMenu.destroy('phoneCallViewDialHistory');
					},
					onPopupDestroy: function ()
					{
						self.historySelectMenu = null;
					}
				}
			}
		);
		this.historySelectMenu.popupWindow.show();
	};

	Keypad.prototype._onLineSelectClick = function(e)
	{
		var self = this;
		var menuItems = [];
		this.availableLines.forEach(function(lineId)
		{
			menuItems.push({
				id: "selectLine_" + lineId,
				text: BX.util.htmlspecialchars(self.getLineName(lineId)),
				onclick: function()
				{
					self.lineSelectMenu.close();
					self.setSelectedLineId(lineId);
				}
			})
		});

		this.lineSelectMenu = BX.PopupMenu.create(
			'phoneCallViewSelectLine',
			this.elements.lineSelector,
			menuItems,
			{
				autoHide: true,
				zIndex: this.zIndex + 100,
				closeByEsc: true,
				bindOptions: {
					position: 'top'
				},
				offsetLeft: 35,
				angle: {
					offset: 33
				},
				overlay: {
					backgroundColor: 'white',
					opacity: 0
				},
				maxHeight: 600,
				events: {
					onPopupClose : function()
					{
						self.lineSelectMenu.popupWindow.destroy();
						BX.PopupMenu.destroy('phoneCallViewSelectLine');
					},
					onPopupDestroy: function ()
					{
						self.lineSelectMenu = null;
					}
				}
			}
		);
		this.lineSelectMenu.popupWindow.show();
	};

	Keypad.prototype.show = function()
	{
		if (this.popup)
		{
			this.popup.show();
			this.elements.input.focus();
		}
	};

	Keypad.prototype.close = function()
	{
		if(this.lineSelectMenu)
			this.lineSelectMenu.destroy();

		if(this.historySelectMenu)
			this.historySelectMenu.destroy();

		if(this.interceptErrorPopup)
			this.interceptErrorPopup.close();

		if(this.popup)
			this.popup.close();
	};

	Keypad.prototype.destroy = function()
	{
		if(this.lineSelectMenu)
			this.lineSelectMenu.destroy();

		if(this.historySelectMenu)
			this.historySelectMenu.destroy();

		if(this.interceptErrorPopup)
			this.interceptErrorPopup.destroy();

		if(this.popup)
			this.popup.destroy();

		this.popup = null;
	};

	BX.PhoneKeypad = Keypad;

	var FormManager = function(params)
	{
		this.node = params.node;
		this.currentForm = null;
		this.callbacks = {
			onFormLoad: BX.type.isFunction(params.onFormLoad) ? params.onFormLoad : BX.DoNothing,
			onFormUnLoad: BX.type.isFunction(params.onFormUnLoad) ? params.onFormUnLoad : BX.DoNothing,
			onFormSend: BX.type.isFunction(params.onFormSend) ? params.onFormSend : BX.DoNothing
		}
	};

	/**
 	 * @param {object} params
	 * @param {int} params.id
	 * @param {string} params.secCode
	 */
	FormManager.prototype.load = function(params)
	{
		var formData = this.getFormData(params);
		window.Bitrix24FormLoader.load(formData);
		this.currentForm = formData;
	};

	FormManager.prototype.unload = function()
	{
		if(this.currentForm)
		{
			window.Bitrix24FormLoader.unload(this.currentForm);
			this.currentForm = null;
		}
	};

	/**
	 * @param {object} params
	 * @param {int} params.id
	 * @param {string} params.secCode
	 * @returns {object}
	 */
	FormManager.prototype.getFormData = function (params)
	{
		return {
			id: params.id,
			sec: params.secCode,
			type: 'inline',
			lang: 'ru',
			ref: window.location.href,
			node: this.node,
			handlers:
			{
				'load': this._onFormLoad.bind(this),
				'unload': this._onFormUnLoad.bind(this),
				'send': this.onFormSend.bind(this)
			},
			options:
			{
				'borders': false,
				'logo': false
			}
		}
	};

	FormManager.prototype._onFormLoad = function(form)
	{
		this.callbacks.onFormLoad(form);
	};

	FormManager.prototype._onFormUnLoad = function(form)
	{
		this.callbacks.onFormUnLoad(form);
	};

	FormManager.prototype.onFormSend = function(form)
	{
		this.callbacks.onFormSend(form);
	};
	
	const BackgroundWorker = {
		/** @type {PhoneCallView} */
		CallCard: null,

		isExternalCall: false,

		/** @type {boolean} */
		isUsed: false,
		UndefinedCallCard: {
			result: 'error',
			errorCode: 'Call card is undefined'
		},

		isActiveIntoCurrentCall: function ()
		{
			return this.isExternalCall && this.isUsed
		},

		initializePlacement: function()
		{
			var placement = BX.rest.AppLayout.initializePlacement('PAGE_BACKGROUND_WORKER');
			this.initializeInterface(placement);
		},

		initializeInterface: function(placement)
		{

			if (this.isDesktop())
			{
				this.initializeDesktopInterfaceMethods(placement);
				this.bindDesktopEvents();
				this.addDesktopEventHandlers();
			}
			else
			{
				this.initializeBrowserInterfaceMethods(placement);
			}

			this.initializeInterfaceEvents(placement)
		},

		initializeBrowserInterfaceMethods: function(placement)
		{
			placement.prototype.CallCardSetMute = (params, callback) => this.setMute(params, callback);
			placement.prototype.CallCardSetHold = (params, callback) => this.setHold(params, callback);
			placement.prototype.CallCardSetUiState = (params, callback) => this.setUiState(params, callback);
			placement.prototype.CallCardGetListUiStates = (params, callback) => this.getListUiStates(params, callback);
			placement.prototype.CallCardSetCardTitle = (params, callback) => this.setCardTitle(params, callback);
			placement.prototype.CallCardSetStatusText = (params, callback) => this.setStatusText(params, callback);
			placement.prototype.CallCardClose = (params, callback) => this.close(params, callback);
			placement.prototype.CallCardStartTimer = (params, callback) => this.startTimer(params, callback);
			placement.prototype.CallCardStopTimer = (params, callback) => this.stopTimer(params, callback);
		},

		initializeDesktopInterfaceMethods: function(placement)
		{
			placement.prototype.CallCardSetMute = (params, callback) => this.desktop.setMute(params, callback);
			placement.prototype.CallCardSetHold = (params, callback) =>  this.desktop.setHold(params, callback);
			placement.prototype.CallCardSetUiState = (params, callback) => this.desktop.setUiState(params, callback);
			placement.prototype.CallCardGetListUiStates = (params, callback) => this.desktop.getListUiStates(params, callback);
			placement.prototype.CallCardSetCardTitle = (params, callback) => this.desktop.setCardTitle(params, callback);
			placement.prototype.CallCardSetStatusText = (params, callback) => this.desktop.setStatusText(params, callback);
			placement.prototype.CallCardClose = (params, callback) => this.desktop.close(params, callback);
			placement.prototype.CallCardStartTimer = (params, callback) => this.desktop.startTimer(params, callback);
			placement.prototype.CallCardStopTimer = (params, callback) => this.desktop.stopTimer(params, callback);
		},

		initializeInterfaceEvents: function(placement)
		{
			placement.prototype.events.push('BackgroundCallCard::initialized');
			placement.prototype.events.push('BackgroundCallCard::addCommentButtonClick');
			placement.prototype.events.push('BackgroundCallCard::muteButtonClick');
			placement.prototype.events.push('BackgroundCallCard::holdButtonClick');
			placement.prototype.events.push('BackgroundCallCard::closeButtonClick');
			placement.prototype.events.push('BackgroundCallCard::transferButtonClick');
			placement.prototype.events.push('BackgroundCallCard::cancelTransferButtonClick');
			placement.prototype.events.push('BackgroundCallCard::completeTransferButtonClick');
			placement.prototype.events.push('BackgroundCallCard::hangupButtonClick');
			placement.prototype.events.push('BackgroundCallCard::nextButtonClick');
			placement.prototype.events.push('BackgroundCallCard::skipButtonClick');
			placement.prototype.events.push('BackgroundCallCard::answerButtonClick');
			placement.prototype.events.push('BackgroundCallCard::entityChanged');
			placement.prototype.events.push('BackgroundCallCard::qualityMeterClick');
			placement.prototype.events.push('BackgroundCallCard::dialpadButtonClick');
			placement.prototype.events.push('BackgroundCallCard::makeCallButtonClick');
			placement.prototype.events.push('BackgroundCallCard::notifyAdminButtonClick');
		},

		bindDesktopEvents: function()
		{
			if (!this.desktop.isCorporatePortalPage())
			{
				return;
			}
			for (const event of this.events)
			{
				this.desktop.addCustomEvent('DesktopCallCard' + (event[0].toUpperCase() + event.slice(1)), function(params, callback) {

					BX.onCustomEvent(window, 'BackgroundCallCard::' + event, [params, callback]);
				})
			}

			this.desktop.addCustomEvent('DesktopCallCardInitialized', function(params, callback) {
				if(!BackgroundWorker.CallCard)
				{
					BackgroundWorker.CallCard = true;
				}
				BX.onCustomEvent(window, 'BackgroundCallCard::initialized', [params, callback]);
			});

			this.desktop.addCustomEvent('DesktopCallCardCloseButtonClick', function(params, callback) {
				BackgroundWorker.CallCard = false;
				BX.onCustomEvent(window, 'BackgroundCallCard::closeButtonClick', [params, callback]);
			});

		},

		addDesktopEventHandlers: function()
		{
			this.desktop.addCustomEvent('DesktopCallCardSetUiState',(params, callback) => {
				if (this.isDesktop() && this.desktop.isCorporatePortalPage())
				{
					return;
				}
				this.isUsed = true;
				callback = typeof (callback) === 'function' ? callback : BX.DoNothing;

				const callWindow = this.desktop.getCallCardWindow();
				callWindow.BX.PhoneCallView.BackgroundWorker.setUiState(params, callback);
			});
			this.desktop.addCustomEvent('DesktopCallCardSetMute', (params, callback) => {
				if (this.isDesktop() && this.desktop.isCorporatePortalPage())
				{
					return;
				}
				this.isUsed = true;
				callback = typeof (callback) === 'function' ? callback : BX.DoNothing;

				const callWindow = this.desktop.getCallCardWindow();
				callWindow.BX.PhoneCallView.BackgroundWorker.setMute(params, callback);
			});
			this.desktop.addCustomEvent('DesktopCallCardSetHold',(params, callback) => {
				if (this.isDesktop() && this.desktop.isCorporatePortalPage())
				{
					return;
				}
				this.isUsed = true;
				callback = typeof (callback) === 'function' ? callback : BX.DoNothing;

				const callWindow = this.desktop.getCallCardWindow();
				callWindow.BX.PhoneCallView.BackgroundWorker.setHold(params, callback);
			});
			this.desktop.addCustomEvent('DesktopCallCardGetListUiState',(params, callback) => {
				if (this.isDesktop() && this.desktop.isCorporatePortalPage())
				{
					return;
				}
				this.isUsed = true;
				callback = typeof (callback) === 'function' ? callback : BX.DoNothing;

				this.getListUiStates(params, callback);
			});
			this.desktop.addCustomEvent('DesktopCallCardSetCardTitle', (params, callback) => {
				if (this.isDesktop() && this.desktop.isCorporatePortalPage())
				{
					return;
				}
				this.isUsed = true;
				callback = typeof (callback) === 'function' ? callback : BX.DoNothing;

				const callWindow = this.desktop.getCallCardWindow();
				callWindow.BX.PhoneCallView.BackgroundWorker.setCardTitle(params, callback);
			});
			this.desktop.addCustomEvent('DesktopCallCardSetStatusText', (params, callback) => {
				if (this.isDesktop() && this.desktop.isCorporatePortalPage())
				{
					return;
				}
				this.isUsed = true;
				callback = typeof (callback) === 'function' ? callback : BX.DoNothing;

				const callWindow = this.desktop.getCallCardWindow();
				callWindow.BX.PhoneCallView.BackgroundWorker.setStatusText(params, callback);
			});
			this.desktop.addCustomEvent('DesktopCallCardClose', (params, callback) => {
				if (this.isDesktop() && this.desktop.isCorporatePortalPage())
				{
					return;
				}
				this.isUsed = true;
				callback = typeof (callback) === 'function' ? callback : BX.DoNothing;

				const callWindow = this.desktop.getCallCardWindow();
				this.close(params, callback);
			});
			this.desktop.addCustomEvent('DesktopCallCardStartTimer', (params, callback) => {
				if (this.isDesktop() && this.desktop.isCorporatePortalPage())
				{
					return;
				}
				this.isUsed = true;
				callback = typeof (callback) === 'function' ? callback : BX.DoNothing;

				const callWindow = this.desktop.getCallCardWindow();
				callWindow.BX.PhoneCallView.BackgroundWorker.startTimer(params, callback);
			});
			this.desktop.addCustomEvent('DesktopCallCardStopTimer', (params, callback) => {
				if (this.isDesktop() && this.desktop.isCorporatePortalPage())
				{
					return;
				}
				this.isUsed = true;
				callback = typeof (callback) === 'function' ? callback : BX.DoNothing;

				const callWindow = this.desktop.getCallCardWindow();
				callWindow.BX.PhoneCallView.BackgroundWorker.stopTimer(params, callback);
			});
		},

		events: [
			'addCommentButtonClick',
			'muteButtonClick',
			'holdButtonClick',
			'transferButtonClick',
			'cancelTransferButtonClick',
			'completeTransferButtonClick',
			'hangupButtonClick',
			'nextButtonClick',
			'skipButtonClick',
			'answerButtonClick',
			'entityChanged',
			'qualityMeterClick',
			'dialpadButtonClick',
			'makeCallButtonClick',
			'notifyAdminButtonClick',
		],

		desktop: {
			eventHandlers: [],

			setUiState: function(params, callback)
			{
				if(!BackgroundWorker.CallCard)
				{
					callback(BackgroundWorker.UndefinedCallCard);

					return;
				}
				if (!params.hasOwnProperty('uiState') || !BX.PhoneCallView.UiState[params.uiState])
				{
					callback([{
						result: 'error',
						errorCode: 'Invalid ui state'
					}]);

					return;
				}
				BXDesktopSystem.BroadcastEvent('DesktopCallCardSetUiState', [params, BX.DoNothing]);
				callback([]);
			},

			setMute: function(params, callback){

				if(!BackgroundWorker.CallCard)
				{
					callback(BackgroundWorker.UndefinedCallCard);

					return;
				}
				if (!params.hasOwnProperty('muted'))
				{
					callback({
						result: 'error',
						errorCode: 'missing field muted'
					});

					return;
				}

				BXDesktopSystem.BroadcastEvent('DesktopCallCardSetMute',[params, BX.DoNothing]);
				callback([]);
			},

			setHold: function(params, callback){
				if(!BackgroundWorker.CallCard)
				{
					callback(BackgroundWorker.UndefinedCallCard);

					return;
				}
				if (!params.hasOwnProperty('held'))
				{
					callback([{
						result: 'error',
						errorCode: 'missing field held'
					}]);
				}

				BXDesktopSystem.BroadcastEvent('DesktopCallCardSetHold',[params, BX.DoNothing]);
				callback([]);
			},

			getListUiStates: function(params, callback){
				BackgroundWorker.getListUiStates(params, callback);
			},

			setCardTitle: function(params, callback){
				if(!BackgroundWorker.CallCard)
				{
					callback(BackgroundWorker.UndefinedCallCard);

					return;
				}
				if (!params.hasOwnProperty('title'))
				{
					callback([{
						result: 'error',
						errorCode: 'missing field title'
					}]);

					return
				}

				BXDesktopSystem.BroadcastEvent('DesktopCallCardSetCardTitle',[params, BX.DoNothing]);
				callback([]);
			},

			setStatusText: function(params, callback){
				if(!BackgroundWorker.CallCard)
				{
					callback(BackgroundWorker.UndefinedCallCard);

					return;
				}
				if (!params.hasOwnProperty('statusText'))
				{
					callback([{
						result: 'error',
						errorCode: 'missing field statusText'
					}]);

					return;
				}

				BXDesktopSystem.BroadcastEvent('DesktopCallCardSetStatusText',[params, BX.DoNothing]);
				callback([]);
			},
			close: function(params, callback){
				if(!BackgroundWorker.CallCard)
				{
					callback(BackgroundWorker.UndefinedCallCard);

					return;
				}

				BackgroundWorker.CallCard = false;
				BXDesktopSystem.BroadcastEvent('DesktopCallCardClose',[params, BX.DoNothing]);
				callback([]);
			},
			startTimer: function(params, callback){
				if(!BackgroundWorker.CallCard)
				{
					callback(BackgroundWorker.UndefinedCallCard);

					return;
				}

				BXDesktopSystem.BroadcastEvent('DesktopCallCardStartTimer',[params, BX.DoNothing]);
				callback([]);
			},
			stopTimer: function(params, callback){
				if(!BackgroundWorker.CallCard)
				{
					callback(BackgroundWorker.UndefinedCallCard);

					return;
				}

				BXDesktopSystem.BroadcastEvent('DesktopCallCardStopTimer',[params, BX.DoNothing]);
				callback([]);
			},

			addCustomEvent: function(eventName, eventHandler)
			{
				const realHandler = function (e)
				{
					const arEventParams = [];
					for(const i in e.detail)
					{
						arEventParams.push(e.detail[i]);
					}

					eventHandler.apply(window, arEventParams);
				};

				if(!this.eventHandlers[eventName])
				{
					this.eventHandlers[eventName] = [];
				}

				this.eventHandlers[eventName].push(realHandler);
				window.addEventListener(eventName, realHandler);

				return true;
			},

			removeCustomEvents: function(eventName)
			{
				if(!this.eventHandlers[eventName])
				{
					return false;
				}

				this.eventHandlers[eventName].forEach(function(eventHandler)
				{
					window.removeEventListener(eventName, eventHandler);
				});

				this.eventHandlers[eventName] = [];
			},

			isCallCardPage: function()
			{
				return BXDesktopWindow.GetWindowId() !== BXDesktopSystem.GetMainWindow().GetWindowId()
			},

			isCorporatePortalPage: function()
			{
				return !BX.MessengerCommon.isDesktop();
			},

			getCallCardWindow: function()
			{
				return BXWindows.find(element => element.name === 'callWindow');
			},
		},

		removeDesktopEventHandlers: function()
		{
			for (const event of this.events)
			{
				this.desktop.removeCustomEvents('DesktopCallCard' + (event[0].toUpperCase() + event.slice(1)))
			}
			this.desktop.removeCustomEvents('DesktopCallCardInitialized');
			this.desktop.removeCustomEvents('DesktopCallCardCloseButtonClick');
		},

		onInitialize: function(params)
		{
			if (!this.isExternalCall)
			{
				return;
			}
			if (this.isDesktop() && this.desktop.isCallCardPage())
			{
				BXDesktopSystem.BroadcastEvent('DesktopCallCardInitialized', [params]);

				return;
			}
			BX.onCustomEvent(window, "BackgroundCallCard::initialized", [params]);
		},

		onAddCommentButtonClick: function(params)
		{
			if (!this.isExternalCall)
			{
				return;
			}
			if (this.isDesktop() && this.desktop.isCallCardPage())
			{
				BXDesktopSystem.BroadcastEvent('DesktopCallCardAddCommentButtonClick', [params])
			}
			BX.onCustomEvent(window, "BackgroundCallCard::addCommentButtonClick", [params]);
		},

		onMuteButtonClick: function(params)
		{
			if (!this.isExternalCall)
			{
				return;
			}
			if (this.isDesktop() && this.desktop.isCallCardPage())
			{
				BXDesktopSystem.BroadcastEvent('DesktopCallCardMuteButtonClick', [params])
			}
			BX.onCustomEvent(window, "BackgroundCallCard::muteButtonClick", [params]);
		},

		onHoldButtonClick: function(params)
		{
			if (!this.isExternalCall)
			{
				return;
			}
			if (this.isDesktop() && this.desktop.isCallCardPage())
			{
				BXDesktopSystem.BroadcastEvent('DesktopCallCardHoldButtonClick', [params])
			}
			BX.onCustomEvent(window, "BackgroundCallCard::holdButtonClick", [params]);
		},

		onCloseButtonClick: function(params)
		{
			if (!this.isExternalCall)
			{
				return;
			}
			if (this.isDesktop() && this.desktop.isCallCardPage())
			{
				BXDesktopSystem.BroadcastEvent('DesktopCallCardCloseButtonClick', [params])
			}
			BX.onCustomEvent(window, "BackgroundCallCard::closeButtonClick", [params]);
		},

		onTransferButtonClick: function(params)
		{
			if (!this.isExternalCall)
			{
				return;
			}
			if (this.isDesktop() && this.desktop.isCallCardPage())
			{
				BXDesktopSystem.BroadcastEvent('DesktopCallCardTransferButtonClick', [params])
			}
			BX.onCustomEvent(window, "BackgroundCallCard::transferButtonClick", [params]);
		},

		onCancelTransferButtonClick: function(params)
		{
			if (!this.isExternalCall)
			{
				return;
			}
			if (this.isDesktop() && this.desktop.isCallCardPage())
			{
				BXDesktopSystem.BroadcastEvent('DesktopCallCardCancelTransferButtonClick', [params])
			}
			BX.onCustomEvent(window, "BackgroundCallCard::cancelTransferButtonClick", [params]);
		},

		onCompleteTransferButtonClick: function(params)
		{
			if (!this.isExternalCall)
			{
				return;
			}
			if (this.isDesktop() && this.desktop.isCallCardPage())
			{
				BXDesktopSystem.BroadcastEvent('DesktopCallCardCompleteTransferButtonClick', [params])
			}
			BX.onCustomEvent(window, "BackgroundCallCard::completeTransferButtonClick", [params]);
		},

		onHangupButtonClick: function(params)
		{
			if (!this.isExternalCall)
			{
				return;
			}
			if (this.isDesktop() && this.desktop.isCallCardPage())
			{
				BXDesktopSystem.BroadcastEvent('DesktopCallCardHangupButtonClick', [params])
			}
			BX.onCustomEvent(window, "BackgroundCallCard::hangupButtonClick", [params]);
		},

		onNextButtonClick: function(params)
		{
			if (!this.isExternalCall)
			{
				return;
			}
			if (this.isDesktop() && this.desktop.isCallCardPage())
			{
				BXDesktopSystem.BroadcastEvent('DesktopCallCardNextButtonClick', [params])
			}
			BX.onCustomEvent(window, "BackgroundCallCard::nextButtonClick", [params]);
		},

		onSkipButtonClick: function(params)
		{
			if (!this.isExternalCall)
			{
				return;
			}
			if (this.isDesktop() && this.desktop.isCallCardPage())
			{
				BXDesktopSystem.BroadcastEvent('DesktopCallCardSkipButtonClick', [params])
			}
			BX.onCustomEvent(window, "BackgroundCallCard::skipButtonClick", [params]);
		},

		onAnswerButtonClick: function(params)
		{
			if (!this.isExternalCall)
			{
				return;
			}
			if (this.isDesktop() && this.desktop.isCallCardPage())
			{
				BXDesktopSystem.BroadcastEvent('DesktopCallCardAnswerButtonClick', [params])
			}
			BX.onCustomEvent(window, "BackgroundCallCard::answerButtonClick", [params]);
		},

		onEntityChanged: function(params)
		{
			if (!this.isExternalCall)
			{
				return;
			}
			if (this.isDesktop() && this.desktop.isCallCardPage())
			{
				BXDesktopSystem.BroadcastEvent('DesktopCallCardEntityChanged', [params])
			}
			BX.onCustomEvent(window, "BackgroundCallCard::entityChanged", [params]);
		},

		onQualityMeterClick: function(params)
		{
			if (!this.isExternalCall)
			{
				return;
			}
			if (this.isDesktop() && this.desktop.isCallCardPage())
			{
				BXDesktopSystem.BroadcastEvent('DesktopCallCardQualityMeterClick', [params])
			}
			BX.onCustomEvent(window, "BackgroundCallCard::qualityMeterClick", [params]);
		},

		onDialpadButtonClick: function(params)
		{
			if (!this.isExternalCall)
			{
				return;
			}
			if (this.isDesktop() && this.desktop.isCallCardPage())
			{
				BXDesktopSystem.BroadcastEvent('DesktopCallCardDialpadButtonClick', [params])
			}
			BX.onCustomEvent(window, "BackgroundCallCard::dialpadButtonClick", [params]);
		},

		onMakeCallButtonClick: function(params)
		{
			if (!this.isExternalCall)
			{
				return;
			}
			if (this.isDesktop() && this.desktop.isCallCardPage())
			{
				BXDesktopSystem.BroadcastEvent('DesktopCallCardMakeCallButtonClick', [params])
			}
			BX.onCustomEvent(window, "BackgroundCallCard::makeCallButtonClick", [params]);
		},

		onNotifyAdminButtonClick: function(params)
		{
			if (!this.isExternalCall)
			{
				return;
			}
			if (this.isDesktop() && this.desktop.isCallCardPage())
			{
				BXDesktopSystem.BroadcastEvent('DesktopCallCardNotifyAdminButtonClick', [params])
			}
			BX.onCustomEvent(window, "BackgroundCallCard::notifyAdminButtonClick", [params]);
		},

		/**
		 * @param params
		 * @param {boolean} params.muted
		 * @param {function} callback
		 */
		setMute: function(params, callback)
		{
			this.isUsed = true;
			if(!this.CallCard || !this.isExternalCall)
			{
				callback(this.UndefinedCallCard);

				return;
			}

			if (this.CallCard.isMuted() === !!params.muted)
			{
				callback([]);

				return;
			}
			if (params.muted)
			{
				this.CallCard.setMuted(params.muted)
				BX.addClass(this.CallCard.elements.buttons.mute, 'active');
				if (this.CallCard.isDesktop() && this.CallCard.slave)
				{
					BX.desktop.onCustomEvent(desktopEvents.onMute, []);
				}
				else
				{
					this.CallCard.callbacks.mute();
				}
			}
			else
			{
				this.CallCard.setMuted(params.muted)
				BX.removeClass(this.CallCard.elements.buttons.mute, 'active');
				if(this.CallCard.isDesktop() && this.CallCard.slave)
				{
					BX.desktop.onCustomEvent(desktopEvents.onUnMute, []);
				}
				else
				{
					this.CallCard.callbacks.unmute();
				}
			}
			callback([]);
		},

		/**
		 * @param params
		 * @param {boolean} params.held
		 * @param {function} callback
		 */
		setHold: function(params, callback)
		{
			this.isUsed = true;
			if(!this.CallCard || !this.isExternalCall)
			{
				callback([this.UndefinedCallCard]);

				return;
			}

			if (this.CallCard.isHeld() === !!params.held)
			{
				callback([]);

				return;
			}
			if (params.held)
			{
				this.CallCard.setHeld(params.held)
				BX.addClass(this.CallCard.elements.buttons.hold, 'active');
				if(this.CallCard.isDesktop() && this.CallCard.slave)
				{
					BX.desktop.onCustomEvent(desktopEvents.onHold, []);
				}
				else
				{
					this.CallCard.callbacks.hold();
				}
			}
			else
			{
				this.CallCard.setHeld(params.held);
				BX.removeClass(this.CallCard.elements.buttons.hold, 'active');
				if(this.CallCard.isDesktop() && this.CallCard.slave)
				{
					BX.desktop.onCustomEvent(desktopEvents.onUnHold, []);
				}
				else
				{
					this.CallCard.callbacks.unhold();
				}
			}
			callback([]);
		},

		/**
		 * @param params
		 * @param {string} params.uiState
		 * @param {boolean} [params.disableAutoStartTimer]
		 * @param {function} callback
		 */
		setUiState: function(params, callback)
		{
			this.isUsed = true;
			if (!this.CallCard || !this.isExternalCall)
			{
				callback([this.UndefinedCallCard]);

				return;
			}

			if (params && params.uiState && BX.PhoneCallView.UiState[params.uiState])
			{
				this.CallCard.setUiState(BX.PhoneCallView.UiState[params.uiState]);
				// BX.onCustomEvent(window, "CallCard::CallStateChanged", [callState, additionalParams]);
				// this.setOnSlave(desktopEvents.setCallState, [callState, additionalParams]);
			}
			else
			{
				callback([{
					result: 'error',
					errorCode: 'Invalid ui state'
				}]);

				return;
			}
			if (params.uiState === 'connected')
			{
				if (params.disableAutoStartTimer)
				{
					this.CallCard.stopTimer();
					this.hideTimer();
				}
				else
				{
					this.showTimer();
				}
			}

			if (params.uiState !== 'connected' && !this.CallCard.isTimerStarted())
			{
				this.hideTimer();
			}

			callback([]);
		},

		/**
		 * @param {{}} params
		 * @param {function} callback
		 */
		getListUiStates: function(params, callback)
		{
			this.isUsed = true;
			callback(Object.keys(BX.PhoneCallView.UiState).filter(function(state)
			{
				switch (state)
				{
					case 'sipPhoneError':
						return false;
					case 'idle':
						return false;
					case 'externalCard':
						return false;
					default:
						return true;
				}
			}));
		},

		/**
		 * @param {{}} params
		 * @param {function} callback
		 */
		startTimer: function(params, callback)
		{
			this.isUsed = true;
			if(!this.CallCard || !this.isExternalCall)
			{
				callback([this.UndefinedCallCard]);

				return;
			}

			this.showTimer();
			this.CallCard.startTimer();
		},

		/**
		 * @param {{}} params
		 * @param {function} callback
		 */
		stopTimer: function(params, callback)
		{
			this.isUsed = true;
			if(!this.CallCard || !this.isExternalCall)
			{
				callback([this.UndefinedCallCard]);

				return;
			}

			this.CallCard.stopTimer();
		},

		/**
		 * @param {{}} params
		 * @param {function} callback
		 */
		close: function(params, callback)
		{
			this.isUsed = true;
			if(!this.CallCard || !this.isExternalCall)
			{
				callback([this.UndefinedCallCard]);

				return;
			}

			this.CallCard.close();
			callback([]);

			this.CallCard = false;
		},

		/**
		 * @param params
		 * @param {string} params.title
		 * @param {function} callback
		 */
		setCardTitle: function(params, callback)
		{
			this.isUsed = true;
			if(!this.CallCard || !this.isExternalCall)
			{
				callback([this.UndefinedCallCard]);

				return;
			}
			this.CallCard.setTitle(params.title);
			callback([]);
		},

		/**
		 * @param params
		 * @param {string} params.statusText
		 * @param {function} callback
		 */
		setStatusText: function(params, callback)
		{
			this.isUsed = true;
			if(!this.CallCard || !this.isExternalCall)
			{
				callback([this.UndefinedCallCard]);

				return;
			}
			this.CallCard.setStatusText(params.statusText);
			callback([]);
		},

		showTimer: function()
		{
			if (!BX.PhoneCallView.BackgroundWorker.CallCard.elements.timer.visible)
			{
				BX.PhoneCallView.BackgroundWorker.CallCard.sections.timer.visible = true;
				BX.PhoneCallView.BackgroundWorker.CallCard.elements.timer.style.display = '';
				if (BX.PhoneCallView.BackgroundWorker.CallCard.isFolded())
				{
					BX.PhoneCallView.BackgroundWorker.CallCard.unfoldedElements.timer.style.display = '';
				}
			}
		},

		hideTimer: function()
		{
			if (this.CallCard.sections.timer)
			{
				this.CallCard.sections.timer.visible = false;
			}
			if (this.CallCard.elements.timer)
			{
				this.CallCard.elements.timer.style.display = 'none';
			}
			this.CallCard.initialTimestamp = 0;
		},

		isDesktop: function()
		{
			return typeof(BXDesktopSystem) !== 'undefined';
		},
	};

	BX.PhoneCallView.BackgroundWorker = BackgroundWorker;

	function insertAtCursor(inputElement, value)
	{
		if (inputElement.selectionStart || inputElement.selectionStart == '0')
		{
			var startPos = inputElement.selectionStart;
			var endPos = inputElement.selectionEnd;
			inputElement.value = inputElement.value.substring(0, startPos)
				+ value
				+ inputElement.value.substring(endPos, inputElement.value.length);
			inputElement.selectionStart = startPos + value.length;
			inputElement.selectionEnd = startPos + value.length;
		}
		else
		{
			inputElement.value += value;
		}
	}
})();