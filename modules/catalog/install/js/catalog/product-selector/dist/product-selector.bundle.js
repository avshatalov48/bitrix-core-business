this.BX = this.BX || {};
(function (exports,ui_designTokens,ui_forms,fileinput,catalog_skuTree,main_loader,ui_infoHelper,ui_entitySelector,catalog_productModel,catalog_productSelector,catalog_barcodeScanner,ui_notification,main_core,main_core_events,ui_qrauthorization,spotlight,ui_tour) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7,
	  _t8;
	class ProductSearchSelectorFooter extends ui_entitySelector.DefaultFooter {
	  constructor(dialog, options) {
	    super(dialog, options);
	    this.loader = null;
	    this.errorAdminHint = options.errorAdminHint || '';
	    this.getDialog().subscribe('onSearch', this.handleOnSearch.bind(this));
	  }
	  getContent() {
	    let phrase = '';
	    const isViewCreateButton = this.options.allowCreateItem === true || this.options.allowEditItem === false;
	    if (this.isViewEditButton() && isViewCreateButton) {
	      phrase = main_core.Tag.render(_t || (_t = _`
				<div>${0}</div>
			`), main_core.Loc.getMessage('CATALOG_SELECTOR_SEARCH_POPUP_FOOTER_1'));
	      const createButton = phrase.querySelector('create-button');
	      main_core.Dom.replace(createButton, this.getLabelContainer());
	      const changeButton = phrase.querySelector('change-button');
	      main_core.Dom.replace(changeButton, this.getSaveContainer());
	    } else if (this.isViewEditButton()) {
	      phrase = this.getSaveContainer();
	    } else {
	      phrase = this.getLabelContainer();
	    }
	    return main_core.Tag.render(_t2 || (_t2 = _`
			<div class="ui-selector-search-footer-box">
				${0}
				${0}
				${0}
			</div>
		`), phrase, this.getHintContainer(), this.getLoaderContainer());
	  }
	  isViewEditButton() {
	    return this.options.allowEditItem === true;
	  }
	  getLoader() {
	    if (main_core.Type.isNil(this.loader)) {
	      this.loader = new main_loader.Loader({
	        target: this.getLoaderContainer(),
	        size: 17,
	        color: 'rgba(82, 92, 105, 0.9)'
	      });
	    }
	    return this.loader;
	  }
	  showLoader() {
	    void this.getLoader().show();
	  }
	  hideLoader() {
	    void this.getLoader().hide();
	  }
	  setLabel(label) {
	    if (main_core.Type.isString(label)) {
	      this.getLabelContainer().textContent = label;
	    }
	  }
	  getLabelContainer() {
	    return this.cache.remember('label', () => {
	      return main_core.Tag.render(_t3 || (_t3 = _`
				<span>
					<span onclick="${0}" class="ui-selector-footer-link  ui-selector-footer-link-add">
						${0}
					</span>
					${0}
				</span>
			`), this.handleClick.bind(this), this.getOption('creationLabel', main_core.Loc.getMessage('CATALOG_SELECTOR_SEARCH_POPUP_FOOTER_CREATE')), this.getQueryContainer());
	    });
	  }
	  getQueryContainer() {
	    return this.cache.remember('name-container', () => {
	      return main_core.Tag.render(_t4 || (_t4 = _`
				<span class="ui-selector-search-footer-query"></span>
			`));
	    });
	  }
	  getSaveContainer() {
	    return this.cache.remember('save-container', () => {
	      const className = `ui-selector-footer-link`;
	      const messageId = this.options.inputName === catalog_productSelector.ProductSelector.INPUT_FIELD_BARCODE ? 'CATALOG_SELECTOR_SEARCH_POPUP_FOOTER_BARCODE_CHANGE' : 'CATALOG_SELECTOR_SEARCH_POPUP_FOOTER_CHANGE';
	      return main_core.Tag.render(_t5 || (_t5 = _`
			<span class="${0}" onclick="${0}">
				${0}
			</span>
		`), className, this.onClickSaveChanges.bind(this), main_core.Loc.getMessage(messageId));
	    });
	  }
	  getLoaderContainer() {
	    return this.cache.remember('loader', () => {
	      return main_core.Tag.render(_t6 || (_t6 = _`
				<div class="ui-selector-search-footer-loader"></div>
			`));
	    });
	  }
	  getHintContainer() {
	    return this.cache.remember('hint', () => {
	      let message = null;
	      if (!this.options.allowEditItem && !this.options.allowCreateItem) {
	        message = main_core.Loc.getMessage('CATALOG_SELECTOR_SEARCH_POPUP_DISABLED_FOOTER_ALL_HINT', {
	          '#ADMIN_HINT#': this.errorAdminHint
	        });
	      } else if (!this.options.allowEditItem) {
	        message = main_core.Loc.getMessage('CATALOG_SELECTOR_SEARCH_POPUP_DISABLED_FOOTER_EDIT_HINT', {
	          '#ADMIN_HINT#': this.errorAdminHint
	        });
	      } else if (!this.options.allowCreateItem) {
	        message = main_core.Loc.getMessage('CATALOG_SELECTOR_SEARCH_POPUP_DISABLED_FOOTER_ADD_HINT', {
	          '#ADMIN_HINT#': this.errorAdminHint
	        });
	      }
	      if (!message) {
	        return null;
	      }
	      const hintNode = main_core.Tag.render(_t7 || (_t7 = _`<span class="ui-btn ui-btn-icon-lock ui-btn-link"></span>`));
	      hintNode.dataset.hint = message;
	      hintNode.dataset.hintNoIcon = true;
	      BX.UI.Hint.initNode(hintNode);
	      return main_core.Tag.render(_t8 || (_t8 = _`<div class="product-search-selector-disabled-footer-hint">${0}</div>`), hintNode);
	    });
	  }
	  onClickSaveChanges() {
	    if (!this.options.allowEditItem) {
	      return;
	    }
	    const lastQuery = this.getDialog().getActiveTab().getLastSearchQuery();
	    this.getDialog().emit('ChangeItem:onClick', {
	      query: lastQuery.query
	    });
	    this.getDialog().clearSearch();
	    this.getDialog().hide();
	  }
	  createItem() {
	    if (!this.options.allowCreateItem) {
	      return;
	    }
	    const tagSelector = this.getDialog().getTagSelector();
	    if (tagSelector && tagSelector.isLocked()) {
	      return;
	    }
	    const finalize = () => {
	      this.hideLoader();
	      if (this.getDialog().getTagSelector()) {
	        this.getDialog().getTagSelector().unlock();
	        this.getDialog().focusSearch();
	      }
	    };
	    event.preventDefault();
	    this.showLoader();
	    if (tagSelector) {
	      tagSelector.lock();
	    }
	    this.getDialog().emitAsync('Search:onItemCreateAsync', {
	      searchQuery: this.getDialog().getActiveTab().getLastSearchQuery()
	    }).then(() => {
	      this.getTab().clearResults();
	      this.getDialog().clearSearch();
	      if (this.getDialog().getActiveTab() === this.getTab()) {
	        this.getDialog().selectFirstTab();
	      }
	      finalize();
	    }).catch(() => {
	      finalize();
	    });
	  }
	  handleClick() {
	    this.createItem();
	  }
	  handleOnSearch(event) {
	    const {
	      query
	    } = event.getData();
	    if (this.options.currentValue === query || query === '') {
	      this.hide();
	    } else {
	      this.show();
	    }
	    this.getQueryContainer().textContent = " " + query;
	  }
	}

	let _$1 = t => t,
	  _t$1,
	  _t2$1,
	  _t3$1;
	class ProductCreationLimitedFooter extends ui_entitySelector.DefaultFooter {
	  getContent() {
	    const phrase = main_core.Tag.render(_t$1 || (_t$1 = _$1`
			<div>${0}</div>
		`), main_core.Loc.getMessage('CATALOG_SELECTOR_LIMITED_PRODUCT_CREATION'));
	    const infoButton = main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
			<a class="ui-btn ui-btn-sm ui-btn-primary ui-btn-hover ui-btn-round">
				${0}
			</a>
		`), main_core.Loc.getMessage('CATALOG_SELECTOR_LICENSE_EXPLODE'));
	    main_core.Event.bind(infoButton, 'click', () => {
	      BX.UI.InfoHelper.show('limit_shop_products');
	    });
	    return main_core.Tag.render(_t3$1 || (_t3$1 = _$1`
			<div class="ui-selector-search-footer-box">
				<div class="ui-selector-search-footer-box">
					<div class="tariff-lock"></div>
					${0}
				</div>
				<div>
					${0}
				</div>				
			</div>
		`), phrase, infoButton);
	  }
	}

	class SelectorErrorCode {
	  static getCodes() {
	    return [SelectorErrorCode.NOT_SELECTED_PRODUCT, SelectorErrorCode.FAILED_PRODUCT];
	  }
	}
	SelectorErrorCode.NOT_SELECTED_PRODUCT = 'NOT_SELECTED_PRODUCT';
	SelectorErrorCode.FAILED_PRODUCT = 'FAILED_PRODUCT';

	let _$2 = t => t,
	  _t$2,
	  _t2$2,
	  _t3$2,
	  _t4$1,
	  _t5$1,
	  _t6$1,
	  _t7$1,
	  _t8$1,
	  _t9,
	  _t10,
	  _t11,
	  _t12,
	  _t13;
	class DialogMode {}
	DialogMode.SEARCHING = 'SEARCHING';
	DialogMode.SHOW_PRODUCT_ITEM = 'SHOW_PRODUCT_ITEM';
	DialogMode.SHOW_RECENT = 'SHOW_RECENT';
	var _showSelectedItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showSelectedItem");
	var _loadPreselectedItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadPreselectedItems");
	var _showPreselectedItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showPreselectedItems");
	var _searchItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("searchItem");
	class ProductSearchInput {
	  constructor(id, options = {}) {
	    Object.defineProperty(this, _searchItem, {
	      value: _searchItem2
	    });
	    Object.defineProperty(this, _showPreselectedItems, {
	      value: _showPreselectedItems2
	    });
	    Object.defineProperty(this, _loadPreselectedItems, {
	      value: _loadPreselectedItems2
	    });
	    Object.defineProperty(this, _showSelectedItem, {
	      value: _showSelectedItem2
	    });
	    this.cache = new main_core.Cache.MemoryCache();
	    this.id = id || main_core.Text.getRandom();
	    this.selector = options.selector;
	    if (!(this.selector instanceof catalog_productSelector.ProductSelector)) {
	      throw new Error('Product selector instance not found.');
	    }
	    this.model = options.model || {};
	    this.isEnabledSearch = options.isSearchEnabled;
	    this.isEnabledDetailLink = options.isEnabledDetailLink;
	    this.inputName = options.inputName || catalog_productSelector.ProductSelector.INPUT_FIELD_NAME;
	    this.immutableFieldNames = [catalog_productSelector.ProductSelector.INPUT_FIELD_BARCODE, catalog_productSelector.ProductSelector.INPUT_FIELD_NAME];
	    if (!this.immutableFieldNames.includes(this.inputName)) {
	      this.immutableFieldNames.push(this.inputName);
	    }
	    this.ajaxInProcess = false;
	    this.loadedSelectedItem = null;
	    this.handleSearchInput = main_core.Runtime.debounce(this.searchInDialog, 500, this);
	  }
	  destroy() {}
	  getId() {
	    return this.id;
	  }
	  getSelectorType() {
	    return catalog_productSelector.ProductSelector.INPUT_FIELD_NAME;
	  }
	  getField(fieldName) {
	    return this.model.getField(fieldName);
	  }
	  getValue() {
	    return this.getField(this.inputName);
	  }
	  getFilledValue() {
	    return this.getNameInput().value || '';
	  }
	  isSearchEnabled() {
	    return this.isEnabledSearch;
	  }
	  toggleIcon(icon, value) {
	    if (main_core.Type.isDomNode(icon)) {
	      main_core.Dom.style(icon, 'display', value);
	    }
	  }
	  getNameBlock() {
	    return this.cache.remember('nameBlock', () => {
	      return main_core.Tag.render(_t$2 || (_t$2 = _$2`
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
					${0}
					${0}
					${0}
				</div>
			`), this.getNameTag(), this.getNameInput(), this.getHiddenNameInput());
	    });
	  }
	  getNameTag() {
	    if (!this.model.isNew()) {
	      return '';
	    }
	    return main_core.Tag.render(_t2$2 || (_t2$2 = _$2`
			<div class="ui-ctl-tag">${0}</div>
		`), main_core.Loc.getMessage('CATALOG_SELECTOR_NEW_TAG_TITLE'));
	  }
	  getNameInput() {
	    return this.cache.remember('nameInput', () => {
	      const input = main_core.Tag.render(_t3$2 || (_t3$2 = _$2`
				<input type="text"
					class="ui-ctl-element ui-ctl-textbox"
					autocomplete="off"
					data-name="${0}"
					value="${0}"
					placeholder="${0}"
					title="${0}"
					onchange="${0}"
				>
			`), main_core.Text.encode(this.inputName), main_core.Text.encode(this.getValue()), main_core.Text.encode(this.getPlaceholder()), main_core.Text.encode(this.getValue()), this.handleNameInputHiddenChange.bind(this));
	      if (this.selector.getConfig('SELECTOR_INPUT_DISABLED', false)) {
	        main_core.Dom.addClass(input, 'ui-ctl-disabled');
	        input.setAttribute('disabled', true);
	      }
	      return input;
	    });
	  }
	  getHiddenNameInput() {
	    return this.cache.remember('hiddenNameInput', () => {
	      return main_core.Tag.render(_t4$1 || (_t4$1 = _$2`
				<input
				 	type="hidden"
					name="${0}"
					value="${0}"
				>
			`), main_core.Text.encode(this.inputName), main_core.Text.encode(this.getValue()));
	    });
	  }
	  handleNameInputHiddenChange(event) {
	    this.getHiddenNameInput().value = event.target.value;
	  }
	  getClearIcon() {
	    return this.cache.remember('closeIcon', () => {
	      return main_core.Tag.render(_t5$1 || (_t5$1 = _$2`
				<button
					class="ui-ctl-after ui-ctl-icon-clear"
					onclick="${0}"
				></button>
			`), this.handleClearIconClick.bind(this));
	    });
	  }
	  getArrowIcon() {
	    return this.cache.remember('arrowIcon', () => {
	      return main_core.Tag.render(_t6$1 || (_t6$1 = _$2`
				<a
					href="${0}"
					target="_blank"
					class="ui-ctl-after ui-ctl-icon-forward"
				>
			`), main_core.Text.encode(this.model.getDetailPath()));
	    });
	  }
	  getSearchIcon() {
	    return this.cache.remember('searchIcon', () => {
	      return main_core.Tag.render(_t7$1 || (_t7$1 = _$2`
				<button
					class="ui-ctl-after ui-ctl-icon-search"
					onclick="${0}"
				></button>
			`), this.handleSearchIconClick.bind(this));
	    });
	  }
	  layout() {
	    this.clearInputCache();
	    const block = main_core.Tag.render(_t8$1 || (_t8$1 = _$2`<div class="ui-ctl ui-ctl-w100 ui-ctl-after-icon"></div>`));
	    this.toggleIcon(this.getClearIcon(), 'none');
	    main_core.Dom.append(this.getClearIcon(), block);
	    if (this.isSearchEnabled()) {
	      if (this.selector.isProductSearchEnabled()) {
	        this.initHasDialogItems();
	      }
	      this.toggleIcon(this.getSearchIcon(), main_core.Type.isStringFilled(this.getFilledValue()) ? 'none' : 'block');
	      main_core.Dom.append(this.getSearchIcon(), block);
	      main_core.Event.bind(this.getNameInput(), 'click', this.handleClickNameInput.bind(this));
	      main_core.Event.bind(this.getNameInput(), 'input', this.handleSearchInput);
	      main_core.Event.bind(this.getNameInput(), 'blur', this.handleNameInputBlur.bind(this));
	      main_core.Event.bind(this.getNameInput(), 'keydown', this.handleNameInputKeyDown.bind(this));
	      this.dialogMode = this.model.isCatalogExisted() ? DialogMode.SHOW_PRODUCT_ITEM : DialogMode.SHOW_RECENT;
	    }
	    if (this.showDetailLink() && main_core.Type.isStringFilled(this.getValue())) {
	      this.toggleIcon(this.getClearIcon(), 'none');
	      this.toggleIcon(this.getSearchIcon(), 'none');
	      this.toggleIcon(this.getArrowIcon(), 'block');
	      main_core.Dom.append(this.getArrowIcon(), block);
	    }
	    main_core.Event.bind(this.getNameInput(), 'click', this.handleIconsSwitchingOnNameInput.bind(this));
	    main_core.Event.bind(this.getNameInput(), 'input', this.handleIconsSwitchingOnNameInput.bind(this));
	    main_core.Event.bind(this.getNameInput(), 'change', this.handleNameInputChange.bind(this));
	    main_core.Dom.append(this.getNameBlock(), block);
	    return block;
	  }
	  showDetailLink() {
	    return this.isEnabledDetailLink;
	  }
	  getDialog() {
	    return this.cache.remember('dialog', () => {
	      var _this$getNameInput;
	      const searchTypeId = ProductSearchInput.SEARCH_TYPE_ID;
	      const entity = {
	        id: searchTypeId,
	        options: {
	          iblockId: this.model.getIblockId(),
	          basePriceId: this.model.getBasePriceId(),
	          currency: this.model.getCurrency()
	        },
	        dynamicLoad: true,
	        dynamicSearch: true
	      };
	      const restrictedProductTypes = this.selector.getConfig('RESTRICTED_PRODUCT_TYPES', null);
	      if (!main_core.Type.isNil(restrictedProductTypes)) {
	        entity.options.restrictedProductTypes = restrictedProductTypes;
	      }
	      const params = {
	        id: this.id + '_' + searchTypeId,
	        height: 300,
	        width: Math.max((_this$getNameInput = this.getNameInput()) == null ? void 0 : _this$getNameInput.offsetWidth, 565),
	        context: 'catalog-products',
	        targetNode: this.getNameInput(),
	        enableSearch: false,
	        multiple: false,
	        dropdownMode: true,
	        searchTabOptions: {
	          stub: true,
	          stubOptions: {
	            title: main_core.Tag.message(_t9 || (_t9 = _$2`${0}`), 'CATALOG_SELECTOR_IS_EMPTY_TITLE'),
	            subtitle: this.isAllowedCreateProduct() ? main_core.Tag.message(_t10 || (_t10 = _$2`${0}`), 'CATALOG_SELECTOR_IS_EMPTY_SUBTITLE') : '',
	            arrow: true
	          }
	        },
	        events: {
	          'Item:onSelect': this.onProductSelect.bind(this),
	          'Search:onItemCreateAsync': this.createProduct.bind(this),
	          'ChangeItem:onClick': this.showChangeNotification.bind(this)
	        },
	        entities: [entity]
	      };
	      const settingsCollection = main_core.Extension.getSettings('catalog.product-selector');
	      if (main_core.Type.isObject(settingsCollection.get('limitInfo'))) {
	        params.footer = ProductCreationLimitedFooter;
	      } else if (this.model && this.model.isCatalogExisted()) {
	        params.footer = ProductSearchSelectorFooter;
	        params.footerOptions = {
	          inputName: this.inputName,
	          allowEditItem: this.isAllowedEditProduct(),
	          allowCreateItem: this.isAllowedCreateProduct(),
	          errorAdminHint: settingsCollection.get('errorAdminHint'),
	          creationLabel: main_core.Loc.getMessage('CATALOG_SELECTOR_SEARCH_POPUP_FOOTER_CREATE'),
	          currentValue: this.getValue()
	        };
	      } else {
	        params.searchOptions = {
	          allowCreateItem: this.isAllowedCreateProduct()
	        };
	      }
	      return new ui_entitySelector.Dialog(params);
	    });
	  }
	  initHasDialogItems() {
	    if (!main_core.Type.isNil(this.selector.getConfig('EXIST_DIALOG_ITEMS'))) {
	      return;
	    }
	    if (!this.selector.getModel().isEmpty()) {
	      this.selector.setConfig('EXIST_DIALOG_ITEMS', true);
	      return;
	    }

	    // is null, that not send ajax
	    this.selector.setConfig('EXIST_DIALOG_ITEMS', false);
	    const dialog = this.getDialog();
	    if (dialog.hasDynamicLoad()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _loadPreselectedItems)[_loadPreselectedItems]();
	      dialog.subscribeOnce('onLoad', () => {
	        if (dialog.getPreselectedItems().length > 1) {
	          this.selector.setConfig('EXIST_DIALOG_ITEMS', true);
	        }
	      });
	    } else {
	      this.selector.setConfig('EXIST_DIALOG_ITEMS', true);
	    }
	  }
	  isAllowedCreateProduct() {
	    return this.selector.getConfig('IS_ALLOWED_CREATION_PRODUCT', true) && this.selector.checkProductAddRights();
	  }
	  isAllowedEditProduct() {
	    return this.selector.checkProductEditRights();
	  }
	  handleNameInputKeyDown(event) {
	    const dialog = this.getDialog();
	    if (event.key === 'Enter' && dialog.getActiveTab() === dialog.getSearchTab()) {
	      // prevent a form submit
	      event.stopPropagation();
	      event.preventDefault();
	      if (main_core.Browser.isMac() && event.metaKey || event.ctrlKey) {
	        dialog.getSearchTab().getFooter().createItem();
	      }
	    }
	  }
	  handleIconsSwitchingOnNameInput(event) {
	    this.toggleIcon(this.getArrowIcon(), 'none');
	    if (main_core.Type.isStringFilled(event.target.value)) {
	      this.toggleIcon(this.getClearIcon(), 'block');
	      this.toggleIcon(this.getSearchIcon(), 'none');
	    } else {
	      this.toggleIcon(this.getClearIcon(), 'none');
	      if (this.isSearchEnabled()) {
	        this.toggleIcon(this.getSearchIcon(), 'block');
	      }
	    }
	  }
	  clearInputCache() {
	    this.cache.delete('dialog');
	    this.cache.delete('nameBlock');
	    this.cache.delete('nameInput');
	    this.cache.delete('hiddenNameInput');
	  }
	  handleClearIconClick(event) {
	    this.selector.emit('onBeforeClear', {
	      selectorId: this.selector.getId(),
	      rowId: this.selector.getRowId()
	    });
	    this.loadedSelectedItem = null;
	    if (this.selector.isProductSearchEnabled() && !this.model.isEmpty()) {
	      this.selector.clearState();
	      this.selector.clearLayout();
	      this.selector.layout();
	    } else {
	      const newValue = '';
	      this.toggleIcon(this.getClearIcon(), 'none');
	      this.onChangeValue(newValue);
	    }
	    this.selector.focusName();
	    this.selector.emit('onClear', {
	      selectorId: this.selector.getId(),
	      rowId: this.selector.getRowId()
	    });
	    event.stopPropagation();
	    event.preventDefault();
	  }
	  handleNameInputChange(event) {
	    const value = event.target.value;
	    this.onChangeValue(value);
	  }
	  onChangeValue(value) {
	    const fields = {};
	    this.getNameInput().title = value;
	    this.getNameInput().value = value;
	    fields[this.inputName] = value;
	    main_core_events.EventEmitter.emit('ProductSelector::onNameChange', {
	      rowId: this.selector.getRowId(),
	      fields
	    });
	    if (!this.selector.isEnabledAutosave()) {
	      return;
	    }
	    this.selector.getModel().setFields(fields);
	    this.selector.getModel().save().then(() => {
	      BX.UI.Notification.Center.notify({
	        id: 'saving_field_notify_name',
	        closeButton: false,
	        content: main_core.Tag.render(_t11 || (_t11 = _$2`<div>${0}</div>`), main_core.Loc.getMessage('CATALOG_SELECTOR_SAVING_NOTIFICATION_NAME')),
	        autoHide: true
	      });
	    });
	  }
	  focusName() {
	    requestAnimationFrame(() => this.getNameInput().focus());
	  }
	  searchInDialog() {
	    const searchQuery = this.getFilledValue().trim();
	    if (searchQuery === '') {
	      if (this.isHasDialogItems === false) {
	        this.getDialog().hide();
	        return;
	      }
	      this.loadedSelectedItem = null;
	      babelHelpers.classPrivateFieldLooseBase(this, _showPreselectedItems)[_showPreselectedItems]();
	      return;
	    }
	    this.dialogMode = DialogMode.SEARCHING;
	    babelHelpers.classPrivateFieldLooseBase(this, _searchItem)[_searchItem](searchQuery);
	    this.isSearchingInProcess = true;
	  }
	  handleClickNameInput() {
	    const dialog = this.getDialog();
	    if (dialog.isOpen() || this.getFilledValue() === '' && this.isHasDialogItems === false) {
	      dialog.hide();
	      return;
	    }
	    this.showItems();
	  }
	  showItems() {
	    if (this.getFilledValue() === '') {
	      babelHelpers.classPrivateFieldLooseBase(this, _showPreselectedItems)[_showPreselectedItems]();
	      return;
	    }
	    if (!this.model.isCatalogExisted() || this.dialogMode !== DialogMode.SHOW_PRODUCT_ITEM) {
	      this.searchInDialog();
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _showSelectedItem)[_showSelectedItem]();
	  }
	  handleNameInputBlur(event) {
	    // timeout to toggle clear icon handler while cursor is inside of name input
	    setTimeout(() => {
	      this.toggleIcon(this.getClearIcon(), 'none');
	      if (this.showDetailLink() && main_core.Type.isStringFilled(this.getValue())) {
	        if (this.isSearchEnabled()) {
	          this.toggleIcon(this.getSearchIcon(), 'none');
	        }
	        this.toggleIcon(this.getArrowIcon(), 'block');
	      } else {
	        this.toggleIcon(this.getArrowIcon(), 'none');
	        if (this.isSearchEnabled()) {
	          this.toggleIcon(this.getSearchIcon(), main_core.Type.isStringFilled(this.getFilledValue()) ? 'none' : 'block');
	        }
	      }
	    }, 200);
	    if (this.isSearchEnabled() && this.selector.isEnabledEmptyProductError()) {
	      setTimeout(() => {
	        if (!this.selector.inProcess() && (this.model.isEmpty() || !main_core.Type.isStringFilled(this.getFilledValue()))) {
	          this.model.getErrorCollection().setError(SelectorErrorCode.NOT_SELECTED_PRODUCT, this.selector.getEmptySelectErrorMessage());
	          this.selector.layoutErrors();
	        }
	      }, 200);
	    }
	  }
	  handleSearchIconClick(event) {
	    this.searchInDialog();
	    this.focusName();
	    event.stopPropagation();
	    event.preventDefault();
	  }
	  getImmutableFieldNames() {
	    return this.immutableFieldNames;
	  }
	  setInputValueOnProductSelect(item) {
	    item.getDialog().getTargetNode().value = item.getTitle();
	  }
	  onProductSelect(event) {
	    const item = event.getData().item;
	    this.setInputValueOnProductSelect(item);
	    this.toggleIcon(this.getSearchIcon(), 'none');
	    this.clearErrors();
	    if (this.selector) {
	      const isNew = item.getCustomData().get('isNew');
	      const immutableFields = [];
	      this.getImmutableFieldNames().forEach(key => {
	        if (!main_core.Type.isNil(item.getCustomData().get(key))) {
	          this.model.setField(key, item.getCustomData().get(key));
	          immutableFields.push(key);
	        }
	      });
	      this.selector.onProductSelect(item.getId(), {
	        isNew,
	        immutableFields
	      });
	      this.selector.clearLayout();
	      this.selector.layout();
	    }
	    this.dialogMode = DialogMode.SHOW_PRODUCT_ITEM;
	    this.loadedSelectedItem = item;
	    this.cache.delete('dialog');
	  }
	  clearErrors() {
	    const errors = this.model.getErrorCollection().getErrors();
	    for (const code in errors) {
	      if (catalog_productSelector.ProductSelector.ErrorCodes.getCodes().includes(code)) {
	        this.model.getErrorCollection().removeError(code);
	      }
	    }
	  }
	  createProductModelFromSearchQuery(searchQuery) {
	    const fields = {
	      ...this.selector.getModel().getFields()
	    };
	    fields[this.inputName] = searchQuery;
	    return new catalog_productModel.ProductModel({
	      isSimpleModel: true,
	      isNew: true,
	      currency: this.selector.options.currency,
	      iblockId: this.selector.getModel().getIblockId(),
	      basePriceId: this.selector.getModel().getBasePriceId(),
	      fields
	    });
	  }
	  createProduct(event) {
	    if (this.ajaxInProcess) {
	      return;
	    }
	    this.ajaxInProcess = true;
	    const dialog = event.getTarget();
	    const {
	      searchQuery
	    } = event.getData();
	    const newProduct = this.createProductModelFromSearchQuery(searchQuery.getQuery());
	    main_core_events.EventEmitter.emit(this.selector, 'onBeforeCreate', {
	      model: newProduct
	    });
	    return new Promise((resolve, reject) => {
	      if (!this.checkCreationModel(newProduct)) {
	        this.ajaxInProcess = false;
	        dialog.hide();
	        reject();
	        return;
	      }
	      dialog.showLoader();
	      newProduct.save().then(response => {
	        dialog.hideLoader();
	        const id = main_core.Text.toInteger(response.data.id);
	        const item = dialog.addItem({
	          id,
	          entityId: ProductSearchInput.SEARCH_TYPE_ID,
	          title: searchQuery.getQuery(),
	          tabs: dialog.getRecentTab().getId(),
	          customData: {
	            isNew: true
	          }
	        });
	        this.selector.getModel().setOption('isSimpleModel', false);
	        this.selector.getModel().setOption('isNew', true);
	        this.getImmutableFieldNames().forEach(name => {
	          this.selector.getModel().setField(name, newProduct.getField(name));
	          this.selector.getModel().setOption(name, newProduct.getField(name));
	        });
	        if (item) {
	          item.select();
	        }
	        dialog.hide();
	        this.cache.delete('dialog');
	        this.ajaxInProcess = false;
	        this.isHasDialogItems = true;
	        resolve();
	      }).catch(errorResponse => {
	        dialog.hideLoader();
	        errorResponse.errors.forEach(error => {
	          BX.UI.Notification.Center.notify({
	            closeButton: true,
	            content: main_core.Tag.render(_t12 || (_t12 = _$2`<div>${0}</div>`), error.message),
	            autoHide: true
	          });
	        });
	        this.ajaxInProcess = false;
	        reject();
	      });
	    });
	  }
	  checkCreationModel(creationModel) {
	    return true;
	  }
	  showChangeNotification(event) {
	    const {
	      query
	    } = event.getData();
	    const options = {
	      title: main_core.Loc.getMessage('CATALOG_SELECTOR_SAVING_NOTIFICATION_' + this.selector.getType()),
	      events: {
	        onSave: () => {
	          if (this.selector) {
	            this.selector.getModel().setField(this.inputName, query);
	            this.selector.getModel().save([this.inputName]).catch(errorResponse => {
	              errorResponse.errors.forEach(error => {
	                BX.UI.Notification.Center.notify({
	                  closeButton: true,
	                  content: main_core.Tag.render(_t13 || (_t13 = _$2`<div>${0}</div>`), error.message),
	                  autoHide: true
	                });
	              });
	            });
	          }
	        }
	      }
	    };
	    if (this.selector.getConfig('ROLLBACK_INPUT_AFTER_CANCEL', false)) {
	      options.declineCancelTitle = main_core.Loc.getMessage('CATALOG_SELECTOR_SAVING_NOTIFICATION_CANCEL_TITLE');
	      options.events.onCancel = () => {
	        this.selector.clearLayout();
	        this.selector.layout();
	      };
	    }
	    this.selector.getModel().showSaveNotifier('nameChanger_' + this.selector.getId(), options);
	  }
	  getPlaceholder() {
	    return this.isSearchEnabled() && this.model.isEmpty() ? main_core.Loc.getMessage('CATALOG_SELECTOR_BEFORE_SEARCH_TITLE') : main_core.Loc.getMessage('CATALOG_SELECTOR_VIEW_NAME_TITLE');
	  }
	  removeSpotlight() {}
	  removeQrAuth() {}
	}
	function _showSelectedItem2() {
	  var _dialog$getFooter2;
	  if (!this.selector.isProductSearchEnabled()) {
	    return;
	  }
	  const dialog = this.getDialog();
	  dialog.removeItems();
	  new Promise(resolve => {
	    if (!main_core.Type.isNil(this.loadedSelectedItem)) {
	      resolve();
	      return;
	    }
	    dialog.showLoader();
	    main_core.ajax.runAction('catalog.productSelector.getSkuSelectorItem', {
	      json: {
	        id: this.selector.getModel().getSkuId(),
	        options: {
	          iblockId: this.model.getIblockId(),
	          basePriceId: this.model.getBasePriceId(),
	          currency: this.model.getCurrency()
	        }
	      }
	    }).then(response => {
	      dialog.hideLoader();
	      this.loadedSelectedItem = null;
	      if (main_core.Type.isObject(response.data) && !dialog.isLoading()) {
	        this.loadedSelectedItem = dialog.addItem(response.data);
	      }
	      resolve();
	    });
	  }).then(() => {
	    if (!main_core.Type.isNil(this.loadedSelectedItem)) {
	      var _dialog$getFooter;
	      dialog.setPreselectedItems([this.selector.getModel().getSkuId()]);
	      dialog.getRecentTab().getRootNode().addItem(this.loadedSelectedItem);
	      dialog.selectFirstTab();
	      (_dialog$getFooter = dialog.getFooter()) == null ? void 0 : _dialog$getFooter.hide();
	    } else {
	      this.searchInDialog();
	    }
	  });
	  dialog.getPopup().show();
	  (_dialog$getFooter2 = dialog.getFooter()) == null ? void 0 : _dialog$getFooter2.hide();
	}
	function _loadPreselectedItems2() {
	  const dialog = this.getDialog();
	  if (dialog.isLoading()) {
	    return;
	  }
	  if (this.loadedSelectedItem) {
	    dialog.removeItems();
	    dialog.loadState = 'UNSENT';
	    this.loadedSelectedItem = null;
	  }
	  dialog.load();
	}
	function _showPreselectedItems2() {
	  var _dialog$getFooter3;
	  if (!this.selector.isProductSearchEnabled()) {
	    return;
	  }
	  this.dialogMode = DialogMode.SHOW_RECENT;
	  const dialog = this.getDialog();
	  babelHelpers.classPrivateFieldLooseBase(this, _loadPreselectedItems)[_loadPreselectedItems]();
	  dialog.selectFirstTab();
	  (_dialog$getFooter3 = dialog.getFooter()) == null ? void 0 : _dialog$getFooter3.hide();
	  dialog.show();
	}
	function _searchItem2(searchQuery = '') {
	  if (!this.selector.isProductSearchEnabled()) {
	    return;
	  }
	  const dialog = this.getDialog();
	  dialog.getPopup().show();
	  dialog.search(searchQuery);
	}
	ProductSearchInput.SEARCH_TYPE_ID = 'product';

	let _$3 = t => t,
	  _t$3;
	class ProductImageInput {
	  constructor(id, options = {}) {
	    var _this$selector$getMod;
	    this.id = id || main_core.Text.getRandom();
	    this.selector = options.selector || null;
	    if (!(this.selector instanceof catalog_productSelector.ProductSelector)) {
	      throw new Error('Product selector instance not found.');
	    }
	    this.config = options.config || {};
	    if (!main_core.Type.isStringFilled((_this$selector$getMod = this.selector.getModel()) == null ? void 0 : _this$selector$getMod.getImageCollection().getEditInput())) {
	      this.restoreDefaultInputHtml();
	    }
	    this.enableSaving = options.enableSaving;
	    this.uploaderFieldMap = {};
	  }
	  getId() {
	    return this.id;
	  }
	  setId(id) {
	    this.id = id;
	  }
	  setView(html) {
	    var _this$selector$getMod2;
	    (_this$selector$getMod2 = this.selector.getModel()) == null ? void 0 : _this$selector$getMod2.getImageCollection().setPreview(html);
	  }
	  setInputHtml(html) {
	    var _this$selector$getMod3;
	    (_this$selector$getMod3 = this.selector.getModel()) == null ? void 0 : _this$selector$getMod3.getImageCollection().setEditInput(html);
	  }
	  restoreDefaultInputHtml() {
	    var _this$selector$getMod4, _this$selector$getMod5;
	    const defaultInput = `
			<div class='ui-image-input-container ui-image-input-img--disabled'>
				<div class='adm-fileinput-wrapper '>
					<div class='adm-fileinput-area mode-pict adm-fileinput-drag-area'></div>
				</div>
			</div>
`;
	    (_this$selector$getMod4 = this.selector.getModel()) == null ? void 0 : _this$selector$getMod4.getImageCollection().setEditInput(defaultInput);
	    (_this$selector$getMod5 = this.selector.getModel()) == null ? void 0 : _this$selector$getMod5.getImageCollection().setPreview(defaultInput);
	  }
	  isViewMode() {
	    return this.selector && (this.selector.isViewMode() || !this.selector.model.isSaveable());
	  }
	  isEnabledLiveSaving() {
	    return this.enableSaving;
	  }
	  layout() {
	    var _this$selector$getMod6, _this$selector$getMod7, _this$selector$getMod8, _this$selector$getMod9;
	    const imageContainer = main_core.Tag.render(_t$3 || (_t$3 = _$3`<div></div>`));
	    const html = this.isViewMode() ? (_this$selector$getMod6 = this.selector.getModel()) == null ? void 0 : (_this$selector$getMod7 = _this$selector$getMod6.getImageCollection()) == null ? void 0 : _this$selector$getMod7.getPreview() : (_this$selector$getMod8 = this.selector.getModel()) == null ? void 0 : (_this$selector$getMod9 = _this$selector$getMod8.getImageCollection()) == null ? void 0 : _this$selector$getMod9.getEditInput();
	    main_core.Runtime.html(imageContainer, html);
	    return imageContainer;
	  }
	}

	let _$4 = t => t,
	  _t$4,
	  _t2$3,
	  _t3$3,
	  _t4$2,
	  _t5$2;
	class BarcodeSearchSelectorFooter extends ProductSearchSelectorFooter {
	  constructor(id, options = {}) {
	    super(id, options);
	    this.isEmptyBarcode = options.isEmptyBarcode;
	    this.getDialog().subscribe('SearchTab:onLoad', this.handleOnSearchLoad.bind(this));
	  }
	  getContent() {
	    this.barcodeContent = super.getContent();
	    this.scannerContent = this.getScannerContent();
	    main_core.Dom.style(this.barcodeContent, 'display', 'none');
	    return main_core.Tag.render(_t$4 || (_t$4 = _$4`
			<div class="catalog-footers-container">
				${0}
				${0}
			</div>
		`), this.barcodeContent, this.scannerContent);
	  }
	  isViewEditButton() {
	    return !this.isEmptyBarcode && super.isViewEditButton();
	  }
	  getScannerContent() {
	    const phrase = main_core.Tag.render(_t2$3 || (_t2$3 = _$4`
			<div>${0}</div>
		`), main_core.Loc.getMessage('CATALOG_SELECTOR_SEARCH_POPUP_FOOTER_BARCODE'));
	    const createButton = phrase.querySelector('create-button');
	    main_core.Dom.replace(createButton, this.getScannerLabelContainer());
	    return main_core.Tag.render(_t3$3 || (_t3$3 = _$4`
			<div class="ui-selector-search-footer-box">
				${0}
				${0}
			</div>
		`), phrase, this.getLoaderContainer());
	  }
	  getScannerLabelContainer() {
	    return this.cache.remember('scannerLabel', () => {
	      return main_core.Tag.render(_t4$2 || (_t4$2 = _$4`
				<span onclick="${0}">
					<span class="ui-selector-footer-link ui-selector-footer-link-add footer-link--warehouse-barcode-icon">
						${0}
					</span>
					${0}
				</span>
			`), this.handleScannerClick.bind(this), main_core.Loc.getMessage('CATALOG_SELECTOR_SEARCH_POPUP_FOOTER_BARCODE_START_SCAN_LABEL'), this.getScannerQueryContainer());
	    });
	  }
	  getScannerQueryContainer() {
	    return this.cache.remember('scanner_name-container', () => {
	      return main_core.Tag.render(_t5$2 || (_t5$2 = _$4`
				<span class="ui-selector-search-footer-query"></span>
			`));
	    });
	  }
	  handleScannerClick() {
	    var _this$options;
	    const inputEntity = (_this$options = this.options) == null ? void 0 : _this$options.inputEntity;
	    if (inputEntity) {
	      inputEntity.startMobileScanner();
	    }
	  }
	  handleOnSearch(event) {
	    const {
	      query
	    } = event.getData();
	    if (!main_core.Type.isStringFilled(query)) {
	      this.show();
	      main_core.Dom.style(this.scannerContent, 'display', '');
	      main_core.Dom.style(this.barcodeContent, 'display', 'none');
	    } else if (this.options.currentValue === query) {
	      this.hide();
	    } else {
	      this.show();
	      main_core.Dom.style(this.barcodeContent, 'display', '');
	      main_core.Dom.style(this.scannerContent, 'display', 'none');
	    }
	    this.getQueryContainer().textContent = " " + query;
	    this.getScannerQueryContainer().textContent = " " + query;
	  }
	  handleOnSearchLoad(event) {
	    const {
	      searchTab
	    } = event.getData();
	    this.getDialog().getItems().forEach(item => {
	      if (item.getCustomData().get('BARCODE') === searchTab.getLastSearchQuery().getQuery()) {
	        this.hide();
	      }
	    });
	  }
	}

	let _$5 = t => t,
	  _t$5,
	  _t2$4,
	  _t3$4,
	  _t4$3,
	  _t5$3,
	  _t6$2,
	  _t7$2,
	  _t8$2,
	  _t9$1,
	  _t10$1;
	class BarcodeSearchInput extends ProductSearchInput {
	  constructor(id, options = {}) {
	    super(id, options);
	    this.onFocusHandler = this.handleFocusEvent.bind(this);
	    this.onBlurHandler = this.handleBlurEvent.bind(this);
	    this.focused = false;
	    this.settingsCollection = main_core.Extension.getSettings('catalog.product-selector');
	    this.isInstalledMobileApp = this.selector.getConfig('IS_INSTALLED_MOBILE_APP') || this.settingsCollection.get('isInstallMobileApp');
	    if (!this.settingsCollection.get('isEnabledQrAuth') && this.selector.getConfig('ENABLE_BARCODE_QR_AUTH', true)) {
	      this.qrAuth = new ui_qrauthorization.QrAuthorization();
	      this.qrAuth.createQrCodeImage();
	    }
	  }
	  destroy() {
	    main_core.Event.unbind(this.getNameInput(), 'focus', this.onFocusHandler);
	    main_core.Event.unbind(this.getNameInput(), 'blur', this.onBlurHandler);
	  }
	  handleFocusEvent() {
	    this.focused = true;
	  }
	  handleBlurEvent() {
	    this.focused = false;
	  }
	  isSearchEnabled() {
	    return true;
	  }
	  showDetailLink() {
	    return false;
	  }
	  getNameBlock() {
	    return this.cache.remember('nameBlock', () => {
	      return main_core.Tag.render(_t$5 || (_t$5 = _$5`
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
					${0}
					${0}
				</div>
			`), this.getNameInput(), this.getHiddenNameInput());
	    });
	  }
	  getDialog() {
	    return this.cache.remember('dialog', () => {
	      var _this$getNameInput;
	      const entity = {
	        id: BarcodeSearchInput.SEARCH_TYPE_ID,
	        options: {
	          iblockId: this.model.getIblockId(),
	          basePriceId: this.model.getBasePriceId(),
	          currency: this.model.getCurrency()
	        },
	        dynamicLoad: true,
	        dynamicSearch: true,
	        searchFields: [{
	          name: 'title',
	          type: 'string',
	          system: true,
	          searchable: false
	        }]
	      };
	      const restrictedProductTypes = this.selector.getConfig('RESTRICTED_PRODUCT_TYPES', null);
	      if (!main_core.Type.isNil(restrictedProductTypes)) {
	        entity.options.restrictedProductTypes = restrictedProductTypes;
	      }
	      const params = {
	        id: this.id + '_' + BarcodeSearchInput.SEARCH_TYPE_ID,
	        height: 300,
	        width: Math.max((_this$getNameInput = this.getNameInput()) == null ? void 0 : _this$getNameInput.offsetWidth, 565),
	        context: null,
	        targetNode: this.getNameInput(),
	        enableSearch: false,
	        multiple: false,
	        dropdownMode: true,
	        searchTabOptions: {
	          stub: true,
	          stubOptions: {
	            title: main_core.Tag.message(_t2$4 || (_t2$4 = _$5`${0}`), 'CATALOG_SELECTOR_IS_EMPTY_TITLE'),
	            subtitle: this.isAllowedCreateProduct() ? main_core.Tag.message(_t3$4 || (_t3$4 = _$5`${0}`), 'CATALOG_SELECTOR_IS_EMPTY_SUBTITLE') : '',
	            arrow: true
	          }
	        },
	        events: {
	          'Item:onSelect': this.onProductSelect.bind(this),
	          'Search:onItemCreateAsync': this.createProduct.bind(this),
	          'ChangeItem:onClick': this.showChangeNotification.bind(this)
	        },
	        entities: [entity]
	      };
	      if (this.model.getSkuId() && !main_core.Type.isStringFilled(this.model.getField(this.inputName))) {
	        params.preselectedItems = [[BarcodeSearchInput.SEARCH_TYPE_ID, this.model.getSkuId()]];
	      }
	      if (main_core.Type.isObject(this.settingsCollection.get('limitInfo'))) {
	        params.footer = ProductCreationLimitedFooter;
	      } else {
	        params.footer = BarcodeSearchSelectorFooter;
	        params.footerOptions = {
	          inputEntity: this,
	          isEmptyBarcode: !this.model || !this.model.isCatalogExisted(),
	          inputName: this.inputName,
	          errorAdminHint: this.settingsCollection.get('errorAdminHint'),
	          allowEditItem: this.isAllowedEditProduct(),
	          allowCreateItem: this.isAllowedCreateProduct(),
	          creationLabel: main_core.Loc.getMessage('CATALOG_SELECTOR_SEARCH_POPUP_FOOTER_CREATE_WITH_BARCODE'),
	          currentValue: this.getValue(),
	          searchOptions: {
	            allowCreateItem: this.isAllowedCreateProduct(),
	            footerOptions: {
	              label: main_core.Loc.getMessage('CATALOG_SELECTOR_SEARCH_POPUP_FOOTER_CREATE_WITH_BARCODE')
	            }
	          }
	        };
	      }
	      return new ui_entitySelector.Dialog(params);
	    });
	  }
	  layoutMobileQrPopup() {
	    return this.cache.remember('qrMobilePopup', () => {
	      const closeIcon = main_core.Tag.render(_t4$3 || (_t4$3 = _$5`<span class="popup-window-close-icon"></span>`));
	      main_core.Event.bind(closeIcon, 'click', this.closeMobilePopup.bind(this));
	      let sendButton = '';
	      let helpButton = '';
	      if (top.BX.Helper) {
	        helpButton = main_core.Tag.render(_t5$3 || (_t5$3 = _$5`
					<a class="product-selector-mobile-popup-link ui-btn ui-btn-light-border ui-btn-round">
						${0}
					</a>
				`), main_core.Loc.getMessage('CATALOG_SELECTOR_MOBILE_POPUP_HELP_BUTTON'));
	        main_core.Event.bind(helpButton, 'click', () => {
	          top.BX.Helper.show("redirect=detail&code=14956818");
	        });
	        sendButton = main_core.Tag.render(_t6$2 || (_t6$2 = _$5`
					<a class="product-selector-mobile-popup-link ui-btn ui-btn-link">
						${0}
					</a>
				`), main_core.Loc.getMessage('CATALOG_SELECTOR_MOBILE_POPUP_SEND_PUSH_BUTTON'));
	        main_core.Event.bind(sendButton, 'click', () => {
	          top.BX.Helper.show("redirect=detail&code=15042444");
	        });
	      }
	      return main_core.Tag.render(_t7$2 || (_t7$2 = _$5`
				<div data-role="mobile-popup">
					<div class="product-selector-mobile-popup-overlay"></div>
					<div class="product-selector-mobile-popup-content">
						<div class="product-selector-mobile-popup-title">${0}</div>
						<div class="product-selector-mobile-popup-text">${0}</div>
						<div class="product-selector-mobile-popup-qr">
							${0}
						</div>
						<div class="product-selector-mobile-popup-link-container">
							${0}
							${0}
						</div>
						${0}
					</div>
				</div>
			`), main_core.Loc.getMessage('CATALOG_SELECTOR_MOBILE_POPUP_TITLE'), main_core.Loc.getMessage('CATALOG_SELECTOR_MOBILE_POPUP_INSTRUCTION'), this.qrAuth.getQrNode(), helpButton, sendButton, closeIcon);
	    });
	  }
	  closeMobilePopup() {
	    this.removeQrAuth();
	    main_core.ajax.runAction('catalog.ProductSelector.isInstalledMobileApp', {
	      json: {}
	    }).then(result => {
	      this.selector.emit('onBarcodeQrClose', {});
	      if (result.data === true) {
	        this.selector.emit('onBarcodeScannerInstallChecked', {});
	        this.isInstalledMobileApp = true;
	      }
	    });
	    main_core.userOptions.save('product-selector', 'barcodeQrAuth', 'showed', 'Y');
	  }
	  handleClickNameInput(event) {
	    if (this.qrAuth && this.getDialog().getContainer()) {
	      if (!main_core.Dom.hasClass(this.getDialog().getContainer(), 'qr-barcode-info')) {
	        main_core.Dom.addClass(this.getDialog().getContainer(), 'qr-barcode-info');
	      }
	      if (this.getDialog().getContainer()) {
	        main_core.Dom.append(this.layoutMobileQrPopup(), this.getDialog().getContainer());
	      }
	    }
	    super.handleClickNameInput(event);
	  }
	  showItems() {
	    this.searchInDialog();
	  }
	  onChangeValue(value) {
	    const fields = {};
	    this.getNameInput().title = value;
	    this.getNameInput().value = value;
	    fields[this.inputName] = value;
	    main_core_events.EventEmitter.emit('ProductSelector::onBarcodeChange', {
	      rowId: this.selector.getRowId(),
	      fields
	    });
	    this.selector.emit('onBarcodeChange', {
	      value
	    });
	    if (this.selector.isEnabledAutosave()) {
	      this.selector.getModel().setField(this.inputName, value);
	      this.selector.getModel().showSaveNotifier('barcodeChanger_' + this.selector.getId(), {
	        title: main_core.Loc.getMessage('CATALOG_SELECTOR_SAVING_NOTIFICATION_BARCODE'),
	        disableCancel: true,
	        events: {
	          onSave: () => {
	            if (this.selector) {
	              this.selector.getModel().save([this.inputName]);
	            }
	          }
	        }
	      });
	    }
	  }
	  searchInDialog() {
	    const searchQuery = this.getFilledValue().trim();
	    this.searchByBarcode(searchQuery);
	  }
	  searchByBarcode(searchQuery = '') {
	    if (!this.selector.isProductSearchEnabled()) {
	      return;
	    }
	    const dialog = this.getDialog();
	    if (!dialog) {
	      return;
	    }
	    dialog.removeItems();
	    if (!main_core.Type.isStringFilled(searchQuery)) {
	      if (this.model && this.model.isCatalogExisted()) {
	        dialog.setPreselectedItems([[BarcodeSearchInput.SEARCH_TYPE_ID, this.model.getSkuId()]]);
	        dialog.loadState = 'UNSENT';
	        dialog.load();
	      }
	    }
	    dialog.show();
	    dialog.search(searchQuery);
	  }
	  handleNameInputBlur(event) {
	    // timeout to toggle clear icon handler while cursor is inside of name input
	    setTimeout(() => {
	      this.toggleIcon(this.getClearIcon(), 'none');
	      if (this.showDetailLink() && main_core.Type.isStringFilled(this.getValue())) {
	        this.toggleIcon(this.getSearchIcon(), 'none');
	        this.toggleIcon(this.getArrowIcon(), 'block');
	      } else {
	        this.toggleIcon(this.getArrowIcon(), 'none');
	        this.toggleIcon(this.getSearchIcon(), main_core.Type.isStringFilled(this.getFilledValue()) ? 'none' : 'block');
	      }
	    }, 200);
	  }
	  setInputValueOnProductSelect(item) {
	    item.getDialog().getTargetNode().value = item.getSubtitle();
	  }
	  getCreationProduct(name) {
	    const fields = {
	      ...this.selector.getModel().getFields()
	    };
	    fields[catalog_productSelector.ProductSelector.INPUT_FIELD_NAME] = name;
	    return new catalog_productModel.ProductModel({
	      isSimpleModel: true,
	      isNew: true,
	      currency: this.selector.options.currency,
	      iblockId: this.selector.getModel().getIblockId(),
	      basePriceId: this.selector.getModel().getBasePriceId(),
	      fields
	    });
	  }
	  createProductModelFromSearchQuery(searchQuery) {
	    const model = super.createProductModelFromSearchQuery(searchQuery);
	    model.setField(catalog_productSelector.ProductSelector.INPUT_FIELD_NAME, main_core.Loc.getMessage('CATALOG_SELECTOR_NEW_BARCODE_PRODUCT_NAME'));
	    model.setField(this.inputName, searchQuery);
	    return model;
	  }
	  checkCreationModel(creationModel) {
	    if (!main_core.Type.isStringFilled(creationModel.getField(catalog_productSelector.ProductSelector.INPUT_FIELD_NAME))) {
	      this.model.getErrorCollection().setError(SelectorErrorCode.NOT_SELECTED_PRODUCT, main_core.Loc.getMessage('CATALOG_SELECTOR_EMPTY_TITLE'));
	      return false;
	    }
	    return true;
	  }
	  getPlaceholder() {
	    return this.isSearchEnabled() && this.model.isEmpty() ? main_core.Loc.getMessage('CATALOG_SELECTOR_BEFORE_SEARCH_BARCODE_TITLE') : main_core.Loc.getMessage('CATALOG_SELECTOR_VIEW_BARCODE_TITLE');
	  }
	  handleClearIconClick(event) {
	    this.toggleIcon(this.getClearIcon(), 'none');
	    this.onChangeValue('');
	    this.selector.focusName();
	    event.stopPropagation();
	    event.preventDefault();
	  }
	  startMobileScanner(event) {
	    if (this.isInstalledMobileApp) {
	      this.sendMobilePush(event);
	      return;
	    }
	    if (!this.qrAuth) {
	      this.qrAuth = new ui_qrauthorization.QrAuthorization();
	      this.qrAuth.createQrCodeImage();
	    }
	    if (this.getDialog().isOpen()) {
	      this.getDialog().hide();
	      this.getDialog().subscribeOnce('onHide', this.handleClickNameInput.bind(this));
	    } else {
	      this.handleClickNameInput(event);
	    }
	  }
	  sendMobilePush(event) {
	    event == null ? void 0 : event.preventDefault();
	    this.getDialog().hide();
	    this.getNameInput().focus();
	    if (!this.selector.isEnabledMobileScanning()) {
	      return;
	    }
	    const token = this.selector.getMobileScannerToken();
	    catalog_barcodeScanner.BarcodeScanner.open(token);
	    const repeatLink = main_core.Tag.render(_t8$2 || (_t8$2 = _$5`<span class='ui-notification-balloon-action'>${0}</span>`), main_core.Loc.getMessage('CATALOG_SELECTOR_SEND_PUSH_ON_SCANNER_NOTIFICATION_REPEAT'));
	    main_core.Event.bind(repeatLink, 'click', this.sendMobilePush.bind(this));
	    const content = main_core.Tag.render(_t9$1 || (_t9$1 = _$5`
			<div>
				<span>${0}</span>
				${0}
			</div>
		`), main_core.Loc.getMessage('CATALOG_SELECTOR_SEND_PUSH_ON_SCANNER_NOTIFICATION'), repeatLink);
	    BX.UI.Notification.Center.notify({
	      content,
	      category: 'sending_push_barcode_scanner_notification',
	      autoHideDelay: 5000
	    });
	  }
	  getProductIdByBarcode(barcode) {
	    return main_core.ajax.runAction('catalog.ProductSelector.getProductIdByBarcode', {
	      json: {
	        barcode: barcode
	      }
	    });
	  }
	  applyScannerData(barcode) {
	    this.getProductIdByBarcode(barcode).then(response => {
	      const productId = response == null ? void 0 : response.data;
	      if (productId) {
	        this.selectScannedBarcodeProduct(productId);
	      } else {
	        this.searchByBarcode(barcode);
	      }
	      this.getNameInput().value = main_core.Text.encode(barcode);
	    });
	  }
	  selectScannedBarcodeProduct(productId) {
	    this.toggleIcon(this.getSearchIcon(), 'none');
	    this.clearErrors();
	    if (this.selector) {
	      this.selector.onProductSelect(productId, {
	        isNew: false,
	        immutableFields: []
	      });
	      this.selector.clearLayout();
	      this.selector.layout();
	    }
	    this.cache.delete('dialog');
	  }
	  getBarcodeIcon() {
	    return this.cache.remember('barcodeIcon', () => {
	      const barcodeIcon = main_core.Tag.render(_t10$1 || (_t10$1 = _$5`
				<button	class="ui-ctl-before warehouse-barcode-icon" title="${0}"></button>
			`), main_core.Loc.getMessage('CATALOG_SELECTOR_BARCODE_ICON_TITLE'));
	      if (!this.settingsCollection.get('isShowedBarcodeSpotlightInfo') && this.settingsCollection.get('isAllowedShowBarcodeSpotlightInfo') && this.selector.getConfig('ENABLE_INFO_SPOTLIGHT', true)) {
	        this.spotlight = new BX.SpotLight({
	          id: 'selector_barcode_scanner_info',
	          targetElement: barcodeIcon,
	          autoSave: true,
	          targetVertex: "middle-center",
	          zIndex: 200
	        });
	        this.spotlight.show();
	        main_core_events.EventEmitter.subscribe(this.spotlight, 'BX.SpotLight:onTargetEnter', () => {
	          const guide = new ui_tour.Guide({
	            steps: [{
	              target: barcodeIcon,
	              title: main_core.Loc.getMessage('CATALOG_SELECTOR_BARCODE_SCANNER_FIRST_TIME_HINT_TITLE'),
	              text: main_core.Loc.getMessage('CATALOG_SELECTOR_BARCODE_SCANNER_FIRST_TIME_HINT_TEXT')
	            }],
	            onEvents: true
	          });
	          guide.getPopup().setAutoHide(true);
	          guide.showNextStep();
	          this.selector.setConfig('ENABLE_INFO_SPOTLIGHT', false);
	          this.selector.emit('onSpotlightClose', {});
	        });
	      }
	      main_core.Event.bind(barcodeIcon, 'click', event => {
	        event.preventDefault();
	        if (this.qrAuth) {
	          this.handleClickNameInput(event);
	        } else {
	          this.startMobileScanner(event);
	        }
	      });
	      return barcodeIcon;
	    });
	  }
	  layout() {
	    const block = super.layout();
	    main_core.Dom.append(this.getBarcodeIcon(), block);
	    this.getNameInput().className += ' catalog-product-field-input-barcode';
	    main_core.Event.bind(this.getNameInput(), 'focus', this.onFocusHandler);
	    main_core.Event.bind(this.getNameInput(), 'blur', this.onBlurHandler);
	    return block;
	  }
	  removeSpotlight() {
	    if (this.spotlight) {
	      this.spotlight.close();
	    }
	  }
	  removeQrAuth() {
	    var _this$getDialog$getCo;
	    const mobilePopup = (_this$getDialog$getCo = this.getDialog().getContainer()) == null ? void 0 : _this$getDialog$getCo.querySelector('[data-role="mobile-popup"]');
	    if (mobilePopup) {
	      main_core.Dom.remove(mobilePopup);
	      if (main_core.Dom.hasClass(this.getDialog().getContainer(), 'qr-barcode-info')) {
	        main_core.Dom.removeClass(this.getDialog().getContainer(), 'qr-barcode-info');
	      }
	    }
	    this.qrAuth = null;
	  }
	}
	BarcodeSearchInput.SEARCH_TYPE_ID = 'barcode';

	let _$6 = t => t,
	  _t$6,
	  _t2$5,
	  _t3$5,
	  _t4$4,
	  _t5$4,
	  _t6$3,
	  _t7$3,
	  _t8$3;
	const instances = new Map();
	const iblockSkuTreeProperties = new Map();
	var _inAjaxProcess = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("inAjaxProcess");
	class ProductSelector extends main_core_events.EventEmitter {
	  static getById(id) {
	    return instances.get(id) || null;
	  }
	  constructor(id, options = {}) {
	    super();
	    Object.defineProperty(this, _inAjaxProcess, {
	      writable: true,
	      value: false
	    });
	    this.mode = ProductSelector.MODE_EDIT;
	    this.cache = new main_core.Cache.MemoryCache();
	    this.type = ProductSelector.INPUT_FIELD_NAME;
	    this.mobileScannerToken = null;
	    this.variationChangeHandler = this.handleVariationChange.bind(this);
	    this.onSaveImageHandler = this.onSaveImage.bind(this);
	    this.onChangeFieldsHandler = main_core.Runtime.debounce(this.onChangeFields, 500, this);
	    this.onUploaderIsInitedHandler = this.onUploaderIsInited.bind(this);
	    this.onNameChangeFieldHandler = main_core.Runtime.debounce(this.onNameChange, 500, this);
	    this.setEventNamespace('BX.Catalog.ProductSelector');
	    this.id = id || main_core.Text.getRandom();
	    options.inputFieldName = options.inputFieldName || ProductSelector.INPUT_FIELD_NAME;
	    this.options = options || {};
	    this.settings = main_core.Extension.getSettings('catalog.product-selector');
	    this.type = this.options.type || ProductSelector.INPUT_FIELD_NAME;
	    this.setMode(options.mode);
	    if (options.model && options.model instanceof catalog_productModel.ProductModel) {
	      this.model = options.model;
	    } else {
	      this.model = catalog_productModel.ProductModel.getById(this.id);
	    }
	    if (!(this.model instanceof catalog_productModel.ProductModel)) {
	      this.model = new catalog_productModel.ProductModel({
	        currency: options.currency,
	        iblockId: main_core.Text.toNumber(options.iblockId),
	        basePriceId: main_core.Text.toNumber(options.basePriceId),
	        fields: options.fields,
	        skuTree: options.skuTree,
	        storeMap: options.storeMap
	      });
	    }
	    this.model.getImageCollection().setMorePhotoValues(options.morePhotoValues);
	    if (!main_core.Type.isNil(this.getConfig('DETAIL_PATH'))) {
	      this.model.setDetailPath(this.getConfig('DETAIL_PATH'));
	    }
	    if (options.failedProduct) {
	      this.model.getErrorCollection().setError(SelectorErrorCode.FAILED_PRODUCT, '');
	    }
	    if (this.isShowableEmptyProductError()) {
	      this.model.getErrorCollection().setError(SelectorErrorCode.NOT_SELECTED_PRODUCT, this.getEmptySelectErrorMessage());
	    }
	    if (options.fileView) {
	      this.model.getImageCollection().setPreview(options.fileView);
	    }
	    if (options.fileInput) {
	      this.model.getImageCollection().setEditInput(options.fileInput);
	    }
	    this.layout();
	    if (options.skuTree) {
	      this.updateSkuTree(options.skuTree);
	    }
	    if (options.scannerToken) {
	      this.setMobileScannerToken(options.scannerToken);
	    }
	    this.subscribeEvents();
	    instances.set(this.id, this);
	  }
	  setModel(model) {
	    this.model = model;
	  }
	  getModel() {
	    return this.model;
	  }
	  setMode(mode) {
	    if (!main_core.Type.isNil(mode)) {
	      this.mode = mode === ProductSelector.MODE_VIEW ? ProductSelector.MODE_VIEW : ProductSelector.MODE_EDIT;
	    }
	  }
	  isViewMode() {
	    return this.mode === ProductSelector.MODE_VIEW;
	  }
	  isShortViewFormat() {
	    return this.getConfig('VIEW_FORMAT', ProductSelector.FULL_VIEW_FORMAT) === ProductSelector.SHORT_VIEW_FORMAT;
	  }
	  isSaveable() {
	    return !this.isViewMode() && this.model.isSaveable();
	  }
	  isEnabledAutosave() {
	    return this.isSaveable() && this.getConfig('ENABLE_AUTO_SAVE', false);
	  }
	  isEnabledMobileScanning() {
	    return !this.isViewMode() && this.getConfig('ENABLE_MOBILE_SCANNING', true);
	  }
	  getEmptySelectErrorMessage() {
	    return this.checkProductAddRights() ? main_core.Loc.getMessage('CATALOG_SELECTOR_SELECTED_PRODUCT_TITLE') : main_core.Loc.getMessage('CATALOG_SELECTOR_SELECT_PRODUCT_TITLE');
	  }
	  getMobileScannerToken() {
	    return this.mobileScannerToken || main_core.Text.getRandom(16);
	  }
	  checkProductViewRights() {
	    var _this$model$checkAcce;
	    return (_this$model$checkAcce = this.model.checkAccess(catalog_productModel.RightActionDictionary.ACTION_PRODUCT_VIEW)) != null ? _this$model$checkAcce : true;
	  }
	  checkProductEditRights() {
	    var _this$model$checkAcce2;
	    return (_this$model$checkAcce2 = this.model.checkAccess(catalog_productModel.RightActionDictionary.ACTION_PRODUCT_EDIT)) != null ? _this$model$checkAcce2 : false;
	  }
	  checkProductAddRights() {
	    var _this$model$checkAcce3;
	    return (_this$model$checkAcce3 = this.model.checkAccess(catalog_productModel.RightActionDictionary.ACTION_PRODUCT_ADD)) != null ? _this$model$checkAcce3 : false;
	  }
	  setMobileScannerToken(token) {
	    this.mobileScannerToken = token;
	  }
	  removeMobileScannerToken() {
	    this.mobileScannerToken = null;
	  }
	  getId() {
	    return this.id;
	  }
	  getType() {
	    return this.type;
	  }
	  getConfig(name, defaultValue) {
	    return BX.prop.get(this.options.config, name, defaultValue);
	  }
	  setConfig(name, value) {
	    this.options.config[name] = value;
	    return this;
	  }
	  getRowId() {
	    return this.getConfig('ROW_ID');
	  }
	  getFileInput() {
	    if (!this.fileInput) {
	      this.fileInput = new ProductImageInput(this.options.fileInputId, {
	        selector: this,
	        enableSaving: this.getConfig('ENABLE_IMAGE_CHANGE_SAVING', false)
	      });
	    }
	    return this.fileInput;
	  }
	  isProductSearchEnabled() {
	    return this.getConfig('ENABLE_SEARCH', false) && this.model.getIblockId() > 0 && this.checkProductViewRights();
	  }
	  isSkuTreeEnabled() {
	    return this.getConfig('ENABLE_SKU_TREE', true) !== false;
	  }
	  isImageFieldEnabled() {
	    return this.getConfig('ENABLE_IMAGE_INPUT', true) !== false;
	  }
	  isShowableEmptyProductError() {
	    return this.isEnabledEmptyProductError() && (this.model.isEmpty() && this.model.isChanged() || this.model.isSimple());
	  }
	  isShowableErrors() {
	    return this.isEnabledEmptyProductError() || this.isEnabledEmptyImagesError();
	  }
	  isEnabledEmptyProductError() {
	    return this.getConfig('ENABLE_EMPTY_PRODUCT_ERROR', false);
	  }
	  isEnabledEmptyImagesError() {
	    return this.getConfig('ENABLE_EMPTY_IMAGES_ERROR', false);
	  }
	  isEnabledChangesRendering() {
	    return this.getConfig('ENABLE_CHANGES_RENDERING', true);
	  }
	  isInputDetailLinkEnabled() {
	    return this.getConfig('ENABLE_INPUT_DETAIL_LINK', false) && main_core.Type.isStringFilled(this.model.getDetailPath()) && this.checkProductViewRights();
	  }
	  getWrapper() {
	    if (!this.wrapper) {
	      this.wrapper = document.getElementById(this.id);
	    }
	    return this.wrapper;
	  }
	  renderTo(node) {
	    this.clearLayout();
	    this.wrapper = node;
	    this.layout();
	  }
	  layout() {
	    const wrapper = this.getWrapper();
	    if (!wrapper) {
	      return;
	    }
	    this.defineWrapperClass(wrapper);
	    wrapper.innerHTML = '';
	    const block = main_core.Tag.render(_t$6 || (_t$6 = _$6`<div class="catalog-product-field-inner"></div>`));
	    main_core.Dom.append(this.layoutNameBlock(), block);
	    if (this.getSkuTreeInstance()) {
	      main_core.Dom.append(this.getSkuTreeInstance().layout(), block);
	    }
	    main_core.Dom.append(this.getErrorContainer(), block);
	    if (!this.isViewMode()) {
	      main_core.Dom.append(block, wrapper);
	    }
	    if (this.isImageFieldEnabled()) {
	      if (!main_core.Reflection.getClass('BX.UI.ImageInput')) {
	        if (ProductSelector.UIInputRequest instanceof Promise) {
	          ProductSelector.UIInputRequest.then(() => {
	            this.layoutImage();
	          });
	        } else {
	          ProductSelector.UIInputRequest = new Promise(resolve => {
	            main_core.ajax.runAction('catalog.productSelector.getFileInput', {
	              json: {
	                iblockId: this.getModel().getIblockId()
	              }
	            }).then(() => {
	              this.layoutImage();
	              ProductSelector.UIInputRequest = null;
	              resolve();
	            });
	          });
	        }
	      } else {
	        this.layoutImage();
	      }
	      main_core.Dom.append(this.getImageContainer(), wrapper);
	    }
	    if (this.isViewMode()) {
	      main_core.Dom.append(block, wrapper);
	    }
	    if (this.isViewMode()) {
	      main_core.Dom.append(block, wrapper);
	    }
	    if (this.isShowableErrors) {
	      this.layoutErrors();
	    }
	    this.subscribeToVariationChange();
	  }
	  focusName() {
	    if (this.searchInput) {
	      this.searchInput.focusName();
	    }
	    return this;
	  }
	  getImageContainer() {
	    return this.cache.remember('imageContainer', () => main_core.Tag.render(_t2$5 || (_t2$5 = _$6`<div class="catalog-product-img"></div>`)));
	  }
	  getErrorContainer() {
	    return this.cache.remember('errorContainer', () => main_core.Tag.render(_t3$5 || (_t3$5 = _$6`<div class="catalog-product-error"></div>`)));
	  }
	  layoutErrors() {
	    this.getErrorContainer().innerHTML = '';
	    this.clearImageErrorBorder();
	    if (!this.model.getErrorCollection().hasErrors()) {
	      return;
	    }
	    const errors = this.model.getErrorCollection().getErrors();
	    for (const code in errors) {
	      if (!ProductSelector.ErrorCodes.getCodes().includes(code)) {
	        continue;
	      }
	      if (code === 'EMPTY_IMAGE') {
	        this.setImageErrorBorder();
	      } else {
	        main_core.Dom.append(main_core.Tag.render(_t4$4 || (_t4$4 = _$6`<div class="catalog-product-error-item">${0}</div>`), errors[code].text), this.getErrorContainer());
	        if (this.searchInput) {
	          main_core.Dom.addClass(this.searchInput.getNameBlock(), 'ui-ctl-danger');
	        }
	      }
	    }
	  }
	  setImageErrorBorder() {
	    main_core.Dom.addClass(this.getImageContainer().querySelector('.adm-fileinput-area'), 'adm-fileinput-drag-area-error');
	  }
	  clearImageErrorBorder() {
	    main_core.Dom.removeClass(this.getImageContainer().querySelector('.adm-fileinput-area'), 'adm-fileinput-drag-area-error');
	  }
	  onUploaderIsInited() {
	    if (this.isEnabledEmptyImagesError()) {
	      requestAnimationFrame(this.layoutErrors.bind(this));
	    }
	  }
	  layoutImage() {
	    this.getImageContainer().innerHTML = '';
	    main_core.Dom.append(this.getFileInput().layout(), this.getImageContainer());
	    this.refreshImageSelectorId = null;
	  }
	  clearState() {
	    this.getModel().initFields({
	      ID: '',
	      NAME: '',
	      BARCODE: '',
	      PRODUCT_ID: null,
	      SKU_ID: null
	    }).setOption('isNew', false);
	    this.getFileInput().restoreDefaultInputHtml();
	    this.getModel().clearSkuTree();
	    this.skuTreeInstance = null;
	    this.getModel().getStoreCollection().clear();
	  }
	  clearLayout() {
	    const wrapper = this.getWrapper();
	    if (wrapper) {
	      wrapper.innerHTML = '';
	    }
	    this.unsubscribeToVariationChange();
	  }
	  subscribeEvents() {
	    main_core_events.EventEmitter.subscribe('ProductList::onChangeFields', this.onChangeFieldsHandler);
	    main_core_events.EventEmitter.subscribe('ProductSelector::onNameChange', this.onNameChangeFieldHandler);
	    main_core_events.EventEmitter.subscribe('Catalog.ImageInput::save', this.onSaveImageHandler);
	    main_core_events.EventEmitter.subscribe('onUploaderIsInited', this.onUploaderIsInitedHandler);
	  }
	  unsubscribeEvents() {
	    this.unsubscribeToVariationChange();
	    main_core_events.EventEmitter.unsubscribe('Catalog.ImageInput::save', this.onSaveImageHandler);
	    main_core_events.EventEmitter.unsubscribe('ProductList::onChangeFields', this.onChangeFieldsHandler);
	    main_core_events.EventEmitter.unsubscribe('onUploaderIsInited', this.onUploaderIsInitedHandler);
	    main_core_events.EventEmitter.unsubscribe('onUploaderIsInited', this.onUploaderIsInitedHandler);
	    main_core_events.EventEmitter.unsubscribe('ProductSelector::onNameChange', this.onNameChangeFieldHandler);
	  }
	  defineWrapperClass(wrapper) {
	    if (this.isViewMode()) {
	      main_core.Dom.addClass(wrapper, 'catalog-product-view');
	      main_core.Dom.removeClass(wrapper, 'catalog-product-edit');
	      if (this.isShortViewFormat()) {
	        main_core.Dom.addClass(wrapper, '--short-format');
	      }
	    } else {
	      main_core.Dom.addClass(wrapper, 'catalog-product-edit');
	      main_core.Dom.removeClass(wrapper, 'catalog-product-view');
	    }
	  }
	  getNameBlockView() {
	    const productName = main_core.Text.encode(this.model.getField('NAME'));
	    const namePlaceholder = main_core.Loc.getMessage('CATALOG_SELECTOR_VIEW_NAME_TITLE');
	    if (this.getModel().getDetailPath()) {
	      return main_core.Tag.render(_t5$4 || (_t5$4 = _$6`
				<a href="${0}" title="${0}">${0}</a>
			`), this.getModel().getDetailPath(), namePlaceholder, productName);
	    }
	    return main_core.Tag.render(_t6$3 || (_t6$3 = _$6`<span title="${0}">${0}</span>`), namePlaceholder, productName);
	  }
	  getNameInputFilledValue() {
	    if (this.searchInput) {
	      return this.searchInput.getFilledValue();
	    }
	    return '';
	  }
	  layoutNameBlock() {
	    const block = main_core.Tag.render(_t7$3 || (_t7$3 = _$6`<div class="catalog-product-field-input"></div>`));
	    if (this.isViewMode()) {
	      main_core.Dom.append(this.getNameBlockView(), block);
	    } else {
	      if (this.getType() === ProductSelector.INPUT_FIELD_BARCODE) {
	        if (!this.searchInput) {
	          this.searchInput = new BarcodeSearchInput(this.id, {
	            selector: this,
	            model: this.getModel(),
	            inputName: this.options.inputFieldName
	          });
	        }
	      } else {
	        this.searchInput = new ProductSearchInput(this.id, {
	          selector: this,
	          model: this.getModel(),
	          inputName: this.options.inputFieldName,
	          isSearchEnabled: this.isProductSearchEnabled(),
	          isEnabledEmptyProductError: this.isEnabledEmptyProductError(),
	          isEnabledDetailLink: this.isInputDetailLinkEnabled()
	        });
	      }
	      main_core.Dom.append(this.searchInput.layout(), block);
	    }
	    return block;
	  }
	  searchInDialog() {
	    this.searchInput.searchInDialog();
	    return this;
	  }
	  updateSkuTree(tree) {
	    this.getModel().setSkuTree(tree);
	    this.skuTreeInstance = null;
	    return this;
	  }
	  getIblockSkuTreeProperties() {
	    return new Promise(resolve => {
	      if (iblockSkuTreeProperties.has(this.getModel().getIblockId())) {
	        resolve(iblockSkuTreeProperties.get(this.getModel().getIblockId()));
	      } else {
	        main_core.ajax.runAction('catalog.productSelector.getSkuTreeProperties', {
	          json: {
	            iblockId: this.getModel().getIblockId()
	          }
	        }).then(response => {
	          iblockSkuTreeProperties.set(this.getModel().getIblockId(), response);
	          resolve(response);
	        });
	      }
	    });
	  }
	  getSkuTreeInstance() {
	    var _this$getModel;
	    if (this.isSkuTreeEnabled() && (_this$getModel = this.getModel()) != null && _this$getModel.getSkuTree() && !this.skuTreeInstance) {
	      this.skuTreeInstance = new catalog_skuTree.SkuTree({
	        skuTree: this.getModel().getSkuTree(),
	        selectable: this.getConfig('ENABLE_SKU_SELECTION', true),
	        hideUnselected: this.getConfig('HIDE_UNSELECTED_ITEMS', false),
	        isShortView: this.isViewMode() && this.isShortViewFormat()
	      });
	    }
	    return this.skuTreeInstance;
	  }
	  subscribeToVariationChange() {
	    const skuTree = this.getSkuTreeInstance();
	    if (skuTree) {
	      this.unsubscribeToVariationChange();
	      skuTree.subscribe('SkuProperty::onChange', this.variationChangeHandler);
	    }
	  }
	  unsubscribeToVariationChange() {
	    const skuTree = this.getSkuTreeInstance();
	    if (skuTree) {
	      skuTree.unsubscribe('SkuProperty::onChange', this.variationChangeHandler);
	    }
	  }
	  handleVariationChange(event) {
	    const [skuFields] = event.getData();
	    const productId = main_core.Text.toNumber(skuFields.PARENT_PRODUCT_ID);
	    const variationId = main_core.Text.toNumber(skuFields.ID);
	    if (productId <= 0 || variationId <= 0) {
	      return;
	    }
	    this.emit('onBeforeChange', {
	      selectorId: this.getId(),
	      rowId: this.getRowId()
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _inAjaxProcess)[_inAjaxProcess] = true;
	    main_core.ajax.runAction('catalog.productSelector.getSelectedSku', {
	      json: {
	        variationId,
	        options: {
	          priceId: this.basePriceId,
	          currency: this.model.getCurrency(),
	          urlBuilder: this.getConfig('URL_BUILDER_CONTEXT')
	        }
	      }
	    }).then(response => this.processResponse(response, {
	      ...this.options.config
	    }));
	  }
	  onChangeFields(event) {
	    const eventData = event.getData();
	    if (eventData.rowId !== this.getRowId()) {
	      return;
	    }
	    const fields = eventData.fields;
	    this.getModel().setFields(fields);
	  }
	  reloadFileInput() {
	    var _this$getModel2;
	    main_core.ajax.runAction('catalog.productSelector.getFileInput', {
	      json: {
	        iblockId: this.getModel().getIblockId(),
	        skuId: (_this$getModel2 = this.getModel()) == null ? void 0 : _this$getModel2.getSkuId()
	      }
	    }).then(event => {
	      this.getModel().getImageCollection().setEditInput(event.data.html);
	      if (this.isImageFieldEnabled()) {
	        this.layoutImage();
	      }
	    });
	  }
	  onNameChange(event) {
	    const eventData = event.getData();
	    if (eventData.rowId !== this.getRowId() || !this.isEnabledAutosave()) {
	      return;
	    }
	    const fields = eventData.fields;
	    this.getModel().setFields(fields);
	    this.getModel().save().then(() => {
	      BX.UI.Notification.Center.notify({
	        id: 'saving_field_notify_name',
	        closeButton: false,
	        content: main_core.Tag.render(_t8$3 || (_t8$3 = _$6`<div>${0}</div>`), main_core.Loc.getMessage('CATALOG_SELECTOR_SAVING_NOTIFICATION_NAME_CHANGED')),
	        autoHide: true
	      });
	    });
	  }
	  onSaveImage(event) {
	    const [, inputId, response] = event.getData();
	    if (inputId !== this.getFileInput().getId()) {
	      return;
	    }
	    this.getFileInput().setId(response.data.id);
	    this.getFileInput().setInputHtml(response.data.input);
	    this.getFileInput().setView(response.data.preview);
	    this.getModel().getImageCollection().setMorePhotoValues(response.data.values);
	    if (this.isImageFieldEnabled()) {
	      this.layoutImage();
	    }
	    this.emit('onChange', {
	      selectorId: this.id,
	      rowId: this.getRowId(),
	      fields: this.getModel().getFields(),
	      morePhoto: this.getModel().getImageCollection().getMorePhotoValues()
	    });
	  }
	  inProcess() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _inAjaxProcess)[_inAjaxProcess];
	  }
	  onProductSelect(productId, itemConfig) {
	    this.emit('onProductSelect', {
	      selectorId: this.getId(),
	      rowId: this.getRowId()
	    });
	    this.emit('onBeforeChange', {
	      selectorId: this.getId(),
	      rowId: this.getRowId()
	    });
	    this.productSelectAjaxAction(productId, itemConfig);
	  }
	  productSelectAjaxAction(productId, itemConfig = {
	    isNew: false,
	    immutableFields: []
	  }) {
	    babelHelpers.classPrivateFieldLooseBase(this, _inAjaxProcess)[_inAjaxProcess] = true;
	    main_core.ajax.runAction('catalog.productSelector.getProduct', {
	      json: {
	        productId,
	        options: {
	          priceId: this.basePriceId,
	          currency: this.model.getCurrency(),
	          urlBuilder: this.getConfig('URL_BUILDER_CONTEXT')
	        }
	      }
	    }).then(response => this.processResponse(response, {
	      ...this.options.config,
	      ...itemConfig
	    }, true));
	  }
	  processResponse(response, config = {}, isProductAction = false) {
	    const data = (response == null ? void 0 : response.data) || null;
	    babelHelpers.classPrivateFieldLooseBase(this, _inAjaxProcess)[_inAjaxProcess] = false;
	    const fields = (data == null ? void 0 : data.fields) || [];
	    if (main_core.Type.isArray(config.immutableFields)) {
	      config.immutableFields.forEach(field => {
	        fields[field] = this.getModel().getField(field);
	      });
	      data.fields = fields;
	    }
	    if (isProductAction) {
	      this.clearState();
	    }
	    if (data) {
	      this.changeSelectedElement(data, config);
	    } else if (!isProductAction) {
	      this.productSelectAjaxAction(this.getModel().getProductId());
	    }
	    this.unsubscribeToVariationChange();
	    if (this.isEnabledChangesRendering()) {
	      this.clearLayout();
	      this.layout();
	    }
	    this.emit('onChange', {
	      selectorId: this.id,
	      rowId: this.getRowId(),
	      isNew: config.isNew || false,
	      fields,
	      morePhoto: this.getModel().getImageCollection().getMorePhotoValues()
	    });
	  }
	  changeSelectedElement(data, config) {
	    const productId = main_core.Text.toInteger(data.productId);
	    const productChanged = this.getModel().getProductId() !== productId;
	    if (productChanged) {
	      this.getModel().setOption('productId', productId);
	      this.getModel().setOption('skuId', main_core.Text.toInteger(data.skuId));
	      this.getModel().setOption('isSimpleModel', false);
	      this.getModel().setOption('isNew', config.isNew);
	    }
	    this.getModel().initFields(data.fields);
	    const imageField = {
	      id: '',
	      input: '',
	      preview: '',
	      values: []
	    };
	    if (main_core.Type.isObject(data.image)) {
	      imageField.id = data.image.id;
	      imageField.input = data.image.input;
	      imageField.preview = data.image.preview;
	      imageField.values = data.image.values;
	    }
	    this.getFileInput().setId(imageField.id);
	    this.getFileInput().setInputHtml(imageField.input);
	    this.getFileInput().setView(imageField.preview);
	    this.getModel().getImageCollection().setMorePhotoValues(imageField.values);
	    this.checkEmptyImageError();
	    if (data.detailUrl) {
	      this.getModel().setDetailPath(data.detailUrl);
	    }
	    if (main_core.Type.isObject(data.skuTree)) {
	      this.updateSkuTree(data.skuTree);
	    }
	  }
	  checkEmptyImageError() {
	    if (!main_core.Type.isArrayFilled(this.getModel().getImageCollection().getMorePhotoValues()) && this.isEnabledEmptyImagesError()) {
	      this.getModel().getErrorCollection().setError('EMPTY_IMAGE', main_core.Loc.getMessage('CATALOG_SELECTOR_EMPTY_IMAGE_ERROR'));
	    } else {
	      this.getModel().getErrorCollection().removeError('EMPTY_IMAGE');
	    }
	  }
	  removeSpotlight() {
	    var _this$searchInput;
	    (_this$searchInput = this.searchInput) == null ? void 0 : _this$searchInput.removeSpotlight();
	    this.setConfig('ENABLE_INFO_SPOTLIGHT', false);
	  }
	  removeQrAuth() {
	    var _this$searchInput2;
	    (_this$searchInput2 = this.searchInput) == null ? void 0 : _this$searchInput2.removeQrAuth();
	    this.setConfig('ENABLE_BARCODE_QR_AUTH', false);
	  }
	}
	ProductSelector.MODE_VIEW = 'view';
	ProductSelector.MODE_EDIT = 'edit';
	ProductSelector.SHORT_VIEW_FORMAT = 'short';
	ProductSelector.FULL_VIEW_FORMAT = 'full';
	ProductSelector.INPUT_FIELD_NAME = 'NAME';
	ProductSelector.INPUT_FIELD_BARCODE = 'BARCODE';
	ProductSelector.ErrorCodes = SelectorErrorCode;
	ProductSelector.UIInputRequest = null;

	exports.ProductSelector = ProductSelector;

}((this.BX.Catalog = this.BX.Catalog || {}),BX,BX,BX,BX.Catalog.SkuTree,BX,BX,BX.UI.EntitySelector,BX.Catalog,BX.Catalog,BX.Catalog,BX,BX,BX.Event,BX.UI,BX,BX.UI.Tour));
//# sourceMappingURL=product-selector.bundle.js.map
