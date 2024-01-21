/* eslint-disable */
this.BX = this.BX || {};
(function (exports,ui_designTokens,main_popup,ui_vue3,ui_switcher,ui_forms,main_core_events,main_core) {
	'use strict';

	const CheckboxListSections = {
	  props: ['sections'],
	  methods: {
	    handleClick(key) {
	      this.$emit('sectionToggled', key);
	    },
	    getSectionsItemClassName(sectionValue) {
	      return ['ui-checkbox-list__sections-item', {
	        '--checked': sectionValue
	      }];
	    }
	  },
	  template: `
		<div class="ui-checkbox-list__sections">
			<div 
				v-for="section in sections"
				:key="section.key"
				:title="section.title"
				:class="getSectionsItemClassName(section.value)"
				@click="handleClick(section.key)"
			>
				<div class="ui-checkbox-list__check-box"></div>
				{{ section.title }}
			</div>
		</div>
	`
	};

	const CheckboxListCategory = {
	  props: ['columnCount', 'category', 'options'],
	  methods: {
	    handleCheckBox(id) {
	      this.$emit('changeOption', id);
	    },
	    getOptionClassName(optionValue) {
	      return ['ui-ctl', 'ui-ctl-checkbox', 'ui-checkbox-list__field-item_label', {
	        '--checked': optionValue
	      }];
	    }
	  },
	  template: `
		<div class="ui-checkbox-list__category">
			<div class="ui-checkbox-list__categories-title">
				{{ category.title }}
			</div>
			<div 
				class="ui-checkbox-list__options"
				:style="{'-webkit-column-count': columnCount, 
						 '-moz-column-count': columnCount, 
						 'column-count': columnCount,
						 }"
			>
				<div
					v-for="option in options"
					:key="option.id"
				>
					<label
						:title="option.title"
						:class="getOptionClassName(option.value)"
					>
						<input
							type="checkbox"
							class="ui-ctl-element ui-checkbox-list__field-item_input"
							:checked="option.value"
							@click="handleCheckBox(option.id)"
						>
						<div class="ui-ctl-label-text ui-checkbox-list__field-item_text">{{ option.title }}</div>
					</label>
				</div>
			</div>
		</div>
	`
	};

	const Content = {
	  components: {
	    CheckboxListSections,
	    CheckboxListCategory
	  },
	  props: ['dialog', 'popup', 'columnCount', 'compactField', 'lang', 'sections', 'categories', 'options'],
	  data() {
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
	    renderSwitcher() {
	      if (this.dataCompactField) {
	        const switcher = new BX.UI.Switcher({
	          node: this.$refs.switcher,
	          checked: this.dataCompactField.value,
	          size: 'small',
	          handlers: {
	            toggled: () => this.handleSwitcherToggled()
	          }
	        });
	      }
	    },
	    handleSwitcherToggled() {
	      this.dataCompactField.value = !this.dataCompactField.value;
	    },
	    handleCheckBoxToggled(id) {
	      const item = this.dataOptions.find(option => option.id === id);
	      if (item) {
	        item.value = !item.value;
	      }
	    },
	    clearSearch() {
	      this.search = '';
	    },
	    handleClearSearchButtonClick() {
	      this.$refs.searchInput.focus();
	      this.clearSearch();
	    },
	    handleSectionsToggled(key) {
	      const section = this.dataSections.find(section => section.key === key);
	      if (section) {
	        section.value = !section.value;
	      }
	    },
	    getOptionsByCategory(category) {
	      return this.optionsByTitle.filter(item => item.categoryKey === category);
	    },
	    getCheckedOptionsId() {
	      return this.dataOptions.filter(option => option.value === true).map(option => option.id);
	    },
	    checkLongContent() {
	      if (this.$refs.container) {
	        this.longContent = this.$refs.container.clientHeight < this.$refs.container.scrollHeight;
	      } else {
	        this.longContent = false;
	      }
	    },
	    getBottomIndent() {
	      this.scrollIsBottom = !(this.$refs.container.scrollTop + this.$refs.container.clientHeight >= this.$refs.container.scrollHeight - 10);
	    },
	    getTopIndent() {
	      this.scrollIsTop = this.$refs.container.scrollTop;
	    },
	    handleScroll() {
	      this.getBottomIndent();
	      this.getTopIndent();
	    },
	    handleSearchEscKeyUp() {
	      this.$refs.container.focus();
	      this.clearSearch();
	    },
	    defaultSettings() {
	      this.clearSearch();
	      if (this.dataCompactField && this.dataCompactField.value !== this.dataCompactField.defaultValue) {
	        this.$refs.switcher.click();
	      }
	      this.dataOptions.forEach(option => option.value = option.defaultValue);
	      if (Array.isArray(this.dataSections)) {
	        this.dataSections.forEach(sections => sections.value = true);
	      }
	    },
	    selectAll() {
	      this.categoryBySection.forEach(category => {
	        this.getOptionsByCategory(category.key).forEach(option => option.value = true);
	      });
	    },
	    deselectAll() {
	      this.categoryBySection.forEach(category => {
	        this.getOptionsByCategory(category.key).forEach(option => option.value = false);
	      });
	    },
	    cancel() {
	      this.popup.destroy();
	    },
	    apply() {
	      main_core_events.EventEmitter.emit(this.dialog, 'onApply', {
	        switcher: this.dataCompactField,
	        fields: this.getCheckedOptionsId()
	      });
	      this.popup.destroy();
	    }
	  },
	  watch: {
	    search() {
	      this.$nextTick(() => {
	        this.checkLongContent();
	      });
	    },
	    categoryBySection() {
	      this.$nextTick(() => {
	        this.checkLongContent();
	      });
	    }
	  },
	  computed: {
	    visibleOptions() {
	      if (!Array.isArray(this.dataSections) || !this.dataSections.length) {
	        return this.optionsByTitle;
	      }
	      return this.optionsByTitle.filter(option => {
	        const category = this.dataCategories.find(category => category.key === option.categoryKey);
	        const section = this.dataSections.find(section => section.key === category.sectionKey);
	        return section == null ? void 0 : section.value;
	      });
	    },
	    isEmptyContent() {
	      return this.visibleOptions.length > 0;
	    },
	    isSearchDisabled() {
	      if (this.dataSections && this.dataSections.length) {
	        return !this.dataSections.some(section => section.value);
	      }
	      return false;
	    },
	    isCheckedCheckboxes() {
	      return !this.dataOptions.filter(option => option.value === true).length;
	    },
	    optionsByTitle() {
	      return this.dataOptions.filter(item => item.title.toLowerCase().indexOf(this.search.toLowerCase()) !== -1);
	    },
	    categoryBySection() {
	      if (!Array.isArray(this.dataSections) || !main_core.Type.isArrayFilled(this.dataSections)) {
	        return this.dataCategories;
	      }
	      return this.dataCategories.filter(category => {
	        const section = this.dataSections.find(section => category.sectionKey === section.key);
	        return section == null ? void 0 : section.value;
	      });
	    },
	    wrapperClassName() {
	      return ['ui-checkbox-list__wrapper', {
	        '--long': this.longContent
	      }, {
	        '--bottom': this.scrollIsBottom
	      }, {
	        '--top': this.scrollIsTop
	      }];
	    },
	    searchClassName() {
	      return ['ui-checkbox-list__search', {
	        '--disabled': this.isSearchDisabled
	      }];
	    },
	    applyClassName() {
	      return ['ui-btn ui-btn-success', {
	        'ui-btn-disabled': this.isCheckedCheckboxes
	      }];
	    },
	    SwitcherText() {
	      return main_core.Type.isStringFilled(this.lang.switcher) ? this.lang.switcher : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_SWITCHER');
	    },
	    placeholderText() {
	      return main_core.Type.isStringFilled(this.lang.placeholder) ? this.lang.placeholder : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_PLACEHOLDER');
	    },
	    defaultSettingsBtnText() {
	      return main_core.Type.isStringFilled(this.lang.defaultBtn) ? this.lang.defaultBtn : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS');
	    },
	    applyBtnText() {
	      return main_core.Type.isStringFilled(this.lang.acceptBtn) ? this.lang.acceptBtn : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_ACCEPT_BUTTON');
	    },
	    cancelBtnText() {
	      return main_core.Type.isStringFilled(this.lang.cancelBtn) ? this.lang.cancelBtn : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_CANCEL_BUTTON');
	    },
	    selectAllBtnText() {
	      return main_core.Type.isStringFilled(this.lang.selectAllBtn) ? this.lang.selectAllBtn : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SELECT_ALL');
	    },
	    deselectAllBtnText() {
	      return main_core.Type.isStringFilled(this.lang.deselectAllBtn) ? this.lang.deselectAllBtn : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_DESELECT_ALL');
	    },
	    emptyStateTitleText() {
	      return main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_EMPTY_STATE_TITLE');
	    },
	    emptyStateDescriptionText() {
	      return main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_EMPTY_STATE_DESCRIPTION');
	    }
	  },
	  mounted() {
	    this.renderSwitcher();
	    this.$nextTick(() => {
	      this.checkLongContent();
	    });
	  },
	  template: `
		<div class="ui-checkbox-list">
		<div class="ui-checkbox-list__header">

			<checkbox-list-sections
				v-if="sections"
				:sections="dataSections"
				@sectionToggled="handleSectionsToggled"
			/>

			<div class="ui-checkbox-list__header_options">
				<div
					v-if="compactField"
					class="ui-checkbox-list__switcher"
				>
					<div class="ui-checkbox-list__switcher-text">
						{{ SwitcherText }}
					</div>
					<div class="switcher" ref="switcher"></div>
				</div>
				<div
					:class="searchClassName"
				>
					<div class="ui-checkbox-list__search-wrapper">
						<div class="ui-ctl ui-ctl-textbox ui-ctl-before-icon ui-ctl-after-icon ui-ctl-w100">

							<div class="ui-ctl-before ui-ctl-icon-search"></div>
							<button
								@click="handleClearSearchButtonClick"
								class="ui-ctl-after ui-ctl-icon-clear ui-checkbox-list__search-clear"
							></button>
							<input
								:placeholder="placeholderText"
								type="text"
								class="ui-ctl-element"
								v-model="search"
								@keyup.esc.stop="handleSearchEscKeyUp"
								ref="searchInput"
							>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div
			ref="wrapper"
			:class="wrapperClassName"
		>
			<div
				ref="container"
				class="ui-checkbox-list__container"
				@scroll="handleScroll"
				tabindex="0"
				v-if="isEmptyContent"
			>
				<checkbox-list-category
					v-for="category in categoryBySection"
					:key="category.key"
					:category="category"
					:columnCount="columnCount"
					:options="getOptionsByCategory(category.key)"
					@changeOption="handleCheckBoxToggled"
				/>
			</div>
			<div
				v-else
				class="ui-checkbox-list__empty"
			>
				<img
					src="/bitrix/js/ui/dialogs/checkbox-list/images/ui-checkbox-list-empty.svg"
					:alt="emptyStateTitleText">
				<div class="ui-checkbox-list__empty-title">
					{{ emptyStateTitleText }}
				</div>
				<div class="ui-checkbox-list__empty-description">
					{{ emptyStateDescriptionText }}
				</div>
			</div>
		</div>

		<div class="ui-checkbox-list__footer">
			<div class="ui-checkbox-list__footer-block">
				<div
					class="ui-checkbox-list__footer-link --default"
					@click="defaultSettings()"
				>
					{{ defaultSettingsBtnText }}
				</div>
			</div>
			<div class="ui-checkbox-list__footer-block">
				<button
					@click="apply()"
					:class="applyClassName"
				>
					{{ applyBtnText }}
				</button>
				<button
					@click="cancel()"
					class="ui-btn ui-btn-link"
				>
					{{ cancelBtnText }}
				</button>
			</div>
			<div class="ui-checkbox-list__footer-block --right">
				<div
					@click="selectAll()"
					class="ui-checkbox-list__footer-link"
				>
					{{ selectAllBtnText }}
				</div>
				<div
					@click="deselectAll()"
					class="ui-checkbox-list__footer-link"
				>
					{{ deselectAllBtnText }}
				</div>
			</div>
		</div>
		</div>
	`
	};

	class CheckboxList extends main_core_events.EventEmitter {
	  constructor(options) {
	    super();
	    this.setEventNamespace('BX.UI.Dialogs.CheckboxList');
	    this.subscribeFromOptions(options.events);
	    if (!main_core.Type.isArrayFilled(options.categories)) {
	      throw new Error('CheckboxList: "categories" parameter is required.');
	    }
	    this.categories = options.categories;
	    if (!main_core.Type.isArrayFilled(options.options)) {
	      throw new Error('CheckboxList: "options" parameter is required.');
	    }
	    this.options = options.options;
	    this.compactField = main_core.Type.isPlainObject(options.compactField) ? options.compactField : null;
	    this.sections = main_core.Type.isArray(options.sections) ? options.sections : null;
	    this.lang = main_core.Type.isPlainObject(options.lang) ? options.lang : {};
	    this.popup = null;
	    this.columnCount = main_core.Type.isNumber(options.columnCount) ? options.columnCount : 4;
	    this.popupOptions = main_core.Type.isPlainObject(options.popupOptions) ? options.popupOptions : {};
	  }
	  getPopup() {
	    const container = main_core.Dom.create('div');
	    main_core.Dom.addClass(container, 'ui-checkbox-list__app-container');
	    if (!this.popup) {
	      this.popup = new main_popup.Popup({
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
	        closeByEsc: true,
	        ...this.popupOptions
	      });
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
	  show() {
	    this.getPopup().show();
	  }
	  hide() {
	    this.getPopup().hide();
	  }
	}

	exports.CheckboxList = CheckboxList;

}((this.BX.UI = this.BX.UI || {}),BX,BX.Main,BX.Vue3,BX.UI,BX,BX.Event,BX));
//# sourceMappingURL=bundle.js.map
