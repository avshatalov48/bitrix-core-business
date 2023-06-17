import {ImModelSidebarFileItem, ImModelFile} from 'im.v2.model';
import {Utils} from 'im.v2.lib.utils';
import {Avatar, AvatarSize, ChatTitle} from 'im.v2.component.elements';
import '../../../css/file/document-detail-item.css';
import 'ui.icons';

// @vue/component
export const DocumentDetailItem = {
	name: 'DocumentDetailItem',
	components: {Avatar, ChatTitle},
	props: {
		fileItem: {
			type: Object,
			required: true
		}
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
			const NAME_MAX_LENGTH = 21;

			return Utils.file.getShortFileName(this.file.name, NAME_MAX_LENGTH);
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
		authorId(): number
		{
			return this.sidebarFileItem.authorId;
		}
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
		}
	},
	template: `
		<div 
			class="bx-im-sidebar-file-document-detail-item__container bx-im-sidebar-file-document-detail-item__scope"
			@mouseover="showContextButton = true"
			@mouseleave="showContextButton = false"		
		>
			<div class="bx-im-sidebar-file-document-detail-item__icon-container">
				<div :class="fileIconClass"><i></i></div>
			</div>
			<div class="bx-im-sidebar-file-document-detail-item__content-container" v-bind="viewerAttributes">
				<div class="bx-im-sidebar-file-document-detail-item__content">
					<div class="bx-im-sidebar-file-document-detail-item__document-title" @click="download">
						<span class="bx-im-sidebar-file-document-detail-item__document-title-text">{{fileShortName}}</span>
						<span class="bx-im-sidebar-file-document-detail-item__document-size">{{fileSize}}</span>
					</div>
					<div class="bx-im-sidebar-file-document-detail-item__author-container">
						<template v-if="authorId > 0">
							<Avatar
								:dialogId="authorId"
								:size="AvatarSize.XS"
								:withStatus="false"
								class="bx-im-sidebar-file-document-detail-item__author-avatar"
							/>
							<ChatTitle :dialogId="authorId" :showItsYou="false" />
						</template>
						<span v-else class="bx-im-sidebar-file-document-detail-item__system-author-text">
							{{$Bitrix.Loc.getMessage('IM_SIDEBAR_SYSTEM_USER')}}
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
	`
};