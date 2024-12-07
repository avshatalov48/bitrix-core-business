/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_public,im_v2_const,im_v2_lib_call,im_v2_lib_permission,main_core,im_v2_component_elements,im_v2_component_message_base,im_v2_component_message_elements,im_v2_lib_utils) {
	'use strict';

	const BUTTON_COLOR = '#00ace3';

	// @vue/component
	const CallInviteMessage = {
	  name: 'CallInviteMessage',
	  components: {
	    ButtonComponent: im_v2_component_elements.Button,
	    BaseMessage: im_v2_component_message_base.BaseMessage,
	    DefaultMessageContent: im_v2_component_message_elements.DefaultMessageContent,
	    MessageHeader: im_v2_component_message_elements.MessageHeader
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
	    },
	    isAvailable() {
	      if (this.$store.getters['recent/calls/hasActiveCall'](this.dialogId) && im_v2_lib_call.CallManager.getInstance().getCurrentCallDialogId() === this.dialogId) {
	        return true;
	      }
	      if (this.$store.getters['recent/calls/hasActiveCall']()) {
	        return false;
	      }
	      const chatCanBeCalled = im_v2_lib_call.CallManager.getInstance().chatCanBeCalled(this.dialogId);
	      const chatIsAllowedToCall = im_v2_lib_permission.PermissionManager.getInstance().canPerformAction(im_v2_const.ChatActionType.call, this.dialogId);
	      return chatCanBeCalled && chatIsAllowedToCall;
	    },
	    inviteTitle() {
	      return this.loc('IM_MESSENGER_MESSAGE_CALL_INVITE_TITLE_2');
	    },
	    descriptionTitle() {
	      return this.loc('IM_MESSENGER_MESSAGE_CALL_INVITE_DESCRIPTION');
	    }
	  },
	  methods: {
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    },
	    onCallButtonClick($event) {
	      if (im_v2_lib_utils.Utils.key.isAltOrOption($event)) {
	        im_v2_lib_utils.Utils.browser.openLink(this.componentParams.link);
	      } else {
	        im_public.Messenger.startVideoCall(this.dialogId);
	      }
	    }
	  },
	  template: `
		<BaseMessage :dialogId="dialogId" :item="item">
			<div class="bx-im-message-call-invite__scope bx-im-message-call-invite__container">
				<MessageHeader :withTitle="withTitle" :item="item" />
				<div class="bx-im-message-call-invite__content-container">
					<div class="bx-im-message-call-invite__image"></div>
					<div class="bx-im-message-call-invite__content">
						<div class="bx-im-message-call-invite__title">
							{{ inviteTitle }}
						</div>
						<div class="bx-im-message-call-invite__description">
							{{ descriptionTitle }}
						</div>
						<div v-if="isAvailable" class="bx-im-message-call-invite__buttons_container">
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
				<DefaultMessageContent :item="item" :dialogId="dialogId" :withText="false" :withAttach="false" />
			</div>
		</BaseMessage>
	`
	};

	exports.CallInviteMessage = CallInviteMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Lib));
//# sourceMappingURL=call-invite.bundle.js.map
