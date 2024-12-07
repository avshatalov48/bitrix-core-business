/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_message_base,im_v2_provider_service) {
	'use strict';

	const SAMPLE_MESSAGES = ['IM_MESSAGE_COPILOT_CREATION_ACTION_WHAT_CAN_YOU_DO', 'IM_MESSAGE_COPILOT_CREATION_ACTION_NEW_USER_GREETING', 'IM_MESSAGE_COPILOT_CREATION_ACTION_BIRTHDAY_GREETING', 'IM_MESSAGE_COPILOT_CREATION_ACTION_BIRTHDAY_PRESENT_IDEA'];

	// @vue/component
	const ChatCopilotCreationMessage = {
	  name: 'ChatCopilotCreationMessage',
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
	    sampleMessages: () => SAMPLE_MESSAGES,
	    message() {
	      return this.item;
	    },
	    chatId() {
	      return this.message.chatId;
	    },
	    preparedText() {
	      return this.loc('IM_MESSAGE_COPILOT_CREATION_TEXT', {
	        '#BR#': '\n'
	      });
	    }
	  },
	  methods: {
	    onMessageClick(text) {
	      this.getSendingService().sendMessage({
	        text: this.loc(text),
	        dialogId: this.dialogId
	      });
	    },
	    getSendingService() {
	      if (!this.sendingService) {
	        this.sendingService = im_v2_provider_service.SendingService.getInstance();
	      }
	      return this.sendingService;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<BaseMessage
			:dialogId="dialogId"
			:item="item"
			:withContextMenu="false"
			:withBackground="false"
		>
			<div class="bx-im-message-copilot-creation__container">
				<div class="bx-im-message-copilot-creation__title">CoPilot</div>
				<div class="bx-im-message-copilot-creation__text">{{ preparedText }}</div>
				<div class="bx-im-message-copilot-creation__actions">
					<div
						v-for="message in sampleMessages"
						:key="message"
						@click="onMessageClick(message)"
						class="bx-im-message-copilot-creation__action"
					>
						{{ loc(message) }}
					</div>
				</div>
			</div>
		</BaseMessage>
	`
	};

	exports.ChatCopilotCreationMessage = ChatCopilotCreationMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Message,BX.Messenger.v2.Provider.Service));
//# sourceMappingURL=copilot-creation.bundle.js.map
