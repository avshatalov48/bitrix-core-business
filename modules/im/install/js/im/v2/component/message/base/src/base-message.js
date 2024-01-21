import { Core } from 'im.v2.application.core';
import { Parser } from 'im.v2.lib.parser';
import { ContextMenu, RetryButton, MessageKeyboard } from 'im.v2.component.message.elements';

import './css/base-message.css';

import type { ImModelMessage } from 'im.v2.model';

// @vue/component
export const BaseMessage = {
	name: 'BaseMessage',
	components: { ContextMenu, RetryButton, MessageKeyboard },
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
		withDefaultContextMenu: {
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
	},
	computed:
	{
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
		containerClasses(): Object
		{
			return {
				'--self': this.isSelfMessage,
				'--opponent': this.isOpponentMessage,
				'--has-after-content': Boolean(this.$slots['after-message']),
				'--with-context-menu': this.withDefaultContextMenu,
			};
		},
		bodyClasses(): Object
		{
			return {
				'--transparent': !this.withBackground,
				'--has-error': this.hasError,
			};
		},
		showRetryButton(): boolean
		{
			return this.withRetryButton && this.isSelfMessage && this.hasError;
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
	`,
};
