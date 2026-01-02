/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_application_core,im_v2_const,im_v2_lib_user,im_v2_lib_utils) {
	'use strict';

	let nextChatId = Number.MIN_SAFE_INTEGER;
	let nextUserId = Number.MIN_SAFE_INTEGER;
	let nextMessageId = Number.MIN_SAFE_INTEGER;
	let nextFileId = Number.MIN_SAFE_INTEGER;
	const IdGenerator = {
	  getDialogId() {
	    return im_v2_lib_utils.Utils.text.getUuidV4();
	  },
	  getNextChatId() {
	    nextChatId++;
	    return nextChatId;
	  },
	  getNextUserId() {
	    nextUserId++;
	    return nextUserId;
	  },
	  getNextMessageId() {
	    nextMessageId++;
	    return nextMessageId;
	  },
	  getNextFileId() {
	    nextFileId++;
	    return nextFileId;
	  }
	};

	var _chat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chat");
	var _messages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("messages");
	var _users = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("users");
	var _files = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("files");
	var _getDefaultChatFields = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDefaultChatFields");
	class DemoChatBuilder {
	  constructor() {
	    Object.defineProperty(this, _getDefaultChatFields, {
	      value: _getDefaultChatFields2
	    });
	    Object.defineProperty(this, _chat, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _messages, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _users, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _files, {
	      writable: true,
	      value: []
	    });
	  }
	  static isDemoDialogId(dialogId) {
	    return im_v2_lib_utils.Utils.text.isUuidV4(dialogId);
	  }
	  addChat(fields) {
	    babelHelpers.classPrivateFieldLooseBase(this, _chat)[_chat] = {
	      ...babelHelpers.classPrivateFieldLooseBase(this, _getDefaultChatFields)[_getDefaultChatFields](),
	      ...fields,
	      dialogId: IdGenerator.getDialogId(),
	      chatId: IdGenerator.getNextChatId()
	    };
	    return babelHelpers.classPrivateFieldLooseBase(this, _chat)[_chat];
	  }
	  addMessage(fields) {
	    var _fields$id, _babelHelpers$classPr;
	    const newMessage = {
	      id: (_fields$id = fields.id) != null ? _fields$id : IdGenerator.getNextMessageId(),
	      chatId: (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _chat)[_chat].chatId) != null ? _babelHelpers$classPr : 0,
	      ...fields
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _messages)[_messages].push(newMessage);
	    return newMessage;
	  }
	  getNextMessageId() {
	    return IdGenerator.getNextMessageId();
	  }
	  addUser(fields) {
	    const newUser = {
	      id: IdGenerator.getNextUserId(),
	      ...fields
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _users)[_users].push(newUser);
	    return newUser;
	  }
	  addFile(fields) {
	    var _babelHelpers$classPr2;
	    const newFile = {
	      id: IdGenerator.getNextFileId(),
	      chatId: (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _chat)[_chat].chatId) != null ? _babelHelpers$classPr2 : 0,
	      ...fields
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _files)[_files].push(newFile);
	    return newFile;
	  }
	  addFileTranscription(transcription) {
	    im_v2_application_core.Core.getStore().dispatch('files/setTranscription', transcription);
	  }
	  save() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _messages)[_messages].length > 0) {
	      const [newestMessage] = babelHelpers.classPrivateFieldLooseBase(this, _messages)[_messages].slice(-1);
	      babelHelpers.classPrivateFieldLooseBase(this, _chat)[_chat].lastMessageId = newestMessage.id;
	    }
	    void im_v2_application_core.Core.getStore().dispatch('chats/set', babelHelpers.classPrivateFieldLooseBase(this, _chat)[_chat]);
	    const userManager = new im_v2_lib_user.UserManager();
	    void userManager.addUsersToModel(babelHelpers.classPrivateFieldLooseBase(this, _users)[_users]);
	    void im_v2_application_core.Core.getStore().dispatch('files/set', babelHelpers.classPrivateFieldLooseBase(this, _files)[_files]);
	    void im_v2_application_core.Core.getStore().dispatch('messages/setChatCollection', {
	      messages: babelHelpers.classPrivateFieldLooseBase(this, _messages)[_messages]
	    });
	  }
	  getChat() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _chat)[_chat];
	  }
	  getUsers() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _users)[_users];
	  }
	  getMessages() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _messages)[_messages];
	  }
	  getFiles() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _files)[_files];
	  }
	}
	function _getDefaultChatFields2() {
	  return {
	    type: im_v2_const.ChatType.chat,
	    inited: true,
	    role: im_v2_const.UserRole.member,
	    permissions: {
	      manageUi: im_v2_const.UserRole.member,
	      manageSettings: im_v2_const.UserRole.member,
	      manageUsersAdd: im_v2_const.UserRole.member,
	      manageUsersDelete: im_v2_const.UserRole.member,
	      manageMessages: im_v2_const.UserRole.member
	    }
	  };
	}

	exports.DemoChatBuilder = DemoChatBuilder;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=demo.bundle.js.map
