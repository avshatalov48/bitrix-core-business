this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,landing_ui_form_baseform,landing_ui_highlight,landing_ui_field_basefield,landing_env,landing_ui_component_internal) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	/**
	 * @memberOf BX.Landing.UI.Form
	 */
	var _styleFields = /*#__PURE__*/new WeakMap();
	var _toggleLinkedFields = /*#__PURE__*/new WeakSet();
	var _addReplaceByTemplateCard = /*#__PURE__*/new WeakSet();
	var StyleForm = /*#__PURE__*/function (_BaseForm) {
	  babelHelpers.inherits(StyleForm, _BaseForm);
	  function StyleForm() {
	    var _this;
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, StyleForm);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(StyleForm).call(this, options));
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _addReplaceByTemplateCard);
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
	    _this.collapsed = 'collapsed' in options ? options.collapsed : null;
	    _this.currentTarget = 'currentTarget' in options ? options.currentTarget : null;
	    _this.specialType = 'specialType' in options ? options.specialType : null;
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _styleFields, new Map());
	    _this.onHeaderEnter = _this.onHeaderEnter.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onHeaderLeave = _this.onHeaderLeave.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onHeaderClick = _this.onHeaderClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.prepareHeader();
	    main_core.Event.bind(_this.header, 'click', _this.onHeaderClick);
	    main_core.Event.bind(_this.header, 'mouseenter', _this.onHeaderEnter);
	    main_core.Event.bind(_this.header, 'mouseleave', _this.onHeaderLeave);
	    if (_this.iframe) {
	      _this.onFrameLoad();
	    }
	    if (_this.collapsed) {
	      main_core.Dom.addClass(_this.layout, 'landing-ui-form-style--collapsed');
	    }
	    if (_this.specialType && _this.specialType === 'crm_forms' && landing_env.Env.getInstance().getSpecialType() === 'crm_forms') {
	      _classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _addReplaceByTemplateCard, _addReplaceByTemplateCard2).call(babelHelpers.assertThisInitialized(_this));
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
	      main_core.Dom.toggleClass(this.layout, 'landing-ui-form-style--collapsed');
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
	        BX.Dom.append(field.layout, this.body);
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
	  }, {
	    key: "prepareHeader",
	    value: function prepareHeader() {
	      var headerText = BX.Dom.create({
	        tag: 'div',
	        props: {
	          classList: 'landing-ui-form-header-text'
	        }
	      });
	      if (this.header.childNodes) {
	        this.header.childNodes.forEach(function (childNode) {
	          BX.Dom.append(childNode, headerText);
	        });
	      }
	      BX.Dom.append(headerText, this.header);
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
	        BX.Dom.style(layout, 'display', 'none');
	      }
	      return null;
	    });
	  }

	  // show linked fields
	  if (fieldData.show && main_core.Type.isArray(fieldData.show)) {
	    fieldData.show.map(function (attr) {
	      var layout = babelHelpers.classPrivateFieldGet(_this2, _styleFields).get(attr);
	      if (layout) {
	        BX.Dom.style(layout, 'display', 'block');
	      }
	      return null;
	    });
	  }
	}
	function _addReplaceByTemplateCard2() {
	  var isMinisitesAllowed = landing_env.Env.getInstance().getOptions().allow_minisites;
	  var lockIcon = isMinisitesAllowed ? '' : main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<span class=\"landing-ui-form-lock-icon\"></span>"])));
	  var button = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span class=\"landing-ui-form-replace-by-templates-card-button ui-btn ui-btn-sm ui-btn-primary ui-btn-hover ui-btn-round\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</span>\n\t\t"])), main_core.Loc.getMessage('LANDING_REPLACE_BY_TEMPLATES_BUTTON'), lockIcon);
	  var card = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-form-replace-by-templates-card\">\n\t\t\t<div class=\"landing-ui-form-replace-by-templates-card-title\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t\t", "\n\t\t</div>"])), main_core.Loc.getMessage('LANDING_REPLACE_BY_TEMPLATES_TITLE'), button);
	  main_core.Dom.insertBefore(card, this.header);
	  main_core.Event.bind(button, 'click', function () {
	    if (!isMinisitesAllowed) {
	      BX.UI.InfoHelper.show('limit_crm_forms_templates');
	      return;
	    }

	    // todo: migrate to new analytics?
	    var metrika = new BX.Landing.Metrika(true);
	    metrika.sendLabel(null, 'templateMarket', 'open&replaceLid=' + landingParams['LANDING_ID']);
	    var templatesMarketUrl = landingParams['PAGE_URL_LANDING_REPLACE_FROM_STYLE'];
	    if (templatesMarketUrl) {
	      BX.SidePanel.Instance.open(templatesMarketUrl, {
	        allowChangeHistory: false,
	        cacheable: false,
	        customLeftBoundary: 0
	      });
	    }
	  });
	}

	exports.StyleForm = StyleForm;

}((this.BX.Landing.UI.Form = this.BX.Landing.UI.Form || {}),BX,BX.Landing.UI.Form,BX.Landing.UI,BX.Landing.UI.Field,BX.Landing,BX.Landing.UI.Component));
//# sourceMappingURL=styleform.bundle.js.map
