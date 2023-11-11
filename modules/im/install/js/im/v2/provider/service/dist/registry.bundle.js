/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Provider = this.BX.Messenger.v2.Provider || {};
(function (exports,im_v2_provider_service,im_v2_lib_uuid,im_public,ui_notification,main_core_events,ui_uploader_core,im_v2_lib_rest,ui_vue3_vuex,main_core,im_v2_lib_logger,im_v2_lib_utils,rest_client,im_v2_application_core,im_v2_const,im_v2_lib_user) {
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
	      counter: item.counter,
	      role: im_v2_const.UserRole.member
	    };
	  }
	  prepareChatForAdditionalUser(user) {
	    return {
	      dialogId: user.id,
	      avatar: user.avatar,
	      color: user.color,
	      name: user.name,
	      type: im_v2_const.DialogType.user,
	      role: im_v2_const.UserRole.member
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

	var _restResult = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restResult");
	class ChatDataExtractor {
	  constructor(restResult) {
	    Object.defineProperty(this, _restResult, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult] = restResult;
	  }
	  getChatId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].chat.id;
	  }
	  getDialogId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].chat.dialogId;
	  }
	  isOpenlinesChat() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].chat.type === im_v2_const.DialogType.lines;
	  }
	  getChats() {
	    const mainChat = {
	      ...babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].chat,
	      hasPrevPage: babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].hasPrevPage,
	      hasNextPage: babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].hasNextPage
	    };
	    const chats = {
	      [babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].chat.dialogId]: mainChat
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].users.forEach(user => {
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
	    return (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].files) != null ? _babelHelpers$classPr : [];
	  }
	  getUsers() {
	    var _babelHelpers$classPr2;
	    return (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].users) != null ? _babelHelpers$classPr2 : [];
	  }
	  getAdditionalUsers() {
	    var _babelHelpers$classPr3;
	    return (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].usersShort) != null ? _babelHelpers$classPr3 : [];
	  }
	  getMessages() {
	    var _babelHelpers$classPr4;
	    return (_babelHelpers$classPr4 = babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].messages) != null ? _babelHelpers$classPr4 : [];
	  }
	  getMessagesToStore() {
	    var _babelHelpers$classPr5;
	    return (_babelHelpers$classPr5 = babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].additionalMessages) != null ? _babelHelpers$classPr5 : [];
	  }
	  getPinnedMessageIds() {
	    var _babelHelpers$classPr6;
	    const pinnedMessageIds = [];
	    const pins = (_babelHelpers$classPr6 = babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].pins) != null ? _babelHelpers$classPr6 : [];
	    pins.forEach(pin => {
	      pinnedMessageIds.push(pin.messageId);
	    });
	    return pinnedMessageIds;
	  }
	  getReactions() {
	    var _babelHelpers$classPr7;
	    return (_babelHelpers$classPr7 = babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].reactions) != null ? _babelHelpers$classPr7 : [];
	  }
	}

	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _requestChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("requestChat");
	var _markDialogAsLoading = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("markDialogAsLoading");
	var _markDialogAsLoaded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("markDialogAsLoaded");
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
	function _requestChat2(actionName, params) {
	  const {
	    dialogId
	  } = params;
	  babelHelpers.classPrivateFieldLooseBase(this, _markDialogAsLoading)[_markDialogAsLoading](dialogId);
	  return im_v2_lib_rest.runAction(actionName, {
	    data: params
	  }).then(result => {
	    return babelHelpers.classPrivateFieldLooseBase(this, _updateModels)[_updateModels](result);
	  }).then(result => {
	    if (main_core.Type.isStringFilled(result == null ? void 0 : result.linesDialogId)) {
	      im_public.Messenger.openLines(result.linesDialogId);
	      return;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isDialogLoadedMarkNeeded)[_isDialogLoadedMarkNeeded](actionName)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _markDialogAsLoaded)[_markDialogAsLoaded](dialogId);
	    }
	  }).catch(error => {
	    // eslint-disable-next-line no-console
	    console.error('ChatService: Load: error loading chat', error);
	    throw error;
	  });
	}
	function _markDialogAsLoading2(dialogId) {
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('dialogues/update', {
	    dialogId,
	    fields: {
	      loading: true
	    }
	  });
	}
	function _markDialogAsLoaded2(dialogId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('dialogues/update', {
	    dialogId,
	    fields: {
	      inited: true,
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
	  const dialoguesPromise = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('dialogues/set', extractor.getChats());
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
	  return Promise.all([dialoguesPromise, filesPromise, usersPromise, messagesPromise]);
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
	    return newDialogId;
	  }
	}
	async function _prepareFields2(chatConfig) {
	  var _preparedConfig$manag, _preparedConfig$type$, _preparedConfig$type, _preparedConfig$confe;
	  const preparedConfig = {
	    ...chatConfig
	  };
	  if (preparedConfig.avatar) {
	    preparedConfig.avatar = await im_v2_lib_utils.Utils.file.getBase64(chatConfig.avatar);
	  }
	  preparedConfig.managers = (_preparedConfig$manag = preparedConfig.managers) != null ? _preparedConfig$manag : [];
	  const allMembers = [...preparedConfig.members, ...preparedConfig.managers, preparedConfig.ownerId];
	  preparedConfig.members = [...new Set(allMembers)];
	  return {
	    entityType: (_preparedConfig$type$ = (_preparedConfig$type = preparedConfig.type) == null ? void 0 : _preparedConfig$type.toUpperCase()) != null ? _preparedConfig$type$ : null,
	    title: preparedConfig.title,
	    avatar: preparedConfig.avatar,
	    description: preparedConfig.description,
	    users: preparedConfig.members,
	    managers: preparedConfig.managers,
	    ownerId: preparedConfig.ownerId,
	    searchable: preparedConfig.isAvailableInSearch ? 'Y' : 'N',
	    manageUsers: preparedConfig.manageUsers,
	    manageUi: preparedConfig.manageUi,
	    manageSettings: preparedConfig.manageSettings,
	    canPost: preparedConfig.canPost,
	    conferencePassword: (_preparedConfig$confe = preparedConfig.conferencePassword) != null ? _preparedConfig$confe : null
	  };
	}
	function _addChatToModel2(newDialogId, chatConfig) {
	  let chatType = chatConfig.searchable === 'Y' ? OPEN_CHAT : PRIVATE_CHAT;
	  if (main_core.Type.isStringFilled(chatConfig.entityType)) {
	    chatType = chatConfig.entityType.toLowerCase();
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('dialogues/set', {
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
	      action: true,
	      dateUpdate: new Date()
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
	      action: false,
	      dateUpdate: new Date()
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
	      action: false,
	      dateUpdate: new Date()
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
	      action: true,
	      dateUpdate: new Date()
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
	    const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].getters['dialogues/get'](dialogId);
	    const recentItem = babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].getters['recent/get'](dialogId);
	    if (dialog.markedId === 0 && !(recentItem != null && recentItem.unread)) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('recent/unread', {
	      id: dialogId,
	      action: false,
	      dateUpdate: new Date()
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('dialogues/update', {
	      dialogId,
	      fields: {
	        markedId: 0
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$4)[_restClient$4].callMethod(im_v2_const.RestMethod.imV2ChatRead, {
	      dialogId,
	      onlyRecent: 'Y'
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
	    babelHelpers.classPrivateFieldLooseBase(this, _store$6)[_store$6].dispatch('dialogues/update', {
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
	    babelHelpers.classPrivateFieldLooseBase(this, _store$6)[_store$6].dispatch('dialogues/update', {
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
	      const restResult = result.data();
	      restResult.messages = babelHelpers.classPrivateFieldLooseBase(this, _prepareInitialMessages)[_prepareInitialMessages](restResult.messages);
	      return babelHelpers.classPrivateFieldLooseBase(this, _handleLoadedMessages)[_handleLoadedMessages](result.data());
	    }).then(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	      return true;
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('MessageService: loadInitialMessages error:', error);
	      babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	    });
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
	          },
	          dateUpdate: new Date()
	        }
	      });
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$8)[_restClient$8].callMethod(im_v2_const.RestMethod.imMessageUpdate, {
	      ID: messageId,
	      MESSAGE: text
	    }).catch(error => {
	      im_v2_lib_logger.Logger.error('MessageService: editMessageText error:', error);
	    });
	  }
	}

	var _store$a = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _chatId$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatId");
	var _shallowMessageDelete = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("shallowMessageDelete");
	var _completeMessageDelete = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("completeMessageDelete");
	var _deleteMessageOnServer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("deleteMessageOnServer");
	class DeleteService {
	  constructor(chatId) {
	    Object.defineProperty(this, _deleteMessageOnServer, {
	      value: _deleteMessageOnServer2
	    });
	    Object.defineProperty(this, _completeMessageDelete, {
	      value: _completeMessageDelete2
	    });
	    Object.defineProperty(this, _shallowMessageDelete, {
	      value: _shallowMessageDelete2
	    });
	    Object.defineProperty(this, _store$a, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _chatId$2, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _chatId$2)[_chatId$2] = chatId;
	    babelHelpers.classPrivateFieldLooseBase(this, _store$a)[_store$a] = im_v2_application_core.Core.getStore();
	  }
	  deleteMessage(messageId) {
	    im_v2_lib_logger.Logger.warn('MessageService: deleteMessage', messageId);
	    const message = babelHelpers.classPrivateFieldLooseBase(this, _store$a)[_store$a].getters['messages/getById'](messageId);
	    if (message.viewedByOthers) {
	      babelHelpers.classPrivateFieldLooseBase(this, _shallowMessageDelete)[_shallowMessageDelete](message);
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _completeMessageDelete)[_completeMessageDelete](message);
	    }
	  }
	}
	function _shallowMessageDelete2(message) {
	  babelHelpers.classPrivateFieldLooseBase(this, _store$a)[_store$a].dispatch('messages/update', {
	    id: message.id,
	    fields: {
	      text: '',
	      isDeleted: true,
	      files: [],
	      attach: [],
	      replyId: 0
	    }
	  });
	  const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$a)[_store$a].getters['dialogues/getByChatId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId$2)[_chatId$2]);
	  if (message.id === dialog.lastMessageId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _store$a)[_store$a].dispatch('recent/update', {
	      id: dialog.dialogId,
	      fields: {
	        message: {
	          text: ''
	        }
	      }
	    });
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _deleteMessageOnServer)[_deleteMessageOnServer](message.id);
	}
	function _completeMessageDelete2(message) {
	  const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$a)[_store$a].getters['dialogues/getByChatId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId$2)[_chatId$2]);
	  const previousMessage = babelHelpers.classPrivateFieldLooseBase(this, _store$a)[_store$a].getters['messages/getPreviousMessage']({
	    messageId: message.id,
	    chatId: dialog.chatId
	  });
	  if (message.id === dialog.lastMessageId) {
	    let updatedMessage = {
	      text: ''
	    };
	    if (previousMessage) {
	      updatedMessage = previousMessage;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _store$a)[_store$a].dispatch('recent/update', {
	      id: dialog.dialogId,
	      fields: {
	        message: updatedMessage,
	        dateUpdate: new Date()
	      }
	    });
	    const newLastId = previousMessage ? previousMessage.id : 0;
	    babelHelpers.classPrivateFieldLooseBase(this, _store$a)[_store$a].dispatch('dialogues/update', {
	      dialogId: dialog.dialogId,
	      fields: {
	        lastMessageId: newLastId,
	        lastId: newLastId
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$a)[_store$a].dispatch('dialogues/clearLastMessageViews', {
	      dialogId: dialog.dialogId
	    });
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _store$a)[_store$a].dispatch('messages/delete', {
	    id: message.id
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _deleteMessageOnServer)[_deleteMessageOnServer](message.id);
	}
	function _deleteMessageOnServer2(messageId) {
	  im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatMessageDelete, {
	    data: {
	      id: messageId
	    }
	  }).catch(error => {
	    console.error('MessageService: deleteMessage error:', error);
	  });
	}

	var _chatId$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatId");
	var _store$b = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$9 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
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
	    Object.defineProperty(this, _restClient$9, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _chatId$3)[_chatId$3] = chatId;
	    babelHelpers.classPrivateFieldLooseBase(this, _store$b)[_store$b] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$9)[_restClient$9] = im_v2_application_core.Core.getRestClient();
	  }
	  markMessage(messageId) {
	    im_v2_lib_logger.Logger.warn('MessageService: markMessage', messageId);
	    const {
	      dialogId
	    } = babelHelpers.classPrivateFieldLooseBase(this, _store$b)[_store$b].getters['dialogues/getByChatId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId$3)[_chatId$3]);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$b)[_store$b].dispatch('recent/unread', {
	      id: dialogId,
	      action: true,
	      dateUpdate: new Date()
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$b)[_store$b].dispatch('dialogues/update', {
	      dialogId,
	      fields: {
	        markedId: messageId
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$9)[_restClient$9].callMethod(im_v2_const.RestMethod.imV2ChatMessageMark, {
	      dialogId,
	      id: messageId
	    }).catch(error => {
	      console.error('MessageService: error marking message', error);
	    });
	  }
	}

	var _chatId$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatId");
	var _store$c = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$a = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
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
	    Object.defineProperty(this, _restClient$a, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _chatId$4)[_chatId$4] = chatId;
	    babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$a)[_restClient$a] = im_v2_application_core.Core.getRestClient();
	  }
	  addMessageToFavorite(messageId) {
	    im_v2_lib_logger.Logger.warn('MessageService: addMessageToFavorite', messageId);
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$a)[_restClient$a].callMethod(im_v2_const.RestMethod.imChatFavoriteAdd, {
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
	    babelHelpers.classPrivateFieldLooseBase(this, _store$c)[_store$c].dispatch('sidebar/favorites/deleteByMessageId', {
	      chatId: babelHelpers.classPrivateFieldLooseBase(this, _chatId$4)[_chatId$4],
	      messageId: messageId
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$a)[_restClient$a].callMethod(im_v2_const.RestMethod.imChatFavoriteDelete, {
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

	var _store$d = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _prepareMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareMessage");
	var _handlePagination = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handlePagination");
	var _addMessageToModels = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addMessageToModels");
	var _addMessageToRecent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addMessageToRecent");
	var _sendMessageToServer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendMessageToServer");
	var _updateModels$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateModels");
	var _updateMessageError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateMessageError");
	var _sendScrollEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendScrollEvent");
	var _getDialog$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialog");
	class SendingService$$1 {
	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }
	  constructor() {
	    Object.defineProperty(this, _getDialog$1, {
	      value: _getDialog2$1
	    });
	    Object.defineProperty(this, _sendScrollEvent, {
	      value: _sendScrollEvent2
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
	    Object.defineProperty(this, _prepareMessage, {
	      value: _prepareMessage2
	    });
	    Object.defineProperty(this, _store$d, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d] = im_v2_application_core.Core.getStore();
	  }
	  sendMessage(params) {
	    const {
	      text = '',
	      fileId = '',
	      tempMessageId,
	      dialogId,
	      replyId
	    } = params;
	    if (!main_core.Type.isStringFilled(text) && !main_core.Type.isStringFilled(fileId)) {
	      return Promise.resolve();
	    }
	    im_v2_lib_logger.Logger.warn('SendingService: sendMessage', params);
	    const message = babelHelpers.classPrivateFieldLooseBase(this, _prepareMessage)[_prepareMessage]({
	      text,
	      fileId,
	      tempMessageId,
	      dialogId,
	      replyId
	    });
	    return babelHelpers.classPrivateFieldLooseBase(this, _handlePagination)[_handlePagination](dialogId).then(() => {
	      return babelHelpers.classPrivateFieldLooseBase(this, _addMessageToModels)[_addMessageToModels](message);
	    }).then(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _sendScrollEvent)[_sendScrollEvent]({
	        force: true,
	        dialogId
	      });
	      return babelHelpers.classPrivateFieldLooseBase(this, _sendMessageToServer)[_sendMessageToServer](message);
	    }).then(result => {
	      if (message.withFile) {
	        return;
	      }
	      im_v2_lib_logger.Logger.warn('SendingService: sendMessage result -', result.data());
	      babelHelpers.classPrivateFieldLooseBase(this, _updateModels$2)[_updateModels$2]({
	        oldId: message.temporaryId,
	        newId: result.data(),
	        dialogId: message.dialogId
	      });
	    }).catch(error => {
	      babelHelpers.classPrivateFieldLooseBase(this, _updateMessageError)[_updateMessageError](message.temporaryId);
	      console.error('SendingService: sendMessage error -', error);
	    });
	  }
	}
	function _prepareMessage2(params) {
	  const {
	    text,
	    fileId,
	    tempMessageId,
	    dialogId,
	    replyId
	  } = params;
	  const messageParams = {};
	  if (fileId) {
	    messageParams.FILE_ID = [fileId];
	  }
	  const temporaryId = tempMessageId || im_v2_lib_utils.Utils.text.getUuidV4();
	  return {
	    temporaryId,
	    chatId: babelHelpers.classPrivateFieldLooseBase(this, _getDialog$1)[_getDialog$1](dialogId).chatId,
	    dialogId,
	    replyId,
	    authorId: im_v2_application_core.Core.getUserId(),
	    text,
	    params: messageParams,
	    withFile: Boolean(fileId),
	    unread: false,
	    sending: true
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
	    console.error('SendingService: loadContext error', error);
	  });
	}
	function _addMessageToModels2(message) {
	  babelHelpers.classPrivateFieldLooseBase(this, _addMessageToRecent)[_addMessageToRecent](message);
	  babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d].dispatch('dialogues/clearLastMessageViews', {
	    dialogId: message.dialogId
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d].dispatch('messages/add', message);
	}
	function _addMessageToRecent2(message) {
	  const recentItem = babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d].getters['recent/get'](message.dialogId);
	  if (!recentItem || message.text === '') {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d].dispatch('recent/update', {
	    id: message.dialogId,
	    fields: {
	      message: {
	        id: message.temporaryId,
	        text: message.text,
	        authorId: message.authorId,
	        replyId: message.replyId,
	        status: im_v2_const.MessageStatus.received,
	        sending: true,
	        params: {
	          withFile: false,
	          withAttach: false
	        }
	      },
	      dateUpdate: new Date()
	    }
	  });
	}
	function _sendMessageToServer2(element) {
	  if (element.withFile) {
	    return Promise.resolve();
	  }
	  const query = {
	    template_id: element.temporaryId,
	    dialog_id: element.dialogId
	  };
	  if (element.replyId !== 0) {
	    query.reply_id = element.replyId;
	  }
	  if (element.text) {
	    query.message = element.text;
	  }
	  return im_v2_application_core.Core.getRestClient().callMethod(im_v2_const.RestMethod.imMessageAdd, query);
	}
	function _updateModels2$2(params) {
	  const {
	    oldId,
	    newId,
	    dialogId,
	    replyId
	  } = params;
	  babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d].dispatch('messages/updateWithId', {
	    id: oldId,
	    fields: {
	      id: newId
	    }
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d].dispatch('messages/update', {
	    id: newId,
	    fields: {
	      replyId
	    }
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d].dispatch('dialogues/update', {
	    dialogId,
	    fields: {
	      lastId: newId,
	      lastMessageId: newId
	    }
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d].dispatch('recent/update', {
	    id: dialogId,
	    fields: {
	      message: {
	        sending: false
	      }
	    }
	  });
	}
	function _updateMessageError2(messageId) {
	  babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d].dispatch('messages/update', {
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
	    chatId: babelHelpers.classPrivateFieldLooseBase(this, _getDialog$1)[_getDialog$1](dialogId).chatId,
	    threshold: force ? im_v2_const.DialogScrollThreshold.none : im_v2_const.DialogScrollThreshold.halfScreenUp
	  });
	}
	function _getDialog2$1(dialogId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$d)[_store$d].getters['dialogues/get'](dialogId, true);
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

	var _restClient$b = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	class DiskService {
	  constructor() {
	    Object.defineProperty(this, _restClient$b, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$b)[_restClient$b] = im_v2_application_core.Core.getRestClient();
	  }
	  delete({
	    chatId,
	    fileId
	  }) {
	    const queryParams = {
	      chat_id: chatId,
	      file_id: fileId
	    };
	    return babelHelpers.classPrivateFieldLooseBase(this, _restClient$b)[_restClient$b].callMethod(im_v2_const.RestMethod.imDiskFileDelete, queryParams).catch(error => {
	      console.error('DiskService: error deleting file', error);
	    });
	  }
	  save(fileId) {
	    const queryParams = {
	      file_id: fileId
	    };
	    return babelHelpers.classPrivateFieldLooseBase(this, _restClient$b)[_restClient$b].callMethod(im_v2_const.RestMethod.imDiskFileSave, queryParams).catch(error => {
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
	          this.emit(UploaderWrapper.events.onFileUploadComplete, event);
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

	var _store$e = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$c = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
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
	var _prepareMessageWithFile = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareMessageWithFile");
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
	class UploadingService$$1 {
	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }
	  constructor() {
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
	    Object.defineProperty(this, _prepareMessageWithFile, {
	      value: _prepareMessageWithFile2
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
	    Object.defineProperty(this, _store$e, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$c, {
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
	    babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$c)[_restClient$c] = im_v2_application_core.Core.getRestClient();
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
	        const messageWithFile = babelHelpers.classPrivateFieldLooseBase(this, _prepareMessageWithFile)[_prepareMessageWithFile](file, dialogId, uploaderId, sendAsFile);
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
	        return babelHelpers.classPrivateFieldLooseBase(this, _sendingService)[_sendingService].sendMessage(message);
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
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$c)[_restClient$c].callMethod(im_v2_const.RestMethod.imDiskFileCommit, {
	      chat_id: chatId,
	      message: messageText,
	      template_id: tempMessageId,
	      file_template_id: temporaryFileId,
	      as_file: sendAsFile ? 'Y' : 'N',
	      ...fileIdParams
	    }).catch(error => {
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
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].dispatch('files/add', {
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
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$c)[_restClient$c].callMethod(im_v2_const.RestMethod.imDiskFolderGet, {
	      chat_id: chatId
	    }).then(response => {
	      const {
	        ID: diskFolderId
	      } = response.data();
	      babelHelpers.classPrivateFieldLooseBase(this, _isRequestingDiskFolderId)[_isRequestingDiskFolderId] = false;
	      babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].commit('dialogues/update', {
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
	function _prepareMessageWithFile2(file, dialogId, uploaderId, sendAsFile) {
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
	  babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].dispatch('files/update', {
	    id,
	    fields: {
	      progress: progress === 100 ? 99 : progress,
	      status
	    }
	  });
	}
	function _cancelUpload2(tempMessageId, tempFileId) {
	  babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].dispatch('messages/delete', {
	    id: tempMessageId
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].dispatch('files/delete', {
	    id: tempFileId
	  });
	}
	function _addFileToStore2(file) {
	  const taskId = file.getId();
	  const fileBinary = file.getBinary();
	  const previewData = babelHelpers.classPrivateFieldLooseBase(this, _preparePreview)[_preparePreview](file);
	  babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].dispatch('files/add', {
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
	  babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].dispatch('files/update', {
	    id: file.getId(),
	    fields: {
	      ...previewData
	    }
	  });
	}
	function _updateFileSizeInStore2(file) {
	  babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].dispatch('files/update', {
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
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].getters['dialogues/get'](dialogId);
	}
	function _getCurrentUser2() {
	  const userId = im_v2_application_core.Core.getUserId();
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$e)[_store$e].getters['users/get'](userId);
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
	  const messagesToSend = [];
	  const files = this.getFiles(uploaderId);
	  const text = babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].text;
	  const dialogId = babelHelpers.classPrivateFieldLooseBase(this, _uploaderFilesRegistry)[_uploaderFilesRegistry][uploaderId].dialogId;
	  const hasText = text.length > 0;

	  // if we have more than one file and text, we need to send text message first
	  if (files.length > 1 && hasText) {
	    messagesToSend.push({
	      dialogId,
	      text
	    });
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
	    messagesToSend.push({
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
	  const messagesToSend = babelHelpers.classPrivateFieldLooseBase(this, _createMessagesFromFiles)[_createMessagesFromFiles](uploaderId);
	  messagesToSend.forEach(message => {
	    babelHelpers.classPrivateFieldLooseBase(this, _sendingService)[_sendingService].sendMessage(message);
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
	UploadingService$$1.instance = null;

	class SettingsService {
	  changeSetting(settingName, value) {
	    im_v2_lib_logger.Logger.warn('SettingsService: changeSetting', settingName, value);
	    im_v2_application_core.Core.getStore().dispatch('application/settings/set', {
	      [settingName]: value
	    });
	    let preparedValue = value;
	    if (main_core.Type.isBoolean(preparedValue)) {
	      preparedValue = preparedValue === true ? 'Y' : 'N';
	    }
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2SettingsGeneralUpdate, {
	      data: {
	        userId: im_v2_application_core.Core.getUserId(),
	        name: settingName,
	        value: preparedValue
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

	const collator = new Intl.Collator(undefined, {
	  sensitivity: 'base'
	});
	var _store$f = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _getItemsFromRecentListByQuery = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getItemsFromRecentListByQuery");
	var _getFromStore = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFromStore");
	var _getRecentListItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getRecentListItems");
	var _getSearchSessionListItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSearchSessionListItems");
	var _prepareRecentItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareRecentItem");
	var _searchByQueryWords = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("searchByQueryWords");
	var _searchByDialogFields = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("searchByDialogFields");
	var _searchByUserFields = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("searchByUserFields");
	var _doesItemMatchQuery = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("doesItemMatchQuery");
	var _getDialogIds = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialogIds");
	var _getAllRecentItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getAllRecentItems");
	class RecentStateSearch {
	  constructor() {
	    Object.defineProperty(this, _getAllRecentItems, {
	      value: _getAllRecentItems2
	    });
	    Object.defineProperty(this, _getDialogIds, {
	      value: _getDialogIds2
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
	    Object.defineProperty(this, _getSearchSessionListItems, {
	      value: _getSearchSessionListItems2
	    });
	    Object.defineProperty(this, _getRecentListItems, {
	      value: _getRecentListItems2
	    });
	    Object.defineProperty(this, _getFromStore, {
	      value: _getFromStore2
	    });
	    Object.defineProperty(this, _getItemsFromRecentListByQuery, {
	      value: _getItemsFromRecentListByQuery2
	    });
	    Object.defineProperty(this, _store$f, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f] = im_v2_application_core.Core.getStore();
	  }
	  search(originalLayoutQuery) {
	    const recentCollection = babelHelpers.classPrivateFieldLooseBase(this, _getItemsFromRecentListByQuery)[_getItemsFromRecentListByQuery](originalLayoutQuery);
	    return babelHelpers.classPrivateFieldLooseBase(this, _getDialogIds)[_getDialogIds](recentCollection);
	  }
	}
	function _getItemsFromRecentListByQuery2(query) {
	  const queryWords = im_v2_lib_utils.Utils.text.getWordsFromString(query);
	  return babelHelpers.classPrivateFieldLooseBase(this, _getFromStore)[_getFromStore](queryWords);
	}
	function _getFromStore2(queryWords) {
	  const recentItems = babelHelpers.classPrivateFieldLooseBase(this, _getAllRecentItems)[_getAllRecentItems]();
	  const foundItems = new Map();
	  recentItems.forEach(recentItem => {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _searchByQueryWords)[_searchByQueryWords](recentItem, queryWords)) {
	      foundItems.set(recentItem.dialogId, recentItem);
	    }
	  });
	  return foundItems;
	}
	function _getRecentListItems2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].getters['recent/getRecentCollection'].map(item => {
	    return babelHelpers.classPrivateFieldLooseBase(this, _prepareRecentItem)[_prepareRecentItem](item);
	  });
	}
	function _getSearchSessionListItems2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].getters['recent/search/getCollection'].map(item => {
	    return babelHelpers.classPrivateFieldLooseBase(this, _prepareRecentItem)[_prepareRecentItem](item);
	  });
	}
	function _prepareRecentItem2(item) {
	  const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].getters['dialogues/get'](item.dialogId, true);
	  const isUser = dialog.type === im_v2_const.DialogType.user;
	  const recentItem = {
	    dialogId: item.dialogId,
	    dialog,
	    dateUpdate: item.dateUpdate
	  };
	  if (isUser) {
	    recentItem.user = babelHelpers.classPrivateFieldLooseBase(this, _store$f)[_store$f].getters['users/get'](item.dialogId, true);
	  }
	  return recentItem;
	}
	function _searchByQueryWords2(recentItem, queryWords) {
	  if (recentItem.user) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _searchByUserFields)[_searchByUserFields](recentItem, queryWords);
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _searchByDialogFields)[_searchByDialogFields](recentItem, queryWords);
	}
	function _searchByDialogFields2(recentItem, queryWords) {
	  const searchField = [];
	  if (recentItem.dialog.name) {
	    const dialogNameWords = im_v2_lib_utils.Utils.text.getWordsFromString(recentItem.dialog.name.toLowerCase());
	    searchField.push(...dialogNameWords);
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _doesItemMatchQuery)[_doesItemMatchQuery](searchField, queryWords);
	}
	function _searchByUserFields2(recentItem, queryWords) {
	  const searchField = [];
	  if (recentItem.user.name) {
	    const userNameWords = im_v2_lib_utils.Utils.text.getWordsFromString(recentItem.user.name.toLowerCase());
	    searchField.push(...userNameWords);
	  }
	  if (recentItem.user.workPosition) {
	    const workPositionWords = im_v2_lib_utils.Utils.text.getWordsFromString(recentItem.user.workPosition.toLowerCase());
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
	function _getDialogIds2(items) {
	  return [...items.values()].map(item => {
	    return item.dialogId;
	  });
	}
	function _getAllRecentItems2() {
	  const recentItems = babelHelpers.classPrivateFieldLooseBase(this, _getRecentListItems)[_getRecentListItems]();
	  const searchSessionItems = babelHelpers.classPrivateFieldLooseBase(this, _getSearchSessionListItems)[_getSearchSessionListItems]();
	  const itemsMap = new Map();
	  const mergedArray = [...recentItems, ...searchSessionItems];
	  for (const recentItem of mergedArray) {
	    if (!itemsMap.has(recentItem.dialogId)) {
	      itemsMap.set(recentItem.dialogId, recentItem);
	    }
	  }
	  return [...itemsMap.values()];
	}

	var _itemOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("itemOptions");
	class SearchItem {
	  constructor(itemOptions) {
	    Object.defineProperty(this, _itemOptions, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _itemOptions)[_itemOptions] = itemOptions;
	  }
	  getDialogId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _itemOptions)[_itemOptions].id;
	  }
	  getEntityId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _itemOptions)[_itemOptions].entityId;
	  }
	  getEntityType() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _itemOptions)[_itemOptions].entityType;
	  }
	  getTitle() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _itemOptions)[_itemOptions].title;
	  }
	  getAvatar() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _itemOptions)[_itemOptions].avatar;
	  }
	  isUser() {
	    return this.getEntityType() === im_v2_const.SearchEntityIdTypes.imUser;
	  }
	  isChat() {
	    return this.getEntityType() === im_v2_const.SearchEntityIdTypes.chat;
	  }
	  getCustomData() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _itemOptions)[_itemOptions].customData;
	  }
	  getDateUpdate() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _itemOptions)[_itemOptions].customData.dateUpdate;
	  }
	}

	const EntityId = 'im-recent-v2';
	const ContextId = 'IM_CHAT_SEARCH';
	const SearchDialogId = 'search-experimental';
	const getSearchConfig = () => {
	  return {
	    dialog: {
	      entities: [{
	        id: EntityId,
	        dynamicLoad: true,
	        dynamicSearch: true
	      }],
	      preselectedItems: [],
	      clearUnavailableItems: false,
	      context: ContextId,
	      id: SearchDialogId
	    }
	  };
	};

	var _store$g = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _userManager$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userManager");
	var _setRecentItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setRecentItems");
	var _setRecentSearchItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setRecentSearchItems");
	var _setDialoguesToModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setDialoguesToModel");
	var _prepareDataForModels = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareDataForModels");
	class StoreUpdater {
	  constructor() {
	    Object.defineProperty(this, _prepareDataForModels, {
	      value: _prepareDataForModels2
	    });
	    Object.defineProperty(this, _setDialoguesToModel, {
	      value: _setDialoguesToModel2
	    });
	    Object.defineProperty(this, _setRecentSearchItems, {
	      value: _setRecentSearchItems2
	    });
	    Object.defineProperty(this, _setRecentItems, {
	      value: _setRecentItems2
	    });
	    Object.defineProperty(this, _store$g, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _userManager$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$g)[_store$g] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _userManager$1)[_userManager$1] = new im_v2_lib_user.UserManager();
	  }
	  update(items) {
	    const {
	      users,
	      dialogues,
	      recentItems
	    } = babelHelpers.classPrivateFieldLooseBase(this, _prepareDataForModels)[_prepareDataForModels](items);
	    return Promise.all([babelHelpers.classPrivateFieldLooseBase(this, _userManager$1)[_userManager$1].setUsersToModel(users), babelHelpers.classPrivateFieldLooseBase(this, _setDialoguesToModel)[_setDialoguesToModel](dialogues), babelHelpers.classPrivateFieldLooseBase(this, _setRecentItems)[_setRecentItems](recentItems)]);
	  }
	  updateSearchSession(items) {
	    const {
	      recentItems
	    } = babelHelpers.classPrivateFieldLooseBase(this, _prepareDataForModels)[_prepareDataForModels](items);
	    return babelHelpers.classPrivateFieldLooseBase(this, _setRecentSearchItems)[_setRecentSearchItems](recentItems);
	  }
	  clearSessionSearch() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _store$g)[_store$g].dispatch('recent/search/clear');
	  }
	}
	function _setRecentItems2(items) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$g)[_store$g].dispatch('recent/store', items);
	}
	function _setRecentSearchItems2(items) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$g)[_store$g].dispatch('recent/search/set', items);
	}
	function _setDialoguesToModel2(dialogues) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$g)[_store$g].dispatch('dialogues/set', dialogues);
	}
	function _prepareDataForModels2(items) {
	  const result = {
	    users: [],
	    dialogues: [],
	    recentItems: []
	  };
	  [...items.values()].forEach(item => {
	    const itemData = item.getCustomData();
	    result.recentItems.push({
	      dialogId: item.getDialogId(),
	      date_update: item.getDateUpdate()
	    });
	    if (item.isUser()) {
	      result.users.push(itemData);
	    }
	    if (item.isChat()) {
	      result.dialogues.push({
	        ...itemData,
	        dialogId: item.getDialogId()
	      });
	    }
	  });
	  return result;
	}

	const SEARCH_REQUEST_ENDPOINT = 'ui.entityselector.doSearch';
	const LOAD_LATEST_RESULTS_ENDPOINT = 'ui.entityselector.load';
	const SAVE_ITEM_ENDPOINT = 'ui.entityselector.saveRecentItems';
	var _storeUpdater = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("storeUpdater");
	var _processLatestSearchResponse = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("processLatestSearchResponse");
	var _processSearchResponse = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("processSearchResponse");
	var _getDialogIds$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialogIds");
	var _getItemsFromRecentItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getItemsFromRecentItems");
	var _createItemMap = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createItemMap");
	class BaseServerSearch {
	  constructor() {
	    Object.defineProperty(this, _createItemMap, {
	      value: _createItemMap2
	    });
	    Object.defineProperty(this, _getItemsFromRecentItems, {
	      value: _getItemsFromRecentItems2
	    });
	    Object.defineProperty(this, _getDialogIds$1, {
	      value: _getDialogIds2$1
	    });
	    Object.defineProperty(this, _processSearchResponse, {
	      value: _processSearchResponse2
	    });
	    Object.defineProperty(this, _processLatestSearchResponse, {
	      value: _processLatestSearchResponse2
	    });
	    Object.defineProperty(this, _storeUpdater, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _storeUpdater)[_storeUpdater] = new StoreUpdater();
	  }
	  search(query) {
	    return this.searchRequest(query).then(items => {
	      const itemsCollection = babelHelpers.classPrivateFieldLooseBase(this, _createItemMap)[_createItemMap](items);
	      return babelHelpers.classPrivateFieldLooseBase(this, _processSearchResponse)[_processSearchResponse](itemsCollection);
	    }).then(items => {
	      return babelHelpers.classPrivateFieldLooseBase(this, _getDialogIds$1)[_getDialogIds$1](items);
	    });
	  }
	  searchRequest(query) {
	    const config = {
	      json: getSearchConfig()
	    };
	    config.json.searchQuery = {
	      queryWords: im_v2_lib_utils.Utils.text.getWordsFromString(query),
	      query
	    };
	    return new Promise((resolve, reject) => {
	      main_core.ajax.runAction(SEARCH_REQUEST_ENDPOINT, config).then(response => {
	        im_v2_lib_logger.Logger.warn('Im.SearchLight: Search request result', response);
	        resolve(response.data.dialog.items);
	      }).catch(error => reject(error));
	    });
	  }
	  loadLatestResults() {
	    return this.loadLatestResultsRequest().then(responseFromServer => {
	      const {
	        items,
	        recentItems
	      } = responseFromServer;
	      if (items.length === 0 || recentItems.length === 0) {
	        return new Map();
	      }
	      const itemMap = babelHelpers.classPrivateFieldLooseBase(this, _createItemMap)[_createItemMap](items);
	      const itemsFromRecentItems = babelHelpers.classPrivateFieldLooseBase(this, _getItemsFromRecentItems)[_getItemsFromRecentItems](recentItems, itemMap);
	      return babelHelpers.classPrivateFieldLooseBase(this, _processLatestSearchResponse)[_processLatestSearchResponse](itemsFromRecentItems);
	    }).then(processedItems => {
	      return babelHelpers.classPrivateFieldLooseBase(this, _getDialogIds$1)[_getDialogIds$1](processedItems);
	    });
	  }
	  loadLatestResultsRequest() {
	    const config = {
	      json: getSearchConfig()
	    };
	    return new Promise((resolve, reject) => {
	      main_core.ajax.runAction(LOAD_LATEST_RESULTS_ENDPOINT, config).then(response => {
	        im_v2_lib_logger.Logger.warn('Im.SearchLight: Recent search request result', response);
	        resolve(response.data.dialog);
	      }).catch(error => reject(error));
	    });
	  }
	  addItemsToRecentSearchResults(dialogId) {
	    const recentItems = [{
	      id: dialogId,
	      entityId: EntityId
	    }];
	    const config = {
	      json: {
	        ...getSearchConfig(),
	        recentItems
	      }
	    };
	    return new Promise((resolve, reject) => {
	      main_core.ajax.runAction(SAVE_ITEM_ENDPOINT, config).then(() => {
	        resolve();
	      }).catch(error => reject(error));
	    });
	  }
	  clearSessionSearch() {
	    void babelHelpers.classPrivateFieldLooseBase(this, _storeUpdater)[_storeUpdater].clearSessionSearch();
	  }
	}
	function _processLatestSearchResponse2(items) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _storeUpdater)[_storeUpdater].update(items).then(() => {
	    return items;
	  });
	}
	function _processSearchResponse2(items) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _storeUpdater)[_storeUpdater].update(items).then(() => {
	    return babelHelpers.classPrivateFieldLooseBase(this, _storeUpdater)[_storeUpdater].updateSearchSession(items);
	  }).then(() => {
	    return items;
	  });
	}
	function _getDialogIds2$1(items) {
	  return [...items.values()].map(item => {
	    return item.getDialogId();
	  });
	}
	function _getItemsFromRecentItems2(recentItems, items) {
	  const filledRecentItems = new Map();
	  recentItems.forEach(recentItem => {
	    const [, dialogId] = recentItem;
	    const itemFromMap = items.get(dialogId.toString());
	    if (itemFromMap) {
	      filledRecentItems.set(itemFromMap.getDialogId(), itemFromMap);
	    }
	  });
	  return filledRecentItems;
	}
	function _createItemMap2(items) {
	  const map = new Map();
	  items.forEach(item => {
	    const mapItem = new SearchItem(item);
	    map.set(mapItem.getDialogId(), mapItem);
	  });
	  return map;
	}

	var _restClient$d = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _userManager$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userManager");
	class ChatParticipants {
	  constructor() {
	    Object.defineProperty(this, _restClient$d, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _userManager$2, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$d)[_restClient$d] = im_v2_application_core.Core.getRestClient();
	    babelHelpers.classPrivateFieldLooseBase(this, _userManager$2)[_userManager$2] = new im_v2_lib_user.UserManager();
	  }
	  load(dialogId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _restClient$d)[_restClient$d].callMethod(im_v2_const.RestMethod.imDialogUsersList, {
	      DIALOG_ID: dialogId
	    }).then(response => {
	      const users = response.data();
	      babelHelpers.classPrivateFieldLooseBase(this, _userManager$2)[_userManager$2].setUsersToModel(users);
	      return users.map(user => user.id.toString());
	    }).catch(error => {
	      console.error('MentionService: error', error);
	    });
	  }
	}

	var _store$h = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _recentStateSearch = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("recentStateSearch");
	var _baseServerSearch = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("baseServerSearch");
	var _chatParticipants = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatParticipants");
	var _isExtranet = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isExtranet");
	var _isSelfDialogId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isSelfDialogId");
	class SearchService {
	  constructor() {
	    Object.defineProperty(this, _isSelfDialogId, {
	      value: _isSelfDialogId2
	    });
	    Object.defineProperty(this, _isExtranet, {
	      value: _isExtranet2
	    });
	    Object.defineProperty(this, _store$h, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _recentStateSearch, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _baseServerSearch, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _chatParticipants, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _recentStateSearch)[_recentStateSearch] = new RecentStateSearch();
	    babelHelpers.classPrivateFieldLooseBase(this, _baseServerSearch)[_baseServerSearch] = new BaseServerSearch();
	    babelHelpers.classPrivateFieldLooseBase(this, _chatParticipants)[_chatParticipants] = new ChatParticipants();
	    babelHelpers.classPrivateFieldLooseBase(this, _store$h)[_store$h] = im_v2_application_core.Core.getStore();
	  }
	  loadLatestResults() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _baseServerSearch)[_baseServerSearch].loadLatestResults();
	  }
	  loadChatParticipants(dialogId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _chatParticipants)[_chatParticipants].load(dialogId).then(dialogIds => {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _isSelfDialogId)[_isSelfDialogId](dialogId)) {
	        return dialogIds;
	      }
	      return dialogIds.filter(element => !babelHelpers.classPrivateFieldLooseBase(this, _isSelfDialogId)[_isSelfDialogId](element));
	    });
	  }
	  searchLocal(query) {
	    return Promise.resolve(babelHelpers.classPrivateFieldLooseBase(this, _recentStateSearch)[_recentStateSearch].search(query));
	  }
	  searchOnServer(query) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _baseServerSearch)[_baseServerSearch].search(query);
	  }
	  addItemToRecent(dialogId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _baseServerSearch)[_baseServerSearch].addItemsToRecentSearchResults(dialogId);
	  }
	  clearSessionResult() {
	    babelHelpers.classPrivateFieldLooseBase(this, _baseServerSearch)[_baseServerSearch].clearSessionSearch();
	  }
	  sortByDate(items) {
	    items.sort((firstItem, secondItem) => {
	      const dateUpdate1 = babelHelpers.classPrivateFieldLooseBase(this, _store$h)[_store$h].getters['recent/get'](firstItem).dateUpdate;
	      const dateUpdate2 = babelHelpers.classPrivateFieldLooseBase(this, _store$h)[_store$h].getters['recent/get'](secondItem).dateUpdate;
	      if (!dateUpdate1 || !dateUpdate2) {
	        if (!dateUpdate1 && !dateUpdate2) {
	          if (babelHelpers.classPrivateFieldLooseBase(this, _isExtranet)[_isExtranet](firstItem)) {
	            return 1;
	          }
	          if (babelHelpers.classPrivateFieldLooseBase(this, _isExtranet)[_isExtranet](secondItem)) {
	            return -1;
	          }
	          return 0;
	        }
	        return dateUpdate1 ? -1 : 1;
	      }
	      return dateUpdate2 - dateUpdate1;
	    });
	    return items;
	  }
	}
	function _isExtranet2(dialogId) {
	  const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$h)[_store$h].getters['dialogues/get'](dialogId);
	  if (!dialog) {
	    return false;
	  }
	  if (dialog.type === im_v2_const.DialogType.user) {
	    const user = babelHelpers.classPrivateFieldLooseBase(this, _store$h)[_store$h].getters['users/get'](dialogId);
	    return user && user.extranet;
	  }
	  return dialog.extranet;
	}
	function _isSelfDialogId2(dialogId) {
	  return dialogId === im_v2_application_core.Core.getUserId().toString();
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
	exports.SearchService = SearchService;

}((this.BX.Messenger.v2.Provider.Service = this.BX.Messenger.v2.Provider.Service || {}),BX.Messenger.v2.Provider.Service,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Event,BX.UI.Uploader,BX.Messenger.v2.Lib,BX.Vue3.Vuex,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib));
//# sourceMappingURL=registry.bundle.js.map
