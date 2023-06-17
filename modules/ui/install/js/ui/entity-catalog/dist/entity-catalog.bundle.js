this.BX = this.BX || {};
(function (exports,ui_vue3,ui_vue3_components_hint,ui_feedback_form,ui_icons,ui_advice,item,button,ui_vue3_pinia,group,main_popup,main_core_events,main_core,group$1) {
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
	  props: {
	    groupData: {
	      type: group.GroupData,
	      required: true
	    }
	  },
	  computed: {
	    hasIcon() {
	      return main_core.Type.isStringFilled(this.groupData.icon);
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
					'--disabled': groupData.disabled
				}"
				@click="handleClick"
			>
				<span class="ui-entity-catalog__menu_item-icon" v-if="hasIcon" v-html="groupData.icon"/>
				<span class="ui-entity-catalog__menu_item-text">{{ groupData.name }}</span>
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
	    groups: {
	      type: Array,
	      required: true
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
		<ul class="ui-entity-catalog__menu">
			<Group
				:group-data="group"
				:key="group.id"
				v-for="group in groups"
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
	`
	};

	const MainGroups = {
	  emits: ['groupSelected'],
	  name: 'ui-entity-catalog-main-groups',
	  components: {
	    GroupList
	  },
	  props: {
	    recentGroupData: {
	      type: group$1.GroupData,
	      required: false
	    },
	    groups: {
	      type: Array,
	      required: true
	    },
	    showRecentGroup: {
	      type: Boolean,
	      default: false
	    },
	    searching: {
	      type: Boolean,
	      default: false
	    }
	  },
	  data() {
	    var _this$recentGroupData, _this$groups$find;
	    const recentGroup = this.getRecentGroup();
	    recentGroup[0] = Object.assign(recentGroup[0], (_this$recentGroupData = this.recentGroupData) != null ? _this$recentGroupData : {});
	    let selectedGroup = (_this$groups$find = this.groups.find(group$$1 => group$$1.selected)) != null ? _this$groups$find : null;
	    if (!selectedGroup) {
	      var _recentGroup$find;
	      selectedGroup = (_recentGroup$find = recentGroup.find(group$$1 => group$$1.selected)) != null ? _recentGroup$find : null;
	    }
	    return {
	      shownGroups: this.groups,
	      selectedGroup: null,
	      recentGroup
	    };
	  },
	  watch: {
	    selectedGroup(newGroup) {
	      const newGroupId = newGroup ? newGroup.id : null;
	      this.shownGroups = this.shownGroups.map(groupList => groupList.map(group$$1 => ({
	        ...group$$1,
	        selected: group$$1.id === newGroupId
	      })));
	      if (this.showRecentGroup && newGroupId !== this.recentGroup[0].id) {
	        this.recentGroup = [Object.assign(this.recentGroup[0], {
	          selected: false
	        })];
	      }
	      this.$emit('groupSelected', newGroup);
	    }
	  },
	  beforeUpdate() {
	    if (this.searching) {
	      this.shownGroups = this.shownGroups.map(groupList => groupList.map(group$$1 => ({
	        ...group$$1,
	        selected: false
	      })));
	      this.recentGroup = [Object.assign(this.recentGroup[0], {
	        selected: false
	      })];
	    }
	  },
	  methods: {
	    getRecentGroup() {
	      return [{
	        id: 'recent',
	        name: main_core.Loc.getMessage('UI_JS_ENTITY_CATALOG_GROUP_LIST_RECENT_GROUP_DEFAULT_NAME'),
	        icon: `
					<svg width="18" height="14" viewBox="0 0 18 14" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path class="ui-entity-catalog__svg-icon-blue" fill-rule="evenodd" clip-rule="evenodd" d="M9.369 13.2593C13.0305 13.2593 15.9986 10.2911 15.9986 6.62965C15.9986 2.9682 13.0305 0 9.369 0C6.00693 0 3.22939 2.50263 2.79764 5.74663H0L3.69844 9.44506L7.39687 5.74663H4.48558C4.90213 3.4276 6.93006 1.66789 9.369 1.66789C12.1093 1.66789 14.3308 3.88935 14.3308 6.62965C14.3308 9.36995 12.1093 11.5914 9.369 11.5914C9.2435 11.5914 9.11909 11.5867 8.99593 11.5776V13.249C9.11941 13.2558 9.2438 13.2593 9.369 13.2593ZM10.0865 4.01429H8.41983V8.18096H9.65978H10.0865H12.1195V6.56367H10.0865V4.01429Z"></path>
					</svg>
				`
	      }];
	    },
	    handleGroupSelected(group$$1) {
	      this.selectedGroup = group$$1;
	    },
	    handleRecentGroupSelected(group$$1) {
	      group$$1.selected = true;
	      this.selectedGroup = group$$1;
	    },
	    handleGroupUnselected() {
	      this.selectedGroup = null;
	    }
	  },
	  template: `
		<div class="ui-entity-catalog__main-groups">
			<div class="ui-entity-catalog__main-groups-head">
				<slot name="group-list-header"/>
			</div>
			<div class="ui-entity-catalog__recently" v-if="showRecentGroup">
				<GroupList
					:groups="recentGroup"
					@groupSelected="handleRecentGroupSelected"
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
					:groups="groupList"
					v-for="groupList in shownGroups"
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
	    }
	  },
	  template: `
		<slot name="item" v-bind:itemData="itemData">
			<div class="ui-entity-catalog__option">
				<div class="ui-entity-catalog__option-info">
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
	      type: Array,
	      required: true
	    },
	    itemsToShow: {
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
	    recentGroupData: {
	      type: group$1.GroupData,
	      required: false
	    },
	    groups: {
	      type: Array,
	      required: true
	    },
	    items: {
	      type: Array,
	      required: true
	    },
	    showEmptyGroups: {
	      type: Boolean,
	      default: false
	    },
	    showRecentGroup: {
	      type: Boolean,
	      default: true
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
	    var _this$recentGroupData, _selectedGroup$id, _selectedGroup;
	    let selectedGroup = null;
	    for (const groupList of this.groups) {
	      selectedGroup = groupList.find(group$$1 => group$$1.selected);
	      if (selectedGroup) {
	        break;
	      }
	    }
	    if (main_core.Type.isNil(selectedGroup) && (_this$recentGroupData = this.recentGroupData) != null && _this$recentGroupData.selected) {
	      var _this$recentGroupData2;
	      selectedGroup = {
	        id: 'recent',
	        ...((_this$recentGroupData2 = this.recentGroupData) != null ? _this$recentGroupData2 : {})
	      };
	    }
	    return {
	      selectedGroup,
	      selectedGroupId: (_selectedGroup$id = (_selectedGroup = selectedGroup) == null ? void 0 : _selectedGroup.id) != null ? _selectedGroup$id : null,
	      shownItems: [],
	      shownGroups: this.getDisplayedGroup(),
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
	    ...ui_vue3_pinia.mapWritableState(useGlobalState, {
	      searching: 'searchApplied',
	      filtersApplied: 'filtersApplied',
	      globalGroup: 'currentGroup',
	      shouldShowWelcomeStub: 'shouldShowWelcomeStub'
	    })
	  },
	  watch: {
	    selectedGroup() {
	      this.shouldShowWelcomeStub = false;
	      this.globalGroup = this.selectedGroup;
	    },
	    selectedGroupId() {
	      if (this.searching) {
	        return;
	      }
	      this.shownItems = this.itemsBySelectedGroupId;
	      this.applyFilters();
	    }
	  },
	  created() {
	    this.shownItems = this.itemsBySelectedGroupId;
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
	      return this.groups.map(groupList => groupList.filter(group$$1 => groupIdsWithItems.has(group$$1.id))).filter(groupList => groupList.length > 0);
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
	      if (!main_core.Type.isStringFilled(queryString)) {
	        this.searching = false;
	        this.shownItems = [];
	        return;
	      }
	      this.searching = true;
	      this.selectedGroup = null;
	      this.selectedGroupId = null;
	      this.shownItems = this.items.filter(item$$1 => {
	        var _item$tags;
	        return String(item$$1.title).toLowerCase().includes(queryString) || String(item$$1.description).toLowerCase().includes(queryString) || ((_item$tags = item$$1.tags) == null ? void 0 : _item$tags.some(tag => tag === queryString));
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
				:recent-group-data="this.recentGroupData"
				:groups="this.shownGroups"
				:show-recent-group="showRecentGroup"
				:searching="searching"
				@group-selected="handleGroupSelected"
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
				:items-to-show="shownItems"
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
	var _recentGroupData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("recentGroupData");
	var _showEmptyGroups = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showEmptyGroups");
	var _showRecentGroup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showRecentGroup");
	var _showSearch = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showSearch");
	var _filterOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filterOptions");
	var _application = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("application");
	var _slots = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("slots");
	var _customComponents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("customComponents");
	var _attachTemplate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("attachTemplate");
	var _getDefaultPopupOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDefaultPopupOptions");
	var _getPopupTitleBar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopupTitleBar");
	var _handleClose = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleClose");
	class EntityCatalog extends main_core_events.EventEmitter {
	  constructor(props) {
	    var _props$slots, _props$customComponen;
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
	    Object.defineProperty(this, _recentGroupData, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _showEmptyGroups, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _showRecentGroup, {
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
	    this.setEventNamespace('BX.UI.EntityCatalog');
	    this.setGroups(main_core.Type.isArray(props.groups) ? props.groups : []);
	    this.setItems(main_core.Type.isArray(props.items) ? props.items : []);
	    babelHelpers.classPrivateFieldLooseBase(this, _recentGroupData)[_recentGroupData] = props.recentGroupData;
	    if (main_core.Type.isBoolean(props.canDeselectGroups)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _groups)[_groups].forEach(groupList => {
	        groupList.forEach(group$$1 => {
	          group$$1.deselectable = props.canDeselectGroups;
	        });
	      });
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _showEmptyGroups)[_showEmptyGroups] = main_core.Type.isBoolean(props.showEmptyGroups) ? props.showEmptyGroups : false;
	    babelHelpers.classPrivateFieldLooseBase(this, _showRecentGroup)[_showRecentGroup] = main_core.Type.isBoolean(props.showRecentGroup) ? props.showRecentGroup : false;
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
	    return this;
	  }
	  getItems() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _items)[_items];
	  }
	  setItems(items) {
	    items = items.map(item$$1 => ({
	      button: {},
	      ...item$$1
	    }));
	    babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].length = 0;
	    babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].push(...items);
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
	  close() {
	    babelHelpers.classPrivateFieldLooseBase(this, _application)[_application].unmount();
	    this.getPopup().close();
	  }
	}
	function _attachTemplate2() {
	  var _babelHelpers$classPr, _babelHelpers$classPr2, _babelHelpers$classPr3, _babelHelpers$classPr4, _babelHelpers$classPr5, _babelHelpers$classPr6, _babelHelpers$classPr7, _babelHelpers$classPr8, _babelHelpers$classPr9, _babelHelpers$classPr10;
	  const context = this;
	  const rootProps = {
	    recentGroupData: babelHelpers.classPrivateFieldLooseBase(this, _recentGroupData)[_recentGroupData],
	    groups: babelHelpers.classPrivateFieldLooseBase(this, _groups)[_groups],
	    items: babelHelpers.classPrivateFieldLooseBase(this, _items)[_items],
	    showEmptyGroups: babelHelpers.classPrivateFieldLooseBase(this, _showEmptyGroups)[_showEmptyGroups],
	    showRecentGroups: babelHelpers.classPrivateFieldLooseBase(this, _showRecentGroup)[_showRecentGroup],
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
	      recentGroupData: Object,
	      groups: Array,
	      items: Array,
	      showEmptyGroups: Boolean,
	      showRecentGroups: Boolean,
	      filterOptions: Object
	    },
	    created() {
	      this.$app = context;
	    },
	    template: `
					<Application
						:recent-group-data="recentGroupData"
						:groups="groups"
						:items="items"
						:show-empty-groups="showEmptyGroups"
						:show-recent-group="showRecentGroups"
						:filter-options="filterOptions"
					>
						<template #group-list-header>
							${(_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_GROUP_LIST_HEADER]) != null ? _babelHelpers$classPr : ''}
						</template>
						<template #group="groupSlotProps">
							${(_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_GROUP]) != null ? _babelHelpers$classPr2 : ''}
						</template>
						<template #group-list-footer>
							${(_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_GROUP_LIST_FOOTER]) != null ? _babelHelpers$classPr3 : ''}
						</template>

						<template #main-content-header>
							${(_babelHelpers$classPr4 = babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_MAIN_CONTENT_HEADER]) != null ? _babelHelpers$classPr4 : ''}
						</template>
						<template #main-content-filter-stub v-if="${!!babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_MAIN_CONTENT_FILTERS_STUB]}">
							${babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_MAIN_CONTENT_FILTERS_STUB]}
						</template>
						<template #main-content-filter-stub-title v-if="${!!babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_MAIN_CONTENT_FILTERS_STUB_TITLE]}">
							${babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_MAIN_CONTENT_FILTERS_STUB_TITLE]}
						</template>
						<template #main-content-search-not-found-stub>
							${(_babelHelpers$classPr5 = babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_MAIN_CONTENT_SEARCH_NOT_FOUND]) != null ? _babelHelpers$classPr5 : main_core.Loc.getMessage('UI_JS_ENTITY_CATALOG_GROUP_LIST_ITEM_LIST_SEARCH_STUB_DEFAULT_TITLE')}
						</template>
						<template #main-content-welcome-stub>
							${(_babelHelpers$classPr6 = babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_MAIN_CONTENT_WELCOME_STUB]) != null ? _babelHelpers$classPr6 : ''}
						</template>
						<template #main-content-no-selected-group-stub>
							${(_babelHelpers$classPr7 = babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_MAIN_CONTENT_NO_SELECTED_GROUP_STUB]) != null ? _babelHelpers$classPr7 : ''}
						</template>
						<template #main-content-empty-group-stub>
							${(_babelHelpers$classPr8 = babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_MAIN_CONTENT_EMPTY_GROUP_STUB]) != null ? _babelHelpers$classPr8 : ''}
						</template>
						<template #main-content-empty-group-stub-title>
							${(_babelHelpers$classPr9 = babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_MAIN_CONTENT_EMPTY_GROUP_STUB_TITLE]) != null ? _babelHelpers$classPr9 : ''}
						</template>
						<template #item="itemSlotProps">
							${(_babelHelpers$classPr10 = babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots][EntityCatalog.SLOT_MAIN_CONTENT_ITEM]) != null ? _babelHelpers$classPr10 : ''}
						</template>
					</Application>
				`
	  }, rootProps);
	  babelHelpers.classPrivateFieldLooseBase(this, _application)[_application].use(ui_vue3_pinia.createPinia()).mount(this.getPopup().getContentContainer());
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
	EntityCatalog.SLOT_MAIN_CONTENT_FILTERS_STUB = 'main-content-filter-stub';
	EntityCatalog.SLOT_MAIN_CONTENT_FILTERS_STUB_TITLE = 'main-content-filter-stub-title';
	EntityCatalog.SLOT_MAIN_CONTENT_SEARCH_NOT_FOUND = 'search-not-found';
	EntityCatalog.SLOT_MAIN_CONTENT_WELCOME_STUB = 'main-content-welcome-stub';
	EntityCatalog.SLOT_MAIN_CONTENT_NO_SELECTED_GROUP_STUB = 'main-content-no-selected-group-stub';
	EntityCatalog.SLOT_MAIN_CONTENT_EMPTY_GROUP_STUB = 'main-content-empty-group-stub';
	EntityCatalog.SLOT_MAIN_CONTENT_EMPTY_GROUP_STUB_TITLE = 'main-content-empty-group-stub-title';
	EntityCatalog.SLOT_MAIN_CONTENT_ITEM = 'main-content-item';

	exports.Stubs = Stubs;
	exports.States = States;
	exports.EntityCatalog = EntityCatalog;

}((this.BX.UI = this.BX.UI || {}),BX.Vue3,BX.Vue3.Components,BX,BX,BX.Ui,BX,BX,BX.Vue3.Pinia,BX,BX.Main,BX.Event,BX,BX));
//# sourceMappingURL=entity-catalog.bundle.js.map
