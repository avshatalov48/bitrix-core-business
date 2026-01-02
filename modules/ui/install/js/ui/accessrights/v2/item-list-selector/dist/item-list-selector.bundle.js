/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
this.BX.UI.AccessRights = this.BX.UI.AccessRights || {};
(function (exports,main_popup,ui_buttons,ui_vue3,ui_vue3_directives_hint,ui_draganddrop_draggable,ui_iconSet_actions,ui_iconSet_api_vue,ui_iconSet_outline,ui_forms,main_core,ui_system_chip_vue) {
	'use strict';

	const Checkbox = {
	  name: 'Checkbox',
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon
	  },
	  props: {
	    status: {
	      type: String,
	      required: true,
	      validator: value => ['checked', 'indeterminate', 'unchecked'].includes(value)
	    }
	  },
	  computed: {
	    set() {
	      return ui_iconSet_api_vue.Outline;
	    },
	    iconName() {
	      return this.status === 'checked' ? this.set.CHECK_S : this.set.MINUS_S;
	    },
	    iconColor() {
	      return this.status === 'checked' ? 'var(--ui-color-bg-content-light)' : 'var(--ui-color-accent-main-primary)';
	    }
	  },
	  methods: {
	    handleClick() {
	      this.$emit('change');
	    }
	  },
	  template: `
		<label
		  class="ui-item-selector__checkbox"
		  :class="{ 
		    'ui-item-selector__checkbox--active': status === 'checked',
		    'ui-item-selector__checkbox--indeterminate': status === 'indeterminate'
		  }"
		  @click="handleClick"
		>
		  <BIcon
		    v-if="status !== 'unchecked'"
		    class="ui-item-selector-item__checked-icon"
		    :name="iconName"
		    :color="iconColor"
		    :size="22"
		  ></BIcon>
		</label>
	`
	};

	const Item = {
	  name: 'Item',
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon,
	    Checkbox
	  },
	  props: {
	    id: {
	      type: String,
	      required: true
	    },
	    title: {
	      type: String,
	      required: true
	    },
	    isSelected: {
	      type: Boolean
	    },
	    isDraggable: {
	      type: Boolean
	    },
	    hint: {
	      type: String,
	      default: null
	    }
	  },
	  directives: {
	    hint: ui_vue3_directives_hint.hint
	  },
	  methods: {
	    handleClick(event) {
	      if (!event.target.closest('.ui-item-selector-item__drag-icon')) {
	        this.$emit('update:selected', !this.isSelected);
	      }
	    }
	  },
	  computed: {
	    Outline() {
	      return ui_iconSet_api_vue.Outline;
	    },
	    checkboxStatus() {
	      return this.isSelected ? 'checked' : 'unchecked';
	    },
	    itemClasses() {
	      return {
	        'ui-item-selector-item--active': this.isSelected
	      };
	    }
	  },
	  template: `
		<div
			class="ui-item-selector-item"
			:class="itemClasses"
			@click="handleClick"
			:data-id="id"
			:data-selected="isSelected"
			v-hint="hint"
		>
			<BIcon
				class="ui-item-selector-item__drag-icon"
				:class="isDraggable ? 'ui-item-selector-item__drag_element' : ''"
				:name="Outline.DRAG_M"
				:color="isSelected ? 'var(--ui-color-accent-main-primary-alt)' : 'var(--ui-color-g-content-grey-2)'"
				:style="{ opacity: isDraggable ? '1' : '0' }"
			></BIcon>

			<span class="ui-item-selector-item__title" :title="title">
				{{ title }}
			</span>

			<Checkbox
				:status="checkboxStatus"
			/>
		</div>
	`
	};

	const Selector = {
	  name: 'Selector',
	  components: {
	    Item,
	    BIcon: ui_iconSet_api_vue.BIcon,
	    Checkbox,
	    Chip: ui_system_chip_vue.Chip
	  },
	  props: {
	    items: {
	      type: Array,
	      required: true
	    },
	    maxSelected: {
	      type: Number,
	      default: null
	    }
	  },
	  data() {
	    return {
	      localItems: this.items.map(item => ({
	        ...item
	      })),
	      searchQuery: '',
	      sortDirection: null
	    };
	  },
	  computed: {
	    selectedCount() {
	      return this.localItems.filter(item => item.selected).length;
	    },
	    filteredItems() {
	      let items = [...this.itemsBySearchQuery];
	      if (this.sortDirection) {
	        items.sort((item1, item2) => {
	          if (item1.selected !== item2.selected) {
	            return item1.selected ? -1 : 1;
	          }
	          const compare = item1.title.localeCompare(item2.title);
	          return this.sortDirection === 'asc' ? compare : -compare;
	        });
	      } else {
	        items.sort((item1, item2) => {
	          if (item1.selected !== item2.selected) {
	            return item1.selected ? -1 : 1;
	          }
	          if (!main_core.Type.isUndefined(item1.sort) && !main_core.Type.isUndefined(item2.sort)) {
	            const sortCompare = main_core.Text.toInteger(item1.sort) - main_core.Text.toInteger(item2.sort);
	            if (sortCompare !== 0) {
	              return sortCompare;
	            }
	          }
	          if (this.sortDirection) {
	            const titleCompare = item1.title.localeCompare(item2.title);
	            return this.sortDirection === 'asc' ? titleCompare : -titleCompare;
	          }
	          return 0;
	        });
	      }
	      return items;
	    },
	    itemsBySearchQuery() {
	      let items = [...this.localItems];
	      if (this.searchQuery) {
	        const query = this.searchQuery.toLowerCase();
	        items = items.filter(item => item.title.toLowerCase().includes(query));
	      }
	      return items;
	    },
	    hasActiveItems() {
	      return this.itemsBySearchQuery.some(item => item.selected);
	    },
	    allItemsActive() {
	      const items = this.itemsBySearchQuery;
	      return items.length > 0 && items.every(item => item.selected);
	    },
	    toggleAllStatus() {
	      return this.allItemsActive ? 'checked' : this.hasActiveItems ? 'indeterminate' : 'unchecked';
	    },
	    selectionLimitReached() {
	      return this.selectedCount >= this.maxSelected;
	    },
	    Outline() {
	      return ui_iconSet_api_vue.Outline;
	    },
	    ChipDesign() {
	      return ui_system_chip_vue.ChipDesign;
	    }
	  },
	  mounted() {
	    this.initDragAndDrop();
	  },
	  methods: {
	    handleItemSelect(itemId) {
	      const currentItem = this.localItems.find(item => item.id === itemId);
	      if (currentItem.selected) {
	        currentItem.selected = false;
	        return;
	      }
	      if (!currentItem.selected && this.maxSelected !== null && this.selectionLimitReached) {
	        return;
	      }
	      currentItem.selected = true;
	    },
	    initDragAndDrop() {
	      new ui_draganddrop_draggable.Draggable({
	        container: this.$refs['items-container'].$el,
	        draggable: '.ui-item-selector-item',
	        dragElement: '.ui-item-selector-item__drag_element'
	      }).subscribe('end', this.onDragEnd.bind(this));
	    },
	    onDragEnd() {
	      const itemsMap = this.getCurrentOrder();
	      this.localItems = this.localItems.map(localItem => {
	        const draggedItem = itemsMap.get(localItem.id);
	        return draggedItem ? {
	          ...localItem,
	          sort: draggedItem.sort
	        } : localItem;
	      });
	    },
	    toggleAllItems() {
	      const shouldActivate = !this.hasActiveItems;
	      this.itemsBySearchQuery.forEach(item => {
	        item.selected = shouldActivate;
	      });
	    },
	    toggleSort() {
	      if (this.sortDirection === 'asc') {
	        this.sortDirection = 'desc';
	      } else {
	        this.sortDirection = 'asc';
	      }
	    },
	    getCurrentOrder() {
	      return new Map([...this.$el.querySelectorAll('.ui-item-selector-item')].map((itemElement, index) => [itemElement.dataset.id, {
	        id: itemElement.dataset.id,
	        sort: index + 1,
	        selected: itemElement.dataset.selected === 'true'
	      }]));
	    },
	    getOrder() {
	      this.searchQuery = '';
	      this.filteredItems.forEach((item, index) => {
	        item.sort = index;
	      });
	      return this.filteredItems;
	    }
	  },
	  template: `
		<div class="ui-item-selector">
			<div class="ui-item-selector-controls">
				<div class="ui-ctl ui-ctl-after-icon ui-ctl-w100">
					<input
						type="text"
						class="ui-ctl-element ui-ctl-textbox ui-item-selector-search__input"
						:placeholder="$Bitrix.Loc.getMessage('JS_UI_ITEM_SELECTOR_SEARCH_PLACEHOLDER')"
						v-model="searchQuery"
					>
					<BIcon
						class="ui-item-selector-search-icon"
						:name="Outline.SEARCH"
						:size="20"
						color="var(--ui-color-base-4)"
					></BIcon>
				</div>

				<Chip
					:icon="sortDirection === 'asc' ? Outline.LETTER_SORT_DOWN : Outline.LETTER_SORT_UP"
					:design="sortDirection ? ChipDesign.OutlineAccent : ChipDesign.Outline"
					@click="toggleSort"
				/>

				<Checkbox
					:status="toggleAllStatus"
					@change="toggleAllItems"
				/>
			</div>

			<TransitionGroup tag="div" name="ui-item-selector-items" class="ui-item-selector-items" ref="items-container">
				<Item
					v-for="(item) in filteredItems"
					:key="item.id"
					:id="item.id"
					:title="item.title"
					:isSelected="item.selected"
					:isDraggable="!this.searchQuery"
					@update:selected="handleItemSelect(item.id)"
				/>
			</TransitionGroup>
		</div>
	`
	};

	let _ = t => t,
	  _t,
	  _t2;
	var _targetNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("targetNode");
	var _content = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("content");
	var _items = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("items");
	var _app = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("app");
	var _title = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("title");
	var _subtitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subtitle");
	var _events = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("events");
	var _maxSelected = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("maxSelected");
	var _popup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popup");
	var _rootComponent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("rootComponent");
	var _getPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopup");
	var _createSelector = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createSelector");
	var _getContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getContent");
	var _save = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("save");
	var _getHeader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getHeader");
	class ItemListSelector {
	  constructor(params) {
	    Object.defineProperty(this, _getHeader, {
	      value: _getHeader2
	    });
	    Object.defineProperty(this, _save, {
	      value: _save2
	    });
	    Object.defineProperty(this, _getContent, {
	      value: _getContent2
	    });
	    Object.defineProperty(this, _createSelector, {
	      value: _createSelector2
	    });
	    Object.defineProperty(this, _getPopup, {
	      value: _getPopup2
	    });
	    Object.defineProperty(this, _targetNode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _content, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _items, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _app, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _title, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _subtitle, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _events, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _maxSelected, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _popup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _rootComponent, {
	      writable: true,
	      value: void 0
	    });
	    if (!main_core.Type.isDomNode(params.targetNode)) {
	      throw new Error('Target DOM node not found');
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _targetNode)[_targetNode] = params.targetNode;
	    babelHelpers.classPrivateFieldLooseBase(this, _items)[_items] = params.items;
	    babelHelpers.classPrivateFieldLooseBase(this, _title)[_title] = params.title;
	    babelHelpers.classPrivateFieldLooseBase(this, _subtitle)[_subtitle] = params.subtitle;
	    babelHelpers.classPrivateFieldLooseBase(this, _events)[_events] = params.events;
	    babelHelpers.classPrivateFieldLooseBase(this, _maxSelected)[_maxSelected] = main_core.Type.isInteger(params.maxSelected) ? params.maxSelected : null;
	  }
	  show() {
	    babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup]().show();
	    babelHelpers.classPrivateFieldLooseBase(this, _createSelector)[_createSelector]();
	  }
	  hide() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _app)[_app]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _app)[_app].unmount();
	      babelHelpers.classPrivateFieldLooseBase(this, _app)[_app] = null;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _events)[_events].onHide) {
	      babelHelpers.classPrivateFieldLooseBase(this, _events)[_events].onHide();
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].destroy();
	  }
	  getSelectedItems() {
	    var _this$getCurrentOrder;
	    return (_this$getCurrentOrder = this.getCurrentOrder().filter(item => item.selected)) != null ? _this$getCurrentOrder : [];
	  }
	  getCurrentOrder() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _rootComponent)[_rootComponent]) {
	      return [];
	    }
	    const items = [...babelHelpers.classPrivateFieldLooseBase(this, _rootComponent)[_rootComponent].getOrder().values()];
	    items.sort((item1, item2) => {
	      var _item1$sort, _item2$sort;
	      const sortA = (_item1$sort = item1.sort) != null ? _item1$sort : Infinity;
	      const sortB = (_item2$sort = item2.sort) != null ? _item2$sort : Infinity;
	      return sortA - sortB;
	    });
	    return items;
	  }
	}
	function _getPopup2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup];
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup] = new main_popup.Popup({
	    width: 409,
	    fixed: true,
	    autoHide: true,
	    closeByEsc: true,
	    contentPadding: 0,
	    padding: 24,
	    className: 'ui-item-selector-popup',
	    bindElement: babelHelpers.classPrivateFieldLooseBase(this, _targetNode)[_targetNode],
	    titleBar: {
	      content: babelHelpers.classPrivateFieldLooseBase(this, _getHeader)[_getHeader]()
	    },
	    content: babelHelpers.classPrivateFieldLooseBase(this, _getContent)[_getContent](),
	    buttons: [new ui_buttons.SaveButton({
	      onclick: babelHelpers.classPrivateFieldLooseBase(this, _save)[_save].bind(this),
	      size: ui_buttons.ButtonSize.LARGE,
	      style: ui_buttons.AirButtonStyle.FILLED,
	      useAirDesign: true
	    }), new ui_buttons.CancelButton({
	      onclick: this.hide.bind(this),
	      size: ui_buttons.ButtonSize.LARGE,
	      style: ui_buttons.AirButtonStyle.OUTLINE,
	      useAirDesign: true
	    })],
	    events: {
	      onClose: this.hide.bind(this)
	    }
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup];
	}
	function _createSelector2() {
	  const rootProps = {
	    items: babelHelpers.classPrivateFieldLooseBase(this, _items)[_items],
	    maxSelected: babelHelpers.classPrivateFieldLooseBase(this, _maxSelected)[_maxSelected]
	  };
	  babelHelpers.classPrivateFieldLooseBase(this, _app)[_app] = ui_vue3.BitrixVue.createApp(Selector, rootProps);
	  babelHelpers.classPrivateFieldLooseBase(this, _rootComponent)[_rootComponent] = babelHelpers.classPrivateFieldLooseBase(this, _app)[_app].mount(babelHelpers.classPrivateFieldLooseBase(this, _getContent)[_getContent]());
	}
	function _getContent2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _content)[_content]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _content)[_content] = main_core.Tag.render(_t || (_t = _`
				<div class="ui-item-selector-content"></div>
			`));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _content)[_content];
	}
	function _save2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _events)[_events].onSave) {
	    babelHelpers.classPrivateFieldLooseBase(this, _events)[_events].onSave();
	  }
	  this.hide();
	}
	function _getHeader2() {
	  return main_core.Tag.render(_t2 || (_t2 = _`
			<div class="ui-item-selector-header">
				<div class="ui-item-selector-header-title">${0}</div>
				<div class="ui-item-selector-header-subtitle">${0}</div>
			</div>
		`), main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _title)[_title]), main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _subtitle)[_subtitle]));
	}

	exports.ItemListSelector = ItemListSelector;

}((this.BX.UI.AccessRights.V2 = this.BX.UI.AccessRights.V2 || {}),BX.Main,BX.UI,BX.Vue3,BX.Vue3.Directives,BX.UI.DragAndDrop,BX,BX.UI.IconSet,BX,BX,BX,BX.UI.System.Chip.Vue));
//# sourceMappingURL=item-list-selector.bundle.js.map
