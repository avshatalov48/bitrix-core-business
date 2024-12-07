/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_message_base) {
	'use strict';

	// @vue/component
	const GeneralChannelCreationMessage = {
	  name: 'GeneralChannelCreationMessage',
	  components: {
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
	  methods: {
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
		>
			<div class="bx-im-message-general-channel-creation__container">
				<div class="bx-im-message-general-channel-creation__image"></div>
				<div class="bx-im-message-general-channel-creation__content">
					<div class="bx-im-message-general-channel-creation__title">
						<div class="bx-im-message-general-channel-creation__title-icon"></div>
						{{ loc('IM_MESSAGE_GENERAL_CHANNEL_CREATION_TITLE') }}
					</div>
					<div class="bx-im-message-general-channel-creation__description">
						<ul class="bx-im-message-general-channel-creation__description-list">
							<li>
								<div class="bx-im-message-general-channel-creation__description-list_icon --forward"></div>
								{{ loc('IM_MESSAGE_GENERAL_CHANNEL_CREATION_LIST_FORWARD') }}
							</li>
							<li>
								<div class="bx-im-message-general-channel-creation__description-list_icon --eye"></div>
								{{ loc('IM_MESSAGE_GENERAL_CHANNEL_CREATION_LIST_EYE') }}
							</li>
							<li>
								<div class="bx-im-message-general-channel-creation__description-list_icon --like"></div>
								{{ loc('IM_MESSAGE_GENERAL_CHANNEL_CREATION_LIST_LIKE') }}
							</li>
						</ul>
					</div>
				</div>
			</div>
		</BaseMessage>
	`
	};

	exports.GeneralChannelCreationMessage = GeneralChannelCreationMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Message));
//# sourceMappingURL=general-channel-creation.bundle.js.map
