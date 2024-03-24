/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_message_elements,im_v2_component_message_base,im_v2_lib_parser) {
	'use strict';

	// @vue/component
	const Reply = {
	  name: 'ReplyComponent',
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    replyId: {
	      type: Number,
	      required: true
	    },
	    isForward: {
	      type: Boolean,
	      default: false
	    }
	  },
	  computed: {
	    replyMessage() {
	      return this.$store.getters['messages/getById'](this.replyId);
	    },
	    replyAuthor() {
	      return this.$store.getters['users/get'](this.replyMessage.authorId);
	    },
	    replyTitle() {
	      return this.replyAuthor ? this.replyAuthor.name : this.loc('IM_DIALOG_CHAT_QUOTE_DEFAULT_TITLE');
	    },
	    replyText() {
	      let text = im_v2_lib_parser.Parser.prepareQuote(this.replyMessage);
	      text = im_v2_lib_parser.Parser.decodeText(text);
	      return text;
	    },
	    replyContext() {
	      if (!this.isForward) {
	        return `${this.dialogId}/${this.replyId}`;
	      }
	      const replyMessageChat = this.getChatByChatId(this.replyMessage.chatId);
	      if (!replyMessageChat) {
	        return '';
	      }
	      return `${replyMessageChat.dialogId}/${this.replyId}`;
	    },
	    hasReplyContext() {
	      return this.replyContext.length > 0;
	    }
	  },
	  methods: {
	    getChatByChatId(chatId) {
	      return this.$store.getters['chats/getByChatId'](chatId, true);
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-message-quote" :class="{'--with-context': hasReplyContext}" :data-context="replyContext">
			<div class="bx-im-message-quote__wrap">
				<div class="bx-im-message-quote__name">
					<div class="bx-im-message-quote__name-text">
						{{ replyTitle }}
					</div>
				</div>
				<div class="bx-im-message-quote__text" v-html="replyText"></div>
			</div>
		</div>
	`
	};

	// @vue/component
	const DefaultMessage = {
	  name: 'DefaultMessage',
	  components: {
	    MessageHeader: im_v2_component_message_elements.MessageHeader,
	    BaseMessage: im_v2_component_message_base.BaseMessage,
	    DefaultMessageContent: im_v2_component_message_elements.DefaultMessageContent,
	    ReactionSelector: im_v2_component_message_elements.ReactionSelector,
	    Reply,
	    MessageKeyboard: im_v2_component_message_elements.MessageKeyboard
	  },
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
	      default: true
	    }
	  },
	  computed: {
	    message() {
	      return this.item;
	    },
	    isReply() {
	      return this.message.replyId !== 0;
	    },
	    isForward() {
	      return this.$store.getters['messages/isForward'](this.message.id);
	    },
	    hasKeyboard() {
	      return this.message.keyboard.length > 0;
	    }
	  },
	  template: `
		<BaseMessage :item="item" :dialogId="dialogId">
			<template #before-message v-if="$slots['before-message']">
				<slot name="before-message"></slot>
			</template>
			<div class="bx-im-message-default__container">
				<MessageHeader :withTitle="withTitle" :item="item" />
				<Reply v-if="isReply" :dialogId="dialogId" :replyId="message.replyId" :isForward="isForward" />
				<DefaultMessageContent :item="item" :dialogId="dialogId" />
				<ReactionSelector :messageId="message.id" />
			</div>
			<template #after-message v-if="hasKeyboard">
				<MessageKeyboard :item="item" :dialogId="dialogId" />
			</template>
		</BaseMessage>
	`
	};

	exports.DefaultMessage = DefaultMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Lib));
//# sourceMappingURL=default.bundle.js.map
