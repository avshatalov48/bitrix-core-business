import {SidebarBlock, SidebarDetailBlock} from 'im.v2.const';
import {FilePreviewItem} from './item/file-preview-item';
import {DetailEmptyState} from '../detail-empty-state';
import '../../css/file/preview.css';

import type {ImModelDialog, ImModelSidebarFileItem} from 'im.v2.model';

// @vue/component
export const FilePreview = {
	name: 'FilePreview',
	components: {DetailEmptyState, FilePreviewItem},
	props: {
		isLoading: {
			type: Boolean,
			default: false
		},
		dialogId: {
			type: String,
			required: true
		}
	},
	computed:
	{
		SidebarDetailBlock: () => SidebarDetailBlock,
		files(): ImModelSidebarFileItem[]
		{
			if (this.isMigrationFinished)
			{
				return this.$store.getters['sidebar/files/getLatest'](this.chatId);
			}

			return this.$store.getters['sidebar/files/getLatestUnsorted'](this.chatId);
		},
		hasFiles(): boolean
		{
			return this.files.length > 0;
		},
		isMigrationFinished(): boolean
		{
			return this.$store.state.sidebar.isFilesMigrated;
		},
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
		dialogInited()
		{
			return this.dialog.inited;
		},
		isLoadingState(): boolean
		{
			return !this.dialogInited || this.isLoading;
		}
	},
	methods:
	{
		onOpenDetail()
		{
			if (!this.hasFiles)
			{
				return;
			}

			const block = this.isMigrationFinished ? SidebarBlock.file : SidebarBlock.fileUnsorted;
			const detailBlock = this.isMigrationFinished ? SidebarDetailBlock.media : SidebarDetailBlock.fileUnsorted;

			this.$emit('openDetail', {block, detailBlock});
		}
	},
	template: `
		<div class="bx-im-sidebar-file-preview__scope">
			<div v-if="isLoadingState" class="bx-im-sidebar-file-preview__skeleton"></div>
			<div v-else class="bx-im-sidebar-file-preview__container">
				<div 
					class="bx-im-sidebar-file-preview__header_container" 
					:class="[hasFiles ? '--active': '']" 
					@click="onOpenDetail"
				>
					<span class="bx-im-sidebar-file-preview__title-text">
						{{ $Bitrix.Loc.getMessage('IM_SIDEBAR_MEDIA_DETAIL_TITLE') }}
					</span>
					<div v-if="hasFiles" class="bx-im-sidebar__forward-icon"></div>
				</div>
				<div v-if="hasFiles" class="bx-im-sidebar-file-preview__files-container">
					<FilePreviewItem v-for="file in files" :fileItem="file" />
				</div>
				<DetailEmptyState
					v-else
					:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_MEDIA_AND_FILES_EMPTY')"
					:iconType="SidebarDetailBlock.media"
				/>
			</div>
		</div>
	`
};