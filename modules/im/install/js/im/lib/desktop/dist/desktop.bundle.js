/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,main_core) {
	'use strict';

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var Desktop = /*#__PURE__*/function () {
	  function Desktop() {
	    babelHelpers.classCallCheck(this, Desktop);
	    babelHelpers.defineProperty(this, "clientVersion", 0);
	    babelHelpers.defineProperty(this, "eventHandlers", {});
	    babelHelpers.defineProperty(this, "htmlWrapperHead", null);
	  }
	  babelHelpers.createClass(Desktop, [{
	    key: "addCustomEvent",
	    value: function addCustomEvent(eventName, eventHandler) {
	      var realHandler = function realHandler(event) {
	        eventHandler.apply(window, babelHelpers.toConsumableArray(Object.values(event.detail)));
	      };
	      if (!this.eventHandlers[eventName]) {
	        this.eventHandlers[eventName] = [];
	      }
	      this.eventHandlers[eventName].push(realHandler);
	      window.addEventListener(eventName, realHandler);
	      return true;
	    }
	  }, {
	    key: "removeCustomEvents",
	    value: function removeCustomEvents(eventName) {
	      if (!this.eventHandlers[eventName]) {
	        return false;
	      }
	      this.eventHandlers[eventName].forEach(function (eventHandler) {
	        window.removeEventListener(eventName, eventHandler);
	      });
	      this.eventHandlers[eventName] = [];
	      return true;
	    }
	  }, {
	    key: "onCustomEvent",
	    value: function onCustomEvent(windowTarget, eventName, eventParams) {
	      if (arguments.length === 2) {
	        eventParams = eventName;
	        eventName = windowTarget;
	        windowTarget = 'all';
	      } else if (arguments.length < 2) {
	        return false;
	      }
	      var convertedEventParams = _objectSpread({}, eventParams);
	      if (windowTarget === 'all') {
	        var mainWindow = opener ? opener : top;
	        mainWindow.BXWindows.forEach(function (windowItem) {
	          if (windowItem && windowItem.name !== '' && windowItem.BXDesktopWindow && windowItem.BXDesktopWindow.DispatchCustomEvent) {
	            windowItem.BXDesktopWindow.DispatchCustomEvent(eventName, convertedEventParams);
	          }
	        });
	        mainWindow.BXDesktopWindow.DispatchCustomEvent(eventName, convertedEventParams);
	      } else if (main_core.Type.isObject(windowTarget) && windowTarget.hasOwnProperty("BXDesktopWindow")) {
	        windowTarget.BXDesktopWindow.DispatchCustomEvent(eventName, convertedEventParams);
	      } else {
	        var existingWindow = this.findWindow(windowTarget);
	        if (existingWindow) {
	          existingWindow.BXDesktopWindow.DispatchCustomEvent(eventName, convertedEventParams);
	        }
	      }
	      return true;
	    }
	  }, {
	    key: "findWindow",
	    value: function findWindow() {
	      var name = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'main';
	      var mainWindow = opener ? opener : top;
	      if (name === 'main') {
	        return mainWindow;
	      } else {
	        return mainWindow.BXWindows.find(function (windowItem) {
	          return windowItem.name === name;
	        });
	      }
	    }
	  }, {
	    key: "setWindowResizable",
	    value: function setWindowResizable() {
	      var enabled = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      BXDesktopWindow.SetProperty("resizable", enabled);
	      return true;
	    }
	  }, {
	    key: "setWindowClosable",
	    value: function setWindowClosable() {
	      var enabled = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      BXDesktopWindow.SetProperty("closable", enabled);
	      return true;
	    }
	  }, {
	    key: "setWindowTitle",
	    value: function setWindowTitle(title) {
	      if (main_core.Type.isUndefined(title)) {
	        return false;
	      }
	      title = title.trim();
	      if (title.length <= 0) {
	        return false;
	      }
	      BXDesktopWindow.SetProperty("title", title);
	      return true;
	    }
	  }, {
	    key: "setWindowPosition",
	    value: function setWindowPosition(params) {
	      BXDesktopWindow.SetProperty("position", params);
	      return true;
	    }
	  }, {
	    key: "setWindowMinSize",
	    value: function setWindowMinSize(params) {
	      if (!params.Width || !params.Height) {
	        return false;
	      }
	      BXDesktopWindow.SetProperty("minClientSize", params);
	      return true;
	    }
	  }, {
	    key: "getHtmlPage",
	    value: function getHtmlPage(content, jsContent, initImJs) {
	      var bodyClass = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : '';
	      if (window.BXIM) {
	        return window.BXIM.desktop.getHtmlPage(content, jsContent, initImJs, bodyClass);
	      }
	      content = content || '';
	      jsContent = jsContent || '';
	      bodyClass = bodyClass || '';
	      if (main_core.Type.isDomNode(content)) {
	        content = content.outerHTML;
	      }
	      if (main_core.Type.isDomNode(jsContent)) {
	        jsContent = jsContent.outerHTML;
	      }
	      if (jsContent !== '') {
	        jsContent = '<script>BX.ready(function(){' + jsContent + '});</script>';
	      }
	      if (this.isPopupPageLoaded()) {
	        return '<div class="im-desktop im-desktop-popup ' + bodyClass + '">' + content + jsContent + '</div>';
	      } else {
	        if (this.htmlWrapperHead == null) {
	          this.htmlWrapperHead = document.head.outerHTML.replace(/BX\.PULL\.start\([^)]*\);/g, '');
	        }
	        return '<!DOCTYPE html><html>' + this.htmlWrapperHead + '<body class="im-desktop im-desktop-popup ' + bodyClass + '">' + content + jsContent + '</body></html>';
	      }
	    }
	  }, {
	    key: "isPopupPageLoaded",
	    value: function isPopupPageLoaded() {
	      if (!this.enableInVersion(45)) {
	        return false;
	      }
	      if (window.BXIM && !window.BXIM.isUtfMode) {
	        return false;
	      }
	      if (!BXInternals) {
	        return false;
	      }
	      if (!BXInternals.PopupTemplate) {
	        return false;
	      }
	      if (BXInternals.PopupTemplate === '#PLACEHOLDER#') {
	        return false;
	      }
	      return true;
	    }
	  }, {
	    key: "enableInVersion",
	    value: function enableInVersion(version) {
	      if (main_core.Type.isUndefined(BXDesktopSystem)) {
	        return false;
	      }
	      return this.getApiVersion() >= parseInt(version);
	    }
	  }, {
	    key: "getApiVersion",
	    value: function getApiVersion() {
	      if (main_core.Type.isUndefined(BXDesktopSystem)) {
	        return 0;
	      }
	      if (!this.clientVersion) {
	        this.clientVersion = BXDesktopSystem.GetProperty('versionParts');
	      }
	      return this.clientVersion[3];
	    }
	  }, {
	    key: "isReady",
	    value: function isReady() {
	      return typeof BXDesktopSystem != "undefined";
	    }
	  }]);
	  return Desktop;
	}();

	exports.Desktop = Desktop;

}((this.BX.Messenger.Lib = this.BX.Messenger.Lib || {}),BX));
//# sourceMappingURL=desktop.bundle.js.map
