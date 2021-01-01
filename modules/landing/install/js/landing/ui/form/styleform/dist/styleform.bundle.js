this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,landing_ui_form_baseform,landing_ui_highlight,landing_ui_field_basefield,landing_ui_component_internal) {
	'use strict';

	/**
	 * @memberOf BX.Landing.UI.Form
	 */

	var StyleForm = /*#__PURE__*/function (_BaseForm) {
	  babelHelpers.inherits(StyleForm, _BaseForm);

	  function StyleForm() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, StyleForm);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(StyleForm).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Form.StyleForm');

	    _this.subscribeFromOptions(landing_ui_component_internal.fetchEventsFromOptions(options));

	    main_core.Dom.addClass(_this.layout, 'landing-ui-form-style');
	    _this.iframe = 'iframe' in options ? options.iframe : null;
	    _this.node = 'node' in options ? options.node : null;
	    _this.selector = 'selector' in options ? options.selector : null;
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
	        field.subscribe('onChange', this.onChange.bind(this));
	        this.fields.add(field);
	        this.body.appendChild(field.layout);
	      }
	    }
	  }, {
	    key: "onChange",
	    value: function onChange() {
	      this.emit('onChange');
	    }
	  }]);
	  return StyleForm;
	}(landing_ui_form_baseform.BaseForm);

	exports.StyleForm = StyleForm;

}((this.BX.Landing.UI.Form = this.BX.Landing.UI.Form || {}),BX,BX.Landing.UI.Form,BX.Landing.UI,BX.Landing.UI.Field,BX.Landing.UI.Component));
//# sourceMappingURL=styleform.bundle.js.map
