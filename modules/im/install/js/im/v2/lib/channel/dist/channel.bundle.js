/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_application_core,im_v2_const) {
	'use strict';

	const ChannelManager = {
	  channelTypes: new Set([im_v2_const.ChatType.generalChannel, im_v2_const.ChatType.channel, im_v2_const.ChatType.openChannel]),
	  isChannel(dialogId) {
	    const {
	      type
	    } = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId, true);
	    return ChannelManager.channelTypes.has(type);
	  }
	};

	exports.ChannelManager = ChannelManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Application,BX.Messenger.v2.Const));
//# sourceMappingURL=channel.bundle.js.map
