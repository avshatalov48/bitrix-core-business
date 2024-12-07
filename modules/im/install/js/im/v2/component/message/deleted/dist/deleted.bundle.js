/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_core,im_v2_component_message_base,im_v2_component_message_elements) {
	'use strict';

	// @vue/component
	const DeletedMessage = {
	  name: 'DeletedMessage',
	  components: {
	    BaseMessage: im_v2_component_message_base.BaseMessage,
	    DefaultMessageContent: im_v2_component_message_elements.DefaultMessageContent,
	    AuthorTitle: im_v2_component_message_elements.AuthorTitle
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
	    canSetReactions() {
	      return main_core.Type.isNumber(this.message.id);
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<BaseMessage :dialogId="dialogId" :item="item">
			<div class="bx-im-message-deleted__container bx-im-message-deleted__scope">
				<AuthorTitle v-if="withTitle" :item="item" />
				<div class="bx-im-message-deleted__content-container">
					<div class="bx-im-message-deleted__icon"></div>
					<div class="bx-im-message-deleted__text">{{ loc('IM_MESSENGER_MESSAGE_DELETED') }}</div>	
				</div>
				<DefaultMessageContent :item="item" :dialogId="dialogId" :withText="false" />
			</div>
		</BaseMessage>
	`
	};

	exports.DeletedMessage = DeletedMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message));
//# sourceMappingURL=deleted.bundle.js.map
