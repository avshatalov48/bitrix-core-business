/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_application_core,im_v2_lib_rest,im_v2_const) {
	'use strict';

	const CommentsService = {
	  subscribe(messageId) {
	    void im_v2_application_core.Core.getStore().dispatch('messages/comments/subscribe', messageId);
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatCommentSubscribe, {
	      data: {
	        postId: messageId,
	        createIfNotExists: true,
	        autoJoin: true
	      }
	    }).catch(([error]) => {
	      console.error('CommentsService: subscribe error', error);
	    });
	  },
	  unsubscribe(messageId) {
	    void im_v2_application_core.Core.getStore().dispatch('messages/comments/unsubscribe', messageId);
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatCommentUnsubscribe, {
	      data: {
	        postId: messageId,
	        createIfNotExists: true,
	        autoJoin: true
	      }
	    }).catch(([error]) => {
	      console.error('CommentsService: unsubscribe error', error);
	    });
	  },
	  readAllChannelComments(channelDialogId) {
	    const chat = im_v2_application_core.Core.getStore().getters['chats/get'](channelDialogId, true);
	    const currentChannelCounter = im_v2_application_core.Core.getStore().getters['counters/getChannelCommentsCounter'](chat.chatId);
	    if (currentChannelCounter === 0) {
	      return Promise.resolve();
	    }
	    void im_v2_application_core.Core.getStore().dispatch('counters/readAllChannelComments', chat.chatId);
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatCommentReadAll, {
	      data: {
	        dialogId: channelDialogId
	      }
	    }).catch(([error]) => {
	      console.error('CommentsService: readAllChannelComments error', error);
	    });
	  }
	};

	exports.CommentsService = CommentsService;

}((this.BX.Messenger.v2.Service = this.BX.Messenger.v2.Service || {}),BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Const));
//# sourceMappingURL=comments.bundle.js.map
