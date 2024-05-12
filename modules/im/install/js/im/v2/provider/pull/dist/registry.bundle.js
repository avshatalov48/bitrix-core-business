/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Provider = this.BX.Messenger.v2.Provider || {};
(function (exports,main_core_events,im_v2_lib_uuid,im_v2_provider_service,im_public,im_v2_lib_writing,ui_vue3_vuex,im_v2_lib_counter,im_v2_lib_utils,im_v2_model,im_v2_lib_user,im_v2_lib_desktopApi,im_v2_const,im_v2_lib_notifier,im_v2_lib_desktop,im_v2_lib_call,im_v2_lib_localStorage,im_v2_lib_soundNotification,im_v2_lib_logger,main_core,im_v2_application_core) {
	'use strict';

	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _messageViews = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("messageViews");
	var _setMessageChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setMessageChat");
	var _setUsers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setUsers");
	var _setFiles = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setFiles");
	var _setAdditionalEntities = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setAdditionalEntities");
	var _handleAddingMessageToModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleAddingMessageToModel");
	var _addMessageToModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addMessageToModel");
	var _updateDialog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateDialog");
	var _updateMessageViewedByOthers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateMessageViewedByOthers");
	var _updateChatLastMessageViews = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateChatLastMessageViews");
	var _checkMessageViewsRegistry = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("checkMessageViewsRegistry");
	var _updateMessageViewsRegistry = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateMessageViewsRegistry");
	var _sendScrollEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendScrollEvent");
	var _getDialog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialog");
	class MessagePullHandler {
	  constructor() {
	    Object.defineProperty(this, _getDialog, {
	      value: _getDialog2
	    });
	    Object.defineProperty(this, _sendScrollEvent, {
	      value: _sendScrollEvent2
	    });
	    Object.defineProperty(this, _updateMessageViewsRegistry, {
	      value: _updateMessageViewsRegistry2
	    });
	    Object.defineProperty(this, _checkMessageViewsRegistry, {
	      value: _checkMessageViewsRegistry2
	    });
	    Object.defineProperty(this, _updateChatLastMessageViews, {
	      value: _updateChatLastMessageViews2
	    });
	    Object.defineProperty(this, _updateMessageViewedByOthers, {
	      value: _updateMessageViewedByOthers2
	    });
	    Object.defineProperty(this, _updateDialog, {
	      value: _updateDialog2
	    });
	    Object.defineProperty(this, _addMessageToModel, {
	      value: _addMessageToModel2
	    });
	    Object.defineProperty(this, _handleAddingMessageToModel, {
	      value: _handleAddingMessageToModel2
	    });
	    Object.defineProperty(this, _setAdditionalEntities, {
	      value: _setAdditionalEntities2
	    });
	    Object.defineProperty(this, _setFiles, {
	      value: _setFiles2
	    });
	    Object.defineProperty(this, _setUsers, {
	      value: _setUsers2
	    });
	    Object.defineProperty(this, _setMessageChat, {
	      value: _setMessageChat2
	    });
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _messageViews, {
	      writable: true,
	      value: {}
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	  }
	  handleMessageAdd(params) {
	    im_v2_lib_logger.Logger.warn('MessagePullHandler: handleMessageAdd', params);
	    babelHelpers.classPrivateFieldLooseBase(this, _setMessageChat)[_setMessageChat](params);
	    babelHelpers.classPrivateFieldLooseBase(this, _setUsers)[_setUsers](params);
	    babelHelpers.classPrivateFieldLooseBase(this, _setFiles)[_setFiles](params);
	    babelHelpers.classPrivateFieldLooseBase(this, _setAdditionalEntities)[_setAdditionalEntities](params);
	    const messageWithTemplateId = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['messages/isInChatCollection']({
	      messageId: params.message.templateId
	    });
	    const messageWithRealId = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['messages/isInChatCollection']({
	      messageId: params.message.id
	    });

	    // update message with parsed link info
	    if (messageWithRealId) {
	      im_v2_lib_logger.Logger.warn('New message pull handler: we already have this message', params.message);
	      babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/update', {
	        id: params.message.id,
	        fields: {
	          ...params.message,
	          error: false
	        }
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _sendScrollEvent)[_sendScrollEvent](params.chatId);
	    } else if (!messageWithRealId && messageWithTemplateId) {
	      im_v2_lib_logger.Logger.warn('New message pull handler: we already have the TEMPORARY message', params.message);
	      babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/updateWithId', {
	        id: params.message.templateId,
	        fields: {
	          ...params.message,
	          error: false
	        }
	      });
	    }
	    // it's an opponent message or our own message from somewhere else
	    else if (!messageWithRealId && !messageWithTemplateId) {
	      im_v2_lib_logger.Logger.warn('New message pull handler: we dont have this message', params.message);
	      babelHelpers.classPrivateFieldLooseBase(this, _handleAddingMessageToModel)[_handleAddingMessageToModel](params);
	    }
	    im_v2_lib_writing.WritingManager.getInstance().stopWriting({
	      dialogId: params.dialogId,
	      userId: params.message.senderId
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _updateDialog)[_updateDialog](params);
	  }
	  handleMessageUpdate(params) {
	    im_v2_lib_logger.Logger.warn('MessagePullHandler: handleMessageUpdate', params);
	    im_v2_lib_writing.WritingManager.getInstance().stopWriting({
	      dialogId: params.dialogId,
	      userId: params.senderId
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/update', {
	      id: params.id,
	      fields: {
	        text: params.text,
	        params: params.params
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _sendScrollEvent)[_sendScrollEvent](params.chatId);
	  }
	  handleMessageDelete(params) {
	    im_v2_lib_logger.Logger.warn('MessagePullHandler: handleMessageDelete', params);
	    im_v2_lib_writing.WritingManager.getInstance().stopWriting({
	      dialogId: params.dialogId,
	      userId: params.senderId
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/update', {
	      id: params.id,
	      fields: {
	        text: '',
	        isDeleted: true,
	        files: [],
	        attach: [],
	        replyId: 0
	      }
	    });
	  }
	  handleMessageDeleteComplete(params) {
	    im_v2_lib_logger.Logger.warn('MessagePullHandler: handleMessageDeleteComplete', params);
	    im_v2_lib_writing.WritingManager.getInstance().stopWriting({
	      dialogId: params.dialogId,
	      userId: params.senderId
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/delete', {
	      id: params.id
	    });
	    const dialogUpdateFields = {
	      counter: params.counter
	    };
	    const lastMessageWasDeleted = Boolean(params.newLastMessage);
	    if (lastMessageWasDeleted) {
	      dialogUpdateFields.lastMessageId = params.newLastMessage.id;
	      dialogUpdateFields.lastMessageViews = params.lastMessageViews;
	      babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/store', params.newLastMessage);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('chats/update', {
	      dialogId: params.dialogId,
	      fields: dialogUpdateFields
	    });
	  }
	  handleAddReaction(params) {
	    im_v2_lib_logger.Logger.warn('MessagePullHandler: handleAddReaction', params);
	    const {
	      actualReactions: {
	        reaction: actualReactionsState,
	        usersShort
	      },
	      userId,
	      reaction
	    } = params;
	    if (im_v2_application_core.Core.getUserId() === userId) {
	      actualReactionsState.ownReactions = [reaction];
	    }
	    const userManager = new im_v2_lib_user.UserManager();
	    userManager.addUsersToModel(usersShort);
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/reactions/set', [actualReactionsState]);
	  }
	  handleDeleteReaction(params) {
	    im_v2_lib_logger.Logger.warn('MessagePullHandler: handleDeleteReaction', params);
	    const {
	      actualReactions: {
	        reaction: actualReactionsState
	      }
	    } = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/reactions/set', [actualReactionsState]);
	  }
	  handleMessageParamsUpdate(params) {
	    im_v2_lib_logger.Logger.warn('MessagePullHandler: handleMessageParamsUpdate', params);
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/update', {
	      id: params.id,
	      chatId: params.chatId,
	      fields: {
	        params: params.params
	      }
	    });
	  }
	  handleReadMessage(params, extra) {
	    im_v2_lib_logger.Logger.warn('MessagePullHandler: handleReadMessage', params);
	    const uuidManager = im_v2_lib_uuid.UuidManager.getInstance();
	    if (uuidManager.hasActionUuid(extra.action_uuid)) {
	      im_v2_lib_logger.Logger.warn('MessagePullHandler: handleReadMessage: we have this uuid, skip');
	      uuidManager.removeActionUuid(extra.action_uuid);
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/readMessages', {
	      chatId: params.chatId,
	      messageIds: params.viewedMessages
	    }).then(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('chats/update', {
	        dialogId: params.dialogId,
	        fields: {
	          counter: params.counter,
	          lastId: params.lastId
	        }
	      });
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('MessagePullHandler: error handling readMessage', error);
	    });
	  }
	  handleReadMessageOpponent(params) {
	    im_v2_lib_logger.Logger.warn('MessagePullHandler: handleReadMessageOpponent', params);
	    babelHelpers.classPrivateFieldLooseBase(this, _updateMessageViewedByOthers)[_updateMessageViewedByOthers](params);
	    babelHelpers.classPrivateFieldLooseBase(this, _updateChatLastMessageViews)[_updateChatLastMessageViews](params);
	  }
	  handlePinAdd(params) {
	    im_v2_lib_logger.Logger.warn('MessagePullHandler: handlePinAdd', params);
	    babelHelpers.classPrivateFieldLooseBase(this, _setFiles)[_setFiles](params);
	    babelHelpers.classPrivateFieldLooseBase(this, _setUsers)[_setUsers](params);
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/store', params.additionalMessages);
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/pin/add', {
	      chatId: params.pin.chatId,
	      messageId: params.pin.messageId
	    });
	    if (im_v2_application_core.Core.getUserId() !== params.pin.authorId) ;
	  }
	  handlePinDelete(params) {
	    im_v2_lib_logger.Logger.warn('MessagePullHandler: handlePinDelete', params);
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/pin/delete', {
	      chatId: params.chatId,
	      messageId: params.messageId
	    });
	  }

	  // helpers
	}
	function _setMessageChat2(params) {
	  var _params$message, _params$message$param;
	  if (!(params != null && params.chat[params.chatId])) {
	    return;
	  }
	  const chatToAdd = {
	    ...params.chat[params.chatId],
	    dialogId: params.dialogId
	  };
	  const dialogExists = Boolean(babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog](params.dialogId));
	  const messageWithoutNotification = !params.notify || ((_params$message = params.message) == null ? void 0 : (_params$message$param = _params$message.params) == null ? void 0 : _params$message$param.NOTIFY) === 'N';
	  if (!dialogExists && !messageWithoutNotification && !chatToAdd.role) {
	    chatToAdd.role = im_v2_const.UserRole.member;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('chats/set', chatToAdd);
	}
	function _setUsers2(params) {
	  if (!params.users) {
	    return;
	  }
	  const userManager = new im_v2_lib_user.UserManager();
	  userManager.setUsersToModel(Object.values(params.users));
	}
	function _setFiles2(params) {
	  if (!params.files) {
	    return;
	  }
	  const files = Object.values(params.files);
	  files.forEach(file => {
	    var _params$message2;
	    const templateFileIdExists = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['files/isInCollection']({
	      fileId: (_params$message2 = params.message) == null ? void 0 : _params$message2.templateFileId
	    });
	    if (templateFileIdExists) {
	      var _params$message3;
	      babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('files/updateWithId', {
	        id: (_params$message3 = params.message) == null ? void 0 : _params$message3.templateFileId,
	        fields: file
	      });
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('files/set', file);
	    }
	  });
	}
	function _setAdditionalEntities2(params) {
	  if (!params.message.additionalEntities) {
	    return;
	  }
	  const {
	    additionalMessages,
	    messages,
	    files,
	    users
	  } = params.message.additionalEntities;
	  const newMessages = [...messages, ...additionalMessages];
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/store', newMessages);
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('files/set', files);
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('users/set', users);
	}
	function _handleAddingMessageToModel2(params) {
	  const dialog = babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog](params.dialogId, true);
	  if (dialog.inited && dialog.hasNextPage) {
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/store', params.message);
	    return;
	  }
	  const chatIsOpened = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['application/isChatOpen'](params.dialogId);
	  const unreadMessages = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['messages/getChatUnreadMessages'](params.chatId);
	  if (!chatIsOpened && unreadMessages.length > im_v2_provider_service.MessageService.getMessageRequestLimit()) {
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/store', params.message);
	    const messageService = new im_v2_provider_service.MessageService({
	      chatId: params.chatId
	    });
	    messageService.reloadMessageList();
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _addMessageToModel)[_addMessageToModel](params.message);
	  babelHelpers.classPrivateFieldLooseBase(this, _sendScrollEvent)[_sendScrollEvent](params.chatId);
	}
	function _addMessageToModel2(message) {
	  const newMessage = {
	    ...message
	  };
	  if (message.senderId === im_v2_application_core.Core.getUserId()) {
	    newMessage.unread = false;
	  } else {
	    newMessage.unread = true;
	    newMessage.viewed = false;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/setChatCollection', {
	    messages: [newMessage]
	  });
	}
	function _updateDialog2(params) {
	  const dialog = babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog](params.dialogId, true);
	  const dialogFieldsToUpdate = {};
	  if (params.message.id > dialog.lastMessageId) {
	    dialogFieldsToUpdate.lastMessageId = params.message.id;
	  }
	  if (params.message.senderId === im_v2_application_core.Core.getUserId() && params.message.id > dialog.lastReadId) {
	    dialogFieldsToUpdate.lastId = params.message.id;
	  }
	  dialogFieldsToUpdate.counter = params.counter;
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('chats/update', {
	    dialogId: params.dialogId,
	    fields: dialogFieldsToUpdate
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('chats/clearLastMessageViews', {
	    dialogId: params.dialogId
	  });
	}
	function _updateMessageViewedByOthers2(params) {
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/setViewedByOthers', {
	    ids: params.viewedMessages
	  });
	}
	function _updateChatLastMessageViews2(params) {
	  const dialog = babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog](params.dialogId);
	  if (!dialog) {
	    return;
	  }
	  const isLastMessage = params.viewedMessages.includes(dialog.lastMessageId);
	  if (!isLastMessage) {
	    return;
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _checkMessageViewsRegistry)[_checkMessageViewsRegistry](params.userId, dialog.lastMessageId)) {
	    return;
	  }
	  const hasFirstViewer = Boolean(dialog.lastMessageViews.firstViewer);
	  if (hasFirstViewer) {
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('chats/incrementLastMessageViews', {
	      dialogId: params.dialogId
	    });
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('chats/setLastMessageViews', {
	    dialogId: params.dialogId,
	    fields: {
	      userId: params.userId,
	      userName: params.userName,
	      date: params.date,
	      messageId: dialog.lastMessageId
	    }
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _updateMessageViewsRegistry)[_updateMessageViewsRegistry](params.userId, dialog.lastMessageId);
	}
	function _checkMessageViewsRegistry2(userId, messageId) {
	  var _babelHelpers$classPr;
	  return Boolean((_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _messageViews)[_messageViews][messageId]) == null ? void 0 : _babelHelpers$classPr.has(userId));
	}
	function _updateMessageViewsRegistry2(userId, messageId) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _messageViews)[_messageViews][messageId]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _messageViews)[_messageViews][messageId] = new Set();
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _messageViews)[_messageViews][messageId].add(userId);
	}
	function _sendScrollEvent2(chatId) {
	  main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.scrollToBottom, {
	    chatId,
	    threshold: im_v2_const.DialogScrollThreshold.nearTheBottom
	  });
	}
	function _getDialog2(dialogId, temporary = false) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['chats/get'](dialogId, temporary);
	}

	var _store$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _updateChatUsers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateChatUsers");
	class ChatPullHandler {
	  constructor() {
	    Object.defineProperty(this, _updateChatUsers, {
	      value: _updateChatUsers2
	    });
	    Object.defineProperty(this, _store$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1] = im_v2_application_core.Core.getStore();
	  }
	  handleChatOwner(params) {
	    im_v2_lib_logger.Logger.warn('ChatPullHandler: handleChatOwner', params);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('chats/update', {
	      dialogId: params.dialogId,
	      fields: {
	        ownerId: params.userId
	      }
	    });
	  }
	  handleChatManagers(params) {
	    im_v2_lib_logger.Logger.warn('ChatPullHandler: handleChatManagers', params);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('chats/update', {
	      dialogId: params.dialogId,
	      fields: {
	        managerList: params.list
	      }
	    });
	  }
	  handleChatUserAdd(params) {
	    im_v2_lib_logger.Logger.warn('ChatPullHandler: handleChatUserAdd', params);
	    babelHelpers.classPrivateFieldLooseBase(this, _updateChatUsers)[_updateChatUsers](params);
	  }
	  handleChatUserLeave(params) {
	    im_v2_lib_logger.Logger.warn('ChatPullHandler: handleChatUserLeave', params);
	    const currentUserIsKicked = params.userId === im_v2_application_core.Core.getUserId();
	    if (currentUserIsKicked) {
	      babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('chats/update', {
	        dialogId: params.dialogId,
	        fields: {
	          inited: false
	        }
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('messages/clearChatCollection', {
	        chatId: params.chatId
	      });
	    }
	    const chatIsOpened = babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].getters['application/isChatOpen'](params.dialogId);
	    if (currentUserIsKicked && chatIsOpened) {
	      im_public.Messenger.openChat();
	    }
	    const chatHasCall = im_v2_lib_call.CallManager.getInstance().getCurrentCallDialogId() === params.dialogId;
	    if (currentUserIsKicked && chatHasCall) {
	      im_v2_lib_call.CallManager.getInstance().leaveCurrentCall();
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _updateChatUsers)[_updateChatUsers](params);
	  }
	  handleStartWriting(params) {
	    im_v2_lib_logger.Logger.warn('ChatPullHandler: handleStartWriting', params);
	    const {
	      dialogId,
	      userId,
	      userName
	    } = params;
	    im_v2_lib_writing.WritingManager.getInstance().startWriting({
	      dialogId,
	      userId,
	      userName
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('users/update', {
	      id: userId,
	      fields: {
	        lastActivityDate: new Date()
	      }
	    });
	  }
	  handleChatUnread(params) {
	    im_v2_lib_logger.Logger.warn('ChatPullHandler: handleChatUnread', params);
	    let markedId = 0;
	    if (params.active === true) {
	      markedId = params.markedId;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('chats/update', {
	      dialogId: params.dialogId,
	      fields: {
	        markedId
	      }
	    });
	  }
	  handleChatMuteNotify(params) {
	    if (params.muted) {
	      babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('chats/mute', {
	        dialogId: params.dialogId
	      });
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('chats/unmute', {
	      dialogId: params.dialogId
	    });
	  }
	  handleChatRename(params) {
	    const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].getters['chats/getByChatId'](params.chatId);
	    if (!dialog) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('chats/update', {
	      dialogId: dialog.dialogId,
	      fields: {
	        name: params.name
	      }
	    });
	  }
	  handleChatAvatar(params) {
	    const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].getters['chats/getByChatId'](params.chatId);
	    if (!dialog) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('chats/update', {
	      dialogId: dialog.dialogId,
	      fields: {
	        avatar: params.avatar
	      }
	    });
	  }
	  handleReadAllChats() {
	    im_v2_lib_logger.Logger.warn('ChatPullHandler: handleReadAllChats');
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('chats/clearCounters');
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('recent/clearUnread');
	  }
	}
	function _updateChatUsers2(params) {
	  if (params.users) {
	    const userManager = new im_v2_lib_user.UserManager();
	    userManager.setUsersToModel(Object.values(params.users));
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('chats/update', {
	    dialogId: params.dialogId,
	    fields: {
	      userCounter: params.userCount
	    }
	  });
	}

	var _store$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	class UserPullHandler {
	  constructor() {
	    Object.defineProperty(this, _store$2, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$2)[_store$2] = im_v2_application_core.Core.getStore();
	  }
	  handleUserInvite(params) {
	    if (params.invited) {
	      const userManager = new im_v2_lib_user.UserManager();
	      userManager.setUsersToModel([params.user]);
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _store$2)[_store$2].dispatch('users/update', {
	      id: params.userId,
	      fields: params.user
	    });
	  }
	}

	class DesktopPullHandler {
	  handleDesktopOnline(params) {
	    im_v2_lib_logger.Logger.warn('DesktopPullHandler: handleDesktopOnline', params);
	    const desktopManager = im_v2_lib_desktop.DesktopManager.getInstance();
	    desktopManager.setDesktopActive(true);
	    im_v2_lib_counter.CounterManager.getInstance().removeBrowserTitleCounter();
	  }
	  handleDesktopOffline() {
	    im_v2_lib_logger.Logger.warn('DesktopPullHandler: handleDesktopOffline');
	    im_v2_lib_desktop.DesktopManager.getInstance().setDesktopActive(false);
	  }
	}

	class SettingsPullHandler {
	  handleSettingsUpdate(params) {
	    im_v2_lib_logger.Logger.warn('SettingsPullHandler: handleSettingsUpdate', params);
	    Object.entries(params).forEach(([optionName, optionValue]) => {
	      im_v2_application_core.Core.getStore().dispatch('application/settings/set', {
	        [optionName]: optionValue
	      });
	    });
	  }
	}

	var _messageHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("messageHandler");
	var _chatHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatHandler");
	var _userHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userHandler");
	var _desktopHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("desktopHandler");
	var _settingsHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settingsHandler");
	class BasePullHandler {
	  constructor() {
	    Object.defineProperty(this, _messageHandler, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _chatHandler, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _userHandler, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _desktopHandler, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _settingsHandler, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _messageHandler)[_messageHandler] = new MessagePullHandler();
	    babelHelpers.classPrivateFieldLooseBase(this, _chatHandler)[_chatHandler] = new ChatPullHandler();
	    babelHelpers.classPrivateFieldLooseBase(this, _userHandler)[_userHandler] = new UserPullHandler();
	    babelHelpers.classPrivateFieldLooseBase(this, _desktopHandler)[_desktopHandler] = new DesktopPullHandler();
	    babelHelpers.classPrivateFieldLooseBase(this, _settingsHandler)[_settingsHandler] = new SettingsPullHandler();
	  }
	  getModuleId() {
	    return 'im';
	  }

	  // region 'message'
	  handleMessage(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _messageHandler)[_messageHandler].handleMessageAdd(params);
	  }
	  handleMessageChat(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _messageHandler)[_messageHandler].handleMessageAdd(params);
	  }
	  handleMessageUpdate(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _messageHandler)[_messageHandler].handleMessageUpdate(params);
	  }
	  handleMessageDelete(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _messageHandler)[_messageHandler].handleMessageDelete(params);
	  }
	  handleMessageDeleteComplete(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _messageHandler)[_messageHandler].handleMessageDeleteComplete(params);
	  }
	  handleAddReaction(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _messageHandler)[_messageHandler].handleAddReaction(params);
	  }
	  handleDeleteReaction(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _messageHandler)[_messageHandler].handleDeleteReaction(params);
	  }
	  handleMessageParamsUpdate(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _messageHandler)[_messageHandler].handleMessageParamsUpdate(params);
	  }
	  handleReadMessage(params, extra) {
	    babelHelpers.classPrivateFieldLooseBase(this, _messageHandler)[_messageHandler].handleReadMessage(params, extra);
	  }
	  handleReadMessageChat(params, extra) {
	    babelHelpers.classPrivateFieldLooseBase(this, _messageHandler)[_messageHandler].handleReadMessage(params, extra);
	  }
	  handleReadMessageOpponent(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _messageHandler)[_messageHandler].handleReadMessageOpponent(params);
	  }
	  handleReadMessageChatOpponent(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _messageHandler)[_messageHandler].handleReadMessageOpponent(params);
	  }
	  handlePinAdd(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _messageHandler)[_messageHandler].handlePinAdd(params);
	  }
	  handlePinDelete(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _messageHandler)[_messageHandler].handlePinDelete(params);
	  }
	  // endregion 'message'

	  // region 'chat'
	  handleChatOwner(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _chatHandler)[_chatHandler].handleChatOwner(params);
	  }
	  handleChatManagers(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _chatHandler)[_chatHandler].handleChatManagers(params);
	  }
	  handleChatUserAdd(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _chatHandler)[_chatHandler].handleChatUserAdd(params);
	  }
	  handleChatUserLeave(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _chatHandler)[_chatHandler].handleChatUserLeave(params);
	  }
	  handleStartWriting(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _chatHandler)[_chatHandler].handleStartWriting(params);
	  }
	  handleChatUnread(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _chatHandler)[_chatHandler].handleChatUnread(params);
	  }
	  handleReadAllChats() {
	    babelHelpers.classPrivateFieldLooseBase(this, _chatHandler)[_chatHandler].handleReadAllChats();
	  }
	  handleChatMuteNotify(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _chatHandler)[_chatHandler].handleChatMuteNotify(params);
	  }
	  handleChatRename(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _chatHandler)[_chatHandler].handleChatRename(params);
	  }
	  handleChatAvatar(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _chatHandler)[_chatHandler].handleChatAvatar(params);
	  }
	  // endregion 'chat'

	  // region 'user'
	  handleUserInvite(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _userHandler)[_userHandler].handleUserInvite(params);
	  }
	  // endregion 'user'

	  // region 'desktop'
	  handleDesktopOnline(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _desktopHandler)[_desktopHandler].handleDesktopOnline(params);
	  }
	  handleDesktopOffline() {
	    babelHelpers.classPrivateFieldLooseBase(this, _desktopHandler)[_desktopHandler].handleDesktopOffline();
	  }
	  // endregion 'desktop'

	  // region 'settings'
	  handleSettingsUpdate(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _settingsHandler)[_settingsHandler].handleSettingsUpdate(params);
	  }
	  // endregion 'settings'
	}

	const AddMethodByChatType = {
	  [im_v2_const.ChatType.copilot]: 'recent/setCopilot',
	  default: 'recent/setRecent'
	};

	// noinspection JSUnusedGlobalSymbols
	class RecentPullHandler {
	  getModuleId() {
	    return 'im';
	  }
	  handleMessage(params) {
	    this.handleMessageAdd(params);
	  }
	  handleMessageChat(params) {
	    this.handleMessageAdd(params);
	  }
	  handleMessageAdd(params) {
	    if (params.lines) {
	      return;
	    }
	    const chatUsers = params.userInChat[params.chatId];
	    if (chatUsers && !chatUsers.includes(im_v2_application_core.Core.getUserId())) {
	      return;
	    }
	    im_v2_lib_logger.Logger.warn('RecentPullHandler: handleMessageAdd', params);
	    const newRecentItem = {
	      id: params.dialogId,
	      messageId: params.message.id
	    };
	    const recentItem = im_v2_application_core.Core.getStore().getters['recent/get'](params.dialogId);
	    if (recentItem) {
	      newRecentItem.isFakeElement = false;
	      newRecentItem.isBirthdayPlaceholder = false;
	      newRecentItem.liked = false;
	    }
	    this.setRecentItem(params, newRecentItem);
	  }
	  handleMessageDeleteComplete(params) {
	    const lastMessageWasDeleted = Boolean(params.newLastMessage);
	    if (lastMessageWasDeleted) {
	      this.updateRecentForMessageDelete(params.dialogId, params.newLastMessage.id);
	    }
	    this.updateUnloadedChatCounter(params);
	  }

	  /* region Counters handling */
	  handleReadMessage(params) {
	    this.updateUnloadedChatCounter(params);
	  }
	  handleReadMessageChat(params) {
	    this.updateUnloadedChatCounter(params);
	  }
	  handleUnreadMessage(params) {
	    this.updateUnloadedChatCounter(params);
	  }
	  handleUnreadMessageChat(params) {
	    this.updateUnloadedChatCounter(params);
	  }
	  handleChatMuteNotify(params) {
	    this.updateUnloadedChatCounter(params);
	  }
	  handleChatUnread(params) {
	    im_v2_lib_logger.Logger.warn('RecentPullHandler: handleChatUnread', params);
	    this.updateUnloadedChatCounter({
	      dialogId: params.dialogId,
	      chatId: params.chatId,
	      counter: params.counter,
	      muted: params.muted,
	      unread: params.active
	    });
	    im_v2_application_core.Core.getStore().dispatch('recent/unread', {
	      id: params.dialogId,
	      action: params.active
	    });
	  }
	  /* endregion Counters handling */

	  handleAddReaction(params) {
	    im_v2_lib_logger.Logger.warn('RecentPullHandler: handleAddReaction', params);
	    const recentItem = im_v2_application_core.Core.getStore().getters['recent/get'](params.dialogId);
	    if (!recentItem) {
	      return;
	    }
	    const chatIsOpened = im_v2_application_core.Core.getStore().getters['application/isChatOpen'](params.dialogId);
	    if (chatIsOpened) {
	      return;
	    }
	    const message = im_v2_application_core.Core.getStore().getters['recent/getMessage'](params.dialogId);
	    const isOwnLike = im_v2_application_core.Core.getUserId() === params.userId;
	    const isOwnLastMessage = im_v2_application_core.Core.getUserId() === message.authorId;
	    if (isOwnLike || !isOwnLastMessage) {
	      return;
	    }
	    im_v2_application_core.Core.getStore().dispatch('recent/like', {
	      id: params.dialogId,
	      messageId: params.actualReactions.reaction.messageId,
	      liked: true
	    });
	  }
	  handleChatPin(params) {
	    im_v2_lib_logger.Logger.warn('RecentPullHandler: handleChatPin', params);
	    const recentItem = im_v2_application_core.Core.getStore().getters['recent/get'](params.dialogId);
	    if (!recentItem) {
	      return;
	    }
	    im_v2_application_core.Core.getStore().dispatch('recent/pin', {
	      id: params.dialogId,
	      action: params.active
	    });
	  }
	  handleChatHide(params) {
	    im_v2_lib_logger.Logger.warn('RecentPullHandler: handleChatHide', params);
	    const recentItem = im_v2_application_core.Core.getStore().getters['recent/get'](params.dialogId);
	    if (!recentItem) {
	      return;
	    }
	    im_v2_application_core.Core.getStore().dispatch('recent/delete', {
	      id: params.dialogId
	    });
	  }
	  handleChatUserLeave(params) {
	    im_v2_lib_logger.Logger.warn('RecentPullHandler: handleChatUserLeave', params);
	    const recentItem = im_v2_application_core.Core.getStore().getters['recent/get'](params.dialogId);
	    if (!recentItem || params.userId !== im_v2_application_core.Core.getUserId()) {
	      return;
	    }
	    im_v2_application_core.Core.getStore().dispatch('recent/delete', {
	      id: params.dialogId
	    });
	  }
	  handleUserInvite(params) {
	    var _params$invited;
	    im_v2_lib_logger.Logger.warn('RecentPullHandler: handleUserInvite', params);
	    const messageId = im_v2_lib_utils.Utils.text.getUuidV4();
	    im_v2_application_core.Core.getStore().dispatch('messages/store', {
	      id: messageId,
	      date: params.date
	    });
	    im_v2_application_core.Core.getStore().dispatch('recent/setRecent', {
	      id: params.user.id,
	      invited: (_params$invited = params.invited) != null ? _params$invited : false,
	      isFakeElement: true,
	      messageId
	    });
	  }
	  updateUnloadedChatCounter(params) {
	    const {
	      dialogId,
	      chatId,
	      counter,
	      muted,
	      unread,
	      lines = false
	    } = params;
	    if (lines) {
	      return;
	    }
	    const recentItem = im_v2_application_core.Core.getStore().getters['recent/get'](dialogId);
	    if (recentItem) {
	      return;
	    }
	    im_v2_lib_logger.Logger.warn('RecentPullHandler: updateUnloadedChatCounter:', {
	      dialogId,
	      chatId,
	      counter,
	      muted,
	      unread
	    });
	    let newCounter = 0;
	    if (muted) {
	      newCounter = 0;
	    } else if (unread && counter === 0) {
	      newCounter = 1;
	    } else if (unread && counter > 0) {
	      newCounter = counter;
	    } else if (!unread) {
	      newCounter = counter;
	    }
	    im_v2_application_core.Core.getStore().dispatch('counters/setUnloadedChatCounters', {
	      [chatId]: newCounter
	    });
	  }
	  setRecentItem(params, newRecentItem) {
	    var _params$chat$params$c, _AddMethodByChatType$;
	    const newMessageChatType = (_params$chat$params$c = params.chat[params.chatId]) == null ? void 0 : _params$chat$params$c.type;
	    const addMethod = (_AddMethodByChatType$ = AddMethodByChatType[newMessageChatType]) != null ? _AddMethodByChatType$ : AddMethodByChatType.default;
	    im_v2_application_core.Core.getStore().dispatch(addMethod, newRecentItem);
	  }
	  updateRecentForMessageDelete(dialogId, newLastMessageId) {
	    if (!newLastMessageId) {
	      im_v2_application_core.Core.getStore().dispatch('recent/delete', {
	        id: dialogId
	      });
	      return;
	    }
	    im_v2_application_core.Core.getStore().dispatch('recent/update', {
	      id: dialogId,
	      fields: {
	        messageId: newLastMessageId
	      }
	    });
	  }
	}

	class NotificationPullHandler {
	  constructor() {
	    this.store = im_v2_application_core.Core.getStore();
	    this.userManager = new im_v2_lib_user.UserManager();
	    this.updateCounterDebounced = main_core.Runtime.debounce(this.updateCounter, 1500, this);
	  }
	  getModuleId() {
	    return 'im';
	  }
	  getSubscriptionType() {
	    return 'server';
	  }
	  handleNotifyAdd(params) {
	    if (params.onlyFlash === true) {
	      return;
	    }
	    this.userManager.setUsersToModel(params.users);
	    this.store.dispatch('notifications/set', params);
	    this.updateCounterDebounced(params.counter);
	  }
	  handleNotifyConfirm(params) {
	    this.store.dispatch('notifications/delete', {
	      id: params.id
	    });
	    this.updateCounterDebounced(params.counter);
	  }
	  handleNotifyRead(params) {
	    params.list.forEach(id => {
	      this.store.dispatch('notifications/read', {
	        ids: [id],
	        read: true
	      });
	    });
	    this.updateCounterDebounced(params.counter);
	  }
	  handleNotifyUnread(params) {
	    params.list.forEach(id => {
	      this.store.dispatch('notifications/read', {
	        ids: [id],
	        read: false
	      });
	    });
	    this.updateCounterDebounced(params.counter);
	  }
	  handleNotifyDelete(params) {
	    const idsToDelete = Object.keys(params.id).map(id => Number.parseInt(id, 10));
	    idsToDelete.forEach(id => {
	      this.store.dispatch('notifications/delete', {
	        id
	      });
	    });
	    this.updateCounterDebounced(params.counter);
	  }
	  updateCounter(counter) {
	    this.store.dispatch('notifications/setCounter', counter);
	  }
	}

	class SidebarPullHandler {
	  constructor() {
	    this.store = im_v2_application_core.Core.getStore();
	    this.userManager = new im_v2_lib_user.UserManager();
	  }
	  getModuleId() {
	    return 'im';
	  }

	  // region members
	  handleChatUserAdd(params) {
	    if (this.getMembersCountFromStore(params.chatId) === 0) {
	      return;
	    }
	    void this.userManager.setUsersToModel(Object.values(params.users));
	    void this.store.dispatch('sidebar/members/set', {
	      chatId: params.chatId,
	      users: params.newUsers
	    });
	  }
	  handleChatUserLeave(params) {
	    if (this.getMembersCountFromStore(params.chatId) === 0) {
	      return;
	    }
	    void this.store.dispatch('sidebar/members/delete', {
	      chatId: params.chatId,
	      userId: params.userId
	    });
	  }
	  // endregion

	  // region task
	  handleTaskAdd(params) {
	    if (!this.isSidebarInited(params.link.chatId)) {
	      return;
	    }
	    void this.userManager.setUsersToModel(params.users);
	    void this.store.dispatch('sidebar/tasks/set', {
	      chatId: params.link.chatId,
	      tasks: [params.link]
	    });
	  }
	  handleTaskUpdate(params, extra) {
	    this.handleTaskAdd(params, extra);
	  }
	  handleTaskDelete(params) {
	    if (!this.isSidebarInited(params.chatId)) {
	      return;
	    }
	    void this.store.dispatch('sidebar/tasks/delete', {
	      chatId: params.chatId,
	      id: params.linkId
	    });
	  }
	  // endregion

	  // region meetings
	  handleCalendarAdd(params) {
	    if (!this.isSidebarInited(params.link.chatId)) {
	      return;
	    }
	    void this.userManager.setUsersToModel(params.users);
	    void this.store.dispatch('sidebar/meetings/set', {
	      chatId: params.link.chatId,
	      meetings: [params.link]
	    });
	  }
	  handleCalendarUpdate(params, extra) {
	    this.handleCalendarAdd(params, extra);
	  }
	  handleCalendarDelete(params) {
	    if (!this.isSidebarInited(params.chatId)) {
	      return;
	    }
	    void this.store.dispatch('sidebar/meetings/delete', {
	      chatId: params.chatId,
	      id: params.linkId
	    });
	  }
	  // endregion

	  // region links
	  handleUrlAdd(params) {
	    if (!this.isSidebarInited(params.link.chatId)) {
	      return;
	    }
	    void this.userManager.setUsersToModel(params.users);
	    void this.store.dispatch('sidebar/links/set', {
	      chatId: params.link.chatId,
	      links: [params.link]
	    });
	    const counter = this.store.getters['sidebar/links/getCounter'](params.link.chatId);
	    void this.store.dispatch('sidebar/links/setCounter', {
	      chatId: params.link.chatId,
	      counter: counter + 1
	    });
	  }
	  handleUrlDelete(params) {
	    if (!this.isSidebarInited(params.chatId)) {
	      return;
	    }
	    void this.store.dispatch('sidebar/links/delete', {
	      chatId: params.chatId,
	      id: params.linkId
	    });
	  }
	  // endregion

	  // region favorite
	  handleMessageFavoriteAdd(params) {
	    if (!this.isSidebarInited(params.link.chatId)) {
	      return;
	    }
	    void this.userManager.setUsersToModel(params.users);
	    void this.store.dispatch('files/set', params.files);
	    void this.store.dispatch('messages/store', [params.link.message]);
	    void this.store.dispatch('sidebar/favorites/set', {
	      chatId: params.link.chatId,
	      favorites: [params.link]
	    });
	    const counter = this.store.getters['sidebar/favorites/getCounter'](params.link.chatId);
	    void this.store.dispatch('sidebar/favorites/setCounter', {
	      chatId: params.link.chatId,
	      counter: counter + 1
	    });
	  }
	  handleMessageFavoriteDelete(params) {
	    if (!this.isSidebarInited(params.chatId)) {
	      return;
	    }
	    void this.store.dispatch('sidebar/favorites/delete', {
	      chatId: params.chatId,
	      id: params.linkId
	    });
	  }
	  // endregion

	  // region files
	  handleFileAdd(params) {
	    var _params$link$subType;
	    if (!this.isSidebarInited(params.link.chatId)) {
	      return;
	    }
	    void this.userManager.setUsersToModel(params.users);
	    void this.store.dispatch('files/set', params.files);
	    const subType = (_params$link$subType = params.link.subType) != null ? _params$link$subType : im_v2_const.SidebarDetailBlock.fileUnsorted;
	    void this.store.dispatch('sidebar/files/set', {
	      chatId: params.link.chatId,
	      files: [params.link],
	      subType
	    });
	  }
	  handleFileDelete(params) {
	    var _params$linkId;
	    const chatId = main_core.Type.isNumber(params.chatId) ? params.chatId : Number.parseInt(params.chatId, 10);
	    if (!this.isSidebarInited(chatId)) {
	      return;
	    }
	    const sidebarFileId = (_params$linkId = params.linkId) != null ? _params$linkId : params.fileId;
	    void this.store.dispatch('sidebar/files/delete', {
	      chatId,
	      id: sidebarFileId
	    });
	  }
	  // endregion

	  // region files unsorted
	  handleMessageChat(params) {
	    // handle new files while migration is not finished.
	    if (!this.isSidebarInited(params.chatId) || this.isFilesMigrated()) {
	      return;
	    }
	    void this.userManager.setUsersToModel(Object.values(params.users));
	    void this.store.dispatch('files/set', Object.values(params.files));
	    Object.values(params.files).forEach(file => {
	      void this.store.dispatch('sidebar/files/set', {
	        chatId: file.chatId,
	        files: [file],
	        subType: im_v2_const.SidebarDetailBlock.fileUnsorted
	      });
	    });
	  }
	  // endregion

	  isSidebarInited(chatId) {
	    return this.store.getters['sidebar/isInited'](chatId);
	  }
	  isFilesMigrated() {
	    return this.store.state.sidebar.isFilesMigrated;
	  }
	  getMembersCountFromStore(chatId) {
	    return this.store.getters['sidebar/members/getSize'](chatId);
	  }
	}

	var _shouldShowNotification = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("shouldShowNotification");
	var _shouldShowLinesNotification = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("shouldShowLinesNotification");
	var _isChatOpened = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isChatOpened");
	var _isLinesChatOpened = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isLinesChatOpened");
	var _isImportantMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isImportantMessage");
	var _shouldShowToUser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("shouldShowToUser");
	var _isUserDnd = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isUserDnd");
	var _desktopWillShowNotification = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("desktopWillShowNotification");
	var _flashDesktopIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("flashDesktopIcon");
	var _playOpenedChatMessageSound = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("playOpenedChatMessageSound");
	var _playMessageSound = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("playMessageSound");
	var _restoreLastNotificationId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restoreLastNotificationId");
	var _updateLastNotificationId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateLastNotificationId");
	var _setCurrentUserStatus = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setCurrentUserStatus");
	class NotifierPullHandler {
	  constructor() {
	    Object.defineProperty(this, _setCurrentUserStatus, {
	      value: _setCurrentUserStatus2
	    });
	    Object.defineProperty(this, _updateLastNotificationId, {
	      value: _updateLastNotificationId2
	    });
	    Object.defineProperty(this, _restoreLastNotificationId, {
	      value: _restoreLastNotificationId2
	    });
	    Object.defineProperty(this, _playMessageSound, {
	      value: _playMessageSound2
	    });
	    Object.defineProperty(this, _playOpenedChatMessageSound, {
	      value: _playOpenedChatMessageSound2
	    });
	    Object.defineProperty(this, _flashDesktopIcon, {
	      value: _flashDesktopIcon2
	    });
	    Object.defineProperty(this, _desktopWillShowNotification, {
	      value: _desktopWillShowNotification2
	    });
	    Object.defineProperty(this, _isUserDnd, {
	      value: _isUserDnd2
	    });
	    Object.defineProperty(this, _shouldShowToUser, {
	      value: _shouldShowToUser2
	    });
	    Object.defineProperty(this, _isImportantMessage, {
	      value: _isImportantMessage2
	    });
	    Object.defineProperty(this, _isLinesChatOpened, {
	      value: _isLinesChatOpened2
	    });
	    Object.defineProperty(this, _isChatOpened, {
	      value: _isChatOpened2
	    });
	    Object.defineProperty(this, _shouldShowLinesNotification, {
	      value: _shouldShowLinesNotification2
	    });
	    Object.defineProperty(this, _shouldShowNotification, {
	      value: _shouldShowNotification2
	    });
	    this.lastNotificationId = 0;
	    this.store = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _setCurrentUserStatus)[_setCurrentUserStatus]();
	    babelHelpers.classPrivateFieldLooseBase(this, _restoreLastNotificationId)[_restoreLastNotificationId]();
	  }
	  getModuleId() {
	    return 'im';
	  }
	  handleMessage(params, extraData) {
	    this.handleMessageAdd(params, extraData);
	  }
	  handleMessageChat(params, extraData) {
	    this.handleMessageAdd(params, extraData);
	  }
	  handleMessageAdd(params, extraData) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _shouldShowNotification)[_shouldShowNotification](params, extraData)) {
	      return;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isChatOpened)[_isChatOpened](params.dialogId)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _playOpenedChatMessageSound)[_playOpenedChatMessageSound](params);
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _playMessageSound)[_playMessageSound](params);
	    babelHelpers.classPrivateFieldLooseBase(this, _flashDesktopIcon)[_flashDesktopIcon]();
	    const message = this.store.getters['messages/getById'](params.message.id);
	    const dialog = this.store.getters['chats/get'](params.dialogId, true);
	    const user = this.store.getters['users/get'](message.authorId);
	    im_v2_lib_notifier.NotifierManager.getInstance().showMessage({
	      message,
	      dialog,
	      user,
	      lines: Boolean(params.lines)
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _updateLastNotificationId)[_updateLastNotificationId](params.message.id);
	  }
	  handleNotifyAdd(params, extraData) {
	    if (extraData.server_time_ago > 10) {
	      im_v2_lib_logger.Logger.warn('NotifierPullHandler: notification arrived to the user 30 seconds after it was actually sent, ignore notification');
	      return;
	    }
	    if (params.id <= this.lastNotificationId) {
	      im_v2_lib_logger.Logger.warn('NotifierPullHandler: new notification id is smaller than lastNotificationId');
	      return;
	    }
	    if (params.onlyFlash === true || babelHelpers.classPrivateFieldLooseBase(this, _isUserDnd)[_isUserDnd]() || babelHelpers.classPrivateFieldLooseBase(this, _desktopWillShowNotification)[_desktopWillShowNotification]() || im_v2_lib_call.CallManager.getInstance().hasCurrentCall()) {
	      return;
	    }
	    if (document.hasFocus()) {
	      const areNotificationsOpen = this.store.getters['application/areNotificationsOpen'];
	      if (areNotificationsOpen) {
	        return;
	      }
	    }
	    const notification = this.store.getters['notifications/getById'](params.id);
	    const user = this.store.getters['users/get'](params.userId);
	    if (params.silent !== 'Y') {
	      im_v2_lib_soundNotification.SoundNotificationManager.getInstance().playOnce(im_v2_const.SoundType.reminder);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _flashDesktopIcon)[_flashDesktopIcon]();
	    im_v2_lib_notifier.NotifierManager.getInstance().showNotification(notification, user);
	    babelHelpers.classPrivateFieldLooseBase(this, _updateLastNotificationId)[_updateLastNotificationId](params.id);
	  }
	}
	function _shouldShowNotification2(params, extraData) {
	  var _params$message, _params$message$param;
	  if (extraData.server_time_ago > 10) {
	    im_v2_lib_logger.Logger.warn('NotifierPullHandler: message arrived to the user 30 seconds after it was actually sent, ignore message');
	    return false;
	  }
	  if (params.message.id <= this.lastNotificationId) {
	    im_v2_lib_logger.Logger.warn('NotifierPullHandler: new message id is smaller than lastNotificationId');
	    return false;
	  }
	  if (im_v2_application_core.Core.getUserId() === params.message.senderId) {
	    return false;
	  }
	  if (params.lines && !babelHelpers.classPrivateFieldLooseBase(this, _shouldShowLinesNotification)[_shouldShowLinesNotification](params)) {
	    return false;
	  }
	  const messageWithoutNotification = !params.notify || ((_params$message = params.message) == null ? void 0 : (_params$message$param = _params$message.params) == null ? void 0 : _params$message$param.NOTIFY) === 'N';
	  if (messageWithoutNotification || !babelHelpers.classPrivateFieldLooseBase(this, _shouldShowToUser)[_shouldShowToUser](params) || babelHelpers.classPrivateFieldLooseBase(this, _desktopWillShowNotification)[_desktopWillShowNotification]()) {
	    return false;
	  }
	  const callIsActive = im_v2_lib_call.CallManager.getInstance().hasCurrentCall();
	  if (callIsActive && im_v2_lib_call.CallManager.getInstance().getCurrentCallDialogId() !== params.dialogId.toString()) {
	    return false;
	  }
	  const screenSharingIsActive = im_v2_lib_call.CallManager.getInstance().hasCurrentScreenSharing();
	  if (screenSharingIsActive) {
	    return false;
	  }
	  return true;
	}
	function _shouldShowLinesNotification2(params) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isLinesChatOpened)[_isLinesChatOpened](params.dialogId)) {
	    return false;
	  }
	  const authorId = params.message.senderId;
	  if (authorId > 0 && params.users[authorId].extranet === false) {
	    return true;
	  }
	  const counter = this.store.getters['counters/getSpecificLinesCounter'](params.chatId);
	  return counter === 0;
	}
	function _isChatOpened2(dialogId) {
	  const isChatOpen = this.store.getters['application/isChatOpen'](dialogId);
	  return Boolean(document.hasFocus() && isChatOpen);
	}
	function _isLinesChatOpened2(dialogId) {
	  const isLinesChatOpen = this.store.getters['application/isLinesChatOpen'](dialogId);
	  return Boolean(document.hasFocus() && isLinesChatOpen);
	}
	function _isImportantMessage2(params) {
	  const {
	    message
	  } = params;
	  return message.isImportant || message.importantFor.includes(im_v2_application_core.Core.getUserId());
	}
	function _shouldShowToUser2(params) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isImportantMessage)[_isImportantMessage](params)) {
	    return true;
	  }
	  const dialog = this.store.getters['chats/get'](params.dialogId, true);
	  const isMuted = dialog.muteList.includes(im_v2_application_core.Core.getUserId());
	  return !babelHelpers.classPrivateFieldLooseBase(this, _isUserDnd)[_isUserDnd]() && !isMuted;
	}
	function _isUserDnd2() {
	  const status = this.store.getters['application/settings/get'](im_v2_const.Settings.user.status);
	  return status === im_v2_const.UserStatus.dnd;
	}
	function _desktopWillShowNotification2() {
	  const isDesktopChatWindow = im_v2_lib_desktop.DesktopManager.isChatWindow();
	  return !isDesktopChatWindow && im_v2_lib_desktop.DesktopManager.getInstance().isDesktopActive();
	}
	function _flashDesktopIcon2() {
	  if (!im_v2_lib_desktop.DesktopManager.isDesktop()) {
	    return;
	  }
	  im_v2_lib_desktopApi.DesktopApi.flashIcon();
	}
	function _playOpenedChatMessageSound2(params) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isImportantMessage)[_isImportantMessage](params)) {
	    im_v2_lib_soundNotification.SoundNotificationManager.getInstance().forcePlayOnce(im_v2_const.SoundType.newMessage2);
	    return;
	  }
	  im_v2_lib_soundNotification.SoundNotificationManager.getInstance().playOnce(im_v2_const.SoundType.newMessage2);
	}
	function _playMessageSound2(params) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isImportantMessage)[_isImportantMessage](params)) {
	    im_v2_lib_soundNotification.SoundNotificationManager.getInstance().forcePlayOnce(im_v2_const.SoundType.newMessage1);
	    return;
	  }
	  im_v2_lib_soundNotification.SoundNotificationManager.getInstance().playOnce(im_v2_const.SoundType.newMessage1);
	}
	function _restoreLastNotificationId2() {
	  const rawLastNotificationId = im_v2_lib_localStorage.LocalStorageManager.getInstance().get(im_v2_const.LocalStorageKey.lastNotificationId, 0);
	  this.lastNotificationId = Number.parseInt(rawLastNotificationId, 10);
	}
	function _updateLastNotificationId2(notificationId) {
	  const WRITE_TO_STORAGE_TIMEOUT = 2000;
	  this.lastNotificationId = notificationId;
	  clearTimeout(this.writeToStorageTimeout);
	  this.writeToStorageTimeout = setTimeout(() => {
	    im_v2_lib_localStorage.LocalStorageManager.getInstance().set(im_v2_const.LocalStorageKey.lastNotificationId, notificationId);
	  }, WRITE_TO_STORAGE_TIMEOUT);
	}
	function _setCurrentUserStatus2() {
	  var _applicationData$sett;
	  const applicationData = im_v2_application_core.Core.getApplicationData();
	  if (!((_applicationData$sett = applicationData.settings) != null && _applicationData$sett.status)) {
	    return;
	  }
	  im_v2_application_core.Core.getStore().dispatch('application/settings/set', {
	    [im_v2_const.Settings.user.status]: applicationData.settings.status
	  });
	}

	class LinesPullHandler {
	  constructor() {
	    this.store = im_v2_application_core.Core.getStore();
	  }
	  getModuleId() {
	    return 'im';
	  }
	  handleMessageChat(params) {
	    this.updateUnloadedLinesCounter(params);
	  }
	  handleReadMessageChat(params) {
	    this.updateUnloadedLinesCounter(params);
	  }
	  handleUnreadMessageChat(params) {
	    this.updateUnloadedLinesCounter(params);
	  }
	  handleChatHide(params) {
	    this.updateUnloadedLinesCounter({
	      dialogId: params.dialogId,
	      chatId: params.chatId,
	      lines: params.lines,
	      counter: 0
	    });
	  }
	  updateUnloadedLinesCounter(params) {
	    const {
	      dialogId,
	      chatId,
	      counter,
	      lines
	    } = params;
	    if (!lines || main_core.Type.isUndefined(counter)) {
	      return;
	    }
	    im_v2_lib_logger.Logger.warn('LinesPullHandler: updateUnloadedLinesCounter:', {
	      dialogId,
	      chatId,
	      counter
	    });
	    this.store.dispatch('counters/setUnloadedLinesCounters', {
	      [chatId]: counter
	    });
	  }
	}

	class OnlinePullHandler {
	  constructor() {
	    this.store = im_v2_application_core.Core.getStore();
	  }
	  getModuleId() {
	    return 'online';
	  }
	  getSubscriptionType() {
	    return 'online';
	  }
	  handleUserStatus(params) {
	    const currentUserId = im_v2_application_core.Core.getUserId();
	    if (main_core.Type.isPlainObject(params.users[currentUserId])) {
	      const {
	        status
	      } = params.users[currentUserId];
	      this.store.dispatch('application/settings/set', {
	        status
	      });
	    }
	    Object.values(params.users).forEach(userInfo => {
	      this.store.dispatch('users/update', {
	        id: userInfo.id,
	        fields: {
	          lastActivityDate: userInfo.last_activity_date
	        }
	      });
	    });
	  }
	}

	exports.BasePullHandler = BasePullHandler;
	exports.RecentPullHandler = RecentPullHandler;
	exports.NotificationPullHandler = NotificationPullHandler;
	exports.SidebarPullHandler = SidebarPullHandler;
	exports.NotifierPullHandler = NotifierPullHandler;
	exports.LinesPullHandler = LinesPullHandler;
	exports.OnlinePullHandler = OnlinePullHandler;

}((this.BX.Messenger.v2.Provider.Pull = this.BX.Messenger.v2.Provider.Pull || {}),BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Provider.Service,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Vue3.Vuex,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Model,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Application));
//# sourceMappingURL=registry.bundle.js.map
