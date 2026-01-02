/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core_events,im_v2_lib_layout,im_v2_const,im_v2_application_core) {
	'use strict';

	const EVENT_NAMESPACE = 'BX.Messenger.v2.CreateChatManager';
	var _instance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("instance");
	var _isCreating = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isCreating");
	var _chatType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatType");
	var _chatTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatTitle");
	var _chatAvatarFile = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatAvatarFile");
	var _chatFields = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatFields");
	var _preselectedMembers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("preselectedMembers");
	var _includeCurrentUser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("includeCurrentUser");
	var _ownerId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("ownerId");
	class CreateChatManager extends main_core_events.EventEmitter {
	  static getInstance() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance] = new this();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance];
	  }
	  constructor(props) {
	    super(props);
	    Object.defineProperty(this, _isCreating, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _chatType, {
	      writable: true,
	      value: im_v2_const.ChatType.chat
	    });
	    Object.defineProperty(this, _chatTitle, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _chatAvatarFile, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _chatFields, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _preselectedMembers, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _includeCurrentUser, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _ownerId, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace(EVENT_NAMESPACE);
	  }
	  startChatCreation(chatTypeToCreate, params = {}) {
	    const {
	      clearCurrentCreation = true
	    } = params;
	    if (clearCurrentCreation) {
	      this.setCreationStatus(false);
	    }
	    void im_v2_lib_layout.LayoutManager.getInstance().setLayout({
	      name: im_v2_const.Layout.createChat,
	      entityId: chatTypeToCreate
	    });
	  }
	  isCreating() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _isCreating)[_isCreating];
	  }
	  getChatType() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _chatType)[_chatType];
	  }
	  getChatTitle() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _chatTitle)[_chatTitle];
	  }
	  getChatAvatar() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _chatAvatarFile)[_chatAvatarFile];
	  }
	  setChatType(type) {
	    babelHelpers.classPrivateFieldLooseBase(this, _chatType)[_chatType] = type;
	    this.emit(CreateChatManager.events.chatTypeChange, type);
	  }
	  setCreationStatus(flag) {
	    babelHelpers.classPrivateFieldLooseBase(this, _isCreating)[_isCreating] = flag;
	    this.clearFields();
	    this.emit(CreateChatManager.events.creationStatusChange, flag);
	  }
	  setChatTitle(chatTitle) {
	    babelHelpers.classPrivateFieldLooseBase(this, _chatTitle)[_chatTitle] = chatTitle;
	    this.emit(CreateChatManager.events.titleChange, chatTitle);
	  }
	  setChatAvatar(chatAvatarFile) {
	    babelHelpers.classPrivateFieldLooseBase(this, _chatAvatarFile)[_chatAvatarFile] = chatAvatarFile;
	    this.emit(CreateChatManager.events.avatarChange, chatAvatarFile);
	  }
	  saveFields(chatFields) {
	    babelHelpers.classPrivateFieldLooseBase(this, _chatFields)[_chatFields] = chatFields;
	  }
	  getFields() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _chatFields)[_chatFields];
	  }
	  clearFields() {
	    babelHelpers.classPrivateFieldLooseBase(this, _chatFields)[_chatFields] = null;
	    this.setChatTitle('');
	    this.setChatAvatar(null);
	  }
	  setPreselectedMembers(preselectedMembers) {
	    babelHelpers.classPrivateFieldLooseBase(this, _preselectedMembers)[_preselectedMembers] = preselectedMembers;
	  }
	  getChatMembers() {
	    const mappedMembers = babelHelpers.classPrivateFieldLooseBase(this, _preselectedMembers)[_preselectedMembers].map(item => [item.type, item.id]);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _includeCurrentUser)[_includeCurrentUser]) {
	      mappedMembers.push(['user', im_v2_application_core.Core.getUserId()]);
	    }
	    return mappedMembers;
	  }
	  setIncludeCurrentUser(value) {
	    babelHelpers.classPrivateFieldLooseBase(this, _includeCurrentUser)[_includeCurrentUser] = value;
	  }
	  setOwnerId(ownerId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _ownerId)[_ownerId] = ownerId;
	  }
	  getOwnerId() {
	    var _babelHelpers$classPr;
	    return (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _ownerId)[_ownerId]) != null ? _babelHelpers$classPr : im_v2_application_core.Core.getUserId();
	  }
	  getUndeselectedItems() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _includeCurrentUser)[_includeCurrentUser]) {
	      return [['user', im_v2_application_core.Core.getUserId()]];
	    }
	    return [];
	  }
	  clearExternalFields() {
	    this.setOwnerId(null);
	    this.setIncludeCurrentUser(true);
	    this.setPreselectedMembers([]);
	  }
	}
	CreateChatManager.events = {
	  creationStatusChange: 'creationStatusChange',
	  titleChange: 'titleChange',
	  avatarChange: 'avatarChange',
	  chatTypeChange: 'chatTypeChange'
	};
	Object.defineProperty(CreateChatManager, _instance, {
	  writable: true,
	  value: void 0
	});

	exports.CreateChatManager = CreateChatManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX.Messenger.v2.Application));
//# sourceMappingURL=create-chat.bundle.js.map
