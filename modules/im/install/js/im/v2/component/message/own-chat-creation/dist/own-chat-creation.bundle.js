/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_message_base) {
	'use strict';

	// @vue/component
	const OwnChatCreationMessage = {
	  name: 'OwnChatCreationMessage',
	  components: {
	    BaseMessage: im_v2_component_message_base.BaseMessage
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
	  data() {
	    return {};
	  },
	  computed: {
	    message() {
	      return this.item;
	    },
	    description() {
	      return this.loc('IM_MESSAGE_OWN_CHAT_CREATION_DESCRIPTION', {
	        '#BR#': '\n'
	      });
	    }
	  },
	  methods: {
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<BaseMessage
			:dialogId="dialogId"
			:item="item"
			:withContextMenu="false"
			:withReactions="false"
			:withBackground="false"
			class="bx-im-message-own-chat-creation__scope"
		>
			<div class="bx-im-message-own-chat-creation__container">
				<div class="bx-im-message-own-chat-creation__image"></div>
				<div class="bx-im-message-own-chat-creation__content">
					<div class="bx-im-message-chat-creation__title">
						{{ loc('IM_MESSAGE_OWN_CHAT_CREATION_TITLE') }}
					</div>
					<div class="bx-im-message-own-chat-creation__description">
						{{ description }}
					</div>
				</div>
			</div>
		</BaseMessage>
	`
	};

	exports.OwnChatCreationMessage = OwnChatCreationMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Message));
//# sourceMappingURL=own-chat-creation.bundle.js.map
