/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Provider = this.BX.Messenger.v2.Provider || {};
(function (exports,im_v2_provider_service,im_v2_lib_layout,im_v2_lib_uuid,im_public,ui_vue3_vuex,ui_notification,main_core,im_v2_lib_user,rest_client,im_v2_lib_utils,main_core_events,ui_uploader_core,im_v2_lib_logger,im_v2_lib_rest,im_v2_application_core,im_v2_const,im_v2_lib_analytics) {
	'use strict';

	var _restResult = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restResult");
	var _withBirthdays = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("withBirthdays");
	var _users = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("users");
	var _chats = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chats");
	var _messages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("messages");
	var _files = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("files");
	var _recentItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("recentItems");
	var _extractUser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("extractUser");
	var _extractChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("extractChat");
	var _extractMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("extractMessage");
	var _extractRecentItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("extractRecentItem");
	var _extractBirthdayItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("extractBirthdayItems");
	var _prepareGroupChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareGroupChat");
	var _prepareChatForUser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareChatForUser");
	var _prepareChatForAdditionalUser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareChatForAdditionalUser");
	var _getBirthdayPlaceholder = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getBirthdayPlaceholder");
	class RecentDataExtractor {
	  constructor(params) {
	    Object.defineProperty(this, _getBirthdayPlaceholder, {
	      value: _getBirthdayPlaceholder2
	    });
	    Object.defineProperty(this, _prepareChatForAdditionalUser, {
	      value: _prepareChatForAdditionalUser2
	    });
	    Object.defineProperty(this, _prepareChatForUser, {
	      value: _prepareChatForUser2
	    });
	    Object.defineProperty(this, _prepareGroupChat, {
	      value: _prepareGroupChat2
	    });
	    Object.defineProperty(this, _extractBirthdayItems, {
	      value: _extractBirthdayItems2
	    });
	    Object.defineProperty(this, _extractRecentItem, {
	      value: _extractRecentItem2
	    });
	    Object.defineProperty(this, _extractMessage, {
	      value: _extractMessage2
	    });
	    Object.defineProperty(this, _extractChat, {
	      value: _extractChat2
	    });
	    Object.defineProperty(this, _extractUser, {
	      value: _extractUser2
	    });
	    Object.defineProperty(this, _restResult, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _withBirthdays, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _users, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _chats, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _messages, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _files, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _recentItems, {
	      writable: true,
	      value: {}
	    });
	    const {
	      rawData,
	      withBirthdays = true
	    } = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _withBirthdays)[_withBirthdays] = withBirthdays;
	    babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult] = rawData;
	  }
	  getItems() {
	    const {
	      items = []
	    } = babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult];
	    items.forEach(item => {
	      babelHelpers.classPrivateFieldLooseBase(this, _extractUser)[_extractUser](item);
	      babelHelpers.classPrivateFieldLooseBase(this, _extractChat)[_extractChat](item);
	      babelHelpers.classPrivateFieldLooseBase(this, _extractMessage)[_extractMessage](item);
	      babelHelpers.classPrivateFieldLooseBase(this, _extractRecentItem)[_extractRecentItem](item);
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _extractBirthdayItems)[_extractBirthdayItems]();
	    return {
	      users: Object.values(babelHelpers.classPrivateFieldLooseBase(this, _users)[_users]),
	      chats: Object.values(babelHelpers.classPrivateFieldLooseBase(this, _chats)[_chats]),
	      messages: Object.values(babelHelpers.classPrivateFieldLooseBase(this, _messages)[_messages]),
	      files: Object.values(babelHelpers.classPrivateFieldLooseBase(this, _files)[_files]),
	      recentItems: Object.values(babelHelpers.classPrivateFieldLooseBase(this, _recentItems)[_recentItems])
	    };
	  }
	}
	function _extractUser2(item) {
	  var _item$user;
	  if ((_item$user = item.user) != null && _item$user.id && !babelHelpers.classPrivateFieldLooseBase(this, _users)[_users][item.user.id]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _users)[_users][item.user.id] = item.user;
	  }
	}
	function _extractChat2(item) {
	  if (item.chat) {
	    babelHelpers.classPrivateFieldLooseBase(this, _chats)[_chats][item.id] = babelHelpers.classPrivateFieldLooseBase(this, _prepareGroupChat)[_prepareGroupChat](item);
	    if (item.user.id && !babelHelpers.classPrivateFieldLooseBase(this, _chats)[_chats][item.user.id]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _chats)[_chats][item.user.id] = babelHelpers.classPrivateFieldLooseBase(this, _prepareChatForAdditionalUser)[_prepareChatForAdditionalUser](item.user);
	    }
	  } else if (item.user.id) {
	    const existingRecentItem = im_v2_application_core.Core.getStore().getters['recent/get'](item.user.id);
	    // we should not update real chat with "default" chat data
	    if (!existingRecentItem || !item.options.default_user_record) {
	      babelHelpers.classPrivateFieldLooseBase(this, _chats)[_chats][item.user.id] = babelHelpers.classPrivateFieldLooseBase(this, _prepareChatForUser)[_prepareChatForUser](item);
	    }
	  }
	}
	function _extractMessage2(item) {
	  const message = item.message;
	  if (!message) {
	    return;
	  }
	  if (message.id === 0) {
	    message.id = `${im_v2_const.FakeMessagePrefix}-${item.id}`;
	  }
	  let viewedByOthers = false;
	  if (message.status === im_v2_const.MessageStatus.delivered) {
	    viewedByOthers = true;
	  }
	  if (main_core.Type.isPlainObject(message.file)) {
	    const file = message.file;
	    message.files = [file.id];
	    const existingFile = im_v2_application_core.Core.getStore().getters['files/get'](file.id);
	    // recent has shortened file format, we should not rewrite file if model has it
	    if (!existingFile) {
	      babelHelpers.classPrivateFieldLooseBase(this, _files)[_files][file.id] = file;
	    }
	  }
	  const existingMessage = im_v2_application_core.Core.getStore().getters['messages/getById'](message.id);
	  // recent has shortened attach format, we should not rewrite attach if model has it
	  if (main_core.Type.isArrayFilled(existingMessage == null ? void 0 : existingMessage.attach)) {
	    delete message.attach;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _messages)[_messages][message.id] = {
	    ...message,
	    viewedByOthers
	  };
	}
	function _extractRecentItem2(item) {
	  var _item$message$id, _item$message;
	  const messageId = (_item$message$id = (_item$message = item.message) == null ? void 0 : _item$message.id) != null ? _item$message$id : 0;
	  babelHelpers.classPrivateFieldLooseBase(this, _recentItems)[_recentItems][item.id] = {
	    ...item,
	    messageId
	  };
	}
	function _extractBirthdayItems2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _withBirthdays)[_withBirthdays]) {
	    return;
	  }
	  const {
	    birthdayList = []
	  } = babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult];
	  birthdayList.forEach(item => {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _users)[_users][item.id]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _users)[_users][item.id] = item;
	    }
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _chats)[_chats][item.id]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _chats)[_chats][item.id] = babelHelpers.classPrivateFieldLooseBase(this, _prepareChatForAdditionalUser)[_prepareChatForAdditionalUser](item);
	    }
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _recentItems)[_recentItems][item.id]) {
	      const messageId = `${im_v2_const.FakeMessagePrefix}-${item.id}`;
	      babelHelpers.classPrivateFieldLooseBase(this, _recentItems)[_recentItems][item.id] = {
	        ...babelHelpers.classPrivateFieldLooseBase(this, _getBirthdayPlaceholder)[_getBirthdayPlaceholder](item),
	        messageId
	      };
	      babelHelpers.classPrivateFieldLooseBase(this, _messages)[_messages][messageId] = {
	        id: messageId
	      };
	    }
	  });
	}
	function _prepareGroupChat2(item) {
	  return {
	    ...item.chat,
	    counter: item.counter,
	    dialogId: item.id
	  };
	}
	function _prepareChatForUser2(item) {
	  return {
	    chatId: item.chat_id,
	    avatar: item.user.avatar,
	    color: item.user.color,
	    dialogId: item.id,
	    name: item.user.name,
	    type: im_v2_const.ChatType.user,
	    counter: item.counter,
	    role: im_v2_const.UserRole.member
	  };
	}
	function _prepareChatForAdditionalUser2(user) {
	  return {
	    dialogId: user.id,
	    avatar: user.avatar,
	    color: user.color,
	    name: user.name,
	    type: im_v2_const.ChatType.user,
	    role: im_v2_const.UserRole.member
	  };
	}
	function _getBirthdayPlaceholder2(item) {
	  return {
	    id: item.id,
	    isBirthdayPlaceholder: true
	  };
	}

	class RecentService {
	  constructor() {
	    this.dataIsPreloaded = false;
	    this.firstPageIsLoaded = false;
	    this.itemsPerPage = 50;
	    this.isLoading = false;
	    this.pagesLoaded = 0;
	    this.hasMoreItemsToLoad = true;
	    this.lastMessageDate = null;
	  }
	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }

	  // region public
	  getCollection() {
	    return im_v2_application_core.Core.getStore().getters['recent/getRecentCollection'];
	  }
	  async loadFirstPage({
	    ignorePreloadedItems = false
	  } = {}) {
	    if (this.dataIsPreloaded && !ignorePreloadedItems) {
	      im_v2_lib_logger.Logger.warn('Im.RecentList: first page was preloaded');
	      return Promise.resolve();
	    }
	    this.isLoading = true;
	    const result = await this.requestItems({
	      firstPage: true
	    });
	    this.firstPageIsLoaded = true;
	    return result;
	  }
	  loadNextPage() {
	    if (this.isLoading || !this.hasMoreItemsToLoad) {
	      return Promise.resolve();
	    }
	    this.isLoading = true;
	    return this.requestItems();
	  }
	  setPreloadedData(params) {
	    im_v2_lib_logger.Logger.warn('Im.RecentList: setting preloaded data', params);
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
	  hideChat(dialogId) {
	    im_v2_lib_logger.Logger.warn('Im.RecentList: hide chat', dialogId);
	    const recentItem = im_v2_application_core.Core.getStore().getters['recent/get'](dialogId);
	    if (!recentItem) {
	      return;
	    }
	    im_v2_application_core.Core.getStore().dispatch('recent/delete', {
	      id: dialogId
	    });
	    const chatIsOpened = im_v2_application_core.Core.getStore().getters['application/isChatOpen'](dialogId);
	    if (chatIsOpened) {
	      im_public.Messenger.openChat();
	    }
	    im_v2_application_core.Core.getRestClient().callMethod(im_v2_const.RestMethod.imRecentHide, {
	      DIALOG_ID: dialogId
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('Im.RecentList: hide chat error', error);
	    });
	  }
	  // endregion public

	  async requestItems({
	    firstPage = false
	  } = {}) {
	    const queryParams = this.getQueryParams(firstPage);
	    const result = await im_v2_application_core.Core.getRestClient().callMethod(this.getQueryMethod(), queryParams).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('Im.RecentList: page request error', error);
	    });
	    this.pagesLoaded++;
	    im_v2_lib_logger.Logger.warn(`Im.RecentList: ${firstPage ? 'First' : this.pagesLoaded} page request result`, result.data());
	    const {
	      items,
	      hasMore
	    } = result.data();
	    this.lastMessageDate = this.getLastMessageDate(items);
	    if (!hasMore) {
	      this.hasMoreItemsToLoad = false;
	    }
	    this.isLoading = false;
	    return this.updateModels(result.data());
	  }
	  getQueryMethod() {
	    return im_v2_const.RestMethod.imRecentList;
	  }
	  getQueryParams(firstPage) {
	    return {
	      SKIP_OPENLINES: 'Y',
	      LIMIT: this.itemsPerPage,
	      LAST_MESSAGE_DATE: firstPage ? null : this.lastMessageDate,
	      GET_ORIGINAL_TEXT: 'Y',
	      PARSE_TEXT: 'Y'
	    };
	  }
	  getModelSaveMethod() {
	    return 'recent/setRecent';
	  }
	  updateModels(rawData) {
	    const extractor = new RecentDataExtractor({
	      rawData,
	      ...this.getExtractorOptions()
	    });
	    const extractedItems = extractor.getItems();
	    const {
	      users,
	      chats,
	      messages,
	      files,
	      recentItems
	    } = extractedItems;
	    im_v2_lib_logger.Logger.warn('RecentService: prepared data for models', extractedItems);
	    const usersPromise = im_v2_application_core.Core.getStore().dispatch('users/set', users);
	    const dialoguesPromise = im_v2_application_core.Core.getStore().dispatch('chats/set', chats);
	    const messagesPromise = im_v2_application_core.Core.getStore().dispatch('messages/store', messages);
	    const filesPromise = im_v2_application_core.Core.getStore().dispatch('files/set', files);
	    const recentPromise = im_v2_application_core.Core.getStore().dispatch(this.getModelSaveMethod(), recentItems);
	    return Promise.all([usersPromise, dialoguesPromise, messagesPromise, filesPromise, recentPromise]);
	  }
	  getLastMessageDate(items) {
	    if (items.length === 0) {
	      return '';
	    }
	    return items.slice(-1)[0].message.date;
	  }
	  getExtractorOptions() {
	    return {};
	  }
	}
	RecentService.instance = null;

	var _restResult$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restResult");
	class ChatDataExtractor {
	  constructor(restResult) {
	    Object.defineProperty(this, _restResult$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1] = restResult;
	  }
	  getChatId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].chat.id;
	  }
	  getDialogId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].chat.dialogId;
	  }
	  isOpenlinesChat() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].chat.type === im_v2_const.ChatType.lines;
	  }
	  getChats() {
	    const mainChat = {
	      ...babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].chat,
	      hasPrevPage: babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].hasPrevPage,
	      hasNextPage: babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].hasNextPage
	    };
	    const chats = {
	      [babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].chat.dialogId]: mainChat
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].users.forEach(user => {
	      if (chats[user.id]) {
	        chats[user.id] = {
	          ...chats[user.id],
	          ...im_v2_lib_user.UserManager.getDialogForUser(user)
	        };
	      } else {
	        chats[user.id] = im_v2_lib_user.UserManager.getDialogForUser(user);
	      }
	    });
	    return Object.values(chats);
	  }
	  getFiles() {
	    var _babelHelpers$classPr;
	    return (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].files) != null ? _babelHelpers$classPr : [];
	  }
	  getUsers() {
	    var _babelHelpers$classPr2;
	    return (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].users) != null ? _babelHelpers$classPr2 : [];
	  }
	  getAdditionalUsers() {
	    var _babelHelpers$classPr3;
	    return (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].usersShort) != null ? _babelHelpers$classPr3 : [];
	  }
	  getMessages() {
	    var _babelHelpers$classPr4;
	    return (_babelHelpers$classPr4 = babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].messages) != null ? _babelHelpers$classPr4 : [];
	  }
	  getMessagesToStore() {
	    var _babelHelpers$classPr5;
	    return (_babelHelpers$classPr5 = babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].additionalMessages) != null ? _babelHelpers$classPr5 : [];
	  }
	  getPinnedMessageIds() {
	    var _babelHelpers$classPr6;
	    const pinnedMessageIds = [];
	    const pins = (_babelHelpers$classPr6 = babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].pins) != null ? _babelHelpers$classPr6 : [];
	    pins.forEach(pin => {
	      pinnedMessageIds.push(pin.messageId);
	    });
	    return pinnedMessageIds;
	  }
	  getReactions() {
	    var _babelHelpers$classPr7;
	    return (_babelHelpers$classPr7 = babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].reactions) != null ? _babelHelpers$classPr7 : [];
	  }
	}

	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _requestChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("requestChat");
	var _markDialogAsLoading = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("markDialogAsLoading");
	var _markDialogAsLoaded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("markDialogAsLoaded");
	var _markDialogAsNotLoaded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("markDialogAsNotLoaded");
	var _isDialogLoadedMarkNeeded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isDialogLoadedMarkNeeded");
	var _updateModels = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateModels");
	class LoadService {
	  constructor() {
	    Object.defineProperty(this, _updateModels, {
	      value: _updateModels2
	    });
	    Object.defineProperty(this, _isDialogLoadedMarkNeeded, {
	      value: _isDialogLoadedMarkNeeded2
	    });
	    Object.defineProperty(this, _markDialogAsNotLoaded, {
	      value: _markDialogAsNotLoaded2
	    });
	    Object.defineProperty(this, _markDialogAsLoaded, {
	      value: _markDialogAsLoaded2
	    });
	    Object.defineProperty(this, _markDialogAsLoading, {
	      value: _markDialogAsLoading2
	    });
	    Object.defineProperty(this, _requestChat, {
	      value: _requestChat2
	    });
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	  }
	  loadChat(dialogId) {
	    const params = {
	      dialogId
	    };
	    return babelHelpers.classPrivateFieldLooseBase(this, _requestChat)[_requestChat](im_v2_const.RestMethod.imV2ChatShallowLoad, params);
	  }
	  loadChatWithMessages(dialogId) {
	    const params = {
	      dialogId,
	      messageLimit: im_v2_provider_service.MessageService.getMessageRequestLimit()
	    };
	    return babelHelpers.classPrivateFieldLooseBase(this, _requestChat)[_requestChat](im_v2_const.RestMethod.imV2ChatLoad, params);
	  }
	  loadChatWithContext(dialogId, messageId) {
	    const params = {
	      dialogId,
	      messageId,
	      messageLimit: im_v2_provider_service.MessageService.getMessageRequestLimit()
	    };
	    return babelHelpers.classPrivateFieldLooseBase(this, _requestChat)[_requestChat](im_v2_const.RestMethod.imV2ChatLoadInContext, params);
	  }
	  prepareDialogId(dialogId) {
	    if (!im_v2_lib_utils.Utils.dialog.isExternalId(dialogId)) {
	      return Promise.resolve(dialogId);
	    }
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatGetDialogId, {
	      data: {
	        externalId: dialogId
	      }
	    }).then(result => {
	      return result.dialogId;
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('ChatService: Load: error preparing external id', error);
	    });
	  }
	}
	async function _requestChat2(actionName, params) {
	  const {
	    dialogId
	  } = params;
	  babelHelpers.classPrivateFieldLooseBase(this, _markDialogAsLoading)[_markDialogAsLoading](dialogId);
	  const actionResult = await im_v2_lib_rest.runAction(actionName, {
	    data: params
	  }).catch(error => {
	    // eslint-disable-next-line no-console
	    console.error('ChatService: Load: error loading chat', error);
	    babelHelpers.classPrivateFieldLooseBase(this, _markDialogAsNotLoaded)[_markDialogAsNotLoaded](dialogId);
	    throw error;
	  });
	  const updateModelResult = await babelHelpers.classPrivateFieldLooseBase(this, _updateModels)[_updateModels](actionResult);
	  if (main_core.Type.isStringFilled(updateModelResult == null ? void 0 : updateModelResult.linesDialogId)) {
	    im_v2_lib_layout.LayoutManager.getInstance().setLastOpenedElement(im_v2_const.Layout.chat.name, '');
	    return im_public.Messenger.openLines(updateModelResult.linesDialogId);
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isDialogLoadedMarkNeeded)[_isDialogLoadedMarkNeeded](actionName)) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _markDialogAsLoaded)[_markDialogAsLoaded](dialogId);
	  }
	  return true;
	}
	function _markDialogAsLoading2(dialogId) {
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('chats/update', {
	    dialogId,
	    fields: {
	      loading: true
	    }
	  });
	}
	function _markDialogAsLoaded2(dialogId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('chats/update', {
	    dialogId,
	    fields: {
	      inited: true,
	      loading: false
	    }
	  });
	}
	function _markDialogAsNotLoaded2(dialogId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('chats/update', {
	    dialogId,
	    fields: {
	      loading: false
	    }
	  });
	}
	function _isDialogLoadedMarkNeeded2(actionName) {
	  return actionName !== im_v2_const.RestMethod.imV2ChatShallowLoad;
	}
	function _updateModels2(restResult) {
	  const extractor = new ChatDataExtractor(restResult);
	  if (extractor.isOpenlinesChat()) {
	    return Promise.resolve({
	      linesDialogId: extractor.getDialogId()
	    });
	  }
	  const chatsPromise = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('chats/set', extractor.getChats());
	  const filesPromise = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('files/set', extractor.getFiles());
	  const userManager = new im_v2_lib_user.UserManager();
	  const usersPromise = Promise.all([babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('users/set', extractor.getUsers()), userManager.addUsersToModel(extractor.getAdditionalUsers())]);
	  const messagesPromise = Promise.all([babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/setChatCollection', {
	    messages: extractor.getMessages(),
	    clearCollection: true
	  }), babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/store', extractor.getMessagesToStore()), babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/pin/setPinned', {
	    chatId: extractor.getChatId(),
	    pinnedMessages: extractor.getPinnedMessageIds()
	  }), babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/reactions/set', extractor.getReactions())]);
	  return Promise.all([chatsPromise, filesPromise, usersPromise, messagesPromise]);
	}

	const PRIVATE_CHAT = 'CHAT';
	const OPEN_CHAT = 'OPEN';
	var _restClient = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _store$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _prepareFields = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareFields");
	var _addChatToModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addChatToModel");
	class CreateService {
	  constructor() {
	    Object.defineProperty(this, _addChatToModel, {
	      value: _addChatToModel2
	    });
	    Object.defineProperty(this, _prepareFields, {
	      value: _prepareFields2
	    });
	    Object.defineProperty(this, _restClient, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _store$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient] = im_v2_application_core.Core.getRestClient();
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1] = im_v2_application_core.Core.getStore();
	  }
	  async createChat(chatConfig) {
	    im_v2_lib_logger.Logger.warn('ChatService: createChat', chatConfig);
	    const preparedFields = await babelHelpers.classPrivateFieldLooseBase(this, _prepareFields)[_prepareFields](chatConfig);
	    const createResult = await babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient].callMethod(im_v2_const.RestMethod.imV2ChatAdd, {
	      fields: preparedFields
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('ChatService: createChat error:', error);
	      throw new Error(error);
	    });
	    const {
	      chatId: newChatId
	    } = createResult.data();
	    im_v2_lib_logger.Logger.warn('ChatService: createChat result', newChatId);
	    const newDialogId = `chat${newChatId}`;
	    babelHelpers.classPrivateFieldLooseBase(this, _addChatToModel)[_addChatToModel](newDialogId, preparedFields);
	    return {
	      newDialogId,
	      newChatId
	    };
	  }
	}
	async function _prepareFields2(chatConfig) {
	  var _preparedConfig$manag, _preparedConfig$membe, _preparedConfig$type$, _preparedConfig$type, _preparedConfig$entit, _preparedConfig$entit2, _preparedConfig$title, _preparedConfig$avata, _preparedConfig$descr, _preparedConfig$owner, _preparedConfig$manag2, _preparedConfig$manag3, _preparedConfig$manag4, _preparedConfig$manag5, _preparedConfig$canPo, _preparedConfig$confe;
	  const preparedConfig = {
	    ...chatConfig
	  };
	  if (preparedConfig.avatar) {
	    preparedConfig.avatar = await im_v2_lib_utils.Utils.file.getBase64(chatConfig.avatar);
	  }
	  preparedConfig.managers = (_preparedConfig$manag = preparedConfig.managers) != null ? _preparedConfig$manag : [];
	  preparedConfig.members = (_preparedConfig$membe = preparedConfig.members) != null ? _preparedConfig$membe : [];
	  const allMembers = [...preparedConfig.members, ...preparedConfig.managers];
	  if (preparedConfig.ownerId) {
	    allMembers.push(preparedConfig.ownerId);
	  }
	  preparedConfig.members = [...new Set(allMembers)];
	  return {
	    type: (_preparedConfig$type$ = (_preparedConfig$type = preparedConfig.type) == null ? void 0 : _preparedConfig$type.toUpperCase()) != null ? _preparedConfig$type$ : null,
	    entityType: (_preparedConfig$entit = (_preparedConfig$entit2 = preparedConfig.entityType) == null ? void 0 : _preparedConfig$entit2.toUpperCase()) != null ? _preparedConfig$entit : null,
	    title: (_preparedConfig$title = preparedConfig.title) != null ? _preparedConfig$title : null,
	    avatar: (_preparedConfig$avata = preparedConfig.avatar) != null ? _preparedConfig$avata : null,
	    description: (_preparedConfig$descr = preparedConfig.description) != null ? _preparedConfig$descr : null,
	    users: preparedConfig.members,
	    managers: preparedConfig.managers,
	    ownerId: (_preparedConfig$owner = preparedConfig.ownerId) != null ? _preparedConfig$owner : null,
	    searchable: preparedConfig.isAvailableInSearch ? 'Y' : 'N',
	    manageUsersAdd: (_preparedConfig$manag2 = preparedConfig.manageUsersAdd) != null ? _preparedConfig$manag2 : null,
	    manageUsersDelete: (_preparedConfig$manag3 = preparedConfig.manageUsersDelete) != null ? _preparedConfig$manag3 : null,
	    manageUi: (_preparedConfig$manag4 = preparedConfig.manageUi) != null ? _preparedConfig$manag4 : null,
	    manageSettings: (_preparedConfig$manag5 = preparedConfig.manageSettings) != null ? _preparedConfig$manag5 : null,
	    canPost: (_preparedConfig$canPo = preparedConfig.canPost) != null ? _preparedConfig$canPo : null,
	    conferencePassword: (_preparedConfig$confe = preparedConfig.conferencePassword) != null ? _preparedConfig$confe : null
	  };
	}
	function _addChatToModel2(newDialogId, chatConfig) {
	  let chatType = chatConfig.searchable === 'Y' ? OPEN_CHAT : PRIVATE_CHAT;
	  if (main_core.Type.isStringFilled(chatConfig.entityType)) {
	    chatType = chatConfig.entityType.toLowerCase();
	  }
	  if (main_core.Type.isStringFilled(chatConfig.type)) {
	    chatType = chatConfig.type.toLowerCase();
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('chats/set', {
	    dialogId: newDialogId,
	    type: chatType.toLowerCase(),
	    name: chatConfig.title,
	    userCounter: chatConfig.users.length,
	    role: im_v2_const.UserRole.owner,
	    canPost: chatConfig.canPost
	  });
	}

	const MAX_AVATAR_SIZE = 180;
	class UpdateService {
	  async prepareAvatar(avatarFile) {
	    if (!ui_uploader_core.isResizableImage(avatarFile)) {
	      // eslint-disable-next-line no-console
	      return Promise.reject(new Error('UpdateService: prepareAvatar: incorrect image'));
	    }
	    const {
	      preview: resizedAvatar
	    } = await ui_uploader_core.resizeImage(avatarFile, {
	      width: MAX_AVATAR_SIZE,
	      height: MAX_AVATAR_SIZE
	    });
	    return resizedAvatar;
	  }
	  async changeAvatar(chatId, avatarFile) {
	    im_v2_lib_logger.Logger.warn('ChatService: changeAvatar', chatId, avatarFile);
	    const avatarInBase64 = await im_v2_lib_utils.Utils.file.getBase64(avatarFile);
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatUpdate, {
	      data: {
	        id: chatId,
	        fields: {
	          avatar: avatarInBase64
	        }
	      }
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('ChatService: changeAvatar error:', error);
	      throw new Error(error);
	    });
	  }
	}

	var _store$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _updateChatTitleInModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateChatTitleInModel");
	class RenameService {
	  constructor() {
	    Object.defineProperty(this, _updateChatTitleInModel, {
	      value: _updateChatTitleInModel2
	    });
	    Object.defineProperty(this, _store$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$2)[_store$2] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$1)[_restClient$1] = im_v2_application_core.Core.getRestClient();
	  }
	  renameChat(dialogId, newName) {
	    im_v2_lib_logger.Logger.warn('ChatService: renameChat', dialogId, newName);
	    if (newName === '') {
	      return Promise.resolve();
	    }
	    const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$2)[_store$2].getters['chats/get'](dialogId);
	    const oldName = dialog.name;
	    babelHelpers.classPrivateFieldLooseBase(this, _updateChatTitleInModel)[_updateChatTitleInModel](dialogId, newName);
	    return babelHelpers.classPrivateFieldLooseBase(this, _restClient$1)[_restClient$1].callMethod(im_v2_const.RestMethod.imChatUpdateTitle, {
	      dialog_id: dialogId,
	      title: newName
	    }).then(result => {
	      im_v2_lib_logger.Logger.warn('ChatService: renameChat result', result.data());
	      return Promise.resolve();
	    }).catch(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _updateChatTitleInModel)[_updateChatTitleInModel](dialogId, oldName);
	      throw new Error('Chat rename error');
	    });
	  }
	}
	function _updateChatTitleInModel2(dialogId, title) {
	  babelHelpers.classPrivateFieldLooseBase(this, _store$2)[_store$2].dispatch('chats/update', {
	    dialogId,
	    fields: {
	      name: title
	    }
	  });
	}

	var _store$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _sendMuteRequestDebounced = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendMuteRequestDebounced");
	var _sendMuteRequest = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendMuteRequest");
	class MuteService {
	  constructor() {
	    Object.defineProperty(this, _sendMuteRequest, {
	      value: _sendMuteRequest2
	    });
	    Object.defineProperty(this, _store$3, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _sendMuteRequestDebounced, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$3)[_store$3] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$2)[_restClient$2] = im_v2_application_core.Core.getRestClient();
	    babelHelpers.classPrivateFieldLooseBase(this, _sendMuteRequestDebounced)[_sendMuteRequestDebounced] = main_core.Runtime.debounce(babelHelpers.classPrivateFieldLooseBase(this, _sendMuteRequest)[_sendMuteRequest], ChatService.DEBOUNCE_TIME);
	  }
	  muteChat(dialogId) {
	    im_v2_lib_logger.Logger.warn('ChatService: muteChat', dialogId);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$3)[_store$3].dispatch('chats/mute', {
	      dialogId
	    });
	    const queryParams = {
	      'dialog_id': dialogId,
	      'action': 'Y'
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _sendMuteRequestDebounced)[_sendMuteRequestDebounced](queryParams);
	  }
	  unmuteChat(dialogId) {
	    im_v2_lib_logger.Logger.warn('ChatService: unmuteChat', dialogId);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$3)[_store$3].dispatch('chats/unmute', {
	      dialogId
	    });
	    const queryParams = {
	      'dialog_id': dialogId,
	      'action': 'N'
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _sendMuteRequestDebounced)[_sendMuteRequestDebounced](queryParams);
	  }
	}
	function _sendMuteRequest2(queryParams) {
	  const {
	    dialog_id: dialogId,
	    action
	  } = queryParams;
	  return babelHelpers.classPrivateFieldLooseBase(this, _restClient$2)[_restClient$2].callMethod(im_v2_const.RestMethod.imChatMute, queryParams).catch(error => {
	    const actionText = action === 'Y' ? 'muting' : 'unmuting';
	    console.error(`Im.RecentList: error ${actionText} chat`, error);
	    const actionType = action === 'Y' ? 'chats/unmute' : 'chats/mute';
	    babelHelpers.classPrivateFieldLooseBase(this, _store$3)[_store$3].dispatch(actionType, {
	      dialogId
	    });
	  });
	}

	var _store$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	class PinService {
	  constructor() {
	    Object.defineProperty(this, _store$4, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$3, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$4)[_store$4] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$3)[_restClient$3] = im_v2_application_core.Core.getRestClient();
	  }
	  pinChat(dialogId) {
	    im_v2_lib_logger.Logger.warn('PinService: pinChat', dialogId);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$4)[_store$4].dispatch('recent/pin', {
	      id: dialogId,
	      action: true
	    });
	    const queryParams = {
	      'DIALOG_ID': dialogId,
	      'ACTION': 'Y'
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$3)[_restClient$3].callMethod(im_v2_const.RestMethod.imRecentPin, queryParams).catch(error => {
	      console.error('PinService: error pinning chat', error);
	      babelHelpers.classPrivateFieldLooseBase(this, _store$4)[_store$4].dispatch('recent/pin', {
	        id: dialogId,
	        action: false
	      });
	    });
	  }
	  unpinChat(dialogId) {
	    im_v2_lib_logger.Logger.warn('PinService: unpinChat', dialogId);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$4)[_store$4].dispatch('recent/pin', {
	      id: dialogId,
	      action: false
	    });
	    const queryParams = {
	      'DIALOG_ID': dialogId,
	      'ACTION': 'N'
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$3)[_restClient$3].callMethod(im_v2_const.RestMethod.imRecentPin, queryParams).catch(error => {
	      console.error('PinService: error unpinning chat', error);
	      babelHelpers.classPrivateFieldLooseBase(this, _store$4)[_store$4].dispatch('recent/pin', {
	        id: dialogId,
	        action: true
	      });
	    });
	  }
	}

	const READ_TIMEOUT = 300;
	var _store$5 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _messagesToRead = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("messagesToRead");
	var _readMessagesForChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("readMessagesForChat");
	var _readMessageOnClient = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("readMessageOnClient");
	var _decreaseChatCounter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("decreaseChatCounter");
	var _readMessageOnServer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("readMessageOnServer");
	var _checkChatCounter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("checkChatCounter");
	var _getDialogIdByChatId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialogIdByChatId");
	var _getDialogByChatId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialogByChatId");
	class ReadService {
	  constructor() {
	    Object.defineProperty(this, _getDialogByChatId, {
	      value: _getDialogByChatId2
	    });
	    Object.defineProperty(this, _getDialogIdByChatId, {
	      value: _getDialogIdByChatId2
	    });
	    Object.defineProperty(this, _checkChatCounter, {
	      value: _checkChatCounter2
	    });
	    Object.defineProperty(this, _readMessageOnServer, {
	      value: _readMessageOnServer2
	    });
	    Object.defineProperty(this, _decreaseChatCounter, {
	      value: _decreaseChatCounter2
	    });
	    Object.defineProperty(this, _readMessageOnClient, {
	      value: _readMessageOnClient2
	    });
	    Object.defineProperty(this, _readMessagesForChat, {
	      value: _readMessagesForChat2
	    });
	    Object.defineProperty(this, _store$5, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$4, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _messagesToRead, {
	      writable: true,
	      value: {}
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$4)[_restClient$4] = im_v2_application_core.Core.getRestClient();
	  }
	  readAll() {
	    im_v2_lib_logger.Logger.warn('ReadService: readAll');
	    babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('chats/clearCounters');
	    babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('recent/clearUnread');
	    return babelHelpers.classPrivateFieldLooseBase(this, _restClient$4)[_restClient$4].callMethod(im_v2_const.RestMethod.imV2ChatReadAll).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('ReadService: readAll error', error);
	    });
	  }
	  readDialog(dialogId) {
	    im_v2_lib_logger.Logger.warn('ReadService: readDialog', dialogId);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('recent/unread', {
	      id: dialogId,
	      action: false
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('chats/update', {
	      dialogId,
	      fields: {
	        counter: 0
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$4)[_restClient$4].callMethod(im_v2_const.RestMethod.imV2ChatRead, {
	      dialogId
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('ReadService: error reading chat', error);
	    });
	  }
	  unreadDialog(dialogId) {
	    im_v2_lib_logger.Logger.warn('ReadService: unreadDialog', dialogId);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('recent/unread', {
	      id: dialogId,
	      action: true
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$4)[_restClient$4].callMethod(im_v2_const.RestMethod.imV2ChatUnread, {
	      dialogId
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('ReadService: error setting chat as unread', error);
	      babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('recent/unread', {
	        id: dialogId,
	        action: false
	      });
	    });
	  }
	  readMessage(chatId, messageId) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _messagesToRead)[_messagesToRead][chatId]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _messagesToRead)[_messagesToRead][chatId] = new Set();
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _messagesToRead)[_messagesToRead][chatId].add(messageId);
	    clearTimeout(this.readTimeout);
	    this.readTimeout = setTimeout(() => {
	      Object.entries(babelHelpers.classPrivateFieldLooseBase(this, _messagesToRead)[_messagesToRead]).forEach(([rawChatId, messageIds]) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _readMessagesForChat)[_readMessagesForChat](rawChatId, messageIds);
	      });
	    }, READ_TIMEOUT);
	  }
	  clearDialogMark(dialogId) {
	    im_v2_lib_logger.Logger.warn('ReadService: clear dialog mark', dialogId);
	    const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].getters['chats/get'](dialogId);
	    const recentItem = babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].getters['recent/get'](dialogId);
	    if (dialog.markedId === 0 && !(recentItem != null && recentItem.unread)) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('recent/unread', {
	      id: dialogId,
	      action: false
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('chats/update', {
	      dialogId,
	      fields: {
	        markedId: 0
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$4)[_restClient$4].callMethod(im_v2_const.RestMethod.imV2ChatRead, {
	      dialogId,
	      onlyRecent: 'Y'
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('ReadService: error clearing dialog mark', error);
	    });
	  }
	}
	async function _readMessagesForChat2(rawChatId, messageIds) {
	  const queueChatId = Number.parseInt(rawChatId, 10);
	  im_v2_lib_logger.Logger.warn('ReadService: readMessages', messageIds);
	  if (messageIds.size === 0) {
	    return;
	  }
	  const copiedMessageIds = [...messageIds];
	  delete babelHelpers.classPrivateFieldLooseBase(this, _messagesToRead)[_messagesToRead][queueChatId];
	  const readMessagesCount = await babelHelpers.classPrivateFieldLooseBase(this, _readMessageOnClient)[_readMessageOnClient](queueChatId, copiedMessageIds);
	  im_v2_lib_logger.Logger.warn('ReadService: readMessage, need to reduce counter by', readMessagesCount);
	  await babelHelpers.classPrivateFieldLooseBase(this, _decreaseChatCounter)[_decreaseChatCounter](queueChatId, readMessagesCount);
	  const readResult = await babelHelpers.classPrivateFieldLooseBase(this, _readMessageOnServer)[_readMessageOnServer](queueChatId, copiedMessageIds).catch(error => {
	    // eslint-disable-next-line no-console
	    console.error('ReadService: error reading message', error);
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _checkChatCounter)[_checkChatCounter](readResult);
	}
	function _readMessageOnClient2(chatId, messageIds) {
	  const maxMessageId = Math.max(...messageIds);
	  const dialog = babelHelpers.classPrivateFieldLooseBase(this, _getDialogByChatId)[_getDialogByChatId](chatId);
	  if (maxMessageId > dialog.lastReadId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('chats/update', {
	      dialogId: babelHelpers.classPrivateFieldLooseBase(this, _getDialogIdByChatId)[_getDialogIdByChatId](chatId),
	      fields: {
	        lastId: maxMessageId
	      }
	    });
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('messages/readMessages', {
	    chatId,
	    messageIds
	  });
	}
	function _decreaseChatCounter2(chatId, readMessagesCount) {
	  const chat = babelHelpers.classPrivateFieldLooseBase(this, _getDialogByChatId)[_getDialogByChatId](chatId);
	  let newCounter = chat.counter - readMessagesCount;
	  if (newCounter < 0) {
	    newCounter = 0;
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('chats/update', {
	    dialogId: babelHelpers.classPrivateFieldLooseBase(this, _getDialogIdByChatId)[_getDialogIdByChatId](chatId),
	    fields: {
	      counter: newCounter
	    }
	  });
	}
	function _readMessageOnServer2(chatId, messageIds) {
	  im_v2_lib_logger.Logger.warn('ReadService: readMessages on server', messageIds);
	  return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatMessageRead, {
	    data: {
	      chatId,
	      ids: messageIds,
	      actionUuid: im_v2_lib_uuid.UuidManager.getInstance().getActionUuid()
	    }
	  });
	}
	function _checkChatCounter2(readResult) {
	  const {
	    chatId,
	    counter
	  } = readResult;
	  const dialog = babelHelpers.classPrivateFieldLooseBase(this, _getDialogByChatId)[_getDialogByChatId](chatId);
	  if (dialog.counter > counter) {
	    im_v2_lib_logger.Logger.warn('ReadService: counter from server is lower than local one', dialog.counter, counter);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('chats/update', {
	      dialogId: dialog.dialogId,
	      fields: {
	        counter
	      }
	    });
	  }
	}
	function _getDialogIdByChatId2(chatId) {
	  const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].getters['chats/getByChatId'](chatId);
	  if (!dialog) {
	    return 0;
	  }
	  return dialog.dialogId;
	}
	function _getDialogByChatId2(chatId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].getters['chats/getByChatId'](chatId);
	}

	var _store$6 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$5 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	class UserService {
	  constructor() {
	    Object.defineProperty(this, _store$6, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$5, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$6)[_store$6] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$5)[_restClient$5] = im_v2_application_core.Core.getRestClient();
	  }
	  addToChat(addConfig) {
	    const queryParams = {
	      chat_id: addConfig.chatId,
	      users: addConfig.members,
	      hide_history: !addConfig.showHistory
	    };
	    return babelHelpers.classPrivateFieldLooseBase(this, _restClient$5)[_restClient$5].callMethod(im_v2_const.RestMethod.imChatUserAdd, queryParams);
	  }
	  kickUserFromChat(dialogId, userId) {
	    im_v2_lib_logger.Logger.warn(`UserService: kick user ${userId} from chat ${dialogId}`);
	    const queryParams = {
	      dialogId,
	      userId
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$5)[_restClient$5].callMethod(im_v2_const.RestMethod.imV2ChatDeleteUser, queryParams).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('UserService: error kicking user from chat', error);
	    });
	  }
	  leaveChat(dialogId) {
	    this.kickUserFromChat(dialogId, im_v2_application_core.Core.getUserId());
	    babelHelpers.classPrivateFieldLooseBase(this, _store$6)[_store$6].dispatch('chats/update', {
	      dialogId,
	      fields: {
	        inited: false
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$6)[_store$6].dispatch('recent/delete', {
	      id: dialogId
	    });
	    const chatIsOpened = babelHelpers.classPrivateFieldLooseBase(this, _store$6)[_store$6].getters['application/isChatOpen'](dialogId);
	    if (chatIsOpened) {
	      im_public.Messenger.openChat();
	    }
	  }
	  joinChat(dialogId) {
	    im_v2_lib_logger.Logger.warn(`UserService: join chat ${dialogId}`);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$6)[_store$6].dispatch('chats/update', {
	      dialogId,
	      fields: {
	        role: im_v2_const.UserRole.member
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$5)[_restClient$5].callMethod(im_v2_const.RestMethod.imV2ChatJoin, {
	      dialogId
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('UserService: error joining chat', error);
	    });
	  }
	}

	var _loadService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadService");
	var _createService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createService");
	var _updateService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateService");
	var _renameService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renameService");
	var _muteService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("muteService");
	var _pinService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pinService");
	var _readService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("readService");
	var _userService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userService");
	var _initServices = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initServices");
	class ChatService {
	  constructor() {
	    Object.defineProperty(this, _initServices, {
	      value: _initServices2
	    });
	    Object.defineProperty(this, _loadService, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _createService, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _updateService, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _renameService, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _muteService, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _pinService, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _readService, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _userService, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _initServices)[_initServices]();
	  }

	  // region 'load'
	  loadChat(dialogId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService].loadChat(dialogId);
	  }
	  loadChatWithMessages(dialogId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService].loadChatWithMessages(dialogId);
	  }
	  loadChatWithContext(dialogId, messageId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService].loadChatWithContext(dialogId, messageId);
	  }
	  prepareDialogId(dialogId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService].prepareDialogId(dialogId);
	  }
	  // endregion 'load'

	  // region 'create'
	  createChat(chatConfig) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _createService)[_createService].createChat(chatConfig);
	  }
	  // endregion 'create'

	  // region 'update'
	  prepareAvatar(avatarFile) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _updateService)[_updateService].prepareAvatar(avatarFile);
	  }
	  changeAvatar(chatId, avatarFile) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _updateService)[_updateService].changeAvatar(chatId, avatarFile);
	  }
	  // endregion 'update'

	  // region 'rename'
	  renameChat(dialogId, newName) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _renameService)[_renameService].renameChat(dialogId, newName);
	  }
	  // endregion 'rename'

	  // region 'mute'
	  muteChat(dialogId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _muteService)[_muteService].muteChat(dialogId);
	  }
	  unmuteChat(dialogId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _muteService)[_muteService].unmuteChat(dialogId);
	  }
	  // endregion 'mute'

	  // region 'pin'
	  pinChat(dialogId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _pinService)[_pinService].pinChat(dialogId);
	  }
	  unpinChat(dialogId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _pinService)[_pinService].unpinChat(dialogId);
	  }
	  // endregion 'pin'

	  // region 'read'
	  readAll() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _readService)[_readService].readAll();
	  }
	  readDialog(dialogId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _readService)[_readService].readDialog(dialogId);
	  }
	  unreadDialog(dialogId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _readService)[_readService].unreadDialog(dialogId);
	  }
	  readMessage(chatId, messageId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _readService)[_readService].readMessage(chatId, messageId);
	  }
	  clearDialogMark(dialogId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _readService)[_readService].clearDialogMark(dialogId);
	  }
	  // endregion 'read'

	  // region 'user'
	  leaveChat(dialogId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _userService)[_userService].leaveChat(dialogId);
	  }
	  kickUserFromChat(dialogId, userId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _userService)[_userService].kickUserFromChat(dialogId, userId);
	  }
	  addToChat(addConfig) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _userService)[_userService].addToChat(addConfig);
	  }
	  joinChat(dialogId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _userService)[_userService].joinChat(dialogId);
	  }
	  // endregion 'user
	}
	function _initServices2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService] = new LoadService();
	  babelHelpers.classPrivateFieldLooseBase(this, _createService)[_createService] = new CreateService();
	  babelHelpers.classPrivateFieldLooseBase(this, _updateService)[_updateService] = new UpdateService();
	  babelHelpers.classPrivateFieldLooseBase(this, _renameService)[_renameService] = new RenameService();
	  babelHelpers.classPrivateFieldLooseBase(this, _muteService)[_muteService] = new MuteService();
	  babelHelpers.classPrivateFieldLooseBase(this, _pinService)[_pinService] = new PinService();
	  babelHelpers.classPrivateFieldLooseBase(this, _readService)[_readService] = new ReadService();
	  babelHelpers.classPrivateFieldLooseBase(this, _userService)[_userService] = new UserService();
	}
	ChatService.DEBOUNCE_TIME = 500;

	var _store$7 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$6 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _chatId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatId");
	var _userManager = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userManager");
	var _preparedHistoryMessages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("preparedHistoryMessages");
	var _preparedUnreadMessages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("preparedUnreadMessages");
	var _isLoading = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isLoading");
	var _prepareInitialMessages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareInitialMessages");
	var _handleLoadedMessages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleLoadedMessages");
	var _updateModels$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateModels");
	var _setDialogInited = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setDialogInited");
	var _getDialog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialog");
	class LoadService$1 {
	  constructor(chatId) {
	    Object.defineProperty(this, _getDialog, {
	      value: _getDialog2
	    });
	    Object.defineProperty(this, _setDialogInited, {
	      value: _setDialogInited2
	    });
	    Object.defineProperty(this, _updateModels$1, {
	      value: _updateModels2$1
	    });
	    Object.defineProperty(this, _handleLoadedMessages, {
	      value: _handleLoadedMessages2
	    });
	    Object.defineProperty(this, _prepareInitialMessages, {
	      value: _prepareInitialMessages2
	    });
	    Object.defineProperty(this, _store$7, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$6, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _chatId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _userManager, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _preparedHistoryMessages, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _preparedUnreadMessages, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _isLoading, {
	      writable: true,
	      value: false
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$6)[_restClient$6] = im_v2_application_core.Core.getRestClient();
	    babelHelpers.classPrivateFieldLooseBase(this, _userManager)[_userManager] = new im_v2_lib_user.UserManager();
	    babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId] = chatId;
	  }
	  loadUnread() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] || !babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().hasNextPage) {
	      return Promise.resolve(false);
	    }
	    im_v2_lib_logger.Logger.warn('MessageService: loadUnread');
	    const lastUnreadMessageId = babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].getters['messages/getLastId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId]);
	    if (!lastUnreadMessageId) {
	      im_v2_lib_logger.Logger.warn('MessageService: no lastUnreadMessageId, cant load unread');
	      return Promise.resolve(false);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = true;
	    const query = {
	      chatId: babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId],
	      filter: {
	        lastId: lastUnreadMessageId
	      },
	      order: {
	        id: 'ASC'
	      },
	      limit: LoadService$1.MESSAGE_REQUEST_LIMIT
	    };
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatMessageTail, {
	      data: query
	    }).then(result => {
	      im_v2_lib_logger.Logger.warn('MessageService: loadUnread result', result);
	      babelHelpers.classPrivateFieldLooseBase(this, _preparedUnreadMessages)[_preparedUnreadMessages] = result.messages;
	      return babelHelpers.classPrivateFieldLooseBase(this, _updateModels$1)[_updateModels$1](result);
	    }).then(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	      return true;
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('MessageService: loadUnread error:', error);
	      babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	    });
	  }
	  loadHistory() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] || !babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().hasPrevPage) {
	      return Promise.resolve(false);
	    }
	    im_v2_lib_logger.Logger.warn('MessageService: loadHistory');
	    const lastHistoryMessageId = babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].getters['messages/getFirstId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId]);
	    if (!lastHistoryMessageId) {
	      im_v2_lib_logger.Logger.warn('MessageService: no lastHistoryMessageId, cant load unread');
	      return Promise.resolve();
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = true;
	    const query = {
	      chatId: babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId],
	      filter: {
	        lastId: lastHistoryMessageId
	      },
	      order: {
	        id: 'DESC'
	      },
	      limit: LoadService$1.MESSAGE_REQUEST_LIMIT
	    };
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatMessageTail, {
	      data: query
	    }).then(result => {
	      im_v2_lib_logger.Logger.warn('MessageService: loadHistory result', result);
	      babelHelpers.classPrivateFieldLooseBase(this, _preparedHistoryMessages)[_preparedHistoryMessages] = result.messages;
	      const hasPrevPage = result.hasNextPage;
	      const rawData = {
	        ...result,
	        hasPrevPage,
	        hasNextPage: null
	      };
	      return babelHelpers.classPrivateFieldLooseBase(this, _updateModels$1)[_updateModels$1](rawData);
	    }).then(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	      return true;
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('MessageService: loadHistory error:', error);
	      babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	    });
	  }
	  hasPreparedHistoryMessages() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _preparedHistoryMessages)[_preparedHistoryMessages].length > 0;
	  }
	  drawPreparedHistoryMessages() {
	    if (!this.hasPreparedHistoryMessages()) {
	      return Promise.resolve();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('messages/setChatCollection', {
	      messages: babelHelpers.classPrivateFieldLooseBase(this, _preparedHistoryMessages)[_preparedHistoryMessages]
	    }).then(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _preparedHistoryMessages)[_preparedHistoryMessages] = [];
	      return true;
	    });
	  }
	  hasPreparedUnreadMessages() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _preparedUnreadMessages)[_preparedUnreadMessages].length > 0;
	  }
	  drawPreparedUnreadMessages() {
	    if (!this.hasPreparedUnreadMessages()) {
	      return Promise.resolve();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('messages/setChatCollection', {
	      messages: babelHelpers.classPrivateFieldLooseBase(this, _preparedUnreadMessages)[_preparedUnreadMessages]
	    }).then(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _preparedUnreadMessages)[_preparedUnreadMessages] = [];
	      return true;
	    });
	  }
	  loadContext(messageId) {
	    const query = {
	      [im_v2_const.RestMethod.imV2ChatMessageGetContext]: {
	        id: messageId,
	        range: LoadService$1.MESSAGE_REQUEST_LIMIT
	      },
	      [im_v2_const.RestMethod.imV2ChatMessageRead]: {
	        chatId: babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId],
	        ids: [messageId]
	      }
	    };
	    im_v2_lib_logger.Logger.warn('MessageService: loadContext for: ', messageId);
	    babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = true;
	    return im_v2_lib_rest.callBatch(query).then(data => {
	      im_v2_lib_logger.Logger.warn('MessageService: loadContext result', data);
	      return babelHelpers.classPrivateFieldLooseBase(this, _handleLoadedMessages)[_handleLoadedMessages](data[im_v2_const.RestMethod.imV2ChatMessageGetContext]);
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('MessageService: loadContext error:', error);
	      throw new Error(error);
	    }).finally(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	    });
	  }
	  reloadMessageList() {
	    im_v2_lib_logger.Logger.warn('MessageService: loadChatOnExit for: ', babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId]);
	    let targetMessageId = 0;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().chatId <= 0) {
	      return Promise.resolve();
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().markedId) {
	      targetMessageId = babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().markedId;
	    } else if (babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().savedPositionMessageId) {
	      targetMessageId = babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().savedPositionMessageId;
	    }
	    const wasInitedBefore = babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().inited;
	    babelHelpers.classPrivateFieldLooseBase(this, _setDialogInited)[_setDialogInited](false);
	    if (targetMessageId) {
	      return this.loadContext(targetMessageId).catch(() => {}).finally(() => {
	        babelHelpers.classPrivateFieldLooseBase(this, _setDialogInited)[_setDialogInited](true, wasInitedBefore);
	      });
	    }
	    return this.loadInitialMessages().catch(() => {}).finally(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _setDialogInited)[_setDialogInited](true, wasInitedBefore);
	    });
	  }
	  async loadInitialMessages() {
	    im_v2_lib_logger.Logger.warn('MessageService: loadInitialMessages for: ', babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId]);
	    babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = true;
	    const payload = {
	      data: {
	        chatId: babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId],
	        limit: LoadService$1.MESSAGE_REQUEST_LIMIT
	      }
	    };
	    const restResult = await im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatMessageList, payload).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('MessageService: loadInitialMessages error:', error);
	      babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	      throw new Error(error);
	    });
	    im_v2_lib_logger.Logger.warn('MessageService: loadInitialMessages result', restResult);
	    restResult.messages = babelHelpers.classPrivateFieldLooseBase(this, _prepareInitialMessages)[_prepareInitialMessages](restResult.messages);
	    await babelHelpers.classPrivateFieldLooseBase(this, _handleLoadedMessages)[_handleLoadedMessages](restResult);
	    babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	  }
	  isLoading() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading];
	  }
	}
	function _prepareInitialMessages2(rawMessages) {
	  if (rawMessages.length === 0) {
	    return rawMessages;
	  }
	  const lastMessageId = babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().lastMessageId;
	  const newMaxId = Math.max(...rawMessages.map(message => message.id));
	  if (newMaxId >= lastMessageId) {
	    return rawMessages;
	  }
	  const messagesCollection = babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].getters['messages/get'](babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId]);
	  const additionalMessages = messagesCollection.filter(message => {
	    return message.id > newMaxId;
	  });
	  im_v2_lib_logger.Logger.warn('MessageService: loadInitialMessages: local id is higher than server one', additionalMessages);
	  return [...rawMessages, ...additionalMessages];
	}
	function _handleLoadedMessages2(restResult) {
	  const {
	    messages
	  } = restResult;
	  const messagesPromise = babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('messages/setChatCollection', {
	    messages,
	    clearCollection: true
	  });
	  const updateModelsPromise = babelHelpers.classPrivateFieldLooseBase(this, _updateModels$1)[_updateModels$1](restResult);
	  return Promise.all([messagesPromise, updateModelsPromise]);
	}
	function _updateModels2$1(rawData) {
	  const {
	    files,
	    users,
	    usersShort,
	    reactions,
	    hasPrevPage,
	    hasNextPage,
	    additionalMessages
	  } = rawData;
	  const dialogPromise = babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('chats/update', {
	    dialogId: babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().dialogId,
	    fields: {
	      hasPrevPage,
	      hasNextPage
	    }
	  });
	  const usersPromise = Promise.all([babelHelpers.classPrivateFieldLooseBase(this, _userManager)[_userManager].setUsersToModel(users), babelHelpers.classPrivateFieldLooseBase(this, _userManager)[_userManager].addUsersToModel(usersShort)]);
	  const filesPromise = babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('files/set', files);
	  const reactionsPromise = babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('messages/reactions/set', reactions);
	  const additionalMessagesPromise = babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('messages/store', additionalMessages);
	  return Promise.all([dialogPromise, filesPromise, usersPromise, reactionsPromise, additionalMessagesPromise]);
	}
	function _setDialogInited2(flag, wasInitedBefore = true) {
	  const fields = {
	    inited: flag,
	    loading: !flag
	  };
	  if (flag === true && !wasInitedBefore) {
	    delete fields.inited;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('chats/update', {
	    dialogId: babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().dialogId,
	    fields
	  });
	}
	function _getDialog2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].getters['chats/getByChatId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId]);
	}
	LoadService$1.MESSAGE_REQUEST_LIMIT = 25;

	var _store$8 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$7 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	class PinService$1 {
	  constructor() {
	    Object.defineProperty(this, _store$8, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$7, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$8)[_store$8] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$7)[_restClient$7] = im_v2_application_core.Core.getRestClient();
	  }
	  pinMessage(chatId, messageId) {
	    im_v2_lib_logger.Logger.warn(`Dialog: PinManager: pin message ${messageId}`);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$8)[_store$8].dispatch('messages/pin/add', {
	      chatId,
	      messageId
	    });
	    // EventEmitter.emit(EventType.dialog.scrollToBottom, {chatId});
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$7)[_restClient$7].callMethod(im_v2_const.RestMethod.imV2ChatMessagePin, {
	      id: messageId
	    }).catch(error => {
	      console.error('Dialog: PinManager: error pinning message', error);
	      babelHelpers.classPrivateFieldLooseBase(this, _store$8)[_store$8].dispatch('messages/pin/delete', {
	        chatId,
	        messageId
	      });
	    });
	  }
	  unpinMessage(chatId, messageId) {
	    im_v2_lib_logger.Logger.warn(`Dialog: PinManager: unpin message ${messageId}`);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$8)[_store$8].dispatch('messages/pin/delete', {
	      chatId,
	      messageId
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$7)[_restClient$7].callMethod(im_v2_const.RestMethod.imV2ChatMessageUnpin, {
	      id: messageId
	    }).catch(error => {
	      console.error('Dialog: PinManager: error unpinning message', error);
	      babelHelpers.classPrivateFieldLooseBase(this, _store$8)[_store$8].dispatch('messages/pin/add', {
	        chatId,
	        messageId
	      });
	    });
	  }
	}

	var _updateMessageModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateMessageModel");
	var _getMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMessage");
	class EditService {
	  constructor() {
	    Object.defineProperty(this, _getMessage, {
	      value: _getMessage2
	    });
	    Object.defineProperty(this, _updateMessageModel, {
	      value: _updateMessageModel2
	    });
	  }
	  editMessageText(messageId, text) {
	    im_v2_lib_logger.Logger.warn('MessageService: editMessageText', messageId, text);
	    const message = babelHelpers.classPrivateFieldLooseBase(this, _getMessage)[_getMessage](messageId);
	    if (!message) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _updateMessageModel)[_updateMessageModel](messageId, text);
	    const payload = {
	      data: {
	        id: messageId,
	        fields: {
	          message: text
	        }
	      }
	    };
	    im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatMessageUpdate, payload).catch(error => {
	      im_v2_lib_logger.Logger.error('MessageService: editMessageText error:', error);
	    });
	  }
	}
	function _updateMessageModel2(messageId, text) {
	  const message = babelHelpers.classPrivateFieldLooseBase(this, _getMessage)[_getMessage](messageId);
	  const isEdited = message.viewedByOthers;
	  im_v2_application_core.Core.getStore().dispatch('messages/update', {
	    id: messageId,
	    fields: {
	      text,
	      isEdited
	    }
	  });
	}
	function _getMessage2(messageId) {
	  return im_v2_application_core.Core.getStore().getters['messages/getById'](messageId);
	}

	var _store$9 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _chatId$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatId");
	var _shallowMessageDelete = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("shallowMessageDelete");
	var _completeMessageDelete = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("completeMessageDelete");
	var _updateRecentForCompleteDelete = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateRecentForCompleteDelete");
	var _updateChatForCompleteDelete = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateChatForCompleteDelete");
	var _deleteMessageOnServer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("deleteMessageOnServer");
	var _deleteTemporaryMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("deleteTemporaryMessage");
	var _getPreviousMessageId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPreviousMessageId");
	var _sendDeleteEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendDeleteEvent");
	class DeleteService {
	  constructor(chatId) {
	    Object.defineProperty(this, _sendDeleteEvent, {
	      value: _sendDeleteEvent2
	    });
	    Object.defineProperty(this, _getPreviousMessageId, {
	      value: _getPreviousMessageId2
	    });
	    Object.defineProperty(this, _deleteTemporaryMessage, {
	      value: _deleteTemporaryMessage2
	    });
	    Object.defineProperty(this, _deleteMessageOnServer, {
	      value: _deleteMessageOnServer2
	    });
	    Object.defineProperty(this, _updateChatForCompleteDelete, {
	      value: _updateChatForCompleteDelete2
	    });
	    Object.defineProperty(this, _updateRecentForCompleteDelete, {
	      value: _updateRecentForCompleteDelete2
	    });
	    Object.defineProperty(this, _completeMessageDelete, {
	      value: _completeMessageDelete2
	    });
	    Object.defineProperty(this, _shallowMessageDelete, {
	      value: _shallowMessageDelete2
	    });
	    Object.defineProperty(this, _store$9, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _chatId$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _chatId$1)[_chatId$1] = chatId;
	    babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9] = im_v2_application_core.Core.getStore();
	  }
	  async deleteMessage(messageId) {
	    im_v2_lib_logger.Logger.warn('MessageService: deleteMessage', messageId);
	    if (im_v2_lib_utils.Utils.text.isUuidV4(messageId)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _deleteTemporaryMessage)[_deleteTemporaryMessage](messageId);
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _sendDeleteEvent)[_sendDeleteEvent](messageId);
	    const message = babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].getters['messages/getById'](messageId);
	    if (message.viewedByOthers) {
	      await babelHelpers.classPrivateFieldLooseBase(this, _shallowMessageDelete)[_shallowMessageDelete](message);
	    } else {
	      await babelHelpers.classPrivateFieldLooseBase(this, _completeMessageDelete)[_completeMessageDelete](message);
	    }
	  }
	}
	function _shallowMessageDelete2(message) {
	  babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].dispatch('messages/update', {
	    id: message.id,
	    fields: {
	      text: '',
	      isDeleted: true,
	      files: [],
	      attach: [],
	      replyId: 0
	    }
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _deleteMessageOnServer)[_deleteMessageOnServer](message.id);
	}
	function _completeMessageDelete2(message) {
	  const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].getters['chats/getByChatId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId$1)[_chatId$1]);
	  if (message.id === dialog.lastMessageId) {
	    const newLastId = babelHelpers.classPrivateFieldLooseBase(this, _getPreviousMessageId)[_getPreviousMessageId](message.id);
	    babelHelpers.classPrivateFieldLooseBase(this, _updateRecentForCompleteDelete)[_updateRecentForCompleteDelete](newLastId);
	    babelHelpers.classPrivateFieldLooseBase(this, _updateChatForCompleteDelete)[_updateChatForCompleteDelete](newLastId);
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].dispatch('messages/delete', {
	    id: message.id
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _deleteMessageOnServer)[_deleteMessageOnServer](message.id);
	}
	function _updateRecentForCompleteDelete2(newLastId) {
	  const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].getters['chats/getByChatId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId$1)[_chatId$1]);
	  if (!newLastId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].dispatch('recent/delete', {
	      id: dialog.dialogId
	    });
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].dispatch('recent/update', {
	    id: dialog.dialogId,
	    fields: {
	      messageId: newLastId
	    }
	  });
	}
	function _updateChatForCompleteDelete2(newLastId) {
	  const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].getters['chats/getByChatId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId$1)[_chatId$1]);
	  babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].dispatch('chats/update', {
	    dialogId: dialog.dialogId,
	    fields: {
	      lastMessageId: newLastId,
	      lastId: newLastId
	    }
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].dispatch('chats/clearLastMessageViews', {
	    dialogId: dialog.dialogId
	  });
	}
	function _deleteMessageOnServer2(messageId) {
	  return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatMessageDelete, {
	    data: {
	      id: messageId
	    }
	  }).catch(error => {
	    // eslint-disable-next-line no-console
	    console.error('MessageService: deleteMessage error:', error);
	  });
	}
	function _deleteTemporaryMessage2(messageId) {
	  const chat = babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].getters['chats/getByChatId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId$1)[_chatId$1]);
	  const recentItem = babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].getters['recent/get'](chat.dialogId);
	  if (recentItem.messageId === messageId) {
	    const newLastId = babelHelpers.classPrivateFieldLooseBase(this, _getPreviousMessageId)[_getPreviousMessageId](messageId);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].dispatch('recent/update', {
	      id: chat.dialogId,
	      fields: {
	        messageId: newLastId
	      }
	    });
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].dispatch('messages/delete', {
	    id: messageId
	  });
	}
	function _getPreviousMessageId2(messageId) {
	  var _previousMessage$id;
	  const previousMessage = babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].getters['messages/getPreviousMessage']({
	    messageId,
	    chatId: babelHelpers.classPrivateFieldLooseBase(this, _chatId$1)[_chatId$1]
	  });
	  return (_previousMessage$id = previousMessage == null ? void 0 : previousMessage.id) != null ? _previousMessage$id : 0;
	}
	function _sendDeleteEvent2(messageId) {
	  main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.onMessageDeleted, {
	    messageId
	  });
	}

	var _chatId$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatId");
	var _store$a = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$8 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	class MarkService {
	  constructor(chatId) {
	    Object.defineProperty(this, _chatId$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _store$a, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$8, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _chatId$2)[_chatId$2] = chatId;
	    babelHelpers.classPrivateFieldLooseBase(this, _store$a)[_store$a] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$8)[_restClient$8] = im_v2_application_core.Core.getRestClient();
	  }
	  markMessage(messageId) {
	    im_v2_lib_logger.Logger.warn('MessageService: markMessage', messageId);
	    const {
	      dialogId
	    } = babelHelpers.classPrivateFieldLooseBase(this, _store$a)[_store$a].getters['chats/getByChatId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId$2)[_chatId$2]);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$a)[_store$a].dispatch('recent/unread', {
	      id: dialogId,
	      action: true
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$a)[_store$a].dispatch('chats/update', {
	      dialogId,
	      fields: {
	        markedId: messageId
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$8)[_restClient$8].callMethod(im_v2_const.RestMethod.imV2ChatMessageMark, {
	      dialogId,
	      id: messageId
	    }).catch(error => {
	      console.error('MessageService: error marking message', error);
	    });
	  }
	}

	var _chatId$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatId");
	var _store$b = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$9 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	class FavoriteService {
	  constructor(chatId) {
	    Object.defineProperty(this, _chatId$3, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _store$b, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$9, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _chatId$3)[_chatId$3] = chatId;
	    babelHelpers.classPrivateFieldLooseBase(this, _store$b)[_store$b] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$9)[_restClient$9] = im_v2_application_core.Core.getRestClient();
	  }
	  addMessageToFavorite(messageId) {
	    im_v2_lib_logger.Logger.warn('MessageService: addMessageToFavorite', messageId);
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$9)[_restClient$9].callMethod(im_v2_const.RestMethod.imChatFavoriteAdd, {
	      MESSAGE_ID: messageId
	    }).catch(error => {
	      console.error('MessageService: error adding message to favorite', error);
	    });
	    BX.UI.Notification.Center.notify({
	      content: main_core.Loc.getMessage('IM_MESSAGE_SERVICE_ADD_MESSAGE_TO_FAVORITE_SUCCESS')
	    });
	  }
	  removeMessageFromFavorite(messageId) {
	    im_v2_lib_logger.Logger.warn('MessageService: removeMessageFromFavorite', messageId);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$b)[_store$b].dispatch('sidebar/favorites/deleteByMessageId', {
	      chatId: babelHelpers.classPrivateFieldLooseBase(this, _chatId$3)[_chatId$3],
	      messageId: messageId
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$9)[_restClient$9].callMethod(im_v2_const.RestMethod.imChatFavoriteDelete, {
	      MESSAGE_ID: messageId
	    }).catch(error => {
	      console.error('MessageService: error removing message from favorite', error);
	    });
	  }
	}

	var _loadService$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadService");
	var _pinService$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pinService");
	var _editService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("editService");
	var _deleteService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("deleteService");
	var _markService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("markService");
	var _favoriteService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("favoriteService");
	var _initServices$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initServices");
	class MessageService {
	  static getMessageRequestLimit() {
	    return LoadService$1.MESSAGE_REQUEST_LIMIT;
	  }
	  constructor(params) {
	    Object.defineProperty(this, _initServices$1, {
	      value: _initServices2$1
	    });
	    Object.defineProperty(this, _loadService$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _pinService$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _editService, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _deleteService, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _markService, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _favoriteService, {
	      writable: true,
	      value: void 0
	    });
	    const {
	      chatId: _chatId
	    } = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _initServices$1)[_initServices$1](_chatId);
	  }
	  // region 'pagination'
	  loadUnread() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService$1)[_loadService$1].loadUnread();
	  }
	  loadHistory() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService$1)[_loadService$1].loadHistory();
	  }
	  hasPreparedHistoryMessages() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService$1)[_loadService$1].hasPreparedHistoryMessages();
	  }
	  drawPreparedHistoryMessages() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService$1)[_loadService$1].drawPreparedHistoryMessages();
	  }
	  hasPreparedUnreadMessages() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService$1)[_loadService$1].hasPreparedUnreadMessages();
	  }
	  drawPreparedUnreadMessages() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService$1)[_loadService$1].drawPreparedUnreadMessages();
	  }
	  isLoading() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService$1)[_loadService$1].isLoading();
	  }
	  // endregion 'pagination'

	  // region 'context'
	  loadContext(messageId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService$1)[_loadService$1].loadContext(messageId);
	  }
	  // endregion 'context

	  // region 'reload messages'
	  reloadMessageList() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService$1)[_loadService$1].reloadMessageList();
	  }
	  loadInitialMessages() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService$1)[_loadService$1].loadInitialMessages();
	  }
	  // endregion 'reload messages'

	  // region 'pin'
	  pinMessage(chatId, messageId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _pinService$1)[_pinService$1].pinMessage(chatId, messageId);
	  }
	  unpinMessage(chatId, messageId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _pinService$1)[_pinService$1].unpinMessage(chatId, messageId);
	  }
	  // endregion 'pin'

	  // region 'mark'
	  markMessage(messageId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _markService)[_markService].markMessage(messageId);
	  }
	  // endregion 'mark'

	  // region 'favorite'
	  addMessageToFavorite(messageId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _favoriteService)[_favoriteService].addMessageToFavorite(messageId);
	  }
	  removeMessageFromFavorite(messageId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _favoriteService)[_favoriteService].removeMessageFromFavorite(messageId);
	  }
	  // endregion 'favorite'

	  // region 'edit'
	  editMessageText(messageId, text) {
	    babelHelpers.classPrivateFieldLooseBase(this, _editService)[_editService].editMessageText(messageId, text);
	  }
	  // endregion 'edit'

	  // region 'delete'
	  deleteMessage(messageId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _deleteService)[_deleteService].deleteMessage(messageId);
	  }
	  // endregion 'delete'
	}
	function _initServices2$1(chatId) {
	  babelHelpers.classPrivateFieldLooseBase(this, _loadService$1)[_loadService$1] = new LoadService$1(chatId);
	  babelHelpers.classPrivateFieldLooseBase(this, _editService)[_editService] = new EditService();
	  babelHelpers.classPrivateFieldLooseBase(this, _deleteService)[_deleteService] = new DeleteService(chatId);
	  babelHelpers.classPrivateFieldLooseBase(this, _pinService$1)[_pinService$1] = new PinService$1();
	  babelHelpers.classPrivateFieldLooseBase(this, _markService)[_markService] = new MarkService(chatId);
	  babelHelpers.classPrivateFieldLooseBase(this, _favoriteService)[_favoriteService] = new FavoriteService(chatId);
	}

	var _store$c = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _processMessageSending = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("processMessageSending");
	var _handleAddingMessageToModels = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleAddingMessageToModels");
	var _sendAndProcessMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendAndProcessMessage");
	var _prepareMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareMessage");
	var _prepareMessageWithFile = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareMessageWithFile");
	var _preparePrompt = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("preparePrompt");
	var _handlePagination = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handlePagination");
	var _addMessageToModels = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addMessageToModels");
	var _addMessageToRecent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addMessageToRecent");
	var _sendMessageToServer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendMessageToServer");
	var _updateModels$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateModels");
	var _updateMessageError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateMessageError");
	var _removeMessageError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeMessageError");
	var _sendScrollEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendScrollEvent");
	var _getDialog$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialog");
	var _getDialogByChatId$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialogByChatId");
	var _needToSetAsViewed = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("needToSetAsViewed");
	var _handleForwardMessageResponse = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleForwardMessageResponse");
	var _handleForwardMessageError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleForwardMessageError");
	var _prepareForwardMessages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareForwardMessages");
	var _prepareSendForwardRequest = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareSendForwardRequest");
	var _addForwardsToModels = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addForwardsToModels");
	var _getForwardUuidMap = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getForwardUuidMap");
	var _buildForwardContextId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("buildForwardContextId");
	var _logSendErrors = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("logSendErrors");
	class SendingService$$1 {
	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }
	  constructor() {
	    Object.defineProperty(this, _logSendErrors, {
	      value: _logSendErrors2
	    });
	    Object.defineProperty(this, _buildForwardContextId, {
	      value: _buildForwardContextId2
	    });
	    Object.defineProperty(this, _getForwardUuidMap, {
	      value: _getForwardUuidMap2
	    });
	    Object.defineProperty(this, _addForwardsToModels, {
	      value: _addForwardsToModels2
	    });
	    Object.defineProperty(this, _prepareSendForwardRequest, {
	      value: _prepareSendForwardRequest2
	    });
	    Object.defineProperty(this, _prepareForwardMessages, {
	      value: _prepareForwardMessages2
	    });
	    Object.defineProperty(this, _handleForwardMessageError, {
	      value: _handleForwardMessageError2
	    });
	    Object.defineProperty(this, _handleForwardMessageResponse, {
	      value: _handleForwardMessageResponse2
	    });
	    Object.defineProperty(this, _needToSetAsViewed, {
	      value: _needToSetAsViewed2
	    });
	    Object.defineProperty(this, _getDialogByChatId$1, {
	      value: _getDialogByChatId2$1
	    });
	    Object.defineProperty(this, _getDialog$1, {
	      value: _getDialog2$1
	    });
	    Object.defineProperty(this, _sendScrollEvent, {
	      value: _sendScrollEvent2
	    });
	    Object.defineProperty(this, _removeMessageError, {
	      value: _removeMessageError2
	    });
	    Object.defineProperty(this, _updateMessageError, {
	      value: _updateMessageError2
	    });
	    Object.defineProperty(this, _updateModels$2, {
	      value: _updateModels2$2
	    });
	    Object.defineProperty(this, _sendMessageToServer, {
	      value: _sendMessageToServer2
	    });
	    Object.defineProperty(this, _addMessageToRecent, {
	      value: _addMessageToRecent2
	    });
	    Object.defineProperty(this, _addMessageToModels, {
	      value: _addMessageToModels2
	    });
	    Object.defineProperty(this, _handlePagination, {
	      value: _handlePagination2
	    });
	    Object.defineProperty(this, _preparePrompt, {
	      value: _preparePrompt2
	    });
	    Object.defineProperty(this, _prepareMessageWithFile, {
	      value: _prepareMessageWithFile2
	    });
	    Object.defineProperty(this, _prepareMessage, {
	      value: _prepareMessage2
	    });
	    Object.defineProperty(this, _sendAndProcessMessage, {
	      value: _sendAndProcessMessage2
	    });
	    Object.defineProperty(this, _handleAddingMessageToModels, {
	      value: _handleAddingMessageToModels2
	    });
	    Object.defineProperty(this, _processMessageSending, {
	      value: _processMessageSending2
	    });
	    Object.defineProperty(this, _store$c, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c] = im_v2_application_core.Core.getStore();
	  }
	  async sendMessage(params) {
	    const {
	      text = ''
	    } = params;
	    if (!main_core.Type.isStringFilled(text)) {
	      return Promise.resolve();
	    }
	    im_v2_lib_logger.Logger.warn('SendingService: sendMessage', params);
	    const message = babelHelpers.classPrivateFieldLooseBase(this, _prepareMessage)[_prepareMessage](params);
	    return babelHelpers.classPrivateFieldLooseBase(this, _processMessageSending)[_processMessageSending](message);
	  }
	  async sendMessageWithFile(params) {
	    const {
	      text = '',
	      fileId = ''
	    } = params;
	    if (!main_core.Type.isStringFilled(text) && !main_core.Type.isStringFilled(fileId)) {
	      return Promise.resolve();
	    }
	    im_v2_lib_logger.Logger.warn('SendingService: sendMessage with file', params);
	    const message = babelHelpers.classPrivateFieldLooseBase(this, _prepareMessageWithFile)[_prepareMessageWithFile](params);
	    await babelHelpers.classPrivateFieldLooseBase(this, _handleAddingMessageToModels)[_handleAddingMessageToModels](message);
	    return Promise.resolve();
	  }
	  async forwardMessages(params) {
	    const {
	      forwardIds,
	      dialogId,
	      text
	    } = params;
	    if (!main_core.Type.isArrayFilled(forwardIds)) {
	      return Promise.resolve();
	    }
	    im_v2_lib_logger.Logger.warn('SendingService: forwardMessages', params);
	    await babelHelpers.classPrivateFieldLooseBase(this, _handlePagination)[_handlePagination](dialogId);
	    let commentMessage = null;
	    if (main_core.Type.isStringFilled(text)) {
	      commentMessage = babelHelpers.classPrivateFieldLooseBase(this, _prepareMessage)[_prepareMessage](params);
	      await babelHelpers.classPrivateFieldLooseBase(this, _addMessageToModels)[_addMessageToModels](commentMessage);
	    }
	    const forwardUuidMap = babelHelpers.classPrivateFieldLooseBase(this, _getForwardUuidMap)[_getForwardUuidMap](forwardIds);
	    const forwardedMessages = babelHelpers.classPrivateFieldLooseBase(this, _prepareForwardMessages)[_prepareForwardMessages](params, forwardUuidMap);
	    await babelHelpers.classPrivateFieldLooseBase(this, _addForwardsToModels)[_addForwardsToModels](forwardedMessages);
	    babelHelpers.classPrivateFieldLooseBase(this, _sendScrollEvent)[_sendScrollEvent]({
	      force: true,
	      dialogId
	    });
	    try {
	      const requestParams = babelHelpers.classPrivateFieldLooseBase(this, _prepareSendForwardRequest)[_prepareSendForwardRequest]({
	        forwardUuidMap,
	        commentMessage,
	        dialogId
	      });
	      const response = await babelHelpers.classPrivateFieldLooseBase(this, _sendMessageToServer)[_sendMessageToServer](requestParams);
	      im_v2_lib_logger.Logger.warn('SendingService: forwardMessage result -', response);
	      babelHelpers.classPrivateFieldLooseBase(this, _handleForwardMessageResponse)[_handleForwardMessageResponse]({
	        response,
	        dialogId,
	        commentMessage
	      });
	    } catch (errors) {
	      babelHelpers.classPrivateFieldLooseBase(this, _handleForwardMessageError)[_handleForwardMessageError]({
	        commentMessage,
	        forwardUuidMap
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _logSendErrors)[_logSendErrors](errors, 'forwardMessage');
	    }
	    return Promise.resolve();
	  }
	  async retrySendMessage(params) {
	    const {
	      tempMessageId,
	      dialogId
	    } = params;
	    const unsentMessage = babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c].getters['messages/getById'](tempMessageId);
	    if (!unsentMessage) {
	      return Promise.resolve();
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _removeMessageError)[_removeMessageError](tempMessageId);
	    const message = babelHelpers.classPrivateFieldLooseBase(this, _prepareMessage)[_prepareMessage]({
	      text: unsentMessage.text,
	      dialogId,
	      tempMessageId: unsentMessage.id,
	      replyId: unsentMessage.replyId
	    });
	    return babelHelpers.classPrivateFieldLooseBase(this, _sendAndProcessMessage)[_sendAndProcessMessage](message);
	  }
	  async sendCopilotPrompt(params) {
	    const {
	      text = ''
	    } = params;
	    if (!main_core.Type.isStringFilled(text)) {
	      return Promise.resolve();
	    }
	    im_v2_lib_logger.Logger.warn('SendingService: sendCopilotPrompt', params);
	    const message = babelHelpers.classPrivateFieldLooseBase(this, _preparePrompt)[_preparePrompt](params);
	    return babelHelpers.classPrivateFieldLooseBase(this, _processMessageSending)[_processMessageSending](message);
	  }
	}
	async function _processMessageSending2(message) {
	  await babelHelpers.classPrivateFieldLooseBase(this, _handleAddingMessageToModels)[_handleAddingMessageToModels](message);
	  return babelHelpers.classPrivateFieldLooseBase(this, _sendAndProcessMessage)[_sendAndProcessMessage](message);
	}
	async function _handleAddingMessageToModels2(message) {
	  await babelHelpers.classPrivateFieldLooseBase(this, _handlePagination)[_handlePagination](message.dialogId);
	  await babelHelpers.classPrivateFieldLooseBase(this, _addMessageToModels)[_addMessageToModels](message);
	  babelHelpers.classPrivateFieldLooseBase(this, _sendScrollEvent)[_sendScrollEvent]({
	    force: true,
	    dialogId: message.dialogId
	  });
	}
	async function _sendAndProcessMessage2(message) {
	  const sendResult = await babelHelpers.classPrivateFieldLooseBase(this, _sendMessageToServer)[_sendMessageToServer](message).catch(errors => {
	    babelHelpers.classPrivateFieldLooseBase(this, _updateMessageError)[_updateMessageError](message.temporaryId);
	    babelHelpers.classPrivateFieldLooseBase(this, _logSendErrors)[_logSendErrors](errors, 'sendAndProcessMessage');
	  });
	  im_v2_lib_logger.Logger.warn('SendingService: sendAndProcessMessage result -', sendResult);
	  const {
	    id
	  } = sendResult;
	  if (!id) {
	    return Promise.resolve();
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _updateModels$2)[_updateModels$2]({
	    oldId: message.temporaryId,
	    newId: id,
	    dialogId: message.dialogId
	  });
	  return Promise.resolve();
	}
	function _prepareMessage2(params) {
	  const {
	    text,
	    tempMessageId,
	    dialogId,
	    replyId,
	    forwardIds
	  } = params;
	  const defaultFields = {
	    authorId: im_v2_application_core.Core.getUserId(),
	    unread: false,
	    sending: true
	  };
	  return {
	    text,
	    dialogId,
	    chatId: babelHelpers.classPrivateFieldLooseBase(this, _getDialog$1)[_getDialog$1](dialogId).chatId,
	    temporaryId: tempMessageId != null ? tempMessageId : im_v2_lib_utils.Utils.text.getUuidV4(),
	    replyId,
	    forwardIds,
	    viewedByOthers: babelHelpers.classPrivateFieldLooseBase(this, _needToSetAsViewed)[_needToSetAsViewed](dialogId),
	    ...defaultFields
	  };
	}
	function _prepareMessageWithFile2(params) {
	  const {
	    fileId
	  } = params;
	  if (!fileId) {
	    throw new Error('SendingService: sendMessageWithFile: no fileId provided');
	  }
	  return {
	    ...babelHelpers.classPrivateFieldLooseBase(this, _prepareMessage)[_prepareMessage](params),
	    params: {
	      FILE_ID: [fileId]
	    }
	  };
	}
	function _preparePrompt2(params) {
	  const {
	    copilot
	  } = params;
	  if (!copilot || !copilot.promptCode) {
	    throw new Error('SendingService: preparePrompt: no code provided');
	  }
	  return {
	    ...babelHelpers.classPrivateFieldLooseBase(this, _prepareMessage)[_prepareMessage](params),
	    copilot
	  };
	}
	function _handlePagination2(dialogId) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _getDialog$1)[_getDialog$1](dialogId).hasNextPage) {
	    return Promise.resolve();
	  }
	  im_v2_lib_logger.Logger.warn('SendingService: sendMessage: there are unread pages, move to chat end');
	  const messageService = new MessageService({
	    chatId: babelHelpers.classPrivateFieldLooseBase(this, _getDialog$1)[_getDialog$1](dialogId).chatId
	  });
	  return messageService.loadContext(babelHelpers.classPrivateFieldLooseBase(this, _getDialog$1)[_getDialog$1](dialogId).lastMessageId).then(() => {
	    babelHelpers.classPrivateFieldLooseBase(this, _sendScrollEvent)[_sendScrollEvent]({
	      dialogId
	    });
	  }).catch(error => {
	    // eslint-disable-next-line no-console
	    console.error('SendingService: loadContext error', error);
	  });
	}
	function _addMessageToModels2(message) {
	  babelHelpers.classPrivateFieldLooseBase(this, _addMessageToRecent)[_addMessageToRecent](message);
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c].dispatch('chats/clearLastMessageViews', {
	    dialogId: message.dialogId
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c].dispatch('messages/add', message);
	}
	function _addMessageToRecent2(message) {
	  const recentItem = babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c].getters['recent/get'](message.dialogId);
	  if (!recentItem || message.text === '') {
	    return;
	  }
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c].dispatch('recent/update', {
	    id: message.dialogId,
	    fields: {
	      messageId: message.temporaryId
	    }
	  });
	}
	function _sendMessageToServer2(message) {
	  const fields = {};
	  if (message.replyId) {
	    fields.replyId = message.replyId;
	  }
	  if (message.forwardIds) {
	    fields.forwardIds = message.forwardIds;
	  }
	  if (message.text) {
	    fields.message = message.text;
	    fields.templateId = message.temporaryId;
	  }
	  if (message.copilot) {
	    fields.copilot = message.copilot;
	  }
	  const queryData = {
	    dialogId: message.dialogId.toString(),
	    fields
	  };
	  return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatMessageSend, {
	    data: queryData
	  });
	}
	function _updateModels2$2(params) {
	  const {
	    oldId,
	    newId,
	    dialogId
	  } = params;
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c].dispatch('messages/updateWithId', {
	    id: oldId,
	    fields: {
	      id: newId
	    }
	  });
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c].dispatch('chats/update', {
	    dialogId,
	    fields: {
	      lastId: newId,
	      lastMessageId: newId
	    }
	  });
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c].dispatch('recent/update', {
	    id: dialogId,
	    fields: {
	      messageId: newId
	    }
	  });
	}
	function _updateMessageError2(messageId) {
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c].dispatch('messages/update', {
	    id: messageId,
	    fields: {
	      error: true
	    }
	  });
	}
	function _removeMessageError2(messageId) {
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c].dispatch('messages/update', {
	    id: messageId,
	    fields: {
	      sending: true,
	      error: false
	    }
	  });
	}
	function _sendScrollEvent2(params = {}) {
	  const {
	    force = false,
	    dialogId
	  } = params;
	  main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.scrollToBottom, {
	    chatId: babelHelpers.classPrivateFieldLooseBase(this, _getDialog$1)[_getDialog$1](dialogId).chatId,
	    threshold: force ? im_v2_const.DialogScrollThreshold.none : im_v2_const.DialogScrollThreshold.halfScreenUp
	  });
	}
	function _getDialog2$1(dialogId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c].getters['chats/get'](dialogId, true);
	}
	function _getDialogByChatId2$1(chatId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c].getters['chats/getByChatId'](chatId, true);
	}
	function _needToSetAsViewed2(dialogId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c].getters['users/bots/isNetwork'](dialogId);
	}
	function _handleForwardMessageResponse2(params) {
	  const {
	    response,
	    dialogId,
	    commentMessage
	  } = params;
	  const {
	    id,
	    uuidMap
	  } = response;
	  if (id) {
	    babelHelpers.classPrivateFieldLooseBase(this, _updateModels$2)[_updateModels$2]({
	      oldId: commentMessage.temporaryId,
	      newId: id,
	      dialogId
	    });
	  }
	  Object.entries(uuidMap).forEach(([uuid, messageId]) => {
	    babelHelpers.classPrivateFieldLooseBase(this, _updateModels$2)[_updateModels$2]({
	      oldId: uuid,
	      newId: messageId,
	      dialogId
	    });
	  });
	}
	function _handleForwardMessageError2({
	  commentMessage,
	  forwardUuidMap
	}) {
	  if (commentMessage) {
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c].dispatch('messages/update', {
	      id: commentMessage.temporaryId,
	      fields: {
	        error: true
	      }
	    });
	  }
	  Object.keys(forwardUuidMap).forEach(uuid => {
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c].dispatch('messages/update', {
	      id: uuid,
	      fields: {
	        error: true
	      }
	    });
	  });
	}
	function _prepareForwardMessages2(params, forwardUuidMap) {
	  const {
	    forwardIds,
	    dialogId
	  } = params;
	  if (forwardIds.length === 0) {
	    return [];
	  }
	  const preparedMessages = [];
	  Object.entries(forwardUuidMap).forEach(([uuid, messageId]) => {
	    const message = babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c].getters['messages/getById'](messageId);
	    if (!message) {
	      return;
	    }
	    const isForward = babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c].getters['messages/isForward'](messageId);
	    preparedMessages.push({
	      ...babelHelpers.classPrivateFieldLooseBase(this, _prepareMessage)[_prepareMessage]({
	        dialogId,
	        text: message.text,
	        tempMessageId: uuid,
	        replyId: message.replyId
	      }),
	      forward: {
	        id: babelHelpers.classPrivateFieldLooseBase(this, _buildForwardContextId)[_buildForwardContextId](message.chatId, messageId),
	        userId: isForward ? message.forward.userId : message.authorId
	      },
	      attach: message.attach,
	      isDeleted: message.isDeleted,
	      files: message.files
	    });
	  });
	  return preparedMessages;
	}
	function _prepareSendForwardRequest2(params) {
	  const {
	    dialogId,
	    forwardUuidMap,
	    commentMessage
	  } = params;
	  const requestPrams = {
	    dialogId,
	    forwardIds: forwardUuidMap
	  };
	  if (commentMessage) {
	    requestPrams.text = commentMessage.text;
	    requestPrams.temporaryId = commentMessage.temporaryId;
	  }
	  return requestPrams;
	}
	function _addForwardsToModels2(forwardedMessages) {
	  const addPromises = [];
	  forwardedMessages.forEach(message => {
	    addPromises.push(babelHelpers.classPrivateFieldLooseBase(this, _addMessageToModels)[_addMessageToModels](message));
	  });
	  return Promise.all(addPromises);
	}
	function _getForwardUuidMap2(forwardIds) {
	  const uuidMap = {};
	  forwardIds.forEach(id => {
	    uuidMap[im_v2_lib_utils.Utils.text.getUuidV4()] = id;
	  });
	  return uuidMap;
	}
	function _buildForwardContextId2(chatId, messageId) {
	  const dialogId = babelHelpers.classPrivateFieldLooseBase(this, _getDialogByChatId$1)[_getDialogByChatId$1](chatId).dialogId;
	  if (dialogId.startsWith('chat')) {
	    return `${dialogId}/${messageId}`;
	  }
	  const currentUser = im_v2_application_core.Core.getUserId();
	  return `${dialogId}:${currentUser}/${messageId}`;
	}
	function _logSendErrors2(errors, methodName) {
	  errors.forEach(error => {
	    // eslint-disable-next-line no-console
	    console.error(`SendingService: ${methodName} error: code: ${error.code} message: ${error.message}`);
	  });
	}
	SendingService$$1.instance = null;

	class NotificationService {
	  constructor() {
	    this.store = null;
	    this.restClient = null;
	    this.limitPerPage = 50;
	    this.isLoading = false;
	    this.lastId = 0;
	    this.lastType = 0;
	    this.hasMoreItemsToLoad = true;
	    this.notificationsToDelete = new Set();
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	    this.deleteWithDebounce = main_core.Runtime.debounce(this.deleteRequest, 500, this);
	    this.userManager = new im_v2_lib_user.UserManager();
	  }
	  loadFirstPage() {
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
	  delete(notificationId) {
	    this.notificationsToDelete.add(notificationId);
	    this.store.dispatch('notifications/delete', {
	      id: notificationId
	    });
	    this.store.dispatch('notifications/deleteFromSearch', {
	      id: notificationId
	    });
	    this.deleteWithDebounce();
	  }
	  sendConfirmAction(notificationId, value) {
	    const requestParams = {
	      'NOTIFY_ID': notificationId,
	      'NOTIFY_VALUE': value
	    };
	    this.store.dispatch('notifications/delete', {
	      id: notificationId
	    });
	    this.restClient.callMethod('im.notify.confirm', requestParams).then(response => {
	      im_v2_lib_logger.Logger.warn(`NotificationService: sendConfirmAction: success`, response);
	    }).catch(error => {
	      console.error(error);
	      //revert?
	    });
	  }

	  sendQuickAnswer(params) {
	    const {
	      id,
	      text,
	      callbackSuccess = () => {},
	      callbackError = () => {}
	    } = params;
	    this.restClient.callMethod(im_v2_const.RestMethod.imNotifyAnswer, {
	      notify_id: id,
	      answer_text: text
	    }).then(response => {
	      callbackSuccess(response);
	    }).catch(error => {
	      console.error(error);
	      callbackError();
	    });
	  }
	  deleteRequest() {
	    const idsToDelete = [...this.notificationsToDelete];
	    this.restClient.callMethod('im.notify.delete', {
	      id: idsToDelete
	    }).then(response => {
	      im_v2_lib_logger.Logger.warn(`NotificationService: deleteRequest: success for ids: ${idsToDelete}`, response);
	    }).catch(error => {
	      console.error(error);
	      //revert?
	    });

	    this.notificationsToDelete.clear();
	  }
	  requestItems({
	    firstPage = false
	  } = {}) {
	    const imNotifyGetQueryParams = {
	      'LIMIT': this.limitPerPage,
	      'CONVERT_TEXT': 'Y'
	    };
	    const batchQueryParams = {
	      [im_v2_const.RestMethod.imNotifyGet]: [im_v2_const.RestMethod.imNotifyGet, imNotifyGetQueryParams]
	    };
	    if (!firstPage) {
	      imNotifyGetQueryParams.LAST_ID = this.lastId;
	      imNotifyGetQueryParams.LAST_TYPE = this.lastType;
	    } else {
	      batchQueryParams[im_v2_const.RestMethod.imNotifySchemaGet] = [im_v2_const.RestMethod.imNotifySchemaGet, {}];
	    }
	    return new Promise(resolve => {
	      this.restClient.callBatch(batchQueryParams, response => {
	        im_v2_lib_logger.Logger.warn('im.notify.get: result', response);
	        resolve(this.handleResponse(response));
	      });
	    });
	  }
	  handleResponse(response) {
	    const imNotifyGetResponse = response[im_v2_const.RestMethod.imNotifyGet].data();
	    this.hasMoreItemsToLoad = !this.isLastPage(imNotifyGetResponse.notifications);
	    if (imNotifyGetResponse.notifications.length === 0) {
	      im_v2_lib_logger.Logger.warn('im.notify.get: no notifications', imNotifyGetResponse);
	      return Promise.resolve();
	    }
	    this.lastId = this.getLastItemId(imNotifyGetResponse.notifications);
	    this.lastType = this.getLastItemType(imNotifyGetResponse.notifications);
	    return this.updateModels(imNotifyGetResponse).then(() => {
	      this.isLoading = false;
	      if (response[im_v2_const.RestMethod.imNotifySchemaGet]) {
	        return response[im_v2_const.RestMethod.imNotifySchemaGet].data();
	      }
	      return {};
	    });
	  }
	  updateModels(imNotifyGetResponse) {
	    this.userManager.setUsersToModel(imNotifyGetResponse.users);
	    return this.store.dispatch('notifications/initialSet', imNotifyGetResponse);
	  }
	  getLastItemId(collection) {
	    return collection[collection.length - 1].id;
	  }
	  getLastItemType(collection) {
	    return this.getItemType(collection[collection.length - 1]);
	  }
	  getItemType(item) {
	    return item.notify_type === im_v2_const.NotificationTypesCodes.confirm ? im_v2_const.NotificationTypesCodes.confirm : im_v2_const.NotificationTypesCodes.simple;
	  }
	  isLastPage(notifications) {
	    if (!main_core.Type.isArrayFilled(notifications) || notifications.length < this.limitPerPage) {
	      return true;
	    }
	    return false;
	  }
	  destroy() {
	    im_v2_lib_logger.Logger.warn('Notification service destroyed');
	  }
	}

	var _restClient$a = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	class DiskService {
	  constructor() {
	    Object.defineProperty(this, _restClient$a, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$a)[_restClient$a] = im_v2_application_core.Core.getRestClient();
	  }
	  delete({
	    chatId,
	    fileId
	  }) {
	    const queryParams = {
	      chat_id: chatId,
	      file_id: fileId
	    };
	    return babelHelpers.classPrivateFieldLooseBase(this, _restClient$a)[_restClient$a].callMethod(im_v2_const.RestMethod.imDiskFileDelete, queryParams).catch(error => {
	      console.error('DiskService: error deleting file', error);
	    });
	  }
	  save(fileId) {
	    const queryParams = {
	      file_id: fileId
	    };
	    return babelHelpers.classPrivateFieldLooseBase(this, _restClient$a)[_restClient$a].callMethod(im_v2_const.RestMethod.imDiskFileSave, queryParams).catch(error => {
	      console.error('DiskService: error saving file on disk', error);
	    });
	  }
	}

	class UnreadRecentService extends RecentService {
	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }
	  getCollection() {
	    return this.store.getters['recent/getUnreadCollection'];
	  }
	  loadFirstPage({
	    ignorePreloadedItems = false
	  } = {}) {
	    this.isLoading = true;
	    return this.requestItems({
	      firstPage: true
	    });
	  }
	  updateModels(rawData) {
	    const {
	      users,
	      dialogues,
	      recent
	    } = this.prepareDataForModels(rawData);
	    const usersPromise = this.store.dispatch('users/set', users);
	    const dialoguesPromise = this.store.dispatch('chats/set', dialogues);
	    const fakeRecent = this.getFakeData(recent);
	    const recentPromise = this.store.dispatch('recent/setUnread', fakeRecent);
	    return Promise.all([usersPromise, dialoguesPromise, recentPromise]);
	  }
	  getFakeData(itemsForModel) {
	    itemsForModel = itemsForModel.slice(-4);
	    itemsForModel.forEach(item => {
	      this.store.dispatch('chats/update', {
	        dialogId: item.id,
	        fields: {
	          counter: 7
	        }
	      });
	    });
	    return itemsForModel;
	  }
	  onUpdateState({
	    data
	  }) {
	    //
	  }
	}
	UnreadRecentService.instance = null;

	var _uploaderRegistry = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploaderRegistry");
	var _onUploadCancelHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onUploadCancelHandler");
	var _addFile = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addFile");
	var _onUploadCancel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onUploadCancel");
	var _removeFileFromUploader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeFileFromUploader");
	class UploaderWrapper extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    Object.defineProperty(this, _removeFileFromUploader, {
	      value: _removeFileFromUploader2
	    });
	    Object.defineProperty(this, _onUploadCancel, {
	      value: _onUploadCancel2
	    });
	    Object.defineProperty(this, _addFile, {
	      value: _addFile2
	    });
	    Object.defineProperty(this, _uploaderRegistry, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _onUploadCancelHandler, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace(UploaderWrapper.eventNamespace);
	    babelHelpers.classPrivateFieldLooseBase(this, _onUploadCancelHandler)[_onUploadCancelHandler] = babelHelpers.classPrivateFieldLooseBase(this, _onUploadCancel)[_onUploadCancel].bind(this);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.uploader.cancel, babelHelpers.classPrivateFieldLooseBase(this, _onUploadCancelHandler)[_onUploadCancelHandler]);
	  }
	  createUploader(options) {
	    const {
	      diskFolderId,
	      uploaderId,
	      autoUpload
	    } = options;
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderRegistry)[_uploaderRegistry][uploaderId] = new ui_uploader_core.Uploader({
	      autoUpload,
	      controller: 'disk.uf.integration.diskUploaderController',
	      multiple: true,
	      controllerOptions: {
	        folderId: diskFolderId
	      },
	      imageResizeWidth: 1280,
	      imageResizeHeight: 1280,
	      imageResizeMode: 'contain',
	      imageResizeFilter: file => {
	        return !file.getCustomData('sendAsFile') && file.getExtension() !== 'gif';
	      },
	      imageResizeMimeType: 'image/jpeg',
	      imageResizeMimeTypeMode: 'force',
	      imagePreviewHeight: 720,
	      imagePreviewWidth: 720,
	      treatOversizeImageAsFile: true,
	      ignoreUnknownImageTypes: true,
	      maxFileSize: null,
	      events: {
	        [ui_uploader_core.UploaderEvent.FILE_ADD_START]: event => {
	          this.emit(UploaderWrapper.events.onFileAddStart, event);
	        },
	        [ui_uploader_core.UploaderEvent.FILE_UPLOAD_START]: event => {
	          this.emit(UploaderWrapper.events.onFileUploadStart, event);
	        },
	        [ui_uploader_core.UploaderEvent.FILE_ADD]: event => {
	          const {
	            file
	          } = event.getData();
	          this.emit(UploaderWrapper.events.onFileAdd, {
	            file,
	            uploaderId
	          });
	        },
	        [ui_uploader_core.UploaderEvent.FILE_UPLOAD_PROGRESS]: event => {
	          this.emit(UploaderWrapper.events.onFileUploadProgress, event);
	        },
	        [ui_uploader_core.UploaderEvent.FILE_UPLOAD_COMPLETE]: event => {
	          const {
	            file
	          } = event.getData();
	          this.emit(UploaderWrapper.events.onFileUploadComplete, {
	            file,
	            uploaderId
	          });
	        },
	        [ui_uploader_core.UploaderEvent.ERROR]: event => {
	          this.emit(UploaderWrapper.events.onFileUploadError, event);
	        },
	        [ui_uploader_core.UploaderEvent.FILE_ERROR]: event => {
	          this.emit(UploaderWrapper.events.onFileUploadError, event);
	        },
	        [ui_uploader_core.UploaderEvent.MAX_FILE_COUNT_EXCEEDED]: event => {
	          this.emit(UploaderWrapper.events.onMaxFileCountExceeded, event);
	        },
	        [ui_uploader_core.UploaderEvent.UPLOAD_COMPLETE]: () => {
	          babelHelpers.classPrivateFieldLooseBase(this, _uploaderRegistry)[_uploaderRegistry][uploaderId].destroy({
	            removeFilesFromServer: false
	          });
	        }
	      }
	    });
	  }
	  start(uploaderId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderRegistry)[_uploaderRegistry][uploaderId].setAutoUpload(true);
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderRegistry)[_uploaderRegistry][uploaderId].start();
	  }
	  addFiles(tasks) {
	    const addedFiles = [];
	    tasks.forEach(task => {
	      const file = babelHelpers.classPrivateFieldLooseBase(this, _addFile)[_addFile](task);
	      if (file) {
	        addedFiles.push(file);
	      }
	    });
	    return addedFiles;
	  }
	  getFiles(uploaderId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _uploaderRegistry)[_uploaderRegistry][uploaderId].getFiles();
	  }
	  destroy() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.uploader.cancel, babelHelpers.classPrivateFieldLooseBase(this, _onUploadCancelHandler)[_onUploadCancelHandler]);
	  }
	}
	function _addFile2(task) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _uploaderRegistry)[_uploaderRegistry][task.uploaderId].addFile(task.file, {
	    id: task.tempFileId,
	    customData: {
	      dialogId: task.dialogId,
	      chatId: task.chatId,
	      tempMessageId: task.tempMessageId,
	      sendAsFile: task.sendAsFile
	    }
	  });
	}
	function _onUploadCancel2(event) {
	  const {
	    tempFileId,
	    tempMessageId
	  } = event.getData();
	  if (!tempFileId || !tempMessageId) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _removeFileFromUploader)[_removeFileFromUploader](tempFileId);
	  this.emit(UploaderWrapper.events.onFileUploadCancel, {
	    tempMessageId,
	    tempFileId
	  });
	}
	function _removeFileFromUploader2(tempFileId) {
	  const uploaderList = Object.values(babelHelpers.classPrivateFieldLooseBase(this, _uploaderRegistry)[_uploaderRegistry]);
	  for (const uploader of uploaderList) {
	    if (!uploader.getFile) {
	      continue;
	    }
	    const file = uploader.getFile(tempFileId);
	    if (file) {
	      file.remove();
	      break;
	    }
	  }
	}
	UploaderWrapper.eventNamespace = 'BX.Messenger.v2.Service.Uploading.UploaderWrapper';
	UploaderWrapper.events = {
	  onFileAddStart: 'onFileAddStart',
	  onFileAdd: 'onFileAdd',
	  onFileUploadStart: 'onFileUploadStart',
	  onFileUploadProgress: 'onFileUploadProgress',
	  onFileUploadComplete: 'onFileUploadComplete',
	  onFileUploadError: 'onFileUploadError',
	  onFileUploadCancel: 'onFileUploadCancel',
	  onMaxFileCountExceeded: 'onMaxFileCountExceeded'
	};

	var _store$d = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$b = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _isRequestingDiskFolderId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isRequestingDiskFolderId");
	var _diskFolderIdRequestPromise = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("diskFolderIdRequestPromise");
	var _uploaderWrapper = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploaderWrapper");
	var _sendingService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendingService");
	var _uploaderFilesRegistry = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploaderFilesRegistry");
	var _createUploader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createUploader");
	var _addFileFromDiskToModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addFileFromDiskToModel");
	var _initUploader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initUploader");
	var _requestDiskFolderId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("requestDiskFolderId");
	var _uploadPreview = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploadPreview");
	var _prepareMessageWithFile$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareMessageWithFile");
	var _updateFileProgress = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateFileProgress");
	var _cancelUpload = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cancelUpload");
	var _addFileToStore = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addFileToStore");
	var _updateFilePreviewInStore = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateFilePreviewInStore");
	var _updateFileSizeInStore = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateFileSizeInStore");
	var _preparePreview = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("preparePreview");
	var _getDiskFolderId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDiskFolderId");
	var _getFileType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFileType");
	var _getFileExtension = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFileExtension");
	var _getDialog$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialog");
	var _getCurrentUser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getCurrentUser");
	var _getChatId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getChatId");
	var _registerFiles = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("registerFiles");
	var _setReadyFilePreview = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setReadyFilePreview");
	var _setMessagesText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setMessagesText");
	var _setAutoUpload = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setAutoUpload");
	var _createMessagesFromFiles = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createMessagesFromFiles");
	var _readyToAddMessages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("readyToAddMessages");
	var _tryToSendMessages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("tryToSendMessages");
	var _prepareFileFromDisk = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareFileFromDisk");
	var _showError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showError");
	var _setMessageError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setMessageError");
	class UploadingService$$1 {
	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }
	  constructor() {
	    Object.defineProperty(this, _setMessageError, {
	      value: _setMessageError2
	    });
	    Object.defineProperty(this, _showError, {
	      value: _showError2
	    });
	    Object.defineProperty(this, _prepareFileFromDisk, {
	      value: _prepareFileFromDisk2
	    });
	    Object.defineProperty(this, _tryToSendMessages, {
	      value: _tryToSendMessages2
	    });
	    Object.defineProperty(this, _readyToAddMessages, {
	      value: _readyToAddMessages2
	    });
	    Object.defineProperty(this, _createMessagesFromFiles, {
	      value: _createMessagesFromFiles2
	    });
	    Object.defineProperty(this, _setAutoUpload, {
	      value: _setAutoUpload2
	    });
	    Object.defineProperty(this, _setMessagesText, {
	      value: _setMessagesText2
	    });
	    Object.defineProperty(this, _setReadyFilePreview, {
	      value: _setReadyFilePreview2
	    });
	    Object.defineProperty(this, _registerFiles, {
	      value: _registerFiles2
	    });
	    Object.defineProperty(this, _getChatId, {
	      value: _getChatId2
	    });
	    Object.defineProperty(this, _getCurrentUser, {
	      value: _getCurrentUser2
	    });
	    Object.defineProperty(this, _getDialog$2, {
	      value: _getDialog2$2
	    });
	    Object.defineProperty(this, _getFileExtension, {
	      value: _getFileExtension2
	    });
	    Object.defineProperty(this, _getFileType, {
	      value: _getFileType2
	    });
	    Object.defineProperty(this, _getDiskFolderId, {
	      value: _getDiskFolderId2
	    });
	    Object.defineProperty(this, _preparePreview, {
	      value: _preparePreview2
	    });
	    Object.defineProperty(this, _updateFileSizeInStore, {
	      value: _updateFileSizeInStore2
	    });
	    Object.defineProperty(this, _updateFilePreviewInStore, {
	      value: _updateFilePreviewInStore2
	    });
	    Object.defineProperty(this, _addFileToStore, {
	      value: _addFileToStore2
	    });
	    Object.defineProperty(this, _cancelUpload, {
	      value: _cancelUpload2
	    });
	    Object.defineProperty(this, _updateFileProgress, {
	      value: _updateFileProgress2
	    });
	    Object.defineProperty(this, _prepareMessageWithFile$1, {
	      value: _prepareMessageWithFile2$1
	    });
	    Object.defineProperty(this, _uploadPreview, {
	      value: _uploadPreview2
	    });
	    Object.defineProperty(this, _requestDiskFolderId, {
	      value: _requestDiskFolderId2
	    });
	    Object.defineProperty(this, _initUploader, {
	      value: _initUploader2
	    });
	    Object.defineProperty(this, _addFileFromDiskToModel, {
	      value: _addFileFromDiskToModel2
	    });
	    Object.defineProperty(this, _createUploader, {
	      value: _createUploader2
	    });
	    Object.defineProperty(this, _store$d, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$b, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isRequestingDiskFolderId, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _diskFolderIdRequestPromise, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _uploaderWrapper, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _sendingService, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _uploaderFilesRegistry, {
	      writable: true,
	      value: {}
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$b)[_restClient$b] = im_v2_application_core.Core.getRestClient();
	    babelHelpers.classPrivateFieldLooseBase(this, _sendingService)[_sendingService] = SendingService$$1.getInstance();
	    babelHelpers.classPrivateFieldLooseBase(this, _initUploader)[_initUploader]();
	  }
	  addFilesFromClipboard(clipboardData, dialogId) {
	    return ui_uploader_core.getFilesFromDataTransfer(clipboardData).then(files => {
	      const imagesOnly = files.filter(file => im_v2_lib_utils.Utils.file.isImage(file.name));
	      if (imagesOnly.length === 0) {
	        return {
	          files: [],
	          uploaderId: ''
	        };
	      }
	      return this.addFiles({
	        files: imagesOnly,
	        dialogId,
	        autoUpload: false
	      });
	    });
	  }
	  addFilesFromInput(files, dialogId, sendAsFile) {
	    if (files.length === 0) {
	      return;
	    }
	    this.addFiles({
	      files,
	      dialogId,
	      autoUpload: true,
	      sendAsFile
	    }).then(({
	      uploaderId
	    }) => {
	      babelHelpers.classPrivateFieldLooseBase(this, _tryToSendMessages)[_tryToSendMessages](uploaderId);
	    }).catch(error => {
	      im_v2_lib_logger.Logger.error('SendingService: sendFilesFromInput error', error);
	    });
	  }
	  addFiles(params) {
	    const {
	      files,
	      dialogId,
	      autoUpload,
	      sendAsFile = false
	    } = params;
	    return babelHelpers.classPrivateFieldLooseBase(this, _createUploader)[_createUploader]({
	      dialogId,
	      autoUpload
	    }).then(uploaderId => {
	      const filesForUploader = [];
	      files.forEach(file => {
	        const messageWithFile = babelHelpers.classPrivateFieldLooseBase(this, _prepareMessageWithFile$1)[_prepareMessageWithFile$1](file, dialogId, uploaderId, sendAsFile);
	        filesForUploader.push(messageWithFile);
	      });
	      const addedFiles = babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrapper)[_uploaderWrapper].addFiles(filesForUploader);
	      babelHelpers.classPrivateFieldLooseBase(this, _registerFiles)[_registerFiles](uploaderId, addedFiles, dialogId, autoUpload);
	      return {
	        files: addedFiles,
	        uploaderId
	      };
	    });
	  }
	  getFiles(uploaderId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrapper)[_uploaderWrapper].getFiles(uploaderId);
	  }
	  start(uploaderId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].autoUpload = true;
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrapper)[_uploaderWrapper].start(uploaderId);
	  }
	  uploadFileFromDisk(files, dialogId) {
	    Object.values(files).forEach(file => {
	      const messageWithFile = babelHelpers.classPrivateFieldLooseBase(this, _prepareFileFromDisk)[_prepareFileFromDisk](file, dialogId);
	      babelHelpers.classPrivateFieldLooseBase(this, _addFileFromDiskToModel)[_addFileFromDiskToModel](messageWithFile).then(() => {
	        const message = {
	          tempMessageId: messageWithFile.tempMessageId,
	          fileId: messageWithFile.tempFileId,
	          dialogId: messageWithFile.dialogId
	        };
	        return babelHelpers.classPrivateFieldLooseBase(this, _sendingService)[_sendingService].sendMessageWithFile(message);
	      }).then(() => {
	        this.commitFile({
	          chatId: messageWithFile.chatId,
	          temporaryFileId: messageWithFile.tempFileId,
	          tempMessageId: messageWithFile.tempMessageId,
	          realFileId: messageWithFile.file.id.slice(1),
	          fromDisk: true
	        });
	      }).catch(error => {
	        console.error('SendingService: sendFilesFromDisk error:', error);
	      });
	    });
	  }
	  checkDiskFolderId(dialogId) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _getDiskFolderId)[_getDiskFolderId](dialogId) > 0) {
	      return Promise.resolve(babelHelpers.classPrivateFieldLooseBase(this, _getDiskFolderId)[_getDiskFolderId](dialogId));
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isRequestingDiskFolderId)[_isRequestingDiskFolderId]) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _diskFolderIdRequestPromise)[_diskFolderIdRequestPromise][dialogId];
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _diskFolderIdRequestPromise)[_diskFolderIdRequestPromise][dialogId] = babelHelpers.classPrivateFieldLooseBase(this, _requestDiskFolderId)[_requestDiskFolderId](dialogId);
	    return babelHelpers.classPrivateFieldLooseBase(this, _diskFolderIdRequestPromise)[_diskFolderIdRequestPromise][dialogId];
	  }
	  commitFile(params) {
	    const {
	      temporaryFileId,
	      tempMessageId,
	      chatId,
	      realFileId,
	      fromDisk,
	      messageText = '',
	      sendAsFile = false
	    } = params;
	    const fileIdParams = {};
	    if (fromDisk) {
	      fileIdParams.disk_id = realFileId;
	    } else {
	      fileIdParams.upload_id = realFileId.toString().slice(1);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$b)[_restClient$b].callMethod(im_v2_const.RestMethod.imDiskFileCommit, {
	      chat_id: chatId,
	      message: messageText,
	      template_id: tempMessageId,
	      file_template_id: temporaryFileId,
	      as_file: sendAsFile ? 'Y' : 'N',
	      ...fileIdParams
	    }).catch(error => {
	      babelHelpers.classPrivateFieldLooseBase(this, _setMessageError)[_setMessageError](tempMessageId);
	      babelHelpers.classPrivateFieldLooseBase(this, _updateFileProgress)[_updateFileProgress](temporaryFileId, 0, im_v2_const.FileStatus.error);
	      im_v2_lib_logger.Logger.error('commitFile error', error);
	    });
	  }
	  sendSeparateMessagesWithFiles(params) {
	    const {
	      uploaderId,
	      text
	    } = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _setMessagesText)[_setMessagesText](uploaderId, text);
	    babelHelpers.classPrivateFieldLooseBase(this, _setAutoUpload)[_setAutoUpload](uploaderId, true);
	    babelHelpers.classPrivateFieldLooseBase(this, _tryToSendMessages)[_tryToSendMessages](uploaderId);
	  }
	  destroy() {
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrapper)[_uploaderWrapper].destroy();
	  }
	}
	function _createUploader2(params) {
	  const {
	    dialogId,
	    autoUpload
	  } = params;
	  const uploaderId = im_v2_lib_utils.Utils.text.getUuidV4();
	  return this.checkDiskFolderId(dialogId).then(diskFolderId => {
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrapper)[_uploaderWrapper].createUploader({
	      diskFolderId,
	      uploaderId,
	      autoUpload
	    });
	    return uploaderId;
	  });
	}
	function _addFileFromDiskToModel2(messageWithFile) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d].dispatch('files/add', {
	    id: messageWithFile.tempFileId,
	    chatId: messageWithFile.chatId,
	    authorId: im_v2_application_core.Core.getUserId(),
	    name: messageWithFile.file.name,
	    type: im_v2_lib_utils.Utils.file.getFileTypeByExtension(messageWithFile.file.ext),
	    extension: messageWithFile.file.ext,
	    size: messageWithFile.file.sizeInt,
	    status: im_v2_const.FileStatus.wait,
	    progress: 0,
	    authorName: babelHelpers.classPrivateFieldLooseBase(this, _getCurrentUser)[_getCurrentUser]().name
	  });
	}
	function _initUploader2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrapper)[_uploaderWrapper] = new UploaderWrapper();
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrapper)[_uploaderWrapper].subscribe(UploaderWrapper.events.onFileAddStart, event => {
	    const {
	      file
	    } = event.getData();
	    babelHelpers.classPrivateFieldLooseBase(this, _addFileToStore)[_addFileToStore](file);
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrapper)[_uploaderWrapper].subscribe(UploaderWrapper.events.onFileAdd, event => {
	    const {
	      file,
	      uploaderId
	    } = event.getData();
	    babelHelpers.classPrivateFieldLooseBase(this, _updateFilePreviewInStore)[_updateFilePreviewInStore](file);
	    babelHelpers.classPrivateFieldLooseBase(this, _setReadyFilePreview)[_setReadyFilePreview](uploaderId, file.getId());
	    babelHelpers.classPrivateFieldLooseBase(this, _tryToSendMessages)[_tryToSendMessages](uploaderId);
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrapper)[_uploaderWrapper].subscribe(UploaderWrapper.events.onFileUploadStart, event => {
	    const {
	      file
	    } = event.getData();
	    babelHelpers.classPrivateFieldLooseBase(this, _updateFileSizeInStore)[_updateFileSizeInStore](file);
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrapper)[_uploaderWrapper].subscribe(UploaderWrapper.events.onFileUploadProgress, event => {
	    const {
	      file
	    } = event.getData();
	    babelHelpers.classPrivateFieldLooseBase(this, _updateFileProgress)[_updateFileProgress](file.getId(), file.getProgress(), im_v2_const.FileStatus.upload);
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrapper)[_uploaderWrapper].subscribe(UploaderWrapper.events.onFileUploadComplete, event => {
	    const {
	      file
	    } = event.getData();
	    babelHelpers.classPrivateFieldLooseBase(this, _updateFileProgress)[_updateFileProgress](file.getId(), file.getProgress(), im_v2_const.FileStatus.wait);
	    babelHelpers.classPrivateFieldLooseBase(this, _uploadPreview)[_uploadPreview](file).then(() => {
	      var _file$getCustomData;
	      this.commitFile({
	        realFileId: file.getServerFileId(),
	        temporaryFileId: file.getId(),
	        chatId: file.getCustomData('chatId'),
	        tempMessageId: file.getCustomData('tempMessageId'),
	        messageText: (_file$getCustomData = file.getCustomData('messageText')) != null ? _file$getCustomData : '',
	        sendAsFile: file.getCustomData('sendAsFile'),
	        fromDisk: false
	      });
	    }).catch(error => {
	      im_v2_lib_logger.Logger.warn('UploadingService: upload preview error', error);
	    });
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrapper)[_uploaderWrapper].subscribe(UploaderWrapper.events.onFileUploadError, event => {
	    const {
	      file,
	      error
	    } = event.getData();
	    babelHelpers.classPrivateFieldLooseBase(this, _updateFileProgress)[_updateFileProgress](file.getId(), 0, im_v2_const.FileStatus.error);
	    babelHelpers.classPrivateFieldLooseBase(this, _setMessageError)[_setMessageError](file.getCustomData('tempMessageId'));
	    babelHelpers.classPrivateFieldLooseBase(this, _showError)[_showError](error);
	    im_v2_lib_logger.Logger.error('UploadingService: upload error', error);
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrapper)[_uploaderWrapper].subscribe(UploaderWrapper.events.onFileUploadCancel, event => {
	    const {
	      tempMessageId,
	      tempFileId
	    } = event.getData();
	    babelHelpers.classPrivateFieldLooseBase(this, _cancelUpload)[_cancelUpload](tempMessageId, tempFileId);
	  });
	}
	function _requestDiskFolderId2(dialogId) {
	  return new Promise((resolve, reject) => {
	    babelHelpers.classPrivateFieldLooseBase(this, _isRequestingDiskFolderId)[_isRequestingDiskFolderId] = true;
	    const chatId = babelHelpers.classPrivateFieldLooseBase(this, _getChatId)[_getChatId](dialogId);
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$b)[_restClient$b].callMethod(im_v2_const.RestMethod.imDiskFolderGet, {
	      chat_id: chatId
	    }).then(response => {
	      const {
	        ID: diskFolderId
	      } = response.data();
	      babelHelpers.classPrivateFieldLooseBase(this, _isRequestingDiskFolderId)[_isRequestingDiskFolderId] = false;
	      babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d].commit('chats/update', {
	        dialogId,
	        fields: {
	          diskFolderId
	        }
	      });
	      resolve(diskFolderId);
	    }).catch(error => {
	      babelHelpers.classPrivateFieldLooseBase(this, _isRequestingDiskFolderId)[_isRequestingDiskFolderId] = false;
	      reject(error);
	    });
	  });
	}
	function _uploadPreview2(file) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _getFileType)[_getFileType](file.getBinary()) === im_v2_const.FileType.file || file.getExtension() === 'gif') {
	    return Promise.resolve();
	  }
	  const id = file.getServerFileId().toString().slice(1);
	  const previewFile = file.getClientPreview();
	  if (!previewFile) {
	    file.setCustomData('sendAsFile', true);
	    return Promise.resolve();
	  }
	  const formData = new FormData();
	  formData.append('id', id);
	  formData.append('previewFile', previewFile, `preview_${file.getName()}.jpg`);
	  return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imDiskFilePreviewUpload, {
	    data: formData
	  }).catch(error => {
	    im_v2_lib_logger.Logger.error('imDiskFilePreviewUpload request error', error);
	  });
	}
	function _prepareMessageWithFile2$1(file, dialogId, uploaderId, sendAsFile) {
	  const tempMessageId = im_v2_lib_utils.Utils.text.getUuidV4();
	  const tempFileId = im_v2_lib_utils.Utils.text.getUuidV4();
	  const chatId = babelHelpers.classPrivateFieldLooseBase(this, _getChatId)[_getChatId](dialogId);
	  return {
	    tempMessageId,
	    tempFileId,
	    file,
	    dialogId,
	    chatId,
	    uploaderId,
	    sendAsFile: sendAsFile && babelHelpers.classPrivateFieldLooseBase(this, _getFileType)[_getFileType](file) !== im_v2_const.FileType.file
	  };
	}
	function _updateFileProgress2(id, progress, status) {
	  babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d].dispatch('files/update', {
	    id,
	    fields: {
	      progress: progress === 100 ? 99 : progress,
	      status
	    }
	  });
	}
	function _cancelUpload2(tempMessageId, tempFileId) {
	  babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d].dispatch('messages/delete', {
	    id: tempMessageId
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d].dispatch('files/delete', {
	    id: tempFileId
	  });
	}
	function _addFileToStore2(file) {
	  const taskId = file.getId();
	  const fileBinary = file.getBinary();
	  const previewData = babelHelpers.classPrivateFieldLooseBase(this, _preparePreview)[_preparePreview](file);
	  babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d].dispatch('files/add', {
	    id: taskId,
	    chatId: file.getCustomData('chatId'),
	    authorId: im_v2_application_core.Core.getUserId(),
	    name: fileBinary.name,
	    type: babelHelpers.classPrivateFieldLooseBase(this, _getFileType)[_getFileType](fileBinary),
	    extension: babelHelpers.classPrivateFieldLooseBase(this, _getFileExtension)[_getFileExtension](fileBinary),
	    status: file.isFailed() ? im_v2_const.FileStatus.error : im_v2_const.FileStatus.progress,
	    progress: 0,
	    authorName: babelHelpers.classPrivateFieldLooseBase(this, _getCurrentUser)[_getCurrentUser]().name,
	    ...previewData
	  });
	}
	function _updateFilePreviewInStore2(file) {
	  const previewData = babelHelpers.classPrivateFieldLooseBase(this, _preparePreview)[_preparePreview](file);
	  babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d].dispatch('files/update', {
	    id: file.getId(),
	    fields: {
	      ...previewData
	    }
	  });
	}
	function _updateFileSizeInStore2(file) {
	  babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d].dispatch('files/update', {
	    id: file.getId(),
	    fields: {
	      size: file.getSize()
	    }
	  });
	}
	function _preparePreview2(file) {
	  if (file.getCustomData('sendAsFile')) {
	    return {};
	  }
	  const preview = {
	    blob: file.getPreviewUrl(),
	    width: file.getPreviewWidth(),
	    height: file.getPreviewHeight()
	  };
	  const previewData = {};
	  if (preview.blob) {
	    previewData.image = {
	      width: preview.width,
	      height: preview.height
	    };
	    previewData.urlPreview = preview.blob;
	  }
	  if (file.getClientPreview()) {
	    previewData.urlShow = URL.createObjectURL(file.getBinary());
	  }
	  return previewData;
	}
	function _getDiskFolderId2(dialogId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _getDialog$2)[_getDialog$2](dialogId).diskFolderId;
	}
	function _getFileType2(file) {
	  let fileType = im_v2_const.FileType.file;
	  if (file.type.startsWith('image')) {
	    fileType = im_v2_const.FileType.image;
	  } else if (file.type.startsWith('video')) {
	    fileType = im_v2_const.FileType.video;
	  }
	  return fileType;
	}
	function _getFileExtension2(file) {
	  return file.name.split('.').splice(-1)[0];
	}
	function _getDialog2$2(dialogId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d].getters['chats/get'](dialogId);
	}
	function _getCurrentUser2() {
	  const userId = im_v2_application_core.Core.getUserId();
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d].getters['users/get'](userId);
	}
	function _getChatId2(dialogId) {
	  var _babelHelpers$classPr;
	  return (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _getDialog$2)[_getDialog$2](dialogId)) == null ? void 0 : _babelHelpers$classPr.chatId;
	}
	function _registerFiles2(uploaderId, files, dialogId, autoUpload) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId] = {
	      filesPreviewStatus: {},
	      dialogId,
	      text: '',
	      autoUpload
	    };
	  }
	  files.forEach(file => {
	    const fileId = file.getId();
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].filesPreviewStatus) {
	      babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].filesPreviewStatus = {};
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].filesPreviewStatus[fileId] = false;
	  });
	}
	function _setReadyFilePreview2(uploaderId, fileId) {
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].filesPreviewStatus[fileId] = true;
	}
	function _setMessagesText2(uploaderId, text) {
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].text = text;
	}
	function _setAutoUpload2(uploaderId, autoUploadFlag) {
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].autoUpload = autoUploadFlag;
	}
	function _createMessagesFromFiles2(uploaderId) {
	  const messagesToSend = {
	    comment: {},
	    files: []
	  };
	  const files = this.getFiles(uploaderId);
	  const text = babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].text;
	  const dialogId = babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].dialogId;
	  const hasText = text.length > 0;

	  // if we have more than one file and text, we need to send text message first
	  if (files.length > 1 && hasText) {
	    messagesToSend.comment = {
	      dialogId,
	      text
	    };
	  }
	  files.forEach(file => {
	    var _file$getCustomData2;
	    if (file.getError()) {
	      return;
	    }
	    const messageId = im_v2_lib_utils.Utils.text.getUuidV4();
	    file.setCustomData('messageId', messageId);
	    if (files.length === 1 && hasText) {
	      file.setCustomData('messageText', text);
	    }
	    messagesToSend.files.push({
	      fileId: file.getId(),
	      tempMessageId: file.getCustomData('tempMessageId'),
	      dialogId,
	      text: (_file$getCustomData2 = file.getCustomData('messageText')) != null ? _file$getCustomData2 : ''
	    });
	  });
	  return messagesToSend;
	}
	function _readyToAddMessages2(uploaderId) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId] || !babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].autoUpload || babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].wasSent) {
	    return false;
	  }
	  const {
	    filesPreviewStatus
	  } = babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId];
	  return Object.values(filesPreviewStatus).every(flag => flag === true);
	}
	function _tryToSendMessages2(uploaderId) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _readyToAddMessages)[_readyToAddMessages](uploaderId)) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].wasSent = true;
	  const {
	    comment,
	    files
	  } = babelHelpers.classPrivateFieldLooseBase(this, _createMessagesFromFiles)[_createMessagesFromFiles](uploaderId);
	  if (comment.text) {
	    void babelHelpers.classPrivateFieldLooseBase(this, _sendingService)[_sendingService].sendMessage(comment);
	  }
	  files.forEach(message => {
	    void babelHelpers.classPrivateFieldLooseBase(this, _sendingService)[_sendingService].sendMessageWithFile(message);
	  });
	  this.start(uploaderId);
	}
	function _prepareFileFromDisk2(file, dialogId) {
	  const tempMessageId = im_v2_lib_utils.Utils.text.getUuidV4();
	  const realFileId = file.id.slice(1); // 'n123' => '123'
	  const tempFileId = `${tempMessageId}|${realFileId}`;
	  return {
	    tempMessageId,
	    tempFileId,
	    dialogId,
	    file,
	    chatId: babelHelpers.classPrivateFieldLooseBase(this, _getDialog$2)[_getDialog$2](dialogId).chatId
	  };
	}
	function _showError2(error) {
	  if (error.getCode() === 'MAX_FILE_SIZE_EXCEEDED') {
	    BX.UI.Notification.Center.notify({
	      content: `${error.getMessage()}<br>${error.getDescription()}`
	    });
	  }
	}
	function _setMessageError2(tempMessageId) {
	  babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d].dispatch('messages/update', {
	    id: tempMessageId,
	    fields: {
	      error: true
	    }
	  });
	}
	UploadingService$$1.instance = null;

	class SettingsService {
	  changeSetting(settingName, value) {
	    im_v2_lib_logger.Logger.warn('SettingsService: changeSetting', settingName, value);
	    im_v2_application_core.Core.getStore().dispatch('application/settings/set', {
	      [settingName]: value
	    });
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2SettingsGeneralUpdate, {
	      data: {
	        userId: im_v2_application_core.Core.getUserId(),
	        name: settingName,
	        value: value
	      }
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('SettingsService: changeSetting error', error);
	    });
	  }
	}

	class LinesService {
	  getDialogIdByUserCode(userCode) {
	    return im_v2_application_core.Core.getRestClient().callMethod(im_v2_const.RestMethod.linesDialogGet, {
	      USER_CODE: userCode
	    }).then(result => {
	      const {
	        dialog_id: dialogId
	      } = result.data();
	      return dialogId;
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('LinesService: error getting dialog id', error);
	    });
	  }
	}

	var _onCreateError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onCreateError");
	var _sendAnalytics = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendAnalytics");
	class CopilotService {
	  constructor() {
	    Object.defineProperty(this, _sendAnalytics, {
	      value: _sendAnalytics2
	    });
	    Object.defineProperty(this, _onCreateError, {
	      value: _onCreateError2
	    });
	  }
	  async createChat() {
	    const chatService = new ChatService();
	    const {
	      newDialogId,
	      newChatId
	    } = await chatService.createChat({
	      type: im_v2_const.ChatType.copilot
	    }).catch(error => {
	      babelHelpers.classPrivateFieldLooseBase(this, _onCreateError)[_onCreateError](error);
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _sendAnalytics)[_sendAnalytics]({
	      chatId: newChatId,
	      dialogId: newDialogId
	    });
	    await chatService.loadChatWithMessages(newDialogId).catch(error => {
	      babelHelpers.classPrivateFieldLooseBase(this, _onCreateError)[_onCreateError](error);
	    });
	    return newDialogId;
	  }
	}
	function _onCreateError2(error) {
	  // eslint-disable-next-line no-console
	  console.error('Copilot chat create error', error);
	  throw new Error('Copilot chat create error');
	}
	function _sendAnalytics2({
	  chatId,
	  dialogId
	}) {
	  im_v2_lib_analytics.Analytics.getInstance().createChat({
	    chatId,
	    dialogId
	  });
	}

	exports.RecentService = RecentService;
	exports.ChatService = ChatService;
	exports.MessageService = MessageService;
	exports.SendingService = SendingService$$1;
	exports.NotificationService = NotificationService;
	exports.DiskService = DiskService;
	exports.UnreadRecentService = UnreadRecentService;
	exports.UploadingService = UploadingService$$1;
	exports.SettingsService = SettingsService;
	exports.LinesService = LinesService;
	exports.CopilotService = CopilotService;

}((this.BX.Messenger.v2.Provider.Service = this.BX.Messenger.v2.Provider.Service || {}),BX.Messenger.v2.Provider.Service,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Vue3.Vuex,BX,BX,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Lib,BX.Event,BX.UI.Uploader,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib));
//# sourceMappingURL=registry.bundle.js.map
