import { FileType } from 'im.v2.const';
import { UnsupportedMessage } from 'im.v2.component.message.unsupported';
import { Utils } from 'im.v2.lib.utils';

import { MediaMessage } from './components/media-message';
import { BaseFileMessage } from './components/base-file-message';
import { AudioMessage } from './components/audio-message';
import { FileCollectionMessage } from './components/file-collection-message';

import type { ImModelMessage, ImModelFile } from 'im.v2.model';

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
		menuIsActiveForId: {
			type: [String, Number],
			default: 0,
		},
	},
	computed:
	{
		FileType: () => FileType,
		message(): ImModelMessage
		{
			return this.item;
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
			return this.messageFiles.every((file) => [FileType.image, FileType.video].includes(file.type));
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

			// file.type value is empty for mkv files
			const isVideo = file.type === FileType.video || Utils.file.getFileExtension(file.name) === 'mkv';
			if (isVideo && hasPreview)
			{
				return FileMessageType.media;
			}

			return FileMessageType.base;
		},
	},
	template: `
		<component 
			:is="componentName" 
			:item="message" 
			:dialogId="dialogId" 
			:withTitle="withTitle" 
			:menuIsActiveForId="menuIsActiveForId"
			:withRetryButton="false"
		/>
	`,
};
