/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core_collections,ui_formElements_view,main_core_events,ui_section,main_core,ui_formElements_field,ui_tabs) {
	'use strict';

	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	var ErrorCollection = /*#__PURE__*/function (_OrderedArray) {
	  babelHelpers.inherits(ErrorCollection, _OrderedArray);
	  function ErrorCollection() {
	    var _this;
	    var errors = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
	    var comparator = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	    babelHelpers.classCallCheck(this, ErrorCollection);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ErrorCollection).call(this, comparator));
	    _this.addItems(errors);
	    return _this;
	  }
	  babelHelpers.createClass(ErrorCollection, [{
	    key: "addItems",
	    value: function addItems(items) {
	      var _iterator = _createForOfIteratorHelper(items),
	        _step;
	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var item = _step.value;
	          this.add(item);
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }
	    }
	  }, {
	    key: "merge",
	    value: function merge(errorCollection) {
	      this.addItems(errorCollection.getAll());
	      return this;
	    }
	  }], [{
	    key: "showSystemError",
	    value: function showSystemError(text) {
	      top.BX.UI.Notification.Center.notify({
	        content: text,
	        position: 'bottom-right',
	        category: 'menu-self-item-popup',
	        autoHideDelay: 3000
	      });
	    }
	  }]);
	  return ErrorCollection;
	}(main_core_collections.OrderedArray);

	var BaseSettingsVisitor = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(BaseSettingsVisitor, _EventEmitter);
	  function BaseSettingsVisitor(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, BaseSettingsVisitor);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BaseSettingsVisitor).call(this, params));
	    _this.setEventNamespace('BX.UI.FormElement.Field');
	    return _this;
	  }
	  babelHelpers.createClass(BaseSettingsVisitor, [{
	    key: "visitSettingsElement",
	    value: function visitSettingsElement(settingsElement) {}
	  }]);
	  return BaseSettingsVisitor;
	}(main_core_events.EventEmitter);
	babelHelpers.defineProperty(BaseSettingsVisitor, "instances", []);

	function _createForOfIteratorHelper$1(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$1(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray$1(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$1(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$1(o, minLen); }
	function _arrayLikeToArray$1(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _errorCollection = /*#__PURE__*/new WeakMap();
	var _parentElement = /*#__PURE__*/new WeakMap();
	var _childrenElements = /*#__PURE__*/new WeakMap();
	var BaseSettingsElement = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(BaseSettingsElement, _EventEmitter);
	  function BaseSettingsElement(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, BaseSettingsElement);
	    params = main_core.Type.isNil(params) ? {} : params;
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BaseSettingsElement).call(this));
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _errorCollection, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _parentElement, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _childrenElements, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _parentElement, null);
	    _this.setEventNamespace('BX.UI.FormElement.Field');
	    if (!main_core.Type.isNil(params.parent)) {
	      _this.setParentElement(params.parent);
	    }
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _childrenElements, []);
	    if (!main_core.Type.isNil(params.children)) {
	      _this.setChildrenElements(params.children);
	    }
	    _this.addChild(params.child);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _errorCollection, new ErrorCollection());
	    return _this;
	  }
	  babelHelpers.createClass(BaseSettingsElement, [{
	    key: "getErrorCollection",
	    value: function getErrorCollection() {
	      return babelHelpers.classPrivateFieldGet(this, _errorCollection);
	    }
	  }, {
	    key: "setErrorCollection",
	    value: function setErrorCollection(errorCollection) {
	      var _babelHelpers$classPr;
	      babelHelpers.classPrivateFieldGet(this, _errorCollection).merge(errorCollection);
	      (_babelHelpers$classPr = babelHelpers.classPrivateFieldGet(this, _parentElement)) === null || _babelHelpers$classPr === void 0 ? void 0 : _babelHelpers$classPr.setErrorCollection(babelHelpers.classPrivateFieldGet(this, _errorCollection));
	    }
	  }, {
	    key: "getParentElement",
	    value: function getParentElement() {
	      return babelHelpers.classPrivateFieldGet(this, _parentElement);
	    }
	  }, {
	    key: "getChildrenElements",
	    value: function getChildrenElements() {
	      return babelHelpers.classPrivateFieldGet(this, _childrenElements);
	    }
	  }, {
	    key: "setParentElement",
	    value: function setParentElement(parent) {
	      if (parent instanceof BaseSettingsElement) {
	        babelHelpers.classPrivateFieldSet(this, _parentElement, parent);
	        babelHelpers.classPrivateFieldGet(this, _parentElement).addChild(this);
	      }
	      return this;
	    }
	  }, {
	    key: "unsetParentElement",
	    value: function unsetParentElement() {
	      babelHelpers.classPrivateFieldSet(this, _parentElement, null);
	    }
	  }, {
	    key: "setChildrenElements",
	    value: function setChildrenElements(value) {
	      var _iterator = _createForOfIteratorHelper$1(value),
	        _step;
	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var element = _step.value;
	          this.addChild(element);
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }
	    }
	  }, {
	    key: "addChild",
	    value: function addChild(child) {
	      if (child instanceof BaseSettingsElement) {
	        if (!babelHelpers.classPrivateFieldGet(this, _childrenElements).includes(child)) {
	          babelHelpers.classPrivateFieldGet(this, _childrenElements).push(child);
	        }
	        if (main_core.Type.isNil(child.getParentElement())) {
	          child.setParentElement(this);
	        }
	      }
	    }
	  }, {
	    key: "removeChild",
	    value: function removeChild(child) {
	      if (child instanceof BaseSettingsElement) {
	        babelHelpers.classPrivateFieldSet(this, _childrenElements, babelHelpers.classPrivateFieldGet(this, _childrenElements).filter(function (element) {
	          return element !== child;
	        }));
	        child.unsetParentElement();
	      }
	    } //#region "Renderable" Interface
	  }, {
	    key: "render",
	    value: function render() {
	      return '';
	    }
	  }, {
	    key: "renderErrors",
	    value: function renderErrors() {
	      return '';
	    }
	  }, {
	    key: "accept",
	    value: function accept(visitor) {
	      visitor.visitSettingsElement(this);
	    }
	  }, {
	    key: "highlight",
	    value: function highlight() {
	      return false;
	    }
	  }, {
	    key: "highlightElement",
	    value: function highlightElement(element) {
	      main_core.Dom.addClass(element, '--founded-item');
	      setTimeout(function () {
	        main_core.Dom.removeClass(element, '--founded-item');
	        main_core.Dom.addClass(element, '--after-founded-item');
	        setTimeout(function () {
	          main_core.Dom.removeClass(element, '--after-founded-item');
	        }, 5000);
	      }, 0);
	    } //#endregion "Renderable" Interface
	  }]);
	  return BaseSettingsElement;
	}(main_core_events.EventEmitter);

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _fieldView = /*#__PURE__*/new WeakMap();
	var _extractErrorsFromEvent = /*#__PURE__*/new WeakSet();
	var _onFailedSave = /*#__PURE__*/new WeakSet();
	var SettingsField = /*#__PURE__*/function (_BaseSettingsElement) {
	  babelHelpers.inherits(SettingsField, _BaseSettingsElement);
	  function SettingsField(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, SettingsField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SettingsField).call(this, params));
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onFailedSave);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _extractErrorsFromEvent);
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _fieldView, {
	      writable: true,
	      value: void 0
	    });
	    if (!(params.fieldView instanceof ui_formElements_view.BaseField)) {
	      throw new Error("Unexpected field type, expected \"BaseField\"");
	    }
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _fieldView, params.fieldView);
	    main_core_events.EventEmitter.subscribe('BX.UI.FormElement.Field:onFailedSave', _classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _onFailedSave, _onFailedSave2).bind(babelHelpers.assertThisInitialized(_this)));
	    return _this;
	  }
	  babelHelpers.createClass(SettingsField, [{
	    key: "getFieldView",
	    value: function getFieldView() {
	      return babelHelpers.classPrivateFieldGet(this, _fieldView);
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      return this.getFieldView().render();
	    }
	  }, {
	    key: "renderErrors",
	    value: function renderErrors() {
	      this.getFieldView().setErrors(this.getErrorCollection().getAll());
	      return this.getFieldView().renderErrors();
	    }
	  }]);
	  return SettingsField;
	}(BaseSettingsElement);
	function _extractErrorsFromEvent2(event) {
	  var _errors$this$getField;
	  var errors = {};
	  for (var type in event.data.errors) {
	    errors = _objectSpread(_objectSpread({}, errors), event.data.errors[type]);
	  }
	  return (_errors$this$getField = errors[this.getFieldView().getName()]) !== null && _errors$this$getField !== void 0 ? _errors$this$getField : [];
	}
	function _onFailedSave2(event) {
	  var fieldErrors = _classPrivateMethodGet(this, _extractErrorsFromEvent, _extractErrorsFromEvent2).call(this, event);
	  this.getErrorCollection().clear();
	  this.setErrorCollection(new ErrorCollection(fieldErrors));
	  this.renderErrors();
	}

	function _createForOfIteratorHelper$2(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$2(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray$2(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$2(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$2(o, minLen); }
	function _arrayLikeToArray$2(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classPrivateFieldInitSpec$2(obj, privateMap, value) { _checkPrivateRedeclaration$2(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$2(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _rowView = /*#__PURE__*/new WeakMap();
	var SettingsRow = /*#__PURE__*/function (_BaseSettingsElement) {
	  babelHelpers.inherits(SettingsRow, _BaseSettingsElement);
	  function SettingsRow(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, SettingsRow);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SettingsRow).call(this, params));
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _rowView, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _rowView, params.row instanceof ui_section.Row || params.row instanceof ui_section.SeparatorRow ? params.row : new ui_section.Row(main_core.Type.isPlainObject(params.row) ? params.row : {}));
	    return _this;
	  }
	  babelHelpers.createClass(SettingsRow, [{
	    key: "getRowView",
	    value: function getRowView() {
	      return babelHelpers.classPrivateFieldGet(this, _rowView);
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var _iterator = _createForOfIteratorHelper$2(this.getChildrenElements()),
	        _step;
	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var element = _step.value;
	          this.getRowView().append(element.render());
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }
	      return this.getRowView().render();
	    }
	  }, {
	    key: "highlight",
	    value: function highlight() {
	      this.highlightElement(this.getRowView().render());
	      return true;
	    }
	  }]);
	  return SettingsRow;
	}(BaseSettingsElement);

	function _createForOfIteratorHelper$3(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$3(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray$3(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$3(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$3(o, minLen); }
	function _arrayLikeToArray$3(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classPrivateFieldInitSpec$3(obj, privateMap, value) { _checkPrivateRedeclaration$3(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$3(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _sectionView = /*#__PURE__*/new WeakMap();
	var _sectionSort = /*#__PURE__*/new WeakMap();
	var SettingsSection = /*#__PURE__*/function (_BaseSettingsElement) {
	  babelHelpers.inherits(SettingsSection, _BaseSettingsElement);
	  function SettingsSection(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, SettingsSection);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SettingsSection).call(this, params));
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _sectionView, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _sectionSort, {
	      writable: true,
	      value: 100
	    });
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _sectionView, params.section instanceof ui_section.Section ? params.section : new ui_section.Section(main_core.Type.isPlainObject(params.section) ? params.section : {}));
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _sectionSort, main_core.Type.isNumber(params.sort) ? params.sort : 100);
	    return _this;
	  }
	  babelHelpers.createClass(SettingsSection, [{
	    key: "getSectionView",
	    value: function getSectionView() {
	      return babelHelpers.classPrivateFieldGet(this, _sectionView);
	    }
	  }, {
	    key: "getSectionSort",
	    value: function getSectionSort() {
	      return babelHelpers.classPrivateFieldGet(this, _sectionSort);
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var _iterator = _createForOfIteratorHelper$3(this.getChildrenElements()),
	        _step;
	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var element = _step.value;
	          this.getSectionView().append(element.render());
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }
	      return this.getSectionView().render();
	    }
	  }, {
	    key: "renderTo",
	    value: function renderTo(targetNode) {
	      return main_core.Dom.append(this.render(), targetNode);
	    }
	  }, {
	    key: "highlight",
	    value: function highlight() {
	      this.highlightElement(this.getSectionView().render());
	      return true;
	    }
	  }]);
	  return SettingsSection;
	}(BaseSettingsElement);

	var _templateObject, _templateObject2, _templateObject3, _templateObject4;
	function _classStaticPrivateFieldSpecSet(receiver, classConstructor, descriptor, value) { _classCheckPrivateStaticAccess(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor(descriptor, "set"); _classApplyDescriptorSet(receiver, descriptor, value); return value; }
	function _classApplyDescriptorSet(receiver, descriptor, value) { if (descriptor.set) { descriptor.set.call(receiver, value); } else { if (!descriptor.writable) { throw new TypeError("attempted to set read only private field"); } descriptor.value = value; } }
	function _classStaticPrivateFieldSpecGet(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor(descriptor, "get"); return _classApplyDescriptorGet(receiver, descriptor); }
	function _classCheckPrivateStaticFieldDescriptor(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }
	function _classCheckPrivateStaticAccess(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	function _classApplyDescriptorGet(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }
	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$4(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$4(obj, privateMap, value) { _checkPrivateRedeclaration$4(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$4(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _content = /*#__PURE__*/new WeakMap();
	var _page = /*#__PURE__*/new WeakMap();
	var _data = /*#__PURE__*/new WeakMap();
	var _analytic = /*#__PURE__*/new WeakMap();
	var _subPage = /*#__PURE__*/new WeakMap();
	var _subPageExtensions = /*#__PURE__*/new WeakMap();
	var _permission = /*#__PURE__*/new WeakMap();
	var _fetchData = /*#__PURE__*/new WeakSet();
	var BaseSettingsPage = /*#__PURE__*/function (_BaseSettingsElement) {
	  babelHelpers.inherits(BaseSettingsPage, _BaseSettingsElement);
	  /**
	   * @type {?Analytic}
	   */

	  function BaseSettingsPage() {
	    var _this;
	    babelHelpers.classCallCheck(this, BaseSettingsPage);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BaseSettingsPage).call(this));
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _fetchData);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "fields", {});
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _content, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _page, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "titlePage", '');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "descriptionPage", '');
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _data, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _analytic, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _subPage, {
	      writable: true,
	      value: new Map()
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _subPageExtensions, {
	      writable: true,
	      value: []
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _permission, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Intranet.Settings');
	    return _this;
	  }
	  babelHelpers.createClass(BaseSettingsPage, [{
	    key: "getAnalytic",
	    value: function getAnalytic() {
	      return babelHelpers.classPrivateFieldGet(this, _analytic);
	    }
	    /**
	     * @param analytic
	     */
	  }, {
	    key: "setAnalytic",
	    value: function setAnalytic(analytic) {
	      babelHelpers.classPrivateFieldSet(this, _analytic, analytic);
	    }
	  }, {
	    key: "setPermission",
	    value: function setPermission(permission) {
	      babelHelpers.classPrivateFieldSet(this, _permission, permission);
	    }
	  }, {
	    key: "getPermission",
	    value: function getPermission() {
	      return babelHelpers.classPrivateFieldGet(this, _permission);
	    }
	  }, {
	    key: "hasValue",
	    value: function hasValue(key) {
	      if (main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _data)) || !main_core.Type.isObject(babelHelpers.classPrivateFieldGet(this, _data))) {
	        return false;
	      }
	      return !main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _data)[key]);
	    }
	  }, {
	    key: "getValue",
	    value: function getValue(key) {
	      if (!this.hasValue(key)) {
	        return null;
	      }
	      return babelHelpers.classPrivateFieldGet(this, _data)[key];
	    }
	  }, {
	    key: "hasData",
	    value: function hasData() {
	      return babelHelpers.classPrivateFieldGet(this, _data) !== null;
	    }
	  }, {
	    key: "getType",
	    value: function getType() {
	      return '';
	    }
	  }, {
	    key: "getPage",
	    value: function getPage() {
	      var _this$getPermission;
	      if (!((_this$getPermission = this.getPermission()) !== null && _this$getPermission !== void 0 && _this$getPermission.canRead())) {
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div id=\"", "-page-wrapper\"></div>"])), this.getType());
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _page)) {
	        return babelHelpers.classPrivateFieldGet(this, _page);
	      }
	      if (main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _data))) {
	        _classPrivateMethodGet$1(this, _fetchData, _fetchData2).call(this);
	      }
	      babelHelpers.classPrivateFieldSet(this, _page, main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div id=\"", "-page-wrapper\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), this.getType(), main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _data)) ? LoaderPage.getWrapper() : this.render()));
	      return babelHelpers.classPrivateFieldGet(this, _page);
	    }
	  }, {
	    key: "reload",
	    value: function reload() {
	      main_core.Dom.remove(this.render());
	      babelHelpers.classPrivateFieldSet(this, _content, null);
	      babelHelpers.classPrivateFieldSet(this, _data, null);
	      main_core.Dom.append(LoaderPage.getWrapper(), this.getPage());
	      _classPrivateMethodGet$1(this, _fetchData, _fetchData2).call(this);
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var _this2 = this;
	      if (babelHelpers.classPrivateFieldGet(this, _content)) {
	        return babelHelpers.classPrivateFieldGet(this, _content);
	      }
	      babelHelpers.classPrivateFieldSet(this, _content, main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div>\n\t\t\t\t<div class=\"intranet-settings__page-header_wrap\">\n\t\t\t\t\t<div class=\"intranet-settings__page-header_inner\">\n\t\t\t\t\t\t<h1 class=\"intranet-settings__page-header\">", "</h1>\n\t\t\t\t\t\t<p class=\"intranet-settings__page-header_desc\">", "</p>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"intranet-settings__header-widget\"></div>\n\t\t\t\t</div>\n\t\t\t\t<form id=\"form-", "-page\" onsubmit=\"return false;\">\n\t\t\t\t\t<div class=\"intranet-settings__content-box\"></div>\n\t\t\t\t</form>\n\t\t\t</div>\n\t\t"])), this.titlePage, this.descriptionPage, this.getType()));
	      var headerWidget = this.headerWidgetRender();
	      var headerWidgetWrapper = babelHelpers.classPrivateFieldGet(this, _content).querySelector('.intranet-settings__header-widget');
	      if (headerWidget) {
	        main_core.Dom.append(headerWidget, headerWidgetWrapper);
	        main_core.Dom.addClass(babelHelpers.classPrivateFieldGet(this, _content).querySelector('.intranet-settings__page-header_wrap'), '--with-header-widget');
	      } else {
	        main_core.Dom.remove(headerWidgetWrapper);
	      }
	      var formNode = babelHelpers.classPrivateFieldGet(this, _content).querySelector('form');
	      var contentNode = formNode.querySelector('.intranet-settings__content-box');
	      formNode.addEventListener('change', function () {
	        var _this2$getPermission;
	        if ((_this2$getPermission = _this2.getPermission()) !== null && _this2$getPermission !== void 0 && _this2$getPermission.canEdit()) {
	          _this2.emit('change', {
	            source: _this2
	          });
	        }
	      });
	      this.appendSections(contentNode);
	      main_core_events.EventEmitter.emit(main_core_events.EventEmitter.GLOBAL_TARGET, 'BX.Intranet.Settings:onContentFetched', {
	        page: this
	      });
	      return babelHelpers.classPrivateFieldGet(this, _content);
	    }
	  }, {
	    key: "hasContent",
	    value: function hasContent() {
	      return !main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _content));
	    }
	  }, {
	    key: "headerWidgetRender",
	    value: function headerWidgetRender() {
	      return '';
	    }
	  }, {
	    key: "onSuccessDataFetched",
	    value: function onSuccessDataFetched(response) {
	      this.setData(response.data);
	    }
	  }, {
	    key: "setData",
	    value: function setData(data) {
	      babelHelpers.classPrivateFieldSet(this, _data, data);
	      babelHelpers.classPrivateFieldGet(this, _subPage).forEach(function (subPage) {
	        subPage.setData(data);
	      });
	      if (babelHelpers.classPrivateFieldGet(this, _page)) {
	        main_core.Dom.clean(babelHelpers.classPrivateFieldGet(this, _page));
	        babelHelpers.classPrivateFieldSet(this, _content, null);
	        main_core.Dom.append(this.render(), babelHelpers.classPrivateFieldGet(this, _page));
	      }
	      main_core_events.EventEmitter.emit(main_core_events.EventEmitter.GLOBAL_TARGET, 'BX.Intranet.Settings:onPageComplete', {
	        page: this
	      });
	    }
	  }, {
	    key: "onFailDataFetched",
	    value: function onFailDataFetched(response) {
	      ErrorCollection.showSystemError(main_core.Loc.getMessage('INTRANET_SETTINGS_ERROR_FETCH_DATA'));
	    }
	  }, {
	    key: "getFormNode",
	    value: function getFormNode() {
	      return this.render().querySelector('form');
	    }
	  }, {
	    key: "appendSections",
	    value: function appendSections(contentNode) {
	      var sections = this.getSections();
	      babelHelpers.classPrivateFieldGet(this, _subPage).forEach(function (subPage) {
	        sections.push.apply(sections, babelHelpers.toConsumableArray(subPage.getSections()));
	      });
	      sections.sort(function (sectionA, sectionB) {
	        return sectionA.getSectionSort() - sectionB.getSectionSort();
	      }).forEach(function (section) {
	        contentNode.appendChild(section.render());
	      });
	    }
	  }, {
	    key: "expandPage",
	    value: function expandPage(subPageExtensions) {
	      if (main_core.Type.isArray(subPageExtensions)) {
	        var _babelHelpers$classPr;
	        (_babelHelpers$classPr = babelHelpers.classPrivateFieldGet(this, _subPageExtensions)).push.apply(_babelHelpers$classPr, babelHelpers.toConsumableArray(subPageExtensions));
	      }
	      return this;
	    }
	  }, {
	    key: "getSections",
	    value: function getSections() {
	      return [];
	    }
	  }, {
	    key: "helpMessageProviderFactory",
	    value: function helpMessageProviderFactory(message) {
	      message = main_core.Type.isNil(message) ? main_core.Loc.getMessage('INTRANET_SETTINGS_FIELD_HELP_MESSAGE') : message;
	      return function (id, node) {
	        return new ui_section.HelpMessage(id, node, message);
	      };
	    }
	  }], [{
	    key: "addToSectionHelper",
	    value: function addToSectionHelper(fieldView, sectionSettings) {
	      var row = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
	      var settingsField = new SettingsField({
	        fieldView: fieldView
	      });
	      new SettingsRow({
	        row: row,
	        child: settingsField,
	        parent: sectionSettings
	      });
	    }
	  }]);
	  return BaseSettingsPage;
	}(BaseSettingsElement);
	function _fetchData2() {
	  var _this3 = this;
	  new Promise(function (resolve, reject) {
	    main_core.Runtime.loadExtension(babelHelpers.classPrivateFieldGet(_this3, _subPageExtensions)).then(function (exports) {
	      // 1. collect data by Event for old extensions
	      main_core_events.EventEmitter.emit(main_core_events.EventEmitter.GLOBAL_TARGET, 'BX.Intranet.Settings:onPageFetched:' + _this3.getType(), event).forEach(function (subPage) {
	        return babelHelpers.classPrivateFieldGet(_this3, _subPage).set(subPage.getType(), subPage);
	      });
	      // 2. collect data by export for new extensions
	      Object.values(exports).forEach(function (desirableClass) {
	        if (main_core.Type.isObject(desirableClass)) {
	          if (desirableClass.prototype instanceof BaseSettingsPage) {
	            var subPage = new desirableClass();
	            babelHelpers.classPrivateFieldGet(_this3, _subPage).set(subPage.getType(), subPage);
	          } else if (desirableClass instanceof BaseSettingsPage) {
	            var _subPage2 = desirableClass;
	            babelHelpers.classPrivateFieldGet(_this3, _subPage).set(_subPage2.getType(), _subPage2);
	          }
	        }
	      });
	      var event = new main_core_events.BaseEvent();
	      var eventResult = main_core_events.EventEmitter.emit(_this3, 'fetch', event).some(function (ajaxPromise) {
	        if (ajaxPromise instanceof Promise) {
	          ajaxPromise.then(resolve, reject);
	          return true;
	        }
	        return false;
	      });
	      if (eventResult !== true) {
	        reject({
	          error: 'The handler for fetching page data was not found. '
	        });
	      }
	    });
	  }).then(this.onSuccessDataFetched.bind(this), this.onFailDataFetched.bind(this));
	}
	var LoaderPage = /*#__PURE__*/function () {
	  function LoaderPage() {
	    babelHelpers.classCallCheck(this, LoaderPage);
	  }
	  babelHelpers.createClass(LoaderPage, null, [{
	    key: "getWrapper",
	    value: function getWrapper() {
	      if (_classStaticPrivateFieldSpecGet(LoaderPage, LoaderPage, _wrapper)) {
	        return _classStaticPrivateFieldSpecGet(LoaderPage, LoaderPage, _wrapper);
	      }
	      _classStaticPrivateFieldSpecSet(LoaderPage, LoaderPage, _wrapper, main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"intranet-settings__loader\"></div>\n\t\t"]))));
	      // const loader = new Loader({target: LoaderPage.#wrapper, size: 200});
	      // loader.show();

	      return _classStaticPrivateFieldSpecGet(LoaderPage, LoaderPage, _wrapper);
	    }
	  }]);
	  return LoaderPage;
	}();
	var _wrapper = {
	  writable: true,
	  value: void 0
	};

	function _classPrivateMethodInitSpec$2(obj, privateSet) { _checkPrivateRedeclaration$5(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$5(obj, privateMap, value) { _checkPrivateRedeclaration$5(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$5(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$2(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _filterCallback = /*#__PURE__*/new WeakMap();
	var _result = /*#__PURE__*/new WeakMap();
	var _do = /*#__PURE__*/new WeakSet();
	var RecursiveFilteringVisitor = /*#__PURE__*/function (_BaseSettingsVisitor) {
	  babelHelpers.inherits(RecursiveFilteringVisitor, _BaseSettingsVisitor);
	  function RecursiveFilteringVisitor() {
	    var _babelHelpers$getProt;
	    var _this;
	    babelHelpers.classCallCheck(this, RecursiveFilteringVisitor);
	    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	      args[_key] = arguments[_key];
	    }
	    _this = babelHelpers.possibleConstructorReturn(this, (_babelHelpers$getProt = babelHelpers.getPrototypeOf(RecursiveFilteringVisitor)).call.apply(_babelHelpers$getProt, [this].concat(args)));
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _do);
	    _classPrivateFieldInitSpec$5(babelHelpers.assertThisInitialized(_this), _filterCallback, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$5(babelHelpers.assertThisInitialized(_this), _result, {
	      writable: true,
	      value: []
	    });
	    return _this;
	  }
	  babelHelpers.createClass(RecursiveFilteringVisitor, [{
	    key: "setFilter",
	    value: function setFilter(filterStrategy) {
	      babelHelpers.classPrivateFieldSet(this, _filterCallback, filterStrategy);
	      return this;
	    }
	  }, {
	    key: "restart",
	    value: function restart(startElement) {
	      babelHelpers.classPrivateFieldSet(this, _result, []);
	      this.visitSettingsElement(startElement);
	      return babelHelpers.classPrivateFieldGet(this, _result);
	    }
	  }, {
	    key: "visitSettingsElement",
	    value: function visitSettingsElement(element) {
	      var _this2 = this;
	      if (_classPrivateMethodGet$2(this, _do, _do2).call(this, element)) {
	        babelHelpers.classPrivateFieldGet(this, _result).push(element);
	      }
	      if (element.getChildrenElements().length > 0) {
	        element.getChildrenElements().forEach(function (childElement) {
	          _this2.visitSettingsElement(childElement);
	        });
	      }
	    }
	  }], [{
	    key: "startFrom",
	    value: function startFrom(startElement, filterStrategy) {
	      return this.getInstance().setFilter(filterStrategy).restart(startElement);
	    }
	  }, {
	    key: "getInstance",
	    value: function getInstance() {
	      if (!this.instance) {
	        this.instance = new this();
	      }
	      return this.instance;
	    }
	  }]);
	  return RecursiveFilteringVisitor;
	}(BaseSettingsVisitor);
	function _do2(element) {
	  if (typeof babelHelpers.classPrivateFieldGet(this, _filterCallback) === 'function') {
	    return babelHelpers.classPrivateFieldGet(this, _filterCallback).call(this, element) === true;
	  }
	  return false;
	}

	function _classPrivateMethodInitSpec$3(obj, privateSet) { _checkPrivateRedeclaration$6(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$6(obj, privateMap, value) { _checkPrivateRedeclaration$6(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$6(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$3(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _filterCallback$1 = /*#__PURE__*/new WeakMap();
	var _result$1 = /*#__PURE__*/new WeakMap();
	var _do$1 = /*#__PURE__*/new WeakSet();
	var AscendingOpeningVisitor = /*#__PURE__*/function (_BaseSettingsVisitor) {
	  babelHelpers.inherits(AscendingOpeningVisitor, _BaseSettingsVisitor);
	  function AscendingOpeningVisitor() {
	    var _babelHelpers$getProt;
	    var _this;
	    babelHelpers.classCallCheck(this, AscendingOpeningVisitor);
	    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	      args[_key] = arguments[_key];
	    }
	    _this = babelHelpers.possibleConstructorReturn(this, (_babelHelpers$getProt = babelHelpers.getPrototypeOf(AscendingOpeningVisitor)).call.apply(_babelHelpers$getProt, [this].concat(args)));
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _do$1);
	    _classPrivateFieldInitSpec$6(babelHelpers.assertThisInitialized(_this), _filterCallback$1, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$6(babelHelpers.assertThisInitialized(_this), _result$1, {
	      writable: true,
	      value: []
	    });
	    return _this;
	  }
	  babelHelpers.createClass(AscendingOpeningVisitor, [{
	    key: "setFilter",
	    value: function setFilter(filterStrategy) {
	      babelHelpers.classPrivateFieldSet(this, _filterCallback$1, filterStrategy);
	      return this;
	    }
	  }, {
	    key: "restart",
	    value: function restart(startElement) {
	      babelHelpers.classPrivateFieldSet(this, _result$1, []);
	      this.visitSettingsElement(startElement);
	      return babelHelpers.classPrivateFieldGet(this, _result$1);
	    }
	  }, {
	    key: "visitSettingsElement",
	    value: function visitSettingsElement(element) {
	      if (_classPrivateMethodGet$3(this, _do$1, _do2$1).call(this, element)) {
	        babelHelpers.classPrivateFieldGet(this, _result$1).push(element);
	      }
	      if (element.getParentElement()) {
	        this.visitSettingsElement(element.getParentElement());
	      }
	    }
	  }], [{
	    key: "startFrom",
	    value: function startFrom(startElement, filterStrategy) {
	      return this.getInstance().setFilter(filterStrategy).restart(startElement);
	    }
	  }, {
	    key: "getInstance",
	    value: function getInstance() {
	      if (!this.instance) {
	        this.instance = new this();
	      }
	      return this.instance;
	    }
	  }]);
	  return AscendingOpeningVisitor;
	}(BaseSettingsVisitor);
	function _do2$1(element) {
	  if (typeof babelHelpers.classPrivateFieldGet(this, _filterCallback$1) === 'function') {
	    return babelHelpers.classPrivateFieldGet(this, _filterCallback$1).call(this, element) === true;
	  }
	  return false;
	}

	function _createForOfIteratorHelper$4(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$4(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray$4(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$4(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$4(o, minLen); }
	function _arrayLikeToArray$4(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classPrivateFieldInitSpec$7(obj, privateMap, value) { _checkPrivateRedeclaration$7(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$7(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _fieldView$1 = /*#__PURE__*/new WeakMap();
	var TabField = /*#__PURE__*/function (_BaseSettingsElement) {
	  babelHelpers.inherits(TabField, _BaseSettingsElement);
	  function TabField(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, TabField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TabField).call(this, params));
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _fieldView$1, {
	      writable: true,
	      value: void 0
	    });
	    _this.setParentElement(params.parent);
	    if (params.fieldView instanceof ui_tabs.Tab) {
	      babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _fieldView$1, params.fieldView);
	    } else if (params.tabsOptions) {
	      babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _fieldView$1, new ui_tabs.Tab(params.tabsOptions));
	    } else {
	      throw new Error('Tab field in Settings is not correct.');
	    }
	    if (params.parent.getFieldView() instanceof ui_tabs.Tabs) {
	      params.parent.getFieldView().addItem(babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _fieldView$1));
	    }
	    if (_this.getParentElement() instanceof ui_formElements_field.TabsField) {
	      babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _fieldView$1).subscribe('changeTab', function () {
	        _this.getParentElement().activateTab(babelHelpers.assertThisInitialized(_this));
	      });
	    }
	    return _this;
	  }
	  babelHelpers.createClass(TabField, [{
	    key: "getFieldView",
	    value: function getFieldView() {
	      return babelHelpers.classPrivateFieldGet(this, _fieldView$1);
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var _iterator = _createForOfIteratorHelper$4(this.getChildrenElements()),
	        _step;
	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var element = _step.value;
	          main_core.Dom.append(element.render(), this.getFieldView().getBodyDataContainer());
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }
	      return this.getFieldView().getBody();
	    }
	  }, {
	    key: "highlight",
	    value: function highlight() {
	      this.highlightElement(this.getFieldView().getBody());
	      this.highlightElement(this.getFieldView().getHeader());
	      return true;
	    }
	  }]);
	  return TabField;
	}(BaseSettingsElement);

	function _classPrivateFieldInitSpec$8(obj, privateMap, value) { _checkPrivateRedeclaration$8(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$8(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _fieldView$2 = /*#__PURE__*/new WeakMap();
	var TabsField = /*#__PURE__*/function (_BaseSettingsElement) {
	  babelHelpers.inherits(TabsField, _BaseSettingsElement);
	  function TabsField(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, TabsField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TabsField).call(this, params));
	    _classPrivateFieldInitSpec$8(babelHelpers.assertThisInitialized(_this), _fieldView$2, {
	      writable: true,
	      value: void 0
	    });
	    _this.setParentElement(params.parent);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _fieldView$2, new ui_tabs.Tabs(params.tabsOptions));
	    babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _fieldView$2).getItems().forEach(function (tab) {
	      new TabField({
	        parent: babelHelpers.assertThisInitialized(_this),
	        fieldView: tab
	      });
	    });
	    return _this;
	  }
	  babelHelpers.createClass(TabsField, [{
	    key: "activateTab",
	    value: function activateTab(tabField) {
	      var withAnimation = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
	      this.getFieldView().activateItem(tabField.getFieldView(), withAnimation);
	      tabField.render();
	    }
	  }, {
	    key: "getFieldView",
	    value: function getFieldView() {
	      return babelHelpers.classPrivateFieldGet(this, _fieldView$2);
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      return babelHelpers.classPrivateFieldGet(this, _fieldView$2).getContainer();
	    }
	  }]);
	  return TabsField;
	}(BaseSettingsElement);

	exports.BaseSettingsElement = BaseSettingsElement;
	exports.BaseSettingsPage = BaseSettingsPage;
	exports.BaseSettingsVisitor = BaseSettingsVisitor;
	exports.RecursiveFilteringVisitor = RecursiveFilteringVisitor;
	exports.AscendingOpeningVisitor = AscendingOpeningVisitor;
	exports.ErrorCollection = ErrorCollection;
	exports.SettingsField = SettingsField;
	exports.SettingsRow = SettingsRow;
	exports.SettingsSection = SettingsSection;
	exports.TabsField = TabsField;
	exports.TabField = TabField;

}((this.BX.UI.FormElements = this.BX.UI.FormElements || {}),BX.Collections,BX.UI.FormElements,BX.Event,BX.UI,BX,BX.UI.FormElements,BX.UI));
//# sourceMappingURL=field.bundle.js.map
