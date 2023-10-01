this.BX = this.BX || {};
this.BX.Catalog = this.BX.Catalog || {};
this.BX.Catalog.EntityEditor = this.BX.Catalog.EntityEditor || {};
(function (exports,ui_entitySelector,catalog_entityEditor_field_productset,main_core_events) {
	'use strict';

	const Const = Object.freeze({
	  TYPE: {
	    SECTION: 'SECTION'
	  },
	  ENTITY_ID: {
	    SECTION: 'agent-contractor-section'
	  }
	});

	class SectionSetField extends catalog_entityEditor_field_productset.ProductSetField {
	  constructor(id, settings) {
	    super(id, settings);
	    this.initialize(id, settings);
	    this._input = null;
	    this._inputWrapper = null;
	    this.innerWrapper = null;
	    this.entityList = null;
	    this.tagSelector = null;
	  }
	  getProductSelector(value) {
	    const iblockId = this.getIBlockIdFromModel();
	    if (!this.tagSelector) {
	      this.tagSelector = new ui_entitySelector.TagSelector({
	        // items: currentSelectedItems,
	        textBoxWidth: '100%',
	        multiple: true,
	        dialogOptions: {
	          context: 'catalog_document_sectionset',
	          entities: [{
	            id: Const.ENTITY_ID.SECTION,
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
	          footer: catalog_entityEditor_field_productset.Footer
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
	          entityId: Const.ENTITY_ID.SECTION
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
	        PRODUCT_TYPE: Const.TYPE.SECTION
	      });
	      this.entityList.push({
	        PRODUCT_ID: item.getId(),
	        PRODUCT_TYPE: Const.TYPE.SECTION,
	        PRODUCT_NAME: item.getTitle()
	      });
	    });
	    this._input.value = JSON.stringify(values);
	  }
	  static create(id, settings) {
	    const self = new this(id, settings);
	    self.initialize(id, settings);
	    return self;
	  }
	}

	class SectionSetFieldFactory$$1 {
	  constructor(entityEditorControlFactory = 'BX.UI.EntityEditorControlFactory') {
	    main_core_events.EventEmitter.subscribe(`${entityEditorControlFactory}:onInitialize`, event => {
	      const [, eventArgs] = event.getCompatData();
	      eventArgs.methods.sectionSet = this.factory.bind(this);
	    });
	  }
	  factory(type, controlId, settings) {
	    if (type === 'sectionSet') {
	      return SectionSetField.create(controlId, settings);
	    }
	    return null;
	  }
	}

	exports.SectionSetField = SectionSetField;
	exports.SectionSetFieldFactory = SectionSetFieldFactory$$1;

}((this.BX.Catalog.EntityEditor.Field = this.BX.Catalog.EntityEditor.Field || {}),BX.UI.EntitySelector,BX.Catalog.EntityEditor.Field,BX.Event));
//# sourceMappingURL=sectionset.bundle.js.map
