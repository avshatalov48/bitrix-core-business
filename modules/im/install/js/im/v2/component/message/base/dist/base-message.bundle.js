/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_application_core,im_v2_lib_parser,im_v2_component_message_elements) {
	'use strict';

	// @vue/component
	const BaseMessage = {
	  name: 'BaseMessage',
	  components: {
	    ContextMenu: im_v2_component_message_elements.ContextMenu,
	    RetryButton: im_v2_component_message_elements.RetryButton,
	    MessageKeyboard: im_v2_component_message_elements.MessageKeyboard
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
	    withBackground: {
	      type: Boolean,
	      default: true
	    },
	    withDefaultContextMenu: {
	      type: Boolean,
	      default: true
	    },
	    withRetryButton: {
	      type: Boolean,
	      default: true
	    },
	    menuIsActiveForId: {
	      type: [Number, String],
	      default: 0
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
	        '--opponent': this.isOpponentMessage,
	        '--has-after-content': Boolean(this.$slots['after-message']),
	        '--with-context-menu': this.withDefaultContextMenu
	      };
	    },
	    bodyClasses() {
	      return {
	        '--transparent': !this.withBackground,
	        '--has-error': this.hasError
	      };
	    },
	    showRetryButton() {
	      return this.withRetryButton && this.isSelfMessage && this.hasError;
	    },
	    hasError() {
	      return this.message.error;
	    }
	  },
	  methods: {
	    onContainerClick(event) {
	      im_v2_lib_parser.Parser.executeClickEvent(event);
	    }
	  },
	  template: `
		<div class="bx-im-message-base__scope bx-im-message-base__wrap" :class="containerClasses" :data-id="message.id">
			<slot name="before-message"></slot>
			<div
				class="bx-im-message-base__container" 
				:class="containerClasses"
				@click="onContainerClick"
			>
				<div class="bx-im-message-base__body-with-retry-button">
					<RetryButton v-if="showRetryButton" :message="message" :dialogId="dialogId"/>
					<div class="bx-im-message-base__body" :class="bodyClasses">
						<slot></slot>
					</div>
				</div>
				<ContextMenu 
					v-if="!hasError && withDefaultContextMenu" 
					:message="message" 
					:menuIsActiveForId="menuIsActiveForId" 
				/>
			</div>
			<slot name="after-message"></slot>
		</div>
	`
	};

	exports.BaseMessage = BaseMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Message));
//# sourceMappingURL=base-message.bundle.js.map
