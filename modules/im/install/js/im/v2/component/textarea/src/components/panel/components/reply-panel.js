import { Utils } from 'im.v2.lib.utils';
import { Parser } from 'im.v2.lib.parser';
import { FileType } from 'im.v2.const';

import '../css/message-panel.css';

import type { ImModelMessage, ImModelFile, ImModelUser } from 'im.v2.model';

const NAME_MAX_LENGTH = 40;

// @vue/component
export const ReplyPanel = {
	name: 'ReplyPanel',
	props:
	{
		messageId: {
			type: Number,
			required: true,
		},
	},
	emits: ['close'],
	computed:
	{
		message(): ImModelMessage
		{
			return this.$store.getters['messages/getById'](this.messageId);
		},
		replyAuthor(): ImModelUser
		{
			return this.$store.getters['users/get'](this.message.authorId);
		},
		replyTitle(): string
		{
			return this.replyAuthor ? this.replyAuthor.name : this.loc('IM_DIALOG_CHAT_QUOTE_DEFAULT_TITLE');
		},
		messageFile(): ImModelFile
		{
			return this.$store.getters['messages/getMessageFiles'](this.message.id)[0];
		},
		isFile(): boolean
		{
			return this.messageFile && this.messageFile.type === FileType.file;
		},
		isVideo(): boolean
		{
			return this.messageFile && this.messageFile.type === FileType.video;
		},
		isImage(): boolean
		{
			return this.messageFile && this.messageFile.type === FileType.image;
		},
		isAudio(): boolean
		{
			return this.messageFile && this.messageFile.type === FileType.audio;
		},
		showIcon(): boolean
		{
			return this.messageFile ? !this.messageFile.urlPreview : false;
		},
		truncatedFileName(): string
		{
			return Utils.file.getShortFileName(this.messageFile.name, NAME_MAX_LENGTH);
		},
		isMessageDeleted(): boolean
		{
			return this.message.isDeleted;
		},
		messageText(): string
		{
			if (this.isFile)
			{
				return this.truncatedFileName;
			}

			if (this.isAudio)
			{
				return this.loc('IM_TEXTAREA_REPLY_AUDIO_TITLE');
			}

			if (this.isMessageDeleted)
			{
				return this.loc('IM_TEXTAREA_REPLY_DELETED_TITLE');
			}

			return Parser.purify(this.message);
		},
		iconClass(): string
		{
			const iconType = Utils.file.getIconTypeByFilename(this.messageFile.name);

			return `ui-icon-file-${iconType}`;
		},
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-message-panel__container">
			<div class="bx-im-message-panel__icon --quote"></div>
			<div v-if="showIcon" class="bx-im-message-panel-file__icon">
				<div :class="iconClass" class="ui-icon"><i></i></div>
			</div>
			<div v-else-if="isImage || isVideo" class="bx-im-message-panel__image">
				<img 
					v-if="this.messageFile.urlPreview" 
					class="bx-im-message-panel__image_img" 
					:src="this.messageFile.urlPreview"
                    :alt="this.messageFile.name"
				>
			</div>
			<div class="bx-im-message-panel__content">
				<div class="bx-im-message-panel__title">{{ replyTitle }}</div>
				<div class="bx-im-message-panel__text">{{ messageText }}</div>
			</div>
			<div @click="$emit('close')" class="bx-im-message-panel__close"></div>
		</div>
	`,
};
