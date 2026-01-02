/* eslint-disable */
this.BX = this.BX || {};
(function (exports,ui_vue3,ui_vue3_components_hint,ui_feedback_form,ui_icons,ui_cnt,ui_vue3_components_counter,ui_advice,item,button,ui_vue3_pinia,group,main_popup,main_core_events,main_core,group$1) {
	'use strict';

	const feedback = {
	  beforeMount(element, bindings) {
	    main_core.Event.bind(element, 'click', event => {
	      event.preventDefault();
	      BX.UI.Feedback.Form.open(bindings.value);
	    });
	  }
	};

	const Group = {
	  emits: ['selected', 'unselected'],
	  name: 'ui-entity-catalog-group',
	  components: {
	    Counter: ui_vue3_components_counter.Counter
	  },
	  props: {
	    /** @type GroupData */
	    groupData: {
	      type: group.GroupData,
	      required: true
	    }
	  },
	  computed: {
	    hasIcon() {
	      return main_core.Type.isStringFilled(this.groupData.icon);
	    },
	    getCounterStyle() {
	      return ui_cnt.CounterStyle.FILLED_SUCCESS;
	    },
	    getCounterValue() {
	      var _this$groupData$custo, _this$groupData;
	      const custom = (_this$groupData$custo = (_this$groupData = this.groupData) == null ? void 0 : _this$groupData.customData) != null ? _this$groupData$custo : {};
	      const value = custom.counterValue;
	      const isValueInteger = Number.isInteger(value);
	      return isValueInteger && value > 0 ? value : null;
	    }
	  },
	  methods: {
	    handleClick() {
	      if (this.groupData.deselectable) {
	        this.$emit(!this.groupData.selected ? 'selected' : 'unselected', this.groupData);
	      } else if (!this.groupData.selected) {
	        this.$emit('selected', this.groupData);
	      }
	    }
	  },
	  template: `
		<slot name="group" v-bind:groupData="groupData" v-bind:handleClick="handleClick">
			<li
				:class="{
					'ui-entity-catalog__menu_item': true,
					'--active': groupData.selected,
					'--disabled': groupData.disabled,
				}"
				@click="handleClick"
			>
				<span class="ui-entity-catalog__menu_item-icon" v-if="hasIcon" v-html="groupData.icon"/>
				<span class="ui-entity-catalog__menu_item-text">{{ groupData.name }}</span>
				<span
					v-if="getCounterValue !== null"
					class="ui-entity-catalog__menu_item-entity-count"
				>
					<Counter
						:value="getCounterValue"
						:style="getCounterStyle"
					>
					</Counter>
				</span>
			</li>
		</slot>
	`
	};

	const GroupList = {
	  emits: ['groupSelected', 'groupUnselected'],
	  name: 'ui-entity-selector-group-list',
	  components: {
	    Group
	  },
	  props: {
	    /** @type Array<Array<GroupData>> */
	    groups: {
	      type: Array,
	      required: true
	    }
	  },
	  computed: {
	    groupLists() {
	      if (!this.groups || this.groups.length === 0) {
	        return [];
	      }
	      if (Array.isArray(this.groups[0])) {
	        return this.groups;
	      }
	      return [this.groups];
	    },
	    headerLists() {
	      return this.groupLists.map(list => list.filter(g => !!g.isHeaderGroup)).filter(list => list.length > 0);
	    },
	    mainLists() {
	      return this.groupLists.map(list => list.filter(g => !g.isHeaderGroup)).filter(list => list.length > 0);
	    }
	  },
	  methods: {
	    handleGroupSelected(group$$1) {
	      this.$emit('groupSelected', group$$1);
	    },
	    handleGroupUnselected(group$$1) {
	      this.$emit('groupUnselected', group$$1);
	    }
	  },
	  template: `
		<div>
			<div
				class="ui-entity-catalog__header-groups-content"
				v-if="headerLists && headerLists.length"
			>
				<ul
					class="ui-entity-catalog__menu"
					v-for="(groupList, listIndex) in headerLists"
					:key="'header-'+listIndex"
				>
					<Group
						:group-data="group"
						:key="group.id"
						v-for="group in groupList"
						@selected="handleGroupSelected"
						@unselected="handleGroupUnselected"
					>
						<template #group="groupSlotProps">
							<slot
								name="group"
								v-bind:groupData="groupSlotProps.groupData"
								v-bind:handleClick="groupSlotProps.handleClick"
							/>
						</template>
					</Group>
				</ul>
			</div>

			<div>
				<ul
					class="ui-entity-catalog__menu"
					v-for="(groupList, listIndex) in mainLists"
					:key="'main-'+listIndex"
				>
					<Group
						:group-data="group"
						:key="group.id"
						v-for="group in groupList"
						@selected="handleGroupSelected"
						@unselected="handleGroupUnselected"
					>
						<template #group="groupSlotProps">
							<slot
								name="group"
								v-bind:groupData="groupSlotProps.groupData"
								v-bind:handleClick="groupSlotProps.handleClick"
							/>
						</template>
					</Group>
				</ul>
			</div>
		</div>
	`
	};

	const MainGroups = {
	  emits: ['groupSelected'],
	  name: 'ui-entity-catalog-main-groups',
	  components: {
	    GroupList
	  },
	  props: {
	    groups: {
	      /** @type Array<Array<GroupData>> */
	      type: Array,
	      required: true
	    },
	    searching: {
	      type: Boolean,
	      default: false
	    },
	    selectedGroup: {
	      /** @type GroupData */
	      type: Object,
	      default: null
	    }
	  },
	  computed: {
	    processedGroups() {
	      const selectedGroupId = this.searching ? null : this.selectedGroup ? this.selectedGroup.id : null;
	      const groupsClone = BX.Runtime.clone(this.groups);
	      groupsClone.forEach(groupList => {
	        groupList.forEach(group$$1 => {
	          group$$1.selected = group$$1.id === selectedGroupId;
	        });
	      });
	      return groupsClone;
	    },
	    headerLists() {
	      return this.processedGroups.map(list => list.filter(g => g.isHeaderGroup)).filter(list => list.length > 0);
	    },
	    mainLists() {
	      return this.processedGroups.map(list => list.filter(g => !g.isHeaderGroup)).filter(list => list.length > 0);
	    }
	  },
	  methods: {
	    handleGroupSelected(group$$1) {
	      this.$emit('groupSelected', group$$1);
	    },
	    handleGroupUnselected() {
	      this.$emit('groupSelected', null);
	    }
	  },
	  template: `
		<div class="ui-entity-catalog__main-groups">
			<div class="ui-entity-catalog__main-groups-head">
				<slot name="group-list-header"/>
			</div>
			<div
				class="ui-entity-catalog__header-groups-content"
				v-if="headerLists && headerLists.length"
			>
				<GroupList
					:groups="headerLists"
					@groupSelected="handleGroupSelected"
					@groupUnselected="handleGroupUnselected"
				>
					<template #group="groupSlotProps">
						<slot
							name="group"
							v-bind:groupData="groupSlotProps.groupData"
							v-bind:handleClick="groupSlotProps.handleClick"
						/>
					</template>
				</GroupList>
			</div>
			<div class="ui-entity-catalog__main-groups-content">
				<GroupList
					:groups="mainLists"
					@groupSelected="handleGroupSelected"
					@groupUnselected="handleGroupUnselected"
				>
					<template #group="groupSlotProps">
						<slot
							name="group"
							v-bind:groupData="groupSlotProps.groupData"
							v-bind:handleClick="groupSlotProps.handleClick"
						/>
					</template>
				</GroupList>
			</div>
			<div class="ui-entity-catalog__main-groups-footer">
				<slot name="group-list-footer"/>
			</div>
		</div>
	`
	};

	const ItemListAdvice = {
	  name: 'ui-entity-catalog-item-list-advice',
	  props: {
	    groupData: {
	      type: group.GroupData,
	      required: true
	    }
	  },
	  computed: {
	    getAvatar: function () {
	      return main_core.Type.isStringFilled(this.groupData.adviceAvatar) ? this.groupData.adviceAvatar : '/bitrix/js/ui/entity-catalog/images/ui-entity-catalog--nata.jpg';
	    }
	  },
	  methods: {
	    renderAdvice() {
	      main_core.Dom.clean(this.$refs.container);
	      const advice = new ui_advice.Advice({
	        content: this.groupData.adviceTitle,
	        avatarImg: this.getAvatar,
	        anglePosition: ui_advice.Advice.AnglePosition.BOTTOM
	      });
	      advice.renderTo(this.$refs.container);
	    }
	  },
	  mounted() {
	    this.renderAdvice();
	  },
	  updated() {
	    this.renderAdvice();
	  },
	  template: `
		<div ref="container"></div>
	`
	};

	const Button = {
	  name: 'ui-entity-catalog-button',
	  props: {
	    buttonData: {
	      type: button.ButtonData,
	      required: true
	    },
	    eventData: {
	      type: Object,
	      required: true
	    }
	  },
	  computed: {
	    buttonText() {
	      return main_core.Type.isStringFilled(this.buttonData.text) ? this.buttonData.text : main_core.Loc.getMessage('UI_JS_ENTITY_CATALOG_ITEM_DEFAULT_BUTTON_TEXT');
	    }
	  },
	  methods: {
	    handleButtonClick(pointerEvent) {
	      const event = new main_core_events.BaseEvent({
	        data: {
	          eventData: this.eventData,
	          originalEvent: pointerEvent
	        }
	      });
	      if (main_core.Type.isFunction(this.buttonData.action)) {
	        this.buttonData.action.call(this, event);
	      }
	    }
	  },
	  template: `
		<div class="ui-entity-catalog__option-btn-block">
			<div 
				class="ui-entity-catalog__btn"
				:class="{'--lock': buttonData.locked}"
				@click="handleButtonClick"
			>{{buttonText}}</div>
		</div>
	`
	};

	const Item = {
	  name: 'ui-entity-catalog-item',
	  components: {
	    Button
	  },
	  props: {
	    itemData: {
	      type: item.ItemData,
	      required: true
	    }
	  },
	  computed: {
	    buttonData() {
	      if (!main_core.Type.isPlainObject(this.itemData.button)) {
	        this.itemData.button = {};
	      }
	      return this.itemData.button;
	    },
	    topText() {
	      var _this$itemData$custom, _this$itemData;
	      const custom = (_this$itemData$custom = (_this$itemData = this.itemData) == null ? void 0 : _this$itemData.customData) != null ? _this$itemData$custom : {};
	      if (main_core.Type.isStringFilled(custom.topText)) {
	        return custom.topText;
	      }
	      return null;
	    }
	  },
	  template: `
		<slot name="item" v-bind:itemData="itemData">
			<div 
				class="ui-entity-catalog__option"
				:data-item-id="String(itemData.id)"
			>
				<div class="ui-entity-catalog__option-info">
					<div
						v-if="topText"
						class="ui-entity-catalog__option-info_top_text"
					>
						{{ topText }}
					</div>
					<div class="ui-entity-catalog__option-info_name">
						<span>{{itemData.title}}</span>
						<span class="ui-entity-catalog__option-info_label" v-if="itemData.subtitle">{{itemData.subtitle}}</span>
					</div>
					<div class="ui-entity-catalog__option-info_description">
						{{itemData.description}}
					</div>
				</div>
				<Button :buttonData="buttonData" :event-data="itemData"/>
			</div>
		</slot>
	`
	};

	const ItemList = {
	  name: 'ui-entity-selector-item-list',
	  components: {
	    Item
	  },
	  props: {
	    items: {
	      /** @type Array<ItemData> */
	      Type: Array,
	      required: true
	    }
	  },
	  template: `
		<div class="ui-entity-catalog__content">
			<div class="ui-entity-catalog__options">
				<Item 
					:item-data="item"
					:key="item.id"
					v-for="item in items"
				>
					<template #item="itemSlotProps">
						<slot name="item" v-bind:itemData="itemSlotProps.itemData"/>
					</template>
				</Item>
			</div>
		</div>
	`
	};

	const EmptyContent = {
	  template: `
		<div class="ui-entity-catalog__content --help-block">
			<div class="ui-entity-catalog__empty-content">
				<div class="ui-entity-catalog__empty-content_icon">
					<img src="/bitrix/js/ui/entity-catalog/images/ui-entity-catalog--search-icon.svg" alt="Choose a grouping">
				</div>
				<div class="ui-entity-catalog__empty-content_text">
					<slot/>
				</div>
			</div>
		</div>
		`
	};

	const useGlobalState = ui_vue3_pinia.defineStore('global-state', {
	  state: () => ({
	    searchQuery: '',
	    searchApplied: false,
	    filtersApplied: false,
	    currentGroup: group.GroupData,
	    shouldShowWelcomeStub: true
	  })
	});

	const MainContent = {
	  name: 'ui-entity-catalog-main-content',
	  components: {
	    ItemListAdvice,
	    ItemList,
	    EmptyContent
	  },
	  props: {
	    items: {
	      /** @type Array<ItemData> */
	      type: Array,
	      required: true
	    },
	    itemsToShow: {
	      /** @type Array<ItemData> */
	      type: Array
	    },
	    group: {
	      type: group.GroupData,
	      required: true
	    },
	    searching: {
	      type: Boolean,
	      default: false
	    }
	  },
	  computed: {
	    ...ui_vue3_pinia.mapState(useGlobalState, ['filtersApplied', 'shouldShowWelcomeStub']),
	    showAdvice() {
	      return this.group && main_core.Type.isStringFilled(this.group.adviceTitle) && !this.searching;
	    },
	    hasItems() {
	      return this.group && this.items.length > 0;
	    },
	    showWelcomeStub() {
	      return this.showNoSelectedGroupStub && this.shouldShowWelcomeStub;
	    },
	    showNoSelectedGroupStub() {
	      return !this.group && !this.searching;
	    },
	    showFiltersStub() {
	      const hasFilterStubTitle = !!this.$slots['main-content-filter-stub-title'];
	      return hasFilterStubTitle && this.hasItems && this.filtersApplied && this.itemsToShow.length <= 0;
	    },
	    showSearchStub() {
	      return (!this.group || this.hasItems) && this.searching && this.itemsToShow.length <= 0;
	    },
	    showEmptyGroupStub() {
	      return this.group && this.itemsToShow.length === 0;
	    },
	    showSeparator() {
	      return this.showAdvice && this.items.length <= 0;
	    }
	  },
	  beforeUpdate() {
	    this.$refs.content.scrollTop = 0;
	  },
	  template: `
		<div class="ui-entity-catalog__main-content">
			<div class="ui-entity-catalog__main-content-head">
				<slot name="main-content-header"/>
			</div>
			<ItemListAdvice v-if="showAdvice" :groupData="group" />

			<hr class="ui-entity-catalog__main-separator" v-if="showSeparator">

			<div class="ui-entity-catalog__main-content-body" ref="content">
				<slot name="main-content-welcome-stub" v-if="showWelcomeStub"/>
				<slot name="main-content-no-selected-group-stub" v-else-if="showNoSelectedGroupStub"/>
				<slot name="main-content-filter-stub" v-if="showFiltersStub">
					<EmptyContent>
						<slot name="main-content-filter-stub-title"/>
					</EmptyContent>
				</slot>
				<slot name="main-content-search-stub" v-else-if="showSearchStub">
					<EmptyContent>
						<slot name="main-content-search-not-found-stub"/>
					</EmptyContent>
				</slot>
				<slot name="main-content-empty-group-stub" v-else-if="showEmptyGroupStub">
					<EmptyContent>
						<slot name="main-content-empty-group-stub-title"/>
					</EmptyContent> 
				</slot>
				<ItemList v-else :items="itemsToShow">
					<template #item="itemSlotProps">
						<slot name="item" v-bind:itemData="itemSlotProps.itemData"/>
					</template>
				</ItemList>
				<div class="ui-entity-catalog__main-content-footer">
					<slot name="main-content-footer"/>
				</div>
			</div>
		</div>
	`
	};

	let _ = t => t,
	  _t,
	  _t2;
	const TitleBarFilter = {
	  emits: ['onApplyFilters'],
	  name: 'ui-entity-catalog-titlebar-filter',
	  props: {
	    filters: {
	      type: Array,
	      required: true
	    },
	    multiple: {
	      type: Boolean,
	      default: false
	    }
	  },
	  data() {
	    return {
	      appliedFilters: this.getAppliedFilters(),
	      allFilters: this.filters
	    };
	  },
	  methods: {
	    showMenu() {
	      main_popup.MenuManager.create({
	        id: 'ui-entity-catalog-titlebar-filter-menu',
	        bindElement: this.$el,
	        minWidth: 271,
	        autoHide: true,
	        contentColor: 'white',
	        draggable: false,
	        cacheable: false,
	        items: this.getItems()
	      }).show();
	    },
	    getItems() {
	      const items = [];
	      for (const key in this.allFilters) {
	        const html = main_core.Tag.render(_t || (_t = _`
					<div style="display: flex">
						<div>${0}</div>
					</div>
				`), main_core.Text.encode(this.filters[key].text));
	        if (this.allFilters[key].applied) {
	          main_core.Dom.append(main_core.Tag.render(_t2 || (_t2 = _`<div class="ui-entity-catalog__filter-block_selected"></div>`)), html);
	        }
	        items.push({
	          html,
	          onclick: (event, item$$1) => {
	            if (this.allFilters[key].applied) {
	              delete this.appliedFilters[this.allFilters[key].id];
	            } else {
	              if (!this.multiple) {
	                this.clearAllAction();
	              }
	              this.appliedFilters[this.allFilters[key].id] = this.allFilters[key];
	            }
	            this.allFilters[key].applied = !this.allFilters[key].applied;
	            this.$emit('onApplyFilters', new main_core_events.BaseEvent({
	              data: this.appliedFilters
	            }));
	            item$$1.getMenuWindow().close();
	          }
	        });
	      }
	      items.push({
	        delimiter: true
	      });
	      items.push(this.getClearAllFilter());
	      return items;
	    },
	    getClearAllFilter() {
	      return {
	        html: `
					<div style="display: flex">
						<div>${main_core.Loc.getMessage('UI_JS_ENTITY_CATALOG_RESET_FILTER')}</div>
					</div>
				`,
	        onclick: (event, item$$1) => {
	          this.clearAllAction();
	          this.$emit('onApplyFilters', new main_core_events.BaseEvent({
	            data: this.appliedFilters
	          }));
	          item$$1.getMenuWindow().close();
	        }
	      };
	    },
	    clearAllAction() {
	      this.appliedFilters = {};
	      this.allFilters = this.allFilters.map(filter => ({
	        ...filter,
	        applied: false
	      }));
	    },
	    getAppliedFilters() {
	      const appliedFilters = {};
	      for (const key in this.filters) {
	        if (this.filters[key].applied) {
	          appliedFilters[this.filters[key].id] = this.filters[key];
	        }
	      }
	      if (Object.keys(appliedFilters).length > 0) {
	        this.$emit('onApplyFilters', new main_core_events.BaseEvent({
	          data: appliedFilters
	        }));
	      }
	      return appliedFilters;
	    }
	  },
	  template: `
		<div 
			:class="{
				'ui-entity-catalog__titlebar_btn-filter': true,
				'--active': Object.keys(appliedFilters).length > 0
			}"
			@click="showMenu">
		</div>
	`
	};

	const Search = {
	  emits: ['onSearch'],
	  name: 'ui-entity-catalog-titlebar-search',
	  data() {
	    return {
	      opened: false,
	      debounceSearchHandler: null,
	      queryString: '',
	      showClearSearch: false
	    };
	  },
	  watch: {
	    queryString(newString) {
	      this.showClearSearch = this.opened && this.$refs['search-input'] && main_core.Type.isStringFilled(newString);
	    }
	  },
	  created() {
	    this.debounceSearchHandler = main_core.debounce(event => {
	      this.onSearch(event.target.value);
	    }, 255);
	  },
	  methods: {
	    openSearch() {
	      this.opened = true;
	      this.$nextTick(() => {
	        this.$refs['search-input'].focus();
	      });
	    },
	    onSearch(queryString) {
	      this.queryString = queryString;
	      this.$emit('onSearch', new main_core_events.BaseEvent({
	        data: {
	          queryString: queryString ? queryString.toString() : ''
	        }
	      }));
	    },
	    clearSearch() {
	      if (this.showClearSearch) {
	        this.$refs['search-input'].value = '';
	        this.onSearch('');
	      }
	    }
	  },
	  template: `
		<div class="ui-ctl ui-ctl-after-icon ui-ctl-w100 ui-ctl-round" @click.once="openSearch">
			<a 
				:class="{
					'ui-ctl-after': true,
					'ui-ctl-icon-search': !showClearSearch,
					'ui-ctl-icon-clear': showClearSearch
				}"
				@click="clearSearch"
			/>
			<input
				type="text"
				class="ui-ctl-element ui-ctl-textbox"
				placeholder="${main_core.Loc.getMessage('UI_JS_ENTITY_CATALOG_GROUP_LIST_SEARCH_PLACEHOLDER')}"
				ref="search-input"
				v-if="opened"
				@input="debounceSearchHandler"
			/>
		</div>
	`
	};

	const Application = {
	  name: 'ui-entity-catalog-application',
	  components: {
	    MainGroups,
	    MainContent,
	    TitleBarFilter,
	    Search
	  },
	  props: {
	    /** @type Array<Array<GroupData>> */
	    groups: {
	      type: Array,
	      required: true
	    },
	    /** @type Array<ItemData> */
	    items: {
	      type: Array,
	      required: true
	    },
	    showEmptyGroups: {
	      type: Boolean,
	      default: false
	    },
	    filterOptions: {
	      type: Object,
	      default: {
	        filterItems: [],
	        multiple: false
	      }
	    }
	  },
	  data() {
	    var _selectedGroup$id, _selectedGroup;
	    let selectedGroup = null;
	    for (const groupList of this.groups) {
	      selectedGroup = groupList.find(group$$1 => group$$1.selected);
	      if (selectedGroup) {
	        break;
	      }
	    }
	    return {
	      selectedGroup,
	      selectedGroupId: (_selectedGroup$id = (_selectedGroup = selectedGroup) == null ? void 0 : _selectedGroup.id) != null ? _selectedGroup$id : null,
	      shownItems: [],
	      shownGroups: [],
	      lastSearchString: '',
	      filters: []
	    };
	  },
	  computed: {
	    itemsBySelectedGroupId() {
	      var _this$selectedGroup;
	      const items = this.items.filter(item$$1 => item$$1.groupIds.some(id => id === this.selectedGroupId));
	      return (_this$selectedGroup = this.selectedGroup) != null && _this$selectedGroup.compare ? items.sort(this.selectedGroup.compare) : items;
	    },
	    computedShownGroups() {
	      if (this.showEmptyGroups) {
	        return main_core.Runtime.clone(this.groups);
	      }
	      const groupIdsWithItems = new Set();
	      this.items.forEach(item$$1 => {
	        item$$1.groupIds.forEach(groupId => groupIdsWithItems.add(groupId));
	      });
	      return this.groups.map(groupList => groupList.filter(group$$1 => group$$1.isHeaderGroup === true || groupIdsWithItems.has(group$$1.id))).filter(list => list.length > 0);
	    },
	    computedShownItems() {
	      if (this.searching && main_core.Type.isStringFilled(this.lastSearchString)) {
	        const q = this.lastSearchString;
	        let result = this.items.filter(item$$1 => {
	          var _item$tags;
	          return String(item$$1.title).toLowerCase().includes(q) || String(item$$1.description).toLowerCase().includes(q) || ((_item$tags = item$$1.tags) == null ? void 0 : _item$tags.some(tag => tag === q));
	        });
	        for (const filterId in this.filters) {
	          result = result.filter(this.filters[filterId].action);
	        }
	        return result;
	      }
	      let result = this.itemsBySelectedGroupId.slice();
	      for (const filterId in this.filters) {
	        result = result.filter(this.filters[filterId].action);
	      }
	      return result;
	    },
	    ...ui_vue3_pinia.mapWritableState(useGlobalState, {
	      searchQuery: 'searchQuery',
	      searching: 'searchApplied',
	      filtersApplied: 'filtersApplied',
	      globalGroup: 'currentGroup',
	      shouldShowWelcomeStub: 'shouldShowWelcomeStub'
	    })
	  },
	  watch: {
	    computedShownItems: {
	      handler() {
	        this.$nextTick(() => {
	          this.$emit('itemsRendered');
	        });
	      },
	      flush: 'post'
	    },
	    computedShownGroups: {
	      immediate: true,
	      handler(newVal) {
	        // quick replace in-place to keep same array object reference
	        this.shownGroups.splice(0, this.shownGroups.length, ...newVal);
	        if (!this.selectedGroupId) {
	          const selected = this.shownGroups.flat().find(g => g.selected);
	          if (selected) {
	            this.selectedGroup = selected;
	            this.selectedGroupId = selected.id;
	          }
	        }
	      }
	    },
	    selectedGroup() {
	      this.shouldShowWelcomeStub = false;
	      this.globalGroup = this.selectedGroup;
	    }
	  },
	  methods: {
	    getDisplayedGroup() {
	      if (this.showEmptyGroups) {
	        return main_core.Runtime.clone(this.groups);
	      }
	      const groupIdsWithItems = new Set();
	      this.items.forEach(item$$1 => {
	        item$$1.groupIds.forEach(groupId => {
	          groupIdsWithItems.add(groupId);
	        });
	      });
	      return this.groups.map(groupList => groupList.filter(group$$1 => group$$1.isHeaderGroup === true || groupIdsWithItems.has(group$$1.id))).filter(groupList => groupList.length > 0);
	    },
	    handleGroupSelected(group$$1) {
	      var _this$$refs$search;
	      this.searching = false;
	      (_this$$refs$search = this.$refs.search) == null ? void 0 : _this$$refs$search.clearSearch();
	      this.selectedGroupId = group$$1 ? group$$1.id : null;
	      this.selectedGroup = group$$1 != null ? group$$1 : null;
	    },
	    onSearch(event) {
	      const queryString = event.getData().queryString.toLowerCase();
	      this.lastSearchString = queryString;
	      this.searchQuery = queryString || '';
	      if (!main_core.Type.isStringFilled(queryString)) {
	        this.searching = false;
	        this.shownItems = [];
	        return;
	      }
	      this.searching = true;
	      this.selectedGroup = null;
	      this.selectedGroupId = null;
	      this.shownItems = this.items.filter(item$$1 => {
	        var _item$tags2;
	        return String(item$$1.title).toLowerCase().includes(queryString) || String(item$$1.description).toLowerCase().includes(queryString) || ((_item$tags2 = item$$1.tags) == null ? void 0 : _item$tags2.some(tag => tag === queryString));
	      });
	      this.applyFilters();
	    },
	    onApplyFilterClick(event) {
	      this.filters = event.getData();
	      if (this.searching) {
	        this.onSearch(new main_core_events.BaseEvent({
	          data: {
	            queryString: this.lastSearchString
	          }
	        }));
	        return;
	      }
	      this.shownItems = this.itemsBySelectedGroupId;
	      this.applyFilters();
	    },
	    applyFilters() {
	      this.filtersApplied = Object.values(this.filters).length > 0;
	      for (const filterId in this.filters) {
	        this.shownItems = this.shownItems.filter(this.filters[filterId].action);
	      }
	    },
	    getFilterNode() {
	      return this.$root.$app.getPopup().getTitleContainer().querySelector('[data-role="titlebar-filter"]');
	    },
	    getSearchNode() {
	      return this.$root.$app.getPopup().getTitleContainer().querySelector('[data-role="titlebar-search"]');
	    },
	    stopPropagation(event) {
	      event.stopPropagation();
	    }
	  },
	  template: `
		<div class="ui-entity-catalog__main">
			<MainGroups
				:groups="this.shownGroups"
				:searching="searching"
				@group-selected="handleGroupSelected"
				:selected-group="selectedGroup"
			>
				<template #group-list-header>
					<slot name="group-list-header"/>
				</template>
				<template #group="groupSlotProps">
					<slot
						name="group"
						v-bind:groupData="groupSlotProps.groupData"
						v-bind:handleClick="groupSlotProps.handleClick"
					/>
				</template>
				<template #group-list-footer>
					<slot name="group-list-footer"/>
				</template>
			</MainGroups>
			<MainContent
				:items="itemsBySelectedGroupId"
				:items-to-show="computedShownItems"
				:group="selectedGroup"
				:searching="searching"
			>
				<template #main-content-header>
					<slot name="main-content-header"/>
				</template>
				<template #main-content-no-selected-group-stub>
					<slot name="main-content-no-selected-group-stub"/>
				</template>
				<template #main-content-welcome-stub>
					<slot name="main-content-welcome-stub"/>
				</template>
				<template #main-content-filter-stub v-if="$slots['main-content-filter-stub']">
					<slot name="main-content-filter-stub"/>
				</template>
				<template #main-content-filter-stub-title v-if="$slots['main-content-filter-stub-title']">
					<slot name="main-content-filter-stub-title"/>
				</template>
				<template #main-content-search-stub>
					<slot name="main-content-search-stub"></slot>
				</template>
				<template #main-content-search-not-found-stub>
					<slot name="main-content-search-not-found-stub"/>
				</template>
				<template #main-content-empty-group-stub>
					<slot name="main-content-empty-group-stub"/>
				</template>
				<template #main-content-empty-group-stub-title>
					<slot name="main-content-empty-group-stub-title"/>
				</template>
				<template #item="itemSlotProps">
					<slot name="item" v-bind:itemData="itemSlotProps.itemData"/>
				</template>
				<template #main-content-footer>
					<slot name="main-content-footer"/>
				</template>
			</MainContent>
			<Teleport v-if="getFilterNode()" :to="getFilterNode()">
				<TitleBarFilter
					:filters="filterOptions.filterItems"
					:multiple="filterOptions.multiple"
					@onApplyFilters="onApplyFilterClick"
					@mousedown="stopPropagation"
				/>
			</Teleport>
			<Teleport v-if="getSearchNode()" :to="getSearchNode()">
				<Search @onSearch="onSearch" ref="search" @mousedown="stopPropagation"/>
			</Teleport>
		</div>
	`
	};

	let _$1 = t => t,
	  _t$1,
	  _t2$1;
	const Stubs = {
	  EmptyContent
	};
	const States = {
	  useGlobalState
	};
	var _popup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popup");
	var _popupOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popupOptions");
	var _popupTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popupTitle");
	var _customTitleBar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("customTitleBar");
	var _groups = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("groups");
	var _items = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("items");
	var _showEmptyGroups = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showEmptyGroups");
	var _showSearch = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showSearch");
	var _filterOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filterOptions");
	var _application = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("application");
	var _slots = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("slots");
	var _customComponents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("customComponents");
	var _recentGroupData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("recentGroupData");
	var _showRecentGroup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showRecentGroup");
	var _vueInstance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("vueInstance");
	var _resolveGroupsForTemplate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("resolveGroupsForTemplate");
	var _attachTemplate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("attachTemplate");
	var _getDefaultPopupOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDefaultPopupOptions");
	var _getPopupTitleBar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopupTitleBar");
	var _handleClose = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleClose");
	class EntityCatalog extends main_core_events.EventEmitter {
	  // backward compatability

	  constructor(props) {
	    var _props$recentGroupDat, _props$slots, _props$customComponen;
	    super();
	    Object.defineProperty(this, _handleClose, {
	      value: _handleClose2
	    });
	    Object.defineProperty(this, _getPopupTitleBar, {
	      value: _getPopupTitleBar2
	    });
	    Object.defineProperty(this, _getDefaultPopupOptions, {
	      value: _getDefaultPopupOptions2
	    });
	    Object.defineProperty(this, _attachTemplate, {
	      value: _attachTemplate2
	    });
	    Object.defineProperty(this, _resolveGroupsForTemplate, {
	      value: _resolveGroupsForTemplate2
	    });
	    Object.defineProperty(this, _popup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _popupOptions, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _popupTitle, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _customTitleBar, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _groups, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _items, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _showEmptyGroups, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _showSearch, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _filterOptions, {
	      writable: true,
	      value: {
	        filterItems: [],
	        multiple: false
	      }
	    });
	    Object.defineProperty(this, _application, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _slots, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _customComponents, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _recentGroupData, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _showRecentGroup, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _vueInstance, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.UI.EntityCatalog');
	    this.setGroups(main_core.Type.isArray(props.groups) ? props.groups : []);
	    this.setItems(main_core.Type.isArray(props.items) ? props.items : []);

	    // backward compatibility
	    babelHelpers.classPrivateFieldLooseBase(this, _recentGroupData)[_recentGroupData] = (_props$recentGroupDat = props.recentGroupData) != null ? _props$recentGroupDat : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _showRecentGroup)[_showRecentGroup] = main_core.Type.isBoolean(props.showRecentGroup) ? props.showRecentGroup : false;
	    if (main_core.Type.isBoolean(props.canDeselectGroups)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _groups)[_groups].forEach(groupList => {
	        groupList.forEach(group$$1 => {
	          group$$1.deselectable = props.canDeselectGroups;
	        });
	      });
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _showEmptyGroups)[_showEmptyGroups] = main_core.Type.isBoolean(props.showEmptyGroups) ? props.showEmptyGroups : false;
	    babelHelpers.classPrivateFieldLooseBase(this, _showSearch)[_showSearch] = main_core.Type.isBoolean(props.showSearch) ? props.showSearch : false;
	    if (main_core.Type.isPlainObject(props.filterOptions)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _filterOptions)[_filterOptions] = props.filterOptions;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _popupTitle)[_popupTitle] = main_core.Type.isString(props.title) ? props.title : '';
	    babelHelpers.classPrivateFieldLooseBase(this, _customTitleBar)[_customTitleBar] = props.customTitleBar ? props.customTitleBar : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _popupOptions)[_popupOptions] = Object.assign(babelHelpers.classPrivateFieldLooseBase(this, _getDefaultPopupOptions)[_getDefaultPopupOptions](), main_core.Type.isObject(props.popupOptions) ? props.popupOptions : {});
	    babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots] = (_props$slots = props.slots) != null ? _props$slots : {};
	    babelHelpers.classPrivateFieldLooseBase(this, _customComponents)[_customComponents] = (_props$customComponen = props.customComponents) != null ? _props$customComponen : {};
	    this.subscribeFromOptions(props.events);
	  }
	  setGroups(groups) {
	    babelHelpers.classPrivateFieldLooseBase(this, _groups)[_groups] = groups.map(groupList => {
	      if (!main_core.Type.isArray(groupList)) {
	        groupList = [groupList];
	      }
	      return groupList.map(group$$1 => ({
	        selected: false,
	        deselectable: true,
	        ...group$$1
	      }));
	    });
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _vueInstance)[_vueInstance] || !babelHelpers.classPrivateFieldLooseBase(this, _vueInstance)[_vueInstance].localGroups) {
	      return this;
	    }
	    if (this.isGroupsStructureChanged(babelHelpers.classPrivateFieldLooseBase(this, _groups)[_groups])) {
	      try {
	        babelHelpers.classPrivateFieldLooseBase(this, _vueInstance)[_vueInstance].refreshGroups(babelHelpers.classPrivateFieldLooseBase(this, _groups)[_groups]);
	      } catch (e) {
	        console.error(e);
	        babelHelpers.classPrivateFieldLooseBase(this, _vueInstance)[_vueInstance].localGroups = babelHelpers.classPrivateFieldLooseBase(this, _groups)[_groups];
	      }
	    } else {
	      const countersMap = {};
	      for (const list of babelHelpers.classPrivateFieldLooseBase(this, _groups)[_groups]) {
	        for (const g of list) {
	          var _g$customData$counter, _g$customData;
	          countersMap[String(g.id)] = (_g$customData$counter = (_g$customData = g.customData) == null ? void 0 : _g$customData.counterValue) != null ? _g$customData$counter : null;
	        }
	      }
	      this._updateCountersInGroupLists(babelHelpers.classPrivateFieldLooseBase(this, _vueInstance)[_vueInstance].localGroups, countersMap);
	    }
	    return this;
	  }
	  isGroupsStructureChanged(newGroups) {
	    if (!Array.isArray(babelHelpers.classPrivateFieldLooseBase(this, _groups)[_groups]) || !Array.isArray(newGroups)) {
	      return true;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _groups)[_groups].length !== newGroups.length) {
	      return true;
	    }
	    for (let i = 0; i < newGroups.length; i++) {
	      const oldList = babelHelpers.classPrivateFieldLooseBase(this, _groups)[_groups][i] || [];
	      const newList = newGroups[i] || [];
	      if (oldList.length !== newList.length) {
	        return true;
	      }
	      for (let j = 0; j < newList.length; j++) {
	        var _oldList$j, _newList$j;
	        if (String((_oldList$j = oldList[j]) == null ? void 0 : _oldList$j.id) !== String((_newList$j = newList[j]) == null ? void 0 : _newList$j.id)) {
	          return true;
	        }
	      }
	    }
	    return false;
	  }
	  _updateCountersInGroupLists(groupLists, countersMap = {}) {
	    if (!Array.isArray(groupLists)) return;
	    for (const list of groupLists) {
	      for (const group$$1 of list) {
	        const id = String(group$$1.id);
	        if (Object.prototype.hasOwnProperty.call(countersMap, id)) {
	          var _group$customData;
	          const custom = (_group$customData = group$$1.customData) != null ? _group$customData : {};
	          group$$1.customData = Object.assign({}, custom, {
	            counterValue: countersMap[id]
	          });
	        }
	      }
	    }
	  }
	  updateGroupCounter(groupId, counterValue) {
	    const idStr = String(groupId);
	    const countersMap = {
	      [idStr]: counterValue
	    };
	    this._updateCountersInGroupLists(babelHelpers.classPrivateFieldLooseBase(this, _groups)[_groups], countersMap);
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _vueInstance)[_vueInstance]) {
	      return this;
	    }
	    try {
	      this._updateCountersInGroupLists(babelHelpers.classPrivateFieldLooseBase(this, _vueInstance)[_vueInstance].localGroups, countersMap);
	    } catch (e) {
	      if (typeof babelHelpers.classPrivateFieldLooseBase(this, _vueInstance)[_vueInstance].refreshGroups === 'function') {
	        babelHelpers.classPrivateFieldLooseBase(this, _vueInstance)[_vueInstance].refreshGroups(babelHelpers.classPrivateFieldLooseBase(this, _groups)[_groups]);
	      }
	    }
	    return this;
	  }
	  getItems() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _items)[_items];
	  }
	  setItems(items = []) {
	    babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].length = 0;
	    babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].push(...items);
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _vueInstance)[_vueInstance] || typeof babelHelpers.classPrivateFieldLooseBase(this, _vueInstance)[_vueInstance].refreshItems !== 'function') {
	      return this;
	    }
	    try {
	      babelHelpers.classPrivateFieldLooseBase(this, _vueInstance)[_vueInstance].refreshItems(babelHelpers.classPrivateFieldLooseBase(this, _items)[_items]);
	    } catch (e) {
	      console.error(e);
	    }
	    return this;
	  }
	  updateItemById(id, patch = {}) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _vueInstance)[_vueInstance] && typeof babelHelpers.classPrivateFieldLooseBase(this, _vueInstance)[_vueInstance].updateItemById === 'function') {
	      try {
	        babelHelpers.classPrivateFieldLooseBase(this, _vueInstance)[_vueInstance].updateItemById(id, patch);
	        return this;
	      } catch (e) {
	        console.error(e);
	      }
	    }
	    const itemForUpdate = babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].find(item$$1 => String(item$$1.id) === String(id));
	    if (itemForUpdate) {
	      Object.assign(itemForUpdate, patch);
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].push(Object.assign({
	        id
	      }, patch));
	    }
	    return this;
	  }
	  show() {
	    babelHelpers.classPrivateFieldLooseBase(this, _attachTemplate)[_attachTemplate]();
	    this.getPopup().show();
	  }
	  isShown() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup] && babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].isShown();
	  }
	  getPopup() {
	    if (main_core.Type.isNil(babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup])) {
	      babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup] = new main_popup.Popup(babelHelpers.classPrivateFieldLooseBase(this, _popupOptions)[_popupOptions]);
	      babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].setResizeMode(true);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup];
	  }
	  selectGroup(groupId) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _vueInstance)[_vueInstance] && babelHelpers.classPrivateFieldLooseBase(this, _vueInstance)[_vueInstance].$refs.application) {
	      const group$$1 = babelHelpers.classPrivateFieldLooseBase(this, _vueInstance)[_vueInstance].localGroups.flat().find(g => String(g.id) === String(groupId));
	      if (group$$1) {
	        babelHelpers.classPrivateFieldLooseBase(this, _vueInstance)[_vueInstance].$refs.application.handleGroupSelected(group$$1);
	      }
	    }
	    return this;
	  }
	  close() {
	    try {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _application)[_application] && typeof babelHelpers.classPrivateFieldLooseBase(this, _application)[_application].unmount === 'function') {
	        babelHelpers.classPrivateFieldLooseBase(this, _application)[_application].unmount();
	      }
	      if (babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) {
	        babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].close();
	      }
	    } catch (e) {
	      console.error(e);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _application)[_application] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _vueInstance)[_vueInstance] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup] = null;
	  }
	}
	function _resolveGroupsForTemplate2() {
	  var _babelHelpers$classPr, _babelHelpers$classPr2, _babelHelpers$classPr3, _babelHelpers$classPr4, _babelHelpers$classPr5, _babelHelpers$classPr6, _babelHelpers$classPr7, _babelHelpers$classPr8, _babelHelpers$classPr9;
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _recentGroupData)[_recentGroupData] || !babelHelpers.classPrivateFieldLooseBase(this, _showRecentGroup)[_showRecentGroup]) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _groups)[_groups];
	  }

	  // clone groups shallowly to avoid mutating original arrays
	  const groupsClone = babelHelpers.classPrivateFieldLooseBase(this, _groups)[_groups].map(list => list.slice());
	  const recent = {
	    isHeaderGroup: true,
	    id: (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _recentGroupData)[_recentGroupData].id) != null ? _babelHelpers$classPr : 'recent',
	    name: (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _recentGroupData)[_recentGroupData].name) != null ? _babelHelpers$classPr2 : '',
	    icon: (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _recentGroupData)[_recentGroupData].icon) != null ? _babelHelpers$classPr3 : '',
	    tags: (_babelHelpers$classPr4 = babelHelpers.classPrivateFieldLooseBase(this, _recentGroupData)[_recentGroupData].tags) != null ? _babelHelpers$classPr4 : [],
	    adviceTitle: babelHelpers.classPrivateFieldLooseBase(this, _recentGroupData)[_recentGroupData].adviceTitle,
	    adviceAvatar: babelHelpers.classPrivateFieldLooseBase(this, _recentGroupData)[_recentGroupData].adviceAvatar,
	    selected: !!babelHelpers.classPrivateFieldLooseBase(this, _recentGroupData)[_recentGroupData].selected,
	    disabled: !!babelHelpers.classPrivateFieldLooseBase(this, _recentGroupData)[_recentGroupData].disabled,
	    deselectable: (_babelHelpers$classPr5 = babelHelpers.classPrivateFieldLooseBase(this, _recentGroupData)[_recentGroupData].deselectable) != null ? _babelHelpers$classPr5 : true,
	    compare: babelHelpers.classPrivateFieldLooseBase(this, _recentGroupData)[_recentGroupData].compare,
	    customData: Object.assign({}, (_babelHelpers$classPr6 = babelHelpers.classPrivateFieldLooseBase(this, _recentGroupData)[_recentGroupData].customData) != null ? _babelHelpers$classPr6 : {}, {
	      // prefer canonical name `counterValue`, fallback to legacy `newEntitiesCount`
	      counterValue: (_babelHelpers$classPr7 = (_babelHelpers$classPr8 = babelHelpers.classPrivateFieldLooseBase(this, _recentGroupData)[_recentGroupData].customData) == null ? void 0 : _babelHelpers$classPr8.counterValue) != null ? _babelHelpers$classPr7 : (_babelHelpers$classPr9 = babelHelpers.classPrivateFieldLooseBase(this, _recentGroupData)[_recentGroupData].customData) == null ? void 0 : _babelHelpers$classPr9.newEntitiesCount
	    })
	  };
	  if (groupsClone.length > 0 && Array.isArray(groupsClone[0]) && groupsClone[0].some(g => g.isHeaderGroup)) {
	    groupsClone[0].unshift(recent);
	    return groupsClone;
	  }
	  return [[recent], ...groupsClone];
	}
	function _attachTemplate2() {
	  var _babelHelpers$classPr10, _babelHelpers$classPr11, _babelHelpers$classPr12, _babelHelpers$classPr13, _babelHelpers$classPr14, _babelHelpers$classPr15, _babelHelpers$classPr16, _babelHelpers$classPr17, _babelHelpers$classPr18, _babelHelpers$classPr19, _babelHelpers$classPr20;
	  const container = this.getPopup().getContentContainer();
	  if (babelHelpers.classPrivateFieldLooseBase(this, _application)[_application] && typeof babelHelpers.classPrivateFieldLooseBase(this, _application)[_application].unmount === 'function') {
	    try {
	      babelHelpers.classPrivateFieldLooseBase(this, _application)[_application].unmount();
	    } catch (e) {
	      console.error(e);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _application)[_application] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _vueInstance)[_vueInstance] = null;
	  }
	  const context = this;
	  const groupsToPass = babelHelpers.classPrivateFieldLooseBase(this, _resolveGroupsForTemplate)[_resolveGroupsForTemplate]();
	  const rootProps = {
	    groups: groupsToPass,
	    items: babelHelpers.classPrivateFieldLooseBase(this, _items)[_items],
	    showEmptyGroups: babelHelpers.classPrivateFieldLooseBase(this, _showEmptyGroups)[_showEmptyGroups],
	    filterOptions: babelHelpers.classPrivateFieldLooseBase(this, _filterOptions)[_filterOptions]
	  };
	  babelHelpers.classPrivateFieldLooseBase(this, _application)[_application] = ui_vue3.BitrixVue.createApp({
	    name: 'ui-entity-catalog',
	    components: Object.assign(babelHelpers.classPrivateFieldLooseBase(this, _customComponents)[_customComponents], {
	      Application,
	      Hint: ui_vue3_components_hint.Hint,
	      Button
	    }),
	    directives: {
	      feedback
	    },
	    props: {
	      groups: Array,
	      items: Array,
	      showEmptyGroups: Boolean,
	      filterOptions: Object
	    },
	    created() {
	      this.$app = context;
	    },
	    data() {
	      return {
	        localGroups: this.groups,
	        localItems: this.items
	      };
	    },
	    methods: {
	      onItemsRendered() {
	        this.$app.emit('onItemsRendered');
	      },
	      refreshGroups(groups) {
	        this.localGroups = groups;
	      },
	      refreshItems(newItems = []) {
	        try {
	          const existingMap = new Map(this.localItems.map(it => [String(it.id), it]));
	          newItems.forEach(newIt => {
	            const id = String(newIt.id);
	            if (existingMap.has(id)) {
	              Object.assign(existingMap.get(id), newIt);
	            } else {
	              this.localItems.push(newIt);
	              existingMap.set(id, this.localItems[this.localItems.length - 1]);
	            }
	          });
	          const newIds = new Set(newItems.map(it => String(it.id)));
	          for (let i = this.localItems.length - 1; i >= 0; i--) {
	            if (!newIds.has(String(this.localItems[i].id))) {
	              this.localItems.splice(i, 1);
	            }
	          }
	        } catch (e) {
	          console.error(e);
	          this.localItems.splice(0, this.localItems.length, ...newItems);
	        }
	      },
	      updateItemById(id, patch = {}) {
	        try {
	          const it = this.localItems.find(x => String(x.id) === String(id));
	          if (it) {
	            Object.assign(it, patch);
	          } else {
	            this.localItems.push(Object.assign({
	              id
	            }, patch));
	          }
	        } catch (e) {
	          console.error(e);
	        }
	      }
	    },
	    template: `
					<Application
						ref="application"
						@itemsRendered="onItemsRendered"
						:groups="localGroups"
						:items="localItems"
						:show-empty-groups="showEmptyGroups"
						:filter-options="filterOptions"
					>
						<template #group-list-header>
							${(_babelHelpers$classPr10 = babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_GROUP_LIST_HEADER]) != null ? _babelHelpers$classPr10 : ''}
						</template>
						<template #group="groupSlotProps">
							${(_babelHelpers$classPr11 = babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_GROUP]) != null ? _babelHelpers$classPr11 : ''}
						</template>
						<template #group-list-footer>
							${(_babelHelpers$classPr12 = babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_GROUP_LIST_FOOTER]) != null ? _babelHelpers$classPr12 : ''}
						</template>

						<template #main-content-header>
							${(_babelHelpers$classPr13 = babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_MAIN_CONTENT_HEADER]) != null ? _babelHelpers$classPr13 : ''}
						</template>
						<template #main-content-footer>
							${(_babelHelpers$classPr14 = babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_MAIN_CONTENT_FOOTER]) != null ? _babelHelpers$classPr14 : ''}
						</template>
						<template #main-content-filter-stub v-if="${!!babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_MAIN_CONTENT_FILTERS_STUB]}">
							${babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_MAIN_CONTENT_FILTERS_STUB]}
						</template>
						<template #main-content-filter-stub-title v-if="${!!babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_MAIN_CONTENT_FILTERS_STUB_TITLE]}">
							${babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_MAIN_CONTENT_FILTERS_STUB_TITLE]}
						</template>
						<template #main-content-search-not-found-stub>
							${(_babelHelpers$classPr15 = babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_MAIN_CONTENT_SEARCH_NOT_FOUND]) != null ? _babelHelpers$classPr15 : main_core.Loc.getMessage('UI_JS_ENTITY_CATALOG_GROUP_LIST_ITEM_LIST_SEARCH_STUB_DEFAULT_TITLE')}
						</template>
						<template v-if="${Boolean(babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_MAIN_CONTENT_SEARCH_STUB])}" #main-content-search-stub>
							${babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_MAIN_CONTENT_SEARCH_STUB]}
						</template>
						<template #main-content-welcome-stub>
							${(_babelHelpers$classPr16 = babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_MAIN_CONTENT_WELCOME_STUB]) != null ? _babelHelpers$classPr16 : ''}
						</template>
						<template #main-content-no-selected-group-stub>
							${(_babelHelpers$classPr17 = babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_MAIN_CONTENT_NO_SELECTED_GROUP_STUB]) != null ? _babelHelpers$classPr17 : ''}
						</template>
						<template #main-content-empty-group-stub>
							${(_babelHelpers$classPr18 = babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_MAIN_CONTENT_EMPTY_GROUP_STUB]) != null ? _babelHelpers$classPr18 : ''}
						</template>
						<template #main-content-empty-group-stub-title>
							${(_babelHelpers$classPr19 = babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_MAIN_CONTENT_EMPTY_GROUP_STUB_TITLE]) != null ? _babelHelpers$classPr19 : ''}
						</template>
						<template #item="itemSlotProps">
							${(_babelHelpers$classPr20 = babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_MAIN_CONTENT_ITEM]) != null ? _babelHelpers$classPr20 : ''}
						</template>
					</Application>
				`
	  }, rootProps);
	  babelHelpers.classPrivateFieldLooseBase(this, _vueInstance)[_vueInstance] = babelHelpers.classPrivateFieldLooseBase(this, _application)[_application].use(ui_vue3_pinia.createPinia()).mount(container);
	}
	function _getDefaultPopupOptions2() {
	  return {
	    className: 'ui-catalog-popup ui-entity-catalog__scope',
	    titleBar: babelHelpers.classPrivateFieldLooseBase(this, _getPopupTitleBar)[_getPopupTitleBar](),
	    noAllPaddings: true,
	    closeByEsc: true,
	    contentBackground: EntityCatalog.DEFAULT_POPUP_COLOR,
	    draggable: true,
	    width: EntityCatalog.DEFAULT_POPUP_WIDTH,
	    height: EntityCatalog.DEFAULT_POPUP_HEIGHT,
	    minWidth: EntityCatalog.DEFAULT_POPUP_WIDTH,
	    minHeight: EntityCatalog.DEFAULT_POPUP_HEIGHT,
	    autoHide: false
	  };
	}
	function _getPopupTitleBar2() {
	  const titleBar = babelHelpers.classPrivateFieldLooseBase(this, _customTitleBar)[_customTitleBar] ? babelHelpers.classPrivateFieldLooseBase(this, _customTitleBar)[_customTitleBar] : main_core.Tag.render(_t$1 || (_t$1 = _$1`<div>${0}</div>`), main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _popupTitle)[_popupTitle]));
	  return {
	    content: main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
				<div class="popup-window-titlebar-text ui-entity-catalog-popup-titlebar">
					${0}
					
					${0}
					${0}
					<span
						class="popup-window-close-icon popup-window-titlebar-close-icon"
						onclick="${0}"
						></span>
				</div>
			`), titleBar, babelHelpers.classPrivateFieldLooseBase(this, _showSearch)[_showSearch] ? `<div class="ui-entity-catalog__titlebar_search" data-role="titlebar-search"></div>` : '', babelHelpers.classPrivateFieldLooseBase(this, _filterOptions)[_filterOptions].filterItems.length > 0 ? '<div data-role="titlebar-filter"></div>' : '', babelHelpers.classPrivateFieldLooseBase(this, _handleClose)[_handleClose].bind(this))
	  };
	}
	function _handleClose2() {
	  this.close();
	}
	EntityCatalog.DEFAULT_POPUP_WIDTH = 881;
	EntityCatalog.DEFAULT_POPUP_HEIGHT = 621;
	EntityCatalog.DEFAULT_POPUP_COLOR = '#edeef0';
	EntityCatalog.SLOT_GROUP_LIST_HEADER = 'group-list-header';
	EntityCatalog.SLOT_GROUP = 'group';
	EntityCatalog.SLOT_GROUP_LIST_FOOTER = 'group-list-footer';
	EntityCatalog.SLOT_MAIN_CONTENT_HEADER = 'main-content-header';
	EntityCatalog.SLOT_MAIN_CONTENT_FOOTER = 'main-content-footer';
	EntityCatalog.SLOT_MAIN_CONTENT_FILTERS_STUB = 'main-content-filter-stub';
	EntityCatalog.SLOT_MAIN_CONTENT_FILTERS_STUB_TITLE = 'main-content-filter-stub-title';
	EntityCatalog.SLOT_MAIN_CONTENT_SEARCH_NOT_FOUND = 'search-not-found';
	EntityCatalog.SLOT_MAIN_CONTENT_WELCOME_STUB = 'main-content-welcome-stub';
	EntityCatalog.SLOT_MAIN_CONTENT_NO_SELECTED_GROUP_STUB = 'main-content-no-selected-group-stub';
	EntityCatalog.SLOT_MAIN_CONTENT_EMPTY_GROUP_STUB = 'main-content-empty-group-stub';
	EntityCatalog.SLOT_MAIN_CONTENT_EMPTY_GROUP_STUB_TITLE = 'main-content-empty-group-stub-title';
	EntityCatalog.SLOT_MAIN_CONTENT_ITEM = 'main-content-item';
	EntityCatalog.SLOT_MAIN_CONTENT_SEARCH_STUB = 'main-content-search-stub';

	exports.Stubs = Stubs;
	exports.States = States;
	exports.EntityCatalog = EntityCatalog;

}((this.BX.UI = this.BX.UI || {}),BX.Vue3,BX.Vue3.Components,BX.UI.Feedback,BX,BX.UI,BX.UI.Vue3.Components,BX.UI,BX,BX,BX.Vue3.Pinia,BX,BX.Main,BX.Event,BX,BX));
//# sourceMappingURL=entity-catalog.bundle.js.map
