import { Type } from 'main.core';

import { MessageStatus, ReactionList, DefaultMessageContent, ReactionSelector, ContextMenu } from 'im.v2.component.message.elements';
import { BaseMessage } from 'im.v2.component.message.base';
import { FileType } from 'im.v2.const';

import { ImageItem } from './items/image';

import '../css/image-messsage.css';

import type { ImModelMessage, ImModelFile } from 'im.v2.model';

// @vue/component
export const ImageMessage = {
	name: 'ImageMessage',
	components: {
		ReactionList,
		BaseMessage,
		MessageStatus,
		DefaultMessageContent,
		ImageItem,
		ReactionSelector,
		ContextMenu,
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
		onlyImage(): boolean
		{
			return this.message.text.length === 0 && this.message.attach.length === 0;
		},
		onlyImageOrVideo(): boolean
		{
			return this.messageFile.type === FileType.image || this.messageFile.type === FileType.video;
		},
		messageFile(): ImModelFile
		{
			const firstFileId = this.message.files[0];

			return this.$store.getters['files/get'](firstFileId, true);
		},
		needBackground(): boolean
		{
			if (this.message.text.length > 0)
			{
				return true;
			}

			return !this.onlyImageOrVideo;
		},
		canSetReactions(): boolean
		{
			return Type.isNumber(this.message.id);
		},
	},
	template: `
		<BaseMessage 
			:item="item" 
			:dialogId="dialogId" 
			:withBackground="needBackground" 
			:withDefaultContextMenu="!onlyImage"
		>
			<div class="bx-im-message-image__container">
				<div class="bx-im-message-image__content-with-menu">
					<div class="bx-im-message-image__content">
						<ImageItem
							:key="messageFile.id"
							:item="messageFile"
							:messageId="message.id"
						/>
						<template v-if="onlyImage">
							<div class="bx-im-message-image__message-status-container">
								<MessageStatus :item="message" :isOverlay="onlyImage" />
							</div>
							<ReactionSelector :messageId="message.id" />
						</template>
					</div>
					<ContextMenu v-if="onlyImage" :message="message" :menuIsActiveForId="menuIsActiveForId" />
				</div>
				<div v-if="onlyImage" class="bx-im-message-image__reaction-list-container">
					<ReactionList :messageId="message.id" />
				</div>
				<div v-if="!onlyImage" class="bx-im-message-image__default-message-container">
					<DefaultMessageContent :item="item" :dialogId="dialogId" />
					<ReactionSelector :messageId="message.id" />
				</div>
			</div>
		</BaseMessage>
	`,
};
