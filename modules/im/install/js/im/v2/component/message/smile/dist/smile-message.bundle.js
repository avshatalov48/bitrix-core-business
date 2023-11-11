/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_lib_parser,im_v2_component_message_base,im_v2_component_message_elements) {
	'use strict';

	// @vue/component
	const SmileMessage = {
	  name: 'MediaMessage',
	  components: {
	    BaseMessage: im_v2_component_message_base.BaseMessage,
	    MessageStatus: im_v2_component_message_elements.MessageStatus
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['contextMenuClick', 'quoteMessage'],
	  computed: {
	    message() {
	      return this.item;
	    },
	    formattedText() {
	      return im_v2_lib_parser.Parser.decodeMessage(this.message);
	    }
	  },
	  template: `
		<BaseMessage
			:dialogId="dialogId"
			:item="item"
			:withMessageStatus="false"
			:withText="false"
			:withBackground="false"
			:withReactions="true"
			reactionsSelectorSlot="content"
			@contextMenuClick="$emit('contextMenuClick', $event)"
			@quoteMessage="$emit('quoteMessage', $event)"
		>
			<div class="bx-im-message-base__text --emoji" v-html="formattedText"></div>
			<MessageStatus :item="message" :isOverlay="true" />
		</BaseMessage>
	`
	};

	exports.SmileMessage = SmileMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message));
//# sourceMappingURL=smile-message.bundle.js.map
