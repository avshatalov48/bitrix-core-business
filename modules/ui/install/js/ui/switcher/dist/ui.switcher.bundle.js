/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	var _templateObject, _templateObject2;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	function _classStaticPrivateFieldSpecGet(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor(descriptor, "get"); return _classApplyDescriptorGet(receiver, descriptor); }
	function _classCheckPrivateStaticFieldDescriptor(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }
	function _classCheckPrivateStaticAccess(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	function _classApplyDescriptorGet(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }
	var SwitcherSize = Object.freeze({
	  medium: 'medium',
	  small: 'small'
	});
	var SwitcherColor = Object.freeze({
	  primary: 'primary',
	  green: 'green'
	});
	var _classNameSize = /*#__PURE__*/new WeakMap();
	var _classNameColor = /*#__PURE__*/new WeakMap();
	var _disabled = /*#__PURE__*/new WeakMap();
	var _inputName = /*#__PURE__*/new WeakMap();
	var _loading = /*#__PURE__*/new WeakMap();
	var _classNameOff = /*#__PURE__*/new WeakMap();
	var _classNameLock = /*#__PURE__*/new WeakMap();
	var _attributeName = /*#__PURE__*/new WeakMap();
	var _initNode = /*#__PURE__*/new WeakSet();
	var _fireEvent = /*#__PURE__*/new WeakSet();
	var Switcher = /*#__PURE__*/function () {
	  /**
	   * Switcher.
	   *
	   * @param {object} [options] - Options.
	   * @param {string} [options.attributeName] - Name of switcher attribute.
	   * @param {Element} [options.node] - Node.
	   * @param {string} [options.id] - ID.
	   * @param {123} [options.checked] - Checked.
	   * @param {string} [options.inputName] - Input name.
	   * @constructor
	   */
	  function Switcher(options) {
	    var _value, _value2;
	    babelHelpers.classCallCheck(this, Switcher);
	    _classPrivateMethodInitSpec(this, _fireEvent);
	    _classPrivateMethodInitSpec(this, _initNode);
	    _classPrivateFieldInitSpec(this, _classNameSize, {
	      writable: true,
	      value: (_value = {}, babelHelpers.defineProperty(_value, SwitcherSize.small, 'ui-switcher-size-sm'), babelHelpers.defineProperty(_value, SwitcherSize.medium, ''), _value)
	    });
	    _classPrivateFieldInitSpec(this, _classNameColor, {
	      writable: true,
	      value: (_value2 = {}, babelHelpers.defineProperty(_value2, SwitcherColor.primary, ''), babelHelpers.defineProperty(_value2, SwitcherColor.green, 'ui-switcher-color-green'), _value2)
	    });
	    babelHelpers.defineProperty(this, "node", null);
	    babelHelpers.defineProperty(this, "checked", false);
	    babelHelpers.defineProperty(this, "id", '');
	    _classPrivateFieldInitSpec(this, _disabled, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec(this, _inputName, {
	      writable: true,
	      value: ''
	    });
	    _classPrivateFieldInitSpec(this, _loading, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _classNameOff, {
	      writable: true,
	      value: 'ui-switcher-off'
	    });
	    _classPrivateFieldInitSpec(this, _classNameLock, {
	      writable: true,
	      value: 'ui-switcher-lock'
	    });
	    _classPrivateFieldInitSpec(this, _attributeName, {
	      writable: true,
	      value: 'data-switcher'
	    });
	    this.init(options);
	    Switcher.list.push(this);
	  }
	  babelHelpers.createClass(Switcher, [{
	    key: "init",
	    value: function init() {
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      babelHelpers.classPrivateFieldSet(this, _attributeName, main_core.Type.isString(options.attributeName) ? options.attributeName : babelHelpers.classPrivateFieldGet(this, _attributeName));
	      this.handlers = main_core.Type.isPlainObject(options.handlers) ? options.handlers : {};
	      babelHelpers.classPrivateFieldSet(this, _inputName, main_core.Type.isString(options.inputName) ? options.inputName : '');
	      babelHelpers.classPrivateFieldSet(this, _loading, false);
	      this.events = {
	        toggled: 'toggled',
	        checked: 'checked',
	        unchecked: 'unchecked'
	      };
	      if (options.node) {
	        if (!main_core.Type.isDomNode(options.node)) {
	          throw new Error('Parameter `node` DOM Node expected.');
	        }
	        this.node = options.node;
	        var data = this.node.getAttribute(babelHelpers.classPrivateFieldGet(this, _attributeName));
	        try {
	          data = JSON.parse(data) || {};
	        } catch (e) {
	          data = {};
	        }
	        if (data.id) {
	          this.id = data.id;
	        }
	        this.checked = Boolean(data.checked);
	        babelHelpers.classPrivateFieldSet(this, _inputName, data.inputName);
	        if (main_core.Type.isString(data.color) && Object.values(SwitcherColor).includes(data.color)) {
	          options.color = data.color;
	        }
	        if (main_core.Type.isString(data.size) && Object.values(SwitcherSize).includes(data.size)) {
	          options.size = data.size;
	        }
	      } else {
	        this.node = document.createElement('span');
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _classNameSize)[options.size]) {
	        main_core.Dom.addClass(this.node, babelHelpers.classPrivateFieldGet(this, _classNameSize)[options.size]);
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _classNameColor)[options.color]) {
	        main_core.Dom.addClass(this.node, babelHelpers.classPrivateFieldGet(this, _classNameColor)[options.color]);
	      }
	      if (main_core.Type.isString(options.id) || main_core.Type.isNumber(options.id)) {
	        this.id = options.id;
	      } else if (!this.id) {
	        this.id = Math.random();
	      }
	      if (main_core.Type.isString(options.inputName)) {
	        babelHelpers.classPrivateFieldSet(this, _inputName, options.inputName);
	      }
	      this.checked = main_core.Type.isBoolean(options.checked) ? options.checked : this.checked;
	      babelHelpers.classPrivateFieldSet(this, _disabled, main_core.Type.isBoolean(options.disabled) ? options.disabled : babelHelpers.classPrivateFieldGet(this, _disabled));
	      _classPrivateMethodGet(this, _initNode, _initNode2).call(this);
	      this.check(this.checked, false);
	      this.disable(babelHelpers.classPrivateFieldGet(this, _disabled), false);
	    }
	  }, {
	    key: "disable",
	    value: function disable(disabled, fireEvents) {
	      if (this.isLoading()) {
	        return;
	      }
	      fireEvents = fireEvents !== false;
	      if (babelHelpers.classPrivateFieldGet(this, _disabled)) {
	        main_core.Dom.addClass(this.node, babelHelpers.classPrivateFieldGet(this, _classNameLock));
	        fireEvents ? _classPrivateMethodGet(this, _fireEvent, _fireEvent2).call(this, this.events.lock) : null;
	      } else {
	        main_core.Dom.removeClass(this.node, babelHelpers.classPrivateFieldGet(this, _classNameLock));
	        fireEvents ? _classPrivateMethodGet(this, _fireEvent, _fireEvent2).call(this, this.events.unlock) : null;
	      }
	      if (fireEvents) {
	        _classPrivateMethodGet(this, _fireEvent, _fireEvent2).call(this, this.events.toggled);
	      }
	    }
	  }, {
	    key: "check",
	    value: function check(checked, fireEvents) {
	      if (this.isLoading()) {
	        return;
	      }
	      this.checked = !!checked;
	      if (this.inputNode) {
	        this.inputNode.value = this.checked ? 'Y' : 'N';
	      }
	      fireEvents = fireEvents !== false;
	      if (this.checked) {
	        main_core.Dom.removeClass(this.node, babelHelpers.classPrivateFieldGet(this, _classNameOff));
	        fireEvents ? _classPrivateMethodGet(this, _fireEvent, _fireEvent2).call(this, this.events.unchecked) : null;
	      } else {
	        main_core.Dom.addClass(this.node, babelHelpers.classPrivateFieldGet(this, _classNameOff));
	        fireEvents ? _classPrivateMethodGet(this, _fireEvent, _fireEvent2).call(this, this.events.checked) : null;
	      }
	      if (fireEvents) {
	        _classPrivateMethodGet(this, _fireEvent, _fireEvent2).call(this, this.events.toggled);
	      }
	    }
	  }, {
	    key: "isDisabled",
	    value: function isDisabled() {
	      return babelHelpers.classPrivateFieldGet(this, _disabled);
	    }
	  }, {
	    key: "isChecked",
	    value: function isChecked() {
	      return this.checked;
	    }
	  }, {
	    key: "toggle",
	    value: function toggle() {
	      if (this.isDisabled()) {
	        return;
	      }
	      this.check(!this.isChecked());
	    }
	  }, {
	    key: "setLoading",
	    value: function setLoading(mode) {
	      babelHelpers.classPrivateFieldSet(this, _loading, Boolean(mode));
	      var cursor = this.getNode().querySelector('.ui-switcher-cursor');
	      if (babelHelpers.classPrivateFieldGet(this, _loading)) {
	        var svg = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<svg viewBox=\"25 25 50 50\">\n\t\t\t\t\t<circle\n\t\t\t\t\t\tclass=\"ui-sidepanel-wrapper-loader-path\"\n\t\t\t\t\t\tcx=\"50\"\n\t\t\t\t\t\tcy=\"50\"\n\t\t\t\t\t\tr=\"19\"\n\t\t\t\t\t\tfill=\"none\"\n\t\t\t\t\t\tstroke-width=\"5\"\n\t\t\t\t\t\tstroke-miterlimit=\"10\"\n\t\t\t\t\t>\n\t\t\t\t\t</circle>\n\t\t\t\t</svg>\n\t\t\t"])));
	        main_core.Dom.append(svg, cursor);
	      } else {
	        cursor.innerHTML = '';
	      }
	    }
	  }, {
	    key: "isLoading",
	    value: function isLoading() {
	      return babelHelpers.classPrivateFieldGet(this, _loading);
	    }
	  }, {
	    key: "renderTo",
	    value: function renderTo(targetNode) {
	      if (!main_core.Type.isDomNode(targetNode)) {
	        throw new Error('Target node must be HTMLElement');
	      }
	      return main_core.Dom.append(this.getNode(), targetNode);
	    }
	  }, {
	    key: "getNode",
	    value: function getNode() {
	      return this.node;
	    }
	  }, {
	    key: "getAttributeName",
	    value: function getAttributeName() {
	      return babelHelpers.classPrivateFieldGet(this, _attributeName);
	    }
	  }, {
	    key: "getInputName",
	    value: function getInputName() {
	      return babelHelpers.classPrivateFieldGet(this, _inputName);
	    }
	  }], [{
	    key: "getById",
	    value: function getById(id) {
	      return Switcher.list.find(function (item) {
	        return item.id === id;
	      }) || null;
	    }
	  }, {
	    key: "initByClassName",
	    value: function initByClassName() {
	      var nodes = document.getElementsByClassName(Switcher.className);
	      Array.from(nodes).forEach(function (node) {
	        if (node.getAttribute(_classStaticPrivateFieldSpecGet(Switcher, Switcher, _attributeInitName))) {
	          return;
	        }
	        new Switcher({
	          node: node
	        });
	      });
	    }
	  }, {
	    key: "getList",
	    value: function getList() {
	      return Switcher.list;
	    }
	  }]);
	  return Switcher;
	}();
	function _initNode2() {
	  if (this.node.getAttribute(_classStaticPrivateFieldSpecGet(Switcher, Switcher, _attributeInitName))) {
	    return;
	  }
	  this.node.setAttribute(_classStaticPrivateFieldSpecGet(Switcher, Switcher, _attributeInitName), 'y');
	  main_core.Dom.addClass(this.node, Switcher.className);
	  this.node.innerHTML = '<span class="ui-switcher-cursor"></span>\n' + '<span class="ui-switcher-enabled">' + main_core.Loc.getMessage('UI_SWITCHER_ON') + '</span>\n' + '<span class="ui-switcher-disabled">' + main_core.Loc.getMessage('UI_SWITCHER_OFF') + '</span>\n';
	  if (babelHelpers.classPrivateFieldGet(this, _inputName)) {
	    this.inputNode = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input type=\"hidden\" name=\"", "\" />\n\t\t\t"])), babelHelpers.classPrivateFieldGet(this, _inputName));
	    main_core.Dom.append(this.inputNode, this.node);
	  }
	  main_core.bind(this.node, 'click', this.toggle.bind(this));
	}
	function _fireEvent2(eventName) {
	  main_core.onCustomEvent(this, eventName);
	  if (this.handlers[eventName]) {
	    this.handlers[eventName].call(this);
	  }
	}
	var _attributeInitName = {
	  writable: true,
	  value: 'data-switcher-init'
	};
	babelHelpers.defineProperty(Switcher, "list", []);
	babelHelpers.defineProperty(Switcher, "className", 'ui-switcher');

	exports.SwitcherSize = SwitcherSize;
	exports.SwitcherColor = SwitcherColor;
	exports.Switcher = Switcher;

}((this.BX.UI = this.BX.UI || {}),BX));
//# sourceMappingURL=ui.switcher.bundle.js.map
