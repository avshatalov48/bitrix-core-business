/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,im_v2_lib_user,ui_vue3_vuex,im_v2_application_core,im_v2_const,im_v2_lib_utils) {
	'use strict';

	const EntityId = 'im-recent-v2';
	const ContextId = 'IM_CHAT_SEARCH';
	const SearchDialogId = 'im-chat-search';
	const getSearchConfig = searchConfig => {
	  const entity = {
	    id: EntityId,
	    dynamicLoad: true,
	    dynamicSearch: true,
	    options: prepareConfigOptions(searchConfig)
	  };
	  return {
	    dialog: {
	      entities: [entity],
	      preselectedItems: [],
	      clearUnavailableItems: false,
	      context: ContextId,
	      id: SearchDialogId
	    }
	  };
	};
	const prepareConfigOptions = searchConfig => {
	  const options = {
	    withChatByUsers: false
	  };
	  if (!searchConfig) {
	    return {
	      ...options,
	      exclude: []
	    };
	  }
	  const exclude = [];
	  if (main_core.Type.isBoolean(searchConfig.chats) && !searchConfig.chats) {
	    exclude.push('chats');
	  }
	  if (main_core.Type.isBoolean(searchConfig.users) && !searchConfig.users) {
	    exclude.push('users');
	  }
	  return {
	    ...options,
	    exclude
	  };
	};

	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _userManager = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userManager");
	var _updateChats = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateChats");
	var _prepareDataForModels = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareDataForModels");
	class StoreUpdater {
	  constructor() {
	    Object.defineProperty(this, _prepareDataForModels, {
	      value: _prepareDataForModels2
	    });
	    Object.defineProperty(this, _updateChats, {
	      value: _updateChats2
	    });
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _userManager, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _userManager)[_userManager] = new im_v2_lib_user.UserManager();
	  }
	  update(items) {
	    const {
	      users,
	      chats
	    } = babelHelpers.classPrivateFieldLooseBase(this, _prepareDataForModels)[_prepareDataForModels](items);
	    return Promise.all([this.updateUsers(users), babelHelpers.classPrivateFieldLooseBase(this, _updateChats)[_updateChats](chats)]);
	  }
	  updateUsers(users) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _userManager)[_userManager].setUsersToModel(users);
	  }
	}
	function _updateChats2(dialogues) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('chats/set', dialogues);
	}
	function _prepareDataForModels2(items) {
	  const result = {
	    users: [],
	    chats: []
	  };
	  items.forEach(item => {
	    const itemData = item.customData;
	    if (item.entityType === im_v2_const.SearchEntityIdTypes.imUser) {
	      result.users.push(itemData);
	    }
	    if (item.entityType === im_v2_const.SearchEntityIdTypes.chat) {
	      result.chats.push({
	        ...itemData,
	        dialogId: item.id
	      });
	    }
	  });
	  return result;
	}

	const collator = new Intl.Collator(undefined, {
	  sensitivity: 'base'
	});
	var _searchConfig = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("searchConfig");
	var _store$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _search = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("search");
	var _getRecentListItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getRecentListItems");
	var _prepareRecentItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareRecentItem");
	var _searchByQueryWords = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("searchByQueryWords");
	var _searchByDialogFields = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("searchByDialogFields");
	var _searchByUserFields = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("searchByUserFields");
	var _doesItemMatchQuery = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("doesItemMatchQuery");
	var _getLocalItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getLocalItems");
	var _getLocalItemsFromDialogIds = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getLocalItemsFromDialogIds");
	var _mergeItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("mergeItems");
	var _filterByConfig = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filterByConfig");
	var _getRecentItemDate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getRecentItemDate");
	class LocalSearch {
	  constructor(searchConfig) {
	    Object.defineProperty(this, _getRecentItemDate, {
	      value: _getRecentItemDate2
	    });
	    Object.defineProperty(this, _filterByConfig, {
	      value: _filterByConfig2
	    });
	    Object.defineProperty(this, _mergeItems, {
	      value: _mergeItems2
	    });
	    Object.defineProperty(this, _getLocalItemsFromDialogIds, {
	      value: _getLocalItemsFromDialogIds2
	    });
	    Object.defineProperty(this, _getLocalItems, {
	      value: _getLocalItems2
	    });
	    Object.defineProperty(this, _doesItemMatchQuery, {
	      value: _doesItemMatchQuery2
	    });
	    Object.defineProperty(this, _searchByUserFields, {
	      value: _searchByUserFields2
	    });
	    Object.defineProperty(this, _searchByDialogFields, {
	      value: _searchByDialogFields2
	    });
	    Object.defineProperty(this, _searchByQueryWords, {
	      value: _searchByQueryWords2
	    });
	    Object.defineProperty(this, _prepareRecentItem, {
	      value: _prepareRecentItem2
	    });
	    Object.defineProperty(this, _getRecentListItems, {
	      value: _getRecentListItems2
	    });
	    Object.defineProperty(this, _search, {
	      value: _search2
	    });
	    Object.defineProperty(this, _searchConfig, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _store$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _searchConfig)[_searchConfig] = searchConfig;
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1] = im_v2_application_core.Core.getStore();
	  }
	  search(query, localCollection) {
	    const localItems = babelHelpers.classPrivateFieldLooseBase(this, _getLocalItems)[_getLocalItems](localCollection);
	    const result = babelHelpers.classPrivateFieldLooseBase(this, _search)[_search](query, localItems);
	    return babelHelpers.classPrivateFieldLooseBase(this, _filterByConfig)[_filterByConfig](result);
	  }
	}
	function _search2(query, localItems) {
	  const queryWords = im_v2_lib_utils.Utils.text.getWordsFromString(query);
	  const foundItems = new Map();
	  localItems.forEach(localItem => {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _searchByQueryWords)[_searchByQueryWords](localItem, queryWords)) {
	      foundItems.set(localItem.dialogId, {
	        dialogId: localItem.dialogId,
	        dateMessage: localItem.dateMessage
	      });
	    }
	  });
	  return [...foundItems.values()];
	}
	function _getRecentListItems2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].getters['recent/getSortedCollection'].map(item => {
	    const itemDate = babelHelpers.classPrivateFieldLooseBase(this, _getRecentItemDate)[_getRecentItemDate](item);
	    return babelHelpers.classPrivateFieldLooseBase(this, _prepareRecentItem)[_prepareRecentItem](item.dialogId, itemDate);
	  });
	}
	function _prepareRecentItem2(dialogId, dateMessage) {
	  const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].getters['chats/get'](dialogId, true);
	  const isUser = dialog.type === im_v2_const.ChatType.user;
	  const recentItem = {
	    dialogId,
	    dialog,
	    dateMessage
	  };
	  if (isUser) {
	    recentItem.user = babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].getters['users/get'](dialogId, true);
	  }
	  return recentItem;
	}
	function _searchByQueryWords2(localItem, queryWords) {
	  if (localItem.user) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _searchByUserFields)[_searchByUserFields](localItem, queryWords);
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _searchByDialogFields)[_searchByDialogFields](localItem, queryWords);
	}
	function _searchByDialogFields2(localItem, queryWords) {
	  const searchField = [];
	  if (localItem.dialog.name) {
	    const dialogNameWords = im_v2_lib_utils.Utils.text.getWordsFromString(localItem.dialog.name.toLowerCase());
	    searchField.push(...dialogNameWords);
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _doesItemMatchQuery)[_doesItemMatchQuery](searchField, queryWords);
	}
	function _searchByUserFields2(localItem, queryWords) {
	  const searchField = [];
	  if (localItem.user.name) {
	    const userNameWords = im_v2_lib_utils.Utils.text.getWordsFromString(localItem.user.name.toLowerCase());
	    searchField.push(...userNameWords);
	  }
	  if (localItem.user.workPosition) {
	    const workPositionWords = im_v2_lib_utils.Utils.text.getWordsFromString(localItem.user.workPosition.toLowerCase());
	    searchField.push(...workPositionWords);
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _doesItemMatchQuery)[_doesItemMatchQuery](searchField, queryWords);
	}
	function _doesItemMatchQuery2(fieldsForSearch, queryWords) {
	  let found = 0;
	  queryWords.forEach(queryWord => {
	    let queryWordsMatchCount = 0;
	    fieldsForSearch.forEach(field => {
	      const word = field.slice(0, queryWord.length);
	      if (collator.compare(queryWord, word) === 0) {
	        queryWordsMatchCount++;
	      }
	    });
	    if (queryWordsMatchCount > 0) {
	      found++;
	    }
	  });
	  return found >= queryWords.length;
	}
	function _getLocalItems2(localCollection) {
	  const recentItems = babelHelpers.classPrivateFieldLooseBase(this, _getRecentListItems)[_getRecentListItems]();
	  const localItems = babelHelpers.classPrivateFieldLooseBase(this, _getLocalItemsFromDialogIds)[_getLocalItemsFromDialogIds](localCollection);
	  return babelHelpers.classPrivateFieldLooseBase(this, _mergeItems)[_mergeItems](localItems, recentItems);
	}
	function _getLocalItemsFromDialogIds2(localCollection) {
	  return localCollection.map(item => {
	    return babelHelpers.classPrivateFieldLooseBase(this, _prepareRecentItem)[_prepareRecentItem](item.dialogId, item.dateMessage);
	  });
	}
	function _mergeItems2(items1, items2) {
	  const itemsMap = new Map();
	  const mergedArray = [...items1, ...items2];
	  for (const recentItem of mergedArray) {
	    if (!itemsMap.has(recentItem.dialogId)) {
	      itemsMap.set(recentItem.dialogId, recentItem);
	    }
	  }
	  return [...itemsMap.values()];
	}
	function _filterByConfig2(items) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _searchConfig)[_searchConfig]) {
	    return items;
	  }
	  return items.filter(item => {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _searchConfig)[_searchConfig].chats && item.dialogId.startsWith('chat')) {
	      return true;
	    }
	    return !item.dialogId.startsWith('chat') && babelHelpers.classPrivateFieldLooseBase(this, _searchConfig)[_searchConfig].users;
	  });
	}
	function _getRecentItemDate2(item) {
	  var _babelHelpers$classPr;
	  const dateMessage = (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].getters['recent/getMessage'](item.dialogId)) == null ? void 0 : _babelHelpers$classPr.date;
	  if (!dateMessage) {
	    return '';
	  }
	  return dateMessage.toISOString();
	}

	exports.getSearchConfig = getSearchConfig;
	exports.EntityId = EntityId;
	exports.StoreUpdater = StoreUpdater;
	exports.LocalSearch = LocalSearch;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Messenger.v2.Lib,BX.Vue3.Vuex,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib));
//# sourceMappingURL=search.bundle.js.map
