/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,ui_vue3_components_hint) {
	'use strict';

	// @vue/component
	const ChatHint = {
	  name: 'ChatHint',
	  components: {
	    Hint: ui_vue3_components_hint.Hint
	  },
	  props: {
	    text: {
	      type: String,
	      required: false,
	      default: ''
	    },
	    html: {
	      type: String,
	      required: false,
	      default: ''
	    },
	    popupOptions: {
	      type: Object,
	      required: false,
	      default() {
	        return {};
	      }
	    }
	  },
	  computed: {
	    preparedPopupOptions() {
	      return {
	        targetContainer: document.body,
	        ...this.popupOptions
	      };
	    }
	  },
	  template: `
		<Hint :text="text" :html="html" :popupOptions="preparedPopupOptions" />
	`
	};

	exports.ChatHint = ChatHint;

}((this.BX.Messenger.v2.Component.Elements = this.BX.Messenger.v2.Component.Elements || {}),BX.Vue3.Components));
//# sourceMappingURL=hint.bundle.js.map
