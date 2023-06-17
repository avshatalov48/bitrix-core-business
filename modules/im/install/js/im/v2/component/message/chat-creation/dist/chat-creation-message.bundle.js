this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_core,im_v2_component_elements,im_v2_component_entitySelector,im_public) {
	'use strict';

	const BUTTON_COLOR = '#00ace3';

	// @vue/component
	const ChatCreationMessage = {
	  name: 'ChatCreationMessage',
	  components: {
	    Button: im_v2_component_elements.Button,
	    AddToChat: im_v2_component_entitySelector.AddToChat
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
	    descriptionPhrase() {
	      return this.loc('IM_MESSAGE_CHAT_CREATION_DESCRIPTION', {
	        '#DECORATION_START#': '<span class="bx-im-message-chat-creation__action">',
	        '#DECORATION_END#': '</span>'
	      });
	    },
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
	  mounted() {
	    const actionElement = document.querySelector('.bx-im-message-chat-creation__action');
	    main_core.Event.bind(actionElement, 'click', () => {
	      console.warn('ACTION CLICK');
	    });
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
		<div class="bx-im-message-chat-creation__scope bx-im-message-chat-creation__container">
			<div class="bx-im-message-chat-creation__image"></div>
			<div class="bx-im-message-chat-creation__content">
				<div class="bx-im-message-chat-creation__title">{{ loc('IM_MESSAGE_CHAT_CREATION_TITLE_V2') }}</div>
				<div class="bx-im-message-chat-creation__description" v-html="descriptionPhrase"></div>
				<div class="bx-im-message-chat-creation__buttons_container">
					<div class="bx-im-message-chat-creation__buttons_item">
						<Button
							:size="ButtonSize.L" 
							:icon="ButtonIcon.Call" 
							:customColorScheme="buttonColorScheme"
							:isRounded="true"
							:text="loc('IM_MESSAGE_CHAT_CREATION_BUTTON_VIDEOCALL')"
							@click="onCallButtonClick"
						/>
					</div>
					<div class="bx-im-message-chat-creation__buttons_item">
						<Button
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
	`
	};

	exports.ChatCreationMessage = ChatCreationMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.EntitySelector,BX.Messenger.v2.Lib));
//# sourceMappingURL=chat-creation-message.bundle.js.map
