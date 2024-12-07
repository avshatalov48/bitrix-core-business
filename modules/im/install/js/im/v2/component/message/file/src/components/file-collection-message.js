import { DefaultMessageContent, MessageHeader, MessageFooter } from 'im.v2.component.message.elements';
import { BaseMessage } from 'im.v2.component.message.base';
import { FileType } from 'im.v2.const';

import { BaseFileContextMenu } from '../classes/base-file-context-menu';
import { BaseFileItem } from './items/base-file';

import '../css/file-collection-message.css';

import type { ImModelMessage } from 'im.v2.model';

const FILES_LIMIT = 10;

// @vue/component
export const FileCollectionMessage = {
	name: 'FileCollectionMessage',
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
		messageId(): string | number
		{
			return this.message.id;
		},
		fileIds(): number[]
		{
			return this.message.files.slice(0, FILES_LIMIT);
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
			<div class="bx-im-message-file-collection__container">
				<MessageHeader :withTitle="withTitle" :item="item" class="bx-im-message-file-collection__author-title" />
				<div class="bx-im-message-file-collection__items">
					<BaseFileItem
						v-for="fileId in fileIds"
						:key="fileId"
						:id="fileId"
						:messageId="messageId"
						@openContextMenu="onOpenContextMenu"
					/>
				</div>
				<DefaultMessageContent 
					:item="item" 
					:dialogId="dialogId"
					class="bx-im-message-file-collection__default-content" 
				/>
			</div>
			<MessageFooter :item="item" :dialogId="dialogId" />
		</BaseMessage>
	`,
};
