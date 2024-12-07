import { Core } from 'im.v2.application.core';
import { Parser } from 'im.v2.lib.parser';
import { ContextMenu, RetryButton, MessageKeyboard, ReactionSelector } from 'im.v2.component.message.elements';
import { ChatActionType, ChatType } from 'im.v2.const';
import { PermissionManager } from 'im.v2.lib.permission';
import { ChannelManager } from 'im.v2.lib.channel';

import './css/base-message.css';

import type { ImModelChat, ImModelMessage } from 'im.v2.model';

// @vue/component
export const BaseMessage = {
	name: 'BaseMessage',
	components: { ContextMenu, RetryButton, MessageKeyboard, ReactionSelector },
	props:
	{
		item: {
			type: Object,
			required: true,
		},
		dialogId: {
			type: String,
			required: true,
		},
		withBackground: {
			type: Boolean,
			default: true,
		},
		withContextMenu: {
			type: Boolean,
			default: true,
		},
		withReactions: {
			type: Boolean,
			default: true,
		},
		withRetryButton: {
			type: Boolean,
			default: true,
		},
		menuIsActiveForId: {
			type: [Number, String],
			default: 0,
		},
		afterMessageWidthLimit: {
			type: Boolean,
			default: true,
		},
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		message(): ImModelMessage
		{
			return this.item;
		},
		isSystemMessage(): boolean
		{
			return this.message.authorId === 0;
		},
		isSelfMessage(): boolean
		{
			return this.message.authorId === Core.getUserId();
		},
		isOpponentMessage(): boolean
		{
			return !this.isSystemMessage && !this.isSelfMessage;
		},
		isChannelPost(): boolean
		{
			return ChannelManager.isChannel(this.dialogId);
		},
		showMessageAngle(): boolean
		{
			const hasAfterContent = Boolean(this.$slots['after-message']);

			return !this.withBackground || this.isChannelPost || hasAfterContent;
		},
		containerClasses(): {[className: string]: boolean}
		{
			return {
				'--self': this.isSelfMessage,
				'--opponent': this.isOpponentMessage,
				'--has-error': this.hasError,
				'--has-after-content': Boolean(this.$slots['after-message']),
			};
		},
		bodyClasses(): {[className: string]: boolean}
		{
			return {
				'--transparent': !this.withBackground,
				'--no-angle': this.showMessageAngle,
			};
		},
		showRetryButton(): boolean
		{
			return this.withRetryButton && this.isSelfMessage && this.hasError;
		},
		showContextMenu(): boolean
		{
			return this.withContextMenu && !this.hasError && this.canOpenContextMenu;
		},
		canOpenContextMenu(): boolean
		{
			return PermissionManager.getInstance().canPerformAction(ChatActionType.openMessageMenu, this.dialogId);
		},
		hasError(): boolean
		{
			return this.message.error;
		},
	},
	methods:
	{
		onContainerClick(event: PointerEvent)
		{
			Parser.executeClickEvent(event);
		},
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
	`,
};
