/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core_events,im_v2_const,main_core,im_v2_lib_logger) {
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
	    if (!DesktopFeature[code]) {
	      return false;
	    }
	    return this.getApiVersion() >= DesktopFeature[code].version;
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
	    if (main_core.Type.isDomNode(html)) {
	      html = html.outerHTML;
	    }
	    if (main_core.Type.isDomNode(js)) {
	      js = js.outerHTML;
	    }
	    main_core.Event.ready();
	    if (main_core.Type.isStringFilled(js)) {
	      js = `
				<script type="text/javascript">
					BX.ready(() => {
						${js}
					});
				</script>
			`;
	    }
	    const head = document.head.outerHTML.replaceAll(/BX\.PULL\.start\([^)]*\);/g, '');
	    return `
			<!DOCTYPE html>
			<html>
				${head}
				<body class="im-desktop im-desktop-popup">
					${html}${js}
				</body>
			</html>
		`;
	  },
	  setWindowSize(width, height) {
	    BXDesktopWindow.SetProperty("clientSize", {
	      Width: width,
	      Height: height
	    });
	  },
	  setMinimumWindowSize(width, height) {
	    BXDesktopWindow.SetProperty("minClientSize", {
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

	const DesktopSettingsKey = {
	  smoothing: 'bxd_camera_smoothing',
	  telemetry: 'bxd_telemetry',
	  sliderBindingsStatus: 'sliderBindingsStatus'
	};
	const settingsFunctions = {
	  getCameraSmoothingStatus() {
	    return this.getCustomSetting(DesktopSettingsKey.smoothing, '0') === '1';
	  },
	  setCameraSmoothingStatus(status) {
	    const preparedStatus = status === true ? '1' : '0';
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
	  getBackgroundImage() {
	    if (!this.isDesktop()) {
	      return {
	        id: 'none',
	        source: ''
	      };
	    }
	    const id = BXDesktopSystem.QuerySettings("bxd_camera_background_id") || 'none';
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
	    setTimeout(() => {
	      this.setCallMask(false);
	      BXDesktopSystem.StoreSettings('bxd_camera_background_id', id);
	      BXDesktopSystem.StoreSettings('bxd_camera_background', source);
	      promise.resolve();
	    }, 100);
	    return promise;
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
	  ...loggerFunctions
	};

	exports.DesktopApi = DesktopApi;
	exports.DesktopFeature = DesktopFeature;
	exports.DesktopSettingsKey = DesktopSettingsKey;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Event,BX.Messenger.v2.Const,BX,BX.Messenger.v2.Lib));
//# sourceMappingURL=desktop-api.bundle.js.map
