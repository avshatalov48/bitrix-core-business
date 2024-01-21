/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_application_core,im_v2_const) {
	'use strict';

	const writingTimeByChatType = {
	  [im_v2_const.ChatType.copilot]: 180000,
	  default: 35000
	};
	var _instance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("instance");
	var _writingTimers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("writingTimers");
	var _alreadyWriting = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("alreadyWriting");
	var _buildTimerId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("buildTimerId");
	var _setTimer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setTimer");
	var _clearTimer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clearTimer");
	var _updateChatWritingList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateChatWritingList");
	var _getWritingTime = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getWritingTime");
	var _getChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getChat");
	class WritingManager {
	  constructor() {
	    Object.defineProperty(this, _getChat, {
	      value: _getChat2
	    });
	    Object.defineProperty(this, _getWritingTime, {
	      value: _getWritingTime2
	    });
	    Object.defineProperty(this, _updateChatWritingList, {
	      value: _updateChatWritingList2
	    });
	    Object.defineProperty(this, _clearTimer, {
	      value: _clearTimer2
	    });
	    Object.defineProperty(this, _setTimer, {
	      value: _setTimer2
	    });
	    Object.defineProperty(this, _buildTimerId, {
	      value: _buildTimerId2
	    });
	    Object.defineProperty(this, _alreadyWriting, {
	      value: _alreadyWriting2
	    });
	    Object.defineProperty(this, _writingTimers, {
	      writable: true,
	      value: {}
	    });
	  }
	  static getInstance() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance] = new this();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance];
	  }
	  startWriting(payload) {
	    const {
	      dialogId,
	      userId,
	      userName
	    } = payload;
	    const chat = babelHelpers.classPrivateFieldLooseBase(this, _getChat)[_getChat](dialogId);
	    if (!chat) {
	      return;
	    }
	    const timerId = babelHelpers.classPrivateFieldLooseBase(this, _buildTimerId)[_buildTimerId](dialogId, userId);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _alreadyWriting)[_alreadyWriting](chat, userId)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _clearTimer)[_clearTimer](timerId);
	      babelHelpers.classPrivateFieldLooseBase(this, _writingTimers)[_writingTimers][timerId] = babelHelpers.classPrivateFieldLooseBase(this, _setTimer)[_setTimer](dialogId, userId);
	      return;
	    }
	    const newWritingList = [{
	      userId,
	      userName
	    }, ...chat.writingList];
	    babelHelpers.classPrivateFieldLooseBase(this, _updateChatWritingList)[_updateChatWritingList](dialogId, newWritingList);
	    babelHelpers.classPrivateFieldLooseBase(this, _writingTimers)[_writingTimers][timerId] = babelHelpers.classPrivateFieldLooseBase(this, _setTimer)[_setTimer](dialogId, userId);
	  }
	  stopWriting(payload) {
	    const {
	      dialogId,
	      userId
	    } = payload;
	    const chat = babelHelpers.classPrivateFieldLooseBase(this, _getChat)[_getChat](dialogId);
	    if (!chat) {
	      return;
	    }
	    const timerId = babelHelpers.classPrivateFieldLooseBase(this, _buildTimerId)[_buildTimerId](dialogId, userId);
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _alreadyWriting)[_alreadyWriting](chat, userId)) {
	      return;
	    }
	    const newWritingList = chat.writingList.filter(item => item.userId !== userId);
	    babelHelpers.classPrivateFieldLooseBase(this, _updateChatWritingList)[_updateChatWritingList](dialogId, newWritingList);
	    babelHelpers.classPrivateFieldLooseBase(this, _clearTimer)[_clearTimer](timerId);
	  }
	}
	function _alreadyWriting2(chat, userId) {
	  return chat.writingList.some(el => el.userId === userId);
	}
	function _buildTimerId2(dialogId, userId) {
	  return `${dialogId}|${userId}`;
	}
	function _setTimer2(dialogId, userId) {
	  const writingStatusTime = babelHelpers.classPrivateFieldLooseBase(this, _getWritingTime)[_getWritingTime](dialogId);
	  return setTimeout(() => {
	    this.stopWriting({
	      dialogId,
	      userId
	    });
	  }, writingStatusTime);
	}
	function _clearTimer2(timerId) {
	  clearTimeout(babelHelpers.classPrivateFieldLooseBase(this, _writingTimers)[_writingTimers][timerId]);
	  delete babelHelpers.classPrivateFieldLooseBase(this, _writingTimers)[_writingTimers][timerId];
	}
	function _updateChatWritingList2(dialogId, writingList) {
	  im_v2_application_core.Core.getStore().dispatch('chats/update', {
	    dialogId,
	    fields: {
	      writingList
	    }
	  });
	}
	function _getWritingTime2(dialogId) {
	  var _writingTimeByChatTyp;
	  const chat = babelHelpers.classPrivateFieldLooseBase(this, _getChat)[_getChat](dialogId);
	  return (_writingTimeByChatTyp = writingTimeByChatType[chat.type]) != null ? _writingTimeByChatTyp : writingTimeByChatType.default;
	}
	function _getChat2(dialogId) {
	  return im_v2_application_core.Core.getStore().getters['chats/get'](dialogId);
	}
	Object.defineProperty(WritingManager, _instance, {
	  writable: true,
	  value: void 0
	});

	exports.WritingManager = WritingManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Application,BX.Messenger.v2.Const));
//# sourceMappingURL=writing.bundle.js.map
