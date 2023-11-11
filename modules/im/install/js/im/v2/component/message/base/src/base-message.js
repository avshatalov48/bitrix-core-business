import { Core } from 'im.v2.application.core';
import { Parser } from 'im.v2.lib.parser';
import { ContextMenu } from 'im.v2.component.message.elements';
import { UserRole } from 'im.v2.const';

import './css/base-message.css';

import type { ImModelMessage, ImModelDialog } from 'im.v2.model';

// @vue/component
export const BaseMessage = {
	name: 'BaseMessage',
	components: { ContextMenu },
	props:
	{
		item: {
			type: Object,
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
		menuIsActiveForId: {
			type: [Number, String],
			default: 0,
		},
		dialogId: {
			type: String,
			required: true,
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
			};
		},
		bodyClasses(): Object
		{
			return {
				'--transparent': !this.withBackground,
				'--with-default-context-menu': this.withDefaultContextMenu,
			};
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
	`,
};
