/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,ui_designTokens,ui_fonts_opensans,im_v2_lib_logger,im_v2_lib_search,main_core_events,im_public,im_v2_lib_menu,im_v2_lib_call,im_v2_lib_permission,main_core,im_v2_lib_utils,im_v2_lib_textHighlighter,im_v2_lib_dateFormatter,im_v2_application_core,im_v2_const,im_v2_component_elements) {
	'use strict';

	const SEARCH_REQUEST_ENDPOINT = 'ui.entityselector.doSearch';
	const LOAD_LATEST_RESULTS_ENDPOINT = 'ui.entityselector.load';
	const SAVE_ITEM_ENDPOINT = 'ui.entityselector.saveRecentItems';
	var _searchConfig = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("searchConfig");
	var _storeUpdater = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("storeUpdater");
	var _loadLatestResultsRequest = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadLatestResultsRequest");
	var _searchRequest = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("searchRequest");
	var _getDialogIdAndDate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialogIdAndDate");
	var _getItemsFromRecentItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getItemsFromRecentItems");
	class BaseServerSearch {
	  constructor(searchConfig) {
	    Object.defineProperty(this, _getItemsFromRecentItems, {
	      value: _getItemsFromRecentItems2
	    });
	    Object.defineProperty(this, _getDialogIdAndDate, {
	      value: _getDialogIdAndDate2
	    });
	    Object.defineProperty(this, _searchRequest, {
	      value: _searchRequest2
	    });
	    Object.defineProperty(this, _loadLatestResultsRequest, {
	      value: _loadLatestResultsRequest2
	    });
	    Object.defineProperty(this, _searchConfig, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _storeUpdater, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _searchConfig)[_searchConfig] = searchConfig;
	    babelHelpers.classPrivateFieldLooseBase(this, _storeUpdater)[_storeUpdater] = new im_v2_lib_search.StoreUpdater();
	  }
	  async search(query) {
	    const items = await babelHelpers.classPrivateFieldLooseBase(this, _searchRequest)[_searchRequest](query);
	    await babelHelpers.classPrivateFieldLooseBase(this, _storeUpdater)[_storeUpdater].update(items);
	    return babelHelpers.classPrivateFieldLooseBase(this, _getDialogIdAndDate)[_getDialogIdAndDate](items);
	  }
	  async loadLatestResults() {
	    const response = await babelHelpers.classPrivateFieldLooseBase(this, _loadLatestResultsRequest)[_loadLatestResultsRequest]();
	    const {
	      items,
	      recentItems
	    } = response;
	    if (items.length === 0 || recentItems.length === 0) {
	      return [];
	    }
	    const itemsFromRecentItems = babelHelpers.classPrivateFieldLooseBase(this, _getItemsFromRecentItems)[_getItemsFromRecentItems](recentItems, items);
	    await babelHelpers.classPrivateFieldLooseBase(this, _storeUpdater)[_storeUpdater].update(itemsFromRecentItems);
	    return babelHelpers.classPrivateFieldLooseBase(this, _getDialogIdAndDate)[_getDialogIdAndDate](itemsFromRecentItems);
	  }
	  addItemsToRecentSearchResults(dialogId) {
	    const recentItems = [{
	      id: dialogId,
	      entityId: im_v2_lib_search.EntityId
	    }];
	    const config = {
	      json: {
	        ...im_v2_lib_search.getSearchConfig(babelHelpers.classPrivateFieldLooseBase(this, _searchConfig)[_searchConfig]),
	        recentItems
	      }
	    };
	    return main_core.ajax.runAction(SAVE_ITEM_ENDPOINT, config);
	  }
	}
	async function _loadLatestResultsRequest2() {
	  const config = {
	    json: im_v2_lib_search.getSearchConfig(babelHelpers.classPrivateFieldLooseBase(this, _searchConfig)[_searchConfig])
	  };
	  let items = {
	    items: [],
	    recentItems: []
	  };
	  try {
	    const response = await main_core.ajax.runAction(LOAD_LATEST_RESULTS_ENDPOINT, config);
	    im_v2_lib_logger.Logger.warn('Search service: latest search request result', response);
	    items = response.data.dialog;
	  } catch (error) {
	    im_v2_lib_logger.Logger.warn('Search service: latest search request error', error);
	  }
	  return items;
	}
	async function _searchRequest2(query) {
	  const config = {
	    json: im_v2_lib_search.getSearchConfig(babelHelpers.classPrivateFieldLooseBase(this, _searchConfig)[_searchConfig])
	  };
	  config.json.searchQuery = {
	    queryWords: im_v2_lib_utils.Utils.text.getWordsFromString(query),
	    query
	  };
	  let items = [];
	  try {
	    const response = await main_core.ajax.runAction(SEARCH_REQUEST_ENDPOINT, config);
	    im_v2_lib_logger.Logger.warn('Search service: request result', response);
	    items = response.data.dialog.items;
	  } catch (error) {
	    im_v2_lib_logger.Logger.warn('Search service: error', error);
	  }
	  return items;
	}
	function _getDialogIdAndDate2(items) {
	  return items.map(item => {
	    var _item$customData$date, _item$customData;
	    return {
	      dialogId: item.id.toString(),
	      dateMessage: (_item$customData$date = (_item$customData = item.customData) == null ? void 0 : _item$customData.dateMessage) != null ? _item$customData$date : ''
	    };
	  });
	}
	function _getItemsFromRecentItems2(recentItems, items) {
	  const filledRecentItems = [];
	  recentItems.forEach(([, dialogId]) => {
	    const found = items.find(recentItem => {
	      return recentItem.id === dialogId.toString();
	    });
	    if (found) {
	      filledRecentItems.push(found);
	    }
	  });
	  return filledRecentItems;
	}

	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _localSearch = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("localSearch");
	var _baseServerSearch = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("baseServerSearch");
	var _localCollection = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("localCollection");
	var _isExtranet = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isExtranet");
	class SearchService {
	  constructor(searchConfig) {
	    Object.defineProperty(this, _isExtranet, {
	      value: _isExtranet2
	    });
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _localSearch, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _baseServerSearch, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _localCollection, {
	      writable: true,
	      value: new Map()
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _localSearch)[_localSearch] = new im_v2_lib_search.LocalSearch(searchConfig);
	    babelHelpers.classPrivateFieldLooseBase(this, _baseServerSearch)[_baseServerSearch] = new BaseServerSearch(searchConfig);
	  }
	  loadLatestResults() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _baseServerSearch)[_baseServerSearch].loadLatestResults();
	  }
	  searchLocal(query) {
	    const localCollection = [...babelHelpers.classPrivateFieldLooseBase(this, _localCollection)[_localCollection].values()];
	    return babelHelpers.classPrivateFieldLooseBase(this, _localSearch)[_localSearch].search(query, localCollection);
	  }
	  async search(query) {
	    const searchResult = await babelHelpers.classPrivateFieldLooseBase(this, _baseServerSearch)[_baseServerSearch].search(query);
	    searchResult.forEach(searchItem => {
	      babelHelpers.classPrivateFieldLooseBase(this, _localCollection)[_localCollection].set(searchItem.dialogId, searchItem);
	    });
	    return searchResult;
	  }
	  saveItemToRecentSearch(dialogId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _baseServerSearch)[_baseServerSearch].addItemsToRecentSearchResults(dialogId);
	  }
	  clearSessionResult() {
	    babelHelpers.classPrivateFieldLooseBase(this, _localCollection)[_localCollection].clear();
	  }
	  sortByDate(items) {
	    items.sort((firstItem, secondItem) => {
	      if (!firstItem.dateMessage || !secondItem.dateMessage) {
	        if (!firstItem.dateMessage && !secondItem.dateMessage) {
	          if (babelHelpers.classPrivateFieldLooseBase(this, _isExtranet)[_isExtranet](firstItem.dialogId)) {
	            return 1;
	          }
	          if (babelHelpers.classPrivateFieldLooseBase(this, _isExtranet)[_isExtranet](secondItem.dialogId)) {
	            return -1;
	          }
	          return 0;
	        }
	        return firstItem.dateMessage ? -1 : 1;
	      }
	      return im_v2_lib_utils.Utils.date.cast(secondItem.dateMessage) - im_v2_lib_utils.Utils.date.cast(firstItem.dateMessage);
	    });
	    return items;
	  }
	}
	function _isExtranet2(dialogId) {
	  const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['chats/get'](dialogId);
	  if (!dialog) {
	    return false;
	  }
	  if (dialog.type === im_v2_const.ChatType.user) {
	    const user = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['users/get'](dialogId);
	    return user && user.type === im_v2_const.UserType.extranet;
	  }
	  return dialog.extranet;
	}

	class SearchContextMenu extends im_v2_lib_menu.BaseMenu {
	  constructor() {
	    super();
	    this.id = 'im-chat-search-context-menu';
	    this.callManager = im_v2_lib_call.CallManager.getInstance();
	    this.permissionManager = im_v2_lib_permission.PermissionManager.getInstance();
	  }
	  getMenuItems() {
	    return [this.getOpenItem(), this.getOpenProfileItem(), this.getChatsWithUserItem()];
	  }
	  getOpenItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_OPEN'),
	      onclick: () => {
	        im_public.Messenger.openChat(this.context.dialogId);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getOpenProfileItem() {
	    if (!this.isUser() || this.isBot()) {
	      return null;
	    }
	    const profileUri = im_v2_lib_utils.Utils.user.getProfileLink(this.context.dialogId);
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_OPEN_PROFILE_V2'),
	      href: profileUri,
	      onclick: () => {
	        this.menuInstance.close();
	      }
	    };
	  }
	  getChatsWithUserItem() {
	    if (!this.isUser() || this.isBot()) {
	      return null;
	    }
	    const isAnyChatOpened = this.store.getters['application/getLayout'].entityId.length > 0;
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_FIND_CHATS_WITH_USER_MSGVER_1'),
	      onclick: async () => {
	        if (!isAnyChatOpened) {
	          await im_public.Messenger.openChat(this.context.dialogId);
	        }
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.open, {
	          panel: im_v2_const.SidebarDetailBlock.chatsWithUser,
	          standalone: true,
	          dialogId: this.context.dialogId
	        });
	        this.menuInstance.close();
	      }
	    };
	  }
	  isUser() {
	    return this.store.getters['chats/isUser'](this.context.dialogId);
	  }
	  isBot() {
	    if (!this.isUser()) {
	      return false;
	    }
	    const user = this.store.getters['users/get'](this.context.dialogId);
	    return user.type === im_v2_const.UserType.bot;
	  }
	}

	const ItemTextByChatType = {
	  [im_v2_const.ChatType.openChannel]: main_core.Loc.getMessage('IM_SEARCH_ITEM_OPEN_CHANNEL_TYPE_GROUP'),
	  [im_v2_const.ChatType.generalChannel]: main_core.Loc.getMessage('IM_SEARCH_ITEM_OPEN_CHANNEL_TYPE_GROUP'),
	  [im_v2_const.ChatType.channel]: main_core.Loc.getMessage('IM_SEARCH_ITEM_PRIVATE_CHANNEL_TYPE_GROUP'),
	  [im_v2_const.ChatType.collab]: main_core.Loc.getMessage('IM_SEARCH_ITEM_COLLAB_TYPE'),
	  default: main_core.Loc.getMessage('IM_SEARCH_ITEM_CHAT_TYPE_GROUP_V2')
	};

	// @vue/component
	const SearchItem = {
	  name: 'SearchItem',
	  components: {
	    ChatAvatar: im_v2_component_elements.ChatAvatar,
	    ChatTitleWithHighlighting: im_v2_component_elements.ChatTitleWithHighlighting
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    dateMessage: {
	      type: String,
	      default: ''
	    },
	    withDate: {
	      type: Boolean,
	      default: false
	    },
	    selected: {
	      type: Boolean,
	      required: false
	    },
	    query: {
	      type: String,
	      default: ''
	    }
	  },
	  emits: ['clickItem', 'openContextMenu'],
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    user() {
	      return this.$store.getters['users/get'](this.dialogId, true);
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    isChat() {
	      return !this.isUser;
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.ChatType.user;
	    },
	    position() {
	      if (!this.isUser) {
	        return '';
	      }
	      return this.$store.getters['users/getPosition'](this.dialogId);
	    },
	    userItemText() {
	      if (!this.position) {
	        return this.loc('IM_SEARCH_ITEM_USER_TYPE_GROUP_V2');
	      }
	      return im_v2_lib_textHighlighter.highlightText(main_core.Text.encode(this.position), this.query);
	    },
	    chatItemText() {
	      var _ItemTextByChatType$t;
	      return (_ItemTextByChatType$t = ItemTextByChatType[this.dialog.type]) != null ? _ItemTextByChatType$t : ItemTextByChatType.default;
	    },
	    itemText() {
	      return this.isUser ? this.userItemText : this.chatItemText;
	    },
	    itemTextForTitle() {
	      return this.isUser ? this.position : this.chatItemText;
	    },
	    formattedDate() {
	      if (!this.dateMessage) {
	        return null;
	      }
	      const date = im_v2_lib_utils.Utils.date.cast(this.dateMessage);
	      return this.formatDate(date);
	    }
	  },
	  methods: {
	    onClick(event) {
	      this.$emit('clickItem', {
	        dialogId: this.dialogId,
	        nativeEvent: event
	      });
	    },
	    onRightClick(event) {
	      if (event.altKey && event.shiftKey) {
	        return;
	      }
	      this.$emit('openContextMenu', {
	        dialogId: this.dialogId,
	        nativeEvent: event
	      });
	    },
	    formatDate(date) {
	      return im_v2_lib_dateFormatter.DateFormatter.formatByTemplate(date, im_v2_lib_dateFormatter.DateTemplate.recent);
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div 
			@click="onClick" 
			@click.right.prevent="onRightClick" 
			class="bx-im-search-item__container bx-im-search-item__scope"
			:class="{'--selected': selected}"
		>
			<div class="bx-im-search-item__avatar-container">
				<ChatAvatar 
					:avatarDialogId="dialogId" 
					:contextDialogId="dialogId" 
					:size="AvatarSize.XL" 
				/>
			</div>
			<div class="bx-im-search-item__content-container">
				<div class="bx-im-search-item__content_header">
					<ChatTitleWithHighlighting :dialogId="dialogId" :textToHighlight="query" />
					<div v-if="withDate && formattedDate" class="bx-im-search-item__date">
						<span>{{ formattedDate }}</span>
					</div>
				</div>
				<div class="bx-im-search-item__item-text" :title="itemTextForTitle" v-html="itemText"></div>
			</div>
			<div v-if="selected" class="bx-im-chat-search-item__selected"></div>
		</div>
	`
	};

	// @vue/component
	const MyNotes = {
	  name: 'MyNotes',
	  emits: ['clickItem'],
	  computed: {
	    dialogId() {
	      return im_v2_application_core.Core.getUserId().toString();
	    },
	    name() {
	      return this.$Bitrix.Loc.getMessage('IM_SEARCH_MY_NOTES');
	    }
	  },
	  created() {
	    this.contextMenuManager = new SearchContextMenu();
	  },
	  beforeUnmount() {
	    this.contextMenuManager.destroy();
	  },
	  methods: {
	    onClick(event) {
	      this.$emit('clickItem', {
	        dialogId: this.dialogId,
	        nativeEvent: event
	      });
	    }
	  },
	  template: `
		<div 
			class="bx-im-search-my-notes__container bx-im-search-my-notes__scope"
			@click="onClick" 
			@click.right.prevent
		>
			<div class="bx-im-search-my-notes__avatar"></div>
			<div class="bx-im-search-my-notes__title" :title="name">
				{{ name }}
			</div>
		</div>
	`
	};

	// @vue/component
	const CarouselUser = {
	  name: 'CarouselUser',
	  components: {
	    ChatAvatar: im_v2_component_elements.ChatAvatar
	  },
	  props: {
	    userId: {
	      type: Number,
	      required: true
	    },
	    selected: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emits: ['clickItem', 'openContextMenu'],
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    userDialogId() {
	      return this.userId.toString();
	    },
	    user() {
	      return this.$store.getters['users/get'](this.userDialogId, true);
	    },
	    name() {
	      var _this$user$firstName;
	      return (_this$user$firstName = this.user.firstName) != null ? _this$user$firstName : this.user.name;
	    },
	    isExtranet() {
	      return this.user.type === im_v2_const.UserType.extranet;
	    }
	  },
	  created() {
	    this.contextMenuManager = new SearchContextMenu();
	  },
	  beforeUnmount() {
	    this.contextMenuManager.destroy();
	  },
	  methods: {
	    onClick(event) {
	      this.$emit('clickItem', {
	        dialogId: this.userDialogId,
	        nativeEvent: event
	      });
	    },
	    onRightClick(event) {
	      if (event.altKey && event.shiftKey) {
	        return;
	      }
	      this.$emit('openContextMenu', {
	        dialogId: this.userDialogId,
	        nativeEvent: event
	      });
	    }
	  },
	  template: `
		<div 
			class="bx-im-carousel-user__container bx-im-carousel-user__scope"
			:class="{'--extranet': isExtranet, '--selected': selected}"
			@click="onClick" 
			@click.right.prevent="onRightClick"
		>
			<div v-if="selected" class="bx-im-carousel-user__selected-mark"></div>
			<ChatAvatar 
				:avatarDialogId="userDialogId" 
				:contextDialogId="userDialogId" 
				:size="AvatarSize.XL" 
			/>
			<div class="bx-im-carousel-user__title" :title="name">
				{{ name }}
			</div>
		</div>
	`
	};

	const SHOW_USERS_LIMIT = 6;

	// @vue/component
	const RecentUsersCarousel = {
	  name: 'RecentUsersCarousel',
	  components: {
	    CarouselUser,
	    MyNotes
	  },
	  props: {
	    selectMode: {
	      type: Boolean,
	      default: false
	    },
	    showMyNotes: {
	      type: Boolean,
	      default: true
	    },
	    selectedItems: {
	      type: Array,
	      required: false,
	      default: () => []
	    }
	  },
	  emits: ['clickItem', 'openContextMenu'],
	  computed: {
	    users() {
	      const recentUsers = [];
	      this.$store.getters['recent/getSortedCollection'].forEach(recentItem => {
	        if (this.isChat(recentItem.dialogId)) {
	          return;
	        }
	        const user = this.$store.getters['users/get'](recentItem.dialogId, true);
	        const isBot = user.type === im_v2_const.UserType.bot;
	        if (isBot || user.id === im_v2_application_core.Core.getUserId()) {
	          return;
	        }
	        recentUsers.push(user);
	      });
	      return recentUsers.map(user => user.id);
	    },
	    items() {
	      const limit = this.showMyNotes ? SHOW_USERS_LIMIT - 1 : SHOW_USERS_LIMIT;
	      return this.users.slice(0, limit);
	    },
	    currentUserId() {
	      return im_v2_application_core.Core.getUserId();
	    }
	  },
	  methods: {
	    isChat(dialogId) {
	      return dialogId.startsWith('chat');
	    },
	    isSelected(userId) {
	      const dialogId = userId.toString();
	      return this.selectedItems.includes(dialogId);
	    },
	    loc(key) {
	      return this.$Bitrix.Loc.getMessage(key);
	    }
	  },
	  template: `
		<div class="bx-im-recent-users-carousel__container bx-im-recent-users-carousel__scope">
			<div class="bx-im-recent-users-carousel__title-container">
				<span class="bx-im-recent-users-carousel__section-title">
					{{ loc('IM_SEARCH_SECTION_RECENT_CHATS') }}
				</span>
			</div>
			<div class="bx-im-recent-users-carousel__users-container">
				<MyNotes
					v-if="showMyNotes"
					@clickItem="$emit('clickItem', $event)" 
				/>
				<CarouselUser
					v-for="userId in items"
					:key="userId"
					:userId="userId"
					:selectMode="selectMode"
					:selected="isSelected(userId)"
					@clickItem="$emit('clickItem', $event)"
					@openContextMenu="$emit('openContextMenu', $event)"
				/>
			</div>
		</div>
	`
	};

	// @vue/component
	const LatestSearchResult = {
	  name: 'LatestSearchResult',
	  components: {
	    RecentUsersCarousel,
	    SearchItem,
	    Loader: im_v2_component_elements.Loader
	  },
	  props: {
	    items: {
	      type: Array,
	      default: () => []
	    },
	    isLoading: {
	      type: Boolean,
	      default: false
	    },
	    selectMode: {
	      type: Boolean,
	      default: false
	    },
	    selectedItems: {
	      type: Array,
	      required: false,
	      default: () => []
	    },
	    showMyNotes: {
	      type: Boolean,
	      default: true
	    }
	  },
	  emits: ['clickItem', 'openContextMenu'],
	  computed: {
	    searchItems() {
	      return this.items;
	    },
	    title() {
	      return this.$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_RECENT');
	    }
	  },
	  methods: {
	    isSelected(dialogId) {
	      return this.selectedItems.includes(dialogId.toString());
	    }
	  },
	  template: `
		<div class="bx-im-latest-search-result__scope">
			<RecentUsersCarousel
				:selectMode="selectMode"
				:selectedItems="selectedItems"
				:showMyNotes="showMyNotes"
				@clickItem="$emit('clickItem', $event)"
				@openContextMenu="$emit('openContextMenu', $event)"
			/>
			<div class="bx-im-latest-search-result__title">{{ title }}</div>
			<SearchItem
				v-for="item in searchItems"
				:key="item.dialogId"
				:dialogId="item.dialogId"
				:selected="isSelected(item.dialogId)"
				@clickItem="$emit('clickItem', $event)"
				@openContextMenu="$emit('openContextMenu', $event)"
			/>
			<Loader v-if="isLoading" class="bx-im-latest-search-result__loader" />
		</div>
	`
	};

	// @vue/component
	const EmptyState = {
	  name: 'EmptyState',
	  computed: {
	    title() {
	      return this.$Bitrix.Loc.getMessage('IM_SEARCH_RESULT_NOT_FOUND');
	    },
	    subTitle() {
	      return this.$Bitrix.Loc.getMessage('IM_SEARCH_RESULT_NOT_FOUND_DESCRIPTION');
	    }
	  },
	  template: `
		<div class="bx-im-search-empty-state__container bx-im-search-empty-state__scope">
			<div class="bx-im-search-empty-state__icon"></div>
			<div class="bx-im-search-empty-state__title">
				{{ title }}
			</div>
			<div class="bx-im-search-empty-state__subtitle">
				{{ subTitle }}
			</div>
		</div>
	`
	};

	// @vue/component
	const SearchResult = {
	  name: 'SearchResult',
	  components: {
	    SearchItem,
	    EmptyState,
	    Loader: im_v2_component_elements.Loader
	  },
	  props: {
	    items: {
	      type: Array,
	      default: () => []
	    },
	    isLoading: {
	      type: Boolean,
	      default: false
	    },
	    query: {
	      type: String,
	      default: ''
	    },
	    selectMode: {
	      type: Boolean,
	      default: false
	    },
	    selectedItems: {
	      type: Array,
	      required: false,
	      default: () => []
	    },
	    showMyNotes: {
	      type: Boolean,
	      default: true
	    }
	  },
	  emits: ['clickItem', 'openContextMenu'],
	  computed: {
	    searchResult() {
	      return this.items;
	    },
	    isEmptyState() {
	      return this.items.length === 0;
	    }
	  },
	  methods: {
	    isSelected(item) {
	      return this.selectedItems.includes(item.dialogId);
	    }
	  },
	  template: `
		<div class="bx-im-search-result__scope">
			<SearchItem
				v-for="item in items"
				:key="item.dialogId"
				:dialogId="item.dialogId"
				:dateMessage="item.dateMessage"
				:withDate="true"
				:selectMode="selectMode"
				:isSelected="isSelected(item)"
				:query="query"
				@clickItem="$emit('clickItem', $event)"
				@openContextMenu="$emit('openContextMenu', $event)"
			/>
			<EmptyState v-if="isEmptyState" />
		</div>
	`
	};

	// @vue/component
	const ChatSearch = {
	  name: 'ChatSearch',
	  components: {
	    ScrollWithGradient: im_v2_component_elements.ScrollWithGradient,
	    LatestSearchResult,
	    SearchResult
	  },
	  props: {
	    searchQuery: {
	      type: String,
	      default: ''
	    },
	    searchMode: {
	      type: Boolean,
	      required: true
	    },
	    selectMode: {
	      type: Boolean,
	      default: false
	    },
	    saveSearchHistory: {
	      type: Boolean,
	      default: false
	    },
	    showMyNotes: {
	      type: Boolean,
	      default: true
	    },
	    selectedItems: {
	      type: Array,
	      required: false,
	      default: () => []
	    },
	    searchConfig: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['clickItem', 'loading', 'scroll'],
	  data() {
	    return {
	      isRecentLoading: false,
	      isServerLoading: false,
	      currentServerQueries: 0,
	      result: {
	        recent: [],
	        usersAndChats: []
	      }
	    };
	  },
	  computed: {
	    cleanQuery() {
	      return this.searchQuery.trim().toLowerCase();
	    },
	    showLatestSearchResult() {
	      return this.cleanQuery.length === 0;
	    }
	  },
	  watch: {
	    cleanQuery(newQuery, previousQuery) {
	      if (newQuery.length === 0) {
	        this.searchService.clearSessionResult();
	      }
	      if (newQuery === previousQuery) {
	        return;
	      }
	      this.startSearch(newQuery);
	    },
	    isServerLoading(newValue) {
	      this.$emit('loading', newValue);
	    },
	    searchMode(newValue, oldValue) {
	      if (!newValue && oldValue) {
	        this.searchService.clearSessionResult();
	        void this.loadRecentSearchFromServer();
	      }
	    }
	  },
	  created() {
	    this.initSettings();
	    this.contextMenuManager = new SearchContextMenu();
	    this.searchService = new SearchService(this.searchConfig);
	    this.searchOnServerDelayed = main_core.Runtime.debounce(this.searchOnServer, 400, this);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.errors.accessDenied, this.onDelete);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.search.keyPressed, this.onKeyPressed);
	    void this.loadRecentSearchFromServer();
	  },
	  beforeUnmount() {
	    this.contextMenuManager.destroy();
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.errors.accessDenied, this.onDelete);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.search.keyPressed, this.onKeyPressed);
	  },
	  methods: {
	    async loadRecentSearchFromServer() {
	      this.isRecentLoading = true;
	      this.result.recent = await this.searchService.loadLatestResults();
	      this.isRecentLoading = false;
	    },
	    initSettings() {
	      const settings = main_core.Extension.getSettings('im.v2.component.search.chat-search');
	      const defaultMinTokenSize = 3;
	      this.minTokenSize = settings.get('minTokenSize', defaultMinTokenSize);
	    },
	    startSearch(query) {
	      if (query.length > 0) {
	        const result = this.searchService.searchLocal(query);
	        if (query !== this.cleanQuery) {
	          return;
	        }
	        this.result.usersAndChats = this.searchService.sortByDate(result);
	      }
	      if (query.length >= this.minTokenSize) {
	        this.isServerLoading = true;
	        this.searchOnServerDelayed(query);
	      }
	      if (query.length === 0) {
	        this.cleanSearchResult();
	      }
	    },
	    cleanSearchResult() {
	      this.result.usersAndChats = [];
	    },
	    async searchOnServer(query) {
	      this.currentServerQueries++;
	      const searchResult = await this.searchService.search(query);
	      if (query !== this.cleanQuery) {
	        this.stopLoader();
	        return;
	      }
	      const mergedItems = this.mergeResults(this.result.usersAndChats, searchResult);
	      this.result.usersAndChats = this.searchService.sortByDate(mergedItems);
	      this.stopLoader();
	    },
	    stopLoader() {
	      this.currentServerQueries--;
	      if (this.currentServerQueries > 0) {
	        return;
	      }
	      this.isServerLoading = false;
	    },
	    onOpenContextMenu(event) {
	      if (this.selectMode) {
	        return;
	      }
	      const {
	        dialogId,
	        nativeEvent
	      } = event;
	      if (im_v2_lib_utils.Utils.key.isAltOrOption(nativeEvent)) {
	        return;
	      }
	      this.contextMenuManager.openMenu({
	        dialogId
	      }, nativeEvent.currentTarget);
	    },
	    onDelete({
	      data: eventData
	    }) {
	      const {
	        dialogId
	      } = eventData;
	      this.result.recent = this.result.recent.filter(recentItem => {
	        return recentItem !== dialogId;
	      });
	      this.result.usersAndChats = this.result.usersAndChats.filter(dialogIdFromSearch => {
	        return dialogIdFromSearch !== dialogId;
	      });
	    },
	    onScroll(event) {
	      this.$emit('scroll', event);
	      this.contextMenuManager.destroy();
	    },
	    async onClickItem(event) {
	      if (this.saveSearchHistory) {
	        void this.searchService.saveItemToRecentSearch(event.dialogId);
	      }
	      this.$emit('clickItem', event);
	    },
	    onKeyPressed(event) {
	      if (!this.searchMode) {
	        return;
	      }
	      const {
	        keyboardEvent
	      } = event.getData();
	      if (im_v2_lib_utils.Utils.key.isCombination(keyboardEvent, 'Enter')) {
	        this.onPressEnterKey(event);
	      }
	    },
	    onPressEnterKey(keyboardEvent) {
	      const firstItem = this.getFirstItemFromSearchResults();
	      if (!firstItem) {
	        return;
	      }
	      void this.onClickItem({
	        dialogId: firstItem.dialogId,
	        nativeEvent: keyboardEvent
	      });
	    },
	    getFirstItemFromSearchResults() {
	      if (this.showLatestSearchResult && this.result.recent.length > 0) {
	        return this.result.recent[0];
	      }
	      if (this.result.usersAndChats.length > 0) {
	        return this.result.usersAndChats[0];
	      }
	      return null;
	    },
	    mergeResults(originalItems, newItems) {
	      const mergedItems = [...originalItems, ...newItems].map(item => {
	        return [item.dialogId, item];
	      });
	      const result = new Map(mergedItems);
	      return [...result.values()];
	    }
	  },
	  template: `
		<ScrollWithGradient :gradientHeight="28" :withShadow="false" @scroll="onScroll"> 
			<div class="bx-im-chat-search__container bx-im-chat-search__scope">
				<LatestSearchResult
					v-if="showLatestSearchResult"
					:items="result.recent"
					:selectMode="selectMode"
					:selectedItems="selectedItems"
					:showMyNotes="showMyNotes"
					:isLoading="isRecentLoading"
					@clickItem="onClickItem"
					@openContextMenu="onOpenContextMenu"
				/>
				<SearchResult
					v-else
					:items="result.usersAndChats"
					:selectMode="selectMode"
					:selectedItems="selectedItems"
					:isLoading="isServerLoading"
					:query="cleanQuery"
					@clickItem="onClickItem"
					@openContextMenu="onOpenContextMenu"
				/>
			</div>
		</ScrollWithGradient> 
	`
	};

	exports.ChatSearch = ChatSearch;

}((this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {}),BX,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Component.Elements));
//# sourceMappingURL=chat-search.bundle.js.map
