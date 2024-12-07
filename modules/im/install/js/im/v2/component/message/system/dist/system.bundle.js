/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_core,im_v2_component_message_base,im_v2_component_message_elements) {
	'use strict';

	// @vue/component
	const SystemMessage = {
	  name: 'SystemMessage',
	  components: {
	    BaseMessage: im_v2_component_message_base.BaseMessage,
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
	    message() {
	      return this.item;
	    },
	    canSetReactions() {
	      return main_core.Type.isNumber(this.message.id);
	    }
	  },
	  template: `
		<BaseMessage
			:dialogId="dialogId"
			:item="item"
			:withTitle="false"
			:withBackground="false"
			class="bx-im-message-system__scope"
		>
			<div class="bx-im-message-system__container">
				<DefaultMessageContent :item="item" :dialogId="dialogId" />
			</div>
		</BaseMessage>
	`
	};

	exports.SystemMessage = SystemMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message));
//# sourceMappingURL=system.bundle.js.map
