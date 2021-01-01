this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,landing_ui_form_baseform,landing_env,landing_loc) {
	'use strict';

	/**
	 * @memberOf BX.Landing.UI.Form
	 */

	var DynamicBlockForm = /*#__PURE__*/function (_BaseForm) {
	  babelHelpers.inherits(DynamicBlockForm, _BaseForm);

	  function DynamicBlockForm(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, DynamicBlockForm);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DynamicBlockForm).call(this, options));
	    _this.type = options.type;
	    _this.forms = options.forms;
	    _this.code = options.code;
	    _this.onSourceChangeHandler = options.onSourceChange;
	    _this.dynamicParams = options.dynamicParams;
	    _this.settingFieldsSelectors = ['source'];

	    _this.addField(_this.createSourceField());

	    return _this;
	  }

	  babelHelpers.createClass(DynamicBlockForm, [{
	    key: "createSourceField",
	    value: function createSourceField() {
	      var _this2 = this;

	      var value = '';

	      if (main_core.Type.isPlainObject(this.dynamicParams) && main_core.Type.isPlainObject(this.dynamicParams.wrapper) && main_core.Type.isPlainObject(this.dynamicParams.wrapper.settings) && main_core.Type.isString(this.dynamicParams.wrapper.settings.source)) {
	        value = this.dynamicParams.wrapper.settings.source;
	      }

	      var source = DynamicBlockForm.getSourceById(value);

	      if (!source) {
	        var _DynamicBlockForm$get = DynamicBlockForm.getSources();

	        var _DynamicBlockForm$get2 = babelHelpers.slicedToArray(_DynamicBlockForm$get, 1);

	        source = _DynamicBlockForm$get2[0];
	      }

	      setTimeout(function () {
	        _this2.onSourceChangeHandler(source);
	      }, 0);
	      return new BX.Landing.UI.Field.Dropdown({
	        title: landing_loc.Loc.getMessage('LANDING_CARDS__SOURCE_FIELD_TITLE'),
	        selector: 'source',
	        content: value,
	        items: DynamicBlockForm.getSourceFieldItems(),
	        onValueChange: function onValueChange(field) {
	          _this2.onSourceChangeHandler(DynamicBlockForm.getSourceById(field.getValue()));
	        }
	      });
	    }
	  }, {
	    key: "serialize",
	    value: function serialize() {
	      return this.fields.reduce(function (acc, field) {
	        var value = field.getValue();

	        if (field.selector === 'source') {
	          acc.source = value;
	          acc.settings[field.selector] = value;
	        } else if (value === '@hide' || main_core.Type.isPlainObject(value) && value.id === '@hide') {
	          acc.references[field.selector] = '@hide';

	          if (main_core.Dom.hasClass(field.layout, 'landing-ui-field-dynamic-dropdown')) {
	            acc.stubs[field.selector] = '';
	          } else if (main_core.Dom.hasClass(field.layout, 'landing-ui-field-dynamic-image')) {
	            acc.stubs[field.selector] = {
	              id: -1,
	              src: 'data:image/gif;base64,R0lGODlhAQABAIAAAP',
	              alt: ''
	            };
	          }
	        } else if (DynamicBlockForm.isReference(value)) {
	          acc.references[field.selector] = {
	            id: value
	          };
	        } else if (main_core.Type.isPlainObject(value) && main_core.Type.isString(value.id)) {
	          acc.references[field.selector] = value;
	        } else {
	          acc.stubs[field.selector] = value;
	        }

	        return acc;
	      }, {
	        settings: {},
	        references: {},
	        stubs: {}
	      });
	    }
	  }], [{
	    key: "getSources",
	    value: function getSources() {
	      return landing_env.Env.getInstance().getOptions().sources;
	    }
	  }, {
	    key: "getSourceById",
	    value: function getSourceById(id) {
	      return DynamicBlockForm.getSources().find(function (source) {
	        return String(source.id) === String(id);
	      });
	    }
	  }, {
	    key: "getSourceFieldItems",
	    value: function getSourceFieldItems() {
	      return DynamicBlockForm.getSources().map(function (source) {
	        return {
	          name: source.name,
	          value: source.id
	        };
	      });
	    }
	  }, {
	    key: "isReference",
	    value: function isReference(value) {
	      var sources = DynamicBlockForm.getSources();

	      if (main_core.Type.isArray(sources)) {
	        return sources.some(function (source) {
	          if (main_core.Type.isArray(source.references)) {
	            return source.references.some(function (reference) {
	              return reference.id === value;
	            });
	          }

	          return false;
	        });
	      }

	      return false;
	    }
	  }]);
	  return DynamicBlockForm;
	}(landing_ui_form_baseform.BaseForm);

	exports.DynamicBlockForm = DynamicBlockForm;

}((this.BX.Landing.UI.Form = this.BX.Landing.UI.Form || {}),BX,BX.Landing.UI.Form,BX.Landing,BX.Landing));
//# sourceMappingURL=dynamicblockform.bundle.js.map
