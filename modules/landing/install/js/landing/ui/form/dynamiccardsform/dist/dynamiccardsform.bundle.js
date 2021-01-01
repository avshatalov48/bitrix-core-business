this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,landing_ui_form_baseform,landing_env,landing_ui_field_sourcefield,landing_loc,landing_main) {
	'use strict';

	/**
	 * @memberOf BX.Landing.UI.Form
	 */

	var DynamicCardsForm = /*#__PURE__*/function (_BaseForm) {
	  babelHelpers.inherits(DynamicCardsForm, _BaseForm);

	  function DynamicCardsForm(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, DynamicCardsForm);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DynamicCardsForm).call(this, options));
	    _this.type = options.type;
	    _this.code = options.code;
	    _this.presets = options.presets;
	    _this.sync = options.sync;
	    _this.forms = options.forms;
	    _this.id = "".concat(_this.code.replace('.', ''), "-").concat(BX.Landing.Utils.random());
	    _this.onSourceChangeHandler = options.onSourceChange;
	    _this.dynamicParams = options.dynamicParams;
	    _this.settingFieldsSelectors = ['source', 'pagesCount', 'detailPage', 'useSef'];
	    _this.sourceField = _this.createSourceField();
	    _this.pagesField = _this.createPagesField();

	    _this.addField(_this.sourceField);

	    _this.addField(_this.pagesField);

	    _this.detailPageGroup = new BX.Landing.UI.Card.DynamicFieldsGroup({
	      items: [_this.createLinkField()]
	    });

	    _this.addCard(_this.detailPageGroup);

	    return _this;
	  }

	  babelHelpers.createClass(DynamicCardsForm, [{
	    key: "createSourceField",
	    value: function createSourceField() {
	      var _this2 = this;

	      var sourceItems = DynamicCardsForm.getSourceItems();

	      var _sourceItems = babelHelpers.slicedToArray(sourceItems, 1),
	          firstItem = _sourceItems[0];

	      var value = {
	        source: firstItem.value,
	        filter: firstItem.filter
	      };

	      if (main_core.Type.isPlainObject(this.dynamicParams) && main_core.Type.isPlainObject(this.dynamicParams.settings) && main_core.Type.isPlainObject(this.dynamicParams.settings.source)) {
	        value.source = this.dynamicParams.settings.source.source;
	        value.filter = this.dynamicParams.settings.source.filter;
	        value.sort = this.dynamicParams.settings.source.sort;
	      }

	      return new landing_ui_field_sourcefield.SourceField({
	        selector: 'source',
	        title: landing_loc.Loc.getMessage('LANDING_CARDS__SOURCE_FIELD_TITLE'),
	        items: sourceItems,
	        value: value,
	        onValueChange: function onValueChange(field) {
	          var fieldValue = field.getValue();
	          var source = DynamicCardsForm.getSources().find(function (item) {
	            return item.id === fieldValue.source;
	          });
	          setTimeout(function () {
	            if (!_this2.sourceField.isDetailPageAllowed()) {
	              main_core.Dom.style(_this2.detailPageGroup.layout, 'display', 'none');
	            } else {
	              main_core.Dom.style(_this2.detailPageGroup.layout, 'display', null);
	            }

	            _this2.onSourceChangeHandler(source);
	          }, 0);
	        }
	      });
	    }
	  }, {
	    key: "createPagesField",
	    value: function createPagesField() {
	      return new BX.Landing.UI.Field.Pages({
	        selector: 'pagesCount',
	        title: landing_loc.Loc.getMessage('LANDING_CARDS__PAGES_FIELD_TITLE'),
	        value: this.dynamicParams.settings.pagesCount
	      });
	    }
	  }, {
	    key: "createLinkField",
	    value: function createLinkField() {
	      var content = {
	        text: '',
	        href: ''
	      };

	      if (main_core.Type.isPlainObject(this.dynamicParams) && main_core.Type.isPlainObject(this.dynamicParams.settings) && main_core.Type.isPlainObject(this.dynamicParams.settings.detailPage)) {
	        content = this.dynamicParams.settings.detailPage;
	      }

	      return new BX.Landing.UI.Field.Link({
	        selector: 'detailPage',
	        title: landing_loc.Loc.getMessage('LANDING_CARDS__DETAIL_PAGE_FIELD_TITLE'),
	        textOnly: true,
	        disableCustomURL: true,
	        disableBlocks: true,
	        disallowType: true,
	        allowedTypes: [BX.Landing.UI.Field.LinkURL.TYPE_PAGE],
	        detailPageMode: true,
	        sourceField: this.fields.find(function (field) {
	          return field.selector === 'source';
	        }),
	        options: {
	          siteId: landing_env.Env.getInstance().getOptions().site_id,
	          landingId: landing_main.Main.getInstance().id,
	          filter: {
	            '=TYPE': landing_env.Env.getInstance().getOptions().params.type
	          }
	        },
	        content: content
	      });
	    }
	  }, {
	    key: "serialize",
	    value: function serialize() {
	      var _this3 = this;

	      var isDetailPageAllowed = this.sourceField.isDetailPageAllowed();
	      return this.fields.reduce(function (acc, field) {
	        if (field.selector === 'detailPage' && !isDetailPageAllowed) {
	          return acc;
	        }

	        var value = field.getValue();

	        if (_this3.settingFieldsSelectors.includes(field.selector)) {
	          if (field.selector === 'source') {
	            acc.source = value.source;
	          }

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
	        } else if (DynamicCardsForm.isReference(value) || main_core.Type.isPlainObject(value) && main_core.Type.isString(value.id)) {
	          if (DynamicCardsForm.isReference(value)) {
	            acc.references[field.selector] = {
	              id: value
	            };
	          } else {
	            acc.references[field.selector] = value;
	          }
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
	    key: "getSourceItems",
	    value: function getSourceItems() {
	      return DynamicCardsForm.getSources().map(function (item) {
	        return {
	          name: item.name,
	          value: item.id,
	          url: item.url ? item.url.filter : '',
	          filter: item.filter,
	          sort: {
	            items: item.sort.map(function (sortItem) {
	              return {
	                name: sortItem.name,
	                value: sortItem.id
	              };
	            })
	          },
	          settings: item.settings
	        };
	      });
	    }
	  }, {
	    key: "isReference",
	    value: function isReference(value) {
	      var sources = DynamicCardsForm.getSources();

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
	  return DynamicCardsForm;
	}(landing_ui_form_baseform.BaseForm);

	exports.DynamicCardsForm = DynamicCardsForm;

}((this.BX.Landing.UI.Form = this.BX.Landing.UI.Form || {}),BX,BX.Landing.UI.Form,BX.Landing,BX.Landing.UI.Field,BX.Landing,BX.Landing));
//# sourceMappingURL=dynamiccardsform.bundle.js.map
