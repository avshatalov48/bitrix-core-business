/* eslint-disable */
this.BX = this.BX || {};
(function (exports,checkboxList_css,main_popup,ui_designTokens,ui_vue3,ui_forms,ui_switcher,main_core_events,main_core) {
	'use strict';

	const viewMode = {
	  view: 'view',
	  edit: 'edit'
	};
	const CheckboxListOption = {
	  props: ['id', 'title', 'isChecked', 'isLocked', 'isEditable', 'context'],
	  emits: ['onToggleOption'],
	  data() {
	    return {
	      viewMode: viewMode.view,
	      titleData: this.title,
	      isCheckedValue: this.isChecked
	    };
	  },
	  methods: {
	    getId() {
	      return this.id;
	    },
	    getValue() {
	      return this.isCheckedValue;
	    },
	    setValue(value) {
	      this.isCheckedValue = value;
	    },
	    getTitle() {
	      var _this$$refs$title$inn, _this$$refs$title;
	      return (_this$$refs$title$inn = (_this$$refs$title = this.$refs.title) == null ? void 0 : _this$$refs$title.innerText) != null ? _this$$refs$title$inn : this.titleData;
	    },
	    setTitle(title) {
	      this.titleData = title;
	    },
	    setStateFromProps(value = null) {
	      this.viewMode = viewMode.view;
	      this.titleData = this.title;
	      this.isCheckedValue = value === null ? this.isChecked : value;
	    },
	    getOptionClassName({
	      isChecked,
	      isLocked
	    }) {
	      return ['ui-ctl', 'ui-ctl-checkbox', 'ui-checkbox-list__field-item_label', {
	        '--checked': isChecked
	      }, {
	        '--disabled': isLocked
	      }, {
	        '--editable': !(this.isViewMode || isLocked)
	      }];
	    },
	    getLabelClassName() {
	      return ['ui-ctl-label-text', 'ui-checkbox-list__field-item_text', {
	        '--editable': this.isEditMode && !this.isLocked
	      }];
	    },
	    emitHandleCheckBox(event) {
	      setTimeout(() => {
	        const {
	          id,
	          title,
	          isChecked,
	          isLocked,
	          isEditable,
	          context
	        } = this;
	        main_core_events.EventEmitter.emit('ui:checkbox-list:check-option', {
	          id,
	          title,
	          isChecked,
	          isLocked,
	          isEditable,
	          context,
	          viewMode: this.viewMode
	        });
	      });
	    },
	    handleCheckBox(event) {
	      if (this.isLocked) {
	        // eslint-disable-next-line no-param-reassign
	        event.target.checked = !event.target.checked;
	      } else {
	        this.isCheckedValue = !this.isCheckedValue;
	      }
	      const {
	        id,
	        title,
	        isLocked,
	        isCheckedValue,
	        isEditable,
	        context
	      } = this;
	      this.$emit('onToggleOption', {
	        id,
	        title,
	        isChecked: isCheckedValue,
	        isLocked,
	        isEditable,
	        context,
	        viewMode: this.viewMode
	      });
	    },
	    onToggleViewMode() {
	      this.viewMode = this.isEditMode ? viewMode.view : viewMode.edit;
	      if (this.viewMode === viewMode.view) {
	        return;
	      }
	      void this.$nextTick(() => this.setFocusOnTitle());
	    },
	    setFocusOnTitle() {
	      this.$refs.title.focus();
	      const range = document.createRange();
	      const selection = window.getSelection();
	      range.selectNodeContents(this.$refs.title);
	      range.collapse(false);
	      selection.removeAllRanges();
	      selection.addRange(range);
	    },
	    onChangeTitle({
	      target
	    }) {
	      this.titleData = target.innerText;
	    }
	  },
	  computed: {
	    isEditMode() {
	      return this.viewMode === viewMode.edit;
	    },
	    isViewMode() {
	      return this.viewMode === viewMode.view;
	    },
	    labelClassName() {
	      return this.getLabelClassName();
	    }
	  },
	  template: `
		<label
			:title="titleData"
			:class="getOptionClassName({ isChecked: isCheckedValue, isLocked })"
			@click="this.emitHandleCheckBox"
		>
			<input
				type="checkbox"
				class="ui-ctl-element ui-checkbox-list__field-item_input"
				:checked="isCheckedValue"
				@click="this.handleCheckBox"
			>
			<div
				:class="labelClassName"
				:contenteditable="(isViewMode || isLocked) ? 'false' : 'true'"
				@keydown.enter.prevent
				@blur="onChangeTitle"
				ref="title"
			>
				{{ titleData }}
			</div>
	
			<div v-if="isLocked" class="ui-checkbox-list__field-item_locked"></div>
			<div
				v-else-if="isEditable"
				class="ui-checkbox-list__field-item_edit"
				@click.prevent="onToggleViewMode"
			></div>
		</label>
	`
	};

	const CheckboxListCategory = {
	  props: ['columnCount', 'category', 'options', 'context', 'isActiveSearch', 'isEditableOptionsTitle', 'onChange', 'setOptionRef'],
	  emits: ['onToggleOption'],
	  components: {
	    CheckboxListOption
	  },
	  methods: {
	    setRef(ref) {
	      if (ref) {
	        this.setOptionRef(ref.getId(), ref);
	      }
	    },
	    onToggleOption(event) {
	      this.$emit('onToggleOption', event);
	    }
	  },
	  template: `
		<div
			v-if="options.length > 0 || !isActiveSearch"
			class="ui-checkbox-list__category"
		>
			<div v-if="category" class="ui-checkbox-list__categories-title">
				{{ category.title }}
			</div>
			<div 
				class="ui-checkbox-list__options"
				:style="{ 'column-count': columnCount }"
			>
				<div
					v-for="option in options"
					:key="option.id"
				>
					<checkbox-list-option
						:context="context"
						:id="option.id"
						:title="option.title"
						:isChecked="option.value"
						:isLocked="option?.locked"
						:isEditable="isEditableOptionsTitle"
						:ref="setRef"
						@onToggleOption="onToggleOption"
					/>
				</div>
			</div>
		</div>
	`
	};

	const CheckboxComponent = {
	  props: ['id', 'title'],
	  data() {
	    return {
	      dataTitle: this.title,
	      dataId: this.id,
	      checked: false
	    };
	  },
	  methods: {
	    handleClick(key) {
	      this.checked = !this.checked;
	      this.$emit('onToggled', this.checked);
	    }
	  },
	  template: `
		<div class="ui-checkbox-list__footer-custom-element --checkbox" @click="handleClick">
			<input type="checkbox" :name="dataId" v-model="checked">
			<label :for="dataId">{{ dataTitle }}</label>
		</div>
	`
	};

	const TextToggleComponent = {
	  props: ['id', 'title', 'dataItems'],
	  data() {
	    return {
	      dataTitle: this.title,
	      dataId: this.id,
	      value: null
	    };
	  },
	  methods: {
	    handleClick(key) {
	      let index = this.dataItems.findIndex(item => item.value === this.value);
	      if (index >= this.dataItems.length - 1) {
	        index = 0;
	      } else {
	        index++;
	      }
	      this.value = this.dataItems[index].value;
	      this.$emit('onToggled', this.value);
	    }
	  },
	  computed: {
	    currentLabel() {
	      var _this$dataItems$find;
	      if (this.value === null && main_core.Type.isArrayFilled(this.dataItems)) {
	        this.value = this.dataItems[0].value;
	        return this.dataItems[0].label;
	      }
	      return (_this$dataItems$find = this.dataItems.find(item => item.value === this.value)) == null ? void 0 : _this$dataItems$find.label;
	    }
	  },
	  template: `
		<div class="ui-checkbox-list__footer-custom-element --texttoggle" @click="handleClick">
			<span class="ui-checkbox-list__texttoggle__title">{{ dataTitle }}</span>
			<span class="ui-checkbox-list__texttoggle__value">{{ currentLabel }}</span>
			<input type="hidden" :name="dataId" v-model="value">
		</div>
	`
	};

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
				<div class="ui-checkbox-list__section_title">{{ section.title }}</div>
			</div>
		</div>
	`
	};

	const Content = {
	  components: {
	    CheckboxListSections,
	    CheckboxListCategory,
	    CheckboxComponent,
	    TextToggleComponent
	  },
	  props: ['dialog', 'popup', 'columnCount', 'compactField', 'customFooterElements', 'lang', 'sections', 'categories', 'options', 'params', 'context'],
	  data() {
	    return {
	      dataSections: this.sections,
	      dataCategories: this.categories,
	      dataCompactField: this.compactField,
	      dataOptions: this.getPreparedDataOptions(),
	      dataParams: this.getPreparedParams(),
	      optionsRef: new Map(),
	      search: '',
	      longContent: false,
	      scrollIsBottom: true,
	      scrollIsTop: false
	    };
	  },
	  methods: {
	    getPreparedDataOptions() {
	      return new Map(this.options.map(option => [option.id, option]));
	    },
	    getPreparedParams() {
	      var _params$useSearch, _params$useSectioning, _params$closeAfterApp, _params$showBackToDef, _params$isEditableOpt, _params$destroyPopupA;
	      const {
	        params
	      } = this;
	      return {
	        useSearch: Boolean((_params$useSearch = params.useSearch) != null ? _params$useSearch : true),
	        useSectioning: Boolean((_params$useSectioning = params.useSectioning) != null ? _params$useSectioning : true),
	        closeAfterApply: Boolean((_params$closeAfterApp = params.closeAfterApply) != null ? _params$closeAfterApp : true),
	        showBackToDefaultSettings: Boolean((_params$showBackToDef = params.showBackToDefaultSettings) != null ? _params$showBackToDef : true),
	        isEditableOptionsTitle: Boolean((_params$isEditableOpt = params.isEditableOptionsTitle) != null ? _params$isEditableOpt : false),
	        destroyPopupAfterClose: Boolean((_params$destroyPopupA = params.destroyPopupAfterClose) != null ? _params$destroyPopupA : true)
	      };
	    },
	    renderSwitcher() {
	      if (this.dataCompactField) {
	        new BX.UI.Switcher({
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
	      if (this.dataCompactField) {
	        this.dataCompactField.value = !this.dataCompactField.value;
	      }
	    },
	    clearSearch() {
	      this.search = '';
	    },
	    handleClearSearchButtonClick() {
	      this.setFocusToSearchInput();
	      this.clearSearch();
	    },
	    setFocusToSearchInput() {
	      var _this$$refs, _this$$refs$searchInp;
	      (_this$$refs = this.$refs) == null ? void 0 : (_this$$refs$searchInp = _this$$refs.searchInput) == null ? void 0 : _this$$refs$searchInp.focus();
	    },
	    handleSectionsToggled(key) {
	      const section = this.dataSections.find(item => item.key === key);
	      if (section) {
	        section.value = !section.value;
	      }
	    },
	    getOptionsByCategory(category = null) {
	      return this.getOptions().filter(item => item.categoryKey === category);
	    },
	    getOptions() {
	      return this.optionsByTitle;
	    },
	    getCheckedOptionsId() {
	      return this.getCheckedOptions().map(option => option.getId());
	    },
	    getCheckedOptions() {
	      return this.getOptionRefs().filter(option => option.getValue());
	    },
	    checkLongContent() {
	      if (this.$refs.container) {
	        this.longContent = this.$refs.container.clientHeight < this.$refs.container.scrollHeight;
	      } else {
	        this.longContent = false;
	      }
	    },
	    getBottomIndent() {
	      const {
	        scrollTop,
	        clientHeight,
	        scrollHeight
	      } = this.$refs.container;
	      this.scrollIsBottom = scrollTop + clientHeight < scrollHeight - 10;
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
	      const event = new main_core_events.BaseEvent({
	        data: {
	          switcher: this.dataCompactField,
	          fields: this.getCheckedOptionsId()
	        }
	      });
	      main_core_events.EventEmitter.emit(this.dialog, 'onDefault', event);
	      if (event.isDefaultPrevented()) {
	        return;
	      }
	      this.clearSearch();
	      const {
	        dataCompactField,
	        sections,
	        categories,
	        $refs
	      } = this;
	      if (dataCompactField && dataCompactField.value !== dataCompactField.defaultValue) {
	        $refs.switcher.click();
	      }
	      this.dataSections = sections;
	      this.dataOptions = this.getPreparedDataOptions();
	      this.dataCategories = categories;
	      this.setDefaultValuesForOptions();
	    },
	    setDefaultValuesForOptions() {
	      void this.$nextTick(() => {
	        this.getOptionRefs().forEach(option => option.setValue(this.dataOptions.get(option.getId()).defaultValue));
	      });
	    },
	    toggleOption(id) {
	      const option = this.optionsRef.get(id);
	      if (!option) {
	        return;
	      }
	      option.setValue(!option.getValue());
	    },
	    onSelectAllClick() {
	      if (this.isAllSelected) {
	        this.deselectAll();
	      } else {
	        this.selectAll();
	      }
	    },
	    select(id, value = true) {
	      const option = this.getOptionRefs().find(item => item.id === id);
	      option == null ? void 0 : option.setValue(value);
	    },
	    selectAll() {
	      const visibleOptionIds = new Set(this.getOptions().map(option => option.id));
	      this.getOptionRefs().forEach(option => {
	        return !option.isLocked && visibleOptionIds.has(option.getId()) && option.setValue(true);
	      });
	    },
	    deselectAll() {
	      const visibleOptionIds = new Set(this.getOptions().map(option => option.id));
	      this.getOptionRefs().forEach(option => {
	        return !option.isLocked && visibleOptionIds.has(option.getId()) && option.setValue(false);
	      });
	    },
	    getOptionRefs() {
	      return [...this.optionsRef.values()];
	    },
	    cancel() {
	      main_core_events.EventEmitter.emit(this.dialog, 'onCancel');
	      this.restoreOptionValues();
	      this.destroyOrClosePopup();
	    },
	    restoreOptionValues() {
	      this.getOptionRefs().forEach(option => option.setStateFromProps());
	    },
	    apply() {
	      if (this.isCheckedCheckboxes) {
	        return;
	      }
	      const fields = this.getCheckedOptionsId();
	      const eventParams = {
	        switcher: this.dataCompactField,
	        fields,
	        data: {
	          titles: this.getOptionTitles()
	        }
	      };
	      main_core_events.EventEmitter.emit(this.dialog, 'onApply', eventParams);
	      this.adjustOptions(fields);
	      if (this.dataParams.closeAfterApply) {
	        this.destroyOrClosePopup();
	      }
	    },
	    getOptionTitles() {
	      const titles = {};
	      this.getOptionRefs().forEach(option => {
	        titles[option.getId()] = option.getTitle();
	      });
	      return titles;
	    },
	    adjustOptions(checkedFieldIds = []) {
	      for (const option of this.optionsRef.values()) {
	        const id = option.getId();
	        const value = checkedFieldIds.includes(id);
	        this.dataOptions.set(id, {
	          ...this.dataOptions.get(id),
	          title: option.getTitle(),
	          value
	        });
	        void this.$nextTick(() => option.setStateFromProps(value));
	      }
	    },
	    destroyOrClosePopup() {
	      if (this.dataParams.destroyPopupAfterClose) {
	        this.destroyPopup();
	      } else {
	        this.closePopup();
	      }
	    },
	    destroyPopup() {
	      this.popup.destroy();
	    },
	    closePopup() {
	      this.popup.close();
	    },
	    setOptionRef(id, ref) {
	      this.optionsRef.set(id, ref);
	    },
	    isAllSectionsDisabled() {
	      return main_core.Type.isArrayFilled(this.dataSections) && this.dataSections.every(section => section.value === false);
	    },
	    onToggleOption(event) {
	      if (this.dataOptions.has(event.id)) {
	        const option = this.dataOptions.get(event.id);
	        option.value = event.isChecked;
	        this.dataOptions.set(event.id, option);
	      }
	    }
	  },
	  watch: {
	    search() {
	      void this.$nextTick(() => this.checkLongContent());
	    },
	    categoryBySection() {
	      void this.$nextTick(() => this.checkLongContent());
	    }
	  },
	  computed: {
	    visibleOptions() {
	      const {
	        dataSections,
	        optionsByTitle,
	        dataCategories
	      } = this;
	      if (!main_core.Type.isArrayFilled(dataSections)) {
	        return optionsByTitle;
	      }
	      return optionsByTitle.filter(option => {
	        const category = dataCategories.find(item => item.key === option.categoryKey);
	        const section = dataSections.find(item => item.key === category.sectionKey);
	        return section == null ? void 0 : section.value;
	      });
	    },
	    isEmptyContent() {
	      return main_core.Type.isArrayFilled(this.visibleOptions);
	    },
	    // @temporary temp, waiting for a new ui for this case
	    isNarrowWidth() {
	      return window.innerWidth * 0.9 < 500;
	    },
	    isSearchDisabled() {
	      if (main_core.Type.isArrayFilled(this.dataSections)) {
	        return !this.dataSections.some(section => section.value);
	      }
	      return false;
	    },
	    isCheckedCheckboxes() {
	      for (const option of this.optionsRef.values()) {
	        if (option.getValue() === true && option.locked !== true) {
	          return false;
	        }
	      }
	      return true;
	    },
	    optionsByTitle() {
	      const options = [...this.dataOptions.values()];
	      return options.filter(item => item.title.toLowerCase().includes(this.search.toLowerCase()));
	    },
	    categoryBySection() {
	      if (!main_core.Type.isArrayFilled(this.dataSections)) {
	        return this.dataCategories;
	      }
	      return this.dataCategories.filter(category => {
	        const section = this.dataSections.find(item => category.sectionKey === item.key);
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
	      return ['ui-btn ui-btn-primary', {
	        'ui-btn-disabled': this.isCheckedCheckboxes
	      }];
	    },
	    selectAllClassName() {
	      return ['ui-checkbox-list__footer-link --select-all', {
	        '--narrow': this.isNarrowWidth
	      }];
	    },
	    switcherText() {
	      return main_core.Type.isStringFilled(this.lang.switcher) ? this.lang.switcher : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_SWITCHER');
	    },
	    placeholderText() {
	      return main_core.Type.isStringFilled(this.lang.placeholder) ? this.lang.placeholder : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_PLACEHOLDER');
	    },
	    defaultSettingsBtnText() {
	      return main_core.Type.isStringFilled(this.lang.defaultBtn) ? this.lang.defaultBtn : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_MSGVER_1');
	    },
	    applyBtnText() {
	      return main_core.Type.isStringFilled(this.lang.acceptBtn) ? this.lang.acceptBtn : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_ACCEPT_BUTTON');
	    },
	    cancelBtnText() {
	      return main_core.Type.isStringFilled(this.lang.cancelBtn) ? this.lang.cancelBtn : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_CANCEL_BUTTON');
	    },
	    selectAllBtnText() {
	      return main_core.Type.isStringFilled(this.lang.selectAllBtn) ? this.lang.selectAllBtn : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SELECT_ALL_MSGVER_1');
	    },
	    emptyStateTitleText() {
	      if (this.isAllSectionsDisabled()) {
	        return main_core.Type.isStringFilled(this.lang.allSectionsDisabledTitle) ? this.lang.allSectionsDisabledTitle : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_EMPTY_STATE_TITLE_MSGVER_1');
	      }
	      return main_core.Type.isStringFilled(this.lang.emptyStateTitle) ? this.lang.emptyStateTitle : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_EMPTY_STATE_TITLE_MSGVER_1');
	    },
	    emptyStateDescriptionText() {
	      if (this.isAllSectionsDisabled()) {
	        return '';
	      }
	      return main_core.Type.isStringFilled(this.lang.emptyStateDescription) ? this.lang.emptyStateDescription : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_EMPTY_STATE_DESCRIPTION_MSGVER_1');
	    },
	    isAllSelected() {
	      const isAllSelected = this.getOptionRefs().filter(option => !option.isLocked).every(option => option.getValue() === true);
	      const isSomeSelected = this.getOptionRefs().filter(option => !option.isLocked).some(option => option.getValue() === true && !option.isLocked);
	      if (!isAllSelected && isSomeSelected && this.$refs.selectAllCheckbox) {
	        this.$refs.selectAllCheckbox.indeterminate = true;
	        return false;
	      }
	      if (this.$refs.selectAllCheckbox) {
	        this.$refs.selectAllCheckbox.indeterminate = false;
	      }
	      return isAllSelected;
	    }
	  },
	  mounted() {
	    this.renderSwitcher();
	    void this.$nextTick(() => {
	      this.checkLongContent();
	      this.setFocusToSearchInput();
	    });
	  },
	  template: `
		<div class="ui-checkbox-list">
			<div
				class="ui-checkbox-list__header"
				v-if="dataParams.useSearch || (dataSections && dataParams.useSectioning)"
			>
				<div class="ui-checkbox-list__header_options">
					<div
						v-if="dataCompactField"
						class="ui-checkbox-list__switcher"
					>
						<div class="ui-checkbox-list__switcher-text">
							{{ switcherText }}
						</div>
						<div class="switcher" ref="switcher"></div>
					</div>
					<div
						v-if="dataParams.useSearch"
						:class="searchClassName"
					>
						<div class="ui-checkbox-list__search-wrapper">
							<div class="ui-ctl ui-ctl-textbox ui-ctl-after-icon ui-ctl-w100">
								<input
									:placeholder="placeholderText"
									type="text"
									class="ui-ctl-element"
									v-model="search"
									@keyup.esc.stop="handleSearchEscKeyUp"
									ref="searchInput"
								>
								<button
									v-if="search.length > 0"
									@click="handleClearSearchButtonClick"
									class="ui-ctl-after ui-ctl-icon-clear ui-checkbox-list__search-clear"
								></button>
								<div
									v-else
									class="ui-ctl-after ui-ctl-icon-search"
								></div>
							</div>
						</div>
					</div>
				</div>
				<checkbox-list-sections
					v-if="dataSections && dataParams.useSectioning"
					:sections="dataSections"
					@sectionToggled="handleSectionsToggled"
				/>
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
						v-if="dataParams.useSectioning"
						v-for="category in categoryBySection"
						:key="category.key"
						:context="context"
						:category="category"
						:columnCount="columnCount"
						:options="getOptionsByCategory(category.key)"
						:isActiveSearch="search.length > 0"
						:isEditableOptionsTitle="dataParams.isEditableOptionsTitle"
						:setOptionRef="setOptionRef"
						@onToggleOption="onToggleOption"
					/>
	
					<checkbox-list-category
						v-else
						:context="context"
						:columnCount="columnCount"
						:options="getOptions()"
						:isActiveSearch="search.length > 0"
						:isEditableOptionsTitle="dataParams.isEditableOptionsTitle"
						:setOptionRef="setOptionRef"
						@onToggleOption="onToggleOption"
					/>
				</div>
				<div
					v-else
					class="ui-checkbox-list__empty"
				>
					<img
						src="/bitrix/js/ui/dialogs/checkbox-list/images/ui-checkbox-list-empty.svg"
						:alt="emptyStateTitleText"
					>
					<div class="ui-checkbox-list__empty-title">
						{{ emptyStateTitleText }}
					</div>
					<div class="ui-checkbox-list__empty-description">
						{{ emptyStateDescriptionText }}
					</div>
	
					<div
						class="ui-checkbox-list__options"
						:style="{ 'column-count': columnCount, opacity: 0 }"
					>
						<div>
							<label class="ui-ctl"></label>
						</div>
					</div>
				</div>
			</div>

			<div class="ui-checkbox-list__footer">
				<div class="ui-checkbox-list__footer-block --left">
					<div
						@click="onSelectAllClick()"
						:class="selectAllClassName"
					>
						<input 
							type="checkbox" 
							name="selectAllCheckbox"
							ref="selectAllCheckbox"
							v-model="isAllSelected"
						>
						<label
							v-if="!isNarrowWidth"
							for="selectAllCheckbox"
						>
							{{ selectAllBtnText }}
						</label>
					</div>
	
					<div
						v-if="customFooterElements"
						v-for="customElement in customFooterElements"
					>
						<checkbox-component
							v-if="customElement.type === 'checkbox'"
							:id="customElement.id"
							:title="customElement.title"
							@onToggled="customElement.onClick"
						/>
						<text-toggle-component
							v-if="customElement.type === 'textToggle'"
							:id="customElement.id"
							:title="customElement.title"
							:dataItems="customElement.dataItems"
							@onToggled="customElement.onClick"
						/>
					</div>
				</div>
				<div class="ui-checkbox-list__footer-block --right">
					<div
						v-if="dataParams.showBackToDefaultSettings"
						class="ui-checkbox-list__footer-link --default"
						@click="defaultSettings()"
					>
						{{ defaultSettingsBtnText }}
					</div>
				</div>
				<div class="ui-checkbox-list__footer-block --center">
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
			</div>
		</div>
	`
	};

	var _getColumnCount = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getColumnCount");
	var _getLayoutComponent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getLayoutComponent");
	class CheckboxList extends main_core_events.EventEmitter {
	  constructor(options) {
	    var _this$params$useSecti;
	    super();
	    Object.defineProperty(this, _getLayoutComponent, {
	      value: _getLayoutComponent2
	    });
	    Object.defineProperty(this, _getColumnCount, {
	      value: _getColumnCount2
	    });
	    this.layoutApp = null;
	    this.layoutComponent = null;
	    this.setEventNamespace('BX.UI.Dialogs.CheckboxList');
	    this.subscribeFromOptions(options.events);
	    this.context = main_core.Type.isPlainObject(options.context) ? options.context : null;
	    this.compactField = main_core.Type.isPlainObject(options.compactField) ? options.compactField : null;
	    this.sections = main_core.Type.isArray(options.sections) ? options.sections : null;
	    this.lang = main_core.Type.isPlainObject(options.lang) ? options.lang : {};
	    this.popup = null;
	    this.columnCount = main_core.Type.isNumber(options.columnCount) ? options.columnCount : 4;
	    this.popupOptions = main_core.Type.isPlainObject(options.popupOptions) ? options.popupOptions : {};
	    this.params = main_core.Type.isPlainObject(options.params) ? options.params : {};
	    const useSectioning = (_this$params$useSecti = this.params.useSectioning) != null ? _this$params$useSecti : true;
	    if (useSectioning && !main_core.Type.isArray(options.categories)) {
	      throw new Error('CheckboxList: "categories" parameter is required.');
	    }
	    this.categories = options.categories;
	    if (useSectioning && !main_core.Type.isArray(options.options)) {
	      throw new Error('CheckboxList: "options" parameter is required.');
	    }
	    this.options = options.options;
	    this.customFooterElements = main_core.Type.isArrayFilled(options.customFooterElements) ? options.customFooterElements : [];
	    this.closeAfterApply = main_core.Type.isBoolean(options.closeAfterApply) ? options.closeAfterApply : true;
	  }
	  getPopup() {
	    const container = main_core.Dom.create('div');
	    main_core.Dom.addClass(container, 'ui-checkbox-list__app-container');
	    if (!this.popup) {
	      const {
	        lang,
	        layoutComponent,
	        popupOptions
	      } = this;
	      const {
	        innerWidth,
	        innerHeight
	      } = window;
	      this.popup = new main_popup.Popup({
	        className: 'ui-checkbox-list-popup',
	        width: 997,
	        maxWidth: Math.round(innerWidth * 0.9),
	        overlay: true,
	        autoHide: true,
	        minHeight: 200,
	        maxHeight: Math.round(innerHeight * 0.9),
	        borderRadius: 20,
	        contentPadding: 0,
	        contentBackground: 'transparent',
	        animation: 'fading-slide',
	        titleBar: lang.title,
	        content: container,
	        closeIcon: true,
	        closeByEsc: true,
	        ...popupOptions,
	        events: {
	          onPopupClose: () => layoutComponent == null ? void 0 : layoutComponent.restoreOptionValues()
	        }
	      });
	      const {
	        compactField,
	        customFooterElements,
	        sections,
	        categories,
	        options,
	        popup,
	        params,
	        context
	      } = this;
	      this.layoutApp = ui_vue3.BitrixVue.createApp(Content, {
	        compactField,
	        customFooterElements,
	        lang,
	        sections,
	        categories,
	        options,
	        popup,
	        columnCount: babelHelpers.classPrivateFieldLooseBase(this, _getColumnCount)[_getColumnCount](),
	        params,
	        context,
	        dialog: this
	      });

	      // eslint-disable-next-line unicorn/consistent-destructuring
	      this.layoutComponent = this.layoutApp.mount(container);
	    }
	    return this.popup;
	  }
	  show() {
	    this.getPopup().show();
	    babelHelpers.classPrivateFieldLooseBase(this, _getLayoutComponent)[_getLayoutComponent]().setFocusToSearchInput();
	  }
	  hide() {
	    var _this$layoutComponent;
	    (_this$layoutComponent = this.layoutComponent) == null ? void 0 : _this$layoutComponent.destroyOrClosePopup();
	  }
	  destroy() {
	    if (!this.layoutApp) {
	      return;
	    }
	    this.hide();
	    this.layoutApp.unmount();
	    this.layoutComponent = null;
	    this.popup = null;
	  }
	  isShown() {
	    return this.popup && this.popup.isShown();
	  }
	  getOptions() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getLayoutComponent)[_getLayoutComponent]().getOptions();
	  }
	  getSelectedOptions() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getLayoutComponent)[_getLayoutComponent]().getCheckedOptionsId();
	  }
	  handleSwitcherToggled(id) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getLayoutComponent)[_getLayoutComponent]().handleSwitcherToggled(id);
	  }
	  handleOptionToggled(id) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getLayoutComponent)[_getLayoutComponent]().toggleOption(id);
	  }
	  saveColumns(columnIds, callback) {
	    if (!main_core.Type.isArrayFilled(columnIds)) {
	      return;
	    }
	    columnIds.forEach(id => this.selectOption(id));
	    this.apply();
	  }
	  selectOption(id, value) {
	    // to maintain backward compatibility without creating dependencies on main within the ticket #187991
	    // @todo remove later and set default value = true in the function signature
	    if (value !== false) {
	      // eslint-disable-next-line no-param-reassign
	      value = true;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _getLayoutComponent)[_getLayoutComponent]().select(id, value);
	  }
	  apply() {
	    babelHelpers.classPrivateFieldLooseBase(this, _getLayoutComponent)[_getLayoutComponent]().apply();
	  }
	}
	function _getColumnCount2() {
	  let {
	    columnCount
	  } = this;
	  const {
	    innerWidth
	  } = window;
	  if (innerWidth <= 480) {
	    columnCount = 1;
	  } else if (innerWidth <= 768 && columnCount > 2) {
	    columnCount = 2;
	  }
	  return columnCount;
	}
	function _getLayoutComponent2() {
	  if (!this.layoutComponent) {
	    void this.getPopup();
	  }
	  return this.layoutComponent;
	}

	exports.CheckboxList = CheckboxList;

}((this.BX.UI = this.BX.UI || {}),BX,BX.Main,BX,BX.Vue3,BX,BX.UI,BX.Event,BX));
//# sourceMappingURL=bundle.js.map
