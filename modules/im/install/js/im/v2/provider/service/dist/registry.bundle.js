/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_lib_feature,im_v2_provider_service,imopenlines_v2_lib_openlines,im_v2_lib_roleManager,im_v2_lib_uuid,im_v2_lib_layout,im_public,im_v2_lib_copilot,im_v2_lib_access,ui_vue3_vuex,ui_notification,main_core,im_v2_lib_user,rest_client,im_v2_lib_utils,main_core_events,ui_uploader_core,im_v2_lib_logger,im_v2_lib_analytics,im_v2_application_core,im_v2_lib_rest,im_v2_const) {
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
	var _mergeFileIds = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("mergeFileIds");
	class RecentDataExtractor {
	  constructor(params) {
	    Object.defineProperty(this, _mergeFileIds, {
	      value: _mergeFileIds2
	    });
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
	      items = [],
	      copilot
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
	      recentItems: Object.values(babelHelpers.classPrivateFieldLooseBase(this, _recentItems)[_recentItems]),
	      copilot
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
	  const existingMessage = im_v2_application_core.Core.getStore().getters['messages/getById'](message.id);
	  // recent has shortened attach format, we should not rewrite attach if model has it
	  if (main_core.Type.isArrayFilled(existingMessage == null ? void 0 : existingMessage.attach)) {
	    delete message.attach;
	  }
	  if (main_core.Type.isPlainObject(message.file)) {
	    const file = message.file;
	    if (existingMessage) {
	      // recent doesn't know about several files in one message,
	      // we should not rewrite message files, so we merge it.
	      message.files = babelHelpers.classPrivateFieldLooseBase(this, _mergeFileIds)[_mergeFileIds](existingMessage, file.id);
	    } else {
	      message.files = [file.id];
	    }
	    const existingFile = im_v2_application_core.Core.getStore().getters['files/get'](file.id);
	    // recent has shortened file format, we should not rewrite file if model has it
	    if (!existingFile) {
	      babelHelpers.classPrivateFieldLooseBase(this, _files)[_files][file.id] = file;
	    }
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
	    dialogId: item.id,
	    copilot: item.copilot
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
	function _mergeFileIds2(existingMessage, fileId) {
	  const existingMessageFilesIds = existingMessage.files.map(id => {
	    return Number.parseInt(id, 10);
	  });
	  const setOfFileIds = new Set([...existingMessageFilesIds, fileId]);
	  return [...setOfFileIds];
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
	      im_v2_lib_layout.LayoutManager.getInstance().clearCurrentLayoutEntityId();
	      void im_v2_lib_layout.LayoutManager.getInstance().deleteLastOpenedElementById(dialogId);
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
	      recentItems,
	      copilot
	    } = extractedItems;
	    im_v2_lib_logger.Logger.warn('RecentService: prepared data for models', extractedItems);
	    const usersPromise = im_v2_application_core.Core.getStore().dispatch('users/set', users);
	    const dialoguesPromise = im_v2_application_core.Core.getStore().dispatch('chats/set', chats);
	    const messagesPromise = im_v2_application_core.Core.getStore().dispatch('messages/store', messages);
	    const filesPromise = im_v2_application_core.Core.getStore().dispatch('files/set', files);
	    const recentPromise = im_v2_application_core.Core.getStore().dispatch(this.getModelSaveMethod(), recentItems);
	    const copilotManager = new im_v2_lib_copilot.CopilotManager();
	    const copilotPromise = copilotManager.handleRecentListResponse(copilot);
	    return Promise.all([usersPromise, dialoguesPromise, messagesPromise, filesPromise, recentPromise, copilotPromise]);
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

	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _updateModels = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateModels");
	class DeleteService {
	  constructor() {
	    Object.defineProperty(this, _updateModels, {
	      value: _updateModels2
	    });
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	  }
	  async deleteChat(dialogId) {
	    im_v2_lib_logger.Logger.warn(`ChatService: deleteChat, dialogId: ${dialogId}`);
	    const deleteResult = await im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatDelete, {
	      data: {
	        dialogId
	      }
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('ChatService: deleteChat error:', error);
	      throw new Error(error);
	    });
	    await babelHelpers.classPrivateFieldLooseBase(this, _updateModels)[_updateModels](dialogId);
	    return deleteResult;
	  }
	  async deleteCollab(dialogId) {
	    im_v2_lib_logger.Logger.warn(`ChatService: deleteCollab, dialogId: ${dialogId}`);
	    const deleteResult = await im_v2_lib_rest.runAction(im_v2_const.RestMethod.socialnetworkCollabDelete, {
	      data: {
	        dialogId
	      }
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('ChatService: deleteCollab error:', error);
	      throw error;
	    });
	    await babelHelpers.classPrivateFieldLooseBase(this, _updateModels)[_updateModels](dialogId);
	    return deleteResult;
	  }
	}
	function _updateModels2(dialogId) {
	  void babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('chats/update', {
	    dialogId,
	    fields: {
	      inited: false
	    }
	  });
	  void babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('recent/delete', {
	    id: dialogId
	  });
	  const chat = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['chats/get'](dialogId, true);
	  void babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/clearChatCollection', {
	    chatId: chat.chatId
	  });
	}

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
	  isCopilotChat() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].chat.type === im_v2_const.ChatType.copilot;
	  }
	  getChats() {
	    const mainChat = {
	      ...babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].chat,
	      hasPrevPage: babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].hasPrevPage,
	      hasNextPage: babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].hasNextPage,
	      tariffRestrictions: babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].tariffRestrictions
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
	  getCommentInfo() {
	    var _babelHelpers$classPr5;
	    return (_babelHelpers$classPr5 = babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].commentInfo) != null ? _babelHelpers$classPr5 : [];
	  }
	  getCollabInfo() {
	    var _babelHelpers$classPr6;
	    return (_babelHelpers$classPr6 = babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].collabInfo) != null ? _babelHelpers$classPr6 : null;
	  }
	  getMessagesToStore() {
	    var _babelHelpers$classPr7;
	    return (_babelHelpers$classPr7 = babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].additionalMessages) != null ? _babelHelpers$classPr7 : [];
	  }
	  getPinnedMessageIds() {
	    var _babelHelpers$classPr8;
	    const pinnedMessageIds = [];
	    const pins = (_babelHelpers$classPr8 = babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].pins) != null ? _babelHelpers$classPr8 : [];
	    pins.forEach(pin => {
	      pinnedMessageIds.push(pin.messageId);
	    });
	    return pinnedMessageIds;
	  }
	  getReactions() {
	    var _babelHelpers$classPr9;
	    return (_babelHelpers$classPr9 = babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].reactions) != null ? _babelHelpers$classPr9 : [];
	  }
	  getCopilot() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].copilot;
	  }
	  getSession() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _restResult$1)[_restResult$1].session;
	  }
	}

	var _store$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _requestChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("requestChat");
	var _markDialogAsLoading = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("markDialogAsLoading");
	var _markDialogAsLoaded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("markDialogAsLoaded");
	var _markDialogAsNotLoaded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("markDialogAsNotLoaded");
	var _isDialogLoadedMarkNeeded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isDialogLoadedMarkNeeded");
	var _updateModels$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateModels");
	var _needLayoutRedirect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("needLayoutRedirect");
	var _redirectToLayout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("redirectToLayout");
	var _needRedirectToCopilotLayout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("needRedirectToCopilotLayout");
	var _needRedirectToOpenLinesLayout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("needRedirectToOpenLinesLayout");
	var _handleChatLoadError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleChatLoadError");
	var _showNotification = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showNotification");
	class LoadService {
	  constructor() {
	    Object.defineProperty(this, _showNotification, {
	      value: _showNotification2
	    });
	    Object.defineProperty(this, _handleChatLoadError, {
	      value: _handleChatLoadError2
	    });
	    Object.defineProperty(this, _needRedirectToOpenLinesLayout, {
	      value: _needRedirectToOpenLinesLayout2
	    });
	    Object.defineProperty(this, _needRedirectToCopilotLayout, {
	      value: _needRedirectToCopilotLayout2
	    });
	    Object.defineProperty(this, _redirectToLayout, {
	      value: _redirectToLayout2
	    });
	    Object.defineProperty(this, _needLayoutRedirect, {
	      value: _needLayoutRedirect2
	    });
	    Object.defineProperty(this, _updateModels$1, {
	      value: _updateModels2$1
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
	    Object.defineProperty(this, _store$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1] = im_v2_application_core.Core.getStore();
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
	  async loadComments(postId) {
	    const params = {
	      postId,
	      messageLimit: im_v2_provider_service.MessageService.getMessageRequestLimit(),
	      autoJoin: true,
	      createIfNotExists: true
	    };
	    const {
	      chatId
	    } = await babelHelpers.classPrivateFieldLooseBase(this, _requestChat)[_requestChat](im_v2_const.RestMethod.imV2ChatLoad, params);
	    return babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('messages/comments/set', {
	      messageId: postId,
	      chatId
	    });
	  }
	  async loadCommentInfo(channelDialogId) {
	    const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].getters['chats/get'](channelDialogId, true);
	    const messages = babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].getters['messages/getByChatId'](dialog.chatId);
	    const messageIds = messages.map(message => message.id);
	    const {
	      commentInfo,
	      usersShort
	    } = await im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatMessageCommentInfoList, {
	      data: {
	        messageIds
	      }
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('ChatService: Load: error loading comment info', error);
	    });
	    const userManager = new im_v2_lib_user.UserManager();
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('messages/comments/set', commentInfo);
	    void userManager.addUsersToModel(usersShort);
	  }
	  resetChat(dialogId) {
	    const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].getters['chats/get'](dialogId, true);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('messages/clearChatCollection', {
	      chatId: dialog.chatId
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('chats/update', {
	      dialogId,
	      fields: {
	        inited: false
	      }
	    });
	  }
	}
	async function _requestChat2(actionName, params) {
	  const {
	    dialogId,
	    messageId
	  } = params;
	  babelHelpers.classPrivateFieldLooseBase(this, _markDialogAsLoading)[_markDialogAsLoading](dialogId);
	  const actionResult = await im_v2_lib_rest.runAction(actionName, {
	    data: params
	  }).catch(errors => {
	    // eslint-disable-next-line no-console
	    console.error('ChatService: Load: error loading chat', errors);
	    babelHelpers.classPrivateFieldLooseBase(this, _handleChatLoadError)[_handleChatLoadError](errors);
	    babelHelpers.classPrivateFieldLooseBase(this, _markDialogAsNotLoaded)[_markDialogAsNotLoaded](dialogId);
	    throw errors;
	  });
	  if (babelHelpers.classPrivateFieldLooseBase(this, _needLayoutRedirect)[_needLayoutRedirect](actionResult)) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _redirectToLayout)[_redirectToLayout](actionResult, messageId);
	  }
	  const {
	    dialogId: loadedDialogId,
	    chatId
	  } = await babelHelpers.classPrivateFieldLooseBase(this, _updateModels$1)[_updateModels$1](actionResult);
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isDialogLoadedMarkNeeded)[_isDialogLoadedMarkNeeded](actionName)) {
	    await babelHelpers.classPrivateFieldLooseBase(this, _markDialogAsLoaded)[_markDialogAsLoaded](loadedDialogId);
	  }
	  return {
	    dialogId: loadedDialogId,
	    chatId
	  };
	}
	function _markDialogAsLoading2(dialogId) {
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('chats/update', {
	    dialogId,
	    fields: {
	      loading: true
	    }
	  });
	}
	function _markDialogAsLoaded2(dialogId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('chats/update', {
	    dialogId,
	    fields: {
	      inited: true,
	      loading: false
	    }
	  });
	}
	function _markDialogAsNotLoaded2(dialogId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('chats/update', {
	    dialogId,
	    fields: {
	      loading: false
	    }
	  });
	}
	function _isDialogLoadedMarkNeeded2(actionName) {
	  return actionName !== im_v2_const.RestMethod.imV2ChatShallowLoad;
	}
	async function _updateModels2$1(restResult) {
	  const extractor = new ChatDataExtractor(restResult);
	  const chatsPromise = babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('chats/set', extractor.getChats());
	  const filesPromise = babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('files/set', extractor.getFiles());
	  const userManager = new im_v2_lib_user.UserManager();
	  const usersPromise = Promise.all([babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('users/set', extractor.getUsers()), userManager.addUsersToModel(extractor.getAdditionalUsers())]);
	  const messagesPromise = Promise.all([babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('messages/setChatCollection', {
	    messages: extractor.getMessages(),
	    clearCollection: true
	  }), babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('messages/store', extractor.getMessagesToStore()), babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('messages/pin/setPinned', {
	    chatId: extractor.getChatId(),
	    pinnedMessages: extractor.getPinnedMessageIds()
	  }), babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('messages/reactions/set', extractor.getReactions()), babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('messages/comments/set', extractor.getCommentInfo())]);
	  const copilotManager = new im_v2_lib_copilot.CopilotManager();
	  const copilotPromise = copilotManager.handleChatLoadResponse(extractor.getCopilot());
	  const openLinesPromise = imopenlines_v2_lib_openlines.OpenLinesManager.handleChatLoadResponse(extractor.getSession());
	  const collabPromise = babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('chats/collabs/set', {
	    chatId: extractor.getChatId(),
	    collabInfo: extractor.getCollabInfo()
	  });
	  await Promise.all([chatsPromise, filesPromise, usersPromise, messagesPromise, copilotPromise, openLinesPromise, collabPromise]);
	  return {
	    dialogId: extractor.getDialogId(),
	    chatId: extractor.getChatId()
	  };
	}
	function _needLayoutRedirect2(actionResult) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _needRedirectToCopilotLayout)[_needRedirectToCopilotLayout](actionResult) || babelHelpers.classPrivateFieldLooseBase(this, _needRedirectToOpenLinesLayout)[_needRedirectToOpenLinesLayout](actionResult);
	}
	function _redirectToLayout2(actionResult, contextId = 0) {
	  const extractor = new ChatDataExtractor(actionResult);
	  im_v2_lib_layout.LayoutManager.getInstance().setLastOpenedElement(im_v2_const.Layout.chat.name, '');
	  if (babelHelpers.classPrivateFieldLooseBase(this, _needRedirectToCopilotLayout)[_needRedirectToCopilotLayout](actionResult)) {
	    return im_public.Messenger.openCopilot(extractor.getDialogId(), contextId);
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _needRedirectToOpenLinesLayout)[_needRedirectToOpenLinesLayout](actionResult)) {
	    return im_public.Messenger.openLines(extractor.getDialogId());
	  }
	  return Promise.resolve();
	}
	function _needRedirectToCopilotLayout2(actionResult) {
	  const extractor = new ChatDataExtractor(actionResult);
	  const currentLayoutName = im_v2_lib_layout.LayoutManager.getInstance().getLayout().name;
	  return extractor.isCopilotChat() && currentLayoutName !== im_v2_const.Layout.copilot.name;
	}
	function _needRedirectToOpenLinesLayout2(actionResult) {
	  const optionOpenLinesV2Activated = im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.openLinesV2);
	  if (optionOpenLinesV2Activated) {
	    return false;
	  }
	  const extractor = new ChatDataExtractor(actionResult);
	  return extractor.isOpenlinesChat() && main_core.Type.isStringFilled(extractor.getDialogId());
	}
	function _handleChatLoadError2(errors) {
	  const [firstError] = errors;
	  if (firstError.code === im_v2_lib_access.AccessErrorCode.chatNotFound) {
	    babelHelpers.classPrivateFieldLooseBase(this, _showNotification)[_showNotification](main_core.Loc.getMessage('IM_CONTENT_CHAT_ACCESS_ERROR_MSGVER_1'));
	  }
	}
	function _showNotification2(text) {
	  BX.UI.Notification.Center.notify({
	    content: text
	  });
	}

	const PRIVATE_CHAT = 'CHAT';
	const OPEN_CHAT = 'OPEN';
	var _restClient = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _store$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _prepareFields = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareFields");
	var _addCollabToModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addCollabToModel");
	var _addChatToModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addChatToModel");
	var _sendAnalytics = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendAnalytics");
	class CreateService {
	  constructor() {
	    Object.defineProperty(this, _sendAnalytics, {
	      value: _sendAnalytics2
	    });
	    Object.defineProperty(this, _addChatToModel, {
	      value: _addChatToModel2
	    });
	    Object.defineProperty(this, _addCollabToModel, {
	      value: _addCollabToModel2
	    });
	    Object.defineProperty(this, _prepareFields, {
	      value: _prepareFields2
	    });
	    Object.defineProperty(this, _restClient, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _store$2, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient] = im_v2_application_core.Core.getRestClient();
	    babelHelpers.classPrivateFieldLooseBase(this, _store$2)[_store$2] = im_v2_application_core.Core.getStore();
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
	    babelHelpers.classPrivateFieldLooseBase(this, _sendAnalytics)[_sendAnalytics](newDialogId);
	    return {
	      newDialogId,
	      newChatId
	    };
	  }
	  async createCollab(collabConfig) {
	    im_v2_lib_logger.Logger.warn('ChatService: createCollab', collabConfig);
	    const preparedFields = await babelHelpers.classPrivateFieldLooseBase(this, _prepareFields)[_prepareFields](collabConfig);
	    const params = {
	      ownerId: preparedFields.ownerId,
	      name: preparedFields.title,
	      description: preparedFields.description,
	      avatarId: preparedFields.avatar,
	      moderatorMembers: im_v2_lib_utils.Utils.user.prepareSelectorIds(collabConfig.moderatorMembers),
	      permissions: collabConfig.permissions,
	      options: collabConfig.options
	    };
	    const createResult = await im_v2_lib_rest.runAction(im_v2_const.RestMethod.socialnetworkCollabCreate, {
	      data: params
	    }).catch(([error]) => {
	      // eslint-disable-next-line no-console
	      console.error('ChatService: createCollab error:', error);
	      throw error;
	    });
	    const {
	      chatId: newChatId
	    } = createResult;
	    im_v2_lib_logger.Logger.warn('ChatService: createCollab result', newChatId);
	    const newDialogId = `chat${newChatId}`;
	    babelHelpers.classPrivateFieldLooseBase(this, _addCollabToModel)[_addCollabToModel](newDialogId, preparedFields);
	    babelHelpers.classPrivateFieldLooseBase(this, _sendAnalytics)[_sendAnalytics](newDialogId);
	    return {
	      newDialogId,
	      newChatId
	    };
	  }
	}
	async function _prepareFields2(chatConfig) {
	  var _preparedConfig$manag, _preparedConfig$membe, _preparedConfig$type, _preparedConfig$entit;
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
	  const result = {
	    type: (_preparedConfig$type = preparedConfig.type) == null ? void 0 : _preparedConfig$type.toUpperCase(),
	    entityType: (_preparedConfig$entit = preparedConfig.entityType) == null ? void 0 : _preparedConfig$entit.toUpperCase(),
	    title: preparedConfig.title,
	    avatar: preparedConfig.avatar,
	    description: preparedConfig.description,
	    users: preparedConfig.members,
	    memberEntities: preparedConfig.memberEntities,
	    managers: preparedConfig.managers,
	    ownerId: preparedConfig.ownerId,
	    searchable: preparedConfig.isAvailableInSearch ? 'Y' : 'N',
	    manageUsersAdd: preparedConfig.manageUsersAdd,
	    manageUsersDelete: preparedConfig.manageUsersDelete,
	    manageUi: preparedConfig.manageUi,
	    manageSettings: preparedConfig.manageSettings,
	    manageMessages: preparedConfig.manageMessages,
	    conferencePassword: preparedConfig.conferencePassword,
	    copilotMainRole: preparedConfig.copilotMainRole
	  };
	  Object.entries(result).forEach(([key, value]) => {
	    if (main_core.Type.isUndefined(value)) {
	      delete result[key];
	    }
	  });
	  return result;
	}
	function _addCollabToModel2(newDialogId, collabConfig) {
	  babelHelpers.classPrivateFieldLooseBase(this, _store$2)[_store$2].dispatch('chats/set', {
	    dialogId: newDialogId,
	    type: im_v2_const.ChatType.collab,
	    name: collabConfig.title
	  });
	}
	function _addChatToModel2(newDialogId, chatConfig) {
	  let chatType = chatConfig.searchable === 'Y' ? OPEN_CHAT : PRIVATE_CHAT;
	  if (main_core.Type.isStringFilled(chatConfig.entityType)) {
	    chatType = chatConfig.entityType.toLowerCase();
	  }
	  if (main_core.Type.isStringFilled(chatConfig.type)) {
	    chatType = chatConfig.type.toLowerCase();
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _store$2)[_store$2].dispatch('chats/set', {
	    dialogId: newDialogId,
	    type: chatType.toLowerCase(),
	    name: chatConfig.title,
	    userCounter: chatConfig.users.length,
	    role: im_v2_const.UserRole.owner,
	    permissions: {
	      manageUi: chatConfig.manageUi,
	      manageSettings: chatConfig.manageSettings,
	      manageUsersAdd: chatConfig.manageUsersAdd,
	      manageUsersDelete: chatConfig.manageUsersDelete,
	      manageMessages: chatConfig.manageMessages
	    }
	  });
	}
	function _sendAnalytics2(dialogId) {
	  im_v2_lib_analytics.Analytics.getInstance().ignoreNextChatOpen(dialogId);
	}

	var _store$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _prepareFields$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareFields");
	var _updateChatInModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateChatInModel");
	class UpdateService {
	  constructor() {
	    Object.defineProperty(this, _updateChatInModel, {
	      value: _updateChatInModel2
	    });
	    Object.defineProperty(this, _prepareFields$1, {
	      value: _prepareFields2$1
	    });
	    Object.defineProperty(this, _store$3, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$3)[_store$3] = im_v2_application_core.Core.getStore();
	  }
	  async prepareAvatar(avatarFile) {
	    if (!ui_uploader_core.isResizableImage(avatarFile)) {
	      // eslint-disable-next-line no-console
	      return Promise.reject(new Error('UpdateService: prepareAvatar: incorrect image'));
	    }
	    const MAX_AVATAR_SIZE = 180;
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
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatUpdateAvatar, {
	      data: {
	        id: chatId,
	        avatar: avatarInBase64
	      }
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('ChatService: changeAvatar error:', error);
	      throw new Error(error);
	    });
	  }
	  async updateChat(chatId, chatConfig) {
	    im_v2_lib_logger.Logger.warn(`ChatService: updateChat, chatId: ${chatId}`, chatConfig);
	    const preparedFields = await babelHelpers.classPrivateFieldLooseBase(this, _prepareFields$1)[_prepareFields$1](chatConfig);
	    const updateResult = await im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatUpdate, {
	      data: {
	        id: chatId,
	        fields: preparedFields
	      },
	      id: chatId
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('ChatService: updateChat error:', error);
	      throw new Error(error);
	    });
	    im_v2_lib_logger.Logger.warn('ChatService: updateChat result', updateResult);
	    const dialogId = `chat${chatId}`;
	    await babelHelpers.classPrivateFieldLooseBase(this, _updateChatInModel)[_updateChatInModel](dialogId, chatConfig);
	    return updateResult;
	  }
	  async updateCollab(dialogId, collabConfig) {
	    im_v2_lib_logger.Logger.warn(`ChatService: updateCollab, dialogId: ${dialogId}`, collabConfig);
	    const preparedFields = await babelHelpers.classPrivateFieldLooseBase(this, _prepareFields$1)[_prepareFields$1](collabConfig);
	    let payload = {
	      dialogId,
	      name: preparedFields.title,
	      description: preparedFields.description,
	      avatarId: preparedFields.avatar
	    };
	    if (collabConfig.groupSettings) {
	      const groupSettings = collabConfig.groupSettings;
	      payload = {
	        ...payload,
	        ownerId: groupSettings.ownerId,
	        addModeratorMembers: im_v2_lib_utils.Utils.user.prepareSelectorIds(groupSettings.addModeratorMembers),
	        deleteModeratorMembers: im_v2_lib_utils.Utils.user.prepareSelectorIds(groupSettings.deleteModeratorMembers),
	        permissions: groupSettings.permissions,
	        options: groupSettings.options
	      };
	    }
	    const updateResult = await im_v2_lib_rest.runAction(im_v2_const.RestMethod.socialnetworkCollabUpdate, {
	      data: payload
	    }).catch(([error]) => {
	      // eslint-disable-next-line no-console
	      console.error('ChatService: updateCollab error:', error);
	      throw error;
	    });
	    im_v2_lib_logger.Logger.warn('ChatService: updateCollab result', updateResult);
	    return updateResult;
	  }
	  async getMemberEntities(chatId) {
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatMemberEntitiesList, {
	      data: {
	        chatId
	      }
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('ChatService: getMemberEntities error:', error);
	      throw new Error(error);
	    });
	  }
	}
	async function _prepareFields2$1(chatConfig) {
	  const result = {
	    title: chatConfig.title,
	    description: chatConfig.description,
	    ownerId: chatConfig.ownerId,
	    searchable: chatConfig.isAvailableInSearch ? 'Y' : 'N',
	    manageUi: chatConfig.manageUi,
	    manageUsersAdd: chatConfig.manageUsersAdd,
	    manageUsersDelete: chatConfig.manageUsersDelete,
	    manageMessages: chatConfig.manageMessages,
	    addedMemberEntities: chatConfig.addedMemberEntities,
	    deletedMemberEntities: chatConfig.deletedMemberEntities,
	    addedManagers: chatConfig.addedManagers,
	    deletedManagers: chatConfig.deletedManagers
	  };
	  if (chatConfig.avatar) {
	    result.avatar = await im_v2_lib_utils.Utils.file.getBase64(chatConfig.avatar);
	  }
	  Object.entries(result).forEach(([key, value]) => {
	    if (main_core.Type.isUndefined(value)) {
	      delete result[key];
	    }
	  });
	  return result;
	}
	function _updateChatInModel2(dialogId, chatConfig) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$3)[_store$3].dispatch('chats/update', {
	    dialogId,
	    fields: {
	      name: chatConfig.title,
	      description: chatConfig.description,
	      ownerId: chatConfig.ownerId,
	      managerList: chatConfig.managers,
	      type: chatConfig.type,
	      role: im_v2_lib_roleManager.getChatRoleForUser(chatConfig),
	      permissions: {
	        manageUi: chatConfig.manageUi,
	        manageUsersAdd: chatConfig.manageUsersAdd,
	        manageUsersDelete: chatConfig.manageUsersDelete,
	        manageMessages: chatConfig.manageMessages
	      }
	    }
	  });
	}

	var _store$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _updateChatTitleInModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateChatTitleInModel");
	class RenameService {
	  constructor() {
	    Object.defineProperty(this, _updateChatTitleInModel, {
	      value: _updateChatTitleInModel2
	    });
	    Object.defineProperty(this, _store$4, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$4)[_store$4] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$1)[_restClient$1] = im_v2_application_core.Core.getRestClient();
	  }
	  renameChat(dialogId, newName) {
	    im_v2_lib_logger.Logger.warn('ChatService: renameChat', dialogId, newName);
	    if (newName === '') {
	      return Promise.resolve();
	    }
	    const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$4)[_store$4].getters['chats/get'](dialogId);
	    const oldName = dialog.name;
	    babelHelpers.classPrivateFieldLooseBase(this, _updateChatTitleInModel)[_updateChatTitleInModel](dialogId, newName);
	    return babelHelpers.classPrivateFieldLooseBase(this, _restClient$1)[_restClient$1].callMethod(im_v2_const.RestMethod.imChatUpdateTitle, {
	      dialog_id: dialogId,
	      title: newName
	    }).catch(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _updateChatTitleInModel)[_updateChatTitleInModel](dialogId, oldName);
	      throw new Error('Chat rename error');
	    });
	  }
	}
	function _updateChatTitleInModel2(dialogId, title) {
	  babelHelpers.classPrivateFieldLooseBase(this, _store$4)[_store$4].dispatch('chats/update', {
	    dialogId,
	    fields: {
	      name: title
	    }
	  });
	}

	var _store$5 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _sendMuteRequestDebounced = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendMuteRequestDebounced");
	var _sendMuteRequest = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendMuteRequest");
	class MuteService {
	  constructor() {
	    Object.defineProperty(this, _sendMuteRequest, {
	      value: _sendMuteRequest2
	    });
	    Object.defineProperty(this, _store$5, {
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
	    babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$2)[_restClient$2] = im_v2_application_core.Core.getRestClient();
	    const DEBOUNCE_TIME = 500;
	    babelHelpers.classPrivateFieldLooseBase(this, _sendMuteRequestDebounced)[_sendMuteRequestDebounced] = main_core.Runtime.debounce(babelHelpers.classPrivateFieldLooseBase(this, _sendMuteRequest)[_sendMuteRequest], DEBOUNCE_TIME);
	  }
	  muteChat(dialogId) {
	    im_v2_lib_logger.Logger.warn('ChatService: muteChat', dialogId);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('chats/mute', {
	      dialogId
	    });
	    const queryParams = {
	      dialog_id: dialogId,
	      action: 'Y'
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _sendMuteRequestDebounced)[_sendMuteRequestDebounced](queryParams);
	  }
	  unmuteChat(dialogId) {
	    im_v2_lib_logger.Logger.warn('ChatService: unmuteChat', dialogId);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('chats/unmute', {
	      dialogId
	    });
	    const queryParams = {
	      dialog_id: dialogId,
	      action: 'N'
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
	    // eslint-disable-next-line no-console
	    console.error(`Im.RecentList: error ${actionText} chat`, error);
	    const actionType = action === 'Y' ? 'chats/unmute' : 'chats/mute';
	    babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch(actionType, {
	      dialogId
	    });
	  });
	}

	var _store$6 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	class PinService {
	  constructor() {
	    Object.defineProperty(this, _store$6, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$3, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$6)[_store$6] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$3)[_restClient$3] = im_v2_application_core.Core.getRestClient();
	  }
	  pinChat(dialogId) {
	    im_v2_lib_logger.Logger.warn('PinService: pinChat', dialogId);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$6)[_store$6].dispatch('recent/pin', {
	      id: dialogId,
	      action: true
	    });
	    const queryParams = {
	      DIALOG_ID: dialogId,
	      ACTION: 'Y'
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$3)[_restClient$3].callMethod(im_v2_const.RestMethod.imRecentPin, queryParams).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('PinService: error pinning chat', error);
	      babelHelpers.classPrivateFieldLooseBase(this, _store$6)[_store$6].dispatch('recent/pin', {
	        id: dialogId,
	        action: false
	      });
	    });
	  }
	  unpinChat(dialogId) {
	    im_v2_lib_logger.Logger.warn('PinService: unpinChat', dialogId);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$6)[_store$6].dispatch('recent/pin', {
	      id: dialogId,
	      action: false
	    });
	    const queryParams = {
	      DIALOG_ID: dialogId,
	      ACTION: 'N'
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$3)[_restClient$3].callMethod(im_v2_const.RestMethod.imRecentPin, queryParams).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('PinService: error unpinning chat', error);
	      babelHelpers.classPrivateFieldLooseBase(this, _store$6)[_store$6].dispatch('recent/pin', {
	        id: dialogId,
	        action: true
	      });
	    });
	  }
	}

	const READ_TIMEOUT = 300;
	var _store$7 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _messagesToRead = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("messagesToRead");
	var _readMessagesForChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("readMessagesForChat");
	var _readMessageOnClient = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("readMessageOnClient");
	var _decreaseCommentCounter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("decreaseCommentCounter");
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
	    Object.defineProperty(this, _decreaseCommentCounter, {
	      value: _decreaseCommentCounter2
	    });
	    Object.defineProperty(this, _readMessageOnClient, {
	      value: _readMessageOnClient2
	    });
	    Object.defineProperty(this, _readMessagesForChat, {
	      value: _readMessagesForChat2
	    });
	    Object.defineProperty(this, _store$7, {
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
	    babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$4)[_restClient$4] = im_v2_application_core.Core.getRestClient();
	  }
	  readAll() {
	    im_v2_lib_logger.Logger.warn('ReadService: readAll');
	    babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('chats/clearCounters');
	    babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('recent/clearUnread');
	    return babelHelpers.classPrivateFieldLooseBase(this, _restClient$4)[_restClient$4].callMethod(im_v2_const.RestMethod.imV2ChatReadAll).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('ReadService: readAll error', error);
	    });
	  }
	  readDialog(dialogId) {
	    im_v2_lib_logger.Logger.warn('ReadService: readDialog', dialogId);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('recent/unread', {
	      id: dialogId,
	      action: false
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('chats/update', {
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
	    babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('recent/unread', {
	      id: dialogId,
	      action: true
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$4)[_restClient$4].callMethod(im_v2_const.RestMethod.imV2ChatUnread, {
	      dialogId
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('ReadService: error setting chat as unread', error);
	      babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('recent/unread', {
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
	  async readChatQueuedMessages(chatId) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _messagesToRead)[_messagesToRead][chatId]) {
	      return true;
	    }
	    clearTimeout(this.readTimeout);
	    return babelHelpers.classPrivateFieldLooseBase(this, _readMessagesForChat)[_readMessagesForChat](chatId, babelHelpers.classPrivateFieldLooseBase(this, _messagesToRead)[_messagesToRead][chatId]);
	  }
	  clearDialogMark(dialogId) {
	    im_v2_lib_logger.Logger.warn('ReadService: clear dialog mark', dialogId);
	    const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].getters['chats/get'](dialogId);
	    const recentItem = babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].getters['recent/get'](dialogId);
	    if (dialog.markedId === 0 && !(recentItem != null && recentItem.unread)) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('recent/unread', {
	      id: dialogId,
	      action: false
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('chats/update', {
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
	    return true;
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
	  return true;
	}
	function _readMessageOnClient2(chatId, messageIds) {
	  const maxMessageId = Math.max(...messageIds);
	  const dialog = babelHelpers.classPrivateFieldLooseBase(this, _getDialogByChatId)[_getDialogByChatId](chatId);
	  if (maxMessageId > dialog.lastReadId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('chats/update', {
	      dialogId: babelHelpers.classPrivateFieldLooseBase(this, _getDialogIdByChatId)[_getDialogIdByChatId](chatId),
	      fields: {
	        lastId: maxMessageId
	      }
	    });
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('messages/readMessages', {
	    chatId,
	    messageIds
	  });
	}
	function _decreaseCommentCounter2(chatId, readMessagesCount) {
	  const chat = babelHelpers.classPrivateFieldLooseBase(this, _getDialogByChatId)[_getDialogByChatId](chatId);
	  let newCounter = chat.counter - readMessagesCount;
	  if (newCounter < 0) {
	    newCounter = 0;
	  }
	  const counters = {
	    [chat.parentChatId]: {
	      [chatId]: newCounter
	    }
	  };
	  return im_v2_application_core.Core.getStore().dispatch('counters/setCommentCounters', counters);
	}
	function _decreaseChatCounter2(chatId, readMessagesCount) {
	  const chat = babelHelpers.classPrivateFieldLooseBase(this, _getDialogByChatId)[_getDialogByChatId](chatId);
	  if (chat.type === im_v2_const.ChatType.comment) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _decreaseCommentCounter)[_decreaseCommentCounter](chatId, readMessagesCount);
	  }
	  let newCounter = chat.counter - readMessagesCount;
	  if (newCounter < 0) {
	    newCounter = 0;
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('chats/update', {
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
	  if (!readResult) {
	    return;
	  }
	  const {
	    chatId,
	    counter
	  } = readResult;
	  const dialog = babelHelpers.classPrivateFieldLooseBase(this, _getDialogByChatId)[_getDialogByChatId](chatId);
	  if (dialog.counter > counter) {
	    im_v2_lib_logger.Logger.warn('ReadService: counter from server is lower than local one', dialog.counter, counter);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('chats/update', {
	      dialogId: dialog.dialogId,
	      fields: {
	        counter
	      }
	    });
	  }
	}
	function _getDialogIdByChatId2(chatId) {
	  const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].getters['chats/getByChatId'](chatId);
	  if (!dialog) {
	    return 0;
	  }
	  return dialog.dialogId;
	}
	function _getDialogByChatId2(chatId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].getters['chats/getByChatId'](chatId);
	}

	const DeleteUserErrorCode = {
	  userInvitedFromStructure: 'USER_INVITED_FROM_STRUCTURE',
	  userNotFound: 'USER_NOT_FOUND'
	};
	var _store$8 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$5 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _onChatLeave = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onChatLeave");
	var _onChatKickError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onChatKickError");
	var _onChatLeaveError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onChatLeaveError");
	var _showNotification$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showNotification");
	var _getErrorCode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getErrorCode");
	class UserService {
	  constructor() {
	    Object.defineProperty(this, _getErrorCode, {
	      value: _getErrorCode2
	    });
	    Object.defineProperty(this, _showNotification$1, {
	      value: _showNotification2$1
	    });
	    Object.defineProperty(this, _onChatLeaveError, {
	      value: _onChatLeaveError2
	    });
	    Object.defineProperty(this, _onChatKickError, {
	      value: _onChatKickError2
	    });
	    Object.defineProperty(this, _onChatLeave, {
	      value: _onChatLeave2
	    });
	    Object.defineProperty(this, _store$8, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$5, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$8)[_store$8] = im_v2_application_core.Core.getStore();
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
	  async kickUserFromChat(dialogId, userId) {
	    const queryParams = {
	      dialogId,
	      userId
	    };
	    try {
	      await babelHelpers.classPrivateFieldLooseBase(this, _restClient$5)[_restClient$5].callMethod(im_v2_const.RestMethod.imV2ChatDeleteUser, queryParams);
	    } catch (error) {
	      babelHelpers.classPrivateFieldLooseBase(this, _onChatKickError)[_onChatKickError](error);
	    }
	  }
	  async kickUserFromCollab(dialogId, userId) {
	    const USER_ENTITY_ID = 'user';
	    const members = [[USER_ENTITY_ID, userId]];
	    const payload = {
	      data: {
	        dialogId,
	        members
	      }
	    };
	    try {
	      await im_v2_lib_rest.runAction(im_v2_const.RestMethod.socialnetworkMemberDelete, payload);
	    } catch (errors) {
	      console.error('UserService: error kicking from collab', errors);
	      babelHelpers.classPrivateFieldLooseBase(this, _showNotification$1)[_showNotification$1](main_core.Loc.getMessage('IM_MESSAGE_SERVICE_KICK_COLLAB_DEFAULT_ERROR'));
	    }
	  }
	  async leaveChat(dialogId) {
	    const queryParams = {
	      dialogId,
	      userId: im_v2_application_core.Core.getUserId()
	    };
	    try {
	      await babelHelpers.classPrivateFieldLooseBase(this, _restClient$5)[_restClient$5].callMethod(im_v2_const.RestMethod.imV2ChatDeleteUser, queryParams);
	      babelHelpers.classPrivateFieldLooseBase(this, _onChatLeave)[_onChatLeave](dialogId);
	    } catch (error) {
	      babelHelpers.classPrivateFieldLooseBase(this, _onChatLeaveError)[_onChatLeaveError](error);
	    }
	  }
	  async leaveCollab(dialogId) {
	    const payload = {
	      data: {
	        dialogId
	      }
	    };
	    try {
	      await im_v2_lib_rest.runAction(im_v2_const.RestMethod.socialnetworkMemberLeave, payload);
	      babelHelpers.classPrivateFieldLooseBase(this, _onChatLeave)[_onChatLeave](dialogId);
	    } catch (errors) {
	      console.error('UserService: leave collab error', errors);
	      babelHelpers.classPrivateFieldLooseBase(this, _showNotification$1)[_showNotification$1](main_core.Loc.getMessage('IM_MESSAGE_SERVICE_LEAVE_COLLAB_DEFAULT_ERROR'));
	    }
	  }
	  joinChat(dialogId) {
	    im_v2_lib_logger.Logger.warn(`UserService: join chat ${dialogId}`);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$8)[_store$8].dispatch('chats/update', {
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
	  addManager(dialogId, userId) {
	    im_v2_lib_logger.Logger.warn(`UserService: add manager ${userId} to ${dialogId}`);
	    const {
	      managerList
	    } = babelHelpers.classPrivateFieldLooseBase(this, _store$8)[_store$8].getters['chats/get'](dialogId);
	    if (managerList.includes(userId)) {
	      return;
	    }
	    const newManagerList = [...managerList, userId];
	    babelHelpers.classPrivateFieldLooseBase(this, _store$8)[_store$8].dispatch('chats/update', {
	      dialogId,
	      fields: {
	        managerList: newManagerList
	      }
	    });
	    const payload = {
	      data: {
	        dialogId,
	        userIds: [userId]
	      }
	    };
	    im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatAddManagers, payload).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('UserService: add manager error', error);
	    });
	  }
	  removeManager(dialogId, userId) {
	    im_v2_lib_logger.Logger.warn(`UserService: remove manager ${userId} from ${dialogId}`);
	    const {
	      managerList
	    } = babelHelpers.classPrivateFieldLooseBase(this, _store$8)[_store$8].getters['chats/get'](dialogId);
	    if (!managerList.includes(userId)) {
	      return;
	    }
	    const newManagerList = managerList.filter(managerId => managerId !== userId);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$8)[_store$8].dispatch('chats/update', {
	      dialogId,
	      fields: {
	        managerList: newManagerList
	      }
	    });
	    const payload = {
	      data: {
	        dialogId,
	        userIds: [userId]
	      }
	    };
	    im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatDeleteManagers, payload).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('UserService: remove manager error', error);
	    });
	  }
	}
	function _onChatLeave2(dialogId) {
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$8)[_store$8].dispatch('chats/update', {
	    dialogId,
	    fields: {
	      inited: false
	    }
	  });
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$8)[_store$8].dispatch('recent/delete', {
	    id: dialogId
	  });
	  const chatIsOpened = babelHelpers.classPrivateFieldLooseBase(this, _store$8)[_store$8].getters['application/isChatOpen'](dialogId);
	  if (chatIsOpened) {
	    im_v2_lib_layout.LayoutManager.getInstance().clearCurrentLayoutEntityId();
	    void im_v2_lib_layout.LayoutManager.getInstance().deleteLastOpenedElementById(dialogId);
	  }
	}
	function _onChatKickError2(error) {
	  var _NotificationTextByEr;
	  // eslint-disable-next-line no-console
	  console.error('UserService: error kicking from chat', error);
	  const NotificationTextByErrorCode = {
	    [DeleteUserErrorCode.userInvitedFromStructure]: main_core.Loc.getMessage('IM_MESSAGE_SERVICE_KICK_CHAT_STRUCTURE_ERROR_MSGVER_1'),
	    default: main_core.Loc.getMessage('IM_MESSAGE_SERVICE_KICK_CHAT_DEFAULT_ERROR')
	  };
	  const errorCode = babelHelpers.classPrivateFieldLooseBase(this, _getErrorCode)[_getErrorCode](error);
	  const notificationText = (_NotificationTextByEr = NotificationTextByErrorCode[errorCode]) != null ? _NotificationTextByEr : NotificationTextByErrorCode.default;
	  babelHelpers.classPrivateFieldLooseBase(this, _showNotification$1)[_showNotification$1](notificationText);
	}
	function _onChatLeaveError2(error) {
	  var _NotificationTextByEr2;
	  // eslint-disable-next-line no-console
	  console.error('UserService: error leaving chat', error);
	  const NotificationTextByErrorCode = {
	    [DeleteUserErrorCode.userInvitedFromStructure]: main_core.Loc.getMessage('IM_MESSAGE_SERVICE_LEAVE_CHAT_STRUCTURE_ERROR'),
	    default: main_core.Loc.getMessage('IM_MESSAGE_SERVICE_LEAVE_CHAT_DEFAULT_ERROR')
	  };
	  const errorCode = babelHelpers.classPrivateFieldLooseBase(this, _getErrorCode)[_getErrorCode](error);
	  const notificationText = (_NotificationTextByEr2 = NotificationTextByErrorCode[errorCode]) != null ? _NotificationTextByEr2 : NotificationTextByErrorCode.default;
	  babelHelpers.classPrivateFieldLooseBase(this, _showNotification$1)[_showNotification$1](notificationText);
	}
	function _showNotification2$1(text) {
	  BX.UI.Notification.Center.notify({
	    content: text,
	    autoHideDelay: 5000
	  });
	}
	function _getErrorCode2(error) {
	  const {
	    answer: {
	      error: errorCode
	    }
	  } = error;
	  return errorCode;
	}

	var _loadService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadService");
	var _createService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createService");
	var _updateService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateService");
	var _renameService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renameService");
	var _muteService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("muteService");
	var _pinService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pinService");
	var _readService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("readService");
	var _userService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userService");
	var _deleteService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("deleteService");
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
	    Object.defineProperty(this, _deleteService, {
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
	  loadComments(postId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService].loadComments(postId);
	  }
	  loadCommentInfo(channelDialogId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService].loadCommentInfo(channelDialogId);
	  }
	  prepareDialogId(dialogId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService].prepareDialogId(dialogId);
	  }
	  resetChat(dialogId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService].resetChat(dialogId);
	  }
	  // endregion 'load'

	  // region 'create'
	  createChat(chatConfig) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _createService)[_createService].createChat(chatConfig);
	  }
	  createCollab(collabConfig) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _createService)[_createService].createCollab(collabConfig);
	  }
	  // endregion 'create'

	  // region 'update'
	  prepareAvatar(avatarFile) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _updateService)[_updateService].prepareAvatar(avatarFile);
	  }
	  changeAvatar(chatId, avatarFile) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _updateService)[_updateService].changeAvatar(chatId, avatarFile);
	  }
	  updateChat(chatId, chatConfig) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _updateService)[_updateService].updateChat(chatId, chatConfig);
	  }
	  updateCollab(dialogId, collabConfig) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _updateService)[_updateService].updateCollab(dialogId, collabConfig);
	  }
	  getMemberEntities(chatId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _updateService)[_updateService].getMemberEntities(chatId);
	  }
	  // endregion 'update'

	  // region 'delete'
	  deleteChat(dialogId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _deleteService)[_deleteService].deleteChat(dialogId);
	  }
	  deleteCollab(dialogId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _deleteService)[_deleteService].deleteCollab(dialogId);
	  }
	  // endregion 'delete'

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
	  readChatQueuedMessages(chatId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _readService)[_readService].readChatQueuedMessages(chatId);
	  }
	  clearDialogMark(dialogId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _readService)[_readService].clearDialogMark(dialogId);
	  }
	  // endregion 'read'

	  // region 'user'
	  leaveChat(dialogId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _userService)[_userService].leaveChat(dialogId);
	  }
	  leaveCollab(dialogId) {
	    void babelHelpers.classPrivateFieldLooseBase(this, _userService)[_userService].leaveCollab(dialogId);
	  }
	  kickUserFromChat(dialogId, userId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _userService)[_userService].kickUserFromChat(dialogId, userId);
	  }
	  kickUserFromCollab(dialogId, userId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _userService)[_userService].kickUserFromCollab(dialogId, userId);
	  }
	  addToChat(addConfig) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _userService)[_userService].addToChat(addConfig);
	  }
	  joinChat(dialogId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _userService)[_userService].joinChat(dialogId);
	  }
	  addManager(dialogId, userId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _userService)[_userService].addManager(dialogId, userId);
	  }
	  removeManager(dialogId, userId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _userService)[_userService].removeManager(dialogId, userId);
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
	  babelHelpers.classPrivateFieldLooseBase(this, _deleteService)[_deleteService] = new DeleteService();
	}

	var _store$9 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _chatId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatId");
	var _userManager = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userManager");
	var _preparedHistoryMessages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("preparedHistoryMessages");
	var _preparedUnreadMessages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("preparedUnreadMessages");
	var _isLoading = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isLoading");
	var _prepareInitialMessages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareInitialMessages");
	var _handleLoadedMessages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleLoadedMessages");
	var _updateModels$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateModels");
	var _setDialogInited = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setDialogInited");
	var _prepareTariffRestrictions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareTariffRestrictions");
	var _getDialog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialog");
	var _handleLoadContextError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleLoadContextError");
	var _showNotification$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showNotification");
	var _sendAnalytics$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendAnalytics");
	class LoadService$1 {
	  constructor(chatId) {
	    Object.defineProperty(this, _sendAnalytics$1, {
	      value: _sendAnalytics2$1
	    });
	    Object.defineProperty(this, _showNotification$2, {
	      value: _showNotification2$2
	    });
	    Object.defineProperty(this, _handleLoadContextError, {
	      value: _handleLoadContextError2
	    });
	    Object.defineProperty(this, _getDialog, {
	      value: _getDialog2
	    });
	    Object.defineProperty(this, _prepareTariffRestrictions, {
	      value: _prepareTariffRestrictions2
	    });
	    Object.defineProperty(this, _setDialogInited, {
	      value: _setDialogInited2
	    });
	    Object.defineProperty(this, _updateModels$2, {
	      value: _updateModels2$2
	    });
	    Object.defineProperty(this, _handleLoadedMessages, {
	      value: _handleLoadedMessages2
	    });
	    Object.defineProperty(this, _prepareInitialMessages, {
	      value: _prepareInitialMessages2
	    });
	    Object.defineProperty(this, _store$9, {
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
	    babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _userManager)[_userManager] = new im_v2_lib_user.UserManager();
	    babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId] = chatId;
	  }
	  async loadUnread() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] || !babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().hasNextPage) {
	      return Promise.resolve(false);
	    }
	    im_v2_lib_logger.Logger.warn('MessageService: loadUnread');
	    const lastUnreadMessageId = babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].getters['messages/getLastId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId]);
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
	    const result = await im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatMessageTail, {
	      data: query
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('MessageService: loadUnread error:', error);
	      babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	    });
	    im_v2_lib_logger.Logger.warn('MessageService: loadUnread result', result);
	    babelHelpers.classPrivateFieldLooseBase(this, _preparedUnreadMessages)[_preparedUnreadMessages] = result.messages;
	    const rawData = {
	      ...result,
	      tariffRestrictions: babelHelpers.classPrivateFieldLooseBase(this, _prepareTariffRestrictions)[_prepareTariffRestrictions](result.tariffRestrictions)
	    };
	    await babelHelpers.classPrivateFieldLooseBase(this, _updateModels$2)[_updateModels$2](rawData);
	    babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	    return Promise.resolve();
	  }
	  async loadHistory() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] || !babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().hasPrevPage) {
	      return Promise.resolve(false);
	    }
	    im_v2_lib_logger.Logger.warn('MessageService: loadHistory');
	    const lastHistoryMessageId = babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].getters['messages/getFirstId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId]);
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
	    const result = await im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatMessageTail, {
	      data: query
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('MessageService: loadHistory error:', error);
	      babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	    });
	    im_v2_lib_logger.Logger.warn('MessageService: loadHistory result', result);
	    babelHelpers.classPrivateFieldLooseBase(this, _preparedHistoryMessages)[_preparedHistoryMessages] = result.messages;
	    const hasPrevPage = result.hasNextPage;
	    const rawData = {
	      ...result,
	      hasPrevPage,
	      hasNextPage: null
	    };
	    await babelHelpers.classPrivateFieldLooseBase(this, _updateModels$2)[_updateModels$2](rawData);
	    babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	    return Promise.resolve();
	  }
	  hasPreparedHistoryMessages() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _preparedHistoryMessages)[_preparedHistoryMessages].length > 0;
	  }
	  drawPreparedHistoryMessages() {
	    if (!this.hasPreparedHistoryMessages()) {
	      return Promise.resolve();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].dispatch('messages/setChatCollection', {
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
	    return babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].dispatch('messages/setChatCollection', {
	      messages: babelHelpers.classPrivateFieldLooseBase(this, _preparedUnreadMessages)[_preparedUnreadMessages]
	    }).then(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _preparedUnreadMessages)[_preparedUnreadMessages] = [];
	      return true;
	    });
	  }
	  async loadFirstPage() {
	    im_v2_lib_logger.Logger.warn('MessageService: loadFirstPage for: ', babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId]);
	    babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = true;
	    const payload = {
	      data: {
	        chatId: babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId],
	        limit: LoadService$1.MESSAGE_REQUEST_LIMIT,
	        order: {
	          id: 'ASC'
	        }
	      }
	    };
	    const restResult = await im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatMessageTail, payload).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('MessageService: loadFirstPage error:', error);
	      babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	      throw new Error(error);
	    });
	    im_v2_lib_logger.Logger.warn('MessageService: loadFirstPage result', restResult);
	    await babelHelpers.classPrivateFieldLooseBase(this, _handleLoadedMessages)[_handleLoadedMessages](restResult);
	    await babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].dispatch('chats/update', {
	      dialogId: babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().dialogId,
	      fields: {
	        hasPrevPage: false,
	        hasNextPage: restResult.hasNextPage
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
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
	      babelHelpers.classPrivateFieldLooseBase(this, _sendAnalytics$1)[_sendAnalytics$1](error);
	      babelHelpers.classPrivateFieldLooseBase(this, _handleLoadContextError)[_handleLoadContextError](error);
	      // eslint-disable-next-line no-console
	      console.error('MessageService: loadContext error:', error);
	      throw new Error(error);
	    }).finally(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	    });
	  }
	  async loadContextByChatId(chatId) {
	    const queryParams = {
	      data: {
	        commentChatId: chatId
	      }
	    };
	    const result = await im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatMessageGetContext, queryParams).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('MessageService: loadHistory error:', error);
	    });
	    const commentInfo = result.commentInfo;
	    const targetCommentInfo = commentInfo.find(item => {
	      return item.chatId === chatId;
	    });
	    const targetMessageId = targetCommentInfo == null ? void 0 : targetCommentInfo.messageId;
	    im_v2_lib_logger.Logger.warn('MessageService: loadContextByChatId result', result);
	    void babelHelpers.classPrivateFieldLooseBase(this, _handleLoadedMessages)[_handleLoadedMessages](result);
	    return targetMessageId;
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
	      return this.loadContext(targetMessageId).finally(() => {
	        babelHelpers.classPrivateFieldLooseBase(this, _setDialogInited)[_setDialogInited](true, wasInitedBefore);
	      });
	    }
	    return this.loadInitialMessages().finally(() => {
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
	  const messagesCollection = babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].getters['messages/getByChatId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId]);
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
	  const messagesPromise = babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].dispatch('messages/setChatCollection', {
	    messages,
	    clearCollection: true
	  });
	  const updateModelsPromise = babelHelpers.classPrivateFieldLooseBase(this, _updateModels$2)[_updateModels$2](restResult);
	  return Promise.all([messagesPromise, updateModelsPromise]);
	}
	function _updateModels2$2(rawData) {
	  const {
	    files,
	    users,
	    usersShort,
	    reactions,
	    hasPrevPage,
	    hasNextPage,
	    additionalMessages,
	    commentInfo,
	    copilot,
	    tariffRestrictions
	  } = rawData;
	  const dialogPromise = babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].dispatch('chats/update', {
	    dialogId: babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().dialogId,
	    fields: {
	      hasPrevPage,
	      hasNextPage,
	      tariffRestrictions
	    }
	  });
	  const usersPromise = Promise.all([babelHelpers.classPrivateFieldLooseBase(this, _userManager)[_userManager].setUsersToModel(users), babelHelpers.classPrivateFieldLooseBase(this, _userManager)[_userManager].addUsersToModel(usersShort)]);
	  const filesPromise = babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].dispatch('files/set', files);
	  const reactionsPromise = babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].dispatch('messages/reactions/set', reactions);
	  const additionalMessagesPromise = babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].dispatch('messages/store', additionalMessages);
	  const commentInfoPromise = babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].dispatch('messages/comments/set', commentInfo);
	  const copilotManager = new im_v2_lib_copilot.CopilotManager();
	  const copilotPromise = copilotManager.handleChatLoadResponse(copilot);
	  return Promise.all([dialogPromise, filesPromise, usersPromise, reactionsPromise, additionalMessagesPromise, commentInfoPromise, copilotPromise]);
	}
	function _setDialogInited2(flag, wasInitedBefore = true) {
	  const fields = {
	    inited: flag,
	    loading: !flag
	  };
	  if (flag === true && !wasInitedBefore) {
	    delete fields.inited;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].dispatch('chats/update', {
	    dialogId: babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().dialogId,
	    fields
	  });
	}
	function _prepareTariffRestrictions2(restrictions) {
	  const dialogId = babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().dialogId;
	  const chat = babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].getters['chats/get'](dialogId);
	  if (!chat) {
	    return restrictions;
	  }
	  const {
	    tariffRestrictions: {
	      isHistoryLimitExceeded
	    }
	  } = chat;
	  if (isHistoryLimitExceeded === true) {
	    return {
	      ...restrictions,
	      isHistoryLimitExceeded: true
	    };
	  }
	  return restrictions;
	}
	function _getDialog2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].getters['chats/getByChatId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId]);
	}
	function _handleLoadContextError2(error) {
	  if (error.code !== im_v2_lib_access.AccessErrorCode.messageNotFound) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _showNotification$2)[_showNotification$2](main_core.Loc.getMessage('IM_CONTENT_CHAT_CONTEXT_MESSAGE_NOT_FOUND'));
	}
	function _showNotification2$2(text) {
	  BX.UI.Notification.Center.notify({
	    content: text
	  });
	}
	function _sendAnalytics2$1(error) {
	  if (error.code !== im_v2_lib_access.AccessErrorCode.messageNotFound) {
	    return;
	  }
	  const chat = babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]();
	  const dialogId = chat.dialogId;
	  im_v2_lib_analytics.Analytics.getInstance().messageDelete.onNotFoundNotification({
	    dialogId
	  });
	}
	LoadService$1.MESSAGE_REQUEST_LIMIT = 25;

	var _store$a = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$6 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	class PinService$1 {
	  constructor() {
	    Object.defineProperty(this, _store$a, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$6, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$a)[_store$a] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$6)[_restClient$6] = im_v2_application_core.Core.getRestClient();
	  }
	  pinMessage(chatId, messageId) {
	    im_v2_lib_logger.Logger.warn(`Dialog: PinManager: pin message ${messageId}`);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$a)[_store$a].dispatch('messages/pin/add', {
	      chatId,
	      messageId
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$6)[_restClient$6].callMethod(im_v2_const.RestMethod.imV2ChatMessagePin, {
	      id: messageId
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('Dialog: PinManager: error pinning message', error);
	      babelHelpers.classPrivateFieldLooseBase(this, _store$a)[_store$a].dispatch('messages/pin/delete', {
	        chatId,
	        messageId
	      });
	    });
	  }
	  unpinMessage(chatId, messageId) {
	    im_v2_lib_logger.Logger.warn(`Dialog: PinManager: unpin message ${messageId}`);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$a)[_store$a].dispatch('messages/pin/delete', {
	      chatId,
	      messageId
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$6)[_restClient$6].callMethod(im_v2_const.RestMethod.imV2ChatMessageUnpin, {
	      id: messageId
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('Dialog: PinManager: error unpinning message', error);
	      babelHelpers.classPrivateFieldLooseBase(this, _store$a)[_store$a].dispatch('messages/pin/add', {
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

	var _store$b = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _chatId$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatId");
	var _shallowMessageDelete = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("shallowMessageDelete");
	var _canDeleteCompletely = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canDeleteCompletely");
	var _completeMessageDelete = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("completeMessageDelete");
	var _updateRecentForCompleteDelete = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateRecentForCompleteDelete");
	var _updateChatForCompleteDelete = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateChatForCompleteDelete");
	var _deleteMessageOnServer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("deleteMessageOnServer");
	var _deleteTemporaryMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("deleteTemporaryMessage");
	var _getPreviousMessageId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPreviousMessageId");
	var _sendDeleteEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendDeleteEvent");
	var _getChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getChat");
	class DeleteService$1 {
	  constructor(chatId) {
	    Object.defineProperty(this, _getChat, {
	      value: _getChat2
	    });
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
	    Object.defineProperty(this, _canDeleteCompletely, {
	      value: _canDeleteCompletely2
	    });
	    Object.defineProperty(this, _shallowMessageDelete, {
	      value: _shallowMessageDelete2
	    });
	    Object.defineProperty(this, _store$b, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _chatId$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _chatId$1)[_chatId$1] = chatId;
	    babelHelpers.classPrivateFieldLooseBase(this, _store$b)[_store$b] = im_v2_application_core.Core.getStore();
	  }
	  async deleteMessage(messageId) {
	    im_v2_lib_logger.Logger.warn('MessageService: deleteMessage', messageId);
	    if (im_v2_lib_utils.Utils.text.isUuidV4(messageId)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _deleteTemporaryMessage)[_deleteTemporaryMessage](messageId);
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _sendDeleteEvent)[_sendDeleteEvent](messageId);
	    const message = babelHelpers.classPrivateFieldLooseBase(this, _store$b)[_store$b].getters['messages/getById'](messageId);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _canDeleteCompletely)[_canDeleteCompletely](message)) {
	      void babelHelpers.classPrivateFieldLooseBase(this, _completeMessageDelete)[_completeMessageDelete](message);
	      return;
	    }
	    void babelHelpers.classPrivateFieldLooseBase(this, _shallowMessageDelete)[_shallowMessageDelete](message);
	  }
	}
	function _shallowMessageDelete2(message) {
	  babelHelpers.classPrivateFieldLooseBase(this, _store$b)[_store$b].dispatch('messages/update', {
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
	function _canDeleteCompletely2(message) {
	  const alwaysCompleteDeleteChats = [im_v2_const.ChatType.channel, im_v2_const.ChatType.openChannel, im_v2_const.ChatType.generalChannel];
	  const neverCompleteDeleteChats = [im_v2_const.ChatType.comment, im_v2_const.ChatType.lines];
	  const chat = babelHelpers.classPrivateFieldLooseBase(this, _getChat)[_getChat]();
	  if (alwaysCompleteDeleteChats.includes(chat.type)) {
	    return true;
	  }
	  if (neverCompleteDeleteChats.includes(chat.type)) {
	    return false;
	  }
	  return !message.viewedByOthers;
	}
	function _completeMessageDelete2(message) {
	  const chat = babelHelpers.classPrivateFieldLooseBase(this, _getChat)[_getChat]();
	  if (message.id === chat.lastMessageId) {
	    const newLastId = babelHelpers.classPrivateFieldLooseBase(this, _getPreviousMessageId)[_getPreviousMessageId](message.id);
	    babelHelpers.classPrivateFieldLooseBase(this, _updateRecentForCompleteDelete)[_updateRecentForCompleteDelete](newLastId);
	    babelHelpers.classPrivateFieldLooseBase(this, _updateChatForCompleteDelete)[_updateChatForCompleteDelete](newLastId);
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _store$b)[_store$b].dispatch('messages/delete', {
	    id: message.id
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _deleteMessageOnServer)[_deleteMessageOnServer](message.id);
	}
	function _updateRecentForCompleteDelete2(newLastId) {
	  const chat = babelHelpers.classPrivateFieldLooseBase(this, _getChat)[_getChat]();
	  if (!newLastId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _store$b)[_store$b].dispatch('recent/delete', {
	      id: chat.dialogId
	    });
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _store$b)[_store$b].dispatch('recent/update', {
	    id: chat.dialogId,
	    fields: {
	      messageId: newLastId
	    }
	  });
	}
	function _updateChatForCompleteDelete2(newLastId) {
	  const chat = babelHelpers.classPrivateFieldLooseBase(this, _getChat)[_getChat]();
	  babelHelpers.classPrivateFieldLooseBase(this, _store$b)[_store$b].dispatch('chats/update', {
	    dialogId: chat.dialogId,
	    fields: {
	      lastMessageId: newLastId,
	      lastId: newLastId
	    }
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _store$b)[_store$b].dispatch('chats/clearLastMessageViews', {
	    dialogId: chat.dialogId
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
	  const chat = babelHelpers.classPrivateFieldLooseBase(this, _getChat)[_getChat]();
	  const recentItem = babelHelpers.classPrivateFieldLooseBase(this, _store$b)[_store$b].getters['recent/get'](chat.dialogId);
	  if (recentItem.messageId === messageId) {
	    const newLastId = babelHelpers.classPrivateFieldLooseBase(this, _getPreviousMessageId)[_getPreviousMessageId](messageId);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$b)[_store$b].dispatch('recent/update', {
	      id: chat.dialogId,
	      fields: {
	        messageId: newLastId
	      }
	    });
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _store$b)[_store$b].dispatch('messages/delete', {
	    id: messageId
	  });
	}
	function _getPreviousMessageId2(messageId) {
	  var _previousMessage$id;
	  const previousMessage = babelHelpers.classPrivateFieldLooseBase(this, _store$b)[_store$b].getters['messages/getPreviousMessage']({
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
	function _getChat2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$b)[_store$b].getters['chats/getByChatId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId$1)[_chatId$1]);
	}

	var _chatId$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatId");
	var _store$c = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$7 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	class MarkService {
	  constructor(chatId) {
	    Object.defineProperty(this, _chatId$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _store$c, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$7, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _chatId$2)[_chatId$2] = chatId;
	    babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$7)[_restClient$7] = im_v2_application_core.Core.getRestClient();
	  }
	  markMessage(messageId) {
	    im_v2_lib_logger.Logger.warn('MessageService: markMessage', messageId);
	    const {
	      dialogId
	    } = babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c].getters['chats/getByChatId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId$2)[_chatId$2]);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c].dispatch('recent/unread', {
	      id: dialogId,
	      action: true
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c].dispatch('chats/update', {
	      dialogId,
	      fields: {
	        markedId: messageId
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$7)[_restClient$7].callMethod(im_v2_const.RestMethod.imV2ChatMessageMark, {
	      dialogId,
	      id: messageId
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('MessageService: error marking message', error);
	    });
	  }
	}

	var _chatId$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatId");
	var _store$d = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$8 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	class FavoriteService {
	  constructor(chatId) {
	    Object.defineProperty(this, _chatId$3, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _store$d, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$8, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _chatId$3)[_chatId$3] = chatId;
	    babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$8)[_restClient$8] = im_v2_application_core.Core.getRestClient();
	  }
	  addMessageToFavorite(messageId) {
	    im_v2_lib_logger.Logger.warn('MessageService: addMessageToFavorite', messageId);
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$8)[_restClient$8].callMethod(im_v2_const.RestMethod.imChatFavoriteAdd, {
	      MESSAGE_ID: messageId
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('MessageService: error adding message to favorite', error);
	    });
	    BX.UI.Notification.Center.notify({
	      content: main_core.Loc.getMessage('IM_MESSAGE_SERVICE_ADD_MESSAGE_TO_FAVORITE_SUCCESS')
	    });
	  }
	  removeMessageFromFavorite(messageId) {
	    im_v2_lib_logger.Logger.warn('MessageService: removeMessageFromFavorite', messageId);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d].dispatch('sidebar/favorites/deleteByMessageId', {
	      chatId: babelHelpers.classPrivateFieldLooseBase(this, _chatId$3)[_chatId$3],
	      messageId
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$8)[_restClient$8].callMethod(im_v2_const.RestMethod.imChatFavoriteDelete, {
	      MESSAGE_ID: messageId
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('MessageService: error removing message from favorite', error);
	    });
	  }
	}

	var _loadService$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadService");
	var _pinService$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pinService");
	var _editService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("editService");
	var _deleteService$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("deleteService");
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
	    Object.defineProperty(this, _deleteService$1, {
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
	  loadContextByChatId(chatId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService$1)[_loadService$1].loadContextByChatId(chatId);
	  }
	  loadFirstPage() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService$1)[_loadService$1].loadFirstPage();
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
	    babelHelpers.classPrivateFieldLooseBase(this, _deleteService$1)[_deleteService$1].deleteMessage(messageId);
	  }
	  // endregion 'delete'
	}
	function _initServices2$1(chatId) {
	  babelHelpers.classPrivateFieldLooseBase(this, _loadService$1)[_loadService$1] = new LoadService$1(chatId);
	  babelHelpers.classPrivateFieldLooseBase(this, _editService)[_editService] = new EditService();
	  babelHelpers.classPrivateFieldLooseBase(this, _deleteService$1)[_deleteService$1] = new DeleteService$1(chatId);
	  babelHelpers.classPrivateFieldLooseBase(this, _pinService$1)[_pinService$1] = new PinService$1();
	  babelHelpers.classPrivateFieldLooseBase(this, _markService)[_markService] = new MarkService(chatId);
	  babelHelpers.classPrivateFieldLooseBase(this, _favoriteService)[_favoriteService] = new FavoriteService(chatId);
	}

	var _store$e = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _addLoadingMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addLoadingMessage");
	var _processMessageSending = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("processMessageSending");
	var _handleAddingMessageToModels = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleAddingMessageToModels");
	var _sendAndProcessMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendAndProcessMessage");
	var _prepareMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareMessage");
	var _prepareMessageWithFiles = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareMessageWithFiles");
	var _preparePrompt = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("preparePrompt");
	var _handlePagination = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handlePagination");
	var _addMessageToModels = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addMessageToModels");
	var _addMessageToRecent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addMessageToRecent");
	var _sendMessageToServer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendMessageToServer");
	var _updateModels$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateModels");
	var _updateMessageError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateMessageError");
	var _removeMessageError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeMessageError");
	var _sendScrollEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendScrollEvent");
	var _getDialog$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialog");
	var _getDialogByChatId$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialogByChatId");
	var _needToSetAsViewed = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("needToSetAsViewed");
	var _handleForwardMessageResponse = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleForwardMessageResponse");
	var _handleForwardMessageError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleForwardMessageError");
	var _prepareForwardMessages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareForwardMessages");
	var _prepareForwardParams = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareForwardParams");
	var _prepareSendForwardRequest = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareSendForwardRequest");
	var _addForwardsToModels = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addForwardsToModels");
	var _getForwardUuidMap = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getForwardUuidMap");
	var _buildForwardContextId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("buildForwardContextId");
	var _logSendErrors = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("logSendErrors");
	var _clearLastMessageViews = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clearLastMessageViews");
	var _sendForwardRequest = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendForwardRequest");
	class SendingService$$1 {
	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }
	  constructor() {
	    Object.defineProperty(this, _sendForwardRequest, {
	      value: _sendForwardRequest2
	    });
	    Object.defineProperty(this, _clearLastMessageViews, {
	      value: _clearLastMessageViews2
	    });
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
	    Object.defineProperty(this, _prepareForwardParams, {
	      value: _prepareForwardParams2
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
	    Object.defineProperty(this, _updateModels$3, {
	      value: _updateModels2$3
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
	    Object.defineProperty(this, _prepareMessageWithFiles, {
	      value: _prepareMessageWithFiles2
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
	    Object.defineProperty(this, _addLoadingMessage, {
	      value: _addLoadingMessage2
	    });
	    Object.defineProperty(this, _store$e, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e] = im_v2_application_core.Core.getStore();
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
	  async sendMessageWithFiles(params) {
	    const {
	      text = '',
	      fileIds = []
	    } = params;
	    if (!main_core.Type.isStringFilled(text) && !main_core.Type.isArrayFilled(fileIds)) {
	      return;
	    }
	    im_v2_lib_logger.Logger.warn('SendingService: sendMessage with files', params);
	    const message = babelHelpers.classPrivateFieldLooseBase(this, _prepareMessageWithFiles)[_prepareMessageWithFiles](params);
	    await babelHelpers.classPrivateFieldLooseBase(this, _handlePagination)[_handlePagination](message.dialogId);
	    await babelHelpers.classPrivateFieldLooseBase(this, _addLoadingMessage)[_addLoadingMessage](message);
	    await babelHelpers.classPrivateFieldLooseBase(this, _addMessageToRecent)[_addMessageToRecent](message);
	    await babelHelpers.classPrivateFieldLooseBase(this, _clearLastMessageViews)[_clearLastMessageViews](message.dialogId);
	    babelHelpers.classPrivateFieldLooseBase(this, _sendScrollEvent)[_sendScrollEvent]({
	      force: true,
	      dialogId: message.dialogId
	    });
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
	    const sortForwardIds = [...forwardIds].sort();
	    const forwardUuidMap = babelHelpers.classPrivateFieldLooseBase(this, _getForwardUuidMap)[_getForwardUuidMap](sortForwardIds);
	    const forwardedMessages = babelHelpers.classPrivateFieldLooseBase(this, _prepareForwardMessages)[_prepareForwardMessages](params, forwardUuidMap);
	    await babelHelpers.classPrivateFieldLooseBase(this, _addForwardsToModels)[_addForwardsToModels](forwardedMessages);
	    babelHelpers.classPrivateFieldLooseBase(this, _sendScrollEvent)[_sendScrollEvent]({
	      force: true,
	      dialogId
	    });
	    return babelHelpers.classPrivateFieldLooseBase(this, _sendForwardRequest)[_sendForwardRequest]({
	      forwardUuidMap,
	      commentMessage,
	      dialogId
	    });
	  }
	  async retrySendMessage(params) {
	    const {
	      tempMessageId,
	      dialogId
	    } = params;
	    const unsentMessage = babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].getters['messages/getById'](tempMessageId);
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
	    if (main_core.Type.isStringFilled(unsentMessage.forward.id)) {
	      const [, forwardId] = unsentMessage.forward.id.split('/');
	      const forwardUuidMap = {
	        [unsentMessage.id]: forwardId
	      };
	      return babelHelpers.classPrivateFieldLooseBase(this, _sendForwardRequest)[_sendForwardRequest]({
	        forwardUuidMap,
	        dialogId
	      });
	    }
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
	async function _addLoadingMessage2(message) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].dispatch('messages/addLoadingMessage', {
	    message
	  });
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
	  babelHelpers.classPrivateFieldLooseBase(this, _updateModels$3)[_updateModels$3]({
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
	function _prepareMessageWithFiles2(params) {
	  const {
	    fileIds
	  } = params;
	  if (!main_core.Type.isArrayFilled(fileIds)) {
	    throw new Error('SendingService: sendMessageWithFile: no fileId provided');
	  }
	  return {
	    ...babelHelpers.classPrivateFieldLooseBase(this, _prepareMessage)[_prepareMessage](params),
	    params: {
	      FILE_ID: fileIds
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
	  void babelHelpers.classPrivateFieldLooseBase(this, _clearLastMessageViews)[_clearLastMessageViews](message.dialogId);
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].dispatch('messages/add', message);
	}
	function _addMessageToRecent2(message) {
	  var _message$params;
	  const hasMessageText = main_core.Type.isStringFilled(message.text);
	  const hasMessageFile = main_core.Type.isArrayFilled((_message$params = message.params) == null ? void 0 : _message$params.FILE_ID);
	  if (hasMessageText || hasMessageFile) {
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].dispatch('recent/update', {
	      id: message.dialogId,
	      fields: {
	        messageId: message.temporaryId
	      }
	    });
	  }
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
	function _updateModels2$3(params) {
	  const {
	    oldId,
	    newId,
	    dialogId
	  } = params;
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].dispatch('messages/updateWithId', {
	    id: oldId,
	    fields: {
	      id: newId
	    }
	  });
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].dispatch('chats/update', {
	    dialogId,
	    fields: {
	      lastId: newId,
	      lastMessageId: newId
	    }
	  });
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].dispatch('recent/update', {
	    id: dialogId,
	    fields: {
	      messageId: newId
	    }
	  });
	}
	function _updateMessageError2(messageId) {
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].dispatch('messages/update', {
	    id: messageId,
	    fields: {
	      error: true
	    }
	  });
	}
	function _removeMessageError2(messageId) {
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].dispatch('messages/update', {
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
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].getters['chats/get'](dialogId, true);
	}
	function _getDialogByChatId2$1(chatId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].getters['chats/getByChatId'](chatId, true);
	}
	function _needToSetAsViewed2(dialogId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].getters['users/bots/isNetwork'](dialogId);
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
	    babelHelpers.classPrivateFieldLooseBase(this, _updateModels$3)[_updateModels$3]({
	      oldId: commentMessage.temporaryId,
	      newId: id,
	      dialogId
	    });
	  }
	  Object.entries(uuidMap).forEach(([uuid, messageId]) => {
	    babelHelpers.classPrivateFieldLooseBase(this, _updateModels$3)[_updateModels$3]({
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
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].dispatch('messages/update', {
	      id: commentMessage.temporaryId,
	      fields: {
	        error: true
	      }
	    });
	  }
	  Object.keys(forwardUuidMap).forEach(uuid => {
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].dispatch('messages/update', {
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
	    const message = babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].getters['messages/getById'](messageId);
	    if (!message) {
	      return;
	    }
	    preparedMessages.push({
	      ...babelHelpers.classPrivateFieldLooseBase(this, _prepareMessage)[_prepareMessage]({
	        dialogId,
	        text: message.text,
	        tempMessageId: uuid,
	        replyId: message.replyId
	      }),
	      forward: babelHelpers.classPrivateFieldLooseBase(this, _prepareForwardParams)[_prepareForwardParams](messageId),
	      attach: message.attach,
	      isDeleted: message.isDeleted,
	      files: message.files
	    });
	  });
	  return preparedMessages;
	}
	function _prepareForwardParams2(messageId) {
	  const message = babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].getters['messages/getById'](messageId);
	  const chat = babelHelpers.classPrivateFieldLooseBase(this, _getDialogByChatId$1)[_getDialogByChatId$1](message.chatId);
	  const isForward = babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].getters['messages/isForward'](messageId);
	  const userId = isForward ? message.forward.userId : message.authorId;
	  const chatType = isForward ? message.forward.chatType : chat.type;
	  let chatTitle = isForward ? message.forward.chatTitle : chat.name;
	  if (chatType === im_v2_const.ChatType.channel) {
	    chatTitle = null;
	  }
	  return {
	    id: babelHelpers.classPrivateFieldLooseBase(this, _buildForwardContextId)[_buildForwardContextId](message.chatId, messageId),
	    userId,
	    chatType,
	    chatTitle
	  };
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
	function _clearLastMessageViews2(dialogId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].dispatch('chats/clearLastMessageViews', {
	    dialogId
	  });
	}
	async function _sendForwardRequest2({
	  forwardUuidMap,
	  commentMessage,
	  dialogId
	}) {
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
	      NOTIFY_ID: notificationId,
	      NOTIFY_VALUE: value
	    };
	    this.store.dispatch('notifications/delete', {
	      id: notificationId
	    });
	    this.restClient.callMethod('im.notify.confirm', requestParams).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error(error);
	    });
	  }
	  async sendQuickAnswer(params) {
	    const {
	      id,
	      text,
	      callbackSuccess = () => {},
	      callbackError = () => {}
	    } = params;
	    try {
	      const response = await this.restClient.callMethod(im_v2_const.RestMethod.imNotifyAnswer, {
	        notify_id: id,
	        answer_text: text
	      });
	      callbackSuccess(response);
	    } catch (error) {
	      // eslint-disable-next-line no-console
	      console.error(error);
	      callbackError();
	    }
	  }
	  deleteRequest() {
	    const idsToDelete = [...this.notificationsToDelete];
	    this.restClient.callMethod('im.notify.delete', {
	      id: idsToDelete
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error(error);
	    });
	    this.notificationsToDelete.clear();
	  }
	  requestItems({
	    firstPage = false
	  } = {}) {
	    const imNotifyGetQueryParams = {
	      LIMIT: this.limitPerPage,
	      CONVERT_TEXT: 'Y'
	    };
	    const batchQueryParams = {
	      [im_v2_const.RestMethod.imNotifyGet]: [im_v2_const.RestMethod.imNotifyGet, imNotifyGetQueryParams]
	    };
	    if (firstPage) {
	      batchQueryParams[im_v2_const.RestMethod.imNotifySchemaGet] = [im_v2_const.RestMethod.imNotifySchemaGet, {}];
	    } else {
	      imNotifyGetQueryParams.LAST_ID = this.lastId;
	      imNotifyGetQueryParams.LAST_TYPE = this.lastType;
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
	    if (!main_core.Type.isArrayFilled(notifications)) {
	      return true;
	    }
	    return notifications.length < this.limitPerPage;
	  }
	  destroy() {
	    im_v2_lib_logger.Logger.warn('Notification service destroyed');
	  }
	}

	var _restClient$9 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	class DiskService {
	  constructor() {
	    Object.defineProperty(this, _restClient$9, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$9)[_restClient$9] = im_v2_application_core.Core.getRestClient();
	  }
	  delete({
	    chatId,
	    fileId
	  }) {
	    const queryParams = {
	      chat_id: chatId,
	      file_id: fileId
	    };
	    return babelHelpers.classPrivateFieldLooseBase(this, _restClient$9)[_restClient$9].callMethod(im_v2_const.RestMethod.imDiskFileDelete, queryParams).catch(error => {
	      console.error('DiskService: error deleting file', error);
	    });
	  }
	  async save(fileIds) {
	    try {
	      const normalizedIds = fileIds.map(id => Number.parseInt(id, 10));
	      await im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2DiskFileSave, {
	        data: {
	          ids: normalizedIds
	        }
	      });
	    } catch (error) {
	      console.error('DiskService: error saving file on disk', error);
	    }
	  }
	}

	const MAX_FILES_IN_ONE_MESSAGE = 10;
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
	      autoUpload = false,
	      chatId,
	      dialogId
	    } = options;
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderRegistry)[_uploaderRegistry][uploaderId] = new ui_uploader_core.Uploader({
	      autoUpload,
	      controller: 'disk.uf.integration.diskUploaderController',
	      multiple: true,
	      controllerOptions: {
	        folderId: diskFolderId,
	        chat: {
	          chatId,
	          dialogId
	        }
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
	        [ui_uploader_core.UploaderEvent.UPLOAD_COMPLETE]: event => {
	          this.emit(UploaderWrapper.events.onUploadComplete, {
	            uploaderId
	          });
	        }
	      }
	    });
	  }
	  start(uploaderId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderRegistry)[_uploaderRegistry][uploaderId].setAutoUpload(true);
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderRegistry)[_uploaderRegistry][uploaderId].start();
	  }
	  destroyUploader(uploaderId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderRegistry)[_uploaderRegistry][uploaderId].destroy({
	      removeFilesFromServer: false
	    });
	  }
	  addFiles(tasks) {
	    const firstTenTasks = tasks.slice(0, MAX_FILES_IN_ONE_MESSAGE);
	    const addedFiles = [];
	    firstTenTasks.forEach(task => {
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
	  onMaxFileCountExceeded: 'onMaxFileCountExceeded',
	  onUploadComplete: 'onUploadComplete'
	};

	var _store$f = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$a = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _isRequestingDiskFolderId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isRequestingDiskFolderId");
	var _diskFolderIdRequestPromise = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("diskFolderIdRequestPromise");
	var _uploaderWrapper = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploaderWrapper");
	var _sendingService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendingService");
	var _uploaderFilesRegistry = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploaderFilesRegistry");
	var _createUploader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createUploader");
	var _registerSourceFilesCount = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("registerSourceFilesCount");
	var _addFiles = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addFiles");
	var _addFileFromDiskToModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addFileFromDiskToModel");
	var _initUploader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initUploader");
	var _isMediaFile = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isMediaFile");
	var _setFileMapping = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setFileMapping");
	var _requestDiskFolderId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("requestDiskFolderId");
	var _tryCommit = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("tryCommit");
	var _uploadPreview = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploadPreview");
	var _prepareFileForUploader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareFileForUploader");
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
	var _unregisterFiles = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("unregisterFiles");
	var _setPreviewCreatedStatus = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setPreviewCreatedStatus");
	var _setPreviewSentStatus = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setPreviewSentStatus");
	var _setMessagesText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setMessagesText");
	var _setAutoUpload = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setAutoUpload");
	var _createMessagesFromFiles = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createMessagesFromFiles");
	var _createMessageFromFiles = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createMessageFromFiles");
	var _readyToAddMessages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("readyToAddMessages");
	var _readyToCommit = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("readyToCommit");
	var _tryToSendMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("tryToSendMessage");
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
	    Object.defineProperty(this, _tryToSendMessage, {
	      value: _tryToSendMessage2
	    });
	    Object.defineProperty(this, _readyToCommit, {
	      value: _readyToCommit2
	    });
	    Object.defineProperty(this, _readyToAddMessages, {
	      value: _readyToAddMessages2
	    });
	    Object.defineProperty(this, _createMessageFromFiles, {
	      value: _createMessageFromFiles2
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
	    Object.defineProperty(this, _setPreviewSentStatus, {
	      value: _setPreviewSentStatus2
	    });
	    Object.defineProperty(this, _setPreviewCreatedStatus, {
	      value: _setPreviewCreatedStatus2
	    });
	    Object.defineProperty(this, _unregisterFiles, {
	      value: _unregisterFiles2
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
	    Object.defineProperty(this, _prepareFileForUploader, {
	      value: _prepareFileForUploader2
	    });
	    Object.defineProperty(this, _uploadPreview, {
	      value: _uploadPreview2
	    });
	    Object.defineProperty(this, _tryCommit, {
	      value: _tryCommit2
	    });
	    Object.defineProperty(this, _requestDiskFolderId, {
	      value: _requestDiskFolderId2
	    });
	    Object.defineProperty(this, _setFileMapping, {
	      value: _setFileMapping2
	    });
	    Object.defineProperty(this, _isMediaFile, {
	      value: _isMediaFile2
	    });
	    Object.defineProperty(this, _initUploader, {
	      value: _initUploader2
	    });
	    Object.defineProperty(this, _addFileFromDiskToModel, {
	      value: _addFileFromDiskToModel2
	    });
	    Object.defineProperty(this, _addFiles, {
	      value: _addFiles2
	    });
	    Object.defineProperty(this, _registerSourceFilesCount, {
	      value: _registerSourceFilesCount2
	    });
	    Object.defineProperty(this, _createUploader, {
	      value: _createUploader2
	    });
	    Object.defineProperty(this, _store$f, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$a, {
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
	    babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$a)[_restClient$a] = im_v2_application_core.Core.getRestClient();
	    babelHelpers.classPrivateFieldLooseBase(this, _sendingService)[_sendingService] = SendingService$$1.getInstance();
	    babelHelpers.classPrivateFieldLooseBase(this, _initUploader)[_initUploader]();
	  }
	  async uploadFromClipboard(params) {
	    const {
	      clipboardEvent,
	      dialogId,
	      autoUpload,
	      imagesOnly
	    } = params;
	    const {
	      clipboardData
	    } = clipboardEvent;
	    if (!clipboardData || !ui_uploader_core.isFilePasted(clipboardData)) {
	      return '';
	    }
	    clipboardEvent.preventDefault();
	    let files = await ui_uploader_core.getFilesFromDataTransfer(clipboardData);
	    if (imagesOnly) {
	      files = files.filter(file => im_v2_lib_utils.Utils.file.isImage(file.name));
	      if (imagesOnly.length === 0) {
	        return '';
	      }
	    }
	    const {
	      uploaderFiles,
	      uploaderId
	    } = await babelHelpers.classPrivateFieldLooseBase(this, _addFiles)[_addFiles]({
	      files,
	      dialogId,
	      autoUpload
	    });
	    if (uploaderFiles.length === 0) {
	      return '';
	    }
	    return uploaderId;
	  }
	  async uploadFromInput(params) {
	    const {
	      event,
	      sendAsFile,
	      autoUpload,
	      dialogId
	    } = params;
	    const rawFiles = Object.values(event.target.files);
	    if (rawFiles.length === 0) {
	      return '';
	    }
	    const {
	      uploaderId
	    } = await babelHelpers.classPrivateFieldLooseBase(this, _addFiles)[_addFiles]({
	      files: rawFiles,
	      dialogId,
	      autoUpload,
	      sendAsFile
	    });
	    return uploaderId;
	  }
	  async uploadFromDragAndDrop(params) {
	    const {
	      event,
	      dialogId,
	      autoUpload,
	      sendAsFile
	    } = params;
	    event.preventDefault();
	    const rawFiles = await ui_uploader_core.getFilesFromDataTransfer(event.dataTransfer);
	    if (rawFiles.length === 0) {
	      return '';
	    }
	    const {
	      uploaderId
	    } = await babelHelpers.classPrivateFieldLooseBase(this, _addFiles)[_addFiles]({
	      files: rawFiles,
	      dialogId,
	      autoUpload,
	      sendAsFile
	    });
	    return uploaderId;
	  }
	  getSourceFilesCount(uploaderId) {
	    var _babelHelpers$classPr;
	    return (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].sourceFilesCount) != null ? _babelHelpers$classPr : 0;
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
	          fileIds: [messageWithFile.tempFileId],
	          dialogId: messageWithFile.dialogId
	        };
	        return babelHelpers.classPrivateFieldLooseBase(this, _sendingService)[_sendingService].sendMessageWithFiles(message);
	      }).then(() => {
	        this.commitFile({
	          chatId: messageWithFile.chatId,
	          temporaryFileId: messageWithFile.tempFileId,
	          tempMessageId: messageWithFile.tempMessageId,
	          realFileId: messageWithFile.file.id.slice(1),
	          fromDisk: true
	        });
	      }).catch(error => {
	        // eslint-disable-next-line no-console
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
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$a)[_restClient$a].callMethod(im_v2_const.RestMethod.imDiskFileCommit, {
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
	  commitMessage(uploaderId) {
	    const dialogId = babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].dialogId;
	    const chatId = babelHelpers.classPrivateFieldLooseBase(this, _getChatId)[_getChatId](dialogId);
	    const fileIds = babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrapper)[_uploaderWrapper].getFiles(uploaderId).map(file => {
	      return file.getServerFileId().toString().slice(1);
	    });
	    const sendAsFile = babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrapper)[_uploaderWrapper].getFiles(uploaderId).every(file => {
	      return file.getCustomData('sendAsFile');
	    });
	    return babelHelpers.classPrivateFieldLooseBase(this, _restClient$a)[_restClient$a].callMethod(im_v2_const.RestMethod.imDiskFileCommit, {
	      chat_id: chatId,
	      message: babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].text,
	      template_id: babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].tempMessageId,
	      // file_template_id: temporaryFileId,
	      as_file: sendAsFile ? 'Y' : 'N',
	      upload_id: fileIds
	    });
	  }
	  // we don't use it now, because we always send several files in ONE message
	  // noinspection JSUnusedGlobalSymbols
	  sendSeparateMessagesWithFiles(params) {
	    const {
	      uploaderId,
	      text
	    } = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _setMessagesText)[_setMessagesText](uploaderId, text);
	    babelHelpers.classPrivateFieldLooseBase(this, _setAutoUpload)[_setAutoUpload](uploaderId, true);
	    babelHelpers.classPrivateFieldLooseBase(this, _tryToSendMessages)[_tryToSendMessages](uploaderId);
	  }
	  sendMessageWithFiles(params) {
	    const {
	      uploaderId,
	      text
	    } = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _setMessagesText)[_setMessagesText](uploaderId, text);
	    babelHelpers.classPrivateFieldLooseBase(this, _setAutoUpload)[_setAutoUpload](uploaderId, true);
	    babelHelpers.classPrivateFieldLooseBase(this, _tryToSendMessage)[_tryToSendMessage](uploaderId);
	  }
	  removeFileFromUploader(options) {
	    const {
	      uploaderId,
	      filesIds
	    } = options;
	    const files = babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrapper)[_uploaderWrapper].getFiles(uploaderId).filter(file => {
	      return filesIds.includes(file.getId());
	    });
	    files.forEach(file => {
	      file.remove();
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _unregisterFiles)[_unregisterFiles](uploaderId, files);
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
	  const chatId = babelHelpers.classPrivateFieldLooseBase(this, _getChatId)[_getChatId](dialogId);
	  return this.checkDiskFolderId(dialogId).then(diskFolderId => {
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrapper)[_uploaderWrapper].createUploader({
	      diskFolderId,
	      uploaderId,
	      autoUpload,
	      chatId,
	      dialogId
	    });
	    return uploaderId;
	  });
	}
	function _registerSourceFilesCount2({
	  uploaderId,
	  filesCount
	}) {
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].sourceFilesCount = filesCount;
	}
	function _addFiles2(params) {
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
	      const preparedFile = babelHelpers.classPrivateFieldLooseBase(this, _prepareFileForUploader)[_prepareFileForUploader](file, dialogId, uploaderId, sendAsFile);
	      filesForUploader.push(preparedFile);
	    });
	    const addedFiles = babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrapper)[_uploaderWrapper].addFiles(filesForUploader);
	    babelHelpers.classPrivateFieldLooseBase(this, _registerFiles)[_registerFiles](uploaderId, addedFiles, dialogId, autoUpload);
	    babelHelpers.classPrivateFieldLooseBase(this, _registerSourceFilesCount)[_registerSourceFilesCount]({
	      filesCount: files.length,
	      uploaderId
	    });
	    return {
	      uploaderFiles: addedFiles,
	      uploaderId
	    };
	  });
	}
	function _addFileFromDiskToModel2(messageWithFile) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].dispatch('files/add', {
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
	    babelHelpers.classPrivateFieldLooseBase(this, _setPreviewCreatedStatus)[_setPreviewCreatedStatus](uploaderId, file.getId());
	    babelHelpers.classPrivateFieldLooseBase(this, _tryToSendMessage)[_tryToSendMessage](uploaderId);
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
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrapper)[_uploaderWrapper].subscribe(UploaderWrapper.events.onFileUploadComplete, async event => {
	    const {
	      file,
	      uploaderId
	    } = event.getData();
	    const serverFileId = file.getServerFileId().toString().slice(1);
	    const temporaryFileId = file.getId();
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isMediaFile)[_isMediaFile](temporaryFileId)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setFileMapping)[_setFileMapping]({
	        serverFileId,
	        temporaryFileId
	      });
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _updateFileProgress)[_updateFileProgress](temporaryFileId, file.getProgress(), im_v2_const.FileStatus.wait);
	    await babelHelpers.classPrivateFieldLooseBase(this, _uploadPreview)[_uploadPreview](file);
	    babelHelpers.classPrivateFieldLooseBase(this, _setPreviewSentStatus)[_setPreviewSentStatus](uploaderId, temporaryFileId);
	    void babelHelpers.classPrivateFieldLooseBase(this, _tryCommit)[_tryCommit](uploaderId);
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
	function _isMediaFile2(fileId) {
	  const mediaFileTypes = [im_v2_const.FileType.image, im_v2_const.FileType.video];
	  const file = babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].getters['files/get'](fileId);
	  return Boolean(file) && mediaFileTypes.includes(file.type);
	}
	function _setFileMapping2(options) {
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].dispatch('files/setTemporaryFileMapping', options);
	}
	function _requestDiskFolderId2(dialogId) {
	  return new Promise((resolve, reject) => {
	    babelHelpers.classPrivateFieldLooseBase(this, _isRequestingDiskFolderId)[_isRequestingDiskFolderId] = true;
	    const chatId = babelHelpers.classPrivateFieldLooseBase(this, _getChatId)[_getChatId](dialogId);
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$a)[_restClient$a].callMethod(im_v2_const.RestMethod.imDiskFolderGet, {
	      chat_id: chatId
	    }).then(response => {
	      const {
	        ID: diskFolderId
	      } = response.data();
	      babelHelpers.classPrivateFieldLooseBase(this, _isRequestingDiskFolderId)[_isRequestingDiskFolderId] = false;
	      babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].commit('chats/update', {
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
	async function _tryCommit2(uploaderId) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _readyToCommit)[_readyToCommit](uploaderId)) {
	    return;
	  }
	  await this.commitMessage(uploaderId);
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderWrapper)[_uploaderWrapper].destroyUploader(uploaderId);
	}
	async function _uploadPreview2(file) {
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
	function _prepareFileForUploader2(file, dialogId, uploaderId, sendAsFile) {
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
	    sendAsFile
	  };
	}
	function _updateFileProgress2(id, progress, status) {
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].dispatch('files/update', {
	    id,
	    fields: {
	      progress: progress === 100 ? 99 : progress,
	      status
	    }
	  });
	}
	function _cancelUpload2(tempMessageId, tempFileId) {
	  const message = babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].getters['messages/getById'](tempMessageId);
	  if (message) {
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].dispatch('messages/delete', {
	      id: tempMessageId
	    });
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].dispatch('files/delete', {
	      id: tempFileId
	    });
	    const chat = babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].getters['chats/getByChatId'](message.chatId);
	    const lastMessageId = babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].getters['messages/findLastChatMessageId'](message.chatId);
	    if (lastMessageId > -1) {
	      void babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].dispatch('recent/update', {
	        id: chat.dialogId,
	        fields: {
	          messageId: lastMessageId
	        }
	      });
	    } else {
	      void babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].dispatch('recent/delete', {
	        id: chat.dialogId
	      });
	    }
	  }
	}
	function _addFileToStore2(file) {
	  const taskId = file.getId();
	  const fileBinary = file.getBinary();
	  const previewData = babelHelpers.classPrivateFieldLooseBase(this, _preparePreview)[_preparePreview](file);
	  const asFile = file.getCustomData('sendAsFile');
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].dispatch('files/add', {
	    id: taskId,
	    chatId: file.getCustomData('chatId'),
	    authorId: im_v2_application_core.Core.getUserId(),
	    name: fileBinary.name,
	    size: file.getSize(),
	    type: asFile ? im_v2_const.FileType.file : babelHelpers.classPrivateFieldLooseBase(this, _getFileType)[_getFileType](fileBinary),
	    extension: babelHelpers.classPrivateFieldLooseBase(this, _getFileExtension)[_getFileExtension](fileBinary),
	    status: file.isFailed() ? im_v2_const.FileStatus.error : im_v2_const.FileStatus.progress,
	    progress: 0,
	    authorName: babelHelpers.classPrivateFieldLooseBase(this, _getCurrentUser)[_getCurrentUser]().name,
	    ...previewData
	  });
	}
	function _updateFilePreviewInStore2(file) {
	  const previewData = babelHelpers.classPrivateFieldLooseBase(this, _preparePreview)[_preparePreview](file);
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].dispatch('files/update', {
	    id: file.getId(),
	    fields: {
	      ...previewData
	    }
	  });
	}
	function _updateFileSizeInStore2(file) {
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].dispatch('files/update', {
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
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].getters['chats/get'](dialogId);
	}
	function _getCurrentUser2() {
	  const userId = im_v2_application_core.Core.getUserId();
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].getters['users/get'](userId);
	}
	function _getChatId2(dialogId) {
	  var _babelHelpers$classPr2;
	  return (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _getDialog$2)[_getDialog$2](dialogId)) == null ? void 0 : _babelHelpers$classPr2.chatId;
	}
	function _registerFiles2(uploaderId, files, dialogId, autoUpload) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId] = {
	      previewCreatedStatus: {},
	      previewSentStatus: {},
	      dialogId,
	      text: '',
	      autoUpload
	    };
	  }
	  files.forEach(file => {
	    const fileId = file.getId();
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].previewCreatedStatus) {
	      babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].previewCreatedStatus = {};
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].previewCreatedStatus[fileId] = false;
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].previewSentStatus[fileId] = false;
	  });
	}
	function _unregisterFiles2(uploaderId, files) {
	  const entry = babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId];
	  if (entry) {
	    files.forEach(file => {
	      const fileId = file.getId();
	      if (babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].previewCreatedStatus) {
	        delete babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].previewCreatedStatus[fileId];
	        delete babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].previewSentStatus[fileId];
	      }
	    });
	  }
	}
	function _setPreviewCreatedStatus2(uploaderId, fileId) {
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].previewCreatedStatus[fileId] = true;
	}
	function _setPreviewSentStatus2(uploaderId, fileId) {
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].previewSentStatus[fileId] = true;
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
	    var _file$getCustomData;
	    if (file.getError()) {
	      return;
	    }
	    const messageId = im_v2_lib_utils.Utils.text.getUuidV4();
	    file.setCustomData('messageId', messageId);
	    if (files.length === 1 && hasText) {
	      file.setCustomData('messageText', text);
	    }
	    messagesToSend.files.push({
	      fileIds: [file.getId()],
	      tempMessageId: file.getCustomData('tempMessageId'),
	      dialogId,
	      text: (_file$getCustomData = file.getCustomData('messageText')) != null ? _file$getCustomData : ''
	    });
	  });
	  return messagesToSend;
	}
	function _createMessageFromFiles2(uploaderId) {
	  const tempMessageId = im_v2_lib_utils.Utils.text.getUuidV4();
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].tempMessageId = tempMessageId;
	  const fileIds = [];
	  const files = this.getFiles(uploaderId);
	  files.forEach(file => {
	    if (!file.getError()) {
	      fileIds.push(file.getId());
	    }
	  });
	  const text = babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].text;
	  const dialogId = babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].dialogId;
	  return {
	    fileIds,
	    tempMessageId,
	    dialogId,
	    text
	  };
	}
	function _readyToAddMessages2(uploaderId) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId] || !babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].autoUpload || babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].wasSent) {
	    return false;
	  }
	  const {
	    previewCreatedStatus
	  } = babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId];
	  return Object.values(previewCreatedStatus).every(flag => flag === true);
	}
	function _readyToCommit2(uploaderId) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId]) {
	    return false;
	  }
	  const {
	    previewSentStatus
	  } = babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId];
	  return Object.values(previewSentStatus).every(flag => flag === true);
	}
	function _tryToSendMessage2(uploaderId) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _readyToAddMessages)[_readyToAddMessages](uploaderId)) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].wasSent = true;
	  const message = babelHelpers.classPrivateFieldLooseBase(this, _createMessageFromFiles)[_createMessageFromFiles](uploaderId);
	  void babelHelpers.classPrivateFieldLooseBase(this, _sendingService)[_sendingService].sendMessageWithFiles(message);
	  this.start(uploaderId);
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
	    void babelHelpers.classPrivateFieldLooseBase(this, _sendingService)[_sendingService].sendMessageWithFiles(message);
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
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].dispatch('messages/update', {
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
	        value
	      }
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('SettingsService: changeSetting error', error);
	    });
	  }
	}

	class LinesService {
	  async getDialogIdByUserCode(userCode) {
	    const result = await im_v2_application_core.Core.getRestClient().callMethod(im_v2_const.RestMethod.linesDialogGet, {
	      USER_CODE: userCode
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('LinesService: error getting dialog id', error);
	    });
	    const {
	      dialog_id: dialogId
	    } = result.data();
	    return dialogId;
	  }
	}

	var _onCreateError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onCreateError");
	var _sendAnalytics$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendAnalytics");
	class CopilotService$$1 {
	  constructor() {
	    Object.defineProperty(this, _sendAnalytics$2, {
	      value: _sendAnalytics2$2
	    });
	    Object.defineProperty(this, _onCreateError, {
	      value: _onCreateError2
	    });
	  }
	  async createChat({
	    roleCode
	  }) {
	    const chatService = new ChatService();
	    const {
	      newDialogId,
	      newChatId
	    } = await chatService.createChat({
	      type: im_v2_const.ChatType.copilot,
	      copilotMainRole: roleCode
	    }).catch(error => {
	      babelHelpers.classPrivateFieldLooseBase(this, _onCreateError)[_onCreateError](error);
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _sendAnalytics$2)[_sendAnalytics$2]({
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
	function _sendAnalytics2$2({
	  chatId,
	  dialogId
	}) {
	  im_v2_lib_analytics.Analytics.getInstance().copilot.onCreateChat(chatId);
	  im_v2_lib_analytics.Analytics.getInstance().ignoreNextChatOpen(dialogId);
	}

	const CommentsService = {
	  subscribe(messageId) {
	    im_v2_application_core.Core.getStore().dispatch('messages/comments/subscribe', messageId);
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatCommentSubscribe, {
	      data: {
	        postId: messageId,
	        createIfNotExists: true,
	        autoJoin: true
	      }
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('CommentsService: subscribe error', error);
	    });
	  },
	  unsubscribe(messageId) {
	    im_v2_application_core.Core.getStore().dispatch('messages/comments/unsubscribe', messageId);
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatCommentUnsubscribe, {
	      data: {
	        postId: messageId,
	        createIfNotExists: true,
	        autoJoin: true
	      }
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('CommentsService: unsubscribe error', error);
	    });
	  },
	  readAllChannelComments(channelDialogId) {
	    const chat = im_v2_application_core.Core.getStore().getters['chats/get'](channelDialogId, true);
	    const currentChannelCounter = im_v2_application_core.Core.getStore().getters['counters/getChannelCommentsCounter'](chat.chatId);
	    if (currentChannelCounter === 0) {
	      return Promise.resolve();
	    }
	    im_v2_application_core.Core.getStore().dispatch('counters/readAllChannelComments', chat.chatId);
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatCommentReadAll, {
	      data: {
	        dialogId: channelDialogId
	      }
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('CommentsService: readAllChannelComments error', error);
	    });
	  }
	};

	exports.RecentService = RecentService;
	exports.ChatService = ChatService;
	exports.MessageService = MessageService;
	exports.SendingService = SendingService$$1;
	exports.NotificationService = NotificationService;
	exports.DiskService = DiskService;
	exports.UploadingService = UploadingService$$1;
	exports.SettingsService = SettingsService;
	exports.LinesService = LinesService;
	exports.CopilotService = CopilotService$$1;
	exports.CommentsService = CommentsService;

}((this.BX.Messenger.v2.Service = this.BX.Messenger.v2.Service || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX.OpenLines.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Vue3.Vuex,BX,BX,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Lib,BX.Event,BX.UI.Uploader,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Const));
//# sourceMappingURL=registry.bundle.js.map
