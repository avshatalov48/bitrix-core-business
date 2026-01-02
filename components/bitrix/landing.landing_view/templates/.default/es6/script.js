/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_popup,landing_metrika,ui_designTokens,main_core,main_core_events,pull_client,landing_pageobject,landing_ui_highlight,landing_animation_copilot,landing_env,landing_history,landing_ui_panel_stylepanel) {
	'use strict';

	var Loc = /*#__PURE__*/function () {
	  function Loc() {
	    babelHelpers.classCallCheck(this, Loc);
	  }
	  babelHelpers.createClass(Loc, null, [{
	    key: "loadMessages",
	    value: function loadMessages(messages) {
	      Loc.messages = messages;
	    }
	  }, {
	    key: "getMessage",
	    value: function getMessage(code) {
	      return Loc.messages[code];
	    }
	  }]);
	  return Loc;
	}();

	var Devices = {
	  defaultDevice: {
	    tablet: 'iphone14pro',
	    mobile: 'iphone14pro'
	  },
	  devices: {
	    delimiter1: {
	      code: 'delimiter',
	      langCode: 'LANDING_PREVIEW_DEVICE_MOBILES'
	    },
	    iphone14pro: {
	      name: 'iPhone 14 Pro',
	      code: 'iphone14pro',
	      className: '--iphone-14-pro',
	      width: 393,
	      height: 852
	    },
	    iPhoneXR: {
	      name: 'iPhone XR',
	      code: 'iPhoneXR',
	      className: '--iphone-xr',
	      width: 414,
	      height: 896
	    },
	    iPhoneSE: {
	      name: 'iPhone SE',
	      code: 'iPhoneSE',
	      className: '--iphone-se',
	      width: 375,
	      height: 667
	    },
	    SamsungGalaxyNote10: {
	      name: 'Samsung Galaxy Note10',
	      code: 'SamsungGalaxyNote10',
	      className: '--samsung-galaxy-note10',
	      width: 412,
	      height: 896
	    },
	    SamsungGalaxyS8: {
	      name: 'Samsung Galaxy S8+',
	      code: 'SamsungGalaxyS8',
	      className: '--samsung-galaxy-s8-plus',
	      width: 360,
	      height: 740
	    },
	    GooglePixel4: {
	      name: 'Google Pixel 4',
	      code: 'GooglePixel4',
	      className: '--google-pixel-4',
	      width: 353,
	      height: 745
	    },
	    delimiter2: {
	      code: 'delimiter',
	      langCode: 'LANDING_PREVIEW_DEVICE_TABLETS'
	    },
	    iPad: {
	      name: 'iPad',
	      code: 'iPad',
	      className: '--ipad',
	      width: 810,
	      height: 1080
	    },
	    iPadMini: {
	      name: 'iPad Mini',
	      code: 'iPadMini',
	      className: '--ipad-mini',
	      width: 744,
	      height: 1133
	    },
	    SamsungGalaxyTabS8: {
	      name: 'Samsung Galaxy Tab S8',
	      code: 'SamsungGalaxyTabS8',
	      className: '--samsung-galaxy-tab-s8',
	      width: 800,
	      height: 1280
	    }
	  }
	};

	var _templateObject, _templateObject2, _templateObject3;
	var DeviceUI = /*#__PURE__*/function () {
	  function DeviceUI() {
	    babelHelpers.classCallCheck(this, DeviceUI);
	  }
	  babelHelpers.createClass(DeviceUI, null, [{
	    key: "getPreview",
	    /**
	     * Returns Landing Preview Block above the screen.
	     *
	     * @param {Options} options Preview options.
	     * @return {HTMLElement}
	     */
	    value: function getPreview(options) {
	      if (options.messages) {
	        DeviceUI.messages = options.messages;
	      }
	      if (!localStorage.getItem('deviceOrientation')) {
	        localStorage.setItem('deviceOrientation', 'portrait');
	      }
	      var rotateClick = function rotateClick() {
	        if (localStorage.getItem('deviceOrientation') === 'portrait') {
	          localStorage.setItem('deviceOrientation', 'landscape');
	        } else {
	          localStorage.setItem('deviceOrientation', 'portrait');
	        }
	        main_core.Dom.style(layout.wrapper, 'width', "".concat(layout.wrapper.offsetHeight, "px"));
	        main_core.Dom.style(layout.wrapper, 'height', "".concat(layout.wrapper.offsetWidth, "px"));
	        main_core.Dom.style(layout.frame, 'width', "".concat(layout.frame.offsetHeight, "px"));
	        main_core.Dom.style(layout.frame, 'height', "".concat(layout.frame.offsetWidth, "px"));
	        layout.wrapper.querySelector('[data-role="device-orientation"]').innerHTML = localStorage.getItem('deviceOrientation');
	      };
	      var hidden = localStorage.getItem('deviceHidden') === 'true';
	      var layout = {
	        wrapper: null,
	        rotate: main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-device-rotate\" onclick=\"", "\" data-role=\"landing-device-rotate\"></div>"])), rotateClick),
	        frame: main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<iframe data-role=\"landing-device-preview-iframe\" src=\"", "\"></iframe>"])), options.frameUrl)
	      };
	      layout.wrapper = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-device-wrapper", "\">\n\t\t\t\t<div class=\"landing-device-name\" onclick=\"", "\">\n\t\t\t\t\t<span data-role=\"device-name\">Device</span>\n\t\t\t\t\t<span data-role=\"device-orientation\" class=\"landing-device-orientation\">Orientation</span>\n\t\t\t\t</div>\n\t\t\t\t", "\n\t\t\t\t<div class=\"landing-device-preview\" data-role=\"landing-device-preview\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), hidden ? ' landing-device-wrapper-hidden' : '', options.clickHandler, layout.rotate, layout.frame);
	      return layout.wrapper;
	    }
	    /**
	     * Creates and open menu with list of devices.
	     *
	     * @param {HTMLElement} bindElement HTML element to bind position of menu.
	     * @param {Array<DeviceItem>} devices List of devices.
	     * @param {(device: DeviceItem) => {}} clickHandler Invokes when user clicked on the menu item.
	     */
	  }, {
	    key: "openDeviceMenu",
	    value: function openDeviceMenu(bindElement, devices, clickHandler) {
	      var menuId = 'device_selector';
	      var menu = main_popup.MenuManager.getMenuById(menuId);
	      if (menu) {
	        menu.show();
	        return;
	      }
	      var menuItems = [];
	      devices.forEach(function (device) {
	        if (device.code === 'delimiter') {
	          menuItems.push(new main_popup.MenuItem({
	            delimiter: true,
	            text: device.langCode ? DeviceUI.messages[device.langCode] : ''
	          }));
	          return;
	        }
	        menuItems.push(new main_popup.MenuItem({
	          id: device.className,
	          html: String(device.name),
	          onclick: function onclick() {
	            main_popup.MenuManager.getMenuById(menuId).close();
	            clickHandler(device);
	          }
	        }));
	      });
	      var bindNode = bindElement.parentNode || document.body;
	      menu = main_popup.MenuManager.create({
	        id: menuId,
	        bindElement: bindNode,
	        className: 'landing-ui-block-actions-popup',
	        items: menuItems,
	        offsetTop: 0,
	        offsetLeft: 40,
	        minWidth: bindNode.offsetWidth,
	        animation: 'fading-slide',
	        events: {
	          onPopupShow: function onPopupShow() {
	            menu.getPopupWindow().setMinWidth(bindNode.offsetWidth);
	          }
	        }
	      });
	      menu.show();
	    }
	  }]);
	  return DeviceUI;
	}();

	var _templateObject$1;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _options = /*#__PURE__*/new WeakMap();
	var _frameUrl = /*#__PURE__*/new WeakMap();
	var _editorFrameWrapper = /*#__PURE__*/new WeakMap();
	var _previewElement = /*#__PURE__*/new WeakMap();
	var _previewWindow = /*#__PURE__*/new WeakMap();
	var _previewLoader = /*#__PURE__*/new WeakMap();
	var _currentDevice = /*#__PURE__*/new WeakMap();
	var _editorEnabled = /*#__PURE__*/new WeakMap();
	var _pendingReload = /*#__PURE__*/new WeakMap();
	var _commandsToRefresh = /*#__PURE__*/new WeakMap();
	var _registerListeners = /*#__PURE__*/new WeakSet();
	var _backendAction = /*#__PURE__*/new WeakSet();
	var _reloadPreviewWindow = /*#__PURE__*/new WeakSet();
	var _scrollDevice = /*#__PURE__*/new WeakSet();
	var _resolveDeviceByType = /*#__PURE__*/new WeakSet();
	var _getPreviewNode = /*#__PURE__*/new WeakSet();
	var _setPreview = /*#__PURE__*/new WeakSet();
	var _removePreview = /*#__PURE__*/new WeakSet();
	var _setDevice = /*#__PURE__*/new WeakSet();
	var _adjustPreviewScroll = /*#__PURE__*/new WeakSet();
	var _buildPreview = /*#__PURE__*/new WeakSet();
	var _onClickDeviceSelector = /*#__PURE__*/new WeakSet();
	var _showPreview = /*#__PURE__*/new WeakSet();
	var _hidePreview = /*#__PURE__*/new WeakSet();
	var Device =
	// window object of iframe

	/**
	 * Device constructor.
	 *
	 * @param {Options} options Constructor options.
	 */
	function Device(_options2) {
	  babelHelpers.classCallCheck(this, Device);
	  _classPrivateMethodInitSpec(this, _hidePreview);
	  _classPrivateMethodInitSpec(this, _showPreview);
	  _classPrivateMethodInitSpec(this, _onClickDeviceSelector);
	  _classPrivateMethodInitSpec(this, _buildPreview);
	  _classPrivateMethodInitSpec(this, _adjustPreviewScroll);
	  _classPrivateMethodInitSpec(this, _setDevice);
	  _classPrivateMethodInitSpec(this, _removePreview);
	  _classPrivateMethodInitSpec(this, _setPreview);
	  _classPrivateMethodInitSpec(this, _getPreviewNode);
	  _classPrivateMethodInitSpec(this, _resolveDeviceByType);
	  _classPrivateMethodInitSpec(this, _scrollDevice);
	  _classPrivateMethodInitSpec(this, _reloadPreviewWindow);
	  _classPrivateMethodInitSpec(this, _backendAction);
	  _classPrivateMethodInitSpec(this, _registerListeners);
	  _classPrivateFieldInitSpec(this, _options, {
	    writable: true,
	    value: void 0
	  });
	  _classPrivateFieldInitSpec(this, _frameUrl, {
	    writable: true,
	    value: void 0
	  });
	  _classPrivateFieldInitSpec(this, _editorFrameWrapper, {
	    writable: true,
	    value: void 0
	  });
	  _classPrivateFieldInitSpec(this, _previewElement, {
	    writable: true,
	    value: void 0
	  });
	  _classPrivateFieldInitSpec(this, _previewWindow, {
	    writable: true,
	    value: void 0
	  });
	  _classPrivateFieldInitSpec(this, _previewLoader, {
	    writable: true,
	    value: void 0
	  });
	  _classPrivateFieldInitSpec(this, _currentDevice, {
	    writable: true,
	    value: null
	  });
	  _classPrivateFieldInitSpec(this, _editorEnabled, {
	    writable: true,
	    value: false
	  });
	  _classPrivateFieldInitSpec(this, _pendingReload, {
	    writable: true,
	    value: false
	  });
	  _classPrivateFieldInitSpec(this, _commandsToRefresh, {
	    writable: true,
	    value: ['Landing::upBlock', 'Landing::downBlock', 'Landing::showBlock', 'Landing::hideBlock', 'Landing::markDeletedBlock', 'Landing::addBlock', 'Landing::copyBlock', 'Landing::moveBlock', 'Block::changeNodeName', 'Block::updateContent', 'Block::getContent', 'Landing\\Block::addCard', 'Landing\\Block::cloneCard', 'Landing\\Block::removeCard', 'Landing\\Block::updateNodes', 'Landing\\Block::updateStyles', 'Landing\\Block::saveForm' // fake-action
	    ]
	  });

	  this.target = _options2.target || document.body;
	  babelHelpers.classPrivateFieldSet(this, _frameUrl, _options2.frameUrl);
	  babelHelpers.classPrivateFieldSet(this, _editorFrameWrapper, _options2.editorFrameWrapper);
	  babelHelpers.classPrivateFieldSet(this, _options, _options2);
	  _classPrivateMethodGet(this, _registerListeners, _registerListeners2).call(this, _options2);
	  _classPrivateMethodGet(this, _buildPreview, _buildPreview2).call(this, _options2);
	  _classPrivateMethodGet(this, _showPreview, _showPreview2).call(this);
	  _classPrivateMethodGet(this, _setDevice, _setDevice2).call(this, _classPrivateMethodGet(this, _resolveDeviceByType, _resolveDeviceByType2).call(this, 'mobile'));
	}

	/**
	 * Registers Handlers you need.
	 *
	 * @param {Options} options Constructor options.
	 */;
	function _registerListeners2(options) {
	  var _this = this;
	  // when user click different window size
	  BX.addCustomEvent('BX.Landing.Main:editorSizeChange', function (deviceType) {
	    _classPrivateMethodGet(_this, _setDevice, _setDevice2).call(_this, _classPrivateMethodGet(_this, _resolveDeviceByType, _resolveDeviceByType2).call(_this, deviceType));
	  });

	  // listen messages from editor frame
	  window.addEventListener('message', function (event) {
	    var data = event.data || {};
	    if (data.action === 'editorenable') {
	      if (!!data.payload.enable) {
	        babelHelpers.classPrivateFieldSet(_this, _editorEnabled, true);
	      } else {
	        if (babelHelpers.classPrivateFieldGet(_this, _pendingReload)) {
	          _classPrivateMethodGet(_this, _reloadPreviewWindow, _reloadPreviewWindow2).call(_this);
	        }
	        babelHelpers.classPrivateFieldSet(_this, _editorEnabled, false);
	        babelHelpers.classPrivateFieldSet(_this, _pendingReload, false);
	      }
	    } else if (data.action === 'backendaction') {
	      _classPrivateMethodGet(_this, _backendAction, _backendAction2).call(_this, data.payload);
	    }
	  });
	}
	function _backendAction2(payload) {
	  if (babelHelpers.classPrivateFieldGet(this, _commandsToRefresh).includes(payload.action)) {
	    if (babelHelpers.classPrivateFieldGet(this, _editorEnabled)) {
	      babelHelpers.classPrivateFieldSet(this, _pendingReload, true);
	    } else {
	      var _payload$data, _payload$data3, _payload$data3$update, _payload$data3$update2;
	      var blockId = null;
	      if ((_payload$data = payload.data) !== null && _payload$data !== void 0 && _payload$data.block) {
	        var _payload$data2;
	        blockId = (_payload$data2 = payload.data) === null || _payload$data2 === void 0 ? void 0 : _payload$data2.block;
	      }
	      if ((_payload$data3 = payload.data) !== null && _payload$data3 !== void 0 && (_payload$data3$update = _payload$data3.updateNodes) !== null && _payload$data3$update !== void 0 && (_payload$data3$update2 = _payload$data3$update.data) !== null && _payload$data3$update2 !== void 0 && _payload$data3$update2.block) {
	        var _payload$data4, _payload$data4$update, _payload$data4$update2;
	        blockId = (_payload$data4 = payload.data) === null || _payload$data4 === void 0 ? void 0 : (_payload$data4$update = _payload$data4.updateNodes) === null || _payload$data4$update === void 0 ? void 0 : (_payload$data4$update2 = _payload$data4$update.data) === null || _payload$data4$update2 === void 0 ? void 0 : _payload$data4$update2.block;
	      }
	      _classPrivateMethodGet(this, _reloadPreviewWindow, _reloadPreviewWindow2).call(this, blockId);
	    }
	  }
	}
	function _reloadPreviewWindow2(blockId) {
	  if (babelHelpers.classPrivateFieldGet(this, _previewWindow)) {
	    var blockIdPrefix = 'editor';
	    var timestamp = Date.now();
	    babelHelpers.classPrivateFieldGet(this, _previewWindow).location.href = babelHelpers.classPrivateFieldGet(this, _frameUrl) + '?ts=' + timestamp + '&scrollTo=' + blockIdPrefix + blockId;
	  }
	}
	function _scrollDevice2(topInPercent) {
	  if (babelHelpers.classPrivateFieldGet(this, _previewWindow)) {
	    var _document = babelHelpers.classPrivateFieldGet(this, _previewWindow).document;
	    var scrollHeight = Math.max(_document.body.scrollHeight, _document.documentElement.scrollHeight, _document.body.offsetHeight, _document.documentElement.offsetHeight, _document.body.clientHeight, _document.documentElement.clientHeight);
	    babelHelpers.classPrivateFieldGet(this, _previewWindow).scroll(0, scrollHeight * topInPercent / 100);
	  }
	}
	function _resolveDeviceByType2(deviceType) {
	  var _Devices$defaultDevic;
	  var deviceCode = localStorage.getItem('deviceCode');
	  if (deviceCode && Devices.devices[deviceCode]) {
	    return Devices.devices[deviceCode];
	  }
	  deviceCode = (_Devices$defaultDevic = Devices.defaultDevice) === null || _Devices$defaultDevic === void 0 ? void 0 : _Devices$defaultDevic[deviceType];
	  if (!deviceCode) {
	    return;
	  }
	  return Devices.devices[deviceCode];
	}
	function _getPreviewNode2() {
	  if (!babelHelpers.classPrivateFieldGet(this, _previewLoader)) {
	    Loc.loadMessages(babelHelpers.classPrivateFieldGet(this, _options).messages);
	    babelHelpers.classPrivateFieldSet(this, _previewLoader, main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-device-loader\">\n\t\t\t\t\t<div class=\"landing-device-loader-icon\"></div>\n\t\t\t\t\t<div class=\"landing-device-loader-text\">", "</div>\n\t\t\t\t</div>\n\t\t\t"])), Loc.getMessage('LANDING_TPL_PREVIEW_LOADING')));
	  }
	  return babelHelpers.classPrivateFieldGet(this, _previewLoader);
	}
	function _setPreview2(target) {
	  if (!target) {
	    return;
	  }
	  main_core.Dom.append(_classPrivateMethodGet(this, _getPreviewNode, _getPreviewNode2).call(this), target);
	}
	function _removePreview2() {
	  var _this2 = this;
	  main_core.Dom.addClass(_classPrivateMethodGet(this, _getPreviewNode, _getPreviewNode2).call(this), '--hide');
	  main_core.Event.bind(_classPrivateMethodGet(this, _getPreviewNode, _getPreviewNode2).call(this), 'transitionend', function () {
	    main_core.Dom.remove(_classPrivateMethodGet(_this2, _getPreviewNode, _getPreviewNode2).call(_this2));
	  });
	}
	function _setDevice2(newDevice) {
	  var _this3 = this;
	  if (!newDevice) {
	    return;
	  }
	  localStorage.setItem('deviceCode', newDevice.code);

	  // remove old class within preview
	  if (babelHelpers.classPrivateFieldGet(this, _currentDevice)) {
	    main_core.Dom.removeClass(babelHelpers.classPrivateFieldGet(this, _previewElement), babelHelpers.classPrivateFieldGet(this, _currentDevice).className);
	    babelHelpers.classPrivateFieldGet(this, _previewElement).style.removeProperty("top");
	  }
	  babelHelpers.classPrivateFieldSet(this, _currentDevice, newDevice);
	  babelHelpers.classPrivateFieldGet(this, _previewElement).querySelector('[data-role="device-name"]').innerHTML = newDevice.name;
	  babelHelpers.classPrivateFieldGet(this, _previewElement).querySelector('[data-role="device-orientation"]').innerHTML = localStorage.getItem('deviceOrientation');
	  var frame = babelHelpers.classPrivateFieldGet(this, _previewElement).querySelector('[data-role="landing-device-preview-iframe"]');
	  var frameWrapper = babelHelpers.classPrivateFieldGet(this, _previewElement).querySelector('[data-role="landing-device-preview"]');
	  frame.onload = function () {
	    return _classPrivateMethodGet(_this3, _removePreview, _removePreview2).call(_this3);
	  };

	  // scale for device
	  if (frame && frameWrapper && babelHelpers.classPrivateFieldGet(this, _currentDevice).width && babelHelpers.classPrivateFieldGet(this, _currentDevice).height) {
	    var scale = window.innerHeight / (babelHelpers.classPrivateFieldGet(this, _currentDevice).height + 300);
	    var padding = parseInt(window.getComputedStyle(frameWrapper).padding);
	    var param1 = babelHelpers.classPrivateFieldGet(this, _currentDevice).width;
	    var param2 = babelHelpers.classPrivateFieldGet(this, _currentDevice).height;
	    if (localStorage.getItem('deviceOrientation') === 'landscape') {
	      param1 = babelHelpers.classPrivateFieldGet(this, _currentDevice).height;
	      param2 = babelHelpers.classPrivateFieldGet(this, _currentDevice).width;
	    }
	    frame.style.setProperty("width", "".concat(param1, "px"));
	    frame.style.setProperty("height", "".concat(param2, "px"));
	    frameWrapper.style.setProperty("transform", "scale(".concat(scale, ")"));
	    babelHelpers.classPrivateFieldGet(this, _previewElement).style.setProperty("width", "".concat((param1 + padding * 2) * scale, "px"));
	    babelHelpers.classPrivateFieldGet(this, _previewElement).style.setProperty("height", "".concat((param2 + padding * 2) * scale, "px"));
	  }
	  main_core.Dom.addClass(babelHelpers.classPrivateFieldGet(this, _previewElement), babelHelpers.classPrivateFieldGet(this, _currentDevice).className);
	}
	function _adjustPreviewScroll2() {
	  var documentEditorFrame = babelHelpers.classPrivateFieldGet(this, _editorFrameWrapper).querySelector('iframe').contentWindow.document;
	  var scrollHeight = Math.max(documentEditorFrame.body.scrollHeight, documentEditorFrame.documentElement.scrollHeight, documentEditorFrame.body.offsetHeight, documentEditorFrame.documentElement.offsetHeight, documentEditorFrame.body.clientHeight, documentEditorFrame.documentElement.clientHeight);
	  var scrollTop = documentEditorFrame.documentElement.scrollTop || documentEditorFrame.body.scrollTop;
	  _classPrivateMethodGet(this, _scrollDevice, _scrollDevice2).call(this, scrollTop / scrollHeight * 100);
	}
	function _buildPreview2(options) {
	  if (!babelHelpers.classPrivateFieldGet(this, _previewElement)) {
	    babelHelpers.classPrivateFieldSet(this, _previewElement, DeviceUI.getPreview({
	      frameUrl: options.frameUrl,
	      clickHandler: _classPrivateMethodGet(this, _onClickDeviceSelector, _onClickDeviceSelector2).bind(this),
	      messages: options.messages
	    }));
	    main_core.Dom.hide(babelHelpers.classPrivateFieldGet(this, _previewElement));
	    this.target.appendChild(babelHelpers.classPrivateFieldGet(this, _previewElement));

	    // #170065
	    // this.#previewElement.querySelector('iframe').contentWindow.addEventListener('load', () => {
	    if (!babelHelpers.classPrivateFieldGet(this, _previewWindow)) {
	      babelHelpers.classPrivateFieldSet(this, _previewWindow, babelHelpers.classPrivateFieldGet(this, _previewElement).querySelector('iframe').contentWindow);
	      var previewDocument = babelHelpers.classPrivateFieldGet(this, _previewElement).querySelector('iframe').contentWindow.document;
	      main_core.Dom.removeClass(previewDocument.querySelector('html'), 'bx-no-touch');
	      main_core.Dom.addClass(previewDocument.querySelector('html'), 'bx-touch');
	    }
	    _classPrivateMethodGet(this, _adjustPreviewScroll, _adjustPreviewScroll2).call(this);
	    // });
	  }
	}
	function _onClickDeviceSelector2() {
	  DeviceUI.openDeviceMenu(babelHelpers.classPrivateFieldGet(this, _previewElement).querySelector('[data-role="device-name"]'), Object.values(Devices.devices), _classPrivateMethodGet(this, _setDevice, _setDevice2).bind(this));
	}
	function _showPreview2() {
	  main_core.Dom.show(babelHelpers.classPrivateFieldGet(this, _previewElement));
	  _classPrivateMethodGet(this, _setPreview, _setPreview2).call(this, babelHelpers.classPrivateFieldGet(this, _previewElement));
	}

	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _iframe = /*#__PURE__*/new WeakMap();
	var _postInternalCommand = /*#__PURE__*/new WeakSet();
	var Action = /*#__PURE__*/function () {
	  function Action(options) {
	    babelHelpers.classCallCheck(this, Action);
	    _classPrivateMethodInitSpec$1(this, _postInternalCommand);
	    _classPrivateFieldInitSpec$1(this, _iframe, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _iframe, options.iframe);
	    if (!main_core.Type.isDomNode(babelHelpers.classPrivateFieldGet(this, _iframe))) {
	      throw new Error("Missed 'frame' option as iFrame Element.");
	    }
	  }

	  /**
	   * Sends action with payload to child window.
	   *
	   * @param {string} action Command to internal iframe.
	   * @param {Object} payload Command's payload.
	   */
	  babelHelpers.createClass(Action, [{
	    key: "onDesignerBlockClick",
	    /**
	     * Handles on Designer click.
	     *
	     * @param {number} blockId Block id.
	     */
	    value: function onDesignerBlockClick(blockId) {
	      _classPrivateMethodGet$1(this, _postInternalCommand, _postInternalCommand2).call(this, 'onDesignerBlockClick', {
	        blockId: blockId
	      });
	    }
	    /**
	     * Handles on Style Block click.
	     *
	     * @param {number} blockId Block id.
	     */
	  }, {
	    key: "onStyleBlockClick",
	    value: function onStyleBlockClick(blockId) {
	      _classPrivateMethodGet$1(this, _postInternalCommand, _postInternalCommand2).call(this, 'onStyleBlockClick', {
	        blockId: blockId
	      });
	    }
	    /**
	     * Handles on Edit Block click.
	     *
	     * @param {number} blockId Block id.
	     */
	  }, {
	    key: "onEditBlockClick",
	    value: function onEditBlockClick(blockId) {
	      _classPrivateMethodGet$1(this, _postInternalCommand, _postInternalCommand2).call(this, 'onEditBlockClick', {
	        blockId: blockId
	      });
	    }
	    /**
	     * Handles on Down Block click.
	     *
	     * @param {number} blockId Block id.
	     */
	  }, {
	    key: "onSortDownBlockClick",
	    value: function onSortDownBlockClick(blockId) {
	      _classPrivateMethodGet$1(this, _postInternalCommand, _postInternalCommand2).call(this, 'onSortDownBlockClick', {
	        blockId: blockId
	      });
	    }
	    /**
	     * Handles on Up Block click.
	     *
	     * @param {number} blockId Block id.
	     */
	  }, {
	    key: "onSortUpBlockClick",
	    value: function onSortUpBlockClick(blockId) {
	      _classPrivateMethodGet$1(this, _postInternalCommand, _postInternalCommand2).call(this, 'onSortUpBlockClick', {
	        blockId: blockId
	      });
	    }
	    /**
	     * Handles on Remove Block click.
	     *
	     * @param {number} blockId Block id.
	     */
	  }, {
	    key: "onRemoveBlockClick",
	    value: function onRemoveBlockClick(blockId) {
	      _classPrivateMethodGet$1(this, _postInternalCommand, _postInternalCommand2).call(this, 'onRemoveBlockClick', {
	        blockId: blockId
	      });
	    }
	    /**
	     * Handles on Change State Block click.
	     *
	     * @param {number} blockId Block id.
	     */
	  }, {
	    key: "onChangeStateBlockClick",
	    value: function onChangeStateBlockClick(blockId) {
	      _classPrivateMethodGet$1(this, _postInternalCommand, _postInternalCommand2).call(this, 'onChangeStateBlockClick', {
	        blockId: blockId
	      });
	    }
	    /**
	     * Handles on Cut Block click.
	     *
	     * @param {number} blockId Block id.
	     */
	  }, {
	    key: "onCutBlockClick",
	    value: function onCutBlockClick(blockId) {
	      _classPrivateMethodGet$1(this, _postInternalCommand, _postInternalCommand2).call(this, 'onCutBlockClick', {
	        blockId: blockId
	      });
	    }
	    /**
	     * Handles on Copy Block click.
	     *
	     * @param {number} blockId Block id.
	     */
	  }, {
	    key: "onCopyBlockClick",
	    value: function onCopyBlockClick(blockId) {
	      _classPrivateMethodGet$1(this, _postInternalCommand, _postInternalCommand2).call(this, 'onCopyBlockClick', {
	        blockId: blockId
	      });
	    }
	    /**
	     * Handles on Paste Block click.
	     *
	     * @param {number} blockId Block id.
	     */
	  }, {
	    key: "onPasteBlockClick",
	    value: function onPasteBlockClick(blockId) {
	      _classPrivateMethodGet$1(this, _postInternalCommand, _postInternalCommand2).call(this, 'onPasteBlockClick', {
	        blockId: blockId
	      });
	    }
	    /**
	     * Handles on Feedback click.
	     *
	     * @param {number} blockId Block id.
	     */
	  }, {
	    key: "onFeedbackClick",
	    value: function onFeedbackClick(blockId) {
	      _classPrivateMethodGet$1(this, _postInternalCommand, _postInternalCommand2).call(this, 'onFeedbackClick', {
	        blockId: blockId
	      });
	    }
	    /**
	     * Handles on Save In Library click.
	     *
	     * @param {number} blockId Block id.
	     */
	  }, {
	    key: "onSaveInLibraryClick",
	    value: function onSaveInLibraryClick(blockId) {
	      _classPrivateMethodGet$1(this, _postInternalCommand, _postInternalCommand2).call(this, 'onSaveInLibraryClick', {
	        blockId: blockId
	      });
	    }
	    /**
	     * Hide opened editor panel.
	     */
	  }, {
	    key: "onHideEditorPanel",
	    value: function onHideEditorPanel() {
	      _classPrivateMethodGet$1(this, _postInternalCommand, _postInternalCommand2).call(this, 'onHideEditorPanel');
	    }
	  }]);
	  return Action;
	}();
	function _postInternalCommand2(action, payload) {
	  babelHelpers.classPrivateFieldGet(this, _iframe).contentWindow.postMessage({
	    action: action,
	    payload: payload
	  }, window.location.origin);
	}

	var _templateObject$2, _templateObject2$1, _templateObject3$1, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9;
	var UI = /*#__PURE__*/function () {
	  function UI() {
	    babelHelpers.classCallCheck(this, UI);
	  }
	  babelHelpers.createClass(UI, null, [{
	    key: "setPendingMenuItemValue",
	    /**
	     * Till Menu for this block not show, sets predefined prop value for menu item.
	     *
	     * @param {number} blockId
	     * @param {string} itemCode
	     * @param {string} itemProp
	     * @param {mixed} value
	     */
	    value: function setPendingMenuItemValue(blockId, itemCode, itemProp, value) {
	      if (!UI.pendingMenuItems[blockId]) {
	        UI.pendingMenuItems[blockId] = {};
	      }
	      if (!UI.pendingMenuItems[blockId][itemCode]) {
	        UI.pendingMenuItems[blockId][itemCode] = {};
	      }
	      UI.pendingMenuItems[blockId][itemCode][itemProp] = value;
	    }
	    /**
	     * Returns predefined prop value for menu item (if exists).
	     *
	     * @param {number} blockId
	     * @param {string} itemCode
	     * @param {string} itemProp
	     */
	  }, {
	    key: "getPendingMenuItemValue",
	    value: function getPendingMenuItemValue(blockId, itemCode, itemProp) {
	      if (UI.pendingMenuItems[blockId] && UI.pendingMenuItems[blockId][itemCode]) {
	        return UI.pendingMenuItems[blockId][itemCode][itemProp] || null;
	      }
	      return null;
	    }
	    /**
	     * Returns Designer Button.
	     *
	     * @param {() => {}} onClick Click handler.
	     * @return {HTMLButtonElement}
	     */
	  }, {
	    key: "getDesignerBlockButton",
	    value: function getDesignerBlockButton(onClick) {
	      var title = Loc.getMessage('LANDING_TPL_EXT_BUTTON_DESIGNER_BLOCK');
	      var button = main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button class=\"landing-ui-button landing-ui-button-action --separate", "\" type=\"button\" title=\"", "\">\n\t\t\t\t<span class=\"landing-ui-button-text\">", "</span>\n\t\t\t</button>\n\t\t"])), onClick ? '' : ' landing-ui-disabled', title, title);
	      if (onClick) {
	        main_core.Event.bind(button, 'click', onClick);
	      }
	      return button;
	    }
	    /**
	     * Returns Style Block Button.
	     *
	     * @param {() => {}} onClick Click handler.
	     * @return {HTMLButtonElement}
	     */
	  }, {
	    key: "getStyleBlockButton",
	    value: function getStyleBlockButton(onClick) {
	      var label = Loc.getMessage('LANDING_TPL_EXT_BUTTON_STYLE_BLOCK');
	      var title = Loc.getMessage('LANDING_TPL_EXT_BUTTON_STYLE_BLOCK_TITLE');
	      var button = main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button class=\"landing-ui-button landing-ui-button-action --separate", "\" type=\"button\" title=\"", "\">\n\t\t\t\t<span class=\"landing-ui-button-text\">", "</span>\n\t\t\t</button>\n\t\t"])), onClick ? '' : ' landing-ui-disabled', title, label);
	      if (onClick) {
	        main_core.Event.bind(button, 'click', onClick);
	      }
	      return button;
	    }
	    /**
	     * Returns Edit Block Button.
	     *
	     * @param {() => {}} onClick Click handler.
	     * @return {HTMLButtonElement}
	     */
	  }, {
	    key: "getEditBlockButton",
	    value: function getEditBlockButton(onClick) {
	      //data-id="content"
	      var label = Loc.getMessage('LANDING_TPL_EXT_BUTTON_EDIT_BLOCK');
	      var title = Loc.getMessage('LANDING_TPL_EXT_BUTTON_EDIT_BLOCK_TITLE');
	      var button = main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button class=\"landing-ui-button landing-ui-button-action --separate", "\" type=\"button\" title=\"", "\" data-id=\"content\">\n\t\t\t\t<span class=\"landing-ui-button-text\">", "</span>\n\t\t\t</button>\n\t\t"])), onClick ? '' : ' landing-ui-disabled', title, label);
	      if (onClick) {
	        main_core.Event.bind(button, 'click', onClick);
	      }
	      return button;
	    }
	    /**
	     * Returns left container for block's actions.
	     *
	     * @param {LeftContainerOptions} options Options for left container.
	     * @return {HTMLDivElement}
	     */
	  }, {
	    key: "getLeftContainer",
	    value: function getLeftContainer(options) {
	      return main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-external-left-container\">\n\t\t\t\t<div class=\"landing-ui-external-left-top-hr\"></div>\n\t\t\t\t<div class=\"landing-ui-external-body\">\n\t\t\t\t\t<div class=\"landing-ui-external-panel\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"landing-ui-external-left-bottom-hr\"></div>\n\t\t\t</div>\n\t\t"])), UI.getDesignerBlockButton(options.designerBlockClick), UI.getStyleBlockButton(options.styleBlockClick), UI.getEditBlockButton(options.editBlockClick));
	    }
	    /**
	     * Returns Sort Down Button.
	     *
	     * @param {() => {}} onClick Click handler.
	     * @return {HTMLButtonElement}
	     */
	  }, {
	    key: "getSortDownBlockButton",
	    value: function getSortDownBlockButton(onClick) {
	      var title = Loc.getMessage('LANDING_TPL_EXT_BUTTON_DOWN_BLOCK');
	      var button = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button class=\"landing-ui-button landing-ui-button-action", "\" type=\"button\" data-id=\"down\" title=\"", "\"><span class=\"landing-ui-button-text\">&nbsp;</span></button>\n\t\t"])), onClick ? '' : ' landing-ui-disabled', title);
	      if (onClick) {
	        main_core.Event.bind(button, 'click', onClick);
	      }
	      return button;
	    }
	    /**
	     * Returns Sort Up Button.
	     *
	     * @param {() => {}} onClick Click handler.
	     * @return {HTMLButtonElement}
	     */
	  }, {
	    key: "getSortUpBlockButton",
	    value: function getSortUpBlockButton(onClick) {
	      var title = Loc.getMessage('LANDING_TPL_EXT_BUTTON_UP_BLOCK');
	      var button = main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button class=\"landing-ui-button landing-ui-button-action", "\" type=\"button\" data-id=\"up\" title=\"", "\"><span class=\"landing-ui-button-text\">&nbsp;</span></button>\n\t\t"])), onClick ? '' : ' landing-ui-disabled', title);
	      if (onClick) {
	        main_core.Event.bind(button, 'click', onClick);
	      }
	      return button;
	    }
	    /**
	     * Returns Additional Items Menu for Block.
	     *
	     * @param {number} blockId Block id.
	     * @return {Menu}
	     */
	  }, {
	    key: "getBlockAdditionalMenu",
	    value: function getBlockAdditionalMenu(blockId) {
	      return main_popup.MenuManager.getMenuById('block_actions_' + blockId);
	    }
	    /**
	     * Closes Additional Items Menu for Block.
	     *
	     * @param {number} blockId Block id.
	     */
	  }, {
	    key: "closeBlockAdditionalMenu",
	    value: function closeBlockAdditionalMenu(blockId) {
	      var menu = UI.getBlockAdditionalMenu(blockId);
	      if (menu) {
	        menu.close();
	      }
	    }
	    /**
	     * Change state for Additional Menu Item 'Activate'.
	     *
	     * @param {number} blockId Block id.
	     * @param {boolean} state State.
	     */
	  }, {
	    key: "changeStateMenuItem",
	    value: function changeStateMenuItem(blockId, state) {
	      var menu = UI.getBlockAdditionalMenu(blockId);
	      var title = Loc.getMessage(!state ? 'LANDING_TPL_EXT_BUTTON_ACTIONS_SHOW' : 'LANDING_TPL_EXT_BUTTON_ACTIONS_HIDE');
	      if (menu) {
	        BX.Landing.Utils.setTextContent(menu.getMenuItem('show_hide').getLayout()['text'], title);
	      } else {
	        UI.setPendingMenuItemValue(blockId, 'show_hide', 'state', state);
	      }
	    }
	    /**
	     * Enables/disables paste-item.
	     *
	     * @param {number} blockId Block id.
	     * @param {boolean} enablePaste Flag.
	     */
	  }, {
	    key: "changePasteMenuItem",
	    value: function changePasteMenuItem(blockId, enablePaste) {
	      var menu = UI.getBlockAdditionalMenu(blockId);
	      if (menu) {
	        var item = menu.getMenuItem('paste');
	        if (item) {
	          if (enablePaste) {
	            item.enable();
	          } else {
	            item.disable();
	          }
	        }
	      } else {
	        UI.setPendingMenuItemValue(blockId, 'paste', 'disabled', !enablePaste);
	      }
	    }
	    /**
	     * Returns List of Actions for Block.
	     *
	     * @param {number} blockId Block id.
	     * @param {AdditionalActions} actions Additional actions for Block.
	     * @return {HTMLButtonElement}
	     */
	  }, {
	    key: "getActionsList",
	    value: function getActionsList(blockId, actions) {
	      var label = Loc.getMessage('LANDING_TPL_EXT_BUTTON_ACTIONS_BLOCK');
	      var title = Loc.getMessage('LANDING_TPL_EXT_BUTTON_ACTIONS_BLOCK_TITLE');
	      var actionButton = main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button class=\"landing-ui-button landing-ui-button-action\" type=\"button\" data-id=\"actions\" title=\"", "\">\n\t\t\t\t<span class=\"landing-ui-button-text\">", "</span>\n\t\t\t</button>\n\t\t"])), title, label);

	      // when click is occurred open exists menu or create new one
	      main_core.Event.bind(actionButton, 'click', function (event) {
	        if (actions.onOpenAdditionalMenu) {
	          actions.onOpenAdditionalMenu(blockId);
	          event.stopPropagation();
	        }
	        var menu = UI.getBlockAdditionalMenu(blockId);
	        if (menu) {
	          menu.show();
	          return;
	        }
	        main_popup.MenuManager.create({
	          id: 'block_actions_' + blockId,
	          bindElement: actionButton,
	          className: 'landing-ui-block-actions-popup',
	          angle: {
	            position: 'top',
	            offset: 95
	          },
	          offsetTop: -6,
	          offsetLeft: -26,
	          items: [new main_popup.MenuItem({
	            id: 'show_hide',
	            disabled: !actions.changeStateClick,
	            text: Loc.getMessage(actions.state || UI.getPendingMenuItemValue(blockId, 'show_hide', 'state') ? 'LANDING_TPL_EXT_BUTTON_ACTIONS_HIDE' : 'LANDING_TPL_EXT_BUTTON_ACTIONS_SHOW'),
	            onclick: function onclick() {
	              actions.changeStateClick();
	            }
	          }), new main_popup.MenuItem({
	            id: 'cut',
	            disabled: !actions.cutClick,
	            text: Loc.getMessage('LANDING_TPL_EXT_BUTTON_ACTIONS_CUT'),
	            onclick: function onclick() {
	              actions.cutClick();
	            }
	          }), new main_popup.MenuItem({
	            id: 'copy',
	            disabled: !actions.copyClick,
	            text: Loc.getMessage('LANDING_TPL_EXT_BUTTON_ACTIONS_COPY'),
	            onclick: function onclick() {
	              actions.copyClick();
	            }
	          }), new main_popup.MenuItem({
	            id: 'paste',
	            disabled: !actions.pasteClick || UI.getPendingMenuItemValue(blockId, 'paste', 'disabled'),
	            text: Loc.getMessage('LANDING_TPL_EXT_BUTTON_ACTIONS_PASTE'),
	            onclick: function onclick() {
	              actions.pasteClick();
	            }
	          }), new main_popup.MenuItem({
	            id: 'feedback',
	            disabled: !actions.feedbackClick,
	            text: Loc.getMessage('LANDING_TPL_EXT_BUTTON_ACTIONS_FEEDBACK'),
	            onclick: function onclick() {
	              actions.feedbackClick();
	            }
	          }), actions.saveInLibrary ? new main_popup.MenuItem({
	            delimiter: true
	          }) : null, new main_popup.MenuItem({
	            id: 'save_in_library',
	            disabled: !actions.saveInLibrary,
	            text: Loc.getMessage('LANDING_TPL_EXT_BUTTON_ACTIONS_SAVE_IN_LIBRARY'),
	            onclick: function onclick() {
	              actions.saveInLibrary();
	            }
	          })]
	        }).show();
	      });
	      return actionButton;
	    }
	    /**
	     * Returns Remove Button.
	     *
	     * @param {() => {}} onClick Click handler.
	     * @return {HTMLButtonElement}
	     */
	  }, {
	    key: "getRemoveBlockButton",
	    value: function getRemoveBlockButton(onClick) {
	      var title = Loc.getMessage('LANDING_TPL_EXT_BUTTON_REMOVE_BLOCK');
	      var button = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button class=\"landing-ui-button landing-ui-button-action", "\" type=\"button\" data-id=\"remove\" title=\"", "\"><span class=\"landing-ui-button-text\">&nbsp;</span></button>\n\t\t"])), onClick ? '' : ' landing-ui-disabled', title);
	      if (onClick) {
	        main_core.Event.bind(button, 'click', onClick);
	      }
	      return button;
	    }
	    /**
	     * Returns right container for block's actions.
	     *
	     * @param {RightContainerOptions} options Options for right container.
	     * @return {HTMLDivElement}
	     */
	  }, {
	    key: "getRightContainer",
	    value: function getRightContainer(options) {
	      return main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-external-right-container\">\n\t\t\t\t<div class=\"landing-ui-external-right-top-hr\"></div>\n\t\t\t\t<div class=\"landing-ui-external-body\">\n\t\t\t\t\t<div class=\"landing-ui-external-panel\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"landing-ui-external-right-bottom-hr\"></div>\n\t\t\t</div>\n\t\t"])), UI.getSortDownBlockButton(options.sortDownBlockClick), UI.getSortUpBlockButton(options.sortUpBlockClick), UI.getActionsList(options.blockId, options), UI.getRemoveBlockButton(options.removeBlockClick));
	    }
	  }]);
	  return UI;
	}();
	babelHelpers.defineProperty(UI, "pendingMenuItems", {});

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function _classPrivateMethodInitSpec$2(obj, privateSet) { _checkPrivateRedeclaration$2(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$2(obj, privateMap, value) { _checkPrivateRedeclaration$2(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$2(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$2(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _action = /*#__PURE__*/new WeakMap();
	var _externalBlocks = /*#__PURE__*/new WeakMap();
	var _container = /*#__PURE__*/new WeakMap();
	var _iframeWrapper = /*#__PURE__*/new WeakMap();
	var _currentOpenBlockId = /*#__PURE__*/new WeakMap();
	var _currentOpenMenuBlock = /*#__PURE__*/new WeakMap();
	var _listenChildFrame = /*#__PURE__*/new WeakSet();
	var _onStorageChange = /*#__PURE__*/new WeakSet();
	var _registerBlocks = /*#__PURE__*/new WeakSet();
	var _getBlock = /*#__PURE__*/new WeakSet();
	var _updateBlock = /*#__PURE__*/new WeakSet();
	var _changeState = /*#__PURE__*/new WeakSet();
	var _hideAllControls = /*#__PURE__*/new WeakSet();
	var _showControls = /*#__PURE__*/new WeakSet();
	var _hideAndShowControls = /*#__PURE__*/new WeakSet();
	var _onChangeMode = /*#__PURE__*/new WeakSet();
	var ExternalControls = function ExternalControls(options) {
	  var _this = this;
	  babelHelpers.classCallCheck(this, ExternalControls);
	  _classPrivateMethodInitSpec$2(this, _onChangeMode);
	  _classPrivateMethodInitSpec$2(this, _hideAndShowControls);
	  _classPrivateMethodInitSpec$2(this, _showControls);
	  _classPrivateMethodInitSpec$2(this, _hideAllControls);
	  _classPrivateMethodInitSpec$2(this, _changeState);
	  _classPrivateMethodInitSpec$2(this, _updateBlock);
	  _classPrivateMethodInitSpec$2(this, _getBlock);
	  _classPrivateMethodInitSpec$2(this, _registerBlocks);
	  _classPrivateMethodInitSpec$2(this, _onStorageChange);
	  _classPrivateMethodInitSpec$2(this, _listenChildFrame);
	  _classPrivateFieldInitSpec$2(this, _action, {
	    writable: true,
	    value: void 0
	  });
	  _classPrivateFieldInitSpec$2(this, _externalBlocks, {
	    writable: true,
	    value: void 0
	  });
	  _classPrivateFieldInitSpec$2(this, _container, {
	    writable: true,
	    value: void 0
	  });
	  _classPrivateFieldInitSpec$2(this, _iframeWrapper, {
	    writable: true,
	    value: void 0
	  });
	  _classPrivateFieldInitSpec$2(this, _currentOpenBlockId, {
	    writable: true,
	    value: void 0
	  });
	  _classPrivateFieldInitSpec$2(this, _currentOpenMenuBlock, {
	    writable: true,
	    value: void 0
	  });
	  options = options || {};
	  babelHelpers.classPrivateFieldSet(this, _container, options.container);
	  babelHelpers.classPrivateFieldSet(this, _iframeWrapper, options.iframeWrapper);
	  if (!main_core.Type.isDomNode(babelHelpers.classPrivateFieldGet(this, _container))) {
	    throw new Error("Missed 'container' option as Dom Node.");
	  }
	  if (!main_core.Type.isDomNode(babelHelpers.classPrivateFieldGet(this, _iframeWrapper))) {
	    throw new Error("Missed 'iframe' option as Dom Node.");
	  }
	  babelHelpers.classPrivateFieldSet(this, _externalBlocks, new Map());
	  babelHelpers.classPrivateFieldSet(this, _action, new Action({
	    iframe: babelHelpers.classPrivateFieldGet(this, _iframeWrapper).querySelector('iframe')
	  }));
	  Loc.loadMessages(options.messages);
	  window.addEventListener('message', _classPrivateMethodGet$2(this, _listenChildFrame, _listenChildFrame2).bind(this));
	  babelHelpers.classPrivateFieldGet(this, _container).addEventListener('click', function (event) {
	    babelHelpers.classPrivateFieldGet(_this, _action).onHideEditorPanel();
	  });
	  window.addEventListener('storage', _classPrivateMethodGet$2(this, _onStorageChange, _onStorageChange2).bind(this));
	}

	/**
	 * Handler on listening child iframe commands.
	 *
	 * @param event
	 */;
	function _listenChildFrame2(event) {
	  var data = event.data || {};
	  if (!data.payload) {
	    return;
	  }
	  if (data.action === 'register') {
	    _classPrivateMethodGet$2(this, _registerBlocks, _registerBlocks2).call(this, data.payload.blocks);
	  } else if (data.action === 'showcontrols') {
	    _classPrivateMethodGet$2(this, _showControls, _showControls2).call(this, data.payload.blockId, data.payload.top, data.payload.height);
	  } else if (data.action === 'changestate') {
	    _classPrivateMethodGet$2(this, _changeState, _changeState2).call(this, data.payload.blockId, data.payload.state);
	  } else if (data.action === 'mode') {
	    _classPrivateMethodGet$2(this, _onChangeMode, _onChangeMode2).call(this, data.payload);
	  } else if (data.action === 'hideall') {
	    _classPrivateMethodGet$2(this, _hideAllControls, _hideAllControls2).call(this);
	  } else if (data.action === 'showblockcontrols') {
	    _classPrivateMethodGet$2(this, _hideAllControls, _hideAllControls2).call(this);
	    _classPrivateMethodGet$2(this, _hideAndShowControls, _hideAndShowControls2).call(this, data.payload.blockId);
	  }
	}
	function _onStorageChange2() {
	  var blocks = babelHelpers.classPrivateFieldGet(this, _externalBlocks).values();
	  var allowPaste = !!window.localStorage.getItem('landingBlockId');
	  for (var i = 0, c = babelHelpers.classPrivateFieldGet(this, _externalBlocks).size; i < c; i++) {
	    var blockItem = blocks.next().value;
	    UI.changePasteMenuItem(blockItem.id, allowPaste);
	    _classPrivateMethodGet$2(this, _updateBlock, _updateBlock2).call(this, blockItem.id, _objectSpread(_objectSpread({}, blockItem), {}, {
	      permissions: _objectSpread(_objectSpread({}, blockItem.permissions), {}, {
	        allowPaste: allowPaste
	      })
	    }));
	  }
	}
	function _registerBlocks2(blocks) {
	  var _this2 = this;
	  blocks.map(function (block) {
	    var blockId = block.id;

	    // left controls
	    block.leftContainer = UI.getLeftContainer({
	      designerBlockClick: block.permissions.allowDesignBlock ? function () {
	        babelHelpers.classPrivateFieldGet(_this2, _action).onDesignerBlockClick(blockId);
	      } : null,
	      styleBlockClick: block.permissions.allowModifyStyles ? function () {
	        babelHelpers.classPrivateFieldGet(_this2, _action).onStyleBlockClick(blockId);
	      } : null,
	      editBlockClick: block.permissions.allowEditContent ? function () {
	        babelHelpers.classPrivateFieldGet(_this2, _action).onEditBlockClick(blockId);
	      } : null
	    });

	    // right controls
	    block.rightContainer = UI.getRightContainer({
	      blockId: blockId,
	      state: block.state,
	      sortDownBlockClick: block.permissions.allowSorting ? function () {
	        babelHelpers.classPrivateFieldGet(_this2, _action).onSortDownBlockClick(blockId);
	        _classPrivateMethodGet$2(_this2, _hideAllControls, _hideAllControls2).call(_this2);
	      } : null,
	      sortUpBlockClick: block.permissions.allowSorting ? function () {
	        babelHelpers.classPrivateFieldGet(_this2, _action).onSortUpBlockClick(blockId);
	        _classPrivateMethodGet$2(_this2, _hideAllControls, _hideAllControls2).call(_this2);
	      } : null,
	      removeBlockClick: block.permissions.allowRemove ? function () {
	        babelHelpers.classPrivateFieldGet(_this2, _action).onRemoveBlockClick(blockId);
	        _classPrivateMethodGet$2(_this2, _hideAllControls, _hideAllControls2).call(_this2);
	      } : null,
	      onOpenAdditionalMenu: function onOpenAdditionalMenu(blockId) {
	        babelHelpers.classPrivateFieldSet(_this2, _currentOpenMenuBlock, blockId);
	        setTimeout(function () {
	          babelHelpers.classPrivateFieldGet(_this2, _action).onHideEditorPanel();
	        }, 0);
	      },
	      changeStateClick: block.permissions.allowChangeState ? function () {
	        UI.closeBlockAdditionalMenu(blockId);
	        babelHelpers.classPrivateFieldGet(_this2, _action).onChangeStateBlockClick(blockId);
	        _classPrivateMethodGet$2(_this2, _hideAndShowControls, _hideAndShowControls2).call(_this2, blockId);
	      } : null,
	      cutClick: block.permissions.allowRemove ? function () {
	        babelHelpers.classPrivateFieldGet(_this2, _action).onCutBlockClick(blockId);
	        _classPrivateMethodGet$2(_this2, _hideAllControls, _hideAllControls2).call(_this2);
	      } : null,
	      copyClick: function copyClick() {
	        UI.closeBlockAdditionalMenu(blockId);
	        babelHelpers.classPrivateFieldGet(_this2, _action).onCopyBlockClick(blockId);
	        _classPrivateMethodGet$2(_this2, _hideAndShowControls, _hideAndShowControls2).call(_this2, blockId);
	      },
	      pasteClick: function pasteClick() {
	        UI.closeBlockAdditionalMenu(blockId);
	        babelHelpers.classPrivateFieldGet(_this2, _action).onPasteBlockClick(blockId);
	        _classPrivateMethodGet$2(_this2, _hideAndShowControls, _hideAndShowControls2).call(_this2, blockId);
	      },
	      feedbackClick: function feedbackClick() {
	        UI.closeBlockAdditionalMenu(blockId);
	        babelHelpers.classPrivateFieldGet(_this2, _action).onFeedbackClick(blockId);
	      },
	      saveInLibrary: block.permissions.allowSaveInLibrary ? function () {
	        UI.closeBlockAdditionalMenu(blockId);
	        babelHelpers.classPrivateFieldGet(_this2, _action).onSaveInLibraryClick(blockId);
	      } : null
	    });
	    main_core.Dom.append(block.leftContainer, babelHelpers.classPrivateFieldGet(_this2, _container));
	    main_core.Dom.append(block.rightContainer, babelHelpers.classPrivateFieldGet(_this2, _container));
	    main_core.Dom.hide(block.leftContainer);
	    main_core.Dom.hide(block.rightContainer);
	    babelHelpers.classPrivateFieldGet(_this2, _externalBlocks).set(blockId, block);
	  });
	}
	function _getBlock2(blockId) {
	  return babelHelpers.classPrivateFieldGet(this, _externalBlocks).get(parseInt(blockId));
	}
	function _updateBlock2(blockId, data) {
	  babelHelpers.classPrivateFieldGet(this, _externalBlocks).set(parseInt(blockId), data);
	}
	function _changeState2(blockId, state) {
	  var block = _classPrivateMethodGet$2(this, _getBlock, _getBlock2).call(this, blockId);
	  if (block) {
	    UI.changeStateMenuItem(blockId, state);
	    _classPrivateMethodGet$2(this, _updateBlock, _updateBlock2).call(this, blockId, _objectSpread(_objectSpread({}, block), {}, {
	      state: state
	    }));
	  }
	}
	function _hideAllControls2() {
	  if (babelHelpers.classPrivateFieldGet(this, _currentOpenBlockId)) {
	    var blockItem = babelHelpers.classPrivateFieldGet(this, _externalBlocks).get(babelHelpers.classPrivateFieldGet(this, _currentOpenBlockId));
	    main_core.Dom.hide(blockItem.leftContainer);
	    main_core.Dom.hide(blockItem.rightContainer);
	  } else {
	    var blocks = babelHelpers.classPrivateFieldGet(this, _externalBlocks).values();
	    for (var i = 0, c = babelHelpers.classPrivateFieldGet(this, _externalBlocks).size; i < c; i++) {
	      var _blockItem = blocks.next().value;
	      main_core.Dom.hide(_blockItem.leftContainer);
	      main_core.Dom.hide(_blockItem.rightContainer);
	    }
	  }

	  // if some menu is opened, close it
	  if (babelHelpers.classPrivateFieldGet(this, _currentOpenMenuBlock)) {
	    UI.closeBlockAdditionalMenu(babelHelpers.classPrivateFieldGet(this, _currentOpenMenuBlock));
	    babelHelpers.classPrivateFieldSet(this, _currentOpenMenuBlock, null);
	  }
	}
	function _showControls2(blockId, top, height) {
	  var block = _classPrivateMethodGet$2(this, _getBlock, _getBlock2).call(this, blockId);
	  if (!block) {
	    return;
	  }
	  var iframeRect = babelHelpers.classPrivateFieldGet(this, _iframeWrapper).getBoundingClientRect();
	  _classPrivateMethodGet$2(this, _hideAllControls, _hideAllControls2).call(this);
	  babelHelpers.classPrivateFieldSet(this, _currentOpenBlockId, block.id);
	  babelHelpers.classPrivateFieldGet(this, _action).onHideEditorPanel();
	  top = parseInt(top);

	  // adjust top and bottom borders
	  if (top < 0 && height + top > 50) {
	    height = height + top;
	    top = 0;
	    main_core.Dom.addClass(block.leftContainer, 'hide-top');
	    main_core.Dom.addClass(block.rightContainer, 'hide-top');
	  } else {
	    main_core.Dom.removeClass(block.leftContainer, 'hide-top');
	    main_core.Dom.removeClass(block.rightContainer, 'hide-top');
	  }
	  main_core.Dom.show(block.leftContainer);
	  main_core.Dom.show(block.rightContainer);

	  // adjust top and heights
	  block.leftContainer.style.width = iframeRect.left + 'px';
	  block.leftContainer.style.top = top + 'px';
	  block.leftContainer.style.height = height + 'px';
	  block.rightContainer.style.width = iframeRect.left + 'px';
	  block.rightContainer.style.left = iframeRect.left + iframeRect.width + 'px';
	  block.rightContainer.style.top = top + 'px';
	  block.rightContainer.style.height = height + 'px';
	}
	function _hideAndShowControls2(blockId) {
	  var _this3 = this;
	  var block = _classPrivateMethodGet$2(this, _getBlock, _getBlock2).call(this, blockId);
	  if (block) {
	    babelHelpers.classPrivateFieldSet(this, _currentOpenBlockId, null);
	    main_core.Dom.hide(block.leftContainer);
	    main_core.Dom.hide(block.rightContainer);
	    setTimeout(function () {
	      babelHelpers.classPrivateFieldSet(_this3, _currentOpenBlockId, block.id);
	      main_core.Dom.show(block.leftContainer);
	      main_core.Dom.show(block.rightContainer);
	    }, 500);
	  }
	}
	function _onChangeMode2(data) {
	  if (data.type === 'internal') {
	    _classPrivateMethodGet$2(this, _hideAllControls, _hideAllControls2).call(this);
	  }
	}

	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _regeneratorRuntime() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == babelHelpers["typeof"](value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
	function _classPrivateMethodInitSpec$3(obj, privateSet) { _checkPrivateRedeclaration$3(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$3(obj, privateMap, value) { _checkPrivateRedeclaration$3(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$3(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$3(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _chatId = /*#__PURE__*/new WeakMap();
	var _copilotChat = /*#__PURE__*/new WeakMap();
	var _animationCopilot = /*#__PURE__*/new WeakMap();
	var _copilotChatEvents = /*#__PURE__*/new WeakMap();
	var _copilotChatMessageType = /*#__PURE__*/new WeakMap();
	var _generationId = /*#__PURE__*/new WeakMap();
	var _isGenerationProcessed = /*#__PURE__*/new WeakMap();
	var _blocks = /*#__PURE__*/new WeakMap();
	var _selectMode = /*#__PURE__*/new WeakMap();
	var _selectedBlock = /*#__PURE__*/new WeakMap();
	var _isSelectedBlockGeneratable = /*#__PURE__*/new WeakMap();
	var _editorWindow = /*#__PURE__*/new WeakMap();
	var _highlight = /*#__PURE__*/new WeakMap();
	var _highlightSelectable = /*#__PURE__*/new WeakMap();
	var _subscribePullEvents = /*#__PURE__*/new WeakSet();
	var _bindBlocksSelectEvents = /*#__PURE__*/new WeakSet();
	var _unbindBlocksSelectEvents = /*#__PURE__*/new WeakSet();
	var BlockGenerator = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(BlockGenerator, _EventEmitter);
	  babelHelpers.createClass(BlockGenerator, null, [{
	    key: "create",
	    value: function () {
	      var _create = babelHelpers.asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee(options) {
	        var generator;
	        return _regeneratorRuntime().wrap(function _callee$(_context) {
	          while (1) switch (_context.prev = _context.next) {
	            case 0:
	              if (!(!options.copilotChat || !options.chatId)) {
	                _context.next = 2;
	                break;
	              }
	              return _context.abrupt("return", null);
	            case 2:
	              generator = new BlockGenerator(options);
	              _context.next = 5;
	              return generator.init();
	            case 5:
	              return _context.abrupt("return", generator);
	            case 6:
	            case "end":
	              return _context.stop();
	          }
	        }, _callee);
	      }));
	      function create(_x) {
	        return _create.apply(this, arguments);
	      }
	      return create;
	    }()
	  }]);
	  function BlockGenerator(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, BlockGenerator);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BlockGenerator).call(this));
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _unbindBlocksSelectEvents);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _bindBlocksSelectEvents);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _subscribePullEvents);
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _chatId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _copilotChat, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _animationCopilot, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _copilotChatEvents, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _copilotChatMessageType, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _generationId, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _isGenerationProcessed, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _blocks, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _selectMode, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _selectedBlock, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _isSelectedBlockGeneratable, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _editorWindow, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _highlight, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _highlightSelectable, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Landing.View.BlockGenerator');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _blocks, new Map());
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _selectedBlock, null);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _isSelectedBlockGeneratable, false);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _highlight, new landing_ui_highlight.Highlight().setBorder(BlockGenerator.highlightColor, BlockGenerator.highlightWidth).setBackground('transparent').setSingleMode());
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _highlightSelectable, new landing_ui_highlight.Highlight().setBorder(BlockGenerator.highlightColor, 0).setBackground(BlockGenerator.highlightBackground).setSingleMode());
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _chatId, options.chatId);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _copilotChat, options.copilotChat);
	    _this.onBlockMouseEnter = _this.onBlockMouseEnter.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onBlockClick = _this.onBlockClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onChatMessage = _this.onChatMessage.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onEditorLoad = _this.onEditorLoad.bind(babelHelpers.assertThisInitialized(_this));
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _editorWindow, landing_pageobject.PageObject.getEditorWindow());
	    if (!babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _editorWindow)) {
	      console.error('Can not load editor window');
	    }
	    if (babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _editorWindow).document.readyState === 'complete') {
	      _this.onEditorLoad();
	    } else {
	      main_core.Event.bind(babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _editorWindow), 'load', _this.onEditorLoad);
	    }
	    return _this;
	  }
	  babelHelpers.createClass(BlockGenerator, [{
	    key: "init",
	    value: function () {
	      var _init = babelHelpers.asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee2() {
	        var exports;
	        return _regeneratorRuntime().wrap(function _callee2$(_context2) {
	          while (1) switch (_context2.prev = _context2.next) {
	            case 0:
	              _context2.next = 2;
	              return main_core.Runtime.loadExtension('landing.copilot.chat');
	            case 2:
	              exports = _context2.sent;
	              babelHelpers.classPrivateFieldSet(this, _copilotChatEvents, exports.CopilotChatEvents);
	              babelHelpers.classPrivateFieldSet(this, _copilotChatMessageType, exports.CopilotChatMessageType);
	            case 5:
	            case "end":
	              return _context2.stop();
	          }
	        }, _callee2, this);
	      }));
	      function init() {
	        return _init.apply(this, arguments);
	      }
	      return init;
	    }()
	  }, {
	    key: "onEditorLoad",
	    value: function onEditorLoad() {
	      var _this2 = this;
	      var blocks = landing_pageobject.PageObject.getBlocks();
	      blocks.forEach(function (block) {
	        babelHelpers.classPrivateFieldGet(_this2, _blocks).set(block.id, {
	          id: block.id,
	          anchor: block.anchor,
	          content: block.content
	        });
	      });
	      babelHelpers.classPrivateFieldGet(this, _editorWindow).BX.addCustomEvent(babelHelpers.classPrivateFieldGet(this, _editorWindow), 'BX.Landing.Block:init', function (event) {
	        _this2.onBlockAdd(event.blockId, event.block.id, event.content);
	      });
	      if (babelHelpers.classPrivateFieldGet(this, _selectMode)) {
	        _classPrivateMethodGet$3(this, _bindBlocksSelectEvents, _bindBlocksSelectEvents2).call(this);
	      }
	    }
	  }, {
	    key: "onBlockAdd",
	    value: function onBlockAdd(blockId, anchor, content) {
	      babelHelpers.classPrivateFieldGet(this, _blocks).set(blockId, {
	        id: blockId,
	        anchor: anchor,
	        content: content
	      });
	      if (babelHelpers.classPrivateFieldGet(this, _selectMode)) {
	        _classPrivateMethodGet$3(this, _bindBlocksSelectEvents, _bindBlocksSelectEvents2).call(this);
	      }
	    }
	  }, {
	    key: "onBlockClick",
	    value: function onBlockClick(event) {
	      var _this3 = this;
	      var target = event.currentTarget;
	      var selectedBlock = null;
	      var _iterator = _createForOfIteratorHelper(babelHelpers.classPrivateFieldGet(this, _blocks).values()),
	        _step;
	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var block = _step.value;
	          if (target === block.content) {
	            selectedBlock = block;
	          }
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }
	      var chatId = babelHelpers.classPrivateFieldGet(this, _chatId);
	      if (selectedBlock === null) {
	        return;
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _selectedBlock) !== null && babelHelpers.classPrivateFieldGet(this, _selectedBlock).id === selectedBlock.id) {
	        return;
	      }
	      babelHelpers.classPrivateFieldSet(this, _selectedBlock, selectedBlock);
	      main_core.ajax.runAction('landing.api.copilot.checkBlockGeneratable', {
	        data: {
	          blockId: selectedBlock.id,
	          chatId: chatId
	        }
	      }).then(function (result) {
	        if (result.data) {
	          babelHelpers.classPrivateFieldSet(_this3, _isSelectedBlockGeneratable, true);
	          babelHelpers.classPrivateFieldGet(_this3, _highlightSelectable).show(babelHelpers.classPrivateFieldGet(_this3, _selectedBlock).content);
	        } else {
	          babelHelpers.classPrivateFieldSet(_this3, _isSelectedBlockGeneratable, false);
	          babelHelpers.classPrivateFieldGet(_this3, _highlightSelectable).hide();
	        }
	      })["catch"](function () {
	        babelHelpers.classPrivateFieldSet(_this3, _isSelectedBlockGeneratable, false);
	        babelHelpers.classPrivateFieldGet(_this3, _highlightSelectable).hide();
	      });
	    }
	  }, {
	    key: "onChatMessage",
	    value: function onChatMessage(event) {
	      var _event$getData,
	        _this4 = this;
	      var message = (_event$getData = event.getData()) === null || _event$getData === void 0 ? void 0 : _event$getData.message;
	      if (message && (message.authorId || 0) > 0 && message.type === babelHelpers.classPrivateFieldGet(this, _copilotChatMessageType).DEFAULT) {
	        if (babelHelpers.classPrivateFieldGet(this, _selectedBlock) === null) {
	          main_core.ajax.runAction('landing.api.copilot.sendBlockGenerationNeedSelectMessage', {
	            data: {
	              siteId: landing_env.Env.getInstance().getSiteId()
	            }
	          });
	          return;
	        }
	        if (!babelHelpers.classPrivateFieldGet(this, _isSelectedBlockGeneratable)) {
	          main_core.ajax.runAction('landing.api.copilot.sendBlockGenerationWrongSelectMessage', {
	            data: {
	              siteId: landing_env.Env.getInstance().getSiteId()
	            }
	          });
	          return;
	        }
	        var loadAnimation = Promise.resolve();
	        if (!babelHelpers.classPrivateFieldGet(this, _animationCopilot)) {
	          var editorWindow = landing_pageobject.PageObject.getEditorWindow();
	          loadAnimation = editorWindow.BX.Runtime.loadExtension('landing.animation.copilot').then(function (exports) {
	            babelHelpers.classPrivateFieldSet(_this4, _animationCopilot, new exports.Copilot());
	            babelHelpers.classPrivateFieldGet(_this4, _animationCopilot).subscribe('onBlockFinish', _this4.onBlockAnimateFinish.bind(_this4));
	            _classPrivateMethodGet$3(_this4, _subscribePullEvents, _subscribePullEvents2).call(_this4);
	          });
	        }
	        loadAnimation.then(function () {
	          return main_core.ajax.runAction('landing.api.copilot.executeBlockGeneration', {
	            data: {
	              siteId: landing_env.Env.getInstance().getSiteId(),
	              blockId: babelHelpers.classPrivateFieldGet(_this4, _selectedBlock).id,
	              wishes: message.content
	            }
	          });
	        }).then(function (result) {
	          if (result.status === 'success') {
	            babelHelpers.classPrivateFieldSet(_this4, _generationId, result.data);
	            babelHelpers.classPrivateFieldGet(_this4, _animationCopilot).setBlocksData([{
	              id: babelHelpers.classPrivateFieldGet(_this4, _selectedBlock).id,
	              anchor: babelHelpers.classPrivateFieldGet(_this4, _selectedBlock).anchor,
	              element: babelHelpers.classPrivateFieldGet(_this4, _selectedBlock).content,
	              images: []
	            }]).disableEditor().showLoader();
	            _this4.onBlockGenerationStart();
	          }
	        })["catch"](function (err) {
	          console.error('Error while execute block generation', err);
	        });
	      }
	    }
	  }, {
	    key: "onBlockGenerationStart",
	    value: function onBlockGenerationStart() {
	      var _this5 = this;
	      babelHelpers.classPrivateFieldSet(this, _isGenerationProcessed, true);
	      setTimeout(function () {
	        if (babelHelpers.classPrivateFieldGet(_this5, _isGenerationProcessed)) {
	          _this5.emit('onGenerationStart');
	        }
	      }, 3000);
	    }
	  }, {
	    key: "onBlockAnimateFinish",
	    value: function onBlockAnimateFinish() {
	      var _this6 = this;
	      babelHelpers.classPrivateFieldSet(this, _isGenerationProcessed, false);
	      babelHelpers.classPrivateFieldSet(this, _selectedBlock, null);
	      babelHelpers.classPrivateFieldGet(this, _highlightSelectable).hide();
	      setTimeout(function () {
	        _this6.emit('onGenerationFinish');
	      }, 3000);
	    }
	  }, {
	    key: "setSelectMode",
	    value: function setSelectMode() {
	      var stylePanel = landing_ui_panel_stylepanel.StylePanel.getInstance();
	      if (stylePanel.isShown()) {
	        stylePanel.hide();
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _selectMode)) {
	        return;
	      }
	      babelHelpers.classPrivateFieldSet(this, _selectMode, true);
	      _classPrivateMethodGet$3(this, _bindBlocksSelectEvents, _bindBlocksSelectEvents2).call(this);
	      if (babelHelpers.classPrivateFieldGet(this, _copilotChat) && babelHelpers.classPrivateFieldGet(this, _copilotChatEvents) && babelHelpers.classPrivateFieldGet(this, _copilotChatMessageType)) {
	        babelHelpers.classPrivateFieldGet(this, _copilotChat).subscribe(babelHelpers.classPrivateFieldGet(this, _copilotChatEvents).NEW_MESSAGE, this.onChatMessage);
	      }
	      this.disableEditor();
	    }
	  }, {
	    key: "disableEditor",
	    value: function disableEditor() {
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldGet(this, _editorWindow).document.body, BlockGenerator.selectModeClass);
	    }
	  }, {
	    key: "unsetSelectMode",
	    value: function unsetSelectMode() {
	      if (!babelHelpers.classPrivateFieldGet(this, _selectMode)) {
	        return;
	      }
	      babelHelpers.classPrivateFieldSet(this, _selectMode, false);
	      _classPrivateMethodGet$3(this, _unbindBlocksSelectEvents, _unbindBlocksSelectEvents2).call(this);
	      babelHelpers.classPrivateFieldGet(this, _highlight).hide();
	      babelHelpers.classPrivateFieldGet(this, _highlightSelectable).hide();
	      if (babelHelpers.classPrivateFieldGet(this, _copilotChat) && babelHelpers.classPrivateFieldGet(this, _copilotChatEvents)) {
	        babelHelpers.classPrivateFieldGet(this, _copilotChat).unsubscribe(babelHelpers.classPrivateFieldGet(this, _copilotChatEvents).NEW_MESSAGE, this.onChatMessage);
	      }
	      this.enableEditor();
	    }
	  }, {
	    key: "enableEditor",
	    value: function enableEditor() {
	      main_core.Dom.removeClass(babelHelpers.classPrivateFieldGet(this, _editorWindow).document.body, BlockGenerator.selectModeClass);
	    }
	  }, {
	    key: "onBlockMouseEnter",
	    value: function onBlockMouseEnter(event) {
	      babelHelpers.classPrivateFieldGet(this, _highlight).show(event.currentTarget);
	    }
	  }]);
	  return BlockGenerator;
	}(main_core_events.EventEmitter);
	function _subscribePullEvents2() {
	  var _this7 = this;
	  pull_client.PULL.subscribe({
	    type: pull_client.PullClient.SubscriptionType.Server,
	    moduleId: 'landing',
	    callback: function callback(event) {
	      if (!babelHelpers.classPrivateFieldGet(_this7, _isGenerationProcessed)) {
	        return;
	      }
	      if (event.params.generationId !== undefined && babelHelpers.classPrivateFieldGet(_this7, _generationId) !== null && event.params.generationId !== babelHelpers.classPrivateFieldGet(_this7, _generationId)) {
	        return;
	      }
	      var command = event.command;
	      if (command === 'LandingCopilotGeneration:onGenerationFinish') {
	        landing_history.History.getInstance().push();
	      }
	      if (command === 'LandingCopilotGeneration:onGenerationError') {
	        babelHelpers.classPrivateFieldGet(_this7, _animationCopilot).stop();
	        babelHelpers.classPrivateFieldGet(_this7, _animationCopilot).hideLoader();
	        babelHelpers.classPrivateFieldGet(_this7, _animationCopilot).enableEditor();
	        _this7.onBlockAnimateFinish();
	      }
	    }
	  });
	}
	function _bindBlocksSelectEvents2() {
	  var _iterator2 = _createForOfIteratorHelper(babelHelpers.classPrivateFieldGet(this, _blocks).values()),
	    _step2;
	  try {
	    for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	      var block = _step2.value;
	      var node = block.content;
	      main_core.Event.bind(node, 'mouseenter', this.onBlockMouseEnter);
	      main_core.Event.bind(node, 'click', this.onBlockClick);
	    }
	  } catch (err) {
	    _iterator2.e(err);
	  } finally {
	    _iterator2.f();
	  }
	}
	function _unbindBlocksSelectEvents2() {
	  var _iterator3 = _createForOfIteratorHelper(babelHelpers.classPrivateFieldGet(this, _blocks).values()),
	    _step3;
	  try {
	    for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
	      var block = _step3.value;
	      var node = block.content;
	      main_core.Event.unbind(node, 'mouseenter', this.onBlockMouseEnter);
	      main_core.Event.unbind(node, 'click', this.onBlockClick);
	    }
	  } catch (err) {
	    _iterator3.e(err);
	  } finally {
	    _iterator3.f();
	  }
	}
	babelHelpers.defineProperty(BlockGenerator, "selectModeClass", 'landing-copilot-generation');
	babelHelpers.defineProperty(BlockGenerator, "highlightColor", '#8e52ec');
	babelHelpers.defineProperty(BlockGenerator, "highlightWidth", 4);
	babelHelpers.defineProperty(BlockGenerator, "highlightBackground", 'rgba(142, 82, 236, 0.4)');

	var _templateObject$3, _templateObject2$2, _templateObject3$2, _templateObject4$1, _templateObject5$1, _templateObject6$1, _templateObject7$1, _templateObject8$1;
	function _regeneratorRuntime$1() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime$1 = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == babelHelpers["typeof"](value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
	function _classPrivateMethodInitSpec$4(obj, privateSet) { _checkPrivateRedeclaration$4(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$4(obj, privateMap, value) { _checkPrivateRedeclaration$4(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$4(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$4(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _copilotChat$1 = /*#__PURE__*/new WeakMap();
	var _copilotChatOptions = /*#__PURE__*/new WeakMap();
	var _blockGenerator = /*#__PURE__*/new WeakMap();
	var _siteGenerationId = /*#__PURE__*/new WeakMap();
	var _metrika = /*#__PURE__*/new WeakMap();
	var _initCopilotChat = /*#__PURE__*/new WeakSet();
	var SlidePanel = /*#__PURE__*/function () {
	  function SlidePanel(options) {
	    babelHelpers.classCallCheck(this, SlidePanel);
	    _classPrivateMethodInitSpec$4(this, _initCopilotChat);
	    _classPrivateFieldInitSpec$4(this, _copilotChat$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(this, _copilotChatOptions, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(this, _blockGenerator, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(this, _siteGenerationId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(this, _metrika, {
	      writable: true,
	      value: void 0
	    });
	    this.layout = {
	      container: null,
	      pulse: null,
	      close: null,
	      switcherPreview: null,
	      switcherChat: null,
	      chat: null,
	      preview: null
	    };
	    this.switchers = [];
	    babelHelpers.classPrivateFieldSet(this, _copilotChatOptions, options.copilotChatOptions);
	    babelHelpers.classPrivateFieldSet(this, _siteGenerationId, options.siteGenerationId);
	    babelHelpers.classPrivateFieldSet(this, _metrika, new landing_metrika.Metrika(true, 'ai'));
	    this.init();
	  }
	  babelHelpers.createClass(SlidePanel, [{
	    key: "getSwitcherChat",
	    value: function getSwitcherChat() {
	      var _this = this;
	      if (!this.layout.switcherChat) {
	        this.layout.switcherChat = main_core.Tag.render(_templateObject$3 || (_templateObject$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-slide-panel__navigation-item --chat\">\n\t\t\t\t\t<div class=\"ui-icon-set --copilot-ai\"></div>\n\t\t\t\t</div>\n\t\t\t"])));
	        this.switchers.push(this.layout.switcherChat);
	        main_core.Event.bind(this.layout.switcherChat, 'click', function () {
	          _this.showChat();
	          _this.hidePreview();
	          _this.adjustSwitcher(_this.layout.switcherChat);
	        });
	      }
	      return this.layout.switcherChat;
	    }
	  }, {
	    key: "getSwitcherPreview",
	    value: function getSwitcherPreview() {
	      var _this2 = this;
	      if (!this.layout.switcherPreview) {
	        this.layout.switcherPreview = main_core.Tag.render(_templateObject2$2 || (_templateObject2$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-slide-panel__navigation-item --preview\">\n\t\t\t\t\t<div class=\"ui-icon-set --mobile-2\"></div>\n\t\t\t\t</div>\n\t\t\t"])));
	        this.switchers.push(this.layout.switcherPreview);
	        main_core.Event.bind(this.layout.switcherPreview, 'click', function () {
	          _this2.showPreview();
	          _this2.hideChat();
	          _this2.adjustSwitcher(_this2.layout.switcherPreview);
	        });
	      }
	      return this.layout.switcherPreview;
	    }
	  }, {
	    key: "showChat",
	    value: function () {
	      var _showChat = babelHelpers.asyncToGenerator( /*#__PURE__*/_regeneratorRuntime$1().mark(function _callee() {
	        var _this3 = this;
	        var slider, _babelHelpers$classPr, siteType;
	        return _regeneratorRuntime$1().wrap(function _callee$(_context) {
	          while (1) switch (_context.prev = _context.next) {
	            case 0:
	              slider = null;
	              if (!babelHelpers.classPrivateFieldGet(this, _copilotChatOptions).isCopilotFeatureEnabled) {
	                slider = babelHelpers.classPrivateFieldGet(this, _copilotChatOptions).copilotFeatureEnabledSlider;
	              } else if (!babelHelpers.classPrivateFieldGet(this, _copilotChatOptions).isCopilotActive) {
	                slider = babelHelpers.classPrivateFieldGet(this, _copilotChatOptions).copilotUnactiveSlider;
	              }
	              if (!slider) {
	                _context.next = 5;
	                break;
	              }
	              BX.UI.InfoHelper.show(slider);
	              return _context.abrupt("return");
	            case 5:
	              _context.prev = 5;
	              if (babelHelpers.classPrivateFieldGet(this, _copilotChat$1)) {
	                _context.next = 14;
	                break;
	              }
	              _context.t0 = babelHelpers;
	              _context.t1 = this;
	              _context.t2 = _copilotChat$1;
	              _context.next = 12;
	              return _classPrivateMethodGet$4(this, _initCopilotChat, _initCopilotChat2).call(this);
	            case 12:
	              _context.t3 = _context.sent;
	              _context.t0.classPrivateFieldSet.call(_context.t0, _context.t1, _context.t2, _context.t3);
	            case 14:
	              if (!(!babelHelpers.classPrivateFieldGet(this, _blockGenerator) && babelHelpers.classPrivateFieldGet(this, _copilotChat$1))) {
	                _context.next = 23;
	                break;
	              }
	              _context.t4 = babelHelpers;
	              _context.t5 = this;
	              _context.t6 = _blockGenerator;
	              _context.next = 20;
	              return BlockGenerator.create({
	                chatId: babelHelpers.classPrivateFieldGet(this, _copilotChatOptions).chatId || null,
	                copilotChat: babelHelpers.classPrivateFieldGet(this, _copilotChat$1)
	              });
	            case 20:
	              _context.t7 = _context.sent;
	              _context.t4.classPrivateFieldSet.call(_context.t4, _context.t5, _context.t6, _context.t7);
	              if (babelHelpers.classPrivateFieldGet(this, _blockGenerator)) {
	                babelHelpers.classPrivateFieldGet(this, _blockGenerator).subscribe('onGenerationStart', function () {
	                  _this3.hide();
	                });
	                babelHelpers.classPrivateFieldGet(this, _blockGenerator).subscribe('onGenerationFinish', function () {
	                  _this3.show();
	                  _this3.showChat();
	                  _this3.adjustSwitcher(_this3.layout.switcherChat);
	                });
	              }
	            case 23:
	              if (babelHelpers.classPrivateFieldGet(this, _copilotChat$1).isShown() === false) {
	                babelHelpers.classPrivateFieldGet(this, _copilotChat$1).show();
	                siteType = babelHelpers.classPrivateFieldGet(this, _siteGenerationId) ? 'ai' : 'manual';
	                babelHelpers.classPrivateFieldGet(this, _metrika).sendData({
	                  category: 'block_edition',
	                  event: 'open',
	                  c_section: 'site_editor',
	                  p3: ['siteType', siteType]
	                });
	              }
	              (_babelHelpers$classPr = babelHelpers.classPrivateFieldGet(this, _blockGenerator)) === null || _babelHelpers$classPr === void 0 ? void 0 : _babelHelpers$classPr.setSelectMode();
	              main_core.Dom.addClass(this.getChatContainer(), '--show');
	              _context.next = 31;
	              break;
	            case 28:
	              _context.prev = 28;
	              _context.t8 = _context["catch"](5);
	              console.error(_context.t8);
	            case 31:
	            case "end":
	              return _context.stop();
	          }
	        }, _callee, this, [[5, 28]]);
	      }));
	      function showChat() {
	        return _showChat.apply(this, arguments);
	      }
	      return showChat;
	    }()
	  }, {
	    key: "hideChat",
	    value: function hideChat() {
	      var _babelHelpers$classPr2, _babelHelpers$classPr3;
	      (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldGet(this, _copilotChat$1)) === null || _babelHelpers$classPr2 === void 0 ? void 0 : _babelHelpers$classPr2.hide();
	      (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldGet(this, _blockGenerator)) === null || _babelHelpers$classPr3 === void 0 ? void 0 : _babelHelpers$classPr3.unsetSelectMode();
	      main_core.Dom.removeClass(this.getChatContainer(), '--show');
	    }
	  }, {
	    key: "showPreview",
	    value: function showPreview() {
	      main_core.Dom.addClass(this.getPreviewContainer(), '--show');
	    }
	  }, {
	    key: "hidePreview",
	    value: function hidePreview() {
	      main_core.Dom.removeClass(this.getPreviewContainer(), '--show');
	    }
	  }, {
	    key: "getClose",
	    value: function getClose() {
	      var _this4 = this;
	      if (!this.layout.close) {
	        this.layout.close = main_core.Tag.render(_templateObject3$2 || (_templateObject3$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-slide-panel__navigation-item --close\">\n\t\t\t\t\t<div class=\"ui-icon-set --cross-45\"></div>\n\t\t\t\t</div>\n\t\t\t"])));
	        main_core.Event.bind(this.layout.close, 'click', function () {
	          return _this4.hide();
	        });
	      }
	      return this.layout.close;
	    }
	  }, {
	    key: "getPulse",
	    value: function getPulse() {
	      var _this5 = this;
	      if (!this.layout.pulse) {
	        if (babelHelpers.classPrivateFieldGet(this, _copilotChatOptions).isCopilotFeatureAvailable) {
	          this.layout.pulse = main_core.Tag.render(_templateObject4$1 || (_templateObject4$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"landing-slide-panel__navigation-item --pulse --animate\">\n\t\t\t\t\t\t<div class=\"ui-icon-set --copilot-ai\"></div>\n\t\t\t\t\t\t<div class=\"ui-icon-set --mobile-2\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t"])));
	        } else {
	          this.layout.pulse = main_core.Tag.render(_templateObject5$1 || (_templateObject5$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"landing-slide-panel__navigation-item --pulse\">\n\t\t\t\t\t\t<div class=\"ui-icon-set --mobile-2\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t"])));
	        }
	        main_core.Event.bind(this.layout.pulse, 'click', function () {
	          return _this5.show();
	        });
	      }
	      return this.layout.pulse;
	    }
	  }, {
	    key: "getChatContainer",
	    value: function getChatContainer() {
	      if (!this.layout.chat) {
	        this.layout.chat = main_core.Tag.render(_templateObject6$1 || (_templateObject6$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-slide-panel__container-item --chat\"></div>\n\t\t\t"])));
	      }
	      return this.layout.chat;
	    }
	  }, {
	    key: "getPreviewContainer",
	    value: function getPreviewContainer() {
	      if (!this.layout.preview) {
	        this.layout.preview = main_core.Tag.render(_templateObject7$1 || (_templateObject7$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-slide-panel__container-item --preview\"></div>\n\t\t\t"])));
	      }
	      return this.layout.preview;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.layout.container) {
	        this.layout.container = main_core.Tag.render(_templateObject8$1 || (_templateObject8$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-slide-panel\">\n\t\t\t\t\t<div class=\"landing-slide-panel-inner\">\t\t\t\t\t\n\t\t\t\t\t\t<div class=\"landing-slide-panel__container\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"landing-slide-panel__navigation\">\n\t\t\t\t\t\t \t", "\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), this.getChatContainer(), this.getPreviewContainer(), babelHelpers.classPrivateFieldGet(this, _copilotChatOptions).isCopilotFeatureAvailable ? this.getSwitcherChat() : '', this.getSwitcherPreview(), this.getClose(), this.getPulse());
	      }
	      return this.layout.container;
	    }
	  }, {
	    key: "adjustSwitcher",
	    value: function adjustSwitcher(switcher) {
	      this.switchers.forEach(function (item) {
	        return main_core.Dom.removeClass(item, '--active');
	      });
	      if (main_core.Type.isDomNode(switcher)) {
	        main_core.Dom.addClass(switcher, '--active');
	      }
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      main_core.Dom.addClass(this.getContainer(), '--open');
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      main_core.Dom.removeClass(this.getContainer(), '--open');
	      this.hideChat();
	      this.hidePreview();
	      this.adjustSwitcher();
	    }
	  }, {
	    key: "init",
	    value: function init() {
	      main_core.Dom.append(this.getContainer(), document.body);
	    }
	  }]);
	  return SlidePanel;
	}();
	function _initCopilotChat2() {
	  return _initCopilotChat3.apply(this, arguments);
	}
	function _initCopilotChat3() {
	  _initCopilotChat3 = babelHelpers.asyncToGenerator( /*#__PURE__*/_regeneratorRuntime$1().mark(function _callee2() {
	    var _babelHelpers$classPr4,
	      _babelHelpers$classPr5,
	      _this$layout$switcher,
	      _babelHelpers$classPr6,
	      _babelHelpers$classPr7,
	      _babelHelpers$classPr8,
	      _this6 = this;
	    var exports, Chat, chat;
	    return _regeneratorRuntime$1().wrap(function _callee2$(_context2) {
	      while (1) switch (_context2.prev = _context2.next) {
	        case 0:
	          _context2.next = 2;
	          return main_core.Runtime.loadExtension('landing.copilot.chat');
	        case 2:
	          exports = _context2.sent;
	          Chat = exports.Chat;
	          this.copilotChatEvents = exports.CopilotChatEvents;
	          this.copilotChatMessageType = exports.CopilotChatMessageType;
	          _context2.next = 8;
	          return Chat.getCopilotChatInstance({
	            entityId: (_babelHelpers$classPr4 = babelHelpers.classPrivateFieldGet(this, _copilotChatOptions)) === null || _babelHelpers$classPr4 === void 0 ? void 0 : _babelHelpers$classPr4.entityId,
	            chatId: (_babelHelpers$classPr5 = babelHelpers.classPrivateFieldGet(this, _copilotChatOptions)) === null || _babelHelpers$classPr5 === void 0 ? void 0 : _babelHelpers$classPr5.chatId,
	            scenario: 'site_with_ai_change_block',
	            showChatButtonElement: (_this$layout$switcher = this.layout.switcherChat) !== null && _this$layout$switcher !== void 0 ? _this$layout$switcher : null,
	            isSiteEditChat: ((_babelHelpers$classPr6 = babelHelpers.classPrivateFieldGet(this, _copilotChatOptions)) === null || _babelHelpers$classPr6 === void 0 ? void 0 : _babelHelpers$classPr6.isSiteEditChat) === true
	          });
	        case 8:
	          chat = _context2.sent;
	          if (!((_babelHelpers$classPr7 = babelHelpers.classPrivateFieldGet(this, _copilotChatOptions)) !== null && _babelHelpers$classPr7 !== void 0 && _babelHelpers$classPr7.chatId) && (_babelHelpers$classPr8 = babelHelpers.classPrivateFieldGet(this, _copilotChatOptions)) !== null && _babelHelpers$classPr8 !== void 0 && _babelHelpers$classPr8.onChatCreate) {
	            chat.subscribe(this.copilotChatEvents.INIT_CHAT, function (event) {
	              babelHelpers.classPrivateFieldGet(_this6, _copilotChatOptions).onChatCreate(event.getData());
	              babelHelpers.classPrivateFieldGet(_this6, _copilotChatOptions).chatId = event.getData().chatId;
	            });
	          }
	          return _context2.abrupt("return", chat);
	        case 11:
	        case "end":
	          return _context2.stop();
	      }
	    }, _callee2, this);
	  }));
	  return _initCopilotChat3.apply(this, arguments);
	}

	exports.Device = Device;
	exports.ExternalControls = ExternalControls;
	exports.SlidePanel = SlidePanel;

}((this.BX.Landing.View = this.BX.Landing.View || {}),BX.Main,BX.Landing,BX,BX,BX.Event,BX,BX.Landing,BX.Landing.UI,BX.Landing.Animation,BX.Landing,BX.Landing,BX.Landing.UI.Panel));
//# sourceMappingURL=script.js.map
