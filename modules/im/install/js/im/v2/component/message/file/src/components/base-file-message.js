import { Type } from 'main.core';

import { DefaultMessageContent, ReactionSelector, AuthorTitle } from 'im.v2.component.message.elements';
import { BaseMessage } from 'im.v2.component.message.base';
import { FileType } from 'im.v2.const';

import { BaseFileContextMenu } from '../classes/base-file-context-menu';
import { BaseFileItem } from './items/base-file';

import '../css/base-file-message.css';

import type { ImModelMessage, ImModelFile } from 'im.v2.model';

// @vue/component
export const BaseFileMessage = {
	name: 'BaseFileMessage',
	components: {
		BaseMessage,
		DefaultMessageContent,
		BaseFileItem,
		ReactionSelector,
		AuthorTitle,
	},
	props: {
		item: {
			type: Object,
			required: true,
		},
		dialogId: {
			type: String,
			required: true,
		},
		withTitle: {
			type: Boolean,
			default: true,
		},
	},
	computed:
	{
		FileType: () => FileType,
		message(): ImModelMessage
		{
			return this.item;
		},
		messageFile(): ImModelFile
		{
			const firstFileId = this.message.files[0];

			return this.$store.getters['files/get'](firstFileId, true);
		},
		canSetReactions(): boolean
		{
			return Type.isNumber(this.message.id);
		},
	},
	created()
	{
		this.contextMenu = new BaseFileContextMenu();
	},
	beforeUnmount()
	{
		this.contextMenu.destroy();
	},
	methods:
	{
		onOpenContextMenu(event: PointerEvent)
		{
			const context = { dialogId: this.dialogId, ...this.message };
			this.contextMenu.openMenu(context, event.target);
		},
	},
	template: `
		<BaseMessage :item="item" :dialogId="dialogId">
			<div class="bx-im-message-base-file__container">
				<AuthorTitle v-if="withTitle" :item="item" class="bx-im-message-base-file__author-title" />
				<BaseFileItem
					:key="messageFile.id"
					:item="messageFile"
					:messageId="message.id"
					@openContextMenu="onOpenContextMenu"
				/>
				<DefaultMessageContent :item="item" :dialogId="dialogId" />
				<ReactionSelector :messageId="message.id" />
			</div>
		</BaseMessage>
	`,
};
