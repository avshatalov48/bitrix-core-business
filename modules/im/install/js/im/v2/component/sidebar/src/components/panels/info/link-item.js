import { MessageAvatar, AvatarSize, ChatTitle } from 'im.v2.component.elements';
import { Utils } from 'im.v2.lib.utils';
import { Text } from 'main.core';

import './css/link-item.css';

import type { ImModelSidebarLinkItem } from 'im.v2.model';
import { highlightText } from 'im.v2.lib.text-highlighter';

// @vue/component
export const LinkItem = {
	name: 'LinkItem',
	components: { MessageAvatar, ChatTitle },
	props:
	{
		link: {
			type: Object,
			required: true,
		},
		contextDialogId: {
			type: String,
			required: true,
		},
		searchQuery: {
			type: String,
			default: '',
		},
	},
	emits: ['contextMenuClick'],
	data(): { showContextButton: boolean } {
		return {
			showContextButton: false,
		};
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		linkItem(): ImModelSidebarLinkItem
		{
			return this.link;
		},
		source(): string
		{
			return this.linkItem.source;
		},
		shortDescription(): string
		{
			let hostName = '';
			try
			{
				hostName = new URL(this.source).hostname;
			}
			catch (error)
			{
				hostName = this.source;
				console.error(error);
			}

			return hostName;
		},
		description(): string
		{
			const { name, description } = this.linkItem.richData;
			const descriptionToShow = description || name || this.source;

			if (this.searchQuery.length === 0)
			{
				return Utils.text.convertHtmlEntities(descriptionToShow);
			}

			return highlightText(Text.encode(descriptionToShow), this.searchQuery);
		},
		authorDialogId(): string
		{
			return this.linkItem.authorId.toString();
		},
		hasPreview(): boolean
		{
			return Boolean(this.linkItem.richData?.previewUrl);
		},
		previewStyles(): Object
		{
			return {
				backgroundImage: `url('${this.linkItem.richData?.previewUrl}')`,
				backgroundSize: 'cover',
				backgroundRepeat: 'no-repeat',
			};
		},
		iconTypeClass(): string
		{
			switch (this.linkItem.richData?.type)
			{
				case 'TASKS':
					return '--task';
				case 'LANDING':
					return '--landing';
				case 'POST':
					return '--post';
				case 'CALENDAR':
					return '--calendar';
				default:
					return '--common';
			}
		},
	},
	methods:
	{
		onContextMenuClick(event)
		{
			this.$emit('contextMenuClick', {
				id: this.linkItem.id,
				authorId: this.linkItem.authorId,
				messageId: this.linkItem.messageId,
				source: this.source,
				target: event.currentTarget,
			});
		},
	},
	template: `
		<div 
			class="bx-im-link-item__container bx-im-link-item__scope"
			@mouseover="showContextButton = true"
			@mouseleave="showContextButton = false"
		>
			<template v-if="hasPreview">
				<div class="bx-im-link-item__icon-container" :style="previewStyles"></div>
			</template>
			<template v-else>
				<div class="bx-im-link-item__icon-container" :class="iconTypeClass">
					<div class="bx-im-link-item__icon" :class="iconTypeClass" ></div>
				</div>
			</template>
			<div class="bx-im-link-item__content">
				<div class="bx-im-link-item__short-description-text">{{ shortDescription }}</div>
				<a :href="source" :title="source" target="_blank" class="bx-im-link-item__description-text" v-html="description"></a>
				<div class="bx-im-link-item__author-container">
					<MessageAvatar 
						:messageId="linkItem.messageId" 
						:authorId="linkItem.authorId"
						:size="AvatarSize.XS"
						class="bx-im-link-item__author-avatar" 
					/>
					<ChatTitle :dialogId="authorDialogId" :showItsYou="false" class="bx-im-link-item__author-text" />
				</div>
			</div>
			<div v-if="showContextButton" class="bx-im-link-item__context-menu">
				<button class="bx-im-messenger__context-menu-icon" @click="onContextMenuClick"></button>
			</div>
		</div>
	`,
};
