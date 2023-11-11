/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_core,im_v2_component_elements,im_v2_component_message_base,im_v2_component_message_elements,im_v2_lib_utils) {
	'use strict';

	const BUTTON_COLOR = '#00ace3';

	// @vue/component
	const CallInviteMessage = {
	  name: 'CallInviteMessage',
	  components: {
	    ButtonComponent: im_v2_component_elements.Button,
	    BaseMessage: im_v2_component_message_base.BaseMessage,
	    ReactionSelector: im_v2_component_message_elements.ReactionSelector,
	    AuthorTitle: im_v2_component_message_elements.AuthorTitle,
	    DefaultMessageContent: im_v2_component_message_elements.DefaultMessageContent
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
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonIcon: () => im_v2_component_elements.ButtonIcon,
	    buttonColorScheme() {
	      return {
	        backgroundColor: 'transparent',
	        borderColor: BUTTON_COLOR,
	        iconColor: BUTTON_COLOR,
	        textColor: BUTTON_COLOR,
	        hoverColor: 'transparent'
	      };
	    },
	    message() {
	      return this.item;
	    },
	    componentParams() {
	      return this.item.componentParams;
	    },
	    canSetReactions() {
	      return main_core.Type.isNumber(this.message.id);
	    }
	  },
	  methods: {
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    },
	    onCallButtonClick() {
	      im_v2_lib_utils.Utils.browser.openLink(this.componentParams.link);
	    }
	  },
	  template: `
		<BaseMessage :dialogId="dialogId" :item="item">
			<div class="bx-im-message-call-invite__scope bx-im-message-call-invite__container">
				<AuthorTitle v-if="withTitle" :item="item" />
				<div class="bx-im-message-call-invite__content-container">
					<div class="bx-im-message-call-invite__image"></div>
					<div class="bx-im-message-call-invite__content">
						<div class="bx-im-message-call-invite__title">
							{{ loc('IM_MESSENGER_MESSAGE_CALL_INVITE_TITLE_2') }}
						</div>
						<div class="bx-im-message-call-invite__description">
							{{ loc('IM_MESSENGER_MESSAGE_CALL_INVITE_DESCRIPTION') }}
						</div>
						<div class="bx-im-message-call-invite__buttons_container">
							<div class="bx-im-message-call-invite__buttons_item">
								<ButtonComponent
									:size="ButtonSize.L"
									:icon="ButtonIcon.Call"
									:customColorScheme="buttonColorScheme"
									:isRounded="true"
									:text="loc('IM_MESSENGER_MESSAGE_CALL_INVITE_BUTTON_JOIN')"
									@click="onCallButtonClick"
								/>
							</div>
						</div>
					</div>
				</div>
				<DefaultMessageContent :item="item" :dialogId="dialogId" :withText="false" />
				<ReactionSelector :messageId="message.id" />
			</div>
		</BaseMessage>
	`
	};

	exports.CallInviteMessage = CallInviteMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Lib));
//# sourceMappingURL=call-invite.bundle.js.map
