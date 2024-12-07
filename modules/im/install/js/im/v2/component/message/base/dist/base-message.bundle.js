/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_application_core,im_v2_lib_parser,im_v2_component_message_elements,im_v2_const,im_v2_lib_permission,im_v2_lib_channel) {
	'use strict';

	// @vue/component
	const BaseMessage = {
	  name: 'BaseMessage',
	  components: {
	    ContextMenu: im_v2_component_message_elements.ContextMenu,
	    RetryButton: im_v2_component_message_elements.RetryButton,
	    MessageKeyboard: im_v2_component_message_elements.MessageKeyboard,
	    ReactionSelector: im_v2_component_message_elements.ReactionSelector
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
	    withContextMenu: {
	      type: Boolean,
	      default: true
	    },
	    withReactions: {
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
	    },
	    afterMessageWidthLimit: {
	      type: Boolean,
	      default: true
	    }
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
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
	    isChannelPost() {
	      return im_v2_lib_channel.ChannelManager.isChannel(this.dialogId);
	    },
	    showMessageAngle() {
	      const hasAfterContent = Boolean(this.$slots['after-message']);
	      return !this.withBackground || this.isChannelPost || hasAfterContent;
	    },
	    containerClasses() {
	      return {
	        '--self': this.isSelfMessage,
	        '--opponent': this.isOpponentMessage,
	        '--has-error': this.hasError,
	        '--has-after-content': Boolean(this.$slots['after-message'])
	      };
	    },
	    bodyClasses() {
	      return {
	        '--transparent': !this.withBackground,
	        '--no-angle': this.showMessageAngle
	      };
	    },
	    showRetryButton() {
	      return this.withRetryButton && this.isSelfMessage && this.hasError;
	    },
	    showContextMenu() {
	      return this.withContextMenu && !this.hasError && this.canOpenContextMenu;
	    },
	    canOpenContextMenu() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformAction(im_v2_const.ChatActionType.openMessageMenu, this.dialogId);
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
		<div class="bx-im-message-base__wrap bx-im-message-base__scope" :class="containerClasses" :data-id="message.id">
			<div
				class="bx-im-message-base__container" 
				@click="onContainerClick"
			>
				<!-- Before content -->
				<slot name="before-message"></slot>
				<!-- Content + retry + context menu -->
				<div class="bx-im-message-base__content">
					<div class="bx-im-message-base__body" :class="bodyClasses">
						<slot></slot>
						<ReactionSelector v-if="withReactions" :messageId="message.id" />
					</div>
					<RetryButton v-if="showRetryButton" :message="message" :dialogId="dialogId"/>
					<ContextMenu
						v-else-if="showContextMenu"
						:dialogId="dialogId"
						:message="message" 
						:menuIsActiveForId="menuIsActiveForId" 
					/>
					<div v-else class="bx-im-message-base__context-menu-placeholder"></div>
				</div>
				<!-- After content -->
				<div
					v-if="$slots['after-message']"
					class="bx-im-message-base__bottom"
					:class="{'--width-limit': afterMessageWidthLimit}"
				>
					<div class="bx-im-message-base__bottom-content">
						<slot name="after-message"></slot>
					</div>
				</div>
			</div>
		</div>
	`
	};

	exports.BaseMessage = BaseMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=base-message.bundle.js.map
