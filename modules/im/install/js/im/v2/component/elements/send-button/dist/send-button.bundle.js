/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_const,im_v2_lib_utils) {
	'use strict';

	// @vue/component
	const SendButton = {
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    },
	    editMode: {
	      type: Boolean,
	      default: false
	    },
	    isDisabled: {
	      type: Boolean,
	      default: false
	    }
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    dialogTypeClass() {
	      return `--${this.dialog.type}`;
	    },
	    buttonHint() {
	      const sendByEnter = this.$store.getters['application/settings/get'](im_v2_const.Settings.hotkey.sendByEnter);
	      const ctrlKey = im_v2_lib_utils.Utils.platform.isMac() ? 'Cmd' : 'Ctrl';
	      const sendCombination = sendByEnter ? 'Enter' : `${ctrlKey} + Enter`;
	      return this.loc('IM_TEXTAREA_ICON_SEND_TEXT', {
	        '#SEND_MESSAGE_COMBINATION#': sendCombination
	      });
	    }
	  },
	  methods: {
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div
			:title="buttonHint"
			class="bx-im-elements-send-button"
			:class="[{'--edit': editMode, '--disabled': isDisabled, }, dialogTypeClass]"
		></div>
	`
	};

	exports.SendButton = SendButton;

}((this.BX.Messenger.v2.Component.Elements = this.BX.Messenger.v2.Component.Elements || {}),BX.Messenger.v2.Const,BX.Messenger.v2.Lib));
//# sourceMappingURL=send-button.bundle.js.map
