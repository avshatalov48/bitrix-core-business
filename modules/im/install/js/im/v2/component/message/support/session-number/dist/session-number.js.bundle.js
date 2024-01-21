/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_message_default) {
	'use strict';

	const SESSION_ID_PARAMS_KEY = 'imolSid';

	// @vue/component
	const SupportSessionNumberMessage = {
	  name: 'SupportSessionNumber',
	  components: {
	    DefaultMessage: im_v2_component_message_default.DefaultMessage
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
	  data() {
	    return {};
	  },
	  computed: {
	    message() {
	      return this.item;
	    },
	    sessionNumberText() {
	      return this.loc('IM_MESSAGE_SUPPORT_SESSION_NUMBER_TEXT', {
	        '#SESSION_NUMBER#': this.sessionNumber
	      });
	    },
	    sessionNumber() {
	      var _this$message$compone;
	      return (_this$message$compone = this.message.componentParams) == null ? void 0 : _this$message$compone[SESSION_ID_PARAMS_KEY];
	    }
	  },
	  methods: {
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<DefaultMessage :item="item" :dialogId="dialogId" :withTitle="withTitle">
			<template #before-message>
				<div class="bx-im-message-support-session-number__container">
					<div class="bx-im-message-support-session-number__content">
						{{ sessionNumberText }}
					</div>
				</div>
			</template>
		</DefaultMessage>
	`
	};

	exports.SupportSessionNumberMessage = SupportSessionNumberMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Message));
//# sourceMappingURL=session-number.js.bundle.js.map
