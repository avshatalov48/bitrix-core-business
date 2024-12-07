import { DefaultMessageContent, MessageHeader, MessageFooter } from 'im.v2.component.message.elements';
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
		MessageHeader,
		MessageFooter,
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
		onOpenContextMenu({ event, fileId }: { event: PointerEvent, fileId: number })
		{
			const context = { dialogId: this.dialogId, fileId, ...this.message };
			this.contextMenu.openMenu(context, event.target);
		},
	},
	template: `
		<BaseMessage :item="item" :dialogId="dialogId">
			<div class="bx-im-message-base-file__container">
				<MessageHeader :withTitle="withTitle" :item="item" class="bx-im-message-base-file__author-title" />
				<BaseFileItem
					:key="messageFile.id"
					:id="messageFile.id"
					:messageId="message.id"
					@openContextMenu="onOpenContextMenu"
				/>
				<DefaultMessageContent :item="item" :dialogId="dialogId" />
			</div>
			<MessageFooter :item="item" :dialogId="dialogId" />
		</BaseMessage>
	`,
};
