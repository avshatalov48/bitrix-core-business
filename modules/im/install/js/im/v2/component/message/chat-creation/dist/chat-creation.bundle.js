/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_public,im_v2_component_elements,im_v2_component_entitySelector,im_v2_component_message_base) {
	'use strict';

	const BUTTON_COLOR = '#00ace3';

	// @vue/component
	const ChatCreationMessage = {
	  name: 'ChatCreationMessage',
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
	    }
	  },
	  methods: {
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    },
	    onCallButtonClick() {
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
					:bindElement="$refs['add-members-button'] || {}"
					:chatId="chatId"
					:dialogId="dialogId"
					:showPopup="showAddToChatPopup"
					:popupConfig="{offsetTop: 0, offsetLeft: 0}"
					@close="showAddToChatPopup = false"
				/>
			</div>
		</BaseMessage>
	`
	};

	exports.ChatCreationMessage = ChatCreationMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.EntitySelector,BX.Messenger.v2.Component.Message));
//# sourceMappingURL=chat-creation.bundle.js.map
