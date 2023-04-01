this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Provider = this.BX.Messenger.v2.Provider || {};
(function (exports,main_core_events,im_v2_const,im_v2_lib_logger) {
	'use strict';

	class RecentService {
	  static getInstance($Bitrix) {
	    if (!this.instance) {
	      this.instance = new this($Bitrix);
	    }
	    return this.instance;
	  }
	  constructor($Bitrix) {
	    this.store = null;
	    this.restClient = null;
	    this.dataIsPreloaded = false;
	    this.itemsPerPage = 50;
	    this.isLoading = false;
	    this.pagesLoaded = 0;
	    this.hasMoreItemsToLoad = true;
	    this.lastMessageDate = null;
	    this.controller = $Bitrix.Data.get('controller');
	    this.store = $Bitrix.Data.get('controller').store;
	    this.restClient = $Bitrix.RestClient.get();
	    this.onUpdateStateHandler = this.onUpdateState.bind(this);
	    this.onUserRequestHandler = this.onUserRequest.bind(this);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.recent.updateState, this.onUpdateStateHandler);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.recent.requestUser, this.onUserRequestHandler);
	  }

	  // region public
	  loadFirstPage({
	    ignorePreloadedItems = false
	  } = {}) {
	    if (this.dataIsPreloaded && !ignorePreloadedItems) {
	      im_v2_lib_logger.Logger.warn(`Im.RecentList: first page was preloaded`);
	      return Promise.resolve();
	    }
	    this.isLoading = true;
	    return this.requestItems({
	      firstPage: true
	    });
	  }
	  loadNextPage() {
	    if (this.isLoading || !this.hasMoreItemsToLoad) {
	      return Promise.resolve();
	    }
	    this.isLoading = true;
	    return this.requestItems();
	  }
	  setPreloadedData(params) {
	    im_v2_lib_logger.Logger.warn(`Im.RecentList: setting preloaded data`, params);
	    const {
	      items,
	      hasMore
	    } = params;
	    this.lastMessageDate = this.getLastMessageDate(items);
	    if (!hasMore) {
	      this.hasMoreItemsToLoad = false;
	    }
	    this.dataIsPreloaded = true;
	    this.updateModels(params);
	  }
	  // endregion public

	  requestItems({
	    firstPage = false
	  } = {}) {
	    const queryParams = {
	      'SKIP_OPENLINES': 'Y',
	      'LIMIT': this.itemsPerPage,
	      'LAST_MESSAGE_DATE': firstPage ? null : this.lastMessageDate,
	      'GET_ORIGINAL_TEXT': 'Y'
	    };
	    return this.restClient.callMethod(im_v2_const.RestMethod.imRecentList, queryParams).then(result => {
	      this.pagesLoaded++;
	      im_v2_lib_logger.Logger.warn(`Im.RecentList: ${this.pagesLoaded} page request result`, result.data());
	      const {
	        items,
	        hasMore
	      } = result.data();
	      this.lastMessageDate = this.getLastMessageDate(items);
	      if (!hasMore) {
	        this.hasMoreItemsToLoad = false;
	      }
	      return this.updateModels(result.data()).then(() => {
	        this.isLoading = false;
	      });
	    }).catch(error => {
	      console.error('Im.RecentList: page request error', error);
	    });
	  }
	  updateModels(rawData) {
	    const {
	      users,
	      dialogues,
	      recent
	    } = this.prepareDataForModels(rawData);
	    const usersPromise = this.store.dispatch('users/set', users);
	    if (rawData.botList) {
	      this.store.dispatch('users/setBotList', rawData.botList);
	    }
	    const dialoguesPromise = this.store.dispatch('dialogues/set', dialogues);
	    const recentPromise = this.store.dispatch('recent/set', recent);
	    return Promise.all([usersPromise, dialoguesPromise, recentPromise]);
	  }
	  onUpdateState({
	    data
	  }) {
	    im_v2_lib_logger.Logger.warn(`Im.RecentList: setting UpdateState data`, data);
	    this.updateModels(data);
	  }
	  onUserRequest({
	    data: {
	      userId
	    }
	  }) {
	    this.restClient.callMethod(im_v2_const.RestMethod.imUserGet, {
	      id: userId
	    }).then(result => {
	      im_v2_lib_logger.Logger.warn(`Im.RecentList: addition user request result`, result.data());
	      this.store.dispatch('users/set', result.data());
	    }).catch(error => {
	      console.error('Im.RecentList: user request error', error);
	    });
	  }
	  prepareDataForModels({
	    items,
	    birthdayList = []
	  }) {
	    const result = {
	      users: [],
	      dialogues: [],
	      recent: []
	    };
	    items.forEach(item => {
	      // user
	      if (item.user && item.user.id && !this.isAddedAlready(result, 'users', item.user.id)) {
	        result.users.push(item.user);
	      }

	      // chat
	      if (item.chat) {
	        result.dialogues.push(this.prepareGroupChat(item));
	        if (item.user.id && !this.isAddedAlready(result, 'dialogues', item.user.id)) {
	          result.dialogues.push(this.prepareChatForAdditionalUser(item.user));
	        }
	      } else if (item.user.id) {
	        const existingRecentItem = this.store.getters['recent/get'](item.user.id);
	        // we should not update real chat with "default" chat data
	        if (!existingRecentItem || !item.options.default_user_record) {
	          result.dialogues.push(this.prepareChatForUser(item));
	        }
	      }

	      // recent
	      result.recent.push({
	        ...item
	      });
	    });
	    birthdayList.forEach(item => {
	      if (!this.isAddedAlready(result, 'users', item.id)) {
	        result.users.push(item);
	        result.dialogues.push(this.prepareChatForAdditionalUser(item));
	      }
	      if (!this.isAddedAlready(result, 'recent', item.id)) {
	        result.recent.push(this.getBirthdayPlaceholder(item));
	      }
	    });
	    im_v2_lib_logger.Logger.warn(`Im.RecentList: prepared data for models`, result);
	    return result;
	  }
	  isAddedAlready(result, type, id) {
	    if (type === 'users') {
	      return result.users.some(user => user.id === id);
	    } else if (type === 'dialogues') {
	      return result.dialogues.some(chat => chat.dialogId === id);
	    } else if (type === 'recent') {
	      return result.recent.some(item => item.id === id);
	    }
	    return false;
	  }
	  prepareGroupChat(item) {
	    return {
	      ...item.chat,
	      counter: item.counter,
	      dialogId: item.id
	    };
	  }
	  prepareChatForUser(item) {
	    return {
	      chatId: item.chat_id,
	      avatar: item.user.avatar,
	      color: item.user.color,
	      dialogId: item.id,
	      name: item.user.name,
	      type: im_v2_const.ChatTypes.user,
	      counter: item.counter
	    };
	  }
	  prepareChatForAdditionalUser(user) {
	    return {
	      dialogId: user.id,
	      avatar: user.avatar,
	      color: user.color,
	      name: user.name,
	      type: im_v2_const.ChatTypes.user
	    };
	  }
	  getBirthdayPlaceholder(item) {
	    return {
	      id: item.id,
	      options: {
	        birthdayPlaceholder: true
	      }
	    };
	  }
	  getLastMessageDate(items) {
	    if (items.length === 0) {
	      return '';
	    }
	    return items.slice(-1)[0].message.date;
	  }
	}
	RecentService.instance = null;

	exports.RecentService = RecentService;

}((this.BX.Messenger.v2.Provider.Service = this.BX.Messenger.v2.Provider.Service || {}),BX.Event,BX.Messenger.v2.Const,BX.Messenger.v2.Lib));
//# sourceMappingURL=registry.bundle.js.map
