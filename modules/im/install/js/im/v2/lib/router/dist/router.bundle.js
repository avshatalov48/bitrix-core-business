/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_public,im_v2_const) {
	'use strict';

	const Router = {
	  init() {
	    Router.checkGetParams();
	  },
	  checkGetParams() {
	    const urlParams = new URLSearchParams(window.location.search);
	    if (urlParams.has(im_v2_const.GetParameter.openNotifications)) {
	      void im_public.Messenger.openNotifications();
	    } else if (urlParams.has(im_v2_const.GetParameter.openHistory)) {
	      const dialogId = urlParams.get(im_v2_const.GetParameter.openHistory);
	      void im_public.Messenger.openLinesHistory(dialogId);
	    } else if (urlParams.has(im_v2_const.GetParameter.openLines)) {
	      const dialogId = urlParams.get(im_v2_const.GetParameter.openLines);
	      void im_public.Messenger.openLines(dialogId);
	    } else if (urlParams.has(im_v2_const.GetParameter.botContext)) {
	      const dialogId = urlParams.get(im_v2_const.GetParameter.openChat);
	      const context = urlParams.get(im_v2_const.GetParameter.botContext);
	      void im_public.Messenger.openChatWithBotContext(dialogId, context);
	    } else if (urlParams.has(im_v2_const.GetParameter.openChat)) {
	      const dialogId = urlParams.get(im_v2_const.GetParameter.openChat);
	      void im_public.Messenger.openChat(dialogId, extractMessageId(urlParams));
	    } else if (urlParams.has(im_v2_const.GetParameter.openSettings)) {
	      const settingsSection = urlParams.get(im_v2_const.GetParameter.openSettings);
	      void im_public.Messenger.openSettings({
	        onlyPanel: settingsSection == null ? void 0 : settingsSection.toLowerCase()
	      });
	    } else if (urlParams.has(im_v2_const.GetParameter.openCopilotChat)) {
	      const dialogId = urlParams.get(im_v2_const.GetParameter.openCopilotChat);
	      void im_public.Messenger.openCopilot(dialogId);
	    } else if (urlParams.has(im_v2_const.GetParameter.openChannel)) {
	      const dialogId = urlParams.get(im_v2_const.GetParameter.openChannel);
	      void im_public.Messenger.openNavigationItem({
	        id: im_v2_const.NavigationMenuItem.channel,
	        entityId: dialogId
	      });
	    } else if (urlParams.has(im_v2_const.GetParameter.openCollab)) {
	      const dialogId = urlParams.get(im_v2_const.GetParameter.openCollab);
	      void im_public.Messenger.openCollab(dialogId != null ? dialogId : '');
	    } else if (urlParams.has(im_v2_const.GetParameter.openTaskComments)) {
	      const dialogId = urlParams.get(im_v2_const.GetParameter.openTaskComments);
	      void im_public.Messenger.openTaskComments(dialogId != null ? dialogId : '', extractMessageId(urlParams));
	    }
	  }
	};
	const extractMessageId = urlParams => {
	  let messageId = urlParams.get(im_v2_const.GetParameter.openMessage);
	  messageId = messageId ? Number(messageId) : 0;
	  return messageId;
	};

	exports.Router = Router;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Const));
//# sourceMappingURL=router.bundle.js.map
