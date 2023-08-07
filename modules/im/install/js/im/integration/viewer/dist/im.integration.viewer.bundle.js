/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.Integration = this.BX.Messenger.Integration || {};
(function (exports,disk_viewer_onlyofficeItem,main_core) {
	'use strict';

	var OnlyOfficeChatItem = /*#__PURE__*/function (_OnlyOfficeItem) {
	  babelHelpers.inherits(OnlyOfficeChatItem, _OnlyOfficeItem);
	  function OnlyOfficeChatItem(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, OnlyOfficeChatItem);
	    options = options || {};
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(OnlyOfficeChatItem).call(this, options));
	    _this.chatId = options.imChatId;
	    return _this;
	  }
	  babelHelpers.createClass(OnlyOfficeChatItem, [{
	    key: "setPropertiesByNode",
	    value: function setPropertiesByNode(node) {
	      babelHelpers.get(babelHelpers.getPrototypeOf(OnlyOfficeChatItem.prototype), "setPropertiesByNode", this).call(this, node);
	      this.chatId = node.dataset.imChatId;
	    }
	  }, {
	    key: "loadData",
	    value: function loadData() {
	      /** @see BXIM.callController.currentCall */
	      if (!main_core.Reflection.getClass('BXIM.callController.currentCall')) {
	        return babelHelpers.get(babelHelpers.getPrototypeOf(OnlyOfficeChatItem.prototype), "loadData", this).call(this);
	      }
	      var callController = BXIM.callController;
	      var dialogId = callController.currentCall.associatedEntity.id;
	      var chatId = this.getChatId(dialogId);
	      if (!chatId || chatId != this.chatId) {
	        return babelHelpers.get(babelHelpers.getPrototypeOf(OnlyOfficeChatItem.prototype), "loadData", this).call(this);
	      }
	      callController.unfold();
	      callController.showDocumentEditor({
	        viewerItem: this,
	        force: true
	      });
	      return new BX.Promise();
	    }
	  }, {
	    key: "getChatId",
	    value: function getChatId(dialogId) {
	      return dialogId.toString().startsWith('chat') ? dialogId.substr(4) : BXIM.messenger.userChat[dialogId];
	    }
	  }]);
	  return OnlyOfficeChatItem;
	}(disk_viewer_onlyofficeItem.OnlyOfficeItem);

	var OnlyOfficeResumeItem = /*#__PURE__*/function (_OnlyOfficeChatItem) {
	  babelHelpers.inherits(OnlyOfficeResumeItem, _OnlyOfficeChatItem);
	  function OnlyOfficeResumeItem() {
	    babelHelpers.classCallCheck(this, OnlyOfficeResumeItem);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(OnlyOfficeResumeItem).apply(this, arguments));
	  }
	  babelHelpers.createClass(OnlyOfficeResumeItem, [{
	    key: "loadData",
	    value: function loadData() {
	      /** @see BXIM.callController.currentCall */
	      if (!main_core.Reflection.getClass('BXIM.callController.currentCall')) {
	        return babelHelpers.get(babelHelpers.getPrototypeOf(OnlyOfficeResumeItem.prototype), "loadData", this).call(this);
	      }
	      var messageId = BX.MessengerCommon.diskGetMessageId(this.chatId, this.objectId);
	      if (!messageId) {
	        return babelHelpers.get(babelHelpers.getPrototypeOf(OnlyOfficeResumeItem.prototype), "loadData", this).call(this);
	      }
	      var callId = BX.MessengerCommon.getMessageParam(messageId, 'CALL_ID');
	      var callController = BXIM.callController;
	      if (!callId) {
	        return babelHelpers.get(babelHelpers.getPrototypeOf(OnlyOfficeResumeItem.prototype), "loadData", this).call(this);
	      }
	      if (callId != callController.currentCall.id) {
	        return babelHelpers.get(babelHelpers.getPrototypeOf(OnlyOfficeResumeItem.prototype), "loadData", this).call(this);
	      } else {
	        callController.unfold();
	        callController.showDocumentEditor({
	          type: BX.Call.Controller.DocumentType.Resume,
	          force: true
	        });
	      }
	      return new BX.Promise();
	    }
	  }]);
	  return OnlyOfficeResumeItem;
	}(OnlyOfficeChatItem);

	exports.OnlyOfficeChatItem = OnlyOfficeChatItem;
	exports.OnlyOfficeResumeItem = OnlyOfficeResumeItem;

}((this.BX.Messenger.Integration.Viewer = this.BX.Messenger.Integration.Viewer || {}),BX.Disk.Viewer,BX));
//# sourceMappingURL=im.integration.viewer.bundle.js.map
