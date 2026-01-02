/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_message_base,ui_iconSet_api_vue,ui_vue3_components_richLoc,im_v2_lib_utils,im_v2_lib_helpdesk,im_v2_lib_notifier,im_v2_const,im_v2_component_message_elements) {
	'use strict';

	const ICON_SIZE = 18;

	// @vue/component
	const BottomPanelContent = {
	  name: 'BottomPanelContent',
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon,
	    RichLoc: ui_vue3_components_richLoc.RichLoc,
	    MessageStatus: im_v2_component_message_elements.MessageStatus
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    }
	  },
	  computed: {
	    Color: () => im_v2_const.Color,
	    ICON_SIZE: () => ICON_SIZE,
	    OutlineIcons: () => ui_iconSet_api_vue.Outline,
	    message() {
	      return this.item;
	    }
	  },
	  methods: {
	    async onCopyClick() {
	      await im_v2_lib_utils.Utils.text.copyToClipboard(this.message.text);
	      im_v2_lib_notifier.Notifier.onCopyTextComplete();
	    },
	    onWarningDetailsClick() {
	      const ARTICLE_CODE = '25754438';
	      im_v2_lib_helpdesk.openHelpdeskArticle(ARTICLE_CODE);
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-message-ai-assistant-answer__bottom-panel">
			<BIcon
				:name="OutlineIcons.COPY"
				:color="Color.accentSoftBorderBlue"
				:hoverable="true"
				:title="loc('IM_MESSAGE_AI_ASSISTANT_ANSWER_ACTION_COPY')"
				:size="ICON_SIZE"
				@click="onCopyClick"
				class="bx-im-message-ai-assistant-answer__copy_icon"
			/>
			<span class="bx-im-message-ai-assistant-answer__warning">
				<RichLoc
					:text="loc('IM_MESSAGE_AI_ASSISTANT_ANSWER_WARNING')"
					placeholder="[url]"
				>
					<template #url="{ text }">
						<span class="bx-im-message-ai-assistant-answer__warning_link" @click="onWarningDetailsClick">
							{{ text }}
						</span>
					</template>
				</RichLoc>
			</span>
			<div class="bx-im-message-ai-assistant-answer__status-container">
				<MessageStatus :item="message"/>
			</div>
		</div>
	`
	};

	// @vue/component
	const AiAssistantMessage = {
	  name: 'AiAssistantMessage',
	  components: {
	    AuthorTitle: im_v2_component_message_elements.AuthorTitle,
	    BaseMessage: im_v2_component_message_base.BaseMessage,
	    DefaultMessageContent: im_v2_component_message_elements.DefaultMessageContent,
	    BottomPanelContent,
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
		<BaseMessage :item="item" :dialogId="dialogId" class="bx-im-message-ai-assistant-base-message__container">
			<div class="bx-im-message-default__container">
				<AuthorTitle v-if="withTitle" :item="message"/>
				<DefaultMessageContent :item="message" :dialogId="dialogId" :withMessageStatus="false" />
			</div>
			<BottomPanelContent :item="message" />
			<template #after-message v-if="hasKeyboard">
				<MessageKeyboard :item="message" :dialogId="dialogId" />
			</template>
		</BaseMessage>
	`
	};

	exports.AiAssistantMessage = AiAssistantMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Message,BX.UI.IconSet,BX.UI.Vue3.Components,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX.Messenger.v2.Component.Message));
//# sourceMappingURL=ai-assistant-answer.bundle.js.map
