this.BX = this.BX || {};
(function (exports,main_core,main_core_events,ui_formElements_view,ui_section,ui_switcher) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3;
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _node = /*#__PURE__*/new WeakMap();
	var _id = /*#__PURE__*/new WeakMap();
	var _inputName = /*#__PURE__*/new WeakMap();
	var _title = /*#__PURE__*/new WeakMap();
	var _switcher = /*#__PURE__*/new WeakMap();
	var _field = /*#__PURE__*/new WeakMap();
	var _isChecked = /*#__PURE__*/new WeakMap();
	var _settingsPath = /*#__PURE__*/new WeakMap();
	var _settingsTitle = /*#__PURE__*/new WeakMap();
	var _infoHelperCode = /*#__PURE__*/new WeakMap();
	var NestedSwitcherItem = /*#__PURE__*/function () {
	  function NestedSwitcherItem(params) {
	    babelHelpers.classCallCheck(this, NestedSwitcherItem);
	    _classPrivateFieldInitSpec(this, _node, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _id, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _inputName, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _title, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _switcher, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _field, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _isChecked, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _settingsPath, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _settingsTitle, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _infoHelperCode, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _id, params.id);
	    babelHelpers.classPrivateFieldSet(this, _inputName, params.inputName);
	    babelHelpers.classPrivateFieldSet(this, _title, params.title);
	    babelHelpers.classPrivateFieldSet(this, _isChecked, params.isChecked);
	    babelHelpers.classPrivateFieldSet(this, _settingsPath, main_core.Type.isString(params.settingsPath) ? params.settingsPath : null);
	    babelHelpers.classPrivateFieldSet(this, _settingsTitle, main_core.Type.isString(params.settingsTitle) ? params.settingsTitle : null);
	    babelHelpers.classPrivateFieldSet(this, _infoHelperCode, main_core.Type.isString(params.infoHelperCode) ? params.infoHelperCode : null);
	    this.isDefault = main_core.Type.isBoolean(params.isDefault) ? params.isDefault : false;
	    this.getSwitcher();
	    babelHelpers.classPrivateFieldSet(this, _field, new ui_formElements_view.SingleChecker({
	      switcher: this.getSwitcher(),
	      inputName: params.inputName,
	      isEnable: !this.isDefault,
	      helpMessageProvider: this.getHelpMessageProvider(params.id, babelHelpers.classPrivateFieldGet(this, _switcher).getNode(), params.helpMessage)
	    }));
	  }
	  babelHelpers.createClass(NestedSwitcherItem, [{
	    key: "getId",
	    value: function getId() {
	      return babelHelpers.classPrivateFieldGet(this, _id);
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      if (babelHelpers.classPrivateFieldGet(this, _node)) {
	        return babelHelpers.classPrivateFieldGet(this, _node);
	      }
	      babelHelpers.classPrivateFieldSet(this, _node, main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-section__row-tool-selector --tool-selector", "\">\n\t\t\t\t<div class=\"ui-section__tools-subgroup_left-wrapper\">\n\t\t\t\t\t<div class=\"ui-section__switcher-row_wrapper\"/>\n\t\t\t\t\t<div class=\"ui-section__row-tool-selector_title\">", "</div>\n\t\t\t\t</div>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), babelHelpers.classPrivateFieldGet(this, _isChecked) ? ' --active --checked' : '', babelHelpers.classPrivateFieldGet(this, _title), this.getLink()));
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
	    key: "getField",
	    value: function getField() {
	      return babelHelpers.classPrivateFieldGet(this, _field);
	    }
	  }, {
	    key: "createSwitcher",
	    value: function createSwitcher(node) {
	      var _this = this;
	      return new ui_switcher.Switcher({
	        inputName: babelHelpers.classPrivateFieldGet(this, _inputName),
	        node: node,
	        checked: babelHelpers.classPrivateFieldGet(this, _isChecked),
	        id: babelHelpers.classPrivateFieldGet(this, _id),
	        size: ui_switcher.SwitcherSize.extraSmall,
	        handlers: {
	          'checked': function checked() {
	            // There is in error in Switcher UI, so we have inversion in event names.
	            main_core.Dom.removeClass(_this.render(), '--active --checked');
	            main_core_events.EventEmitter.emit(_this.getSwitcher(), 'inactive');
	          },
	          'unchecked': function unchecked() {
	            main_core.Dom.addClass(_this.render(), '--active --checked');
	            main_core_events.EventEmitter.emit(_this.getSwitcher(), 'active');
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
	    key: "getLink",
	    value: function getLink() {
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
	  }, {
	    key: "getHelpMessageProvider",
	    value: function getHelpMessageProvider(id, node, message) {
	      return function () {
	        var helpMessagePopup = new ui_section.HelpMessage(id, node, message);
	        helpMessagePopup.getPopup().setOffset({
	          offsetLeft: 14
	        });
	        return helpMessagePopup;
	      };
	    }
	  }]);
	  return NestedSwitcherItem;
	}();

	var _templateObject$1, _templateObject2$1, _templateObject3$1, _templateObject4;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _mainTool = /*#__PURE__*/new WeakMap();
	var _sectionWrapper = /*#__PURE__*/new WeakMap();
	var _turnOnMainAndRequiredTools = /*#__PURE__*/new WeakSet();
	var _turnOffUnrequiredTools = /*#__PURE__*/new WeakSet();
	var NestedSwitcher = /*#__PURE__*/function (_Section) {
	  babelHelpers.inherits(NestedSwitcher, _Section);
	  function NestedSwitcher(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, NestedSwitcher);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(NestedSwitcher).call(this, options));
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _turnOffUnrequiredTools);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _turnOnMainAndRequiredTools);
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _mainTool, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _sectionWrapper, {
	      writable: true,
	      value: void 0
	    });
	    _this.linkTitle = main_core.Type.isString(options.linkTitle) ? options.linkTitle : null;
	    _this.link = main_core.Type.isString(options.link) ? options.link : null;
	    _this.isChecked = main_core.Type.isBoolean(options.isChecked) ? options.isChecked : false;
	    _this.items = main_core.Type.isArray(options.items) ? options.items : [];
	    _this.isNestedMenu = _this.items.length > 0;
	    _this.infoHelperCode = main_core.Type.isString(options.infoHelperCode) ? options.infoHelperCode : null;
	    if (!main_core.Type.isString(options.mainInputName)) {
	      throw new Error('Missing required parameter');
	    }
	    _this.mainInputName = options.mainInputName;
	    _this.render();
	    _this.items.forEach(function (item) {
	      _this.append(item.render());
	    });
	    _this.field = new ui_formElements_view.SingleChecker({
	      switcher: babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _mainTool)
	    });
	    return _this;
	  }
	  babelHelpers.createClass(NestedSwitcher, [{
	    key: "getContent",
	    value: function getContent() {
	      var _this2 = this;
	      if (babelHelpers.classPrivateFieldGet(this, _sectionWrapper)) {
	        return babelHelpers.classPrivateFieldGet(this, _sectionWrapper);
	      }
	      babelHelpers.classPrivateFieldSet(this, _sectionWrapper, main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div id=\"", "\" class=\"ui-section__wrapper --tool-selector", " ", "\" >\n\t\t\t\t<div class=\"ui-section__header\">\n\t\t\t\t\t<div class=\"ui-section__header-left-wrapper\">\n\t\t\t\t\t\t<span class=\"ui-section__switcher-wrapper\" onclick=\"event.stopPropagation()\"/>\n\t\t\t\t\t\t<span class=\"ui-section__title\">", "</span>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t<div class=\"ui-section__content ui-section__section-body_inner\">\n\t\t\t\t\t<div class=\"ui-section__row_box\"></div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), this.id, this.isChecked ? ' --checked' : '', this.canCollapse ? ' clickable' : '', this.title, this.getMenuIcon(), this.getLink()));
	      babelHelpers.classPrivateFieldSet(this, _mainTool, this.createSwitcher(babelHelpers.classPrivateFieldGet(this, _sectionWrapper).querySelector('.ui-section__switcher-wrapper')));
	      main_core_events.EventEmitter.subscribe(babelHelpers.classPrivateFieldGet(this, _mainTool), 'toggled', function () {
	        _this2.toggle(babelHelpers.classPrivateFieldGet(_this2, _mainTool).isChecked());
	        babelHelpers.classPrivateFieldGet(_this2, _mainTool).inputNode.form.dispatchEvent(new Event('change'));
	        main_core.Dom[babelHelpers.classPrivateFieldGet(_this2, _mainTool).isChecked() ? 'addClass' : 'removeClass'](babelHelpers.classPrivateFieldGet(_this2, _sectionWrapper), '--checked');
	        _this2.items.forEach(function (item) {
	          return item.getSwitcher().check(babelHelpers.classPrivateFieldGet(_this2, _mainTool).isChecked());
	        });
	      });
	      this.items.forEach(function (item) {
	        if (item.isDefault !== true)
	          // if only this item is not required
	          {
	            main_core_events.EventEmitter.subscribe(item.getSwitcher(), 'inactive', _classPrivateMethodGet(_this2, _turnOffUnrequiredTools, _turnOffUnrequiredTools2).bind(_this2));
	          }
	        main_core_events.EventEmitter.subscribe(item.getSwitcher(), 'active', _classPrivateMethodGet(_this2, _turnOnMainAndRequiredTools, _turnOnMainAndRequiredTools2).bind(_this2));
	      });
	      return babelHelpers.classPrivateFieldGet(this, _sectionWrapper);
	    }
	  }, {
	    key: "getMenuIcon",
	    value: function getMenuIcon() {
	      if (this.isNestedMenu) {
	        return main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span class=\"ui-section__collapse-icon ui-icon-set ", " --tool-selector-icon\"></span>\n\t\t\t"])), this.isOpen ? this.className.arrowTop : this.className.arrowDown);
	      }
	      return null;
	    }
	  }, {
	    key: "getFields",
	    value: function getFields() {
	      var result = [];
	      result.push(this.field);
	      this.items.forEach(function (item) {
	        result.push(item.getField());
	      });
	      return result;
	    }
	  }, {
	    key: "getLink",
	    value: function getLink() {
	      if (main_core.Type.isNil(this.linkTitle)) {
	        return null;
	      }
	      if (!main_core.Type.isNil(this.link)) {
	        return main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<a target=\"_blank\" href=\"", "\" class=\"ui-section__header-link ui-section__tools-description-link\" onclick=\"event.stopPropagation()\">", "</a>\n\t\t\t"])), this.link, this.linkTitle);
	      }
	      if (!main_core.Type.isNil(this.infoHelperCode)) {
	        return main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<a href=\"javascript:top.BX.UI.InfoHelper.show('", "')\" class=\"ui-section__header-link ui-section__tools-description-link\" onclick=\"event.stopPropagation()\">", "</a>\n\t\t\t"])), this.infoHelperCode, this.linkTitle);
	      }
	      return null;
	    }
	  }, {
	    key: "createSwitcher",
	    value: function createSwitcher(node) {
	      return new ui_switcher.Switcher({
	        inputName: this.mainInputName,
	        node: node,
	        checked: this.isChecked,
	        id: this.id
	      });
	    }
	  }]);
	  return NestedSwitcher;
	}(ui_section.Section);
	function _turnOnMainAndRequiredTools2(item) {
	  babelHelpers.classPrivateFieldGet(this, _mainTool).inputNode.form.dispatchEvent(new Event('change'));
	  if (babelHelpers.classPrivateFieldGet(this, _mainTool).isChecked()) {
	    return;
	  }
	  babelHelpers.classPrivateFieldGet(this, _mainTool).check(true, false);
	  this.toggle(true);
	  main_core.Dom.addClass(babelHelpers.classPrivateFieldGet(this, _sectionWrapper), '--checked');
	  this.items.forEach(function (item) {
	    return item.isDefault && !item.getSwitcher().isChecked() ? item.getSwitcher().check(true) : null;
	  });
	}
	function _turnOffUnrequiredTools2(_ref) {
	  var target = _ref.target;
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
	}

	exports.NestedSwitcherItem = NestedSwitcherItem;
	exports.NestedSwitcher = NestedSwitcher;

}((this.BX.UI = this.BX.UI || {}),BX,BX.Event,BX.UI.FormElements,BX.UI,BX.UI));
//# sourceMappingURL=nested-switcher.bundle.js.map
