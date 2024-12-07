import { EventEmitter } from 'main.core.events';

import { Loader } from 'im.v2.component.elements';
import { EventType, SidebarDetailBlock, SidebarFileTypes } from 'im.v2.const';

import { DateGroup } from '../../elements/date-group/date-group';
import { DetailHeader } from '../../elements/detail-header/detail-header';
import { FileUnsorted } from '../../../classes/panels/file-unsorted';
import { DetailEmptyState } from '../../elements/detail-empty-state/detail-empty-state';
import { FileMenu } from '../../../classes/context-menu/file/file-menu';
import { TariffLimit } from '../../elements/tariff-limit/tariff-limit';
import { DocumentDetailItem } from '../file/components/document-detail-item';
import { SidebarCollectionFormatter } from '../../../classes/sidebar-collection-formatter';

import './detail.css';

import type { JsonObject } from 'main.core';
import type { ImModelSidebarFileItem, ImModelChat } from 'im.v2.model';

// @vue/component
export const FileUnsortedPanel = {
	name: 'FileUnsortedPanel',
	components: { DateGroup, DocumentDetailItem, DetailEmptyState, DetailHeader, Loader, TariffLimit },
	props: {
		dialogId: {
			type: String,
			required: true,
		},
		secondLevel: {
			type: Boolean,
			default: false,
		},
	},
	data(): JsonObject
	{
		return {
			isLoading: false,
		};
	},
	computed:
	{
		SidebarDetailBlock: () => SidebarDetailBlock,
		files(): ImModelSidebarFileItem[]
		{
			return this.$store.getters['sidebar/files/get'](this.chatId, SidebarFileTypes.fileUnsorted);
		},
		formattedCollection(): Array
		{
			return this.collectionFormatter.format(this.files);
		},
		isEmptyState(): boolean
		{
			return this.formattedCollection.length === 0;
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
		hasHistoryLimit(): boolean
		{
			return this.$store.getters['sidebar/files/isHistoryLimitExceeded'](this.chatId);
		},
	},
	created()
	{
		this.service = new FileUnsorted({ dialogId: this.dialogId });
		this.collectionFormatter = new SidebarCollectionFormatter();
		this.contextMenu = new FileMenu();
	},
	beforeUnmount()
	{
		this.collectionFormatter.destroy();
		this.contextMenu.destroy();
	},
	methods:
	{
		needToLoadNextPage(event: Event): boolean
		{
			const target = event.target;
			const isAtThreshold = target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;
			const hasNextPage = this.$store.getters['sidebar/files/hasNextPage'](this.chatId, SidebarFileTypes.fileUnsorted);

			return isAtThreshold && hasNextPage;
		},
		async onScroll(event: Event)
		{
			this.contextMenu.destroy();

			if (this.isLoading || !this.needToLoadNextPage(event))
			{
				return;
			}

			this.isLoading = true;
			await this.service.loadNextPage();
			this.isLoading = false;
		},
		onContextMenuClick(event, target)
		{
			const item = {
				...event,
				dialogId: this.dialogId,
			};

			this.contextMenu.openMenu(item, target);
		},
		onBackClick()
		{
			EventEmitter.emit(EventType.sidebar.close, { panel: SidebarDetailBlock.fileUnsorted });
		},
	},
	template: `
		<div class="bx-im-sidebar-file-unsorted-detail__scope">
			<DetailHeader
				:dialogId="dialogId"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_FILEUNSORTED_DETAIL_TITLE')"
				:secondLevel="secondLevel"
				@back="onBackClick"
			/>
			<div class="bx-im-sidebar-file-unsorted-detail__container bx-im-sidebar-detail__container" @scroll="onScroll">
				<div v-for="dateGroup in formattedCollection" class="bx-im-sidebar-file-unsorted-detail__date-group_container">
					<DateGroup :dateText="dateGroup.dateGroupTitle" />
					<DocumentDetailItem
						v-for="file in dateGroup.items"
						:fileItem="file"
						:contextDialogId="dialogId"
						@contextMenuClick="onContextMenuClick"
					/>
				</div>
				<TariffLimit
					v-if="hasHistoryLimit"
					:dialogId="dialogId"
					:panel="SidebarDetailBlock.fileUnsorted"
					class="bx-im-sidebar-file-unsorted-detail__tariff-limit-container"
				/>
				<DetailEmptyState
					v-if="!isLoading && isEmptyState"
					:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_FILES_EMPTY')"
					:iconType="SidebarDetailBlock.document"
				/>
				<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
			</div>
		</div>
	`,
};
