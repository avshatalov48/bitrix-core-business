import { Type } from 'main.core';
import { FileType } from 'im.v2.const';
import { UnsupportedMessage } from 'im.v2.component.message.unsupported';
import { UploadingService } from 'im.v2.provider.service.uploading';
import { UploaderFile } from 'ui.uploader.core';

import { MediaMessage } from './components/media-message';
import { BaseFileMessage } from './components/base-file-message';
import { AudioMessage } from './components/audio-message';
import { FileCollectionMessage } from './components/file-collection-message';
import { MediaContent } from './components/media-content';

import type { ImModelMessage, ImModelFile, ImModelChat } from 'im.v2.model';

const FileMessageType = Object.freeze({
	media: 'MediaMessage',
	audio: 'AudioMessage',
	base: 'BaseFileMessage',
	collection: 'FileCollectionMessage',
});

// @vue/component
export const FileMessage = {
	name: 'FileMessage',
	components: {
		BaseFileMessage,
		MediaMessage,
		AudioMessage,
		UnsupportedMessage,
		FileCollectionMessage,
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
		containerHeight: {
			type: [Number, null],
			default: null,
		},
	},
	computed:
	{
		FileType: () => FileType,
		message(): ImModelMessage
		{
			return this.$store.getters['messages/getById'](this.item.id);
		},
		messageFiles(): ImModelFile[]
		{
			const files = [];

			if (this.message.files.length === 0)
			{
				return files;
			}

			this.message.files.forEach((fileId: number) => {
				const file: ImModelFile = this.$store.getters['files/get'](fileId, true);
				files.push(file);
			});

			return files;
		},
		isGallery(): boolean
		{
			const allowedGalleryTypes: Set<string> = new Set([FileType.image, FileType.video]);
			const isMediaOnly: boolean = this.messageFiles.every((file) => {
				return allowedGalleryTypes.has(file.type);
			});
			const hasImageProp: boolean = this.messageFiles.some((file) => {
				return file.image !== false;
			});

			return isMediaOnly && hasImageProp;
		},
		componentName(): string
		{
			if (this.messageFiles.length > 1)
			{
				return this.isGallery ? FileMessageType.media : FileMessageType.collection;
			}

			const file = this.messageFiles[0];
			const hasPreview = Boolean(file.image);

			if (file.type === FileType.image && hasPreview)
			{
				return FileMessageType.media;
			}

			if (file.type === FileType.audio)
			{
				return FileMessageType.audio;
			}

			if (file.type === FileType.video && hasPreview)
			{
				return FileMessageType.media;
			}

			return FileMessageType.base;
		},
		isRealMessage(): boolean
		{
			return this.$store.getters['messages/isRealMessage'](this.message.id);
		},
	},
	methods: {
		onCancel(event: { file: ImModelFile })
		{
			const canceledFileId: string = event.file.id;
			const uploadingService: UploadingService = UploadingService.getInstance();
			const uploaderId: string = uploadingService.getUploaderIdByFileId(canceledFileId);

			uploadingService.removeFileFromUploader({
				uploaderId,
				filesIds: [canceledFileId],
				restartUploading: true,
			});

			const uploaderFiles: Array<UploaderFile> = uploadingService.getFiles(uploaderId);
			if (Type.isArrayFilled(uploaderFiles))
			{
				const actualMessageFiles: Array<string> = uploaderFiles.map((file: UploaderFile) => {
					return file.getId();
				});

				void this.$store.dispatch('messages/update', {
					id: this.message.id,
					fields: {
						files: actualMessageFiles,
					},
				});
			}
			else
			{
				const chatId: number = this.message.chatId;
				void this.$store.dispatch('messages/delete', { id: this.message.id });

				const chat: ImModelChat = this.$store.getters['chats/getByChatId'](chatId);
				const lastMessageId: string | number | null = this.$store.getters['messages/findLastChatMessageId'](chatId);

				if (Type.isString(lastMessageId) || Type.isNumber(lastMessageId))
				{
					void this.$store.dispatch('recent/update', {
						id: chat.dialogId,
						fields: { messageId: lastMessageId },
					});
				}
				else
				{
					void this.$store.dispatch('recent/delete', {
						id: chat.dialogId,
					});
				}
			}
		},
	},
	template: `
		<component 
			:is="componentName" 
			:item="message" 
			:dialogId="dialogId"
			:withTitle="withTitle" 
			:containerHeight="containerHeight"
			:withContextMenu="isRealMessage"
			@cancelClick="onCancel"
		/>
	`,
};

export { MediaContent };
