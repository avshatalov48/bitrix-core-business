/* eslint-disable */
(function (exports,main_core,ui_dialogs_messagebox,bp_field_type,bizproc_globals) {
	'use strict';

	var _templateObject;
	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var namespace = main_core.Reflection.namespace('BX.Bizproc.Component');
	var GlobalFieldEditComponent = /*#__PURE__*/function () {
	  function GlobalFieldEditComponent(options) {
	    babelHelpers.classCallCheck(this, GlobalFieldEditComponent);
	    babelHelpers.defineProperty(this, "correspondenceModeToIdName", {
	      constant: 'Constant',
	      variable: 'Variable'
	    });
	    if (!main_core.Type.isPlainObject(options)) {
	      return;
	    }
	    this.oldProperty = options.property;
	    this.documentType = options.documentType;
	    this.signedDocumentType = options.signedDocumentType;
	    this.mode = options.mode;
	    this.availableTypes = options.types;
	    this.visibilityNames = options.visibilityNames;
	    this.inputValueId = options.inputValueId;
	    this.multipleNode = options.multipleNode;
	    this.saveButtonNode = options.saveButtonNode;
	    this.form = options.form;
	    this.slider = options.slider;
	  }
	  babelHelpers.createClass(GlobalFieldEditComponent, [{
	    key: "init",
	    value: function init() {
	      this.sliderDict = this.slider ? this.slider.getData() : null;
	      this.editInputValue(this.oldProperty['Type'], this.oldProperty);
	      main_core.Event.bind(this.saveButtonNode, 'click', this.saveHandler.bind(this));
	    }
	  }, {
	    key: "editInputValue",
	    value: function editInputValue(type, property) {
	      var prop = main_core.Runtime.clone(property);
	      var defaultProperty = {
	        Type: type !== null && type !== void 0 ? type : 'string',
	        Multiple: false,
	        Default: '',
	        Placeholder: main_core.Loc.getMessage('BIZPROC_GLOBALFIELD_EDIT_TMP_EMPTY')
	      };
	      if (this.availableTypes[defaultProperty.Type] === undefined) {
	        defaultProperty.Type = Object.keys(this.availableTypes)[0];
	      }
	      if (!main_core.Type.isPlainObject(property)) {
	        prop = defaultProperty;
	      } else {
	        if (main_core.Type.isString(property['Multiple'])) {
	          prop['Multiple'] = property['Multiple'] === 'Y';
	        }
	        prop = _objectSpread(_objectSpread({}, defaultProperty), prop);
	      }
	      this.multipleNode.value = prop['Multiple'] ? 'Y' : 'N';
	      if (prop.Type === 'select' && property.Options === undefined) {
	        var _this$oldProperty$Opt;
	        prop.Options = (_this$oldProperty$Opt = this.oldProperty.Options) !== null && _this$oldProperty$Opt !== void 0 ? _this$oldProperty$Opt : {};
	      }
	      var control = BX.Bizproc.FieldType.renderControl(this.documentType, prop, 'VALUE', prop['Default'], 'public');
	      control.className = 'ui-ctl ui-ctl-textbox ui-ctl-w100';
	      control.id = this.inputValueId;
	      var inputValueNode = document.getElementById(this.inputValueId);
	      if (inputValueNode) {
	        inputValueNode.replaceWith(control);
	      }
	      if (prop.Type !== 'select' && document.getElementById('bizproc_globEditComponent')) {
	        document.getElementById('bizproc_globEditComponent').remove();
	        this.oldProperty.Options = {};
	      } else if (prop.Type === 'select' && !document.getElementById('bizproc_globEditComponent')) {
	        var wrapper = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-ctl ui-ctl-w100\" id=\"bizproc_globEditComponent\"></div>"])));
	        var optionControl = BX.Bizproc.FieldType.createControlOptions(prop, this.setSelectOptionFromForm.bind(this));
	        optionControl.className = 'ui-form-label';
	        main_core.Dom.append(optionControl, wrapper);
	        wrapper.getElementsByTagName('textarea')[0].className = 'ui-ctl-element ui-ctl-textarea ui-ctl-resize-y ui-ctl-w100';
	        main_core.Dom.style(wrapper.getElementsByTagName('textarea')[0], 'paddingTop', '6px');
	        wrapper.getElementsByTagName('button')[0].className = 'ui-btn ui-btn-xs ui-btn-light-border';
	        control.before(wrapper);
	      }
	      if (prop['Type'] === 'user') {
	        return;
	      }
	      if (control.getElementsByTagName('a').length > 0) {
	        var buttonAdd = control.getElementsByTagName('a')[0];
	        main_core.Event.bind(buttonAdd, 'click', function () {
	          var values = document.getElementsByName('VALUE[]');
	          var value = main_core.Runtime.clone(values[values.length - 1]);
	          if (prop['Type'] !== 'date' && prop['Type'] !== 'datetime') {
	            // remove wrapper div
	            var parent = values[values.length - 1].parentNode;
	            if (parent) {
	              parent.remove();
	            }
	            main_core.Dom.insertBefore(value, buttonAdd.parentNode);
	          }
	        });
	      }
	    }
	  }, {
	    key: "setSelectOptionFromForm",
	    value: function setSelectOptionFromForm(options) {
	      this.oldProperty.Options = options;
	      this.editInputValue('select', {
	        Options: options,
	        Type: 'select',
	        Multiple: this.multipleNode.value
	      });
	    }
	  }, {
	    key: "saveHandler",
	    value: function saveHandler() {
	      var _this = this;
	      var formElements = this.form.elements;
	      var id = main_core.Type.isStringFilled(this.oldProperty['id']) ? this.oldProperty['id'] : '';
	      var property = {
	        Name: formElements['NAME'].value,
	        Description: formElements['DESCRIPTION'].value,
	        Type: formElements['TYPE'].value,
	        Options: '',
	        Default: this.getValues(formElements),
	        Visibility: formElements['VISIBILITY'].value,
	        Multiple: formElements['MULTIPLE'].value,
	        Required: 'N'
	      };
	      if (!this.validateName(property.Name)) {
	        return true;
	      }
	      if (!main_core.Type.isStringFilled(id)) {
	        var date = new Date();
	        id = this.correspondenceModeToIdName[this.mode] + date.getTime().toString();
	      }
	      if (this.oldProperty.Options) {
	        property.Options = this.oldProperty.Options;
	      }
	      var me = this;
	      bizproc_globals.Globals.Manager.Instance.upsertGlobalsAction(id, property, this.signedDocumentType, this.mode).then(function (response) {
	        if (response.data && response.data.error) {
	          ui_dialogs_messagebox.MessageBox.alert(response.data.error, function () {
	            main_core.Dom.removeClass(me.saveButtonNode, 'ui-btn-wait');
	            return true;
	          });
	        } else {
	          me.sliderDict.set(id, _objectSpread(_objectSpread({}, property), {}, {
	            VisibilityName: _this.visibilityNames[property.Visibility]
	          }));
	          me.slider.close();
	        }
	      });
	      return true;
	    }
	  }, {
	    key: "getValues",
	    value: function getValues(formElements) {
	      if (formElements['VALUE']) {
	        return formElements['VALUE'].value;
	      }
	      if (formElements['VALUE[]']) {
	        var radioNodeList = formElements['VALUE[]'];
	        var values = [];
	        if (main_core.Type.isElementNode(radioNodeList)) {
	          if (radioNodeList.tagName !== 'SELECT') {
	            return radioNodeList.value;
	          }
	          for (var i in Object.keys(radioNodeList.selectedOptions)) {
	            values.push(radioNodeList.selectedOptions[i].value);
	          }
	          return values;
	        }
	        for (var _i in radioNodeList) {
	          if (radioNodeList.hasOwnProperty(_i)) {
	            values.push(radioNodeList[_i].value);
	          }
	        }
	        return values;
	      }
	    }
	  }, {
	    key: "validateName",
	    value: function validateName(name) {
	      var me = this;
	      if (!name) {
	        ui_dialogs_messagebox.MessageBox.alert(BX.Loc.getMessage('BIZPROC_GLOBALFIELD_EDIT_TMP_EMPTY_NAME'), function () {
	          main_core.Dom.removeClass(me.saveButtonNode, 'ui-btn-wait');
	          return true;
	        });
	        return false;
	      }
	      return true;
	    }
	  }]);
	  return GlobalFieldEditComponent;
	}();
	namespace.GlobalFieldEditComponent = GlobalFieldEditComponent;

}((this.window = this.window || {}),BX,BX.UI.Dialogs,BX,BX.Bizproc));
//# sourceMappingURL=script.js.map
