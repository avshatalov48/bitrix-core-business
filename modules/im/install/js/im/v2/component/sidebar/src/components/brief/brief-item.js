import {ImModelSidebarFileItem, ImModelFile} from 'im.v2.model';
import {Utils} from 'im.v2.lib.utils';
import {Avatar, AvatarSize, ChatTitle} from 'im.v2.component.elements';
import '../../css/brief/brief-item.css';
import 'ui.icons';

// @vue/component
export const BriefItem = {
	name: 'BriefItem',
	components: {Avatar, ChatTitle},
	props: {
		brief: {
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
			return this.brief;
		},
		file(): ImModelFile
		{
			return this.$store.getters['files/get'](this.sidebarFileItem.fileId, true);
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
			class="bx-im-sidebar-brief-item__container bx-im-sidebar-brief-item__scope"
			@mouseover="showContextButton = true"
			@mouseleave="showContextButton = false"
		>
			<div class="bx-im-sidebar-brief-item__icon-container"></div>
			<div class="bx-im-sidebar-brief-item__content-container">
				<div class="bx-im-sidebar-brief-item__content">
					<div class="bx-im-sidebar-brief-item__title" @click="download" v-bind="viewerAttributes">
						<span class="bx-im-sidebar-brief-item__title-text" :title="file.name">{{fileShortName}}</span>
						<span class="bx-im-sidebar-brief-item__size-text">{{fileSize}}</span>
					</div>
					<div class="bx-im-sidebar-brief-item__author-container">
						<Avatar 
							:dialogId="sidebarFileItem.authorId" 
							:size="AvatarSize.XS" 
							:withStatus="false" 
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
	`
};