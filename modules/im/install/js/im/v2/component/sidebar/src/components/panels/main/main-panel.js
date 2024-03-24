import { Logger } from 'im.v2.lib.logger';
import { ImModelChat } from 'im.v2.model';

import { Main } from '../../../classes/panels/main';
import { getAvailableBlocks } from '../../../classes/panels/helpers/get-available-blocks';

import { InfoPreview } from './info';
import { FilePreview } from './file';
import { TaskPreview } from './task';
import { MainHeader } from './header';
import { MarketPreview } from './market';
import { MeetingPreview } from './meeting';
import { ChatPreview } from './chat-preview';
import { UserPreview } from './user-preview';
import { FileUnsortedPreview } from './file-unsorted';

import './css/main-panel.css';

import type { JsonObject } from 'main.core';

// @vue/component
export const MainPanel = {
	name: 'MainPanel',
	components: {
		MainHeader,
		ChatPreview,
		UserPreview,
		InfoPreview,
		FilePreview,
		TaskPreview,
		MeetingPreview,
		FileUnsortedPreview,
		MarketPreview,
	},
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
		blocks(): string[]
		{
			return getAvailableBlocks(this.dialogId);
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		dialogInited(): boolean
		{
			return this.dialog.inited;
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
		hasInitialData(): boolean
		{
			return this.$store.getters['sidebar/isInited'](this.chatId);
		},
	},
	watch:
	{
		chatId(newValue: number)
		{
			if (newValue > 0)
			{
				this.initializeSidebar();
			}
		},
	},
	created()
	{
		this.initializeSidebar();
	},
	methods:
	{
		getPreviewComponentName(block: string): string
		{
			return `${block}Preview`;
		},
		initializeSidebar()
		{
			if (this.hasInitialData)
			{
				this.isLoading = false;

				return;
			}
			this.sidebarService = new Main({ dialogId: this.dialogId });

			this.isLoading = true;
			this.sidebarService.requestInitialData().then(() => {
				this.isLoading = false;
			}).catch((error) => {
				Logger.warn('Sidebar: request initial data error:', error);
			});
		},
	},
	template: `
		<div class="bx-im-sidebar-main-panel__container">
			<MainHeader :dialogId="dialogId" />
			<div class="bx-im-sidebar-main-panel__blocks">
				<component
					v-for="block in blocks"
					:key="block"
					class="bx-im-sidebar-main-panel__block"
					:is="getPreviewComponentName(block)"
					:isLoading="isLoading"
					:dialogId="dialogId"
				/>
			</div>
		</div>
	`,
};
