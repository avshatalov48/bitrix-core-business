/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_application_core,im_v2_lib_parser,im_v2_component_message_elements,im_v2_const) {
	'use strict';

	// @vue/component
	const BaseMessage = {
	  name: 'BaseMessage',
	  components: {
	    ContextMenu: im_v2_component_message_elements.ContextMenu
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    withBackground: {
	      type: Boolean,
	      default: true
	    },
	    withDefaultContextMenu: {
	      type: Boolean,
	      default: true
	    },
	    menuIsActiveForId: {
	      type: [Number, String],
	      default: 0
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    message() {
	      return this.item;
	    },
	    isSystemMessage() {
	      return this.message.authorId === 0;
	    },
	    isSelfMessage() {
	      return this.message.authorId === im_v2_application_core.Core.getUserId();
	    },
	    isOpponentMessage() {
	      return !this.isSystemMessage && !this.isSelfMessage;
	    },
	    containerClasses() {
	      return {
	        '--self': this.isSelfMessage,
	        '--opponent': this.isOpponentMessage
	      };
	    },
	    bodyClasses() {
	      return {
	        '--transparent': !this.withBackground,
	        '--with-default-context-menu': this.withDefaultContextMenu
	      };
	    }
	  },
	  methods: {
	    onContainerClick(event) {
	      im_v2_lib_parser.Parser.executeClickEvent(event);
	    }
	  },
	  template: `
		<div 
			:data-id="message.id"
			class="bx-im-message-base__scope bx-im-message-base__container" 
			:class="containerClasses"
			@click="onContainerClick"
		>
			<div class="bx-im-message-base__body" :class="bodyClasses">
				<slot></slot>
			</div>
			<ContextMenu v-if="withDefaultContextMenu" :message="message" :menuIsActiveForId="menuIsActiveForId" />
		</div>
	`
	};

	exports.BaseMessage = BaseMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Const));
//# sourceMappingURL=base-message.bundle.js.map
