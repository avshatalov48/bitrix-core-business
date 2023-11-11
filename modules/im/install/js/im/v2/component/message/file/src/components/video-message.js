import { Type } from 'main.core';

import {
	MessageStatus,
	ReactionList,
	DefaultMessageContent,
	ReactionSelector,
	ContextMenu,
} from 'im.v2.component.message.elements';
import { BaseMessage } from 'im.v2.component.message.base';
import { FileType } from 'im.v2.const';

import { VideoItem } from './items/video';

import '../css/video-message.css';

import type { ImModelMessage, ImModelFile } from 'im.v2.model';

// @vue/component
export const VideoMessage = {
	name: 'VideoMessage',
	components: {
		ReactionList,
		BaseMessage,
		MessageStatus,
		DefaultMessageContent,
		VideoItem,
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
		onlyVideo(): boolean
		{
			return this.message.text.length === 0 && this.message.attach.length === 0;
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
		<BaseMessage 
			:item="item" 
			:dialogId="dialogId" 
			:withBackground="!onlyVideo"
			:withDefaultContextMenu="!onlyVideo"
		>
			<div class="bx-im-message-video__container bx-im-message-video__scope">
				<div class="bx-im-message-video__content-with-menu">
					<div class="bx-im-message-video__content">
						<VideoItem
							:key="messageFile.id"
							:item="messageFile"
							:messageId="message.id"
						/>
						<template v-if="onlyVideo">
							<div class="bx-im-message-video__message-status-container">
								<MessageStatus :item="message" :isOverlay="onlyVideo" />
							</div>
							<ReactionSelector :messageId="message.id" />
						</template>
					</div>
					<ContextMenu v-if="onlyVideo" :message="message" :menuIsActiveForId="menuIsActiveForId" />
				</div>
				<div v-if="onlyVideo" class="bx-im-message-video__reaction-list-container">
					<ReactionList :messageId="message.id" />
				</div>
				<div v-if="!onlyVideo" class="bx-im-message-video__default-message-container">
					<DefaultMessageContent :item="item" :dialogId="dialogId" />
					<ReactionSelector :messageId="message.id" />
				</div>
			</div>
		</BaseMessage>
	`,
};
