import { BaseEvent, EventEmitter } from 'main.core.events';

import { Logger } from 'im.v2.lib.logger';
import { LocalStorageManager } from 'im.v2.lib.local-storage';
import { EventType, LocalStorageKey, SidebarDetailBlock } from 'im.v2.const';

import { SidebarPanel } from './components/sidebar-panel';

import './css/icons.css';
import './css/sidebar.css';

import type { JsonObject } from 'main.core';

type SidebarPanelType = $Values<typeof SidebarDetailBlock>;

// @vue/component
export const ChatSidebar = {
	name: 'ChatSidebar',
	components: { SidebarPanel },
	props:
	{
		originDialogId: {
			type: String,
			required: true,
		},
		isActive: {
			type: Boolean,
			default: true,
		},
	},
	emits: ['changePanel'],
	data(): JsonObject
	{
		return {
			needTopLevelTransition: true,
			needSecondLevelTransition: true,

			topLevelPanelType: '',
			topLevelPanelDialogId: '',
			topLevelPanelStandalone: false,

			secondLevelPanelType: '',
			secondLevelPanelDialogId: '',
			secondLevelPanelEntityId: '',
			secondLevelPanelStandalone: false,
		};
	},
	computed:
	{
		SidebarDetailBlock: () => SidebarDetailBlock,
		topLevelTransitionName(): string
		{
			return this.needTopLevelTransition ? 'top-level-panel' : '';
		},
		secondLevelTransitionName(): string
		{
			return this.needSecondLevelTransition ? 'second-level-panel' : '';
		},
		canShowTopPanel(): boolean
		{
			const membersPanel = this.topLevelPanelType === SidebarDetailBlock.members;
			const personalChat = !this.originDialogId.startsWith('chat');
			if (membersPanel && personalChat)
			{
				return false;
			}

			const messageSearchPanel = this.topLevelPanelType === SidebarDetailBlock.messageSearch;

			return !messageSearchPanel;
		},
	},
	watch:
	{
		originDialogId(newValue: string, oldValue: string)
		{
			const chatSwitched = Boolean(newValue && oldValue);
			if (chatSwitched)
			{
				this.needTopLevelTransition = false;
			}

			if (!this.topLevelPanelStandalone)
			{
				this.updateTopPanelOriginDialogId(newValue);
			}

			const isSecondLevelPanelOpened = this.secondLevelPanelType.length > 0;
			if (isSecondLevelPanelOpened && !this.secondLevelPanelStandalone)
			{
				this.closeSecondLevelPanel();
			}

			if (!this.canShowTopPanel)
			{
				this.closeTopPanel();
			}
		},
		topLevelPanelType(newValue: SidebarPanelType, oldValue: SidebarPanelType)
		{
			this.needTopLevelTransition = oldValue.length === 0 || newValue.length === 0;

			const isMainPanelOpened = newValue === SidebarDetailBlock.main;
			this.saveSidebarOpenedState(isMainPanelOpened);
		},
		secondLevelPanelType(newValue: SidebarPanelType, oldValue: SidebarPanelType)
		{
			this.needSecondLevelTransition = !(newValue && oldValue);
		},
	},
	created()
	{
		Logger.warn('ChatSidebar: created');
		this.restoreOpenState();
	},
	mounted()
	{
		EventEmitter.subscribe(EventType.sidebar.open, this.onSidebarOpen);
		EventEmitter.subscribe(EventType.sidebar.close, this.onSidebarClose);
	},
	beforeUnmount()
	{
		EventEmitter.unsubscribe(EventType.sidebar.open, this.onSidebarOpen);
		EventEmitter.unsubscribe(EventType.sidebar.close, this.onSidebarClose);
	},
	methods:
	{
		onSidebarOpen(event: BaseEvent<{panel: SidebarPanelType, standalone: boolean, dialogId: string}>)
		{
			if (!this.isActive)
			{
				return;
			}
			const { panel = '', standalone = false, dialogId, entityId = '' } = event.getData();

			const needToCloseSecondLevelPanel = !standalone && panel && this.secondLevelPanelType === panel;
			if (needToCloseSecondLevelPanel)
			{
				this.closeSecondLevelPanel();

				return;
			}

			const needToOpenSecondLevelPanel = this.topLevelPanelType && this.topLevelPanelType !== panel;
			if (needToOpenSecondLevelPanel)
			{
				this.openSecondLevelPanel(panel, dialogId, standalone, entityId);
			}
			else
			{
				this.openTopPanel(panel, dialogId, standalone);
			}
		},
		onSidebarClose(event: BaseEvent<{panel: SidebarPanelType}>)
		{
			if (!this.isActive)
			{
				return;
			}
			this.needTopLevelTransition = true;

			const { panel = '' } = event.getData();
			const needToCloseSecondLevelPanel = panel && this.secondLevelPanelType === panel;
			if (needToCloseSecondLevelPanel)
			{
				this.closeSecondLevelPanel();
			}
			else
			{
				this.closeSecondLevelPanel();
				this.closeTopPanel();
			}
		},
		restoreOpenState()
		{
			const sidebarOpenState = LocalStorageManager.getInstance().get(LocalStorageKey.sidebarOpened);
			if (!sidebarOpenState)
			{
				return;
			}

			this.openTopPanel(SidebarDetailBlock.main, this.originDialogId, false);
		},
		saveSidebarOpenedState(sidebarOpened: boolean)
		{
			const WRITE_TO_STORAGE_TIMEOUT = 200;
			clearTimeout(this.saveSidebarStateTimeout);
			this.saveSidebarStateTimeout = setTimeout(() => {
				LocalStorageManager.getInstance().set(LocalStorageKey.sidebarOpened, sidebarOpened);
			}, WRITE_TO_STORAGE_TIMEOUT);
		},
		openTopPanel(type, dialogId, standalone = false)
		{
			this.topLevelPanelType = type;
			this.topLevelPanelDialogId = dialogId;
			this.topLevelPanelStandalone = standalone;
			this.$emit('changePanel', { panel: this.topLevelPanelType });
		},
		updateTopPanelOriginDialogId(dialogId: string)
		{
			this.topLevelPanelDialogId = dialogId;
		},
		openSecondLevelPanel(type, dialogId, standalone = false, entityId = '')
		{
			this.secondLevelPanelType = type;
			this.secondLevelPanelDialogId = dialogId;
			this.secondLevelPanelStandalone = standalone;
			this.secondLevelPanelEntityId = entityId;
			this.$emit('changePanel', { panel: this.secondLevelPanelType });
		},
		closeTopPanel()
		{
			this.topLevelPanelType = '';
			this.topLevelPanelDialogId = '';
			this.topLevelPanelStandalone = false;
			this.$emit('changePanel', { panel: '' });
		},
		closeSecondLevelPanel()
		{
			this.secondLevelPanelType = '';
			this.secondLevelPanelDialogId = '';
			this.secondLevelPanelStandalone = false;
			this.$emit('changePanel', { panel: this.topLevelPanelType });
		},
	},
	template: `
		<div class="bx-im-sidebar__container">
			<Transition :name="topLevelTransitionName">
				<SidebarPanel
					v-if="topLevelPanelType"
					:dialogId="topLevelPanelDialogId"
					:panel="topLevelPanelType"
				/>
			</Transition>
			<Transition :name="secondLevelTransitionName">
				<SidebarPanel
					v-if="secondLevelPanelType"
					:dialogId="secondLevelPanelDialogId" 
					:panel="secondLevelPanelType"
					:entityId="secondLevelPanelEntityId"
					:secondLevel="true"
				/>
			</Transition>
		</div>
	`,
};
