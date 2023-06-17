this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Provider = this.BX.Messenger.v2.Provider || {};
(function (exports,main_core_events,im_v2_lib_uuid,im_v2_provider_service,im_public,ui_vue3_vuex,im_v2_lib_counter,im_v2_lib_logger,main_core,im_v2_lib_user,im_v2_application_core,im_v2_const,im_v2_lib_notifier,im_v2_lib_desktop,im_v2_lib_call,im_v2_lib_soundNotification) {
	'use strict';

	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _setMessageChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setMessageChat");
	var _setUsers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setUsers");
	var _setFiles = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setFiles");
	var _handleAddingMessageToModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleAddingMessageToModel");
	var _addMessageToModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addMessageToModel");
	var _updateDialog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateDialog");
	var _updateMessageViewedByOthers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateMessageViewedByOthers");
	var _updateChatLastMessageViews = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateChatLastMessageViews");
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
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	  }
	  handleMessageAdd(params) {
	    im_v2_lib_logger.Logger.warn('MessagePullHandler: handleMessageAdd', params);
	    babelHelpers.classPrivateFieldLooseBase(this, _setMessageChat)[_setMessageChat](params);
	    babelHelpers.classPrivateFieldLooseBase(this, _setUsers)[_setUsers](params);
	    babelHelpers.classPrivateFieldLooseBase(this, _setFiles)[_setFiles](params);
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
	        fields: params.message
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _sendScrollEvent)[_sendScrollEvent](params.chatId);
	    } else if (!messageWithRealId && messageWithTemplateId) {
	      im_v2_lib_logger.Logger.warn('New message pull handler: we already have the TEMPORARY message', params.message);
	      babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/updateWithId', {
	        id: params.message.templateId,
	        fields: params.message
	      });
	    }
	    // it's an opponent message or our own message from somewhere else
	    else if (!messageWithRealId && !messageWithTemplateId) {
	      im_v2_lib_logger.Logger.warn('New message pull handler: we dont have this message', params.message);
	      babelHelpers.classPrivateFieldLooseBase(this, _handleAddingMessageToModel)[_handleAddingMessageToModel](params);
	    }

	    //stop writing event
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('dialogues/stopWriting', {
	      dialogId: params.dialogId,
	      userId: params.message.senderId
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _updateDialog)[_updateDialog](params);
	  }
	  handleMessageUpdate(params) {
	    im_v2_lib_logger.Logger.warn('MessagePullHandler: handleMessageUpdate', params);
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('dialogues/stopWriting', {
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
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('dialogues/stopWriting', {
	      dialogId: params.dialogId,
	      userId: params.senderId
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/update', {
	      id: params.id,
	      fields: {
	        params: params.params
	      }
	    });
	  }
	  handleMessageDeleteComplete(params) {
	    im_v2_lib_logger.Logger.warn('MessagePullHandler: handleMessageDeleteComplete', params);
	    // this.store.dispatch('messages/delete', {
	    // 	id: params.id,
	    // 	chatId: params.chatId,
	    // });

	    // this.store.dispatch('dialogues/stopWriting', {
	    // 	dialogId: params.dialogId,
	    // 	userId: params.senderId
	    // });
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
	    // this.store.dispatch('messages/update', {
	    // 	id: params.id,
	    // 	chatId: params.chatId,
	    // 	fields: {params: params.params}
	    // }).then(() => {
	    // 	EventEmitter.emit(EventType.dialog.scrollToBottom, {chatId: params.chatId, cancelIfScrollChange: true});
	    // });
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
	      babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('dialogues/update', {
	        dialogId: params.dialogId,
	        fields: {
	          counter: params.counter,
	          lastId: params.lastId
	        }
	      });
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
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/store', params.link.message);
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/pin/add', {
	      chatId: params.link.chatId,
	      messageId: params.link.messageId
	    });
	    if (im_v2_application_core.Core.getUserId() !== params.link.authorId) ;
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
	  if (!(params != null && params.chat[params.chatId])) {
	    return;
	  }
	  const chatToAdd = {
	    ...params.chat[params.chatId],
	    dialogId: params.dialogId
	  };
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('dialogues/set', chatToAdd);
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
	    var _params$message;
	    const templateFileIdExists = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['files/isInCollection']({
	      fileId: (_params$message = params.message) == null ? void 0 : _params$message.templateFileId
	    });
	    if (templateFileIdExists) {
	      var _params$message2;
	      babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('files/updateWithId', {
	        id: (_params$message2 = params.message) == null ? void 0 : _params$message2.templateFileId,
	        fields: file
	      });
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('files/set', file);
	    }
	  });
	}
	function _handleAddingMessageToModel2(params) {
	  const dialog = babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog](params.dialogId, true);
	  if (dialog.inited && dialog.hasNextPage) {
	    return;
	  }
	  const chatIsOpened = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['application/isChatOpen'](params.dialogId);
	  const unreadMessages = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['messages/getChatUnreadMessages'](params.chatId);
	  if (!chatIsOpened && unreadMessages.length > im_v2_provider_service.MessageService.getMessageRequestLimit()) {
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
	  if (message.senderId !== im_v2_application_core.Core.getUserId()) {
	    newMessage.unread = true;
	    newMessage.viewed = false;
	  } else {
	    newMessage.unread = false;
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
	  } else {
	    dialogFieldsToUpdate.counter = params.counter;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('dialogues/update', {
	    dialogId: params.dialogId,
	    fields: dialogFieldsToUpdate
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('dialogues/clearLastMessageViews', {
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
	  const hasFirstViewer = !!dialog.lastMessageViews.firstViewer;
	  if (hasFirstViewer) {
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('dialogues/incrementLastMessageViews', {
	      dialogId: params.dialogId
	    });
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('dialogues/setLastMessageViews', {
	    dialogId: params.dialogId,
	    fields: {
	      userId: params.userId,
	      userName: params.userName,
	      date: params.date,
	      messageId: dialog.lastMessageId
	    }
	  });
	}
	function _sendScrollEvent2(chatId) {
	  main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.scrollToBottom, {
	    chatId,
	    threshold: im_v2_const.DialogScrollThreshold.nearTheBottom
	  });
	}
	function _getDialog2(dialogId, temporary = false) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['dialogues/get'](dialogId, temporary);
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
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('dialogues/update', {
	      dialogId: params.dialogId,
	      fields: {
	        ownerId: params.userId
	      }
	    });
	  }
	  handleChatManagers(params) {
	    im_v2_lib_logger.Logger.warn('ChatPullHandler: handleChatManagers', params);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('dialogues/update', {
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
	      babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('dialogues/update', {
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
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('dialogues/startWriting', {
	      dialogId,
	      userId,
	      userName
	    });
	  }
	  handleChatUnread(params) {
	    im_v2_lib_logger.Logger.warn('ChatPullHandler: handleChatUnread', params);
	    let markedId = 0;
	    if (params.active === true) {
	      markedId = params.markedId;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('dialogues/update', {
	      dialogId: params.dialogId,
	      fields: {
	        markedId
	      }
	    });
	  }
	  handleChatMuteNotify(params) {
	    if (params.muted) {
	      babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('dialogues/mute', {
	        dialogId: params.dialogId
	      });
	      return true;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('dialogues/unmute', {
	      dialogId: params.dialogId
	    });
	  }
	  handleChatRename(params) {
	    const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].getters['dialogues/getByChatId'](params.chatId);
	    if (!dialog) {
	      return false;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('dialogues/update', {
	      dialogId: dialog.dialogId,
	      fields: {
	        name: params.name
	      }
	    });
	  }
	  handleChatAvatar(params) {
	    const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].getters['dialogues/getByChatId'](params.chatId);
	    if (!dialog) {
	      return false;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('dialogues/update', {
	      dialogId: dialog.dialogId,
	      fields: {
	        avatar: params.avatar
	      }
	    });
	  }
	  handleReadAllChats() {
	    im_v2_lib_logger.Logger.warn('ChatPullHandler: handleReadAllChats');
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('dialogues/clearCounters');
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('recent/clearUnread');
	  }
	}
	function _updateChatUsers2(params) {
	  if (params.users) {
	    const userManager = new im_v2_lib_user.UserManager();
	    userManager.setUsersToModel(params.users);
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('dialogues/update', {
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
	    desktopManager.setDesktopVersion(params.version);
	    im_v2_lib_counter.CounterManager.getInstance().removeBrowserTitleCounter();
	  }
	  handleDesktopOffline() {
	    im_v2_lib_logger.Logger.warn('DesktopPullHandler: handleDesktopOffline');
	    im_v2_lib_desktop.DesktopManager.getInstance().setDesktopActive(false);
	  }
	}

	var _messageHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("messageHandler");
	var _chatHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatHandler");
	var _userHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userHandler");
	var _desktopHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("desktopHandler");
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
	    babelHelpers.classPrivateFieldLooseBase(this, _messageHandler)[_messageHandler] = new MessagePullHandler();
	    babelHelpers.classPrivateFieldLooseBase(this, _chatHandler)[_chatHandler] = new ChatPullHandler();
	    babelHelpers.classPrivateFieldLooseBase(this, _userHandler)[_userHandler] = new UserPullHandler();
	    babelHelpers.classPrivateFieldLooseBase(this, _desktopHandler)[_desktopHandler] = new DesktopPullHandler();
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
	}

	class RecentPullHandler {
	  constructor() {
	    this.store = im_v2_application_core.Core.getStore();
	    this.userManager = new im_v2_lib_user.UserManager();
	  }
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
	      return false;
	    }
	    const currentUserId = im_v2_application_core.Core.getUserId();
	    if (currentUserId && params.userInChat[params.chatId] && !params.userInChat[params.chatId].includes(currentUserId)) {
	      return false;
	    }
	    let attach = false;
	    if (main_core.Type.isArray(params.message.params['ATTACH'])) {
	      attach = params.message.params['ATTACH'];
	    }
	    let file = false;
	    if (main_core.Type.isArray(params.message.params['FILE_ID'])) {
	      file = params.files[params.message.params['FILE_ID'][0]];
	    }
	    im_v2_lib_logger.Logger.warn('RecentPullHandler: handleMessageAdd', params);
	    const newRecentItem = {
	      id: params.dialogId,
	      message: {
	        id: params.message.id,
	        text: params.message.text,
	        date: params.message.date,
	        senderId: params.message.senderId,
	        sending: false,
	        attach,
	        file
	      }
	    };
	    const recentItem = this.store.getters['recent/get'](params.dialogId);
	    if (recentItem) {
	      newRecentItem.options = {
	        birthdayPlaceholder: false
	      };
	      this.store.dispatch('recent/like', {
	        id: params.dialogId,
	        liked: false
	      });
	    }
	    const {
	      senderId
	    } = params.message;
	    const usersModel = this.store.state.users;
	    if (usersModel != null && usersModel.botList[senderId] && usersModel.botList[senderId].type === 'human') {
	      const {
	        text
	      } = params.message;
	      setTimeout(() => {
	        this.store.dispatch('recent/setRecent', newRecentItem);
	      }, this.getWaitTimeForHumanBot(text));
	      return;
	    }
	    this.store.dispatch('recent/setRecent', newRecentItem);
	  }
	  handleMessageUpdate(params, extra, command) {
	    const recentItem = this.store.getters['recent/get'](params.dialogId);
	    if (!recentItem || recentItem.message.id !== params.id) {
	      return false;
	    }
	    im_v2_lib_logger.Logger.warn('RecentPullHandler: handleMessageUpdate', params, command);
	    let text = params.text;
	    if (command === 'messageDelete') {
	      text = main_core.Loc.getMessage('IM_PULL_RECENT_MESSAGE_DELETED');
	    }
	    this.store.dispatch('recent/update', {
	      id: params.dialogId,
	      fields: {
	        message: {
	          id: params.id,
	          text: text,
	          date: recentItem.message.date,
	          status: recentItem.message.status,
	          senderId: params.senderId,
	          params: {
	            withFile: false,
	            withAttach: false
	          }
	        }
	      }
	    });
	  }
	  handleMessageDelete(params, extra, command) {
	    this.handleMessageUpdate(params, extra, command);
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
	    this.store.dispatch('recent/unread', {
	      id: params.dialogId,
	      action: params.active
	    });
	  }
	  /* endregion Counters handling */

	  handleReadMessageOpponent(params) {
	    im_v2_lib_logger.Logger.warn('RecentPullHandler: handleReadMessageOpponent', params);
	    const recentItem = this.store.getters['recent/get'](params.dialogId);
	    const lastReadMessage = Number.parseInt(params.lastId, 10);
	    if (!recentItem || recentItem.message.id !== lastReadMessage) {
	      return false;
	    }
	    this.store.dispatch('recent/update', {
	      id: params.dialogId,
	      fields: {
	        message: {
	          ...recentItem.message,
	          status: im_v2_const.MessageStatus.delivered
	        }
	      }
	    });
	  }
	  handleReadMessageChatOpponent(params) {
	    this.handleReadMessageOpponent(params);
	  }
	  handleUnreadMessageOpponent(params) {
	    im_v2_lib_logger.Logger.warn('RecentPullHandler: handleUnreadMessageOpponent', params);
	    const recentItem = this.store.getters['recent/get'](params.dialogId);
	    if (!recentItem) {
	      return false;
	    }
	    this.store.dispatch('recent/update', {
	      id: params.dialogId,
	      fields: {
	        message: {
	          ...recentItem.message,
	          status: im_v2_const.MessageStatus.received
	        }
	      }
	    });
	  }
	  handleUnreadMessageChatOpponent(params) {
	    im_v2_lib_logger.Logger.warn('RecentPullHandler: handleUnreadMessageChatOpponent', params);
	    const recentItem = this.store.getters['recent/get'](params.dialogId);
	    if (!recentItem) {
	      return false;
	    }
	    this.store.dispatch('recent/update', {
	      id: params.dialogId,
	      fields: {
	        message: {
	          ...recentItem.message,
	          status: params.chatMessageStatus
	        }
	      }
	    });
	  }
	  handleAddReaction(params) {
	    im_v2_lib_logger.Logger.warn('RecentPullHandler: handleAddReaction', params);
	    const recentItem = this.store.getters['recent/get'](params.dialogId);
	    if (!recentItem) {
	      return false;
	    }
	    const chatIsOpened = this.store.getters['application/isChatOpen'](params.dialogId);
	    if (chatIsOpened) {
	      return false;
	    }
	    const isOwnLike = im_v2_application_core.Core.getUserId() === params.senderId;
	    const isOwnLastMessage = im_v2_application_core.Core.getUserId() === recentItem.message.senderId;
	    if (isOwnLike || !isOwnLastMessage) {
	      return false;
	    }
	    this.store.dispatch('recent/like', {
	      id: params.dialogId,
	      messageId: params.id,
	      liked: true
	    });
	  }
	  handleDeleteReaction(params) {
	    // Logger.warn('RecentPullHandler: handleDeleteReaction', params);
	    // const recentItem = this.store.getters['recent/get'](params.dialogId);
	    // if (!recentItem)
	    // {
	    // 	return false;
	    // }
	  }
	  handleChatPin(params) {
	    im_v2_lib_logger.Logger.warn('RecentPullHandler: handleChatPin', params);
	    const recentItem = this.store.getters['recent/get'](params.dialogId);
	    if (!recentItem) {
	      return false;
	    }
	    this.store.dispatch('recent/pin', {
	      id: params.dialogId,
	      action: params.active
	    });
	  }
	  handleChatHide(params) {
	    im_v2_lib_logger.Logger.warn('RecentPullHandler: handleChatHide', params);
	    const recentItem = this.store.getters['recent/get'](params.dialogId);
	    if (!recentItem) {
	      return false;
	    }
	    this.store.dispatch('recent/delete', {
	      id: params.dialogId
	    });
	  }
	  handleChatUserLeave(params) {
	    im_v2_lib_logger.Logger.warn('RecentPullHandler: handleChatUserLeave', params);
	    const recentItem = this.store.getters['recent/get'](params.dialogId);
	    if (!recentItem) {
	      return false;
	    }
	    if (params.userId !== im_v2_application_core.Core.getUserId()) {
	      return false;
	    }
	    this.store.dispatch('recent/delete', {
	      id: params.dialogId
	    });
	  }
	  handleUserInvite(params) {
	    var _params$invited;
	    im_v2_lib_logger.Logger.warn('RecentPullHandler: handleUserInvite', params);
	    this.store.dispatch('recent/setRecent', {
	      id: params.user.id,
	      invited: (_params$invited = params.invited) != null ? _params$invited : false
	    });
	    this.userManager.setUsersToModel([params.user]);
	  }
	  getWaitTimeForHumanBot(text) {
	    const INITIAL_WAIT = 1000;
	    const WAIT_PER_WORD = 300;
	    const WAIT_LIMIT = 5000;
	    let waitTime = text.split(' ').length * WAIT_PER_WORD + INITIAL_WAIT;
	    if (waitTime > WAIT_LIMIT) {
	      waitTime = WAIT_LIMIT;
	    }
	    return waitTime;
	  }
	  updateUnloadedChatCounter(params) {
	    const {
	      dialogId,
	      chatId,
	      counter,
	      muted,
	      unread
	    } = params;
	    const recentItem = this.store.getters['recent/get'](dialogId);
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
	    let newCounter;
	    if (muted) {
	      newCounter = 0;
	    } else if (unread && counter === 0) {
	      newCounter = 1;
	    } else if (unread && counter > 0) {
	      newCounter = counter;
	    } else if (!unread) {
	      newCounter = counter;
	    }
	    this.store.dispatch('recent/setUnloadedChatCounters', {
	      [chatId]: newCounter
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
	      return false;
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
	  getSubscriptionType() {
	    return 'server';
	  }

	  //region members
	  handleChatUserAdd(params) {
	    if (!this.isSidebarInited(params.chatId)) {
	      return;
	    }
	    this.userManager.setUsersToModel(Object.values(params.users));
	    this.store.dispatch('sidebar/members/set', {
	      chatId: params.chatId,
	      users: params.newUsers
	    });
	  }
	  handleChatUserLeave(params) {
	    if (!this.isSidebarInited(params.chatId)) {
	      return;
	    }
	    this.store.dispatch('sidebar/members/delete', {
	      chatId: params.chatId,
	      userId: params.userId
	    });
	  }
	  //endregion

	  //region task
	  handleTaskAdd(params) {
	    if (!this.isSidebarInited(params.link.chatId)) {
	      return;
	    }
	    this.userManager.setUsersToModel(params.users);
	    this.store.dispatch('sidebar/tasks/set', {
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
	    this.store.dispatch('sidebar/tasks/delete', {
	      chatId: params.chatId,
	      id: params.linkId
	    });
	  }
	  //endregion

	  //region meetings
	  handleCalendarAdd(params) {
	    if (!this.isSidebarInited(params.link.chatId)) {
	      return;
	    }
	    this.userManager.setUsersToModel(params.users);
	    this.store.dispatch('sidebar/meetings/set', {
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
	    this.store.dispatch('sidebar/meetings/delete', {
	      chatId: params.chatId,
	      id: params.linkId
	    });
	  }
	  //endregion

	  //region links
	  handleUrlAdd(params) {
	    if (!this.isSidebarInited(params.link.chatId)) {
	      return;
	    }
	    this.userManager.setUsersToModel(params.users);
	    this.store.dispatch('sidebar/links/set', {
	      chatId: params.link.chatId,
	      links: [params.link]
	    });
	    const counter = this.store.getters['sidebar/links/getCounter'](params.link.chatId);
	    this.store.dispatch('sidebar/links/setCounter', {
	      chatId: params.link.chatId,
	      counter: counter + 1
	    });
	  }
	  handleUrlDelete(params) {
	    if (!this.isSidebarInited(params.chatId)) {
	      return;
	    }
	    this.store.dispatch('sidebar/links/delete', {
	      chatId: params.chatId,
	      id: params.linkId
	    });
	  }
	  //endregion

	  //region favorite
	  handleMessageFavoriteAdd(params) {
	    if (!this.isSidebarInited(params.link.chatId)) {
	      return;
	    }
	    this.userManager.setUsersToModel(params.users);
	    this.store.dispatch('files/set', params.files);
	    this.store.dispatch('messages/store', [params.link.message]);
	    this.store.dispatch('sidebar/favorites/set', {
	      chatId: params.link.chatId,
	      favorites: [params.link]
	    });
	    const counter = this.store.getters['sidebar/favorites/getCounter'](params.link.chatId);
	    this.store.dispatch('sidebar/favorites/setCounter', {
	      chatId: params.link.chatId,
	      counter: counter + 1
	    });
	  }
	  handleMessageFavoriteDelete(params) {
	    if (!this.isSidebarInited(params.chatId)) {
	      return;
	    }
	    this.store.dispatch('sidebar/favorites/delete', {
	      chatId: params.chatId,
	      id: params.linkId
	    });
	  }
	  //endregion

	  //region files
	  handleFileAdd(params) {
	    if (!this.isSidebarInited(params.link.chatId)) {
	      return;
	    }
	    this.userManager.setUsersToModel(params.users);
	    this.store.dispatch('files/set', params.files);
	    if (!params.link.subType) {
	      params.link.subType = im_v2_const.SidebarDetailBlock.fileUnsorted;
	    }
	    this.store.dispatch('sidebar/files/set', {
	      chatId: params.link.chatId,
	      files: [params.link]
	    });
	  }
	  handleFileDelete(params) {
	    const chatId = main_core.Type.isNumber(params.chatId) ? params.chatId : Number.parseInt(params.chatId, 10);
	    if (!this.isSidebarInited(chatId)) {
	      return;
	    }
	    const sidebarFileId = params.linkId ? params.linkId : params.fileId;
	    this.store.dispatch('sidebar/files/delete', {
	      chatId: chatId,
	      id: sidebarFileId
	    });
	  }
	  //endregion

	  //region files unsorted
	  handleMessage(params) {
	    // handle new files while migration is not finished.
	    if (!this.isSidebarInited(params.chatId) || this.isFilesMigrated()) {
	      return;
	    }
	    this.userManager.setUsersToModel(Object.values(params.users));
	    this.store.dispatch('files/set', Object.values(params.files));
	    Object.values(params.files).forEach(file => {
	      file.subType = im_v2_const.SidebarDetailBlock.fileUnsorted;
	      this.store.dispatch('sidebar/files/set', {
	        chatId: file.chatId,
	        files: [file]
	      });
	    });
	  }
	  //endregion

	  isSidebarInited(chatId) {
	    return this.store.getters['sidebar/isInited'](chatId);
	  }
	  isFilesMigrated() {
	    return this.store.state.sidebar.isFilesMigrated;
	  }
	}

	var _shouldShowNotification = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("shouldShowNotification");
	var _isChatOpened = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isChatOpened");
	var _isUserDnd = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isUserDnd");
	var _isDesktopActive = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isDesktopActive");
	class NotifierPullHandler {
	  constructor() {
	    Object.defineProperty(this, _isDesktopActive, {
	      value: _isDesktopActive2
	    });
	    Object.defineProperty(this, _isUserDnd, {
	      value: _isUserDnd2
	    });
	    Object.defineProperty(this, _isChatOpened, {
	      value: _isChatOpened2
	    });
	    Object.defineProperty(this, _shouldShowNotification, {
	      value: _shouldShowNotification2
	    });
	    this.store = im_v2_application_core.Core.getStore();
	  }
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
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _shouldShowNotification)[_shouldShowNotification](params)) {
	      return;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isChatOpened)[_isChatOpened](params)) {
	      im_v2_lib_soundNotification.SoundNotificationManager.getInstance().playOnce(im_v2_const.SoundType.newMessage2);
	      return;
	    }
	    im_v2_lib_soundNotification.SoundNotificationManager.getInstance().playOnce(im_v2_const.SoundType.newMessage1);
	    const message = this.store.getters['messages/getById'](params.message.id);
	    const dialog = this.store.getters['dialogues/get'](params.dialogId, true);
	    const user = this.store.getters['users/get'](message.authorId);
	    im_v2_lib_notifier.NotifierManager.getInstance().showMessage(message, dialog, user);
	  }
	  handleNotifyAdd(params) {
	    if (params.onlyFlash === true || babelHelpers.classPrivateFieldLooseBase(this, _isUserDnd)[_isUserDnd]() || babelHelpers.classPrivateFieldLooseBase(this, _isDesktopActive)[_isDesktopActive]()) {
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
	    im_v2_lib_notifier.NotifierManager.getInstance().showNotification(notification, user);
	  }
	}
	function _shouldShowNotification2(params) {
	  var _params$message, _params$message$param;
	  if (im_v2_application_core.Core.getUserId() === params.message.senderId) {
	    return false;
	  }
	  if (!params.notify || ((_params$message = params.message) == null ? void 0 : (_params$message$param = _params$message.params) == null ? void 0 : _params$message$param.NOTIFY) === 'N' || babelHelpers.classPrivateFieldLooseBase(this, _isUserDnd)[_isUserDnd]() || babelHelpers.classPrivateFieldLooseBase(this, _isDesktopActive)[_isDesktopActive]()) {
	    return false;
	  }
	  const dialog = this.store.getters['dialogues/get'](params.dialogId, true);
	  if (dialog.type === im_v2_const.DialogType.lines) {
	    return;
	  }
	  const isMuted = dialog.muteList.includes(im_v2_application_core.Core.getUserId());
	  if (isMuted) {
	    return false;
	  }
	  const screenSharingIsActive = im_v2_lib_call.CallManager.getInstance().hasCurrentScreenSharing();
	  if (screenSharingIsActive) {
	    return false;
	  }
	  return true;
	}
	function _isChatOpened2(params) {
	  const isChatOpen = this.store.getters['application/isChatOpen'](params.dialogId);
	  return !!(document.hasFocus() && isChatOpen);
	}
	function _isUserDnd2() {
	  const currentUser = this.store.getters['users/get'](im_v2_application_core.Core.getUserId(), true);
	  return currentUser.status === im_v2_const.UserStatus.dnd;
	}
	function _isDesktopActive2() {
	  return im_v2_lib_desktop.DesktopManager.getInstance().isDesktopActive();
	}

	exports.BasePullHandler = BasePullHandler;
	exports.RecentPullHandler = RecentPullHandler;
	exports.NotificationPullHandler = NotificationPullHandler;
	exports.SidebarPullHandler = SidebarPullHandler;
	exports.NotifierPullHandler = NotifierPullHandler;

}((this.BX.Messenger.v2.Provider.Pull = this.BX.Messenger.v2.Provider.Pull || {}),BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Provider.Service,BX.Messenger.v2.Lib,BX.Vue3.Vuex,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=registry.bundle.js.map
