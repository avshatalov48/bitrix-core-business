/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_lib_user,im_v2_lib_copilot,im_v2_lib_analytics,main_core_events,im_v2_lib_utils,rest_client,im_v2_lib_notifier,ui_vue3_vuex,im_v2_application_core,im_v2_const,im_v2_lib_logger,im_v2_lib_rest) {
	'use strict';

	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _chatId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatId");
	var _userManager = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userManager");
	var _preparedHistoryMessages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("preparedHistoryMessages");
	var _preparedUnreadMessages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("preparedUnreadMessages");
	var _isLoading = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isLoading");
	var _prepareInitialMessages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareInitialMessages");
	var _handleLoadedMessages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleLoadedMessages");
	var _updateModels = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateModels");
	var _setDialogInited = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setDialogInited");
	var _prepareTariffRestrictions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareTariffRestrictions");
	var _getDialog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialog");
	var _sendAnalytics = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendAnalytics");
	class LoadService {
	  constructor(chatId) {
	    Object.defineProperty(this, _sendAnalytics, {
	      value: _sendAnalytics2
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
	    Object.defineProperty(this, _updateModels, {
	      value: _updateModels2
	    });
	    Object.defineProperty(this, _handleLoadedMessages, {
	      value: _handleLoadedMessages2
	    });
	    Object.defineProperty(this, _prepareInitialMessages, {
	      value: _prepareInitialMessages2
	    });
	    Object.defineProperty(this, _store, {
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
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _userManager)[_userManager] = new im_v2_lib_user.UserManager();
	    babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId] = chatId;
	  }
	  async loadUnread() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] || !babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().hasNextPage) {
	      return Promise.resolve(false);
	    }
	    im_v2_lib_logger.Logger.warn('MessageService: loadUnread');
	    const lastUnreadMessageId = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['messages/getLastId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId]);
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
	      limit: LoadService.MESSAGE_REQUEST_LIMIT
	    };
	    const result = await im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatMessageTail, {
	      data: query
	    }).catch(error => {
	      console.error('MessageService: loadUnread error:', error);
	      babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	    });
	    im_v2_lib_logger.Logger.warn('MessageService: loadUnread result', result);
	    babelHelpers.classPrivateFieldLooseBase(this, _preparedUnreadMessages)[_preparedUnreadMessages] = result.messages;
	    const rawData = {
	      ...result,
	      tariffRestrictions: babelHelpers.classPrivateFieldLooseBase(this, _prepareTariffRestrictions)[_prepareTariffRestrictions](result.tariffRestrictions)
	    };
	    await babelHelpers.classPrivateFieldLooseBase(this, _updateModels)[_updateModels](rawData);
	    babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	    return Promise.resolve();
	  }
	  async loadHistory() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] || !babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().hasPrevPage) {
	      return Promise.resolve(false);
	    }
	    im_v2_lib_logger.Logger.warn('MessageService: loadHistory');
	    const lastHistoryMessageId = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['messages/getFirstId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId]);
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
	      limit: LoadService.MESSAGE_REQUEST_LIMIT
	    };
	    const result = await im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatMessageTail, {
	      data: query
	    }).catch(error => {
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
	    await babelHelpers.classPrivateFieldLooseBase(this, _updateModels)[_updateModels](rawData);
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
	    return babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/setChatCollection', {
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
	    return babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/setChatCollection', {
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
	        limit: LoadService.MESSAGE_REQUEST_LIMIT,
	        order: {
	          id: 'ASC'
	        }
	      }
	    };
	    const restResult = await im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatMessageTail, payload).catch(([error]) => {
	      console.error('MessageService: loadFirstPage error:', error);
	      babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	      throw error;
	    });
	    im_v2_lib_logger.Logger.warn('MessageService: loadFirstPage result', restResult);
	    await babelHelpers.classPrivateFieldLooseBase(this, _handleLoadedMessages)[_handleLoadedMessages](restResult);
	    await babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('chats/update', {
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
	        range: LoadService.MESSAGE_REQUEST_LIMIT
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
	      babelHelpers.classPrivateFieldLooseBase(this, _sendAnalytics)[_sendAnalytics](error);
	      im_v2_lib_notifier.Notifier.message.handleLoadContextError(error);
	      console.error('MessageService: loadContext error:', error);
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
	    const result = await im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatMessageGetContext, queryParams).catch(([error]) => {
	      console.error('MessageService: loadContextByChatId error:', error);
	      throw error;
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
	      return;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().markedId) {
	      targetMessageId = babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().markedId;
	    } else if (babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().savedPositionMessageId) {
	      targetMessageId = babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().savedPositionMessageId;
	    }
	    const wasInitedBefore = babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().inited;
	    babelHelpers.classPrivateFieldLooseBase(this, _setDialogInited)[_setDialogInited](false);
	    if (targetMessageId) {
	      void this.loadContext(targetMessageId).finally(() => {
	        babelHelpers.classPrivateFieldLooseBase(this, _setDialogInited)[_setDialogInited](true, wasInitedBefore);
	      });
	    }
	    void this.loadInitialMessages().finally(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _setDialogInited)[_setDialogInited](true, wasInitedBefore);
	    });
	  }
	  async loadInitialMessages() {
	    im_v2_lib_logger.Logger.warn('MessageService: loadInitialMessages for: ', babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId]);
	    babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = true;
	    const payload = {
	      data: {
	        chatId: babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId],
	        limit: LoadService.MESSAGE_REQUEST_LIMIT
	      }
	    };
	    const restResult = await im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatMessageList, payload).catch(([error]) => {
	      console.error('MessageService: loadInitialMessages error:', error);
	      babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	      throw error;
	    });
	    im_v2_lib_logger.Logger.warn('MessageService: loadInitialMessages result', restResult);
	    restResult.messages = babelHelpers.classPrivateFieldLooseBase(this, _prepareInitialMessages)[_prepareInitialMessages](restResult.messages);
	    await babelHelpers.classPrivateFieldLooseBase(this, _handleLoadedMessages)[_handleLoadedMessages](restResult);
	    babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	    return Promise.resolve();
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
	  const messagesCollection = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['messages/getByChatId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId]);
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
	  const messagesPromise = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/setChatCollection', {
	    messages,
	    clearCollection: true
	  });
	  const updateModelsPromise = babelHelpers.classPrivateFieldLooseBase(this, _updateModels)[_updateModels](restResult);
	  return Promise.all([messagesPromise, updateModelsPromise]);
	}
	function _updateModels2(rawData) {
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
	  const dialogPromise = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('chats/update', {
	    dialogId: babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().dialogId,
	    fields: {
	      hasPrevPage,
	      hasNextPage,
	      tariffRestrictions
	    }
	  });
	  const usersPromise = Promise.all([babelHelpers.classPrivateFieldLooseBase(this, _userManager)[_userManager].setUsersToModel(users), babelHelpers.classPrivateFieldLooseBase(this, _userManager)[_userManager].addUsersToModel(usersShort)]);
	  const filesPromise = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('files/set', files);
	  const reactionsPromise = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/reactions/set', reactions);
	  const additionalMessagesPromise = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/store', additionalMessages);
	  const commentInfoPromise = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/comments/set', commentInfo);
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
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('chats/update', {
	    dialogId: babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().dialogId,
	    fields
	  });
	}
	function _prepareTariffRestrictions2(restrictions) {
	  const dialogId = babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]().dialogId;
	  const chat = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['chats/get'](dialogId);
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
	  return babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['chats/getByChatId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId]);
	}
	function _sendAnalytics2(error) {
	  if (error.code !== im_v2_const.ErrorCode.message.notFound) {
	    return;
	  }
	  const chat = babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]();
	  const dialogId = chat.dialogId;
	  im_v2_lib_analytics.Analytics.getInstance().messageDelete.onNotFoundNotification({
	    dialogId
	  });
	}
	LoadService.MESSAGE_REQUEST_LIMIT = 25;

	var _store$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	class PinService {
	  constructor() {
	    Object.defineProperty(this, _store$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient] = im_v2_application_core.Core.getRestClient();
	  }
	  pinMessage(chatId, messageId) {
	    im_v2_lib_logger.Logger.warn(`Dialog: PinManager: pin message ${messageId}`);
	    const payload = {
	      chatId,
	      messageId
	    };
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('messages/pin/add', payload);
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient].callMethod(im_v2_const.RestMethod.imV2ChatMessagePin, {
	      id: messageId
	    }).catch(result => {
	      console.error('Dialog: PinManager: error pinning message', result.error());
	      void babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('messages/pin/delete', payload);
	    });
	  }
	  unpinMessage(chatId, messageId) {
	    im_v2_lib_logger.Logger.warn(`Dialog: PinManager: unpin message ${messageId}`);
	    const payload = {
	      chatId,
	      messageId
	    };
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('messages/pin/delete', payload);
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient].callMethod(im_v2_const.RestMethod.imV2ChatMessageUnpin, {
	      id: messageId
	    }).catch(result => {
	      console.error('Dialog: PinManager: error unpinning message', result.error());
	      void babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('messages/pin/add', payload);
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
	    im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatMessageUpdate, payload).catch(([error]) => {
	      console.error('MessageService: editMessageText error:', error);
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

	var _store$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _chatId$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatId");
	var _updateModels$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateModels");
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
	class DeleteService {
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
	    Object.defineProperty(this, _updateModels$1, {
	      value: _updateModels2$1
	    });
	    Object.defineProperty(this, _store$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _chatId$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _chatId$1)[_chatId$1] = chatId;
	    babelHelpers.classPrivateFieldLooseBase(this, _store$2)[_store$2] = im_v2_application_core.Core.getStore();
	  }
	  async deleteMessages(messageIds) {
	    im_v2_lib_logger.Logger.warn('MessageService: deleteMessage', messageIds);
	    const deleteMessageIds = [];
	    messageIds.forEach(messageId => {
	      if (im_v2_lib_utils.Utils.text.isUuidV4(messageId)) {
	        babelHelpers.classPrivateFieldLooseBase(this, _deleteTemporaryMessage)[_deleteTemporaryMessage](messageId);
	        return;
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _sendDeleteEvent)[_sendDeleteEvent](messageId);
	      babelHelpers.classPrivateFieldLooseBase(this, _updateModels$1)[_updateModels$1](messageId);
	      deleteMessageIds.push(messageId);
	    });
	    if (deleteMessageIds.length > 0) {
	      void babelHelpers.classPrivateFieldLooseBase(this, _deleteMessageOnServer)[_deleteMessageOnServer](deleteMessageIds);
	    }
	  }
	}
	function _updateModels2$1(messageId) {
	  const message = babelHelpers.classPrivateFieldLooseBase(this, _store$2)[_store$2].getters['messages/getById'](messageId);
	  if (babelHelpers.classPrivateFieldLooseBase(this, _canDeleteCompletely)[_canDeleteCompletely](message)) {
	    void babelHelpers.classPrivateFieldLooseBase(this, _completeMessageDelete)[_completeMessageDelete](message);
	    return;
	  }
	  void babelHelpers.classPrivateFieldLooseBase(this, _shallowMessageDelete)[_shallowMessageDelete](message);
	}
	function _shallowMessageDelete2(message) {
	  babelHelpers.classPrivateFieldLooseBase(this, _store$2)[_store$2].dispatch('messages/update', {
	    id: message.id,
	    fields: {
	      text: '',
	      isDeleted: true,
	      files: [],
	      attach: [],
	      replyId: 0
	    }
	  });
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
	  babelHelpers.classPrivateFieldLooseBase(this, _store$2)[_store$2].dispatch('messages/delete', {
	    id: message.id
	  });
	}
	function _updateRecentForCompleteDelete2(newLastId) {
	  const chat = babelHelpers.classPrivateFieldLooseBase(this, _getChat)[_getChat]();
	  if (!newLastId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _store$2)[_store$2].dispatch('recent/delete', {
	      id: chat.dialogId
	    });
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _store$2)[_store$2].dispatch('recent/update', {
	    id: chat.dialogId,
	    fields: {
	      messageId: newLastId
	    }
	  });
	}
	function _updateChatForCompleteDelete2(newLastId) {
	  const chat = babelHelpers.classPrivateFieldLooseBase(this, _getChat)[_getChat]();
	  babelHelpers.classPrivateFieldLooseBase(this, _store$2)[_store$2].dispatch('chats/update', {
	    dialogId: chat.dialogId,
	    fields: {
	      lastMessageId: newLastId,
	      lastId: newLastId
	    }
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _store$2)[_store$2].dispatch('chats/clearLastMessageViews', {
	    dialogId: chat.dialogId
	  });
	}
	function _deleteMessageOnServer2(messageIds) {
	  return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatMessageDelete, {
	    data: {
	      messageIds
	    }
	  }).catch(error => {
	    // eslint-disable-next-line no-console
	    console.error('MessageService: deleteMessage error:', error);
	  });
	}
	function _deleteTemporaryMessage2(messageId) {
	  const chat = babelHelpers.classPrivateFieldLooseBase(this, _getChat)[_getChat]();
	  const recentItem = babelHelpers.classPrivateFieldLooseBase(this, _store$2)[_store$2].getters['recent/get'](chat.dialogId);
	  if (recentItem.messageId === messageId) {
	    const newLastId = babelHelpers.classPrivateFieldLooseBase(this, _getPreviousMessageId)[_getPreviousMessageId](messageId);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$2)[_store$2].dispatch('recent/update', {
	      id: chat.dialogId,
	      fields: {
	        messageId: newLastId
	      }
	    });
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _store$2)[_store$2].dispatch('messages/delete', {
	    id: messageId
	  });
	}
	function _getPreviousMessageId2(messageId) {
	  var _previousMessage$id;
	  const previousMessage = babelHelpers.classPrivateFieldLooseBase(this, _store$2)[_store$2].getters['messages/getPreviousMessage']({
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
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$2)[_store$2].getters['chats/getByChatId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId$1)[_chatId$1]);
	}

	var _chatId$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatId");
	var _store$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	class MarkService {
	  constructor(chatId) {
	    Object.defineProperty(this, _chatId$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _store$3, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _chatId$2)[_chatId$2] = chatId;
	    babelHelpers.classPrivateFieldLooseBase(this, _store$3)[_store$3] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$1)[_restClient$1] = im_v2_application_core.Core.getRestClient();
	  }
	  markMessage(messageId) {
	    im_v2_lib_logger.Logger.warn('MessageService: markMessage', messageId);
	    const {
	      dialogId
	    } = babelHelpers.classPrivateFieldLooseBase(this, _store$3)[_store$3].getters['chats/getByChatId'](babelHelpers.classPrivateFieldLooseBase(this, _chatId$2)[_chatId$2]);
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$3)[_store$3].dispatch('recent/unread', {
	      id: dialogId,
	      action: true
	    });
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$3)[_store$3].dispatch('chats/update', {
	      dialogId,
	      fields: {
	        markedId: messageId
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$1)[_restClient$1].callMethod(im_v2_const.RestMethod.imV2ChatMessageMark, {
	      dialogId,
	      id: messageId
	    }).catch(result => {
	      console.error('MessageService: error marking message', result.error());
	    });
	  }
	}

	var _chatId$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatId");
	var _store$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	class FavoriteService {
	  constructor(chatId) {
	    Object.defineProperty(this, _chatId$3, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _store$4, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$2, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _chatId$3)[_chatId$3] = chatId;
	    babelHelpers.classPrivateFieldLooseBase(this, _store$4)[_store$4] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$2)[_restClient$2] = im_v2_application_core.Core.getRestClient();
	  }
	  addMessageToFavorite(messageId) {
	    im_v2_lib_logger.Logger.warn('MessageService: addMessageToFavorite', messageId);
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$2)[_restClient$2].callMethod(im_v2_const.RestMethod.imChatFavoriteAdd, {
	      MESSAGE_ID: messageId
	    }).catch(result => {
	      console.error('MessageService: error adding message to favorite', result.error());
	    });
	    im_v2_lib_notifier.Notifier.message.onAddToFavoriteComplete();
	  }
	  removeMessageFromFavorite(messageId) {
	    im_v2_lib_logger.Logger.warn('MessageService: removeMessageFromFavorite', messageId);
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$4)[_store$4].dispatch('sidebar/favorites/deleteByMessageId', {
	      chatId: babelHelpers.classPrivateFieldLooseBase(this, _chatId$3)[_chatId$3],
	      messageId
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$2)[_restClient$2].callMethod(im_v2_const.RestMethod.imChatFavoriteDelete, {
	      MESSAGE_ID: messageId
	    }).catch(result => {
	      console.error('MessageService: error removing message from favorite', result.error());
	    });
	  }
	}

	var _chatId$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatId");
	var _store$5 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	class TranscribeService {
	  constructor(chatId) {
	    Object.defineProperty(this, _chatId$4, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _store$5, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _chatId$4)[_chatId$4] = chatId;
	    babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5] = im_v2_application_core.Core.getStore();
	  }
	  transcribe(fileId) {
	    im_v2_lib_logger.Logger.warn('TranscribeService: transcribe:', fileId);
	    const payload = {
	      data: {
	        chatId: babelHelpers.classPrivateFieldLooseBase(this, _chatId$4)[_chatId$4],
	        fileId
	      }
	    };
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('files/setTranscription', {
	      fileId,
	      status: im_v2_const.TranscriptionStatus.PENDING,
	      transcriptText: null
	    });
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2DiskFileTranscribe, payload).then(result => {
	      void babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('files/setTranscription', result);
	    }).catch(error => {
	      void babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('files/setTranscription', {
	        fileId,
	        status: im_v2_const.TranscriptionStatus.ERROR,
	        transcriptText: null
	      });
	      console.error('TranscribeService: transcribe error:', error);
	    });
	  }
	}

	var _loadService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadService");
	var _pinService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pinService");
	var _editService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("editService");
	var _deleteService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("deleteService");
	var _markService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("markService");
	var _favoriteService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("favoriteService");
	var _transcribeService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("transcribeService");
	var _initServices = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initServices");
	class MessageService {
	  static getMessageRequestLimit() {
	    return LoadService.MESSAGE_REQUEST_LIMIT;
	  }
	  constructor(params) {
	    Object.defineProperty(this, _initServices, {
	      value: _initServices2
	    });
	    Object.defineProperty(this, _loadService, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _pinService, {
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
	    Object.defineProperty(this, _transcribeService, {
	      writable: true,
	      value: void 0
	    });
	    const {
	      chatId: _chatId
	    } = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _initServices)[_initServices](_chatId);
	  }
	  // region 'pagination'
	  loadUnread() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService].loadUnread();
	  }
	  loadHistory() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService].loadHistory();
	  }
	  hasPreparedHistoryMessages() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService].hasPreparedHistoryMessages();
	  }
	  drawPreparedHistoryMessages() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService].drawPreparedHistoryMessages();
	  }
	  hasPreparedUnreadMessages() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService].hasPreparedUnreadMessages();
	  }
	  drawPreparedUnreadMessages() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService].drawPreparedUnreadMessages();
	  }
	  isLoading() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService].isLoading();
	  }
	  // endregion 'pagination'

	  // region 'context'
	  loadContext(messageId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService].loadContext(messageId);
	  }
	  loadContextByChatId(chatId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService].loadContextByChatId(chatId);
	  }
	  loadFirstPage() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService].loadFirstPage();
	  }
	  // endregion 'context

	  // region 'reload messages'
	  reloadMessageList() {
	    babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService].reloadMessageList();
	  }
	  loadInitialMessages() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService].loadInitialMessages();
	  }
	  // endregion 'reload messages'

	  // region 'pin'
	  pinMessage(chatId, messageId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _pinService)[_pinService].pinMessage(chatId, messageId);
	  }
	  unpinMessage(chatId, messageId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _pinService)[_pinService].unpinMessage(chatId, messageId);
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
	  deleteMessages(messageIds) {
	    babelHelpers.classPrivateFieldLooseBase(this, _deleteService)[_deleteService].deleteMessages(messageIds);
	  }
	  // endregion 'delete'

	  // region 'transcribe'
	  transcribe(fileId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _transcribeService)[_transcribeService].transcribe(fileId);
	  }
	  // endregion 'delete'
	}
	function _initServices2(chatId) {
	  babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService] = new LoadService(chatId);
	  babelHelpers.classPrivateFieldLooseBase(this, _editService)[_editService] = new EditService();
	  babelHelpers.classPrivateFieldLooseBase(this, _deleteService)[_deleteService] = new DeleteService(chatId);
	  babelHelpers.classPrivateFieldLooseBase(this, _pinService)[_pinService] = new PinService();
	  babelHelpers.classPrivateFieldLooseBase(this, _markService)[_markService] = new MarkService(chatId);
	  babelHelpers.classPrivateFieldLooseBase(this, _favoriteService)[_favoriteService] = new FavoriteService(chatId);
	  babelHelpers.classPrivateFieldLooseBase(this, _transcribeService)[_transcribeService] = new TranscribeService(chatId);
	}

	exports.MessageService = MessageService;

}((this.BX.Messenger.v2.Service = this.BX.Messenger.v2.Service || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Event,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Lib,BX.Vue3.Vuex,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=message.bundle.js.map
