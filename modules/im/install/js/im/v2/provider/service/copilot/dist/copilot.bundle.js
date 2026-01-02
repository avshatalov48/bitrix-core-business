/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_const,im_v2_lib_analytics,im_v2_provider_service_chat) {
	'use strict';

	var _sendAnalytics = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendAnalytics");
	class CopilotService {
	  constructor() {
	    Object.defineProperty(this, _sendAnalytics, {
	      value: _sendAnalytics2
	    });
	  }
	  async createChat({
	    roleCode
	  }) {
	    const chatService = new im_v2_provider_service_chat.ChatService();
	    const {
	      newDialogId,
	      newChatId
	    } = await chatService.createChat({
	      type: im_v2_const.ChatType.copilot,
	      copilotMainRole: roleCode
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _sendAnalytics)[_sendAnalytics]({
	      chatId: newChatId,
	      dialogId: newDialogId
	    });
	    await chatService.loadChatWithMessages(newDialogId);
	    return newDialogId;
	  }
	}
	function _sendAnalytics2({
	  chatId,
	  dialogId
	}) {
	  im_v2_lib_analytics.Analytics.getInstance().copilot.onCreateChat(chatId);
	  im_v2_lib_analytics.Analytics.getInstance().ignoreNextChatOpen(dialogId);
	}

	exports.CopilotService = CopilotService;

}((this.BX.Messenger.v2.Service = this.BX.Messenger.v2.Service || {}),BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Service));
//# sourceMappingURL=copilot.bundle.js.map
