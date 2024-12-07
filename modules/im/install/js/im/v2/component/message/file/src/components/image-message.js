import { Type } from 'main.core';

import {
	MessageStatus,
	ReactionList,
	DefaultMessageContent,
	MessageHeader,
	MessageFooter,
} from 'im.v2.component.message.elements';
import { BaseMessage } from 'im.v2.component.message.base';
import { ChatType, FileType } from 'im.v2.const';

import { ImageItem } from './items/image';

import '../css/image-messsage.css';

import type { ImModelMessage, ImModelFile, ImModelChat } from 'im.v2.model';

// @vue/component
export const ImageMessage = {
	name: 'ImageMessage',
	components: {
		ReactionList,
		BaseMessage,
		MessageStatus,
		DefaultMessageContent,
		ImageItem,
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
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId);
		},
		onlyImage(): boolean
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
		showContextMenu(): boolean
		{
			return this.onlyImage;
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
			<div class="bx-im-message-image__container">
				<MessageHeader :withTitle="false" :item="item" class="bx-im-message-image__header" />
				<div class="bx-im-message-image__content">
					<ImageItem
						:key="messageFile.id"
						:item="messageFile"
						:message="message"
					/>
					<div v-if="onlyImage" class="bx-im-message-image__message-status-container">
						<MessageStatus :item="message" :isOverlay="onlyImage" />
					</div>
				</div>
				<div v-if="showBottomContainer" class="bx-im-message-image__bottom-container">
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
				<div v-if="onlyImage" class="bx-im-message-image__reaction-list-container">
					<ReactionList :messageId="message.id" />
				</div>
			</template>
		</BaseMessage>
	`,
};
