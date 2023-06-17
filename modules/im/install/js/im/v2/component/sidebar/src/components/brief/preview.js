import {SidebarBlock, SidebarDetailBlock, SidebarFileTypes} from 'im.v2.const';
import {FileMenu} from '../../classes/context-menu/file/file-menu';
import {DetailEmptyState} from '../detail-empty-state';
import {BriefItem} from './brief-item';
import '../../css/brief/preview.css';

import type {ImModelDialog, ImModelSidebarFileItem} from 'im.v2.model';

// @vue/component
export const BriefPreview = {
	name: 'BriefPreview',
	components: {DetailEmptyState, BriefItem},
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
	emits: ['openDetail'],
	data() {
		return {
			showAddButton: false
		};
	},
	computed:
	{
		SidebarDetailBlock: () => SidebarDetailBlock,
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
		firstBrief(): ?ImModelSidebarFileItem
		{
			return this.$store.getters['sidebar/files/get'](this.chatId, SidebarFileTypes.brief)[0];
		},
		isLoadingState(): boolean
		{
			return !this.dialogInited || this.isLoading;
		}
	},
	created()
	{
		this.contextMenu = new FileMenu();
	},
	beforeUnmount()
	{
		this.contextMenu.destroy();
	},
	methods:
	{
		onOpenDetail()
		{
			if (!this.firstBrief)
			{
				return;
			}

			this.$emit('openDetail', {block: SidebarBlock.brief, detailBlock: SidebarDetailBlock.brief});
		},
		onContextMenuClick(event, target)
		{
			const item = {
				...event,
				dialogId: this.dialogId
			};

			this.contextMenu.openMenu(item, target);
		}
	},
	template: `
		<div class="bx-im-sidebar-brief-preview__scope">
			<div v-if="isLoadingState" class="bx-im-sidebar-brief-preview__skeleton"></div>
			<div v-else class="bx-im-sidebar-brief-preview__container">
				<div 
					class="bx-im-sidebar-brief-preview__header_container" 
					:class="[firstBrief ? '--active': '']" 
					@click="onOpenDetail"
				>
					<span class="bx-im-sidebar-brief-preview__title-text">
						{{ $Bitrix.Loc.getMessage('IM_SIDEBAR_BRIEF_DETAIL_TITLE') }}
					</span>
					<div v-if="firstBrief" class="bx-im-sidebar__forward-icon"></div>
				</div>
				<BriefItem 
					v-if="firstBrief" 
					:brief="firstBrief"
					@contextMenuClick="onContextMenuClick"
				/>
				<DetailEmptyState 
					v-else 
					:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_BRIEFS_EMPTY')"
					:iconType="SidebarDetailBlock.brief"
				/>
			</div>
		</div>
	`
};