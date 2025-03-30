/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_message_unsupported,vote_component_message) {
	'use strict';

	// @vue/component
	const VoteMessage = {
	  name: 'VoteMessage',
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    },
	    withTitle: {
	      type: Boolean,
	      required: true
	    }
	  },
	  computed: {
	    messageComponentToRender() {
	      return vote_component_message.VoteChatDisplay ? vote_component_message.VoteChatDisplay : im_v2_component_message_unsupported.UnsupportedMessage;
	    }
	  },
	  template: `
		<component :is="messageComponentToRender" :item="item" :dialogId="dialogId" :withTitle="withTitle" />
	`
	};

	exports.VoteMessage = VoteMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Message,BX.Vote.Component));
//# sourceMappingURL=vote.bundle.js.map
