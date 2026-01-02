import 'ui.icons';
import { Text } from 'main.core';

import { Utils } from 'im.v2.lib.utils';
import { highlightText } from 'im.v2.lib.text-highlighter';
import { ChatTitle } from 'im.v2.component.elements.chat-title';
import { MessageAvatar, AvatarSize } from 'im.v2.component.elements.avatar';

import '../css/file-detail-item.css';

import type { JsonObject } from 'main.core';
import type { ImModelSidebarFileItem, ImModelFile } from 'im.v2.model';

// @vue/component
export const FileDetailItem = {
	name: 'FileDetailItem',
	components: { MessageAvatar, ChatTitle },
	props: {
		fileItem: {
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
			required: false,
		},
		viewerContext: {
			type: String,
			default: '',
		},
	},
	emits: ['contextMenuClick'],
	data(): JsonObject
	{
		return {
			showContextButton: false,
		};
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		sidebarFileItem(): ImModelSidebarFileItem
		{
			return this.fileItem;
		},
		file(): ImModelFile
		{
			return this.$store.getters['files/get'](this.sidebarFileItem.fileId, true);
		},
		fileIconClass(): string
		{
			return `ui-icon ui-icon-file-${this.file.icon}`;
		},
		fileShortName(): string
		{
			const NAME_MAX_LENGTH = 15;
			const shortName = Utils.file.getShortFileName(this.file.name, NAME_MAX_LENGTH);

			if (this.searchQuery.length === 0)
			{
				return Text.encode(shortName);
			}

			return highlightText(Text.encode(shortName), this.searchQuery);
		},
		fileSize(): string
		{
			return Utils.file.formatFileSize(this.file.size);
		},
		viewerAttributes(): Object
		{
			return Utils.file.getViewerDataAttributes({
				viewerAttributes: this.file.viewerAttrs,
				previewImageSrc: this.file.urlPreview,
				context: this.viewerContext,
			});
		},
		isViewerAvailable(): boolean
		{
			return Object.keys(this.viewerAttributes).length > 0;
		},
		authorId(): number
		{
			return this.sidebarFileItem.authorId;
		},
	},
	methods:
	{
		download()
		{
			if (this.isViewerAvailable)
			{
				return;
			}

			window.open(this.file.urlDownload, '_blank');
		},
		onContextMenuClick(event)
		{
			this.$emit('contextMenuClick', {
				sidebarFile: this.sidebarFileItem,
				file: this.file,
				messageId: this.sidebarFileItem.messageId,
			}, event.currentTarget);
		},
	},
	template: `
		<div 
			class="bx-im-sidebar-file-detail-item__container bx-im-sidebar-file-detail-item__scope"
			@mouseover="showContextButton = true"
			@mouseleave="showContextButton = false"
		>
			<div class="bx-im-sidebar-file-detail-item__icon-container">
				<div :class="fileIconClass"><i></i></div>
			</div>
			<div class="bx-im-sidebar-file-detail-item__content-container" v-bind="viewerAttributes">
				<div class="bx-im-sidebar-file-detail-item__content">
					<div class="bx-im-sidebar-file-detail-item__file-title" @click="download" :title="file.name">
						<span class="bx-im-sidebar-file-detail-item__file-title-text" v-html="fileShortName"></span>
						<span class="bx-im-sidebar-file-detail-item__file-size">{{fileSize}}</span>
					</div>
					<div class="bx-im-sidebar-file-detail-item__author-container">
						<template v-if="authorId > 0">
							<MessageAvatar
								:messageId="sidebarFileItem.messageId"
								:authorId="sidebarFileItem.authorId"
								:size="AvatarSize.XS"
								class="bx-im-sidebar-file-detail-item__author-avatar"
							/>
							<ChatTitle :dialogId="authorId" :showItsYou="false" />
						</template>
						<span v-else class="bx-im-sidebar-file-detail-item__system-author-text">
							{{ $Bitrix.Loc.getMessage('IM_SIDEBAR_SYSTEM_USER') }}
						</span>
					</div>
				</div>
			</div>
			<button
				v-if="showContextButton"
				class="bx-im-messenger__context-menu-icon" 
				@click="onContextMenuClick"
			></button>
		</div>
	`,
};
