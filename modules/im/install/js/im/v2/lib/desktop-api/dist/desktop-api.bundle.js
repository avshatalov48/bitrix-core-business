/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_lib_utils,im_v2_lib_logger,main_core,im_v2_const,main_core_events) {
	'use strict';

	const lifecycleFunctions = {
	  isDesktop() {
	    return main_core.Type.isObject(window.BXDesktopSystem);
	  },
	  restart() {
	    var _BXDesktopSystem;
	    if (this.getApiVersion() < 74) {
	      return;
	    }
	    (_BXDesktopSystem = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem.Restart();
	  },
	  shutdown() {
	    var _BXDesktopSystem2;
	    (_BXDesktopSystem2 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem2.Shutdown();
	  }
	};

	const DesktopFeature = {
	  mask: {
	    id: 'mask',
	    version: 72
	  },
	  restart: {
	    id: 'restart',
	    version: 74
	  },
	  accountManagement: {
	    id: 'accountManagement',
	    version: 75
	  },
	  openNewTab: {
	    id: 'openNewTab',
	    version: 76
	  },
	  openPage: {
	    id: 'openPage',
	    version: 79
	  }
	};

	const versionFunctions = {
	  getApiVersion() {
	    if (!this.isDesktop()) {
	      return 0;
	    }

	    // eslint-disable-next-line no-unused-vars
	    const [majorVersion, minorVersion, buildVersion, apiVersion] = window.BXDesktopSystem.GetProperty('versionParts');
	    return apiVersion;
	  },
	  isFeatureEnabled(code) {
	    var _window$BXDesktopSyst;
	    return Boolean((_window$BXDesktopSyst = window.BXDesktopSystem) == null ? void 0 : _window$BXDesktopSyst.FeatureEnabled(code));
	  },
	  isFeatureSupported(code) {
	    return this.isFeatureSupportedInVersion(this.getApiVersion(), code);
	  },
	  isFeatureSupportedInVersion(version, code) {
	    if (!DesktopFeature[code]) {
	      return false;
	    }
	    return version >= DesktopFeature[code].version;
	  }
	};

	const eventHandlers = {};
	const eventFunctions = {
	  subscribe(eventName, handler) {
	    if (!this.isDesktop()) {
	      return;
	    }
	    const preparedHandler = event => {
	      var _event$detail;
	      const params = (_event$detail = event.detail) != null ? _event$detail : [];
	      handler.apply(window, params);
	    };
	    if (!eventHandlers[eventName]) {
	      eventHandlers[eventName] = [];
	    }
	    eventHandlers[eventName].push(preparedHandler);
	    main_core.Event.bind(window, eventName, preparedHandler);
	  },
	  unsubscribe(eventName, handler) {
	    if (!main_core.Type.isFunction(handler)) {
	      if (!main_core.Type.isArrayFilled(eventHandlers[eventName])) {
	        return;
	      }
	      eventHandlers[eventName].forEach(eventHandler => {
	        main_core.Event.unbind(window, eventName, eventHandler);
	      });
	      return;
	    }
	    main_core.Event.unbind(window, eventName, handler);
	  },
	  emit(eventName, params = []) {
	    const mainWindow = opener || top;
	    const allWindows = mainWindow.BXWindows;
	    allWindows.forEach(window => {
	      var _window$BXDesktopWind;
	      if (!window || window.name === '') {
	        return;
	      }
	      window == null ? void 0 : (_window$BXDesktopWind = window.BXDesktopWindow) == null ? void 0 : _window$BXDesktopWind.DispatchCustomEvent(eventName, params);
	    });
	    this.emitToMainWindow(eventName, params);
	  },
	  emitToMainWindow(eventName, params = []) {
	    var _mainWindow$BXDesktop, _mainWindow$BXDesktop2;
	    const mainWindow = opener || top;
	    (_mainWindow$BXDesktop = mainWindow.BXDesktopSystem) == null ? void 0 : (_mainWindow$BXDesktop2 = _mainWindow$BXDesktop.GetMainWindow()) == null ? void 0 : _mainWindow$BXDesktop2.DispatchCustomEvent(eventName, params);
	  }
	};

	const DesktopSettingsKey = {
	  smoothing: 'bxd_camera_smoothing',
	  smoothing_v2: 'bxd_camera_smoothing_v2',
	  telemetry: 'bxd_telemetry',
	  sliderBindingsStatus: 'sliderBindingsStatus'
	};
	const settingsFunctions = {
	  getCameraSmoothingStatus() {
	    return this.getCustomSetting(DesktopSettingsKey.smoothing, '0') === '1';
	  },
	  setCameraSmoothingStatus(status) {
	    const preparedStatus = status === true ? '1' : '0';
	    if (this.getApiVersion() > 76) {
	      this.setCustomSetting(DesktopSettingsKey.smoothing_v2, preparedStatus);
	      return;
	    }
	    this.setCustomSetting(DesktopSettingsKey.smoothing, preparedStatus);
	  },
	  isTwoWindowMode() {
	    var _BXDesktopSystem;
	    return Boolean((_BXDesktopSystem = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem.IsTwoWindowsMode());
	  },
	  setTwoWindowMode(flag) {
	    var _BXDesktopSystem3;
	    if (flag === true) {
	      var _BXDesktopSystem2;
	      (_BXDesktopSystem2 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem2.V10();
	      return;
	    }
	    (_BXDesktopSystem3 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem3.V8();
	  },
	  getAutostartStatus() {
	    var _BXDesktopSystem4;
	    return (_BXDesktopSystem4 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem4.GetProperty('autostart');
	  },
	  setAutostartStatus(flag) {
	    var _BXDesktopSystem5;
	    (_BXDesktopSystem5 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem5.SetProperty('autostart', flag);
	  },
	  getTelemetryStatus() {
	    return this.getCustomSetting(DesktopSettingsKey.telemetry, '1') === '1';
	  },
	  setTelemetryStatus(flag) {
	    this.setCustomSetting(DesktopSettingsKey.telemetry, flag ? '1' : '0');
	  },
	  setCustomSetting(name, value) {
	    var _BXDesktopSystem6;
	    (_BXDesktopSystem6 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem6.StoreSettings(name, value);
	  },
	  getCustomSetting(name, defaultValue) {
	    var _BXDesktopSystem7;
	    return (_BXDesktopSystem7 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem7.QuerySettings(name, defaultValue);
	  }
	};

	const windowFunctions = {
	  isTwoWindowMode() {
	    var _BXDesktopSystem;
	    return Boolean((_BXDesktopSystem = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem.IsTwoWindowsMode());
	  },
	  isChatWindow() {
	    const settings = main_core.Extension.getSettings('im.v2.lib.desktop-api');
	    return this.isDesktop() && settings.get('isChatWindow');
	  },
	  isChatTab() {
	    return this.isChatWindow() || this.isDesktop() && location.href.includes('&IM_TAB=Y');
	  },
	  setActiveTab(target = window) {
	    var _target$BXDesktopSyst;
	    if (!main_core.Type.isObject(target)) {
	      return;
	    }
	    (_target$BXDesktopSyst = target.BXDesktopSystem) == null ? void 0 : _target$BXDesktopSyst.SetActiveTab();
	  },
	  showWindow(target = window) {
	    var _target$BXDesktopWind;
	    if (!main_core.Type.isObject(target)) {
	      return;
	    }
	    (_target$BXDesktopWind = target.BXDesktopWindow) == null ? void 0 : _target$BXDesktopWind.ExecuteCommand('show');
	  },
	  activateWindow(target = window) {
	    this.setActiveTab(target);
	    this.showWindow(target);
	  },
	  hideWindow(target = window) {
	    var _target$BXDesktopWind2;
	    if (!main_core.Type.isObject(target)) {
	      return;
	    }
	    (_target$BXDesktopWind2 = target.BXDesktopWindow) == null ? void 0 : _target$BXDesktopWind2.ExecuteCommand('hide');
	  },
	  closeWindow(target = window) {
	    var _target$BXDesktopWind3;
	    if (!main_core.Type.isObject(target)) {
	      return;
	    }
	    (_target$BXDesktopWind3 = target.BXDesktopWindow) == null ? void 0 : _target$BXDesktopWind3.ExecuteCommand('close');
	  },
	  hideLoader() {
	    main_core.Dom.remove(document.getElementById('bx-desktop-loader'));
	  },
	  reloadWindow() {
	    const event = new main_core_events.BaseEvent();
	    main_core_events.EventEmitter.emit(window, im_v2_const.EventType.desktop.onReload, event);
	    location.reload();
	  },
	  findWindow(name = '') {
	    const mainWindow = opener || top;
	    return mainWindow.BXWindows.find(window => (window == null ? void 0 : window.name) === name);
	  },
	  openPage(url, options = {}) {
	    const anchorElement = main_core.Dom.create({
	      tag: 'a',
	      attrs: {
	        href: url
	      }
	    });
	    if (anchorElement.host !== location.host) {
	      setTimeout(() => this.hideWindow(), 100);
	      return Promise.resolve(false);
	    }
	    if (!settingsFunctions.isTwoWindowMode()) {
	      if (options.skipNativeBrowser === true) {
	        setTimeout(() => this.hideWindow(), 100);
	        return Promise.resolve(false);
	      }
	      im_v2_lib_utils.Utils.browser.openLink(anchorElement.href);

	      // workaround timeout, if application is activated on hit, it cant be hidden immediately
	      setTimeout(() => this.hideWindow(), 100);
	      return Promise.resolve(true);
	    }
	    this.createTab(anchorElement.href);
	    return Promise.resolve(true);
	  },
	  createTab(path) {
	    const preparedPath = main_core.Dom.create({
	      tag: 'a',
	      attrs: {
	        href: path
	      }
	    }).href;
	    BXDesktopSystem.CreateTab(preparedPath);
	  },
	  createImTab(path) {
	    const preparedPath = main_core.Dom.create({
	      tag: 'a',
	      attrs: {
	        href: path
	      }
	    }).href;
	    BXDesktopSystem.CreateImTab(preparedPath);
	  },
	  createWindow(name, callback) {
	    BXDesktopSystem.GetWindow(name, callback);
	  },
	  createTopmostWindow(htmlContent) {
	    return BXDesktopSystem.ExecuteCommand('topmost.show.html', htmlContent);
	  },
	  setWindowPosition(rawParams) {
	    var _BXDesktopWindow;
	    const preparedParams = {};
	    Object.entries(rawParams).forEach(([key, value]) => {
	      const preparedKey = key[0].toUpperCase() + key.slice(1);
	      preparedParams[preparedKey] = value;
	    });
	    (_BXDesktopWindow = BXDesktopWindow) == null ? void 0 : _BXDesktopWindow.SetProperty('position', preparedParams);
	  },
	  prepareHtml(html, js) {
	    let plainHtml = '';
	    if (main_core.Type.isDomNode(html)) {
	      plainHtml = html.outerHTML;
	    } else {
	      plainHtml = html;
	    }
	    let plainJs = '';
	    if (main_core.Type.isDomNode(js)) {
	      plainJs = js.outerHTML;
	    } else {
	      plainJs = js;
	    }
	    main_core.Event.ready();
	    if (main_core.Type.isStringFilled(plainJs)) {
	      plainJs = `
				<script>
					BX.ready(() => {
						${plainJs}
					});
				</script>
			`;
	    }
	    const head = document.head.outerHTML.replaceAll(/BX\.PULL\.start\([^)]*\);/g, '');
	    return `
			<!DOCTYPE html>
			<html lang="">
				${head}
				<body class="im-desktop im-desktop-popup">
					${plainHtml}${plainJs}
				</body>
			</html>
		`;
	  },
	  setWindowSize(width, height) {
	    BXDesktopWindow.SetProperty('clientSize', {
	      Width: width,
	      Height: height
	    });
	  },
	  setMinimumWindowSize(width, height) {
	    BXDesktopWindow.SetProperty('minClientSize', {
	      Width: width,
	      Height: height
	    });
	  }
	};

	const iconFunctions = {
	  setCounter(counter, important = false) {
	    var _BXDesktopSystem, _BXDesktopSystem2;
	    const preparedCounter = counter.toString();
	    (_BXDesktopSystem = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem.SetIconBadge(preparedCounter, important);
	    (_BXDesktopSystem2 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem2.SetTabBadge(0, preparedCounter);
	  },
	  setBrowserIconBadge(counter) {
	    var _BXDesktopSystem3;
	    (_BXDesktopSystem3 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem3.SetBrowserIconBadge(counter.toString());
	  },
	  setIconStatus(status) {
	    var _BXDesktopSystem4;
	    (_BXDesktopSystem4 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem4.SetIconStatus(status);
	  },
	  setOfflineIcon() {
	    var _BXDesktopSystem5;
	    (_BXDesktopSystem5 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem5.SetIconStatus('offline');
	  },
	  flashIcon() {
	    var _BXDesktopSystem6;
	    if (!main_core.Browser.isWin()) {
	      return;
	    }
	    (_BXDesktopSystem6 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem6.FlashIcon();
	  }
	};

	const commonFunctions = {
	  prepareResourcePath(source) {
	    let result = '';
	    try {
	      const url = new URL(source, location.origin);
	      result = url.href;
	    } catch {
	      // empty
	    }
	    return result;
	  }
	};

	const legacyFunctions = {
	  changeTab(tabId) {
	    const settings = main_core.Extension.getSettings('im.v2.lib.desktop-api');
	    const v2 = settings.get('v2');
	    if (v2) {
	      return;
	    }
	    BX.desktop.changeTab(tabId);
	  }
	};

	const notificationFunctions = {
	  removeNativeNotifications() {
	    var _BXDesktopSystem;
	    if (this.getApiVersion() < 74) {
	      return;
	    }
	    (_BXDesktopSystem = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem.NotificationRemoveAll();
	  }
	};

	const loggerFunctions = {
	  writeToLogFile(filename, text) {
	    var _BXDesktopSystem;
	    if (!main_core.Type.isStringFilled(filename)) {
	      console.error('Desktop logger: filename is not defined');
	      return;
	    }
	    let textPrepared = '';
	    if (main_core.Type.isString(text)) {
	      textPrepared = text;
	    } else if (main_core.Type.isNumber(text)) {
	      textPrepared = text.toString();
	    } else {
	      textPrepared = JSON.stringify(text);
	    }
	    (_BXDesktopSystem = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem.Log(filename, textPrepared);
	  },
	  printWelcomePrompt() {
	    const version = BXDesktopSystem.GetProperty('versionParts').join('.');
	    let osName = 'unknown';
	    if (main_core.Browser.isMac()) {
	      osName = 'MacOS';
	    } else if (main_core.Browser.isWin()) {
	      osName = 'Windows';
	    } else if (main_core.Browser.isLinux()) {
	      osName = 'Linux';
	    }
	    const promptMessage = main_core.Loc.getMessage('IM_LIB_DESKTOP_API_WELCOME_PROMPT', {
	      '#VERSION#': version,
	      '#OS#': osName
	    });
	    im_v2_lib_logger.Logger.desktop(promptMessage);
	  },
	  setLogInfo(logFunction) {
	    BXDesktopSystem.LogInfo = logFunction;
	  }
	};

	const callMaskFunctions = {
	  getCallMask() {
	    if (!this.isDesktop()) {
	      return {
	        id: ''
	      };
	    }
	    return {
	      id: BXDesktopSystem.QuerySettings('bxd_camera_3dbackground_id') || ''
	    };
	  },
	  setCallMaskLoadHandlers(callback) {
	    this.subscribe('BX3dAvatarReady', callback);
	    this.subscribe('BX3dAvatarError', callback);
	  },
	  setCallMask(id, maskUrl, backgroundUrl) {
	    if (this.getApiVersion() < 72) {
	      return false;
	    }
	    if (!id) {
	      BXDesktopSystem.Set3dAvatar('', '');
	      BXDesktopSystem.StoreSettings('bxd_camera_3dbackground_id', '');
	      return true;
	    }
	    maskUrl = this.prepareResourcePath(maskUrl);
	    backgroundUrl = this.prepareResourcePath(backgroundUrl);
	    BXDesktopSystem.Set3dAvatar(maskUrl, backgroundUrl);
	    BXDesktopSystem.StoreSettings('bxd_camera_3dbackground_id', id);
	    return true;
	  }
	};

	const callBackgroundFunctions = {
	  isBlur(source) {
	    return source.toString().toLowerCase().includes('blur');
	  },
	  getLimitationBackground(source) {
	    const limitation = BX.message('call_features');
	    const defaultLimitation = {
	      enable: true
	    };
	    let limitationType = '';
	    if (source && source !== 'none') {
	      limitationType = `${this.isBlur(source) ? 'blur_' : ''}background`;
	    }
	    const currentLimitation = limitationType ? limitation == null ? void 0 : limitation[`call_${limitationType}`] : null;
	    if (!currentLimitation) {
	      return defaultLimitation;
	    }
	    return {
	      enable: currentLimitation.enable,
	      articleCode: currentLimitation.articleCode
	    };
	  },
	  openArticle(articleCode) {
	    const infoHelper = BX.UI.InfoHelper;
	    if (infoHelper.isOpen()) {
	      infoHelper.close();
	    }
	    infoHelper.show(articleCode);
	  },
	  handleLimitationBackground(limitationObj, handle) {
	    const {
	      enable,
	      articleCode
	    } = limitationObj;
	    if (enable && typeof handle === "function") {
	      handle();
	    }
	    if (!enable && articleCode) {
	      this.openArticle(articleCode);
	    }
	  },
	  getBackgroundImage() {
	    var _this$getLimitationBa;
	    const id = BXDesktopSystem.QuerySettings("bxd_camera_background_id") || 'none';
	    if (!this.isDesktop() || !((_this$getLimitationBa = this.getLimitationBackground(id)) != null && _this$getLimitationBa.enable)) {
	      return {
	        id: 'none',
	        source: ''
	      };
	    }
	    return {
	      id
	    };
	  },
	  setCallBackground(id, source) {
	    if (source === 'none' || source === '') {
	      source = '';
	    } else if (source === 'blur') ; else if (source === 'gaussianBlur') {
	      source = 'GaussianBlur';
	    } else {
	      source = this.prepareResourcePath(source);
	    }
	    var promise = new BX.Promise();
	    const limitation = this.getLimitationBackground(source);
	    let currentSource = '';
	    let currentId = '';
	    this.handleLimitationBackground(limitation, () => {
	      currentSource = source;
	      currentId = id;
	    });
	    setTimeout(() => {
	      this.setCallMask(false);
	      BXDesktopSystem.StoreSettings('bxd_camera_background_id', currentId);
	      BXDesktopSystem.StoreSettings('bxd_camera_background', currentSource);
	      promise.resolve(currentId || "none");
	    }, 100);
	    return promise;
	  }
	};

	/* eslint-disable no-undef */
	const accountFunctions = {
	  openAddAccountTab() {
	    var _BXDesktopSystem;
	    (_BXDesktopSystem = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem.AccountAddForm();
	  },
	  deleteAccount(host, login) {
	    var _BXDesktopSystem2;
	    (_BXDesktopSystem2 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem2.AccountDelete(host, login);
	  },
	  connectAccount(host, login, protocol, userLang) {
	    var _BXDesktopSystem3;
	    (_BXDesktopSystem3 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem3.AccountConnect(host, login, protocol, userLang);
	  },
	  disconnectAccount(host) {
	    var _BXDesktopSystem4;
	    (_BXDesktopSystem4 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem4.AccountDisconnect(host);
	  },
	  getAccountList() {
	    var _BXDesktopSystem5;
	    return (_BXDesktopSystem5 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem5.AccountList();
	  },
	  login() {
	    return new Promise(resolve => {
	      var _BXDesktopSystem6;
	      (_BXDesktopSystem6 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem6.Login({
	        // there is no fail callback. If it fails, desktop will show login form
	        success: () => resolve()
	      });
	    });
	  },
	  async logout() {
	    try {
	      var _BXDesktopSystem7;
	      await main_core.ajax.runAction(im_v2_const.RestMethod.imV2DesktopLogout);
	      (_BXDesktopSystem7 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem7.Logout(2);
	    } catch (error) {
	      var _BXDesktopSystem8;
	      console.error('DesktopApi logout error', error);
	      (_BXDesktopSystem8 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem8.Logout(3);
	    }
	  },
	  async terminate() {
	    try {
	      await main_core.ajax.runAction(im_v2_const.RestMethod.imV2DesktopLogout);
	    } finally {
	      lifecycleFunctions.shutdown();
	    }
	  }
	};

	const diskFunctions = {
	  startDiskSync() {
	    var _BXFileStorage;
	    (_BXFileStorage = BXFileStorage) == null ? void 0 : _BXFileStorage.SyncPause(false);
	    const event = new main_core_events.BaseEvent({
	      compatData: [true]
	    });
	    main_core_events.EventEmitter.emit(window, im_v2_const.EventType.desktop.onSyncPause, event);
	  },
	  stopDiskSync() {
	    var _BXFileStorage2;
	    (_BXFileStorage2 = BXFileStorage) == null ? void 0 : _BXFileStorage2.SyncPause(true);
	    const event = new main_core_events.BaseEvent({
	      compatData: [false]
	    });
	    main_core_events.EventEmitter.emit(window, im_v2_const.EventType.desktop.onSyncPause, event);
	  }
	};

	const debugFunctions = {
	  openDeveloperTools() {
	    var _BXDesktopWindow;
	    (_BXDesktopWindow = BXDesktopWindow) == null ? void 0 : _BXDesktopWindow.OpenDeveloperTools();
	  },
	  openLogsFolder() {
	    var _BXDesktopSystem;
	    (_BXDesktopSystem = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem.OpenLogsFolder();
	  }
	};

	const DesktopApi = {
	  ...lifecycleFunctions,
	  ...commonFunctions,
	  ...versionFunctions,
	  ...eventFunctions,
	  ...windowFunctions,
	  ...iconFunctions,
	  ...notificationFunctions,
	  ...settingsFunctions,
	  ...legacyFunctions,
	  ...callBackgroundFunctions,
	  ...callMaskFunctions,
	  ...loggerFunctions,
	  ...accountFunctions,
	  ...diskFunctions,
	  ...debugFunctions
	};

	exports.DesktopApi = DesktopApi;
	exports.DesktopFeature = DesktopFeature;
	exports.DesktopSettingsKey = DesktopSettingsKey;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Const,BX.Event));
//# sourceMappingURL=desktop-api.bundle.js.map
