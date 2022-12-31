this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,landing_ui_form_baseform,landing_ui_highlight,landing_ui_field_basefield,landing_ui_component_internal) {
	'use strict';

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	/**
	 * @memberOf BX.Landing.UI.Form
	 */

	var _styleFields = /*#__PURE__*/new WeakMap();

	var _toggleLinkedFields = /*#__PURE__*/new WeakSet();

	var StyleForm = /*#__PURE__*/function (_BaseForm) {
	  babelHelpers.inherits(StyleForm, _BaseForm);

	  function StyleForm() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, StyleForm);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(StyleForm).call(this, options));

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _toggleLinkedFields);

	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _styleFields, {
	      writable: true,
	      value: void 0
	    });

	    _this.setEventNamespace('BX.Landing.UI.Form.StyleForm');

	    _this.subscribeFromOptions(landing_ui_component_internal.fetchEventsFromOptions(options));

	    main_core.Dom.addClass(_this.layout, 'landing-ui-form-style');
	    _this.iframe = 'iframe' in options ? options.iframe : null;
	    _this.node = 'node' in options ? options.node : null;
	    _this.selector = 'selector' in options ? options.selector : null;
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _styleFields, new Map());
	    _this.onHeaderEnter = _this.onHeaderEnter.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onHeaderLeave = _this.onHeaderLeave.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onHeaderClick = _this.onHeaderClick.bind(babelHelpers.assertThisInitialized(_this));
	    main_core.Event.bind(_this.header, 'click', _this.onHeaderClick);
	    main_core.Event.bind(_this.header, 'mouseenter', _this.onHeaderEnter);
	    main_core.Event.bind(_this.header, 'mouseleave', _this.onHeaderLeave);

	    if (_this.type === 'attrs') {
	      main_core.Dom.addClass(_this.header, 'landing-ui-static');
	    }

	    if (_this.iframe) {
	      _this.onFrameLoad();
	    }

	    return _this;
	  }

	  babelHelpers.createClass(StyleForm, [{
	    key: "onFrameLoad",
	    value: function onFrameLoad() {
	      if (!this.node) {
	        this.node = babelHelpers.toConsumableArray(this.iframe.document.querySelectorAll(this.selector));
	      }
	    }
	  }, {
	    key: "onHeaderEnter",
	    value: function onHeaderEnter() {
	      landing_ui_highlight.Highlight.getInstance().show(this.node);
	    } // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "onHeaderLeave",
	    value: function onHeaderLeave() {
	      landing_ui_highlight.Highlight.getInstance().hide();
	    } // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "onHeaderClick",
	    value: function onHeaderClick(event) {
	      event.preventDefault();
	    }
	  }, {
	    key: "addField",
	    value: function addField(field) {
	      if (field) {
	        var _field$data;

	        var attrKey = field === null || field === void 0 ? void 0 : (_field$data = field.data) === null || _field$data === void 0 ? void 0 : _field$data.attrKey;
	        field.subscribe('onChange', this.onChange.bind(this));
	        field.subscribe('onInit', this.onInit.bind(this));
	        this.fields.add(field);
	        this.body.appendChild(field.layout);

	        if (attrKey) {
	          babelHelpers.classPrivateFieldGet(this, _styleFields).set(attrKey, field.getLayout());
	        }
	      }
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      _classPrivateMethodGet(this, _toggleLinkedFields, _toggleLinkedFields2).call(this, event.getData());

	      this.emit('onChange');
	    }
	  }, {
	    key: "onInit",
	    value: function onInit(event) {
	      _classPrivateMethodGet(this, _toggleLinkedFields, _toggleLinkedFields2).call(this, event.getData());

	      this.emit('onInit');
	    }
	  }]);
	  return StyleForm;
	}(landing_ui_form_baseform.BaseForm);

	function _toggleLinkedFields2(fieldData) {
	  var _this2 = this;

	  // hide linked fields
	  if (fieldData.hide && main_core.Type.isArray(fieldData.hide)) {
	    fieldData.hide.map(function (attr) {
	      var layout = babelHelpers.classPrivateFieldGet(_this2, _styleFields).get(attr);

	      if (layout) {
	        layout.style.display = 'none';
	      }
	    });
	  } // show linked fields


	  if (fieldData.show && main_core.Type.isArray(fieldData.show)) {
	    fieldData.show.map(function (attr) {
	      var layout = babelHelpers.classPrivateFieldGet(_this2, _styleFields).get(attr);

	      if (layout) {
	        layout.style.display = 'block';
	      }
	    });
	  }
	}

	exports.StyleForm = StyleForm;

}((this.BX.Landing.UI.Form = this.BX.Landing.UI.Form || {}),BX,BX.Landing.UI.Form,BX.Landing.UI,BX.Landing.UI.Field,BX.Landing.UI.Component));
//# sourceMappingURL=styleform.bundle.js.map
