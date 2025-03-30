/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_lib_utils,im_v2_application_core,im_v2_const,im_v2_lib_smileManager,imopenlines_v2_lib_messageManager) {
	'use strict';

	const serverComponentList = new Set([im_v2_const.MessageComponent.unsupported, im_v2_const.MessageComponent.chatCreation, im_v2_const.MessageComponent.ownChatCreation, im_v2_const.MessageComponent.conferenceCreation, im_v2_const.MessageComponent.callInvite, im_v2_const.MessageComponent.copilotCreation, im_v2_const.MessageComponent.copilotMessage, im_v2_const.MessageComponent.supportVote, im_v2_const.MessageComponent.supportSessionNumber, im_v2_const.MessageComponent.supportChatCreation, im_v2_const.MessageComponent.zoomInvite, im_v2_const.MessageComponent.copilotAddedUsers, im_v2_const.MessageComponent.supervisorUpdateFeature, im_v2_const.MessageComponent.supervisorEnableFeature, im_v2_const.MessageComponent.sign, im_v2_const.MessageComponent.checkIn, im_v2_const.MessageComponent.generalChatCreationMessage, im_v2_const.MessageComponent.generalChannelCreationMessage, im_v2_const.MessageComponent.channelCreationMessage, im_v2_const.MessageComponent.callMessage, im_v2_const.MessageComponent.voteMessage]);
	var _message = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("message");
	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _isServerComponent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isServerComponent");
	var _hasFiles = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasFiles");
	var _hasText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasText");
	var _hasAttach = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasAttach");
	var _isEmptyMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isEmptyMessage");
	var _isDeletedMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isDeletedMessage");
	var _isSystemMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isSystemMessage");
	var _isEmojiOnly = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isEmojiOnly");
	var _hasSmilesOnly = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasSmilesOnly");
	var _hasOnlyText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasOnlyText");
	var _isForward = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isForward");
	class MessageComponentManager {
	  constructor(message) {
	    Object.defineProperty(this, _isForward, {
	      value: _isForward2
	    });
	    Object.defineProperty(this, _hasOnlyText, {
	      value: _hasOnlyText2
	    });
	    Object.defineProperty(this, _hasSmilesOnly, {
	      value: _hasSmilesOnly2
	    });
	    Object.defineProperty(this, _isEmojiOnly, {
	      value: _isEmojiOnly2
	    });
	    Object.defineProperty(this, _isSystemMessage, {
	      value: _isSystemMessage2
	    });
	    Object.defineProperty(this, _isDeletedMessage, {
	      value: _isDeletedMessage2
	    });
	    Object.defineProperty(this, _isEmptyMessage, {
	      value: _isEmptyMessage2
	    });
	    Object.defineProperty(this, _hasAttach, {
	      value: _hasAttach2
	    });
	    Object.defineProperty(this, _hasText, {
	      value: _hasText2
	    });
	    Object.defineProperty(this, _hasFiles, {
	      value: _hasFiles2
	    });
	    Object.defineProperty(this, _isServerComponent, {
	      value: _isServerComponent2
	    });
	    Object.defineProperty(this, _message, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _message)[_message] = message;
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	  }
	  getName() {
	    const openLinesManager = new imopenlines_v2_lib_messageManager.OpenLinesMessageManager(babelHelpers.classPrivateFieldLooseBase(this, _message)[_message]);
	    if (openLinesManager.checkComponentInOpenLinesList()) {
	      return openLinesManager.getMessageComponent();
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage)[_isDeletedMessage]()) {
	      return im_v2_const.MessageComponent.deleted;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isServerComponent)[_isServerComponent]()) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].componentId;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isSystemMessage)[_isSystemMessage]()) {
	      return im_v2_const.MessageComponent.system;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _hasFiles)[_hasFiles]()) {
	      return im_v2_const.MessageComponent.file;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isEmojiOnly)[_isEmojiOnly]() || babelHelpers.classPrivateFieldLooseBase(this, _hasSmilesOnly)[_hasSmilesOnly]()) {
	      return im_v2_const.MessageComponent.smile;
	    }
	    return im_v2_const.MessageComponent.default;
	  }
	}
	function _isServerComponent2() {
	  return serverComponentList.has(babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].componentId);
	}
	function _hasFiles2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].files.length > 0;
	}
	function _hasText2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].text.length > 0;
	}
	function _hasAttach2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].attach.length > 0;
	}
	function _isEmptyMessage2() {
	  return !babelHelpers.classPrivateFieldLooseBase(this, _hasText)[_hasText]() && !babelHelpers.classPrivateFieldLooseBase(this, _hasFiles)[_hasFiles]() && !babelHelpers.classPrivateFieldLooseBase(this, _hasAttach)[_hasAttach]();
	}
	function _isDeletedMessage2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].isDeleted || babelHelpers.classPrivateFieldLooseBase(this, _isEmptyMessage)[_isEmptyMessage]();
	}
	function _isSystemMessage2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].authorId === 0;
	}
	function _isEmojiOnly2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].replyId > 0) {
	    return false;
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isForward)[_isForward]()) {
	    return false;
	  }
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _hasOnlyText)[_hasOnlyText]()) {
	    return false;
	  }
	  return im_v2_lib_utils.Utils.text.isEmojiOnly(babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].text);
	}
	function _hasSmilesOnly2() {
	  var _smileManager$smileLi, _smileManager$smileLi2;
	  if (babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].replyId > 0) {
	    return false;
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isForward)[_isForward]()) {
	    return false;
	  }
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _hasOnlyText)[_hasOnlyText]()) {
	    return false;
	  }

	  // todo: need to sync with getSmileRatio in lib/parser/src/functions/smile.js
	  const smileManager = im_v2_lib_smileManager.SmileManager.getInstance();
	  const smiles = (_smileManager$smileLi = (_smileManager$smileLi2 = smileManager.smileList) == null ? void 0 : _smileManager$smileLi2.smiles) != null ? _smileManager$smileLi : [];
	  const sortedSmiles = [...smiles].sort((a, b) => {
	    return b.typing.localeCompare(a.typing);
	  });
	  const pattern = sortedSmiles.map(smile => {
	    return im_v2_lib_utils.Utils.text.escapeRegex(smile.typing);
	  }).join('|');
	  const replacedText = babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].text.replaceAll(new RegExp(pattern, 'g'), '');
	  const hasOnlySmiles = replacedText.trim().length === 0;
	  const matchOnlySmiles = new RegExp(`(?:(?:${pattern})\\s*){4,}`);
	  return hasOnlySmiles && !matchOnlySmiles.test(babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].text);
	}
	function _hasOnlyText2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _hasText)[_hasText]()) {
	    return false;
	  }
	  return !babelHelpers.classPrivateFieldLooseBase(this, _hasFiles)[_hasFiles]() && !babelHelpers.classPrivateFieldLooseBase(this, _hasAttach)[_hasAttach]();
	}
	function _isForward2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['messages/isForward'](babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].id);
	}

	exports.MessageComponentManager = MessageComponentManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.OpenLines.v2.Lib));
//# sourceMappingURL=message-component-manager.bundle.js.map
