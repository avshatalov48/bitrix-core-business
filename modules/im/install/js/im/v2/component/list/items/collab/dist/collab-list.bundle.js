/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_lib_utils,im_v2_component_elements,im_v2_component_list_items_recent,im_v2_application_core,im_v2_lib_rest,im_v2_lib_logger,im_v2_lib_user,main_core,im_v2_const,im_v2_lib_layout,im_v2_lib_menu) {
	'use strict';

	// @vue/component
	const EmptyState = {
	  name: 'EmptyState',
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-list-collab__empty">
			<div class="bx-im-list-collab__empty_icon"></div>
			<div class="bx-im-list-collab__empty_text">
				{{ loc('IM_LIST_COLLAB_EMPTY_V2') }}
			</div>
		</div>
	`
	};

	var _itemsPerPage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("itemsPerPage");
	var _isLoading = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isLoading");
	var _pagesLoaded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pagesLoaded");
	var _hasMoreItemsToLoad = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasMoreItemsToLoad");
	var _lastMessageDate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("lastMessageDate");
	var _requestItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("requestItems");
	var _updateModels = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateModels");
	var _getChatsWithCounters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getChatsWithCounters");
	var _getLastMessageDate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getLastMessageDate");
	var _filterPinnedItemsMessages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filterPinnedItemsMessages");
	class CollabService {
	  constructor() {
	    Object.defineProperty(this, _filterPinnedItemsMessages, {
	      value: _filterPinnedItemsMessages2
	    });
	    Object.defineProperty(this, _getLastMessageDate, {
	      value: _getLastMessageDate2
	    });
	    Object.defineProperty(this, _getChatsWithCounters, {
	      value: _getChatsWithCounters2
	    });
	    Object.defineProperty(this, _updateModels, {
	      value: _updateModels2
	    });
	    Object.defineProperty(this, _requestItems, {
	      value: _requestItems2
	    });
	    this.firstPageIsLoaded = false;
	    Object.defineProperty(this, _itemsPerPage, {
	      writable: true,
	      value: 50
	    });
	    Object.defineProperty(this, _isLoading, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _pagesLoaded, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _hasMoreItemsToLoad, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _lastMessageDate, {
	      writable: true,
	      value: 0
	    });
	  }
	  async loadFirstPage() {
	    babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = true;
	    const result = await babelHelpers.classPrivateFieldLooseBase(this, _requestItems)[_requestItems]({
	      firstPage: true
	    });
	    this.firstPageIsLoaded = true;
	    return result;
	  }
	  loadNextPage() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] || !babelHelpers.classPrivateFieldLooseBase(this, _hasMoreItemsToLoad)[_hasMoreItemsToLoad]) {
	      return Promise.resolve();
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = true;
	    return babelHelpers.classPrivateFieldLooseBase(this, _requestItems)[_requestItems]();
	  }
	  hasMoreItemsToLoad() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _hasMoreItemsToLoad)[_hasMoreItemsToLoad];
	  }
	}
	async function _requestItems2({
	  firstPage = false
	} = {}) {
	  const queryParams = {
	    data: {
	      limit: babelHelpers.classPrivateFieldLooseBase(this, _itemsPerPage)[_itemsPerPage],
	      filter: {
	        lastMessageDate: firstPage ? null : babelHelpers.classPrivateFieldLooseBase(this, _lastMessageDate)[_lastMessageDate]
	      }
	    }
	  };
	  const result = await im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2RecentCollabTail, queryParams).catch(error => {
	    // eslint-disable-next-line no-console
	    console.error('Im.CollabList: page request error', error);
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _pagesLoaded)[_pagesLoaded]++;
	  im_v2_lib_logger.Logger.warn(`Im.CollabList: ${firstPage ? 'First' : babelHelpers.classPrivateFieldLooseBase(this, _pagesLoaded)[_pagesLoaded]} page request result`, result);
	  const {
	    hasNextPage
	  } = result;
	  babelHelpers.classPrivateFieldLooseBase(this, _lastMessageDate)[_lastMessageDate] = babelHelpers.classPrivateFieldLooseBase(this, _getLastMessageDate)[_getLastMessageDate](result);
	  if (!hasNextPage) {
	    babelHelpers.classPrivateFieldLooseBase(this, _hasMoreItemsToLoad)[_hasMoreItemsToLoad] = false;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	  return babelHelpers.classPrivateFieldLooseBase(this, _updateModels)[_updateModels](result);
	}
	function _updateModels2(restResult) {
	  const {
	    users,
	    chats,
	    messages,
	    files,
	    recentItems
	  } = restResult;
	  const chatsWithCounters = babelHelpers.classPrivateFieldLooseBase(this, _getChatsWithCounters)[_getChatsWithCounters](chats, recentItems);
	  const usersPromise = new im_v2_lib_user.UserManager().setUsersToModel(users);
	  const dialoguesPromise = im_v2_application_core.Core.getStore().dispatch('chats/set', chatsWithCounters);
	  const messagesPromise = im_v2_application_core.Core.getStore().dispatch('messages/store', messages);
	  const filesPromise = im_v2_application_core.Core.getStore().dispatch('files/set', files);
	  const recentPromise = im_v2_application_core.Core.getStore().dispatch('recent/setCollab', recentItems);
	  return Promise.all([usersPromise, dialoguesPromise, messagesPromise, filesPromise, recentPromise]);
	}
	function _getChatsWithCounters2(chats, recentItems) {
	  const chatMap = {};
	  chats.forEach(chat => {
	    chatMap[chat.id] = chat;
	  });
	  recentItems.forEach(recentItem => {
	    const {
	      counter,
	      chatId
	    } = recentItem;
	    if (counter === 0) {
	      return;
	    }
	    chatMap[chatId] = {
	      ...chatMap[chatId],
	      counter
	    };
	  });
	  return Object.values(chatMap);
	}
	function _getLastMessageDate2(restResult) {
	  const messages = babelHelpers.classPrivateFieldLooseBase(this, _filterPinnedItemsMessages)[_filterPinnedItemsMessages](restResult);
	  if (messages.length === 0) {
	    return '';
	  }

	  // comparing strings in atom format works correctly because the format is lexically sortable
	  let firstMessageDate = messages[0].date;
	  messages.forEach(message => {
	    if (message.date < firstMessageDate) {
	      firstMessageDate = message.date;
	    }
	  });
	  return firstMessageDate;
	}
	function _filterPinnedItemsMessages2(restResult) {
	  const {
	    messages,
	    recentItems
	  } = restResult;
	  return messages.filter(message => {
	    const chatId = message.chat_id;
	    const recentItem = recentItems.find(item => {
	      return item.chatId === chatId;
	    });
	    return recentItem.pinned === false;
	  });
	}

	class CollabRecentMenu extends im_v2_lib_menu.RecentMenu {
	  getMenuItems() {
	    return [this.getUnreadMessageItem(), this.getPinMessageItem(), this.getMuteItem()
	    // this.getLeaveItem(),
	    ];
	  }

	  getOpenItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_OPEN'),
	      onclick: () => {
	        im_v2_lib_layout.LayoutManager.getInstance().setLayout({
	          name: im_v2_const.Layout.collab.name,
	          entityId: this.context.dialogId
	        });
	        this.menuInstance.close();
	      }
	    };
	  }
	}

	// @vue/component
	const CollabList = {
	  name: 'CollabList',
	  components: {
	    EmptyState,
	    LoadingState: im_v2_component_elements.ListLoadingState,
	    RecentItem: im_v2_component_list_items_recent.RecentItem
	  },
	  emits: ['chatClick'],
	  data() {
	    return {
	      isLoading: false,
	      isLoadingNextPage: false,
	      firstPageLoaded: false
	    };
	  },
	  computed: {
	    collection() {
	      return this.$store.getters['recent/getCollabCollection'];
	    },
	    preparedItems() {
	      return [...this.collection].sort((a, b) => {
	        const firstMessage = this.$store.getters['messages/getById'](a.messageId);
	        const secondMessage = this.$store.getters['messages/getById'](b.messageId);
	        return secondMessage.date - firstMessage.date;
	      });
	    },
	    pinnedItems() {
	      return this.preparedItems.filter(item => {
	        return item.pinned === true;
	      });
	    },
	    generalItems() {
	      return this.preparedItems.filter(item => {
	        return item.pinned === false;
	      });
	    },
	    isEmptyCollection() {
	      return this.collection.length === 0;
	    }
	  },
	  created() {
	    this.contextMenuManager = new CollabRecentMenu();
	  },
	  beforeUnmount() {
	    this.contextMenuManager.destroy();
	  },
	  async activated() {
	    this.isLoading = true;
	    await this.getRecentService().loadFirstPage();
	    this.firstPageLoaded = true;
	    this.isLoading = false;
	  },
	  methods: {
	    async onScroll(event) {
	      this.contextMenuManager.close();
	      if (!im_v2_lib_utils.Utils.dom.isOneScreenRemaining(event.target) || !this.getRecentService().hasMoreItemsToLoad) {
	        return;
	      }
	      this.isLoadingNextPage = true;
	      await this.getRecentService().loadNextPage();
	      this.isLoadingNextPage = false;
	    },
	    onClick(item) {
	      this.$emit('chatClick', item.dialogId);
	    },
	    onRightClick(item, event) {
	      event.preventDefault();
	      this.contextMenuManager.openMenu(item, event.currentTarget);
	    },
	    getRecentService() {
	      if (!this.service) {
	        this.service = new CollabService();
	      }
	      return this.service;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-list-collab__container">
			<LoadingState v-if="isLoading && !firstPageLoaded" />
			<div v-else @scroll="onScroll" class="bx-im-list-collab__scroll-container">
				<EmptyState v-if="isEmptyCollection" />
				<div v-if="pinnedItems.length > 0" class="bx-im-list-collab__pinned_container">
					<RecentItem
						v-for="item in pinnedItems"
						:key="item.dialogId"
						:item="item"
						@click="onClick(item)"
						@click.right="onRightClick(item, $event)"
					/>
				</div>
				<div class="bx-im-list-collab__general_container">
					<RecentItem
						v-for="item in generalItems"
						:key="item.dialogId"
						:item="item"
						@click="onClick(item)"
						@click.right="onRightClick(item, $event)"
					/>
				</div>
				<LoadingState v-if="isLoadingNextPage" />
			</div>
		</div>
	`
	};

	exports.CollabList = CollabList;

}((this.BX.Messenger.v2.Component.List = this.BX.Messenger.v2.Component.List || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.List,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=collab-list.bundle.js.map
