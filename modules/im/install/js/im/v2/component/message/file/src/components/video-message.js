import { Type } from 'main.core';

import { ChatType, FileType } from 'im.v2.const';
import {
	MessageStatus,
	ReactionList,
	DefaultMessageContent,
	MessageHeader,
	MessageFooter,
} from 'im.v2.component.message.elements';
import { BaseMessage } from 'im.v2.component.message.base';

import { VideoItem } from './items/video';

import '../css/video-message.css';

import type { ImModelMessage, ImModelFile, ImModelChat } from 'im.v2.model';

// @vue/component
export const VideoMessage = {
	name: 'VideoMessage',
	components: {
		ReactionList,
		BaseMessage,
		MessageStatus,
		DefaultMessageContent,
		VideoItem,
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
		menuIsActiveForId: {
			type: [String, Number],
			default: 0,
		},
	},
	computed:
	{
		FileType: () => FileType,
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId);
		},
		message(): ImModelMessage
		{
			return this.item;
		},
		onlyVideo(): boolean
		{
			return this.message.text.length === 0 && this.message.attach.length === 0;
		},
		hasText(): boolean
		{
			return this.message.text.length > 0;
		},
		hasAttach(): boolean
		{
			return this.message.attach.length > 0;
		},
		showBottomContainer(): boolean
		{
			return this.hasText || this.hasAttach;
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
	template: `
		<BaseMessage :item="item" :dialogId="dialogId">
			<div class="bx-im-message-video__container">
				<MessageHeader :withTitle="false" :item="item" class="bx-im-message-video__header" />
				<div class="bx-im-message-video__content">
					<VideoItem
						:key="messageFile.id"
						:item="messageFile"
						:message="message"
					/>
					<div v-if="onlyVideo" class="bx-im-message-video__message-status-container">
						<MessageStatus :item="message" :isOverlay="onlyVideo" />
					</div>
				</div>
				<div v-if="showBottomContainer" class="bx-im-message-video__bottom-container">
					<DefaultMessageContent
						v-if="hasText || hasAttach"
						:item="item"
						:dialogId="dialogId"
						:withText="hasText"
						:withAttach="hasAttach"
					/>
				</div>
				<MessageFooter :item="item" :dialogId="dialogId" />
			</div>
			<template #after-message>
				<div v-if="onlyVideo" class="bx-im-message-video__reaction-list-container">
					<ReactionList :messageId="message.id" />
				</div>
			</template>
		</BaseMessage>
	`,
};
