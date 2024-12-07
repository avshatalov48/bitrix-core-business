/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_message_default,call_component_callMessage) {
	'use strict';

	// @vue/component
	const CallMessage = {
	  name: 'CallMessage',
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
	      required: true
	    }
	  },
	  computed: {
	    messageComponentToRender() {
	      return call_component_callMessage.CallMessage ? call_component_callMessage.CallMessage : im_v2_component_message_default.DefaultMessage;
	    }
	  },
	  template: `
		<component :is="messageComponentToRender" :item="item" :dialogId="dialogId" :withTitle="withTitle" />
	`
	};

	exports.CallMessage = CallMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Message,BX.Call.Component));
//# sourceMappingURL=call-message.bundle.js.map
