import { EventEmitter } from 'main.core.events';

import { SidebarDetailBlock, EventType } from 'im.v2.const';

import { FilePreviewItem } from '../../file/components/file-preview-item';
import { DetailEmptyState } from '../../../elements/detail-empty-state/detail-empty-state';

import '../css/file.css';

import type { ImModelChat, ImModelSidebarFileItem } from 'im.v2.model';

// @vue/component
export const FilePreview = {
	name: 'FilePreview',
	components: { DetailEmptyState, FilePreviewItem },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
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
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
	},
	methods:
	{
		onOpenDetail()
		{
			if (!this.hasFiles)
			{
				return;
			}

			const panel = this.isMigrationFinished ? SidebarDetailBlock.file : SidebarDetailBlock.fileUnsorted;

			EventEmitter.emit(EventType.sidebar.open, {
				panel,
				dialogId: this.dialogId,
			});
		},
	},
	template: `
		<div class="bx-im-sidebar-file-preview__scope">
			<div class="bx-im-sidebar-file-preview__container">
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
	`,
};
