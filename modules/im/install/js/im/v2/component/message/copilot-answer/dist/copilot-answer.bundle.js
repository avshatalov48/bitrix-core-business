/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,ui_notification,im_v2_lib_parser,im_v2_provider_service,main_core,im_v2_component_message_elements,im_v2_component_message_base) {
	'use strict';

	// @vue/component
	const CopilotMessage = {
	  name: 'CopilotMessage',
	  components: {
	    AuthorTitle: im_v2_component_message_elements.AuthorTitle,
	    BaseMessage: im_v2_component_message_base.BaseMessage,
	    DefaultMessageContent: im_v2_component_message_elements.DefaultMessageContent,
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
	    hasMore() {
	      var _this$message$compone2;
	      return ((_this$message$compone2 = this.message.componentParams) == null ? void 0 : _this$message$compone2.copilotHasMore) === true;
	    }
	  },
	  methods: {
	    onCopyClick() {
	      var _BX$clipboard;
	      if ((_BX$clipboard = BX.clipboard) != null && _BX$clipboard.copy(this.message.text)) {
	        BX.UI.Notification.Center.notify({
	          content: main_core.Loc.getMessage('IM_MESSAGE_COPILOT_ANSWER_ACTION_COPY_SUCCESS')
	        });
	      }
	    },
	    onContinueClick() {
	      this.getSendingService().sendMessage({
	        text: this.loc('IM_MESSAGE_COPILOT_ANSWER_CONTINUE_TEXT'),
	        dialogId: this.dialogId
	      });
	    },
	    getSendingService() {
	      if (!this.sendingService) {
	        this.sendingService = im_v2_provider_service.SendingService.getInstance();
	      }
	      return this.sendingService;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<BaseMessage :item="item" :dialogId="dialogId" class="bx-im-message-copilot-base-message__container">
			<div class="bx-im-message-default__container bx-im-message-copilot-answer__container" :class="{'--error': isError}">
				<AuthorTitle v-if="withTitle" :item="item" />
				<div class="bx-im-message-default-content__container bx-im-message-default-content__scope">
					<div class="bx-im-message-default-content__text" v-html="formattedText"></div>
					<div class="bx-im-message-default-content__bottom-panel">
						<div v-if="!isError" class="bx-im-message-copilot-answer__actions">
							<div class="bx-im-message-copilot-answer__action" @click="onCopyClick">
								<div class="bx-im-message-copilot-answer__action_icon --copy"></div>
								<div class="bx-im-message-copilot-answer__action_text">
									{{ loc('IM_MESSAGE_COPILOT_ANSWER_ACTION_COPY') }}
								</div>
							</div>
							<div v-if="hasMore" class="bx-im-message-copilot-answer__action" @click="onContinueClick">
								<div class="bx-im-message-copilot-answer__action_icon --continue"></div>
								<div class="bx-im-message-copilot-answer__action_text">
									{{ loc('IM_MESSAGE_COPILOT_ANSWER_ACTION_CONTINUE') }}
								</div>
							</div>
						</div>
						<div class="bx-im-message-default-content__status-container">
							<MessageStatus :item="message" />
						</div>
					</div>
				</div>
			</div>
		</BaseMessage>
	`
	};

	exports.CopilotMessage = CopilotMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Provider.Service,BX,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message));
//# sourceMappingURL=copilot-answer.bundle.js.map
