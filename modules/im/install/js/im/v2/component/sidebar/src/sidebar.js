import {Logger} from 'im.v2.lib.logger';
import {ImModelDialog} from 'im.v2.model';
import {SidebarFileTabTypes} from 'im.v2.const';
import {SidebarService} from './classes/sidebar-service';
import {MainDetail} from './components/main/detail';
import {MainPreview} from './components/main/preview';
import {InfoPreview} from './components/info/preview';
import {LinkDetail} from './components/info/link-detail';
import {FavoriteDetail} from './components/info/favorite-detail';
import {MediaDetail} from './components/file/media-detail';
import {AudioDetail} from './components/file/audio-detail';
import {DocumentDetail} from './components/file/document-detail';
import {OtherDetail} from './components/file/other-detail';
import {FileUnsortedPreview} from './components/file-unsorted/preview';
import {FileUnsortedDetail} from './components/file-unsorted/detail';
import {FilePreview} from './components/file/preview';
import {TaskPreview} from './components/task/preview';
import {TaskDetail} from './components/task/detail';
import {BriefDetail} from './components/brief/detail';
import {BriefPreview} from './components/brief/preview';
import {MeetingPreview} from './components/meeting/preview';
import {MeetingDetail} from './components/meeting/detail';
import {SignPreview} from './components/sign/preview';
import {SignDetail} from './components/sign/detail';
import {MarketPreview} from './components/market/preview';
import {MarketDetail} from './components/market/detail';
import {SidebarHeader} from './components/header';
import {DetailHeader} from './components/detail-header';
import {DetailTabs} from './components/detail-tabs';
import {AvailabilityManager} from './classes/availability-manager';
import {SettingsManager} from './classes/settings-manager';
import './css/sidebar.css';
import './css/icons.css';

// @vue/component
export const ChatSidebar = {
	name: 'ChatSidebar',
	components: {
		DetailHeader, DetailTabs, SidebarHeader, MainDetail, MainPreview, InfoPreview, LinkDetail, FavoriteDetail, MediaDetail, AudioDetail, DocumentDetail, OtherDetail, FilePreview, TaskPreview,
		TaskDetail, BriefDetail, BriefPreview, MeetingPreview, MeetingDetail, SignPreview, SignDetail,
		FileUnsortedDetail, FileUnsortedPreview, MarketPreview, MarketDetail
	},
	props:
	{
		dialogId: {
			type: String,
			required: true
		},
		sidebarDetailBlock: {
			type: String,
			default: null
		}
	},
	emits: ['back'],
	data()
	{
		return {
			isLoading: false,
			detailBlock: null,
			detailBlockEntityId: null,
			detailTransition: 'right-panel-detail-transition'
		};
	},
	computed:
	{
		blocks(): string[]
		{
			return this.availabilityManager.getBlocks();
		},
		hasInitialData()
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
		getBlockServiceInstance()
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
		dialogInited()
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
	},
	watch:
	{
		sidebarDetailBlock(newValue: string, oldValue: string)
		{
			if (!oldValue && newValue)
			{
				this.detailBlock = newValue;
			}
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
			this.isLoading = true;
			if (!this.dialogInited)
			{
				return;
			}

			this.sidebarService.setChatId(this.chatId);
			this.sidebarService.setDialogId(this.dialogId);

			if (this.hasInitialData)
			{
				this.isLoading = false;

				return;
			}

			this.sidebarService.requestInitialData().then(() => {
				this.isLoading = false;
			});
		},
		onOpenDetail(data: Object)
		{
			const {detailBlock, entityId = ''} = data;
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
		},
		onTabSelect(tab: string)
		{
			this.detailBlock = tab;
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
				<DetailHeader :detailBlock="detailBlock" :dialogId="dialogId" :chatId="chatId" @back="onClickBack"/>
				<DetailTabs v-if="tabs.length > 0" :tabs="tabs" @tabSelect="onTabSelect" />
				<component
					:is="detailComponent"
					:dialogId="dialogId"
					:chatId="chatId"
					:detailBlock="detailBlock"
					:detailBlockEntityId="detailBlockEntityId"
					:service="getBlockServiceInstance"
					@back="onClickBack"
				/>
			</div>
		</transition> 
	`
};