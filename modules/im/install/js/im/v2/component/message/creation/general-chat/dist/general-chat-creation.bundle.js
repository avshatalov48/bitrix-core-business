/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_message_base) {
	'use strict';

	// @vue/component
	const GeneralChatCreationMessage = {
	  name: 'GeneralChatCreationMessage',
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
		>
		<div class="bx-im-message-general-chat-creation__container">
			<div class="bx-im-message-general-chat-creation__image"></div>
			<div class="bx-im-message-general-chat-creation__content">
				<div class="bx-im-message-chat-creation__title">
					<div class="bx-im-message-chat-creation__title-icon"></div>
					{{ loc('IM_MESSAGE_GENERAL_CHAT_CREATION_TITLE') }}
				</div>
				<div class="bx-im-message-general-chat-creation__description">
					<ul class="bx-im-message-general-chat-creation__description-list">
						<li>
							<div class="bx-im-message-general-chat-creation__description-list_icon --chat"></div>
							{{ loc('IM_MESSAGE_GENERAL_CHAT_CREATION_LIST_CHATS') }}
						</li>
						<li>
							<div class="bx-im-message-general-chat-creation__description-list_icon --stress"></div>
							{{ loc('IM_MESSAGE_GENERAL_CHAT_CREATION_LIST_STRESS') }}
						</li>
						<li>
							<div class="bx-im-message-general-chat-creation__description-list_icon --persons"></div>
							{{ loc('IM_MESSAGE_GENERAL_CHAT_CREATION_LIST_PERSONS') }}
						</li>
					</ul>
				</div>
			</div>
		</div>
		</BaseMessage>
	`
	};

	exports.GeneralChatCreationMessage = GeneralChatCreationMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Message));
//# sourceMappingURL=general-chat-creation.bundle.js.map
