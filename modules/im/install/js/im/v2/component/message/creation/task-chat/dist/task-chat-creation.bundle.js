/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_message_base) {
	'use strict';

	// @vue/component
	const TaskChatCreationMessage = {
	  name: 'TaskChatCreationMessage',
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
	  computed: {
	    message() {
	      return this.item;
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
			class="bx-im-message-task-chat-creation__scope"
		>
			<div class="bx-im-message-task-chat-creation__container">
				<div class="bx-im-message-task-chat-creation__image"></div>
				<div class="bx-im-message-task-chat-creation__content">
					<div class="bx-im-message-task-chat-creation__title">
						{{ loc('IM_MESSAGE_TASK_CHAT_CREATION_TITLE') }}
					</div>
					<ul class="bx-im-message-task-chat-creation__list">
						<li>
							<div class="bx-im-message-task-chat-creation__list_icon --camera"></div>
							<span>{{ loc('IM_MESSAGE_TASK_CHAT_CREATION_LIST_CAMERA') }}</span>
						</li>
						<li>
							<div class="bx-im-message-task-chat-creation__list_icon --file"></div>
							<span>{{ loc('IM_MESSAGE_TASK_CHAT_CREATION_LIST_FILE') }}</span>
						</li>
						<li>
							<div class="bx-im-message-task-chat-creation__list_icon --result"></div>
							<span>{{ loc('IM_MESSAGE_TASK_CHAT_CREATION_LIST_RESULT') }}</span>
						</li>
						<li>
							<div class="bx-im-message-task-chat-creation__list_icon --forward"></div>
							<span>{{ loc('IM_MESSAGE_TASK_CHAT_CREATION_LIST_FORWARD') }}</span>
						</li>
					</ul>
				</div>
			</div>
		</BaseMessage>
	`
	};

	exports.TaskChatCreationMessage = TaskChatCreationMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Message));
//# sourceMappingURL=task-chat-creation.bundle.js.map
