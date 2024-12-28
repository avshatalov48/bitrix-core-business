/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_message_elements,im_v2_component_message_base) {
	'use strict';

	// @vue/component
	const DefaultMessage = {
	  name: 'DefaultMessage',
	  components: {
	    MessageHeader: im_v2_component_message_elements.MessageHeader,
	    MessageFooter: im_v2_component_message_elements.MessageFooter,
	    BaseMessage: im_v2_component_message_base.BaseMessage,
	    DefaultMessageContent: im_v2_component_message_elements.DefaultMessageContent,
	    ReactionSelector: im_v2_component_message_elements.ReactionSelector,
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

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message));
//# sourceMappingURL=default.bundle.js.map
