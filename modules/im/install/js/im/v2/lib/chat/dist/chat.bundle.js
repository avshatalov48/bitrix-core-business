/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_application_core,im_v2_const) {
	'use strict';

	const GetParameterByChatType = {
	  [im_v2_const.ChatType.taskComments]: im_v2_const.GetParameter.openTaskComments
	};
	const ChatManager = {
	  buildChatLink(dialogId) {
	    var _GetParameterByChatTy;
	    const chat = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId);
	    const chatGetParameter = (_GetParameterByChatTy = GetParameterByChatType[chat.type]) != null ? _GetParameterByChatTy : im_v2_const.GetParameter.openChat;
	    const getParams = new URLSearchParams({
	      [chatGetParameter]: dialogId
	    });
	    return `${im_v2_application_core.Core.getHost()}${im_v2_const.Path.online}?${getParams.toString()}`;
	  },
	  buildMessageLink(dialogId, messageId) {
	    const chatLink = this.buildChatLink(dialogId);
	    const chatUrl = new URL(chatLink);
	    chatUrl.searchParams.set(im_v2_const.GetParameter.openMessage, messageId.toString());
	    return chatUrl.toString();
	  }
	};

	exports.ChatManager = ChatManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Application,BX.Messenger.v2.Const));
//# sourceMappingURL=chat.bundle.js.map
