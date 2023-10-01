this.BX = this.BX || {};
this.BX.Catalog = this.BX.Catalog || {};
this.BX.Catalog.EntityEditor = this.BX.Catalog.EntityEditor || {};
(function (exports,ui_entitySelector,main_core,main_core_events) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3;
	class Footer extends ui_entitySelector.DefaultFooter {
	  constructor(dialog, options) {
	    super(dialog, options);
	    this.selectAllButton = main_core.Tag.render(_t || (_t = _`<div class="ui-selector-footer-link ui-selector-search-footer-label--hide">${0}</div>`), main_core.Loc.getMessage('ENTITY_EDITOR_PRODUCT_SET_ALL_SELECT_LABEL'));
	    main_core.Event.bind(this.selectAllButton, 'click', this.selectAll.bind(this));
	    this.deselectAllButton = main_core.Tag.render(_t2 || (_t2 = _`<div class="ui-selector-footer-link ui-selector-search-footer-label--hide">${0}</div>`), main_core.Loc.getMessage('ENTITY_EDITOR_PRODUCT_SET_ALL_DESELECT_LABEL'));
	    main_core.Event.bind(this.deselectAllButton, 'click', this.deselectAll.bind(this));
	    this.getDialog().subscribe('Item:onSelect', this.onItemStatusChange.bind(this));
	    this.getDialog().subscribe('Item:onDeselect', this.onItemStatusChange.bind(this));
	    this.getDialog().subscribe('onLoad', this.toggleSelectButtons.bind(this));
	  }
	  getContent() {
	    return main_core.Tag.render(_t3 || (_t3 = _`
			<div class="ui-selector-search-footer-box">
				${0}
				${0}
			</div>
		`), this.selectAllButton, this.deselectAllButton);
	  }
	  toggleSelectButtons() {
	    if (this.getAmountSelectedItems() === this.getAmountItems()) {
	      if (main_core.Dom.hasClass(this.deselectAllButton, 'ui-selector-search-footer-label--hide')) {
	        main_core.Dom.addClass(this.selectAllButton, 'ui-selector-search-footer-label--hide');
	        main_core.Dom.removeClass(this.deselectAllButton, 'ui-selector-search-footer-label--hide');
	      }
	    } else if (main_core.Dom.hasClass(this.selectAllButton, 'ui-selector-search-footer-label--hide')) {
	      main_core.Dom.addClass(this.deselectAllButton, 'ui-selector-search-footer-label--hide');
	      main_core.Dom.removeClass(this.selectAllButton, 'ui-selector-search-footer-label--hide');
	    }
	  }
	  selectAll() {
	    if (this.getAmountSelectedItems() === this.getAmountItems()) {
	      return;
	    }
	    const activeTab = this.getDialog().getActiveTab();
	    if (activeTab) {
	      let children = activeTab.getRootNode().getChildren();
	      if (children.items.length > 0) {
	        children.forEach(child => {
	          child.getItem().select();
	        });
	      }
	    } else {
	      this.getDialog().getItems().forEach(item => {
	        item.select();
	      });
	    }
	  }
	  deselectAll() {
	    this.getDialog().getSelectedItems().forEach(item => {
	      item.deselect();
	    });
	  }
	  onItemStatusChange() {
	    this.toggleSelectButtons();
	  }
	  getAmountSelectedItems() {
	    let amount = 0;
	    const activeTab = this.getDialog().getActiveTab();
	    if (activeTab) {
	      amount = activeTab.getRootNode().getChildren().items.filter(item => item.item.isSelected()).length;
	    } else {
	      amount = this.getDialog().getSelectedItems().length;
	    }
	    return amount;
	  }
	  getAmountItems() {
	    let amount = 0;
	    const activeTab = this.getDialog().getActiveTab();
	    if (activeTab) {
	      amount = activeTab.getRootNode().getChildren().items.length;
	    } else {
	      amount = this.getDialog().getItems().length;
	    }
	    return amount;
	  }
	}

	const Const = Object.freeze({
	  TYPE: {
	    PRODUCT: 'PRODUCT'
	  },
	  ENTITY_ID: {
	    PRODUCT_VARIATION: 'agent-contractor-product-variation'
	  }
	});

	let _$1 = t => t,
	  _t$1,
	  _t2$1,
	  _t3$1,
	  _t4;
	class ProductSetField extends BX.UI.EntityEditorField {
	  constructor(id, settings) {
	    super();
	    this.initialize(id, settings);
	    this._input = null;
	    this._inputWrapper = null;
	    this.innerWrapper = null;
	    this.entityList = null;
	    this.tagSelector = null;
	  }
	  getContentWrapper() {
	    return this.innerWrapper;
	  }
	  layout(options = {}) {
	    if (this._hasLayout) {
	      return;
	    }
	    this.ensureWrapperCreated({});
	    this.adjustWrapper();
	    let title = this.getTitle();
	    if (this.isDragEnabled()) {
	      this._wrapper.appendChild(this.createDragButton());
	    }
	    this._wrapper.appendChild(this.createTitleNode(title));
	    let name = this.getName();
	    let value = this.getValue();
	    if (!this.entityList) {
	      this.entityList = this.getEntityListFromModel();
	    }
	    this._inputWrapper = main_core.Tag.render(_t$1 || (_t$1 = _$1`<div></div>`));
	    this._wrapper.appendChild(this._inputWrapper);
	    let inputValue = '';
	    if (value.length > 0) {
	      inputValue = JSON.stringify(value);
	    }
	    this._input = main_core.Tag.render(_t2$1 || (_t2$1 = _$1`<input name="${0}" type="hidden" value=""/>`), name);
	    this._input.value = inputValue;
	    this._wrapper.appendChild(this._input);
	    this.innerWrapper = main_core.Tag.render(_t3$1 || (_t3$1 = _$1`<div class="ui-entity-editor-content-block"></div>`));
	    this._wrapper.appendChild(this.innerWrapper);
	    if (this._mode === BX.UI.EntityEditorMode.edit) {
	      this.getProductSelector(this.entityList);
	      this.tagSelector.setReadonly(false);
	      this.tagSelector.renderTo(this.innerWrapper);
	      if (BX.UI.EntityEditorModeOptions.check(this._modeOptions, BX.UI.EntityEditorModeOptions.individual)) {
	        this.tagSelector.getDialog().show();
	      }
	    } else
	      // if(this._mode === BX.UI.EntityEditorMode.view)
	      {
	        if (this.hasContentToDisplay()) {
	          this.getProductSelector(this.entityList, true);
	          this.tagSelector.setReadonly(true);
	          this.tagSelector.renderTo(this.innerWrapper);
	        } else {
	          const viewModeDisplay = main_core.Tag.render(_t4 || (_t4 = _$1`<div class="ui-entity-editor-content-block-text">${0}</div>`), main_core.Loc.getMessage('ENTITY_EDITOR_PRODUCT_SET_NOT_FILLED'));
	          this.innerWrapper.appendChild(viewModeDisplay);
	        }
	      }
	    if (this.isContextMenuEnabled()) {
	      this._wrapper.appendChild(this.createContextMenuButton());
	    }
	    if (this.isDragEnabled()) {
	      this.initializeDragDropAbilities();
	    }
	    this.registerLayout(options);
	    this._hasLayout = true;
	  }
	  getProductSelector(value) {
	    const iblockId = this.getIBlockIdFromModel();
	    if (!this.tagSelector) {
	      this.tagSelector = new ui_entitySelector.TagSelector({
	        textBoxWidth: '100%',
	        multiple: true,
	        dialogOptions: {
	          context: 'catalog_document_productset',
	          entities: [{
	            id: Const.ENTITY_ID.PRODUCT_VARIATION,
	            options: {
	              iblockId: iblockId
	            }
	          }],
	          searchOptions: {
	            allowCreateItem: false
	          },
	          events: {
	            'Item:onSelect': event => {
	              this.handleUserSelectorChanges(event);
	              this._changeHandler();
	            },
	            'Item:onDeselect': event => {
	              this.handleUserSelectorChanges(event);
	              this._changeHandler();
	            }
	          },
	          footer: Footer
	        }
	      });
	    }
	    if (this.tagSelector.getDialog() && value.length > 0) {
	      const dialog = this.tagSelector.getDialog();
	      value.forEach(item => {
	        dialog.addItem({
	          id: item.PRODUCT_ID,
	          title: item.PRODUCT_NAME,
	          avatar: item.IMAGE,
	          selected: true,
	          entityId: Const.ENTITY_ID.PRODUCT_VARIATION
	        });
	      });
	    }
	    return this.tagSelector;
	  }
	  handleUserSelectorChanges(event) {
	    this.entityList = [];
	    const values = [];
	    const selectedItems = event.getTarget().getSelectedItems();
	    selectedItems.forEach(item => {
	      values.push({
	        PRODUCT_ID: item.getId(),
	        PRODUCT_TYPE: Const.TYPE.PRODUCT
	      });
	      this.entityList.push({
	        PRODUCT_ID: item.getId(),
	        PRODUCT_TYPE: Const.TYPE.PRODUCT,
	        PRODUCT_NAME: item.getTitle()
	      });
	    });
	    this._input.value = JSON.stringify(values);
	  }
	  validate(result) {
	    if (!(this._mode === BX.UI.EntityEditorMode.edit && this._input)) {
	      throw "BX.Catalog.Entity-editor.Field.ProductSet. Invalid validation context";
	    }
	    this.clearError();
	    if (this.hasValidators()) {
	      return this.executeValidators(result);
	    }
	    let isValid = !(this.isRequired() || this.isRequiredByAttribute()) || BX.util.trim(this._input.value) !== "";
	    if (!isValid) {
	      result.addError(BX.UI.EntityValidationError.create({
	        field: this
	      }));
	      this.showRequiredFieldError(this._input);
	    }
	    return isValid;
	  }
	  hasValue() {
	    if (this.getValue().length === 0) {
	      return false;
	    }
	    return super.hasValue();
	  }
	  getModeSwitchType(mode) {
	    let result = BX.UI.EntityEditorModeSwitchType.common;
	    if (mode === BX.UI.EntityEditorMode.edit) {
	      result |= BX.UI.EntityEditorModeSwitchType.button | BX.UI.EntityEditorModeSwitchType.content;
	    }
	    return result;
	  }
	  getIBlockIdFromModel() {
	    return this._model.getSchemeField(this._schemeElement, 'iblockId', 0);
	  }
	  getEntityListFromModel() {
	    return this._model.getSchemeField(this._schemeElement, 'entityList', []);
	  }
	  rollback() {
	    this.entityList = this.getEntityListFromModel();
	  }
	  static create(id, settings) {
	    let self = new this(id, settings);
	    self.initialize(id, settings);
	    return self;
	  }
	}

	class ProductSetFieldFactory$$1 {
	  constructor(entityEditorControlFactory = 'BX.UI.EntityEditorControlFactory') {
	    main_core_events.EventEmitter.subscribe(entityEditorControlFactory + ':onInitialize', event => {
	      const [, eventArgs] = event.getCompatData();
	      eventArgs.methods['productSet'] = this.factory.bind(this);
	    });
	  }
	  factory(type, controlId, settings) {
	    if (type === 'productSet') {
	      return ProductSetField.create(controlId, settings);
	    }
	    return null;
	  }
	}

	exports.ProductSetField = ProductSetField;
	exports.ProductSetFieldFactory = ProductSetFieldFactory$$1;
	exports.Footer = Footer;

}((this.BX.Catalog.EntityEditor.Field = this.BX.Catalog.EntityEditor.Field || {}),BX.UI.EntitySelector,BX,BX.Event));
//# sourceMappingURL=productset.bundle.js.map
