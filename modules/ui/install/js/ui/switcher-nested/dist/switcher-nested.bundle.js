/* eslint-disable */
this.BX = this.BX || {};
(function (exports,ui_draganddrop_draggable,main_core,main_core_events,main_popup,ui_section,ui_switcher) {
	'use strict';

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _popup = /*#__PURE__*/new WeakMap();
	var _id = /*#__PURE__*/new WeakMap();
	var _bindElement = /*#__PURE__*/new WeakMap();
	var _message = /*#__PURE__*/new WeakMap();
	var _getPopup = /*#__PURE__*/new WeakSet();
	var WarningMessage = /*#__PURE__*/function () {
	  function WarningMessage(options) {
	    babelHelpers.classCallCheck(this, WarningMessage);
	    _classPrivateMethodInitSpec(this, _getPopup);
	    _classPrivateFieldInitSpec(this, _popup, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _id, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _bindElement, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _message, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _id, options.id);
	    babelHelpers.classPrivateFieldSet(this, _bindElement, options.bindElement);
	    babelHelpers.classPrivateFieldSet(this, _message, options.message);
	  }
	  babelHelpers.createClass(WarningMessage, [{
	    key: "show",
	    value: function show() {
	      _classPrivateMethodGet(this, _getPopup, _getPopup2).call(this).show();
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      _classPrivateMethodGet(this, _getPopup, _getPopup2).call(this).close();
	    }
	  }]);
	  return WarningMessage;
	}();
	function _getPopup2() {
	  if (babelHelpers.classPrivateFieldGet(this, _popup)) {
	    return babelHelpers.classPrivateFieldGet(this, _popup);
	  }
	  babelHelpers.classPrivateFieldSet(this, _popup, new main_popup.Popup({
	    id: babelHelpers.classPrivateFieldGet(this, _id),
	    bindElement: babelHelpers.classPrivateFieldGet(this, _bindElement),
	    content: babelHelpers.classPrivateFieldGet(this, _message),
	    darkMode: true,
	    autoHide: true,
	    angle: true,
	    offsetLeft: 14,
	    bindOptions: {
	      position: 'bottom'
	    },
	    closeByEsc: true
	  }));
	  return babelHelpers.classPrivateFieldGet(this, _popup);
	}

	var _templateObject, _templateObject2, _templateObject3;
	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _node = /*#__PURE__*/new WeakMap();
	var _id$1 = /*#__PURE__*/new WeakMap();
	var _inputName = /*#__PURE__*/new WeakMap();
	var _title = /*#__PURE__*/new WeakMap();
	var _switcher = /*#__PURE__*/new WeakMap();
	var _isChecked = /*#__PURE__*/new WeakMap();
	var _settingsPath = /*#__PURE__*/new WeakMap();
	var _settingsTitle = /*#__PURE__*/new WeakMap();
	var _infoHelperCode = /*#__PURE__*/new WeakMap();
	var _warning = /*#__PURE__*/new WeakMap();
	var _warningMessage = /*#__PURE__*/new WeakMap();
	var _isDefault = /*#__PURE__*/new WeakMap();
	var _isDisabled = /*#__PURE__*/new WeakMap();
	var _getLink = /*#__PURE__*/new WeakSet();
	var SwitcherNestedItem = /*#__PURE__*/function () {
	  function SwitcherNestedItem(options) {
	    var _this = this;
	    babelHelpers.classCallCheck(this, SwitcherNestedItem);
	    _classPrivateMethodInitSpec$1(this, _getLink);
	    _classPrivateFieldInitSpec$1(this, _node, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(this, _id$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(this, _inputName, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(this, _title, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(this, _switcher, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(this, _isChecked, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(this, _settingsPath, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(this, _settingsTitle, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(this, _infoHelperCode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(this, _warning, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(this, _warningMessage, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(this, _isDefault, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(this, _isDisabled, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _id$1, options.id);
	    babelHelpers.classPrivateFieldSet(this, _inputName, options.inputName);
	    babelHelpers.classPrivateFieldSet(this, _title, options.title);
	    babelHelpers.classPrivateFieldSet(this, _isChecked, options.isChecked);
	    babelHelpers.classPrivateFieldSet(this, _settingsPath, main_core.Type.isString(options.settingsPath) ? options.settingsPath : null);
	    babelHelpers.classPrivateFieldSet(this, _settingsTitle, main_core.Type.isString(options.settingsTitle) ? options.settingsTitle : null);
	    babelHelpers.classPrivateFieldSet(this, _infoHelperCode, main_core.Type.isString(options.infoHelperCode) ? options.infoHelperCode : null);
	    babelHelpers.classPrivateFieldSet(this, _isDefault, main_core.Type.isBoolean(options.isDefault) ? options.isDefault : false);
	    babelHelpers.classPrivateFieldSet(this, _isDisabled, main_core.Type.isBoolean(options.isDisabled) ? options.isDisabled : false);
	    babelHelpers.classPrivateFieldSet(this, _warningMessage, options.helpMessage);
	    main_core.Event.bind(this.getSwitcher().getNode(), 'click', function () {
	      if (babelHelpers.classPrivateFieldGet(_this, _isDisabled)) {
	        _this.getWarningMessage().show();
	        _this.getSwitcher().check(babelHelpers.classPrivateFieldGet(_this, _isChecked), false);
	      } else if (babelHelpers.classPrivateFieldGet(_this, _isDefault) && !_this.getSwitcher().isChecked()) {
	        _this.getSwitcher().check(true, false);
	        _this.getWarningMessage().show();
	      }
	    });
	  }
	  babelHelpers.createClass(SwitcherNestedItem, [{
	    key: "getId",
	    value: function getId() {
	      return babelHelpers.classPrivateFieldGet(this, _id$1);
	    }
	  }, {
	    key: "isDefault",
	    value: function isDefault() {
	      return babelHelpers.classPrivateFieldGet(this, _isDefault);
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      if (babelHelpers.classPrivateFieldGet(this, _node)) {
	        return babelHelpers.classPrivateFieldGet(this, _node);
	      }
	      babelHelpers.classPrivateFieldSet(this, _node, main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-section__row-tool-selector --tool-selector", "\">\n\t\t\t\t<div class=\"ui-section__tools-subgroup_left-wrapper\">\n\t\t\t\t\t<div class=\"ui-section__switcher-row_wrapper\"/>\n\t\t\t\t\t<div class=\"ui-section__row-tool-selector_title\">", "</div>\n\t\t\t\t</div>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), babelHelpers.classPrivateFieldGet(this, _isChecked) ? ' --active --checked' : '', babelHelpers.classPrivateFieldGet(this, _title), _classPrivateMethodGet$1(this, _getLink, _getLink2).call(this)));
	      return babelHelpers.classPrivateFieldGet(this, _node);
	    }
	  }, {
	    key: "getSwitcher",
	    value: function getSwitcher() {
	      if (babelHelpers.classPrivateFieldGet(this, _switcher) instanceof ui_switcher.Switcher) {
	        return babelHelpers.classPrivateFieldGet(this, _switcher);
	      }
	      babelHelpers.classPrivateFieldSet(this, _switcher, this.createSwitcher(this.render().querySelector('.ui-section__switcher-row_wrapper')));
	      return babelHelpers.classPrivateFieldGet(this, _switcher);
	    }
	  }, {
	    key: "createSwitcher",
	    value: function createSwitcher(node) {
	      var _this2 = this;
	      return new ui_switcher.Switcher({
	        inputName: babelHelpers.classPrivateFieldGet(this, _inputName),
	        node: node,
	        checked: babelHelpers.classPrivateFieldGet(this, _isChecked),
	        id: babelHelpers.classPrivateFieldGet(this, _id$1),
	        size: ui_switcher.SwitcherSize.extraSmall,
	        handlers: {
	          checked: function checked() {
	            // There is in error in Switcher UI, so we have inversion in event names.
	            if (!babelHelpers.classPrivateFieldGet(_this2, _isDisabled) && !babelHelpers.classPrivateFieldGet(_this2, _isDefault)) {
	              main_core.Dom.removeClass(_this2.render(), '--active --checked');
	              main_core_events.EventEmitter.emit(_this2.getSwitcher(), 'inactive');
	            }
	          },
	          unchecked: function unchecked() {
	            if (!babelHelpers.classPrivateFieldGet(_this2, _isDisabled)) {
	              main_core.Dom.addClass(_this2.render(), '--active --checked');
	              main_core_events.EventEmitter.emit(_this2.getSwitcher(), 'active');
	            }
	          }
	        }
	      });
	    }
	  }, {
	    key: "renderTo",
	    value: function renderTo(targetNode) {
	      if (!main_core.Type.isDomNode(targetNode)) {
	        throw new Error('Target node must be HTMLElement');
	      }
	      return main_core.Dom.append(this.render(), targetNode);
	    }
	  }, {
	    key: "getWarningMessage",
	    value: function getWarningMessage() {
	      if (babelHelpers.classPrivateFieldGet(this, _warning)) {
	        return babelHelpers.classPrivateFieldGet(this, _warning);
	      }
	      babelHelpers.classPrivateFieldSet(this, _warning, new WarningMessage({
	        id: this.getId(),
	        bindElement: this.getSwitcher().getNode(),
	        message: babelHelpers.classPrivateFieldGet(this, _warningMessage)
	      }));
	      return babelHelpers.classPrivateFieldGet(this, _warning);
	    }
	  }]);
	  return SwitcherNestedItem;
	}();
	function _getLink2() {
	  if (main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _settingsTitle))) {
	    return null;
	  }
	  if (!main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _settingsPath))) {
	    return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<a target=\"_blank\" data-slider-ignore-autobinding=\"true\" href=\"", "\" class=\"ui-section__tools-subgroup-description-link\">", "</a>\n\t\t\t"])), babelHelpers.classPrivateFieldGet(this, _settingsPath), babelHelpers.classPrivateFieldGet(this, _settingsTitle));
	  }
	  if (!main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _infoHelperCode))) {
	    return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<a href=\"javascript:top.BX.UI.InfoHelper.show('", "')\" class=\"ui-section__tools-subgroup-description-link\">", "</a>\n\t\t\t"])), babelHelpers.classPrivateFieldGet(this, _infoHelperCode), babelHelpers.classPrivateFieldGet(this, _settingsTitle));
	  }
	  return null;
	}

	var _templateObject$1, _templateObject2$1, _templateObject3$1, _templateObject4, _templateObject5;
	function _classPrivateMethodInitSpec$2(obj, privateSet) { _checkPrivateRedeclaration$2(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$2(obj, privateMap, value) { _checkPrivateRedeclaration$2(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$2(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$2(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _mainTool = /*#__PURE__*/new WeakMap();
	var _sectionWrapper = /*#__PURE__*/new WeakMap();
	var _isDefault$1 = /*#__PURE__*/new WeakMap();
	var _isDisabled$1 = /*#__PURE__*/new WeakMap();
	var _warningMessage$1 = /*#__PURE__*/new WeakMap();
	var _helpMessage = /*#__PURE__*/new WeakMap();
	var _draggable = /*#__PURE__*/new WeakMap();
	var _turnOnMainAndRequiredTools = /*#__PURE__*/new WeakSet();
	var _turnOffDispensableTools = /*#__PURE__*/new WeakSet();
	var _getMenuIcon = /*#__PURE__*/new WeakSet();
	var _getDraggableIcon = /*#__PURE__*/new WeakSet();
	var _getLink$1 = /*#__PURE__*/new WeakSet();
	var _createSwitcher = /*#__PURE__*/new WeakSet();
	var SwitcherNested = /*#__PURE__*/function (_Section) {
	  babelHelpers.inherits(SwitcherNested, _Section);
	  function SwitcherNested(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, SwitcherNested);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SwitcherNested).call(this, options));
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _createSwitcher);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _getLink$1);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _getDraggableIcon);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _getMenuIcon);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _turnOffDispensableTools);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _turnOnMainAndRequiredTools);
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _mainTool, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _sectionWrapper, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _isDefault$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _isDisabled$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _warningMessage$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _helpMessage, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _draggable, {
	      writable: true,
	      value: null
	    });
	    _this.linkTitle = main_core.Type.isString(options.linkTitle) ? options.linkTitle : null;
	    _this.link = main_core.Type.isString(options.link) ? options.link : null;
	    _this.isChecked = main_core.Type.isBoolean(options.isChecked) ? options.isChecked : false;
	    _this.items = main_core.Type.isArray(options.items) ? options.items : [];
	    _this.isNestedMenu = _this.items.length > 0;
	    _this.infoHelperCode = main_core.Type.isString(options.infoHelperCode) ? options.infoHelperCode : null;
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _isDefault$1, main_core.Type.isBoolean(options.isDefault) ? options.isDefault : false);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _isDisabled$1, main_core.Type.isBoolean(options.isDisabled) ? options.isDisabled : false);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _helpMessage, main_core.Type.isString(options.helpMessage) ? options.helpMessage : null);
	    if (options.draggable instanceof ui_draganddrop_draggable.Draggable) {
	      babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _draggable, options.draggable);
	    }
	    if (!main_core.Type.isString(options.mainInputName)) {
	      throw new Error('Missing required parameter');
	    }
	    _this.mainInputName = options.mainInputName;
	    _this.render();
	    _this.items.forEach(function (item) {
	      _this.append(item.render());
	    });
	    return _this;
	  }
	  babelHelpers.createClass(SwitcherNested, [{
	    key: "getContent",
	    value: function getContent() {
	      var _classPrivateMethodGe,
	        _this2 = this;
	      if (babelHelpers.classPrivateFieldGet(this, _sectionWrapper)) {
	        return babelHelpers.classPrivateFieldGet(this, _sectionWrapper);
	      }
	      babelHelpers.classPrivateFieldSet(this, _sectionWrapper, main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-section__wrapper --tool-selector", " ", "\" >\n\t\t\t\t<div class=\"ui-section__header\">\n\t\t\t\t\t<div class=\"ui-section__header-left-wrapper\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t<span class=\"ui-section__switcher-wrapper\" onclick=\"event.stopPropagation()\"/>\n\t\t\t\t\t\t<span class=\"ui-section__title\">", "</span>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t<div class=\"ui-section__content ui-section__section-body_inner\">\n\t\t\t\t\t<div class=\"ui-section__row_box\"></div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), this.isChecked ? ' --checked' : '', this.isNestedMenu ? ' clickable' : '', (_classPrivateMethodGe = _classPrivateMethodGet$2(this, _getDraggableIcon, _getDraggableIcon2).call(this)) !== null && _classPrivateMethodGe !== void 0 ? _classPrivateMethodGe : '', this.title, _classPrivateMethodGet$2(this, _getMenuIcon, _getMenuIcon2).call(this), _classPrivateMethodGet$2(this, _getLink$1, _getLink2$1).call(this)));
	      babelHelpers.classPrivateFieldSet(this, _mainTool, _classPrivateMethodGet$2(this, _createSwitcher, _createSwitcher2).call(this, babelHelpers.classPrivateFieldGet(this, _sectionWrapper).querySelector('.ui-section__switcher-wrapper')));
	      if (babelHelpers.classPrivateFieldGet(this, _helpMessage)) {
	        babelHelpers.classPrivateFieldSet(this, _warningMessage$1, this.getWarningMessage(babelHelpers.classPrivateFieldGet(this, _helpMessage)));
	      }
	      main_core_events.EventEmitter.subscribe(babelHelpers.classPrivateFieldGet(this, _mainTool), 'toggled', function () {
	        if (babelHelpers.classPrivateFieldGet(_this2, _isDisabled$1)) {
	          babelHelpers.classPrivateFieldGet(_this2, _mainTool).check(!babelHelpers.classPrivateFieldGet(_this2, _mainTool).isChecked(), false);
	          if (babelHelpers.classPrivateFieldGet(_this2, _warningMessage$1)) {
	            babelHelpers.classPrivateFieldGet(_this2, _warningMessage$1).show();
	          }
	        } else if (babelHelpers.classPrivateFieldGet(_this2, _isDefault$1)) {
	          babelHelpers.classPrivateFieldGet(_this2, _mainTool).check(true, false);
	          if (babelHelpers.classPrivateFieldGet(_this2, _warningMessage$1)) {
	            babelHelpers.classPrivateFieldGet(_this2, _warningMessage$1).show();
	          }
	        } else {
	          _this2.toggle(babelHelpers.classPrivateFieldGet(_this2, _mainTool).isChecked());
	          babelHelpers.classPrivateFieldGet(_this2, _mainTool).inputNode.form.dispatchEvent(new Event('change'));
	          main_core.Dom[babelHelpers.classPrivateFieldGet(_this2, _mainTool).isChecked() ? 'addClass' : 'removeClass'](babelHelpers.classPrivateFieldGet(_this2, _sectionWrapper), '--checked');
	          _this2.items.forEach(function (item) {
	            return item.getSwitcher().check(babelHelpers.classPrivateFieldGet(_this2, _mainTool).isChecked());
	          });
	        }
	      });
	      this.items.forEach(function (item) {
	        if (item.isDefault() !== true)
	          // if only this item is not required
	          {
	            main_core_events.EventEmitter.subscribe(item.getSwitcher(), 'inactive', _classPrivateMethodGet$2(_this2, _turnOffDispensableTools, _turnOffDispensableTools2).bind(_this2));
	          }
	        main_core_events.EventEmitter.subscribe(item.getSwitcher(), 'active', _classPrivateMethodGet$2(_this2, _turnOnMainAndRequiredTools, _turnOnMainAndRequiredTools2).bind(_this2));
	      });
	      return babelHelpers.classPrivateFieldGet(this, _sectionWrapper);
	    }
	  }, {
	    key: "getWarningMessage",
	    value: function getWarningMessage(message) {
	      if (babelHelpers.classPrivateFieldGet(this, _warningMessage$1)) {
	        return babelHelpers.classPrivateFieldGet(this, _warningMessage$1);
	      }
	      babelHelpers.classPrivateFieldSet(this, _warningMessage$1, new WarningMessage({
	        id: this.id,
	        bindElement: babelHelpers.classPrivateFieldGet(this, _mainTool).getNode(),
	        message: message
	      }));
	      return babelHelpers.classPrivateFieldGet(this, _warningMessage$1);
	    }
	  }, {
	    key: "isDefault",
	    value: function isDefault() {
	      return babelHelpers.classPrivateFieldGet(this, _isDefault$1);
	    }
	  }]);
	  return SwitcherNested;
	}(ui_section.Section);
	function _turnOnMainAndRequiredTools2() {
	  babelHelpers.classPrivateFieldGet(this, _mainTool).inputNode.form.dispatchEvent(new Event('change'));
	  if (babelHelpers.classPrivateFieldGet(this, _mainTool).isChecked()) {
	    return;
	  }
	  babelHelpers.classPrivateFieldGet(this, _mainTool).check(true, false);
	  this.toggle(true);
	  main_core.Dom.addClass(babelHelpers.classPrivateFieldGet(this, _sectionWrapper), '--checked');
	  this.items.forEach(function (item) {
	    return item.isDefault() && !item.getSwitcher().isChecked() ? item.getSwitcher().check(true) : null;
	  });
	}
	function _turnOffDispensableTools2() {
	  babelHelpers.classPrivateFieldGet(this, _mainTool).inputNode.form.dispatchEvent(new Event('change'));
	  if (babelHelpers.classPrivateFieldGet(this, _mainTool).isChecked() !== true) {
	    return;
	  }
	  if (this.items.some(function (item) {
	    return item.getSwitcher().isChecked();
	  })) {
	    return;
	  }
	  babelHelpers.classPrivateFieldGet(this, _mainTool).check(false, false);
	  main_core.Dom.removeClass(babelHelpers.classPrivateFieldGet(this, _sectionWrapper), '--checked');
	}
	function _getMenuIcon2() {
	  if (this.isNestedMenu) {
	    return main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span class=\"ui-section__collapse-icon ui-icon-set ", " --tool-selector-icon\"></span>\n\t\t\t"])), this.isOpen ? this.className.arrowTop : this.className.arrowDown);
	  }
	  return null;
	}
	function _getDraggableIcon2() {
	  if (babelHelpers.classPrivateFieldGet(this, _draggable)) {
	    return main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div onclick=\"event.stopPropagation()\" class=\"ui-section__dragdrop-icon-wrapper\">\n\t\t\t\t\t<div onclick=\"event.stopPropagation()\" class=\"ui-section__dragdrop-icon\"/>\n\t\t\t\t</div>\n\t\t\t"])));
	  }
	  return null;
	}
	function _getLink2$1() {
	  if (main_core.Type.isNil(this.linkTitle)) {
	    return null;
	  }
	  if (!main_core.Type.isNil(this.link)) {
	    return main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<a target=\"_blank\" href=\"", "\" class=\"ui-section__header-link ui-section__tools-description-link\" onclick=\"event.stopPropagation()\">", "</a>\n\t\t\t"])), this.link, this.linkTitle);
	  }
	  if (!main_core.Type.isNil(this.infoHelperCode)) {
	    return main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<a href=\"javascript:top.BX.UI.InfoHelper.show('", "')\" class=\"ui-section__header-link ui-section__tools-description-link\" onclick=\"event.stopPropagation()\">", "</a>\n\t\t\t"])), this.infoHelperCode, this.linkTitle);
	  }
	  return null;
	}
	function _createSwitcher2(node) {
	  return new ui_switcher.Switcher({
	    inputName: this.mainInputName,
	    node: node,
	    checked: this.isChecked,
	    id: this.id
	  });
	}

	exports.SwitcherNested = SwitcherNested;
	exports.SwitcherNestedItem = SwitcherNestedItem;
	exports.WarningMessage = WarningMessage;

}((this.BX.UI = this.BX.UI || {}),BX.UI.DragAndDrop,BX,BX.Event,BX.Main,BX.UI,BX.UI));
//# sourceMappingURL=switcher-nested.bundle.js.map
