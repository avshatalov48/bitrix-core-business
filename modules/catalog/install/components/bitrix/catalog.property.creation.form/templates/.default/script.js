(function (exports,main_core) {
	'use strict';

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<p class=\"ui-entity-editor-field-error-text\">", "</p>\n\t\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var PropertyCreationForm =
	/*#__PURE__*/
	function (_BX$Catalog$IblockFie) {
	  babelHelpers.inherits(PropertyCreationForm, _BX$Catalog$IblockFie);

	  function PropertyCreationForm() {
	    babelHelpers.classCallCheck(this, PropertyCreationForm);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PropertyCreationForm).apply(this, arguments));
	  }

	  babelHelpers.createClass(PropertyCreationForm, [{
	    key: "initialize",
	    value: function initialize(id) {
	      var settings = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      babelHelpers.get(babelHelpers.getPrototypeOf(PropertyCreationForm.prototype), "initialize", this).call(this, id, settings);
	      BX.addCustomEvent(this, "onSave", BX.delegate(this.onFormSave, this));
	      BX.addCustomEvent(this, "onCancel", BX.delegate(this.onFormCancel, this));
	      this.componentName = settings.componentName || '';
	      this.signedParameters = settings.signedParameters || '';
	    }
	  }, {
	    key: "getInputTitle",
	    value: function getInputTitle() {
	      return !this.isCreationMode() ? this._field.getTitle() : '';
	    }
	  }, {
	    key: "isCreationMode",
	    value: function isCreationMode() {
	      return this._field === null;
	    }
	  }, {
	    key: "onFormSave",
	    value: function onFormSave(sender, params) {
	      if (this._isLocked) {
	        return;
	      }

	      this._isLocked = true;
	      BX.addClass(this._saveButton, "ui-btn-wait");
	      var fields = this.formatConfiguratorFields(params);

	      if (this.isCreationMode()) {
	        this.addProperty(fields);
	      } else {
	        this.updateProperty(fields);
	      }
	    }
	  }, {
	    key: "addProperty",
	    value: function addProperty(fields) {
	      var _this = this;

	      main_core.ajax.runComponentAction(this.componentName, 'addProperty', {
	        mode: 'class',
	        signedParameters: this.signedParameters,
	        data: fields
	      }).then(function (response) {
	        fields.CODE = response.data.PROPERTY_GRID_CODE;
	        BX.SidePanel.Instance.postMessage(window, 'PropertyCreationForm:onAdd', {
	          fields: fields
	        });

	        _this.onFormCancel();
	      }).catch(this.onError.bind(this));
	    }
	  }, {
	    key: "updateProperty",
	    value: function updateProperty(fields) {
	      var _this2 = this;

	      main_core.ajax.runComponentAction(this.componentName, 'updateProperty', {
	        mode: 'class',
	        signedParameters: this.signedParameters,
	        data: fields
	      }).then(function (response) {
	        BX.SidePanel.Instance.postMessage(window, 'PropertyCreationForm:onModify', {
	          fields: fields
	        });

	        _this2.onFormCancel();
	      }).catch(this.onError.bind(this));
	    }
	  }, {
	    key: "onError",
	    value: function onError(response) {
	      var _this3 = this;

	      main_core.Dom.removeClass(this._saveButton, "ui-btn-wait");
	      this._isLocked = false;

	      if (this._errorContainer) {
	        main_core.Dom.clean(this._errorContainer);

	        if (main_core.Type.isArray(response.errors)) {
	          response.errors.forEach(function (error) {
	            _this3.showError(error);
	          });
	        }
	      }
	    }
	  }, {
	    key: "getFormFieldName",
	    value: function getFormFieldName(name) {
	      return 'fields[' + name + ']';
	    }
	  }, {
	    key: "showError",
	    value: function showError(error) {
	      this._errorContainer.append(main_core.Tag.render(_templateObject(), error.message));
	    }
	  }, {
	    key: "formatConfiguratorFields",
	    value: function formatConfiguratorFields(fields) {
	      var _this4 = this;

	      var form = new FormData();
	      var formatted = {
	        NAME: fields.label,
	        MULTIPLE: fields.multiple ? 'Y' : 'N',
	        IS_REQUIRED: fields.mandatory ? 'Y' : 'N',
	        PROPERTY_TYPE: 'S'
	      };

	      if (fields.field) {
	        formatted.ID = fields.field.getId();
	      }

	      switch (fields.typeId) {
	        case 'directory':
	          formatted.DIRECTORY_NAME = fields.directoryName;
	          formatted.USER_TYPE = 'directory';
	          fields.enumeration = fields.enumeration || [];
	          fields.enumeration.forEach(function (enumItem, key) {
	            form.append(_this4.getFormFieldName('VALUES][' + key + '][SORT'), enumItem.SORT);
	            form.append(_this4.getFormFieldName('VALUES][' + key + '][VALUE'), enumItem.VALUE.value);

	            if (!_this4.isCreationMode()) {
	              form.append(_this4.getFormFieldName('VALUES][' + key + '][XML_ID'), enumItem.XML_ID);
	              form.append(_this4.getFormFieldName('VALUES][' + key + '][FILE_ID'), enumItem.FILE_ID);
	            }

	            form.append('FILES[' + enumItem.SORT + ']', enumItem.VALUE.file);
	          });
	          break;

	        case 'list':
	          formatted.PROPERTY_TYPE = 'L';
	          fields.enumeration = fields.enumeration || [];
	          fields.enumeration.forEach(function (enumItem, key) {
	            form.append(_this4.getFormFieldName('VALUES][' + key + '][SORT'), enumItem.SORT);
	            form.append(_this4.getFormFieldName('VALUES][' + key + '][VALUE'), enumItem.VALUE);
	            form.append(_this4.getFormFieldName('VALUES][' + key + '][ID'), enumItem.ID);
	          });
	          break;

	        case 'boolean':
	          formatted.PROPERTY_TYPE = 'L';
	          formatted.VALUES = ['Y'];
	          formatted.LIST_TYPE = 'C';
	          break;
	      }

	      for (var _i = 0, _Object$entries = Object.entries(formatted); _i < _Object$entries.length; _i++) {
	        var _Object$entries$_i = babelHelpers.slicedToArray(_Object$entries[_i], 2),
	            key = _Object$entries$_i[0],
	            item = _Object$entries$_i[1];

	        form.append(this.getFormFieldName(key), item);
	      }

	      return form;
	    }
	  }, {
	    key: "onFormCancel",
	    value: function onFormCancel() {
	      BX.SidePanel.Instance.close();
	    }
	  }]);
	  return PropertyCreationForm;
	}(BX.Catalog.IblockFieldConfigurator);

	main_core.Reflection.namespace('BX.Catalog').PropertyCreationForm = PropertyCreationForm;

}((this.window = this.window || {}),BX));
//# sourceMappingURL=script.js.map
