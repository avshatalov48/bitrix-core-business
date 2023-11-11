import { Logger } from 'im.v2.lib.logger';
import { ImModelDialog } from 'im.v2.model';
import { SidebarDetailBlock, SidebarFileTabTypes } from 'im.v2.const';
import { SidebarService } from './classes/sidebar-service';
import { MainDetail } from './components/main/detail';
import { MainPreview } from './components/main/preview';
import { InfoPreview } from './components/info/preview';
import { LinkDetail } from './components/info/link-detail';
import { FavoriteDetail } from './components/info/favorite-detail';
import { MediaDetail } from './components/file/media-detail';
import { AudioDetail } from './components/file/audio-detail';
import { DocumentDetail } from './components/file/document-detail';
import { OtherDetail } from './components/file/other-detail';
import { FileUnsortedPreview } from './components/file-unsorted/preview';
import { FileUnsortedDetail } from './components/file-unsorted/detail';
import { FilePreview } from './components/file/preview';
import { MessageSearchDetail } from './components/message-search/detail';
import { TaskPreview } from './components/task/preview';
import { TaskDetail } from './components/task/detail';
import { BriefDetail } from './components/brief/detail';
import { BriefPreview } from './components/brief/preview';
import { MeetingPreview } from './components/meeting/preview';
import { MeetingDetail } from './components/meeting/detail';
import { SignPreview } from './components/sign/preview';
import { SignDetail } from './components/sign/detail';
import { MarketPreview } from './components/market/preview';
import { MarketDetail } from './components/market/detail';
import { SidebarHeader } from './components/header';
import { DetailHeader } from './components/detail-header';
import { DetailTabs } from './components/detail-tabs';
import { SearchHeader } from './components/message-search/search-header';
import { AvailabilityManager } from './classes/availability-manager';
import { SettingsManager } from './classes/settings-manager';
import './css/sidebar.css';
import './css/icons.css';

import type { JsonObject } from 'main.core';

// @vue/component
export const ChatSidebar = {
	name: 'ChatSidebar',
	components: {
		DetailHeader,
		DetailTabs,
		SidebarHeader,
		MainDetail,
		MainPreview,
		InfoPreview,
		LinkDetail,
		FavoriteDetail,
		MediaDetail,
		AudioDetail,
		DocumentDetail,
		OtherDetail,
		FilePreview,
		TaskPreview,
		TaskDetail,
		BriefDetail,
		BriefPreview,
		MeetingPreview,
		MeetingDetail,
		SignPreview,
		SignDetail,
		FileUnsortedDetail,
		FileUnsortedPreview,
		MarketPreview,
		MarketDetail,
		MessageSearchDetail,
		SearchHeader,
	},
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
		sidebarDetailBlock: {
			type: String,
			default: null,
		},
	},
	emits: ['back'],
	data(): JsonObject
	{
		return {
			isLoading: false,
			detailBlock: null,
			detailBlockEntityId: null,
			detailTransition: 'right-panel-detail-transition',
			query: '',
		};
	},
	computed:
	{
		blocks(): string[]
		{
			return this.availabilityManager.getBlocks();
		},
		hasInitialData(): boolean
		{
			return this.$store.getters['sidebar/isInited'](this.chatId);
		},
		detailComponent(): ?string
		{
			if (!this.detailBlock)
			{
				return null;
			}

			return `${this.detailBlock}Detail`;
		},
		getBlockServiceInstance(): Object
		{
			return this.sidebarService.getBlockInstance(this.detailBlock);
		},
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
		dialogInited(): boolean
		{
			return this.dialog.inited;
		},
		tabs(): string[]
		{
			if (SidebarFileTabTypes[this.detailBlock])
			{
				return Object.values(SidebarFileTabTypes);
			}

			return [];
		},
		needShowDefaultDetailHeader(): boolean
		{
			return this.detailBlock !== SidebarDetailBlock.messageSearch;
		},
	},
	watch:
	{
		sidebarDetailBlock(newValue: string)
		{
			this.detailBlock = newValue;
		},
		dialogInited(newValue: boolean, oldValue: boolean)
		{
			if (newValue === true && oldValue === false)
			{
				this.initializeSidebar();
			}
		},
	},
	created()
	{
		Logger.warn('Sidebar: Chat Sidebar created');
		this.settingsManager = new SettingsManager();
		this.availabilityManager = new AvailabilityManager(this.settingsManager, this.dialogId);
		this.sidebarService = new SidebarService(this.availabilityManager);
		this.initializeSidebar();
	},
	mounted()
	{
		if (this.sidebarDetailBlock)
		{
			this.detailBlock = this.sidebarDetailBlock;
			this.detailTransition = '';
		}
	},
	methods:
	{
		initializeSidebar()
		{
			if (!this.dialogInited)
			{
				this.isLoading = true;

				return;
			}

			this.sidebarService.setChatId(this.chatId);
			this.sidebarService.setDialogId(this.dialogId);

			if (this.hasInitialData)
			{
				return;
			}

			this.isLoading = true;
			this.sidebarService.requestInitialData().then(() => {
				this.isLoading = false;
			}).catch((error) => {
				Logger.warn('Sidebar: request initial data error:', error);
			});
		},
		onOpenDetail(data: Object)
		{
			const { detailBlock, entityId = '' } = data;
			this.detailBlock = detailBlock;
			this.detailBlockEntityId = entityId.toString();
		},
		getPreviewComponentName(block: string): string
		{
			return `${block}Preview`;
		},
		onClickBack()
		{
			this.detailBlock = null;
			this.detailTransition = 'right-panel-detail-transition';
			this.$emit('back');
			this.query = '';
		},
		onTabSelect(tab: string)
		{
			this.detailBlock = tab;
		},
		onChangeQuery(query)
		{
			this.query = query;
		},
	},
	template: `
		<SidebarHeader :isLoading="isLoading" :dialogId="dialogId" :chatId="chatId" />
		<div class="bx-im-sidebar__container bx-im-sidebar__scope">
			<component
				v-for="block in blocks"
				:key="block"
				class="bx-im-sidebar__box"
				:is="getPreviewComponentName(block)"
				:isLoading="isLoading"
				:dialogId="dialogId"
				@openDetail="onOpenDetail"
			/>
		</div>
		<transition :name="detailTransition">
			<div v-if="detailComponent && dialogInited" class="bx-im-sidebar__detail_container bx-im-sidebar__scope">
				<DetailHeader 
					v-if="needShowDefaultDetailHeader" 
					:detailBlock="detailBlock" 
					:dialogId="dialogId" 
					:chatId="chatId" 
					@back="onClickBack" 
				/>
				<SearchHeader v-else @changeQuery="onChangeQuery" @back="onClickBack" />
				<DetailTabs v-if="tabs.length > 0" :tabs="tabs" @tabSelect="onTabSelect" />
				<component
					:is="detailComponent"
					:dialogId="dialogId"
					:chatId="chatId"
					:detailBlock="detailBlock"
					:detailBlockEntityId="detailBlockEntityId"
					:service="getBlockServiceInstance"
					:searchQuery="query"
					@back="onClickBack"
				/>
			</div>
		</transition> 
	`,
};
