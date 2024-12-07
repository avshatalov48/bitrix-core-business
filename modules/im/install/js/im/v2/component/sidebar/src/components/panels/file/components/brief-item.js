import 'ui.icons';
import { Text } from 'main.core';

import { ImModelSidebarFileItem, ImModelFile } from 'im.v2.model';
import { Utils } from 'im.v2.lib.utils';
import { MessageAvatar, AvatarSize, ChatTitle } from 'im.v2.component.elements';
import { highlightText } from 'im.v2.lib.text-highlighter';

import '../css/brief-item.css';

// @vue/component
export const BriefItem = {
	name: 'BriefItem',
	components: { MessageAvatar, ChatTitle },
	props: {
		brief: {
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
	},
	emits: ['contextMenuClick'],
	data() {
		return {
			showContextButton: false,
		};
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		sidebarFileItem(): ImModelSidebarFileItem
		{
			return this.brief;
		},
		file(): ImModelFile
		{
			return this.$store.getters['files/get'](this.sidebarFileItem.fileId, true);
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
			return Utils.file.getViewerDataAttributes(this.file.viewerAttrs);
		},
		isViewerAvailable(): boolean
		{
			return Object.keys(this.viewerAttributes).length > 0;
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

			const urlToOpen = this.file.urlShow ? this.file.urlShow : this.file.urlDownload;
			window.open(urlToOpen, '_blank');
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
			class="bx-im-sidebar-brief-item__container bx-im-sidebar-brief-item__scope"
			@mouseover="showContextButton = true"
			@mouseleave="showContextButton = false"
		>
			<div class="bx-im-sidebar-brief-item__icon-container"></div>
			<div class="bx-im-sidebar-brief-item__content-container">
				<div class="bx-im-sidebar-brief-item__content">
					<div class="bx-im-sidebar-brief-item__title" @click="download" v-bind="viewerAttributes">
						<span class="bx-im-sidebar-brief-item__title-text" :title="file.name" v-html="fileShortName"></span>
						<span class="bx-im-sidebar-brief-item__size-text">{{fileSize}}</span>
					</div>
					<div class="bx-im-sidebar-brief-item__author-container">
						<MessageAvatar 
							:messageId="sidebarFileItem.messageId"
							:authorId="sidebarFileItem.authorId"
							:size="AvatarSize.XS"
							class="bx-im-sidebar-brief-item__author-avatar" 
						/>
						<ChatTitle :dialogId="sidebarFileItem.authorId" :showItsYou="false" />
					</div>
				</div>
			</div>
			<button
				v-if="showContextButton"
				class="bx-im-messenger__context-menu-icon bx-im-sidebar-brief-item__context-menu-button"
				@click="onContextMenuClick"
			></button>
		</div>
	`,
};
