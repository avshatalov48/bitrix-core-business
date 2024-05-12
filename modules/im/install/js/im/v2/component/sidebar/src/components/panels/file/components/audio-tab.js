import { SidebarDetailBlock, SidebarFileTypes } from 'im.v2.const';
import { Loader } from 'im.v2.component.elements';

import { File } from '../../../../classes/panels/file';
import { DateGroup } from '../../../elements/date-group/date-group';
import { AudioDetailItem } from './audio-detail-item';
import { DetailEmptyState } from '../../../elements/detail-empty-state/detail-empty-state';
import { FileMenu } from '../../../../classes/context-menu/file/file-menu';
import { SidebarCollectionFormatter } from '../../../../classes/sidebar-collection-formatter';

import '../css/audio-tab.css';

import type { JsonObject } from 'main.core';
import type { ImModelChat, ImModelSidebarFileItem } from 'im.v2.model';

// @vue/component
export const AudioTab = {
	name: 'AudioTab',
	components: { DetailEmptyState, AudioDetailItem, DateGroup, Loader },
	props:
	{
		dialogId: {
			type: String,
			required: true,
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
			return this.$store.getters['sidebar/files/get'](this.chatId, SidebarFileTypes.audio);
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
	},
	created()
	{
		this.service = new File({ dialogId: this.dialogId });
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
		onContextMenuClick(event, target)
		{
			const item = {
				...event,
				dialogId: this.dialogId,
			};

			this.contextMenu.openMenu(item, target);
		},
		needToLoadNextPage(event: Event): boolean
		{
			const target = event.target;
			const isAtThreshold = target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;
			const hasNextPage = this.$store.getters['sidebar/files/hasNextPage'](this.chatId, SidebarFileTypes.audio);

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
			await this.service.loadNextPage(SidebarFileTypes.audio);
			this.isLoading = false;
		},
	},
	template: `
		<div class="bx-im-sidebar-file-audio-detail__scope bx-im-sidebar-detail__container" @scroll="onScroll">
			<div v-for="dateGroup in formattedCollection" class="bx-im-sidebar-file-audio-detail__date-group_container">
				<DateGroup :dateText="dateGroup.dateGroupTitle" />
				<AudioDetailItem
					v-for="file in dateGroup.items"
					:id="file.id"
					:fileItem="file"
					@contextMenuClick="onContextMenuClick"
				/>
			</div>
			<DetailEmptyState
				v-if="!isLoading && isEmptyState"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_FILES_EMPTY')"
				:iconType="SidebarDetailBlock.audio"
			/>
			<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
		</div>
	`,
};
