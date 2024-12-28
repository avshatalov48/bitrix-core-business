/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,ui_notification,im_public,im_v2_component_elements,im_v2_component_message_base,call_lib_analytics) {
	'use strict';

	const BUTTON_COLOR = '#00ace3';

	// @vue/component
	const ConferenceCreationMessage = {
	  name: 'ConferenceCreationMessage',
	  components: {
	    ButtonComponent: im_v2_component_elements.Button,
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
	      return this.$store.getters['chats/getByChatId'](this.chatId);
	    }
	  },
	  methods: {
	    onStartButtonClick() {
	      call_lib_analytics.Analytics.getInstance().onChatStartConferenceClick({
	        chatId: this.chatId
	      });
	      im_public.Messenger.openConference({
	        code: this.dialog.public.code
	      });
	    },
	    onCopyLinkClick() {
	      if (BX.clipboard.copy(this.dialog.public.link)) {
	        BX.UI.Notification.Center.notify({
	          content: this.loc('IM_MESSAGE_CONFERENCE_CREATION_LINK_COPY_SUCCESS')
	        });
	      }
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<BaseMessage
			:dialogId="dialogId"
			:item="item"
			:withContextMenu="false"
			:withReactions="false"
			:withBackground="false"
			class="bx-im-message-conference-creation__scope"
		>
			<div class="bx-im-message-conference-creation__container">
				<div class="bx-im-message-conference-creation__image"></div>
				<div class="bx-im-message-conference-creation__content">
					<div class="bx-im-message-conference-creation__title">
						{{ loc('IM_MESSAGE_CONFERENCE_CREATION_TITLE') }}
					</div>
					<div class="bx-im-message-conference-creation__description">
						{{ loc('IM_MESSAGE_CONFERENCE_CREATION_DESCRIPTION') }}
					</div>
					<div class="bx-im-message-conference-creation__buttons_container">
						<div class="bx-im-message-conference-creation__buttons_item">
							<ButtonComponent
								:size="ButtonSize.L" 
								:icon="ButtonIcon.Camera" 
								:customColorScheme="buttonColorScheme"
								:isRounded="true"
								:text="loc('IM_MESSAGE_CONFERENCE_CREATION_BUTTON_START')"
								@click="onStartButtonClick"
							/>
						</div>
						<div class="bx-im-message-conference-creation__buttons_item">
							<ButtonComponent
								:size="ButtonSize.L"
								:icon="ButtonIcon.Link"
								:customColorScheme="buttonColorScheme"
								:isRounded="true"
								:text="loc('IM_MESSAGE_CONFERENCE_CREATION_BUTTON_COPY_LINK')"
								@click="onCopyLinkClick"
							/>
						</div>
					</div>
				</div>
			</div>
		</BaseMessage>
	`
	};

	exports.ConferenceCreationMessage = ConferenceCreationMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.Message,BX.Call.Lib));
//# sourceMappingURL=conference-creation.bundle.js.map
