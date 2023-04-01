this.BX = this.BX || {};
(function (exports,ui_designTokens,main_popup,ui_vue3,ui_switcher,ui_forms,main_core_events,main_core) {
	'use strict';

	var CheckboxListSections = {
	  props: ['sections'],
	  methods: {
	    handleClick: function handleClick(key) {
	      this.$emit('sectionToggled', key);
	    },
	    getSectionsItemClassName: function getSectionsItemClassName(sectionValue) {
	      return ['ui-checkbox-list__sections-item', {
	        '--checked': sectionValue
	      }];
	    }
	  },
	  template: "\n\t\t<div class=\"ui-checkbox-list__sections\">\n\t\t\t<div \n\t\t\t\tv-for=\"section in sections\"\n\t\t\t\t:key=\"section.key\"\n\t\t\t\t:title=\"section.title\"\n\t\t\t\t:class=\"getSectionsItemClassName(section.value)\"\n\t\t\t\t@click=\"handleClick(section.key)\"\n\t\t\t>\n\t\t\t\t<div class=\"ui-checkbox-list__check-box\"></div>\n\t\t\t\t{{ section.title }}\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	var CheckboxListCategory = {
	  props: ['columnCount', 'category', 'options'],
	  methods: {
	    handleCheckBox: function handleCheckBox(id) {
	      this.$emit('changeOption', id);
	    },
	    getOptionClassName: function getOptionClassName(optionValue) {
	      return ['ui-ctl', 'ui-ctl-checkbox', 'ui-checkbox-list__field-item_label', {
	        '--checked': optionValue
	      }];
	    }
	  },
	  template: "\n\t\t<div class=\"ui-checkbox-list__category\">\n\t\t\t<div class=\"ui-checkbox-list__categories-title\">\n\t\t\t\t{{ category.title }}\n\t\t\t</div>\n\t\t\t<div \n\t\t\t\tclass=\"ui-checkbox-list__options\"\n\t\t\t\t:style=\"{'-webkit-column-count': columnCount, \n\t\t\t\t\t\t '-moz-column-count': columnCount, \n\t\t\t\t\t\t 'column-count': columnCount,\n\t\t\t\t\t\t }\"\n\t\t\t>\n\t\t\t\t<div\n\t\t\t\t\tv-for=\"option in options\"\n\t\t\t\t\t:key=\"option.id\"\n\t\t\t\t>\n\t\t\t\t\t<label\n\t\t\t\t\t\t:title=\"option.title\"\n\t\t\t\t\t\t:class=\"getOptionClassName(option.value)\"\n\t\t\t\t\t>\n\t\t\t\t\t\t<input\n\t\t\t\t\t\t\ttype=\"checkbox\"\n\t\t\t\t\t\t\tclass=\"ui-ctl-element ui-checkbox-list__field-item_input\"\n\t\t\t\t\t\t\t:checked=\"option.value\"\n\t\t\t\t\t\t\t@click=\"handleCheckBox(option.id)\"\n\t\t\t\t\t\t>\n\t\t\t\t\t\t<div class=\"ui-ctl-label-text ui-checkbox-list__field-item_text\">{{ option.title }}</div>\n\t\t\t\t\t</label>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	var Content = {
	  components: {
	    CheckboxListSections: CheckboxListSections,
	    CheckboxListCategory: CheckboxListCategory
	  },
	  props: ['dialog', 'popup', 'columnCount', 'compactField', 'lang', 'sections', 'categories', 'options'],
	  data: function data() {
	    return {
	      dataSections: this.sections,
	      dataCategories: this.categories,
	      dataOptions: this.options,
	      dataCompactField: this.compactField,
	      search: '',
	      longContent: false,
	      scrollIsBottom: true,
	      scrollIsTop: false
	    };
	  },
	  methods: {
	    renderSwitcher: function renderSwitcher() {
	      var _this = this;

	      if (this.dataCompactField) {
	        var switcher = new BX.UI.Switcher({
	          node: this.$refs.switcher,
	          checked: this.dataCompactField.value,
	          size: 'small',
	          handlers: {
	            toggled: function toggled() {
	              return _this.handleSwitcherToggled();
	            }
	          }
	        });
	      }
	    },
	    handleSwitcherToggled: function handleSwitcherToggled() {
	      this.dataCompactField.value = !this.dataCompactField.value;
	    },
	    handleCheckBoxToggled: function handleCheckBoxToggled(id) {
	      var item = this.dataOptions.find(function (option) {
	        return option.id === id;
	      });

	      if (item) {
	        item.value = !item.value;
	      }
	    },
	    clearSearch: function clearSearch() {
	      this.search = '';
	    },
	    handleClearSearchButtonClick: function handleClearSearchButtonClick() {
	      this.$refs.searchInput.focus();
	      this.clearSearch();
	    },
	    handleSectionsToggled: function handleSectionsToggled(key) {
	      var section = this.dataSections.find(function (section) {
	        return section.key === key;
	      });

	      if (section) {
	        section.value = !section.value;
	      }
	    },
	    getOptionsByCategory: function getOptionsByCategory(category) {
	      return this.optionsByTitle.filter(function (item) {
	        return item.categoryKey === category;
	      });
	    },
	    getCheckedOptionsId: function getCheckedOptionsId() {
	      return this.dataOptions.filter(function (option) {
	        return option.value === true;
	      }).map(function (option) {
	        return option.id;
	      });
	    },
	    checkLongContent: function checkLongContent() {
	      if (this.$refs.container) {
	        this.longContent = this.$refs.container.clientHeight < this.$refs.container.scrollHeight;
	      } else {
	        this.longContent = false;
	      }
	    },
	    getBottomIndent: function getBottomIndent() {
	      this.scrollIsBottom = !(this.$refs.container.scrollTop + this.$refs.container.clientHeight >= this.$refs.container.scrollHeight - 10);
	    },
	    getTopIndent: function getTopIndent() {
	      this.scrollIsTop = this.$refs.container.scrollTop;
	    },
	    handleScroll: function handleScroll() {
	      this.getBottomIndent();
	      this.getTopIndent();
	    },
	    handleSearchEscKeyUp: function handleSearchEscKeyUp() {
	      this.$refs.container.focus();
	      this.clearSearch();
	    },
	    defaultSettings: function defaultSettings() {
	      this.clearSearch();

	      if (this.dataCompactField && this.dataCompactField.value !== this.dataCompactField.defaultValue) {
	        this.$refs.switcher.click();
	      }

	      this.dataOptions.forEach(function (option) {
	        return option.value = option.defaultValue;
	      });

	      if (Array.isArray(this.dataSections)) {
	        this.dataSections.forEach(function (sections) {
	          return sections.value = true;
	        });
	      }
	    },
	    selectAll: function selectAll() {
	      var _this2 = this;

	      this.categoryBySection.forEach(function (category) {
	        _this2.getOptionsByCategory(category.key).forEach(function (option) {
	          return option.value = true;
	        });
	      });
	    },
	    deselectAll: function deselectAll() {
	      var _this3 = this;

	      this.categoryBySection.forEach(function (category) {
	        _this3.getOptionsByCategory(category.key).forEach(function (option) {
	          return option.value = false;
	        });
	      });
	    },
	    cancel: function cancel() {
	      this.popup.destroy();
	    },
	    apply: function apply() {
	      main_core_events.EventEmitter.emit(this.dialog, 'onApply', {
	        switcher: this.dataCompactField,
	        fields: this.getCheckedOptionsId()
	      });
	      this.popup.destroy();
	    }
	  },
	  watch: {
	    search: function search() {
	      var _this4 = this;

	      this.$nextTick(function () {
	        _this4.checkLongContent();
	      });
	    },
	    categoryBySection: function categoryBySection() {
	      var _this5 = this;

	      this.$nextTick(function () {
	        _this5.checkLongContent();
	      });
	    }
	  },
	  computed: {
	    visibleOptions: function visibleOptions() {
	      var _this6 = this;

	      if (!Array.isArray(this.dataSections) || !this.dataSections.length) {
	        return this.optionsByTitle;
	      }

	      return this.optionsByTitle.filter(function (option) {
	        var category = _this6.dataCategories.find(function (category) {
	          return category.key === option.categoryKey;
	        });

	        var section = _this6.dataSections.find(function (section) {
	          return section.key === category.sectionKey;
	        });

	        return section === null || section === void 0 ? void 0 : section.value;
	      });
	    },
	    isEmptyContent: function isEmptyContent() {
	      return this.visibleOptions.length > 0;
	    },
	    isSearchDisabled: function isSearchDisabled() {
	      if (this.dataSections) {
	        return !this.dataSections.some(function (section) {
	          return section.value;
	        });
	      }

	      return false;
	    },
	    optionsByTitle: function optionsByTitle() {
	      var _this7 = this;

	      return this.dataOptions.filter(function (item) {
	        return item.title.toLowerCase().indexOf(_this7.search.toLowerCase()) !== -1;
	      });
	    },
	    categoryBySection: function categoryBySection() {
	      var _this8 = this;

	      if (!Array.isArray(this.dataSections) || !main_core.Type.isArrayFilled(this.dataSections)) {
	        return this.dataCategories;
	      }

	      return this.dataCategories.filter(function (category) {
	        var section = _this8.dataSections.find(function (section) {
	          return category.sectionKey === section.key;
	        });

	        return section === null || section === void 0 ? void 0 : section.value;
	      });
	    },
	    wrapperClassName: function wrapperClassName() {
	      return ['ui-checkbox-list__wrapper', {
	        '--long': this.longContent
	      }, {
	        '--bottom': this.scrollIsBottom
	      }, {
	        '--top': this.scrollIsTop
	      }];
	    },
	    searchClassName: function searchClassName() {
	      return ['ui-checkbox-list__search', {
	        '--disabled': this.isSearchDisabled
	      }];
	    },
	    SwitcherText: function SwitcherText() {
	      return main_core.Type.isStringFilled(this.lang.switcher) ? this.lang.switcher : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_SWITCHER');
	    },
	    placeholderText: function placeholderText() {
	      return main_core.Type.isStringFilled(this.lang.placeholder) ? this.lang.placeholder : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_PLACEHOLDER');
	    },
	    defaultSettingsBtnText: function defaultSettingsBtnText() {
	      return main_core.Type.isStringFilled(this.lang.defaultBtn) ? this.lang.defaultBtn : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS');
	    },
	    applyBtnText: function applyBtnText() {
	      return main_core.Type.isStringFilled(this.lang.acceptBtn) ? this.lang.acceptBtn : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_ACCEPT_BUTTON');
	    },
	    cancelBtnText: function cancelBtnText() {
	      return main_core.Type.isStringFilled(this.lang.cancelBtn) ? this.lang.cancelBtn : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_CANCEL_BUTTON');
	    },
	    selectAllBtnText: function selectAllBtnText() {
	      return main_core.Type.isStringFilled(this.lang.selectAllBtn) ? this.lang.selectAllBtn : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SELECT_ALL');
	    },
	    deselectAllBtnText: function deselectAllBtnText() {
	      return main_core.Type.isStringFilled(this.lang.deselectAllBtn) ? this.lang.deselectAllBtn : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_DESELECT_ALL');
	    },
	    emptyStateTitleText: function emptyStateTitleText() {
	      return main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_EMPTY_STATE_TITLE');
	    },
	    emptyStateDescriptionText: function emptyStateDescriptionText() {
	      return main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_EMPTY_STATE_DESCRIPTION');
	    }
	  },
	  mounted: function mounted() {
	    var _this9 = this;

	    this.renderSwitcher();
	    this.$nextTick(function () {
	      _this9.checkLongContent();
	    });
	  },
	  template: "\n\t\t<div class=\"ui-checkbox-list\">\n\t\t<div class=\"ui-checkbox-list__header\">\n\n\t\t\t<checkbox-list-sections\n\t\t\t\tv-if=\"sections\"\n\t\t\t\t:sections=\"dataSections\"\n\t\t\t\t@sectionToggled=\"handleSectionsToggled\"\n\t\t\t/>\n\n\t\t\t<div class=\"ui-checkbox-list__header_options\">\n\t\t\t\t<div\n\t\t\t\t\tv-if=\"compactField\"\n\t\t\t\t\tclass=\"ui-checkbox-list__switcher\"\n\t\t\t\t>\n\t\t\t\t\t<div class=\"ui-checkbox-list__switcher-text\">\n\t\t\t\t\t\t{{ SwitcherText }}\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"switcher\" ref=\"switcher\"></div>\n\t\t\t\t</div>\n\t\t\t\t<div\n\t\t\t\t\t:class=\"searchClassName\"\n\t\t\t\t>\n\t\t\t\t\t<div class=\"ui-checkbox-list__search-wrapper\">\n\t\t\t\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-before-icon ui-ctl-after-icon ui-ctl-w100\">\n\n\t\t\t\t\t\t\t<div class=\"ui-ctl-before ui-ctl-icon-search\"></div>\n\t\t\t\t\t\t\t<button\n\t\t\t\t\t\t\t\t@click=\"handleClearSearchButtonClick\"\n\t\t\t\t\t\t\t\tclass=\"ui-ctl-after ui-ctl-icon-clear ui-checkbox-list__search-clear\"\n\t\t\t\t\t\t\t></button>\n\t\t\t\t\t\t\t<input\n\t\t\t\t\t\t\t\t:placeholder=\"placeholderText\"\n\t\t\t\t\t\t\t\ttype=\"text\"\n\t\t\t\t\t\t\t\tclass=\"ui-ctl-element\"\n\t\t\t\t\t\t\t\tv-model=\"search\"\n\t\t\t\t\t\t\t\t@keyup.esc.stop=\"handleSearchEscKeyUp\"\n\t\t\t\t\t\t\t\tref=\"searchInput\"\n\t\t\t\t\t\t\t>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\n\t\t<div\n\t\t\tref=\"wrapper\"\n\t\t\t:class=\"wrapperClassName\"\n\t\t>\n\t\t\t<div\n\t\t\t\tref=\"container\"\n\t\t\t\tclass=\"ui-checkbox-list__container\"\n\t\t\t\t@scroll=\"handleScroll\"\n\t\t\t\ttabindex=\"0\"\n\t\t\t\tv-if=\"isEmptyContent\"\n\t\t\t>\n\t\t\t\t<checkbox-list-category\n\t\t\t\t\tv-for=\"category in categoryBySection\"\n\t\t\t\t\t:key=\"category.key\"\n\t\t\t\t\t:category=\"category\"\n\t\t\t\t\t:columnCount=\"columnCount\"\n\t\t\t\t\t:options=\"getOptionsByCategory(category.key)\"\n\t\t\t\t\t@changeOption=\"handleCheckBoxToggled\"\n\t\t\t\t/>\n\t\t\t</div>\n\t\t\t<div\n\t\t\t\tv-else\n\t\t\t\tclass=\"ui-checkbox-list__empty\"\n\t\t\t>\n\t\t\t\t<img\n\t\t\t\t\tsrc=\"/bitrix/js/ui/dialogs/checkbox-list/images/ui-checkbox-list-empty.svg\"\n\t\t\t\t\t:alt=\"emptyStateTitleText\">\n\t\t\t\t<div class=\"ui-checkbox-list__empty-title\">\n\t\t\t\t\t{{ emptyStateTitleText }}\n\t\t\t\t</div>\n\t\t\t\t<div class=\"ui-checkbox-list__empty-description\">\n\t\t\t\t\t{{ emptyStateDescriptionText }}\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\n\t\t<div class=\"ui-checkbox-list__footer\">\n\t\t\t<div class=\"ui-checkbox-list__footer-block\">\n\t\t\t\t<div\n\t\t\t\t\tclass=\"ui-checkbox-list__footer-link --default\"\n\t\t\t\t\t@click=\"defaultSettings()\"\n\t\t\t\t>\n\t\t\t\t\t{{ defaultSettingsBtnText }}\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<div class=\"ui-checkbox-list__footer-block\">\n\t\t\t\t<button\n\t\t\t\t\t@click=\"apply()\"\n\t\t\t\t\tclass=\"ui-btn ui-btn-success\">\n\t\t\t\t\t{{ applyBtnText }}\n\t\t\t\t</button>\n\n\t\t\t\t<button\n\t\t\t\t\t@click=\"cancel()\"\n\t\t\t\t\tclass=\"ui-btn ui-btn-link\"\n\t\t\t\t>\n\t\t\t\t\t{{ cancelBtnText }}\n\t\t\t\t</button>\n\t\t\t</div>\n\t\t\t<div class=\"ui-checkbox-list__footer-block --right\">\n\t\t\t\t<div\n\t\t\t\t\t@click=\"selectAll()\"\n\t\t\t\t\tclass=\"ui-checkbox-list__footer-link\"\n\t\t\t\t>\n\t\t\t\t\t{{ selectAllBtnText }}\n\t\t\t\t</div>\n\t\t\t\t<div\n\t\t\t\t\t@click=\"deselectAll()\"\n\t\t\t\t\tclass=\"ui-checkbox-list__footer-link\"\n\t\t\t\t>\n\t\t\t\t\t{{ deselectAllBtnText }}\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\t\t</div>\n\t"
	};

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	var CheckboxList = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(CheckboxList, _EventEmitter);

	  function CheckboxList(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, CheckboxList);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CheckboxList).call(this));

	    _this.setEventNamespace('BX.UI.Dialogs.CheckboxList');

	    _this.subscribeFromOptions(options.events);

	    if (!main_core.Type.isArrayFilled(options.categories)) {
	      throw new Error('CheckboxList: "categories" parameter is required.');
	    }

	    _this.categories = options.categories;

	    if (!main_core.Type.isArrayFilled(options.options)) {
	      throw new Error('CheckboxList: "options" parameter is required.');
	    }

	    _this.options = options.options;
	    _this.compactField = main_core.Type.isPlainObject(options.compactField) ? options.compactField : null;
	    _this.sections = main_core.Type.isArray(options.sections) ? options.sections : null;
	    _this.lang = main_core.Type.isPlainObject(options.lang) ? options.lang : {};
	    _this.popup = null;
	    _this.columnCount = main_core.Type.isNumber(options.columnCount) ? options.columnCount : 4;
	    _this.popupOptions = main_core.Type.isPlainObject(options.popupOptions) ? options.popupOptions : {};
	    return _this;
	  }

	  babelHelpers.createClass(CheckboxList, [{
	    key: "getPopup",
	    value: function getPopup() {
	      var container = main_core.Dom.create('div');
	      main_core.Dom.addClass(container, 'ui-checkbox-list__app-container');

	      if (!this.popup) {
	        this.popup = new main_popup.Popup(_objectSpread({
	          className: 'ui-checkbox-list-popup',
	          width: 997,
	          overlay: true,
	          autoHide: true,
	          minHeight: 422,
	          borderRadius: 20,
	          contentPadding: 0,
	          contentBackground: 'transparent',
	          animation: 'fading-slide',
	          titleBar: this.lang.title,
	          content: container,
	          closeIcon: true,
	          closeByEsc: true
	        }, this.popupOptions));
	        ui_vue3.BitrixVue.createApp(Content, {
	          compactField: this.compactField,
	          lang: this.lang,
	          sections: this.sections,
	          categories: this.categories,
	          options: this.options,
	          popup: this.popup,
	          columnCount: this.columnCount,
	          dialog: this
	        }).mount(container);
	      }

	      return this.popup;
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      this.getPopup().show();
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      this.getPopup().hide();
	    }
	  }]);
	  return CheckboxList;
	}(main_core_events.EventEmitter);

	exports.CheckboxList = CheckboxList;

}((this.BX.UI = this.BX.UI || {}),BX,BX.Main,BX.Vue3,BX,BX,BX.Event,BX));
//# sourceMappingURL=bundle.js.map
