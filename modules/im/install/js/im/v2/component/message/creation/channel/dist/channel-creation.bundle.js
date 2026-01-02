/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_message_base) {
	'use strict';

	// @vue/component
	const ChannelCreationMessage = {
	  name: 'ChannelCreationMessage',
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
	  computed: {
	    description() {
	      return this.loc('IM_MESSAGE_CHANNEL_CREATION_DESCRIPTION', {
	        '#BR#': '\n'
	      });
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
			<div class="bx-im-message-channel-creation__container">
				<div class="bx-im-message-channel-creation__image"></div>
				<div class="bx-im-message-channel-creation__content">
					<div class="bx-im-message-channel-creation__title">
						{{ loc('IM_MESSAGE_CHANNEL_CREATION_TITLE') }}
					</div>
					<div class="bx-im-message-channel-creation__description">
						{{ description }}
					</div>
				</div>
			</div>
		</BaseMessage>
	`
	};

	exports.ChannelCreationMessage = ChannelCreationMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Message));
//# sourceMappingURL=channel-creation.bundle.js.map
