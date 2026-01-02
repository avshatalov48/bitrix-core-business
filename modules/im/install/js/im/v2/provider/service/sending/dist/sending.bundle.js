/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,main_core_events,im_v2_lib_utils,im_v2_lib_logger,im_v2_lib_rest,im_v2_application_core,im_v2_const,im_v2_provider_service_message) {
	'use strict';

	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
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
	var _updateModels = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateModels");
	var _updateMessageError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateMessageError");
	var _removeMessageError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeMessageError");
	var _sendScrollEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendScrollEvent");
	var _getDialog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialog");
	var _getDialogByChatId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialogByChatId");
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
	class SendingService {
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
	    Object.defineProperty(this, _getDialogByChatId, {
	      value: _getDialogByChatId2
	    });
	    Object.defineProperty(this, _getDialog, {
	      value: _getDialog2
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
	    Object.defineProperty(this, _updateModels, {
	      value: _updateModels2
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
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	  }
	  async sendMessage(params) {
	    const {
	      text = ''
	    } = params;
	    if (!main_core.Type.isStringFilled(text)) {
	      return;
	    }
	    im_v2_lib_logger.Logger.warn('SendingService: sendMessage', params);
	    const message = babelHelpers.classPrivateFieldLooseBase(this, _prepareMessage)[_prepareMessage](params);
	    void babelHelpers.classPrivateFieldLooseBase(this, _processMessageSending)[_processMessageSending](message);
	  }
	  async sendMessageWithFiles(params) {
	    const {
	      text = '',
	      fileIds = []
	    } = params;
	    if (!main_core.Type.isStringFilled(text) && !main_core.Type.isArrayFilled(fileIds)) {
	      return Promise.resolve();
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
	    const unsentMessage = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['messages/getById'](tempMessageId);
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
	  return babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/addLoadingMessage', {
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
	  babelHelpers.classPrivateFieldLooseBase(this, _updateModels)[_updateModels]({
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
	    chatId: babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog](dialogId).chatId,
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
	async function _handlePagination2(dialogId) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog](dialogId).hasNextPage) {
	    return Promise.resolve();
	  }
	  im_v2_lib_logger.Logger.warn('SendingService: sendMessage: there are unread pages, move to chat end');
	  const messageService = new im_v2_provider_service_message.MessageService({
	    chatId: babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog](dialogId).chatId
	  });
	  await messageService.loadContext(babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog](dialogId).lastMessageId);
	  babelHelpers.classPrivateFieldLooseBase(this, _sendScrollEvent)[_sendScrollEvent]({
	    dialogId
	  });
	  return Promise.resolve();
	}
	function _addMessageToModels2(message) {
	  babelHelpers.classPrivateFieldLooseBase(this, _addMessageToRecent)[_addMessageToRecent](message);
	  void babelHelpers.classPrivateFieldLooseBase(this, _clearLastMessageViews)[_clearLastMessageViews](message.dialogId);
	  return babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/add', message);
	}
	function _addMessageToRecent2(message) {
	  var _message$params;
	  const hasMessageText = main_core.Type.isStringFilled(message.text);
	  const hasMessageFile = main_core.Type.isArrayFilled((_message$params = message.params) == null ? void 0 : _message$params.FILE_ID);
	  if (hasMessageText || hasMessageFile) {
	    void babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('recent/update', {
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
	function _updateModels2(params) {
	  const {
	    oldId,
	    newId,
	    dialogId
	  } = params;
	  void babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/updateWithId', {
	    id: oldId,
	    fields: {
	      id: newId
	    }
	  });
	  void babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('chats/update', {
	    dialogId,
	    fields: {
	      lastId: newId,
	      lastMessageId: newId
	    }
	  });
	  void babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('recent/update', {
	    id: dialogId,
	    fields: {
	      messageId: newId
	    }
	  });
	}
	function _updateMessageError2(messageId) {
	  void babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/update', {
	    id: messageId,
	    fields: {
	      error: true
	    }
	  });
	}
	function _removeMessageError2(messageId) {
	  void babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/update', {
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
	    chatId: babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog](dialogId).chatId,
	    threshold: force ? im_v2_const.DialogScrollThreshold.none : im_v2_const.DialogScrollThreshold.halfScreenUp
	  });
	}
	function _getDialog2(dialogId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['chats/get'](dialogId, true);
	}
	function _getDialogByChatId2(chatId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['chats/getByChatId'](chatId, true);
	}
	function _needToSetAsViewed2(dialogId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['users/bots/isNetwork'](dialogId);
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
	    babelHelpers.classPrivateFieldLooseBase(this, _updateModels)[_updateModels]({
	      oldId: commentMessage.temporaryId,
	      newId: id,
	      dialogId
	    });
	  }
	  Object.entries(uuidMap).forEach(([uuid, messageId]) => {
	    babelHelpers.classPrivateFieldLooseBase(this, _updateModels)[_updateModels]({
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
	    void babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/update', {
	      id: commentMessage.temporaryId,
	      fields: {
	        error: true
	      }
	    });
	  }
	  Object.keys(forwardUuidMap).forEach(uuid => {
	    void babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/update', {
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
	    const message = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['messages/getById'](messageId);
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
	  const message = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['messages/getById'](messageId);
	  const chat = babelHelpers.classPrivateFieldLooseBase(this, _getDialogByChatId)[_getDialogByChatId](message.chatId);
	  const isForward = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['messages/isForward'](messageId);
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
	  const dialogId = babelHelpers.classPrivateFieldLooseBase(this, _getDialogByChatId)[_getDialogByChatId](chatId).dialogId;
	  if (dialogId.startsWith('chat')) {
	    return `${dialogId}/${messageId}`;
	  }
	  const currentUser = im_v2_application_core.Core.getUserId();
	  return `${dialogId}:${currentUser}/${messageId}`;
	}
	function _logSendErrors2(errors, methodName) {
	  errors.forEach(error => {
	    console.error(`SendingService: ${methodName} error: code: ${error.code} message: ${error.message}`);
	  });
	}
	function _clearLastMessageViews2(dialogId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('chats/clearLastMessageViews', {
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
	SendingService.instance = null;

	exports.SendingService = SendingService;

}((this.BX.Messenger.v2.Service = this.BX.Messenger.v2.Service || {}),BX,BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Service));
//# sourceMappingURL=sending.bundle.js.map
