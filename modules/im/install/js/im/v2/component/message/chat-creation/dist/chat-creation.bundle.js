/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_public,im_v2_component_elements,im_v2_component_entitySelector,im_v2_component_message_base,call_lib_analytics,im_v2_lib_call,ui_vue3_directives_hint) {
	'use strict';

	const BUTTON_COLOR = '#00ace3';

	// @vue/component
	const ChatCreationMessage = {
	  name: 'ChatCreationMessage',
	  directives: {
	    hint: ui_vue3_directives_hint.hint
	  },
	  components: {
	    ButtonComponent: im_v2_component_elements.Button,
	    AddToChat: im_v2_component_entitySelector.AddToChat,
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
	    return {
	      showAddToChatPopup: false
	    };
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
	    chatId() {
	      return this.message.chatId;
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    hasActiveCurrentCall() {
	      return im_v2_lib_call.CallManager.getInstance().hasActiveCurrentCall(this.dialogId);
	    },
	    hasActiveAnotherCall() {
	      return im_v2_lib_call.CallManager.getInstance().hasActiveAnotherCall(this.dialogId);
	    },
	    isActive() {
	      if (this.hasActiveCurrentCall) {
	        return true;
	      }
	      if (this.hasActiveAnotherCall) {
	        return false;
	      }
	      return im_v2_lib_call.CallManager.getInstance().chatCanBeCalled(this.dialogId);
	    },
	    userLimit() {
	      return im_v2_lib_call.CallManager.getInstance().getCallUserLimit();
	    },
	    isChatUserLimitExceeded() {
	      return im_v2_lib_call.CallManager.getInstance().isChatUserLimitExceeded(this.dialogId);
	    },
	    hintContent() {
	      if (this.isChatUserLimitExceeded) {
	        return {
	          text: this.loc('IM_LIB_CALL_USER_LIMIT_EXCEEDED_TOOLTIP', {
	            '#USER_LIMIT#': this.userLimit
	          }),
	          popupOptions: {
	            bindOptions: {
	              position: 'bottom'
	            },
	            angle: {
	              position: 'top'
	            },
	            targetContainer: document.body,
	            offsetLeft: 82,
	            offsetTop: 0
	          }
	        };
	      }
	      return null;
	    }
	  },
	  methods: {
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    },
	    onCallButtonClick() {
	      call_lib_analytics.Analytics.getInstance().onChatCreationMessageStartCallClick({
	        chatId: this.chatId
	      });
	      im_public.Messenger.startVideoCall(this.dialogId);
	    },
	    onInviteButtonClick() {
	      this.showAddToChatPopup = true;
	    }
	  },
	  template: `
		<BaseMessage
			:dialogId="dialogId"
			:item="item"
			:withContextMenu="false"
			:withReactions="false"
			:withBackground="false"
			class="bx-im-message-chat-creation__scope"
		>
			<div class="bx-im-message-chat-creation__container">
				<div class="bx-im-message-chat-creation__image"></div>
				<div class="bx-im-message-chat-creation__content">
					<div class="bx-im-message-chat-creation__title">
						{{ loc('IM_MESSAGE_CHAT_CREATION_TITLE_V2') }}
					</div>
					<div class="bx-im-message-chat-creation__description">
						{{ loc('IM_MESSAGE_CHAT_CREATION_DESCRIPTION') }}
					</div>
					<div class="bx-im-message-chat-creation__buttons_container">
						<div class="bx-im-message-chat-creation__buttons_item">
							<ButtonComponent
								:size="ButtonSize.L" 
								:icon="ButtonIcon.Call" 
								:customColorScheme="buttonColorScheme"
								:isRounded="true"
								:text="loc('IM_MESSAGE_CHAT_CREATION_BUTTON_VIDEOCALL')"
								@click="onCallButtonClick"
								:isDisabled="!isActive"
								v-hint="hintContent"
							/>
						</div>
						<div class="bx-im-message-chat-creation__buttons_item">
							<ButtonComponent
								:size="ButtonSize.L"
								:icon="ButtonIcon.AddUser"
								:customColorScheme="buttonColorScheme"
								:isRounded="true"
								:text="loc('IM_MESSAGE_CHAT_CREATION_BUTTON_INVITE_USERS')"
								@click="onInviteButtonClick"
								ref="add-members-button"
							/>
						</div>
					</div>
				</div>
				<AddToChat
					v-if="showAddToChatPopup"
					:bindElement="$refs['add-members-button'] || {}"
					:dialogId="dialogId"
					:popupConfig="{offsetTop: 0, offsetLeft: 0}"
					@close="showAddToChatPopup = false"
				/>
			</div>
		</BaseMessage>
	`
	};

	exports.ChatCreationMessage = ChatCreationMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.EntitySelector,BX.Messenger.v2.Component.Message,BX.Call.Lib,BX.Messenger.v2.Lib,BX.Vue3.Directives));
//# sourceMappingURL=chat-creation.bundle.js.map
