/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_message_elements,im_v2_component_message_base,main_core,im_v2_lib_parser) {
	'use strict';

	const NO_CONTEXT_TAG = 'none';

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
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    replyMessage() {
	      return this.$store.getters['messages/getById'](this.replyId);
	    },
	    replyMessageChat() {
	      var _this$replyMessage;
	      return this.$store.getters['chats/getByChatId']((_this$replyMessage = this.replyMessage) == null ? void 0 : _this$replyMessage.chatId);
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
	    isQuoteFromTheSameChat() {
	      var _this$replyMessage2;
	      return ((_this$replyMessage2 = this.replyMessage) == null ? void 0 : _this$replyMessage2.chatId) === this.dialog.chatId;
	    },
	    replyContext() {
	      if (!this.isQuoteFromTheSameChat) {
	        return NO_CONTEXT_TAG;
	      }
	      if (!this.isForward) {
	        return `${this.dialogId}/${this.replyId}`;
	      }
	      return `${this.replyMessageChat.dialogId}/${this.replyId}`;
	    },
	    canShowReply() {
	      return !main_core.Type.isNil(this.replyMessage) && !main_core.Type.isNil(this.replyMessageChat);
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div v-if="canShowReply" class="bx-im-message-quote" :data-context="replyContext">
			<div class="bx-im-message-quote__wrap">
				<div class="bx-im-message-quote__name">
					<div class="bx-im-message-quote__name-text">{{ replyTitle }}</div>
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
	    MessageFooter: im_v2_component_message_elements.MessageFooter,
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
		<BaseMessage :item="item" :dialogId="dialogId" :afterMessageWidthLimit="false">
			<template #before-message v-if="$slots['before-message']">
				<slot name="before-message"></slot>
			</template>
			<div class="bx-im-message-default__container">
				<MessageHeader :withTitle="withTitle" :item="item" />
				<Reply v-if="isReply" :dialogId="dialogId" :replyId="message.replyId" :isForward="isForward" />
				<DefaultMessageContent :item="item" :dialogId="dialogId" />
			</div>
			<MessageFooter :item="item" :dialogId="dialogId" />
			<template #after-message v-if="hasKeyboard">
				<MessageKeyboard :item="item" :dialogId="dialogId" />
			</template>
		</BaseMessage>
	`
	};

	exports.DefaultMessage = DefaultMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX,BX.Messenger.v2.Lib));
//# sourceMappingURL=default.bundle.js.map
