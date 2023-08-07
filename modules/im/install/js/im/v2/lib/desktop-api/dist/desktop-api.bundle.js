/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core_events,im_v2_const,main_core) {
	'use strict';

	const versionFunctions = {
	  getApiVersion() {
	    if (!this.isDesktop()) {
	      return 0;
	    }
	    const [majorVersion, minorVersion, buildVersion, apiVersion] = window.BXDesktopSystem.GetProperty('versionParts');
	    return apiVersion;
	  },
	  isFeatureEnabled(code) {
	    var _window$BXDesktopSyst;
	    return !!((_window$BXDesktopSyst = window.BXDesktopSystem) != null && _window$BXDesktopSyst.FeatureEnabled(code));
	  }
	};

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
	    main_core.Event.bind(window, eventName, preparedHandler);
	  },
	  unsubscribe(eventName, handler) {
	    main_core.Event.unbind(window, eventName, handler);
	  },
	  emit(eventName, params = []) {
	    var _BXDesktopWindow;
	    (_BXDesktopWindow = BXDesktopWindow) == null ? void 0 : _BXDesktopWindow.DispatchCustomEvent(eventName, params);
	  },
	  emitToMainWindow(eventName, params = []) {
	    var _BXDesktopSystem, _BXDesktopSystem$GetM;
	    (_BXDesktopSystem = BXDesktopSystem) == null ? void 0 : (_BXDesktopSystem$GetM = _BXDesktopSystem.GetMainWindow()) == null ? void 0 : _BXDesktopSystem$GetM.DispatchCustomEvent(eventName, params);
	  }
	};

	const windowFunctions = {
	  showWindow() {
	    var _BXDesktopSystem, _window$BXDesktopWind;
	    (_BXDesktopSystem = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem.SetActiveTab();
	    (_window$BXDesktopWind = window.BXDesktopWindow) == null ? void 0 : _window$BXDesktopWind.ExecuteCommand('show');
	  },
	  hideWindow() {
	    var _window$BXDesktopWind2;
	    (_window$BXDesktopWind2 = window.BXDesktopWindow) == null ? void 0 : _window$BXDesktopWind2.ExecuteCommand('hide');
	  },
	  closeWindow() {
	    var _window$BXDesktopWind3;
	    (_window$BXDesktopWind3 = window.BXDesktopWindow) == null ? void 0 : _window$BXDesktopWind3.ExecuteCommand('close');
	  },
	  closeWindowByName(name = '') {
	    var _window$BXDesktopWind4;
	    const window = this.findWindow(name);
	    if (!window) {
	      return;
	    }
	    (_window$BXDesktopWind4 = window.BXDesktopWindow) == null ? void 0 : _window$BXDesktopWind4.ExecuteCommand('close');
	  },
	  reloadWindow() {
	    const event = new main_core_events.BaseEvent();
	    main_core_events.EventEmitter.emit(window, im_v2_const.EventType.desktop.onReload, event);
	    location.reload();
	  },
	  findWindow(name = '') {
	    var _opener;
	    const mainWindow = (_opener = opener) != null ? _opener : top;
	    return mainWindow.BXWindows.find(window => (window == null ? void 0 : window.name) === name);
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
	    return `
			<!DOCTYPE html>
			<html>
				<body class="im-desktop im-desktop-popup">
					${html}${js}
				</body>
			</html>
		`;
	  }
	};

	const iconFunctions = {
	  setCounter(counter, important = false) {
	    var _BXDesktopSystem, _BXDesktopSystem2;
	    const preparedCounter = counter.toString();
	    (_BXDesktopSystem = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem.SetIconBadge(preparedCounter, important);
	    (_BXDesktopSystem2 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem2.SetTabBadge(0, preparedCounter);
	  },
	  setIconStatus(status) {
	    var _BXDesktopSystem3;
	    (_BXDesktopSystem3 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem3.SetIconStatus(status);
	  },
	  setOfflineIcon() {
	    var _BXDesktopSystem4;
	    (_BXDesktopSystem4 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem4.SetIconStatus('offline');
	  },
	  setBrowserIconBadge(counter) {
	    var _BXDesktopSystem5;
	    (_BXDesktopSystem5 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem5.SetBrowserIconBadge(counter.toString());
	  }
	};

	const DesktopSettingsKey = {
	  smoothing: 'bxd_camera_smoothing'
	};
	const settingsFunctions = {
	  getCameraSmoothingStatus() {
	    var _BXDesktopSystem;
	    return ((_BXDesktopSystem = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem.QuerySettings(DesktopSettingsKey.smoothing, '0')) === '1';
	  },
	  setCameraSmoothingStatus(status) {
	    var _BXDesktopSystem2;
	    const preparedStatus = status === true ? '1' : '0';
	    (_BXDesktopSystem2 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem2.StoreSettings(DesktopSettingsKey.smoothing, preparedStatus);
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

	const DesktopApi = {
	  isDesktop() {
	    return main_core.Type.isObject(window.BXDesktopSystem);
	  },
	  isTwoWindowMode() {
	    var _BXDesktopSystem;
	    return !!((_BXDesktopSystem = BXDesktopSystem) != null && _BXDesktopSystem.IsTwoWindowsMode());
	  },
	  isChatWindow() {
	    return location.href.includes('desktop_app');
	  },
	  exit() {
	    var _BXDesktopSystem2;
	    (_BXDesktopSystem2 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem2.Shutdown();
	  },
	  log(fileName, text) {
	    var _BXDesktopSystem3;
	    (_BXDesktopSystem3 = BXDesktopSystem) == null ? void 0 : _BXDesktopSystem3.Log(fileName, text);
	  },
	  ...versionFunctions,
	  ...eventFunctions,
	  ...windowFunctions,
	  ...iconFunctions,
	  ...settingsFunctions,
	  ...legacyFunctions
	};

	exports.DesktopApi = DesktopApi;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Event,BX.Messenger.v2.Const,BX));
//# sourceMappingURL=desktop-api.bundle.js.map
