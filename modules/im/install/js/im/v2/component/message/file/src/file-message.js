import { FileType } from 'im.v2.const';
import { UnsupportedMessage } from 'im.v2.component.message.unsupported';
import { Utils } from 'im.v2.lib.utils';

import { ImageMessage } from './components/image-message';
import { BaseFileMessage } from './components/base-file-message';
import { VideoMessage } from './components/video-message';
import { AudioMessage } from './components/audio-message';

import type { ImModelMessage, ImModelFile } from 'im.v2.model';

const FileMessageType = Object.freeze({
	image: 'ImageMessage',
	audio: 'AudioMessage',
	video: 'VideoMessage',
	base: 'BaseFileMessage',
	collection: 'CollectionFileMessage',
});

// @vue/component
export const FileMessage = {
	name: 'FileMessage',
	components: {
		BaseFileMessage,
		ImageMessage,
		VideoMessage,
		AudioMessage,
		UnsupportedMessage,
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
		componentName(): string
		{
			const file = this.messageFiles[0];
			const hasPreview = Boolean(file.image);

			if (file.type === FileType.image && hasPreview)
			{
				return FileMessageType.image;
			}

			if (file.type === FileType.audio)
			{
				return FileMessageType.audio;
			}

			// file.type value is empty for mkv files
			const isVideo = file.type === FileType.video || Utils.file.getFileExtension(file.name) === 'mkv';
			if (isVideo && hasPreview)
			{
				return FileMessageType.video;
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
		/>
	`,
};
