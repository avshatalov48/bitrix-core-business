/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_message_base) {
	'use strict';

	const TITLE_PARAMS_KEY = 'bannerTitle';
	// @vue/component
	const SupportChatCreationMessage = {
	  name: 'SupportChatCreationMessage',
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
	    },
	    componentParams() {
	      return this.message.componentParams;
	    },
	    title() {
	      return this.componentParams[TITLE_PARAMS_KEY];
	    },
	    text() {
	      return this.message.text;
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
			:withBackground="false"
			:withContextMenu="false"
			:withReactions="false"
			class="bx-im-message-support-chat-creation__scope"
		>
			<div class="bx-im-message-support-chat-creation__container">
				<div class="bx-im-message-support-chat-creation__image" />
				<div class="bx-im-message-support-chat-creation__content">
					<div class="bx-im-message-chat-creation__title">
						{{ title }}
					</div>
					<div class="bx-im-message-support-chat-creation__description">
						{{ text }}
					</div>
				</div>
			</div>
		</BaseMessage>
	`
	};

	exports.SupportChatCreationMessage = SupportChatCreationMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Message));
//# sourceMappingURL=chat-creation.bundle.js.map
