import { Type } from 'main.core';

import { MessageStatus, ReactionList, DefaultMessageContent, ReactionSelector } from 'im.v2.component.message.elements';
import { BaseMessage } from 'im.v2.component.message.base';
import { FileType, MessageType } from 'im.v2.const';

import { AudioItem } from './items/audio';

import '../css/audio-message.css';

import type { ImModelMessage, ImModelFile } from 'im.v2.model';

// @vue/component
export const AudioMessage = {
	name: 'AudioMessage',
	components: {
		ReactionList,
		BaseMessage,
		MessageStatus,
		DefaultMessageContent,
		AudioItem,
		ReactionSelector,
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
		messageType(): $Values<typeof MessageType>
		{
			return this.$store.getters['messages/getMessageType'](this.message.id);
		},
	},
	template: `
		<BaseMessage :item="item" :dialogId="dialogId">
			<div class="bx-im-message-audio__container">
				<AudioItem
					:key="messageFile.id"
					:item="messageFile"
					:messageId="message.id"
					:messageType="messageType"
				/>
			</div>
			<div class="bx-im-message-audio__default-message-container">
				<DefaultMessageContent :item="item" :dialogId="dialogId" />
				<ReactionSelector :messageId="message.id" />
			</div>
		</BaseMessage>
	`,
};
