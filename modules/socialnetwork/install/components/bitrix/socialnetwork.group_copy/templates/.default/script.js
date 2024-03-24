this.BX = this.BX || {};
(function (exports,ui_buttons,main_core) {
	'use strict';

	var _templateObject;
	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var ErrorAlert = /*#__PURE__*/function () {
	  function ErrorAlert(options) {
	    babelHelpers.classCallCheck(this, ErrorAlert);
	    options = _objectSpread(_objectSpread({}, {
	      id: main_core.Text.getRandom(),
	      message: "Error!"
	    }), options);
	    this.id = options.id;
	    this.message = options.message;
	    this.classes = new Map([["container", "ui-alert ui-alert-danger"], ["message", "ui-alert-message"]]);
	  }
	  babelHelpers.createClass(ErrorAlert, [{
	    key: "render",
	    value: function render() {
	      this.id = main_core.Text.encode(this.id);
	      this.message = main_core.Text.encode(this.message);
	      return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div id=\"", "\" class=\"", "\">\n\t\t\t\t<span class=\"", "\">", "</span>\n\t\t\t</div>\n\t\t"])), this.id, this.classes.get("container"), this.classes.get("message"), this.message);
	    }
	  }]);
	  return ErrorAlert;
	}();

	function ownKeys$1(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$1(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$1(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$1(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var BaseField = /*#__PURE__*/function (_Event$EventEmitter) {
	  babelHelpers.inherits(BaseField, _Event$EventEmitter);
	  function BaseField(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, BaseField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BaseField).call(this, options));
	    options = _objectSpread$1(_objectSpread$1({}, {
	      fieldTitle: "title",
	      fieldName: "name",
	      validators: [],
	      onCustomChange: null,
	      parentNode: null,
	      value: "",
	      focus: false,
	      visible: true
	    }), options);
	    _this.fieldTitle = options.fieldTitle;
	    _this.fieldName = options.fieldName;
	    _this.validators = options.validators;
	    _this.onCustomChange = options.onCustomChange;
	    _this.parentNode = options.parentNode;
	    _this.titleControl = null;
	    _this.titleId = main_core.Text.encode(_this.fieldName) + "-" + "title";
	    _this.innerControl = null;
	    _this.innerControlId = _this.fieldName + "-" + main_core.Text.getRandom();
	    _this.value = options.value ? options.value : "";
	    _this.focus = options.focus;
	    _this.visible = options.visible;
	    _this.container = null;
	    _this.ids = new Map([["container", "social-group-copy-field-" + _this.fieldName]]);
	    _this.errorContainer = new Set();
	    _this.classes = new Map([["container", "social-group-copy-fields-item"], ["leftColumn", "social-group-copy-fields-item-column-left"], ["rightColumn", "social-group-copy-fields-item-column-right"], ["fieldTitle", "social-group-copy-fields-item-name"], ["errorMark", "ui-ctl-danger"], ["hide", "hide"]]);
	    if (main_core.Type.isDomNode(_this.parentNode)) {
	      _this.observerParent = new MutationObserver(_this.onAppendToParent.bind(babelHelpers.assertThisInitialized(_this)));
	      _this.observerParent.observe(_this.parentNode, {
	        childList: true
	      });
	    }
	    return _this;
	  }
	  babelHelpers.createClass(BaseField, [{
	    key: "setClass",
	    value: function setClass(id, name) {
	      this.classes.set(id, name);
	    }
	  }, {
	    key: "addClass",
	    value: function addClass(id, name) {
	      if (this.classes.has(id)) {
	        this.classes.set(id, this.classes.get(id) + " " + name);
	      }
	    }
	  }, {
	    key: "getType",
	    value: function getType() {
	      return this.constructor.name();
	    }
	  }, {
	    key: "getControl",
	    value: function getControl() {
	      return this.innerControl;
	    }
	  }, {
	    key: "onAppendToParent",
	    value: function onAppendToParent(mutations) {
	      this.titleControl = document.getElementById(this.titleId);
	      this.innerControl = document.getElementById(this.innerControlId);
	      this.container = document.getElementById(this.ids.get("container"));
	      if (main_core.Type.isFunction(this.onCustomChange)) {
	        main_core.Event.bind(this.innerControl, "change", this.onCustomChange);
	      }
	      if (this.focus) {
	        this.innerControl.focus();
	      }
	    }
	  }, {
	    key: "validate",
	    value: function validate() {
	      var _this2 = this;
	      this.errorContainer.clear();
	      if (!this.visible) {
	        return true;
	      }
	      var parentNode = this.parentNode ? this.parentNode : this.innerControl.parentNode;
	      this.validators.forEach(function (validatorClass) {
	        var validator = new validatorClass();
	        var errorId = "social-group-" + _this2.fieldName + "-" + validatorClass.getType();
	        var errorDom = document.getElementById(errorId);
	        if (main_core.Type.isDomNode(errorDom)) {
	          errorDom.remove();
	        }
	        if (validator.validate(_this2.value)) {
	          main_core.Dom.removeClass(parentNode, _this2.classes.get("errorMark"));
	        } else {
	          main_core.Dom.addClass(parentNode, _this2.classes.get("errorMark"));
	          var error = new ErrorAlert({
	            id: errorId,
	            message: validator.getErrorMessage()
	          });
	          parentNode.before(error.render());
	          _this2.errorContainer.add(error);
	        }
	      });
	      return this.errorContainer.size === 0;
	    }
	  }, {
	    key: "getErrorContainer",
	    value: function getErrorContainer() {
	      return this.errorContainer;
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      this.value = value;
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      if (!this.visible) {
	        return "";
	      }
	      return this.value;
	    }
	  }, {
	    key: "getName",
	    value: function getName() {
	      return this.fieldName;
	    }
	  }, {
	    key: "changeTitle",
	    value: function changeTitle(title) {
	      this.fieldTitle = title;
	      this.titleControl.innerHTML = main_core.Text.encode(this.fieldTitle);
	    }
	  }, {
	    key: "toggleVisible",
	    value: function toggleVisible(bool) {
	      this.visible = bool;
	      if (bool) {
	        main_core.Dom.removeClass(this.container, this.classes.get("hide"));
	      } else {
	        main_core.Dom.addClass(this.container, this.classes.get("hide"));
	      }
	    }
	  }]);
	  return BaseField;
	}(main_core.Event.EventEmitter);

	var _templateObject$1, _templateObject2;
	function ownKeys$2(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$2(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$2(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$2(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var TextField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(TextField, _BaseField);
	  function TextField(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, TextField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TextField).call(this, options));
	    options = _objectSpread$2(_objectSpread$2({}, {
	      placeHolder: ""
	    }), options);
	    _this.placeHolder = options.placeHolder;
	    _this.classes.set("control", "ui-ctl ui-ctl-textbox ui-ctl-w100");
	    _this.classes.set("innerControl", "ui-ctl-element");
	    return _this;
	  }

	  /**
	   * @returns {HTMLElement}
	   */
	  babelHelpers.createClass(TextField, [{
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"", "\">\n\t\t\t\t<div class=\"", "\">\n\t\t\t\t\t<div class=\"", "\">", "</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), this.classes.get("container"), this.classes.get("leftColumn"), this.classes.get("fieldTitle"), this.fieldTitle, this.classes.get("rightColumn"), this.renderRightColumn());
	    }
	    /**
	     * @returns {HTMLElement}
	     */
	  }, {
	    key: "renderRightColumn",
	    value: function renderRightColumn() {
	      var onChange = this.onChange.bind(this);
	      this.fieldTitle = main_core.Text.encode(this.fieldTitle);
	      this.fieldName = main_core.Text.encode(this.fieldName);
	      this.value = main_core.Text.encode(this.value);
	      this.placeHolder = main_core.Text.encode(this.placeHolder);
	      return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"", "\">\n\t\t\t\t<input id=\"", "\" type=\"text\" value=\"", "\" \n\t\t\t\t\tname=\"", "\" class=\"", "\" \n\t\t\t\t\tonchange=\"", "\" placeholder=\"", "\">\n\t\t\t</div>\n\t\t"])), this.classes.get("control"), this.innerControlId, this.value, this.fieldName, this.classes.get("innerControl"), onChange, this.placeHolder);
	    }
	  }, {
	    key: "changePlaceHolder",
	    value: function changePlaceHolder(text) {
	      this.innerControl.placeholder = main_core.Text.encode(text);
	    }
	  }, {
	    key: "onChange",
	    value: function onChange() {
	      this.setValue(this.innerControl.value);
	      this.validate();
	    }
	  }]);
	  return TextField;
	}(BaseField);

	var RequireValidator = /*#__PURE__*/function () {
	  function RequireValidator() {
	    babelHelpers.classCallCheck(this, RequireValidator);
	    this.errorMessage = main_core.Loc.getMessage("SGCG_REQUIRE_ERROR");
	  }
	  babelHelpers.createClass(RequireValidator, [{
	    key: "validate",
	    value: function validate(value) {
	      return this.constructor.isValid(value);
	    }
	  }, {
	    key: "getErrorMessage",
	    value: function getErrorMessage() {
	      return this.errorMessage;
	    }
	  }], [{
	    key: "getType",
	    value: function getType() {
	      return "require";
	    }
	  }, {
	    key: "isValid",
	    value: function isValid(value) {
	      if (main_core.Type.isArray(value)) {
	        return value.length > 0;
	      } else {
	        return value !== undefined && String(value).trim().length > 0;
	      }
	    }
	  }]);
	  return RequireValidator;
	}();

	var _templateObject$2, _templateObject2$1;
	function ownKeys$3(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$3(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$3(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$3(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var TextAreaField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(TextAreaField, _BaseField);
	  function TextAreaField(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, TextAreaField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TextAreaField).call(this, options));
	    options = _objectSpread$3(_objectSpread$3({}, {
	      placeHolder: ""
	    }), options);
	    _this.placeHolder = options.placeHolder;
	    _this.classes.set("control", "ui-ctl ui-ctl-textarea");
	    _this.classes.set("innerControl", "ui-ctl-element");
	    return _this;
	  }

	  /**
	   * @returns {HTMLElement}
	   */
	  babelHelpers.createClass(TextAreaField, [{
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"", "\">\n\t\t\t\t<div class=\"", "\">\n\t\t\t\t\t<div class=\"", "\">", "</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), this.classes.get("container"), this.classes.get("leftColumn"), this.classes.get("fieldTitle"), this.fieldTitle, this.classes.get("rightColumn"), this.renderRightColumn());
	    }
	    /**
	     * @returns {HTMLElement}
	     */
	  }, {
	    key: "renderRightColumn",
	    value: function renderRightColumn() {
	      var onChange = this.onChange.bind(this);
	      this.fieldTitle = main_core.Text.encode(this.fieldTitle);
	      this.fieldName = main_core.Text.encode(this.fieldName);
	      this.value = main_core.Text.encode(this.value);
	      return main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"", "\">\n\t\t\t\t<textarea id=\"", "\" type=\"text\" name=\"", "\" onchange=\"", "\" \n\t\t\t\tclass=\"", "\" placeholder=\"", "\">", "</textarea>\n\t\t\t</div>\n\t\t"])), this.classes.get("control"), this.innerControlId, this.fieldName, onChange, this.classes.get("innerControl"), this.placeHolder, this.value);
	    }
	  }, {
	    key: "onChange",
	    value: function onChange() {
	      this.setValue(this.innerControl.value);
	      this.validate();
	    }
	  }]);
	  return TextAreaField;
	}(BaseField);

	var _templateObject$3;
	function ownKeys$4(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$4(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$4(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$4(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var Form = /*#__PURE__*/function () {
	  function Form(options) {
	    babelHelpers.classCallCheck(this, Form);
	    options = _objectSpread$4(_objectSpread$4({}, {
	      requestSender: null,
	      groupData: {},
	      copyButtonId: "",
	      cancelButtonId: ""
	    }), options);
	    this.requestSender = options.requestSender;
	    this.groupData = options.groupData;
	    this.isProject = this.groupData.PROJECT === "Y";
	    this.copyButtonId = options.copyButtonId;
	    this.cancelButtonId = options.cancelButtonId;
	    this.fields = [];
	    this.blocks = [];
	    this.errorContainer = new Map();
	    this.errorDomContainer = null;
	    this.ids = new Map([["errorDomContainer", "social-group-copy-error"]]);
	    this.classes = new Map([["form", "social-group-copy-form"], ["base", "social-group-copy-base"], ["nameContainer", "social-group-copy-name"], ["name", "social-group-copy-name-title"], ["descriptionContainer", "social-group-copy-description"], ["descriptionArea", "social-group-copy-description-area"], ["description", "social-group-copy-description"], ["descriptionSeparator", "social-group-copy-separator-line"], ["errorDomContainer", "social-group-copy-error"], ["fields", "social-group-copy-fields"], ["blocks", "social-group-copy-blocks"]]);
	    this.checkboxEventName = "BX.Socialnetwork.CheckboxField";
	  }
	  babelHelpers.createClass(Form, [{
	    key: "renderTo",
	    value: function renderTo(formContainer) {
	      if (!main_core.Type.isDomNode(formContainer)) {
	        throw new Error("Form: HTMLElement for form not found");
	      }
	      formContainer.appendChild(this.render());
	      this.errorDomContainer = document.getElementById(this.ids.get("errorDomContainer"));
	      this.emitFormAppend(formContainer);
	      this.bindButtons();
	    }
	  }, {
	    key: "onCopy",
	    value: function onCopy() {
	      var _this = this;
	      if (this.validate()) {
	        var uiCopyButton = new ui_buttons.Button({
	          buttonContainer: this.copyButton
	        });
	        if (uiCopyButton.isWaiting()) {
	          return;
	        }
	        uiCopyButton.setWaiting(true);
	        this.requestSender.copyGroup(this.getRequestData()).then(function (response) {
	          _this.handleResponse(response);
	        })["catch"](function (response) {
	          uiCopyButton.setWaiting(false);
	          _this.handleResponse(response);
	        });
	      }
	    }
	  }, {
	    key: "onClose",
	    value: function onClose() {
	      this.constructor.closeSlider();
	    }
	  }, {
	    key: "bindButtons",
	    value: function bindButtons() {
	      this.copyButton = document.getElementById(this.copyButtonId);
	      this.cancelButton = document.getElementById(this.cancelButtonId);
	      if (!main_core.Type.isDomNode(this.copyButton) || !main_core.Type.isDomNode(this.cancelButton)) {
	        throw new Error("Form: buttons are not found");
	      }
	      main_core.Event.bind(this.copyButton, "click", this.onCopy.bind(this));
	      main_core.Event.bind(this.cancelButton, "click", this.onClose.bind(this));
	    }
	  }, {
	    key: "emitFormAppend",
	    value: function emitFormAppend(formContainer) {
	      this.fields.forEach(function (field) {
	        field.onAppendToParent(formContainer);
	      });
	      this.blocks.forEach(function (block) {
	        block.onAppendToParent(formContainer);
	      });
	    }
	  }, {
	    key: "addField",
	    value: function addField(field) {
	      this.fields.push(field);
	    }
	  }, {
	    key: "getFields",
	    value: function getFields() {
	      return this.fields;
	    }
	  }, {
	    key: "addBlock",
	    value: function addBlock(block) {
	      this.blocks.push(block);
	    }
	    /**
	     * @returns {HTMLElement}
	     */
	  }, {
	    key: "render",
	    value: function render() {
	      var nameField = new TextField({
	        fieldName: "name",
	        validators: [RequireValidator],
	        placeHolder: this.isProject ? main_core.Loc.getMessage("SGCG_PROJECT_NAME_FIELD") : main_core.Loc.getMessage("SGCG_GROUP_NAME_FIELD"),
	        focus: true
	      });
	      nameField.setClass("control", this.classes.get("name"));
	      main_core.Event.EventEmitter.subscribe(this.checkboxEventName + ":project:onChange", function (baseEvent) {
	        nameField.changePlaceHolder(baseEvent.data.checked ? main_core.Loc.getMessage("SGCG_PROJECT_NAME_FIELD") : main_core.Loc.getMessage("SGCG_GROUP_NAME_FIELD"));
	      });
	      var descriptionField = new TextAreaField({
	        fieldName: "description",
	        value: this.groupData.DESCRIPTION,
	        placeHolder: this.isProject ? main_core.Loc.getMessage("SGCG_PROJECT_DESCRIPTION_FIELD") : main_core.Loc.getMessage("SGCG_GROUP_DESCRIPTION_FIELD")
	      });
	      descriptionField.setClass("control", this.classes.get("descriptionArea"));
	      descriptionField.setClass("innerControl", this.classes.get("description"));
	      var fields = this.fields.map(function (field) {
	        return field.render();
	      });
	      var blocks = this.blocks.map(function (block) {
	        return block.render();
	      });
	      this.addField(nameField);
	      this.addField(descriptionField);
	      return main_core.Tag.render(_templateObject$3 || (_templateObject$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<form class=\"", "\" novalidate>\n\t\t\t\t<div id=\"", "\" class=\"", "\"></div>\n\t\t\t\t<div class=\"", "\">\n\t\t\t\t\t<div class=\"", "\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"", "\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t<div class=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</form>\n\t\t"])), this.classes.get("form"), this.ids.get("errorDomContainer"), this.classes.get("errorDomContainer"), this.classes.get("base"), this.classes.get("nameContainer"), nameField.renderRightColumn(), this.classes.get("descriptionContainer"), descriptionField.renderRightColumn(), this.classes.get("fields"), fields, this.classes.get("blocks"), blocks);
	    }
	  }, {
	    key: "validate",
	    value: function validate() {
	      var _this2 = this;
	      this.fields.forEach(function (field) {
	        if (field.validate()) {
	          _this2.errorContainer["delete"](field);
	        } else {
	          _this2.errorContainer.set(field, field.getErrorContainer());
	        }
	      });
	      return this.errorContainer.size === 0;
	    }
	  }, {
	    key: "getRequestData",
	    value: function getRequestData() {
	      var fieldsValues = {
	        id: this.groupData.ID
	      };
	      this.fields.forEach(function (field) {
	        fieldsValues[field.getName()] = field.getValue();
	      });
	      var blocksValues = {};
	      this.blocks.forEach(function (block) {
	        blocksValues = _objectSpread$4(_objectSpread$4({}, blocksValues), block.getValues());
	      });
	      var formData = new FormData();
	      for (var _i = 0, _Object$entries = Object.entries(Object.assign(fieldsValues, blocksValues)); _i < _Object$entries.length; _i++) {
	        var _Object$entries$_i = babelHelpers.slicedToArray(_Object$entries[_i], 2),
	          name = _Object$entries$_i[0],
	          value = _Object$entries$_i[1];
	        if (value instanceof Blob) {
	          formData.append(name, value, value.name);
	        } else {
	          formData.append(name, main_core.Type.isObjectLike(value) ? JSON.stringify(value) : value);
	        }
	      }
	      return formData;
	    }
	  }, {
	    key: "handleResponse",
	    value: function handleResponse(response) {
	      if (response.errors.length) {
	        this.displayResponseError(new ErrorAlert({
	          message: response.errors.shift().message
	        }));
	      } else {
	        var urlToCopiedGroup = response.data;
	        if (urlToCopiedGroup.length) {
	          top.window.location.href = urlToCopiedGroup;
	        } else {
	          this.displayResponseError(new ErrorAlert({
	            message: "Unknown error"
	          }));
	        }
	      }
	    }
	  }, {
	    key: "displayResponseError",
	    value: function displayResponseError(errorAlert) {
	      while (this.errorDomContainer.hasChildNodes()) {
	        this.errorDomContainer.removeChild(this.errorDomContainer.firstChild);
	      }
	      this.errorDomContainer.appendChild(errorAlert.render());
	    }
	  }], [{
	    key: "closeSlider",
	    value: function closeSlider() {
	      // eslint-ignore-next-line
	      window.top.BX.onCustomEvent("BX.Bitrix24.PageSlider:close", [false]);
	    }
	  }]);
	  return Form;
	}();

	function ownKeys$5(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$5(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$5(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$5(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var RequestSender = /*#__PURE__*/function () {
	  function RequestSender(options) {
	    babelHelpers.classCallCheck(this, RequestSender);
	    options = _objectSpread$5(_objectSpread$5({}, {
	      signedParameters: ""
	    }), options);
	    this.signedParameters = options.signedParameters;
	    this.isProject = false;
	  }
	  babelHelpers.createClass(RequestSender, [{
	    key: "selectGroup",
	    value: function selectGroup(groupId) {
	      var _this = this;
	      return new Promise(function (resolve, reject) {
	        main_core.ajax.runComponentAction("bitrix:socialnetwork.group_copy", "getGroupData", {
	          mode: "class",
	          signedParameters: _this.signedParameters,
	          data: {
	            groupId: parseInt(groupId)
	          }
	        }).then(resolve, reject);
	      });
	    }
	  }, {
	    key: "copyGroup",
	    value: function copyGroup(requestData) {
	      var _this2 = this;
	      return new Promise(function (resolve, reject) {
	        main_core.ajax.runComponentAction("bitrix:socialnetwork.group_copy", "copyGroup", {
	          mode: "class",
	          signedParameters: _this2.signedParameters,
	          data: requestData,
	          analyticsLabel: {
	            project: _this2.isProject ? "Y" : "N"
	          }
	        }).then(resolve, reject);
	      });
	    }
	  }, {
	    key: "setProjectMarker",
	    value: function setProjectMarker(bool) {
	      this.isProject = Boolean(bool);
	    }
	  }]);
	  return RequestSender;
	}();

	var _templateObject$4, _templateObject2$2;
	function ownKeys$6(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$6(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$6(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$6(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var CustomField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(CustomField, _BaseField);
	  function CustomField(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, CustomField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CustomField).call(this, options));
	    options = _objectSpread$6(_objectSpread$6({}, {
	      fieldContainerId: ""
	    }), options);
	    _this.fieldContainerId = options.fieldContainerId;
	    _this.classes.set("control", "ui-ctl ui-ctl-textbox ui-ctl-wa");
	    _this.classes.set("innerControl", "social-group-copy-ui-ctl-element");
	    _this.classes.set("customContainer", "social-group-copy-custom-container");
	    return _this;
	  }

	  /**
	   * @returns {HTMLElement}
	   */
	  babelHelpers.createClass(CustomField, [{
	    key: "render",
	    value: function render() {
	      this.fieldTitle = main_core.Text.encode(this.fieldTitle);
	      return main_core.Tag.render(_templateObject$4 || (_templateObject$4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div id=\"", "\" class=\"", "\">\n\t\t\t\t<div class=\"", "\">\n\t\t\t\t\t<div id=\"", "\" class=\"", "\">", "</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), main_core.Text.encode(this.ids.get("container")), this.classes.get("container"), this.classes.get("leftColumn"), this.titleId, this.classes.get("fieldTitle"), this.fieldTitle, this.classes.get("rightColumn"), this.renderRightColumn());
	    }
	    /**
	     * @returns {HTMLElement}
	     */
	  }, {
	    key: "renderRightColumn",
	    value: function renderRightColumn() {
	      var customContainer = document.getElementById(this.fieldContainerId);
	      main_core.Dom.removeClass(customContainer, this.classes.get("customContainer"));
	      return main_core.Tag.render(_templateObject2$2 || (_templateObject2$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"", "\">\n\t\t\t\t<div id=\"", "\" class=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), this.classes.get("control"), this.innerControlId, this.classes.get("innerControl"), customContainer);
	    }
	  }]);
	  return CustomField;
	}(BaseField);

	var ImageField = /*#__PURE__*/function (_CustomField) {
	  babelHelpers.inherits(ImageField, _CustomField);
	  function ImageField(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, ImageField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ImageField).call(this, options));
	    _this.classes.set("innerControl", "social-group-copy-link-upload social-group-copy-link-upload-set");
	    _this.classes.set("uploadSet", "social-group-copy-link-upload-set");
	    _this.init();
	    return _this;
	  }
	  babelHelpers.createClass(ImageField, [{
	    key: "init",
	    value: function init() {
	      // eslint-ignore-next-line
	      this.uploaderInstance = BX.UploaderManager.getById(this.fieldName);
	      if (this.uploaderInstance) {
	        // eslint-ignore-next-line
	        BX.addCustomEvent(this.uploaderInstance, "onQueueIsChanged", this.onQueueIsChanged.bind(this));
	      }
	    }
	  }, {
	    key: "onAppendToParent",
	    value: function onAppendToParent() {
	      babelHelpers.get(babelHelpers.getPrototypeOf(ImageField.prototype), "onAppendToParent", this).call(this);
	      var currentValue = this.getCurrentValue();
	      babelHelpers.get(babelHelpers.getPrototypeOf(ImageField.prototype), "setValue", this).call(this, currentValue);
	      if (!currentValue) {
	        main_core.Dom.removeClass(this.innerControl, this.classes.get("uploadSet"));
	      }
	    }
	  }, {
	    key: "onQueueIsChanged",
	    value: function onQueueIsChanged(uploaderInstance, action, fileId, file) {
	      // eslint-ignore-next-line
	      BX.addCustomEvent(file, "onUploadDone", this.onUploadDone.bind(this));
	      switch (action) {
	        case "add":
	          main_core.Dom.addClass(this.innerControl, this.classes.get("uploadSet"));
	          break;
	        case "delete":
	          main_core.Dom.removeClass(this.innerControl, this.classes.get("uploadSet"));
	          babelHelpers.get(babelHelpers.getPrototypeOf(ImageField.prototype), "setValue", this).call(this, "");
	          break;
	      }
	    }
	  }, {
	    key: "onUploadDone",
	    value: function onUploadDone(status, file, agent, pIndex) {
	      babelHelpers.get(babelHelpers.getPrototypeOf(ImageField.prototype), "setValue", this).call(this, this.getCurrentValue());
	    }
	  }, {
	    key: "getCurrentValue",
	    value: function getCurrentValue() {
	      var item = Object.values(this.uploaderInstance.getItems().items)[0];
	      if (!item) {
	        return '';
	      }
	      if (item.file instanceof Blob) {
	        return item.file;
	      }
	      return item.file.file_id;
	    }
	  }]);
	  return ImageField;
	}(CustomField);

	var DateValidator = /*#__PURE__*/function () {
	  function DateValidator() {
	    babelHelpers.classCallCheck(this, DateValidator);
	    this.errorMessage = main_core.Loc.getMessage("SGCG_FORMAT_ERROR");
	  }
	  babelHelpers.createClass(DateValidator, [{
	    key: "validate",
	    value: function validate(value) {
	      return this.constructor.isValid(value);
	    }
	  }, {
	    key: "getErrorMessage",
	    value: function getErrorMessage() {
	      return this.errorMessage;
	    }
	  }], [{
	    key: "getType",
	    value: function getType() {
	      return "date";
	    }
	  }, {
	    key: "isValid",
	    value: function isValid(value) {
	      //todo
	      return true;
	    }
	  }]);
	  return DateValidator;
	}();

	var _templateObject$5, _templateObject2$3;
	var DateField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(DateField, _BaseField);
	  function DateField(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, DateField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DateField).call(this, options));
	    _this.ids.set("container", "social-group-copy-date-field-" + _this.fieldName);
	    _this.classes.set("control", "ui-ctl ui-ctl-after-icon ui-ctl-datetime");
	    _this.classes.set("icon", "ui-ctl-after ui-ctl-icon-calendar");
	    _this.classes.set("innerControl", "ui-ctl-element");
	    _this.validators.push(DateValidator);
	    return _this;
	  }
	  babelHelpers.createClass(DateField, [{
	    key: "onAppendToParent",
	    value: function onAppendToParent() {
	      babelHelpers.get(babelHelpers.getPrototypeOf(DateField.prototype), "onAppendToParent", this).call(this);
	      main_core.Event.bind(this.innerControl, "click", this.showCalendar.bind(this));
	    }
	  }, {
	    key: "setParentNode",
	    value: function setParentNode(node) {
	      if (main_core.Type.isDomNode(node)) {
	        this.parentNode = node;
	      }
	    }
	    /**
	     * @returns {HTMLElement}
	     */
	  }, {
	    key: "render",
	    value: function render() {
	      this.fieldTitle = main_core.Text.encode(this.fieldTitle);
	      return main_core.Tag.render(_templateObject$5 || (_templateObject$5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div id=\"", "\" class=\"", "\">\n\t\t\t\t<div class=\"", "\">\n\t\t\t\t\t<div id=\"", "\" class=\"", "\">", "</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), main_core.Text.encode(this.ids.get("container")), this.classes.get("container"), this.classes.get("leftColumn"), this.titleId, this.classes.get("fieldTitle"), this.fieldTitle, this.classes.get("rightColumn"), this.renderRightColumn());
	    }
	    /**
	     * @returns {HTMLElement}
	     */
	  }, {
	    key: "renderRightColumn",
	    value: function renderRightColumn() {
	      this.fieldName = main_core.Text.encode(this.fieldName);
	      this.value = main_core.Text.encode(this.value);
	      var onChange = this.onChange.bind(this);
	      return main_core.Tag.render(_templateObject2$3 || (_templateObject2$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"", "\">\n\t\t\t\t<div class=\"", "\"></div>\n\t\t\t\t<input id=\"", "\" type=\"text\" autocomplete=\"off\" value=\"", "\" name=\"\n\t\t\t\t\t", "\" class=\"", "\" onchange=\"", "\">\n\t\t\t</div>\n\t\t"])), this.classes.get("control"), this.classes.get("icon"), this.innerControlId, this.value, this.fieldName, this.classes.get("innerControl"), onChange);
	    }
	  }, {
	    key: "showCalendar",
	    value: function showCalendar() {
	      /* eslint-disable */
	      BX.calendar({
	        node: this.innerControl,
	        field: this.innerControl,
	        bTime: false,
	        bSetFocus: false,
	        bHideTime: false
	      });
	      /* eslint-enable */
	    }
	  }, {
	    key: "onChange",
	    value: function onChange() {
	      this.setValue(this.innerControl.value);
	      this.validate();
	    }
	  }]);
	  return DateField;
	}(BaseField);

	var _templateObject$6;
	var DateRangeField = /*#__PURE__*/function (_CustomField) {
	  babelHelpers.inherits(DateRangeField, _CustomField);
	  function DateRangeField(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, DateRangeField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DateRangeField).call(this, options));
	    _this.startPoint = null;
	    _this.endPoint = null;
	    _this.ids.set("container", "social-group-copy-date-range-field-" + _this.fieldName);
	    _this.ids.set("baseContainer", "social-group-copy-date-range-base-" + _this.fieldName);
	    _this.classes.set("baseContainer", "social-group-copy-field-container " + "social-group-copy-field-container-datetime social-group-copy-field-datetime");
	    _this.classes.set("delimiter", "social-group-copy-field-block social-group-copy-field-block-between");
	    return _this;
	  }
	  babelHelpers.createClass(DateRangeField, [{
	    key: "onAppendToParent",
	    value: function onAppendToParent() {
	      babelHelpers.get(babelHelpers.getPrototypeOf(DateRangeField.prototype), "onAppendToParent", this).call(this);
	      this.baseContainer = document.getElementById(this.ids.get("baseContainer"));
	      this.startPoint.setParentNode(this.baseContainer);
	      this.startPoint.onAppendToParent();
	      this.endPoint.onAppendToParent();
	      this.toggleVisible(this.visible);
	    }
	    /**
	     * @returns {HTMLElement}
	     */
	  }, {
	    key: "renderRightColumn",
	    value: function renderRightColumn() {
	      this.startPoint = new DateField({
	        fieldName: "range_start_point",
	        validators: [RequireValidator]
	      });
	      this.endPoint = new DateField({
	        fieldName: "range_end_point"
	      });
	      return main_core.Tag.render(_templateObject$6 || (_templateObject$6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div>\n\t\t\t\t<div id=\"", "\" class=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"", "\"></div>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), this.ids.get("baseContainer"), this.classes.get("baseContainer"), this.startPoint.renderRightColumn(), this.classes.get("delimiter"), this.endPoint.renderRightColumn());
	    }
	  }, {
	    key: "validate",
	    value: function validate() {
	      this.errorContainer.clear();
	      if (!this.visible) {
	        return true;
	      }
	      return this.startPoint && this.startPoint.validate();
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return {
	        start_point: this.startPoint.getValue(),
	        end_point: this.endPoint.getValue()
	      };
	    }
	  }]);
	  return DateRangeField;
	}(CustomField);

	function ownKeys$7(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$7(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$7(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$7(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var UserField = /*#__PURE__*/function (_CustomField) {
	  babelHelpers.inherits(UserField, _CustomField);
	  function UserField(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, UserField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(UserField).call(this, options));
	    options = _objectSpread$7(_objectSpread$7({}, {
	      selectorId: "",
	      multiple: true
	    }), options);
	    _this.selectorId = options.selectorId;
	    _this.multiple = options.multiple;
	    _this.value = _this.multiple ? {} : "";
	    _this.init();
	    return _this;
	  }
	  babelHelpers.createClass(UserField, [{
	    key: "init",
	    value: function init() {
	      // eslint-ignore-next-line
	      BX.addCustomEvent("BX.Main.User.SelectorController:select", this.onSelect.bind(this));
	      // eslint-ignore-next-line
	      BX.addCustomEvent("BX.Main.User.SelectorController:unSelect", this.onUnSelect.bind(this));
	    }
	  }, {
	    key: "onSelect",
	    value: function onSelect(info) {
	      if (this.selectorId !== info.selectorId) {
	        return;
	      }
	      if (main_core.Type.isUndefined(info.item) || main_core.Type.isUndefined(info.item.entityId)) {
	        return;
	      }
	      this.setValue(info.item.entityId);
	    }
	  }, {
	    key: "onUnSelect",
	    value: function onUnSelect(info) {
	      if (this.selectorId !== info.selectorId) {
	        return;
	      }
	      if (main_core.Type.isUndefined(info.item) || main_core.Type.isUndefined(info.item.entityId)) {
	        return;
	      }
	      this.deleteValue(info.item.entityId);
	      this.validate();
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      if (this.multiple) {
	        this.value[value] = value;
	      } else {
	        this.value = value;
	      }
	    }
	  }, {
	    key: "deleteValue",
	    value: function deleteValue(value) {
	      if (this.multiple) {
	        delete this.value[value];
	      } else {
	        this.value = "";
	      }
	    }
	  }]);
	  return UserField;
	}(CustomField);

	var _templateObject$7, _templateObject2$4;
	function ownKeys$8(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$8(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$8(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$8(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var SelectField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(SelectField, _BaseField);
	  function SelectField(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, SelectField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SelectField).call(this, options));
	    options = _objectSpread$8(_objectSpread$8({}, {
	      list: {}
	    }), options);
	    _this.list = options.list;
	    _this.onChangeCallback = _this.onChange.bind(babelHelpers.assertThisInitialized(_this));
	    _this.classes.set("control", "ui-ctl ui-ctl-after-icon ui-ctl-dropdown");
	    _this.classes.set("controlAngle", "ui-ctl-after ui-ctl-icon-angle");
	    _this.classes.set("innerControl", "ui-ctl-element");
	    return _this;
	  }

	  /**
	   * @returns {HTMLElement}
	   */
	  babelHelpers.createClass(SelectField, [{
	    key: "render",
	    value: function render() {
	      this.fieldTitle = main_core.Text.encode(this.fieldTitle);
	      this.fieldName = main_core.Text.encode(this.fieldName);
	      this.value = main_core.Text.encode(this.value);
	      return main_core.Tag.render(_templateObject$7 || (_templateObject$7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"", "\">\n\t\t\t\t<div class=\"", "\">\n\t\t\t\t\t<div id=\"", "\" class=\"", "\">", "</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), this.classes.get("container"), this.classes.get("leftColumn"), this.titleId, this.classes.get("fieldTitle"), this.fieldTitle, this.classes.get("rightColumn"), this.renderRightColumn());
	    }
	    /**
	     * @returns {HTMLElement}
	     */
	  }, {
	    key: "renderRightColumn",
	    value: function renderRightColumn() {
	      var options = this.getOptions();
	      return main_core.Tag.render(_templateObject2$4 || (_templateObject2$4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"", "\">\n\t\t\t\t<div class=\"", "\"></div>\n\t\t\t\t<select id=\"", "\" name=\"", "\" class=\"", "\" onchange=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t</select>\n\t\t\t</div>\n\t\t"])), this.classes.get("control"), this.classes.get("controlAngle"), this.innerControlId, this.fieldName, this.classes.get("innerControl"), this.onChangeCallback, options.join(""));
	    }
	  }, {
	    key: "getOptions",
	    value: function getOptions() {
	      var _this2 = this;
	      return Object.entries(this.list).map(function (_ref) {
	        var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	          id = _ref2[0],
	          value = _ref2[1];
	        return "<option ".concat(id === _this2.value ? "selected" : "", " value=\"").concat(main_core.Text.encode(id), "\">").concat(main_core.Text.encode(value), "</option>");
	      });
	    }
	  }, {
	    key: "changeOptions",
	    value: function changeOptions(data) {
	      this.list = data;
	      var options = this.getOptions();
	      this.innerControl.innerHTML = options.join("");
	    }
	  }, {
	    key: "onChange",
	    value: function onChange() {
	      this.validate();
	    }
	  }, {
	    key: "validate",
	    value: function validate() {
	      babelHelpers.get(babelHelpers.getPrototypeOf(SelectField.prototype), "setValue", this).call(this, this.innerControl.value);
	      return babelHelpers.get(babelHelpers.getPrototypeOf(SelectField.prototype), "validate", this).call(this);
	    }
	  }]);
	  return SelectField;
	}(BaseField);

	var _templateObject$8;
	function ownKeys$9(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$9(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$9(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$9(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var Options = /*#__PURE__*/function () {
	  function Options(options) {
	    babelHelpers.classCallCheck(this, Options);
	    options = _objectSpread$9(_objectSpread$9({}, {
	      switcher: null
	    }), options);
	    this.switcher = options.switcher;
	    this.options = [];
	    this.block = null;
	    this.list = null;
	    this.ids = new Map([["blockId", "social-group-copy-options-block"], ["listId", "social-group-copy-options-list-block"]]);
	    this.classes = new Map([["block", "social-group-copy-options-block"], ["switcher", "social-group-copy-options-title-block"], ["optionsList", "social-group-copy-options-list hide fade-out"]]);
	  }
	  babelHelpers.createClass(Options, [{
	    key: "onAppendToParent",
	    value: function onAppendToParent() {
	      var _this = this;
	      this.block = document.getElementById(this.ids.get("blockId"));
	      this.list = document.getElementById(this.ids.get("listId"));
	      this.options.forEach(function (option) {
	        return option.onAppendToParent(_this.list);
	      });
	      this.switcher.onAppendToParent(this.list);
	    }
	  }, {
	    key: "addOption",
	    value: function addOption(option) {
	      this.options.push(option);
	      this.switcher.addOption(option);
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_templateObject$8 || (_templateObject$8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div id=\"", "\" class=\"", "\">\n\t\t\t\t", "\n\t\t\t\t<div id=\"", "\" class=\"", "\"></div>\n\t\t\t</div>\n\t\t"])), this.ids.get("blockId"), this.classes.get("block"), this.switcher.render(), this.ids.get("listId"), this.classes.get("optionsList"));
	    }
	  }, {
	    key: "getValues",
	    value: function getValues() {
	      var optionsValues = {};
	      this.options.forEach(function (option) {
	        optionsValues = _objectSpread$9(_objectSpread$9({}, optionsValues), option.getValues());
	      });
	      return optionsValues;
	    }
	  }]);
	  return Options;
	}();

	var _templateObject$9;
	function ownKeys$a(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$a(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$a(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$a(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var Switcher = /*#__PURE__*/function () {
	  function Switcher(options) {
	    babelHelpers.classCallCheck(this, Switcher);
	    options = _objectSpread$a(_objectSpread$a({}, {
	      title: "Title"
	    }), options);
	    this.title = options.title;
	    this.container = null;
	    this.options = [];
	    this.ids = new Map([["switcherId", "social-group-copy-switcher"]]);
	    this.classes = new Map([["switcher", "social-group-copy-switcher"], ["switcherMore", "social-group-copy-switcher-more"], ["switcherPromo", "social-group-copy-switcher-promo"], ["opened", "opened"], ["hide", "hide"], ["easingIn", "fade-in"], ["easingOut", "fade-out"]]);
	  }
	  babelHelpers.createClass(Switcher, [{
	    key: "addOption",
	    value: function addOption(option) {
	      this.options.push(option);
	    }
	  }, {
	    key: "onAppendToParent",
	    value: function onAppendToParent(optionsContainer) {
	      this.optionsContainer = optionsContainer;
	      this.container = document.getElementById(this.ids.get("switcherId"));
	      main_core.Event.bind(this.container, "click", this.onClick.bind(this));
	    }
	  }, {
	    key: "isOpened",
	    value: function isOpened() {
	      return main_core.Dom.hasClass(this.container, this.classes.get("opened"));
	    }
	    /**
	     * @returns {HTMLElement}
	     */
	  }, {
	    key: "render",
	    value: function render() {
	      this.title = main_core.Text.encode(this.title);
	      var options = this.options.map(function (option) {
	        return option.renderPromo();
	      });
	      return main_core.Tag.render(_templateObject$9 || (_templateObject$9 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div id=\"", "\" class=\"", "\">\n\t\t\t\t<div class=\"", "\">", "</div>\n\t\t\t\t<div class=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), this.ids.get("switcherId"), this.classes.get("switcher"), this.classes.get("switcherMore"), this.title, this.classes.get("switcherPromo"), options);
	    }
	  }, {
	    key: "onClick",
	    value: function onClick(event) {
	      var target = event.target;
	      var targetId = target.getAttribute("id");
	      if (!targetId) {
	        target = event.currentTarget;
	        targetId = target.getAttribute("id");
	      }
	      if (targetId === this.ids.get("switcherId")) {
	        this.switchOptions();
	      }
	    }
	  }, {
	    key: "switchOptions",
	    value: function switchOptions() {
	      this.constructor.switchOptions(this);
	    }
	  }], [{
	    key: "switchOptions",
	    value: function switchOptions(switcher) {
	      if (main_core.Dom.hasClass(switcher.optionsContainer, switcher.classes.get("easingOut"))) {
	        main_core.Dom.removeClass(switcher.optionsContainer, switcher.classes.get("hide"));
	        main_core.Dom.removeClass(switcher.optionsContainer, switcher.classes.get("easingOut"));
	        main_core.Dom.addClass(switcher.container, switcher.classes.get("opened"));
	        main_core.Dom.addClass(switcher.optionsContainer, switcher.classes.get("easingIn"));
	      } else {
	        main_core.Dom.removeClass(switcher.container, switcher.classes.get("opened"));
	        main_core.Dom.removeClass(switcher.optionsContainer, switcher.classes.get("easingIn"));
	        main_core.Dom.addClass(switcher.optionsContainer, switcher.classes.get("easingOut"));
	      }
	    }
	  }]);
	  return Switcher;
	}();

	var _templateObject$a;
	function ownKeys$b(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$b(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$b(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$b(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var CheckboxField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(CheckboxField, _BaseField);
	  function CheckboxField(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, CheckboxField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CheckboxField).call(this, options));
	    options = _objectSpread$b(_objectSpread$b({}, {
	      checked: true,
	      disabled: false
	    }), options);
	    _this.checked = options.checked;
	    _this.disabled = options.disabled;
	    _this.classes.set("control", "ui-ctl ui-ctl-checkbox ui-ctl-wa ui-ctl-xs social-group-copy-checkbox");
	    _this.classes.set("innerControl", "ui-ctl-element");
	    _this.classes.set("title", "ui-ctl-label-text");

	    // todo tmp delete after main 20.0.200
	    _this.eventNamespace = "BX.Socialnetwork.CheckboxField:";
	    if (typeof _this.setEventNamespace === "function") {
	      _this.eventNamespace = "";
	      _this.setEventNamespace("BX.Socialnetwork.CheckboxField");
	    }
	    return _this;
	  }
	  babelHelpers.createClass(CheckboxField, [{
	    key: "setChecked",
	    value: function setChecked(checked) {
	      this.innerControl.checked = checked;
	    }
	    /**
	     * @returns {HTMLElement}
	     */
	  }, {
	    key: "render",
	    value: function render() {
	      var onChange = this.onChange.bind(this);
	      this.fieldTitle = main_core.Text.encode(this.fieldTitle);
	      this.fieldName = main_core.Text.encode(this.fieldName);
	      return main_core.Tag.render(_templateObject$a || (_templateObject$a = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<label class=\"", "\">\n\t\t\t\t<input id=\"", "\" ", " ", " \n\t\t\t\t\ttype=\"checkbox\" name=\"", "\" \n\t\t\t\t\tonchange=\"", "\" class=\"", "\">\n\t\t\t\t<div id=\"", "\" class=\"", "\">", "</div>\n\t\t\t</label>\n\t\t"])), this.classes.get("control"), this.innerControlId, this.disabled ? "disabled" : "", this.checked ? "checked" : "", this.fieldName, onChange, this.classes.get("innerControl"), this.titleId, this.classes.get("title"), this.fieldTitle);
	    }
	  }, {
	    key: "onChange",
	    value: function onChange() {
	      this.validate();
	      this.emit(this.eventNamespace + this.fieldName + ":onChange", {
	        checked: this.innerControl.checked
	      });
	    }
	  }, {
	    key: "isDisabled",
	    value: function isDisabled() {
	      return Boolean(this.innerControl.disabled);
	    }
	    /**
	     * @param {Boolean} disabled
	     */
	  }, {
	    key: "changeDisabled",
	    value: function changeDisabled(disabled) {
	      if (disabled) {
	        this.innerControl.checked = false;
	        this.setValue("");
	      }
	      this.innerControl.disabled = disabled;
	    }
	  }, {
	    key: "validate",
	    value: function validate() {
	      this.setValue(this.innerControl.checked ? "Y" : "");
	      return babelHelpers.get(babelHelpers.getPrototypeOf(CheckboxField.prototype), "validate", this).call(this);
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.innerControl.checked ? "Y" : "";
	    }
	  }]);
	  return CheckboxField;
	}(BaseField);

	var _templateObject$b, _templateObject2$5;
	function ownKeys$c(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$c(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$c(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$c(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var ChildOption = /*#__PURE__*/function () {
	  function ChildOption(options) {
	    babelHelpers.classCallCheck(this, ChildOption);
	    options = _objectSpread$c(_objectSpread$c({}, {
	      fieldTitle: "title",
	      data: {},
	      switcher: null,
	      value: ""
	    }), options);
	    this.fieldTitle = main_core.Text.encode(options.fieldTitle);
	    this.data = options.data;
	    this.value = options.value ? options.value : "";
	    this.switcher = options.switcher;
	    this.titleControl = null;
	    this.titleId = "child-option-title-id";
	    this.optionsContainer = null;
	    this.blockId = "base";
	    this.block = null;
	    this.fields = new Set();
	    this.classes = new Map([["optionItem", "social-group-copy-options-item social-group-copy-form-field-list-block"], ["leftColumn", "social-group-copy-options-item-column-left"], ["fieldTitle", "social-group-copy-options-item-name"], ["rightColumn", "social-group-copy-options-item-column-right"], ["promoText", "social-group-copy-switcher-promo-text"], ["itemHighlight", "item-highlight"]]);
	  }
	  babelHelpers.createClass(ChildOption, [{
	    key: "onAppendToParent",
	    value: function onAppendToParent(optionsContainer) {
	      this.optionsContainer = optionsContainer;
	      if (main_core.Type.isPlainObject(this.data)) {
	        this.append(this.data);
	      }
	      this.fields.forEach(function (field) {
	        return field.onAppendToParent();
	      });
	      this.block = document.getElementById(this.blockId);
	      this.titleControl = document.getElementById(this.titleId);
	    }
	  }, {
	    key: "append",
	    value: function append(data) {
	      this.optionsContainer.appendChild(main_core.Tag.render(_templateObject$b || (_templateObject$b = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div id=\"", "\" class=\"", "\">\n\t\t\t\t\t<div class=\"", "\">\n\t\t\t\t\t\t<div id=\"", "\" class=\"", "\">", "</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"", "\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), this.blockId, this.classes.get("optionItem"), this.classes.get("leftColumn"), this.titleId, this.classes.get("fieldTitle"), this.fieldTitle, this.classes.get("rightColumn"), this.getChildRender(data)));
	    }
	    /**
	     * @returns {HTMLElement}
	     */
	  }, {
	    key: "getChildRender",
	    value: function getChildRender(data) {
	      return main_core.Tag.render(_templateObject2$5 || (_templateObject2$5 = babelHelpers.taggedTemplateLiteral([""])));
	    }
	  }, {
	    key: "getValues",
	    value: function getValues() {
	      var fieldsValues = {};
	      this.fields.forEach(function (field) {
	        fieldsValues[field.getName()] = field.getValue();
	      });
	      return fieldsValues;
	    }
	  }, {
	    key: "onClick",
	    value: function onClick(event) {
	      var _this = this;
	      this.switcher.switchOptions();
	      if (this.switcher.isOpened()) {
	        setTimeout(function () {
	          main_core.Dom.addClass(_this.block, _this.classes.get("itemHighlight"));
	          var position = main_core.Dom.getPosition(_this.block);
	          window.scrollBy({
	            top: position.top,
	            left: position.left,
	            behavior: "smooth"
	          });
	          // todo hack for slider
	          //this.block.scrollIntoView({behavior: "smooth", block: "start"});
	          setTimeout(function () {
	            main_core.Dom.removeClass(_this.block, _this.classes.get("itemHighlight"));
	          }, 3000);
	        }, 1000);
	      }
	    }
	  }, {
	    key: "changeTitle",
	    value: function changeTitle(title) {
	      this.fieldTitle = title;
	      this.titleControl.innerHTML = main_core.Text.encode(this.fieldTitle);
	    }
	  }]);
	  return ChildOption;
	}();

	var _templateObject$c, _templateObject2$6, _templateObject3, _templateObject4;
	var Features = /*#__PURE__*/function (_ChildOption) {
	  babelHelpers.inherits(Features, _ChildOption);
	  function Features(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, Features);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Features).call(this, options));
	    _this.promoId = "features";
	    _this.blockId = "features-block";
	    _this.classes.set("featuresList", "social-group-copy-features-list");
	    _this.classes.set("featureItem", "social-group-copy-feature-item social-group-copy-feature-item-tree");
	    return _this;
	  }
	  babelHelpers.createClass(Features, [{
	    key: "onAppendToParent",
	    value: function onAppendToParent(optionsContainer) {
	      babelHelpers.get(babelHelpers.getPrototypeOf(Features.prototype), "onAppendToParent", this).call(this, optionsContainer);
	      main_core.Event.bind(document.getElementById(this.promoId), "click", this.onClick.bind(this));
	    }
	  }, {
	    key: "renderPromo",
	    value: function renderPromo() {
	      return main_core.Tag.render(_templateObject$c || (_templateObject$c = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span id=\"", "\" class=\"", "\">", "</span>\n\t\t"])), this.promoId, this.classes.get("promoText"), this.fieldTitle);
	    }
	    /**
	     * @returns {HTMLElement}
	     */
	  }, {
	    key: "getChildRender",
	    value: function getChildRender(data) {
	      return main_core.Tag.render(_templateObject2$6 || (_templateObject2$6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"", "\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), this.classes.get("featuresList"), this.getFeaturesRender(data));
	    }
	  }, {
	    key: "selfClean",
	    value: function selfClean() {
	      if (main_core.Type.isDomNode(this.block)) {
	        main_core.Dom.remove(this.block);
	      }
	    }
	  }, {
	    key: "getFeaturesRender",
	    value: function getFeaturesRender(features) {
	      var _this2 = this;
	      return Object.values(features).map(function (feature) {
	        var hasProperty = Object.prototype.hasOwnProperty;
	        var childrenFields = Object.values(feature["Children"]).map(function (featureChild) {
	          var childField = new CheckboxField({
	            fieldTitle: featureChild["Title"],
	            fieldName: "features[" + feature["Name"] + "][" + featureChild["Name"] + "]",
	            validators: [],
	            parentNode: _this2.optionsContainer,
	            checked: hasProperty.call(featureChild, "Checked") ? featureChild["Checked"] : true
	          });
	          _this2.fields.add(childField);
	          return childField;
	        });
	        var childrenRender = childrenFields.map(function (childrenField) {
	          return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["", ""])), childrenField.render());
	        });
	        var featureField = new CheckboxField({
	          fieldTitle: feature["Title"],
	          fieldName: "features[" + feature["Name"] + "][active]",
	          validators: [],
	          parentNode: _this2.optionsContainer,
	          checked: hasProperty.call(feature, "Checked") ? feature["Checked"] : true,
	          onCustomChange: function (event) {
	            this.forEach(function (childrenField) {
	              childrenField.setChecked(event.currentTarget.checked);
	            });
	          }.bind(childrenFields)
	        });
	        _this2.fields.add(featureField);
	        return main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this2.classes.get("featureItem"), featureField.render(), childrenRender);
	      });
	    }
	  }]);
	  return Features;
	}(ChildOption);

	var _templateObject$d;
	var Rights = /*#__PURE__*/function (_ChildOption) {
	  babelHelpers.inherits(Rights, _ChildOption);
	  function Rights(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, Rights);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Rights).call(this, options));
	    _this.promoId = "rights";
	    _this.blockId = "rights-block";
	    _this.titleId = "rights-option-title-id";
	    _this.selectField = null;
	    return _this;
	  }
	  babelHelpers.createClass(Rights, [{
	    key: "onAppendToParent",
	    value: function onAppendToParent(optionsContainer) {
	      babelHelpers.get(babelHelpers.getPrototypeOf(Rights.prototype), "onAppendToParent", this).call(this, optionsContainer);
	      main_core.Event.bind(document.getElementById(this.promoId), "click", this.onClick.bind(this));
	    }
	  }, {
	    key: "renderPromo",
	    value: function renderPromo() {
	      return main_core.Tag.render(_templateObject$d || (_templateObject$d = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span id=\"", "\" class=\"", "\">\n\t\t\t\t", "\n\t\t\t</span>\n\t\t"])), this.promoId, this.classes.get("promoText"), main_core.Loc.getMessage("SGCG_OPTIONS_PERMS_TITLE"));
	    }
	  }, {
	    key: "getChildRender",
	    value: function getChildRender(data) {
	      this.selectField = new SelectField({
	        fieldTitle: this.fieldTitle,
	        fieldName: "initiate_perms",
	        value: this.value,
	        list: data
	      });
	      this.fields.add(this.selectField);
	      return this.selectField.renderRightColumn();
	    }
	  }, {
	    key: "changeSelectOptions",
	    value: function changeSelectOptions(data) {
	      this.selectField.changeOptions(data);
	    }
	  }]);
	  return Rights;
	}(ChildOption);

	function ownKeys$d(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$d(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$d(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$d(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var SelectorField = /*#__PURE__*/function (_CustomField) {
	  babelHelpers.inherits(SelectorField, _CustomField);
	  function SelectorField(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, SelectorField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SelectorField).call(this, options));
	    options = _objectSpread$d(_objectSpread$d({}, {
	      selectorId: ""
	    }), options);
	    _this.selectorId = options.selectorId;
	    _this.selector = null;
	    _this.value = {};
	    _this.init();
	    return _this;
	  }
	  babelHelpers.createClass(SelectorField, [{
	    key: "init",
	    value: function init() {
	      var _this2 = this;
	      /* eslint-disable */
	      this.selector = BX.UI.TileSelector.getById(this.selectorId);
	      if (this.selector) {
	        BX.addCustomEvent(this.selector, this.selector.events.search, this.onSearch.bind(this));
	        BX.addCustomEvent(this.selector, this.selector.events.input, this.onInput.bind(this));
	        BX.addCustomEvent(this.selector, this.selector.events.buttonSelect, this.buttonSelect.bind(this));
	        BX.addCustomEvent(this.selector, this.selector.events.tileRemove, this.removeTile.bind(this));
	        BX.addCustomEvent(this.selector, this.selector.events.tileClick, this.clickTile.bind(this));
	        this.selector.getTiles().map(function (tile) {
	          _this2.setValue(tile.id, tile);
	        });
	      }
	      /* eslint-enable */
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(key, value) {
	      this.value[key] = value;
	    }
	  }, {
	    key: "deleteValue",
	    value: function deleteValue(key) {
	      delete this.value[key];
	    }
	  }, {
	    key: "onSearch",
	    value: function onSearch(inputValue) {
	      if (inputValue) {
	        this.selector.addTile(inputValue, [], inputValue);
	        var tile = this.selector.getTile(inputValue);
	        this.setValue(tile.id, tile);
	      }
	    }
	  }, {
	    key: "onInput",
	    value: function onInput(inputValue) {
	      //todo later ajax search.tags.input
	    }
	  }, {
	    key: "buttonSelect",
	    value: function buttonSelect() {
	      //todo if need
	    }
	  }, {
	    key: "removeTile",
	    value: function removeTile(tile) {
	      this.deleteValue(tile.id);
	    }
	  }, {
	    key: "clickTile",
	    value: function clickTile(tile) {
	      //todo if need
	    }
	  }]);
	  return SelectorField;
	}(CustomField);

	var TagField = /*#__PURE__*/function (_SelectorField) {
	  babelHelpers.inherits(TagField, _SelectorField);
	  function TagField() {
	    babelHelpers.classCallCheck(this, TagField);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TagField).apply(this, arguments));
	  }
	  babelHelpers.createClass(TagField, [{
	    key: "getValue",
	    value: function getValue() {
	      return Object.keys(this.value).join(",");
	    }
	  }]);
	  return TagField;
	}(SelectorField);

	var _templateObject$e;
	function ownKeys$e(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$e(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$e(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$e(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var Tags = /*#__PURE__*/function (_ChildOption) {
	  babelHelpers.inherits(Tags, _ChildOption);
	  function Tags(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, Tags);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Tags).call(this, options));
	    options = _objectSpread$e(_objectSpread$e({}, {
	      tagsFieldContainerId: ""
	    }), options);
	    _this.promoId = "tags";
	    _this.blockId = "tags-block";
	    _this.tagsFieldContainerId = options.tagsFieldContainerId;
	    return _this;
	  }
	  babelHelpers.createClass(Tags, [{
	    key: "onAppendToParent",
	    value: function onAppendToParent(optionsContainer) {
	      babelHelpers.get(babelHelpers.getPrototypeOf(Tags.prototype), "onAppendToParent", this).call(this, optionsContainer);
	      main_core.Event.bind(document.getElementById(this.promoId), "click", this.onClick.bind(this));
	    }
	  }, {
	    key: "renderPromo",
	    value: function renderPromo() {
	      return main_core.Tag.render(_templateObject$e || (_templateObject$e = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span id=\"", "\" class=\"", "\">", "</span>\n\t\t"])), this.promoId, this.classes.get("promoText"), this.fieldTitle);
	    }
	  }, {
	    key: "getChildRender",
	    value: function getChildRender(data) {
	      var tagField = new TagField({
	        selectorId: "tags-list",
	        fieldTitle: this.fieldTitle,
	        fieldName: "keywords",
	        fieldContainerId: this.tagsFieldContainerId
	      });
	      this.fields.add(tagField);
	      return tagField.renderRightColumn();
	    }
	  }]);
	  return Tags;
	}(ChildOption);

	var _templateObject$f, _templateObject2$7, _templateObject3$1, _templateObject4$1, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9;
	function ownKeys$f(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$f(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$f(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$f(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var Types = /*#__PURE__*/function (_ChildOption) {
	  babelHelpers.inherits(Types, _ChildOption);
	  function Types(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, Types);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Types).call(this, options));
	    options = _objectSpread$f(_objectSpread$f({}, {
	      isProject: false,
	      isExtranetGroup: false,
	      isExtranetInstalled: false,
	      isIntranetInstalled: false,
	      isLandingInstalled: false
	    }), options);
	    _this.isProject = options.isProject;
	    _this.isExtranetGroup = options.isExtranetGroup;
	    _this.isExtranetInstalled = options.isExtranetInstalled;
	    _this.isIntranetInstalled = options.isIntranetInstalled;
	    _this.isLandingInstalled = options.isLandingInstalled;
	    _this.promoId = "types";
	    _this.blockId = "types-block";
	    _this.titleId = "types-option-title-id";
	    _this.classes.set("typesList", "social-group-copy-types-list");
	    _this.classes.set("typeItem", "social-group-copy-feature-item");
	    _this.checkboxEventName = "BX.Socialnetwork.CheckboxField";
	    return _this;
	  }
	  babelHelpers.createClass(Types, [{
	    key: "onAppendToParent",
	    value: function onAppendToParent(optionsContainer) {
	      babelHelpers.get(babelHelpers.getPrototypeOf(Types.prototype), "onAppendToParent", this).call(this, optionsContainer);
	      main_core.Event.bind(document.getElementById(this.promoId), "click", this.onClick.bind(this));
	    }
	  }, {
	    key: "renderPromo",
	    value: function renderPromo() {
	      return main_core.Tag.render(_templateObject$f || (_templateObject$f = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span id=\"", "\" class=\"", "\">\n\t\t\t\t", "\n\t\t\t</span>\n\t\t"])), this.promoId, this.classes.get("promoText"), main_core.Loc.getMessage("SGCG_OPTIONS_TYPE_TITLE"));
	    }
	  }, {
	    key: "getChildRender",
	    value: function getChildRender(data) {
	      return main_core.Tag.render(_templateObject2$7 || (_templateObject2$7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"", "\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), this.classes.get("typesList"), this.getTypesRender(data));
	    }
	  }, {
	    key: "getTypesRender",
	    value: function getTypesRender(data) {
	      var result = [];
	      var visibleField = this.createVisibleField(data);
	      result.push(main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["", ""])), visibleField.render()));
	      var openField = this.createOpenedField(data);
	      result.push(main_core.Tag.render(_templateObject4$1 || (_templateObject4$1 = babelHelpers.taggedTemplateLiteral(["", ""])), openField.render()));
	      var closeField = this.createCloseField(data);
	      result.push(main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["", ""])), closeField.render()));
	      if (this.isExtranetInstalled) {
	        var extranetField = this.createExtranetField(data);
	        result.push(main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["", ""])), extranetField.render()));
	      }
	      if (this.isIntranetInstalled) {
	        var projectField = this.createProjectField(data);
	        result.push(main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["", ""])), projectField.render()));
	      }
	      if (this.isLandingInstalled) {
	        var landingField = this.createLandingField(data);
	        result.push(main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["", ""])), landingField.render()));
	      }
	      return main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["", ""])), result);
	    }
	  }, {
	    key: "createVisibleField",
	    value: function createVisibleField(data) {
	      var visibleField = new CheckboxField({
	        fieldTitle: this.isProject ? main_core.Loc.getMessage("SGCG_OPTIONS_PROJECT_TYPE_VISIBLE") : main_core.Loc.getMessage("SGCG_OPTIONS_GROUP_TYPE_VISIBLE"),
	        fieldName: "visible",
	        validators: [],
	        checked: data["VISIBLE"] === "Y" && !this.isExtranetGroup,
	        disabled: this.isExtranetGroup
	      });
	      this.fields.add(visibleField);
	      this.subscribeToField(this.checkboxEventName + ":project:onChange", function (baseEvent) {
	        visibleField.changeTitle(baseEvent.data.checked ? main_core.Loc.getMessage("SGCG_OPTIONS_PROJECT_TYPE_VISIBLE") : main_core.Loc.getMessage("SGCG_OPTIONS_GROUP_TYPE_VISIBLE"));
	      });
	      this.subscribeToField(this.checkboxEventName + ":extranet_group:onChange", function (baseEvent) {
	        visibleField.changeDisabled(baseEvent.data.checked);
	      });
	      return visibleField;
	    }
	  }, {
	    key: "createOpenedField",
	    value: function createOpenedField(data) {
	      var openField = new CheckboxField({
	        fieldTitle: this.isProject ? main_core.Loc.getMessage("SGCG_OPTIONS_PROJECT_TYPE_OPEN") : main_core.Loc.getMessage("SGCG_OPTIONS_GROUP_TYPE_OPEN"),
	        fieldName: "opened",
	        validators: [],
	        checked: data["OPENED"] === "Y" && !this.isExtranetGroup,
	        disabled: data["VISIBLE"] !== "Y" || this.isExtranetGroup
	      });
	      this.fields.add(openField);
	      this.subscribeToField(this.checkboxEventName + ":project:onChange", function (baseEvent) {
	        openField.changeTitle(baseEvent.data.checked ? main_core.Loc.getMessage("SGCG_OPTIONS_PROJECT_TYPE_OPEN") : main_core.Loc.getMessage("SGCG_OPTIONS_GROUP_TYPE_OPEN"));
	      });
	      this.subscribeToField(this.checkboxEventName + ":visible:onChange", function (baseEvent) {
	        openField.changeDisabled(!baseEvent.data.checked);
	      });
	      this.subscribeToField(this.checkboxEventName + ":extranet_group:onChange", function (baseEvent) {
	        openField.changeDisabled(baseEvent.data.checked);
	      });
	      return openField;
	    }
	  }, {
	    key: "createCloseField",
	    value: function createCloseField(data) {
	      var closeField = new CheckboxField({
	        fieldTitle: this.isProject ? main_core.Loc.getMessage("SGCG_OPTIONS_PROJECT_TYPE_CLOSED") : main_core.Loc.getMessage("SGCG_OPTIONS_GROUP_TYPE_CLOSED"),
	        fieldName: "closed",
	        validators: [],
	        checked: data["CLOSED"] === "Y",
	        disabled: this.isExtranetGroup
	      });
	      this.fields.add(closeField);
	      this.subscribeToField(this.checkboxEventName + ":project:onChange", function (baseEvent) {
	        closeField.changeTitle(baseEvent.data.checked ? main_core.Loc.getMessage("SGCG_OPTIONS_PROJECT_TYPE_CLOSED") : main_core.Loc.getMessage("SGCG_OPTIONS_GROUP_TYPE_CLOSED"));
	      });
	      this.subscribeToField(this.checkboxEventName + ":extranet_group:onChange", function (baseEvent) {
	        closeField.changeDisabled(baseEvent.data.checked);
	      });
	      return closeField;
	    }
	  }, {
	    key: "createExtranetField",
	    value: function createExtranetField(data) {
	      var extranetField = new CheckboxField({
	        fieldTitle: this.isProject ? main_core.Loc.getMessage("SGCG_OPTIONS_PROJECT_TYPE_EXTRANET") : main_core.Loc.getMessage("SGCG_OPTIONS_GROUP_TYPE_EXTRANET"),
	        fieldName: "extranet_group",
	        validators: [],
	        checked: this.isExtranetGroup
	      });
	      this.fields.add(extranetField);
	      this.subscribeToField(this.checkboxEventName + ":project:onChange", function (baseEvent) {
	        extranetField.changeTitle(baseEvent.data.checked ? main_core.Loc.getMessage("SGCG_OPTIONS_PROJECT_TYPE_EXTRANET") : main_core.Loc.getMessage("SGCG_OPTIONS_GROUP_TYPE_EXTRANET"));
	      });
	      return extranetField;
	    }
	  }, {
	    key: "createProjectField",
	    value: function createProjectField(data) {
	      var projectField = new CheckboxField({
	        fieldTitle: main_core.Loc.getMessage("SGCG_OPTIONS_TYPE_PROJECT"),
	        fieldName: "project",
	        validators: [],
	        checked: data["PROJECT"] === "Y"
	      });
	      this.fields.add(projectField);
	      return projectField;
	    }
	  }, {
	    key: "createLandingField",
	    value: function createLandingField(data) {
	      var landingField = new CheckboxField({
	        fieldTitle: main_core.Loc.getMessage("SGCG_OPTIONS_TYPE_LANDING_MSGVER_2"),
	        fieldName: "landing",
	        validators: [],
	        checked: data["LANDING"] === "Y"
	      });
	      this.fields.add(landingField);
	      return landingField;
	    }
	  }, {
	    key: "subscribeToField",
	    value: function subscribeToField(eventName, callback) {
	      main_core.Event.EventEmitter.subscribe(eventName, callback);
	    }
	  }]);
	  return Types;
	}(ChildOption);

	function ownKeys$g(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$g(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$g(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$g(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var CopyingManager = /*#__PURE__*/function () {
	  function CopyingManager(options) {
	    babelHelpers.classCallCheck(this, CopyingManager);
	    options = _objectSpread$g(_objectSpread$g({}, {
	      signedParameters: "",
	      formContainerId: "",
	      isProject: "N",
	      isExtranet: "N",
	      isExtranetGroup: "N",
	      isExtranetInstalled: "N",
	      isIntranetInstalled: "N",
	      groupData: {},
	      imageFieldContainerId: "",
	      ownerFieldContainerId: "",
	      helpersFieldContainerId: "",
	      isLandingInstalled: "",
	      tagsFieldContainerId: "",
	      copyButtonId: "",
	      cancelButtonId: ""
	    }), options);
	    this.signedParameters = options.signedParameters;
	    this.formContainerId = options.formContainerId;
	    this.isProject = options.isProject === "Y";
	    this.isExtranet = options.isExtranet === "Y";
	    this.isExtranetGroup = options.isExtranetGroup === "Y";
	    this.isExtranetInstalled = options.isExtranetInstalled === "Y";
	    this.isIntranetInstalled = options.isIntranetInstalled === "Y";
	    this.isLandingInstalled = options.isLandingInstalled === "Y";
	    this.groupData = options.groupData;
	    this.subjects = this.groupData["SUBJECTS"];
	    this.features = this.groupData["FEATURES"];
	    this.initiatePerms = this.groupData["LIST_INITIATE_PERMS"];
	    this.imageFieldContainerId = options.imageFieldContainerId;
	    this.ownerFieldContainerId = options.ownerFieldContainerId;
	    this.helpersFieldContainerId = options.helpersFieldContainerId;
	    this.tagsFieldContainerId = options.tagsFieldContainerId;
	    this.requestSender = new RequestSender({
	      signedParameters: this.signedParameters
	    });
	    this.requestSender.setProjectMarker(this.isProject);
	    this.form = new Form({
	      requestSender: this.requestSender,
	      groupData: options.groupData,
	      copyButtonId: options.copyButtonId,
	      cancelButtonId: options.cancelButtonId
	    });
	    this.buildForm();
	  }
	  babelHelpers.createClass(CopyingManager, [{
	    key: "buildForm",
	    value: function buildForm() {
	      this.checkboxEventName = "BX.Socialnetwork.CheckboxField";
	      this.form.addField(new ImageField({
	        fieldTitle: main_core.Loc.getMessage("SGCG_UPLOAD_IMAGE_TITLE"),
	        fieldName: "image_id",
	        fieldContainerId: this.imageFieldContainerId
	      }));
	      this.form.addField(this.createDate());
	      if (!this.isProject) {
	        this.form.addField(this.createRangeDate());
	      }
	      this.form.addField(this.createOwner());
	      this.form.addField(new UserField({
	        selectorId: "group-copy-helpers",
	        fieldTitle: this.isProject ? main_core.Loc.getMessage("SGCG_PROJECT_HELPERS_TITLE") : main_core.Loc.getMessage("SGCG_GROUP_HELPERS_TITLE"),
	        fieldName: "moderators",
	        fieldContainerId: this.helpersFieldContainerId
	      }));
	      if (main_core.Type.isPlainObject(this.subjects)) {
	        this.form.addField(this.createSubject());
	      }
	      var switcher = new Switcher({
	        title: main_core.Loc.getMessage("SGCG_OPTIONS_TITLE")
	      });
	      var options = new Options({
	        switcher: switcher
	      });
	      var features = new Features({
	        fieldTitle: main_core.Loc.getMessage("SGCG_OPTIONS_FEATURES_TITLE"),
	        data: this.features,
	        switcher: switcher
	      });
	      options.addOption(features);

	      //todo SPAM_PERMS
	      options.addOption(this.createRights(switcher));
	      options.addOption(this.createTags(switcher));
	      options.addOption(this.createTypes(switcher));
	      this.form.addBlock(options);
	      this.form.renderTo(document.getElementById(this.formContainerId));
	    }
	  }, {
	    key: "subscribeToField",
	    value: function subscribeToField(eventName, callback) {
	      main_core.Event.EventEmitter.subscribe(eventName, callback);
	    }
	  }, {
	    key: "createTypes",
	    value: function createTypes(switcher) {
	      var types = new Types({
	        fieldTitle: this.isProject ? main_core.Loc.getMessage("SGCG_OPTIONS_PROJECT_TYPE_LABEL") : main_core.Loc.getMessage("SGCG_OPTIONS_GROUP_TYPE_LABEL"),
	        data: this.groupData,
	        switcher: switcher,
	        isProject: this.isProject,
	        isExtranetGroup: this.isExtranetGroup,
	        isExtranetInstalled: this.isExtranetInstalled,
	        isIntranetInstalled: this.isIntranetInstalled,
	        isLandingInstalled: this.isLandingInstalled
	      });
	      this.subscribeToField(this.checkboxEventName + ":project:onChange", function (baseEvent) {
	        types.changeTitle(baseEvent.data.checked ? main_core.Loc.getMessage("SGCG_OPTIONS_PROJECT_TYPE_LABEL") : main_core.Loc.getMessage("SGCG_OPTIONS_GROUP_TYPE_LABEL"));
	      });
	      return types;
	    }
	  }, {
	    key: "createTags",
	    value: function createTags(switcher) {
	      return new Tags({
	        fieldTitle: main_core.Loc.getMessage("SGCG_OPTIONS_KEYWORDS_TITLE"),
	        tagsFieldContainerId: this.tagsFieldContainerId,
	        switcher: switcher
	      });
	    }
	  }, {
	    key: "createRights",
	    value: function createRights(switcher) {
	      var _this = this;
	      var rights = new Rights({
	        fieldTitle: this.isProject ? main_core.Loc.getMessage("SGCG_OPTIONS_PROJECT_PERMS_LABEL") : main_core.Loc.getMessage("SGCG_OPTIONS_GROUP_PERMS_LABEL"),
	        value: this.groupData["INITIATE_PERMS"],
	        data: this.isProject ? this.initiatePerms.project : this.initiatePerms.group,
	        switcher: switcher
	      });
	      this.subscribeToField(this.checkboxEventName + ":project:onChange", function (baseEvent) {
	        rights.changeSelectOptions(baseEvent.data.checked ? _this.initiatePerms.project : _this.initiatePerms.group);
	        rights.changeTitle(baseEvent.data.checked ? main_core.Loc.getMessage("SGCG_OPTIONS_PROJECT_PERMS_LABEL") : main_core.Loc.getMessage("SGCG_OPTIONS_GROUP_PERMS_LABEL"));
	      });
	      return rights;
	    }
	  }, {
	    key: "createSubject",
	    value: function createSubject() {
	      var subject = new SelectField({
	        fieldTitle: this.isProject ? main_core.Loc.getMessage("SGCG_PROJECT_SUBJECT") : main_core.Loc.getMessage("SGCG_GROUP_SUBJECT"),
	        fieldName: "subject_id",
	        value: this.groupData["SUBJECT_ID"],
	        list: this.subjects
	      });
	      this.subscribeToField(this.checkboxEventName + ":project:onChange", function (baseEvent) {
	        subject.changeTitle(baseEvent.data.checked ? main_core.Loc.getMessage("SGCG_PROJECT_SUBJECT") : main_core.Loc.getMessage("SGCG_GROUP_SUBJECT"));
	      });
	      return subject;
	    }
	  }, {
	    key: "createOwner",
	    value: function createOwner() {
	      var owner = new UserField({
	        selectorId: "group-copy-owner",
	        multiple: false,
	        fieldTitle: this.isProject ? main_core.Loc.getMessage("SGCG_PROJECT_OWNER_TITLE") : main_core.Loc.getMessage("SGCG_GROUP_OWNER_TITLE"),
	        fieldName: "owner_id",
	        validators: [RequireValidator],
	        fieldContainerId: this.ownerFieldContainerId
	      });
	      this.subscribeToField(this.checkboxEventName + ":project:onChange", function (baseEvent) {
	        owner.changeTitle(baseEvent.data.checked ? main_core.Loc.getMessage("SGCG_PROJECT_OWNER_TITLE") : main_core.Loc.getMessage("SGCG_GROUP_OWNER_TITLE"));
	      });
	      return owner;
	    }
	  }, {
	    key: "createDate",
	    value: function createDate() {
	      var _this2 = this;
	      var date = new DateField({
	        fieldTitle: this.isProject ? main_core.Loc.getMessage("SGCG_PROJECT_START_POINT_TITLE") : main_core.Loc.getMessage("SGCG_GROUP_START_POINT_TITLE"),
	        fieldName: "start_point",
	        validators: [RequireValidator]
	      });
	      this.subscribeToField(this.checkboxEventName + ":project:onChange", function (baseEvent) {
	        if (_this2.isProject) {
	          date.changeTitle(baseEvent.data.checked ? main_core.Loc.getMessage("SGCG_PROJECT_START_POINT_TITLE") : main_core.Loc.getMessage("SGCG_GROUP_START_POINT_TITLE"));
	        } else {
	          date.toggleVisible(!baseEvent.data.checked);
	          date.changeTitle(baseEvent.data.checked ? main_core.Loc.getMessage("SGCG_PROJECT_RANGE_TITLE") : main_core.Loc.getMessage("SGCG_GROUP_START_POINT_TITLE"));
	        }
	      });
	      return date;
	    }
	  }, {
	    key: "createRangeDate",
	    value: function createRangeDate() {
	      var _this3 = this;
	      var dateRange = new DateRangeField({
	        fieldTitle: main_core.Loc.getMessage("SGCG_PROJECT_RANGE_TITLE"),
	        fieldName: "project_term",
	        visible: false
	      });
	      this.subscribeToField(this.checkboxEventName + ":project:onChange", function (baseEvent) {
	        if (!_this3.isProject) {
	          dateRange.toggleVisible(baseEvent.data.checked);
	        }
	      });
	      return dateRange;
	    }
	  }]);
	  return CopyingManager;
	}();

	exports.CopyingManager = CopyingManager;

}((this.BX.Socialnetwork = this.BX.Socialnetwork || {}),BX.UI,BX));
//# sourceMappingURL=script.js.map
