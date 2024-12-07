/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,ui_notification,main_core,im_v2_lib_utils,im_v2_lib_parser,im_v2_component_message_base,im_v2_component_message_elements) {
	'use strict';

	// @vue/component
	const CopilotMessage = {
	  name: 'CopilotMessage',
	  components: {
	    AuthorTitle: im_v2_component_message_elements.AuthorTitle,
	    BaseMessage: im_v2_component_message_base.BaseMessage,
	    ReactionList: im_v2_component_message_elements.ReactionList,
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
	    formattedText() {
	      return im_v2_lib_parser.Parser.decodeMessage(this.item);
	    },
	    canSetReactions() {
	      return main_core.Type.isNumber(this.message.id);
	    },
	    isReply() {
	      return this.message.replyId !== 0;
	    },
	    isError() {
	      var _this$message$compone;
	      return ((_this$message$compone = this.message.componentParams) == null ? void 0 : _this$message$compone.copilotError) === true;
	    },
	    warningText() {
	      return this.loc('IM_MESSAGE_COPILOT_ANSWER_WARNING', {
	        '#LINK_START#': '<a class="bx-im-message-copilot-answer__warning_more">',
	        '#LINK_END#': '</a>'
	      });
	    }
	  },
	  methods: {
	    async onCopyClick() {
	      await im_v2_lib_utils.Utils.text.copyToClipboard(this.message.text);
	      BX.UI.Notification.Center.notify({
	        content: main_core.Loc.getMessage('IM_MESSAGE_COPILOT_ANSWER_ACTION_COPY_SUCCESS')
	      });
	    },
	    onWarningDetailsClick(event) {
	      var _BX$Helper;
	      if (!main_core.Dom.hasClass(event.target, 'bx-im-message-copilot-answer__warning_more')) {
	        return;
	      }
	      const ARTICLE_CODE = '20412666';
	      (_BX$Helper = BX.Helper) == null ? void 0 : _BX$Helper.show(`redirect=detail&code=${ARTICLE_CODE}`);
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<BaseMessage :item="item" :dialogId="dialogId" class="bx-im-message-copilot-base-message__container">
			<div class="bx-im-message-default__container bx-im-message-copilot-answer__container" :class="{'--error': isError}">
				<AuthorTitle v-if="withTitle" :item="item" />
				<div class="bx-im-message-default-content__container bx-im-message-default-content__scope">
					<div class="bx-im-message-default-content__text" v-html="formattedText"></div>
					<ReactionList
						v-if="canSetReactions"
						:messageId="message.id"
						:contextDialogId="dialogId"
						class="bx-im-message-default-content__reaction-list"
					/>
					<div v-if="isError" class="bx-im-message-default-content__bottom-panel">
						<div class="bx-im-message-default-content__status-container">
							<MessageStatus :item="message" />
						</div>
					</div>
				</div>
			</div>
			<div v-if="!isError" class="bx-im-message-copilot-answer__bottom-panel">
				<div class="bx-im-message-copilot-answer__panel-content">
					<button
						:title="loc('IM_MESSAGE_COPILOT_ANSWER_ACTION_COPY')"
						@click="onCopyClick"
						class="bx-im-message-copilot-answer__copy_icon"
					></button>
					<span 
						v-html="warningText"
						@click="onWarningDetailsClick"
						class="bx-im-message-copilot-answer__warning"
					></span>
				</div>
				<div class="bx-im-message-default-content__status-container">
					<MessageStatus :item="message" />
				</div>
			</div>
		</BaseMessage>
	`
	};

	exports.CopilotMessage = CopilotMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message));
//# sourceMappingURL=copilot-answer.bundle.js.map
