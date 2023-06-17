this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Provider = this.BX.Messenger.v2.Provider || {};
(function (exports,im_v2_lib_uuid,im_public,rest_client,ui_notification,ui_vue3_vuex,im_v2_lib_rest,im_v2_provider_service,im_v2_lib_utils,main_core_events,im_v2_lib_uploader,main_core,im_v2_application_core,im_v2_lib_logger,im_v2_const,im_v2_lib_user) {
	'use strict';

	class RecentService {
	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }
	  constructor() {
	    this.store = null;
	    this.restClient = null;
	    this.dataIsPreloaded = false;
	    this.itemsPerPage = 50;
	    this.isLoading = false;
	    this.pagesLoaded = 0;
	    this.hasMoreItemsToLoad = true;
	    this.lastMessageDate = null;
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	    this.onUpdateStateHandler = this.onUpdateState.bind(this);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.recent.updateState, this.onUpdateStateHandler);
	  }

	  // region public
	  getCollection() {
	    return this.store.getters['recent/getRecentCollection'];
	  }
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
	  hideChat(dialogId) {
	    im_v2_lib_logger.Logger.warn(`Im.RecentList: hide chat`, dialogId);
	    const recentItem = this.store.getters['recent/get'](dialogId);
	    if (!recentItem) {
	      return false;
	    }
	    this.store.dispatch('recent/delete', {
	      id: dialogId
	    });
	    const chatIsOpened = this.store.getters['application/isChatOpen'](dialogId);
	    if (chatIsOpened) {
	      im_public.Messenger.openChat();
	    }
	    this.restClient.callMethod(im_v2_const.RestMethod.imRecentHide, {
	      'DIALOG_ID': dialogId
	    }).catch(error => {
	      console.error('Im.RecentList: hide chat error', error);
	    });
	  }
	  // endregion public

	  requestItems({
	    firstPage = false
	  } = {}) {
	    const queryParams = this.getQueryParams(firstPage);
	    return this.restClient.callMethod(this.getQueryMethod(), queryParams).then(result => {
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
	      return this.updateModels(result.data()).then(() => {
	        this.isLoading = false;
	      });
	    }).catch(error => {
	      console.error('Im.RecentList: page request error', error);
	    });
	  }
	  getQueryMethod() {
	    return im_v2_const.RestMethod.imRecentList;
	  }
	  getQueryParams(firstPage) {
	    return {
	      'SKIP_OPENLINES': 'Y',
	      'LIMIT': this.itemsPerPage,
	      'LAST_MESSAGE_DATE': firstPage ? null : this.lastMessageDate,
	      'GET_ORIGINAL_TEXT': 'Y'
	    };
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
	    const recentPromise = this.store.dispatch('recent/setRecent', recent);
	    return Promise.all([usersPromise, dialoguesPromise, recentPromise]);
	  }
	  onUpdateState({
	    data
	  }) {
	    im_v2_lib_logger.Logger.warn(`Im.RecentList: setting UpdateState data`, data);
	    this.updateModels(data);
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
	      type: im_v2_const.DialogType.user,
	      counter: item.counter
	    };
	  }
	  prepareChatForAdditionalUser(user) {
	    return {
	      dialogId: user.id,
	      avatar: user.avatar,
	      color: user.color,
	      name: user.name,
	      type: im_v2_const.DialogType.user
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

	class ChatDataExtractor {
	  constructor(response) {
	    this.response = {};
	    this.chatId = 0;
	    this.dialogId = '';
	    this.rawUsers = [];
	    this.users = {};
	    this.dialogues = {};
	    this.files = {};
	    this.messages = {};
	    this.reactions = [];
	    this.additionalUsers = [];
	    this.messagesToStore = {};
	    this.pinnedMessageIds = [];
	    this.response = response;
	  }
	  extractData() {
	    this.extractChatResult();
	    this.extractUserResult();
	    this.extractMessageListResult();
	    this.extractContextResult();
	    this.extractPinnedMessagesResult();
	    this.fillChatsForUsers();
	  }
	  isOpenlinesChat() {
	    const chat = this.dialogues[this.dialogId];
	    if (!chat) {
	      return false;
	    }
	    return chat.type === im_v2_const.DialogType.lines;
	  }
	  getChatId() {
	    return this.chatId;
	  }
	  getUsers() {
	    return this.rawUsers;
	  }
	  getDialogues() {
	    return Object.values(this.dialogues);
	  }
	  getMessages() {
	    return Object.values(this.messages);
	  }
	  getMessagesToStore() {
	    return Object.values(this.messagesToStore);
	  }
	  getFiles() {
	    return Object.values(this.files);
	  }
	  getPinnedMessages() {
	    return this.pinnedMessageIds;
	  }
	  getReactions() {
	    return this.reactions;
	  }
	  getAdditionalUsers() {
	    return this.additionalUsers;
	  }
	  extractChatResult() {
	    const chat = this.response[im_v2_const.RestMethod.imChatGet];
	    this.chatId = chat.id;
	    this.dialogId = chat.dialog_id;
	    if (!this.dialogues[chat.dialog_id]) {
	      this.dialogues[chat.dialog_id] = chat;
	    }
	  }
	  extractUserResult() {
	    // solo user for group chats
	    const soloUser = this.response[im_v2_const.RestMethod.imUserGet];
	    if (soloUser) {
	      this.rawUsers = [soloUser];
	      return;
	    }

	    // two users for 1v1
	    const userList = this.response[im_v2_const.RestMethod.imUserListGet];
	    if (userList) {
	      this.rawUsers = Object.values(userList);
	    }
	  }
	  extractMessageListResult() {
	    const messageList = this.response[im_v2_const.RestMethod.imV2ChatMessageList];
	    if (!messageList) {
	      return;
	    }
	    this.extractPaginationFlags(messageList);
	    this.extractMessages(messageList);
	    this.extractReactions(messageList);
	    this.extractAdditionalUsers(messageList);
	  }
	  extractPaginationFlags(data) {
	    const {
	      hasPrevPage,
	      hasNextPage
	    } = data;
	    this.dialogues[this.dialogId] = {
	      ...this.dialogues[this.dialogId],
	      hasPrevPage,
	      hasNextPage
	    };
	  }
	  extractContextResult() {
	    const contextMessageList = this.response[im_v2_const.RestMethod.imV2ChatMessageGetContext];
	    if (!contextMessageList) {
	      return;
	    }
	    this.extractPaginationFlags(contextMessageList);
	    this.extractMessages(contextMessageList);
	    this.extractReactions(contextMessageList);
	    this.extractAdditionalUsers(contextMessageList);
	  }
	  extractReactions(data) {
	    const {
	      reactions
	    } = data;
	    this.reactions = reactions;
	  }
	  extractAdditionalUsers(data) {
	    const {
	      usersShort
	    } = data;
	    this.additionalUsers = usersShort;
	  }
	  extractPinnedMessagesResult() {
	    const pinMessageList = this.response[im_v2_const.RestMethod.imV2ChatPinTail];
	    if (!pinMessageList) {
	      return;
	    }
	    const {
	      list = [],
	      users: pinnedUsers = [],
	      files: pinnedFiles = []
	    } = pinMessageList;
	    this.rawUsers = [...this.rawUsers, ...pinnedUsers];
	    pinnedFiles.forEach(file => {
	      this.files[file.id] = file;
	    });
	    list.forEach(pinnedItem => {
	      this.pinnedMessageIds.push(pinnedItem.messageId);
	      this.messagesToStore[pinnedItem.message.id] = pinnedItem.message;
	    });
	  }
	  extractMessages(data) {
	    const {
	      messages,
	      users,
	      files
	    } = data;
	    files.forEach(file => {
	      this.files[file.id] = file;
	    });
	    messages.forEach(message => {
	      this.messages[message.id] = message;
	    });
	    this.rawUsers = [...this.rawUsers, ...users];
	  }
	  fillChatsForUsers() {
	    this.rawUsers.forEach(user => {
	      if (!this.dialogues[user.id]) {
	        this.dialogues[user.id] = im_v2_lib_user.UserManager.getDialogForUser(user);
	      } else {
	        this.dialogues[user.id] = {
	          ...this.dialogues[user.id],
	          ...im_v2_lib_user.UserManager.getDialogForUser(user)
	        };
	      }
	    });
	  }
	}

	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _loadChatRequest = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadChatRequest");
	var _updateModels = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateModels");
	var _prepareLoadChatQuery = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareLoadChatQuery");
	var _prepareLoadChatWithMessagesQuery = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareLoadChatWithMessagesQuery");
	var _prepareLoadChatWithContextQuery = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareLoadChatWithContextQuery");
	class LoadService {
	  constructor() {
	    Object.defineProperty(this, _prepareLoadChatWithContextQuery, {
	      value: _prepareLoadChatWithContextQuery2
	    });
	    Object.defineProperty(this, _prepareLoadChatWithMessagesQuery, {
	      value: _prepareLoadChatWithMessagesQuery2
	    });
	    Object.defineProperty(this, _prepareLoadChatQuery, {
	      value: _prepareLoadChatQuery2
	    });
	    Object.defineProperty(this, _updateModels, {
	      value: _updateModels2
	    });
	    Object.defineProperty(this, _loadChatRequest, {
	      value: _loadChatRequest2
	    });
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	  }
	  loadChat(dialogId) {
	    if (!main_core.Type.isStringFilled(dialogId)) {
	      return Promise.reject(new Error('ChatService: loadChat: dialogId is not provided'));
	    }
	    const query = babelHelpers.classPrivateFieldLooseBase(this, _prepareLoadChatQuery)[_prepareLoadChatQuery](dialogId);
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadChatRequest)[_loadChatRequest](dialogId, query);
	  }
	  loadChatWithMessages(dialogId) {
	    if (!main_core.Type.isStringFilled(dialogId)) {
	      return Promise.reject(new Error('ChatService: loadChatWithMessages: dialogId is not provided'));
	    }
	    const query = babelHelpers.classPrivateFieldLooseBase(this, _prepareLoadChatWithMessagesQuery)[_prepareLoadChatWithMessagesQuery](dialogId);
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadChatRequest)[_loadChatRequest](dialogId, query);
	  }
	  loadChatWithContext(dialogId, messageId) {
	    if (!main_core.Type.isStringFilled(dialogId)) {
	      return Promise.reject(new Error('ChatService: loadChatWithContext: dialogId is not provided'));
	    }
	    if (!messageId || !main_core.Type.isNumber(messageId)) {
	      return Promise.reject(new Error('ChatService: loadChatWithContext: messageId is not provided'));
	    }
	    const query = babelHelpers.classPrivateFieldLooseBase(this, _prepareLoadChatWithContextQuery)[_prepareLoadChatWithContextQuery](dialogId, messageId);
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadChatRequest)[_loadChatRequest](dialogId, query);
	  }
	}
	function _loadChatRequest2(dialogId, query) {
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('dialogues/update', {
	    dialogId: dialogId,
	    fields: {
	      loading: true
	    }
	  });
	  return im_v2_lib_rest.callBatch(query).then(data => {
	    return babelHelpers.classPrivateFieldLooseBase(this, _updateModels)[_updateModels](data);
	  }).then(() => {
	    return babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('dialogues/update', {
	      dialogId: dialogId,
	      fields: {
	        inited: true,
	        loading: false
	      }
	    });
	  });
	}
	function _updateModels2(response) {
	  const extractor = new ChatDataExtractor(response);
	  extractor.extractData();
	  if (extractor.isOpenlinesChat()) {
	    return Promise.reject('OL chats are not supported');
	  }
	  const dialoguesPromise = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('dialogues/set', extractor.getDialogues());
	  const filesPromise = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('files/set', extractor.getFiles());
	  const userManager = new im_v2_lib_user.UserManager();
	  const usersPromise = [babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('users/set', extractor.getUsers()), userManager.addUsersToModel(extractor.getAdditionalUsers())];
	  const messagesPromise = [babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/setChatCollection', {
	    messages: extractor.getMessages(),
	    clearCollection: true
	  }), babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/store', extractor.getMessagesToStore()), babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/pin/setPinned', {
	    chatId: extractor.getChatId(),
	    pinnedMessages: extractor.getPinnedMessages()
	  }), babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/reactions/set', extractor.getReactions())];
	  return Promise.all([dialoguesPromise, filesPromise, Promise.all(usersPromise), Promise.all(messagesPromise)]);
	}
	function _prepareLoadChatQuery2(dialogId) {
	  const query = {
	    [im_v2_const.RestMethod.imChatGet]: {
	      dialog_id: dialogId
	    }
	  };
	  const isChat = dialogId.toString().startsWith('chat');
	  if (isChat) {
	    query[im_v2_const.RestMethod.imUserGet] = {};
	  } else {
	    query[im_v2_const.RestMethod.imUserListGet] = {
	      id: [im_v2_application_core.Core.getUserId(), dialogId]
	    };
	  }
	  return query;
	}
	function _prepareLoadChatWithMessagesQuery2(dialogId) {
	  const query = babelHelpers.classPrivateFieldLooseBase(this, _prepareLoadChatQuery)[_prepareLoadChatQuery](dialogId);
	  query[im_v2_const.RestMethod.imV2ChatMessageList] = {
	    dialogId,
	    limit: im_v2_provider_service.MessageService.getMessageRequestLimit()
	  };
	  query[im_v2_const.RestMethod.imV2ChatPinTail] = {
	    chatId: `$result[${im_v2_const.RestMethod.imChatGet}][id]`
	  };
	  return query;
	}
	function _prepareLoadChatWithContextQuery2(dialogId, messageId) {
	  const query = babelHelpers.classPrivateFieldLooseBase(this, _prepareLoadChatQuery)[_prepareLoadChatQuery](dialogId);
	  query[im_v2_const.RestMethod.imV2ChatMessageGetContext] = {
	    id: messageId,
	    range: im_v2_provider_service.MessageService.getMessageRequestLimit()
	  };
	  query[im_v2_const.RestMethod.imV2ChatMessageRead] = {
	    dialogId,
	    ids: [messageId]
	  };
	  return query;
	}

	const PRIVATE_CHAT = 'CHAT';
	const OPEN_CHAT = 'OPEN';
	var _restClient = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _store$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _addChatToModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addChatToModel");
	class CreateService {
	  constructor() {
	    Object.defineProperty(this, _addChatToModel, {
	      value: _addChatToModel2
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
	  createChat(chatConfig) {
	    im_v2_lib_logger.Logger.warn('ChatService: createChat', chatConfig);
	    return babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient].callMethod(im_v2_const.RestMethod.imV2ChatAdd, {
	      fields: {
	        title: chatConfig.title,
	        description: chatConfig.description,
	        users: chatConfig.members,
	        ownerId: chatConfig.ownerId,
	        searchable: chatConfig.isAvailableInSearch ? 'Y' : 'N'
	      }
	    }).then(result => {
	      const {
	        chatId: newChatId
	      } = result.data();
	      im_v2_lib_logger.Logger.warn('ChatService: createChat result', newChatId);
	      const newDialogId = `chat${newChatId}`;
	      babelHelpers.classPrivateFieldLooseBase(this, _addChatToModel)[_addChatToModel](newDialogId, chatConfig);
	      return newDialogId;
	    }).catch(error => {
	      console.error('ChatService: createChat error:', error);
	      throw new Error(error);
	    });
	  }
	}
	function _addChatToModel2(newDialogId, chatConfig) {
	  const chatType = chatConfig.isAvailableInSearch ? OPEN_CHAT : PRIVATE_CHAT;
	  babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('dialogues/set', {
	    dialogId: newDialogId,
	    type: chatType.toLowerCase(),
	    name: chatConfig.title,
	    userCounter: chatConfig.members.length
	  });
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
	    const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$2)[_store$2].getters['dialogues/get'](dialogId);
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
	  babelHelpers.classPrivateFieldLooseBase(this, _store$2)[_store$2].dispatch('dialogues/update', {
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
	    babelHelpers.classPrivateFieldLooseBase(this, _store$3)[_store$3].dispatch('dialogues/mute', {
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
	    babelHelpers.classPrivateFieldLooseBase(this, _store$3)[_store$3].dispatch('dialogues/unmute', {
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
	    const actionType = action === 'Y' ? 'dialogues/unmute' : 'dialogues/mute';
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
	    babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('dialogues/clearCounters');
	    babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('recent/clearUnread');
	    return babelHelpers.classPrivateFieldLooseBase(this, _restClient$4)[_restClient$4].callMethod(im_v2_const.RestMethod.imV2ChatReadAll).catch(error => {
	      console.error('ReadService: readAll error', error);
	    });
	  }
	  readDialog(dialogId) {
	    im_v2_lib_logger.Logger.warn('ReadService: readDialog', dialogId);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('recent/unread', {
	      id: dialogId,
	      action: false
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('dialogues/update', {
	      dialogId,
	      fields: {
	        counter: 0
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$4)[_restClient$4].callMethod(im_v2_const.RestMethod.imV2ChatRead, {
	      dialogId
	    }).catch(error => {
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
	      Object.entries(babelHelpers.classPrivateFieldLooseBase(this, _messagesToRead)[_messagesToRead]).forEach(([queueChatId, messageIds]) => {
	        queueChatId = +queueChatId;
	        im_v2_lib_logger.Logger.warn('ReadService: readMessages', messageIds);
	        if (messageIds.size === 0) {
	          return;
	        }
	        const copiedMessageIds = [...messageIds];
	        delete babelHelpers.classPrivateFieldLooseBase(this, _messagesToRead)[_messagesToRead][queueChatId];
	        babelHelpers.classPrivateFieldLooseBase(this, _readMessageOnClient)[_readMessageOnClient](queueChatId, copiedMessageIds).then(readMessagesCount => {
	          im_v2_lib_logger.Logger.warn('ReadService: readMessage, need to reduce counter by', readMessagesCount);
	          return babelHelpers.classPrivateFieldLooseBase(this, _decreaseChatCounter)[_decreaseChatCounter](queueChatId, readMessagesCount);
	        }).then(() => {
	          return babelHelpers.classPrivateFieldLooseBase(this, _readMessageOnServer)[_readMessageOnServer](queueChatId, copiedMessageIds);
	        }).then(readResult => {
	          babelHelpers.classPrivateFieldLooseBase(this, _checkChatCounter)[_checkChatCounter](readResult);
	        }).catch(error => {
	          console.error('ReadService: error reading message', error);
	        });
	      });
	    }, READ_TIMEOUT);
	  }
	  clearDialogMark(dialogId) {
	    im_v2_lib_logger.Logger.warn('ReadService: clear dialog mark', dialogId);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('recent/unread', {
	      id: dialogId,
	      action: false
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('dialogues/update', {
	      dialogId,
	      fields: {
	        markedId: 0
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$4)[_restClient$4].callMethod(im_v2_const.RestMethod.imV2ChatRead, {
	      dialogId,
	      onlyRecent: true
	    }).catch(error => {
	      console.error('ReadService: error clearing dialog mark', error);
	    });
	  }
	}
	function _readMessageOnClient2(chatId, messageIds) {
	  const maxMessageId = Math.max(...messageIds);
	  const dialog = babelHelpers.classPrivateFieldLooseBase(this, _getDialogByChatId)[_getDialogByChatId](chatId);
	  if (maxMessageId > dialog.lastReadId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('dialogues/update', {
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
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('dialogues/decreaseCounter', {
	    dialogId: babelHelpers.classPrivateFieldLooseBase(this, _getDialogIdByChatId)[_getDialogIdByChatId](chatId),
	    count: readMessagesCount
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
	    babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('dialogues/update', {
	      dialogId: dialog.dialogId,
	      fields: {
	        counter
	      }
	    });
	  }
	}
	function _getDialogIdByChatId2(chatId) {
	  const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].getters['dialogues/getByChatId'](chatId);
	  if (!dialog) {
	    return 0;
	  }
	  return dialog.dialogId;
	}
	function _getDialogByChatId2(chatId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].getters['dialogues/getByChatId'](chatId);
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
	    const chatId = dialogId.slice(4);
	    const queryParams = {
	      user_id: userId,
	      chat_id: chatId
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$5)[_restClient$5].callMethod(im_v2_const.RestMethod.imChatUserDelete, queryParams).catch(error => {
	      console.error('Im.Lib.Menu: error kicking user from chat', error);
	    });
	  }
	  leaveChat(dialogId) {
	    this.kickUserFromChat(dialogId, im_v2_application_core.Core.getUserId());
	    babelHelpers.classPrivateFieldLooseBase(this, _store$6)[_store$6].dispatch('recent/delete', {
	      id: dialogId
	    });
	    const chatIsOpened = babelHelpers.classPrivateFieldLooseBase(this, _store$6)[_store$6].getters['application/isChatOpen'](dialogId);
	    if (chatIsOpened) {
	      im_public.Messenger.openChat();
	    }
	  }
	}

	var _loadService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadService");
	var _createService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createService");
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
	  // endregion 'load'

	  // region 'create'
	  createChat(chatConfig) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _createService)[_createService].createChat(chatConfig);
	  }
	  // endregion 'create'

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
	  // endregion 'user
	}
	function _initServices2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService] = new LoadService();
	  babelHelpers.classPrivateFieldLooseBase(this, _createService)[_createService] = new CreateService();
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
	      }
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
	      }
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
	    }).finally(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	    });
	  }
	  reloadMessageList() {
	    im_v2_lib_logger.Logger.warn('MessageService: loadChatOnExit for: ', babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId]);
	    let targetMessageId = 0;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().markedId) {
	      targetMessageId = babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().markedId;
	    } else if (babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().savedPositionMessageId) {
	      targetMessageId = babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().savedPositionMessageId;
	    }
	    const wasInitedBefore = babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().inited;
	    babelHelpers.classPrivateFieldLooseBase(this, _setDialogInited)[_setDialogInited](false);
	    if (targetMessageId) {
	      return this.loadContext(targetMessageId).then(() => {
	        babelHelpers.classPrivateFieldLooseBase(this, _setDialogInited)[_setDialogInited](true, wasInitedBefore);
	      });
	    }
	    return this.loadInitialMessages().then(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _setDialogInited)[_setDialogInited](true, wasInitedBefore);
	    });
	  }
	  loadInitialMessages() {
	    im_v2_lib_logger.Logger.warn('MessageService: loadInitialMessages for: ', babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId]);
	    babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = true;
	    return babelHelpers.classPrivateFieldLooseBase(this, _restClient$6)[_restClient$6].callMethod(im_v2_const.RestMethod.imV2ChatMessageList, {
	      chatId: babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId],
	      limit: LoadService$1.MESSAGE_REQUEST_LIMIT
	    }).then(result => {
	      im_v2_lib_logger.Logger.warn('MessageService: loadInitialMessages result', result.data());
	      return babelHelpers.classPrivateFieldLooseBase(this, _handleLoadedMessages)[_handleLoadedMessages](result.data());
	    }).then(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	      return true;
	    }).catch(error => {
	      console.error('MessageService: loadInitialMessages error:', error);
	      babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	    });
	  }
	  isLoading() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading];
	  }
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
	    hasNextPage
	  } = rawData;
	  const dialogPromise = babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('dialogues/update', {
	    dialogId: babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().dialogId,
	    fields: {
	      hasPrevPage,
	      hasNextPage
	    }
	  });
	  const usersPromise = Promise.all([babelHelpers.classPrivateFieldLooseBase(this, _userManager)[_userManager].setUsersToModel(users), babelHelpers.classPrivateFieldLooseBase(this, _userManager)[_userManager].addUsersToModel(usersShort)]);
	  const filesPromise = babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('files/set', files);
	  const reactionsPromise = babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('messages/reactions/set', reactions);
	  return Promise.all([dialogPromise, filesPromise, usersPromise, reactionsPromise]);
	}
	function _setDialogInited2(flag, wasInitedBefore = true) {
	  const fields = {
	    inited: flag,
	    loading: !flag
	  };
	  if (flag === true && !wasInitedBefore) {
	    delete fields.inited;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('dialogues/update', {
	    dialogId: babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().dialogId,
	    fields
	  });
	}
	function _getDialog2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].getters['dialogues/getByChatId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId]);
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

	var _store$9 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$8 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _chatId$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatId");
	class EditService {
	  constructor(chatId) {
	    Object.defineProperty(this, _store$9, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$8, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _chatId$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _chatId$1)[_chatId$1] = chatId;
	    babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$8)[_restClient$8] = im_v2_application_core.Core.getRestClient();
	  }
	  editMessageText(messageId, text) {
	    im_v2_lib_logger.Logger.warn('MessageService: editMessageText', messageId, text);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].dispatch('messages/update', {
	      id: messageId,
	      fields: {
	        text,
	        isEdited: true
	      }
	    });
	    const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].getters['dialogues/getByChatId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId$1)[_chatId$1]);
	    if (messageId === dialog.lastMessageId) {
	      babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].dispatch('recent/update', {
	        id: dialog.dialogId,
	        fields: {
	          message: {
	            text
	          }
	        }
	      });
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$8)[_restClient$8].callMethod(im_v2_const.RestMethod.imMessageUpdate, {
	      'ID': messageId,
	      'MESSAGE': text
	    }).catch(error => {
	      console.error('MessageService: editMessageText error:', error);
	    });
	  }
	}

	var _store$a = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$9 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _chatId$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatId");
	class DeleteService {
	  constructor(chatId) {
	    Object.defineProperty(this, _store$a, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$9, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _chatId$2, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _chatId$2)[_chatId$2] = chatId;
	    babelHelpers.classPrivateFieldLooseBase(this, _store$a)[_store$a] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$9)[_restClient$9] = im_v2_application_core.Core.getRestClient();
	  }
	  deleteMessage(messageId) {
	    im_v2_lib_logger.Logger.warn('MessageService: deleteMessage', messageId);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$a)[_store$a].dispatch('messages/update', {
	      id: messageId,
	      fields: {
	        text: '',
	        params: {
	          'IS_DELETED': 'Y',
	          'FILE_ID': []
	        }
	      }
	    });
	    const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$a)[_store$a].getters['dialogues/getByChatId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId$2)[_chatId$2]);
	    if (messageId === dialog.lastMessageId) {
	      babelHelpers.classPrivateFieldLooseBase(this, _store$a)[_store$a].dispatch('recent/update', {
	        id: dialog.dialogId,
	        fields: {
	          message: {
	            text: ''
	          }
	        }
	      });
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$9)[_restClient$9].callMethod(im_v2_const.RestMethod.imMessageDelete, {
	      'ID': messageId
	    }).catch(error => {
	      console.error('MessageService: deleteMessage error:', error);
	    });
	  }
	}

	var _chatId$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatId");
	var _store$b = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$a = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	class MarkService {
	  constructor(chatId) {
	    Object.defineProperty(this, _chatId$3, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _store$b, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$a, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _chatId$3)[_chatId$3] = chatId;
	    babelHelpers.classPrivateFieldLooseBase(this, _store$b)[_store$b] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$a)[_restClient$a] = im_v2_application_core.Core.getRestClient();
	  }
	  markMessage(messageId) {
	    im_v2_lib_logger.Logger.warn('MessageService: markMessage', messageId);
	    const {
	      dialogId
	    } = babelHelpers.classPrivateFieldLooseBase(this, _store$b)[_store$b].getters['dialogues/getByChatId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId$3)[_chatId$3]);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$b)[_store$b].dispatch('recent/unread', {
	      id: dialogId,
	      action: true
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$b)[_store$b].dispatch('dialogues/update', {
	      dialogId,
	      fields: {
	        markedId: messageId
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$a)[_restClient$a].callMethod(im_v2_const.RestMethod.imV2ChatMessageMark, {
	      dialogId,
	      id: messageId
	    }).catch(error => {
	      console.error('MessageService: error marking message', error);
	    });
	  }
	}

	var _chatId$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatId");
	var _store$c = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$b = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	class FavoriteService {
	  constructor(chatId) {
	    Object.defineProperty(this, _chatId$4, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _store$c, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$b, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _chatId$4)[_chatId$4] = chatId;
	    babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$b)[_restClient$b] = im_v2_application_core.Core.getRestClient();
	  }
	  addMessageToFavorite(messageId) {
	    im_v2_lib_logger.Logger.warn('MessageService: addMessageToFavorite', messageId);
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$b)[_restClient$b].callMethod(im_v2_const.RestMethod.imChatFavoriteAdd, {
	      MESSAGE_ID: messageId
	    }).catch(error => {
	      console.error('MessageService: error adding message to favorite', error);
	    });
	    BX.UI.Notification.Center.notify({
	      content: main_core.Loc.getMessage('IM_MESSAGE_SERVICE_SAVE_MESSAGE_SUCCESS')
	    });
	  }
	  removeMessageFromFavorite(messageId) {
	    im_v2_lib_logger.Logger.warn('MessageService: removeMessageFromFavorite', messageId);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c].dispatch('sidebar/favorites/deleteByMessageId', {
	      chatId: babelHelpers.classPrivateFieldLooseBase(this, _chatId$4)[_chatId$4],
	      messageId: messageId
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$b)[_restClient$b].callMethod(im_v2_const.RestMethod.imChatFavoriteDelete, {
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
	  babelHelpers.classPrivateFieldLooseBase(this, _editService)[_editService] = new EditService(chatId);
	  babelHelpers.classPrivateFieldLooseBase(this, _deleteService)[_deleteService] = new DeleteService(chatId);
	  babelHelpers.classPrivateFieldLooseBase(this, _pinService$1)[_pinService$1] = new PinService$1();
	  babelHelpers.classPrivateFieldLooseBase(this, _markService)[_markService] = new MarkService(chatId);
	  babelHelpers.classPrivateFieldLooseBase(this, _favoriteService)[_favoriteService] = new FavoriteService(chatId);
	}

	var _uploader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploader");
	var _store$d = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$c = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _getFilePreview = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFilePreview");
	var _onStartUpload = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onStartUpload");
	var _onProgress = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onProgress");
	var _onComplete = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onComplete");
	var _onUploadError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onUploadError");
	var _onUploadCancel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onUploadCancel");
	class UploadManager extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    Object.defineProperty(this, _onUploadCancel, {
	      value: _onUploadCancel2
	    });
	    Object.defineProperty(this, _onUploadError, {
	      value: _onUploadError2
	    });
	    Object.defineProperty(this, _onComplete, {
	      value: _onComplete2
	    });
	    Object.defineProperty(this, _onProgress, {
	      value: _onProgress2
	    });
	    Object.defineProperty(this, _onStartUpload, {
	      value: _onStartUpload2
	    });
	    Object.defineProperty(this, _getFilePreview, {
	      value: _getFilePreview2
	    });
	    Object.defineProperty(this, _uploader, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _store$d, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$c, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$c)[_restClient$c] = im_v2_application_core.Core.getRestClient();
	    this.setEventNamespace(UploadManager.eventNamespace);
	    this.onUploadCancelHandler = babelHelpers.classPrivateFieldLooseBase(this, _onUploadCancel)[_onUploadCancel].bind(this);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.uploader.cancel, this.onUploadCancelHandler);
	    this.initUploader();
	  }
	  initUploader() {
	    babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader] = new im_v2_lib_uploader.Uploader();
	    babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].subscribe(im_v2_lib_uploader.Uploader.EVENTS.startUpload, babelHelpers.classPrivateFieldLooseBase(this, _onStartUpload)[_onStartUpload].bind(this));
	    babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].subscribe(im_v2_lib_uploader.Uploader.EVENTS.progressUpdate, babelHelpers.classPrivateFieldLooseBase(this, _onProgress)[_onProgress].bind(this));
	    babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].subscribe(im_v2_lib_uploader.Uploader.EVENTS.complete, babelHelpers.classPrivateFieldLooseBase(this, _onComplete)[_onComplete].bind(this));
	    babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].subscribe(im_v2_lib_uploader.Uploader.EVENTS.fileMaxSizeExceeded, babelHelpers.classPrivateFieldLooseBase(this, _onUploadError)[_onUploadError].bind(this));
	    babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].subscribe(im_v2_lib_uploader.Uploader.EVENTS.uploadFileError, babelHelpers.classPrivateFieldLooseBase(this, _onUploadError)[_onUploadError].bind(this));
	    babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].subscribe(im_v2_lib_uploader.Uploader.EVENTS.createFileError, babelHelpers.classPrivateFieldLooseBase(this, _onUploadError)[_onUploadError].bind(this));
	  }
	  addUploadTask(temporaryFileId, file, diskFolderId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getFilePreview)[_getFilePreview](file).then(({
	      preview
	    }) => {
	      const previewBlob = preview ? {
	        previewBlob: preview.blob
	      } : {};
	      babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].addTask({
	        taskId: temporaryFileId,
	        fileData: file,
	        fileName: file.name,
	        diskFolderId: diskFolderId,
	        generateUniqueName: true,
	        ...previewBlob
	      });
	      return {
	        taskId: temporaryFileId,
	        file: file,
	        preview: preview
	      };
	    });
	  }
	  cancel(taskId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].deleteTask(taskId);
	  }
	  destroy() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.uploader.cancel, this.onUploadCancelHandler);
	  }
	}
	function _getFilePreview2(file) {
	  return im_v2_lib_uploader.PreviewManager.get(file).then(preview => {
	    return {
	      preview
	    };
	  }).catch(error => {
	    console.warn(`Couldn't get preview for file ${file.name}. Error: ${error}`);
	    return {};
	  });
	}
	function _onStartUpload2(event) {
	  this.emit(UploadManager.events.onFileUploadProgress, event);
	}
	function _onProgress2(event) {
	  this.emit(UploadManager.events.onFileUploadProgress, event);
	}
	function _onComplete2(event) {
	  this.emit(UploadManager.events.onFileUploadComplete, event);
	}
	function _onUploadError2(event) {
	  this.emit(UploadManager.events.onFileUploadError, event);
	}
	function _onUploadCancel2(event) {
	  this.emit(UploadManager.events.onFileUploadCancel, event);
	}
	UploadManager.eventNamespace = 'BX.Messenger.v2.Textarea.UploadManager';
	UploadManager.events = {
	  onFileUploadProgress: 'onFileUploadProgress',
	  onFileUploadComplete: 'onFileUploadComplete',
	  onFileUploadError: 'onFileUploadError',
	  onFileUploadCancel: 'onFileUploadCancel'
	};

	var _store$e = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$d = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _isRequestingDiskFolderId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isRequestingDiskFolderId");
	var _diskFolderIdRequestPromise = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("diskFolderIdRequestPromise");
	var _uploadManager = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploadManager");
	var _uploadRegistry = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploadRegistry");
	var _addFileFromDiskToModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addFileFromDiskToModel");
	var _initUploadManager = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initUploadManager");
	var _requestDiskFolderId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("requestDiskFolderId");
	var _updateFileProgress = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateFileProgress");
	var _cancelUpload = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cancelUpload");
	var _addFileToModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addFileToModel");
	var _getDiskFolderId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDiskFolderId");
	var _getFileType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFileType");
	var _getFileExtension = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFileExtension");
	var _getDialog$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialog");
	var _getCurrentUser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getCurrentUser");
	var _addFileToUploadRegistry = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addFileToUploadRegistry");
	var _getChatId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getChatId");
	class FileService extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    Object.defineProperty(this, _getChatId, {
	      value: _getChatId2
	    });
	    Object.defineProperty(this, _addFileToUploadRegistry, {
	      value: _addFileToUploadRegistry2
	    });
	    Object.defineProperty(this, _getCurrentUser, {
	      value: _getCurrentUser2
	    });
	    Object.defineProperty(this, _getDialog$1, {
	      value: _getDialog2$1
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
	    Object.defineProperty(this, _addFileToModel, {
	      value: _addFileToModel2
	    });
	    Object.defineProperty(this, _cancelUpload, {
	      value: _cancelUpload2
	    });
	    Object.defineProperty(this, _updateFileProgress, {
	      value: _updateFileProgress2
	    });
	    Object.defineProperty(this, _requestDiskFolderId, {
	      value: _requestDiskFolderId2
	    });
	    Object.defineProperty(this, _initUploadManager, {
	      value: _initUploadManager2
	    });
	    Object.defineProperty(this, _addFileFromDiskToModel, {
	      value: _addFileFromDiskToModel2
	    });
	    Object.defineProperty(this, _store$e, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$d, {
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
	    Object.defineProperty(this, _uploadManager, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _uploadRegistry, {
	      writable: true,
	      value: {}
	    });
	    this.setEventNamespace(FileService.eventNamespace);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$d)[_restClient$d] = im_v2_application_core.Core.getRestClient();
	    babelHelpers.classPrivateFieldLooseBase(this, _uploadManager)[_uploadManager] = new UploadManager();
	    babelHelpers.classPrivateFieldLooseBase(this, _initUploadManager)[_initUploadManager]();
	  }
	  uploadFile(messageWithFile) {
	    const {
	      temporaryFileId,
	      rawFile,
	      diskFolderId
	    } = messageWithFile;
	    babelHelpers.classPrivateFieldLooseBase(this, _addFileToUploadRegistry)[_addFileToUploadRegistry](temporaryFileId, messageWithFile);
	    return babelHelpers.classPrivateFieldLooseBase(this, _uploadManager)[_uploadManager].addUploadTask(temporaryFileId, rawFile, diskFolderId).then(uploadTask => {
	      return babelHelpers.classPrivateFieldLooseBase(this, _addFileToModel)[_addFileToModel](uploadTask);
	    });
	  }
	  uploadFileFromDisk(messageWithFile) {
	    const {
	      temporaryFileId,
	      rawFile
	    } = messageWithFile;
	    babelHelpers.classPrivateFieldLooseBase(this, _addFileToUploadRegistry)[_addFileToUploadRegistry](temporaryFileId, messageWithFile);
	    return babelHelpers.classPrivateFieldLooseBase(this, _addFileFromDiskToModel)[_addFileFromDiskToModel](temporaryFileId, rawFile);
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
	      realFileId,
	      fromDisk
	    } = params;
	    const messageWithFile = this.getMessageWithFile(temporaryFileId);
	    const fileIdParams = {};
	    if (fromDisk) {
	      fileIdParams.disk_id = realFileId;
	    } else {
	      fileIdParams.upload_id = realFileId;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$d)[_restClient$d].callMethod(im_v2_const.RestMethod.imDiskFileCommit, {
	      chat_id: messageWithFile.chatId,
	      message: '',
	      // we don't have feature to send files with text right now
	      template_id: messageWithFile.temporaryMessageId,
	      file_template_id: temporaryFileId,
	      ...fileIdParams
	    }).catch(error => {
	      console.error('fileCommit error', error);
	    });
	  }
	  getMessageWithFile(taskId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _uploadRegistry)[_uploadRegistry][taskId];
	  }
	  destroy() {
	    babelHelpers.classPrivateFieldLooseBase(this, _uploadManager)[_uploadManager].destroy();
	  }
	}
	function _addFileFromDiskToModel2(id, file) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].dispatch('files/add', {
	    id: id,
	    chatId: this.getMessageWithFile(id).chatId,
	    authorId: im_v2_application_core.Core.getUserId(),
	    name: file.name,
	    type: im_v2_lib_utils.Utils.file.getFileTypeByExtension(file.ext),
	    extension: file.ext,
	    size: file.sizeInt,
	    status: im_v2_const.FileStatus.wait,
	    progress: 0,
	    authorName: babelHelpers.classPrivateFieldLooseBase(this, _getCurrentUser)[_getCurrentUser]().name
	  });
	}
	function _initUploadManager2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _uploadManager)[_uploadManager] = new UploadManager();
	  babelHelpers.classPrivateFieldLooseBase(this, _uploadManager)[_uploadManager].subscribe(UploadManager.events.onFileUploadProgress, event => {
	    const {
	      task
	    } = event.getData();
	    babelHelpers.classPrivateFieldLooseBase(this, _updateFileProgress)[_updateFileProgress](task.taskId, task.progress, im_v2_const.FileStatus.upload);
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _uploadManager)[_uploadManager].subscribe(UploadManager.events.onFileUploadComplete, event => {
	    const {
	      task,
	      result
	    } = event.getData();
	    babelHelpers.classPrivateFieldLooseBase(this, _updateFileProgress)[_updateFileProgress](task.taskId, task.progress, im_v2_const.FileStatus.wait);
	    this.commitFile({
	      temporaryFileId: task.taskId,
	      realFileId: result.data.file.id,
	      fromDisk: false
	    });
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _uploadManager)[_uploadManager].subscribe(UploadManager.events.onFileUploadError, event => {
	    const {
	      task
	    } = event.getData();
	    babelHelpers.classPrivateFieldLooseBase(this, _updateFileProgress)[_updateFileProgress](task.taskId, 0, im_v2_const.FileStatus.error);
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _uploadManager)[_uploadManager].subscribe(UploadManager.events.onFileUploadCancel, event => {
	    const {
	      taskId
	    } = event.getData();
	    babelHelpers.classPrivateFieldLooseBase(this, _cancelUpload)[_cancelUpload](taskId);
	  });
	}
	function _requestDiskFolderId2(dialogId) {
	  return new Promise((resolve, reject) => {
	    babelHelpers.classPrivateFieldLooseBase(this, _isRequestingDiskFolderId)[_isRequestingDiskFolderId] = true;
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$d)[_restClient$d].callMethod(im_v2_const.RestMethod.imDiskFolderGet, {
	      chat_id: babelHelpers.classPrivateFieldLooseBase(this, _getChatId)[_getChatId](dialogId)
	    }).then(response => {
	      const {
	        ID: diskFolderId
	      } = response.data();
	      babelHelpers.classPrivateFieldLooseBase(this, _isRequestingDiskFolderId)[_isRequestingDiskFolderId] = false;
	      babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].commit('dialogues/update', {
	        dialogId: dialogId,
	        fields: {
	          diskFolderId: diskFolderId
	        }
	      });
	      resolve(diskFolderId);
	    }).catch(error => {
	      babelHelpers.classPrivateFieldLooseBase(this, _isRequestingDiskFolderId)[_isRequestingDiskFolderId] = false;
	      reject(error);
	    });
	  });
	}
	function _updateFileProgress2(id, progress, status) {
	  babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].dispatch('files/update', {
	    id: id,
	    fields: {
	      progress: progress === 100 ? 99 : progress,
	      status: status
	    }
	  });
	}
	function _cancelUpload2(taskId) {
	  const messageId = this.getMessageWithFile(taskId).temporaryMessageId;
	  babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].dispatch('messages/delete', {
	    id: messageId
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _uploadManager)[_uploadManager].cancel(taskId);
	}
	function _addFileToModel2(fileToUpload) {
	  const {
	    taskId,
	    file,
	    preview
	  } = fileToUpload;
	  const previewData = {};
	  if (preview.blob) {
	    previewData.image = {
	      width: preview.width,
	      height: preview.height
	    };
	    previewData.urlPreview = URL.createObjectURL(preview.blob);
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].dispatch('files/add', {
	    id: taskId,
	    chatId: this.getMessageWithFile(taskId).chatId,
	    authorId: im_v2_application_core.Core.getUserId(),
	    name: file.name,
	    type: babelHelpers.classPrivateFieldLooseBase(this, _getFileType)[_getFileType](file),
	    extension: babelHelpers.classPrivateFieldLooseBase(this, _getFileExtension)[_getFileExtension](file),
	    size: file.size,
	    status: im_v2_const.FileStatus.progress,
	    progress: 0,
	    authorName: babelHelpers.classPrivateFieldLooseBase(this, _getCurrentUser)[_getCurrentUser]().name,
	    ...previewData
	  });
	}
	function _getDiskFolderId2(dialogId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _getDialog$1)[_getDialog$1](dialogId).diskFolderId;
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
	function _getDialog2$1(dialogId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].getters['dialogues/get'](dialogId);
	}
	function _getCurrentUser2() {
	  const userId = im_v2_application_core.Core.getUserId();
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].getters['users/get'](userId);
	}
	function _addFileToUploadRegistry2(fileId, fileToUpload) {
	  babelHelpers.classPrivateFieldLooseBase(this, _uploadRegistry)[_uploadRegistry][fileId] = {
	    chatId: babelHelpers.classPrivateFieldLooseBase(this, _getChatId)[_getChatId](fileToUpload.dialogId),
	    ...fileToUpload
	  };
	}
	function _getChatId2(dialogId) {
	  var _babelHelpers$classPr;
	  return (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _getDialog$1)[_getDialog$1](dialogId)) == null ? void 0 : _babelHelpers$classPr.chatId;
	}
	FileService.eventNamespace = 'BX.Messenger.v2.Textarea.UploadingService';
	FileService.events = {
	  sendMessageWithFile: 'sendMessageWithFile'
	};

	var _store$f = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _fileService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fileService");
	var _prepareMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareMessage");
	var _handlePagination = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handlePagination");
	var _addMessageToModels = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addMessageToModels");
	var _addMessageToRecent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addMessageToRecent");
	var _sendMessageToServer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendMessageToServer");
	var _updateMessageId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateMessageId");
	var _updateMessageError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateMessageError");
	var _sendScrollEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendScrollEvent");
	var _getDialog$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialog");
	class SendingService {
	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }
	  constructor() {
	    Object.defineProperty(this, _getDialog$2, {
	      value: _getDialog2$2
	    });
	    Object.defineProperty(this, _sendScrollEvent, {
	      value: _sendScrollEvent2
	    });
	    Object.defineProperty(this, _updateMessageError, {
	      value: _updateMessageError2
	    });
	    Object.defineProperty(this, _updateMessageId, {
	      value: _updateMessageId2
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
	    Object.defineProperty(this, _prepareMessage, {
	      value: _prepareMessage2
	    });
	    Object.defineProperty(this, _store$f, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _fileService, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _fileService)[_fileService] = new FileService();
	  }
	  sendMessage(params) {
	    const {
	      text = '',
	      fileId = '',
	      temporaryMessageId,
	      dialogId
	    } = params;
	    if (!main_core.Type.isStringFilled(text) && !main_core.Type.isStringFilled(fileId)) {
	      return;
	    }
	    im_v2_lib_logger.Logger.warn(`SendingService: sendMessage`, params);
	    const message = babelHelpers.classPrivateFieldLooseBase(this, _prepareMessage)[_prepareMessage]({
	      text,
	      fileId,
	      temporaryMessageId,
	      dialogId
	    });
	    return babelHelpers.classPrivateFieldLooseBase(this, _handlePagination)[_handlePagination](dialogId).then(() => {
	      return babelHelpers.classPrivateFieldLooseBase(this, _addMessageToModels)[_addMessageToModels](message);
	    }).then(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _sendScrollEvent)[_sendScrollEvent]({
	        force: true,
	        dialogId
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _sendMessageToServer)[_sendMessageToServer](message);
	    });
	  }
	  sendFilesFromInput(files, dialogId) {
	    if (files.length === 0) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _fileService)[_fileService].checkDiskFolderId(dialogId).then(diskFolderId => {
	      files.forEach(rawFile => {
	        const temporaryMessageId = im_v2_lib_utils.Utils.text.getUuidV4();
	        const temporaryFileId = im_v2_lib_utils.Utils.text.getUuidV4();
	        const fileToUpload = {
	          temporaryMessageId,
	          temporaryFileId,
	          rawFile,
	          diskFolderId,
	          dialogId
	        };
	        babelHelpers.classPrivateFieldLooseBase(this, _fileService)[_fileService].uploadFile(fileToUpload).then(() => {
	          this.sendMessage({
	            temporaryMessageId: temporaryMessageId,
	            fileId: temporaryFileId,
	            dialogId: dialogId
	          });
	        });
	      });
	    });
	  }
	  sendFilesFromDisk(files, dialogId) {
	    Object.values(files).forEach(file => {
	      const temporaryMessageId = im_v2_lib_utils.Utils.text.getUuidV4();
	      const realFileId = file.id.slice(1); //'n123' => '123'
	      const temporaryFileId = `${temporaryMessageId}|${realFileId}`;
	      babelHelpers.classPrivateFieldLooseBase(this, _fileService)[_fileService].uploadFileFromDisk({
	        temporaryMessageId,
	        temporaryFileId,
	        dialogId,
	        rawFile: file
	      }).then(() => {
	        return this.sendMessage({
	          temporaryMessageId,
	          fileId: temporaryFileId,
	          dialogId
	        });
	      }).then(() => {
	        babelHelpers.classPrivateFieldLooseBase(this, _fileService)[_fileService].commitFile({
	          temporaryFileId: temporaryFileId,
	          realFileId: realFileId,
	          fromDisk: true
	        });
	      });
	    });
	  }
	  destroy() {
	    babelHelpers.classPrivateFieldLooseBase(this, _fileService)[_fileService].destroy();
	  }
	}
	function _prepareMessage2(params) {
	  const {
	    text,
	    fileId,
	    temporaryMessageId,
	    dialogId
	  } = params;
	  const messageParams = {};
	  if (fileId) {
	    messageParams.FILE_ID = [fileId];
	  }
	  const temporaryId = temporaryMessageId || im_v2_lib_utils.Utils.text.getUuidV4();
	  return {
	    temporaryId,
	    chatId: babelHelpers.classPrivateFieldLooseBase(this, _getDialog$2)[_getDialog$2](dialogId).chatId,
	    dialogId: dialogId,
	    authorId: im_v2_application_core.Core.getUserId(),
	    text,
	    params: messageParams,
	    withFile: !!fileId,
	    unread: false,
	    sending: true
	  };
	}
	function _handlePagination2(dialogId) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _getDialog$2)[_getDialog$2](dialogId).hasNextPage) {
	    return Promise.resolve();
	  }
	  im_v2_lib_logger.Logger.warn('SendingService: sendMessage: there are unread pages, move to chat end');
	  const messageService = new im_v2_provider_service.MessageService({
	    chatId: babelHelpers.classPrivateFieldLooseBase(this, _getDialog$2)[_getDialog$2](dialogId).chatId
	  });
	  return messageService.loadContext(babelHelpers.classPrivateFieldLooseBase(this, _getDialog$2)[_getDialog$2](dialogId).lastMessageId).then(() => {
	    babelHelpers.classPrivateFieldLooseBase(this, _sendScrollEvent)[_sendScrollEvent]({
	      dialogId
	    });
	  }).catch(error => {
	    console.error('SendingService: loadContext error', error);
	  });
	}
	function _addMessageToModels2(message) {
	  babelHelpers.classPrivateFieldLooseBase(this, _addMessageToRecent)[_addMessageToRecent](message);
	  babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].dispatch('dialogues/clearLastMessageViews', {
	    dialogId: message.dialogId
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].dispatch('messages/add', message);
	}
	function _addMessageToRecent2(message) {
	  const recentItem = babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].getters['recent/get'](message.dialogId);
	  if (!recentItem || message.text === '') {
	    return false;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].dispatch('recent/update', {
	    id: message.dialogId,
	    fields: {
	      message: {
	        id: message.temporaryId,
	        text: message.text,
	        authorId: message.authorId,
	        status: im_v2_const.MessageStatus.received,
	        sending: true,
	        params: {
	          withFile: false,
	          withAttach: false
	        }
	      }
	    }
	  });
	}
	function _sendMessageToServer2(element) {
	  if (element.withFile) {
	    return;
	  }
	  const query = {
	    [im_v2_const.RestMethod.imMessageAdd]: {
	      template_id: element.temporaryId,
	      dialog_id: element.dialogId
	    },
	    [im_v2_const.RestMethod.imV2ChatRead]: {
	      dialogId: element.dialogId,
	      onlyRecent: true
	    }
	  };
	  if (element.text) {
	    query[im_v2_const.RestMethod.imMessageAdd].message = element.text;
	  }
	  im_v2_lib_rest.callBatch(query).then(result => {
	    im_v2_lib_logger.Logger.warn('SendingService: sendMessage result -', result[im_v2_const.RestMethod.imMessageAdd]);
	    babelHelpers.classPrivateFieldLooseBase(this, _updateMessageId)[_updateMessageId]({
	      oldId: element.temporaryId,
	      newId: result[im_v2_const.RestMethod.imMessageAdd],
	      dialogId: element.dialogId
	    });
	  }).catch(error => {
	    babelHelpers.classPrivateFieldLooseBase(this, _updateMessageError)[_updateMessageError](element.temporaryId);
	    console.error('SendingService: sendMessage error -', error);
	  });
	}
	function _updateMessageId2(params) {
	  const {
	    oldId,
	    newId,
	    dialogId
	  } = params;
	  babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].dispatch('messages/updateWithId', {
	    id: oldId,
	    fields: {
	      id: newId
	    }
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].dispatch('dialogues/update', {
	    dialogId: dialogId,
	    fields: {
	      lastId: newId,
	      lastMessageId: newId
	    }
	  });
	}
	function _updateMessageError2(messageId) {
	  babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].dispatch('messages/update', {
	    id: messageId,
	    fields: {
	      error: true
	    }
	  });
	}
	function _sendScrollEvent2(params = {}) {
	  const {
	    force = false,
	    dialogId
	  } = params;
	  main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.scrollToBottom, {
	    chatId: babelHelpers.classPrivateFieldLooseBase(this, _getDialog$2)[_getDialog$2](dialogId).chatId,
	    threshold: force ? im_v2_const.DialogScrollThreshold.none : im_v2_const.DialogScrollThreshold.halfScreenUp
	  });
	}
	function _getDialog2$2(dialogId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].getters['dialogues/get'](dialogId);
	}
	SendingService.instance = null;

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
	      [im_v2_const.RestMethodHandler.imNotifyGet]: [im_v2_const.RestMethod.imNotifyGet, imNotifyGetQueryParams]
	    };
	    if (!firstPage) {
	      imNotifyGetQueryParams.LAST_ID = this.lastId;
	      imNotifyGetQueryParams.LAST_TYPE = this.lastType;
	    } else {
	      batchQueryParams[im_v2_const.RestMethodHandler.imNotifySchemaGet] = [im_v2_const.RestMethod.imNotifySchemaGet, {}];
	    }
	    return new Promise(resolve => {
	      this.restClient.callBatch(batchQueryParams, response => {
	        im_v2_lib_logger.Logger.warn('im.notify.get: result', response);
	        resolve(this.handleResponse(response));
	      });
	    });
	  }
	  handleResponse(response) {
	    const imNotifyGetResponse = response[im_v2_const.RestMethodHandler.imNotifyGet].data();
	    this.hasMoreItemsToLoad = !this.isLastPage(imNotifyGetResponse.notifications);
	    if (imNotifyGetResponse.notifications.length === 0) {
	      im_v2_lib_logger.Logger.warn('im.notify.get: no notifications', imNotifyGetResponse);
	      return Promise.resolve();
	    }
	    this.lastId = this.getLastItemId(imNotifyGetResponse.notifications);
	    this.lastType = this.getLastItemType(imNotifyGetResponse.notifications);
	    return this.updateModels(imNotifyGetResponse).then(() => {
	      this.isLoading = false;
	      if (response[im_v2_const.RestMethodHandler.imNotifySchemaGet]) {
	        return response[im_v2_const.RestMethodHandler.imNotifySchemaGet].data();
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
	    const dialoguesPromise = this.store.dispatch('dialogues/set', dialogues);
	    const fakeRecent = this.getFakeData(recent);
	    const recentPromise = this.store.dispatch('recent/setUnread', fakeRecent);
	    return Promise.all([usersPromise, dialoguesPromise, recentPromise]);
	  }
	  getFakeData(itemsForModel) {
	    itemsForModel = itemsForModel.slice(-4);
	    itemsForModel.forEach(item => {
	      this.store.dispatch('dialogues/update', {
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

	exports.RecentService = RecentService;
	exports.ChatService = ChatService;
	exports.MessageService = MessageService;
	exports.SendingService = SendingService;
	exports.NotificationService = NotificationService;
	exports.UnreadRecentService = UnreadRecentService;

}((this.BX.Messenger.v2.Provider.Service = this.BX.Messenger.v2.Provider.Service || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX,BX.Vue3.Vuex,BX.Messenger.v2.Lib,BX.Messenger.v2.Provider.Service,BX.Messenger.v2.Lib,BX.Event,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX.Messenger.v2.Lib));
//# sourceMappingURL=registry.bundle.js.map
