/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,ui_notification,main_core,im_v2_lib_parser,im_v2_component_message_base,ui_vue3,main_core_events,im_v2_const,im_v2_lib_utils,im_v2_application_core,im_v2_component_message_elements) {
	'use strict';

	// @vue/component
	const CopilotAuthorTitle = ui_vue3.BitrixVue.cloneComponent(im_v2_component_message_elements.AuthorTitle, {
	  name: 'CopilotAuthorTitle',
	  computed: {
	    isCopilot() {
	      const authorId = Number.parseInt(this.authorDialogId, 10);
	      const copilotUserId = this.$store.getters['users/bots/getCopilotUserId'];
	      return copilotUserId === authorId;
	    }
	  },
	  methods: {
	    onAuthorNameClick() {
	      const authorId = Number.parseInt(this.authorDialogId, 10);
	      if (!authorId || authorId === im_v2_application_core.Core.getUserId() || this.isCopilot) {
	        return;
	      }
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.insertMention, {
	        mentionText: this.user.name,
	        mentionReplacement: im_v2_lib_utils.Utils.text.getMentionBbCode(this.user.id, this.user.name)
	      });
	    }
	  },
	  template: `
		<div 
			v-if="showTitle" 
			@click="onAuthorNameClick" 
			class="bx-im-message-copilot-author-title__container"
			:class="{'--clickable': !isCopilot}"
		>
			<ChatTitle
				:dialogId="authorDialogId"
				:showItsYou="false"
				:withColor="true"
				:withLeftIcon="false"
			/>
		</div>
	`
	});

	// @vue/component
	const CopilotMessage = {
	  name: 'CopilotMessage',
	  components: {
	    CopilotAuthorTitle,
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
	    warningText() {
	      return this.loc('IM_MESSAGE_COPILOT_ANSWER_WARNING', {
	        '#LINK_START#': '<a class="bx-im-message-copilot-answer__warning_more">',
	        '#LINK_END#': '</a>'
	      });
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
				<CopilotAuthorTitle v-if="withTitle" :item="item" />
				<div class="bx-im-message-default-content__container bx-im-message-default-content__scope">
					<div class="bx-im-message-default-content__text" v-html="formattedText"></div>
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

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Message,BX.Vue3,BX.Event,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Component.Message));
//# sourceMappingURL=copilot-answer.bundle.js.map
