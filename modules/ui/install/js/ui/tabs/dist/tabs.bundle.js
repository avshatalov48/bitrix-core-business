/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core_collections,main_core,main_core_events,main_loader) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var justCounter = {
	  localId: 0,
	  localSorting: 0
	};
	var _parentElement = /*#__PURE__*/new WeakMap();
	var _id = /*#__PURE__*/new WeakMap();
	var _sort = /*#__PURE__*/new WeakMap();
	var _head = /*#__PURE__*/new WeakMap();
	var _body = /*#__PURE__*/new WeakMap();
	var _dataContainer = /*#__PURE__*/new WeakMap();
	var _active = /*#__PURE__*/new WeakMap();
	var _restricted = /*#__PURE__*/new WeakMap();
	var _bannerCode = /*#__PURE__*/new WeakMap();
	var _helpDeskCode = /*#__PURE__*/new WeakMap();
	var _loader = /*#__PURE__*/new WeakMap();
	var _initHead = /*#__PURE__*/new WeakSet();
	var _initBody = /*#__PURE__*/new WeakSet();
	var _loadBody = /*#__PURE__*/new WeakSet();
	var _showLoader = /*#__PURE__*/new WeakSet();
	var _removeLoader = /*#__PURE__*/new WeakSet();
	var Tab = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Tab, _EventEmitter);
	  function Tab(_options) {
	    var _this;
	    var parentElement = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	    babelHelpers.classCallCheck(this, Tab);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Tab).call(this, {}));
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _removeLoader);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _showLoader);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _loadBody);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _initBody);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _initHead);
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _parentElement, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _id, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _sort, {
	      writable: true,
	      value: 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _head, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _body, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _dataContainer, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _active, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _restricted, {
	      writable: true,
	      value: true
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _bannerCode, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _helpDeskCode, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _loader, {
	      writable: true,
	      value: null
	    });
	    _this.setEventNamespace('UI:Tabs:');
	    _this.setParent(parentElement);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _id, main_core.Type.isStringFilled(_options.id) ? _options.id : 'TabId' + ++justCounter.localId);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _sort, main_core.Type.isInteger(_options.sort) ? _options.sort : ++justCounter.localSorting);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _active, main_core.Type.isBoolean(_options.active) ? _options.active : false);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _restricted, _options.restricted === true);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _bannerCode, main_core.Type.isStringFilled(_options.bannerCode) ? _options.bannerCode : null);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _helpDeskCode, main_core.Type.isStringFilled(_options.helpDeskCode) ? _options.helpDeskCode : null);
	    _classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _initHead, _initHead2).call(babelHelpers.assertThisInitialized(_this), _options.head);
	    _classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _initBody, _initBody2).call(babelHelpers.assertThisInitialized(_this), _options.body);
	    return _this;
	  }
	  babelHelpers.createClass(Tab, [{
	    key: "getId",
	    value: function getId() {
	      return babelHelpers.classPrivateFieldGet(this, _id);
	    }
	  }, {
	    key: "getSort",
	    value: function getSort() {
	      return babelHelpers.classPrivateFieldGet(this, _sort);
	    }
	  }, {
	    key: "setParent",
	    value: function setParent(parentElement) {
	      if (parentElement instanceof Tabs) {
	        babelHelpers.classPrivateFieldSet(this, _parentElement, parentElement);
	      }
	    }
	  }, {
	    key: "isRestricted",
	    value: function isRestricted() {
	      return babelHelpers.classPrivateFieldGet(this, _restricted);
	    }
	  }, {
	    key: "getBannerCode",
	    value: function getBannerCode() {
	      return babelHelpers.classPrivateFieldGet(this, _bannerCode);
	    }
	  }, {
	    key: "showBanner",
	    value: function showBanner(event) {
	      if (this.getBannerCode()) {
	        BX.UI.InfoHelper.show(this.getBannerCode());
	      }
	      if (event) {
	        event.stopPropagation();
	        event.preventDefault();
	      }
	    }
	  }, {
	    key: "getHeader",
	    value: function getHeader() {
	      return babelHelpers.classPrivateFieldGet(this, _head);
	    }
	  }, {
	    key: "getBody",
	    value: function getBody() {
	      return babelHelpers.classPrivateFieldGet(this, _body);
	    } // Here just in case
	  }, {
	    key: "getBodyDataContainer",
	    value: function getBodyDataContainer() {
	      return babelHelpers.classPrivateFieldGet(this, _dataContainer);
	    }
	  }, {
	    key: "inactivate",
	    value: function inactivate() {
	      var withAnimation = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      main_core.Dom.removeClass(babelHelpers.classPrivateFieldGet(this, _body), 'ui-tabs__tab-active-animation');
	      if (withAnimation !== false) {
	        main_core.Dom.addClass(babelHelpers.classPrivateFieldGet(this, _body), 'ui-tabs__tab-active-animation');
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _active) === true) {
	        main_core.Dom.removeClass(babelHelpers.classPrivateFieldGet(this, _head), '--header-active');
	        main_core.Dom.removeClass(babelHelpers.classPrivateFieldGet(this, _body), '--body-active');
	        babelHelpers.classPrivateFieldSet(this, _active, false);
	        this.emit('onInactive');
	      }
	      return this;
	    }
	  }, {
	    key: "activate",
	    value: function activate() {
	      var withAnimation = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      main_core.Dom.removeClass(babelHelpers.classPrivateFieldGet(this, _body), 'ui-tabs__tab-active-animation');
	      if (withAnimation !== false) {
	        main_core.Dom.addClass(babelHelpers.classPrivateFieldGet(this, _body), 'ui-tabs__tab-active-animation');
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _active) !== true) {
	        main_core.Dom.addClass(babelHelpers.classPrivateFieldGet(this, _head), '--header-active');
	        main_core.Dom.addClass(babelHelpers.classPrivateFieldGet(this, _body), '--body-active');
	        babelHelpers.classPrivateFieldSet(this, _active, true);
	        this.emit('onActive');
	      }
	      return this;
	    }
	  }, {
	    key: "isActive",
	    value: function isActive() {
	      return babelHelpers.classPrivateFieldGet(this, _active);
	    }
	  }, {
	    key: "showError",
	    value: function showError(_ref) {
	      var message = _ref.message,
	        code = _ref.code;
	      var errorContainer = this.getBody().querySelector('[data-bx-role="error-container"]');
	      if (errorContainer) {
	        errorContainer.innerText = message || code;
	      }
	      main_core.Dom.addClass(this.getBodyContainer(), 'ui-avatar-editor--error');
	    }
	  }]);
	  return Tab;
	}(main_core_events.EventEmitter);
	function _initHead2(headOptions) {
	  var _options$className,
	    _this2 = this;
	  var options = main_core.Type.isPlainObject(headOptions) ? headOptions : main_core.Type.isStringFilled(headOptions) ? {
	    title: headOptions
	  } : {};
	  var innerHeader;
	  if (main_core.Type.isDomNode(headOptions)) {
	    innerHeader = headOptions;
	  } else if (babelHelpers.classPrivateFieldGet(this, _restricted) !== true) {
	    var _options$description, _Text$encode;
	    innerHeader = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div title=\"", "\">", "</div>"])), main_core.Text.encode((_options$description = options.description) !== null && _options$description !== void 0 ? _options$description : ''), (_Text$encode = main_core.Text.encode(options.title)) !== null && _Text$encode !== void 0 ? _Text$encode : '&nbsp;');
	  } else {
	    var _options$description2;
	    innerHeader = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-tabs__tab-header-container-inner\" title=\"", "\">\n\t\t\t\t<div class=\"ui-tabs__tab-header-container-inner-title\">", "</div>\n\t\t\t\t<div class=\"ui-tabs__tab-header-container-inner-lockbox\"><span class=\"ui-icon-set --lock field-has-lock\"></span></div>\n\t\t\t</div>"])), main_core.Text.encode((_options$description2 = options.description) !== null && _options$description2 !== void 0 ? _options$description2 : ''), main_core.Text.encode(options.title));
	    main_core.Event.bind(innerHeader, 'click', this.showBanner.bind(this));
	  }
	  babelHelpers.classPrivateFieldSet(this, _head, main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-tabs__tab-header-container ", "\" data-bx-role=\"tab-header\" data-bx-name=\"", "\">", "</span>"])), main_core.Text.encode((_options$className = options.className) !== null && _options$className !== void 0 ? _options$className : ''), main_core.Text.encode(babelHelpers.classPrivateFieldGet(this, _id)), innerHeader));
	  main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _head), 'click', function () {
	    _this2.emit('changeTab');
	  });
	}
	function _initBody2(body) {
	  var _this3 = this;
	  babelHelpers.classPrivateFieldSet(this, _dataContainer, main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-tabs__tab-body_data\"></div>"]))));
	  babelHelpers.classPrivateFieldSet(this, _body, main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-tabs__tab-body_inner\"></div>"]))));
	  babelHelpers.classPrivateFieldGet(this, _body).dataset.id = babelHelpers.classPrivateFieldGet(this, _id);
	  babelHelpers.classPrivateFieldGet(this, _body).dataset.role = 'body';
	  babelHelpers.classPrivateFieldGet(this, _body).appendChild(babelHelpers.classPrivateFieldGet(this, _dataContainer));
	  if (body) {
	    this.subscribe('onActive', function () {
	      _classPrivateMethodGet(_this3, _loadBody, _loadBody2).call(_this3, body);
	    });
	  }
	}
	function _loadBody2(body) {
	  var _this4 = this;
	  var resultBody = body;
	  if (main_core.Type.isFunction(body)) {
	    resultBody = body(this);
	  }
	  var promiseBody;
	  if (!resultBody || Object.prototype.toString.call(resultBody) === "[object Promise]" || resultBody.toString() === "[object BX.Promise]") {
	    promiseBody = resultBody;
	    _classPrivateMethodGet(this, _showLoader, _showLoader2).call(this);
	  } else {
	    promiseBody = Promise.resolve(resultBody);
	  }
	  promiseBody.then(function (result) {
	    _classPrivateMethodGet(_this4, _removeLoader, _removeLoader2).call(_this4);
	    if (main_core.Type.isDomNode(result)) {
	      babelHelpers.classPrivateFieldGet(_this4, _dataContainer).appendChild(result);
	    } else if (main_core.Type.isString(result)) {
	      babelHelpers.classPrivateFieldGet(_this4, _dataContainer).innerHTML = result; //HTML! Not Text.encoded
	    } else {
	      throw new Error('Tab body has to be a text or a dom-element.');
	    }
	    _this4.emit('onLoad');
	  }, function (reason) {
	    console.log('reason: ', reason);
	    _classPrivateMethodGet(_this4, _removeLoader, _removeLoader2).call(_this4);
	    babelHelpers.classPrivateFieldGet(_this4, _dataContainer).innerHTML = reason;
	    _this4.emit('onLoadErrored');
	  });
	}
	function _showLoader2() {
	  babelHelpers.classPrivateFieldSet(this, _loader, new main_loader.Loader({
	    target: babelHelpers.classPrivateFieldGet(this, _dataContainer),
	    color: 'rgba(82, 92, 105, 0.9)',
	    mode: 'inline'
	  }));
	  babelHelpers.classPrivateFieldGet(this, _loader).show().then(function () {
	    console.log('The loader is shown');
	  });
	}
	function _removeLoader2() {
	  if (babelHelpers.classPrivateFieldGet(this, _loader)) {
	    babelHelpers.classPrivateFieldGet(this, _loader).destroy();
	    babelHelpers.classPrivateFieldSet(this, _loader, null);
	  }
	}

	var _templateObject$1, _templateObject2$1;
	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var justCounter$1 = {
	  localId: 0
	};
	var _index = /*#__PURE__*/new WeakMap();
	var _id$1 = /*#__PURE__*/new WeakMap();
	var _items = /*#__PURE__*/new WeakMap();
	var _activeItem = /*#__PURE__*/new WeakMap();
	var _body$1 = /*#__PURE__*/new WeakMap();
	var Tabs = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Tabs, _EventEmitter);
	  function Tabs(options) {
	    var _options$items;
	    var _this;
	    babelHelpers.classCallCheck(this, Tabs);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Tabs).call(this));
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _index, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _id$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _items, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _activeItem, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _body$1, {
	      writable: true,
	      value: void 0
	    });
	    options = main_core.Type.isObjectLike(options) ? options : {};
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _index, ++justCounter$1.localId);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _id$1, main_core.Type.isStringFilled(options.id) ? options.id : 'TabsId' + babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _index));
	    _this.setEventNamespace('UI:Tabs:' + options.id);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _items, new main_core_collections.OrderedArray(function (tabA, tabB) {
	      return tabA.getSort() > tabB.getSort() ? 1 : -1;
	    }));
	    Array.from((_options$items = options.items) !== null && _options$items !== void 0 ? _options$items : []).forEach(function (TabOptionsType) {
	      return _this.addItem(new Tab(TabOptionsType));
	    });
	    _this.activateItemDebounced = main_core.Runtime.debounce(_this.activateItemDebounced, 100, babelHelpers.assertThisInitialized(_this));
	    if (babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _items).count() > 0 && !(babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _activeItem) instanceof Tab)) {
	      _this.activateItem(babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _items).getFirst());
	    }
	    return _this;
	  }
	  babelHelpers.createClass(Tabs, [{
	    key: "getIndex",
	    value: function getIndex() {
	      return babelHelpers.classPrivateFieldGet(this, _index);
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return babelHelpers.classPrivateFieldGet(this, _id$1);
	    }
	  }, {
	    key: "addItem",
	    value: function addItem(tab) {
	      var _this2 = this;
	      babelHelpers.classPrivateFieldGet(this, _items).add(tab);
	      tab.setParent(this);
	      if (tab.isActive()) {
	        this.activateItem(tab);
	      }
	      tab.subscribe('changeTab', function () {
	        _this2.activateItem(tab);
	      });
	    }
	  }, {
	    key: "activateItem",
	    value: function activateItem(tab) {
	      var withAnimation = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
	      if (babelHelpers.classPrivateFieldGet(this, _items).has(tab) && babelHelpers.classPrivateFieldGet(this, _activeItem) !== tab) {
	        var inactiveTab = null;
	        if (babelHelpers.classPrivateFieldGet(this, _activeItem) instanceof Tab) {
	          inactiveTab = babelHelpers.classPrivateFieldGet(this, _activeItem);
	        }
	        babelHelpers.classPrivateFieldSet(this, _activeItem, tab);
	        this.activateItemDebounced(tab, inactiveTab, withAnimation);
	      }
	    }
	  }, {
	    key: "activateItemDebounced",
	    value: function activateItemDebounced(activeTab) {
	      var inactiveTab = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      var withAnimation = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;
	      if (inactiveTab) {
	        inactiveTab.inactivate(withAnimation);
	      }
	      activeTab.activate(withAnimation);
	    }
	  }, {
	    key: "getBodyContainer",
	    value: function getBodyContainer() {
	      if (!babelHelpers.classPrivateFieldGet(this, _body$1)) {
	        babelHelpers.classPrivateFieldSet(this, _body$1, main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-tabs__tabs-body-container\" data-bx-role=\"bodies\"></div>\n\t\t\t"]))));
	      }
	      return babelHelpers.classPrivateFieldGet(this, _body$1);
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      var _this3 = this;
	      if (this.content) {
	        return this.content;
	      }
	      this.content = main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-tabs__tabs-container\">\n\t\t\t\t<div class=\"ui-tabs__tabs-header-container\" data-bx-role=\"headers\"></div>\n\t\t\t\t", "\n\t\t\t</div>"])), this.getBodyContainer());
	      var headers = this.content.querySelector('[data-bx-role="headers"]');
	      babelHelpers.classPrivateFieldGet(this, _items).forEach(function (tab) {
	        main_core.Dom.append(tab.getHeader(), headers);
	        main_core.Dom.append(tab.getBody(), _this3.getBodyContainer());
	      });
	      return this.content;
	    }
	  }, {
	    key: "getItems",
	    value: function getItems() {
	      return babelHelpers.classPrivateFieldGet(this, _items);
	    }
	  }]);
	  return Tabs;
	}(main_core_events.EventEmitter);

	exports.Tabs = Tabs;
	exports.Tab = Tab;

}((this.BX.UI = this.BX.UI || {}),BX.Collections,BX,BX.Event,BX));
//# sourceMappingURL=tabs.bundle.js.map
