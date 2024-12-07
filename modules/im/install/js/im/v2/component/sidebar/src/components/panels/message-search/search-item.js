import { Text } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { ChatAvatar, AvatarSize, ChatTitle } from 'im.v2.component.elements';
import { EventType } from 'im.v2.const';
import { Parser } from 'im.v2.lib.parser';
import { highlightText } from 'im.v2.lib.text-highlighter';

import './css/search-item.css';

import type { ImModelMessage } from 'im.v2.model';

// @vue/component
export const SearchItem = {
	name: 'SearchItem',
	components: { ChatAvatar, ChatTitle },
	props:
	{
		messageId: {
			type: [String, Number],
			required: true,
		},
		dialogId: {
			type: String,
			required: true,
		},
		query: {
			type: String,
			default: '',
		},
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		message(): ImModelMessage
		{
			return this.$store.getters['messages/getById'](this.messageId);
		},
		authorDialogId(): string
		{
			return this.message.authorId.toString();
		},
		isSystem(): boolean
		{
			return this.message.authorId === 0;
		},
		messageText(): string
		{
			const purifiedMessage = Parser.purifyMessage(this.message);

			return highlightText(Text.encode(purifiedMessage), this.query);
		},
	},
	methods:
	{
		onItemClick()
		{
			EventEmitter.emit(EventType.dialog.goToMessageContext, {
				messageId: this.messageId,
				dialogId: this.dialogId,
			});
		},
		onMessageBodyClick(event)
		{
			if (event.target.tagName === 'A')
			{
				event.stopPropagation();
			}
		},
	},
	template: `
		<div 
			class="bx-im-message-search-item__container bx-im-message-search-item__scope" 
			@click.stop="onItemClick"
		>
			<div class="bx-im-message-search-item__header-container">
				<div class="bx-im-message-search-item__author-container">
					<template v-if="!isSystem">
						<ChatAvatar
							:size="AvatarSize.XS"
							:avatarDialogId="authorDialogId"
							:contextDialogId="dialogId"
							class="bx-im-message-search-item__author-avatar"
						/>
						<ChatTitle 
							:dialogId="authorDialogId" 
							:showItsYou="false" 
							class="bx-im-message-search-item__author-text" 
						/>
					</template>
					<template v-else>
						<span class="bx-im-message-search-item__system-author">
							{{ $Bitrix.Loc.getMessage('IM_SIDEBAR_SYSTEM_MESSAGE') }}
						</span>
					</template>
				</div>
			</div>
			<div class="bx-im-message-search-item__message-text" v-html="messageText" @click="onMessageBodyClick"></div>
		</div>
	`,
};
