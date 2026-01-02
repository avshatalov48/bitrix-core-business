/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_component_elements_loader,im_v2_lib_logger,im_public,im_v2_lib_menu,im_v2_lib_call,im_v2_lib_permission,im_v2_lib_textHighlighter,im_v2_lib_dateFormatter,im_v2_component_elements_chatTitle,im_v2_component_elements_avatar,im_v2_component_elements_searchInput,ui_designTokens,ui_fonts_opensans,main_core,main_core_events,im_v2_application_core,im_v2_lib_utils,im_v2_const,im_v2_lib_analytics,im_v2_lib_search,im_v2_component_elements_scrollWithGradient) {
	'use strict';

	const SEARCH_REQUEST_ENDPOINT = 'ui.entityselector.doSearch';
	const LOAD_LATEST_RESULTS_ENDPOINT = 'ui.entityselector.load';
	const SAVE_ITEM_ENDPOINT = 'ui.entityselector.saveRecentItems';
	var _localSearch = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("localSearch");
	var _localCollection = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("localCollection");
	var _searchConfig = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("searchConfig");
	var _storeUpdater = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("storeUpdater");
	var _loadLatestResultsRequest = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadLatestResultsRequest");
	var _searchRequest = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("searchRequest");
	var _getDialogIdAndDate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialogIdAndDate");
	var _getItemsFromRecentItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getItemsFromRecentItems");
	class SearchService {
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
	    Object.defineProperty(this, _localSearch, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _localCollection, {
	      writable: true,
	      value: new Map()
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
	    babelHelpers.classPrivateFieldLooseBase(this, _localSearch)[_localSearch] = new im_v2_lib_search.LocalSearch(searchConfig);
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
	  searchLocal(query) {
	    const localCollection = [...babelHelpers.classPrivateFieldLooseBase(this, _localCollection)[_localCollection].values()];
	    return babelHelpers.classPrivateFieldLooseBase(this, _localSearch)[_localSearch].search(query, localCollection);
	  }
	  async search(query) {
	    const items = await babelHelpers.classPrivateFieldLooseBase(this, _searchRequest)[_searchRequest](query);
	    await babelHelpers.classPrivateFieldLooseBase(this, _storeUpdater)[_storeUpdater].update(items);
	    const searchResult = babelHelpers.classPrivateFieldLooseBase(this, _getDialogIdAndDate)[_getDialogIdAndDate](items);
	    searchResult.forEach(searchItem => {
	      babelHelpers.classPrivateFieldLooseBase(this, _localCollection)[_localCollection].set(searchItem.dialogId, searchItem);
	    });
	    return searchResult;
	  }
	  saveItemToRecentSearch(dialogId) {
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
	    void main_core.ajax.runAction(SAVE_ITEM_ENDPOINT, config);
	  }
	  clearSessionResult() {
	    babelHelpers.classPrivateFieldLooseBase(this, _localCollection)[_localCollection].clear();
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
	      title: main_core.Loc.getMessage('IM_LIB_MENU_OPEN'),
	      onClick: () => {
	        void im_public.Messenger.openChat(this.context.dialogId);
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
	      title: main_core.Loc.getMessage('IM_LIB_MENU_OPEN_PROFILE_V2'),
	      onClick: () => {
	        BX.SidePanel.Instance.open(profileUri);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getChatsWithUserItem() {
	    if (!this.isUser() || this.isBot() || this.isChatWithCurrentUser()) {
	      return null;
	    }
	    const isAnyChatOpened = this.store.getters['application/getLayout'].entityId.length > 0;
	    return {
	      title: main_core.Loc.getMessage('IM_LIB_MENU_FIND_SHARED_CHATS'),
	      onClick: async () => {
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
	  isChatWithCurrentUser() {
	    return this.getCurrentUserId() === Number.parseInt(this.context.dialogId, 10);
	  }
	}

	const getFirstItemFromSearchResults = ({
	  searchResult,
	  recentItems
	}) => {
	  if (searchResult.length > 0) {
	    return searchResult[0];
	  }
	  if (recentItems.length > 0) {
	    return recentItems[0];
	  }
	  return null;
	};

	const DEFAULT_MIN_TOKEN_SIZE = 3;
	const getMinTokenSize = () => {
	  const settings = main_core.Extension.getSettings('im.v2.component.search');
	  return settings.get('minTokenSize', DEFAULT_MIN_TOKEN_SIZE);
	};

	const mergeSearchItems = (originalItems, newItems) => {
	  const mergedItems = [...originalItems, ...newItems].map(item => {
	    return [item.dialogId, item];
	  });
	  const result = new Map(mergedItems);
	  return [...result.values()];
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
	    ChatAvatar: im_v2_component_elements_avatar.ChatAvatar,
	    ChatTitleWithHighlighting: im_v2_component_elements_chatTitle.ChatTitleWithHighlighting
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
	    },
	    replaceWithNotes: {
	      type: Boolean,
	      default: true
	    }
	  },
	  emits: ['clickItem', 'openContextMenu'],
	  computed: {
	    AvatarSize: () => im_v2_component_elements_avatar.AvatarSize,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    isChat() {
	      return !this.isUser;
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.ChatType.user;
	    },
	    isNotes() {
	      if (!this.replaceWithNotes) {
	        return false;
	      }
	      return this.$store.getters['chats/isNotes'](this.dialogId);
	    },
	    avatarType() {
	      if (!this.replaceWithNotes) {
	        return '';
	      }
	      return this.isNotes ? im_v2_component_elements_avatar.ChatAvatarType.notes : '';
	    },
	    titleType() {
	      if (!this.replaceWithNotes) {
	        return '';
	      }
	      return this.isNotes ? im_v2_component_elements_chatTitle.ChatTitleType.notes : '';
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
	      if (this.isNotes) {
	        return this.notesText;
	      }
	      return this.isUser ? this.userItemText : this.chatItemText;
	    },
	    itemTextForTitle() {
	      if (this.isNotes) {
	        return this.notesText;
	      }
	      return this.isUser ? this.position : this.chatItemText;
	    },
	    notesText() {
	      return this.loc('IM_LIST_RECENT_CHAT_SELF_SUBTITLE');
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
					:customType="avatarType"
				/>
			</div>
			<div class="bx-im-search-item__content-container" :class="{'--centered': isNotes}">
				<div class="bx-im-search-item__content_header">
					<ChatTitleWithHighlighting
						:dialogId="dialogId"
						:textToHighlight="query"
						:customType="titleType"
						:showItsYou="!replaceWithNotes"
					/>
					<div v-if="withDate && formattedDate" class="bx-im-search-item__date">
						<span>{{ formattedDate }}</span>
					</div>
				</div>
				<div v-if="itemText" class="bx-im-search-item__item-text" :title="itemTextForTitle" v-html="itemText"></div>
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
	    ChatAvatar: im_v2_component_elements_avatar.ChatAvatar
	  },
	  props: {
	    userDialogId: {
	      type: String,
	      required: true
	    },
	    selected: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emits: ['clickItem', 'openContextMenu'],
	  computed: {
	    AvatarSize: () => im_v2_component_elements_avatar.AvatarSize,
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
	  emits: ['clickItem', 'openContextMenu'],
	  computed: {
	    usersDialogIds() {
	      return im_v2_lib_search.getUsersFromRecentItems({
	        withFakeUsers: false
	      }).map(({
	        dialogId
	      }) => dialogId);
	    },
	    items() {
	      return this.usersDialogIds.slice(0, SHOW_USERS_LIMIT - 1);
	    }
	  },
	  methods: {
	    isChat(dialogId) {
	      return dialogId.startsWith('chat');
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
					@clickItem="$emit('clickItem', $event)" 
				/>
				<CarouselUser
					v-for="userDialogId in items"
					:key="userDialogId"
					:userDialogId="userDialogId"
					@clickItem="$emit('clickItem', $event)"
					@openContextMenu="$emit('openContextMenu', $event)"
				/>
			</div>
		</div>
	`
	};

	// @vue/component
	const ChatSearch = {
	  name: 'ChatSearch',
	  components: {
	    ScrollWithGradient: im_v2_component_elements_scrollWithGradient.ScrollWithGradient,
	    SearchItem,
	    EmptyState,
	    RecentUsersCarousel,
	    Loader: im_v2_component_elements_loader.Loader
	  },
	  props: {
	    query: {
	      type: String,
	      default: ''
	    },
	    searchMode: {
	      type: Boolean,
	      required: true
	    }
	  },
	  emits: ['loading'],
	  data() {
	    return {
	      isRecentLoading: false,
	      isServerLoading: false,
	      currentServerQueries: 0,
	      recentItems: [],
	      searchResult: []
	    };
	  },
	  computed: {
	    cleanQuery() {
	      return this.query.trim().toLowerCase();
	    },
	    showLatestSearchResult() {
	      return this.cleanQuery.length === 0;
	    },
	    isEmptyState() {
	      return this.searchResult.length === 0;
	    }
	  },
	  watch: {
	    cleanQuery(newQuery) {
	      if (newQuery.length === 0) {
	        this.cleanSearchResult();
	      }
	      this.startSearch(newQuery);
	    },
	    isServerLoading(newValue) {
	      this.$emit('loading', newValue);
	    },
	    searchMode(newValue) {
	      if (!newValue) {
	        this.searchService.clearSessionResult();
	        void this.loadRecentSearchFromServer();
	      }
	    }
	  },
	  created() {
	    this.contextMenuManager = new SearchContextMenu();
	    this.searchService = new SearchService({
	      chats: true,
	      users: true
	    });
	    this.searchOnServerDelayed = main_core.Runtime.debounce(this.searchOnServer, 400, this);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.errors.accessDenied, this.onDelete);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.search.keyPressed, this.onKeyPressed);
	    void this.loadRecentSearchFromServer();
	  },
	  beforeUnmount() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.errors.accessDenied, this.onDelete);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.search.keyPressed, this.onKeyPressed);
	  },
	  methods: {
	    async loadRecentSearchFromServer() {
	      this.isRecentLoading = true;
	      this.recentItems = await this.searchService.loadLatestResults();
	      this.isRecentLoading = false;
	    },
	    startSearch(query) {
	      if (query.length > 0) {
	        const result = this.searchService.searchLocal(query);
	        if (query !== this.cleanQuery) {
	          return;
	        }
	        this.searchResult = im_v2_lib_search.sortByDate(result);
	      }
	      if (query.length >= getMinTokenSize()) {
	        this.isServerLoading = true;
	        this.searchOnServerDelayed(query);
	      }
	    },
	    cleanSearchResult() {
	      this.searchResult = [];
	      this.searchService.clearSessionResult();
	    },
	    async searchOnServer(query) {
	      this.currentServerQueries++;
	      const searchResult = await this.searchService.search(query);
	      if (query !== this.cleanQuery) {
	        this.stopLoader();
	        return;
	      }
	      const mergedItems = mergeSearchItems(this.searchResult, searchResult);
	      this.searchResult = im_v2_lib_search.sortByDate(mergedItems);
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
	      this.recentItems = this.recentItems.filter(recentItem => {
	        return recentItem !== dialogId;
	      });
	      this.searchResult = this.searchResult.filter(dialogIdFromSearch => {
	        return dialogIdFromSearch !== dialogId;
	      });
	    },
	    onScroll() {
	      this.contextMenuManager.destroy();
	    },
	    async onClickItem(event) {
	      const {
	        dialogId,
	        nativeEvent
	      } = event;
	      this.searchService.saveItemToRecentSearch(dialogId);
	      void im_public.Messenger.openChat(dialogId);
	      if (!im_v2_lib_utils.Utils.key.isAltOrOption(nativeEvent)) {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.search.close);
	      }
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
	      const firstItem = getFirstItemFromSearchResults({
	        searchResult: this.searchResult,
	        recentItems: this.recentItems
	      });
	      if (!firstItem) {
	        return;
	      }
	      void this.onClickItem({
	        dialogId: firstItem.dialogId,
	        nativeEvent: keyboardEvent
	      });
	    },
	    loc(key) {
	      return this.$Bitrix.Loc.getMessage(key);
	    }
	  },
	  template: `
		<ScrollWithGradient :gradientHeight="28" :withShadow="false" @scroll="onScroll"> 
			<div class="bx-im-chat-search__container">
				<template v-if="showLatestSearchResult">
					<RecentUsersCarousel
						@clickItem="onClickItem"
						@openContextMenu="onOpenContextMenu"
					/>
					<div class="bx-im-chat-search__title">{{ loc('IM_SEARCH_SECTION_RECENT') }}</div>
					<SearchItem
						v-for="item in recentItems"
						:key="item.dialogId"
						:dialogId="item.dialogId"
						:replaceWithNotes="true"
						@clickItem="onClickItem"
						@openContextMenu="onOpenContextMenu"
					/>
					<Loader v-if="isRecentLoading" class="bx-im-chat-search__loader" />
				</template>
				<template v-else>
					<SearchItem
						v-for="item in searchResult"
						:key="item.dialogId"
						:dialogId="item.dialogId"
						:dateMessage="item.dateMessage"
						:withDate="true"
						:query="cleanQuery"
						:replaceWithNotes="true"
						@clickItem="onClickItem"
						@openContextMenu="onOpenContextMenu"
					/>
					<EmptyState v-if="isEmptyState" />
				</template>
			</div>
		</ScrollWithGradient> 
	`
	};

	// @vue/component
	const ChatSearchInput = {
	  name: 'ChatSearchInput',
	  components: {
	    SearchInput: im_v2_component_elements_searchInput.SearchInput
	  },
	  props: {
	    searchMode: {
	      type: Boolean,
	      required: true
	    },
	    isLoading: {
	      type: Boolean,
	      required: false
	    },
	    delayForFocusOnStart: {
	      type: [Number, null],
	      default: null
	    },
	    withIcon: {
	      type: Boolean,
	      default: true
	    }
	  },
	  emits: ['closeSearch', 'openSearch', 'updateSearch'],
	  created() {
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.search.close, this.onClose);
	  },
	  beforeUnmount() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.search.close, this.onClose);
	  },
	  methods: {
	    onInputFocus() {
	      this.$emit('openSearch');
	    },
	    onClose() {
	      this.$emit('closeSearch');
	    },
	    onInputUpdate(query) {
	      this.$emit('updateSearch', query);
	    },
	    onKeyPressed(event) {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.search.keyPressed, {
	        keyboardEvent: event
	      });
	    },
	    loc(key) {
	      return this.$Bitrix.Loc.getMessage(key);
	    }
	  },
	  template: `
		<SearchInput
			:placeholder="loc('IM_SEARCH_INPUT_PLACEHOLDER_V2')"
			:searchMode="searchMode"
			:isLoading="isLoading"
			:withLoader="true"
			:delayForFocusOnStart="delayForFocusOnStart"
			:withIcon="withIcon"
			@inputFocus="onInputFocus"
			@inputBlur="onClose"
			@queryChange="onInputUpdate"
			@keyPressed="onKeyPressed"
			@close="onClose"
			@closeByEsc="onClose"
		/>
	`
	};

	// @vue/component
	const AddToChat = {
	  name: 'AddToChat',
	  components: {
	    ScrollWithGradient: im_v2_component_elements_scrollWithGradient.ScrollWithGradient,
	    SearchItem,
	    EmptyState
	  },
	  props: {
	    query: {
	      type: String,
	      default: ''
	    },
	    dialogId: {
	      type: String,
	      required: true
	    },
	    selectedItems: {
	      type: Array,
	      required: false,
	      default: () => []
	    }
	  },
	  emits: ['clickItem'],
	  data() {
	    return {
	      isLoading: false,
	      currentServerQueries: 0,
	      searchResult: []
	    };
	  },
	  computed: {
	    showLatestSearchResult() {
	      return this.query.length === 0;
	    },
	    isEmptyState() {
	      return this.searchResult.length === 0;
	    }
	  },
	  watch: {
	    query(newQuery) {
	      if (newQuery.length === 0) {
	        this.cleanSearchResult();
	      }
	      this.startSearch(newQuery);
	    }
	  },
	  created() {
	    this.searchService = new SearchService({
	      chats: false,
	      users: true
	    });
	    this.searchOnServerDelayed = main_core.Runtime.debounce(this.searchOnServer, 400, this);
	    this.recentSearchItems = im_v2_lib_search.getUsersFromRecentItems({
	      withFakeUsers: true
	    });
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.search.keyPressed, this.onKeyPressed);
	  },
	  beforeUnmount() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.search.keyPressed, this.onKeyPressed);
	  },
	  methods: {
	    startSearch(query) {
	      if (query.length > 0) {
	        const result = this.searchService.searchLocal(query);
	        if (query !== this.query) {
	          return;
	        }
	        this.searchResult = im_v2_lib_search.sortByDate(result);
	      }
	      if (query.length >= getMinTokenSize()) {
	        this.isLoading = true;
	        this.searchOnServerDelayed(query);
	      }
	    },
	    cleanSearchResult() {
	      this.searchService.clearSessionResult();
	      this.searchResult = [];
	    },
	    async searchOnServer(query) {
	      this.currentServerQueries++;
	      const searchResult = await this.searchService.search(query);
	      if (query !== this.query) {
	        this.stopLoader();
	        return;
	      }
	      const mergedItems = mergeSearchItems(this.searchResult, searchResult);
	      this.searchResult = im_v2_lib_search.sortByDate(mergedItems);
	      this.stopLoader();
	    },
	    stopLoader() {
	      this.currentServerQueries--;
	      if (this.currentServerQueries > 0) {
	        return;
	      }
	      this.isLoading = false;
	    },
	    async onClickItem(event, itemIndex) {
	      im_v2_lib_analytics.Analytics.getInstance().userAdd.onSelectUserFromSearchResult({
	        dialogId: this.dialogId,
	        position: itemIndex + 1
	      });
	      this.$emit('clickItem', event);
	    },
	    async onClickItemRecentItem(event, itemIndex) {
	      im_v2_lib_analytics.Analytics.getInstance().userAdd.onSelectUserFromRecent({
	        dialogId: this.dialogId,
	        position: itemIndex + 1
	      });
	      this.$emit('clickItem', event);
	    },
	    onKeyPressed(event) {
	      const {
	        keyboardEvent
	      } = event.getData();
	      if (im_v2_lib_utils.Utils.key.isCombination(keyboardEvent, 'Enter')) {
	        this.onPressEnterKey(event);
	      }
	    },
	    onPressEnterKey(keyboardEvent) {
	      const firstItem = getFirstItemFromSearchResults({
	        searchResult: this.searchResult,
	        recentItems: this.recentSearchItems
	      });
	      if (!firstItem) {
	        return;
	      }
	      void this.onClickItem({
	        dialogId: firstItem.dialogId,
	        nativeEvent: keyboardEvent
	      });
	    },
	    isSelected(dialogId) {
	      return this.selectedItems.includes(dialogId);
	    },
	    loc(key) {
	      return this.$Bitrix.Loc.getMessage(key);
	    }
	  },
	  template: `
		<ScrollWithGradient :gradientHeight="28" :withShadow="false"> 
			<div class="bx-im-chat-search__container">
				<div class="bx-im-chat-search__title">
					{{ loc('IM_SEARCH_SECTION_RECENT_CHATS') }}
				</div>
				<template v-if="showLatestSearchResult">
					<SearchItem
						v-for="(item, index) in recentSearchItems"
						:key="item.dialogId"
						:dialogId="item.dialogId"
						:selected="isSelected(item.dialogId)"
						:replaceWithNotes="false"
						@clickItem="onClickItemRecentItem($event, index)"
					/>
				</template>
				<template v-else>
					<SearchItem
						v-for="(item, index) in searchResult"
						:key="item.dialogId"
						:dialogId="item.dialogId"
						:dateMessage="item.dateMessage"
						:withDate="true"
						:isSelected="isSelected(item.dialogId)"
						:query="query"
						:replaceWithNotes="false"
						@clickItem="onClickItem($event, index)"
					/>
					<EmptyState v-if="isEmptyState" />
				</template>
			</div>
		</ScrollWithGradient> 
	`
	};

	// @vue/component
	const ForwardSearch = {
	  name: 'ForwardSearch',
	  components: {
	    ScrollWithGradient: im_v2_component_elements_scrollWithGradient.ScrollWithGradient,
	    SearchItem,
	    EmptyState
	  },
	  props: {
	    query: {
	      type: String,
	      default: ''
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['clickItem', 'loading'],
	  data() {
	    return {
	      isLoading: false,
	      currentServerQueries: 0,
	      searchResult: []
	    };
	  },
	  computed: {
	    showLatestSearchResult() {
	      return this.query.length === 0;
	    },
	    recentSearchItems() {
	      const filteredRecent = this.recentListItems.filter(({
	        dialogId
	      }) => {
	        return dialogId !== im_v2_application_core.Core.getUserId().toString();
	      });
	      filteredRecent.unshift({
	        dialogId: im_v2_application_core.Core.getUserId().toString(),
	        dateMessage: ''
	      });
	      return filteredRecent;
	    },
	    isEmptyState() {
	      return this.searchResult.length === 0;
	    }
	  },
	  watch: {
	    query(newQuery) {
	      if (newQuery.length === 0) {
	        this.cleanSearchResult();
	      }
	      this.startSearch(newQuery);
	    },
	    isLoading(newValue) {
	      this.$emit('loading', newValue);
	    }
	  },
	  created() {
	    this.searchService = new SearchService({
	      chats: true,
	      users: true
	    });
	    this.searchOnServerDelayed = main_core.Runtime.debounce(this.searchOnServer, 400, this);
	    this.recentListItems = im_v2_lib_search.getRecentListItems({
	      withFakeUsers: true
	    });
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.search.keyPressed, this.onKeyPressed);
	  },
	  beforeUnmount() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.search.keyPressed, this.onKeyPressed);
	  },
	  methods: {
	    startSearch(query) {
	      if (query.length > 0) {
	        const result = this.searchService.searchLocal(query);
	        if (query !== this.query) {
	          return;
	        }
	        this.searchResult = im_v2_lib_search.sortByDate(result);
	      }
	      if (query.length >= getMinTokenSize()) {
	        this.isLoading = true;
	        this.searchOnServerDelayed(query);
	      }
	    },
	    cleanSearchResult() {
	      this.searchResult = [];
	      this.searchService.clearSessionResult();
	    },
	    async searchOnServer(query) {
	      this.currentServerQueries++;
	      const searchResult = await this.searchService.search(query);
	      if (query !== this.query) {
	        this.stopLoader();
	        return;
	      }
	      const mergedItems = mergeSearchItems(this.searchResult, searchResult);
	      this.searchResult = im_v2_lib_search.sortByDate(mergedItems);
	      this.stopLoader();
	    },
	    stopLoader() {
	      this.currentServerQueries--;
	      if (this.currentServerQueries > 0) {
	        return;
	      }
	      this.isLoading = false;
	    },
	    async onClickItem(event, itemIndex) {
	      im_v2_lib_analytics.Analytics.getInstance().messageForward.onSelectRecipientFromSearchResult({
	        dialogId: this.dialogId,
	        position: itemIndex + 1
	      });
	      this.$emit('clickItem', event);
	    },
	    async onClickRecentItem(event, itemIndex) {
	      im_v2_lib_analytics.Analytics.getInstance().messageForward.onSelectRecipientFromRecent({
	        dialogId: this.dialogId,
	        position: itemIndex + 1
	      });
	      this.$emit('clickItem', event);
	    },
	    onKeyPressed(event) {
	      const {
	        keyboardEvent
	      } = event.getData();
	      if (im_v2_lib_utils.Utils.key.isCombination(keyboardEvent, 'Enter')) {
	        this.onPressEnterKey(event);
	      }
	    },
	    onPressEnterKey(keyboardEvent) {
	      const firstItem = getFirstItemFromSearchResults({
	        recentItems: this.recentSearchItems,
	        searchResult: this.searchResult
	      });
	      if (!firstItem) {
	        return;
	      }
	      void this.onClickItem({
	        dialogId: firstItem.dialogId,
	        nativeEvent: keyboardEvent
	      });
	    },
	    loc(key) {
	      return this.$Bitrix.Loc.getMessage(key);
	    }
	  },
	  template: `
		<ScrollWithGradient :gradientHeight="28" :withShadow="false"> 
			<div class="bx-im-chat-search__container">
				<div class="bx-im-chat-search__title">
					{{ loc('IM_SEARCH_SECTION_RECENT_CHATS') }}
				</div>
				<template v-if="showLatestSearchResult">
					<SearchItem
						v-for="(item, index) in recentSearchItems"
						:key="item.dialogId"
						:dialogId="item.dialogId"
						:replaceWithNotes="true"
						@clickItem="onClickRecentItem($event, index)"
					/>
				</template>
				<template v-else>
					<SearchItem
						v-for="(item, index) in searchResult"
						:key="item.dialogId"
						:dialogId="item.dialogId"
						:dateMessage="item.dateMessage"
						:withDate="true"
						:query="query"
						:replaceWithNotes="true"
						@clickItem="onClickItem($event, index)"
					/>
					<EmptyState v-if="isEmptyState" />
				</template>
			</div>
		</ScrollWithGradient> 
	`
	};

	exports.ChatSearch = ChatSearch;
	exports.ChatSearchInput = ChatSearchInput;
	exports.AddToChat = AddToChat;
	exports.ForwardSearch = ForwardSearch;

}((this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {}),BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.Elements,BX,BX,BX,BX.Event,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements));
//# sourceMappingURL=registry.bundle.js.map
