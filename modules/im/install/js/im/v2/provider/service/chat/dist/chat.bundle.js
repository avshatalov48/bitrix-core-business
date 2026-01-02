/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,imopenlines_v2_lib_openlines,call_lib_callTokenManager,im_public,im_v2_provider_service_message,im_v2_lib_copilot,im_v2_lib_user,im_v2_lib_analytics,ui_uploader_core,im_v2_lib_roleManager,im_v2_lib_uuid,ui_vue3_vuex,rest_client,im_v2_lib_utils,im_v2_lib_notifier,im_v2_lib_layout,main_core,im_v2_application_core,im_v2_lib_logger,im_v2_const,im_v2_lib_rest,im_v2_lib_feature) {
	'use strict';

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
	    }).catch(([error]) => {
	      console.error('ChatService: deleteChat error:', error);
	      im_v2_lib_notifier.Notifier.chat.onDeleteError();
	    });
	    await babelHelpers.classPrivateFieldLooseBase(this, _updateModels)[_updateModels](dialogId);
	    return deleteResult;
	  }
	  async deleteCollab(dialogId) {
	    im_v2_lib_logger.Logger.warn(`ChatService: deleteCollab, dialogId: ${dialogId}`);
	    try {
	      await im_v2_lib_rest.runAction(im_v2_const.RestMethod.socialnetworkCollabDelete, {
	        data: {
	          dialogId
	        }
	      });
	      await babelHelpers.classPrivateFieldLooseBase(this, _updateModels)[_updateModels](dialogId);
	      return Promise.resolve();
	    } catch (errors) {
	      const [firstError] = errors;
	      console.error('ChatService: deleteCollab error:', firstError);
	      im_v2_lib_notifier.Notifier.collab.handleDeleteError(firstError);
	      return Promise.resolve();
	    }
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
	    return babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].chat.type === im_v2_const.ChatType.lines;
	  }
	  isCopilotChat() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].chat.type === im_v2_const.ChatType.copilot;
	  }
	  isCollabChat() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].chat.type === im_v2_const.ChatType.collab;
	  }
	  getChats() {
	    const mainChat = {
	      ...babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].chat,
	      hasPrevPage: babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].hasPrevPage,
	      hasNextPage: babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].hasNextPage,
	      tariffRestrictions: babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].tariffRestrictions
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
	  getCommentInfo() {
	    var _babelHelpers$classPr5;
	    return (_babelHelpers$classPr5 = babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].commentInfo) != null ? _babelHelpers$classPr5 : [];
	  }
	  getCollabInfo() {
	    var _babelHelpers$classPr6;
	    return (_babelHelpers$classPr6 = babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].collabInfo) != null ? _babelHelpers$classPr6 : null;
	  }
	  getMessagesToStore() {
	    var _babelHelpers$classPr7;
	    return (_babelHelpers$classPr7 = babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].additionalMessages) != null ? _babelHelpers$classPr7 : [];
	  }
	  getPinnedMessageIds() {
	    var _babelHelpers$classPr8;
	    const pinnedMessageIds = [];
	    const pins = (_babelHelpers$classPr8 = babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].pins) != null ? _babelHelpers$classPr8 : [];
	    pins.forEach(pin => {
	      pinnedMessageIds.push(pin.messageId);
	    });
	    return pinnedMessageIds;
	  }
	  getReactions() {
	    var _babelHelpers$classPr9;
	    return (_babelHelpers$classPr9 = babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].reactions) != null ? _babelHelpers$classPr9 : [];
	  }
	  getCopilot() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].copilot;
	  }
	  getSession() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].session;
	  }
	  getAutoDeleteConfig() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _restResult)[_restResult].messagesAutoDeleteConfigs;
	  }
	}

	const {
	  callInstalled
	} = main_core.Extension.getSettings('im.v2.lib.call');
	var _store$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _requestChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("requestChat");
	var _markDialogAsLoading = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("markDialogAsLoading");
	var _markDialogAsLoaded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("markDialogAsLoaded");
	var _markDialogAsNotLoaded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("markDialogAsNotLoaded");
	var _isDialogLoadedMarkNeeded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isDialogLoadedMarkNeeded");
	var _updateModels$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateModels");
	var _needLayoutRedirect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("needLayoutRedirect");
	var _redirectToLayout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("redirectToLayout");
	var _needRedirectToOpenLinesLayout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("needRedirectToOpenLinesLayout");
	var _checkFeatureDisabled = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("checkFeatureDisabled");
	var _checkCollabFeatureDisabled = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("checkCollabFeatureDisabled");
	var _openFeatureSlider = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("openFeatureSlider");
	class LoadService {
	  constructor() {
	    Object.defineProperty(this, _openFeatureSlider, {
	      value: _openFeatureSlider2
	    });
	    Object.defineProperty(this, _checkCollabFeatureDisabled, {
	      value: _checkCollabFeatureDisabled2
	    });
	    Object.defineProperty(this, _checkFeatureDisabled, {
	      value: _checkFeatureDisabled2
	    });
	    Object.defineProperty(this, _needRedirectToOpenLinesLayout, {
	      value: _needRedirectToOpenLinesLayout2
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
	  loadChatByChatId(chatId) {
	    const params = {
	      chatId,
	      messageLimit: im_v2_provider_service_message.MessageService.getMessageRequestLimit()
	    };
	    return babelHelpers.classPrivateFieldLooseBase(this, _requestChat)[_requestChat](im_v2_const.RestMethod.imV2ChatLoad, params);
	  }
	  loadChatWithMessages(dialogId) {
	    const params = {
	      dialogId,
	      messageLimit: im_v2_provider_service_message.MessageService.getMessageRequestLimit()
	    };
	    return babelHelpers.classPrivateFieldLooseBase(this, _requestChat)[_requestChat](im_v2_const.RestMethod.imV2ChatLoad, params);
	  }
	  loadChatWithContext(dialogId, messageId) {
	    const params = {
	      dialogId,
	      messageId,
	      messageLimit: im_v2_provider_service_message.MessageService.getMessageRequestLimit()
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
	      console.error('ChatService: Load: error preparing external id', error);
	    });
	  }
	  async loadComments(postId) {
	    const params = {
	      postId,
	      messageLimit: im_v2_provider_service_message.MessageService.getMessageRequestLimit(),
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
	      console.error('ChatService: Load: error loading comment info', error);
	    });
	    const userManager = new im_v2_lib_user.UserManager();
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('messages/comments/set', commentInfo);
	    void userManager.addUsersToModel(usersShort);
	  }
	  clearChat(dialogId) {
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
	  }).catch(([error]) => {
	    console.error('ChatService: Load: error loading chat', error);
	    im_v2_lib_notifier.Notifier.chat.handleLoadError(error);
	    babelHelpers.classPrivateFieldLooseBase(this, _markDialogAsNotLoaded)[_markDialogAsNotLoaded](dialogId);
	    throw error;
	  });
	  if (babelHelpers.classPrivateFieldLooseBase(this, _checkFeatureDisabled)[_checkFeatureDisabled](actionResult)) {
	    await babelHelpers.classPrivateFieldLooseBase(this, _markDialogAsNotLoaded)[_markDialogAsNotLoaded](dialogId);
	    await im_public.Messenger.openChat();
	    return babelHelpers.classPrivateFieldLooseBase(this, _openFeatureSlider)[_openFeatureSlider](actionResult);
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _needLayoutRedirect)[_needLayoutRedirect](actionResult)) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _redirectToLayout)[_redirectToLayout](actionResult, messageId);
	  }
	  const {
	    dialogId: loadedDialogId,
	    chatId
	  } = await babelHelpers.classPrivateFieldLooseBase(this, _updateModels$1)[_updateModels$1](actionResult);
	  const {
	    callInfo
	  } = actionResult;
	  if (callInstalled) {
	    call_lib_callTokenManager.CallTokenManager.setToken(callInfo.chatId, callInfo.token);
	  }
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
	  const autoDeletePromise = babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('chats/autoDelete/set', extractor.getAutoDeleteConfig());
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
	  let openLinesPromise = Promise.resolve();
	  if (imopenlines_v2_lib_openlines.OpenLinesManager) {
	    openLinesPromise = imopenlines_v2_lib_openlines.OpenLinesManager.handleChatLoadResponse(extractor.getSession());
	  }
	  const collabPromise = babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('chats/collabs/set', {
	    chatId: extractor.getChatId(),
	    collabInfo: extractor.getCollabInfo()
	  });
	  await Promise.all([chatsPromise, filesPromise, usersPromise, messagesPromise, copilotPromise, openLinesPromise, collabPromise, autoDeletePromise]);
	  return {
	    dialogId: extractor.getDialogId(),
	    chatId: extractor.getChatId()
	  };
	}
	function _needLayoutRedirect2(actionResult) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _needRedirectToOpenLinesLayout)[_needRedirectToOpenLinesLayout](actionResult);
	}
	function _redirectToLayout2(actionResult) {
	  const extractor = new ChatDataExtractor(actionResult);
	  im_v2_lib_layout.LayoutManager.getInstance().setLastOpenedElement(im_v2_const.Layout.chat, '');
	  if (babelHelpers.classPrivateFieldLooseBase(this, _needRedirectToOpenLinesLayout)[_needRedirectToOpenLinesLayout](actionResult)) {
	    return im_public.Messenger.openLines(extractor.getDialogId());
	  }
	  return Promise.resolve();
	}
	function _needRedirectToOpenLinesLayout2(actionResult) {
	  const optionOpenLinesV2Activated = im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.openLinesV2);
	  if (optionOpenLinesV2Activated) {
	    return false;
	  }
	  const extractor = new ChatDataExtractor(actionResult);
	  return extractor.isOpenlinesChat() && main_core.Type.isStringFilled(extractor.getDialogId());
	}
	function _checkFeatureDisabled2(actionResult) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _checkCollabFeatureDisabled)[_checkCollabFeatureDisabled](actionResult);
	}
	function _checkCollabFeatureDisabled2(actionResult) {
	  const extractor = new ChatDataExtractor(actionResult);
	  return extractor.isCollabChat() && !im_v2_lib_feature.FeatureManager.collab.isAvailable();
	}
	function _openFeatureSlider2(actionResult) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _checkCollabFeatureDisabled)[_checkCollabFeatureDisabled](actionResult)) {
	    im_v2_lib_feature.FeatureManager.collab.openFeatureSlider();
	  }
	}

	const PRIVATE_CHAT = 'CHAT';
	const OPEN_CHAT = 'OPEN';
	var _restClient = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _store$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _prepareFields = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareFields");
	var _addCollabToModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addCollabToModel");
	var _addChatToModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addChatToModel");
	var _sendAnalytics = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendAnalytics");
	var _prepareType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareType");
	class CreateService {
	  constructor() {
	    Object.defineProperty(this, _prepareType, {
	      value: _prepareType2
	    });
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
	      console.error('ChatService: createChat error:', error);
	      im_v2_lib_notifier.Notifier.chat.onCreateError();
	      throw error;
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
	      options: {
	        ...collabConfig.options,
	        messagesAutoDeleteDelay: preparedFields.messagesAutoDeleteDelay
	      }
	    };
	    const createResult = await im_v2_lib_rest.runAction(im_v2_const.RestMethod.socialnetworkCollabCreate, {
	      data: params
	    }).catch(([error]) => {
	      console.error('ChatService: createCollab error:', error);
	      im_v2_lib_notifier.Notifier.collab.handleCreateError(error);
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
	  if (preparedConfig.type) {
	    preparedConfig.type = babelHelpers.classPrivateFieldLooseBase(this, _prepareType)[_prepareType](preparedConfig.type);
	  }
	  if (preparedConfig.entityType) {
	    preparedConfig.entityType = babelHelpers.classPrivateFieldLooseBase(this, _prepareType)[_prepareType](preparedConfig.entityType);
	  }
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
	    copilotMainRole: preparedConfig.copilotMainRole,
	    messagesAutoDeleteDelay: preparedConfig.autoDeleteDelay
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
	function _prepareType2(type) {
	  return im_v2_lib_utils.Utils.text.convertCamelToSnakeCase(type).toUpperCase();
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
	    }).catch(([error]) => {
	      console.error('ChatService: changeAvatar error:', error);
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
	    }).catch(([error]) => {
	      console.error('ChatService: updateChat error:', error);
	      im_v2_lib_notifier.Notifier.chat.onUpdateError();
	      throw error;
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
	      console.error('ChatService: updateCollab error:', error);
	      im_v2_lib_notifier.Notifier.collab.handleUpdateError(error);
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
	    }).catch(([error]) => {
	      console.error('ChatService: getMemberEntities error:', error);
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
	    }).catch(result => {
	      babelHelpers.classPrivateFieldLooseBase(this, _updateChatTitleInModel)[_updateChatTitleInModel](dialogId, oldName);
	      console.error('ChatService: renameChat error', result.error());
	      im_v2_lib_notifier.Notifier.chat.onRenameError();
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
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('chats/mute', {
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
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch('chats/unmute', {
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
	  return babelHelpers.classPrivateFieldLooseBase(this, _restClient$2)[_restClient$2].callMethod(im_v2_const.RestMethod.imChatMute, queryParams).catch(result => {
	    const actionText = action === 'Y' ? 'muting' : 'unmuting';
	    console.error(`Im.RecentList: error ${actionText} chat`, result.error());
	    const actionType = action === 'Y' ? 'chats/unmute' : 'chats/mute';
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$5)[_store$5].dispatch(actionType, {
	      dialogId
	    });
	  });
	}

	var _store$6 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	class PinService {
	  constructor() {
	    Object.defineProperty(this, _store$6, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$6)[_store$6] = im_v2_application_core.Core.getStore();
	  }
	  pinChat(dialogId) {
	    im_v2_lib_logger.Logger.warn('PinService: pinChat', dialogId);
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$6)[_store$6].dispatch('recent/pin', {
	      id: dialogId,
	      action: true
	    });
	    im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2RecentPin, {
	      data: {
	        dialogId
	      }
	    }).catch(([error]) => {
	      console.error('PinService: error pinning chat', error);
	      im_v2_lib_notifier.Notifier.recent.handlePinError(error);
	      void babelHelpers.classPrivateFieldLooseBase(this, _store$6)[_store$6].dispatch('recent/pin', {
	        id: dialogId,
	        action: false
	      });
	    });
	  }
	  unpinChat(dialogId) {
	    im_v2_lib_logger.Logger.warn('PinService: unpinChat', dialogId);
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$6)[_store$6].dispatch('recent/pin', {
	      id: dialogId,
	      action: false
	    });
	    im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2RecentUnpin, {
	      data: {
	        dialogId
	      }
	    }).catch(([error]) => {
	      console.error('PinService: error unpinning chat', error);
	      im_v2_lib_notifier.Notifier.recent.onUnpinError();
	      void babelHelpers.classPrivateFieldLooseBase(this, _store$6)[_store$6].dispatch('recent/pin', {
	        id: dialogId,
	        action: true
	      });
	    });
	  }
	}

	const READ_TIMEOUT = 300;
	var _store$7 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
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
	    Object.defineProperty(this, _restClient$3, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _messagesToRead, {
	      writable: true,
	      value: {}
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$3)[_restClient$3] = im_v2_application_core.Core.getRestClient();
	  }
	  readAll() {
	    im_v2_lib_logger.Logger.warn('ReadService: readAll');
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('chats/clearCounters');
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('recent/clearUnread');
	    return babelHelpers.classPrivateFieldLooseBase(this, _restClient$3)[_restClient$3].callMethod(im_v2_const.RestMethod.imV2ChatReadAll).catch(result => {
	      console.error('ReadService: readAll error', result.error());
	    });
	  }
	  readDialog(dialogId) {
	    im_v2_lib_logger.Logger.warn('ReadService: readDialog', dialogId);
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('recent/unread', {
	      id: dialogId,
	      action: false
	    });
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('chats/update', {
	      dialogId,
	      fields: {
	        counter: 0
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$3)[_restClient$3].callMethod(im_v2_const.RestMethod.imV2ChatRead, {
	      dialogId
	    }).catch(result => {
	      console.error('ReadService: error reading chat', result.error());
	    });
	  }
	  unreadDialog(dialogId) {
	    im_v2_lib_logger.Logger.warn('ReadService: unreadDialog', dialogId);
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('recent/unread', {
	      id: dialogId,
	      action: true
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$3)[_restClient$3].callMethod(im_v2_const.RestMethod.imV2ChatUnread, {
	      dialogId
	    }).catch(result => {
	      console.error('ReadService: error setting chat as unread', result.error());
	      void babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('recent/unread', {
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
	        void babelHelpers.classPrivateFieldLooseBase(this, _readMessagesForChat)[_readMessagesForChat](rawChatId, messageIds);
	      });
	    }, READ_TIMEOUT);
	  }
	  async readChatQueuedMessages(chatId) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _messagesToRead)[_messagesToRead][chatId]) {
	      return;
	    }
	    clearTimeout(this.readTimeout);
	    void babelHelpers.classPrivateFieldLooseBase(this, _readMessagesForChat)[_readMessagesForChat](chatId, babelHelpers.classPrivateFieldLooseBase(this, _messagesToRead)[_messagesToRead][chatId]);
	  }
	  clearDialogMark(dialogId) {
	    im_v2_lib_logger.Logger.warn('ReadService: clear dialog mark', dialogId);
	    const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].getters['chats/get'](dialogId);
	    const recentItem = babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].getters['recent/get'](dialogId);
	    if (dialog.markedId === 0 && !(recentItem != null && recentItem.unread)) {
	      return;
	    }
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('recent/unread', {
	      id: dialogId,
	      action: false
	    });
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('chats/update', {
	      dialogId,
	      fields: {
	        markedId: 0
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$3)[_restClient$3].callMethod(im_v2_const.RestMethod.imV2ChatRead, {
	      dialogId,
	      onlyRecent: 'Y'
	    }).catch(result => {
	      console.error('ReadService: error clearing dialog mark', result.error());
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
	  const readResult = await babelHelpers.classPrivateFieldLooseBase(this, _readMessageOnServer)[_readMessageOnServer](queueChatId, copiedMessageIds).catch(([error]) => {
	    console.error('ReadService: error reading message', error);
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _checkChatCounter)[_checkChatCounter](readResult);
	  return true;
	}
	function _readMessageOnClient2(chatId, messageIds) {
	  const maxMessageId = Math.max(...messageIds);
	  const dialog = babelHelpers.classPrivateFieldLooseBase(this, _getDialogByChatId)[_getDialogByChatId](chatId);
	  if (maxMessageId > dialog.lastReadId) {
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('chats/update', {
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
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$7)[_store$7].dispatch('chats/update', {
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

	var _store$8 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _onChatLeave = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onChatLeave");
	class UserService {
	  constructor() {
	    Object.defineProperty(this, _onChatLeave, {
	      value: _onChatLeave2
	    });
	    Object.defineProperty(this, _store$8, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$4, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$8)[_store$8] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$4)[_restClient$4] = im_v2_application_core.Core.getRestClient();
	  }
	  async leaveChat(dialogId) {
	    const queryParams = {
	      dialogId,
	      userId: im_v2_application_core.Core.getUserId()
	    };
	    try {
	      await babelHelpers.classPrivateFieldLooseBase(this, _restClient$4)[_restClient$4].callMethod(im_v2_const.RestMethod.imV2ChatDeleteUser, queryParams);
	      babelHelpers.classPrivateFieldLooseBase(this, _onChatLeave)[_onChatLeave](dialogId);
	    } catch (result) {
	      console.error('UserService: leave chat error', result.error());
	      im_v2_lib_notifier.Notifier.chat.handleLeaveError(result.error());
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
	      console.error('UserService: leave collab error', errors[0]);
	      im_v2_lib_notifier.Notifier.collab.onLeaveError();
	    }
	  }
	  async kickUserFromChat(dialogId, userId) {
	    const queryParams = {
	      dialogId,
	      userId
	    };
	    await babelHelpers.classPrivateFieldLooseBase(this, _restClient$4)[_restClient$4].callMethod(im_v2_const.RestMethod.imV2ChatDeleteUser, queryParams).catch(result => {
	      console.error('UserService: error kicking from chat', result.error());
	      im_v2_lib_notifier.Notifier.chat.handleUserKickError(result.error());
	    });
	  }
	  async kickUserFromCollab(dialogId, userId) {
	    const members = im_v2_lib_utils.Utils.user.prepareSelectorIds(userId);
	    const payload = {
	      data: {
	        dialogId,
	        members
	      }
	    };
	    await im_v2_lib_rest.runAction(im_v2_const.RestMethod.socialnetworkMemberDelete, payload).catch(([error]) => {
	      console.error('UserService: error kicking from collab', error);
	      im_v2_lib_notifier.Notifier.collab.onKickUserError();
	    });
	  }
	  addToChat(addConfig) {
	    const queryParams = {
	      chat_id: addConfig.chatId,
	      users: addConfig.members,
	      hide_history: !addConfig.showHistory
	    };
	    return babelHelpers.classPrivateFieldLooseBase(this, _restClient$4)[_restClient$4].callMethod(im_v2_const.RestMethod.imChatUserAdd, queryParams).catch(result => {
	      console.error('UserService: error adding to chat', result.error());
	      throw result.error();
	    });
	  }
	  joinChat(dialogId) {
	    im_v2_lib_logger.Logger.warn(`UserService: join chat ${dialogId}`);
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$8)[_store$8].dispatch('chats/update', {
	      dialogId,
	      fields: {
	        role: im_v2_const.UserRole.member
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$4)[_restClient$4].callMethod(im_v2_const.RestMethod.imV2ChatJoin, {
	      dialogId
	    }).catch(result => {
	      console.error('UserService: error joining chat', result.error());
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
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$8)[_store$8].dispatch('chats/update', {
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
	    im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatAddManagers, payload).catch(([error]) => {
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
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$8)[_store$8].dispatch('chats/update', {
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
	    im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatDeleteManagers, payload).catch(([error]) => {
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

	var _store$9 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _sendRequestDebounced = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendRequestDebounced");
	var _sendRequest = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendRequest");
	var _handleResponse = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleResponse");
	var _getChatId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getChatId");
	class MessagesAutoDeleteService {
	  constructor() {
	    Object.defineProperty(this, _getChatId, {
	      value: _getChatId2
	    });
	    Object.defineProperty(this, _handleResponse, {
	      value: _handleResponse2
	    });
	    Object.defineProperty(this, _sendRequest, {
	      value: _sendRequest2
	    });
	    Object.defineProperty(this, _store$9, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _sendRequestDebounced, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9] = im_v2_application_core.Core.getStore();
	    const DEBOUNCE_TIME = 500;
	    babelHelpers.classPrivateFieldLooseBase(this, _sendRequestDebounced)[_sendRequestDebounced] = main_core.Runtime.debounce(babelHelpers.classPrivateFieldLooseBase(this, _sendRequest)[_sendRequest], DEBOUNCE_TIME);
	  }
	  setDelay(dialogId, delay) {
	    im_v2_lib_logger.Logger.warn('MessagesAutoDeleteService: setDelay', dialogId, delay);
	    const chatId = babelHelpers.classPrivateFieldLooseBase(this, _getChatId)[_getChatId](dialogId);
	    const previousDelay = babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].getters['chats/autoDelete/getDelay'](chatId);
	    if (previousDelay === delay) {
	      return;
	    }
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].dispatch('chats/autoDelete/set', {
	      chatId,
	      delay
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _sendRequestDebounced)[_sendRequestDebounced]({
	      dialogId,
	      delay,
	      previousDelay
	    });
	  }
	}
	async function _sendRequest2(queryParams) {
	  const {
	    dialogId,
	    delay,
	    previousDelay
	  } = queryParams;
	  try {
	    const response = await im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatSetMessagesAutoDeleteDelay, {
	      data: {
	        dialogId,
	        hours: delay
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _handleResponse)[_handleResponse](delay, response);
	  } catch (error) {
	    console.error('MessagesAutoDeleteService: Error setting auto delete delay', error);
	    void babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].dispatch('chats/autoDelete/set', {
	      chatId: babelHelpers.classPrivateFieldLooseBase(this, _getChatId)[_getChatId](dialogId),
	      delay: previousDelay
	    });
	  }
	}
	function _handleResponse2(delay, response) {
	  const [config] = response.messagesAutoDeleteConfigs;
	  // if we set some delay and server returns 0 delay, then auto delete is disabled by admin
	  if (delay !== config.delay && config.delay === im_v2_const.AutoDeleteDelay.Off) {
	    im_v2_lib_feature.FeatureManager.messagesAutoDelete.openFeatureSlider();
	  }
	  void babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].dispatch('chats/autoDelete/set', {
	    chatId: config.chatId,
	    delay: config.delay
	  });
	}
	function _getChatId2(dialogId) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$9)[_store$9].getters['chats/get'](dialogId).chatId;
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
	var _messagesAutoDeleteService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("messagesAutoDeleteService");
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
	    Object.defineProperty(this, _messagesAutoDeleteService, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _initServices)[_initServices]();
	  }

	  // region 'load'
	  loadChat(dialogId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService].loadChat(dialogId);
	  }
	  loadChatByChatId(chatId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService].loadChatByChatId(chatId);
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
	  clearChat(dialogId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loadService)[_loadService].clearChat(dialogId);
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
	    babelHelpers.classPrivateFieldLooseBase(this, _readService)[_readService].readAll();
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
	    babelHelpers.classPrivateFieldLooseBase(this, _readService)[_readService].readChatQueuedMessages(chatId);
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
	    babelHelpers.classPrivateFieldLooseBase(this, _userService)[_userService].leaveCollab(dialogId);
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

	  // region 'messages auto delete'
	  setMessagesAutoDeleteDelay(dialogId, delay) {
	    babelHelpers.classPrivateFieldLooseBase(this, _messagesAutoDeleteService)[_messagesAutoDeleteService].setDelay(dialogId, delay);
	  }
	  // endregion 'messages auto delete'
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
	  babelHelpers.classPrivateFieldLooseBase(this, _messagesAutoDeleteService)[_messagesAutoDeleteService] = new MessagesAutoDeleteService();
	}

	exports.ChatService = ChatService;

}((this.BX.Messenger.v2.Service = this.BX.Messenger.v2.Service || {}),BX?.OpenLines?.v2?.Lib??{},BX?.Call?.Lib??{},BX?.Messenger?.v2?.Lib??{},BX?.Messenger?.v2?.Service??{},BX?.Messenger?.v2?.Lib??{},BX?.Messenger?.v2?.Lib??{},BX?.Messenger?.v2?.Lib??{},BX?.UI?.Uploader??{},BX?.Messenger?.v2?.Lib??{},BX?.Messenger?.v2?.Lib??{},BX?.Vue3?.Vuex??{},BX??{},BX?.Messenger?.v2?.Lib??{},BX?.Messenger?.v2?.Lib??{},BX?.Messenger?.v2?.Lib??{},BX??{},BX?.Messenger?.v2?.Application??{},BX?.Messenger?.v2?.Lib??{},BX?.Messenger?.v2?.Const??{},BX?.Messenger?.v2?.Lib??{},BX?.Messenger?.v2?.Lib??{}));
//# sourceMappingURL=chat.bundle.js.map
